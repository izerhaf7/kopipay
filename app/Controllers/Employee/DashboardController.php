<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Models\PayrollModel;
use App\Services\AttendanceService;

class DashboardController extends BaseController
{
    public function index()
    {
        $employee = current_employee();
        $service  = new AttendanceService();
        $today    = $service->todayFor($employee);

        $payslips = model(PayrollModel::class)
            ->select('payrolls.*, pp.period_month, pp.period_year, pp.status')
            ->join('payroll_periods pp', 'pp.id = payrolls.payroll_period_id')
            ->where('payrolls.employee_id', (int) $employee->id)
            ->where('pp.status', 'final')
            ->orderBy('pp.period_year', 'DESC')
            ->orderBy('pp.period_month', 'DESC')
            ->findAll(3);

        return view('employee/dashboard', [
            'title'    => 'Beranda',
            'employee' => $employee,
            'today'    => $today,
            'payslips' => $payslips,
        ]);
    }
}
