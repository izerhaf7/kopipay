<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= esc($title ?? 'Beranda') ?> · KopiPay</title>
    <link rel="icon" href="<?= base_url('assets/img/logo.svg') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/kopipay.css') ?>" rel="stylesheet">
</head>
<body class="kp-me-body">
<header class="kp-me-top">
    <div class="d-flex align-items-center justify-content-between">
        <div class="kp-brand">
            <img src="<?= base_url('assets/img/logo.svg') ?>" alt="KopiPay">
            <strong>KopiPay</strong>
        </div>
        <div class="text-end lh-sm">
            <div class="small fw-semibold"><?= esc($employee->full_name ?? user()->username ?? '') ?></div>
            <div style="font-size:.72rem;color:#C4A98F"><?= esc($employee->employee_code ?? '') ?></div>
        </div>
    </div>
</header>
<nav class="kp-me-nav">
    <a href="<?= site_url('me/dashboard') ?>" class="<?= url_is('me/dashboard') ? 'active' : '' ?>">
        <i class="bi bi-house-door"></i> Beranda
    </a>
    <a href="<?= site_url('me/attendance') ?>" class="<?= url_is('me/attendance*') ? 'active' : '' ?>">
        <i class="bi bi-fingerprint"></i> Absen
    </a>
    <a href="<?= site_url('me/payslips') ?>" class="<?= url_is('me/payslips*') ? 'active' : '' ?>">
        <i class="bi bi-receipt"></i> Slip Gaji
    </a>
    <a href="<?= site_url('me/profile') ?>" class="<?= url_is('me/profile*') || url_is('me/password*') ? 'active' : '' ?>">
        <i class="bi bi-person-circle"></i> Profil
    </a>
    <a href="<?= site_url('logout') ?>">
        <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
</nav>

<div class="kp-me-wrap">
    <?= $this->include('layouts/partials/flash') ?>
    <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
