<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollSettingModel extends Model
{
    protected $table         = 'payroll_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'late_grace_minutes', 'late_deduction_per_minute', 'alpha_penalty',
        'self_checkin_enabled', 'checkin_early_minutes', 'allowed_ip_ranges',
        'company_name', 'company_address',
    ];
    protected $useTimestamps = true;

    /**
     * Selalu satu baris. Dibuat otomatis dengan nilai default bila belum ada.
     */
    public function getSettings(): array
    {
        $row = $this->orderBy('id', 'ASC')->first();

        if ($row === null) {
            $defaults = [
                'late_grace_minutes'        => 10,
                'late_deduction_per_minute' => 1000,
                'alpha_penalty'             => 50000,
                'self_checkin_enabled'      => 1,
                'checkin_early_minutes'     => 60,
                'allowed_ip_ranges'         => '',
                'company_name'              => 'KopiPay',
                'company_address'           => 'Jl. Kopi Harum No. 12, Bogor',
            ];

            $this->insert($defaults);

            return $this->find($this->getInsertID()) ?? $defaults;
        }

        return $row;
    }
}
