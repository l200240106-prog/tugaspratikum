<?php
require '../../config/koneksi.php';
/** @var mysqli $koneksi */
require '../auth/auth.php';

$notice = '';
$noticeClass = '';
$role = $_SESSION['role'];
$isAdmin = isAdmin();
$isFinance = isFinance();
$isKaryawan = isKaryawan();

$today = date('Y-m-d');

$totalKaryawan = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM karyawan"))['total'];
$totalDivisi = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM divisi"))['total'];
$totalGaji = (float) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(gaji_bersih), 0) AS total FROM perhitungan"))['total'];
$hadir = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir = 'Hadir'"))['total'];
$izinSakit = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir IN ('Izin', 'Sakit')"))['total'];
$alpha = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = '$today' AND status_hadir = 'Alpha'"))['total'];
$totalAbsensi = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi"))['total'];

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

if (isset($_GET['pesan']) && $_GET['pesan'] === 'unauthorized') {
  $notice = 'Akses ditolak. Silakan gunakan halaman yang sesuai untuk peran Anda.';
  $noticeClass = 'danger';
}

$pageTitle = 'Dashboard WeebeMart';
$pageSubtitle = 'Ringkasan aktivitas dan akses cepat sesuai peran Anda.';
$cards = [];
$actions = [];

if ($isAdmin) {
  $pageTitle = 'Dashboard Admin';
  $pageSubtitle = 'Anda dapat menggunakan semua fitur sistem.';
  $cards = [
    ['value' => $totalKaryawan, 'text' => 'Total Karyawan'],
    ['value' => $hadir, 'text' => 'Hadir Hari Ini'],
    ['value' => $totalDivisi, 'text' => 'Total Divisi'],
    ['value' => rupiah($totalGaji), 'text' => 'Total Gaji Bersih'],
  ];
  $actions = [
    ['url' => '/tugaspratikum/pages/karyawan/karyawan.php', 'label' => 'Kelola Karyawan'],
    ['url' => '/tugaspratikum/pages/absensi/absensi.php', 'label' => 'Kelola Absensi'],
    ['url' => '/tugaspratikum/pages/penggajian/gaji.php', 'label' => 'Akses Gaji'],
  ];
} elseif ($isFinance) {
  $pageTitle = 'Dashboard Keuangan';
  $pageSubtitle = 'Akses sistem penggajian dan periksa absensi untuk penggajian.';
  $cards = [
    ['value' => rupiah($totalGaji), 'text' => 'Total Gaji Bersih'],
    ['value' => $hadir, 'text' => 'Hadir Hari Ini'],
    ['value' => $totalAbsensi, 'text' => 'Total Data Absensi'],
    ['value' => $totalDivisi, 'text' => 'Total Divisi'],
  ];
  $actions = [
    ['url' => '/tugaspratikum/pages/absensi/absensi.php', 'label' => 'Periksa Absensi'],
    ['url' => '/tugaspratikum/pages/penggajian/gaji.php', 'label' => 'Hitung Gaji'],
    ['url' => '/tugaspratikum/pages/penggajian/komponen-gaji.php', 'label' => 'Atur Komponen'],
  ];
} else {
  $pageTitle = 'Dashboard Karyawan';
  $pageSubtitle = 'Akses terbatas ke sistem absensi untuk karyawan biasa.';
  $cards = [
    ['value' => $hadir, 'text' => 'Hadir Hari Ini'],
    ['value' => $izinSakit, 'text' => 'Izin / Sakit Hari Ini'],
    ['value' => $alpha, 'text' => 'Alpha Hari Ini'],
    ['value' => $totalAbsensi, 'text' => 'Total Absensi'],
  ];
  $actions = [
    ['url' => '/tugaspratikum/pages/absensi/absensi.php', 'label' => 'Buka Absensi'],
  ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle); ?></title>
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
        <?= renderNavLinks('dashboard'); ?>
      </ul>

      <a class="nav-button" href="/tugaspratikum/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Panel WeebeMart</p>
      <h1><?= e($pageTitle); ?></h1>
      <p>Halo, <?= e($_SESSION['nama']); ?>. <?= e($pageSubtitle); ?></p>
      <?php if (!empty($notice)) : ?>
        <div class="notice <?= e($noticeClass); ?>"><?= e($notice); ?></div>
      <?php endif; ?>
    </section>

    <section class="summary-grid">
      <?php foreach ($cards as $card) : ?>
        <article class="summary-card"><span><?= e($card['value']); ?></span><p><?= e($card['text']); ?></p></article>
      <?php endforeach; ?>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Fitur Utama</h2>
      </div>
      <div class="button-group">
        <?php foreach ($actions as $action) : ?>
          <a class="small-button" href="<?= e($action['url']); ?>"><?= e($action['label']); ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($isAdmin || $isFinance) : ?>
      <section class="content-card">
        <div class="section-heading">
          <h2>Aktivitas Terbaru</h2>
          <a class="small-button" href="../absensi/absensi.php">Lihat Absensi</a>
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
    <?php endif; ?>
  </main>
</body>
</html>
