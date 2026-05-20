<?php
require 'koneksi.php';
require 'auth.php';
require_role('admin');

$today = date('Y-m-d');
$totalKaryawan = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM karyawan"))['total'];
$totalDivisi = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM divisi"))['total'];
$hadir = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir = 'Hadir'"))['total'];
$totalGaji = (float) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(gaji_bersih), 0) AS total FROM perhitungan"))['total'];

$aktivitas = mysqli_query(
  $koneksi,
  "SELECT absensi.*, karyawan.nama, divisi.nama_divisi
   FROM absensi
   JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   WHERE absensi.tanggal_kehadiran = '$today'
   ORDER BY absensi.id_absensi DESC
   LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="role-dashboard role-admin">
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
        <p class="eyebrow"><span></span>Panel Admin</p>
        <h1>Kontrol Utama WeebeMart</h1>
        <p>Halo, <?= e($_SESSION['nama']); ?>. Admin dapat membuka semua modul dan memantau ringkasan sistem hari ini.</p>
      </div>
      <span class="role-pill">Admin</span>
    </section>

    <section class="summary-grid">
      <article class="summary-card"><span class="blue-text"><?= e($totalKaryawan); ?></span><p>Total Karyawan</p></article>
      <article class="summary-card"><span class="green-text"><?= e($hadir); ?></span><p>Hadir Hari Ini</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($totalDivisi); ?></span><p>Total Divisi</p></article>
      <article class="summary-card"><span class="red-text"><?= e(rupiah($totalGaji)); ?></span><p>Total Gaji Bersih</p></article>
    </section>

    <section class="role-action-grid">
      <a class="role-action" href="karyawan.php"><strong>Data Karyawan</strong><span>Tambah karyawan dan divisi</span></a>
      <a class="role-action" href="absensi.php"><strong>Absensi</strong><span>Input dan lihat kehadiran</span></a>
      <a class="role-action" href="gaji.php"><strong>Penggajian</strong><span>Hitung serta cek gaji</span></a>
      <a class="role-action" href="laporan.php"><strong>Laporan</strong><span>Buka ringkasan laporan</span></a>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Aktivitas Absensi Hari Ini</h2>
        <a class="small-button" href="absensi.php">Input Absensi</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nama</th><th>Divisi</th><th>Tanggal</th><th>Status</th></tr></thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($aktivitas)) : ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['nama_divisi'] ?? '-'); ?></td>
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
