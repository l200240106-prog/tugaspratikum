<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['id_user'])) {
  header('Location: /tugaspratikum/index.php?pesan=login');
  exit;
}

function getAppRole() {
  return $_SESSION['role'] ?? '';
}

function isAdmin() {
  return getAppRole() === 'admin';
}

function isFinance() {
  return getAppRole() === 'keuangan';
}

function isKaryawan() {
  $role = getAppRole();
  return $role !== 'admin' && $role !== 'keuangan';
}

function requireRole(array $allowedRoles) {
  if (!in_array(getAppRole(), $allowedRoles, true)) {
    header('Location: /tugaspratikum/pages/dashboard/dashboard.php?pesan=unauthorized');
    exit;
  }
}

function getNavItems(): array {
  $role = getAppRole();
  $items = [
    ['url' => '/tugaspratikum/pages/dashboard/dashboard.php', 'label' => 'Beranda', 'key' => 'dashboard'],
  ];

  if ($role === 'admin') {
    $items = array_merge($items, [
      ['url' => '/tugaspratikum/pages/karyawan/karyawan.php', 'label' => 'Karyawan', 'key' => 'karyawan'],
      ['url' => '/tugaspratikum/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
      ['url' => '/tugaspratikum/pages/penggajian/komponen-gaji.php', 'label' => 'Komponen', 'key' => 'komponen'],
      ['url' => '/tugaspratikum/pages/penggajian/gaji.php', 'label' => 'Gaji', 'key' => 'gaji'],
      ['url' => '/tugaspratikum/pages/laporan/laporan.php', 'label' => 'Laporan', 'key' => 'laporan'],
      ['url' => '/tugaspratikum/pages/keamanan/keamanan.php', 'label' => 'Keamanan', 'key' => 'keamanan'],
    ]);
  } elseif ($role === 'keuangan') {
    $items = array_merge($items, [
      ['url' => '/tugaspratikum/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
      ['url' => '/tugaspratikum/pages/penggajian/komponen-gaji.php', 'label' => 'Komponen', 'key' => 'komponen'],
      ['url' => '/tugaspratikum/pages/penggajian/gaji.php', 'label' => 'Gaji', 'key' => 'gaji'],
      ['url' => '/tugaspratikum/pages/laporan/laporan.php', 'label' => 'Laporan', 'key' => 'laporan'],
    ]);
  } else {
    $items = array_merge($items, [
      ['url' => '/tugaspratikum/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
    ]);
  }

  return $items;
}

function renderNavLinks(string $active = ''): string {
  $html = '';
  foreach (getNavItems() as $item) {
    $isActive = $item['key'] === $active ? 'active' : '';
    $html .= '<li><a class="' . $isActive . '" href="' . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</a></li>';
  }
  return $html;
}
