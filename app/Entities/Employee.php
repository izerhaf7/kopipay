<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Employee extends Entity
{
    protected $dates = ['hire_date', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'position_id' => 'integer',
        'shift_id'    => 'integer',
        'hire_date'   => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }
}
