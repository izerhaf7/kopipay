<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka rupiah tanpa desimal: 2305000 -> "Rp 2.305.000"
     */
    function rupiah($number, bool $withPrefix = true): string
    {
        $number = (int) round((float) $number);
        $prefix = $withPrefix ? 'Rp ' : '';

        return $prefix . number_format($number, 0, ',', '.');
    }
}

if (! function_exists('tanggal_id')) {
    /**
     * 2026-11-12 -> "12 November 2026"
     */
    function tanggal_id($date, bool $withDay = false): string
    {
        if (empty($date)) {
            return '-';
        }

        $ts = $date instanceof \DateTimeInterface ? $date->getTimestamp() : strtotime((string) $date);

        if ($ts === false) {
            return '-';
        }

        $bulan = nama_bulan((int) date('n', $ts));
        $hari  = $withDay ? ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int) date('w', $ts)] . ', ' : '';

        return $hari . date('j', $ts) . ' ' . $bulan . ' ' . date('Y', $ts);
    }
}

if (! function_exists('nama_bulan')) {
    function nama_bulan(int $month, bool $short = false): string
    {
        $panjang = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $pendek  = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return ($short ? $pendek : $panjang)[$month] ?? '';
    }
}

if (! function_exists('periode_label')) {
    /**
     * "November 2026"
     */
    function periode_label(int $month, int $year): string
    {
        return nama_bulan($month) . ' ' . $year;
    }
}

if (! function_exists('jam_short')) {
    /**
     * "2026-11-12 07:05:00" -> "07:05"
     */
    function jam_short($datetime): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        $ts = $datetime instanceof \DateTimeInterface ? $datetime->getTimestamp() : strtotime((string) $datetime);

        return $ts === false ? '-' : date('H:i', $ts);
    }
}

if (! function_exists('menit_ke_jam')) {
    function menit_ke_jam(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 menit';
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return trim(($h > 0 ? $h . ' jam ' : '') . ($m > 0 ? $m . ' menit' : ''));
    }
}

if (! function_exists('status_badge')) {
    /**
     * Kelas badge Bootstrap untuk status absensi.
     */
    function status_badge(string $status): string
    {
        return match ($status) {
            'hadir' => 'text-bg-success',
            'izin'  => 'text-bg-info',
            'sakit' => 'text-bg-sage',
            'alpha' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }
}

if (! function_exists('status_label')) {
    function status_label(string $status): string
    {
        return ucfirst($status);
    }
}
