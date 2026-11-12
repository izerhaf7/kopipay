<?php

if (! function_exists('kp_settings')) {
    /**
     * Baris tunggal payroll_settings (di-cache per request).
     */
    function kp_settings(): object
    {
        static $settings = null;

        if ($settings === null) {
            $row      = model(\App\Models\PayrollSettingModel::class)->getSettings();
            $settings = is_array($row) ? (object) $row : $row;
        }

        return $settings;
    }
}

if (! function_exists('kp_company')) {
    function kp_company(): array
    {
        $s = kp_settings();

        return [
            'name'    => $s->company_name ?? 'KopiPay',
            'address' => $s->company_address ?? '',
        ];
    }
}
