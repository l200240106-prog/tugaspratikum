<?php
require 'koneksi.php';
require 'auth.php';

$totalKaryawan = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM karyawan'))['total'];
$totalDivisi = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM divisi'))['total'];
$totalAbsensi = (int) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM absensi'))['total'];
$totalGaji = (float) mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COALESCE(SUM(gaji_bersih), 0) AS total FROM perhitungan'))['total'];

$rekapAbsensi = mysqli_query(
  $koneksi,
  'SELECT karyawan.nama, divisi.nama_divisi,
    SUM(absensi.status_hadir = "Hadir") AS hadir,
    SUM(absensi.status_hadir = "Izin") AS izin,
    SUM(absensi.status_hadir = "Sakit") AS sakit,
    SUM(absensi.status_hadir = "Alpha") AS alpha
   FROM karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   LEFT JOIN absensi ON absensi.id_karyawan = karyawan.id_karyawan
   GROUP BY karyawan.id_karyawan, karyawan.nama, divisi.nama_divisi
   ORDER BY karyawan.nama'
);

$rekapGaji = mysqli_query(
  $koneksi,
  'SELECT karyawan.nama, divisi.nama_divisi,
    COALESCE(SUM(perhitungan.total_pendapatan), 0) AS total_pendapatan,
    COALESCE(SUM(perhitungan.total_potongan), 0) AS total_potongan,
    COALESCE(SUM(perhitungan.gaji_bersih), 0) AS gaji_bersih
   FROM karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   LEFT JOIN perhitungan ON perhitungan.id_karyawan = karyawan.id_karyawan
   GROUP BY karyawan.id_karyawan, karyawan.nama, divisi.nama_divisi
   ORDER BY karyawan.nama'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan WeebeMart</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <li><a href="dashboard.php">Beranda</a></li>
        <li><a href="karyawan.php">Karyawan</a></li>
        <li><a href="absensi.php">Absensi</a></li>
        <li><a href="komponen-gaji.php">Komponen</a></li>
        <li><a href="gaji.php">Gaji</a></li>
        <li><a class="active" href="laporan.php">Laporan</a></li>
      </ul>
      <a class="nav-button" href="logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Laporan</p>
      <h1>Ringkasan Data WeebeMart</h1>
      <p>Laporan ini diambil dari data divisi, karyawan, absensi, dan perhitungan gaji.</p>
    </section>

    <section class="summary-grid">
      <article class="summary-card"><span class="blue-text"><?= e($totalKaryawan); ?></span><p>Karyawan</p></article>
      <article class="summary-card"><span class="green-text"><?= e($totalDivisi); ?></span><p>Divisi</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($totalAbsensi); ?></span><p>Data Absensi</p></article>
      <article class="summary-card"><span class="red-text"><?= e(rupiah($totalGaji)); ?></span><p>Total Gaji Bersih</p></article>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Rekap Absensi per Karyawan</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nama</th><th>Divisi</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpha</th></tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($rekapAbsensi)) : ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['nama_divisi'] ?? '-'); ?></td>
                <td><?= e((int) $row['hadir']); ?></td>
                <td><?= e((int) $row['izin']); ?></td>
                <td><?= e((int) $row['sakit']); ?></td>
                <td><?= e((int) $row['alpha']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Rekap Gaji per Karyawan</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nama</th><th>Divisi</th><th>Pendapatan</th><th>Potongan</th><th>Gaji Bersih</th></tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($rekapGaji)) : ?>
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
