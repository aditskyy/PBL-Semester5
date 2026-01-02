-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 02 Jan 2026 pada 12.48
-- Versi server: 8.0.30
-- Versi PHP: 8.3.13

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
-- Struktur dari tabel `antrian`
--

CREATE TABLE `antrian` (
  `id_antrian` int NOT NULL,
  `kode_jenis` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_loket` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nomor` int DEFAULT NULL,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Menunggu','Dipanggil','Selesai') COLLATE utf8mb4_general_ci DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `token` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `antrian`
--

INSERT INTO `antrian` (`id_antrian`, `kode_jenis`, `kode_loket`, `nomor`, `tanggal`, `status`, `created_at`, `updated_at`, `deleted_at`, `token`) VALUES
(58, 'A', 'A-01', 1, '2025-12-30 16:00:00', 'Selesai', '2025-12-31 05:25:25', '2026-01-02 19:32:14', NULL, '3cae218a'),
(59, 'A', 'A-01', 2, '2025-12-30 16:00:00', 'Selesai', '2025-12-31 05:25:53', '2026-01-02 19:32:15', NULL, '7180861c'),
(60, 'A', 'A-01', 3, '2025-12-30 16:00:00', 'Selesai', '2025-12-31 05:25:55', '2026-01-02 19:32:16', NULL, '0f00c72e'),
(61, 'B', 'B-01', 1, '2025-12-30 16:00:00', 'Menunggu', '2025-12-31 05:25:58', '2026-01-02 19:29:19', NULL, 'e2a5f355'),
(62, 'B', 'B-01', 2, '2025-12-30 16:00:00', 'Menunggu', '2025-12-31 05:25:59', '2026-01-02 19:29:19', NULL, 'b4839536'),
(63, 'B', 'B-01', 3, '2025-12-30 16:00:00', 'Menunggu', '2025-12-31 05:26:03', '2026-01-02 19:29:19', NULL, '736bc9e8'),
(64, 'C', 'C-02', 1, '2025-12-30 16:00:00', 'Menunggu', '2025-12-31 05:26:04', '2026-01-02 14:13:00', NULL, '43e06f85'),
(65, 'C', 'C-02', 2, '2025-12-30 16:00:00', 'Dipanggil', '2025-12-31 05:26:07', '2026-01-02 14:06:57', NULL, 'c17c2437'),
(66, 'C', 'C-01', 3, '2025-12-30 16:00:00', 'Menunggu', '2025-12-31 05:26:09', '2026-01-02 13:03:39', NULL, '7542f07e'),
(67, 'A', 'A-01', 1, '2025-12-31 16:00:00', 'Selesai', '2026-01-01 15:44:13', '2026-01-02 19:32:18', NULL, 'c8641eb3'),
(68, 'A', 'A-01', 1, '2026-01-01 16:00:00', 'Selesai', '2026-01-02 06:46:52', '2026-01-02 19:32:19', NULL, '453d52aa'),
(69, 'A', 'A-01', 2, '2026-01-01 16:00:00', 'Selesai', '2026-01-02 06:47:03', '2026-01-02 19:32:20', NULL, '719f1763'),
(70, 'B', 'B-01', 1, '2026-01-01 16:00:00', 'Menunggu', '2026-01-02 06:47:06', NULL, NULL, 'c1a69f94'),
(75, 'A', 'A-01', 3, '2026-01-01 16:00:00', 'Selesai', '2026-01-02 10:24:27', '2026-01-02 19:32:27', NULL, 'f9c9b4f4'),
(81, 'C', 'C-03', 1, '2026-01-02 12:21:29', 'Menunggu', '2026-01-02 12:21:29', NULL, NULL, '9295f6ea');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_loket`
--

CREATE TABLE `jenis_loket` (
  `kode_jenis` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_jenis` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_loket`
--

INSERT INTO `jenis_loket` (`kode_jenis`, `nama_jenis`, `keterangan`) VALUES
('A', 'Teller', 'Antrian transaksi utama'),
('B', 'Customer Service', 'Antrian layanan nasabah'),
('C', 'Kredit', 'Antrian layanan kredit');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_antrian`
--

CREATE TABLE `log_antrian` (
  `id_log` int NOT NULL,
  `id_antrian` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `aksi` enum('LOGIN','LOGOUT','AMBIL','PANGGIL','SELESAI','RECALL') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_antrian`
--

INSERT INTO `log_antrian` (`id_log`, `id_antrian`, `user_id`, `aksi`, `waktu`) VALUES
(170, 58, 8, 'SELESAI', '2026-01-02 11:32:14'),
(171, 59, 8, 'PANGGIL', '2026-01-02 11:32:14'),
(172, 59, 8, 'SELESAI', '2026-01-02 11:32:15'),
(173, 60, 8, 'PANGGIL', '2026-01-02 11:32:15'),
(174, 60, 8, 'SELESAI', '2026-01-02 11:32:16'),
(175, 67, 8, 'PANGGIL', '2026-01-02 11:32:16'),
(176, 67, 8, 'SELESAI', '2026-01-02 11:32:18'),
(177, 68, 8, 'PANGGIL', '2026-01-02 11:32:18'),
(178, 68, 8, 'SELESAI', '2026-01-02 11:32:19'),
(179, 69, 8, 'PANGGIL', '2026-01-02 11:32:19'),
(180, 69, 8, 'SELESAI', '2026-01-02 11:32:20'),
(181, 75, 8, 'PANGGIL', '2026-01-02 11:32:20'),
(182, 75, 8, 'SELESAI', '2026-01-02 11:32:21'),
(183, 75, 8, 'RECALL', '2026-01-02 11:32:24'),
(184, 75, 8, 'SELESAI', '2026-01-02 11:32:27'),
(185, NULL, 8, 'LOGOUT', '2026-01-02 11:32:37'),
(186, NULL, 8, 'LOGIN', '2026-01-02 11:32:43'),
(187, NULL, 8, 'LOGOUT', '2026-01-02 11:32:44'),
(188, NULL, 7, 'LOGIN', '2026-01-02 11:34:34'),
(192, 81, 7, 'AMBIL', '2026-01-02 12:21:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `loket`
--

CREATE TABLE `loket` (
  `kode_loket` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_loket` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icon` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'confirmation_number',
  `kode_jenis` char(1) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `warna` varchar(7) COLLATE utf8mb4_general_ci DEFAULT '#1E88E5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `loket`
--

INSERT INTO `loket` (`kode_loket`, `nama_loket`, `icon`, `kode_jenis`, `warna`) VALUES
('A-01', 'Teller-01', 'account_balance', 'A', '#1E88E5'),
('A-02', 'Teller-02', 'account_balance', 'A', '#1E88E5'),
('A-03', 'Teller-03', 'account_balance', 'A', '#1E88E5'),
('A-04', 'Teller-04', 'account_balance', 'A', '#1E88E5'),
('A-05', 'Teller-05', 'account_balance', 'A', '#1E88E5'),
('A-06', 'Teller-06', 'account_balance', 'A', '#1E88E5'),
('B-01', 'Customer Service-01', 'people', 'B', '#EF6C00'),
('B-02', 'Customer Service-02', 'people', 'B', '#EF6C00'),
('B-03', 'Customer Service-03', 'people', 'B', '#EF6C00'),
('C-01', 'Kredit-01', 'credit_card', 'C', '#2E7D32'),
('C-02', 'Kredit-02', 'credit_card', 'C', '#2E7D32'),
('C-03', 'Kredit-03', 'credit_card', 'C', '#bb00e0');

-- --------------------------------------------------------

--
-- Struktur dari tabel `profile`
--

CREATE TABLE `profile` (
  `id` int NOT NULL,
  `nama_instansi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color_palette` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profile`
--

INSERT INTO `profile` (`id`, `nama_instansi`, `alamat`, `telp`, `color_palette`) VALUES
(1, 'PUJASERA', 'Politeknik Negeri Bali', '0361 12345', '#0a2e6d');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','operator','guest') COLLATE utf8mb4_general_ci DEFAULT 'guest'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(7, 'admin', '$2y$10$k8gKMkSyF46VRi3RX7paV.SR.k0MGLTdpxwHdgypW2MCd6szkPA.G', 'admin'),
(8, 'operator', '$2y$10$xe1dUOeG7bEtWtXoCKvbsegxZbiKVsQrTFOT5ifQGXpB9wkUh7K/u', 'operator'),
(9, 'operator3', '$2y$10$XO6/Wf5Z1w1ozM.VOU10xemmjftXgY1zekGclZZBqkwf4c5N1dTiC', 'operator');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id_antrian`),
  ADD KEY `kode_loket` (`kode_loket`);

--
-- Indeks untuk tabel `jenis_loket`
--
ALTER TABLE `jenis_loket`
  ADD PRIMARY KEY (`kode_jenis`);

--
-- Indeks untuk tabel `log_antrian`
--
ALTER TABLE `log_antrian`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_antrian` (`id_antrian`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `loket`
--
ALTER TABLE `loket`
  ADD PRIMARY KEY (`kode_loket`),
  ADD KEY `kode_jenis` (`kode_jenis`);

--
-- Indeks untuk tabel `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id_antrian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT untuk tabel `log_antrian`
--
ALTER TABLE `log_antrian`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT untuk tabel `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`kode_loket`) REFERENCES `loket` (`kode_loket`);

--
-- Ketidakleluasaan untuk tabel `log_antrian`
--
ALTER TABLE `log_antrian`
  ADD CONSTRAINT `log_antrian_ibfk_1` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`),
  ADD CONSTRAINT `log_antrian_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `loket`
--
ALTER TABLE `loket`
  ADD CONSTRAINT `loket_ibfk_1` FOREIGN KEY (`kode_jenis`) REFERENCES `jenis_loket` (`kode_jenis`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
