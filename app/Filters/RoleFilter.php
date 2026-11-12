<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pengganti Myth\Auth\Filters\RoleFilter.
 *
 * Perbedaan penting: filter ini mengembalikan halaman **403** bertema KopiPay
 * (bukan exception 500) saat role tidak cocok, sesuai bagian 8.3 dokumen desain.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('authentication');

        if (! $auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }

        foreach ((array) $arguments as $group) {
            if ($group !== '' && in_groups($group)) {
                return null;
            }
        }

        return service('response')
            ->setStatusCode(403)
            ->setBody(view('errors/html/error_403'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
