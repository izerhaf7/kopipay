<?php
/** @var array $settings */
/** @var string $nextCode */
?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<form action="<?= site_url('admin/settings') ?>" method="post">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-cash-coin me-1"></i> Potongan Payroll</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="late_grace_minutes">Toleransi Default (menit)</label>
                            <input type="number" min="0" class="form-control" id="late_grace_minutes" name="late_grace_minutes" required
                                   value="<?= esc(old('late_grace_minutes', $settings['late_grace_minutes'])) ?>">
                            <div class="form-text">Dipakai hanya bila sebuah shift belum punya toleransi sendiri. Toleransi utama diatur per shift.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="late_deduction_per_minute">Potongan per Menit (Rp)</label>
                            <input type="text" inputmode="numeric" class="form-control kp-money-input" id="late_deduction_per_minute"
                                   name="late_deduction_per_minute" required
                                   value="<?= esc(old('late_deduction_per_minute', $settings['late_deduction_per_minute'])) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="alpha_penalty">Denda Alpha per Hari (Rp)</label>
                            <input type="text" inputmode="numeric" class="form-control kp-money-input" id="alpha_penalty"
                                   name="alpha_penalty" required value="<?= esc(old('alpha_penalty', $settings['alpha_penalty'])) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="checkin_early_minutes">Check-in Dibuka (menit sebelum shift)</label>
                            <input type="number" min="0" class="form-control" id="checkin_early_minutes" name="checkin_early_minutes" required
                                   value="<?= esc(old('checkin_early_minutes', $settings['checkin_early_minutes'])) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-shop me-1"></i> Identitas Usaha</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="company_name">Nama Usaha</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required maxlength="120"
                               value="<?= esc(old('company_name', $settings['company_name'])) ?>">
                        <div class="form-text">Muncul di header slip gaji PDF.</div>
                    </div>
                    <div>
                        <label class="form-label" for="company_address">Alamat</label>
                        <input type="text" class="form-control" id="company_address" name="company_address" maxlength="255"
                               value="<?= esc(old('company_address', $settings['company_address'] ?? '')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card">
                <div class="card-header"><i class="bi bi-fingerprint me-1"></i> Check-in Mandiri</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="self_checkin_enabled"
                               name="self_checkin_enabled" value="1" <?= (int) $settings['self_checkin_enabled'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="self_checkin_enabled">
                            Izinkan karyawan check-in / check-out sendiri
                        </label>
                    </div>

                    <label class="form-label" for="allowed_ip_ranges">Batasan IP (opsional)</label>
                    <input type="text" class="form-control" id="allowed_ip_ranges" name="allowed_ip_ranges" maxlength="255"
                           value="<?= esc(old('allowed_ip_ranges', $settings['allowed_ip_ranges'] ?? '')) ?>"
                           placeholder="192.168.1.0/24, 10.0.0.5">
                    <div class="form-text">
                        Daftar IP/CIDR dipisah koma. Kosongkan bila tanpa batasan.
                        Waktu check-in selalu diambil dari server, bukan perangkat.
                    </div>

                    <hr class="kp-divider">
                    <div class="kp-muted small">
                        <p class="mb-1"><strong>Batasan yang perlu disadari:</strong> check-in lewat tombol masih bisa disalahgunakan
                            (titip absen). Mitigasi v1: batasan IP Wi-Fi kedai, waktu server, dan pencatatan IP.</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-save me-1"></i> Simpan Pengaturan</button>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.kp-money-input').forEach(function (input) {
    const format = () => {
        const digits = input.value.replace(/\D/g, '');
        input.value = digits === '' ? '' : new Intl.NumberFormat('id-ID').format(digits);
    };
    input.addEventListener('input', format);
    format();
});
</script>
<?= $this->endSection() ?>
