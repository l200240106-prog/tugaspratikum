<?php
require '../../config/koneksi.php';
require '../auth/auth.php';

$today = date('Y-m-d');

$totalKaryawan = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM karyawan"))['total'];
$totalDivisi = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM divisi"))['total'];
$totalGaji = (float) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(gaji_bersih), 0) AS total FROM perhitungan"))['total'];
$hadir = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir = 'Hadir'"))['total'];
$izinSakit = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir IN ('Izin', 'Sakit')"))['total'];
$alpha = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir = 'Alpha'"))['total'];

$aktivitas = mysqli_query(
  $koneksi,
  "SELECT absensi.*, karyawan.nama, divisi.nama_divisi
   FROM absensi
   JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   WHERE absensi.tanggal_kehadiran = '$today'
   ORDER BY absensi.id_absensi DESC
   LIMIT 4"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard WeebeMart</title>
  <link rel="stylesheet" href="/tugaspratikum/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/tugaspratikum/pages/dashboard/dashboard.php" aria-label="WeebeMart">
        <span class="brand-icon">WM</span>
        <span>Weebe<span>Mart</span></span>
      </a>

      <ul class="nav-links">
        <li><a class="active" href="/tugaspratikum/pages/dashboard/dashboard.php">Beranda</a></li>
        <li><a href="/tugaspratikum/pages/karyawan/karyawan.php">Karyawan</a></li>
        <li><a href="/tugaspratikum/pages/absensi/absensi.php">Absensi</a></li>
        <li><a href="/tugaspratikum/pages/penggajian/komponen-gaji.php">Komponen</a></li>
        <li><a href="/tugaspratikum/pages/penggajian/gaji.php">Gaji</a></li>
        <li><a href="/tugaspratikum/pages/laporan/laporan.php">Laporan</a></li>
      </ul>

      <a class="nav-button" href="/tugaspratikum/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Panel WeebeMart</p>
      <h1>Dashboard WeebeMart</h1>
      <p>Halo, <?= e($_SESSION['nama']); ?>. Ringkasan ini dibaca langsung dari database untuk tanggal <?= e(date('d M Y')); ?>.</p>
    </section>

    <section class="summary-grid">
      <article class="summary-card"><span class="blue-text"><?= e($totalKaryawan); ?></span><p>Total Karyawan</p></article>
      <article class="summary-card"><span class="green-text"><?= e($hadir); ?></span><p>Hadir Hari Ini</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($totalDivisi); ?></span><p>Total Divisi</p></article>
      <article class="summary-card"><span class="red-text"><?= e(rupiah($totalGaji)); ?></span><p>Total Gaji Bersih</p></article>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Aktivitas Terbaru</h2>
        <a class="small-button" href="../absensi/absensi.php">Input Absensi</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nama</th><th>Divisi</th><th>Tanggal</th><th>Status</th></tr>
          </thead>
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
