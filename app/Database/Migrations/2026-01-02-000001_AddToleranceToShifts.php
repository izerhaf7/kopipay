<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Toleransi keterlambatan sekarang menempel pada shift (bisa beda per shift),
 * dengan `payroll_settings.late_grace_minutes` sebagai nilai default/fallback.
 */
class AddToleranceToShifts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('shifts', [
            'tolerance_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 15,
                'after'      => 'end_time',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('shifts', 'tolerance_minutes');
    }
}
