<?= $this->extend('layouts/employee') ?>

<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-body text-center">
        <div class="kp-avatar mx-auto mb-2" style="width:64px;height:64px;font-size:1.4rem">
            <?= esc(strtoupper(substr($employee->full_name, 0, 1))) ?>
        </div>
        <div class="h5 mb-0"><?= esc($employee->full_name) ?></div>
        <div class="kp-muted small"><?= esc($employee->employee_code) ?> · <?= esc($employee->position_name ?? '-') ?></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Data Karyawan</div>
    <div class="card-body">
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
