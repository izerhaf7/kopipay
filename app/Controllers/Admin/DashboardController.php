<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\PayrollModel;
use App\Models\PayrollPeriodModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $employees   = model(EmployeeModel::class);
        $attendances = model(AttendanceModel::class);
        $periods     = model(PayrollPeriodModel::class);
        $payrolls    = model(PayrollModel::class);

        $today = date('Y-m-d');
        $month = (int) date('n');
        $year  = (int) date('Y');

        $totalActive = $employees->where('status', 'aktif')->countAllResults();

        $hadir   = $attendances->countToday($today, 'hadir');
        $late    = $attendances->countToday($today, 'hadir', true);
        $izin    = $attendances->countToday($today, 'izin');
        $sakit   = $attendances->countToday($today, 'sakit');
        $alpha   = $attendances->countToday($today, 'alpha');
        $recorded = $hadir + $izin + $sakit + $alpha;

        $period   = $periods->findByMonthYear($month, $year);
        $monthNet = $period !== null ? $payrolls->totalNetForPeriod((int) $period['id']) : null;

        $open = $attendances->openRecords();

        $recent = $attendances->withEmployee()
            ->orderBy('attendances.work_date', 'DESC')
            ->orderBy('attendances.id', 'DESC')
            ->findAll(8);

        return view('admin/dashboard', [
            'title'     => 'Dashboard',
            'subtitle'  => 'Ringkasan operasional kedai hari ini',
            'totalActive' => $totalActive,
            'hadir'     => $hadir,
            'late'      => $late,
            'izin'      => $izin,
            'sakit'     => $sakit,
            'alpha'     => $alpha,
            'notRecorded' => max(0, $totalActive - $recorded),
            'monthNet'  => $monthNet,
            'period'    => $period,
            'openCount' => count($open),
            'recent'    => $recent,
            'chartMonth' => date('Y-m'),
        ]);
    }

    /**
     * Data JSON untuk grafik tren kehadiran.
     */
    public function chartData()
    {
        $month = (string) ($this->request->getGet('month') ?? date('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $month = (string) ($this->request->getGet('month') ?? date('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        // Grafik memakai enam bulan terakhir, bukan tren per hari.
        $current = new \DateTimeImmutable($month . '-01');
        $start   = $current->modify('-5 months')->format('Y-m-01');
        $end     = $current->format('Y-m-t');
        $rows    = model(AttendanceModel::class)->monthlyChart($start, $end);
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['month_key']] = $row;
        }

        $labels = [];
        $hadir  = [];
        $telat  = [];
        $menit  = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = $current->modify('-' . $i . ' months');
            $key   = $date->format('Y-m');
            $label = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][(int) $date->format('n') - 1] . ' ' . $date->format('Y');
            $labels[] = $label;
            $hadir[]  = (int) ($indexed[$key]['hadir'] ?? 0);
            $telat[]  = (int) ($indexed[$key]['terlambat'] ?? 0);
            $menit[]  = (int) ($indexed[$key]['total_late_minutes'] ?? 0);
        }

        return $this->response->setJSON([
            'month'  => $month,
            'labels' => $labels,
            'series' => [
                'hadir' => $hadir,
                'terlambat' => $telat,
                'menit_terlambat' => $menit,
            ],
        ]);
    }
}
