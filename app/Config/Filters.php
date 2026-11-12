<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use Myth\Auth\Filters\LoginFilter;
use Myth\Auth\Filters\PermissionFilter;
use Myth\Auth\Filters\RoleFilter;

class Filters extends BaseFilters
{
    /**
     * Aliases for filter classes.
     *
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,

        // Myth/Auth
        'login'      => LoginFilter::class,
        'role'       => \App\Filters\RoleFilter::class,
        'permission' => \App\Filters\PermissionFilter::class,

        // KopiPay
        'employeeProfile' => \App\Filters\EmployeeProfileFilter::class,
    ];

    /**
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            // 'toolbar' dimatikan supaya bubble Debug Toolbar tidak muncul
            // di screenshot/demo. Aktifkan lagi saat butuh profiling.
        ],
    ];

    /**
     * @var array{
     *     before: array<string, array{except: list<string>|string}>|list<string>,
     *     after: array<string, array{except: list<string>|string}>|list<string>
     * }
     */
    public array $globals = [
        'before' => [
            'csrf',
            // Semua halaman wajib login, kecuali halaman publik & halaman
            // ganti-password-paksa (kalau tidak, LoginFilter akan melempar
            // redirect ke halaman itu terus-menerus).
            'login' => [
                'except' => [
                    'login',
                    'logout',
                    'reset-password',
                    '/',
                ],
            ],
        ],
        'after' => [
            // 'secureheaders',
        ],
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [];
}
