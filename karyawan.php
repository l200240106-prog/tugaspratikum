<?php
require 'koneksi.php';
require 'auth.php';
require_role('admin');

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form = $_POST['form'] ?? 'karyawan';

  if ($form === 'divisi') {
    $nama_divisi = trim($_POST['nama_divisi'] ?? '');

    if ($nama_divisi) {
      $stmt = mysqli_prepare($koneksi, 'INSERT INTO divisi (nama_divisi) VALUES (?)');
      mysqli_stmt_bind_param($stmt, 's', $nama_divisi);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: karyawan.php?status=divisi');
        exit;
      }

      $pesan = 'Data divisi gagal ditambahkan.';
    } else {
      $pesan = 'Nama divisi wajib diisi.';
    }
  } elseif ($form === 'divisi_update') {
    $id_divisi = (int) ($_POST['id_divisi'] ?? 0);
    $nama_divisi = trim($_POST['nama_divisi'] ?? '');

    if ($id_divisi && $nama_divisi) {
      $stmt = mysqli_prepare($koneksi, 'UPDATE divisi SET nama_divisi = ? WHERE id_divisi = ?');
      mysqli_stmt_bind_param($stmt, 'si', $nama_divisi, $id_divisi);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: karyawan.php?status=divisi_diubah');
        exit;
      }

      $pesan = 'Data divisi gagal diubah.';
    } else {
      $pesan = 'Pilih divisi dan isi nama divisi.';
    }
  } elseif ($form === 'divisi_delete') {
    $id_divisi = (int) ($_POST['id_divisi'] ?? 0);

    if ($id_divisi) {
      $stmt = mysqli_prepare(
        $koneksi,
        'SELECT
          (SELECT COUNT(*) FROM karyawan WHERE id_divisi = ?) AS total_karyawan,
          (SELECT COUNT(*) FROM komponen_gaji WHERE id_divisi = ?) AS total_komponen'
      );
      mysqli_stmt_bind_param($stmt, 'ii', $id_divisi, $id_divisi);
      mysqli_stmt_execute($stmt);
      $relasi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
      $totalKaryawan = (int) ($relasi['total_karyawan'] ?? 0);
      $totalKomponen = (int) ($relasi['total_komponen'] ?? 0);

      if ($totalKaryawan > 0 || $totalKomponen > 0) {
        $pesan = 'Divisi tidak dapat dihapus karena masih digunakan oleh karyawan atau komponen gaji.';
      } else {
        $stmt = mysqli_prepare($koneksi, 'DELETE FROM divisi WHERE id_divisi = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id_divisi);

        if (mysqli_stmt_execute($stmt)) {
          header('Location: karyawan.php?status=divisi_dihapus');
          exit;
        }

        $pesan = 'Data divisi gagal dihapus.';
      }
    } else {
      $pesan = 'Pilih divisi yang ingin dihapus.';
    }
  }

  $nama = trim($_POST['nama'] ?? '');
  $id_divisi = (int) ($_POST['id_divisi'] ?? 0);
  $no_hp = trim($_POST['no_hp'] ?? '');
  $jabatan = trim($_POST['jabatan'] ?? '');
  $alamat = trim($_POST['alamat'] ?? '');
  $tanggal_masuk = $_POST['tanggal_masuk'] ?? date('Y-m-d');
  $status_kerja = $_POST['status_kerja'] ?? 'Aktif';

  if (!$pesan && $form === 'karyawan' && $nama && $id_divisi && $jabatan && $tanggal_masuk) {
    $stmt = mysqli_prepare(
      $koneksi,
      'INSERT INTO karyawan (nama, no_hp, jabatan, alamat, tanggal_masuk, status_kerja, id_divisi) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $no_hp, $jabatan, $alamat, $tanggal_masuk, $status_kerja, $id_divisi);

    if (mysqli_stmt_execute($stmt)) {
      header('Location: karyawan.php?status=ditambahkan');
      exit;
    }

    $pesan = 'Data karyawan gagal ditambahkan.';
  } elseif (!$pesan && $form === 'karyawan') {
    $pesan = 'Lengkapi nama, divisi, jabatan, dan tanggal masuk.';
  }
}

$editDivisi = null;
if (isset($_GET['edit_divisi'])) {
  $idEditDivisi = (int) $_GET['edit_divisi'];
  $stmt = mysqli_prepare($koneksi, 'SELECT * FROM divisi WHERE id_divisi = ? LIMIT 1');
  mysqli_stmt_bind_param($stmt, 'i', $idEditDivisi);
  mysqli_stmt_execute($stmt);
  $editDivisi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$divisi = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY nama_divisi');
$daftarDivisi = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY id_divisi DESC');
$karyawan = mysqli_query(
  $koneksi,
  'SELECT karyawan.*, divisi.nama_divisi
   FROM karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   ORDER BY karyawan.id_karyawan DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Karyawan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?php nav_items('karyawan.php'); ?>
      </ul>
      <a class="nav-button" href="logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Data Karyawan</p>
      <h1>Kelola Karyawan dan Divisi</h1>
      <p>Simpan identitas, kontak, jabatan, alamat, tanggal masuk, status kerja, dan divisi karyawan.</p>
    </section>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'ditambahkan') : ?>
      <div class="notice success">Data karyawan berhasil ditambahkan.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'divisi') : ?>
      <div class="notice success">Data divisi berhasil ditambahkan.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'divisi_diubah') : ?>
      <div class="notice success">Data divisi berhasil diubah.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'divisi_dihapus') : ?>
      <div class="notice success">Data divisi berhasil dihapus.</div>
    <?php elseif ($pesan) : ?>
      <div class="notice danger"><?= e($pesan); ?></div>
    <?php endif; ?>

    <section class="content-card form-card" id="tambah-divisi">
      <div class="section-heading">
        <h2><?= $editDivisi ? 'Edit Divisi' : 'Tambah Divisi'; ?></h2>
        <?php if ($editDivisi) : ?>
          <a class="small-button muted-button" href="karyawan.php#tambah-divisi">Batal Edit</a>
        <?php endif; ?>
      </div>

      <form class="data-form" method="post" action="karyawan.php">
        <input type="hidden" name="form" value="<?= $editDivisi ? 'divisi_update' : 'divisi'; ?>">
        <?php if ($editDivisi) : ?>
          <input type="hidden" name="id_divisi" value="<?= e($editDivisi['id_divisi']); ?>">
        <?php endif; ?>
        <label>Nama Divisi
          <input name="nama_divisi" type="text" value="<?= e($editDivisi['nama_divisi'] ?? ''); ?>" placeholder="Contoh: Operasional" required>
        </label>
        <button class="small-button form-submit" type="submit"><?= $editDivisi ? 'Update Divisi' : 'Simpan Divisi'; ?></button>
      </form>
    </section>

    <section class="content-card form-card" id="tambah-karyawan">
      <div class="section-heading">
        <h2>Tambah Karyawan</h2>
      </div>

      <form class="data-form" method="post" action="karyawan.php">
        <input type="hidden" name="form" value="karyawan">
        <label>Nama Karyawan
          <input name="nama" type="text" placeholder="Nama lengkap" required>
        </label>
        <label>Divisi
          <select name="id_divisi" required>
            <option value="">Pilih divisi</option>
            <?php while ($row = mysqli_fetch_assoc($divisi)) : ?>
              <option value="<?= e($row['id_divisi']); ?>"><?= e($row['nama_divisi']); ?></option>
            <?php endwhile; ?>
          </select>
        </label>
        <label>No HP
          <input name="no_hp" type="text" placeholder="08xxxxxxxxxx">
        </label>
        <label>Jabatan
          <input name="jabatan" type="text" placeholder="Staff Operasional" required>
        </label>
        <label>Tanggal Masuk
          <input name="tanggal_masuk" type="date" value="<?= e(date('Y-m-d')); ?>" required>
        </label>
        <label>Status Kerja
          <select name="status_kerja" required>
            <option value="Aktif">Aktif</option>
            <option value="Kontrak">Kontrak</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </label>
        <label class="form-wide">Alamat
          <textarea name="alamat" rows="3" placeholder="Alamat karyawan"></textarea>
        </label>
        <button class="small-button form-submit" type="submit">Simpan Karyawan</button>
      </form>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Data Divisi</h2>
        <a class="small-button" href="#tambah-divisi">+ Tambah Divisi</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID Divisi</th><th>Nama Divisi</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($daftarDivisi)) : ?>
              <tr>
                <td><?= e($row['id_divisi']); ?></td>
                <td><?= e($row['nama_divisi']); ?></td>
                <td>
                  <div class="table-actions">
                    <a class="small-button muted-button" href="karyawan.php?edit_divisi=<?= e($row['id_divisi']); ?>#tambah-divisi">Edit</a>
                    <form method="post" action="karyawan.php" onsubmit="return confirm('Hapus divisi ini?');">
                      <input type="hidden" name="form" value="divisi_delete">
                      <input type="hidden" name="id_divisi" value="<?= e($row['id_divisi']); ?>">
                      <button class="small-button danger-button" type="submit">Hapus</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Data Karyawan</h2>
        <a class="small-button" href="#tambah-karyawan">+ Tambah Karyawan</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Divisi</th>
              <th>Jabatan</th>
              <th>No HP</th>
              <th>Tanggal Masuk</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($karyawan)) : ?>
              <?php
                $statusClass = $row['status_kerja'] === 'Aktif' ? 'success' : ($row['status_kerja'] === 'Kontrak' ? 'warning' : 'danger');
              ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['nama_divisi']); ?></td>
                <td><?= e($row['jabatan']); ?></td>
                <td><?= e($row['no_hp']); ?></td>
                <td><?= e($row['tanggal_masuk'] ? date('d M Y', strtotime($row['tanggal_masuk'])) : '-'); ?></td>
                <td><span class="badge <?= e($statusClass); ?>"><?= e($row['status_kerja']); ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
