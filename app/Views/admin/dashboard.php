<?php
/**
 * @var int $totalActive
 * @var int $hadir
 * @var int $notRecorded
 * @var int $late
 * @var int $izin
 * @var int $sakit
 * @var int $alpha
 * @var float|null $monthNet
 * @var array|null $period
 * @var string $chartMonth
 * @var array $recent
 * @var int $openCount
 */
?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="kp-stat">
                <div>
                    <div class="kp-stat-label">Karyawan Aktif</div>
                    <div class="kp-stat-value"><?= (int) $totalActive ?></div>
                    <div class="kp-muted small mt-1">terdaftar di kedai</div>
                </div>
                <div class="kp-stat-icon"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="kp-stat">
                <div>
                    <div class="kp-stat-label">Hadir Hari Ini</div>
                    <div class="kp-stat-value"><?= (int) $hadir ?></div>
                    <div class="kp-muted small mt-1"><?= (int) $notRecorded ?> belum absen</div>
                </div>
                <div class="kp-stat-icon is-success"><i class="bi bi-check2-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="kp-stat">
                <div>
                    <div class="kp-stat-label">Terlambat Hari Ini</div>
                    <div class="kp-stat-value"><?= (int) $late ?></div>
                    <div class="kp-muted small mt-1">izin <?= (int) $izin ?> · sakit <?= (int) $sakit ?> · alpha <?= (int) $alpha ?></div>
                </div>
                <div class="kp-stat-icon is-warning"><i class="bi bi-alarm"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="kp-stat">
                <div>
                    <div class="kp-stat-label">Gaji Bulan Ini</div>
                    <div class="kp-stat-value" style="font-size:1.35rem">
                        <?= $monthNet === null ? 'Belum digenerate' : rupiah($monthNet) ?>
                    </div>
                    <div class="kp-muted small mt-1">
                        <?php if ($period !== null): ?>
                            <?= esc(periode_label((int) $period['period_month'], (int) $period['period_year'])) ?> ·
                            <span class="text-uppercase"><?= esc($period['status']) ?></span>
                        <?php else: ?>
                            <?= esc(periode_label((int) date('n'), (int) date('Y'))) ?>
                        <?php endif ?>
                    </div>
                </div>
                <div class="kp-stat-icon is-info"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pie-chart me-1"></i> Grafik Kehadiran Bulanan</span>
                <input type="month" class="form-control form-control-sm" id="kpChartMonth" value="<?= esc($chartMonth) ?>" style="max-width:150px">
            </div>
            <div class="card-body">
                <div class="kp-chart-box"><canvas id="kpAttendanceChart"></canvas></div>
                <div class="kp-chart-summary" id="kpAttendanceSummary"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart-fill me-1"></i> Grafik Keterlambatan Bulanan</div>
            <div class="card-body">
                <div class="kp-chart-box"><canvas id="kpLateChart"></canvas></div>
                <div class="kp-chart-summary" id="kpLateSummary"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-1"></i> Absensi Terbaru</span>
        <a class="small" href="<?= site_url('admin/attendance') ?>">Lihat semua</a>
    </div>
    <div class="kp-table-wrap">
        <table class="table kp-table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>Shift</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent === []): ?>
                    <tr><td colspan="6" class="kp-empty">Belum ada data absensi.</td></tr>
                <?php endif ?>
                <?php foreach ($recent as $row): ?>
                    <tr>
                        <td><?= tanggal_id($row['work_date']) ?></td>
                        <td>
                            <div class="fw-semibold"><?= esc($row['full_name']) ?></div>
                            <div class="kp-muted" style="font-size:.75rem"><?= esc($row['employee_code']) ?></div>
                        </td>
                        <td><?= esc($row['shift_name']) ?></td>
                        <td><?= jam_short($row['check_in']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'hadir' && empty($row['check_out'])): ?>
                                <span class="kp-badge-soft is-warning">belum</span>
                            <?php else: ?>
                                <?= jam_short($row['check_out']) ?>
                            <?php endif ?>
                        </td>
                        <td>
                            <span class="badge <?= status_badge($row['status']) ?>"><?= status_label($row['status']) ?></span>
                            <?php if ((int) $row['late_minutes'] > 0): ?>
                                <span class="kp-badge-soft is-warning ms-1">+<?= (int) $row['late_minutes'] ?> mnt</span>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const attendanceCanvas = document.getElementById('kpAttendanceChart');
    const lateCanvas       = document.getElementById('kpLateChart');
    const input             = document.getElementById('kpChartMonth');
    let attendanceChart;
    let lateChart;

    const months = {
        hadir: '#6F4E37',
        terlambat: '#C68B59',
        menit: '#A67B5B'
    };

    async function loadCharts(month) {
        const res  = await fetch('<?= site_url('admin/dashboard/chart-data') ?>?month=' + encodeURIComponent(month));
        const data = await res.json();

        const totalHadir = data.series.hadir.reduce((a, b) => a + b, 0);
        const totalTelat = data.series.terlambat.reduce((a, b) => a + b, 0);
        const totalMenit = data.series.menit_terlambat.reduce((a, b) => a + b, 0);
        const activeMonth = data.labels[data.labels.length - 1];
        document.getElementById('kpAttendanceSummary').innerHTML = `
            <div class="kp-chart-stat"><strong>${totalHadir}</strong><span>Total hadir</span></div>
            <div class="kp-chart-stat"><strong>${data.series.hadir[data.series.hadir.length - 1]}</strong><span>${activeMonth}</span></div>
            <div class="kp-chart-stat"><strong>${data.labels.length}</strong><span>Bulan</span></div>`;
        document.getElementById('kpLateSummary').innerHTML = `
            <div class="kp-chart-stat"><strong>${totalTelat}</strong><span>Kasus terlambat</span></div>
            <div class="kp-chart-stat"><strong>${totalMenit}</strong><span>Total menit</span></div>
            <div class="kp-chart-stat"><strong>${totalTelat ? Math.round(totalMenit / totalTelat) : 0}</strong><span>Rata-rata menit</span></div>`;

        const attendanceData = {
            labels: data.labels,
            datasets: [{
                data: data.series.hadir,
                backgroundColor: ['#2E4A3C', '#6B8068', '#9AA894', '#C9D1C5', '#6F4E37', '#D8C7A7'],
                borderColor: '#FFF8F0', borderWidth: 2
            }]
        };
        const lateData = {
            labels: data.labels,
            datasets: [{
                data: data.series.menit_terlambat,
                backgroundColor: ['#6F4E37', '#805D43', '#927057', '#A5836B', '#B6967D', '#C9AF98'],
                borderColor: '#FFF8F0', borderWidth: 2
            }]
        };
        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 14, padding: 10, font: { size: 10 } } } }
        };

        if (attendanceChart) {
            attendanceChart.data = attendanceData;
            attendanceChart.update();
            lateChart.data = lateData;
            lateChart.update();
            return;
        }

        attendanceChart = new Chart(attendanceCanvas, { type: 'pie', data: attendanceData, options });
        lateChart = new Chart(lateCanvas, { type: 'pie', data: lateData, options });
    }

    loadCharts(input.value);
    input.addEventListener('change', () => loadCharts(input.value));
})();
</script>
<?= $this->endSection() ?>
