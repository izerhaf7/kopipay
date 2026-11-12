<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\RuleViolationException;
use App\Models\ActivityLogModel;
use App\Models\PositionModel;

class PositionController extends BaseController
{
    protected PositionModel $positions;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->positions = model(PositionModel::class);
        $this->logs      = model(ActivityLogModel::class);
    }

    public function index()
    {
        return view('admin/positions/index', [
            'title'     => 'Jabatan',
            'subtitle'  => 'Tarif harian menempel pada jabatan',
            'positions' => $this->positions->orderBy('daily_rate', 'DESC')->findAll(),
        ]);
    }

    public function new()
    {
        return view('admin/positions/form', [
            'title'   => 'Jabatan Baru',
            'subtitle' => 'Tentukan nama jabatan dan tarif harian',
            'position' => null,
        ]);
    }

    public function create()
    {
        $data = $this->payload();

        if (! $this->positions->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->positions->errors());
        }

        $this->logs->write(user_id(), 'position.create', 'position', (int) $this->positions->getInsertID(), 'Jabatan ' . $data['name'] . ' dibuat');

        return redirect()->to(site_url('admin/positions'))->with('message', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $position = $this->positions->find($id);

        if ($position === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/positions/form', [
            'title'    => 'Edit Jabatan',
            'subtitle' => 'Perubahan tarif tidak mengubah slip payroll lama (snapshot)',
            'position' => $position,
        ]);
    }

    public function update(int $id)
    {
        $position = $this->positions->find($id);

        if ($position === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->payload();

        if (! $this->positions->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->positions->errors());
        }

        $this->logs->write(user_id(), 'position.update', 'position', $id, 'Jabatan ' . $data['name'] . ' diperbarui');

        return redirect()->to(site_url('admin/positions'))->with('message', 'Jabatan diperbarui.');
    }

    public function delete(int $id)
    {
        $position = $this->positions->find($id);

        if ($position === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->positions->employeeCount($id) > 0) {
            return redirect()->to(site_url('admin/positions'))
                ->with('error', 'Jabatan "' . $position['name'] . '" masih dipakai karyawan. Pindahkan karyawannya terlebih dahulu.');
        }

        try {
            $this->positions->delete($id);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/positions'))->with('error', 'Jabatan tidak bisa dihapus karena masih berelasi dengan data lain.');
        }

        $this->logs->write(user_id(), 'position.delete', 'position', $id, 'Jabatan ' . $position['name'] . ' dihapus');

        return redirect()->to(site_url('admin/positions'))->with('message', 'Jabatan dihapus.');
    }

    private function payload(): array
    {
        return [
            'name'        => trim((string) $this->request->getPost('name')),
            'daily_rate'  => (int) preg_replace('/\D/', '', (string) $this->request->getPost('daily_rate')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
        ];
    }
}
