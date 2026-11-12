<?php

if (! function_exists('current_employee')) {
    /**
     * Baris employees milik user yang sedang login (di-cache per request).
     */
    function current_employee(): ?\App\Entities\Employee
    {
        static $employee = false;

        if ($employee === false) {
            $userId = user_id();

            $employee = $userId === null
                ? null
                : model(\App\Models\EmployeeModel::class)->findByUserId((int) $userId);
        }

        return $employee;
    }
}
