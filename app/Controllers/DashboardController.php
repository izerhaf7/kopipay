<?php

namespace App\Controllers;

/**
 * Satu pintu dashboard: "/dashboard" lalu diarahkan sesuai role.
 */
class DashboardController extends BaseController
{
    public function index()
    {
        if (in_groups('admin')) {
            return redirect()->to(site_url('admin/dashboard'));
        }

        if (in_groups('karyawan')) {
            return redirect()->to(site_url('me/dashboard'));
        }

        // Role tidak dikenal: jangan biarkan menggantung.
        service('authentication')->logout();

        return redirect()->to(site_url('login'))->with('error', 'Akun Anda tidak memiliki peran yang valid.');
    }
}
