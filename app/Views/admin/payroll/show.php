<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $isFinal = $period['status'] === 'final'; ?>

<div class="row g-3">
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="kp-stat">
            <div><div class="kp-stat-label">Karyawan</div><div class="kp-stat-value"><?= count($rows) ?></div></div>
            <div class="kp-stat-icon"><i class="bi bi-people"></i></div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="kp-stat">
            <div><div class="kp-stat-label">Total Bruto</div>
                <div class="kp-stat-value" style="font-size:1.25rem"><?= rupiah(array_sum(array_map(static fn ($r) => (int) $r->gross_salary, $rows))) ?></div>
            </div>
            <div class="kp-stat-icon is-info"><i class="bi bi-cash-stack"></i></div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="kp-stat">
            <div><div class="kp-stat-label">Total Potongan</div>
                <div class="kp-stat-value" style="font-size:1.25rem"><?= rupiah(array_sum(array_map(static fn ($r) => $r->totalDeduction(), $rows))) ?></div>
            </div>
            <div class="kp-stat-icon is-warning"><i class="bi bi-dash-circle"></i></div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="kp-stat">
            <div><div class="kp-stat-label">Total Net</div>
                <div class="kp-stat-value" style="font-size:1.25rem"><?= rupiah($total) ?></div>
            </div>
            <div class="kp-stat-icon is-success"><i class="bi bi-wallet2"></i></div>
        </div></div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="kp-eyebrow">Status periode</span>
                <?php if ($isFinal): ?>
                    <span class="badge text-bg-success">Final</span>
                <?php else: ?>
                    <span class="badge text-bg-warning">Draft</span>
                <?php endif ?>
            </div>
            <div class="kp-muted small mt-1">
                <?php if ($isFinal): ?>
                    Absensi pada <?= tanggal_id($period['start_date']) ?>–<?= tanggal_id($period['end_date']) ?> terkunci. Slip terlihat oleh karyawan.
                <?php else: ?>
                    Slip belum terlihat oleh karyawan. Finalisasi setelah data benar.
                <?php endif ?>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-ghost" href="<?= site_url('admin/payroll/' . $period['id'] . '/recap/pdf') ?>">
                <i class="bi bi-file-earmark-pdf me-1"></i> Rekap PDF
            </a>
            <?php if ($isFinal): ?>
                <form action="<?= site_url('admin/payroll/' . $period['id'] . '/reopen') ?>" method="post"
                      onsubmit="return confirm('Buka kembali periode ini sebagai draft? Slip akan disembunyikan dari karyawan.')">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost text-danger" type="submit"><i class="bi bi-unlock me-1"></i> Buka Kembali</button>
                </form>
            <?php else: ?>
                <form action="<?= site_url('admin/payroll/' . $period['id'] . '/finalize') ?>" method="post"
                      onsubmit="return confirm('Finalisasi periode ini? Absensi terkunci dan slip terlihat karyawan.')">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-lock me-1"></i> Finalisasi</button>
                </form>
            <?php endif ?>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><i class="bi bi-list-ul me-1"></i> Rincian Slip</div>
    <div class="kp-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan</th>
                    <th class="text-end">Tarif/Hari</th>
                    <th class="text-center">Hadir</th>
                    <th class="text-center">Izin</th>
                    <th class="text-center">Sakit</th>
                    <th class="text-center">Alpha</th>
                    <th class="text-center">Telat</th>
                    <th class="text-end">Bruto</th>
                    <th class="text-end">Potongan</th>
                    <th class="text-end">Net</th>
                    <th class="text-end">Slip</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="12" class="kp-empty"><i class="bi bi-inbox"></i> Tidak ada karyawan pada periode ini.</td></tr>
                <?php endif ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($row->employee_name) ?></div>
                            <div class="kp-muted" style="font-size:.75rem"><?= esc($row->employee_code) ?></div>
                        </td>
                        <td class="kp-muted"><?= esc($row->position_name) ?></td>
                        <td class="text-end kp-money"><?= rupiah($row->daily_rate) ?></td>
                        <td class="text-center"><?= (int) $row->days_present ?></td>
                        <td class="text-center"><?= (int) $row->days_izin ?></td>
                        <td class="text-center"><?= (int) $row->days_sakit ?></td>
                        <td class="text-center"><?= (int) $row->days_alpha ?></td>
                        <td class="text-center">
                            <?php if ((int) $row->total_late_minutes > 0): ?>
                                <span class="kp-badge-soft is-warning"><?= (int) $row->total_late_minutes ?> mnt</span>
                            <?php else: ?>
                                <span class="kp-muted">—</span>
                            <?php endif ?>
                        </td>
                        <td class="text-end kp-money"><?= rupiah($row->gross_salary) ?></td>
                        <td class="text-end kp-money text-danger"><?= $row->totalDeduction() > 0 ? '-' . rupiah($row->totalDeduction()) : '—' ?></td>
                        <td class="text-end kp-money"><?= rupiah($row->net_salary) ?></td>
                        <td class="text-end">
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/payroll/slip/' . $row->id . '/pdf') ?>" title="Unduh slip PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
            <?php if ($rows !== []): ?>
                <tfoot>
                    <tr style="background:var(--kp-cream)">
                        <td colspan="10" class="fw-semibold text-end">Total Net</td>
                        <td class="text-end kp-money fw-bold"><?= rupiah($total) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif ?>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
