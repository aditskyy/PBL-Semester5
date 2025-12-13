-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 02, 2025 at 01:20 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `antrian1`
--

-- --------------------------------------------------------

--
-- Table structure for table `antrian`
--

CREATE TABLE `antrian` (
  `id_antrian` int NOT NULL,
  `kode_jenis` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_loket` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nomor` int DEFAULT NULL,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Menunggu','Dipanggil','Selesai') COLLATE utf8mb4_general_ci DEFAULT 'Menunggu',
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `antrian`
--

INSERT INTO `antrian` (`id_antrian`, `kode_jenis`, `kode_loket`, `nomor`, `tanggal`, `status`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(11, 'A', 'A-01', 1, '2025-10-24 00:00:00', 'Dipanggil', NULL, '2025-10-24 05:15:35', '2025-12-02 00:32:33', NULL),
(12, 'A', 'A-01', 2, '2025-10-24 00:00:00', 'Menunggu', NULL, '2025-10-24 05:15:35', '2025-11-13 08:16:15', NULL),
(13, 'A', 'A-01', 3, '2025-10-24 00:00:00', 'Menunggu', NULL, '2025-10-24 05:15:35', '2025-11-13 08:16:15', NULL),
(14, 'B', 'B-01', 1, '2025-10-24 00:00:00', 'Dipanggil', NULL, '2025-10-24 05:15:35', NULL, NULL),
(17, 'A', 'A-01', 4, '2025-11-07 20:45:46', 'Menunggu', NULL, '2025-11-08 04:45:46', '2025-11-13 08:16:15', NULL),
(18, 'A', 'A-01', 5, '2025-11-07 20:59:09', 'Menunggu', NULL, '2025-11-08 04:59:09', '2025-11-13 08:16:15', NULL),
(19, 'A', 'A-01', 6, '2025-11-07 20:59:51', 'Menunggu', NULL, '2025-11-08 04:59:51', '2025-11-13 08:16:15', NULL),
(20, 'B', 'B-01', 2, '2025-11-09 08:02:12', 'Menunggu', NULL, '2025-11-09 08:02:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jenis_loket`
--

CREATE TABLE `jenis_loket` (
  `kode_jenis` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `prefix_nomor` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_jenis` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_loket`
--

INSERT INTO `jenis_loket` (`kode_jenis`, `prefix_nomor`, `nama_jenis`, `keterangan`) VALUES
('A', 'A-01', 'Teller', 'Antrian transaksi utama'),
('B', NULL, 'Customer Service', 'Antrian layanan nasabah'),
('C', NULL, 'Kredit', 'Antrian layanan kredit');

-- --------------------------------------------------------

--
-- Table structure for table `log_antrian`
--

CREATE TABLE `log_antrian` (
  `id_log` int NOT NULL,
  `id_antrian` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `aksi` enum('TAMBAH','PANGGIL','SELESAI') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_antrian`
--

INSERT INTO `log_antrian` (`id_log`, `id_antrian`, `user_id`, `aksi`, `waktu`) VALUES
(2, 11, 1, '', '2025-11-06 23:54:24'),
(6, 11, 1, '', '2025-11-06 23:56:56'),
(8, 11, NULL, 'PANGGIL', '2025-11-07 00:40:36'),
(9, 18, NULL, '', '2025-11-07 20:59:09'),
(10, 19, NULL, '', '2025-11-07 20:59:51'),
(11, 11, NULL, 'PANGGIL', '2025-11-07 21:16:50'),
(12, 12, NULL, 'PANGGIL', '2025-11-07 21:17:29'),
(13, 11, 1, '', '2025-11-07 21:20:31'),
(14, 11, 1, 'SELESAI', '2025-11-07 21:20:58'),
(15, 13, NULL, '', '2025-11-12 20:29:19'),
(16, 13, NULL, '', '2025-11-12 20:29:32'),
(17, 17, NULL, '', '2025-11-12 20:31:15'),
(18, 18, NULL, '', '2025-11-12 20:31:22'),
(19, 19, NULL, '', '2025-11-12 20:31:31'),
(20, 19, NULL, '', '2025-11-12 20:31:35'),
(21, 11, NULL, '', '2025-11-12 23:01:06'),
(22, 12, NULL, '', '2025-11-12 23:03:37'),
(23, 13, NULL, '', '2025-11-12 23:03:45'),
(24, 19, NULL, '', '2025-11-12 23:03:47'),
(25, 19, NULL, '', '2025-11-12 23:03:59'),
(26, 11, NULL, '', '2025-11-13 00:15:55'),
(27, 12, NULL, '', '2025-11-13 00:15:57'),
(28, 13, NULL, '', '2025-11-13 00:15:59'),
(29, 17, NULL, '', '2025-11-13 00:16:01'),
(30, 18, NULL, '', '2025-11-13 00:16:03'),
(31, 19, NULL, '', '2025-11-13 00:16:06'),
(32, 19, NULL, '', '2025-11-13 00:16:09'),
(33, 11, NULL, '', '2025-12-01 16:32:33');

-- --------------------------------------------------------

--
-- Table structure for table `loket`
--

CREATE TABLE `loket` (
  `kode_loket` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_loket` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kode_jenis` char(1) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prefix_nomor` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lokasi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loket`
--

INSERT INTO `loket` (`kode_loket`, `nama_loket`, `kode_jenis`, `prefix_nomor`, `lokasi`) VALUES
('A-01', 'Teller-01', 'A', NULL, NULL),
('A-02', 'Teller-02', 'A', NULL, NULL),
('A-03', 'Teller-03', 'A', NULL, NULL),
('A-04', 'Teller-04', 'A', NULL, NULL),
('A-05', 'Teller-05', 'A', NULL, NULL),
('B-01', 'Customer Service-01', 'B', NULL, NULL),
('B-02', 'Customer Service-02', 'B', NULL, NULL),
('B-03', 'Customer Service-03', 'B', NULL, NULL),
('C-01', 'Kredit-01', 'C', NULL, NULL),
('C-02', 'Kredit-02', 'C', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int NOT NULL,
  `gambar_logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color_palette` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_instansi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `tipe_instansi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `gambar_logo`, `color_palette`, `nama_instansi`, `alamat`, `tipe_instansi`) VALUES
(1, 'logo_default.png', '#007AFF', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','operator','guest') COLLATE utf8mb4_general_ci DEFAULT 'guest',
  `kode_jenis` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `kode_jenis`) VALUES
(1, 'admin', 'admin123', 'admin', NULL),
(2, 'operator1', 'op123', 'operator', 'A'),
(3, 'guest1', 'guest', 'guest', NULL),
(4, 'operator2', 'op123', 'operator', 'B');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id_antrian`),
  ADD KEY `kode_loket` (`kode_loket`),
  ADD KEY `fk_antrian_user` (`user_id`);

--
-- Indexes for table `jenis_loket`
--
ALTER TABLE `jenis_loket`
  ADD PRIMARY KEY (`kode_jenis`);

--
-- Indexes for table `log_antrian`
--
ALTER TABLE `log_antrian`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_antrian` (`id_antrian`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `loket`
--
ALTER TABLE `loket`
  ADD PRIMARY KEY (`kode_loket`),
  ADD KEY `kode_jenis` (`kode_jenis`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id_antrian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `log_antrian`
--
ALTER TABLE `log_antrian`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`kode_loket`) REFERENCES `loket` (`kode_loket`),
  ADD CONSTRAINT `fk_antrian_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `log_antrian`
--
ALTER TABLE `log_antrian`
  ADD CONSTRAINT `log_antrian_ibfk_1` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`),
  ADD CONSTRAINT `log_antrian_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loket`
--
ALTER TABLE `loket`
  ADD CONSTRAINT `loket_ibfk_1` FOREIGN KEY (`kode_jenis`) REFERENCES `jenis_loket` (`kode_jenis`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
