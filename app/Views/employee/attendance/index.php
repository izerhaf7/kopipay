<?= $this->extend('layouts/employee') ?>

<?= $this->section('content') ?>
<?php $record = $today['record']; $window = $today['window']; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <div class="kp-eyebrow">Hari kerja</div>
                <div class="fw-semibold mt-1"><?= tanggal_id($today['work_date'], true) ?></div>
                <div class="kp-muted small"><?= esc($today['shift']['name'] ?? '-') ?> · <?= substr($today['shift']['start_time'] ?? '', 0, 5) ?>–<?= substr($today['shift']['end_time'] ?? '', 0, 5) ?></div>
            </div>
            <div class="text-end">
                <?php if ($record === null): ?>
                    <span class="badge text-bg-secondary">Belum absen</span>
                <?php else: ?>
                    <span class="badge <?= status_badge($record['status']) ?>"><?= status_label($record['status']) ?></span>
                <?php endif ?>
            </div>
        </div>

        <?php if ($record === null): ?>
            <?php if ($window !== null && $window['open']): ?>
                <form action="<?= site_url('me/attendance/check-in') ?>" method="post" class="mt-2">
                    <?= csrf_field() ?>
                    <button class="kp-btn-checkin" type="submit"><i class="bi bi-fingerprint me-1"></i> Check-in</button>
                </form>
            <?php else: ?>
                <div class="kp-muted small mt-2">
                    <?= $window === null ? 'Shift belum diatur.' : ($window['before'] ? 'Check-in dibuka pukul ' . $window['window_start'] . '.' : 'Shift hari ini sudah berakhir. Hubungi Admin bila perlu koreksi.') ?>
                </div>
            <?php endif ?>
        <?php elseif ($record['status'] === 'hadir' && empty($record['check_out'])): ?>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <div class="small kp-muted">Masuk <?= jam_short($record['check_in']) ?><?= (int) $record['late_minutes'] > 0 ? ' · telat ' . menit_ke_jam((int) $record['late_minutes']) : '' ?></div>
                <form action="<?= site_url('me/attendance/check-out') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-caramel btn-sm" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Check-out</button>
                </form>
            </div>
        <?php else: ?>
            <div class="small kp-muted">
                Masuk <?= jam_short($record['check_in']) ?> · Keluar <?= jam_short($record['check_out']) ?>
                <?php if (! empty($record['notes'])): ?> · <?= esc($record['notes']) ?><?php endif ?>
            </div>
        <?php endif ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= site_url('me/attendance') ?>">
            <div class="col-5">
                <label class="form-label">Bulan</label>
                <select class="form-select form-select-sm" name="month">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= nama_bulan($m) ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div class="col-4">
                <label class="form-label">Tahun</label>
                <select class="form-select form-select-sm" name="year">
                    <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 2; $y--): ?>
                        <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div class="col-3">
                <button class="btn btn-primary btn-sm w-100" type="submit">Lihat</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-calendar2-week me-1"></i> Riwayat <?= nama_bulan($month) ?> <?= $year ?></div>
    <div class="card-body p-0">
        <?php if ($rows === []): ?>
            <div class="kp-empty"><i class="bi bi-inbox"></i> Belum ada catatan absensi bulan ini.</div>
        <?php else: ?>
            <div class="kp-table-wrap">
                <table class="table kp-table-sm align-middle mb-0">
                    <thead>
                        <tr><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= date('j', strtotime($row['work_date'])) ?> <?= nama_bulan((int) date('n', strtotime($row['work_date'])), true) ?></div>
                                    <div class="kp-muted" style="font-size:.72rem"><?= ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int) date('w', strtotime($row['work_date']))] ?></div>
                                </td>
                                <td><?= jam_short($row['check_in']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'hadir' && empty($row['check_out'])): ?>
                                        <span class="kp-badge-soft is-warning">belum</span>
                                    <?php else: ?>
                                        <?= jam_short($row['check_out']) ?>
                                    <?php endif ?>
                                </td>
                                <td>
                                    <span class="badge <?= status_badge($row['status']) ?>"><?= status_label($row['status']) ?></span>
                                    <?php if ((int) $row['late_minutes'] > 0): ?>
                                        <div class="kp-muted" style="font-size:.7rem">telat <?= (int) $row['late_minutes'] ?> mnt</div>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>
