<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$pesan = '';

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$list_exams = mysqli_query($koneksi, "SELECT * FROM exams ORDER BY id DESC");

if ($exam_id == 0) {
  $first_exam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM exams ORDER BY id DESC LIMIT 1"));
  if ($first_exam) {
    $exam_id = $first_exam['id'];
  }
}

// 1. TAMBAH SOAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_soal'])) {
  $selected_exam = (int)$_POST['exam_id'];
  $pertanyaan    = mysqli_real_escape_string($koneksi, trim($_POST['pertanyaan']));
  $opsi_a        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_a']));
  $opsi_b        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_b']));
  $opsi_c        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_c']));
  $opsi_d        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_d']));
  $opsi_e        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_e']));
  $kunci_jawaban = mysqli_real_escape_string($koneksi, trim($_POST['kunci_jawaban']));

  $query = mysqli_query($koneksi, "INSERT INTO questions (exam_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, kunci_jawaban) 
                                     VALUES ($selected_exam, '$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$opsi_e', '$kunci_jawaban')");
  if ($query) {
    $pesan = 'Butir soal baru berhasil ditambahkan!';
    $exam_id = $selected_exam;
  }
}

// 2. EDIT / UPDATE SOAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_soal'])) {
  $id_soal       = (int)$_POST['id_soal'];
  $pertanyaan    = mysqli_real_escape_string($koneksi, trim($_POST['pertanyaan']));
  $opsi_a        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_a']));
  $opsi_b        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_b']));
  $opsi_c        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_c']));
  $opsi_d        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_d']));
  $opsi_e        = mysqli_real_escape_string($koneksi, trim($_POST['opsi_e']));
  $kunci_jawaban = mysqli_real_escape_string($koneksi, trim($_POST['kunci_jawaban']));

  $update = mysqli_query($koneksi, "UPDATE questions SET 
        pertanyaan = '$pertanyaan',
        opsi_a = '$opsi_a',
        opsi_b = '$opsi_b',
        opsi_c = '$opsi_c',
        opsi_d = '$opsi_d',
        opsi_e = '$opsi_e',
        kunci_jawaban = '$kunci_jawaban'
        WHERE id = $id_soal
    ");

  if ($update) {
    $pesan = 'Butir soal berhasil diperbarui!';
  }
}

// 3. HAPUS SOAL
if (isset($_GET['hapus'])) {
  $id_soal = (int)$_GET['hapus'];
  mysqli_query($koneksi, "DELETE FROM questions WHERE id = $id_soal");
  header("Location: soal.php?exam_id=" . $exam_id);
  exit;
}

