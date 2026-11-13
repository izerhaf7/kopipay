<?php
/**
 * HTML untuk TCPDF (subset HTML/CSS: tabel + inline style).
 *
 * @var array $p
 * @var array $period
 * @var array $company
 */
$periodLabel = periode_label((int) $period['period_month'], (int) $period['period_year']);
$totalDeductions = (int) ($p['late_deduction'] ?? 0)
    + (int) ($p['izin_deduction'] ?? 0)
    + (int) ($p['sakit_deduction'] ?? 0)
    + (int) ($p['alpha_deduction'] ?? 0);
$documentNumber = 'SLIP-' . (int) $period['period_year'] . str_pad((string) $period['period_month'], 2, '0', STR_PAD_LEFT) . '-' . esc($p['employee_code']);
?>
<style>
    .muted { color: #76675D; }
    .rule { border-bottom: 1px solid #D9CBBE; }
    .section-head { background-color: #F3EEE8; color: #4E342E; font-weight: bold; font-size: 9px; }
    .total-row { background-color: #F7F3EE; font-weight: bold; }
    .net-box { background-color: #4E342E; color: #FFFFFF; }
    .tiny { font-size: 7px; color: #76675D; }
</style>
<table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:2px solid #4E342E;">
    <tr>
        <td width="62%" valign="middle" style="padding-bottom:8px;">
            <span style="font-size:16px;font-weight:bold;color:#4E342E;"><?= esc($company['name']) ?></span><br>
            <span style="font-size:8px;color:#76675D;"><?= esc($company['address']) ?></span>
        </td>
        <td width="38%" align="right" valign="middle" style="padding-bottom:8px;">
            <span style="font-size:15px;font-weight:bold;color:#2B211B;">SLIP GAJI</span><br>
            <span style="font-size:10px;font-weight:bold;color:#6F4E37;"><?= esc($periodLabel) ?></span>
        </td>
    </tr>
</table>
<br>
<table width="100%" cellpadding="4" cellspacing="0" style="font-size:8px;background-color:#FAF8F5;border:1px solid #E5DCD2;">
    <tr>
        <td width="18%" class="muted">NAMA</td><td width="42%" style="font-weight:bold;"><?= esc($p['employee_name']) ?></td>
        <td width="17%" class="muted">NO. SLIP</td><td width="23%" style="font-weight:bold;"><?= $documentNumber ?></td>
    </tr>
    <tr>
        <td class="muted">KODE</td><td><?= esc($p['employee_code']) ?></td><td class="muted">JABATAN</td><td><?= esc($p['position_name']) ?></td>
    </tr>
    <tr>
        <td class="muted">PERIODE</td><td colspan="3"><?= esc(tanggal_id($period['start_date'])) ?> s.d. <?= esc(tanggal_id($period['end_date'])) ?></td>
    </tr>
</table>
<br>
<table width="100%" cellpadding="5" cellspacing="0" style="font-size:8.5px;">
    <tr class="section-head"><td colspan="2">PENDAPATAN</td></tr>
    <tr>
        <td width="68%" class="rule">Gaji pokok (<?= (int) $p['days_present'] ?> hari kerja x <?= rupiah($p['daily_rate']) ?>)</td>
        <td width="32%" align="right" class="rule"><?= rupiah($p['gross_salary']) ?></td>
    </tr>
    <tr class="total-row"><td>Total Pendapatan Bruto</td><td align="right"><?= rupiah($p['gross_salary']) ?></td></tr>
</table>
<br>
<table width="100%" cellpadding="5" cellspacing="0" style="font-size:8.5px;">
    <tr class="section-head"><td colspan="2">POTONGAN</td></tr>
    <tr>
        <td width="68%" class="rule">Keterlambatan (<?= (int) $p['total_late_minutes'] ?> menit x <?= rupiah($p['late_rate']) ?>/menit)</td>
        <td width="32%" align="right" class="rule"><?= rupiah($p['late_deduction']) ?></td>
    </tr>
    <tr><td class="rule">Izin (<?= (int) $p['days_izin'] ?> hari x <?= rupiah($p['izin_penalty']) ?>/hari)</td><td align="right" class="rule"><?= rupiah($p['izin_deduction']) ?></td></tr>
    <tr><td class="rule">Sakit (<?= (int) $p['days_sakit'] ?> hari x <?= rupiah($p['sakit_penalty']) ?>/hari)</td><td align="right" class="rule"><?= rupiah($p['sakit_deduction']) ?></td></tr>
    <tr><td class="rule">Alpha (<?= (int) $p['days_alpha'] ?> hari x <?= rupiah($p['alpha_penalty']) ?>/hari)</td><td align="right" class="rule"><?= rupiah($p['alpha_deduction']) ?></td></tr>
    <tr class="total-row"><td>Total Potongan</td><td align="right"><?= rupiah($totalDeductions) ?></td></tr>
</table>
<br>
<table width="100%" cellpadding="9" cellspacing="0" class="net-box">
    <tr>
        <td width="58%" style="font-size:10px;font-weight:bold;">GAJI BERSIH<br><span style="font-size:7px;font-weight:normal;color:#E8DDD3;">Jumlah diterima karyawan</span></td>
        <td width="42%" align="right" style="font-size:15px;font-weight:bold;"><?= rupiah($p['net_salary']) ?></td>
    </tr>
</table>
<br>
<table width="100%" cellpadding="4" cellspacing="0" style="font-size:8px;border:1px solid #E5DCD2;">
    <tr class="section-head"><td colspan="4">RINGKASAN KEHADIRAN</td></tr>
    <tr align="center">
        <td width="25%"><span style="font-size:12px;font-weight:bold;"><?= (int) $p['days_present'] ?></span><br><span class="muted">Hadir</span></td>
        <td width="25%"><span style="font-size:12px;font-weight:bold;"><?= (int) $p['days_izin'] ?></span><br><span class="muted">Izin</span></td>
        <td width="25%"><span style="font-size:12px;font-weight:bold;"><?= (int) $p['days_sakit'] ?></span><br><span class="muted">Sakit</span></td>
        <td width="25%"><span style="font-size:12px;font-weight:bold;"><?= (int) $p['days_alpha'] ?></span><br><span class="muted">Alpha</span></td>
    </tr>
</table>
<br><br>
<table width="100%" cellpadding="3" cellspacing="0" style="font-size:8px;color:#2B211B;">
    <tr>
        <td width="50%" align="center">Diterima oleh,<br><br><br><br>( <?= esc($p['employee_name']) ?> )</td>
        <td width="50%" align="center"><?= esc(tanggal_id($period['end_date'])) ?><br>Admin / Pemilik,<br><br><br>( ____________________ )</td>
    </tr>
</table>
<br>
<table width="100%" cellpadding="4" cellspacing="0" style="border-top:1px solid #D9CBBE;">
    <tr><td align="center" class="tiny">Dokumen resmi KopiPay · Dihasilkan otomatis · Perhitungan mengikuti snapshot payroll periode ini.</td></tr>
</table>
