<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data master awal: jabatan, shift, dan pengaturan payroll.
 * Jalankan: php spark db:seed MasterSeeder
 */
class MasterSeeder extends Seeder
{
    public function run()
    {
        $positions = [
            ['name' => 'Barista', 'daily_rate' => 100000, 'description' => 'Meracik & menyajikan kopi'],
            ['name' => 'Barista Senior', 'daily_rate' => 115000, 'description' => 'Barista dengan tanggung jawab quality control'],
            ['name' => 'Kasir', 'daily_rate' => 95000, 'description' => 'Melayani pembayaran & pesanan'],
            ['name' => 'Kepala Shift', 'daily_rate' => 130000, 'description' => 'Mengawasi operasional satu shift'],
        ];

        foreach ($positions as $position) {
            if ($this->db->table('positions')->where('name', $position['name'])->countAllResults() === 0) {
                $this->db->table('positions')->insert($position);
            }
        }

        $shifts = [
            ['name' => 'Pagi', 'start_time' => '07:00:00', 'end_time' => '15:00:00', 'tolerance_minutes' => 15, 'is_overnight' => 0],
            ['name' => 'Siang', 'start_time' => '15:00:00', 'end_time' => '23:00:00', 'tolerance_minutes' => 15, 'is_overnight' => 0],
            ['name' => 'Malam', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'tolerance_minutes' => 15, 'is_overnight' => 1],
        ];

        foreach ($shifts as $shift) {
            if ($this->db->table('shifts')->where('name', $shift['name'])->countAllResults() === 0) {
                $this->db->table('shifts')->insert($shift);
            }
        }

        if ($this->db->table('payroll_settings')->countAllResults() === 0) {
            $this->db->table('payroll_settings')->insert([
                'late_grace_minutes'        => 15,
                'late_deduction_per_minute' => 1000,
                'alpha_penalty'             => 50000,
                'self_checkin_enabled'      => 1,
                'checkin_early_minutes'     => 60,
                'allowed_ip_ranges'         => '',
                'company_name'              => 'KopiPay Coffee & Kitchen',
                'company_address'           => 'Jl. Kopi Harum No. 12, Bogor, Jawa Barat',
            ]);
        }

        echo "  Master data (jabatan, shift, pengaturan) siap.\n";
    }
}
