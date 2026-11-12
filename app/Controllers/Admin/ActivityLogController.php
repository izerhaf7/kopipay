<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class ActivityLogController extends BaseController
{
    public function index()
    {
        $logs    = model(ActivityLogModel::class);
        $action  = trim((string) $this->request->getGet('action'));
        $builder = $logs->withUser();

        if ($action !== '') {
            $builder->like('activity_logs.action', $action);
        }

        return view('admin/logs/index', [
            'title'    => 'Log Aktivitas',
            'subtitle' => 'Jejak audit aksi sensitif',
            'logs'     => $builder->paginate(25),
            'pager'    => $logs->pager,
            'action'   => $action,
        ]);
    }
}
