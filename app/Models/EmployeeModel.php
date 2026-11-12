<?php

namespace App\Models;

use App\Entities\Employee;
use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table         = 'employees';
    protected $primaryKey    = 'id';
    protected $returnType    = Employee::class;
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'user_id', 'employee_code', 'full_name', 'phone', 'address',
        'position_id', 'shift_id', 'hire_date', 'status',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_code' => 'required|max_length[20]|is_unique[employees.employee_code,id,{id}]',
        'full_name'     => 'required|max_length[120]',
        'position_id'   => 'required|is_natural_no_zero',
        'shift_id'      => 'required|is_natural_no_zero',
        'hire_date'     => 'required|valid_date[Y-m-d]',
        'status'        => 'required|in_list[aktif,nonaktif]',
    ];

    protected $validationMessages = [
        'employee_code' => [
            'required'  => 'Kode karyawan wajib diisi.',
            'is_unique' => 'Kode karyawan sudah dipakai.',
        ],
        'full_name'   => ['required' => 'Nama lengkap wajib diisi.'],
        'position_id' => ['required' => 'Jabatan wajib dipilih.'],
        'shift_id'    => ['required' => 'Shift wajib dipilih.'],
        'hire_date'   => ['required' => 'Tanggal masuk wajib diisi.', 'valid_date' => 'Tanggal masuk tidak valid.'],
        'status'      => ['in_list' => 'Status tidak valid.'],
    ];

    /**
     * Query dasar dengan data jabatan & shift (untuk tabel admin).
     */
    public function withRelations(): self
    {
        $this->select('employees.*, positions.name AS position_name, positions.daily_rate,
                       shifts.name AS shift_name, shifts.start_time, shifts.end_time, shifts.is_overnight')
            ->join('positions', 'positions.id = employees.position_id')
            ->join('shifts', 'shifts.id = employees.shift_id');

        return $this;
    }

    public function findWithRelations(int $id): ?Employee
    {
        return $this->withRelations()->where('employees.id', $id)->first();
    }

    public function findByUserId(int $userId): ?Employee
    {
        return $this->withRelations()->where('employees.user_id', $userId)->first();
    }

    public function activeList(): array
    {
        return $this->withRelations()
            ->where('employees.status', 'aktif')
            ->orderBy('employees.full_name', 'ASC')
            ->findAll();
    }

    /** Kode karyawan berikutnya: KP-001, KP-002, ... */
    public function nextCode(): string
    {
        $row = $this->withDeleted()
            ->select('employee_code')
            ->like('employee_code', 'KP-', 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $number = 1;

        if ($row && preg_match('/(\d+)$/', $row->employee_code, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'KP-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
