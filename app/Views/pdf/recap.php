<?php
/**
 * Rekap gaji bulanan (A4 landscape) untuk TCPDF.
 *
 * @var array $period
 * @var array $rows
 * @var int   $total
 * @var array $company
 */
$periodLabel = periode_label((int) $period['period_month'], (int) $period['period_year']);
?>
<table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:2px solid #4E342E;">
    <tr>
        <td width="60%" style="font-size:13px;font-weight:bold;color:#2B1B12;">
            <?= esc($company['name']) ?>
            <div style="font-size:8px;font-weight:normal;color:#7A6455;"><?= esc($company['address']) ?></div>
        </td>
        <td width="40%" align="right" style="font-size:8px;color:#7A6455;">
            REKAP GAJI BULANAN<br>
            <span style="font-size:10px;color:#2B1B12;font-weight:bold;"><?= esc($periodLabel) ?></span><br>
            <?= esc(tanggal_id($period['start_date'])) ?> - <?= esc(tanggal_id($period['end_date'])) ?>
        </td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="4" cellspacing="0" style="font-size:8px;">
    <thead>
        <tr style="background-color:#4E342E;color:#F5EBDD;font-weight:bold;">
            <td width="4%">No</td>
            <td width="8%">Kode</td>
            <td width="20%">Nama Karyawan</td>
            <td width="13%">Jabatan</td>
            <td width="5%" align="center">Hadir</td>
            <td width="5%" align="center">Izin</td>
            <td width="5%" align="center">Sakit</td>
            <td width="5%" align="center">Alpha</td>
            <td width="6%" align="center">Telat</td>
            <td width="9%" align="right">Bruto</td>
            <td width="9%" align="right">Potongan</td>
            <td width="11%" align="right">Net</td>
        </tr>
    </thead>
    <tbody>
        <?php if ($rows === []): ?>
            <tr><td colspan="12" align="center" style="color:#7A6455;">Tidak ada data pada periode ini.</td></tr>
        <?php endif ?>
        <?php foreach ($rows as $i => $row): ?>
            <tr style="background-color:<?= $i % 2 === 0 ? '#FFF8F0' : '#FFFFFF' ?>;">
                <td><?= $i + 1 ?></td>
                <td><?= esc($row['employee_code']) ?></td>
                <td><?= esc($row['employee_name']) ?></td>
                <td><?= esc($row['position_name']) ?></td>
                <td align="center"><?= (int) $row['days_present'] ?></td>
                <td align="center"><?= (int) $row['days_izin'] ?></td>
                <td align="center"><?= (int) $row['days_sakit'] ?></td>
                <td align="center"><?= (int) $row['days_alpha'] ?></td>
                <td align="center"><?= (int) $row['total_late_minutes'] ?></td>
                <td align="right"><?= rupiah($row['gross_salary']) ?></td>
                <td align="right"><?= rupiah((int) $row['late_deduction'] + (int) $row['alpha_deduction']) ?></td>
                <td align="right" style="font-weight:bold;"><?= rupiah($row['net_salary']) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr style="background-color:#F5EBDD;font-weight:bold;">
            <td colspan="9" align="right">TOTAL</td>
            <td align="right"><?= rupiah(array_sum(array_map(static fn ($r) => (int) $r['gross_salary'], $rows))) ?></td>
            <td align="right"><?= rupiah(array_sum(array_map(static fn ($r) => (int) $r['late_deduction'] + (int) $r['alpha_deduction'], $rows))) ?></td>
            <td align="right"><?= rupiah($total) ?></td>
        </tr>
    </tfoot>
</table>

<br>

<table width="100%" cellpadding="0" cellspacing="0" style="font-size:8px;color:#7A6455;">
    <tr>
        <td width="60%">
            Status periode: <strong style="color:#2B1B12;"><?= strtoupper(esc($period['status'])) ?></strong><br>
            Dokumen dihasilkan otomatis oleh KopiPay pada <?= esc(tanggal_id(date('Y-m-d'))) ?>.
        </td>
        <td width="40%" align="center" style="color:#2B1B12;">
            Admin / Pemilik,
            <br><br><br>
            ( ______________________ )
        </td>
    </tr>
</table>
