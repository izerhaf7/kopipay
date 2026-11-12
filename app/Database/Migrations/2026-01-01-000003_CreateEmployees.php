<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployees extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'employee_code' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'full_name'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            'address'       => ['type' => 'TEXT', 'null' => true],
            'position_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'shift_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'hire_date'     => ['type' => 'DATE', 'null' => false],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => false, 'default' => 'aktif'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('user_id');
        $this->forge->addUniqueKey('employee_code');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('position_id', 'positions', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('shift_id', 'shifts', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('employees', true);
    }

    public function down()
    {
        $this->forge->dropTable('employees', true);
    }
}
