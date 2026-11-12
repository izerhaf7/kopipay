<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Exceptions\RuleViolationException;
use App\Models\AttendanceModel;
use App\Services\AttendanceService;

class AttendanceController extends BaseController
{
    protected AttendanceModel $attendances;
    protected AttendanceService $service;

    public function __construct()
    {
        $this->attendances = model(AttendanceModel::class);
        $this->service     = new AttendanceService();
    }

    public function index()
    {
        $employee = current_employee();
        $today    = $this->service->todayFor($employee);

        $month = (int) ($this->request->getGet('month') ?: date('n'));
        $year  = (int) ($this->request->getGet('year') ?: date('Y'));

        $builder = $this->attendances
            ->where('employee_id', (int) $employee->id)
            ->where('MONTH(work_date)', $month)
            ->where('YEAR(work_date)', $year);

        return view('employee/attendance/index', [
            'title'    => 'Absen',
            'employee' => $employee,
            'today'    => $today,
            'rows'     => $builder->orderBy('work_date', 'DESC')->paginate(20),
            'pager'    => $this->attendances->pager,
            'month'    => $month,
            'year'     => $year,
        ]);
    }

    public function checkIn()
    {
        $employee = current_employee();

        try {
            $record = $this->service->selfCheckIn($employee, $this->request->getIPAddress());
        } catch (RuleViolationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $message = 'Check-in tercatat pukul ' . jam_short($record['check_in']) . '.';

        if ((int) $record['late_minutes'] > 0) {
            $message .= ' Terlambat ' . menit_ke_jam((int) $record['late_minutes']) . '.';
        }

        return redirect()->back()->with('message', $message);
    }

    public function checkOut()
    {
        $employee = current_employee();

        try {
            $record = $this->service->selfCheckOut($employee, $this->request->getIPAddress());
        } catch (RuleViolationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('message', 'Check-out tercatat pukul ' . jam_short($record['check_out']) . '. Terima kasih!');
    }
}
