<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-diagram-3 me-1"></i> Daftar Jabatan</span>
        <a class="btn btn-primary btn-sm" href="<?= site_url('admin/positions/new') ?>">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jabatan
        </a>
    </div>
    <div class="kp-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Jabatan</th>
                    <th>Tarif Harian</th>
                    <th>Keterangan</th>
                    <th>Karyawan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($positions === []): ?>
                    <tr><td colspan="5" class="kp-empty"><i class="bi bi-inbox"></i> Belum ada jabatan.</td></tr>
                <?php endif ?>
                <?php foreach ($positions as $position): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($position['name']) ?></td>
                        <td class="kp-money"><?= rupiah($position['daily_rate']) ?></td>
                        <td class="kp-muted"><?= esc($position['description'] ?? '-') ?></td>
                        <td>
                            <?php $count = model(\App\Models\PositionModel::class)->employeeCount((int) $position['id']); ?>
                            <span class="kp-badge-soft"><?= $count ?> orang</span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/positions/' . $position['id'] . '/edit') ?>">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form class="d-inline" action="<?= site_url('admin/positions/' . $position['id'] . '/delete') ?>" method="post"
                                  onsubmit="return confirm('Hapus jabatan <?= esc($position['name'], 'js') ?>?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-ghost btn-sm text-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<p class="kp-muted small mt-3 mb-0">
    <i class="bi bi-info-circle me-1"></i>
    Tarif menempel pada jabatan. Saat payroll digenerate, tarif disalin ke slip (snapshot), jadi perubahan tarif tidak mengubah slip lama.
</p>
<?= $this->endSection() ?>
