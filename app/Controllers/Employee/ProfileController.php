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
