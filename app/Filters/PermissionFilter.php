<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pengganti Myth\Auth\Filters\PermissionFilter dengan respons 403 bertema.
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('authentication');

        if (! $auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }

        foreach ((array) $arguments as $permission) {
            if ($permission !== '' && has_permission($permission)) {
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
