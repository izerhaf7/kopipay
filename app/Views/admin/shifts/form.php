<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $isEdit = $shift !== null; ?>
<div class="row">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><?= $isEdit ? 'Edit Shift' : 'Shift Baru' ?></div>
            <div class="card-body">
                <form action="<?= $isEdit ? site_url('admin/shifts/' . $shift['id']) : site_url('admin/shifts') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="name">Nama Shift</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="60"
                               value="<?= esc(old('name', $shift['name'] ?? '')) ?>" placeholder="Pagi">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="start_time">Jam Mulai</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required
                                   value="<?= esc(substr(old('start_time', $shift['start_time'] ?? '07:00'), 0, 5)) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="end_time">Jam Selesai</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required
                                   value="<?= esc(substr(old('end_time', $shift['end_time'] ?? '15:00'), 0, 5)) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tolerance_minutes">Toleransi Keterlambatan (menit)</label>
                        <input type="number" min="0" max="120" class="form-control" id="tolerance_minutes" name="tolerance_minutes" required
                               value="<?= esc(old('tolerance_minutes', $shift['tolerance_minutes'] ?? 15)) ?>">
                        <div class="form-text">
                            Terlambat sampai dengan angka ini tidak dihitung. Lewat dari itu, potongan dihitung
                            penuh sejak jam masuk shift.
                        </div>
                    </div>

                    <div class="alert" style="background:#FBF1DE;color:#7A5405;border:0">
                        <i class="bi bi-info-circle me-1"></i>
                        Bila jam selesai lebih kecil dari jam mulai (mis. 22:00 – 06:00), shift otomatis dianggap lewat tengah malam.
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Shift' ?></button>
                        <a class="btn btn-ghost" href="<?= site_url('admin/shifts') ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
