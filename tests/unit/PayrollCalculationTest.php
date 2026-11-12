<?php

namespace Tests\Unit;

use App\Services\PayrollService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Rumus payroll — kasus uji dari bagian 14.1 dokumen desain.
 *
 * @internal
 */
final class PayrollCalculationTest extends CIUnitTestCase
{
    private array $settings = [
        'late_grace_minutes'        => 10,
        'late_deduction_per_minute' => 1000,
        'alpha_penalty'             => 50000,
    ];

    private function calculate(array $row): array
    {
        return (new PayrollService())->calculate($row, $this->settings);
    }

    public function testContohDokumenMenghasilkan2305000(): void
    {
        $result = $this->calculate([
            'days_present'       => 24,
            'days_izin'          => 2,
            'days_sakit'         => 0,
            'days_alpha'         => 1,
            'total_late_minutes' => 45,
            'daily_rate'         => 100000,
        ]);

        $this->assertSame(2400000, $result['gross_salary']);
        $this->assertSame(45000, $result['late_deduction']);
        $this->assertSame(50000, $result['alpha_deduction']);
        $this->assertSame(2305000, $result['net_salary']);
    }

    public function testTanpaPotongan(): void
    {
        $result = $this->calculate([
            'days_present'       => 20,
            'total_late_minutes' => 0,
            'days_alpha'         => 0,
            'daily_rate'         => 95000,
        ]);

        $this->assertSame(1900000, $result['gross_salary']);
        $this->assertSame(0, $result['late_deduction']);
        $this->assertSame(1900000, $result['net_salary']);
    }

    public function testPotonganTidakMelebihiGross(): void
    {
        $result = $this->calculate([
            'days_present'       => 1,
            'total_late_minutes' => 600,
            'days_alpha'         => 0,
            'daily_rate'         => 100000,
        ]);

        // 600 menit x 1.000 = 600.000, dipotong maksimal sebesar gross.
        $this->assertSame(100000, $result['late_deduction']);
        $this->assertSame(0, $result['net_salary']);
    }

    public function testIzinDanSakitTidakMenambahMaupunMengurangi(): void
    {
        $result = $this->calculate([
            'days_present'       => 10,
            'days_izin'          => 3,
            'days_sakit'         => 2,
            'days_alpha'         => 0,
            'total_late_minutes' => 0,
            'daily_rate'         => 100000,
        ]);

        $this->assertSame(1000000, $result['gross_salary']);
        $this->assertSame(1000000, $result['net_salary']);
    }

    public function testNetTidakPernahNegatif(): void
    {
        $result = $this->calculate([
            'days_present'       => 1,
            'days_alpha'         => 5,
            'total_late_minutes' => 0,
            'daily_rate'         => 100000,
        ]);

        // gross 100.000, denda alpha 5 x 50.000 = 250.000
        $this->assertSame(250000, $result['alpha_deduction']);
        $this->assertSame(0, $result['net_salary']);
    }

    public function testRentangPeriodeAdalahAwalSampaiAkhirBulan(): void
    {
        $range = (new PayrollService())->periodRange(2, 2028); // 2028 kabisat

        $this->assertSame('2028-02-01', $range['start']);
        $this->assertSame('2028-02-29', $range['end']);
    }
}
