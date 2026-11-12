<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pastikan user ber-role karyawan benar-benar punya baris di tabel employees.
 * Tanpa ini, halaman /me/* akan gagal karena tidak ada data karyawan.
 */
class EmployeeProfileFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('authentication');

        if (! $auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }

        $employee = model(\App\Models\EmployeeModel::class)->where('user_id', $auth->user()->id)->first();

        if ($employee === null) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akun Anda belum terhubung dengan data karyawan. Hubungi Admin.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
