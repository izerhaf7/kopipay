<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollPeriodModel extends Model
{
    protected $table         = 'payroll_periods';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'period_month', 'period_year', 'start_date', 'end_date',
        'status', 'generated_by', 'generated_at', 'finalized_at',
    ];
    protected $useTimestamps = true;

    public function findByMonthYear(int $month, int $year): ?array
    {
        return $this->where('period_month', $month)->where('period_year', $year)->first();
    }

    public function isDateLocked(string $date): bool
    {
        return $this->where('status', 'final')
            ->where('start_date <=', $date)
            ->where('end_date >=', $date)
            ->countAllResults() > 0;
    }

    public function latest(int $limit = 12): array
    {
        return $this->orderBy('period_year', 'DESC')
            ->orderBy('period_month', 'DESC')
            ->findAll($limit);
    }
}
