<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->setAutoRoute(false);

// ---------------------------------------------------------------------
// Publik
// ---------------------------------------------------------------------
$routes->get('/', 'HomeController::index');
$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout', ['as' => 'logout']);

// Ganti password paksa (dikecualikan dari filter login, lihat Config\Filters)
$routes->get('reset-password', 'PasswordController::forced', ['as' => 'reset-password']);
$routes->post('reset-password', 'PasswordController::updateForced');

// ---------------------------------------------------------------------
// Semua role yang sudah login
// ---------------------------------------------------------------------
$routes->get('dashboard', 'DashboardController::index', ['as' => 'dashboard']);

// ---------------------------------------------------------------------
// Admin / Owner
// ---------------------------------------------------------------------
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'role:admin'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/chart-data', 'DashboardController::chartData');

    // Jabatan
    $routes->get('positions', 'PositionController::index', ['filter' => 'permission:positions.manage']);
    $routes->get('positions/new', 'PositionController::new', ['filter' => 'permission:positions.manage']);
    $routes->post('positions', 'PositionController::create', ['filter' => 'permission:positions.manage']);
    $routes->get('positions/(:num)/edit', 'PositionController::edit/$1', ['filter' => 'permission:positions.manage']);
    $routes->post('positions/(:num)', 'PositionController::update/$1', ['filter' => 'permission:positions.manage']);
    $routes->post('positions/(:num)/delete', 'PositionController::delete/$1', ['filter' => 'permission:positions.manage']);

    // Shift
    $routes->get('shifts', 'ShiftController::index', ['filter' => 'permission:shifts.manage']);
    $routes->get('shifts/new', 'ShiftController::new', ['filter' => 'permission:shifts.manage']);
    $routes->post('shifts', 'ShiftController::create', ['filter' => 'permission:shifts.manage']);
    $routes->get('shifts/(:num)/edit', 'ShiftController::edit/$1', ['filter' => 'permission:shifts.manage']);
    $routes->post('shifts/(:num)', 'ShiftController::update/$1', ['filter' => 'permission:shifts.manage']);
    $routes->post('shifts/(:num)/delete', 'ShiftController::delete/$1', ['filter' => 'permission:shifts.manage']);

    // Karyawan
    $routes->get('employees', 'EmployeeController::index', ['filter' => 'permission:employees.manage']);
    $routes->get('employees/new', 'EmployeeController::new', ['filter' => 'permission:employees.manage']);
    $routes->post('employees', 'EmployeeController::create', ['filter' => 'permission:employees.manage']);
    $routes->get('employees/(:num)/edit', 'EmployeeController::edit/$1', ['filter' => 'permission:employees.manage']);
    $routes->post('employees/(:num)', 'EmployeeController::update/$1', ['filter' => 'permission:employees.manage']);
    $routes->post('employees/(:num)/toggle-status', 'EmployeeController::toggleStatus/$1', ['filter' => 'permission:employees.manage']);
    $routes->post('employees/(:num)/delete', 'EmployeeController::delete/$1', ['filter' => 'permission:employees.manage']);

    // Absensi
    $routes->get('attendance', 'AttendanceController::index', ['filter' => 'permission:attendance.view']);
    $routes->get('attendance/input', 'AttendanceController::bulkInputForm', ['filter' => 'permission:attendance.manage']);
    $routes->post('attendance/input', 'AttendanceController::bulkInput', ['filter' => 'permission:attendance.manage']);
    $routes->post('attendance/mark-alpha', 'AttendanceController::markAlpha', ['filter' => 'permission:attendance.manage']);
    $routes->get('attendance/(:num)/edit', 'AttendanceController::edit/$1', ['filter' => 'permission:attendance.manage']);
    $routes->post('attendance/(:num)', 'AttendanceController::update/$1', ['filter' => 'permission:attendance.manage']);
    $routes->post('attendance/(:num)/delete', 'AttendanceController::delete/$1', ['filter' => 'permission:attendance.manage']);

    // Payroll
    $routes->get('payroll', 'PayrollController::index', ['filter' => 'permission:payroll.view']);
    $routes->post('payroll/generate', 'PayrollController::generate', ['filter' => 'permission:payroll.generate']);
    $routes->get('payroll/(:num)', 'PayrollController::show/$1', ['filter' => 'permission:payroll.view']);
    $routes->post('payroll/(:num)/finalize', 'PayrollController::finalize/$1', ['filter' => 'permission:payroll.finalize']);
    $routes->post('payroll/(:num)/reopen', 'PayrollController::reopen/$1', ['filter' => 'permission:payroll.finalize']);
    $routes->get('payroll/slip/(:num)/pdf', 'PayrollController::slipPdf/$1', ['filter' => 'permission:payroll.export']);
    $routes->get('payroll/(:num)/recap/pdf', 'PayrollController::recapPdf/$1', ['filter' => 'permission:payroll.export']);

    // Manajemen user
    $routes->get('users', 'UserController::index', ['filter' => 'role:admin']);
    $routes->get('users/new', 'UserController::new', ['filter' => 'role:admin']);
    $routes->post('users', 'UserController::create', ['filter' => 'role:admin']);
    $routes->get('users/(:num)/edit', 'UserController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('users/(:num)', 'UserController::update/$1', ['filter' => 'role:admin']);
    $routes->post('users/(:num)/reset', 'UserController::reset/$1', ['filter' => 'role:admin']);
    $routes->post('users/(:num)/delete', 'UserController::delete/$1', ['filter' => 'role:admin']);

    $routes->get('settings', 'SettingController::index', ['filter' => 'permission:settings.manage']);
    $routes->post('settings', 'SettingController::update', ['filter' => 'permission:settings.manage']);
    $routes->get('logs', 'ActivityLogController::index', ['filter' => 'permission:logs.view']);
});

// ---------------------------------------------------------------------
// Karyawan / Kasir
// ---------------------------------------------------------------------
$routes->group('me', ['namespace' => 'App\Controllers\Employee', 'filter' => ['role:karyawan', 'employeeProfile']], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('profile', 'ProfileController::show');
    $routes->get('password', 'ProfileController::password');
    $routes->post('password', 'ProfileController::updatePassword');

    $routes->get('attendance', 'AttendanceController::index', ['filter' => 'permission:attendance.view-own']);
    $routes->post('attendance/check-in', 'AttendanceController::checkIn', ['filter' => 'permission:attendance.self']);
    $routes->post('attendance/check-out', 'AttendanceController::checkOut', ['filter' => 'permission:attendance.self']);

    $routes->get('payslips', 'PayslipController::index', ['filter' => 'permission:slip.view-own']);
    $routes->get('payslips/(:num)/pdf', 'PayslipController::pdf/$1', ['filter' => 'permission:slip.view-own']);
});
