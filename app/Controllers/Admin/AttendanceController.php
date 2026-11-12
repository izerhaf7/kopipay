<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\ShiftModel;
use App\Services\AttendanceService;

class AttendanceController extends BaseController
{
    protected AttendanceModel $attendances;
    protected EmployeeModel $employees;
    protected ShiftModel $shifts;
    protected AttendanceService $service;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->attendances = model(AttendanceModel::class);
        $this->employees   = model(EmployeeModel::class);
        $this->shifts      = model(ShiftModel::class);
        $this->service     = new AttendanceService();
        $this->logs        = model(ActivityLogModel::class);
    }

    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $month  = (int) $this->request->getGet('month');
        $year   = (int) $this->request->getGet('year');
        $status = (string) $this->request->getGet('status');
        $source = (string) $this->request->getGet('source');
        $open   = $this->request->getGet('open');
        $date   = (string) $this->request->getGet('date');

        $builder = $this->attendances->withEmployee();

        if ($q !== '') {
            $builder->groupStart()
                ->like('employees.full_name', $q)
                ->orLike('employees.employee_code', $q)
                ->groupEnd();
        }

        if ($date !== '') {
            $builder->where('attendances.work_date', $date);
        }

        if ($month >= 1 && $month <= 12) {
            $builder->where('MONTH(attendances.work_date)', $month);
        }

        if ($year > 2000) {
            $builder->where('YEAR(attendances.work_date)', $year);
        }

        if (in_array($status, ['hadir', 'izin', 'sakit', 'alpha'], true)) {
            $builder->where('attendances.status', $status);
        }

        if (in_array($source, ['self', 'admin'], true)) {
            $builder->where('attendances.source', $source);
        }

        if ($open === '1') {
            $builder->where('attendances.status', 'hadir')->where('attendances.check_out', null);
        }

        return view('admin/attendance/index', [
            'title'    => 'Absensi',
            'subtitle' => 'Pantau check-in mandiri, input izin/sakit/alpha, dan koreksi',
            'rows'     => $builder->orderBy('attendances.work_date', 'DESC')
                ->orderBy('attendances.id', 'DESC')
                ->paginate(15),
            'pager'    => $this->attendances->pager,
            'employees' => $this->employees->where('status', 'aktif')->orderBy('full_name', 'ASC')->findAll(),
            'filters'  => [
                'q' => $q, 'month' => $month, 'year' => $year,
                'status' => $status, 'source' => $source, 'open' => $open, 'date' => $date,
            ],
            'today'    => $this->service->now()->format('Y-m-d'),
        ]);
    }

    public function bulkInputForm()
    {
        return view('admin/attendance/input', [
            'title'     => 'Input Izin / Sakit / Alpha',
            'subtitle'  => 'Banyak karyawan sekaligus untuk satu tanggal',
            'employees' => $this->employees->where('status', 'aktif')->orderBy('full_name', 'ASC')->findAll(),
            'today'     => $this->service->now()->format('Y-m-d'),
        ]);
    }

    public function bulkInput()
    {
        $employeeIds = (array) ($this->request->getPost('employee_ids') ?? []);
        $status      = (string) $this->request->getPost('status');
        $date        = (string) $this->request->getPost('work_date');
        $notes       = trim((string) $this->request->getPost('notes')) ?: null;

        if ($employeeIds === []) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu karyawan.');
        }

        try {
            $result = $this->service->bulkInput($employeeIds, $status, $date, $notes, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/attendance?date=' . $date))
            ->with('message', sprintf('%d catatan dibuat, %d dilewati karena sudah ada.', $result['created'], $result['skipped']));
    }

    public function markAlpha()
    {
        $date = (string) $this->request->getPost('date');

        if ($date === '') {
            return redirect()->to(site_url('admin/attendance'))->with('error', 'Pilih tanggal terlebih dahulu.');
        }

        try {
            $created = $this->service->markAlpha($date, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->to(site_url('admin/attendance?date=' . $date))->with('error', $e->getMessage());
        }

        $message = $created > 0
            ? $created . ' karyawan ditandai alpha pada ' . tanggal_id($date) . '.'
            : 'Semua karyawan aktif sudah punya catatan pada ' . tanggal_id($date) . '.';

        return redirect()->to(site_url('admin/attendance?date=' . $date))->with('message', $message);
    }

    public function edit(int $id)
    {
        $row = $this->attendances->withEmployee()->where('attendances.id', $id)->first();

        if ($row === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/attendance/form', [
            'title'    => 'Koreksi Absensi',
            'subtitle' => 'Setiap koreksi tercatat di log aktivitas',
            'row'      => $row,
            'shifts'   => $this->shifts->orderBy('start_time', 'ASC')->findAll(),
        ]);
    }

    public function update(int $id)
    {
        $payload = [
            'id'        => $id,
            'shift_id'  => (int) $this->request->getPost('shift_id'),
            'status'    => (string) $this->request->getPost('status'),
            'check_in'  => (string) $this->request->getPost('check_in'),
            'check_out' => (string) $this->request->getPost('check_out'),
            'notes'     => trim((string) $this->request->getPost('notes')) ?: null,
        ];

        try {
            $this->service->adminSave($payload, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/attendance'))->with('message', 'Absensi diperbarui.');
    }

    public function delete(int $id)
    {
        $row = $this->attendances->find($id);

        if ($row === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            $this->service->assertPeriodNotFinal($row['work_date']);
        } catch (RuleViolationException $e) {
            return redirect()->to(site_url('admin/attendance'))->with('error', $e->getMessage());
        }

        $this->attendances->delete($id);
        $this->logs->write(user_id(), 'attendance.delete', 'attendance', $id, 'Absensi ' . $row['work_date'] . ' dihapus');

        return redirect()->to(site_url('admin/attendance'))->with('message', 'Catatan absensi dihapus.');
    }
}
