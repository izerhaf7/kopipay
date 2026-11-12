<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Halaman ganti password paksa (force_pass_reset).
 *
 * Route ini sengaja dikecualikan dari filter `login` karena
 * LocalAuthenticator::check() melempar redirect ke sini selama
 * force_pass_reset masih 1 (kalau tidak, terjadi loop redirect).
 */
class PasswordController extends BaseController
{
    public function forced()
    {
        $auth = service('authentication');

        if (! $auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }

        return view('Auth/force_password', ['title' => 'Ganti Password']);
    }

    public function updateForced(): RedirectResponse
    {
        $auth = service('authentication');

        if (! $auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }

        $rules = [
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $users = model(UserModel::class);
        $user  = $users->find($auth->user()->id);

        if (! $user) {
            return redirect()->to(site_url('logout'));
        }

        $user->setPassword((string) $this->request->getPost('password'));
        $user->setForcePassReset(false);
        $users->save($user);

        $auth->logout();

        return redirect()->to(site_url('login'))->with('message', 'Password berhasil diperbarui. Silakan masuk kembali.');
    }
}
