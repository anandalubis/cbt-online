<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'siswa') {
  header('Location: ../login.php');
  exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

$exam_result = mysqli_query($koneksi, "SELECT id, judul_ujian, durasi_menit FROM exams WHERE id = $exam_id AND status = 'aktif'");
$exam = $exam_result ? mysqli_fetch_assoc($exam_result) : null;
if (!$exam) {
  header('Location: index.php');
  exit;
}

$completed_result = mysqli_query($koneksi, "SELECT id FROM student_exams WHERE user_id = $user_id AND exam_id = $exam_id AND status = 'selesai' LIMIT 1");
if ($completed_result && mysqli_num_rows($completed_result) > 0) {
  header('Location: index.php');
  exit;
}

$in_progress_result = mysqli_query($koneksi, "SELECT id FROM student_exams WHERE user_id = $user_id AND exam_id = $exam_id AND status = 'mengerjakan' LIMIT 1");
$is_in_progress = $in_progress_result && mysqli_num_rows($in_progress_result) > 0;
$question_result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM questions WHERE exam_id = $exam_id");
$total_soal = $question_result ? (int)mysqli_fetch_assoc($question_result)['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    try {
      document.documentElement.dataset.theme = localStorage.getItem('cbt-portal-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', document.documentElement.dataset.theme);
    } catch (error) {
      document.documentElement.dataset.theme = 'light';
      document.documentElement.setAttribute('data-bs-theme', 'light');
    }
  </script>
  <title>Panduan Ujian - CBT Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/theme.css?v=1">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      background-image:
        radial-gradient(at 10% 0%, rgba(59, 130, 246, 0.09), transparent 42%),
        radial-gradient(at 100% 100%, rgba(14, 165, 233, 0.07), transparent 38%);
    }

    .guide-shell {
      width: min(100% - 2rem, 920px);
      margin: 0 auto;
      padding: 2.25rem 0 3rem;
    }

    .guide-brand {
      color: var(--theme-text);
      text-decoration: none;
      font-weight: 800;
      letter-spacing: -0.03em;
    }

    .guide-brand-mark {
      display: inline-grid;
      width: 42px;
      height: 42px;
      margin-right: 0.6rem;
      place-items: center;
      border-radius: 13px;
      background: linear-gradient(135deg, #2563eb, #60a5fa);
      color: #fff;
      box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
    }

    .guide-card {
      margin-top: 2rem;
      overflow: hidden;
      border: 1px solid var(--theme-border);
      border-radius: 1.5rem;
      background: var(--theme-surface);
      box-shadow: var(--theme-shadow);
    }

    .guide-heading {
      padding: clamp(1.5rem, 5vw, 2.5rem);
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(14, 165, 233, 0.035));
      border-bottom: 1px solid var(--theme-border);
    }

    .guide-heading h1 {
      color: var(--theme-text);
      font-size: clamp(1.55rem, 4vw, 2rem);
      letter-spacing: -0.045em;
    }

    .exam-summary {
      display: flex;
      flex-wrap: wrap;
      gap: 0.65rem;
      margin-top: 1.25rem;
    }

    .summary-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.5rem 0.75rem;
      border: 1px solid var(--theme-border);
      border-radius: 999px;
      background: var(--theme-surface);
      color: var(--theme-text-secondary);
      font-size: 0.82rem;
      font-weight: 700;
    }

    .guide-content {
      padding: clamp(1.25rem, 4vw, 2.25rem);
    }

    .guide-list {
      display: grid;
      gap: 0.85rem;
      margin: 1.25rem 0 1.5rem;
      padding: 0;
      list-style: none;
    }

    .guide-item {
      display: flex;
      align-items: flex-start;
      gap: 0.9rem;
      padding: 1rem;
      border: 1px solid var(--theme-border);
      border-radius: 1rem;
      background: var(--theme-surface-soft);
    }

    .guide-item-icon {
      display: grid;
      width: 38px;
      height: 38px;
      flex: 0 0 38px;
      place-items: center;
      border-radius: 12px;
      background: rgba(37, 99, 235, 0.11);
      color: #2563eb;
      font-size: 1.05rem;
    }

    .guide-item strong {
      display: block;
      margin-bottom: 0.2rem;
      color: var(--theme-text);
      font-size: 0.93rem;
    }

    .guide-item p {
      margin: 0;
      color: var(--theme-text-secondary);
      font-size: 0.86rem;
      line-height: 1.6;
    }

    .guide-notice {
      padding: 1rem 1.1rem;
      border: 1px solid rgba(245, 158, 11, 0.3);
      border-radius: 1rem;
      background: rgba(245, 158, 11, 0.09);
      color: var(--theme-text-secondary);
      font-size: 0.85rem;
      line-height: 1.6;
    }

    .guide-notice strong {
      color: var(--theme-text);
    }

    .guide-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--theme-border);
    }

    .guide-acknowledge {
      max-width: 420px;
      color: var(--theme-text-secondary);
      font-size: 0.84rem;
      line-height: 1.5;
    }

    .guide-acknowledge .form-check-input {
      margin-top: 0.2rem;
    }

    @media (max-width: 575.98px) {
      .guide-shell {
        width: min(100% - 1.25rem, 920px);
        padding-top: 1.25rem;
      }

      .guide-card {
        margin-top: 1.25rem;
        border-radius: 1.2rem;
      }

      .guide-actions {
        align-items: stretch;
        flex-direction: column;
      }

      .guide-actions .btn {
        width: 100%;
      }
    }

    html[data-theme="dark"] .guide-heading {
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), rgba(14, 165, 233, 0.07));
    }

    html[data-theme="dark"] .guide-item-icon {
      background: rgba(59, 130, 246, 0.18);
      color: #93c5fd;
    }
  </style>
