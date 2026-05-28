<?php
require '../../config/koneksi.php';
/** @var mysqli $koneksi */
require '../auth/auth.php';
requireRole(['admin', 'keuangan']);

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form = $_POST['form'] ?? 'tambah';

  $id_divisi = (int) ($_POST['id_divisi'] ?? 0);
  $gaji_pokok = (float) ($_POST['gaji_pokok'] ?? 0);
  $tunjangan = (float) ($_POST['tunjangan'] ?? 0);
  $bonus = (float) ($_POST['bonus'] ?? 0);
  $uang_makan = (float) ($_POST['uang_makan'] ?? 0);
  $lembur = (float) ($_POST['lembur'] ?? 0);
  $potongan_pajak = (float) ($_POST['potongan_pajak'] ?? 0);
  $bpjs = (float) ($_POST['bpjs'] ?? 0);

  // ── Hapus Komponen Gaji ──
  if (isset($_POST['hapus_komponen'])) {
    $id_komponen = (int) $_POST['id_komponen'];
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM komponen_gaji WHERE id_komponen = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id_komponen);
    mysqli_stmt_execute($stmt);
    header('Location: komponen-gaji.php?status=dihapus');
    exit;
  }

  // ── Edit Komponen Gaji (UPDATE) ──
  if ($form === 'edit_komponen') {
    $id_komponen = (int) ($_POST['id_komponen'] ?? 0);

    if ($id_komponen && $id_divisi && $gaji_pokok >= 0) {
      $stmt = mysqli_prepare(
        $koneksi,
        'UPDATE komponen_gaji SET id_divisi = ?, gaji_pokok = ?, tunjangan = ?, bonus = ?, uang_makan = ?, lembur = ?, potongan_pajak = ?, bpjs = ? WHERE id_komponen = ?'
      );
      mysqli_stmt_bind_param($stmt, 'idddddddi', $id_divisi, $gaji_pokok, $tunjangan, $bonus, $uang_makan, $lembur, $potongan_pajak, $bpjs, $id_komponen);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: komponen-gaji.php?status=diperbarui');
        exit;
      }

      $pesan = 'Komponen gaji gagal diperbarui.';
    } else {
      $pesan = 'Pilih divisi dan isi gaji pokok.';
    }
  }

  // ── Tambah Komponen Gaji (INSERT) ──
  if (!$pesan && $form === 'tambah') {
    if ($id_divisi && $gaji_pokok >= 0) {
      $stmt = mysqli_prepare(
        $koneksi,
        'INSERT INTO komponen_gaji (id_divisi, gaji_pokok, tunjangan, bonus, uang_makan, lembur, potongan_pajak, bpjs)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
      );
      mysqli_stmt_bind_param($stmt, 'iddddddd', $id_divisi, $gaji_pokok, $tunjangan, $bonus, $uang_makan, $lembur, $potongan_pajak, $bpjs);

      if (mysqli_stmt_execute($stmt)) {
        header('Location: komponen-gaji.php?status=ditambahkan');
        exit;
      }

      $pesan = 'Komponen gaji gagal disimpan.';
    } else {
      $pesan = 'Pilih divisi dan isi gaji pokok.';
    }
  }
}

$divisi = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY nama_divisi');

// Simpan daftar divisi ke array untuk dropdown di modal
$divisiArr = [];
$divisiForModal = mysqli_query($koneksi, 'SELECT * FROM divisi ORDER BY nama_divisi');
while ($d = mysqli_fetch_assoc($divisiForModal)) {
  $divisiArr[] = $d;
}

