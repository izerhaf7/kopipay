<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">Input Izin / Sakit / Alpha</div>
            <div class="card-body">
                <form action="<?= site_url('admin/attendance/input') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="work_date">Tanggal</label>
                            <input type="date" class="form-control" id="work_date" name="work_date" required
                                   max="<?= esc($today) ?>" value="<?= esc(old('work_date', date('Y-m-d', strtotime('-1 day')))) ?>">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="izin" <?= old('status') === 'izin' ? 'selected' : '' ?>>Izin</option>
                                <option value="sakit" <?= old('status') === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                <option value="alpha" <?= old('status') === 'alpha' ? 'selected' : '' ?>>Alpha</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="notes">Keterangan</label>
                            <input type="text" class="form-control" id="notes" name="notes" maxlength="255"
                                   value="<?= esc(old('notes')) ?>" placeholder="mis. izin acara keluarga">
                        </div>
                    </div>

                    <label class="form-label">Pilih Karyawan</label>
                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-ghost btn-sm" type="button" id="checkAll">Pilih semua</button>
                        <button class="btn btn-ghost btn-sm" type="button" id="clearAll">Kosongkan</button>
                    </div>

                    <div class="border rounded-3 p-2" style="max-height:340px;overflow:auto;border-color:var(--kp-border)!important">
                        <?php foreach ($employees as $employee): ?>
                            <div class="form-check py-1">
                                <input class="form-check-input" type="checkbox" name="employee_ids[]"
                                       value="<?= (int) $employee->id ?>" id="emp<?= (int) $employee->id ?>">
                                <label class="form-check-label" for="emp<?= (int) $employee->id ?>">
                                    <span class="fw-semibold"><?= esc($employee->full_name) ?></span>
                                    <span class="kp-muted small">· <?= esc($employee->employee_code) ?> · <?= esc($employee->position_name ?? '') ?></span>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i> Simpan</button>
                        <a class="btn btn-ghost" href="<?= site_url('admin/attendance') ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> Aturan</div>
            <div class="card-body kp-muted small">
                <ul class="mb-0 ps-3">
                    <li>Karyawan yang sudah punya catatan pada tanggal itu akan <strong>dilewati</strong>.</li>
                    <li>Status izin/sakit <strong>tidak dibayar dan tidak dipotong</strong>.</li>
                    <li>Alpha dipotong nominal tetap per hari (lihat Pengaturan).</li>
                    <li>Tanggal pada periode payroll <strong>final</strong> tidak bisa diubah.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('checkAll').addEventListener('click', function () {
    document.querySelectorAll('input[name="employee_ids[]"]').forEach(cb => cb.checked = true);
});
document.getElementById('clearAll').addEventListener('click', function () {
    document.querySelectorAll('input[name="employee_ids[]"]').forEach(cb => cb.checked = false);
});
</script>
<?= $this->endSection() ?>
