<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header">
                Koreksi Absensi — <?= esc($row['full_name']) ?>
                <span class="kp-muted small">(<?= esc($row['employee_code']) ?>)</span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <span class="kp-badge-soft"><i class="bi bi-calendar3"></i> <?= tanggal_id($row['work_date']) ?></span>
                    <span class="kp-badge-soft"><i class="bi bi-clock"></i> <?= esc($row['shift_name']) ?> (<?= substr($row['start_time'], 0, 5) ?>–<?= substr($row['end_time'], 0, 5) ?>)</span>
                    <span class="kp-badge-soft"><i class="bi bi-<?= $row['source'] === 'self' ? 'phone' : 'person-badge' ?>"></i> <?= $row['source'] === 'self' ? 'Mandiri' : 'Admin' ?></span>
                    <?php if (! empty($row['check_in_ip'])): ?>
                        <span class="kp-badge-soft">IP <?= esc($row['check_in_ip']) ?></span>
                    <?php endif ?>
                </div>

                <form action="<?= site_url('admin/attendance/' . $row['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach (['hadir', 'izin', 'sakit', 'alpha'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $row['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="shift_id">Shift (snapshot hari itu)</label>
                            <select class="form-select" id="shift_id" name="shift_id">
                                <?php foreach ($shifts as $shift): ?>
                                    <option value="<?= (int) $shift['id'] ?>" <?= (int) $row['shift_id'] === (int) $shift['id'] ? 'selected' : '' ?>>
                                        <?= esc($shift['name']) ?> (<?= substr($shift['start_time'], 0, 5) ?>–<?= substr($shift['end_time'], 0, 5) ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="check_in">Jam Masuk</label>
                            <input type="time" class="form-control" id="check_in" name="check_in"
                                   value="<?= esc(! empty($row['check_in']) ? date('H:i', strtotime($row['check_in'])) : '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="check_out">Jam Keluar</label>
                            <input type="time" class="form-control" id="check_out" name="check_out"
                                   value="<?= esc(! empty($row['check_out']) ? date('H:i', strtotime($row['check_out'])) : '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">Catatan</label>
                            <input type="text" class="form-control" id="notes" name="notes" maxlength="255"
                                   value="<?= esc($row['notes'] ?? '') ?>" placeholder="mis. lupa check-out, shift ditukar">
                        </div>
                    </div>

                    <div class="alert mt-3 mb-0" style="background:#FBF1DE;color:#7A5405;border:0">
                        <i class="bi bi-info-circle me-1"></i>
                        Status <strong>hadir</strong> wajib punya jam masuk. <code>late_minutes</code> dihitung ulang otomatis
                        dari jam masuk vs jam mulai shift. Untuk shift lewat tengah malam, jam keluar yang lebih kecil
                        otomatis dianggap hari berikutnya.
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i> Simpan Koreksi</button>
                        <a class="btn btn-ghost" href="<?= site_url('admin/attendance') ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-1"></i> Jejak</div>
            <div class="card-body small">
                <div class="d-flex justify-content-between py-1"><span class="kp-muted">Check-in tercatat</span><span class="fw-semibold"><?= jam_short($row['check_in']) ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="kp-muted">Check-out tercatat</span><span class="fw-semibold"><?= jam_short($row['check_out']) ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="kp-muted">Menit terlambat</span><span class="fw-semibold"><?= (int) $row['late_minutes'] ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="kp-muted">Dibuat oleh</span><span class="fw-semibold"><?= $row['source'] === 'self' ? 'Karyawan (mandiri)' : 'Admin' ?></span></div>
                <hr class="kp-divider">
                <p class="kp-muted mb-0">Setiap perubahan jam/status dicatat di <a href="<?= site_url('admin/logs') ?>">log aktivitas</a> (nilai sebelum &amp; sesudah).</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
