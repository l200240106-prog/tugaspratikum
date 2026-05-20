<?php
require 'koneksi.php';
require 'auth.php';
require_role('pergudangan');

header('Location: absensi.php');
exit;

$today = date('Y-m-d');
$idDivisi = (int) ($_SESSION['id_divisi'] ?? 0);
$totalStaf = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM karyawan WHERE id_divisi = $idDivisi"))['total'];
$hadir = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan WHERE karyawan.id_divisi = $idDivisi AND tanggal_kehadiran = '$today' AND status_hadir = 'Hadir'"))['total'];
$izinSakit = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan WHERE karyawan.id_divisi = $idDivisi AND tanggal_kehadiran = '$today' AND status_hadir IN ('Izin', 'Sakit')"))['total'];
$alpha = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan WHERE karyawan.id_divisi = $idDivisi AND tanggal_kehadiran = '$today' AND status_hadir = 'Alpha'"))['total'];

$staf = mysqli_query(
  $koneksi,
  "SELECT karyawan.*, divisi.nama_divisi
   FROM karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   WHERE karyawan.id_divisi = $idDivisi
   ORDER BY karyawan.nama
   LIMIT 8"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pergudangan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="role-dashboard role-warehouse">
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?php nav_items('dashboard.php'); ?>
      </ul>
      <a class="nav-button" href="logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="role-hero">
      <div>
        <p class="eyebrow"><span></span>Panel Pergudangan</p>
        <h1>Pantau Tim Gudang</h1>
        <p>Halo, <?= e($_SESSION['nama']); ?>. Halaman ini menampilkan staf pergudangan dan status kehadiran harian.</p>
      </div>
      <span class="role-pill">Pergudangan</span>
    </section>

    <section class="summary-grid">
      <article class="summary-card"><span class="blue-text"><?= e($totalStaf); ?></span><p>Staf Gudang</p></article>
      <article class="summary-card"><span class="green-text"><?= e($hadir); ?></span><p>Hadir Hari Ini</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($izinSakit); ?></span><p>Izin / Sakit</p></article>
      <article class="summary-card"><span class="red-text"><?= e($alpha); ?></span><p>Alpha</p></article>
    </section>

    <section class="role-action-grid warehouse-actions">
      <a class="role-action" href="absensi.php"><strong>Absensi Gudang</strong><span>Catat kehadiran staf</span></a>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Staf Pergudangan</h2>
        <a class="small-button" href="absensi.php">Input Absensi</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nama</th><th>Jabatan</th><th>No HP</th><th>Status</th></tr></thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($staf)) : ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['jabatan']); ?></td>
                <td><?= e($row['no_hp']); ?></td>
                <td><?= e($row['status_kerja']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
