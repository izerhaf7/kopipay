<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddPhotoToUsers extends Migration
{
    public function up() { $this->forge->addColumn('users', ['photo' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true,'after'=>'username']]); }
    public function down() { $this->forge->dropColumn('users', 'photo'); }
}
