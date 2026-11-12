<?php

namespace App\Services;

use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\PayrollModel;
use App\Models\PayrollPeriodModel;
use App\Models\PayrollSettingModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Generate payroll periode bulanan + rumus potongan.
 *
 * gross_salary    = days_present x daily_rate
 * late_deduction  = MIN(total_late_minutes x late_rate, gross_salary)
 * alpha_deduction = days_alpha x alpha_penalty
 * net_salary      = MAX(0, gross - late - alpha)
 */
class PayrollService
{
    protected PayrollPeriodModel $periods;
    protected PayrollModel $payrolls;
    protected AttendanceModel $attendances;
    protected EmployeeModel $employees;
    protected PayrollSettingModel $settingModel;
    protected ActivityLogModel $logs;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->periods      = model(PayrollPeriodModel::class);
        $this->payrolls     = model(PayrollModel::class);
        $this->attendances  = model(AttendanceModel::class);
        $this->employees    = model(EmployeeModel::class);
        $this->settingModel = model(PayrollSettingModel::class);
        $this->logs         = model(ActivityLogModel::class);
        $this->db           = db_connect();
    }

    /**
     * Rentang tanggal periode (awal bulan s.d. akhir bulan).
     *
     * @return array{start: string, end: string}
     */
    public function periodRange(int $month, int $year): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Rumus payroll — murni, tanpa I/O. Dipakai unit test.
     */
    public function calculate(array $row, array $settings): array
    {
        $present    = (int) ($row['days_present'] ?? 0);
        $izin       = (int) ($row['days_izin'] ?? 0);
        $sakit      = (int) ($row['days_sakit'] ?? 0);
        $alpha      = (int) ($row['days_alpha'] ?? 0);
        $lateTotal  = (int) ($row['total_late_minutes'] ?? 0);

        $dailyRate    = (int) ($row['daily_rate'] ?? 0);
        $lateRate     = (int) ($settings['late_deduction_per_minute'] ?? 0);
        $alphaPenalty = (int) ($settings['alpha_penalty'] ?? 0);

        $gross        = $present * $dailyRate;
        $lateDeduct   = min($lateTotal * $lateRate, $gross);
        $alphaDeduct  = $alpha * $alphaPenalty;
        $net          = max(0, $gross - $lateDeduct - $alphaDeduct);

        return [
            'days_present'       => $present,
            'days_izin'          => $izin,
            'days_sakit'         => $sakit,
            'days_alpha'         => $alpha,
            'total_late_minutes' => $lateTotal,
            'daily_rate'         => $dailyRate,
            'late_rate'          => $lateRate,
            'alpha_penalty'      => $alphaPenalty,
            'gross_salary'       => $gross,
            'late_deduction'     => $lateDeduct,
            'alpha_deduction'    => $alphaDeduct,
            'net_salary'         => $net,
        ];
    }

    /**
     * Peringatan sebelum generate (tidak memblokir).
     *
     * @return array{missing: array, open: array}
     */
    public function warnings(int $month, int $year): array
    {
        $range = $this->periodRange($month, $year);

        $withRecords = $this->db->table('attendances')
            ->distinct()
            ->select('employee_id')
            ->where('work_date >=', $range['start'])
            ->where('work_date <=', $range['end'])
            ->get()
            ->getResultArray();

        $ids = array_column($withRecords, 'employee_id');

        $builder = $this->db->table('employees')
            ->select('employees.id, employees.full_name, employees.employee_code')
            ->where('employees.status', 'aktif')
            ->where('employees.deleted_at', null);

        if ($ids !== []) {
            $builder->whereNotIn('employees.id', $ids);
        }

        $missing = $builder->get()->getResultArray();

        $open = $this->db->table('attendances a')
            ->select('a.id, a.work_date, a.check_in, e.full_name, e.employee_code')
            ->join('employees e', 'e.id = a.employee_id')
            ->where('a.status', 'hadir')
            ->where('a.check_out', null)
            ->where('a.work_date >=', $range['start'])
            ->where('a.work_date <=', $range['end'])
            ->orderBy('a.work_date', 'ASC')
            ->get()
            ->getResultArray();

        return ['missing' => $missing, 'open' => $open];
    }

    /**
     * Generate (atau generate ulang) payroll satu periode.
     *
     * @return array periode
     *
     * @throws RuleViolationException
     */
    public function generate(int $month, int $year, int $userId): array
    {
        if ($month < 1 || $month > 12) {
            throw new RuleViolationException('Bulan tidak valid.');
        }

        if ($year < 2000 || $year > 2100) {
            throw new RuleViolationException('Tahun tidak valid.');
        }

        $range   = $this->periodRange($month, $year);
        $settings = $this->settingModel->getSettings();
        $period  = $this->periods->findByMonthYear($month, $year);

        if ($period !== null && $period['status'] === 'final') {
            throw new RuleViolationException('Periode ' . periode_label($month, $year) . ' sudah final. Buka kembali periode untuk generate ulang.');
        }

        $rows = $this->attendances->summarize($range['start'], $range['end']);

        $this->db->transBegin();

        try {
            if ($period === null) {
                $this->periods->insert([
                    'period_month' => $month,
                    'period_year'  => $year,
                    'start_date'   => $range['start'],
                    'end_date'     => $range['end'],
                    'status'       => 'draft',
                    'generated_by' => $userId,
                    'generated_at' => date('Y-m-d H:i:s'),
                ]);
                $period = $this->periods->find($this->periods->getInsertID());
            } else {
                $this->periods->update($period['id'], [
                    'generated_by' => $userId,
                    'generated_at' => date('Y-m-d H:i:s'),
                ]);

                // Aman: hanya periode draft yang boleh digenerate ulang.
                $this->payrolls->where('payroll_period_id', $period['id'])->delete();
            }

            foreach ($rows as $row) {
                $calc = $this->calculate($row, $settings);

                $this->payrolls->insert(array_merge($calc, [
                    'payroll_period_id' => $period['id'],
                    'employee_id'       => (int) $row['employee_id'],
                    'employee_code'     => $row['employee_code'],
                    'employee_name'     => $row['full_name'],
                    'position_name'     => $row['position_name'],
                ]));
            }

            $this->logs->write($userId, 'payroll.generate', 'payroll_period', (int) $period['id'],
                'Generate payroll ' . periode_label($month, $year) . ' (' . count($rows) . ' karyawan)');

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }

        return $this->periods->find($period['id']);
    }
}
