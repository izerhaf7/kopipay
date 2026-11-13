<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'address'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'photo');
    }
}
