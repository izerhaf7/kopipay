<?php

namespace App\Services;

use App\Entities\Employee;
use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\PayrollPeriodModel;
use App\Models\PayrollSettingModel;
use App\Models\ShiftModel;
use CodeIgniter\I18n\Time;

/**
 * Semua logika absensi: keterlambatan, jendela check-in, check-in/out mandiri,
 * penandaan alpha, dan koreksi oleh Admin.
 */
class AttendanceService
{
    public const TZ = 'Asia/Jakarta';

    protected AttendanceModel $attendances;
    protected EmployeeModel $employees;
    protected ShiftModel $shifts;
    protected PayrollPeriodModel $periods;
    protected PayrollSettingModel $settingModel;
    protected ActivityLogModel $logs;
    protected array $settings;

    /**
     * @param array|null $settings Nilai payroll_settings; diisi manual oleh unit test
     *                             supaya aturan waktu bisa diuji tanpa menyentuh DB.
     */
    public function __construct(?array $settings = null)
    {
        $this->attendances  = model(AttendanceModel::class);
        $this->employees    = model(EmployeeModel::class);
        $this->shifts       = model(ShiftModel::class);
        $this->periods      = model(PayrollPeriodModel::class);
        $this->settingModel = model(PayrollSettingModel::class);
        $this->logs         = model(ActivityLogModel::class);
        $this->settings     = $settings ?? $this->settingModel->getSettings();
    }

    public function settings(): array
    {
        return $this->settings;
    }

    // -----------------------------------------------------------------
    // Waktu & shift
    // -----------------------------------------------------------------

    public function now(): Time
    {
        return Time::now(self::TZ);
    }

    public function parseDateTime(string $date, string $time): \DateTimeImmutable
    {
        $time = strlen($time) === 5 ? $time . ':00' : $time;

        return new \DateTimeImmutable($date . ' ' . $time, new \DateTimeZone(self::TZ));
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable, 2: bool} [mulai, selesai, overnight]
     */
    public function shiftBounds(array $shift, string $workDate): array
    {
        $start = $this->parseDateTime($workDate, (string) $shift['start_time']);
        $end   = $this->parseDateTime($workDate, (string) $shift['end_time']);

        $overnight = (int) ($shift['is_overnight'] ?? 0) === 1 || $end <= $start;

        if ($overnight) {
            $end = $end->modify('+1 day');
        }

        return [$start, $end, $overnight];
    }

    /**
     * Tanggal kerja (tanggal mulainya shift) untuk waktu tertentu.
     * Shift overnight yang masih berjalan pagi ini dihitung sebagai kemarin.
     */
    public function resolveWorkDate(\DateTimeInterface $now, array $shift): string
    {
        [, $end, $overnight] = $this->shiftBounds($shift, $now->format('Y-m-d'));

        if (! $overnight) {
            return $now->format('Y-m-d');
        }

        // Shift malam yang dimulai kemarin masih berjalan bila sekarang
        // belum melewati jam selesai shift (mis. 00:30 < 06:00).
        $today = \DateTimeImmutable::createFromInterface($now);

        return $now->format('H:i:s') < $end->format('H:i:s')
            ? $today->modify('-1 day')->format('Y-m-d')
            : $today->format('Y-m-d');
    }

    /**
     * Menit keterlambatan. Dalam toleransi = 0; di luar toleransi dihitung
     * penuh dari jam mulai shift (sesuai bagian 6.1 dokumen desain).
     */
    public function computeLateMinutes(\DateTimeInterface $checkIn, string $workDate, array $shift): int
    {
        [$start] = $this->shiftBounds($shift, $workDate);

        $minutes = (int) floor(($checkIn->getTimestamp() - $start->getTimestamp()) / 60);

        if ($minutes <= 0) {
            return 0;
        }

        // Toleransi menempel pada shift; nilai di pengaturan hanya default/fallback.
        $grace = isset($shift['tolerance_minutes'])
            ? (int) $shift['tolerance_minutes']
            : (int) ($this->settings['late_grace_minutes'] ?? 0);

        return $minutes <= $grace ? 0 : $minutes;
    }

