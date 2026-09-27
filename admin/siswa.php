<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$pesan = '';
$pesan_error = '';

// 1. TAMBAH SISWA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_siswa'])) {
  $username     = mysqli_real_escape_string($koneksi, trim($_POST['username']));
  $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
  $password     = md5(trim($_POST['password']));

  $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
  if (mysqli_num_rows($cek) > 0) {
    $pesan_error = 'Username / NISN sudah terdaftar!';
  } else {
    $insert = mysqli_query($koneksi, "INSERT INTO users (username, nama_lengkap, password, role) VALUES ('$username', '$nama_lengkap', '$password', 'siswa')");
    if ($insert) {
      $pesan = 'Siswa baru berhasil ditambahkan!';
    }
  }
}

// 2. EDIT / UPDATE SISWA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_siswa'])) {
  $id_siswa     = (int)$_POST['id_siswa'];
  $username     = mysqli_real_escape_string($koneksi, trim($_POST['username']));
  $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
  $password     = trim($_POST['password']);

  // Cek apakah username sudah dipakai orang lain
  $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' AND id != $id_siswa");
  if (mysqli_num_rows($cek) > 0) {
    $pesan_error = 'Username / NISN sudah digunakan oleh siswa lain!';
  } else {
    if (!empty($password)) {
      // Jika kata sandi diisi baru
      $pass_hash = md5($password);
      $update = mysqli_query($koneksi, "UPDATE users SET username = '$username', nama_lengkap = '$nama_lengkap', password = '$pass_hash' WHERE id = $id_siswa AND role = 'siswa'");
    } else {
      // Jika kata sandi dibiarkan kosong (tidak diubah)
      $update = mysqli_query($koneksi, "UPDATE users SET username = '$username', nama_lengkap = '$nama_lengkap' WHERE id = $id_siswa AND role = 'siswa'");
    }

    if ($update) {
      $pesan = 'Data siswa berhasil diperbarui!';
    }
  }
}

// 3. HAPUS SISWA
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  mysqli_query($koneksi, "DELETE FROM users WHERE id = $id AND role = 'siswa'");
  header("Location: siswa.php");
  exit;
}

$list_siswa = mysqli_query($koneksi, "SELECT * FROM users WHERE role = 'siswa' ORDER BY id DESC");
$total_rows = mysqli_num_rows($list_siswa);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    try {
      document.documentElement.dataset.theme = localStorage.getItem('cbt-portal-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', document.documentElement.dataset.theme)
    } catch (e) {
      document.documentElement.dataset.theme = 'light';
      document.documentElement.setAttribute('data-bs-theme', 'light')
    }
  </script>
  <title>Data Siswa - CBT Portal</title>
  <!-- Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
    }

    .main-card {
      border: 1px solid #e2e8f0;
      border-radius: 1.25rem;
      background: #ffffff;
      box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05);
      overflow: hidden;
    }

    .table> :not(caption)>*>* {
      padding: 1rem 1.25rem;
      border-bottom-color: #f1f5f9;
    }

    .table thead th {
      background-color: #f8fafc;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #64748b;
      border-bottom: 1px solid #e2e8f0;
    }

    .user-avatar-initial {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      color: #2563eb;
      font-weight: 700;
      display: inline-grid;
      place-items: center;
      font-size: 0.9rem;
      flex-shrink: 0;
      border: 1px solid #bfdbfe;
    }

    .nisn-pill {
      background: #f1f5f9;
      color: #334155;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 0.3rem 0.65rem;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
    }

    .action-btn {
      width: 34px;
      height: 34px;
      padding: 0;
      display: inline-grid;
      place-items: center;
      border-radius: 10px;
      font-size: 0.88rem;
      transition: all 0.2s ease;
    }

    .action-btn:hover {
      transform: translateY(-2px);
    }

    .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 1.25rem;
    }

    .input-group-modern {
      position: relative;
    }

    .input-group-modern .form-control {
      padding-left: 2.6rem;
      border-radius: 0.75rem;
      border-color: #cbd5e1;
      font-size: 0.9rem;
    }

    .input-group-modern .form-control:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .input-icon-left {
      position: absolute;
      left: 0.9rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      z-index: 5;
      font-size: 1rem;
    }
  </style>
  <link rel="stylesheet" href="../assets/css/admin-responsive.css?v=3">
  <link rel="stylesheet" href="../assets/css/theme.css?v=1">
