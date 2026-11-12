<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$total   = $pager->getTotal();
$perPage = $pager->getPerPage();
$page    = $pager->getCurrentPage();
$from    = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$to      = min($total, $page * $perPage);
$no      = $from;
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-1"></i> Daftar Shift Kerja</span>
        <a class="btn btn-primary btn-sm" href="<?= site_url('admin/shifts/new') ?>">
            <i class="bi bi-plus-lg me-1"></i> Tambah Shift
        </a>
    </div>

    <div class="kp-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:60px">No</th>
                    <th>Nama Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Toleransi (Menit)</th>
                    <th class="text-end" style="width:110px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($shifts === []): ?>
                    <tr><td colspan="6" class="kp-empty"><i class="bi bi-inbox"></i> Belum ada shift. Tambahkan shift pertama.</td></tr>
                <?php endif ?>
                <?php foreach ($shifts as $shift): ?>
                    <tr>
                        <td class="kp-muted"><?= $no++ ?></td>
                        <td class="fw-semibold"><?= esc($shift['name']) ?></td>
                        <td class="kp-money"><?= substr($shift['start_time'], 0, 5) ?></td>
                        <td class="kp-money"><?= substr($shift['end_time'], 0, 5) ?></td>
                        <td><?= (int) ($shift['tolerance_minutes'] ?? 0) ?> menit</td>
                        <td class="text-end" style="white-space:nowrap">
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/shifts/' . $shift['id'] . '/edit') ?>" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form class="d-inline" action="<?= site_url('admin/shifts/' . $shift['id'] . '/delete') ?>" method="post"
                                  onsubmit="return confirm('Hapus shift <?= esc($shift['name'], 'js') ?>?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-ghost btn-sm text-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2"
         style="border-top:1px solid var(--kp-border)">
        <div class="kp-muted small">
            Menampilkan <?= $from ?> sampai <?= $to ?> dari <?= $total ?> entri
        </div>
        <div><?= $pager->links() ?></div>
    </div>
</div>
<?= $this->endSection() ?>
