<?php
date_default_timezone_set('Asia/Makassar');

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sistem_penggajian';

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
