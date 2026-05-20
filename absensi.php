<?php
require 'koneksi.php';
require 'auth.php';

$pesan = '';
$tanggalFilter = $_GET['tanggal'] ?? date('Y-m-d');
$idDivisiUser = (int) ($_SESSION['id_divisi'] ?? 0);
$isAdmin = is_admin();

function karyawan_divisi($koneksi, $id_karyawan) {
  $stmt = mysqli_prepare($koneksi, 'SELECT id_divisi FROM karyawan WHERE id_karyawan = ? LIMIT 1');
  mysqli_stmt_bind_param($stmt, 'i', $id_karyawan);
  mysqli_stmt_execute($stmt);
  $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

  return $row ? (int) $row['id_divisi'] : 0;
}

function absensi_row($koneksi, $id_absensi) {
  $stmt = mysqli_prepare(
    $koneksi,
    'SELECT absensi.*, karyawan.nama, karyawan.id_divisi, divisi.nama_divisi
     FROM absensi
     JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
     LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
     WHERE absensi.id_absensi = ?
     LIMIT 1'
  );
  mysqli_stmt_bind_param($stmt, 'i', $id_absensi);
  mysqli_stmt_execute($stmt);

  return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $aksi = $_POST['aksi'] ?? 'tambah';

  if ($aksi === 'hapus') {
    $id_absensi = (int) ($_POST['id_absensi'] ?? 0);
    $absensi = absensi_row($koneksi, $id_absensi);

    if ($absensi && can_manage_absensi($absensi['id_divisi'])) {
      $stmt = mysqli_prepare($koneksi, 'DELETE FROM absensi WHERE id_absensi = ?');
      mysqli_stmt_bind_param($stmt, 'i', $id_absensi);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: absensi.php?status=dihapus&tanggal=' . urlencode($tanggalFilter));
        exit;
      }

      $pesan = 'Absensi gagal dihapus.';
    } else {
      $pesan = 'Anda tidak memiliki akses untuk menghapus data absensi ini.';
    }
  } else {
    $id_absensi = (int) ($_POST['id_absensi'] ?? 0);
    $id_karyawan = (int) ($_POST['id_karyawan'] ?? 0);
    $tanggal = $_POST['tanggal_kehadiran'] ?? date('Y-m-d');
    $status_hadir = $_POST['status_hadir'] ?? '';
    $idDivisiKaryawan = karyawan_divisi($koneksi, $id_karyawan);
    $bolehSimpan = $isAdmin || ($idDivisiKaryawan === $idDivisiUser);

    if ($aksi === 'edit') {
      $absensiLama = absensi_row($koneksi, $id_absensi);
      $bolehSimpan = $absensiLama && can_manage_absensi($absensiLama['id_divisi']) && ($isAdmin || $idDivisiKaryawan === $idDivisiUser);
    }

    if ($id_karyawan && $tanggal && in_array($status_hadir, ['Hadir', 'Izin', 'Sakit', 'Alpha'], true) && $bolehSimpan) {
      if ($aksi === 'edit') {
        $stmt = mysqli_prepare(
          $koneksi,
          'UPDATE absensi SET id_karyawan = ?, tanggal_kehadiran = ?, status_hadir = ? WHERE id_absensi = ?'
        );
        mysqli_stmt_bind_param($stmt, 'issi', $id_karyawan, $tanggal, $status_hadir, $id_absensi);
        $status = 'diubah';
      } else {
        $stmt = mysqli_prepare(
          $koneksi,
          'INSERT INTO absensi (id_karyawan, tanggal_kehadiran, status_hadir) VALUES (?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'iss', $id_karyawan, $tanggal, $status_hadir);
        $status = 'tersimpan';
      }

      if (mysqli_stmt_execute($stmt)) {
        header('Location: absensi.php?status=' . $status . '&tanggal=' . urlencode($tanggal));
        exit;
      }

      $pesan = 'Absensi gagal disimpan.';
    } elseif (!$bolehSimpan) {
      $pesan = 'Anda hanya dapat menyimpan absensi untuk divisi sendiri.';
    } else {
      $pesan = 'Pilih karyawan, tanggal, dan status absensi.';
    }
  }
}

$editAbsensi = null;
if (isset($_GET['edit'])) {
  $editAbsensi = absensi_row($koneksi, (int) $_GET['edit']);

  if (!$editAbsensi || !can_manage_absensi($editAbsensi['id_divisi'])) {
    $editAbsensi = null;
    $pesan = 'Anda tidak memiliki akses untuk mengedit data absensi ini.';
  }
}

if ($isAdmin) {
  $karyawanList = mysqli_query(
    $koneksi,
    'SELECT karyawan.id_karyawan, karyawan.nama, divisi.nama_divisi
     FROM karyawan
     LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
     ORDER BY karyawan.nama'
  );
} else {
  $karyawanStmt = mysqli_prepare(
    $koneksi,
    'SELECT karyawan.id_karyawan, karyawan.nama, divisi.nama_divisi
     FROM karyawan
     LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
     WHERE karyawan.id_divisi = ?
     ORDER BY karyawan.nama'
  );
  mysqli_stmt_bind_param($karyawanStmt, 'i', $idDivisiUser);
  mysqli_stmt_execute($karyawanStmt);
  $karyawanList = mysqli_stmt_get_result($karyawanStmt);
}

$summarySql = 'SELECT absensi.status_hadir, COUNT(*) AS total
  FROM absensi
  JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
  WHERE absensi.tanggal_kehadiran = ?';
if (!$isAdmin) {
  $summarySql .= ' AND karyawan.id_divisi = ?';
}
$summarySql .= ' GROUP BY absensi.status_hadir';