    /**
     * Status jendela check-in untuk sebuah shift pada tanggal kerja tertentu.
     *
     * @return array{open: bool, before: bool, after: bool, window_start: string, window_end: string, shift_start: string, shift_end: string}
     */
    public function checkInWindow(array $shift, string $workDate, ?\DateTimeInterface $now = null): array
    {
        $now         = $now ?? $this->now();
        [$start, $end] = $this->shiftBounds($shift, $workDate);
        $early       = (int) ($this->settings['checkin_early_minutes'] ?? 60);
        $windowStart = $start->modify('-' . $early . ' minutes');

        $before = $now->getTimestamp() < $windowStart->getTimestamp();
        $after  = $now->getTimestamp() > $end->getTimestamp();

        return [
            'open'         => ! $before && ! $after,
            'before'       => $before,
            'after'        => $after,
            'window_start' => $windowStart->format('H:i'),
            'window_end'   => $end->format('H:i'),
            'shift_start'  => $start->format('H:i'),
            'shift_end'    => $end->format('H:i'),
        ];
    }

    // -----------------------------------------------------------------
    // Check-in / check-out mandiri
    // -----------------------------------------------------------------

    /**
     * @throws RuleViolationException
     */
    public function selfCheckIn(Employee $employee, string $ip): array
    {
        if ((int) ($this->settings['self_checkin_enabled'] ?? 0) !== 1) {
            throw new RuleViolationException('Check-in mandiri sedang dinonaktifkan oleh Admin.');
        }

        if ($employee->status !== 'aktif') {
            throw new RuleViolationException('Akun karyawan tidak aktif. Hubungi Admin.');
        }

        $this->assertIpAllowed($ip);

        $shift = $this->shifts->find((int) $employee->shift_id);

        if ($shift === null) {
            throw new RuleViolationException('Shift karyawan belum diatur. Hubungi Admin.');
        }

        $now      = $this->now();
        $workDate = $this->resolveWorkDate($now, $shift);
        $window   = $this->checkInWindow($shift, $workDate, $now);

        if ($window['before']) {
            throw new RuleViolationException('Belum waktunya check-in. Check-in dibuka pukul ' . $window['window_start'] . '.');
        }

        if ($window['after']) {
            throw new RuleViolationException('Shift hari ini sudah berakhir pukul ' . $window['window_end'] . '. Hubungi Admin untuk koreksi absensi.');
        }

        $this->assertPeriodNotFinal($workDate);

        if ($this->attendances->findForEmployeeDate((int) $employee->id, $workDate) !== null) {
            throw new RuleViolationException('Anda sudah memiliki catatan absensi untuk hari kerja ini.');
        }

        $id = $this->attendances->insert([
            'employee_id'  => (int) $employee->id,
            'shift_id'     => (int) $shift['id'],
            'work_date'    => $workDate,
            'check_in'     => $now->format('Y-m-d H:i:s'),
            'check_out'    => null,
            'status'       => 'hadir',
            'late_minutes' => $this->computeLateMinutes($now, $workDate, $shift),
            'notes'        => null,
            'recorded_by'  => (int) user_id(),
            'source'       => 'self',
            'check_in_ip'  => $ip,
        ], true);

        return $this->attendances->find($id) ?? [];
    }

    /**
     * @throws RuleViolationException
     */
    public function selfCheckOut(Employee $employee, string $ip): array
    {
        $open = $this->attendances
            ->where('employee_id', (int) $employee->id)
            ->where('status', 'hadir')
            ->where('check_out', null)
            ->orderBy('work_date', 'DESC')
            ->first();

        if ($open === null) {
            throw new RuleViolationException('Belum ada check-in yang tercatat, jadi belum bisa check-out.');
        }

        // Hanya catatan hari ini atau kemarin (shift overnight) yang boleh ditutup.
        $now      = $this->now();
        $earliest = $now->modify('-1 day')->format('Y-m-d');

        if ($open['work_date'] < $earliest) {
            throw new RuleViolationException('Catatan check-in terakhir sudah lewat ' . tanggal_id($open['work_date']) . '. Hubungi Admin untuk koreksi.');
        }

        $this->assertPeriodNotFinal($open['work_date']);

        $this->attendances->update($open['id'], [
            'check_out'    => $now->format('Y-m-d H:i:s'),
            'check_out_ip' => $ip,
        ]);

        return $this->attendances->find($open['id']) ?? [];
    }

