<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollPeriods extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'period_month' => ['type' => 'SMALLINT', 'constraint' => 2, 'null' => false],
            'period_year'  => ['type' => 'SMALLINT', 'constraint' => 4, 'null' => false],
            'start_date'   => ['type' => 'DATE', 'null' => false],
            'end_date'     => ['type' => 'DATE', 'null' => false],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false, 'default' => 'draft'],
            'generated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'generated_at' => ['type' => 'DATETIME', 'null' => true],
            'finalized_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['period_year', 'period_month']);
        $this->forge->createTable('payroll_periods', true);
    }

    public function down()
    {
        $this->forge->dropTable('payroll_periods', true);
    }
}
