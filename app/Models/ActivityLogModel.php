<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'action', 'entity', 'entity_id', 'detail', 'created_at'];
    protected $useTimestamps = false;

    public function write(?int $userId, string $action, ?string $entity = null, ?int $entityId = null, ?string $detail = null): void
    {
        $this->insert([
            'user_id'    => $userId,
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'detail'     => $detail,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function withUser(): self
    {
        $this->select('activity_logs.*, users.username')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC')
            ->orderBy('activity_logs.id', 'DESC');

        return $this;
    }
}