    /**
     * Data absensi hari kerja berjalan untuk dashboard karyawan.
     */
    public function todayFor(Employee $employee): array
    {
        $now   = $this->now();
        $shift = $this->shifts->find((int) $employee->shift_id);

        if ($shift === null) {
            return ['now' => $now, 'shift' => null, 'work_date' => $now->format('Y-m-d'), 'record' => null, 'window' => null];
        }

        $workDate = $this->resolveWorkDate($now, $shift);
        $record   = $this->attendances->findForEmployeeDate((int) $employee->id, $workDate);
        $window   = $this->checkInWindow($shift, $workDate, $now);

        $openRecord = $this->attendances
            ->where('employee_id', (int) $employee->id)
            ->where('status', 'hadir')
            ->where('check_out', null)
            ->orderBy('work_date', 'DESC')
            ->first();

        return [
            'now'         => $now,
            'shift'       => $shift,
            'work_date'   => $workDate,
            'record'      => $record,
            'window'      => $window,
            'open_record' => $openRecord,
        ];
    }

    // -----------------------------------------------------------------
    // Aksi Admin
    // -----------------------------------------------------------------

    public function assertPeriodNotFinal(string $workDate): void
    {
        if ($this->periods->isDateLocked($workDate)) {
            throw new RuleViolationException('Periode payroll untuk tanggal ' . tanggal_id($workDate) . ' sudah final dan tidak bisa diubah. Buka kembali periodenya terlebih dahulu.');
        }
    }

    /**
     * Tandai karyawan aktif yang belum punya catatan sebagai alpha.
     *
     * @return int jumlah baris yang dibuat
     */
    public function markAlpha(string $date, int $userId): int
    {
        if ($date >= $this->now()->format('Y-m-d')) {
            throw new RuleViolationException('Alpha hanya bisa ditandai untuk tanggal sebelum hari ini.');
        }

        $this->assertPeriodNotFinal($date);

        $created = 0;
        $active  = $this->employees->where('status', 'aktif')->findAll();

        foreach ($active as $employee) {
            if ($this->attendances->findForEmployeeDate((int) $employee->id, $date) !== null) {
                continue;
            }

            $this->attendances->insert([
                'employee_id'  => (int) $employee->id,
                'shift_id'     => (int) $employee->shift_id,
                'work_date'    => $date,
                'status'       => 'alpha',
                'late_minutes' => 0,
                'notes'        => 'Ditandai Alpha oleh Admin',
                'recorded_by'  => $userId,
                'source'       => 'admin',
            ]);

            $created++;
        }

        $this->logs->write($userId, 'attendance.mark_alpha', 'attendance', null, $created . ' karyawan ditandai alpha pada ' . $date);

        return $created;
    }

