<?php
require 'koneksi.php';
require 'auth.php';
require_role('keuangan');

$totalGaji = (float) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(gaji_bersih), 0) AS total FROM perhitungan"))['total'];
$totalPendapatan = (float) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(total_pendapatan), 0) AS total FROM perhitungan"))['total'];
$totalPotongan = (float) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(total_potongan), 0) AS total FROM perhitungan"))['total'];
$jumlahSlip = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM perhitungan"))['total'];

$gaji = mysqli_query(
  $koneksi,
  "SELECT perhitungan.*, karyawan.nama, divisi.nama_divisi
   FROM perhitungan
   JOIN karyawan ON karyawan.id_karyawan = perhitungan.id_karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   ORDER BY perhitungan.id_perhitungan DESC
   LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Keuangan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="role-dashboard role-finance">
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
        <p class="eyebrow"><span></span>Panel Keuangan</p>
        <h1>Rekap Gaji dan Komponen</h1>
        <p>Halo, <?= e($_SESSION['nama']); ?>. Halaman ini fokus pada komponen gaji, perhitungan, dan laporan pembayaran.</p>
      </div>
      <span class="role-pill">Keuangan</span>
    </section>

    <section class="summary-grid">
      <article class="summary-card"><span class="green-text"><?= e(rupiah($totalPendapatan)); ?></span><p>Total Pendapatan</p></article>
      <article class="summary-card"><span class="red-text"><?= e(rupiah($totalPotongan)); ?></span><p>Total Potongan</p></article>
      <article class="summary-card"><span class="blue-text"><?= e(rupiah($totalGaji)); ?></span><p>Gaji Bersih</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($jumlahSlip); ?></span><p>Data Perhitungan</p></article>
    </section>

    <section class="role-action-grid finance-actions">
      <a class="role-action" href="absensi.php"><strong>Absensi Keuangan</strong><span>Edit dan hapus presensi divisi keuangan</span></a>
      <a class="role-action" href="komponen-gaji.php"><strong>Komponen Gaji</strong><span>Atur gaji pokok dan tunjangan</span></a>
      <a class="role-action" href="gaji.php"><strong>Hitung Gaji</strong><span>Proses gaji karyawan</span></a>
      <a class="role-action" href="laporan.php"><strong>Laporan</strong><span>Lihat rekap data</span></a>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Perhitungan Terbaru</h2>
        <a class="small-button" href="gaji.php">Hitung Gaji</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nama</th><th>Divisi</th><th>Pendapatan</th><th>Potongan</th><th>Bersih</th></tr></thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($gaji)) : ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['nama_divisi'] ?? '-'); ?></td>
                <td><?= e(rupiah($row['total_pendapatan'])); ?></td>
                <td><?= e(rupiah($row['total_potongan'])); ?></td>
                <td><strong><?= e(rupiah($row['gaji_bersih'])); ?></strong></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
