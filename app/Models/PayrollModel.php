<?php

namespace App\Models;

use App\Entities\Payroll;
use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table         = 'payrolls';
    protected $primaryKey    = 'id';
    protected $returnType    = Payroll::class;
    protected $allowedFields = [
        'payroll_period_id', 'employee_id', 'employee_code', 'employee_name', 'position_name',
        'daily_rate', 'late_rate', 'alpha_penalty',
        'days_present', 'days_izin', 'days_sakit', 'days_alpha', 'total_late_minutes',
        'gross_salary', 'late_deduction', 'alpha_deduction', 'net_salary',
    ];
    protected $useTimestamps = true;

    public function byPeriod(int $periodId): array
    {
        return $this->where('payroll_period_id', $periodId)->orderBy('employee_name', 'ASC')->findAll();
    }

    public function findForPeriodEmployee(int $periodId, int $employeeId): ?Payroll
    {
        return $this->where('payroll_period_id', $periodId)
            ->where('employee_id', $employeeId)
            ->first();
    }

    public function totalNetForPeriod(int $periodId): int
    {
        $row = $this->db->table('payrolls')
            ->selectSum('net_salary', 'total')
            ->where('payroll_period_id', $periodId)
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }
}
