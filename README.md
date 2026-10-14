# PT Janur Tangguh Abadi

Sistem Booking Pengiriman Laut berbasis Laravel untuk mengelola proses booking, penawaran harga, invoice, pembayaran, verifikasi, hingga progres pengiriman container laut secara terstruktur.

Aplikasi ini dirancang untuk mendukung alur operasional antara customer, staff, dan admin dalam satu sistem yang terintegrasi.

## Ringkasan Proyek

Proyek ini merupakan aplikasi web yang digunakan untuk:

- Mengelola booking pengiriman laut dari customer
- Menyediakan penawaran harga oleh admin/staff
- Memproses konfirmasi customer terhadap penawaran
- Menerbitkan invoice otomatis
- Mengelola bukti pembayaran dan verifikasi pembayaran
- Menyimpan data operasional pengiriman seperti container, vessel, ETD/ETA, dan surat jalan
- Menampilkan status pengiriman dalam alur yang jelas

## Fitur Utama

### Customer
- Registrasi dan login akun
- Membuat booking pengiriman baru
- Memilih rute dan mengisi detail barang
- Melihat estimasi harga secara real-time
- Melacak status booking melalui kode booking
- Melihat riwayat booking miliknya
- Menyetujui atau menolak penawaran harga
- Mengunggah bukti pembayaran
- Mengunduh invoice dalam format PDF

### Staff / Admin
- Dashboard ringkasan semua booking
- Mengelola master data rute dan harga dasar
- Memberikan penawaran harga final
- Melihat dan menerbitkan invoice
- Verifikasi bukti pembayaran
- Mengelola data operasional pengiriman
- Memperbarui progres pengiriman
- Mengelola status booking sesuai tahapan operasional

## Teknologi yang Digunakan

- Backend: Laravel 10 (PHP 8.1)
- Frontend: Blade, Bootstrap 5, Vite
- CSS: Sass
- Autentikasi: Laravel UI
- PDF Invoice: Dompdf
- Database: MySQL
- API/Token: Laravel Sanctum

## Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan sistem Anda sudah memiliki:

- PHP 8.1+
- Composer
- Node.js dan npm
- MySQL
- Git

## Instalasi

### 1. Clone repository

```bash
git clone https://github.com/Kirigaya1145/project-kp-janur.git
cd project-kp-janur
```

### 2. Install dependency PHP dan JavaScript

```bash
composer install
npm install
```

### 3. Konfigurasikan environment

```bash
cp .env.example .env
php artisan key:generate
```

Lalu sesuaikan konfigurasi database pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

Seeder akan membuat akun default dan data rute contoh untuk pengujian.

### 5. Buat symlink storage

```bash
php artisan storage:link
```

### 6. Build asset frontend

```bash
npm run build
```

Untuk mode development:

```bash
npm run dev
```

### 7. Jalankan aplikasi

```bash
php artisan serve
```

Akses aplikasi di browser melalui:

```text
http://localhost:8000
```

## Akun Default

Akun demo yang dibuat melalui seeder:

| Role | Email | Password |
|---|---|---|
| Admin | admin@janurtangguhabadi.com | password |
| Staff | staff@janurtangguhabadi.com | password |
| Customer | customer@example.com | password |

> Sebaiknya ubah password default saat digunakan di lingkungan produksi.

## Alur Proses Booking

Alur utama aplikasi dapat digambarkan seperti berikut:

1. Customer mengajukan booking
2. Admin/staff memberikan penawaran harga
3. Customer menerima atau menolak penawaran
4. Customer mengunggah bukti pembayaran
5. Admin/staff memverifikasi pembayaran
6. Data operasional disiapkan
7. Progres pengiriman diperbarui hingga selesai

Status booking yang umum dipakai dalam sistem:

- Menunggu Penawaran
- Menunggu Konfirmasi
- Menunggu Pembayaran
- Verifikasi Pembayaran
- Proses Pengiriman
- Selesai

Beberapa status tambahan seperti penawaran ditolak atau dibatalkan juga dapat terjadi sesuai kebutuhan operasional.

## Struktur Folder Utama

```text
project-kp-janur/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── js/
│   ├── sass/
│   └── views/
├── routes/
├── storage/
├── tests/
├── .env.example
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
├── artisan
├── README.md
└── project_kp.sql
```

## Rute Utama

### Publik
- `GET /` — landing page
- `GET /tentang` — halaman tentang perusahaan
- `GET /booking/cek` — cek status booking
- `POST /booking/cek` — submit kode booking

### Customer
- `GET/POST /booking` — form pengajuan booking
- `GET /booking/riwayat` — riwayat booking customer
- `POST /booking/{booking}/konfirmasi-penawaran` — menerima/menolak penawaran
- `POST /invoice/{invoice}/bukti-pembayaran` — upload bukti pembayaran
- `GET /invoice/{invoice}/pdf` — unduh invoice PDF

### Admin / Staff
- `GET /admin/dashboard` — dashboard semua booking
- `GET/POST /admin/rute` — pengelolaan rute dan harga
- `GET /admin/booking/{booking}` — detail booking
- `POST /admin/booking/{booking}/penawaran` — kirim penawaran harga
- `POST /admin/booking/{booking}/operasional` — isi data operasional
- `POST /admin/booking/{booking}/progress` — update progres pengiriman
- `POST /admin/bukti-pembayaran/{bukti}/verifikasi` — verifikasi pembayaran

## Database Inti

Tabel utama yang digunakan oleh sistem:

- `users`
- `rute_harga`
- `bookings`
- `booking_barang`
- `invoice`
- `bukti_pembayaran`
- `booking_container`
- `surat_jalan`
- `status_bookings`
- `company_profiles`

## Catatan Pengembangan

Beberapa area yang masih dapat dikembangkan lebih lanjut:

- Integrasi notifikasi otomatis (email/WhatsApp) saat status berubah
- Penyatuan logika status yang masih memiliki beberapa duplikasi field
- Peningkatan validasi data dan form input
- Optimasi UI/UX untuk operasional admin dan customer

## Lisensi

Proyek ini dibuat untuk kebutuhan internal/pendidikan dan dapat dikembangkan lebih lanjut sesuai kebutuhan organisasi.

---

&copy; PT Janur Tangguh Abadi
