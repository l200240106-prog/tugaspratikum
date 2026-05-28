<?php
date_default_timezone_set('Asia/Makassar');

$host = 'sql200.infinityfree.com';
$user = 'if0_41988589';
$pass = 'lDEgsGfcNj4F';
$db = 'if0_41988589_absenkaryawan';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
  die('Koneksi database gagal: ' . mysqli_connect_error());
}

function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($value) {
  return 'Rp' . number_format((float) $value, 0, ',', '.');
}
