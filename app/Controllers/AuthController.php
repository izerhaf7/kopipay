<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Login/logout KopiPay.
 *
 * Registrasi publik dan reset password via email dimatikan (Config\Auth),
 * akun hanya dibuat Admin lewat AccountService.
 */
class AuthController extends BaseController
{
    protected $auth;
    protected $config;

    public function __construct()
    {
        $this->auth   = service('authentication');
        $this->config = config('Auth');
    }

    public function login()
    {
        if ($this->auth->isLoggedIn()) {
            return redirect()->to(site_url('dashboard'));
        }

        // Simpan halaman asal supaya setelah login user kembali ke sana.
        if (! session('redirect_url')) {
            $_SESSION['redirect_url'] = previous_url() !== current_url() ? previous_url() : site_url('dashboard');
        }

        return view('Auth/login', ['title' => 'Masuk']);
    }

    public function attemptLogin(): RedirectResponse
    {
        $rules = [
            'login'    => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $login    = (string) $this->request->getPost('login');
        $password = (string) $this->request->getPost('password');
        $type     = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        try {
            $ok = $this->auth->attempt([$type => $login, 'password' => $password], false);
        } catch (\CodeIgniter\HTTP\Exceptions\RedirectException $e) {
            // Myth/Auth melempar redirect saat user wajib ganti password.
            return redirect()->to(site_url('reset-password'));
        }

        if (! $ok) {
            return redirect()->back()
                ->withInput()
                ->with('error', $this->auth->error() ?? 'Username atau password salah.');
        }

        $user = $this->auth->user();

        if ($user && $user->force_pass_reset) {
            return redirect()->to(site_url('reset-password'));
        }

        $redirect = session('redirect_url') ?? site_url('dashboard');
        unset($_SESSION['redirect_url']);

        return redirect()->to($redirect)->with('message', 'Selamat datang, ' . ($user->username ?? '') . '.');
    }

    public function logout(): RedirectResponse
    {
        if ($this->auth->isLoggedIn()) {
            $this->auth->logout();
        }

        return redirect()->to(site_url('login'))->with('message', 'Anda sudah keluar.');
    }
}
