<?php
require 'koneksi.php';
require 'auth.php';

header('Location: ' . role_dashboard_path());
exit;
?>
