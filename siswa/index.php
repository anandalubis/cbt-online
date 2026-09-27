<?php
session_start();
require_once '../config/database.php';
global $koneksi;

// Proteksi Halaman Siswa
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
  header("Location: ../login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$nama_siswa = $_SESSION['nama_lengkap'] ?? 'Peserta Didik';
$username_siswa = $_SESSION['username'] ?? '-';
$inisial_siswa = mb_strtoupper(mb_substr($nama_siswa, 0, 1));

// Mengambil Daftar Ujian Aktif beserta status pengerjaan siswa
$query_ujian = mysqli_query($koneksi, "
    SELECT e.*, 
           (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) AS total_soal,
           se.status AS status_siswa,
           se.total_nilai
    FROM exams e
    LEFT JOIN student_exams se ON se.exam_id = e.id AND se.user_id = $user_id
    WHERE e.status = 'aktif'
    ORDER BY e.id DESC
");
$total_tersedia = mysqli_num_rows($query_ujian);
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
  <title>Portal Evaluasi Siswa - CBT Portal</title>
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
      min-height: 100vh;
    }

    /* Modern Topbar Navbar */
    .student-navbar {
      background: #0b1120;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      position: sticky;
      top: 0;
      z-index: 1030;
      backdrop-filter: blur(12px);
    }

    .brand-icon-pill {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      color: #ffffff;
      display: grid;
      place-items: center;
      font-size: 1.25rem;
      box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }

    .user-pill-avatar {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: rgba(59, 130, 246, 0.2);
      color: #93c5fd;
      border: 1px solid rgba(147, 197, 253, 0.25);
      font-weight: 700;
      display: grid;
      place-items: center;
      font-size: 0.85rem;
    }

    /* Student Banner Hero */
    .student-hero {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      border-radius: 1.35rem;
      color: #ffffff;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }

    .student-hero::after {
      content: "";
      position: absolute;
      top: -40%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0) 70%);
      pointer-events: none;
    }

    /* Exam Cards */
    .exam-card-modern {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 1.25rem;
      box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
    }

    .exam-card-modern:hover {
      transform: translateY(-5px);
      box-shadow: 0 18px 30px -8px rgba(15, 23, 42, 0.12);
      border-color: #cbd5e1;
    }

    .exam-icon-circle {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: #eff6ff;
      color: #2563eb;
      display: grid;
      place-items: center;
      font-size: 1.25rem;
      flex-shrink: 0;
    }

    .score-box-result {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 0.65rem 1rem;
    }

    .score-grade {
      font-size: 1.35rem;
      font-weight: 800;
      color: #16a34a;
    }
  </style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=1">
</head>

