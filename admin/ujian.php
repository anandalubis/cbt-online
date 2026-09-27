<?php
session_start();
require_once '../config/database.php';
global $koneksi;

// Proteksi Halaman Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$pesan = '';

// Proses Tambah Ujian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_ujian'])) {
  $judul_ujian  = mysqli_real_escape_string($koneksi, trim($_POST['judul_ujian']));
  $durasi_menit = (int)$_POST['durasi_menit'];
  $token        = strtoupper(mysqli_real_escape_string($koneksi, trim($_POST['token'])));

  $query = mysqli_query($koneksi, "INSERT INTO exams (judul_ujian, durasi_menit, token, status) VALUES ('$judul_ujian', '$durasi_menit', '$token', 'aktif')");
  if ($query) {
    $pesan = 'Paket ujian baru berhasil ditambahkan!';
  }
}

// Proses Hapus Ujian
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  mysqli_query($koneksi, "DELETE FROM exams WHERE id = $id");
  header("Location: ujian.php");
  exit;
}

// Mengambil Data Seluruh Ujian
$list_ujian = mysqli_query($koneksi, "SELECT e.*, (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) AS total_soal FROM exams e ORDER BY e.id DESC");
$total_rows = mysqli_num_rows($list_ujian);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Paket Ujian - CBT Portal</title>
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
      padding: 1.05rem 1.25rem;
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

    .token-badge {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-weight: 700;
      font-size: 0.85rem;
      letter-spacing: 0.08em;
      padding: 0.35rem 0.75rem;
      background: #f1f5f9;
      color: #0f172a;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.28rem 0.7rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    .status-badge.active {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }

    .status-badge.inactive {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }

    .status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: currentColor;
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

    .exam-icon-box {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: #eff6ff;
      color: #2563eb;
      font-size: 1.15rem;
      display: grid;
      place-items: center;
      flex-shrink: 0;
    }
  </style>
  <link rel="stylesheet" href="../assets/css/admin-responsive.css?v=3">
</head>

<body>

  <div class="d-flex">
    <?php include 'partials/navigation.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-grow-1 p-3 p-md-4" style="min-height: 100vh;">

      <!-- Topbar Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom admin-page-titlebar">
        <div>
          <h4 class="fw-bold mb-1 tracking-tight">Manajemen Paket Ujian</h4>
          <p class="text-secondary small mb-0">Konfigurasi jadwal evaluasi, token pengawas, dan durasi pengerjaan</p>
        </div>
        <button class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUjian">
          <i class="bi bi-plus-circle-fill me-1"></i> Buat Ujian Baru
        </button>
      </div>

      <!-- Alerts Feedback -->
      <?php if (!empty($pesan)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 small border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
          <div><?= htmlspecialchars($pesan) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Quick Metrics Summary Bar -->
      <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-journal-bookmark-fill"></i>
            </span>
            <span class="small fw-bold text-dark">Daftar Paket Tersedia</span>
          </div>
          <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
            <i class="bi bi-stack me-1 text-primary"></i> Total: <strong class="text-dark"><?= $total_rows ?></strong> Paket Ujian
          </div>
        </div>
      </div>

      <!-- Tabel Daftar Ujian -->
      <div class="main-card">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4">NAMA & MATA PELAJARAN UJIAN</th>
                <th>DURASI WAKTU</th>
                <th>TOKEN SISWA</th>
                <th>TOTAL SOAL</th>
                <th>STATUS</th>
                <th class="text-end pe-4" style="width: 170px;">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($total_rows > 0): ?>
                <?php while ($u = mysqli_fetch_assoc($list_ujian)):
                  $isActive = ($u['status'] === 'aktif');
                ?>
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="exam-icon-box">
                          <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-dark"><?= htmlspecialchars($u['judul_ujian']) ?></div>
                          <small class="text-muted d-md-none"><i class="bi bi-clock me-1"></i><?= $u['durasi_menit'] ?> Menit</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-secondary border px-2 py-1">
                        <i class="bi bi-clock-history me-1 text-primary"></i> <?= $u['durasi_menit'] ?> Menit
                      </span>
                    </td>
                    <td>
                      <span class="token-badge">
                        <i class="bi bi-key-fill text-muted"></i>
                        <?= htmlspecialchars($u['token']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded-pill">
                        <i class="bi bi-patch-question me-1"></i><?= $u['total_soal'] ?> Butir
                      </span>
                    </td>
                    <td>
                      <span class="status-badge <?= $isActive ? 'active' : 'inactive' ?>">
                        <span class="status-dot"></span>
                        <?= ucfirst($u['status']) ?>
                      </span>
                    </td>
                    <td class="text-end pe-4">
                      <div class="d-inline-flex gap-1 align-items-center">
                        <a href="soal.php?exam_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1 fw-semibold" title="Kelola Butir Soal">
                          <i class="bi bi-list-task me-1"></i> Soal
                        </a>
                        <button type="button"
                          class="btn btn-outline-danger action-btn btn-hapus-ujian"
                          data-id="<?= $u['id'] ?>"
                          data-judul="<?= htmlspecialchars($u['judul_ujian']) ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#modalKonfirmasiHapus"
                          title="Hapus Ujian">
                          <i class="bi bi-trash3-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bi bi-folder-plus fs-1 d-block mb-2 text-secondary opacity-50"></i>
                      <span class="fw-semibold">Belum Ada Paket Ujian</span>
                      <p class="small text-muted mb-3">Mulai buat paket evaluasi baru untuk mengaktifkan sesi ujian.</p>
                      <button class="btn btn-primary rounded-3 px-3 py-2 fw-semibold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUjian">
                        <i class="bi bi-plus-circle me-1"></i> Buat Paket Sekarang
                      </button>
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

  <!-- Modal Tambah Ujian -->
  <div class="modal fade" id="modalTambahUjian" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header border-bottom-0 pb-1 px-4 pt-4">
          <div>
            <h5 class="fw-bold mb-1">Buat Paket Ujian Baru</h5>
            <p class="text-muted small mb-0">Tentukan rincian mata pelajaran, durasi waktu, dan token akses</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="" method="POST">
          <div class="modal-body px-4 py-3">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Nama / Mata Pelajaran Ujian</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3"><i class="bi bi-journal-text"></i></span>
                <input type="text" name="judul_ujian" class="form-control rounded-end-3 border-start-0" placeholder="Contoh: Pemrograman Web Dasar" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Durasi Pengerjaan (Menit)</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3"><i class="bi bi-stopwatch"></i></span>
                <input type="number" name="durasi_menit" class="form-control rounded-end-3 border-start-0" placeholder="Contoh: 60" min="5" required>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label small fw-semibold text-secondary">Token Akses Masuk Siswa</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3"><i class="bi bi-shield-lock"></i></span>
                <input type="text" name="token" id="inputToken" class="form-control text-uppercase fw-bold text-primary border-start-0 border-end-0" placeholder="CBT2026" maxlength="10" required>
                <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="document.getElementById('inputToken').value = Math.random().toString(36).substring(2, 8).toUpperCase()">
                  <i class="bi bi-arrow-repeat me-1"></i> Acak
                </button>
              </div>
              <small class="text-muted" style="font-size: 0.75rem;">Token diberikan kepada peserta ujian sesaat sebelum waktu pengerjaan dimulai.</small>
            </div>
          </div>
          <div class="modal-footer border-top-0 px-4 pb-4 pt-1">
            <button type="button" class="btn btn-light rounded-3 px-3 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah_ujian" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
              <i class="bi bi-check2 me-1"></i> Simpan Paket Ujian
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include 'modal/modal_logout.php'; ?>
  <?php include 'modal/modal_hapus_ujian.php'; ?>

  <script>
    // Handler Modal Konfirmasi Hapus Ujian
    document.querySelectorAll('.btn-hapus-ujian').forEach(button => {
      button.addEventListener('click', function() {
        const idUjian = this.getAttribute('data-id');
        const judulUjian = this.getAttribute('data-judul');

        document.getElementById('teksNamaUjian').innerText = judulUjian;
        document.getElementById('btnEksekusiHapus').setAttribute('href', 'ujian.php?hapus=' + idUjian);
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>