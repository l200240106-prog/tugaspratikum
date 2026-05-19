<?php
require '../../config/koneksi.php';
require '../auth/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akses WeebeMart</title>
  <link rel="stylesheet" href="/tugaspratikum/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/tugaspratikum/pages/dashboard/dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <li><a href="/tugaspratikum/pages/dashboard/dashboard.php">Beranda</a></li>
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
      <p class="eyebrow"><span></span>Akses WeebeMart</p>
      <h1>Akun Login WeebeMart</h1>
      <p>Akun ini dipakai untuk masuk ke aplikasi WeebeMart.</p>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Daftar Akun</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Peran</th><th>Email</th><th>Password</th></tr>
          </thead>
          <tbody>
            <tr><td>Admin</td><td>admin@weebemart.com</td><td>admin123</td></tr>
            <tr><td>Keuangan</td><td>keuangan@weebemart.com</td><td>uang123</td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
