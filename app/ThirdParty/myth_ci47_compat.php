<?php

/**
 * Shim kompatibilitas: myth/auth 1.2.1 vs CodeIgniter 4.6+.
 *
 * LocalAuthenticator::check() melempar
 *   new \CodeIgniter\Router\Exceptions\RedirectException(...)
 * ketika akun punya force_pass_reset = 1. Sejak CI 4.6 kelas itu pindah ke
 * namespace CodeIgniter\HTTP\Exceptions, sehingga request apa pun yang lewat
 * LoginFilter berakhir dengan:
 *   "Class 'CodeIgniter\Router\Exceptions\RedirectException' not found".
 *
 * File ini didaftarkan di composer.json (autoload.files) supaya selalu
 * dimuat sebelum framework menjalankan filter.
 */
if (! class_exists('CodeIgniter\Router\Exceptions\RedirectException')) {
    class_alias(
        \CodeIgniter\HTTP\Exceptions\RedirectException::class,
        'CodeIgniter\Router\Exceptions\RedirectException'
    );
}
