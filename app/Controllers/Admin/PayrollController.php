<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\PayrollModel;
use App\Models\PayrollPeriodModel;
use App\Services\PayrollService;
use App\Services\PdfService;

class PayrollController extends BaseController
{
    protected PayrollPeriodModel $periods;
    protected PayrollModel $payrolls;
    protected PayrollService $service;
    protected PdfService $pdf;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->periods = model(PayrollPeriodModel::class);
        $this->payrolls = model(PayrollModel::class);
        $this->service = new PayrollService();
        $this->pdf     = new PdfService();
        $this->logs    = model(ActivityLogModel::class);
    }

    public function index()
    {
        $month = (int) ($this->request->getGet('month') ?: date('n'));
        $year  = (int) ($this->request->getGet('year') ?: date('Y'));

        $warnings = $this->service->warnings($month, $year);
        $periods  = $this->periods->latest(12);

        $totals = [];

        foreach ($periods as $period) {
            $totals[$period['id']] = $this->payrolls->totalNetForPeriod((int) $period['id']);
        }

        return view('admin/payroll/index', [
            'title'    => 'Payroll',
            'subtitle' => 'Generate, review, lalu finalisasi periode',
            'periods'  => $periods,
            'totals'   => $totals,
            'month'    => $month,
            'year'     => $year,
            'warnings' => $warnings,
        ]);
    }

    public function generate()
    {
        $month = (int) $this->request->getPost('month');
        $year  = (int) $this->request->getPost('year');

        try {
            $period = $this->service->generate($month, $year, (int) user_id());
        } catch (RuleViolationException $e) {
            return redirect()->to(site_url('admin/payroll'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/payroll/' . $period['id']))
            ->with('message', 'Payroll ' . periode_label($month, $year) . ' berhasil digenerate sebagai draft.');
    }

    public function show(int $id)
    {
        $period = $this->periods->find($id);

        if ($period === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rows  = $this->payrolls->byPeriod($id);
        $total = 0;

        foreach ($rows as $row) {
            $total += (int) $row->net_salary;
        }

        return view('admin/payroll/show', [
            'title'    => 'Payroll ' . periode_label((int) $period['period_month'], (int) $period['period_year']),
            'subtitle' => $period['start_date'] . ' s/d ' . $period['end_date'] . ' · status ' . strtoupper($period['status']),
            'period'   => $period,
            'rows'     => $rows,
            'total'    => $total,
        ]);
    }

    public function finalize(int $id)
    {
        $period = $this->periods->find($id);

        if ($period === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($period['status'] === 'final') {
            return redirect()->back()->with('error', 'Periode ini sudah final.');
        }

        $this->periods->update($id, [
            'status'       => 'final',
            'finalized_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logs->write(user_id(), 'payroll.finalize', 'payroll_period', $id, 'Finalisasi periode ' . periode_label((int) $period['period_month'], (int) $period['period_year']));

        return redirect()->to(site_url('admin/payroll/' . $id))
            ->with('message', 'Periode difinalisasi. Slip gaji sekarang terlihat oleh karyawan dan absensi pada periode ini terkunci.');
    }

    public function reopen(int $id)
    {
        $period = $this->periods->find($id);

        if ($period === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($period['status'] === 'draft') {
            return redirect()->back()->with('error', 'Periode ini masih draft.');
        }

        $this->periods->update($id, [
            'status'       => 'draft',
            'finalized_at' => null,
        ]);

        $this->logs->write(user_id(), 'payroll.reopen', 'payroll_period', $id, 'Buka kembali periode ' . periode_label((int) $period['period_month'], (int) $period['period_year']));

        return redirect()->to(site_url('admin/payroll/' . $id))
            ->with('message', 'Periode dibuka kembali sebagai draft. Slip tidak lagi terlihat oleh karyawan.');
    }

    public function slipPdf(int $payrollId)
    {
        $payroll = $this->payrolls->find($payrollId);

        if ($payroll === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $period = $this->periods->find((int) $payroll->payroll_period_id);

        if ($period === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $binary = $this->pdf->slipGaji($payroll->toArray(), $period);
        $name   = 'slip-gaji_' . str_replace('-', '', (string) $payroll->employee_code) . '_'
            . $period['period_year'] . '-' . str_pad((string) $period['period_month'], 2, '0', STR_PAD_LEFT) . '.pdf';

        $this->logs->write(user_id(), 'payroll.export_slip', 'payroll', $payrollId, 'Export slip ' . $payroll->employee_code);

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setBody($binary);
    }

    public function recapPdf(int $id)
    {
        $period = $this->periods->find($id);

        if ($period === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rows   = array_map(static fn ($row) => $row->toArray(), $this->payrolls->byPeriod($id));
        $binary = $this->pdf->rekapGaji($period, $rows);
        $name   = 'rekap-gaji_' . $period['period_year'] . '-' . str_pad((string) $period['period_month'], 2, '0', STR_PAD_LEFT) . '.pdf';

        $this->logs->write(user_id(), 'payroll.export_recap', 'payroll_period', $id, 'Export rekap ' . $id);

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setBody($binary);
    }
}
