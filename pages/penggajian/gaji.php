<?php
require '../../config/koneksi.php';
/** @var mysqli $koneksi */
require '../auth/auth.php';
requireRole(['admin', 'keuangan', 'operasional', 'pergudangan']);

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!in_array(getAppRole(), ['admin', 'keuangan'])) {
    $pesan = 'Akses ditolak: Hanya admin dan keuangan yang dapat menghitung gaji.';
  } else {
    $id_karyawan = (int) ($_POST['id_karyawan'] ?? 0);

  $stmt = mysqli_prepare(
    $koneksi,
    'SELECT karyawan.id_karyawan, komponen_gaji.*
     FROM karyawan
     JOIN komponen_gaji ON komponen_gaji.id_divisi = karyawan.id_divisi
     WHERE karyawan.id_karyawan = ?
     LIMIT 1'
  );
  mysqli_stmt_bind_param($stmt, 'i', $id_karyawan);
  mysqli_stmt_execute($stmt);
  $komponen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

  if ($komponen) {
    $totalPendapatan = (float) $komponen['gaji_pokok'] + (float) $komponen['tunjangan'] + (float) $komponen['bonus'] + (float) $komponen['uang_makan'] + (float) $komponen['lembur'];
    $totalPotongan = (float) $komponen['potongan_pajak'] + (float) $komponen['bpjs'];
    $gajiBersih = $totalPendapatan - $totalPotongan;

    $insert = mysqli_prepare(
      $koneksi,
      'INSERT INTO perhitungan (id_karyawan, total_pendapatan, total_potongan, gaji_bersih) VALUES (?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($insert, 'iddd', $id_karyawan, $totalPendapatan, $totalPotongan, $gajiBersih);

    if (mysqli_stmt_execute($insert)) {
      header('Location: gaji.php?status=dihitung');
      exit;
    }

    $pesan = 'Perhitungan gaji gagal disimpan.';
  } else {
    $pesan = 'Komponen gaji untuk divisi karyawan ini belum tersedia.';
  }
  }
}

$karyawan = mysqli_query(
  $koneksi,
  'SELECT karyawan.id_karyawan, karyawan.nama, divisi.nama_divisi
   FROM karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   ORDER BY karyawan.nama'
);

$gaji = mysqli_query(
  $koneksi,
  'SELECT perhitungan.*, karyawan.nama, divisi.nama_divisi
   FROM perhitungan
   JOIN karyawan ON karyawan.id_karyawan = perhitungan.id_karyawan
   LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
   ORDER BY perhitungan.id_perhitungan DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gaji Karyawan</title>
  <link rel="icon" href="/assets/weebemart.ico">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/pages/dashboard/dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?= renderNavLinks('gaji'); ?>
      </ul>
      <a class="nav-button" href="/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Perhitungan Gaji</p>
      <h1>Rekap Gaji Karyawan</h1>
      <p>Data gaji karyawan WeebeMart dibaca langsung dari tabel perhitungan.</p>
    </section>

    <?php if (isset($_GET['status'])) : ?>
      <div class="notice success">Gaji berhasil dihitung dan disimpan.</div>
    <?php elseif ($pesan) : ?>
      <div class="notice danger"><?= e($pesan); ?></div>
    <?php endif; ?>

    <?php if (in_array(getAppRole(), ['admin', 'keuangan'])): ?>
    <section class="content-card form-card" id="hitung-gaji">
      <div class="section-heading">
        <h2>Hitung Gaji</h2>
      </div>

      <form class="data-form" method="post" action="gaji.php">
        <label>Karyawan
          <select name="id_karyawan" required>
            <option value="">Pilih karyawan</option>
            <?php while ($row = mysqli_fetch_assoc($karyawan)) : ?>
              <option value="<?= e($row['id_karyawan']); ?>"><?= e($row['nama'] . ' - ' . ($row['nama_divisi'] ?? '-')); ?></option>
            <?php endwhile; ?>
          </select>
        </label>
        <button class="small-button form-submit" type="submit">Hitung Gaji</button>
      </form>
    </section>
    <?php endif; ?>

    <section class="content-card">
      <div class="section-heading">
        <h2>Rekap Perhitungan Gaji</h2>
        <?php if (in_array(getAppRole(), ['admin', 'keuangan'])): ?>
        <a class="small-button" href="#hitung-gaji">+ Hitung Gaji</a>
        <?php endif; ?>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nama</th><th>Divisi</th><th>Total Pendapatan</th><th>Total Potongan</th><th>Gaji Bersih</th></tr>
          </thead>
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
