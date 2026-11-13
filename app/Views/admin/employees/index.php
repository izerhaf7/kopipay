<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $cred = session()->getFlashdata('credentials'); ?>
<?php if ($cred): ?>
    <div class="card mb-3" style="border-color:var(--kp-caramel)">
        <div class="card-body">
            <div class="d-flex align-items-start gap-2">
                <div class="kp-stat-icon is-warning"><i class="bi bi-key"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= esc($cred['title']) ?></div>
                    <div class="mt-2 d-flex flex-wrap gap-3">
                        <div><span class="kp-muted small">Kode</span><div class="fw-semibold"><?= esc($cred['code']) ?></div></div>
                        <?php if (! empty($cred['email'])): ?>
                            <div><span class="kp-muted small">Email</span><div class="fw-semibold"><?= esc($cred['email']) ?></div></div>
                        <?php endif ?>
                        <div><span class="kp-muted small">Password</span>
                            <div class="fw-semibold" style="font-family:ui-monospace,monospace"><?= esc($cred['password']) ?></div>
                        </div>
                    </div>
                    <div class="kp-muted small mt-2"><?= esc($cred['note']) ?></div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= site_url('admin/employees') ?>">
            <input type="hidden" name="sort" value="<?= esc($filters['sort']) ?>">
            <input type="hidden" name="dir" value="<?= esc($filters['dir']) ?>">
            <div class="col-6 col-md-1"><label class="form-label">Show</label><select class="form-select" name="per_page" onchange="this.form.submit()"><?php foreach ([10, 25, 50] as $size): ?><option value="<?= $size ?>" <?= $filters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?></option><?php endforeach ?></select></div>
            <div class="col-12 col-md-3"><label class="form-label">Cari</label><input type="text" class="form-control" name="q" value="<?= esc($filters['q']) ?>" placeholder="Nama atau kode karyawan"></div>
            <div class="col-6 col-md-2"><label class="form-label">Jabatan</label><select class="form-select" name="position_id"><option value="0">Semua</option><?php foreach ($positions as $position): ?><option value="<?= (int) $position['id'] ?>" <?= (int) $filters['position_id'] === (int) $position['id'] ? 'selected' : '' ?>><?= esc($position['name']) ?></option><?php endforeach ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label">Shift</label><select class="form-select" name="shift_id"><option value="0">Semua</option><?php foreach ($shifts as $shift): ?><option value="<?= (int) $shift['id'] ?>" <?= (int) $filters['shift_id'] === (int) $shift['id'] ? 'selected' : '' ?>><?= esc($shift['name']) ?></option><?php endforeach ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">Semua</option><option value="aktif" <?= $filters['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= $filters['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></div>
            <div class="col-6 col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search"></i></button><a class="btn btn-ghost" href="<?= site_url('admin/employees') ?>"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-1"></i> Data Karyawan</span>
        <a class="btn btn-primary btn-sm" href="<?= site_url('admin/employees/new') ?>">
            <i class="bi bi-person-plus me-1"></i> Tambah Karyawan
        </a>
    </div>
    <div class="kp-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <?php
                $sortUrl = static function (string $key) use ($filters): string {
                    $next = ($filters['sort'] ?? '') === $key && ($filters['dir'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC';
                    return site_url('admin/employees?' . http_build_query(array_merge($filters, ['sort' => $key, 'dir' => $next])));
                };
                $sortIcon = static function (string $key) use ($filters): string {
                    if (($filters['sort'] ?? '') !== $key) return '<i class="bi bi-arrow-down-up kp-muted ms-1" style="font-size:.7rem"></i>';
                    return ($filters['dir'] ?? 'ASC') === 'ASC' ? '<i class="bi bi-sort-up ms-1"></i>' : '<i class="bi bi-sort-down ms-1"></i>';
                };
                ?>
                <tr>
                    <th><a class="kp-sort-link" href="<?= $sortUrl('name') ?>">Karyawan <?= $sortIcon('name') ?></a></th>
                    <th><a class="kp-sort-link" href="<?= $sortUrl('position') ?>">Jabatan <?= $sortIcon('position') ?></a></th>
                    <th><a class="kp-sort-link" href="<?= $sortUrl('shift') ?>">Shift <?= $sortIcon('shift') ?></a></th>
                    <th><a class="kp-sort-link" href="<?= $sortUrl('hire_date') ?>">Masuk <?= $sortIcon('hire_date') ?></a></th>
                    <th><a class="kp-sort-link" href="<?= $sortUrl('status') ?>">Status <?= $sortIcon('status') ?></a></th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($employees === []): ?>
                    <tr><td colspan="6" class="kp-empty"><i class="bi bi-inbox"></i> Belum ada karyawan.</td></tr>
                <?php endif ?>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="kp-avatar" style="width:32px;height:32px;font-size:.78rem">
                                    <?= esc(strtoupper(substr($employee->full_name, 0, 1))) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= esc($employee->full_name) ?></div>
                                    <div class="kp-muted" style="font-size:.75rem">
                                        <?= esc($employee->employee_code) ?>
                                        <?php if (! empty($employee->phone)): ?> · <?= esc($employee->phone) ?><?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?= esc($employee->position_name ?? '-') ?></div>
                            <div class="kp-muted" style="font-size:.75rem"><?= rupiah($employee->daily_rate ?? 0) ?>/hari</div>
                        </td>
                        <td><?= esc($employee->shift_name ?? '-') ?></td>
                        <td><?= tanggal_id($employee->hire_date) ?></td>
                        <td>
                            <?php if ($employee->status === 'aktif'): ?>
                                <span class="badge text-bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            <?php endif ?>
                        </td>
                        <td class="text-end" style="white-space:nowrap">
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/employees/' . $employee->id) ?>" title="Lihat detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/employees/' . $employee->id . '/edit') ?>" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form class="d-inline" action="<?= site_url('admin/employees/' . $employee->id . '/delete') ?>" method="post"
                                  onsubmit="return confirm('Hapus <?= esc($employee->full_name, 'js') ?>? Riwayat absensi & payroll tetap disimpan.')">
                                <?= csrf_field() ?>
                                <button class="btn btn-ghost btn-sm text-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>
