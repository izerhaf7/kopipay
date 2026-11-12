<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis yang aman ditampilkan ke pengguna
 * (mis. "Belum waktunya check-in", "Periode payroll sudah final").
 */
class RuleViolationException extends RuntimeException
{
}
