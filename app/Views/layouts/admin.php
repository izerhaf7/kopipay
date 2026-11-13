<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard') ?> · KopiPay</title>
    <link rel="icon" href="<?= base_url('assets/img/logo.svg') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/kopipay.css') ?>" rel="stylesheet">
</head>
<body>
<div class="kp-shell">
    <aside class="kp-sidebar" id="kpSidebar">
        <a class="kp-brand" href="<?= site_url('dashboard') ?>">
            <img src="<?= base_url('assets/img/logo.svg') ?>" alt="KopiPay">
            <span>
                <strong>KopiPay</strong>
                <small>Absensi &amp; Payroll</small>
            </span>
        </a>
        <nav class="kp-nav">
            <div class="kp-nav-label">Ringkasan</div>
            <a class="nav-link <?= url_is('admin/dashboard') ? 'active' : '' ?>" href="<?= site_url('admin/dashboard') ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>

            <div class="kp-nav-label">Data Master</div>
            <a class="nav-link <?= url_is('admin/employees*') ? 'active' : '' ?>" href="<?= site_url('admin/employees') ?>">
                <i class="bi bi-people"></i> Karyawan
            </a>
            <a class="nav-link <?= url_is('admin/positions*') ? 'active' : '' ?>" href="<?= site_url('admin/positions') ?>">
                <i class="bi bi-diagram-3"></i> Jabatan
            </a>
            <a class="nav-link <?= url_is('admin/shifts*') ? 'active' : '' ?>" href="<?= site_url('admin/shifts') ?>">
                <i class="bi bi-clock-history"></i> Shift
            </a>

            <div class="kp-nav-label">Operasional</div>
            <a class="nav-link <?= url_is('admin/attendance*') ? 'active' : '' ?>" href="<?= site_url('admin/attendance') ?>">
                <i class="bi bi-calendar2-check"></i> Absensi
            </a>
            <a class="nav-link <?= url_is('admin/payroll*') ? 'active' : '' ?>" href="<?= site_url('admin/payroll') ?>">
                <i class="bi bi-wallet2"></i> Payroll
            </a>

            <a class="nav-link <?= url_is('admin/users*') ? 'active' : '' ?>" href="<?= site_url('admin/users') ?>">
                <i class="bi bi-person-gear"></i> Manajemen User
            </a>

            <div class="kp-nav-label">Sistem</div>
            <a class="nav-link <?= url_is('admin/settings*') ? 'active' : '' ?>" href="<?= site_url('admin/settings') ?>">
                <i class="bi bi-sliders"></i> Pengaturan
            </a>
            <a class="nav-link <?= url_is('admin/logs*') ? 'active' : '' ?>" href="<?= site_url('admin/logs') ?>">
                <i class="bi bi-journal-text"></i> Log Aktivitas
            </a>
            <a class="nav-link <?= url_is('admin/profile*') ? 'active' : '' ?>" href="<?= site_url('admin/profile') ?>">
                <i class="bi bi-person-circle"></i> Profil Saya
            </a>
        </nav>
    </aside>

    <div class="kp-main">
        <header class="kp-topbar">
            <button class="btn btn-ghost btn-sm kp-sidebar-toggle" type="button" id="kpSidebarToggle" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="flex-grow-1">
                <p class="kp-page-title"><?= esc($title ?? 'Dashboard') ?>
                    <?php if (! empty($subtitle)): ?><small><?= esc($subtitle) ?></small><?php endif ?>
                </p>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2 me-2">
                <span class="kp-badge-soft"><i class="bi bi-calendar3"></i> <?= tanggal_id(date('Y-m-d')) ?></span>
            </div>
            <div class="dropdown">
                <button class="btn btn-ghost d-flex align-items-center gap-2 p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php $adminPhoto = user()->photo ?? null; ?>
                    <?php if ($adminPhoto): ?>
                        <img class="kp-avatar" style="object-fit:cover" src="<?= base_url('uploads/users/' . esc($adminPhoto)) ?>" alt="Foto profil">
                    <?php else: ?>
                        <div class="kp-avatar"><?= esc(strtoupper(substr(user()->username ?? 'A', 0, 1))) ?></div>
                    <?php endif ?>
                    <div class="d-none d-sm-block lh-sm text-start">
                        <div class="fw-semibold small"><?= esc(user()->username ?? '') ?></div>
                        <div class="text-muted" style="font-size:.72rem">Administrator</div>
                    </div>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Akun Saya</h6></li>
                    <li><a class="dropdown-item" href="<?= site_url('admin/profile') ?>"><i class="bi bi-person-circle me-2"></i> Profil</a></li>
                    <li><a class="dropdown-item" href="<?= site_url('admin/settings') ?>"><i class="bi bi-sliders me-2"></i> Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                </ul>
            </div>
        </header>

        <main class="kp-content">
            <?= $this->include('layouts/partials/flash') ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const kpSidebar = document.getElementById('kpSidebar');

    document.getElementById('kpSidebarToggle')?.addEventListener('click', function (e) {
        e.stopPropagation();
        kpSidebar.classList.toggle('is-open');
    });

    // klik di area konten atau tekan Esc = tutup sidebar (mobile)
    document.querySelector('.kp-main')?.addEventListener('click', function () {
        if (kpSidebar.classList.contains('is-open') && window.innerWidth < 992) {
            kpSidebar.classList.remove('is-open');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            kpSidebar.classList.remove('is-open');
        }
    });
</script>
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
<?= $this->renderSection('scripts') ?>
</body>
</html>
