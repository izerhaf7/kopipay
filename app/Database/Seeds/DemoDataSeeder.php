<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data demo: 4 karyawan + absensi satu bulan berjalan.
 * Jalankan setelah AuthSeeder & MasterSeeder:
 *   php spark db:seed DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    /** @var list<array{0:string,1:string,2:string,3:string,4:string}> nama, posisi, shift, telepon, email */
    private array $people = [
        ['Rani Puspita', 'Barista', 'Pagi', '081234567001', 'rani@kopipay.local'],
        ['Bagas Prakoso', 'Kasir', 'Siang', '081234567002', 'bagas@kopipay.local'],
        ['Sinta Maharani', 'Barista Senior', 'Pagi', '081234567003', 'sinta@kopipay.local'],
        ['Yoga Pratama', 'Kepala Shift', 'Malam', '081234567004', 'yoga@kopipay.local'],
    ];

    public function run()
    {
        if ($this->db->table('employees')->countAllResults() > 0) {
            echo "  Data karyawan sudah ada, seeder demo dilewati.\n";

            return;
        }

        $positions = $this->db->table('positions')->get()->getResultArray();
        $shifts    = $this->db->table('shifts')->get()->getResultArray();

        $positionMap = array_column($positions, 'id', 'name');
        $shiftMap    = array_column($shifts, 'id', 'name');

        $users = model(\App\Models\UserModel::class);
        $employees = model(\App\Models\EmployeeModel::class);

        $created = [];

        foreach ($this->people as $index => [$name, $position, $shift, $phone, $email]) {
            $code = 'KP-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            $user = new \App\Entities\User([
                'email'    => $email,
                'username' => $code,
                'password' => 'Kopi1234!',
            ]);
            $user->activate();

            $userId = (int) $users->withGroup('karyawan')->insert($user, true);

            $employeeId = (int) $employees->insert([
                'user_id'       => $userId,
                'employee_code' => $code,
                'full_name'     => $name,
                'phone'         => $phone,
                'address'       => 'Bogor, Jawa Barat',
                'position_id'   => $positionMap[$position],
                'shift_id'      => $shiftMap[$shift],
                'hire_date'     => date('Y-m-d', strtotime('-8 months')),
                'status'        => 'aktif',
            ], true);

            $created[] = ['id' => $employeeId, 'shift' => $shifts[array_search($shift, array_column($shifts, 'name'), true)]];
        }

        $this->seedAttendance($created);

        echo "  Demo: 4 karyawan + absensi bulan ini dibuat (password: Kopi1234!).\n";
    }

    private function seedAttendance(array $employees): void
    {
        $grace = (int) ($this->db->table('payroll_settings')->get()->getRowArray()['late_grace_minutes'] ?? 10);

        $start     = date('Y-m-01');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        mt_srand(20260112);

        $rows = [];

        foreach ($employees as $employee) {
            $shift    = $employee['shift'];
            $overnight = (int) $shift['is_overnight'] === 1;

            for ($ts = strtotime($start); $ts <= strtotime($yesterday); $ts = strtotime('+1 day', $ts)) {
                $date    = date('Y-m-d', $ts);
                $weekday = (int) date('N', $ts);

                // Hari Minggu kedai tutup.
                if ($weekday === 7) {
                    continue;
                }

                $roll = mt_rand(1, 100);

                if ($roll <= 82) {
                    $status = 'hadir';
                } elseif ($roll <= 90) {
                    $status = 'izin';
                } elseif ($roll <= 96) {
                    $status = 'sakit';
                } else {
                    $status = 'alpha';
                }

                $row = [
                    'employee_id'  => $employee['id'],
                    'shift_id'     => $shift['id'],
                    'work_date'    => $date,
                    'check_in'     => null,
                    'check_out'    => null,
                    'status'       => $status,
                    'late_minutes' => 0,
                    'notes'        => null,
                    'recorded_by'  => 1,
                    'source'       => 'admin',
                    'check_in_ip'  => null,
                    'check_out_ip' => null,
                ];

                if ($status === 'hadir') {
                    $lateBy = mt_rand(0, 100) <= 18 ? mt_rand(5, 40) : mt_rand(-15, 5);

                    $checkIn  = strtotime($date . ' ' . $shift['start_time']) + ($lateBy * 60);
                    $checkOut = strtotime($date . ' ' . $shift['end_time']) + (mt_rand(-5, 25) * 60);

                    if ($overnight && $checkOut <= $checkIn) {
                        $checkOut = strtotime('+1 day', $checkOut);
                    }

                    $row['check_in']     = date('Y-m-d H:i:s', $checkIn);
                    $row['check_out']    = date('Y-m-d H:i:s', $checkOut);
                    $row['late_minutes'] = $lateBy > $grace ? $lateBy : 0;
                } else {
                    $row['notes'] = ucfirst($status) . ' (data demo)';
                }

                $rows[] = $row;
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            $this->db->table('attendances')->insertBatch($chunk);
        }
    }
}
