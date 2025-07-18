-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 04:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resto_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` text NOT NULL,
  `type` varchar(20) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `type`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Login ke sistem', 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-05-31 05:43:28'),
(2, 2, 'Melakukan transaksi #1', 'transaction', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-05-31 05:43:28'),
(3, 2, 'Melakukan transaksi #2', 'transaction', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-05-31 05:43:28'),
(4, 1, 'Mengubah pengaturan aplikasi', 'settings', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-05-31 05:43:28'),
(5, 2, 'Logout dari sistem', 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-05-31 05:43:28');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `produk_id`, `qty`, `harga`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 2, 25000.00, 50000.00, '2025-05-31 05:43:28'),
(2, 1, 9, 2, 5000.00, 10000.00, '2025-05-31 05:43:28'),
(3, 1, 17, 1, 15000.00, 15000.00, '2025-05-31 05:43:28'),
(4, 2, 3, 2, 30000.00, 60000.00, '2025-05-31 05:43:28'),
(5, 2, 10, 2, 7000.00, 14000.00, '2025-05-31 05:43:28'),
(6, 2, 18, 2, 10000.00, 20000.00, '2025-05-31 05:43:28'),
(7, 2, 11, 2, 12000.00, 24000.00, '2025-05-31 05:43:28'),
(8, 3, 2, 2, 28000.00, 56000.00, '2025-05-31 05:43:28'),
(9, 3, 12, 1, 8000.00, 8000.00, '2025-05-31 05:43:28'),
(10, 3, 19, 1, 8000.00, 8000.00, '2025-05-31 05:43:28'),
(11, 3, 20, 1, 8000.00, 8000.00, '2025-05-31 05:43:28'),
(12, 4, 4, 3, 35000.00, 105000.00, '2025-05-31 05:43:28'),
(13, 4, 13, 3, 10000.00, 30000.00, '2025-05-31 05:43:28'),
(14, 4, 21, 2, 12000.00, 24000.00, '2025-05-31 05:43:28'),
(15, 5, 5, 3, 25000.00, 75000.00, '2025-05-31 05:43:28'),
(16, 5, 14, 1, 8000.00, 8000.00, '2025-05-31 05:43:28'),
(17, 5, 22, 1, 10000.00, 10000.00, '2025-05-31 05:43:28'),
(18, 6, 2, 2, 0.00, 56000.00, '2025-05-31 05:46:56'),
(19, 7, 8, 1, 0.00, 35000.00, '2025-05-31 06:09:08'),
(20, 8, 3, 2, 0.00, 60000.00, '2025-06-02 02:11:43'),
(21, 9, 4, 1, 0.00, 35000.00, '2025-06-02 02:13:08'),
(22, 10, 8, 2, 0.00, 70000.00, '2025-06-02 02:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_menu`
--

