# KopiPay — Sistem Informasi Absensi & Penggajian Karyawan

Aplikasi web untuk mencatat absensi harian karyawan kedai kopi dan menghasilkan payroll
bulanan otomatis, lengkap dengan slip gaji dan rekap PDF.

Dibangun mengikuti dokumen **KopiPay — Technical Design Document v1.1** (SDD-lite),
termasuk revisi 1.1: karyawan bisa **check-in / check-out sendiri** dengan waktu dari server.

---

## 1. Ringkasan fitur

**Admin / Owner**

- Dashboard ringkasan (karyawan aktif, hadir/terlambat hari ini, gaji bulan berjalan) + grafik tren kehadiran harian (Chart.js).
- CRUD jabatan (tarif harian menempel pada jabatan), CRUD shift (termasuk shift lewat tengah malam).
- CRUD karyawan: satu karyawan = satu akun login (dibuat otomatis dalam satu transaksi), reset password, aktif/nonaktif, soft delete.
- Absensi: pantau check-in mandiri, input izin/sakit/alpha massal, tombol **"Tandai Alpha"** untuk yang belum punya catatan, koreksi jam/status (tercatat di log).
- Payroll: generate satu periode dengan satu tombol (idempoten untuk draft), review, finalisasi (mengunci absensi), buka kembali, export **slip gaji PDF** & **rekap bulanan PDF**.
- Pengaturan: toleransi keterlambatan, potongan per menit, denda alpha, jendela check-in, batasan IP, identitas usaha.
- Log aktivitas (jejak audit aksi sensitif).

**Karyawan / Kasir** (mobile-first, tanpa sidebar)

- Beranda: kartu absen hari ini (jam server berjalan, nama shift, status, tombol Check-in / Check-out).
- Riwayat absensi sendiri per bulan.
- Profil (read-only) dan ganti password.
- Daftar slip gaji **periode final** + unduh PDF milik sendiri.

---

## 2. Tech stack & versi yang dipakai

| Lapisan | Teknologi | Versi terpasang |
|---|---|---|
| Bahasa | PHP | 8.2.12 |
| Framework | CodeIgniter 4 (`codeigniter4/appstarter`) | **4.7.6** |
| Auth & RBAC | Myth/Auth | **1.2.1** |
| Database | MariaDB (XAMPP) | 10.4.32 |
| PDF | TCPDF (`tecnickcom/tcpdf`) | **6.11.4** (pin `^6.7`) — jangan TCPDF 7.x, paketnya tidak menyertakan folder `fonts/` |
| Frontend | Bootstrap 5.3 + Bootstrap Icons + Chart.js | via CDN, tanpa build step |
| Test | PHPUnit | 10.5 |

---

## 3. Kebutuhan

- PHP ≥ 8.1 dengan ekstensi `intl`, `mbstring`, `mysqli`, `pdo_mysql`, `curl`
- Composer 2
- MySQL 8 / MariaDB 10.4+ (butuh InnoDB untuk FK & transaksi)
- Laragon / XAMPP, atau cukup `php spark serve`

---

## 4. Instalasi

```bash
# 1. dependency (vendor/ sudah ikut di folder ini, jalankan bila mau bersih)
composer install

# 2. konfigurasi
cp env .env          # bila .env belum ada
#   sesuaikan: app.baseURL, database.default.*, ADMIN_INITIAL_PASSWORD

# 3. database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS kopipay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php spark migrate            # tabel aplikasi + tabel Myth/Auth
php spark db:seed AuthSeeder    # group, permission, akun admin
php spark db:seed MasterSeeder  # jabatan, shift, pengaturan default
php spark db:seed DemoDataSeeder # opsional: 4 karyawan + absensi 1 bulan

# 4. jalankan
php spark serve --port 8080
# buka http://localhost:8080
```

Database test (untuk PHPUnit):

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS kopipay_test"
```

### Reset data demo

```bash
mysql -u root kopipay -e "SET FOREIGN_KEY_CHECKS=0;
  TRUNCATE payrolls; TRUNCATE payroll_periods; TRUNCATE attendances;
  TRUNCATE activity_logs; TRUNCATE employees; TRUNCATE auth_groups_users;
  TRUNCATE users; TRUNCATE positions; TRUNCATE shifts; TRUNCATE payroll_settings;
  SET FOREIGN_KEY_CHECKS=1;"
