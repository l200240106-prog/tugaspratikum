<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['id_user'])) {
  header('Location: htdocs/index.php?pesan=login');
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

/**
 * Cek apakah user bisa mengakses data untuk divisi tertentu.
 * Admin selalu bisa. Jika bukan admin, hanya bisa akses jika `$_SESSION['id_divisi']` sama.
 * @param int $idDivisi
 * @return bool
 */
function canAccessDivision(int $idDivisi): bool {
  if (isAdmin()) return true;
  $sid = isset($_SESSION['id_divisi']) ? (int) $_SESSION['id_divisi'] : 0;
  return $sid > 0 && $sid === (int) $idDivisi;
}

/**
 * Require access to a division or redirect if denied.
 * @param int $idDivisi
 */
function requireDivision(int $idDivisi) {
  if (!canAccessDivision($idDivisi)) {
    header('Location: /tugaspratikum/pages/karyawan/karyawan.php?pesan=forbidden');
    exit;
  }
}

function requireRole(array $allowedRoles) {
  if (!in_array(getAppRole(), $allowedRoles, true)) {
    header('Location: /pages/dashboard/dashboard.php?pesan=unauthorized');
    exit;
  }
}

function getNavItems(): array {
  $role = getAppRole();
  $items = [
    ['url' => '/pages/dashboard/dashboard.php', 'label' => 'Beranda', 'key' => 'dashboard'],
  ];

  if ($role === 'admin') {
    $items = array_merge($items, [
      ['url' => '/pages/karyawan/karyawan.php', 'label' => 'Karyawan', 'key' => 'karyawan'],
      ['url' => '/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
      ['url' => '/pages/penggajian/komponen-gaji.php', 'label' => 'Komponen', 'key' => 'komponen'],
      ['url' => '/pages/penggajian/gaji.php', 'label' => 'Gaji', 'key' => 'gaji'],
      ['url' => '/pages/laporan/laporan.php', 'label' => 'Laporan', 'key' => 'laporan'],
    ]);
  } elseif ($role === 'keuangan') {
    $items = array_merge($items, [
      ['url' => '/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
      ['url' => '/pages/penggajian/komponen-gaji.php', 'label' => 'Komponen', 'key' => 'komponen'],
      ['url' => '/pages/penggajian/gaji.php', 'label' => 'Gaji', 'key' => 'gaji'],
      ['url' => '/pages/laporan/laporan.php', 'label' => 'Laporan', 'key' => 'laporan'],
    ]);
  } elseif ($role === 'operasional' || $role === 'pergudangan') {
    $items = array_merge($items, [
      ['url' => '/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
      ['url' => '/pages/penggajian/gaji.php', 'label' => 'Gaji', 'key' => 'gaji'],
    ]);
  } else {
    $items = array_merge($items, [
      ['url' => '/pages/absensi/absensi.php', 'label' => 'Absensi', 'key' => 'absensi'],
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