CREATE TABLE `kategori_menu` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori_menu`
--

INSERT INTO `kategori_menu` (`id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 'Makanan Utama', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(2, 'Minuman', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(3, 'Snack', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(4, 'Tambahan', '2025-05-31 05:43:27', '2025-05-31 05:43:27');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_resto` varchar(100) NOT NULL DEFAULT 'Nama Restoran',
  `logo` varchar(255) DEFAULT NULL,
  `footer_struk` text DEFAULT NULL,
  `pajak` decimal(5,2) DEFAULT 0.00,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_resto`, `logo`, `footer_struk`, `pajak`, `alamat`, `telepon`, `email`, `created_at`, `updated_at`) VALUES
(1, 'warung bahagia', 'logo_683d15dc08e4c.php', 'Terima kasih atas kunjungan Anda', 0.00, 'Bsd RAYA', '085218543752', '3504moz@anjay.id', '2025-05-31 05:43:27', '2025-06-02 03:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','habis') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kategori_id`, `nama`, `harga`, `stok`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nasi Goreng Special', 25000.00, 50, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(2, 1, 'Mie Goreng Seafood', 28000.00, 43, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:46:56'),
(3, 1, 'Ayam Bakar', 30000.00, 28, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-06-02 02:11:43'),
(4, 1, 'Ikan Bakar', 35000.00, 24, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-06-02 02:13:09'),
(5, 1, 'Sate Ayam (10 tusuk)', 25000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(6, 1, 'Nasi Uduk', 15000.00, 35, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(7, 1, 'Nasi Pecel', 20000.00, 30, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(8, 1, 'Bebek Goreng', 35000.00, 17, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-06-02 02:32:18'),
(9, 2, 'Es Teh Manis', 5000.00, 100, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(10, 2, 'Es Jeruk', 7000.00, 80, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(11, 2, 'Es Campur', 12000.00, 50, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(12, 2, 'Kopi Hitam', 8000.00, 60, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(13, 2, 'Teh Tarik', 10000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(14, 2, 'Es Cendol', 8000.00, 45, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(15, 2, 'Es Milo', 10000.00, 35, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(16, 2, 'Es Susu', 8000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(17, 3, 'Kentang Goreng', 15000.00, 30, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(18, 3, 'Pisang Goreng', 10000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(19, 3, 'Tempe Goreng', 8000.00, 50, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(20, 3, 'Tahu Goreng', 8000.00, 50, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(21, 3, 'Bakwan Jagung', 12000.00, 35, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(22, 3, 'Singkong Goreng', 10000.00, 30, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(23, 3, 'Perkedel', 8000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(24, 3, 'Risoles', 10000.00, 35, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(25, 4, 'Kerupuk', 3000.00, 100, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(26, 4, 'Sambal', 2000.00, 100, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(27, 4, 'Acar', 2000.00, 100, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(28, 4, 'Telur Asin', 5000.00, 50, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(29, 4, 'Tempe Orek', 8000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(30, 4, 'Tahu Orek', 8000.00, 40, NULL, 'tersedia', '2025-05-31 05:43:27', '2025-05-31 05:43:27');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `bayar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kasir_id` int(11) NOT NULL,
  `uang_dibayar` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `tanggal`, `total`, `bayar`, `kasir_id`, `uang_dibayar`, `created_at`) VALUES
(1, '2024-03-20 10:30:00', 75000.00, 100000.00, 2, 100000.00, '2025-05-31 05:43:27'),
(2, '2024-03-20 11:15:00', 120000.00, 150000.00, 2, 150000.00, '2025-05-31 05:43:27'),
(3, '2024-03-20 12:00:00', 85000.00, 100000.00, 2, 100000.00, '2025-05-31 05:43:27'),
(4, '2024-03-20 13:45:00', 150000.00, 200000.00, 2, 200000.00, '2025-05-31 05:43:27'),
(5, '2024-03-20 14:30:00', 95000.00, 100000.00, 2, 100000.00, '2025-05-31 05:43:27'),
(6, '2025-05-31 12:46:56', 56000.00, 0.00, 2, 100000.00, '2025-05-31 05:46:56'),
(7, '2025-05-31 13:09:08', 35000.00, 0.00, 3, 100000.00, '2025-05-31 06:09:08'),
(8, '2025-06-02 09:11:43', 60000.00, 0.00, 3, 100000.00, '2025-06-02 02:11:43'),
(9, '2025-06-02 09:13:08', 35000.00, 0.00, 3, 50000.00, '2025-06-02 02:13:08'),
(10, '2025-06-02 09:32:18', 70000.00, 0.00, 3, 100000.00, '2025-06-02 02:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('kasir','pemilik') NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `status`, `jenis_kelamin`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$KLE/SjjEGc3lsYSMulsy3.9VTSefqeIXw5yS.zgpXDJf/yWYYk31S', 'pemilik', 'aktif', NULL, '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(2, 'Kasir', 'kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir', 'aktif', NULL, '2025-05-31 05:43:27', '2025-05-31 05:43:27'),
(3, 'Mei', 'mei', '$2y$10$1yH8sAYAYROH9EreOkF8AeObXnD2tYkGFKnYorSq09t1g0bv3RlyW', 'kasir', 'aktif', 'P', '2025-05-31 05:49:51', '2025-05-31 05:58:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `kategori_menu`
--
ALTER TABLE `kategori_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kasir_id` (`kasir_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `kategori_menu`
--
ALTER TABLE `kategori_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_menu` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
