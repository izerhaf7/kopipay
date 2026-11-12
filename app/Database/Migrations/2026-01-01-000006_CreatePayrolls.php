<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrolls extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payroll_period_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'employee_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            // snapshot
            'employee_code'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'employee_name'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'position_name'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'daily_rate'         => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'late_rate'          => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'alpha_penalty'      => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            // rekap
            'days_present'       => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'days_izin'          => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'days_sakit'         => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'days_alpha'         => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'total_late_minutes' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'gross_salary'       => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'late_deduction'     => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'alpha_deduction'    => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'net_salary'         => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['payroll_period_id', 'employee_id']);
        $this->forge->addForeignKey('payroll_period_id', 'payroll_periods', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('payrolls', true);
    }

    public function down()
    {
        $this->forge->dropTable('payrolls', true);
    }
}