<body class="student-page">

  <!-- Top Navbar Siswa -->
  <header class="student-navbar py-2 px-3 mb-4 shadow-sm">
    <div class="container d-flex align-items-center justify-content-between">
      <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2 m-0" href="index.php">
        <div class="brand-icon-pill">
          <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
          <span class="d-block" style="font-size: 1.05rem; letter-spacing: -0.02em;">CBT Portal</span>
          <small class="text-white-50" style="font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase;">Peserta Ujian</small>
        </div>
      </a>

      <div class="d-flex align-items-center gap-3">
        <button type="button" class="theme-toggle theme-toggle-compact" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
          <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
        </button>
        <div class="d-none d-sm-flex align-items-center gap-2">
          <div class="user-pill-avatar"><?= htmlspecialchars($inisial_siswa) ?></div>
          <div class="text-start">
            <span class="text-white fw-semibold small d-block"><?= htmlspecialchars($nama_siswa) ?></span>
            <small class="text-secondary" style="font-size: 0.7rem; font-family: monospace;">NISN: <?= htmlspecialchars($username_siswa) ?></small>
          </div>
        </div>

        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiLogout">
          <i class="bi bi-box-arrow-right"></i>
          <span class="d-none d-sm-inline">Keluar</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Container Halaman -->
  <main class="container pb-5">

    <!-- Hero Welcome Card -->
    <div class="student-hero p-4 mb-4">
      <div class="row align-items-center position-relative" style="z-index: 1;">
        <div class="col-lg-8">
          <div class="badge bg-white bg-opacity-10 text-white-50 px-3 py-1 rounded-pill mb-2 small">
            <i class="bi bi-clock-history text-info me-1"></i> Sesi Ujian Aktif
          </div>
          <h3 class="fw-bold mb-2">Halo, <?= htmlspecialchars($nama_siswa) ?>!</h3>
          <p class="text-white-50 mb-0 small" style="max-width: 600px; line-height: 1.6;">
            Silakan pilih paket ujian yang dijadwalkan untuk kelas Anda. Siapkan token verifikasi yang diberikan oleh guru atau pengawas ruang sebelum memulai tes.
          </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <div class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2 rounded-3 small">
            <i class="bi bi-journal-check me-1"></i> <?= $total_tersedia ?> Paket Ujian Tersedia
          </div>
        </div>
      </div>
    </div>

    <!-- Section Heading -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Paket Ujian</h5>
        <small class="text-secondary">Pilih jadwal evaluasi dan pastikan koneksi internet stabil</small>
      </div>
    </div>

    <!-- Grid Kartu Ujian -->
    <div class="row g-4">
      <?php if ($total_tersedia > 0): ?>
        <?php while ($u = mysqli_fetch_assoc($query_ujian)):
          $isDone = ($u['status_siswa'] === 'selesai');
          $inProgress = ($u['status_siswa'] === 'mengerjakan');
        ?>
          <div class="col-md-6 col-lg-4">
            <div class="exam-card-modern p-4">
              <div>
                <!-- Status Badges -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <span class="badge bg-light text-secondary border fw-semibold px-2 py-1 rounded-pill small">
                    <i class="bi bi-stopwatch me-1 text-primary"></i> <?= $u['durasi_menit'] ?> Menit
                  </span>

                  <?php if ($isDone): ?>
                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill small">
                      <i class="bi bi-check2-all me-1"></i>Selesai
                    </span>
                  <?php elseif ($inProgress): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1 rounded-pill small">
                      <i class="bi bi-hourglass-split me-1"></i>Sedang Berjalan
                    </span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill small">
                      <i class="bi bi-circle me-1"></i>Belum Mengikuti
                    </span>
                  <?php endif; ?>
                </div>

                <!-- Judul & Info Ujian -->
                <div class="d-flex align-items-start gap-3 mb-3">
                  <div class="exam-icon-circle">
                    <i class="bi bi-journal-code"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem; line-height: 1.35;"><?= htmlspecialchars($u['judul_ujian']) ?></h5>
                    <span class="text-secondary small">
                      <i class="bi bi-patch-question me-1"></i><?= $u['total_soal'] ?> Butir Pertanyaan
                    </span>
                  </div>
                </div>
              </div>

              <!-- Footer Box / Tombol Eksekusi -->
              <div class="pt-3 border-top mt-3">
                <?php if ($isDone): ?>
                  <div class="score-box-result d-flex justify-content-between align-items-center">
                    <div class="small text-secondary fw-semibold">
                      <i class="bi bi-award-fill text-warning me-1"></i>Skor Perolehan:
                    </div>
                    <div class="score-grade"><?= $u['total_nilai'] ?></div>
                  </div>
                <?php elseif ($inProgress): ?>
                  <a href="petunjuk.php?exam_id=<?= (int)$u['id'] ?>" class="btn btn-warning w-100 rounded-3 fw-bold text-dark shadow-sm py-2 d-flex align-items-center justify-content-center gap-2">
                    <span>Lanjutkan Ujian</span>
                    <i class="bi bi-arrow-right"></i>
                  </a>
                <?php else: ?>
                  <a href="petunjuk.php?exam_id=<?= (int)$u['id'] ?>" class="btn btn-primary w-100 rounded-3 fw-semibold shadow-sm py-2 d-flex align-items-center justify-content-center gap-2">
                    <span>Mulai Ujian</span>
                    <i class="bi bi-arrow-right"></i>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
            <div class="text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
              <h5 class="fw-bold text-dark">Tidak Ada Ujian Aktif</h5>
              <p class="small text-muted mb-0" style="max-width: 400px; margin: 0 auto;">Saat ini belum ada jadwal evaluasi yang diaktifkan oleh pengawas atau admin sekolah.</p>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'modal/modal_logout.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>