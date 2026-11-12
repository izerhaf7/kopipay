<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table         = 'shifts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'start_time', 'end_time', 'tolerance_minutes', 'is_overnight'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name'       => 'required|max_length[60]|is_unique[shifts.name,id,{id}]',
        'start_time' => 'required|regex_match[/^\d{2}:\d{2}(:\d{2})?$/]',
        'end_time'   => 'required|regex_match[/^\d{2}:\d{2}(:\d{2})?$/]',
        'tolerance_minutes' => 'required|is_natural',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Nama shift wajib diisi.',
            'is_unique' => 'Nama shift sudah dipakai.',
        ],
        'start_time' => ['required' => 'Jam mulai wajib diisi.', 'regex_match' => 'Format jam mulai tidak valid.'],
        'end_time'   => ['required' => 'Jam selesai wajib diisi.', 'regex_match' => 'Format jam selesai tidak valid.'],
        'tolerance_minutes' => [
            'required'   => 'Toleransi wajib diisi.',
            'is_natural' => 'Toleransi harus bilangan bulat >= 0.',
        ],
    ];

    public function employeeCount(int $shiftId): int
    {
        return $this->db->table('employees')
            ->where('shift_id', $shiftId)
            ->where('deleted_at', null)
            ->countAllResults();
    }

    public function forSelect(): array
    {
        $rows = $this->orderBy('start_time', 'ASC')->findAll();

        return array_map(static function (array $row): array {
            $row['is_overnight'] = (int) $row['is_overnight'];

            return $row;
        }, $rows);
    }
}
