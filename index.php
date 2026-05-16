<?php
session_start();
require 'koneksi.php';

if (!empty($_SESSION['id_user'])) {
  header('Location: dashboard.php');
  exit;
}

$pesan = '';
$divisiLogin = [];
$divisiResult = mysqli_query($koneksi, 'SELECT id_divisi, nama_divisi FROM divisi ORDER BY nama_divisi');

while ($row = mysqli_fetch_assoc($divisiResult)) {
  $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $row['nama_divisi']));
  $divisiLogin[] = [
    'id_divisi' => (int) $row['id_divisi'],
    'nama_divisi' => $row['nama_divisi'],
    'slug' => $slug,
    'email' => $slug . '@weebemart.com',
    'password' => $slug . '123',
  ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $id_divisi = (int) ($_POST['role'] ?? 0);

  if ($email && $password && $id_divisi) {
    $user = null;

    foreach ($divisiLogin as $divisi) {
      $passwordValid = $password === $divisi['password'];

      if ($divisi['slug'] === 'keuangan' && $password === 'uang123') {
        $passwordValid = true;
      }

      if ($divisi['id_divisi'] === $id_divisi && $email === $divisi['email'] && $passwordValid) {
        $user = $divisi;
        break;
      }
    }

    if ($user) {
      $_SESSION['id_user'] = md5($email);
      $_SESSION['nama'] = $user['nama_divisi'] . ' WeebeMart';
      $_SESSION['email'] = $email;
      $_SESSION['role'] = $user['slug'];
      $_SESSION['id_divisi'] = $user['id_divisi'];
      $_SESSION['nama_divisi'] = $user['nama_divisi'];

      header('Location: dashboard.php');
      exit;
    }

    $pesan = 'Email, password, atau peran tidak sesuai.';
  } else {
    $pesan = 'Email, password, dan peran wajib diisi.';
  }
}

if (isset($_GET['pesan']) && $_GET['pesan'] === 'logout') {
  $pesan = 'Anda sudah keluar dari sistem.';
}

if (isset($_GET['pesan']) && $_GET['pesan'] === 'login') {
  $pesan = 'Silakan login terlebih dahulu.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login WeebeMart</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="login-page login-front">
    <section class="login-info">
      <a class="brand login-brand" href="index.php">
        <span class="brand-icon">WM</span>
        <span>Weebe<span>Mart</span></span>
      </a>

      <p class="eyebrow"><span></span>Akses Sistem</p>
      <h1>Selamat Datang di WeebeMart</h1>
      <p>
        Masuk untuk membuka panel WeebeMart, mengelola karyawan, mencatat
        kehadiran, memeriksa gaji, dan melihat laporan kerja harian.
      </p>

      <div class="login-benefits">
        <article><span>01</span> Admin dapat mengelola data karyawan.</article>
        <article><span>02</span> Keuangan dapat melihat data absensi dan gaji.</article>
        <article><span>03</span> Akses cepat menuju dashboard WeebeMart.</article>
      </div>
    </section>

    <section class="login-card" aria-label="Form login pengguna">
      <h2>Masuk Dashboard</h2>
      <p>Gunakan akun WeebeMart yang tersedia untuk masuk.</p>

      <?php if ($pesan) : ?>
        <div class="notice <?= strpos($pesan, 'keluar') !== false ? 'success' : 'danger'; ?>"><?= e($pesan); ?></div>
      <?php endif; ?>

      <form method="post" action="index.php">
        <label for="role">Masuk sebagai</label>
        <select id="role" name="role" required>
          <option value="">Pilih divisi</option>
          <?php foreach ($divisiLogin as $divisi) : ?>
            <option value="<?= e($divisi['id_divisi']); ?>"><?= e($divisi['nama_divisi']); ?></option>
          <?php endforeach; ?>
        </select>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="operasional@weebemart.com" required>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="operasional123" required>

        <div class="form-row">
          <label class="check-label">
            <input type="checkbox">
            Ingat saya
          </label>
          <a href="#">Lupa password?</a>
        </div>

        <button class="login-button" type="submit">Masuk Dashboard</button>
      </form>
    </section>
  </main>
</body>
</html>