</head>

<body>

  <div class="d-flex">
    <?php include 'partials/navigation.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow-1 p-3 p-md-4" style="min-height: 100vh;">

      <!-- Topbar Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom admin-page-titlebar">
        <div>
          <h4 class="fw-bold mb-1 tracking-tight">Manajemen Data Siswa</h4>
          <p class="text-secondary small mb-0">Kelola akun dan kredensial login peserta ujian CBT</p>
        </div>
        <button class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
          <i class="bi bi-person-plus-fill me-1"></i> Tambah Siswa Baru
        </button>
      </div>

      <!-- Alerts -->
      <?php if (!empty($pesan)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 small border-0 shadow-sm d-flex align-items-center" role="alert">
          <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
          <div><?= htmlspecialchars($pesan) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 small border-0 shadow-sm d-flex align-items-center" role="alert">
          <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
          <div><?= htmlspecialchars($pesan_error) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Filter & Search Toolbar -->
      <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
        <div class="row g-2 align-items-center justify-content-between">
          <div class="col-md-5 col-lg-4">
            <div class="input-group-modern">
              <i class="bi bi-search input-icon-left"></i>
              <input type="text" id="cariSiswa" class="form-control" placeholder="Cari nama atau NISN siswa...">
            </div>
          </div>
          <div class="col-auto">
            <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
              <i class="bi bi-people me-1"></i> Total: <strong class="text-dark" id="totalCounter"><?= $total_rows ?></strong> Siswa
            </div>
          </div>
        </div>
      </div>

      <!-- Table Container -->
      <div class="main-card">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tabelSiswa">
            <thead>
              <tr>
                <th class="ps-4" style="width: 70px;">NO</th>
                <th>SISWA</th>
                <th>NISN / USERNAME</th>
                <th>TANGGAL REGISTRASI</th>
                <th class="text-end pe-4" style="width: 140px;">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($total_rows > 0): ?>
                <?php $no = 1;
                while ($s = mysqli_fetch_assoc($list_siswa)):
                  $initial = mb_strtoupper(mb_substr($s['nama_lengkap'] ?? 'S', 0, 1));
                ?>
                  <tr class="item-baris-siswa">
                    <td class="ps-4 text-muted small fw-semibold"><?= $no++ ?></td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar-initial"><?= htmlspecialchars($initial) ?></div>
                        <div>
                          <div class="fw-bold text-dark item-nama"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                          <small class="text-muted d-md-none"><?= htmlspecialchars($s['username']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="nisn-pill item-nisn"><?= htmlspecialchars($s['username']) ?></span>
                    </td>
                    <td>
                      <div class="small text-dark fw-medium">
                        <i class="bi bi-calendar-event me-1 text-muted"></i>
                        <?= date('d M Y', strtotime($s['created_at'])) ?>
                      </div>
                    </td>
                    <td class="text-end pe-4">
                      <!-- Tombol Edit Siswa -->
                      <button type="button"
                        class="btn btn-outline-warning action-btn me-1 btn-edit-siswa"
                        data-id="<?= $s['id'] ?>"
                        data-username="<?= htmlspecialchars($s['username']) ?>"
                        data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditSiswa"
                        aria-label="Edit data siswa"
                        title="Ubah Data Siswa">
                        <i class="bi bi-pencil-fill"></i>
                      </button>

                      <!-- Tombol Hapus Siswa -->
                      <button type="button"
                        class="btn btn-outline-danger action-btn btn-hapus-siswa"
                        data-id="<?= $s['id'] ?>"
                        data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                        data-username="<?= htmlspecialchars($s['username']) ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalHapusSiswa"
                        aria-label="Hapus siswa"
                        title="Hapus Siswa">
                        <i class="bi bi-trash3-fill"></i>
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="barisKosong">
                  <td colspan="5" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                      <span class="fw-semibold">Belum Ada Data Siswa</span>
                      <p class="small text-muted mb-0">Klik tombol "Tambah Siswa Baru" untuk membuat akun pertama.</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal Tambah Siswa -->
  <div class="modal fade" id="modalTambahSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header border-bottom-0 pb-1 px-4 pt-4">
          <div>
            <h5 class="fw-bold mb-1">Tambah Akun Siswa</h5>
            <p class="text-muted small mb-0">Daftarkan kredensial baru untuk peserta ujian</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="" method="POST">
          <div class="modal-body px-4 py-3">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">NISN / Username Login</label>
              <div class="input-group-modern">
                <i class="bi bi-person-badge input-icon-left"></i>
                <input type="text" name="username" class="form-control" placeholder="Contoh: 10293847" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Nama Lengkap Siswa</label>
              <div class="input-group-modern">
                <i class="bi bi-person input-icon-left"></i>
                <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Siti Rahmawati" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Kata Sandi Default</label>
              <div class="input-group-modern">
                <i class="bi bi-lock input-icon-left"></i>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
              </div>
              <small class="text-muted" style="font-size: 0.75rem;">Kata sandi ini digunakan siswa saat pertama kali login.</small>
            </div>
          </div>
          <div class="modal-footer border-top-0 px-4 pb-4 pt-1">
            <button type="button" class="btn btn-light rounded-3 px-3 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah_siswa" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
              <i class="bi bi-check2 me-1"></i> Simpan Siswa
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Siswa Modern -->
  <div class="modal fade" id="modalEditSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header border-bottom-0 pb-1 px-4 pt-4">
          <div>
            <h5 class="fw-bold mb-1">Ubah Informasi Siswa</h5>
            <p class="text-muted small mb-0">Perbarui profil atau reset kata sandi peserta</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="" method="POST">
          <input type="hidden" name="id_siswa" id="editIdSiswa">
          <div class="modal-body px-4 py-3">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">NISN / Username Login</label>
              <div class="input-group-modern">
                <i class="bi bi-person-badge input-icon-left"></i>
                <input type="text" name="username" id="editUsername" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Nama Lengkap Siswa</label>
              <div class="input-group-modern">
                <i class="bi bi-person input-icon-left"></i>
                <input type="text" name="nama_lengkap" id="editNamaLengkap" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Kata Sandi Baru (Opsional)</label>
              <div class="input-group-modern">
                <i class="bi bi-key input-icon-left"></i>
                <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
              </div>
              <small class="text-muted" style="font-size: 0.75rem;">*Kosongkan jika kata sandi tetap menggunakan yang lama.</small>
            </div>
          </div>
          <div class="modal-footer border-top-0 px-4 pb-4 pt-1">
            <button type="button" class="btn btn-light rounded-3 px-3 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit_siswa" class="btn btn-warning text-dark fw-semibold rounded-3 px-4 shadow-sm">
              <i class="bi bi-check2 me-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include 'modal/modal_logout.php'; ?>
  <?php include 'modal/modal_hapus_siswa.php'; ?>

  <script>
    // Handler Modal Edit Data
    document.querySelectorAll('.btn-edit-siswa').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('editIdSiswa').value = this.getAttribute('data-id');
        document.getElementById('editUsername').value = this.getAttribute('data-username');
        document.getElementById('editNamaLengkap').value = this.getAttribute('data-nama');
      });
    });

    // Handler Modal Hapus Data
    document.querySelectorAll('.btn-hapus-siswa').forEach(button => {
      button.addEventListener('click', function() {
        const idSiswa = this.getAttribute('data-id');
        const namaSiswa = this.getAttribute('data-nama');
        const usernameSiswa = this.getAttribute('data-username');

        document.getElementById('teksNamaSiswa').innerText = namaSiswa;
        document.getElementById('teksUsernameSiswa').innerText = usernameSiswa;
        document.getElementById('btnEksekusiHapusSiswa').setAttribute('href', 'siswa.php?hapus=' + idSiswa);
      });
    });

    // Quick Client-Side Search Filter
    const searchInput = document.getElementById('cariSiswa');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.item-baris-siswa');
        let visibleCount = 0;

        rows.forEach(row => {
          const name = row.querySelector('.item-nama').innerText.toLowerCase();
          const nisn = row.querySelector('.item-nisn').innerText.toLowerCase();
          if (name.includes(query) || nisn.includes(query)) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        const counter = document.getElementById('totalCounter');
        if (counter) counter.innerText = visibleCount;
      });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>