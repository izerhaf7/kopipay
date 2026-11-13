<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Payroll extends Entity
{
    protected $casts = [
        'id'                 => 'integer',
        'payroll_period_id'  => 'integer',
        'employee_id'        => 'integer',
        'daily_rate'         => 'integer',
        'late_rate'          => 'integer',
        'izin_penalty'       => 'integer',
        'sakit_penalty'      => 'integer',
        'alpha_penalty'      => 'integer',
        'days_present'       => 'integer',
        'days_izin'          => 'integer',
        'days_sakit'         => 'integer',
        'days_alpha'         => 'integer',
        'total_late_minutes' => 'integer',
        'gross_salary'       => 'integer',
        'late_deduction'     => 'integer',
        'izin_deduction'     => 'integer',
        'sakit_deduction'    => 'integer',
        'alpha_deduction'    => 'integer',
        'net_salary'         => 'integer',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    public function totalDeduction(): int
    {
        return (int) $this->late_deduction + (int) ($this->izin_deduction ?? 0) + (int) ($this->sakit_deduction ?? 0) + (int) $this->alpha_deduction;
    }
}
