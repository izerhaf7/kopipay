<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Exceptions\RuleViolationException;
use App\Services\AccountService;

class ProfileController extends BaseController
{
    public function show()
    {
        $employee = current_employee();

        return view('employee/profile', [
            'title'    => 'Profil',
            'employee' => $employee,
        ]);
    }

    public function updateProfile()
    {
        $employee = current_employee();
        if ($employee === null) return redirect()->to(site_url('me/profile'))->with('error', 'Profil karyawan tidak ditemukan.');
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true) || $file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->with('error', 'Foto harus JPG, PNG, atau WEBP dan maksimal 2 MB.');
            }
            $name = $file->getRandomName();
            $directory = FCPATH . 'uploads/employees';
            if (! is_dir($directory)) mkdir($directory, 0755, true);
            $file->move($directory, $name);
            model(\App\Models\EmployeeModel::class)->update($employee->id, ['photo' => $name]);
        }
        return redirect()->to(site_url('me/profile'))->with('message', 'Profil berhasil diperbarui.');
    }
    public function password()
    {
        return view('employee/password', [
            'title'    => 'Ganti Password',
            'employee' => current_employee(),
        ]);
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $accounts = new AccountService();

        if (! $accounts->verifyPassword((int) user_id(), (string) $this->request->getPost('current_password'))) {
            return redirect()->back()->with('error', 'Password saat ini salah.');
        }

        try {
            $accounts->changeOwnPassword((int) user_id(), (string) $this->request->getPost('password'));
        } catch (RuleViolationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('me/profile'))->with('message', 'Password berhasil diubah.');
    }
}
