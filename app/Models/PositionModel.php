<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table         = 'positions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'daily_rate', 'description'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name'       => 'required|max_length[100]|is_unique[positions.name,id,{id}]',
        'daily_rate' => 'required|is_natural',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama jabatan wajib diisi.',
            'is_unique'  => 'Nama jabatan sudah dipakai.',
        ],
        'daily_rate' => [
            'required'   => 'Tarif harian wajib diisi.',
            'is_natural' => 'Tarif harian harus bilangan bulat ≥ 0.',
        ],
    ];

    /** Jumlah karyawan aktif yang memakai jabatan ini. */
    public function employeeCount(int $positionId): int
    {
        return $this->db->table('employees')
            ->where('position_id', $positionId)
            ->where('deleted_at', null)
            ->countAllResults();
    }
}
