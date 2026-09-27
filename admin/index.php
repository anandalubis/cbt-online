<?php
session_start();
require_once '../config/database.php';
global $koneksi;

// Proteksi Halaman Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Mengambil Statistik Sistem
$total_siswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'"))['total'];
$total_ujian = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM exams"))['total'];
$total_soal  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM questions"))['total'];
$total_ikut  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM student_exams WHERE status = 'selesai'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - CBT Portal</title>
  <!-- Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    :root {
      --primary-color: #2563eb;
      --card-radius: 1.25rem;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
    }

    /* Ambient Hero Banner */
    .dashboard-hero {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      border-radius: var(--card-radius);
      color: #ffffff;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .dashboard-hero::after {
      content: "";
      position: absolute;
      top: -30%;
      right: -10%;
      width: 320px;
      height: 320px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(59, 130, 246, 0) 70%);
      pointer-events: none;
    }

    /* Modern Metric Cards */
    .stat-card-modern {
      background: #ffffff;
      border-radius: var(--card-radius);
      border: 1px solid #e2e8f0;
      padding: 1.35rem 1.4rem;
      box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .stat-card-modern:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 28px -6px rgba(15, 23, 42, 0.1);
      border-color: #cbd5e1;
    }

    .stat-icon-wrapper {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      font-size: 1.45rem;
      flex-shrink: 0;
    }

    /* Step Action Card Styling */
    .action-step-card {
      background: #ffffff;
      border-radius: 1rem;
      border: 1px solid #e2e8f0;
      padding: 1.35rem;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.2s ease;
    }

    .action-step-card:hover {
      border-color: #3b82f6;
      box-shadow: 0 8px 24px -4px rgba(59, 130, 246, 0.1);
      transform: translateY(-2px);
    }

    .step-badge {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.88rem;
      display: inline-grid;
      place-items: center;
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
          <h4 class="fw-bold mb-1 tracking-tight">Pusat Kendali CBT</h4>
          <p class="text-secondary small mb-0">Kelola operasional dan evaluasi akademik secara real-time</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-shield-check me-1"></i> Mode Administrator
          </div>
        </div>
      </div>

      <!-- Welcome Hero Banner -->
      <div class="dashboard-hero p-4 mb-4">
        <div class="row align-items-center position-relative" style="z-index: 1;">
          <div class="col-lg-8 mb-3 mb-lg-0">
            <span class="badge bg-white bg-opacity-10 text-white-50 px-3 py-1 rounded-pill mb-2 small">
              <i class="bi bi-stars text-warning me-1"></i> Ikhtisar Evaluasi
            </span>
            <h3 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</h3>
            <p class="text-white-50 mb-0 small" style="max-width: 580px; line-height: 1.6;">
              Portal evaluasi CBT siap digunakan. Anda dapat memantau pengerjaan ujian, mengelola data peserta, serta mengonfigurasi butir soal dari panel ini.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a href="ujian.php" class="btn btn-primary px-3 py-2 rounded-3 fw-semibold shadow-sm">
              <i class="bi bi-plus-circle me-1"></i> Mulai Ujian Baru
            </a>
          </div>
        </div>
      </div>

      <!-- 4 Grid Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- Paket Ujian -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-layers-half"></i>
              </div>
              <span class="badge bg-primary-subtle text-primary rounded-pill small px-2 py-1">Aktif</span>
            </div>
            <div class="text-muted small fw-semibold mb-1">Paket Ujian</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_ujian) ?></h3>
          </div>
        </div>

        <!-- Bank Soal -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                <i class="bi bi-patch-question-fill"></i>
              </div>
              <span class="badge bg-success-subtle text-success rounded-pill small px-2 py-1">Bank Soal</span>
            </div>
            <div class="text-muted small fw-semibold mb-1">Total Butir Soal</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_soal) ?></h3>
          </div>
        </div>

        <!-- Data Siswa -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-people-fill"></i>
              </div>
              <span class="badge bg-warning-subtle text-warning rounded-pill small px-2 py-1">Peserta</span>
            </div>
            <div class="text-muted small fw-semibold mb-1">Siswa Terdaftar</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_siswa) ?></h3>
          </div>
        </div>

        <!-- Selesai Ujian -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                <i class="bi bi-patch-check-fill"></i>
              </div>
              <span class="badge bg-info-subtle text-info rounded-pill small px-2 py-1">Tuntas</span>
            </div>
            <div class="text-muted small fw-semibold mb-1">Ujian Selesai</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_ikut) ?></h3>
          </div>
        </div>
      </div>

      <!-- Quick Action Workflow -->
      <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <div>
            <h5 class="fw-bold mb-1">Alur Kerja Evaluasi CBT</h5>
            <p class="text-muted small mb-0">Ikuti tahapan operasional berurutan untuk menjamin kelancaran ujian:</p>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="action-step-card">
              <div>
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="step-badge bg-primary-subtle text-primary">01</span>
                  <h6 class="fw-bold mb-0">Paket Ujian</h6>
                </div>
                <p class="text-secondary small mb-3">Atur mata pelajaran, tanggal rilis, durasi menit pengerjaan, dan generator token.</p>
              </div>
              <a href="ujian.php" class="btn btn-outline-primary w-100 rounded-3 btn-sm py-2 fw-semibold">
                Konfigurasi Ujian <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>

          <div class="col-md-4">
            <div class="action-step-card">
              <div>
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="step-badge bg-success-subtle text-success">02</span>
                  <h6 class="fw-bold mb-0">Bank & Butir Soal</h6>
                </div>
                <p class="text-secondary small mb-3">Input materi soal pilihan ganda, kunci jawaban, dan kaitkan langsung ke paket ujian aktif.</p>
              </div>
              <a href="soal.php" class="btn btn-outline-primary w-100 rounded-3 btn-sm py-2 fw-semibold">
                Kelola Bank Soal <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>

          <div class="col-md-4">
            <div class="action-step-card">
              <div>
                <div class="d-flex align-items-center gap-2 mb-3">
                  <span class="step-badge bg-info-subtle text-info">03</span>
                  <h6 class="fw-bold mb-0">Rekapitulasi Nilai</h6>
                </div>
                <p class="text-secondary small mb-3">Pantau hasil penilaian otomatis, analisa skor peserta, dan cetak laporan hasil evaluasi.</p>
              </div>
              <a href="nilai.php" class="btn btn-outline-primary w-100 rounded-3 btn-sm py-2 fw-semibold">
                Buka Rekap Nilai <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include 'modal/modal_logout.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>