<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
  header("Location: ../login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

$exam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM exams WHERE id = $exam_id AND status = 'aktif'"));
if (!$exam) {
  header("Location: index.php");
  exit;
}

// Cek apakah siswa sudah pernah menyelesaikan ujian ini
$cek_selesai = mysqli_query($koneksi, "SELECT * FROM student_exams WHERE user_id = $user_id AND exam_id = $exam_id AND status = 'selesai'");
if (mysqli_num_rows($cek_selesai) > 0) {
  header("Location: index.php");
  exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mulai'])) {
  $token_input = strtoupper(trim($_POST['token']));

  if ($token_input === $exam['token']) {
    // Cek apakah sudah ada sesi mengerjakan sebelumnya
    $cek_sesi = mysqli_query($koneksi, "SELECT * FROM student_exams WHERE user_id = $user_id AND exam_id = $exam_id");
    if (mysqli_num_rows($cek_sesi) === 0) {
      $waktu_mulai = date('Y-m-d H:i:s');
      mysqli_query($koneksi, "INSERT INTO student_exams (user_id, exam_id, waktu_mulai, status) VALUES ($user_id, $exam_id, '$waktu_mulai', 'mengerjakan')");
    }
    header("Location: ujian.php?exam_id=" . $exam_id);
    exit;
  } else {
    $pesan_error = 'Token yang Anda masukkan tidak valid! Silakan minta token ke pengawas.';
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konfirmasi Pengerjaan - CBT Portal</title>
  <!-- Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #0b1120;
      background-image:
        radial-gradient(at 50% 0%, rgba(37, 99, 246, 0.18) 0px, transparent 60%),
        radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.9) 0px, transparent 50%);
      color: #0f172a;
      min-height: 100vh;
    }

    .confirm-card {
      background: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 1.5rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
      position: relative;
      overflow: hidden;
    }

    .confirm-badge-icon {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      color: #2563eb;
      display: grid;
      place-items: center;
      font-size: 1.85rem;
      margin: 0 auto 1.25rem;
      border: 1px solid #bfdbfe;
    }

    .info-summary-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 1rem;
      padding: 1.15rem 1.25rem;
    }

    .token-input-modern {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      letter-spacing: 0.28em;
      font-size: 1.45rem;
      font-weight: 800;
      border-radius: 0.9rem;
      padding: 0.75rem 1rem;
      border: 2px solid #cbd5e1;
      background: #f8fafc;
      transition: all 0.2s ease;
    }

    .token-input-modern:focus {
      background: #ffffff;
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18);
    }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center py-5">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-5 col-xl-5">

        <div class="confirm-card p-4 p-md-5">
          <!-- Header Card -->
          <div class="text-center mb-4">
            <div class="confirm-badge-icon shadow-sm">
              <i class="bi bi-shield-check"></i>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold small mb-2">
              <i class="bi bi-patch-check-fill me-1"></i> Verifikasi Peserta
            </span>
            <h4 class="fw-bold mb-1 tracking-tight text-dark"><?= htmlspecialchars($exam['judul_ujian']) ?></h4>
            <p class="text-secondary small mb-0">Pastikan informasi identitas telah sesuai sebelum memulai.</p>
          </div>

          <!-- Rincian Parameter Ujian -->
          <div class="info-summary-box mb-4">
            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light-subtle">
              <span class="small text-secondary"><i class="bi bi-person me-1"></i> Peserta</span>
              <strong class="text-dark small"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light-subtle">
              <span class="small text-secondary"><i class="bi bi-clock-history me-1"></i> Alokasi Waktu</span>
              <span class="badge bg-primary-subtle text-primary fw-bold"><?= $exam['durasi_menit'] ?> Menit</span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2">
              <span class="small text-secondary"><i class="bi bi-cpu me-1"></i> Sistem Timer</span>
              <span class="small fw-semibold text-danger d-inline-flex align-items-center gap-1">
                <i class="bi bi-stopwatch"></i> Auto-Submit
              </span>
            </div>
          </div>

          <!-- Feedback Error -->
          <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-4 d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
              <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
              <div><?= htmlspecialchars($pesan_error) ?></div>
            </div>
          <?php endif; ?>

          <!-- Form Masukkan Token -->
          <form action="" method="POST">
            <div class="mb-4 text-center">
              <label class="form-label small fw-bold text-dark text-uppercase tracking-wider mb-2">
                <i class="bi bi-key-fill text-primary me-1"></i> Masukkan Token Ujian
              </label>
              <input type="text"
                name="token"
                class="form-control token-input-modern text-center text-uppercase"
                placeholder="TOKEN"
                maxlength="10"
                autocomplete="off"
                required
                autofocus>
              <small class="text-secondary d-block mt-2" style="font-size: 0.75rem;">
                Token diberikan oleh pengawas di ruang ujian.
              </small>
            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex gap-2">
              <a href="index.php" class="btn btn-light rounded-3 w-50 py-2 fw-semibold text-secondary border">
                <i class="bi bi-arrow-left me-1"></i> Batal
              </a>
              <button type="submit" name="mulai" class="btn btn-primary rounded-3 w-50 py-2 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
                <span>Mulai Tes</span>
                <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </form>

        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>