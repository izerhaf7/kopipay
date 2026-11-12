<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShifts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => false],
            'start_time'   => ['type' => 'TIME', 'null' => false],
            'end_time'     => ['type' => 'TIME', 'null' => false],
            'is_overnight' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('shifts', true);
    }

    public function down()
    {
        $this->forge->dropTable('shifts', true);
    }
}
