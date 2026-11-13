<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Password · KopiPay</title>
    <link rel="icon" href="<?= base_url('assets/img/logo.svg') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/kopipay.css') ?>" rel="stylesheet">
</head>
<body class="kp-login-body">
<div class="kp-login-card">
    <img class="kp-login-logo" src="<?= base_url('assets/img/logo-lockup.svg') ?>" alt="KopiPay">
    <h1 class="h5 text-center mb-1">Ganti Password</h1>
    <p class="text-center kp-muted mb-4" style="font-size:.82rem">
        Akun Anda memakai password sementara. Buat password baru sebelum melanjutkan.
    </p>

    <?= $this->include('layouts/partials/flash') ?>

    <form action="<?= site_url('reset-password') ?>" method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="password">Password Baru</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                <button class="btn btn-ghost" type="button" data-toggle-password="password" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="form-text">Minimal 8 karakter, kombinasi huruf &amp; angka.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirm">Ulangi Password Baru</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required>
                <button class="btn btn-ghost" type="button" data-toggle-password="password_confirm" aria-label="Lihat password"><i class="bi bi-eye"></i></button>
            </div>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Simpan &amp; Lanjutkan</button>
    </form>
</div>
<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-toggle-password]');
        if (!button) return;
        const input = document.getElementById(button.dataset.togglePassword);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        const icon = button.querySelector('i');
        if (icon) icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        button.setAttribute('aria-label', input.type === 'password' ? 'Lihat password' : 'Sembunyikan password');
    });
</script>
</body>
</html>