php spark db:seed AuthSeeder && php spark db:seed MasterSeeder && php spark db:seed DemoDataSeeder
```

---

## 5. Akun untuk demo

| Peran | Login | Password | Keterangan |
|---|---|---|---|
| Admin / Owner | `admin` | `KopiPay!2026` | password awal dari `.env` (`ADMIN_INITIAL_PASSWORD`); akun baru atau hasil reset password tetap wajib ganti saat login pertama |
| Karyawan | `KP-001` | `Kopi1234!` | Rani Puspita — Barista, shift Pagi (07:00–15:00) |
| Karyawan | `KP-002` | `Kopi1234!` | Bagas Prakoso — Kasir, shift Siang (15:00–23:00) |
| Karyawan | `KP-003` | `Kopi1234!` | Sinta Maharani — Barista Senior, shift Pagi |
| Karyawan | `KP-004` | `Kopi1234!` | Yoga Pratama — Kepala Shift, shift Malam (22:00–06:00, lewat tengah malam) |

> Password karyawan di atas hanya dari **DemoDataSeeder**. Karyawan yang dibuat lewat menu
> Admin mendapat password sementara acak yang **ditampilkan sekali** di halaman karyawan.

---

## 6. Struktur proyek

```
app/
├── Config/            Auth.php (Myth), Filters.php, Routes.php, Pager.php
├── Controllers/
│   ├── HomeController.php        "/" → login / dashboard
│   ├── AuthController.php        login & logout (registrasi publik dimatikan)
│   ├── DashboardController.php   "/dashboard" → redirect per role
│   ├── PasswordController.php    ganti password paksa (force_pass_reset)
│   ├── Admin/                    Dashboard, Position, Shift, Employee,
│   │                             Attendance, Payroll, Setting, ActivityLog
│   └── Employee/                 Dashboard, Attendance (check-in/out), Profile, Payslip
├── Entities/          Employee.php, Payroll.php
├── Exceptions/        RuleViolationException.php  (pesan aman untuk pengguna)
├── Filters/           RoleFilter.php, PermissionFilter.php (403 bertema),
│                      EmployeeProfileFilter.php
├── Helpers/           format_helper (rupiah, tanggal_id, jam_short…), setting_helper,
│                      employee_helper (current_employee)
├── Models/            Position, Shift, Employee, Attendance, PayrollPeriod,
│                      Payroll, PayrollSetting, ActivityLog
├── Services/          AttendanceService, PayrollService, AccountService, PdfService
├── Database/
│   ├── Migrations/    8 migrasi (urutan: positions → shifts → employees →
│   │                  attendances → payroll_periods → payrolls →
│   │                  payroll_settings → activity_logs)
│   └── Seeds/         AuthSeeder, MasterSeeder, DemoDataSeeder
└── Views/
    ├── layouts/       admin.php, employee.php, auth.php, partials/flash.php
    ├── admin/         dashboard, positions, shifts, employees, attendance,
    │                  payroll, settings, logs
    ├── employee/      dashboard, attendance, profile, password, payslips
    ├── pdf/           payslip.php (A5), recap.php (A4 landscape)
    ├── pager/         kopipay.php (Bootstrap 5)
    └── errors/html/   error_403.php, error_404.php
