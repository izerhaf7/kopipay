<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePositions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'daily_rate'  => ['type' => 'DECIMAL', 'constraint' => '12,0', 'null' => false, 'default' => 0],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('positions', true);
    }

    public function down()
    {
        $this->forge->dropTable('positions', true);
    }
}
