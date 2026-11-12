<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\ShiftModel;

class ShiftController extends BaseController
{
    protected ShiftModel $shifts;
    protected ActivityLogModel $logs;

    public function __construct()
    {
        $this->shifts = model(ShiftModel::class);
        $this->logs   = model(ActivityLogModel::class);
    }

    public function index()
    {
        return view('admin/shifts/index', [
            'title'    => 'Shift Kerja',
            'subtitle' => 'Jam masuk, jam keluar, dan toleransi keterlambatan per shift',
            'shifts'   => $this->shifts->orderBy('start_time', 'ASC')->paginate(10),
            'pager'    => $this->shifts->pager,
        ]);
    }

    public function new()
    {
        return view('admin/shifts/form', [
            'title'    => 'Shift Baru',
            'subtitle' => 'Shift lewat tengah malam ditandai otomatis',
            'shift'    => null,
        ]);
    }

    public function create()
    {
        $data = $this->payload();

        if (! $this->shifts->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->shifts->errors());
        }

        $this->logs->write(user_id(), 'shift.create', 'shift', (int) $this->shifts->getInsertID(), 'Shift ' . $data['name'] . ' dibuat');

        return redirect()->to(site_url('admin/shifts'))->with('message', 'Shift berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $shift = $this->shifts->find($id);

        if ($shift === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/shifts/form', [
            'title'    => 'Edit Shift',
            'subtitle' => 'Perubahan shift tidak mengubah riwayat absensi lama',
            'shift'    => $shift,
        ]);
    }

    public function update(int $id)
    {
        if ($this->shifts->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->payload();

        if (! $this->shifts->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->shifts->errors());
        }

        $this->logs->write(user_id(), 'shift.update', 'shift', $id, 'Shift ' . $data['name'] . ' diperbarui');

        return redirect()->to(site_url('admin/shifts'))->with('message', 'Shift diperbarui.');
    }

    public function delete(int $id)
    {
        $shift = $this->shifts->find($id);

        if ($shift === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->shifts->employeeCount($id) > 0) {
            return redirect()->to(site_url('admin/shifts'))
                ->with('error', 'Shift "' . $shift['name'] . '" masih dipakai karyawan. Pindahkan karyawannya terlebih dahulu.');
        }

        try {
            $this->shifts->delete($id);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/shifts'))->with('error', 'Shift tidak bisa dihapus karena masih dipakai riwayat absensi.');
        }

        $this->logs->write(user_id(), 'shift.delete', 'shift', $id, 'Shift ' . $shift['name'] . ' dihapus');

        return redirect()->to(site_url('admin/shifts'))->with('message', 'Shift dihapus.');
    }

    private function payload(): array
    {
        $start = (string) $this->request->getPost('start_time');
        $end   = (string) $this->request->getPost('end_time');

        $start = strlen($start) === 5 ? $start . ':00' : $start;
        $end   = strlen($end) === 5 ? $end . ':00' : $end;

        return [
            'name'              => trim((string) $this->request->getPost('name')),
            'start_time'        => $start,
            'end_time'          => $end,
            'tolerance_minutes' => (int) $this->request->getPost('tolerance_minutes'),
            'is_overnight'      => $end <= $start ? 1 : 0,
        ];
    }
}
