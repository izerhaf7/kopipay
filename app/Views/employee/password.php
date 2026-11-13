<?= $this->extend('layouts/employee') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header"><i class="bi bi-shield-lock me-1"></i> Ganti Password</div>
    <div class="card-body">
        <form action="<?= site_url('me/password') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="current_password">Password Saat Ini</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                    <button class="btn btn-ghost" type="button" data-toggle-password="current_password" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password Baru</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    <button class="btn btn-ghost" type="button" data-toggle-password="password" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
                </div>
                <div class="form-text">Minimal 8 karakter. Jangan pakai tanggal lahir atau nama sendiri.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirm">Ulangi Password Baru</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required>
                    <button class="btn btn-ghost" type="button" data-toggle-password="password_confirm" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Simpan Password</button>
                <a class="btn btn-ghost" href="<?= site_url('me/profile') ?>">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