$summaryStmt = mysqli_prepare($koneksi, $summarySql);
if ($isAdmin) {
  mysqli_stmt_bind_param($summaryStmt, 's', $tanggalFilter);
} else {
  mysqli_stmt_bind_param($summaryStmt, 'si', $tanggalFilter, $idDivisiUser);
}
mysqli_stmt_execute($summaryStmt);
$summaryResult = mysqli_stmt_get_result($summaryStmt);
$summary = ['Hadir' => 0, 'Alpha' => 0, 'Izin' => 0, 'Sakit' => 0];
while ($row = mysqli_fetch_assoc($summaryResult)) {
  $summary[$row['status_hadir']] = (int) $row['total'];
}

$logSql = 'SELECT absensi.*, karyawan.nama, karyawan.id_divisi, divisi.nama_divisi
  FROM absensi
  JOIN karyawan ON karyawan.id_karyawan = absensi.id_karyawan
  LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
  WHERE absensi.tanggal_kehadiran = ?';
if (!$isAdmin) {
  $logSql .= ' AND karyawan.id_divisi = ?';
}
$logSql .= ' ORDER BY absensi.id_absensi DESC';

$logStmt = mysqli_prepare($koneksi, $logSql);
if ($isAdmin) {
  mysqli_stmt_bind_param($logStmt, 's', $tanggalFilter);
} else {
  mysqli_stmt_bind_param($logStmt, 'si', $tanggalFilter, $idDivisiUser);
}
mysqli_stmt_execute($logStmt);
$logAbsensi = mysqli_stmt_get_result($logStmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi Karyawan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?php nav_items('absensi.php'); ?>
      </ul>
      <a class="nav-button" href="logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Absensi Karyawan</p>
      <h1>Catat Kehadiran Harian</h1>
      <p>Data absensi karyawan WeebeMart disimpan ke tabel absensi.</p>
    </section>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'tersimpan') : ?>
      <div class="notice success">Absensi berhasil disimpan.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'diubah') : ?>
      <div class="notice success">Absensi berhasil diubah.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'dihapus') : ?>
      <div class="notice success">Absensi berhasil dihapus.</div>
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
        <h2><?= $editAbsensi ? 'Edit Absensi' : 'Input Absensi'; ?></h2>
        <form class="filter-form" method="get" action="absensi.php">
          <input name="tanggal" type="date" value="<?= e($tanggalFilter); ?>">
          <button class="small-button" type="submit">Lihat</button>
        </form>
      </div>

      <form class="data-form" method="post" action="absensi.php">
        <input type="hidden" name="aksi" value="<?= $editAbsensi ? 'edit' : 'tambah'; ?>">
        <input type="hidden" name="id_absensi" value="<?= e($editAbsensi['id_absensi'] ?? ''); ?>">
        <label>Karyawan
          <select name="id_karyawan" required>
            <option value="">Pilih karyawan</option>
            <?php while ($row = mysqli_fetch_assoc($karyawanList)) : ?>
              <option value="<?= e($row['id_karyawan']); ?>" <?= $editAbsensi && (int) $editAbsensi['id_karyawan'] === (int) $row['id_karyawan'] ? 'selected' : ''; ?>>
                <?= e($row['nama'] . ' - ' . ($row['nama_divisi'] ?? '-')); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </label>
        <label>Tanggal
          <input name="tanggal_kehadiran" type="date" value="<?= e($editAbsensi['tanggal_kehadiran'] ?? $tanggalFilter); ?>" required>
        </label>
        <label>Status
          <select name="status_hadir" required>
            <option value="">Pilih status</option>
            <?php foreach (['Hadir', 'Izin', 'Sakit', 'Alpha'] as $status) : ?>
              <option value="<?= e($status); ?>" <?= $editAbsensi && $editAbsensi['status_hadir'] === $status ? 'selected' : ''; ?>><?= e($status); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button class="small-button form-submit" type="submit"><?= $editAbsensi ? 'Simpan Perubahan' : 'Simpan Absensi'; ?></button>
        <?php if ($editAbsensi) : ?>
          <a class="small-button muted-button" href="absensi.php?tanggal=<?= e($tanggalFilter); ?>">Batal</a>
        <?php endif; ?>
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
            <tr><th>ID</th><th>Nama</th><th>Divisi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($logAbsensi)) : ?>
              <?php
                $badge = 'warning';
                if ($row['status_hadir'] === 'Hadir') $badge = 'success';
                if ($row['status_hadir'] === 'Alpha') $badge = 'danger';
                $bolehKelola = can_manage_absensi($row['id_divisi']);
              ?>
              <tr>
                <td><?= e($row['id_absensi']); ?></td>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['nama_divisi'] ?? '-'); ?></td>
                <td><?= e(date('d M Y', strtotime($row['tanggal_kehadiran']))); ?></td>
                <td><span class="badge <?= e($badge); ?>"><?= e($row['status_hadir']); ?></span></td>
                <td>
                  <?php if ($bolehKelola) : ?>
                    <div class="table-actions">
                      <a class="small-button" href="absensi.php?edit=<?= e($row['id_absensi']); ?>&tanggal=<?= e($tanggalFilter); ?>#input-absensi">Edit</a>
                      <form method="post" action="absensi.php?tanggal=<?= e($tanggalFilter); ?>" onsubmit="return confirm('Hapus data absensi ini?');">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id_absensi" value="<?= e($row['id_absensi']); ?>">
                        <button class="small-button danger-button" type="submit">Hapus</button>
                      </form>
                    </div>
                  <?php else : ?>
                    -
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
