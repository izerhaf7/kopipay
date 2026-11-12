<?= $this->extend('layouts/employee') ?>

<?= $this->section('content') ?>
<?php
$record   = $today['record'];
$window   = $today['window'];
$shift    = $today['shift'];
$openRec  = $today['open_record'] ?? null;
$canOut   = $openRec !== null;
$hasIn    = $record !== null;
$isHadir  = $hasIn && $record['status'] === 'hadir';
$hasOut   = $isHadir && ! empty($record['check_out']);
?>

<div class="card kp-checkin-card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="kp-eyebrow" style="color:#C4A98F">Absen hari ini</div>
                <div class="kp-clock mt-1" id="kpClock"><?= $today['now']->format('H:i') ?></div>
                <div class="kp-checkin-meta mt-1" id="kpDate"><?= tanggal_id($today['work_date'], true) ?></div>
            </div>
            <?php if ($shift !== null): ?>
                <div class="text-end">
                    <span class="kp-badge-soft" style="background:rgba(198,139,89,.2);color:#F0DCC6;border-color:rgba(198,139,89,.4)">
                        <i class="bi bi-clock-history"></i> <?= esc($shift['name']) ?>
                    </span>
                    <div class="kp-checkin-meta mt-1"><?= substr($shift['start_time'], 0, 5) ?> – <?= substr($shift['end_time'], 0, 5) ?></div>
                </div>
            <?php endif ?>
        </div>

        <hr style="border-color:rgba(255,255,255,.14);margin:1.1rem 0">

        <?php if (! $hasIn): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge" style="background:rgba(255,255,255,.16);color:#fff">Belum check-in</span>
                <?php if ($window !== null && ! $window['open']): ?>
                    <span class="kp-checkin-meta">
                        <?= $window['before'] ? 'Dibuka pukul ' . $window['window_start'] : 'Shift berakhir ' . $window['window_end'] ?>
                    </span>
                <?php endif ?>
            </div>

            <form action="<?= site_url('me/attendance/check-in') ?>" method="post">
                <?= csrf_field() ?>
                <button class="kp-btn-checkin" type="submit" <?= ($window === null || ! $window['open']) ? 'disabled' : '' ?>>
                    <i class="bi bi-fingerprint me-1"></i> Check-in Sekarang
                </button>
            </form>
            <?php if ($window !== null && ! $window['open']): ?>
                <div class="kp-checkin-meta mt-2 text-center">
                    <?= $window['before'] ? 'Belum waktunya check-in. Sabar ya ☕' : 'Shift hari ini sudah berakhir. Hubungi Admin untuk koreksi.' ?>
                </div>
            <?php endif ?>

        <?php elseif ($isHadir && ! $hasOut): ?>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge" style="background:var(--kp-success);color:#fff">Sudah check-in <?= jam_short($record['check_in']) ?></span>
                <?php if ((int) $record['late_minutes'] > 0): ?>
                    <span class="badge" style="background:var(--kp-warning);color:#2B1B12">Terlambat <?= menit_ke_jam((int) $record['late_minutes']) ?></span>
                <?php else: ?>
                    <span class="badge" style="background:rgba(255,255,255,.16);color:#fff">Tepat waktu</span>
                <?php endif ?>
            </div>

            <form action="<?= site_url('me/attendance/check-out') ?>" method="post"
                  onsubmit="return confirm('Check-out sekarang? Pastikan pekerjaan sudah selesai.')">
                <?= csrf_field() ?>
                <button class="kp-btn-checkout" type="submit">
                    <i class="bi bi-box-arrow-right me-1"></i> Check-out
                </button>
            </form>

        <?php elseif ($isHadir && $hasOut): ?>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge" style="background:var(--kp-success);color:#fff">Masuk <?= jam_short($record['check_in']) ?></span>
                <span class="badge" style="background:rgba(255,255,255,.16);color:#fff">Keluar <?= jam_short($record['check_out']) ?></span>
                <span class="kp-checkin-meta">Absen hari ini selesai. Terima kasih!</span>
            </div>

        <?php else: ?>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge <?= status_badge($record['status']) ?>"><?= status_label($record['status']) ?></span>
                <span class="kp-checkin-meta">
                    <?= ! empty($record['notes']) ? esc($record['notes']) : 'Status absensi hari ini sudah dicatat Admin.' ?>
                </span>
            </div>
        <?php endif ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="kp-eyebrow">Profil</div>
                <div class="h5 mb-1 mt-1"><?= esc($employee->full_name) ?></div>
                <div class="kp-muted small">
                    <?= esc($employee->employee_code) ?> · <?= esc($employee->position_name ?? '-') ?>
                </div>
            </div>
            <div class="kp-avatar" style="width:48px;height:48px"><?= esc(strtoupper(substr($employee->full_name, 0, 1))) ?></div>
        </div>
        <hr class="kp-divider">
        <div class="row text-center g-2">
            <div class="col-4">
                <div class="kp-muted small">Shift</div>
                <div class="fw-semibold"><?= esc($employee->shift_name ?? '-') ?></div>
            </div>
            <div class="col-4">
                <div class="kp-muted small">Masuk</div>
                <div class="fw-semibold" style="font-size:.85rem"><?= $employee->hire_date->format('d/m/Y') ?></div>
            </div>
            <div class="col-4">
                <div class="kp-muted small">Status</div>
                <div class="fw-semibold"><?= esc(ucfirst($employee->status)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt me-1"></i> Slip Gaji Terbaru</span>
        <a class="small" href="<?= site_url('me/payslips') ?>">Semua</a>
    </div>
    <div class="card-body p-0">
        <?php if ($payslips === []): ?>
            <div class="kp-empty"><i class="bi bi-hourglass-split"></i> Belum ada slip gaji final.</div>
        <?php else: ?>
            <?php foreach ($payslips as $slip): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom" style="border-color:var(--kp-border)!important">
                    <div>
                        <div class="fw-semibold"><?= esc(periode_label((int) $slip->period_month, (int) $slip->period_year)) ?></div>
                        <div class="kp-muted small"><?= (int) $slip->days_present ?> hari hadir</div>
                    </div>
                    <div class="text-end">
                        <div class="kp-money"><?= rupiah($slip->net_salary) ?></div>
                        <a class="small" href="<?= site_url('me/payslips/' . $slip->id . '/pdf') ?>">
                            <i class="bi bi-download"></i> Unduh
                        </a>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</div>

<div class="row g-2">
    <div class="col-6">
        <a class="btn btn-ghost w-100 py-3" href="<?= site_url('me/attendance') ?>">
            <i class="bi bi-calendar2-week d-block mb-1"></i> Riwayat Absen
        </a>
    </div>
    <div class="col-6">
        <a class="btn btn-ghost w-100 py-3" href="<?= site_url('me/password') ?>">
            <i class="bi bi-shield-lock d-block mb-1"></i> Ganti Password
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // Jam berjalan dari waktu server (bukan jam perangkat).
    let serverTs = <?= (int) $today['now']->getTimestamp() ?>;
    const clock  = document.getElementById('kpClock');

    const tick = () => {
        serverTs += 1;
        const d = new Date(serverTs * 1000);
        clock.textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    };

    setInterval(tick, 1000);
})();
</script>
<?= $this->endSection() ?>
