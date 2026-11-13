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
<body>
<div class="kp-shell">
    <aside class="kp-sidebar" id="kpSidebar">
        <a class="kp-brand" href="<?= site_url('me/dashboard') ?>">
            <img src="<?= base_url('assets/img/logo.svg') ?>" alt="KopiPay">
            <span><strong>KopiPay</strong><small>Absensi &amp; Payroll</small></span>
        </a>
        <nav class="kp-nav">
            <div class="kp-nav-label">Ringkasan</div>
            <a class="nav-link <?= url_is('me/dashboard') ? 'active' : '' ?>" href="<?= site_url('me/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Dashboard</a>
            <div class="kp-nav-label">Aktivitas Saya</div>
            <a class="nav-link <?= url_is('me/attendance*') ? 'active' : '' ?>" href="<?= site_url('me/attendance') ?>"><i class="bi bi-calendar2-check"></i> Absensi</a>
            <a class="nav-link <?= url_is('me/payslips*') ? 'active' : '' ?>" href="<?= site_url('me/payslips') ?>"><i class="bi bi-wallet2"></i> Slip Gaji</a>
            <div class="kp-nav-label">Akun</div>
            <a class="nav-link <?= url_is('me/profile*') || url_is('me/password*') ? 'active' : '' ?>" href="<?= site_url('me/profile') ?>"><i class="bi bi-person-circle"></i> Profil Saya</a>
        </nav>
    </aside>
    <div class="kp-main">
        <header class="kp-topbar">
            <button class="btn btn-ghost btn-sm kp-sidebar-toggle" type="button" id="kpSidebarToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button>
            <div class="flex-grow-1"><p class="kp-page-title"><?= esc($title ?? 'Dashboard') ?><?php if (! empty($subtitle)): ?><small><?= esc($subtitle) ?></small><?php endif ?></p></div>
            <div class="d-none d-md-flex align-items-center gap-2 me-2"><span class="kp-badge-soft"><i class="bi bi-calendar3"></i> <?= tanggal_id(date('Y-m-d')) ?></span></div>
            <?php $me = current_employee(); $mePhoto = $me?->photo; ?>
            <div class="dropdown"><button class="btn btn-ghost d-flex align-items-center gap-2 p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if ($mePhoto): ?><img class="kp-avatar" style="object-fit:cover" src="<?= base_url('uploads/employees/' . esc($mePhoto)) ?>" alt="Foto profil"><?php else: ?><div class="kp-avatar"><?= esc(strtoupper(substr($me?->full_name ?? user()->username ?? 'K', 0, 1))) ?></div><?php endif ?>
                <div class="d-none d-sm-block lh-sm text-start"><div class="fw-semibold small"><?= esc($me?->full_name ?? user()->username ?? '') ?></div><div class="text-muted" style="font-size:.72rem"><?= esc($me?->employee_code ?? 'Karyawan') ?></div></div><i class="bi bi-chevron-down small"></i>
            </button><ul class="dropdown-menu dropdown-menu-end shadow-sm"><li><h6 class="dropdown-header">Akun Saya</h6></li><li><a class="dropdown-item" href="<?= site_url('me/profile') ?>"><i class="bi bi-person-circle me-2"></i> Profil</a></li><li><a class="dropdown-item" href="<?= site_url('me/password') ?>"><i class="bi bi-shield-lock me-2"></i> Ganti Password</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li></ul></div>
        </header>
        <main class="kp-content">
            <?= $this->include('layouts/partials/flash') ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
