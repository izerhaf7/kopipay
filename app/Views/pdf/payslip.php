<?php
/**
 * HTML untuk TCPDF (subset HTML/CSS: tabel + inline style).
 *
 * @var array $p
 * @var array $period
 * @var array $company
 */
$periodLabel = periode_label((int) $period['period_month'], (int) $period['period_year']);
?>
<table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:2px solid #4E342E;">
    <tr>
        <td width="70%" style="font-size:13px;font-weight:bold;color:#2B1B12;">
            <?= esc($company['name']) ?>
            <div style="font-size:8px;font-weight:normal;color:#7A6455;"><?= esc($company['address']) ?></div>
        </td>
        <td width="30%" align="right" style="font-size:8px;color:#7A6455;">
            SLIP GAJI<br>
            <span style="font-size:10px;color:#2B1B12;font-weight:bold;"><?= esc($periodLabel) ?></span>
        </td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="3" cellspacing="0" style="font-size:9px;color:#2B1B12;">
    <tr>
        <td width="22%" style="color:#7A6455;">Kode Karyawan</td>
        <td width="28%" style="font-weight:bold;"><?= esc($p['employee_code']) ?></td>
        <td width="22%" style="color:#7A6455;">Jabatan</td>
        <td width="28%"><?= esc($p['position_name']) ?></td>
    </tr>
    <tr>
        <td style="color:#7A6455;">Nama</td>
        <td style="font-weight:bold;"><?= esc($p['employee_name']) ?></td>
        <td style="color:#7A6455;">Periode</td>
        <td><?= esc(tanggal_id($period['start_date'])) ?> - <?= esc(tanggal_id($period['end_date'])) ?></td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="4" cellspacing="0" style="font-size:9px;">
    <tr style="background-color:#4E342E;color:#F5EBDD;font-weight:bold;">
        <td width="60%">PENDAPATAN</td>
        <td width="40%" align="right">JUMLAH</td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #E3D3BF;">
            Gaji pokok harian (<?= (int) $p['days_present'] ?> hari x <?= rupiah($p['daily_rate']) ?>)
        </td>
        <td align="right" style="border-bottom:1px solid #E3D3BF;"><?= rupiah($p['gross_salary']) ?></td>
    </tr>
    <tr style="font-weight:bold;background-color:#F5EBDD;">
        <td>Total Pendapatan (Bruto)</td>
        <td align="right"><?= rupiah($p['gross_salary']) ?></td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="4" cellspacing="0" style="font-size:9px;">
    <tr style="background-color:#4E342E;color:#F5EBDD;font-weight:bold;">
        <td width="60%">POTONGAN</td>
        <td width="40%" align="right">JUMLAH</td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #E3D3BF;">
            Keterlambatan (<?= (int) $p['total_late_minutes'] ?> menit x <?= rupiah($p['late_rate']) ?>)
        </td>
        <td align="right" style="border-bottom:1px solid #E3D3BF;"><?= rupiah($p['late_deduction']) ?></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #E3D3BF;">
            Alpha (<?= (int) $p['days_alpha'] ?> hari x <?= rupiah($p['alpha_penalty']) ?>)
        </td>
        <td align="right" style="border-bottom:1px solid #E3D3BF;"><?= rupiah($p['alpha_deduction']) ?></td>
    </tr>
    <tr style="font-weight:bold;background-color:#F5EBDD;">
        <td>Total Potongan</td>
        <td align="right"><?= rupiah((int) $p['late_deduction'] + (int) $p['alpha_deduction']) ?></td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="6" cellspacing="0" style="font-size:11px;background-color:#2B1B12;color:#FFF8F0;">
    <tr>
        <td width="55%" style="font-weight:bold;">GAJI BERSIH (TAKE HOME PAY)</td>
        <td width="45%" align="right" style="font-weight:bold;font-size:13px;"><?= rupiah($p['net_salary']) ?></td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="3" cellspacing="0" style="font-size:8px;color:#2B1B12;">
    <tr>
        <td width="50%" style="color:#7A6455;">Rekap kehadiran</td>
        <td width="50%">
            Hadir <?= (int) $p['days_present'] ?> ·
            Izin <?= (int) $p['days_izin'] ?> ·
            Sakit <?= (int) $p['days_sakit'] ?> ·
            Alpha <?= (int) $p['days_alpha'] ?>
        </td>
    </tr>
</table>

<br><br>

<table width="100%" cellpadding="0" cellspacing="0" style="font-size:8px;color:#2B1B12;">
    <tr>
        <td width="50%" align="center">
            Diterima oleh,
            <br><br><br><br>
            ( <?= esc($p['employee_name']) ?> )
        </td>
        <td width="50%" align="center">
            <?= esc(tanggal_id($period['end_date'])) ?><br>
            Admin / Pemilik,
            <br><br><br>
            ( ______________________ )
        </td>
    </tr>
</table>

<br>
<div style="font-size:7px;color:#7A6455;text-align:center;border-top:1px solid #E3D3BF;padding-top:4px;">
    Dokumen dihasilkan otomatis oleh KopiPay. Rincian mengikuti snapshot tarif saat payroll digenerate.
</div>
