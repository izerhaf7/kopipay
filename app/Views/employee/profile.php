<?= $this->extend('layouts/employee') ?>

<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-body text-center">
        <div class="kp-profile-photo mx-auto mb-2"><?php $photo = $employee->photo ?? null; ?><?php if ($photo): ?><img src="<?= base_url('uploads/employees/' . esc($photo)) ?>" alt="Foto profil"><?php else: ?><span><?= esc(strtoupper(substr($employee->full_name, 0, 1))) ?></span><?php endif ?></div>
        <div class="h5 mb-0"><?= esc($employee->full_name) ?></div>
        <div class="kp-muted small"><?= esc($employee->employee_code) ?> · <?= esc($employee->position_name ?? '-') ?></div>
    </div>
</div>

<div class="card mb-3"><div class="card-body"><form method="post" action="<?= site_url('me/profile/photo') ?>" enctype="multipart/form-data"><?= csrf_field() ?><label class="form-label">Foto Profil</label><input type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/webp" data-crop-photo required><div class="form-text">Pilih foto, preview dan crop tersedia sebelum disimpan. Maksimal 2 MB.</div><button class="btn btn-primary mt-3" type="submit"><i class="bi bi-upload me-1"></i> Simpan Foto</button></form></div></div>
<div class="card mb-3"><div class="card-header">Data Karyawan</div><div class="card-body">
<?php
$fields = [
            'Jabatan'        => $employee->position_name ?? '-',
            'Tarif Harian'   => rupiah($employee->daily_rate ?? 0),
            'Shift'          => ($employee->shift_name ?? '-') . ' (' . substr($employee->start_time ?? '', 0, 5) . '–' . substr($employee->end_time ?? '', 0, 5) . ')',
            'Tanggal Masuk'  => tanggal_id($employee->hire_date),
            'No. HP'         => $employee->phone ?: '-',
            'Alamat'         => $employee->address ?: '-',
            'Status'         => ucfirst($employee->status),
            'Email Akun'     => user()->email ?? '-',
        ];
        ?>
        <?php foreach ($fields as $label => $value): ?>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--kp-border)!important">
                <span class="kp-muted small"><?= esc($label) ?></span>
                <span class="fw-semibold text-end" style="max-width:60%"><?= esc($value) ?></span>
            </div>
        <?php endforeach ?>
        <div class="kp-muted small mt-3">
            <i class="bi bi-info-circle me-1"></i>
            Perubahan data jabatan/shift hanya bisa dilakukan Admin.
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="col-6">
        <a class="btn btn-primary w-100 py-3" href="<?= site_url('me/password') ?>">
            <i class="bi bi-shield-lock d-block mb-1"></i> Ganti Password
        </a>
    </div>
    <div class="col-6">
        <a class="btn btn-ghost w-100 py-3" href="<?= site_url('me/payslips') ?>">
            <i class="bi bi-receipt d-block mb-1"></i> Slip Gaji
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-crop-photo]').forEach(input => {
        const preview = document.createElement('img'); preview.className='kp-crop-preview'; preview.style.cssText='display:none;max-width:100%;max-height:300px;margin-top:12px;border-radius:12px'; input.after(preview);
        let cropper;
        input.addEventListener('change', () => { const file=input.files[0]; if (!file) return; if(cropper) cropper.destroy(); preview.src=URL.createObjectURL(file); preview.style.display='block'; cropper=new Cropper(preview,{aspectRatio:1,viewMode:1}); });
        input.closest('form').addEventListener('submit', e => { if(!cropper) return; e.preventDefault(); cropper.getCroppedCanvas({width:600,height:600}).toBlob(blob => { const dt=new DataTransfer(); dt.items.add(new File([blob], 'profile.jpg', {type:'image/jpeg'})); input.files=dt.files; input.closest('form').submit(); }, 'image/jpeg', .9); });
    });
});
</script>
<?= $this->endSection() ?>
