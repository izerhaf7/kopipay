<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Group, permission, pemetaan group→permission, dan 1 akun Admin awal.
 * Jalankan: php spark db:seed AuthSeeder
 */
class AuthSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // ---------------------------------------------------------- groups
        $groups = [
            ['name' => 'admin', 'description' => 'Admin/Owner: akses penuh'],
            ['name' => 'karyawan', 'description' => 'Karyawan/Kasir: check-in/out, profil & slip sendiri'],
        ];

        foreach ($groups as $group) {
            $exists = $this->db->table('auth_groups')->where('name', $group['name'])->countAllResults();

            if ($exists === 0) {
                $this->db->table('auth_groups')->insert($group);
            }
        }

        // ----------------------------------------------------- permissions
        $permissions = [
            'dashboard.admin'     => 'Melihat dashboard ringkasan',
            'positions.manage'    => 'CRUD jabatan & tarif',
            'shifts.manage'       => 'CRUD shift kerja',
            'employees.manage'    => 'CRUD karyawan',
            'users.manage'        => 'CRUD akun user & reset password',
            'attendance.view'     => 'Melihat & mencari absensi',
            'attendance.manage'   => 'Input izin/sakit/alpha, koreksi & hapus absensi',
            'payroll.view'        => 'Melihat daftar payroll & slip semua karyawan',
            'payroll.generate'    => 'Generate payroll periode',
            'payroll.finalize'    => 'Finalisasi / buka kembali periode',
            'payroll.export'      => 'Export slip & rekap ke PDF',
            'settings.manage'     => 'Ubah pengaturan potongan & identitas usaha',
            'logs.view'           => 'Melihat log aktivitas',
            'profile.view-own'    => 'Melihat profil sendiri',
            'attendance.self'     => 'Check-in / check-out untuk diri sendiri',
            'attendance.view-own' => 'Melihat riwayat absensi sendiri',
            'slip.view-own'       => 'Melihat & unduh slip gaji sendiri',
        ];

        foreach ($permissions as $name => $description) {
            $exists = $this->db->table('auth_permissions')->where('name', $name)->countAllResults();

            if ($exists === 0) {
                $this->db->table('auth_permissions')->insert([
                    'name'        => $name,
                    'description' => $description,
                ]);
            }
        }

        // ------------------------------------------- group → permission
        $adminPermissions = array_values(array_filter(array_keys($permissions), static function (string $name): bool {
            return ! str_ends_with($name, '-own') && $name !== 'attendance.self';
        }));

        $employeePermissions = [
            'profile.view-own',
            'attendance.self',
            'attendance.view-own',
            'slip.view-own',
        ];

        $this->syncGroupPermissions('admin', $adminPermissions);
        $this->syncGroupPermissions('karyawan', $employeePermissions);

        // ------------------------------------------------ akun admin awal
        $username = 'admin';
        $email    = env('ADMIN_INITIAL_EMAIL', 'admin@kopipay.local');
        $password = env('ADMIN_INITIAL_PASSWORD', 'KopiPay!2026');

        $users = model(\App\Models\UserModel::class);

        if ($users->where('username', $username)->first() === null) {
            $user = new \App\Entities\User([
                'email'    => $email,
                'username' => $username,
                'password' => $password,
            ]);
            $user->activate();
            $user->forcePasswordReset();

            $userId = $users->withGroup('admin')->insert($user, true);

            echo "  Akun admin dibuat: {$username} / {$password} (wajib ganti password saat login)\n";
        } else {
            echo "  Akun admin sudah ada, dilewati.\n";

            $userId = (int) $users->where('username', $username)->first()->id;
        }

        // pastikan admin memang di group admin
        $adminGroup = $this->db->table('auth_groups')->where('name', 'admin')->get()->getRowArray();

        if ($adminGroup !== null) {
            $exists = $this->db->table('auth_groups_users')
                ->where('group_id', $adminGroup['id'])
                ->where('user_id', $userId)
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('auth_groups_users')->insert([
                    'group_id'   => $adminGroup['id'],
                    'user_id'    => $userId,
                    'created_at' => $now,
                ]);
            }
        }
    }

    /**
     * @param list<string> $permissionNames
     */
    private function syncGroupPermissions(string $groupName, array $permissionNames): void
    {
        $group = $this->db->table('auth_groups')->where('name', $groupName)->get()->getRowArray();

        if ($group === null) {
            return;
        }

        $this->db->table('auth_groups_permissions')->where('group_id', $group['id'])->delete();

        if ($permissionNames === []) {
            return;
        }

        $permissions = $this->db->table('auth_permissions')
            ->whereIn('name', $permissionNames)
            ->get()
            ->getResultArray();

        $rows = [];

        foreach ($permissions as $permission) {
            $rows[] = ['group_id' => $group['id'], 'permission_id' => $permission['id']];
        }

        if ($rows !== []) {
            $this->db->table('auth_groups_permissions')->insertBatch($rows);
        }
    }
}
