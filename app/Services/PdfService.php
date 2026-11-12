<?php

namespace App\Services;

use App\Models\PayrollSettingModel;

/**
 * Pembungkus TCPDF: controller cukup memanggil satu method dan
 * menerima string PDF untuk dikirim lewat response CI4.
 */
class PdfService
{
    protected PayrollSettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = model(PayrollSettingModel::class);
    }

    public function slipGaji(array $payroll, array $period): string
    {
        $html = view('pdf/payslip', [
            'p'       => $payroll,
            'period'  => $period,
            'company' => $this->company(),
        ]);

        $pdf = new \TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
        $this->setup($pdf, 'Slip Gaji ' . $payroll['employee_name']);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }

    public function rekapGaji(array $period, array $rows): string
    {
        $total = 0;

        foreach ($rows as $row) {
            $total += (int) $row['net_salary'];
        }

        $html = view('pdf/recap', [
            'period'  => $period,
            'rows'    => $rows,
            'total'   => $total,
            'company' => $this->company(),
        ]);

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $this->setup($pdf, 'Rekap Gaji ' . periode_label((int) $period['period_month'], (int) $period['period_year']));
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }

    private function setup(\TCPDF $pdf, string $title): void
    {
        $pdf->SetCreator('KopiPay');
        $pdf->SetAuthor($this->company()['name']);
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetFont('helvetica', '', 9);
    }

    public function company(): array
    {
        $settings = $this->settingModel->getSettings();

        return [
            'name'    => $settings['company_name'] ?? 'KopiPay',
            'address' => $settings['company_address'] ?? '',
        ];
    }
}