$detail_exam = null;
$list_soal = null;
$total_soal_paket = 0;
if ($exam_id > 0) {
  $detail_exam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM exams WHERE id = $exam_id"));
  $list_soal   = mysqli_query($koneksi, "SELECT * FROM questions WHERE exam_id = $exam_id ORDER BY id ASC");
  if ($list_soal) {
    $total_soal_paket = mysqli_num_rows($list_soal);
  }
}
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
  <title>Bank Soal - CBT Portal</title>
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

    .question-card {
      border: 1px solid #e2e8f0;
      border-radius: 1.25rem;
      background: #ffffff;
      box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05);
      transition: all 0.2s ease;
    }

    .question-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
    }

    .option-card {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 0.85rem;
      padding: 0.75rem 0.95rem;
      font-size: 0.9rem;
      height: 100%;
      transition: all 0.15s ease;
    }

    .option-letter {
      display: inline-grid;
      place-items: center;
      width: 26px;
      height: 26px;
      border-radius: 8px;
      background: #e2e8f0;
      color: #475569;
      font-weight: 700;
      font-size: 0.78rem;
      flex-shrink: 0;
    }

    .option-card.correct {
      background-color: #f0fdf4;
      border-color: #86efac;
      color: #14532d;
    }

    .option-card.correct .option-letter {
      background: #16a34a;
      color: #ffffff;
    }

    .badge-key {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.2rem 0.55rem;
      border-radius: 9999px;
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      margin-left: auto;
      flex-shrink: 0;
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

    .option-input-addon {
      background: #f1f5f9;
      font-weight: 700;
      color: #475569;
      border-color: #cbd5e1;
      width: 44px;
      justify-content: center;
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
          <h4 class="fw-bold mb-1 tracking-tight">Manajemen Bank Soal</h4>
          <p class="text-secondary small mb-0">Kelola butir pertanyaan dan kunci jawaban ujian evaluasi</p>
        </div>
        <div class="d-flex gap-2">
          <?php if ($detail_exam): ?>
            <a href="cetak_soal.php?exam_id=<?= $exam_id ?>" target="_blank" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold shadow-sm">
              <i class="bi bi-printer me-1"></i> Cetak / PDF
            </a>
            <button class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
              <i class="bi bi-plus-circle me-1"></i> Tambah Soal
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Alerts -->
      <?php if (!empty($pesan)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 small border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
          <div><?= htmlspecialchars($pesan) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Filter Paket Ujian Toolbar -->
      <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
        <form action="" method="GET" class="row g-2 align-items-center justify-content-between">
          <div class="col-md-7 col-lg-6 d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-layers-half"></i>
            </span>
            <div class="flex-grow-1">
              <select name="exam_id" class="form-select rounded-3 admin-mobile-select" aria-label="Pilih paket ujian" onchange="this.form.submit()">
                <?php
                mysqli_data_seek($list_exams, 0);
                while ($e = mysqli_fetch_assoc($list_exams)): ?>
                  <option value="<?= $e['id'] ?>" <?= ($e['id'] == $exam_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['judul_ujian']) ?> (Token: <?= $e['token'] ?>)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
          <?php if ($detail_exam): ?>
            <div class="col-auto">
              <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                <i class="bi bi-patch-question me-1 text-primary"></i> Total: <strong class="text-dark"><?= $total_soal_paket ?></strong> Butir Soal
              </div>
            </div>
          <?php endif; ?>
        </form>
      </div>

      <!-- Daftar Soal -->
      <?php if ($detail_exam): ?>
        <?php if ($total_soal_paket > 0): ?>
          <?php $no = 1;
          while ($s = mysqli_fetch_assoc($list_soal)): ?>
            <div class="question-card mb-3 p-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">
                  <i class="bi bi-hash me-1"></i> Soal Nomor <?= $no++ ?>
                </span>
                <div class="d-flex gap-1">
                  <!-- Tombol Edit Soal -->
                  <button type="button"
                    class="btn btn-outline-warning action-btn btn-edit-soal"
                    data-id="<?= $s['id'] ?>"
                    data-pertanyaan="<?= htmlspecialchars($s['pertanyaan']) ?>"
                    data-a="<?= htmlspecialchars($s['opsi_a']) ?>"
                    data-b="<?= htmlspecialchars($s['opsi_b']) ?>"
                    data-c="<?= htmlspecialchars($s['opsi_c']) ?>"
                    data-d="<?= htmlspecialchars($s['opsi_d']) ?>"
                    data-e="<?= htmlspecialchars($s['opsi_e']) ?>"
                    data-kunci="<?= $s['kunci_jawaban'] ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditSoal"
                    title="Ubah Soal">
                    <i class="bi bi-pencil-fill"></i>
                  </button>

                  <!-- Tombol Hapus Soal -->
                  <button type="button"
                    class="btn btn-outline-danger action-btn btn-hapus-soal"
                    data-id="<?= $s['id'] ?>"
                    data-nomor="<?= $no - 1 ?>"
                    data-pertanyaan="<?= htmlspecialchars(mb_strimwidth($s['pertanyaan'], 0, 70, '...')) ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#modalHapusSoal"
                    title="Hapus Soal">
                    <i class="bi bi-trash3-fill"></i>
                  </button>
                </div>
              </div>

              <!-- Teks Pertanyaan -->
              <div class="fw-semibold text-dark mb-3 ps-1" style="font-size: 1.05rem; line-height: 1.6; white-space: pre-line;"><?= htmlspecialchars($s['pertanyaan']) ?></div>

              <!-- Pilihan Jawaban Grid -->
              <div class="row g-2">
                <div class="col-md-6">
                  <div class="option-card <?= ($s['kunci_jawaban'] === 'A') ? 'correct' : '' ?>">
                    <span class="option-letter">A</span>
                    <span class="flex-grow-1"><?= htmlspecialchars($s['opsi_a']) ?></span>
                    <?php if ($s['kunci_jawaban'] === 'A'): ?>
                      <span class="badge-key"><i class="bi bi-check2-circle"></i> Kunci</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="option-card <?= ($s['kunci_jawaban'] === 'B') ? 'correct' : '' ?>">
                    <span class="option-letter">B</span>
                    <span class="flex-grow-1"><?= htmlspecialchars($s['opsi_b']) ?></span>
                    <?php if ($s['kunci_jawaban'] === 'B'): ?>
                      <span class="badge-key"><i class="bi bi-check2-circle"></i> Kunci</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="option-card <?= ($s['kunci_jawaban'] === 'C') ? 'correct' : '' ?>">
                    <span class="option-letter">C</span>
                    <span class="flex-grow-1"><?= htmlspecialchars($s['opsi_c']) ?></span>
                    <?php if ($s['kunci_jawaban'] === 'C'): ?>
                      <span class="badge-key"><i class="bi bi-check2-circle"></i> Kunci</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="option-card <?= ($s['kunci_jawaban'] === 'D') ? 'correct' : '' ?>">
                    <span class="option-letter">D</span>
                    <span class="flex-grow-1"><?= htmlspecialchars($s['opsi_d']) ?></span>
                    <?php if ($s['kunci_jawaban'] === 'D'): ?>
                      <span class="badge-key"><i class="bi bi-check2-circle"></i> Kunci</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="col-12">
                  <div class="option-card <?= ($s['kunci_jawaban'] === 'E') ? 'correct' : '' ?>">
                    <span class="option-letter">E</span>
                    <span class="flex-grow-1"><?= htmlspecialchars($s['opsi_e']) ?></span>
                    <?php if ($s['kunci_jawaban'] === 'E'): ?>
                      <span class="badge-key"><i class="bi bi-check2-circle"></i> Kunci</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="text-muted">
              <i class="bi bi-folder2-open display-4 d-block mb-3 text-secondary opacity-50"></i>
              <h5 class="fw-bold text-dark">Belum Ada Soal di Paket Ini</h5>
              <p class="small text-muted mb-3" style="max-width: 420px; margin: 0 auto;">Paket ini belum memiliki butir soal evaluasi. Silakan tambahkan soal untuk mulai merakit ujian.</p>
              <button class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Soal Pertama
              </button>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>

  <!-- Modal Tambah Soal -->
  <div class="modal fade" id="modalTambahSoal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header border-bottom-0 pb-1 px-4 pt-4">
          <div>
            <h5 class="fw-bold mb-1">Tambah Butir Soal Baru</h5>
            <p class="text-muted small mb-0">Tuliskan teks pertanyaan dan 5 opsi jawaban</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="" method="POST">
          <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
          <div class="modal-body px-4 py-3">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Teks Pertanyaan</label>
              <textarea name="pertanyaan" rows="3" class="form-control rounded-3" placeholder="Tuliskan butir soal evaluasi di sini..." required></textarea>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban A</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">A</span>
                  <input type="text" name="opsi_a" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban B</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">B</span>
                  <input type="text" name="opsi_b" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban C</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">C</span>
                  <input type="text" name="opsi_c" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban D</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">D</span>
                  <input type="text" name="opsi_d" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban E</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">E</span>
                  <input type="text" name="opsi_e" class="form-control rounded-end-3" required>
                </div>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label small fw-bold text-dark">Kunci Jawaban yang Benar</label>
              <select name="kunci_jawaban" class="form-select rounded-3 border-primary" required>
                <option value="A">Pilihan A</option>
                <option value="B">Pilihan B</option>
                <option value="C">Pilihan C</option>
                <option value="D">Pilihan D</option>
                <option value="E">Pilihan E</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-top-0 px-4 pb-4 pt-1">
            <button type="button" class="btn btn-light rounded-3 px-3 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah_soal" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
              <i class="bi bi-check2 me-1"></i> Simpan Butir Soal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Soal Modern -->
  <div class="modal fade" id="modalEditSoal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header border-bottom-0 pb-1 px-4 pt-4">
          <div>
            <h5 class="fw-bold mb-1">Ubah Butir Soal</h5>
            <p class="text-muted small mb-0">Perbarui pertanyaan, pilihan opsi, atau kunci jawaban</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="" method="POST">
          <input type="hidden" name="id_soal" id="editIdSoal">
          <div class="modal-body px-4 py-3">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-secondary">Teks Pertanyaan</label>
              <textarea name="pertanyaan" id="editPertanyaan" rows="3" class="form-control rounded-3" required></textarea>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban A</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">A</span>
                  <input type="text" name="opsi_a" id="editOpsiA" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban B</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">B</span>
                  <input type="text" name="opsi_b" id="editOpsiB" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban C</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">C</span>
                  <input type="text" name="opsi_c" id="editOpsiC" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban D</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">D</span>
                  <input type="text" name="opsi_d" id="editOpsiD" class="form-control rounded-end-3" required>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold text-secondary">Pilihan Jawaban E</label>
                <div class="input-group">
                  <span class="input-group-text option-input-addon">E</span>
                  <input type="text" name="opsi_e" id="editOpsiE" class="form-control rounded-end-3" required>
                </div>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label small fw-bold text-dark">Kunci Jawaban yang Benar</label>
              <select name="kunci_jawaban" id="editKunci" class="form-select rounded-3 border-warning" required>
                <option value="A">Pilihan A</option>
                <option value="B">Pilihan B</option>
                <option value="C">Pilihan C</option>
                <option value="D">Pilihan D</option>
                <option value="E">Pilihan E</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-top-0 px-4 pb-4 pt-1">
            <button type="button" class="btn btn-light rounded-3 px-3 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit_soal" class="btn btn-warning text-dark fw-semibold rounded-3 px-4 shadow-sm">
              <i class="bi bi-check2 me-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include 'modal/modal_logout.php'; ?>
  <?php include 'modal/modal_hapus_soal.php'; ?>

  <script>
    // Handler Tombol Edit Soal
    document.querySelectorAll('.btn-edit-soal').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('editIdSoal').value = this.getAttribute('data-id');
        document.getElementById('editPertanyaan').value = this.getAttribute('data-pertanyaan');
        document.getElementById('editOpsiA').value = this.getAttribute('data-a');
        document.getElementById('editOpsiB').value = this.getAttribute('data-b');
        document.getElementById('editOpsiC').value = this.getAttribute('data-c');
        document.getElementById('editOpsiD').value = this.getAttribute('data-d');
        document.getElementById('editOpsiE').value = this.getAttribute('data-e');
        document.getElementById('editKunci').value = this.getAttribute('data-kunci');
      });
    });

    // Handler Tombol Hapus Soal
    document.querySelectorAll('.btn-hapus-soal').forEach(button => {
      button.addEventListener('click', function() {
        const idSoal = this.getAttribute('data-id');
        const nomorSoal = this.getAttribute('data-nomor');
        const cuplikanTeks = this.getAttribute('data-pertanyaan');
        const examId = "<?= $exam_id ?>";

        document.getElementById('labelNomorSoal').innerText = 'Soal Nomor ' + nomorSoal;
        document.getElementById('teksCuplikanSoal').innerText = '"' + cuplikanTeks + '"';
        document.getElementById('btnEksekusiHapusSoal').setAttribute('href', 'soal.php?exam_id=' + examId + '&hapus=' + idSoal);
      });
    });
  </script>

  <script src="../assets/js/admin-mobile-select.js?v=2"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>