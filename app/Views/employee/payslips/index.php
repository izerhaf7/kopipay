<?= $this->extend('layouts/employee') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header"><i class="bi bi-receipt me-1"></i> Slip Gaji Saya</div>
    <div class="card-body p-0">
        <?php if ($rows === []): ?>
            <div class="kp-empty">
                <i class="bi bi-hourglass-split"></i>
                Belum ada slip gaji final. Slip muncul setelah Admin memfinalisasi payroll.
            </div>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <div class="px-3 py-3 border-bottom" style="border-color:var(--kp-border)!important">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold"><?= esc(periode_label((int) $row->period_month, (int) $row->period_year)) ?></div>
                            <div class="kp-muted small">
                                <?= (int) $row->days_present ?> hadir ·
                                <?= (int) $row->days_izin ?> izin ·
                                <?= (int) $row->days_sakit ?> sakit ·
                                <?= (int) $row->days_alpha ?> alpha
                            </div>
                        </div>
                        <span class="badge text-bg-success">Final</span>
                    </div>

                    <div class="row g-2 mt-2 small">
                        <div class="col-4">
                            <div class="kp-muted" style="font-size:.72rem">Bruto</div>
                            <div class="fw-semibold"><?= rupiah($row->gross_salary) ?></div>
                        </div>
                        <div class="col-4">
                            <div class="kp-muted" style="font-size:.72rem">Potongan</div>
                            <div class="fw-semibold text-danger">-<?= rupiah($row->totalDeduction()) ?></div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="kp-muted" style="font-size:.72rem">Diterima</div>
                            <div class="fw-bold"><?= rupiah($row->net_salary) ?></div>
                        </div>
                    </div>

                    <a class="btn btn-ghost btn-sm w-100 mt-2" href="<?= site_url('me/payslips/' . $row->id . '/pdf') ?>">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Unduh Slip PDF
                    </a>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>
