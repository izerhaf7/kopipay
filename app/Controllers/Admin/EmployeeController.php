<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\EmployeeModel;
use App\Models\PositionModel;
use App\Models\ShiftModel;
use App\Services\AccountService;

class EmployeeController extends BaseController
{
    protected EmployeeModel $employees;
    protected PositionModel $positions;
    protected ShiftModel $shifts;
    protected AccountService $accounts;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->employees = model(EmployeeModel::class);
        $this->positions = model(PositionModel::class);
        $this->shifts    = model(ShiftModel::class);
        $this->accounts  = new AccountService();
        $this->logs      = model(ActivityLogModel::class);
    }

    public function index()
    {
        $q          = trim((string) $this->request->getGet('q'));
        $positionId = (int) $this->request->getGet('position_id');
        $shiftId    = (int) $this->request->getGet('shift_id');
        $status     = (string) $this->request->getGet('status');

        $builder = $this->employees->withRelations();

        if ($q !== '') {
            $builder->groupStart()
                ->like('employees.full_name', $q)
                ->orLike('employees.employee_code', $q)
                ->groupEnd();
        }

        if ($positionId > 0) {
            $builder->where('employees.position_id', $positionId);
        }

        if ($shiftId > 0) {
            $builder->where('employees.shift_id', $shiftId);
        }

        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $builder->where('employees.status', $status);
        }

        $sortMap = [
            'name' => 'employees.full_name', 'code' => 'employees.employee_code',
            'position' => 'positions.name', 'shift' => 'shifts.name',
            'hire_date' => 'employees.hire_date', 'status' => 'employees.status',
        ];
        $sort = (string) $this->request->getGet('sort');
        $dir  = strtoupper((string) $this->request->getGet('dir')) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = $sortMap[$sort] ?? 'employees.full_name';
        $perPage = max(5, min(50, (int) ($this->request->getGet('per_page') ?: 10)));

        return view('admin/employees/index', [
            'title'     => 'Karyawan',
            'subtitle'  => 'Data karyawan dan status akun',
            'employees' => $builder->orderBy($orderBy, $dir)->paginate($perPage),
            'pager'     => $this->employees->pager,
            'positions' => $this->positions->orderBy('name', 'ASC')->findAll(),
            'shifts'    => $this->shifts->orderBy('start_time', 'ASC')->findAll(),
            'filters'   => ['q' => $q, 'position_id' => $positionId, 'shift_id' => $shiftId, 'status' => $status, 'sort' => $sort, 'dir' => $dir, 'per_page' => $perPage],
        ]);
    }

    public function new()
    {
        return view('admin/employees/form', [
            'title'     => 'Karyawan Baru',
            'subtitle'  => 'Akun login dibuat otomatis dengan password sementara',
            'employee'  => null,
            'positions' => $this->positions->orderBy('name', 'ASC')->findAll(),
            'shifts'    => $this->shifts->orderBy('start_time', 'ASC')->findAll(),
            'nextCode'  => $this->employees->nextCode(),
        ]);
    }

    public function create()
    {
        $rules = [
            'full_name'   => 'required|max_length[120]',
            'employee_code' => 'permit_empty|max_length[20]|is_unique[employees.employee_code]',
            'phone'       => 'permit_empty|max_length[25]',
            'email'       => 'permit_empty|valid_email|is_unique[users.email]',
            'position_id' => 'required|is_natural_no_zero',
            'shift_id'    => 'required|is_natural_no_zero',
            'hire_date'   => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $result = $this->accounts->createEmployee($this->payload(), (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/employees'))
            ->with('message', 'Karyawan ' . $result['code'] . ' berhasil dibuat.')
            ->with('credentials', [
                'title'    => 'Password sementara (tampil sekali)',
                'code'     => $result['code'],
                'email'    => $result['email'],
                'password' => $result['password'],
                'note'     => 'Karyawan wajib mengganti password saat login pertama.',
            ]);
    }

    public function edit(int $id)
    {
        $employee = $this->employees->findWithRelations($id);

        if ($employee === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/employees/form', [
            'title'     => 'Edit Karyawan',
            'subtitle'  => 'Perubahan data tidak menghapus riwayat absensi & payroll',
            'employee'  => $employee,
            'positions' => $this->positions->orderBy('name', 'ASC')->findAll(),
            'shifts'    => $this->shifts->orderBy('start_time', 'ASC')->findAll(),
            'nextCode'  => $employee->employee_code,
        ]);
    }

    public function update(int $id)
    {
        $employee = $this->employees->find($id);

        if ($employee === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'full_name'     => 'required|max_length[120]',
            'employee_code' => 'required|max_length[20]|is_unique[employees.employee_code,id,' . $id . ']',
            'phone'         => 'permit_empty|max_length[25]',
            'position_id'   => 'required|is_natural_no_zero',
            'shift_id'      => 'required|is_natural_no_zero',
            'hire_date'     => 'required|valid_date[Y-m-d]',
            'status'        => 'required|in_list[aktif,nonaktif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->accounts->updateEmployee($id, $this->payload(), (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/employees'))->with('message', 'Data karyawan diperbarui.');
    }

    public function resetPassword(int $id)
    {
        try {
            $result = $this->accounts->resetPassword($id, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->to(site_url('admin/employees'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/employees'))
            ->with('message', 'Password ' . $result['code'] . ' berhasil direset.')
            ->with('credentials', [
                'title'    => 'Password sementara baru (tampil sekali)',
                'code'     => $result['code'],
                'email'    => '',
                'password' => $result['password'],
                'note'     => 'Karyawan wajib mengganti password saat login berikutnya.',
            ]);
    }

    public function toggleStatus(int $id)
    {
        $employee = $this->employees->find($id);

        if ($employee === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $newStatus = $employee->status === 'aktif' ? 'nonaktif' : 'aktif';

        try {
            $this->accounts->setStatus($id, $newStatus, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->to(site_url('admin/employees'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/employees'))
            ->with('message', 'Status ' . $employee->employee_code . ' diubah menjadi ' . $newStatus . '.');
    }

    public function delete(int $id)
    {
        try {
            $this->accounts->deleteEmployee($id, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->to(site_url('admin/employees'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/employees'))->with('message', 'Karyawan dihapus. Riwayat absensi & payroll tetap tersimpan.');
    }

    private function payload(): array
    {
        return [
            'employee_code' => trim((string) $this->request->getPost('employee_code')),
            'full_name'     => trim((string) $this->request->getPost('full_name')),
            'phone'         => trim((string) $this->request->getPost('phone')) ?: null,
            'address'       => trim((string) $this->request->getPost('address')) ?: null,
            'email'         => trim((string) $this->request->getPost('email')) ?: null,
            'position_id'   => (int) $this->request->getPost('position_id'),
            'shift_id'      => (int) $this->request->getPost('shift_id'),
            'hire_date'     => (string) $this->request->getPost('hire_date'),
            'status'        => (string) ($this->request->getPost('status') ?? 'aktif'),
        ];
    }
}
