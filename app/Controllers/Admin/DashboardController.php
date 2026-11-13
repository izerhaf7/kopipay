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
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');

        $current = new \DateTimeImmutable($month . '-01');
        $start = $current->format('Y-m-01');
        $end = $current->format('Y-m-t');
        $summary = model(AttendanceModel::class)->monthlyChart($start, $end);

        return $this->response->setJSON([
            'month' => $month,
            'attendance' => ['hadir' => (int) ($summary['hadir'] ?? 0), 'tidak_hadir' => (int) ($summary['tidak_hadir'] ?? 0)],
            'punctuality' => ['tepat_waktu' => (int) ($summary['tepat_waktu'] ?? 0), 'terlambat' => (int) ($summary['terlambat'] ?? 0)],
            'total_records' => (int) ($summary['total_records'] ?? 0),
            'total_late_minutes' => (int) ($summary['total_late_minutes'] ?? 0),
        ]);
    }
}
