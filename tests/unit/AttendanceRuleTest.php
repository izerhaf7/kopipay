<?php

namespace Tests\Unit;

use App\Exceptions\RuleViolationException;
use App\Services\AttendanceService;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Aturan absensi: toleransi, shift lewat tengah malam, jendela check-in, IP.
 *
 * @internal
 */
final class AttendanceRuleTest extends CIUnitTestCase
{
    private array $settings = [
        'late_grace_minutes'    => 10,
        'checkin_early_minutes' => 60,
        'self_checkin_enabled'  => 1,
        'allowed_ip_ranges'     => '',
    ];

    private array $pagi = [
        'id' => 1, 'name' => 'Pagi',
        'start_time' => '07:00:00', 'end_time' => '15:00:00', 'tolerance_minutes' => 10, 'is_overnight' => 0,
    ];

    private array $malam = [
        'id' => 3, 'name' => 'Malam',
        'start_time' => '22:00:00', 'end_time' => '06:00:00', 'tolerance_minutes' => 10, 'is_overnight' => 1,
    ];

    private function service(?array $settings = null): AttendanceService
    {
        return new AttendanceService($settings ?? $this->settings);
    }

    private function at(string $dateTime): Time
    {
        return Time::parse($dateTime, 'Asia/Jakarta');
    }

    // ---------------------------------------------------------- keterlambatan

    public function testDalamToleransiTidakDihitung(): void
    {
        $this->assertSame(0, $this->service()->computeLateMinutes($this->at('2026-11-12 07:08:00'), '2026-11-12', $this->pagi));
    }

    public function testDiLuarToleransiDihitungPenuh(): void
    {
        $this->assertSame(12, $this->service()->computeLateMinutes($this->at('2026-11-12 07:12:00'), '2026-11-12', $this->pagi));
    }

    public function testCheckInLebihAwalTidakTerlambat(): void
    {
        $this->assertSame(0, $this->service()->computeLateMinutes($this->at('2026-11-12 06:30:00'), '2026-11-12', $this->pagi));
    }

    public function testToleransiMilikShiftMenimpaNilaiDefault(): void
    {
        $longgar = array_merge($this->pagi, ['tolerance_minutes' => 30]);
        $ketat   = array_merge($this->pagi, ['tolerance_minutes' => 5]);

        // default di pengaturan 10 menit, tapi shift punya angka sendiri
        $this->assertSame(0, $this->service()->computeLateMinutes($this->at('2026-11-12 07:20:00'), '2026-11-12', $longgar));
        $this->assertSame(8, $this->service()->computeLateMinutes($this->at('2026-11-12 07:08:00'), '2026-11-12', $ketat));
    }

    // -------------------------------------------------------- shift overnight

    public function testShiftOvernightBerakhirBesok(): void
    {
        [$start, $end, $overnight] = $this->service()->shiftBounds($this->malam, '2026-11-12');

        $this->assertTrue($overnight);
        $this->assertSame('2026-11-12 22:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-13 06:00:00', $end->format('Y-m-d H:i:s'));
    }

    public function testCheckInLewatTengahMalamMemakaiTanggalKemarin(): void
    {
        $workDate = $this->service()->resolveWorkDate($this->at('2026-11-12 00:30:00'), $this->malam);

        $this->assertSame('2026-11-11', $workDate);
    }

    public function testCheckInMalamHariIniMemakaiTanggalHariIni(): void
    {
        $workDate = $this->service()->resolveWorkDate($this->at('2026-11-12 23:05:00'), $this->malam);

        $this->assertSame('2026-11-12', $workDate);
    }

    public function testShiftBiasaSelaluMemakaiTanggalHariIni(): void
    {
        $this->assertSame(
            '2026-11-12',
            $this->service()->resolveWorkDate($this->at('2026-11-12 06:05:00'), $this->pagi)
        );
    }

    // ------------------------------------------------------ jendela check-in

    public function testJendelaDibukaTepatSatuJamSebelumShift(): void
    {
        $window = $this->service()->checkInWindow($this->pagi, '2026-11-12', $this->at('2026-11-12 06:00:00'));

        $this->assertTrue($window['open']);
        $this->assertSame('06:00', $window['window_start']);
        $this->assertSame('15:00', $window['window_end']);
    }

    public function testTerlaluAwalDitolakOlehJendela(): void
    {
        $window = $this->service()->checkInWindow($this->pagi, '2026-11-12', $this->at('2026-11-12 04:00:00'));

        $this->assertFalse($window['open']);
        $this->assertTrue($window['before']);
    }

    public function testSetelahShiftBerakhirDitolakOlehJendela(): void
    {
        $window = $this->service()->checkInWindow($this->pagi, '2026-11-12', $this->at('2026-11-12 15:30:00'));

        $this->assertFalse($window['open']);
        $this->assertTrue($window['after']);
    }

    public function testJendelaOvernightTerbukaSampaiPagiBerikutnya(): void
    {
        // Shift 22:00-06:00 dimulai 11 Nov; pukul 05:30 tanggal 12 masih di dalam jendela.
        $window = $this->service()->checkInWindow($this->malam, '2026-11-11', $this->at('2026-11-12 05:30:00'));

        $this->assertTrue($window['open']);
    }

}
