<?php
require '../../config/koneksi.php';
require '../auth/auth.php';

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_karyawan = (int) ($_POST['id_karyawan'] ?? 0);
  $tanggal = $_POST['tanggal_kehadiran'] ?? date('Y-m-d');
  $status_hadir = $_POST['status_hadir'] ?? '';

  if ($id_karyawan && $tanggal && in_array($status_hadir, ['Hadir', 'Izin', 'Sakit', 'Alpha'], true)) {
    $stmt = mysqli_prepare(
      $koneksi,
      'INSERT INTO absensi (id_karyawan, tanggal_kehadiran, status_hadir) VALUES (?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmt, 'iss', $id_karyawan, $tanggal, $status_hadir);

    if (mysqli_stmt_execute($stmt)) {
      header('Location: absensi.php?status=tersimpan');
      exit;
    }

    $pesan = 'Absensi gagal disimpan.';
  } else {
    $pesan = 'Pilih karyawan, tanggal, dan status absensi.';
  }
}

$tanggalFilter = $_GET['tanggal'] ?? date('Y-m-d');
$karyawanList = mysqli_query($koneksi, "SELECT id_karyawan, nama FROM karyawan ORDER BY nama");

$summaryStmt = mysqli_prepare(
  $koneksi,
  "SELECT status_hadir, COUNT(*) AS total FROM absensi WHERE tanggal_kehadiran = ? GROUP BY status_hadir"
);
mysqli_stmt_bind_param($summaryStmt, 's', $tanggalFilter);
mysqli_stmt_execute($summaryStmt);
$summaryResult = mysqli_stmt_get_result($summaryStmt);
$summary = ['Hadir' => 0, 'Alpha' => 0, 'Izin' => 0, 'Sakit' => 0];
while ($row = mysqli_fetch_assoc($summaryResult)) {
  $summary[$row['status_hadir']] = (int) $row['total'];
}

$logStmt = mysqli_prepare(
  $koneksi,
  'SELECT absensi.*, karyawan.nama
   FROM absensi
   JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
   WHERE absensi.tanggal_kehadiran = ?
   ORDER BY absensi.id_absensi DESC'
);
mysqli_stmt_bind_param($logStmt, 's', $tanggalFilter);
mysqli_stmt_execute($logStmt);
$logAbsensi = mysqli_stmt_get_result($logStmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi Karyawan</title>
  <link rel="stylesheet" href="/tugaspratikum/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/tugaspratikum/pages/dashboard/dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <li><a href="/tugaspratikum/pages/dashboard/dashboard.php">Beranda</a></li>
        <li><a href="/tugaspratikum/pages/karyawan/karyawan.php">Karyawan</a></li>
        <li><a class="active" href="/tugaspratikum/pages/absensi/absensi.php">Absensi</a></li>
        <li><a href="/tugaspratikum/pages/penggajian/komponen-gaji.php">Komponen</a></li>
        <li><a href="/tugaspratikum/pages/penggajian/gaji.php">Gaji</a></li>
        <li><a href="/tugaspratikum/pages/laporan/laporan.php">Laporan</a></li>
      </ul>
      <a class="nav-button" href="/tugaspratikum/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Absensi Karyawan</p>
      <h1>Catat Kehadiran Harian</h1>
      <p>Data absensi karyawan WeebeMart disimpan ke tabel absensi.</p>
    </section>

    <?php if (isset($_GET['status'])) : ?>
      <div class="notice success">Absensi berhasil disimpan.</div>
    <?php elseif ($pesan) : ?>
      <div class="notice danger"><?= e($pesan); ?></div>
    <?php endif; ?>

    <section class="summary-grid">
      <article class="summary-card"><span class="green-text"><?= e($summary['Hadir']); ?></span><p>Hadir</p></article>
      <article class="summary-card"><span class="orange-text"><?= e($summary['Izin']); ?></span><p>Izin</p></article>
      <article class="summary-card"><span class="blue-text"><?= e($summary['Sakit']); ?></span><p>Sakit</p></article>
      <article class="summary-card"><span class="red-text"><?= e($summary['Alpha']); ?></span><p>Alpha</p></article>
    </section>

    <section class="content-card form-card" id="input-absensi">
      <div class="section-heading">
        <h2>Input Absensi</h2>
        <form class="filter-form" method="get" action="absensi.php">
          <input name="tanggal" type="date" value="<?= e($tanggalFilter); ?>">
          <button class="small-button" type="submit">Lihat</button>
        </form>
      </div>

      <form class="data-form" method="post" action="absensi.php">
        <label>Karyawan
          <select name="id_karyawan" required>
            <option value="">Pilih karyawan</option>
            <?php while ($row = mysqli_fetch_assoc($karyawanList)) : ?>
              <option value="<?= e($row['id_karyawan']); ?>"><?= e($row['nama']); ?></option>
            <?php endwhile; ?>
          </select>
        </label>
        <label>Tanggal
          <input name="tanggal_kehadiran" type="date" value="<?= e($tanggalFilter); ?>" required>
        </label>
        <label>Status
          <select name="status_hadir" required>
            <option value="">Pilih status</option>
            <option value="Hadir">Hadir</option>
            <option value="Izin">Izin</option>
            <option value="Sakit">Sakit</option>
            <option value="Alpha">Alpha</option>
          </select>
        </label>
        <button class="small-button form-submit" type="submit">Simpan Absensi</button>
      </form>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Log Absensi</h2>
        <a class="small-button" href="#input-absensi">+ Input Absensi</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Nama</th><th>Tanggal</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($logAbsensi)) : ?>
              <?php
                $badge = 'warning';
                if ($row['status_hadir'] === 'Hadir') $badge = 'success';
                if ($row['status_hadir'] === 'Alpha') $badge = 'danger';
              ?>
              <tr>
                <td><?= e($row['id_karyawan']); ?></td>
                <td><?= e($row['nama']); ?></td>
                <td><?= e(date('d M Y', strtotime($row['tanggal_kehadiran']))); ?></td>
                <td><span class="badge <?= e($badge); ?>"><?= e($row['status_hadir']); ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