$komponen = mysqli_query(
  $koneksi,
  'SELECT komponen_gaji.*, divisi.nama_divisi
   FROM komponen_gaji
   LEFT JOIN divisi ON divisi.id_divisi = komponen_gaji.id_divisi
   ORDER BY komponen_gaji.id_komponen DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Komponen Gaji per Divisi</title>
  <link rel="icon" href="/assets/weebemart.ico">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar">
      <a class="brand" href="/pages/dashboard/dashboard.php"><span class="brand-icon">WM</span><span>Weebe<span>Mart</span></span></a>
      <ul class="nav-links">
        <?= renderNavLinks('komponen'); ?>
      </ul>
      <a class="nav-button" href="/pages/auth/logout.php">Keluar</a>
    </nav>
  </header>

  <!-- Toast Container -->
  <div class="toast-container" id="toastContainer"></div>

  <main class="page">
    <section class="page-hero">
      <p class="eyebrow"><span></span>Komponen Gaji</p>
      <h1>Atur Komponen Gaji per Divisi</h1>
      <p>Komponen gaji ditentukan berdasarkan divisi. Semua karyawan dalam divisi yang sama otomatis mengikuti komponen gaji divisinya.</p>
    </section>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'ditambahkan') : ?>
      <div class="notice success">Komponen gaji berhasil ditambahkan.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'diperbarui') : ?>
      <div class="notice success">Komponen gaji berhasil diperbarui.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'dihapus') : ?>
      <div class="notice success">Komponen gaji berhasil dihapus.</div>
    <?php elseif ($pesan) : ?>
      <div class="notice danger"><?= e($pesan); ?></div>
    <?php endif; ?>

    <!-- ═══ Form Tambah Komponen ═══ -->
    <section class="content-card form-card" id="tambah-komponen">
      <div class="section-heading">
        <h2>Tambah Komponen Gaji</h2>
      </div>
      <form class="data-form" method="post" action="komponen-gaji.php">
        <input type="hidden" name="form" value="tambah">
        <label>Divisi
          <select name="id_divisi" required>
            <option value="">Pilih divisi</option>
            <?php while ($row = mysqli_fetch_assoc($divisi)) : ?>
              <option value="<?= e($row['id_divisi']); ?>"><?= e($row['nama_divisi']); ?></option>
            <?php endwhile; ?>
          </select>
        </label>
        <label>Gaji Pokok
          <input name="gaji_pokok" type="number" min="0" step="1000" value="0" required>
        </label>
        <label>Tunjangan
          <input name="tunjangan" type="number" min="0" step="1000" value="0">
        </label>
        <label>Bonus
          <input name="bonus" type="number" min="0" step="1000" value="0">
        </label>
        <label>Uang Makan
          <input name="uang_makan" type="number" min="0" step="1000" value="0">
        </label>
        <label>Lembur
          <input name="lembur" type="number" min="0" step="1000" value="0">
        </label>
        <label>Potongan Pajak
          <input name="potongan_pajak" type="number" min="0" step="1000" value="0">
        </label>
        <label>BPJS
          <input name="bpjs" type="number" min="0" step="1000" value="0">
        </label>
        <button class="small-button form-submit" type="submit">Simpan Komponen</button>
      </form>
    </section>

    <!-- ═══ Tabel Komponen Gaji ═══ -->
    <section class="content-card">
      <div class="section-heading">
        <h2>Data Komponen Gaji per Divisi</h2>
        <a class="small-button" href="#tambah-komponen">+ Tambah Komponen</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Divisi</th>
              <th>Gaji Pokok</th>
              <th>Tunjangan</th>
              <th>Bonus</th>
              <th>Uang Makan</th>
              <th>Lembur</th>
              <th>Pajak</th>
              <th>BPJS</th>
              <th>Total Bersih</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($komponen)) :
              $totalPendapatan = (float)$row['gaji_pokok'] + (float)$row['tunjangan'] + (float)$row['bonus'] + (float)$row['uang_makan'] + (float)$row['lembur'];
              $totalPotongan = (float)$row['potongan_pajak'] + (float)$row['bpjs'];
              $totalBersih = $totalPendapatan - $totalPotongan;
            ?>
              <tr>
                <td><?= e($row['nama_divisi'] ?? '-'); ?></td>
                <td><?= e(rupiah($row['gaji_pokok'])); ?></td>
                <td><?= e(rupiah($row['tunjangan'])); ?></td>
                <td><?= e(rupiah($row['bonus'])); ?></td>
                <td><?= e(rupiah($row['uang_makan'])); ?></td>
                <td><?= e(rupiah($row['lembur'])); ?></td>
                <td><?= e(rupiah($row['potongan_pajak'])); ?></td>
                <td><?= e(rupiah($row['bpjs'])); ?></td>
                <td><strong><?= e(rupiah($totalBersih)); ?></strong></td>
                <td>
                  <button type="button" class="btn-edit" onclick='openEditGaji(<?= json_encode([
                    "id_komponen"    => (int)$row["id_komponen"],
                    "id_divisi"      => (int)$row["id_divisi"],
                    "nama_divisi"    => $row["nama_divisi"] ?? "-",
                    "gaji_pokok"     => (float)$row["gaji_pokok"],
                    "tunjangan"      => (float)$row["tunjangan"],
                    "bonus"          => (float)$row["bonus"],
                    "uang_makan"     => (float)$row["uang_makan"],
                    "lembur"         => (float)$row["lembur"],
                    "potongan_pajak" => (float)$row["potongan_pajak"],
                    "bpjs"           => (float)$row["bpjs"],
                  ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                  <button type="button" class="btn-hapus" onclick="hapusKomponen(<?= (int)$row['id_komponen'] ?>)">Hapus</button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- ═══════════════════════════════════════════════════ -->
  <!-- ═══ MODAL: Edit Komponen Gaji Divisi ═══ -->
  <!-- ═══════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="modalEditGaji">
    <div class="modal">
      <div class="modal-header">
        <h2>Edit Komponen Gaji — <span id="modalGajiDivisiName"></span></h2>
        <button type="button" class="modal-close" onclick="closeModal('modalEditGaji')">&times;</button>
      </div>
      <form method="post" action="komponen-gaji.php" id="formEditGaji">
        <input type="hidden" name="form" value="edit_komponen">
        <input type="hidden" name="id_komponen" id="edit_id_komponen">
        <div class="modal-body">
          <div class="modal-form">
            <label>Divisi
              <select name="id_divisi" id="edit_gaji_id_divisi" required>
                <option value="">Pilih divisi</option>
                <?php foreach ($divisiArr as $d): ?>
                  <option value="<?= e($d['id_divisi']) ?>"><?= e($d['nama_divisi']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Gaji Pokok
              <input type="number" name="gaji_pokok" id="edit_gaji_pokok" min="0" step="1000" required oninput="hitungTotal()">
            </label>
            <label>Tunjangan
              <input type="number" name="tunjangan" id="edit_tunjangan" min="0" step="1000" oninput="hitungTotal()">
            </label>
            <label>Bonus
              <input type="number" name="bonus" id="edit_bonus" min="0" step="1000" oninput="hitungTotal()">
            </label>
            <label>Uang Makan
              <input type="number" name="uang_makan" id="edit_uang_makan" min="0" step="1000" oninput="hitungTotal()">
            </label>
            <label>Lembur
              <input type="number" name="lembur" id="edit_lembur" min="0" step="1000" oninput="hitungTotal()">
            </label>
            <label>Potongan Pajak
              <input type="number" name="potongan_pajak" id="edit_potongan_pajak" min="0" step="1000" oninput="hitungTotal()">
            </label>
            <label>BPJS
              <input type="number" name="bpjs" id="edit_bpjs" min="0" step="1000" oninput="hitungTotal()">
            </label>
            <div class="total-row">
              <span>Total Gaji Bersih</span>
              <span class="total-value" id="totalGajiBersih">Rp0</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" onclick="closeModal('modalEditGaji')">Batal</button>
          <button type="submit" class="btn btn-save" id="btnSaveGaji">
            <span class="spinner"></span>
            <span class="btn-text">Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // ── Utilitas ──
    function closeModal(id) {
      document.getElementById(id).classList.remove('active');
    }

    function formatRupiah(angka) {
      return 'Rp' + Math.round(angka).toLocaleString('id-ID');
    }

    // ── Hitung Total Gaji Realtime ──
    function hitungTotal() {
      const get = (id) => parseFloat(document.getElementById(id).value) || 0;
      const pendapatan = get('edit_gaji_pokok') + get('edit_tunjangan') + get('edit_bonus') + get('edit_uang_makan') + get('edit_lembur');
      const potongan = get('edit_potongan_pajak') + get('edit_bpjs');
      const bersih = pendapatan - potongan;
      document.getElementById('totalGajiBersih').textContent = formatRupiah(bersih);
    }

    // ── Open Edit Gaji Modal ──
    function openEditGaji(data) {
      document.getElementById('edit_id_komponen').value = data.id_komponen;
      document.getElementById('edit_gaji_id_divisi').value = data.id_divisi;
      document.getElementById('modalGajiDivisiName').textContent = data.nama_divisi;
      document.getElementById('edit_gaji_pokok').value = data.gaji_pokok;
      document.getElementById('edit_tunjangan').value = data.tunjangan;
      document.getElementById('edit_bonus').value = data.bonus;
      document.getElementById('edit_uang_makan').value = data.uang_makan;
      document.getElementById('edit_lembur').value = data.lembur;
      document.getElementById('edit_potongan_pajak').value = data.potongan_pajak;
      document.getElementById('edit_bpjs').value = data.bpjs;
      hitungTotal();
      document.getElementById('modalEditGaji').classList.add('active');
    }

    // ── Hapus Komponen ──
    function hapusKomponen(id) {
      if (confirm('Hapus komponen gaji ini?')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = 'komponen-gaji.php';
        f.innerHTML = '<input type="hidden" name="hapus_komponen" value="1"><input type="hidden" name="id_komponen" value="' + id + '">';
        document.body.appendChild(f);
        f.submit();
      }
    }

    // ── Loading state saat submit ──
    document.getElementById('formEditGaji')?.addEventListener('submit', function() {
      const btn = document.getElementById('btnSaveGaji');
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
