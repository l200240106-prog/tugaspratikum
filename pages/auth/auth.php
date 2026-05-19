<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['id_user'])) {
  header('Location: ../../index.php?pesan=login');
  exit;
}
