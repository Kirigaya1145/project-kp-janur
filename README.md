# PT Janur Tangguh Abadi — Sistem Booking Pengiriman Laut

Aplikasi web berbasis Laravel untuk mengelola alur booking pengiriman container laut: mulai dari pengajuan booking oleh customer, penawaran harga oleh staff/admin, penerbitan invoice, verifikasi pembayaran, hingga pelacakan progres pengiriman (surat jalan & data container/vessel).

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Ringkasan untuk Presentasi](#ringkasan-untuk-presentasi)
- [Perbedaan dari Laravel Default](#perbedaan-dari-laravel-default)
- [Penjelasan File Proyek](#penjelasan-file-proyek)
- [Alur Booking](#alur-booking)
- [Role & Akun Default](#role--akun-default)
- [Tech Stack](#tech-stack)
- [Instalasi](#instalasi)
- [Struktur Database](#struktur-database)
- [Rute Utama](#rute-utama)
- [Struktur Folder Penting](#struktur-folder-penting)
- [Catatan Pengembangan](#catatan-pengembangan)

## Fitur Utama

### Customer
- Registrasi & login
- Ajukan booking pengiriman (pilih rute, tanggal kirim, jumlah container, daftar barang) lengkap dengan estimasi harga otomatis (real-time di form)
- Cek status booking tanpa login lewat kode booking (`/booking/cek`)
- Lihat riwayat seluruh booking miliknya (`/booking/riwayat`, perlu login)
- Setujui/tolak penawaran harga final dari admin
- Unggah bukti pembayaran
- Unduh invoice dalam format PDF

### Admin / Staff
- Dashboard berisi seluruh booking dari semua customer, dengan ringkasan jumlah per tahap status
- Kelola master data rute & harga dasar per kg
- Beri penawaran harga final ke customer
- Terbitkan/lihat invoice
- Verifikasi atau tolak bukti pembayaran yang diunggah customer
- Input detail operasional (JOA number, container, shipping line, vessel, ETD/ETA) dan surat jalan
- Update progres pengiriman (dalam pengiriman → diterima → selesai, atau dibatalkan)

## Ringkasan untuk Presentasi

**PT Janur Tangguh Abadi — Sistem Booking Pengiriman Laut** adalah aplikasi web untuk mencatat dan mengelola layanan pengiriman barang menggunakan container. Aplikasi ini menggantikan proses yang sebelumnya dapat tersebar di formulir dan komunikasi manual dengan satu alur digital yang dapat dipantau customer dan dikelola admin/staff.

Pengguna utama aplikasi terdiri dari tiga peran:

- **Customer** membuat booking, melihat penawaran, memberi keputusan, mengunggah bukti pembayaran, dan memantau status.
- **Staff** mengelola data operasional booking dan memberikan penawaran.
- **Admin** memiliki akses pengelolaan booking, rute, pembayaran, dan progres operasional.

Nilai utama aplikasi adalah menyimpan data customer, detail barang, harga, invoice, pembayaran, dan progres pengiriman dalam satu alur yang saling terhubung. Customer juga dapat melacak booking menggunakan kode booking.

### Urutan Demo yang Disarankan

1. Tampilkan halaman beranda dan informasi perusahaan.
2. Login sebagai customer, lalu buat booking dengan memilih rute dan mengisi rincian barang.
3. Tunjukkan kode booking dan halaman cek status.
4. Login sebagai admin/staff, buka booking, lalu berikan harga final.
5. Kembali sebagai customer untuk menyetujui penawaran dan melihat invoice.
6. Unggah bukti pembayaran, lalu tunjukkan verifikasi pembayaran dari sisi admin/staff.
7. Lengkapi data container/surat jalan dan ubah progres hingga booking selesai.

Gunakan akun demo pada bagian [Role & Akun Default](#role--akun-default). Untuk presentasi, sebaiknya siapkan data booking pada beberapa status agar tiap layar dapat ditunjukkan tanpa mengulang semua langkah.

## Perbedaan dari Laravel Default

Laravel menyediakan fondasi seperti routing, autentikasi, middleware, akses database, dan template Blade. Proyek ini menggunakan fondasi tersebut untuk membuat proses bisnis pengiriman laut. Bagian berikut menjelaskan implementasi khusus proyek; daftar ini bukan perbandingan diff per baris dengan instalasi Laravel yang bersih.

| Area | Fondasi Laravel | Implementasi proyek |
|---|---|---|
| Halaman | Route dan view awal | Beranda perusahaan, informasi perusahaan, booking, pelacakan, riwayat, dan dashboard admin |
| Pengguna | Autentikasi dasar | Peran `admin`, `staff`, dan `customer`, termasuk pembatasan akses admin/staff |
| Data | Eloquent dan migration | Rute, booking, barang, invoice, bukti pembayaran, container, surat jalan, dan riwayat status |
| Proses bisnis | Tidak tersedia secara bawaan | Penawaran harga, persetujuan customer, penerbitan invoice, verifikasi pembayaran, dan update pengiriman |
| Dokumen | Tidak tersedia secara bawaan | Template invoice Blade yang dirender menjadi PDF dengan Dompdf |
| Data awal | Seeder contoh Laravel | Akun demo, profil perusahaan, dan rute contoh untuk aplikasi Janur |

Autentikasi memakai Laravel UI. Karena itu, sebagian halaman login/register dan komponen framework tetap merupakan scaffolding atau fondasi, bukan fitur bisnis yang dibuat khusus untuk booking.

## Penjelasan File Proyek

### Rute dan Pengendalian Akses

- `routes/web.php` — mendaftarkan halaman publik, rute customer, proses invoice, dan grup admin. Rute bisnis mengarah ke controller aplikasi.
- `app/Http/Kernel.php` — mendaftarkan alias middleware `role` agar grup admin/staff dapat dibatasi berdasarkan peran.
- `app/Http/Middleware/EnsureRole.php` — menolak akses dengan HTTP 403 ketika pengguna tidak memiliki salah satu peran yang diizinkan.

### Controller

- `app/Http/Controllers/PageController.php` — menampilkan beranda dan halaman tentang perusahaan beserta profil perusahaan.
- `app/Http/Controllers/BookingController.php` — menangani alur customer: membuat booking, melihat riwayat dan status, menyetujui/menolak penawaran, mengunggah bukti pembayaran, serta membuat PDF invoice.
- `app/Http/Controllers/AdminBookingController.php` — menangani dashboard, data rute, penawaran, invoice, verifikasi pembayaran, data operasional, dan perubahan progres pengiriman.
- `app/Http/Controllers/HomeController.php` — halaman `/home` yang berasal dari scaffolding autentikasi Laravel UI.

### Model dan Hubungan Data

- `app/Models/Booking.php` — data utama booking serta relasi ke customer, rute, barang, invoice, container, surat jalan, dan riwayat status. Model ini juga mengubah status internal menjadi label sederhana untuk ditampilkan.
- `app/Models/BookingBarang.php` — rincian barang yang dikirim pada booking.
- `app/Models/RuteHarga.php` — master pelabuhan asal, tujuan, dan harga dasar.
- `app/Models/Invoice.php` — data invoice dan relasinya ke booking serta bukti pembayaran.
- `app/Models/BuktiPembayaran.php` — jumlah, file, dan status konfirmasi pembayaran.
- `app/Models/BookingContainer.php` — nomor container dan informasi pelayaran seperti vessel, ETD, dan ETA.
- `app/Models/SuratJalan.php` — informasi dokumen pengantaran dan penerima.
- `app/Models/StatusBooking.php` — catatan perubahan status dan pengguna yang memperbaruinya.
- `app/Models/CompanyProfile.php` — informasi perusahaan yang digunakan pada halaman dan dokumen.
- `app/Models/User.php` — akun pengguna, peran, dan relasi booking. Model ini menyesuaikan identitas primary key pengguna dengan skema proyek.

### Database dan Data Awal

- `database/migrations/2026_09_04_045000_create_company_profiles_table.php` — membuat tabel profil perusahaan.
- `database/migrations/2026_09_04_050440_add_logo_to_company_profiles_table.php` — menambahkan kolom logo perusahaan.
- `database/migrations/2026_09_04_060000_create_booking_workflow_tables.php` — membuat tabel rute dan tabel-tabel inti proses booking, invoice, pembayaran, operasional, serta riwayat status.
- `database/seeders/DatabaseSeeder.php` — menyiapkan akun demo, profil perusahaan, dan tiga rute contoh. Seeder menggunakan `updateOrCreate`, sehingga data demo dapat diselaraskan kembali saat dijalankan.
- Migration Laravel lainnya, seperti migration `users` dan `personal_access_tokens`, mendukung autentikasi dan infrastruktur framework.

### Tampilan dan Aset

- `resources/views/pages/` — halaman customer dan publik: beranda, tentang, form booking, sukses booking, cek status, dan riwayat.
- `resources/views/admin/` — dashboard admin/staff, daftar rute, dan detail booking.
- `resources/views/layouts/app.blade.php` — layout bersama untuk halaman aplikasi.
- `resources/views/pdf/invoice.blade.php` — struktur dan gaya dokumen invoice yang dirender Dompdf.
- `resources/sass/app.scss` dan `resources/sass/_variables.scss` — stylesheet Sass aplikasi.
- `resources/js/app.js` dan `resources/js/bootstrap.js` — entry point JavaScript dan setup frontend.
- `public/images/` — aset gambar publik, termasuk logo yang dapat dipakai di halaman atau dokumen.

### File Konfigurasi Proyek

- `composer.json` — daftar dependency PHP; proyek menggunakan Laravel 10, Laravel UI, Sanctum, dan Dompdf.
- `package.json` dan `vite.config.js` — dependency frontend serta konfigurasi build asset dengan Vite.
- `phpunit.xml`, `tests/` — konfigurasi dan lokasi pengujian Laravel/PHPUnit.
- `.env` — konfigurasi lokal, termasuk koneksi database. Jangan masukkan file ini atau kredensialnya ke repository.

## Alur Booking

Status booking disederhanakan menjadi 6 tahap yang ditampilkan ke pengguna (`Booking::statusSederhana()`):

```
Menunggu Penawaran → Menunggu Konfirmasi → Menunggu Pembayaran
→ Verifikasi Pembayaran → Proses Pengiriman → Selesai
```

Cabang lain yang bisa terjadi di luar jalur utama: **Penawaran Ditolak** (customer menolak harga) dan **Dibatalkan** (admin membatalkan booking).

Ringkas per tahap:

1. Customer mengajukan booking → status `menunggu_penawaran`.
2. Admin/staff memberi harga final → status `menunggu_konfirmasi_customer`.
3. Customer menyetujui → invoice otomatis diterbitkan, status `menunggu_pembayaran`. Customer menolak → status `penawaran_ditolak`.
4. Customer mengunggah bukti bayar → status `menunggu_verifikasi_pembayaran`.
5. Admin memverifikasi → `siap_operasional` (disetujui) atau `pembayaran_ditolak` (ditolak, customer perlu unggah ulang).
6. Admin melengkapi data operasional & mengupdate progres → `dalam_pengiriman` → `diterima` → `selesai` (atau `dibatalkan` di titik mana pun sebelum selesai).

## Role & Akun Default

Tiga role: `admin`, `staff`, `customer`. Akun awal dibuat lewat `DatabaseSeeder`:

| Role | Email | Password |
|---|---|---|
| Admin | admin@janurtangguhabadi.com | password |
| Staff | staff@janurtangguhabadi.com | password |
| Customer | customer@example.com | password |

> Ganti password akun-akun ini sebelum digunakan di lingkungan produksi.

## Tech Stack

- **Backend:** Laravel (PHP)
- **Autentikasi:** `laravel/ui` (Login, Register, Forgot/Reset Password, Email Verification)
- **View:** Blade + Bootstrap 5, Bootstrap Icons
- **Build asset:** Vite (`resources/sass/app.scss`, `resources/js/app.js`)
- **PDF invoice:** [Dompdf](https://github.com/dompdf/dompdf)
- **Database:** MySQL (via Eloquent ORM)
- **API token (opsional):** Laravel Sanctum

## Instalasi

```bash
# 1. Clone & masuk folder proyek
cd project-kp-janur

# 2. Install dependency PHP & JS
composer install
npm install

# 3. Salin file environment lalu sesuaikan koneksi database
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi + seeder (akun default & data rute contoh)
php artisan migrate --seed

# 5. Buat symlink storage (wajib, untuk file bukti pembayaran & logo perusahaan)
php artisan storage:link

# 6. Build asset frontend
npm run build
# atau untuk mode development:
npm run dev

# 7. Jalankan server
php artisan serve
```

Pastikan `.env` sudah berisi konfigurasi database yang benar (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) sebelum menjalankan migrasi.

## Struktur Database

Tabel inti yang dipakai alur bisnis (dibuat lewat `2026_09_04_060000_create_booking_workflow_tables.php`):

| Tabel | Keterangan |
|---|---|
| `users` | Akun admin/staff/customer |
| `rute_harga` | Master rute & harga dasar per kg |
| `bookings` | Data utama booking |
| `booking_barang` | Rincian barang per booking |
| `invoice` | Invoice yang diterbitkan per booking |
| `bukti_pembayaran` | Bukti transfer yang diunggah customer |
| `booking_container` | Detail container & vessel |
| `surat_jalan` | Detail surat jalan pengiriman |
| `status_bookings` | Log riwayat perubahan status |
| `company_profiles` | Profil perusahaan (ditampilkan di landing page & invoice) |

## Rute Utama

**Publik**
- `GET /` — landing page
- `GET /tentang` — tentang perusahaan
- `GET /booking/cek`, `POST /booking/cek` — cek status booking via kode

**Customer (perlu login)**
- `GET/POST /booking` — form & submit booking baru
- `GET /booking/riwayat` — daftar booking milik sendiri
- `POST /booking/{booking}/konfirmasi-penawaran` — setuju/tolak penawaran
- `POST /invoice/{invoice}/bukti-pembayaran` — unggah bukti bayar
- `GET /invoice/{invoice}/pdf` — unduh invoice PDF

**Admin/Staff** (prefix `/admin`, middleware `role:admin,staff`)
- `GET /admin/dashboard` — daftar semua booking
- `GET/POST /admin/rute` — kelola rute & harga dasar
- `GET /admin/booking/{booking}` — detail & kelola booking
- `POST /admin/booking/{booking}/penawaran` — kirim penawaran harga
- `POST /admin/booking/{booking}/invoice` — terbitkan invoice manual
- `POST /admin/booking/{booking}/operasional` — simpan data container & surat jalan
- `POST /admin/booking/{booking}/progress` — update progres pengiriman
- `POST /admin/bukti-pembayaran/{bukti}/verifikasi` — verifikasi/tolak bukti bayar

## Struktur Folder Penting

```
app/
  Http/Controllers/
    AdminBookingController.php   # semua aksi sisi admin/staff
    BookingController.php        # semua aksi sisi customer
    PageController.php            # halaman publik
  Http/Middleware/EnsureRole.php  # pembatas akses berbasis role
  Models/                         # model dan relasi data aplikasi
resources/views/
  admin/                          # dashboard, kelola rute, detail booking admin
  auth/                           # tampilan autentikasi
  pages/                          # halaman publik dan customer
  pdf/invoice.blade.php           # template invoice untuk Dompdf
  layouts/app.blade.php           # layout utama
database/
  migrations/
  seeders/DatabaseSeeder.php      # akun demo, profil dan rute contoh
routes/web.php                    # rute aplikasi
```

## Catatan Pengembangan

Beberapa hal yang masih bisa disederhanakan/dibenahi ke depan:

- Kolom `status_harga` pada tabel `bookings` sudah tidak lagi jadi sumber kebenaran utama sejak status booking mulai memakai `status_booking` yang lebih lengkap — berpotensi disatukan.
- Belum ada notifikasi otomatis (email/WA) ke customer saat status berubah (penawaran keluar, pembayaran diverifikasi, progres update) — saat ini customer harus mengecek manual.
- Ada dua migration untuk tabel password reset (`password_resets` dan `password_reset_tokens`) — hanya satu yang aktif dipakai sesuai konfigurasi `auth.php`, migration yang lain sisa versi lama Laravel.
- Endpoint `admin/booking/{booking}/invoice` (invoice manual) belum punya form pemicu di UI — saat ini invoice selalu dibuat otomatis saat customer menyetujui penawaran.

---

&copy; PT Janur Tangguh Abadi
