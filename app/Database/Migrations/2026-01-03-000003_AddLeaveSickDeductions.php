<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddLeaveSickDeductions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payroll_settings', [
            'izin_penalty'  => ['type'=>'DECIMAL','constraint'=>'12,0','null'=>false,'default'=>0,'after'=>'alpha_penalty'],
            'sakit_penalty' => ['type'=>'DECIMAL','constraint'=>'12,0','null'=>false,'default'=>0,'after'=>'izin_penalty'],
        ]);
        $this->forge->addColumn('payrolls', [
            'izin_penalty'     => ['type'=>'DECIMAL','constraint'=>'12,0','null'=>false,'default'=>0,'after'=>'alpha_penalty'],
            'sakit_penalty'    => ['type'=>'DECIMAL','constraint'=>'12,0','null'=>false,'default'=>0,'after'=>'izin_penalty'],
            'izin_deduction'   => ['type'=>'DECIMAL','constraint'=>'12,0','null'=>false,'default'=>0,'after'=>'alpha_deduction'],
            'sakit_deduction'  => ['type'=>'DECIMAL','constraint'=>'12,0','null'=>false,'default'=>0,'after'=>'izin_deduction'],
        ]);
    }
    public function down()
    {
        $this->forge->dropColumn('payrolls', ['izin_penalty','sakit_penalty','izin_deduction','sakit_deduction']);
        $this->forge->dropColumn('payroll_settings', ['izin_penalty','sakit_penalty']);
    }
}
