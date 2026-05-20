<?php
require '../../config/koneksi.php';
/** @var mysqli $koneksi */
require '../auth/auth.php';
requireRole(['admin', 'keuangan']);

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_divisi = (int) ($_POST['id_divisi'] ?? 0);
  $gaji_pokok = (float) ($_POST['gaji_pokok'] ?? 0);
  $tunjangan = (float) ($_POST['tunjangan'] ?? 0);
  $bonus = (float) ($_POST['bonus'] ?? 0);
  $uang_makan = (float) ($_POST['uang_makan'] ?? 0);
  $lembur = (float) ($_POST['lembur'] ?? 0);
  $potongan_pajak = (float) ($_POST['potongan_pajak'] ?? 0);
  $bpjs = (float) ($_POST['bpjs'] ?? 0);

  if ($id_divisi && $gaji_pokok >= 0) {
    $stmt = mysqli_prepare(
      $koneksi,
      'INSERT INTO komponen_gaji (id_divisi, gaji_pokok, tunjangan, bonus, uang_makan, lembur, potongan_pajak, bpjs)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmt, 'iddddddd', $id_divisi, $gaji_pokok, $tunjangan, $bonus, $uang_makan, $lembur, $potongan_pajak, $bpjs);

    if (mysqli_stmt_execute($stmt)) {
      header('Location: komponen-gaji.php?status=ditambahkan');
      exit;
    }

    $pesan = 'Komponen gaji gagal disimpan.';
  } else {
    $pesan = 'Pilih divisi dan isi gaji pokok.';
  }
}

$divisi = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY nama_divisi');
$komponen = mysqli_query(
  $koneksi,
  'SELECT komponen_gaji.*, divisi.nama_divisi
   FROM komponen_gaji
   LEFT JOIN divisi ON divisi.id_divisi = komponen_gaji.id_divisi
   ORDER BY komponen_gaji.id_komponen DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Komponen Gaji</title>
  <link rel="stylesheet" href="/tugaspratikum/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/tugaspratikum/pages/dashboard/dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?= renderNavLinks('komponen'); ?>
      </ul>
      <a class="nav-button" href="/tugaspratikum/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Komponen Gaji</p>
      <h1>Atur Komponen Gaji per Divisi</h1>
      <p>Data disimpan ke tabel komponen_gaji dan dipakai saat menghitung gaji karyawan.</p>
    </section>

    <?php if (isset($_GET['status'])) : ?>
      <div class="notice success">Komponen gaji berhasil ditambahkan.</div>
    <?php elseif ($pesan) : ?>
      <div class="notice danger"><?= e($pesan); ?></div>
    <?php endif; ?>

    <section class="content-card form-card" id="tambah-komponen">
      <div class="section-heading">
        <h2>Tambah Komponen</h2>
      </div>

      <form class="data-form" method="post" action="komponen-gaji.php">
        <label>Divisi
          <select name="id_divisi" required>
            <option value="">Pilih divisi</option>
            <?php while ($row = mysqli_fetch_assoc($divisi)) : ?>
              <option value="<?= e($row['id_divisi']); ?>"><?= e($row['nama_divisi']); ?></option>
            <?php endwhile; ?>
          </select>
        </label>
        <label>Gaji Pokok
          <input name="gaji_pokok" type="number" min="0" step="1000" value="0" required>
        </label>
        <label>Tunjangan
          <input name="tunjangan" type="number" min="0" step="1000" value="0">
        </label>
        <label>Bonus
          <input name="bonus" type="number" min="0" step="1000" value="0">
        </label>
        <label>Uang Makan
          <input name="uang_makan" type="number" min="0" step="1000" value="0">
        </label>
        <label>Lembur
          <input name="lembur" type="number" min="0" step="1000" value="0">
        </label>
        <label>Potongan Pajak
          <input name="potongan_pajak" type="number" min="0" step="1000" value="0">
        </label>
        <label>BPJS
          <input name="bpjs" type="number" min="0" step="1000" value="0">
        </label>
        <button class="small-button form-submit" type="submit">Simpan Komponen</button>
      </form>
    </section>

    <section class="content-card">
      <div class="section-heading">
        <h2>Data Komponen Gaji</h2>
        <a class="small-button" href="#tambah-komponen">+ Tambah Komponen</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Divisi</th><th>Gaji Pokok</th><th>Tunjangan</th><th>Bonus</th><th>Uang Makan</th><th>Lembur</th><th>Pajak</th><th>BPJS</th></tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($komponen)) : ?>
              <tr>
                <td><?= e($row['nama_divisi'] ?? '-'); ?></td>
                <td><?= e(rupiah($row['gaji_pokok'])); ?></td>
                <td><?= e(rupiah($row['tunjangan'])); ?></td>
                <td><?= e(rupiah($row['bonus'])); ?></td>
                <td><?= e(rupiah($row['uang_makan'])); ?></td>
                <td><?= e(rupiah($row['lembur'])); ?></td>
                <td><?= e(rupiah($row['potongan_pajak'])); ?></td>
                <td><?= e(rupiah($row['bpjs'])); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
