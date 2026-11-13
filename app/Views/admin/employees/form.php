<?php

/** @var object $positions */
/** @var object $employee */
/** @var object $shifts */
/** @var string $nextCode */

?>
<?= $this->extend('layout/backend') ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $isEdit = $employee !== null; ?>
<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><?= $isEdit ? 'Edit Karyawan' : 'Karyawan Baru' ?></div>
            <div class="card-body">
                <form action="<?= $isEdit ? site_url('admin/employees/' . $employee->id) : site_url('admin/employees') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="employee_code">Kode Karyawan</label>
                            <input type="text" class="form-control" id="employee_code" name="employee_code" maxlength="20"
                                value="<?= esc(old('employee_code', $employee->employee_code ?? $nextCode)) ?>" placeholder="KP-001">
                            <div class="form-text">Dipakai juga sebagai username login.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="full_name">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required maxlength="120"
                                value="<?= esc(old('full_name', $employee->full_name ?? '')) ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="position_id">Jabatan</label>
                            <select class="form-select" id="position_id" name="position_id" required>
                                <option value="">Pilih jabatan</option>
                                <?php foreach ($positions as $position): ?>
                                    <option value="<?= (int) $position['id'] ?>"
                                        <?= (int) old('position_id', $employee->position_id ?? 0) === (int) $position['id'] ? 'selected' : '' ?>>
                                        <?= esc($position['name']) ?> — <?= rupiah($position['daily_rate']) ?>/hari
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="shift_id">Shift Default</label>
                            <select class="form-select" id="shift_id" name="shift_id" required>
                                <option value="">Pilih shift</option>
                                <?php foreach ($shifts as $shift): ?>
                                    <option value="<?= (int) $shift['id'] ?>"
                                        <?= (int) old('shift_id', $employee->shift_id ?? 0) === (int) $shift['id'] ? 'selected' : '' ?>>
                                        <?= esc($shift['name']) ?> (<?= substr($shift['start_time'], 0, 5) ?>–<?= substr($shift['end_time'], 0, 5) ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="hire_date">Tanggal Masuk</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" required
                                max="<?= date('Y-m-d') ?>"
                                value="<?= esc(old('hire_date', isset($employee) && $employee->hire_date ? $employee->hire_date->format('Y-m-d') : date('Y-m-d'))) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="aktif" <?= old('status', $employee->status ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= old('status', $employee->status ?? 'aktif') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="photo">Foto Profil</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" data-crop-photo>
                            <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="phone">No. HP</label>
                            <input type="text" class="form-control" id="phone" name="phone" maxlength="25"
                                value="<?= esc(old('phone', $employee->phone ?? '')) ?>" placeholder="0812xxxxxxx">
                        </div>
                        <?php if (! $isEdit): ?>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="email">Email (opsional)</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= esc(old('email')) ?>" placeholder="kosong = dibuat otomatis">
                            </div>
                        <?php endif ?>

                        <div class="col-12">
                            <label class="form-label" for="address">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="2" maxlength="255"><?= esc(old('address', $employee->address ?? '')) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Buat Karyawan + Akun' ?></button>
                        <a class="btn btn-ghost" href="<?= site_url('admin/employees') ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> Catatan</div>
            <div class="card-body kp-muted small">
                <p>Saat karyawan baru disimpan, sistem membuat <strong>akun login</strong> dengan password sementara. Password ditampilkan <strong>sekali</strong> setelah simpan.</p>
                <p class="mb-2">Karyawan wajib mengganti password saat login pertama.</p>
                <hr class="kp-divider">
                <p class="mb-0">Menonaktifkan karyawan tidak menghapus riwayat absensi &amp; slip gaji.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => { document.querySelectorAll('[data-crop-photo]').forEach(input => { const preview=document.createElement('img'); preview.style.cssText='display:none;max-width:100%;max-height:300px;margin-top:12px;border-radius:12px'; input.after(preview); let cropper; input.addEventListener('change',()=>{const file=input.files[0];if(!file)return;if(cropper)cropper.destroy();preview.src=URL.createObjectURL(file);preview.style.display='block';cropper=new Cropper(preview,{aspectRatio:1,viewMode:1});}); input.closest('form').addEventListener('submit',e=>{if(!cropper)return;e.preventDefault();cropper.getCroppedCanvas({width:600,height:600}).toBlob(blob=>{const dt=new DataTransfer();dt.items.add(new File([blob],'profile.jpg',{type:'image/jpeg'}));input.files=dt.files;input.closest('form').submit();},'image/jpeg',.9);});});});
</script>
<?= $this->endSection() ?>