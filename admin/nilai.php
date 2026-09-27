<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$list_exams = mysqli_query($koneksi, "SELECT * FROM exams ORDER BY id DESC");

if ($exam_id == 0) {
  $first_exam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM exams ORDER BY id DESC LIMIT 1"));
  if ($first_exam) {
    $exam_id = $first_exam['id'];
  }
}

$detail_exam = null;
$list_nilai = null;
$total_peserta = 0;
$nilai_tertinggi = 0;
$rata_rata = 0;

if ($exam_id > 0) {
  $detail_exam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM exams WHERE id = $exam_id"));
  $list_nilai  = mysqli_query($koneksi, "
        SELECT se.*, u.nama_lengkap, u.username 
        FROM student_exams se 
        JOIN users u ON se.user_id = u.id 
        WHERE se.exam_id = $exam_id AND se.status = 'selesai'
        ORDER BY se.total_nilai DESC
    ");

  if ($list_nilai) {
    $total_peserta = mysqli_num_rows($list_nilai);
    // Hitung ringkasan statistik ujian aktif
    $stat_calc = mysqli_fetch_assoc(mysqli_query($koneksi, "
      SELECT MAX(total_nilai) AS tertinggi, AVG(total_nilai) AS rata_rata 
      FROM student_exams 
      WHERE exam_id = $exam_id AND status = 'selesai'
    "));
    if ($stat_calc) {
      $nilai_tertinggi = $stat_calc['tertinggi'] ?? 0;
      $rata_rata = round($stat_calc['rata_rata'] ?? 0, 1);
    }
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
  <title>Rekap Nilai - CBT Portal</title>
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

    /* Modern Table Header & Container */
    .rekap-card {
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

    /* Rank Badges */
    .rank-badge {
      width: 32px;
      height: 32px;
      display: inline-grid;
      place-items: center;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.82rem;
    }

    .rank-1 {
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
    }

    .rank-2 {
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #e2e8f0;
    }

    .rank-3 {
      background: #ffedd5;
      color: #9a3412;
      border: 1px solid #fed7aa;
    }

    .rank-general {
      background: #f8fafc;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }

    /* Score Pills */
    .score-badge {
      font-weight: 800;
      font-size: 0.95rem;
      padding: 0.35rem 0.8rem;
      border-radius: 8px;
    }

    .score-pass {
      background: #dcfce7;
      color: #15803d;
    }

    .score-fail {
      background: #fee2e2;
      color: #b91c1c;
    }

    .user-avatar-initial {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: #eff6ff;
      color: #2563eb;
      font-weight: 700;
      display: inline-grid;
      place-items: center;
      font-size: 0.85rem;
      flex-shrink: 0;
    }

    /* Quick Mini Stat Card */
    .mini-stat-box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 1rem;
      padding: 1rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .mini-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      font-size: 1.25rem;
    }

    @media print {

      .no-print,
      .sidebar {
        display: none !important;
      }

      body {
        background: #fff !important;
        color: #000 !important;
      }

      .content-area {
        width: 100% !important;
        padding: 0 !important;
      }

      .rekap-card {
        border: none !important;
        box-shadow: none !important;
      }

      .table {
        border: 1px solid #000;
      }

      .table thead th {
        background-color: #eee !important;
        color: #000 !important;
        border: 1px solid #000;
      }

      .table td {
        border: 1px solid #000;
      }
    }
  </style>
  <link rel="stylesheet" href="../assets/css/admin-responsive.css?v=3">
  <link rel="stylesheet" href="../assets/css/theme.css?v=1">
</head>

<body>

  <div class="d-flex">
    <?php include 'partials/navigation.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow-1 p-3 p-md-4 content-area" style="min-height: 100vh;">

      <!-- Topbar Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom no-print admin-page-titlebar">
        <div>
          <h4 class="fw-bold mb-1 tracking-tight">Rekapitulasi Nilai Siswa</h4>
          <p class="text-secondary small mb-0">Pantau performa evaluasi ujian dan cetak laporan hasil asesmen</p>
        </div>
        <?php if ($detail_exam && mysqli_num_rows($list_nilai) > 0): ?>
          <button onclick="window.print()" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak / Ekspor PDF
          </button>
        <?php endif; ?>
      </div>

      <!-- Filter Pilihan Ujian -->
      <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white no-print">
        <form action="" method="GET" class="row g-2 align-items-center">
          <div class="col-auto">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-filter-circle-fill"></i>
            </span>
          </div>
          <div class="col-auto">
            <label class="small fw-bold text-dark mb-0">Paket Ujian:</label>
          </div>
          <div class="col-md-6 col-lg-5">
            <select name="exam_id" class="form-select rounded-3 admin-mobile-select" aria-label="Pilih paket ujian" onchange="this.form.submit()">
              <?php while ($e = mysqli_fetch_assoc($list_exams)): ?>
                <option value="<?= $e['id'] ?>" <?= ($e['id'] == $exam_id) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($e['judul_ujian']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </form>
      </div>

      <!-- Mini KPI Summary (Jika Ujian Terpilih) -->
      <?php if ($detail_exam): ?>
        <div class="row g-3 mb-4 no-print">
          <div class="col-md-4">
            <div class="mini-stat-box shadow-sm">
              <div class="mini-stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
              </div>
              <div>
                <small class="text-muted fw-semibold d-block">Peserta Selesai</small>
                <h4 class="fw-bold mb-0 text-dark"><?= $total_peserta ?> <span class="small text-muted fw-normal">Siswa</span></h4>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mini-stat-box shadow-sm">
              <div class="mini-stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-award-fill"></i>
              </div>
              <div>
                <small class="text-muted fw-semibold d-block">Nilai Tertinggi</small>
                <h4 class="fw-bold mb-0 text-success"><?= $nilai_tertinggi ?></h4>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mini-stat-box shadow-sm">
              <div class="mini-stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-graph-up-arrow"></i>
              </div>
              <div>
                <small class="text-muted fw-semibold d-block">Rata-Rata Nilai</small>
                <h4 class="fw-bold mb-0 text-dark"><?= $rata_rata ?></h4>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Header Khusus Saat Mode Print -->
      <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h3 class="fw-bold mb-1">BERITA ACARA & REKAPITULASI EVALUASI CBT</h3>
        <h5 class="text-uppercase fw-semibold mb-2"><?= htmlspecialchars($detail_exam['judul_ujian'] ?? '') ?></h5>
        <div class="small text-muted">
          <span>Total Peserta: <?= $total_peserta ?> Siswa</span> &bull;
          <span>Tanggal Cetak Dokumen: <?= date('d F Y, H:i') ?> WIB</span>
        </div>
      </div>

      <!-- Tabel Nilai Siswa -->
      <div class="rekap-card">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4" style="width: 100px;">PERINGKAT</th>
                <th>PESERTA SISWA</th>
                <th>NISN / IDENTITAS</th>
                <th>WAKTU SUBMIT</th>
                <th class="text-end pe-4">NILAI AKHIR</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($detail_exam && mysqli_num_rows($list_nilai) > 0): ?>
                <?php
                $rank = 1;
                while ($n = mysqli_fetch_assoc($list_nilai)):
                  $rankClass = 'rank-general';
                  $rankIcon = "#" . $rank;
                  if ($rank == 1) {
                    $rankClass = 'rank-1';
                    $rankIcon = '<i class="bi bi-trophy-fill text-warning"></i>';
                  } elseif ($rank == 2) {
                    $rankClass = 'rank-2';
                  } elseif ($rank == 3) {
                    $rankClass = 'rank-3';
                  }
                  $studentInitial = mb_strtoupper(mb_substr($n['nama_lengkap'] ?? 'S', 0, 1));
                  $isPassed = ($n['total_nilai'] >= 75);
                ?>
                  <tr>
                    <td class="ps-4">
                      <span class="rank-badge <?= $rankClass ?>" title="Peringkat <?= $rank ?>">
                        <?= $rankIcon ?>
                      </span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar-initial"><?= htmlspecialchars($studentInitial) ?></div>
                        <div>
                          <div class="fw-bold text-dark"><?= htmlspecialchars($n['nama_lengkap']) ?></div>
                          <small class="text-muted d-md-none"><?= htmlspecialchars($n['username']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td class="fw-semibold text-secondary">
                      <code><?= htmlspecialchars($n['username']) ?></code>
                    </td>
                    <td>
                      <div class="small text-dark fw-medium">
                        <i class="bi bi-clock-history me-1 text-muted"></i>
                        <?= date('d M Y', strtotime($n['waktu_selesai'])) ?>
                      </div>
                      <small class="text-muted"><?= date('H:i', strtotime($n['waktu_selesai'])) ?> WIB</small>
                    </td>
                    <td class="text-end pe-4">
                      <span class="score-badge <?= $isPassed ? 'score-pass' : 'score-fail' ?>">
                        <?= $n['total_nilai'] ?>
                      </span>
                    </td>
                  </tr>
                <?php
                  $rank++;
                endwhile;
                ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                      <span class="fw-semibold">Belum Ada Data Rekapitulasi</span>
                      <p class="small text-muted mb-0">Belum ada siswa yang menyelesaikan paket ujian ini.</p>
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

  <?php include 'modal/modal_logout.php'; ?>
  <script src="../assets/js/admin-mobile-select.js?v=2"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>