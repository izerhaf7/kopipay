<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\PayrollModel;
use App\Models\PayrollPeriodModel;
use App\Services\PdfService;

/**
 * Slip gaji milik sendiri. employee_id selalu dari sesi,
 * tidak pernah dari parameter request (anti-IDOR).
 */
class PayslipController extends BaseController
{
    protected PayrollModel $payrolls;
    protected PayrollPeriodModel $periods;
    protected PdfService $pdf;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->payrolls = model(PayrollModel::class);
        $this->periods  = model(PayrollPeriodModel::class);
        $this->pdf      = new PdfService();
        $this->logs     = model(ActivityLogModel::class);
    }

    public function index()
    {
        $employee = current_employee();

        $builder = $this->payrolls
            ->select('payrolls.*, pp.period_month, pp.period_year, pp.start_date, pp.end_date, pp.status')
            ->join('payroll_periods pp', 'pp.id = payrolls.payroll_period_id')
            ->where('payrolls.employee_id', (int) $employee->id)
            ->where('pp.status', 'final');

        return view('employee/payslips/index', [
            'title'    => 'Slip Gaji',
            'employee' => $employee,
            'rows'     => $builder->orderBy('pp.period_year', 'DESC')
                ->orderBy('pp.period_month', 'DESC')
                ->paginate(10),
            'pager'    => $this->payrolls->pager,
        ]);
    }

    public function pdf(int $id)
    {
        $employee = current_employee();

        $payroll = $this->payrolls
            ->select('payrolls.*, pp.period_month, pp.period_year, pp.start_date, pp.end_date, pp.status')
            ->join('payroll_periods pp', 'pp.id = payrolls.payroll_period_id')
            ->where('payrolls.id', $id)
            ->where('payrolls.employee_id', (int) $employee->id)
            ->where('pp.status', 'final')
            ->first();

        // 404 (bukan 403) supaya keberadaan slip orang lain tidak bocor.
        if ($payroll === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $period = $this->periods->find((int) $payroll->payroll_period_id);

        if ($period === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $binary = $this->pdf->slipGaji($payroll->toArray(), $period);
        $name   = 'slip-gaji_' . str_replace('-', '', (string) $employee->employee_code) . '_'
            . $period['period_year'] . '-' . str_pad((string) $period['period_month'], 2, '0', STR_PAD_LEFT) . '.pdf';

        $this->logs->write(user_id(), 'payslip.download', 'payroll', $id, 'Karyawan mengunduh slip gaji sendiri');

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setBody($binary);
    }
}