public/assets/         css/kopipay.css, img/logo.svg, img/logo-lockup.svg
tests/unit/            PayrollCalculationTest.php, AttendanceRuleTest.php
```

Pola arsitektur: **Controller (tipis) → Service (logika bisnis) → Model (query)**.

---

## 7. Routing

| Method | URL | Keterangan |
|---|---|---|
| GET | `/` | redirect ke `/login` atau `/dashboard` |
| GET/POST | `/login`, `/logout` | autentikasi |
| GET/POST | `/reset-password` | ganti password paksa (di luar filter login) |
| GET | `/dashboard` | satu pintu, redirect per role |
| GET | `/admin/dashboard`, `/admin/dashboard/chart-data` | ringkasan + JSON grafik |
| CRUD | `/admin/positions`, `/admin/shifts`, `/admin/employees` | master data |
| POST | `/admin/employees/{id}/reset-password`, `/toggle-status`, `/delete` | aksi akun |
| GET/POST | `/admin/attendance`, `/attendance/input`, `/attendance/{id}/edit` | absensi |
| POST | `/admin/attendance/mark-alpha` | tandai alpha massal |
| GET/POST | `/admin/payroll`, `/admin/payroll/generate`, `/payroll/{id}` | payroll |
| POST | `/admin/payroll/{id}/finalize`, `/reopen` | status periode |
| GET | `/admin/payroll/slip/{id}/pdf`, `/payroll/{id}/recap/pdf` | export PDF |
| GET/POST | `/admin/settings`, `/admin/logs` | pengaturan & audit |
| GET | `/me/dashboard`, `/me/attendance`, `/me/payslips`, `/me/profile` | halaman karyawan |
| POST | `/me/attendance/check-in`, `/check-out` | absen mandiri |
| GET | `/me/payslips/{id}/pdf` | slip sendiri (ownership check) |

Semua aksi yang mengubah data memakai **POST + CSRF**, bukan GET.

Pemeriksaan tabel route: `php spark routes`.

---

## 8. Aturan bisnis

### 8.1 Keterlambatan

```
jadwal_masuk = work_date + shift.start_time
toleransi    = shifts.tolerance_minutes  (fallback: payroll_settings.late_grace_minutes)
terlambat    = check_in > jadwal_masuk + toleransi
late_minutes = terlambat ? selisih_menit(check_in, jadwal_masuk) : 0
```

Toleransi menempel pada **shift** (bisa beda: Pagi 15 menit, Malam 20 menit, dst), sehingga satu
kedai bisa punya kebijakan berbeda per shift. Nilai di menu Pengaturan hanya dipakai sebagai
default bila sebuah shift belum diisi.

Status tetap `hadir`; keterlambatan hanya dicatat di `late_minutes`
(dihitung di `AttendanceService::computeLateMinutes()`, bukan di controller/view).

### 8.2 Rumus payroll

```
gross_salary    = days_present × daily_rate
late_deduction  = MIN(total_late_minutes × late_rate, gross_salary)
alpha_deduction = days_alpha × alpha_penalty
net_salary      = MAX(0, gross - late_deduction - alpha_deduction)
```

Izin & sakit **tidak dibayar dan tidak dipotong**. Nilai tarif disalin (snapshot) ke baris
payroll saat generate, sehingga perubahan tarif di kemudian hari tidak mengubah slip lama.

Contoh (Barista Rp 100.000/hari, potongan telat Rp 1.000/menit, denda alpha Rp 50.000):
hadir 24 hari, telat 45 menit, alpha 1 hari → bruto 2.400.000 − 45.000 − 50.000 =
**Rp 2.305.000**. Kasus ini otomatis diuji di `tests/unit/PayrollCalculationTest.php`.

### 8.3 Check-in / check-out mandiri

Request hanya berisi tombol (POST + CSRF). **Karyawan, tanggal, dan jam ditentukan server**
(`Asia/Jakarta`); body request diabaikan.

Ditolak bila: fitur check-in dimatikan, karyawan nonaktif, IP di luar `allowed_ip_ranges`,
di luar jendela (`start − checkin_early_minutes` … `end`), sudah ada catatan pada hari kerja
itu (juga dijaga `UNIQUE (employee_id, work_date)`), atau tanggal masuk periode payroll final.

Shift lewat tengah malam: `work_date` = tanggal **mulainya** shift. Check-in pukul 00:30 pada
shift 22:00–06:00 dicatat untuk tanggal kemarin, dan check-out paginya menutup baris yang sama.

### 8.4 Status periode payroll

`draft` (boleh generate ulang) → `final` (terkunci: absensi pada rentang itu tidak bisa
diubah dan slip baru terlihat oleh karyawan) → bisa dibuka kembali ke `draft` oleh Admin
(tercatat di log).

---

## 9. Hak akses

RBAC dua lapis: **group** (`admin`, `karyawan`) sebagai gerbang kasar, **permission** granular
untuk aksi sensitif, ditambah **ownership check** di kode untuk data milik sendiri.

| Permission | admin | karyawan |
|---|:--:|:--:|
| `dashboard.admin`, `positions.manage`, `shifts.manage`, `employees.manage` | ✅ | |
| `attendance.view`, `attendance.manage` | ✅ | |
| `payroll.view`, `payroll.generate`, `payroll.finalize`, `payroll.export` | ✅ | |
| `settings.manage`, `logs.view` | ✅ | |
| `profile.view-own`, `attendance.self`, `attendance.view-own`, `slip.view-own` | | ✅ |

Perilaku saat akses ditolak:

| Kondisi | Respons |
|---|---|
| Belum login | redirect ke `/login` (URL tujuan disimpan) |
| Karyawan membuka `/admin/*` | **403** halaman bertema KopiPay |
| Admin membuka `/me/*` | **403** |
| Karyawan membuka slip milik orang lain / periode draft | **404** (tidak membocorkan keberadaan data) |

---

## 10. Pengujian

```bash
php vendor/bin/phpunit -c phpunit.dist.xml --no-coverage
```

Hasil terakhir: **OK (26 tests, 43 assertions)**.

- `tests/unit/PayrollCalculationTest.php` — contoh hitung dokumen (2.305.000), tanpa potongan,
  potongan melebihi gross, izin/sakit netral, net tidak negatif, akhir bulan kabisat.
- `tests/unit/AttendanceRuleTest.php` — toleransi vs di luar toleransi, check-in lebih awal,
  shift overnight (batas tanggal, check-in 00:30 → kemarin), jendela check-in, batasan IP/CIDR.

### Uji manual yang sudah dijalankan (bukan hanya otomatis)

| Skenario | Hasil |
|---|---|
| Login admin → ganti password paksa → dashboard | berhasil, alur `force_pass_reset` jalan |
| Semua halaman admin + form edit | HTTP 200 |
| Generate payroll November 2026 | 4 karyawan, total net Rp 3.401.000; silang dengan `SUM` absensi cocok |
| Finalisasi → karyawan melihat slip; generate ulang draft | idempoten, tidak ada baris ganda |
| Check-in KP-002 saat periode final | ditolak (pesan jelas) |
| Check-in setelah periode dibuka | baris `source=self`, IP tercatat, `late_minutes` benar |
| Check-in ganda | ditolak, tetap 1 baris |
| Check-out | `check_out` + IP terisi |
| Koreksi admin 18:32 → 15:02 | `late_minutes` dihitung ulang jadi 0, tercatat di log (before/after) |
| Karyawan unduh slip sendiri vs slip orang lain | 200 `%PDF` vs 404 |
| Karyawan buka `/admin/*`, admin buka `/me/*` | 403 |
| PDF slip A5 & rekap A4 | dirender & diperiksa visual, total konsisten |

---

## 11. Catatan teknis (hal yang mudah bikin macet)

1. **TCPDF 7.x** (`tecnickcom/tcpdf` 7.0.x) tidak menyertakan folder `fonts/`; memanggil
   `SetFont('helvetica')` gagal dengan `unable to read file: helvetica.json`. Proyek ini
   di-pin ke `^6.7`.
2. **Debug Toolbar** dimatikan lewat `Config\Filters::$required['after']` (entri `toolbar`
   dihapus) supaya bubble-nya tidak menutupi UI saat demo/screenshot. Aktifkan kembali bila
   perlu profiling.
3. **Dua server di satu port**: `netstat -ano | findstr :8080` — bila ada dua proses
   (satu di `127.0.0.1`, satu di `[::1]`), `localhost` bisa dilayani proses lama. Matikan
   yang basi sebelum menyimpulkan ada bug routing.
4. **Myth/Auth + CI4 4.7**: filter `role`/`permission` bawaan melempar `PermissionException`
   (tampil 500 di mode development), sehingga diganti `App\Filters\RoleFilter` &
   `App\Filters\PermissionFilter` yang mengembalikan respons **403** bertema.
5. **Filter gabungan di route group** harus array:
   `['filter' => ['role:karyawan', 'employeeProfile']]`. Bila ditulis
   `'role:karyawan,employeeProfile'`, teks kedua dianggap **argumen role**.
6. Helper autoload untuk package tidak otomatis: `Config\Autoload::$helpers` harus memuat
   `'auth'`, `'format'`, `'setting'`, `'employee'`.
7. PHPUnit CI4 default memakai **SQLite3** (`:memory:`) untuk grup `tests`; tanpa ekstensi
   `sqlite3` akan muncul `CriticalError`. `phpunit.dist.xml` di proyek ini sudah diarahkan ke
   MySQL `kopipay_test`.
8. **Myth/Auth 1.2.1 + CI 4.6+**: `LocalAuthenticator::check()` melempar
   `CodeIgniter\Router\Exceptions\RedirectException`, padahal sejak CI 4.6 kelas itu pindah ke
   `CodeIgniter\HTTP\Exceptions`. Akibatnya akun dengan `force_pass_reset = 1` yang membuka
   halaman apa pun berakhir dengan *"Class ... RedirectException not found"*. Perbaikan:
   `app/ThirdParty/myth_ci47_compat.php` (sebuah `class_alias`) yang didaftarkan pada
   `composer.json` → `autoload.files`, sehingga selalu dimuat sebelum filter berjalan.
   Jalankan `composer dump-autoload` setelah mengubah daftar itu.

---

## 12. Asumsi & pengembangan lanjutan

Asumsi yang dipakai (sesuai dokumen desain): izin & sakit tidak dibayar; potongan telat
dihitung per menit setelah toleransi; alpha dipotong nominal tetap; tarif menempel pada
jabatan; satu shift default per karyawan; satu role `admin`.

Di luar cakupan v1: verifikasi check-in berbasis QR dinamis/GPS/selfie, lembur & tunjangan,
kasbon, role Owner terpisah, roster shift mingguan, notifikasi WhatsApp, tanda tangan digital,
migrasi ke CodeIgniter Shield.
