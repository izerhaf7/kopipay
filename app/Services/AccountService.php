<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Satu karyawan = satu akun login. User + employee dibuat
 * dalam satu transaksi supaya tidak ada akun tanpa profil.
 */
class AccountService
{
    protected EmployeeModel $employees;
    protected UserModel $users;
    protected ActivityLogModel $logs;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->employees = model(EmployeeModel::class);
        $this->users     = model(UserModel::class);
        $this->logs      = model(ActivityLogModel::class);
        $this->db        = db_connect();
    }

    /**
     * @return array{employee_id: int, code: string, email: string, password: string}
     *
     * @throws RuleViolationException
     */
    public function createEmployee(array $data, int $actorId): array
    {
        $code  = trim((string) ($data['employee_code'] ?? ''));
        $code  = $code !== '' ? $code : $this->employees->nextCode();
        $email = trim((string) ($data['email'] ?? ''));
        $email = $email !== '' ? strtolower($email) : strtolower(str_replace(' ', '', $code)) . '@kopipay.local';
        $temp  = $this->randomPassword();

        $this->db->transBegin();

        try {
            $users = model(UserModel::class)->withGroup('karyawan');

            $user = new User([
                'email'    => $email,
                'username' => $code,
                'password' => $temp,
            ]);
            $user->activate();
            $user->forcePasswordReset();

            $userId = (int) $users->insert($user, true);

            if ($userId === 0) {
                throw new RuleViolationException('Gagal membuat akun: ' . implode(' ', $users->errors()));
            }

            $employeeId = (int) $this->employees->insert([
                'user_id'       => $userId,
                'employee_code' => $code,
                'full_name'     => $data['full_name'],
                'phone'         => $data['phone'] ?? null,
                'address'       => $data['address'] ?? null,
                'position_id'   => (int) $data['position_id'],
                'shift_id'      => (int) $data['shift_id'],
                'hire_date'     => $data['hire_date'],
                'status'        => $data['status'] ?? 'aktif',
            ], true);

            if ($employeeId === 0) {
                throw new RuleViolationException('Gagal menyimpan data karyawan: ' . implode(' ', $this->employees->errors()));
            }

            $this->logs->write($actorId, 'employee.create', 'employee', $employeeId, 'Karyawan ' . $code . ' (' . $data['full_name'] . ') dibuat');

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();

            if ($e instanceof RuleViolationException) {
                throw $e;
            }

            throw new RuleViolationException('Gagal membuat karyawan: ' . $e->getMessage());
        }

        return ['employee_id' => $employeeId, 'code' => $code, 'email' => $email, 'password' => $temp];
    }

    /**
     * Update profil karyawan (tanpa menyentuh akun login).
     */
    public function updateEmployee(int $employeeId, array $data, int $actorId): void
    {
        $existing = $this->employees->find($employeeId);

        if ($existing === null) {
            throw new RuleViolationException('Karyawan tidak ditemukan.');
        }

        $this->employees->update($employeeId, [
            'employee_code' => $data['employee_code'],
            'full_name'     => $data['full_name'],
            'phone'         => $data['phone'] ?? null,
            'address'       => $data['address'] ?? null,
            'position_id'   => (int) $data['position_id'],
            'shift_id'      => (int) $data['shift_id'],
            'hire_date'     => $data['hire_date'],
            'status'        => $data['status'] ?? 'aktif',
        ]);

        if ($this->employees->errors() !== []) {
            throw new RuleViolationException(implode(' ', $this->employees->errors()));
        }

        // username mengikuti kode karyawan supaya konsisten saat login
        if ($existing->employee_code !== $data['employee_code']) {
            $this->users->update((int) $existing->user_id, ['username' => $data['employee_code']]);
        }

        $this->logs->write($actorId, 'employee.update', 'employee', $employeeId, 'Data karyawan diperbarui');
    }

    /**
     * Reset password oleh Admin → password sementara baru + wajib ganti saat login.
     *
     * @return array{code: string, password: string}
     */
    public function resetPassword(int $employeeId, int $actorId): array
    {
        $employee = $this->employees->withDeleted()->find($employeeId);

        if ($employee === null) {
            throw new RuleViolationException('Karyawan tidak ditemukan.');
        }

        $password = $this->randomPassword();
        $user     = $this->users->find((int) $employee->user_id);

        if ($user === null) {
            throw new RuleViolationException('Akun login karyawan tidak ditemukan.');
        }

        $user->setPassword($password);
        $user->forcePasswordReset();
        $user->activate();
        $this->users->save($user);

        $this->logs->write($actorId, 'employee.reset_password', 'employee', $employeeId, 'Password direset untuk ' . $employee->employee_code);

        return ['code' => $employee->employee_code, 'password' => $password];
    }

    /**
     * Aktif / nonaktifkan karyawan: status employee + users.active sekaligus.
     */
    public function setStatus(int $employeeId, string $status, int $actorId): void
    {
        if (! in_array($status, ['aktif', 'nonaktif'], true)) {
            throw new RuleViolationException('Status tidak valid.');
        }

        $employee = $this->employees->find($employeeId);

        if ($employee === null) {
            throw new RuleViolationException('Karyawan tidak ditemukan.');
        }

        $this->employees->update($employeeId, ['status' => $status]);
        $this->users->update((int) $employee->user_id, ['active' => $status === 'aktif' ? 1 : 0]);

        $this->logs->write($actorId, 'employee.toggle_status', 'employee', $employeeId, 'Status diubah menjadi ' . $status);
    }

    /**
     * Soft delete karyawan + nonaktifkan akun.
     */
    public function deleteEmployee(int $employeeId, int $actorId): void
    {
        $employee = $this->employees->find($employeeId);

        if ($employee === null) {
            throw new RuleViolationException('Karyawan tidak ditemukan.');
        }

        $this->employees->update($employeeId, ['status' => 'nonaktif']);
        $this->users->update((int) $employee->user_id, ['active' => 0]);
        $this->employees->delete($employeeId);

        $this->logs->write($actorId, 'employee.delete', 'employee', $employeeId, 'Karyawan ' . $employee->employee_code . ' dihapus (soft delete)');
    }

    /**
     * Ganti password milik sendiri.
     */
    public function changeOwnPassword(int $userId, string $newPassword): void
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new RuleViolationException('Akun tidak ditemukan.');
        }

        $user->setPassword($newPassword);
        $user->setForcePassReset(false);
        $this->users->save($user);

        $this->logs->write($userId, 'auth.change_password', 'user', $userId, 'Password diubah sendiri');
    }

    public function verifyPassword(int $userId, string $password): bool
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            return false;
        }

        return service('passwords')->verify($password, $user->password_hash);
    }

    public function randomPassword(): string
    {
        return 'Kopi' . random_int(1000, 9999) . '!';
    }
}
