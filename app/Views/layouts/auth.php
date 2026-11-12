<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Masuk') ?> · KopiPay</title>
    <link rel="icon" href="<?= base_url('assets/img/logo.svg') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/kopipay.css') ?>" rel="stylesheet">
</head>
<body class="kp-login-body">
    <div class="kp-login-card">
        <img class="kp-login-logo" src="<?= base_url('assets/img/logo-lockup.svg') ?>" alt="KopiPay">
        <p class="text-center kp-muted mb-4" style="font-size:.85rem">Sistem Informasi Absensi &amp; Penggajian</p>

        <?= $this->include('layouts/partials/flash') ?>
        <?= $this->renderSection('content') ?>

        <hr class="kp-divider">
        <p class="text-center kp-muted mb-0" style="font-size:.75rem">
            &copy; <?= date('Y') ?> <?= esc(kp_company()['name']) ?> · Kedai Kopi
        </p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
