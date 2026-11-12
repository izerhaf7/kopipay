<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendances extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'shift_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'work_date'     => ['type' => 'DATE', 'null' => false],
            'check_in'      => ['type' => 'DATETIME', 'null' => true],
            'check_out'     => ['type' => 'DATETIME', 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false, 'default' => 'hadir'],
            'late_minutes'  => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'notes'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'recorded_by'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'source'        => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false, 'default' => 'admin'],
            'check_in_ip'   => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'check_out_ip'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['employee_id', 'work_date']);
        $this->forge->addKey('work_date');
        $this->forge->addKey(['work_date', 'status']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('shift_id', 'shifts', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('attendances', true);
    }

    public function down()
    {
        $this->forge->dropTable('attendances', true);
    }
}
