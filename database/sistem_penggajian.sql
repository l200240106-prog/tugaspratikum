-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026 at 02:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_penggajian`
--

CREATE DATABASE IF NOT EXISTS `sistem_penggajian`;
USE `sistem_penggajian`;

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

DROP TABLE IF EXISTS `absensi`;
CREATE TABLE `absensi` (
  `id_absensi` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `tanggal_kehadiran` date DEFAULT NULL,
  `status_hadir` enum('Hadir','Izin','Sakit','Alpha') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id_absensi`, `id_karyawan`, `tanggal_kehadiran`, `status_hadir`) VALUES
(1, 1, '2026-05-01', 'Hadir'),
(2, 1, '2026-05-02', 'Hadir'),
(3, 1, '2026-05-03', 'Alpha'),
(4, 2, '2026-05-01', 'Hadir'),
(5, 2, '2026-05-02', 'Izin'),
(6, 3, '2026-05-01', 'Hadir'),
(7, 3, '2026-05-01', 'Hadir'),
(8, 4, '2026-05-01', 'Hadir'),
(9, 4, '2026-05-02', 'Hadir'),
(10, 5, '2026-05-01', 'Hadir'),
(11, 5, '2026-05-02', 'Izin');

-- --------------------------------------------------------

--
-- Table structure for table `divisi`
--

DROP TABLE IF EXISTS `divisi`;
CREATE TABLE `divisi` (
  `id_divisi` int(11) NOT NULL,
  `nama_divisi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `divisi`
--

INSERT INTO `divisi` (`id_divisi`, `nama_divisi`) VALUES
(1, 'Operasional'),
(2, 'Keuangan'),
(3, 'Pergudangan'),
(4, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
CREATE TABLE `karyawan` (
  `id_karyawan` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `jabatan` varchar(80) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `status_kerja` enum('Aktif','Kontrak','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `id_divisi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama`, `no_hp`, `jabatan`, `alamat`, `tanggal_masuk`, `status_kerja`, `id_divisi`) VALUES
(1, 'Wibi', '081234567890', 'Staff Operasional', 'Makassar', '2026-05-01', 'Aktif', 1),
(2, 'Tasya', '081111111111', 'Staff Keuangan', 'Makassar', '2026-05-01', 'Aktif', 2),
(3, 'Eka', '0812345665533', 'Admin Keuangan', 'Gowa', '2026-05-01', 'Kontrak', 2),
(4, 'Dinda', '082222222222', 'Staff Gudang', 'Maros', '2026-05-01', 'Aktif', 3),
(5, 'Restu', '08111133221', 'Staff Operasional', 'Makassar', '2026-05-01', 'Aktif', 1);

-- --------------------------------------------------------

--
-- Table structure for table `komponen_gaji`
--

DROP TABLE IF EXISTS `komponen_gaji`;
CREATE TABLE `komponen_gaji` (
  `id_komponen` int(11) NOT NULL,
  `id_divisi` int(11) DEFAULT NULL,
  `gaji_pokok` decimal(12,2) DEFAULT NULL,
  `tunjangan` decimal(12,2) DEFAULT NULL,
  `bonus` decimal(12,2) DEFAULT NULL,
  `uang_makan` decimal(12,2) DEFAULT NULL,
  `lembur` decimal(12,2) DEFAULT NULL,
  `potongan_pajak` decimal(12,2) DEFAULT NULL,
  `bpjs` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perhitungan`
--

DROP TABLE IF EXISTS `perhitungan`;
CREATE TABLE `perhitungan` (
  `id_perhitungan` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `total_pendapatan` decimal(12,2) DEFAULT NULL,
  `total_potongan` decimal(12,2) DEFAULT NULL,
  `gaji_bersih` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indexes for table `divisi`
--
ALTER TABLE `divisi`
  ADD PRIMARY KEY (`id_divisi`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD KEY `id_divisi` (`id_divisi`);

--
-- Indexes for table `komponen_gaji`
--
ALTER TABLE `komponen_gaji`
  ADD PRIMARY KEY (`id_komponen`),
  ADD KEY `id_divisi` (`id_divisi`);

--
-- Indexes for table `perhitungan`
--
ALTER TABLE `perhitungan`
  ADD PRIMARY KEY (`id_perhitungan`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `divisi`
--
ALTER TABLE `divisi`
  MODIFY `id_divisi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `komponen_gaji`
--
ALTER TABLE `komponen_gaji`
  MODIFY `id_komponen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perhitungan`
--
ALTER TABLE `perhitungan`
  MODIFY `id_perhitungan` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`);

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`id_divisi`) REFERENCES `divisi` (`id_divisi`);

--
-- Constraints for table `komponen_gaji`
--
ALTER TABLE `komponen_gaji`
  ADD CONSTRAINT `komponen_gaji_ibfk_1` FOREIGN KEY (`id_divisi`) REFERENCES `divisi` (`id_divisi`);

--
-- Constraints for table `perhitungan`
--
ALTER TABLE `perhitungan`
  ADD CONSTRAINT `perhitungan_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
