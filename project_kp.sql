-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2026 at 02:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
USE `project_kp`;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_kp`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_customer` varchar(100) NOT NULL,
  `email_customer` varchar(100) DEFAULT NULL,
  `no_hp_customer` varchar(20) DEFAULT NULL,
  `rute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `harga_estimasi` decimal(12,2) DEFAULT NULL,
  `harga_final` decimal(12,2) DEFAULT NULL,
  `status_harga` enum('menunggu_penawaran','sudah_ditawarkan','dikonfirmasi_customer','ditolak_customer') NOT NULL DEFAULT 'menunggu_penawaran',
  `status_booking` varchar(50) NOT NULL DEFAULT 'menunggu_penawaran',
  `diberikan_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_konfirmasi` datetime DEFAULT NULL,
  `estimasi_waktu` varchar(50) DEFAULT NULL,
  `tanggal_booking` date DEFAULT NULL,
  `tanggal_pengiriman` date DEFAULT NULL,
  `waktu_muat` datetime DEFAULT NULL,
  `jumlah_container` int(10) DEFAULT 1,
  `asal` varchar(150) DEFAULT NULL,
  `tujuan` varchar(150) DEFAULT NULL,
  `total_harga` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `kode_booking`, `user_id`, `nama_customer`, `email_customer`, `no_hp_customer`, `rute_id`, `harga_estimasi`, `harga_final`, `status_harga`, `status_booking`, `diberikan_oleh`, `tanggal_konfirmasi`, `estimasi_waktu`, `tanggal_booking`, `tanggal_pengiriman`, `waktu_muat`, `jumlah_container`, `asal`, `tujuan`, `total_harga`) VALUES
(1, 'BKJ-001', NULL, 'PT. Bangun Konstruksi Jaya', NULL, NULL, NULL, NULL, NULL, 'menunggu_penawaran', 'menunggu_penawaran', NULL, NULL, NULL, '2026-06-02', '2026-06-05', '2026-06-02 00:00:00', 1, 'Surabaya', 'Sorong', 21500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `booking_barang`
--

CREATE TABLE `booking_barang` (
  `booking_barang_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_barang` varchar(100) DEFAULT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `qty` int(10) DEFAULT 1,
  `berat_kg` decimal(8,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_barang`
--

INSERT INTO `booking_barang` (`booking_barang_id`, `booking_id`, `kategori_barang`, `nama_barang`, `qty`, `berat_kg`, `keterangan`) VALUES
(1, 1, 'Material Konstruksi', 'Pipa', 1, 0.00, '1x20\', Sesuai SJ pabrik');

-- --------------------------------------------------------

--
-- Table structure for table `booking_container`
--

