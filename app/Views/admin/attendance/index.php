<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= site_url('admin/attendance') ?>">
            <div class="col-12 col-md-3">
                <label class="form-label">Cari karyawan</label>
                <input type="text" class="form-control" name="q" value="<?= esc($filters['q']) ?>" placeholder="Nama / kode">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" name="date" value="<?= esc($filters['date']) ?>">
            </div>
            <div class="col-3 col-md-1">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="month">
                    <option value="0">-</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= (int) $filters['month'] === $m ? 'selected' : '' ?>><?= nama_bulan($m, true) ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div class="col-3 col-md-1">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="year">
                    <option value="0">-</option>
                    <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= (int) $filters['year'] === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua</option>
                    <?php foreach (['hadir', 'izin', 'sakit', 'alpha'] as $st): ?>
                        <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label">Sumber</label>
                <select class="form-select" name="source">
                    <option value="">-</option>
                    <option value="self" <?= $filters['source'] === 'self' ? 'selected' : '' ?>>Mandiri</option>
                    <option value="admin" <?= $filters['source'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <div class="form-check form-switch mt-4 me-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="open" name="open" value="1"
                        <?= $filters['open'] === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="open">Belum check-out</label>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-ghost" href="<?= site_url('admin/attendance') ?>"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="kp-eyebrow mb-1">Aksi cepat</div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a class="btn btn-primary btn-sm" href="<?= site_url('admin/attendance/input') ?>">
                        <i class="bi bi-pencil-square me-1"></i> Input izin / sakit / alpha
                    </a>
                    <form class="d-flex gap-2" action="<?= site_url('admin/attendance/mark-alpha') ?>" method="post"
                          onsubmit="return confirm('Tandai semua karyawan aktif yang belum punya catatan sebagai ALPHA pada tanggal ini?')">
                        <?= csrf_field() ?>
                        <input type="date" class="form-control form-control-sm" name="date" max="<?= date('Y-m-d', strtotime('-1 day')) ?>"
                               value="<?= date('Y-m-d', strtotime('-1 day')) ?>" style="max-width:160px">
                        <button class="btn btn-ghost btn-sm" type="submit">
                            <i class="bi bi-flag me-1"></i> Tandai Alpha
                        </button>
                    </form>
                </div>
                <div class="kp-muted small mt-2">Alpha hanya bisa ditandai untuk tanggal sebelum hari ini.</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="kp-eyebrow mb-1">Info</div>
                <p class="small mb-0 mt-2 kp-muted">
                    Check-in karyawan masuk otomatis dengan sumber <strong>Mandiri</strong>.
                    Admin menginput izin/sakit/alpha dan melakukan koreksi bila karyawan lupa check-in/out.
                    Setiap koreksi tercatat di log aktivitas.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-calendar2-check me-1"></i> Catatan Absensi</div>
    <div class="kp-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>Shift</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Status</th>
                    <th>Telat</th>
                    <th>Sumber</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="9" class="kp-empty"><i class="bi bi-inbox"></i> Tidak ada catatan absensi untuk filter ini.</td></tr>
                <?php endif ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td style="white-space:nowrap">
                            <?= tanggal_id($row['work_date']) ?>
                            <div class="kp-muted" style="font-size:.72rem"><?= esc($row['position_name']) ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= esc($row['full_name']) ?></div>
                            <div class="kp-muted" style="font-size:.75rem"><?= esc($row['employee_code']) ?></div>
                        </td>
                        <td><?= esc($row['shift_name']) ?></td>
                        <td><?= jam_short($row['check_in']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'hadir' && empty($row['check_out'])): ?>
                                <span class="kp-badge-soft is-warning">belum</span>
                            <?php else: ?>
                                <?= jam_short($row['check_out']) ?>
                            <?php endif ?>
                        </td>
                        <td><span class="badge <?= status_badge($row['status']) ?>"><?= status_label($row['status']) ?></span></td>
                        <td>
                            <?php if ((int) $row['late_minutes'] > 0): ?>
                                <span class="kp-badge-soft is-warning"><?= (int) $row['late_minutes'] ?> mnt</span>
                            <?php else: ?>
                                <span class="kp-muted">—</span>
                            <?php endif ?>
                        </td>
                        <td>
                            <?php if ($row['source'] === 'self'): ?>
                                <span class="kp-badge-soft is-success" title="<?= esc($row['check_in_ip'] ?? '') ?>">
                                    <i class="bi bi-phone"></i> Mandiri
                                </span>
                            <?php else: ?>
                                <span class="kp-badge-soft"><i class="bi bi-person-badge"></i> Admin</span>
                            <?php endif ?>
                        </td>
                        <td class="text-end" style="white-space:nowrap">
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/attendance/' . $row['id'] . '/edit') ?>"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" action="<?= site_url('admin/attendance/' . $row['id'] . '/delete') ?>" method="post"
                                  onsubmit="return confirm('Hapus catatan absensi ini?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-ghost btn-sm text-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>