    /**
     * Input izin / sakit / alpha untuk banyak karyawan sekaligus.
     *
     * @return array{created: int, skipped: int}
     */
    public function bulkInput(array $employeeIds, string $status, string $date, ?string $notes, int $userId): array
    {
        if (! in_array($status, ['izin', 'sakit', 'alpha'], true)) {
            throw new RuleViolationException('Status harus izin, sakit, atau alpha.');
        }

        if ($date > $this->now()->format('Y-m-d')) {
            throw new RuleViolationException('Tanggal tidak boleh melewati hari ini.');
        }

        $this->assertPeriodNotFinal($date);

        $created = 0;
        $skipped = 0;

        foreach ($employeeIds as $employeeId) {
            $employee = $this->employees->find((int) $employeeId);

            if ($employee === null) {
                continue;
            }

            if ($this->attendances->findForEmployeeDate((int) $employee->id, $date) !== null) {
                $skipped++;
                continue;
            }

            $this->attendances->insert([
                'employee_id'  => (int) $employee->id,
                'shift_id'     => (int) $employee->shift_id,
                'work_date'    => $date,
                'status'       => $status,
                'late_minutes' => 0,
                'notes'        => $notes,
                'recorded_by'  => $userId,
                'source'       => 'admin',
            ]);

            $created++;
        }

        $this->logs->write($userId, 'attendance.bulk_input', 'attendance', null,
            sprintf('%s pada %s: %d dibuat, %d dilewati', $status, $date, $created, $skipped));

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Simpan koreksi absensi oleh Admin. late_minutes selalu dihitung ulang.
     */
    public function adminSave(array $data, int $userId): array
    {
        $existing = $this->attendances->find((int) ($data['id'] ?? 0));

        if ($existing === null) {
            throw new RuleViolationException('Catatan absensi tidak ditemukan.');
        }

        $this->assertPeriodNotFinal($existing['work_date']);

        $shift    = $this->shifts->find((int) ($data['shift_id'] ?? $existing['shift_id']));
        $status   = $data['status'] ?? 'hadir';
        $checkIn  = null;
        $checkOut = null;
        $late     = 0;

        if ($status === 'hadir') {
            $date    = $existing['work_date'];
            $checkIn = ! empty($data['check_in']) ? $date . ' ' . $data['check_in'] . ':00' : ($existing['check_in'] ?? null);

            if ($checkIn === null) {
                throw new RuleViolationException('Jam check-in wajib diisi untuk status hadir.');
            }

            if (! empty($data['check_out'])) {
                $checkOut = $date . ' ' . $data['check_out'] . ':00';

                // Shift lewat tengah malam: check-out jatuh di hari berikutnya.
                if ($checkOut <= $checkIn && $shift !== null) {
                    [$start, $end, $overnight] = $this->shiftBounds($shift, $date);

                    if ($overnight) {
                        $checkOut = date('Y-m-d H:i:s', strtotime($checkOut . ' +1 day'));
                    }
                }

                if ($checkOut < $checkIn) {
                    throw new RuleViolationException('Jam check-out tidak boleh lebih awal dari check-in.');
                }
            }

            if ($shift !== null) {
                $late = $this->computeLateMinutes(new \DateTimeImmutable($checkIn, new \DateTimeZone(self::TZ)), $date, $shift);
            }
        }

        $payload = [
            'shift_id'     => (int) ($data['shift_id'] ?? $existing['shift_id']),
            'status'       => $status,
            'check_in'     => $status === 'hadir' ? $checkIn : null,
            'check_out'    => $status === 'hadir' ? $checkOut : null,
            'late_minutes' => $late,
            'notes'        => $data['notes'] ?? null,
            'recorded_by'  => $userId,
        ];

        $this->attendances->update($existing['id'], $payload);

        $this->logs->write($userId, 'attendance.correct', 'attendance', (int) $existing['id'], json_encode([
            'before' => [
                'status'       => $existing['status'],
                'check_in'     => $existing['check_in'],
                'check_out'    => $existing['check_out'],
                'late_minutes' => $existing['late_minutes'],
            ],
            'after' => [
                'status'       => $payload['status'],
                'check_in'     => $payload['check_in'],
                'check_out'    => $payload['check_out'],
                'late_minutes' => $payload['late_minutes'],
            ],
        ], JSON_UNESCAPED_UNICODE));

        return $this->attendances->find($existing['id']) ?? [];
    }

    // -----------------------------------------------------------------
    // Pembatasan IP
    // -----------------------------------------------------------------

    public function assertIpAllowed(string $ip): void
    {
        $ranges = trim((string) ($this->settings['allowed_ip_ranges'] ?? ''));

        if ($ranges === '') {
            return;
        }

        $ip = $this->normalizeIp($ip);

        foreach (explode(',', $ranges) as $range) {
            $range = trim($range);

            if ($range !== '' && $this->ipInRange($ip, $this->normalizeIp($range))) {
                return;
            }
        }

        throw new RuleViolationException('Check-in hanya bisa dilakukan dari jaringan Wi-Fi kedai (' . $ranges . ').');
    }

    private function normalizeIp(string $ip): string
    {
        $ip = trim($ip);

        if ($ip === '::1') {
            return '127.0.0.1';
        }

        if (str_starts_with($ip, '::ffff:')) {
            return substr($ip, 7);
        }

        return $ip;
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range, 2);
        $bits            = (int) $bits;

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false || $bits < 0 || $bits > 32) {
            return false;
        }

        $mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
