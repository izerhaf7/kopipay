<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 · Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/kopipay.css') ?>" rel="stylesheet">
</head>
<body>
<div class="kp-error-page">
    <div>
        <img src="<?= base_url('assets/img/logo.svg') ?>" alt="" width="64" class="mb-3">
        <div class="kp-error-code">403</div>
        <h1 class="h4 mt-2">Akses ditolak</h1>
        <p class="kp-muted">Halaman ini bukan untuk peran akun Anda.</p>
        <a class="btn btn-primary mt-2" href="<?= site_url('dashboard') ?>">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
</body>
</html>
