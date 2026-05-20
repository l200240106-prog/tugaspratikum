<?php
require 'koneksi.php';
require 'auth.php';
require_role('operasional');

header('Location: absensi.php');
exit;

$today = date('Y-m-d');
$idDivisi = (int) ($_SESSION['id_divisi'] ?? 0);
$totalStaf = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM karyawan WHERE id_divisi = $idDivisi"))['total'];
$hadir = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan WHERE karyawan.id_divisi = $idDivisi AND tanggal_kehadiran = '$today' AND status_hadir = 'Hadir'"))['total'];
$izinSakit = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan WHERE karyawan.id_divisi = $idDivisi AND tanggal_kehadiran = '$today' AND status_hadir IN ('Izin', 'Sakit')"))['total'];
$alpha = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan WHERE karyawan.id_divisi = $idDivisi AND tanggal_kehadiran = '$today' AND status_hadir = 'Alpha'"))['total'];

$aktivitas = mysqli_query(
  $koneksi,
  "SELECT absensi.*, karyawan.nama
   FROM absensi
   JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
   WHERE karyawan.id_divisi = $idDivisi AND absensi.tanggal_kehadiran = '$today'
   ORDER BY absensi.id_absensi DESC
   LIMIT 8"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Operasional</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="role-dashboard role-operation">
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
        <p class="eyebrow"><span></span>Panel Operasional</p>
        <h1>Kehadiran Operasional</h1>
        <p>Halo, <?= e($_SESSION['nama']); ?>. Dashboard ini dipusatkan untuk absensi dan aktivitas staf operasional.</p>
      </div>
      <span class="role-pill">Operasional</span>
    </section>

    <section class="summary-grid">
      <article class="summary-card"><span class="blue-text"><?= e($totalStaf); ?></span><p>Staf Operasional</p></article>
      <article class="summary-card"><span class="green-text"><?= e($hadir); ?></span><p>Hadir Hari Ini</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($izinSakit); ?></span><p>Izin / Sakit</p></article>
      <article class="summary-card"><span class="red-text"><?= e($alpha); ?></span><p>Alpha</p></article>
    </section>

    <section class="role-action-grid operation-actions">
      <a class="role-action" href="absensi.php"><strong>Input Absensi</strong><span>Catat status kehadiran hari ini</span></a>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Absensi Operasional Hari Ini</h2>
        <a class="small-button" href="absensi.php">Input Absensi</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nama</th><th>Tanggal</th><th>Status</th></tr></thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($aktivitas)) : ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e(date('d M Y', strtotime($row['tanggal_kehadiran']))); ?></td>
                <td><?= e($row['status_hadir']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
