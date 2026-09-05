# PT Janur Tangguh Abadi — Sistem Booking Pengiriman Laut

Aplikasi web berbasis Laravel untuk mengelola alur booking pengiriman container laut: mulai dari pengajuan booking oleh customer, penawaran harga oleh staff/admin, penerbitan invoice, verifikasi pembayaran, hingga pelacakan progres pengiriman (surat jalan & data container/vessel).

## Daftar Isi

- [Fitur Utama](#fitur-utama)
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
    Auth/                        # scaffolding login/register/reset password
  Models/                        # Booking, Invoice, RuteHarga, dll.
resources/views/
  admin/                         # dashboard, kelola rute, detail booking admin
  pages/                         # landing page, form booking, cek/riwayat booking
  pdf/invoice.blade.php          # template invoice untuk Dompdf
  layouts/app.blade.php          # layout utama (navbar, footer, styling)
database/
  migrations/
  seeders/DatabaseSeeder.php     # akun default & data rute contoh
routes/web.php                   # seluruh rute aplikasi
```

## Catatan Pengembangan

Beberapa hal yang masih bisa disederhanakan/dibenahi ke depan:

- Kolom `status_harga` pada tabel `bookings` sudah tidak lagi jadi sumber kebenaran utama sejak status booking mulai memakai `status_booking` yang lebih lengkap — berpotensi disatukan.
- Belum ada notifikasi otomatis (email/WA) ke customer saat status berubah (penawaran keluar, pembayaran diverifikasi, progres update) — saat ini customer harus mengecek manual.
- Ada dua migration untuk tabel password reset (`password_resets` dan `password_reset_tokens`) — hanya satu yang aktif dipakai sesuai konfigurasi `auth.php`, migration yang lain sisa versi lama Laravel.
- Endpoint `admin/booking/{booking}/invoice` (invoice manual) belum punya form pemicu di UI — saat ini invoice selalu dibuat otomatis saat customer menyetujui penawaran.

---

&copy; PT Janur Tangguh Abadi
