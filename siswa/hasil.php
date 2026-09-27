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

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT se.*, e.judul_ujian 
    FROM student_exams se 
    JOIN exams e ON se.exam_id = e.id 
    WHERE se.user_id = $user_id AND se.exam_id = $exam_id AND se.status = 'selesai'
"));

if (!$data) {
  header("Location: index.php");
  exit;
}

$nilai = (float)$data['total_nilai'];
$isPassed = ($nilai >= 75);
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
  <title>Hasil Evaluasi - CBT Portal</title>
  <!-- Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #0b1120;
      background-image:
        radial-gradient(at 50% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 60%),
        radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.95) 0px, transparent 50%);
      color: #0f172a;
      min-height: 100vh;
    }

    .result-card {
      background: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 1.5rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
      position: relative;
      overflow: hidden;
    }

    .success-icon-badge {
      width: 76px;
      height: 76px;
      border-radius: 22px;
      background: rgba(16, 185, 129, 0.1);
      color: #10b981;
      display: inline-grid;
      place-items: center;
      font-size: 2.2rem;
      border: 2px solid rgba(16, 185, 129, 0.25);
      box-shadow: 0 0 28px rgba(16, 185, 129, 0.2);
      margin-bottom: 1.25rem;
    }

    .score-banner-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 1.25rem;
      padding: 1.75rem 1.25rem;
      position: relative;
    }

    .score-number {
      font-family: 'JetBrains Mono', monospace;
      font-size: 3.8rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: -0.03em;
    }

    .badge-status-kkm {
      font-size: 0.78rem;
      font-weight: 700;
      padding: 0.35rem 0.85rem;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }

    .status-pass {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }

    .status-review {
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
    }
  </style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=1">
</head>

<body class="student-page d-flex align-items-center justify-content-center py-5">

  <button type="button" class="theme-toggle theme-toggle-compact theme-toggle-float" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
    <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
  </button>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-5 col-xl-5">

        <div class="result-card p-4 p-md-5 text-center">

          <!-- Success Badge Icon -->
          <div class="success-icon-badge">
            <i class="bi bi-award-fill"></i>
          </div>

          <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold small mb-2">
            <i class="bi bi-check-circle-fill me-1"></i> Asesmen Selesai
          </span>
          <h4 class="fw-bold mb-1 tracking-tight text-dark">Hasil Pengerjaan Ujian</h4>
          <p class="text-secondary small mb-4"><?= htmlspecialchars($data['judul_ujian']) ?></p>

          <!-- Score Display Box -->
          <div class="score-banner-box mb-4">
            <small class="text-muted fw-bold d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase;">
              NILAI AKHIR PEROLEHAN
            </small>

            <div class="score-number <?= $isPassed ? 'text-primary' : 'text-danger' ?> mb-2">
              <?= $data['total_nilai'] ?>
            </div>

            <div class="mb-3">
              <?php if ($isPassed): ?>
                <span class="badge-status-kkm status-pass">
                  <i class="bi bi-patch-check-fill"></i> Tuntas / Memenuhi KKM
                </span>
              <?php else: ?>
                <span class="badge-status-kkm status-review">
                  <i class="bi bi-info-circle-fill"></i> Evaluasi / Perlu Pengayaan
                </span>
              <?php endif; ?>
            </div>

            <div class="pt-3 border-top border-light-subtle">
              <small class="text-secondary d-flex align-items-center justify-content-center gap-1" style="font-size: 0.78rem;">
                <i class="bi bi-clock-history text-muted"></i>
                Diserahkan pada: <strong class="text-dark"><?= date('d M Y, H:i', strtotime($data['waktu_selesai'])) ?> WIB</strong>
              </small>
            </div>
          </div>

          <!-- Navigation Back -->
          <a href="index.php" class="btn btn-primary rounded-3 w-100 py-2 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-house-door-fill"></i>
            <span>Kembali ke Beranda Siswa</span>
          </a>

        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>