<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\PayrollSettingModel;

class SettingController extends BaseController
{
    protected PayrollSettingModel $settings;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->settings = model(PayrollSettingModel::class);
        $this->logs     = model(ActivityLogModel::class);
    }

    public function index()
    {
        return view('admin/settings/index', [
            'title'    => 'Pengaturan',
            'subtitle' => 'Potongan keterlambatan, alpha, dan identitas usaha',
            'settings' => $this->settings->getSettings(),
        ]);
    }

    public function update()
    {
        $rules = [
            'late_grace_minutes'        => 'required|is_natural',
            'late_deduction_per_minute' => 'required|is_natural',
            'alpha_penalty'             => 'required|is_natural',
            'izin_penalty'              => 'required|is_natural',
            'sakit_penalty'             => 'required|is_natural',
            'checkin_early_minutes'     => 'required|is_natural',
            'company_name'              => 'required|max_length[120]',
            'company_address'           => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settings = $this->settings->getSettings();

        $payload = [
            'late_grace_minutes'        => (int) $this->request->getPost('late_grace_minutes'),
            'late_deduction_per_minute' => (int) preg_replace('/\D/', '', (string) $this->request->getPost('late_deduction_per_minute')),
            'alpha_penalty'             => (int) preg_replace('/\D/', '', (string) $this->request->getPost('alpha_penalty')),
            'izin_penalty'              => (int) preg_replace('/\D/', '', (string) $this->request->getPost('izin_penalty')),
            'sakit_penalty'             => (int) preg_replace('/\D/', '', (string) $this->request->getPost('sakit_penalty')),
            'checkin_early_minutes'     => (int) $this->request->getPost('checkin_early_minutes'),
            'self_checkin_enabled'      => $this->request->getPost('self_checkin_enabled') ? 1 : 0,
            'company_name'              => trim((string) $this->request->getPost('company_name')),
            'company_address'           => trim((string) $this->request->getPost('company_address')) ?: null,
        ];

        $this->settings->update((int) $settings['id'], $payload);
        $this->logs->write(user_id(), 'settings.update', 'payroll_setting', (int) $settings['id'], 'Pengaturan payroll diperbarui');

        return redirect()->to(site_url('admin/settings'))->with('message', 'Pengaturan disimpan.');
    }
}
