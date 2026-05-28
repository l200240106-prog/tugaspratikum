<?php
require '../../config/koneksi.php';
/** @var mysqli $koneksi */
require '../auth/auth.php';

$pesan = '';
$pesanType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form = $_POST['form'] ?? 'karyawan';

  // ── Hapus Divisi ──
  if (isset($_POST['hapus_divisi'])) {
    if (isAdmin()) {
      $id = (int) $_POST['id_divisi'];
      $stmt = mysqli_prepare($koneksi, 'DELETE FROM divisi WHERE id_divisi = ?');
      mysqli_stmt_bind_param($stmt, 'i', $id);
      mysqli_stmt_execute($stmt);
      header('Location: karyawan.php?status=divisi_dihapus');
      exit;
    }
  }

  // ── Hapus Karyawan ──
  if (isset($_POST['hapus_karyawan'])) {
    if (isAdmin()) {
      $id = (int) $_POST['id_karyawan'];
      $stmt = mysqli_prepare($koneksi, 'DELETE FROM karyawan WHERE id_karyawan = ?');
      mysqli_stmt_bind_param($stmt, 'i', $id);
      mysqli_stmt_execute($stmt);
      header('Location: karyawan.php?status=dihapus');
      exit;
    }
  }

  // ── Tambah Divisi ──
  if ($form === 'divisi') {
    if (!isAdmin()) {
      $pesan = 'Akses ditolak: hanya admin dapat mengelola divisi.';
    } else {
      $nama_divisi = trim($_POST['nama_divisi'] ?? '');

      if ($nama_divisi) {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO divisi (nama_divisi) VALUES (?)');
        mysqli_stmt_bind_param($stmt, 's', $nama_divisi);

        if (mysqli_stmt_execute($stmt)) {
          header('Location: karyawan.php?status=divisi');
          exit;
        }

        $pesan = 'Data divisi gagal disimpan.';
      } else {
        $pesan = 'Nama divisi wajib diisi.';
      }
    }
  }

  // ── Tambah Karyawan ──
  if (!$pesan && $form === 'karyawan') {
    $nama = trim($_POST['nama'] ?? '');
    $id_divisi = (int) ($_POST['id_divisi'] ?? 0);
    $no_hp = trim($_POST['no_hp'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? date('Y-m-d');
    $status_kerja = $_POST['status_kerja'] ?? 'Tetap';

    if (!isAdmin()) {
      $id_divisi = isset($_SESSION['id_divisi']) ? (int) $_SESSION['id_divisi'] : 0;
    }

    if ($nama && $id_divisi && $jabatan && $tanggal_masuk) {
      requireDivision($id_divisi);

      $stmt = mysqli_prepare($koneksi, 'INSERT INTO karyawan (nama, no_hp, jabatan, alamat, tanggal_masuk, status_kerja, id_divisi) VALUES (?, ?, ?, ?, ?, ?, ?)');
      mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $no_hp, $jabatan, $alamat, $tanggal_masuk, $status_kerja, $id_divisi);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: karyawan.php?status=ditambahkan');
        exit;
      }
      $pesan = 'Data karyawan gagal ditambahkan.';
    } else {
      $pesan = 'Lengkapi nama, divisi, jabatan, dan tanggal masuk.';
    }
  }

  // ── Edit Karyawan (dari modal) ──
  if (!$pesan && $form === 'edit_karyawan') {
    $id_karyawan = (int) ($_POST['id_karyawan'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $id_divisi = (int) ($_POST['id_divisi'] ?? 0);
    $no_hp = trim($_POST['no_hp'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';
    $status_kerja = $_POST['status_kerja'] ?? 'Tetap';

    if (!isAdmin()) {
      $id_divisi = isset($_SESSION['id_divisi']) ? (int) $_SESSION['id_divisi'] : 0;
    }

    if ($id_karyawan && $nama && $id_divisi && $jabatan && $tanggal_masuk) {
      requireDivision($id_divisi);

      $stmt = mysqli_prepare($koneksi, 'UPDATE karyawan SET nama = ?, no_hp = ?, jabatan = ?, alamat = ?, tanggal_masuk = ?, status_kerja = ?, id_divisi = ? WHERE id_karyawan = ?');
      mysqli_stmt_bind_param($stmt, 'ssssssii', $nama, $no_hp, $jabatan, $alamat, $tanggal_masuk, $status_kerja, $id_divisi, $id_karyawan);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: karyawan.php?status=diperbarui');
        exit;
      }
      $pesan = 'Data karyawan gagal diperbarui.';
    } else {
      $pesan = 'Lengkapi semua field yang wajib diisi.';
    }
  }
}

// ── Query Data ──
$divisi = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY nama_divisi');
$daftarDivisi = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY id_divisi DESC');

// Simpan daftar divisi ke array untuk digunakan di modal dropdown
$divisiArr = [];
$divisiDropdown = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY nama_divisi');
while ($d = mysqli_fetch_assoc($divisiDropdown)) {
  $divisiArr[] = $d;
}

if (isAdmin()) {
  $karyawan = mysqli_query(
    $koneksi,
    'SELECT karyawan.*, divisi.nama_divisi
     FROM karyawan
     LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
     ORDER BY karyawan.id_karyawan DESC'
  );
} else {
  $sid = isset($_SESSION['id_divisi']) ? (int) $_SESSION['id_divisi'] : 0;
  $karyawan = mysqli_prepare(
    $koneksi,
    'SELECT karyawan.*, divisi.nama_divisi
     FROM karyawan
     LEFT JOIN divisi ON divisi.id_divisi = karyawan.id_divisi
     WHERE karyawan.id_divisi = ?
     ORDER BY karyawan.id_karyawan DESC'
  );
  mysqli_stmt_bind_param($karyawan, 'i', $sid);
  mysqli_stmt_execute($karyawan);
  $karyawan = mysqli_stmt_get_result($karyawan);
  // Filter daftarDivisi untuk non-admin supaya hanya menampilkan satu divisi pada UI
  $daftarDivisi = mysqli_query($koneksi, "SELECT * FROM divisi WHERE id_divisi = " . $sid . " ORDER BY id_divisi DESC");
  $divisi = mysqli_query($koneksi, "SELECT * FROM divisi WHERE id_divisi = " . $sid . " ORDER BY nama_divisi");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Karyawan</title>
  <link rel="icon" href="/assets/weebemart.ico">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/pages/dashboard/dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?= renderNavLinks('karyawan'); ?>
      </ul>
      <a class="nav-button" href="/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <!-- Toast Container -->
  <div class="toast-container" id="toastContainer"></div>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Data Karyawan</p>
      <h1>Kelola Karyawan dan Divisi</h1>
      <p>Simpan identitas, kontak, jabatan, alamat, tanggal masuk, status kerja, dan divisi karyawan.</p>
    </section>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'ditambahkan') : ?>
      <div class="notice success">Data karyawan berhasil ditambahkan.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'diperbarui') : ?>
      <div class="notice success">Data karyawan berhasil diperbarui.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'dihapus') : ?>
      <div class="notice success">Data karyawan berhasil dihapus.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'divisi') : ?>
      <div class="notice success">Data divisi berhasil disimpan.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'divisi_dihapus') : ?>
      <div class="notice success">Data divisi berhasil dihapus.</div>
    <?php elseif ($pesan) : ?>
      <div class="notice danger"><?= e($pesan); ?></div>
    <?php endif; ?>

    <!-- ═══ Form Tambah Divisi ═══ -->
    <?php if (isAdmin()) : ?>
    <section class="content-card form-card" id="tambah-divisi">
      <div class="section-heading">
        <h2 id="divisi-form-title">Tambah Divisi</h2>
      </div>
      <form class="data-form" method="post" action="karyawan.php" id="formDivisi">
        <input type="hidden" name="form" value="divisi" id="divisi-form-type">
        <label>Nama Divisi
          <input name="nama_divisi" id="divisi-nama" type="text" placeholder="Contoh: Operasional" required>
        </label>
        <button class="small-button form-submit" type="submit" id="divisi-submit-btn">Simpan Divisi</button>
      </form>
    </section>
    <?php endif; ?>

    <!-- ═══ Form Tambah Karyawan ═══ -->
    <section class="content-card form-card" id="tambah-karyawan">
      <div class="section-heading">
        <h2>Tambah Karyawan</h2>
      </div>
      <form class="data-form" method="post" action="karyawan.php">
        <input type="hidden" name="form" value="karyawan">
        <label>Nama Karyawan
          <input name="nama" type="text" placeholder="Nama lengkap" required>
        </label>
        <label>Divisi
          <?php if (isAdmin()) : ?>
            <select name="id_divisi" required>
              <option value="">Pilih divisi</option>
              <?php while ($row = mysqli_fetch_assoc($divisi)) : ?>
                <option value="<?= e($row['id_divisi']); ?>"><?= e($row['nama_divisi']); ?></option>
              <?php endwhile; ?>
            </select>
          <?php else :
            $myDiv = mysqli_fetch_assoc($divisi);
          ?>
            <input type="text" value="<?= e($myDiv['nama_divisi'] ?? '-'); ?>" disabled>
            <input type="hidden" name="id_divisi" value="<?= e($myDiv['id_divisi'] ?? ''); ?>">
          <?php endif; ?>
        </label>
        <label>No HP
          <input name="no_hp" type="text" placeholder="08xxxxxxxxxx">
        </label>
        <label>Jabatan
          <input name="jabatan" type="text" placeholder="Staff Operasional" required>
        </label>
        <label>Tanggal Masuk
          <input name="tanggal_masuk" type="date" value="<?= e(date('Y-m-d')); ?>" required>
        </label>
        <label>Status Kerja
          <select name="status_kerja" required>
            <option value="Tetap">Tetap</option>
            <option value="Kontrak">Kontrak</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </label>
        <label class="form-wide">Alamat
          <textarea name="alamat" rows="3" placeholder="Alamat karyawan"></textarea>
        </label>
        <button class="small-button form-submit" type="submit">Simpan Karyawan</button>
      </form>
    </section>

    <!-- ═══ Tabel Divisi ═══ -->
    <section class="content-card">
      <div class="section-heading">
        <h2>Data Divisi</h2>
        <?php if (isAdmin()) : ?>
          <a class="small-button" href="#tambah-divisi">+ Tambah Divisi</a>
        <?php endif; ?>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Divisi</th>
              <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($daftarDivisi)) : ?>
              <tr>
                <td><?= e($row['id_divisi']); ?></td>
                <td><?= e($row['nama_divisi']); ?></td>
                <?php if (isAdmin()): ?>
                <td>
                  <button type="button" class="btn-hapus" onclick="hapusDivisi(<?= (int)$row['id_divisi'] ?>)">Hapus</button>
                </td>
                <?php endif; ?>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ═══ Tabel Karyawan ═══ -->
    <section class="content-card">
      <div class="section-heading">
        <h2>Data Karyawan</h2>
        <a class="small-button" href="#tambah-karyawan">+ Tambah Karyawan</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Divisi</th>
              <th>Jabatan</th>
              <th>No HP</th>
              <th>Tanggal Masuk</th>
              <th>Status</th>
              <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($karyawan)) : ?>
              <?php
                $statusClass = match($row['status_kerja']) {
                  'Tetap' => 'success',
                  'Kontrak' => 'warning',
                  default => 'danger',
                };
              ?>
              <tr>
                <td><?= e($row['nama']); ?></td>
                <td><?= e($row['nama_divisi']); ?></td>
                <td><?= e($row['jabatan']); ?></td>
                <td><?= e($row['no_hp']); ?></td>
                <td><?= e($row['tanggal_masuk'] ? date('d M Y', strtotime($row['tanggal_masuk'])) : '-'); ?></td>
                <td><span class="badge <?= e($statusClass); ?>"><?= e($row['status_kerja']); ?></span></td>
                <?php if (isAdmin()): ?>
                <td>
                  <button type="button" class="btn-edit" onclick='openEditKaryawan(<?= json_encode([
                    "id_karyawan" => (int)$row["id_karyawan"],
                    "nama"        => $row["nama"],
                    "id_divisi"   => (int)$row["id_divisi"],
                    "no_hp"       => $row["no_hp"],
                    "jabatan"     => $row["jabatan"],
                    "alamat"      => $row["alamat"],
                    "tanggal_masuk" => $row["tanggal_masuk"],
                    "status_kerja"  => $row["status_kerja"],
                  ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                  <button type="button" class="btn-hapus" onclick="hapusKaryawan(<?= (int)$row['id_karyawan'] ?>)">Hapus</button>
                </td>
                <?php endif; ?>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- ═══════════════════════════════════════════════════ -->
  <!-- ═══ MODAL: Edit Karyawan ═══ -->
  <!-- ═══════════════════════════════════════════════════ -->
  <?php if (isAdmin()): ?>
  <div class="modal-overlay" id="modalEditKaryawan">
    <div class="modal">
      <div class="modal-header">
        <h2>Edit Data Karyawan</h2>
        <button type="button" class="modal-close" onclick="closeModal('modalEditKaryawan')">&times;</button>
      </div>
      <form method="post" action="karyawan.php" id="formEditKaryawan">
        <input type="hidden" name="form" value="edit_karyawan">
        <input type="hidden" name="id_karyawan" id="edit_id_karyawan">
        <div class="modal-body">
          <div class="modal-form">
            <label>Nama Karyawan
              <input type="text" name="nama" id="edit_nama" placeholder="Nama lengkap" required>
            </label>
            <label>Divisi
              <select name="id_divisi" id="edit_id_divisi" required>
                <option value="">Pilih divisi</option>
                <?php foreach ($divisiArr as $d): ?>
                  <option value="<?= e($d['id_divisi']) ?>"><?= e($d['nama_divisi']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>No HP
              <input type="text" name="no_hp" id="edit_no_hp" placeholder="08xxxxxxxxxx">
            </label>
            <label>Jabatan
              <input type="text" name="jabatan" id="edit_jabatan" placeholder="Staff Operasional" required>
            </label>
            <label>Tanggal Masuk
              <input type="date" name="tanggal_masuk" id="edit_tanggal_masuk" required>
            </label>
            <label>Status Kerja
              <select name="status_kerja" id="edit_status_kerja" required>
                <option value="Tetap">Tetap</option>
                <option value="Kontrak">Kontrak</option>
                <option value="Nonaktif">Nonaktif</option>
              </select>
            </label>
            <label class="form-wide">Alamat
              <textarea name="alamat" id="edit_alamat" rows="3" placeholder="Alamat karyawan"></textarea>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" onclick="closeModal('modalEditKaryawan')">Batal</button>
          <button type="submit" class="btn btn-save" id="btnSaveKaryawan">
            <span class="spinner"></span>
            <span class="btn-text">Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script>
    // ── Utilitas ──
    function closeModal(id) {
      document.getElementById(id).classList.remove('active');
    }

    function showToast(message, type) {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = 'toast ' + type;
      toast.textContent = message;
      container.appendChild(toast);
      setTimeout(() => toast.remove(), 3600);
    }

    // ── Divisi CRUD ──

    function hapusDivisi(id) {
      if (confirm('Hapus divisi ini? Semua karyawan di divisi ini akan kehilangan relasinya.')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = 'karyawan.php';
        f.innerHTML = '<input type="hidden" name="hapus_divisi" value="1"><input type="hidden" name="id_divisi" value="' + id + '">';
        document.body.appendChild(f);
        f.submit();
      }
    }

    // ── Karyawan CRUD ──
    function openEditKaryawan(data) {
      document.getElementById('edit_id_karyawan').value = data.id_karyawan;
      document.getElementById('edit_nama').value = data.nama;
      document.getElementById('edit_id_divisi').value = data.id_divisi;
      document.getElementById('edit_no_hp').value = data.no_hp || '';
      document.getElementById('edit_jabatan').value = data.jabatan;
      document.getElementById('edit_alamat').value = data.alamat || '';
      document.getElementById('edit_tanggal_masuk').value = data.tanggal_masuk;
      document.getElementById('edit_status_kerja').value = data.status_kerja;
      document.getElementById('modalEditKaryawan').classList.add('active');
    }

    function hapusKaryawan(id) {
      if (confirm('Hapus data karyawan ini?')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = 'karyawan.php';
        f.innerHTML = '<input type="hidden" name="hapus_karyawan" value="1"><input type="hidden" name="id_karyawan" value="' + id + '">';
        document.body.appendChild(f);
        f.submit();
      }
    }

    // ── Loading state saat submit ──
    document.getElementById('formEditKaryawan')?.addEventListener('submit', function() {
      const btn = document.getElementById('btnSaveKaryawan');
      btn.classList.add('loading');
      btn.disabled = true;
    });

    // ── Klik overlay untuk tutup modal ──
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
      });
    });

    // ── Escape untuk tutup modal ──
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
      }
    });
  </script>
</body>
</html>
