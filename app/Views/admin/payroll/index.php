<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-magic me-1"></i> Generate Payroll</div>
            <div class="card-body">
                <form action="<?= site_url('admin/payroll/generate') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" for="month">Bulan</label>
                            <select class="form-select" id="month" name="month">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= nama_bulan($m) ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="year">Tahun</label>
                            <select class="form-select" id="year" name="year">
                                <?php for ($y = (int) date('Y') + 1; $y >= (int) date('Y') - 2; $y--): ?>
                                    <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                    </div>

                    <div class="alert mt-3 mb-3" style="background:#FBF1DE;color:#7A5405;border:0">
                        <i class="bi bi-info-circle me-1"></i>
                        Generate bersifat <strong>idempoten</strong> untuk periode draft: data lama di periode itu dihapus
                        lalu dihitung ulang. Periode final terkunci.
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-calculator me-1"></i> Generate / Generate Ulang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-1"></i> Peringatan Sebelum Generate</span>
                <span class="kp-badge-soft"><?= esc(periode_label($month, $year)) ?></span>
            </div>
            <div class="card-body">
                <?php if ($warnings['missing'] === [] && $warnings['open'] === []): ?>
                    <p class="mb-0" style="color:#2F5730"><i class="bi bi-check-circle-fill me-1"></i> Data absensi periode ini terlihat lengkap.</p>
                <?php endif ?>

                <?php if ($warnings['missing'] !== []): ?>
                    <div class="mb-3">
                        <div class="fw-semibold mb-1">Karyawan aktif tanpa catatan absensi sama sekali (<?= count($warnings['missing']) ?>)</div>
                        <ul class="mb-0 ps-3 small kp-muted">
                            <?php foreach ($warnings['missing'] as $row): ?>
                                <li><?= esc($row['full_name']) ?> <span class="kp-muted">(<?= esc($row['employee_code']) ?>)</span></li>
                            <?php endforeach ?>
                        </ul>
                        <div class="kp-muted small mt-1">Mereka tidak akan muncul di payroll periode ini.</div>
                    </div>
                <?php endif ?>

                <?php if ($warnings['open'] !== []): ?>
                    <div>
                        <div class="fw-semibold mb-1">Catatan hadir tanpa check-out (<?= count($warnings['open']) ?>)</div>
                        <ul class="mb-0 ps-3 small kp-muted">
                            <?php foreach (array_slice($warnings['open'], 0, 8) as $row): ?>
                                <li><?= esc($row['full_name']) ?> · <?= tanggal_id($row['work_date']) ?> · masuk <?= jam_short($row['check_in']) ?></li>
                            <?php endforeach ?>
                        </ul>
                        <?php if (count($warnings['open']) > 8): ?>
                            <div class="kp-muted small mt-1">+<?= count($warnings['open']) - 8 ?> lainnya...</div>
                        <?php endif ?>
                        <a class="small d-inline-block mt-1" href="<?= site_url('admin/attendance?open=1') ?>">Lengkapi sekarang</a>
                    </div>
                <?php endif ?>

                <hr class="kp-divider">
                <div class="kp-muted small">
                    Status karyawan dihitung per tanggal kerja. Zona waktu server: <strong>Asia/Jakarta</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><i class="bi bi-wallet2 me-1"></i> Riwayat Periode</div>
    <div class="kp-table-wrap">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Rentang</th>
                    <th>Status</th>
                    <th>Total Net</th>
                    <th>Digenerate</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($periods === []): ?>
                    <tr><td colspan="6" class="kp-empty"><i class="bi bi-inbox"></i> Belum ada periode payroll. Generate dulu di atas.</td></tr>
                <?php endif ?>
                <?php foreach ($periods as $period): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc(periode_label((int) $period['period_month'], (int) $period['period_year'])) ?></td>
                        <td class="kp-muted small"><?= tanggal_id($period['start_date']) ?> – <?= tanggal_id($period['end_date']) ?></td>
                        <td>
                            <?php if ($period['status'] === 'final'): ?>
                                <span class="badge text-bg-success">Final</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning">Draft</span>
                            <?php endif ?>
                        </td>
                        <td class="kp-money"><?= rupiah($totals[$period['id']] ?? 0) ?></td>
                        <td class="kp-muted small"><?= ! empty($period['generated_at']) ? tanggal_id($period['generated_at']) : '-' ?></td>
                        <td class="text-end">
                            <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/payroll/' . $period['id']) ?>">
                                Buka <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
