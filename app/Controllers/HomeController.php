<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Tidak ada landing page: "/" langsung diarahkan ke login atau dashboard.
     */
    public function index()
    {
        if (service('authentication')->isLoggedIn()) {
            return redirect()->to(site_url('dashboard'));
        }

        return redirect()->to(site_url('login'));
    }
}