CREATE TABLE `booking_container` (
  `container_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `joa_number` varchar(50) DEFAULT NULL,
  `no_container` varchar(50) DEFAULT NULL,
  `shipping_line` varchar(100) DEFAULT NULL,
  `feeder_vessel` varchar(100) DEFAULT NULL,
  `connecting_vessel` varchar(100) DEFAULT NULL,
  `destination` varchar(150) DEFAULT NULL,
  `stuff_date` date DEFAULT NULL,
  `etd` date DEFAULT NULL,
  `eta` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_container`
--

INSERT INTO `booking_container` (`container_id`, `booking_id`, `joa_number`, `no_container`, `shipping_line`, `feeder_vessel`, `connecting_vessel`, `destination`, `stuff_date`, `etd`, `eta`) VALUES
(1, 1, '001/BKJ/VI/SUB-SRG/26', 'TAKU2299149', 'TANTO', 'TANTO SENANG V.183', NULL, 'SORONG', '2026-06-02', '2026-06-05', '2026-06-12');

-- --------------------------------------------------------

--
-- Table structure for table `bukti_pembayaran`
--

CREATE TABLE `bukti_pembayaran` (
  `bukti_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `file_bukti` varchar(255) NOT NULL,
  `tanggal_upload` datetime NOT NULL DEFAULT current_timestamp(),
  `status_konfirmasi` enum('menunggu','dikonfirmasi','ditolak') NOT NULL DEFAULT 'menunggu',
  `dikonfirmasi_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_konfirmasi` datetime DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_profiles`
--

CREATE TABLE `company_profiles` (
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `nama_perusahaan` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `sejarah` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_profiles`
--

INSERT INTO `company_profiles` (`profile_id`, `nama_perusahaan`, `logo`, `visi`, `misi`, `sejarah`, `alamat`, `email`, `telepon`) VALUES
(1, 'PT. Janur Tangguh Abadi', 'images/logo-janur-nobg.png', 'JTA menjadi perusahaan forwarding internasional yang memahami kebutuhan pelanggan dan memberikan solusi terbaik.', 'JTA berkomitmen memberikan layanan yang tepat waktu dan dapat diandalkan dengan harga yang wajar. JTA senantiasa berupaya memberikan layanan transportasi dan logistik yang paling efektif dan efisien kepada para pelanggannya.', 'PT Janur Tangguh Abadi (JTA) didirikan pada Desember 2019 sebagai perusahaan jasa pengurusan transportasi (freight forwarding) umum yang berlokasi di Surabaya, Jawa Timur, Indonesia. Perusahaan terus berkembang dalam menangani pengiriman internasional maupun domestik, baik untuk pengiriman keluar (outbound) maupun masuk (inbound). Dengan memahami bahwa Indonesia merupakan negara kepulauan, JTA menyediakan layanan transportasi ke berbagai pulau besar seperti Kalimantan, Sulawesi, Bali, dan lainnya, dengan Surabaya sebagai pelabuhan utama (hub). JTA didukung oleh staf profesional yang terlatih untuk mengoperasikan dan menangani pengiriman, baik untuk kegiatan impor, ekspor, pengurusan bea cukai (customs brokerage), transportasi darat, hingga pengiriman proyek kargo. JTA juga menjalin kemitraan profesional yang kuat dengan berbagai perusahaan kargo besar, penyedia jasa angkutan komersial, dan perusahaan pelayaran, serta memiliki jaringan kerja (networking) yang baik.', 'Jl. Ikan Sepat IV No. 26, Tanjung Perak, Surabaya 60177', 'info@janurtangguhabadi.com', '+62 31 9901 8632');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `no_invoice` varchar(50) NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_invoice` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ppn_persen` decimal(5,2) DEFAULT 0.00,
  `ppn_nominal` decimal(12,2) DEFAULT 0.00,
  `total_bayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `terms` varchar(20) DEFAULT 'CASH',
  `status_bayar` enum('belum_lunas','lunas','sebagian') DEFAULT 'belum_lunas',
  `tanggal_lunas` date DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`invoice_id`, `no_invoice`, `booking_id`, `tanggal_invoice`, `subtotal`, `ppn_persen`, `ppn_nominal`, `total_bayar`, `terms`, `status_bayar`, `tanggal_lunas`, `catatan`) VALUES
(1, '001/BKJ/VI/SUB-SRG/26', 1, '2026-06-08', 21500000.00, 1.10, 236500.00, 21736500.00, 'CASH', 'belum_lunas', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_09_04_050440_add_logo_to_company_profiles_table', 2),
(6, '2026_09_04_045000_create_company_profiles_table', 3),
(7, '2026_09_04_060000_create_booking_workflow_tables', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rute_harga`
--

CREATE TABLE `rute_harga` (
  `rute_id` bigint(20) UNSIGNED NOT NULL,
  `pelabuhan_asal` varchar(150) NOT NULL,
  `pelabuhan_tujuan` varchar(150) NOT NULL,
  `harga_dasar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rute_harga`
--

INSERT INTO `rute_harga` (`rute_id`, `pelabuhan_asal`, `pelabuhan_tujuan`, `harga_dasar`, `keterangan`) VALUES
(1, 'Surabaya', 'Sorong', 8500.00, NULL),
(2, 'Surabaya', 'Banjarmasin', 5000.00, NULL),
(3, 'Jakarta', 'Makassar', 7000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `status_bookings`
--

CREATE TABLE `status_bookings` (
  `status_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('diproses','selesai','dibatalkan') NOT NULL DEFAULT 'diproses',
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surat_jalan`
--

CREATE TABLE `surat_jalan` (
  `surat_jalan_id` bigint(20) UNSIGNED NOT NULL,
  `no_surat_jalan` varchar(50) NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `kendaraan` varchar(50) DEFAULT NULL,
  `nopol_kendaraan` varchar(20) DEFAULT NULL,
  `nama_sopir` varchar(100) DEFAULT NULL,
  `penerima_kepada` varchar(150) DEFAULT NULL,
  `lokasi_penerima` varchar(150) DEFAULT NULL,
  `nama_pengirim` varchar(100) DEFAULT NULL,
  `nama_penerima_ttd` varchar(100) DEFAULT NULL,
  `tanggal_diterima` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat_jalan`
--

INSERT INTO `surat_jalan` (`surat_jalan_id`, `no_surat_jalan`, `booking_id`, `tanggal`, `kendaraan`, `nopol_kendaraan`, `nama_sopir`, `penerima_kepada`, `lokasi_penerima`, `nama_pengirim`, `nama_penerima_ttd`, `tanggal_diterima`) VALUES
(1, '001/JTA/SUB-BDJ/2020', 1, '2026-06-02', 'Truk', 'L 8975 UUD', NULL, 'PT. Bangun Konstruksi Jaya, Surabaya', 'SORONG', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','staff','customer') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`) VALUES
(2, 'Gyro', 'gyro@gmail.com', '$2y$10$1O84KFMI7JnV1lDYtPxk6uPvVI8ds.bmqrERf8xiw.e9dkoqdZ6zu', NULL, NULL, 'customer'),
(3, 'Admin Janur', 'admin@janurtangguhabadi.com', '$2y$10$ahZ8w2IQbMZwZnXqWdL1C.NtfklmWFVjboIsmUHibokbW96NdFL3.', NULL, NULL, 'admin'),
(4, 'Staff Operasional', 'staff@janurtangguhabadi.com', '$2y$10$n8Ryy0g5V0k1npLoHlIweeDLrTUhOl07xss4AFAZDp56gCTeS6MRq', NULL, NULL, 'staff'),
(5, 'Customer Demo', 'customer@example.com', '$2y$10$jZY9KJ2yLQLbYeDYdbmK1.j1WUk8KKdsSHWghvy7srvBqrDH.z9du', NULL, NULL, 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `fk_bookings_user` (`user_id`),
  ADD KEY `fk_bookings_rute` (`rute_id`),
  ADD KEY `fk_bookings_staff` (`diberikan_oleh`);

--
-- Indexes for table `booking_barang`
--
ALTER TABLE `booking_barang`
  ADD PRIMARY KEY (`booking_barang_id`),
  ADD KEY `fk_bb_booking` (`booking_id`);

--
-- Indexes for table `booking_container`
--
ALTER TABLE `booking_container`
  ADD PRIMARY KEY (`container_id`),
  ADD KEY `fk_bc_booking` (`booking_id`);

--
-- Indexes for table `bukti_pembayaran`
--
ALTER TABLE `bukti_pembayaran`
  ADD PRIMARY KEY (`bukti_id`),
  ADD KEY `fk_bp_invoice` (`invoice_id`),
  ADD KEY `fk_bp_user` (`dikonfirmasi_oleh`);

--
-- Indexes for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD PRIMARY KEY (`profile_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `no_invoice` (`no_invoice`),
  ADD KEY `fk_invoice_booking` (`booking_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `rute_harga`
--
ALTER TABLE `rute_harga`
  ADD PRIMARY KEY (`rute_id`),
  ADD UNIQUE KEY `uq_rute` (`pelabuhan_asal`,`pelabuhan_tujuan`);

--
-- Indexes for table `status_bookings`
--
ALTER TABLE `status_bookings`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `fk_sb_booking` (`booking_id`),
  ADD KEY `fk_sb_user` (`updated_by`);

--
-- Indexes for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  ADD PRIMARY KEY (`surat_jalan_id`),
  ADD UNIQUE KEY `no_surat_jalan` (`no_surat_jalan`),
  ADD KEY `fk_sj_booking` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_barang`
--
ALTER TABLE `booking_barang`
  MODIFY `booking_barang_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_container`
--
ALTER TABLE `booking_container`
  MODIFY `container_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bukti_pembayaran`
--
ALTER TABLE `bukti_pembayaran`
  MODIFY `bukti_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_profiles`
--
ALTER TABLE `company_profiles`
  MODIFY `profile_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rute_harga`
--
ALTER TABLE `rute_harga`
  MODIFY `rute_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status_bookings`
--
ALTER TABLE `status_bookings`
  MODIFY `status_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  MODIFY `surat_jalan_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_rute` FOREIGN KEY (`rute_id`) REFERENCES `rute_harga` (`rute_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_staff` FOREIGN KEY (`diberikan_oleh`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_barang`
--
ALTER TABLE `booking_barang`
  ADD CONSTRAINT `fk_bb_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_container`
--
ALTER TABLE `booking_container`
  ADD CONSTRAINT `fk_bc_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `bukti_pembayaran`
--
ALTER TABLE `bukti_pembayaran`
  ADD CONSTRAINT `fk_bp_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bp_user` FOREIGN KEY (`dikonfirmasi_oleh`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `status_bookings`
--
ALTER TABLE `status_bookings`
  ADD CONSTRAINT `fk_sb_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sb_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  ADD CONSTRAINT `fk_sj_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
