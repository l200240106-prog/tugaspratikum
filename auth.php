<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['id_user'])) {
  header('Location: index.php?pesan=login');
  exit;
}

function role_dashboard_path($role = null) {
  $role = $role ?? ($_SESSION['role'] ?? 'admin');

  $pages = [
    'admin' => 'dashboard-admin.php',
    'keuangan' => 'dashboard-keuangan.php',
    'pergudangan' => 'absensi.php',
    'operasional' => 'absensi.php',
  ];

  return $pages[$role] ?? 'absensi.php';
}

function role_label($role = null) {
  $role = $role ?? ($_SESSION['role'] ?? 'admin');

  $labels = [
    'admin' => 'Admin',
    'keuangan' => 'Keuangan',
    'pergudangan' => 'Pergudangan',
    'operasional' => 'Operasional',
  ];

  return $labels[$role] ?? ucwords(str_replace('-', ' ', (string) $role));
}

function require_role($role) {
  if (($_SESSION['role'] ?? '') !== $role) {
    header('Location: ' . role_dashboard_path());
    exit;
  }
}

function require_roles(array $roles) {
  if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
    header('Location: ' . role_dashboard_path());
    exit;
  }
}

function is_admin() {
  return ($_SESSION['role'] ?? '') === 'admin';
}

function is_keuangan() {
  return ($_SESSION['role'] ?? '') === 'keuangan';
}

function can_manage_absensi($id_divisi = null) {
  if (is_admin()) {
    return true;
  }

  return is_keuangan() && (int) $id_divisi === (int) ($_SESSION['id_divisi'] ?? 0);
}

function nav_items($active = '') {
  $role = $_SESSION['role'] ?? '';
  $items = [
    'absensi.php' => 'Absensi',
  ];

  if ($role === 'admin') {
    $items = [
      'dashboard.php' => 'Beranda',
      'karyawan.php' => 'Karyawan',
      'absensi.php' => 'Absensi',
      'komponen-gaji.php' => 'Komponen',
      'gaji.php' => 'Gaji',
      'laporan.php' => 'Laporan',
    ];
  } elseif ($role === 'keuangan') {
    $items = [
      'dashboard.php' => 'Beranda',
      'absensi.php' => 'Absensi',
      'komponen-gaji.php' => 'Komponen',
      'gaji.php' => 'Gaji',
      'laporan.php' => 'Laporan',
    ];
  }

  foreach ($items as $href => $label) {
    $class = $href === $active ? ' class="active"' : '';
    echo '<li><a' . $class . ' href="' . e($href) . '">' . e($label) . '</a></li>';
  }
}