</head>

<body class="student-page">
  <main class="guide-shell">
    <div class="d-flex align-items-center justify-content-between gap-3">
      <a href="index.php" class="guide-brand d-inline-flex align-items-center">
        <span class="guide-brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
        CBT Portal
      </a>
      <button type="button" class="theme-toggle theme-toggle-compact" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
        <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
      </button>
    </div>

    <section class="guide-card" aria-labelledby="guide-title">
      <header class="guide-heading">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-3">
          <i class="bi bi-shield-check me-1"></i> Panduan peserta
        </span>
        <h1 class="fw-bold mb-2" id="guide-title">Persiapkan diri sebelum ujian</h1>
        <p class="text-secondary mb-0">Luangkan waktu sejenak untuk membaca tata tertib agar pengerjaan berjalan lancar dan hasil tersimpan dengan baik.</p>
        <div class="exam-summary" aria-label="Ringkasan ujian">
          <span class="summary-chip"><i class="bi bi-journal-text text-primary"></i><?= htmlspecialchars($exam['judul_ujian']) ?></span>
          <span class="summary-chip"><i class="bi bi-patch-question text-primary"></i><?= $total_soal ?> soal</span>
          <span class="summary-chip"><i class="bi bi-clock text-primary"></i><?= (int)$exam['durasi_menit'] ?> menit</span>
        </div>
      </header>

      <div class="guide-content">
        <h2 class="h5 fw-bold mb-1" style="color: var(--theme-text);">Tata tertib pengerjaan</h2>
        <p class="text-secondary small mb-0">Pastikan perangkat dan jaringan siap sebelum memasukkan token ujian.</p>

        <ul class="guide-list">
          <li class="guide-item">
            <span class="guide-item-icon"><i class="bi bi-wifi"></i></span>
            <div><strong>Gunakan koneksi yang stabil</strong>
              <p>Jika memungkinkan, tetap gunakan perangkat dan jaringan yang sama sampai ujian selesai.</p>
            </div>
          </li>
          <li class="guide-item">
            <span class="guide-item-icon"><i class="bi bi-arrow-clockwise"></i></span>
            <div><strong>Jangan refresh atau menutup halaman ujian</strong>
              <p>Waktu ujian tetap berjalan. Jawaban yang belum dikumpulkan dapat hilang jika halaman dimuat ulang atau ditutup.</p>
            </div>
          </li>
          <li class="guide-item">
            <span class="guide-item-icon"><i class="bi bi-stopwatch"></i></span>
            <div><strong>Perhatikan penghitung waktu</strong>
              <p>Ketika waktu habis, sistem akan mengumpulkan jawaban secara otomatis. Jawaban yang belum dipilih akan dianggap kosong.</p>
            </div>
          </li>
          <li class="guide-item">
            <span class="guide-item-icon"><i class="bi bi-check2-square"></i></span>
            <div><strong>Periksa jawaban sebelum mengumpulkan</strong>
              <p>Setelah dikumpulkan, jawaban dinilai dan tidak dapat diubah kembali.</p>
            </div>
          </li>
          <li class="guide-item">
            <span class="guide-item-icon"><i class="bi bi-person-check"></i></span>
            <div><strong>Kerjakan secara mandiri</strong>
              <p>Ikuti instruksi pengawas dan ketentuan akademik yang berlaku selama ujian.</p>
            </div>
          </li>
        </ul>

        <div class="guide-notice mb-4" role="note">
          <i class="bi bi-info-circle-fill text-warning me-1"></i>
          <strong>Catatan waktu:</strong>
          <?php if ($is_in_progress): ?>
            ujian ini sudah dimulai sebelumnya. Sisa waktu terus berjalan sejak waktu mulai pertama, termasuk saat Anda berada di halaman panduan ini.
          <?php else: ?>
            waktu mulai dihitung setelah token yang benar dikonfirmasi. Siapkan token dari pengawas sebelum melanjutkan.
          <?php endif; ?>
        </div>

        <form class="guide-actions" action="konfirmasi.php" method="GET">
          <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
          <label class="form-check guide-acknowledge mb-0">
            <input class="form-check-input" type="checkbox" id="guideAcknowledged" required>
            <span class="form-check-label">Saya telah membaca dan memahami tata tertib ujian.</span>
          </label>
          <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold" id="continueToToken" disabled>
            Lanjut ke Konfirmasi Token <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </form>
      </div>
    </section>
  </main>

  <script>
    const acknowledgement = document.getElementById('guideAcknowledged');
    const continueButton = document.getElementById('continueToToken');
    acknowledgement.addEventListener('change', () => {
      continueButton.disabled = !acknowledgement.checked;
    });
  </script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>