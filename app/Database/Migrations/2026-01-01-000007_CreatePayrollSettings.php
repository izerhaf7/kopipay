<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'late_grace_minutes'       => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 10],
            'late_deduction_per_minute'=> ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 1000],
            'alpha_penalty'            => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 50000],
            'self_checkin_enabled'     => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'checkin_early_minutes'    => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 60],
            'allowed_ip_ranges'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'company_name'             => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false, 'default' => 'KopiPay'],
            'company_address'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
            'updated_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('payroll_settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('payroll_settings', true);
    }
}
