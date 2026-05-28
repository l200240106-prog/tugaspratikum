-- phpMyAdmin SQL Dump
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `divisi` (
  `id_divisi` int(11) NOT NULL AUTO_INCREMENT,
  `nama_divisi` varchar(100) NOT NULL,
  PRIMARY KEY (`id_divisi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `divisi` (`id_divisi`, `nama_divisi`) VALUES
(1, 'Operasional'),
(2, 'Keuangan'),
(3, 'Pergudangan'),
(4, 'Admin');

CREATE TABLE `karyawan` (
  `id_karyawan` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(15),
  `jabatan` varchar(80),
  `alamat` text,
  `tanggal_masuk` date,
  `status_kerja` enum('Aktif','Kontrak','Nonaktif') DEFAULT 'Aktif',
  `id_divisi` int(11),
  PRIMARY KEY (`id_karyawan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `karyawan`
(`id_karyawan`,`nama`,`no_hp`,`jabatan`,`alamat`,`tanggal_masuk`,`status_kerja`,`id_divisi`)
VALUES
(1,'Wibi','081234567890','Staff Operasional','Makassar','2026-05-01','Aktif',1),
(2,'Tasya','081111111111','Staff Keuangan','Makassar','2026-05-01','Aktif',2),
(3,'Eka','0812345665533','Admin Keuangan','Gowa','2026-05-01','Kontrak',2),
(4,'Dinda','082222222222','Staff Gudang','Maros','2026-05-01','Aktif',3),
(5,'Restu','08111133221','Staff Operasional','Makassar','2026-05-01','Aktif',1);

CREATE TABLE `absensi` (
  `id_absensi` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11),
  `tanggal_kehadiran` date,
  `status_hadir` enum('Hadir','Izin','Sakit','Alpha'),
  PRIMARY KEY (`id_absensi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `absensi`
(`id_absensi`,`id_karyawan`,`tanggal_kehadiran`,`status_hadir`)
VALUES
(1,1,'2026-05-01','Hadir'),
(2,1,'2026-05-02','Hadir'),
(3,1,'2026-05-03','Alpha'),
(4,2,'2026-05-01','Hadir');

CREATE TABLE `perhitungan` (
  `id_perhitungan` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11),
  `total_pendapatan` decimal(12,2),
  `total_potongan` decimal(12,2),
  `gaji_bersih` decimal(12,2),
  PRIMARY KEY (`id_perhitungan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `komponen_gaji` (
  `id_komponen` int(11) NOT NULL AUTO_INCREMENT,
  `id_divisi` int(11),
  `gaji_pokok` decimal(12,2),
  `tunjangan` decimal(12,2),
  `bonus` decimal(12,2),
  `uang_makan` decimal(12,2),
  `lembur` decimal(12,2),
  `potongan_pajak` decimal(12,2),
  `bpjs` decimal(12,2),
  PRIMARY KEY (`id_komponen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;