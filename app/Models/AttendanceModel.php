<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table         = 'attendances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'employee_id', 'shift_id', 'work_date', 'check_in', 'check_out',
        'status', 'late_minutes', 'notes', 'recorded_by', 'source',
        'check_in_ip', 'check_out_ip',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id' => 'required|is_natural_no_zero',
        'work_date'   => 'required|valid_date[Y-m-d]',
        'status'      => 'required|in_list[hadir,izin,sakit,alpha]',
    ];

    public function withEmployee(): self
    {
        $this->select('attendances.*, employees.full_name, employees.employee_code,
                       positions.name AS position_name, shifts.name AS shift_name,
                       shifts.start_time, shifts.end_time')
            ->join('employees', 'employees.id = attendances.employee_id')
            ->join('positions', 'positions.id = employees.position_id')
            ->join('shifts', 'shifts.id = attendances.shift_id');

        return $this;
    }

    public function findForEmployeeDate(int $employeeId, string $workDate): ?array
    {
        return $this->where('employee_id', $employeeId)
            ->where('work_date', $workDate)
            ->first();
    }

    /**
     * Rekap absensi per karyawan dalam satu rentang tanggal (untuk payroll).
     */
    public function summarize(string $start, string $end): array
    {
        return $this->db->table('attendances a')
            ->select('e.id AS employee_id, e.employee_code, e.full_name,
                      p.name AS position_name, p.daily_rate,
                      SUM(a.status = "hadir") AS days_present,
                      SUM(a.status = "izin")  AS days_izin,
                      SUM(a.status = "sakit") AS days_sakit,
                      SUM(a.status = "alpha") AS days_alpha,
                      COALESCE(SUM(a.late_minutes), 0) AS total_late_minutes', false)
            ->join('employees e', 'e.id = a.employee_id')
            ->join('positions p', 'p.id = e.position_id')
            ->where('a.work_date >=', $start)
            ->where('a.work_date <=', $end)
            ->groupBy('e.id')
            ->orderBy('e.full_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Rekap enam bulan terakhir untuk grafik ringkasan dashboard.
     */
    public function monthlyChart(string $start, string $end): array
    {
        return $this->db->table('attendances')
            ->select('DATE_FORMAT(work_date, "%Y-%m") AS month_key,
                      SUM(status IN ("hadir", "izin", "sakit", "alpha")) AS total_records,
                      SUM(status = "hadir") AS hadir,
                      SUM(status = "hadir" AND late_minutes > 0) AS terlambat,
                      COALESCE(SUM(late_minutes), 0) AS total_late_minutes', false)
            ->where('work_date >=', $start)
            ->where('work_date <=', $end)
            ->groupBy('month_key')
            ->orderBy('month_key', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Pasangan tanggal untuk grafik harian lama.
     */
    public function dailyChart(string $start, string $end): array
    {
        return $this->db->table('attendances')
            ->select('work_date,
                      SUM(status = "hadir" AND late_minutes = 0) AS tepat_waktu,
                      SUM(status = "hadir" AND late_minutes > 0) AS terlambat,
                      SUM(status = "izin") AS izin,
                      SUM(status = "sakit") AS sakit,
                      SUM(status = "alpha") AS alpha', false)
            ->where('work_date >=', $start)
            ->where('work_date <=', $end)
            ->groupBy('work_date')
            ->orderBy('work_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function countToday(string $date, ?string $status = null, ?bool $lateOnly = null): int
    {
        $builder = $this->db->table('attendances')->where('work_date', $date);

        if ($status !== null) {
            $builder->where('status', $status);
        }

        if ($lateOnly === true) {
            $builder->where('late_minutes >', 0);
        }

        return $builder->countAllResults();
    }

    public function openRecords(): array
    {
        return $this->withEmployee()
            ->where('attendances.status', 'hadir')
            ->where('attendances.check_out', null)
            ->orderBy('attendances.work_date', 'DESC')
            ->findAll();
    }
}
