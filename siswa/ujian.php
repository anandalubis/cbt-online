<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login'], $_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
  header("Location: ../login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

// Validasi Sesi Ujian
$sesi = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT se.*, e.judul_ujian, e.durasi_menit 
    FROM student_exams se 
    JOIN exams e ON se.exam_id = e.id 
    WHERE se.user_id = $user_id AND se.exam_id =$exam_id
"));

if (!$sesi || $sesi['status'] === 'selesai') {
  header("Location: index.php");
  exit;
}

// Hitung Sisa Waktu (Durasi Menit - Selisih Waktu Mulai)
$waktu_mulai_detik = strtotime($sesi['waktu_mulai']);
$waktu_sekarang_detik = time();
$durasi_total_detik = $sesi['durasi_menit'] * 60;
$detik_berjalan = $waktu_sekarang_detik - $waktu_mulai_detik;
$sisa_detik = $durasi_total_detik - $detik_berjalan;

// Jika sisa waktu sudah habis sebelum buka halaman
if ($sisa_detik <= 0) {
  $sisa_detik = 0;
}

// PROSES SUBMIT JAWABAN & AUTO-GRADING
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ujian'])) {
  $answers = isset($_POST['jawaban']) ? $_POST['jawaban'] : [];
  $student_exam_id = $sesi['id'];

  // Ambil kunci jawaban asli
  $query_questions = mysqli_query($koneksi, "SELECT id, kunci_jawaban FROM questions WHERE exam_id = $exam_id");
  $total_soal = mysqli_num_rows($query_questions);
  $jumlah_benar = 0;

  while ($q = mysqli_fetch_assoc($query_questions)) {
    $qid = $q['id'];
    $jawaban_terpilih = isset($answers[$qid]) ? $answers[$qid] : null;

    // Simpan jawaban siswa ke student_answers
    if ($jawaban_terpilih !== null) {
      mysqli_query($koneksi, "INSERT INTO student_answers (student_exam_id, question_id, jawaban_siswa) VALUES ($student_exam_id, $qid, '$jawaban_terpilih')");
    }

    if ($jawaban_terpilih === $q['kunci_jawaban']) {
      $jumlah_benar++;
    }
  }

  // Hitung Nilai Akhir (Skala 0 - 100)
  $nilai_akhir = ($total_soal > 0) ? ($jumlah_benar / $total_soal) * 100 : 0;
  $waktu_selesai = date('Y-m-d H:i:s');

  // Update status ujian jadi selesai
  mysqli_query($koneksi, "UPDATE student_exams SET total_nilai = $nilai_akhir, waktu_selesai = '$waktu_selesai', status = 'selesai' WHERE id =$student_exam_id");

  header("Location: hasil.php?exam_id=" . $exam_id);
  exit;
}

// Ambil Daftar Soal
$soal_list = mysqli_query($koneksi, "SELECT * FROM questions WHERE exam_id = $exam_id ORDER BY id ASC");
$daftar_soal = [];
while ($soal = mysqli_fetch_assoc($soal_list)) {
  $daftar_soal[] = $soal;
}
$total_soal_tampil = count($daftar_soal);
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
  <title>Lembar Evaluasi - <?= htmlspecialchars($sesi['judul_ujian']) ?></title>
  <!-- Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f1f5f9;
      color: #0f172a;
      user-select: none;
      -webkit-user-select: none;
    }

    /* Floating Sticky Header Timer */
    .sticky-top-timer {
      position: sticky;
      top: 1rem;
      z-index: 1025;
    }

    .timer-container {
      background: #0b1120;
      background-image: radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.25) 0px, transparent 65%);
      color: #ffffff;
      border-radius: 1.25rem;
      padding: 0.95rem 1.45rem;
      box-shadow: 0 16px 36px -6px rgba(15, 23, 42, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(12px);
    }

    .countdown-display {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.45rem;
      font-weight: 700;
      color: #fbbf24;
      background: rgba(251, 191, 36, 0.12);
      border: 1px solid rgba(251, 191, 36, 0.3);
      padding: 0.25rem 0.85rem;
      border-radius: 10px;
      display: inline-block;
      letter-spacing: 0.05em;
    }

    /* Question Cards */
    .exam-question-card {
      border: 1px solid #e2e8f0;
      border-radius: 1.35rem;
      background: #ffffff;
      box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04);
      margin-bottom: 1.5rem;
      padding: 1.85rem 2rem;
      transition: border-color 0.2s ease;
      display: none;
      scroll-margin-top: 8rem;
    }

    .exam-question-card.is-active {
      display: block;
      animation: question-enter 0.24s ease both;
    }

    @keyframes question-enter {
      from {
        opacity: 0;
        transform: translateY(8px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .question-progress-panel {
      padding: 1.25rem 1.4rem;
      border: 1px solid #e2e8f0;
      border-radius: 1.25rem;
      background: #ffffff;
      box-shadow: 0 8px 24px -8px rgba(15, 23, 42, 0.1);
    }

    .question-navigator {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(42px, 1fr));
      gap: 0.55rem;
      margin-top: 1rem;
    }

    .question-nav-button {
      position: relative;
      min-width: 42px;
      min-height: 42px;
      border: 1px solid #dbe3ef;
      border-radius: 12px;
      background: #f8fafc;
      color: #475569;
      font-size: 0.84rem;
      font-weight: 800;
      transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .question-nav-button:hover {
      transform: translateY(-2px);
      border-color: #93c5fd;
    }

    .question-nav-button.is-current {
      outline: 3px solid rgba(37, 99, 235, 0.2);
      outline-offset: 1px;
    }

    .question-nav-button.is-answered {
      border-color: #86efac;
      background: #dcfce7;
      color: #15803d;
    }

    .question-nav-button.is-doubtful {
      border-color: #fcd34d;
      background: #fef3c7;
      color: #b45309;
    }

    .question-status-dot {
      position: absolute;
      top: 4px;
      right: 4px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #f59e0b;
      display: none;
    }

    .question-nav-button.is-doubtful .question-status-dot {
      display: block;
    }

    .question-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 0.8rem 1.1rem;
      color: #64748b;
      font-size: 0.75rem;
      font-weight: 700;
    }

    .question-legend span {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .legend-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: #cbd5e1;
    }

    .legend-dot.answered {
      background: #22c55e;
    }

    .legend-dot.doubtful {
      background: #f59e0b;
    }

    .question-controls {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      border-top: 1px solid #e2e8f0;
      margin-top: 1.5rem;
      padding-top: 1.25rem;
    }

    .doubtful-button {
      border: 1px solid #fcd34d;
      background: #fffbeb;
      color: #a16207;
      font-weight: 700;
    }

    .doubtful-button:hover,
    .doubtful-button.is-marked {
      border-color: #f59e0b;
      background: #fef3c7;
      color: #92400e;
    }

    .question-state-badge {
      font-size: 0.75rem;
      font-weight: 700;
    }

    @media (max-width: 575.98px) {
      .exam-question-card {
        padding: 1.25rem 1rem;
      }

      .question-progress-panel {
        padding: 1rem;
      }

      .question-controls {
        flex-wrap: wrap;
      }

      .question-controls .btn {
        flex: 1 1 auto;
      }

      .doubtful-button {
        order: 3;
        width: 100%;
      }

      .question-navigator {
        grid-template-columns: repeat(auto-fill, minmax(38px, 1fr));
        gap: 0.4rem;
      }
    }

    html[data-theme="dark"] .question-progress-panel {
      background: var(--theme-surface);
      border-color: var(--theme-border);
      box-shadow: var(--theme-shadow);
    }

    html[data-theme="dark"] .question-nav-button {
      background: var(--theme-surface-soft);
      border-color: var(--theme-border-strong);
      color: var(--theme-text-secondary);
    }

    html[data-theme="dark"] .question-nav-button.is-answered {
      background: rgba(34, 197, 94, 0.16);
      border-color: rgba(74, 222, 128, 0.5);
      color: #86efac;
    }

    html[data-theme="dark"] .question-nav-button.is-doubtful,
    html[data-theme="dark"] .doubtful-button,
    html[data-theme="dark"] .doubtful-button.is-marked {
      background: rgba(245, 158, 11, 0.14);
      border-color: rgba(251, 191, 36, 0.48);
      color: #fcd34d;
    }

    html[data-theme="dark"] .option-radio-card {
      background-color: var(--theme-surface-soft);
      border-color: var(--theme-border);
    }

    html[data-theme="dark"] .option-badge-letter {
      background: var(--theme-surface-muted);
      border-color: var(--theme-border);
      color: var(--theme-text-secondary);
    }

    html[data-theme="dark"] .option-text-label,
    html[data-theme="dark"] .question-title {
      color: var(--theme-text) !important;
    }

    html[data-theme="dark"] .option-radio-card.selected-active {
      background: rgba(37, 99, 235, 0.18);
      border-color: #60a5fa;
    }

    html[data-theme="dark"] .option-radio-card.selected-active .option-text-label {
      color: #bfdbfe !important;
    }

    html[data-theme="dark"] .question-controls {
      border-color: var(--theme-border);
    }

    .question-title {
      font-size: 1.12rem;
      font-weight: 600;
      line-height: 1.65;
      color: #1e293b;
    }

    /* Interactive Option Items */
    .option-radio-card {
      position: relative;
      display: flex;
      align-items: center;
      gap: 0.9rem;
      border: 1.5px solid #e2e8f0;
      border-radius: 1rem;
      padding: 0.85rem 1.15rem;
      margin-bottom: 0.65rem;
      cursor: pointer;
      background-color: #ffffff;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .option-radio-card:hover {
      background-color: #f8fafc;
      border-color: #cbd5e1;
      transform: translateX(3px);
    }

    .option-radio-card input[type="radio"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .option-badge-letter {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      background: #f1f5f9;
      color: #475569;
      font-weight: 700;
      font-size: 0.88rem;
      display: grid;
      place-items: center;
      flex-shrink: 0;
      transition: all 0.2s ease;
      border: 1px solid #e2e8f0;
    }

    .option-text-label {
      flex: 1;
      font-size: 0.95rem;
      color: #334155;
      margin-bottom: 0;
      cursor: pointer;
      line-height: 1.5;
    }

    /* Radio Checked State Style */
    .option-radio-card input[type="radio"]:checked+.option-badge-letter {
      background: #2563eb;
      color: #ffffff;
      border-color: #2563eb;
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
    }

    .option-radio-card.selected-active {
      border-color: #3b82f6;
      background: #eff6ff;
    }

    .option-radio-card.selected-active .option-text-label {
      color: #1e40af;
      font-weight: 600;
    }
  </style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=1">
</head>

<body class="student-page">

  <div class="container py-3 py-md-4">

    <!-- Floating Header & Timer Section -->
    <div class="sticky-top-timer mb-4">
      <div class="timer-container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="p-2 rounded-3 bg-white bg-opacity-10 text-primary fs-4 d-none d-sm-grid place-items-center" style="width: 44px; height: 44px;">
            <i class="bi bi-mortarboard-fill text-info"></i>
          </div>
          <div>
            <h5 class="fw-bold mb-0 text-white"><?= htmlspecialchars($sesi['judul_ujian']) ?></h5>
            <small class="text-white-50">
              <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($_SESSION['nama_lengkap']) ?> &bull;
              <span class="text-info"><?= $total_soal_tampil ?> Butir Soal</span>
            </small>
          </div>
        </div>

        <div class="text-end d-flex align-items-center gap-3 ms-auto">
          <button type="button" class="theme-toggle theme-toggle-compact theme-toggle-on-dark" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
            <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
          </button>
          <div class="d-none d-md-block text-end">
            <small class="text-white-50 d-block" style="font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase;">Sisa Waktu Ujian</small>
            <span class="badge bg-danger bg-opacity-25 text-danger-emphasis border border-danger border-opacity-25 px-2 py-0" style="font-size: 0.65rem;">Auto-Submit Aktif</span>
          </div>
          <div id="countdown" class="countdown-display shadow-sm">--:--:--</div>
        </div>
      </div>
    </div>

    <!-- Lembar Pertanyaan Ujian -->
    <form id="formUjian" action="" method="POST">
      <input type="hidden" name="submit_ujian" value="1">

      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <section class="question-progress-panel mb-4" aria-label="Navigasi dan status soal">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
              <div>
                <h6 class="fw-bold mb-1">Navigasi soal</h6>
                <p class="text-secondary small mb-0">Pilih nomor untuk berpindah. Soal ragu-ragu bisa ditinjau kembali kapan saja.</p>
              </div>
              <button type="button" class="btn btn-success rounded-3 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiSelesai">
                <i class="bi bi-check2-circle"></i><span>Selesaikan ujian</span>
              </button>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 mb-2">
              <div class="question-legend" aria-label="Keterangan status soal">
                <span><i class="legend-dot"></i>Belum dijawab</span>
                <span><i class="legend-dot answered"></i>Sudah dijawab</span>
                <span><i class="legend-dot doubtful"></i>Ragu-ragu</span>
              </div>
              <small class="fw-bold text-secondary"><span id="answeredCount">0</span> / <?= $total_soal_tampil ?> dijawab</small>
            </div>
            <div class="progress rounded-pill" style="height: 6px;" role="progressbar" aria-label="Progres jawaban">
              <div id="answerProgress" class="progress-bar bg-success rounded-pill" style="width: 0%; transition: width 0.25s ease;"></div>
            </div>

            <div class="question-navigator" id="questionNavigator" aria-label="Pilih nomor soal">
              <?php foreach ($daftar_soal as $index => $s): ?>
                <button type="button" class="question-nav-button" data-question-target="<?= $index ?>" aria-label="Buka soal nomor <?= $index + 1 ?>">
                  <?= $index + 1 ?><span class="question-status-dot" aria-hidden="true"></span>
                </button>
              <?php endforeach; ?>
            </div>
          </section>

          <?php foreach ($daftar_soal as $index => $s): ?>
            <div class="exam-question-card<?= $index === 0 ? ' is-active' : '' ?>" id="soal-block-<?= (int)$s['id'] ?>" data-question-index="<?= $index ?>">
              <!-- Nomor Soal Pill -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold">
                  <i class="bi bi-question-circle-fill me-1"></i> Soal Nomor <?= $index + 1 ?> dari <?= $total_soal_tampil ?>
                </span>
                <span class="question-state-badge text-secondary" data-question-state>Belum dijawab</span>
              </div>

              <!-- Teks Pertanyaan -->
              <div class="question-title mb-4 ps-1" style="white-space: pre-line;"><?= htmlspecialchars($s['pertanyaan']) ?></div>

              <!-- Container Pilihan Radio -->
              <div class="options-container">
                <!-- Opsi A -->
                <label class="option-radio-card" for="opt_a_<?= $s['id'] ?>">
                  <input type="radio" name="jawaban[<?= $s['id'] ?>]" id="opt_a_<?= $s['id'] ?>" value="A">
                  <span class="option-badge-letter">A</span>
                  <span class="option-text-label"><?= htmlspecialchars($s['opsi_a']) ?></span>
                </label>

                <!-- Opsi B -->
                <label class="option-radio-card" for="opt_b_<?= $s['id'] ?>">
                  <input type="radio" name="jawaban[<?= $s['id'] ?>]" id="opt_b_<?= $s['id'] ?>" value="B">
                  <span class="option-badge-letter">B</span>
                  <span class="option-text-label"><?= htmlspecialchars($s['opsi_b']) ?></span>
                </label>

                <!-- Opsi C -->
                <label class="option-radio-card" for="opt_c_<?= $s['id'] ?>">
                  <input type="radio" name="jawaban[<?= $s['id'] ?>]" id="opt_c_<?= $s['id'] ?>" value="C">
                  <span class="option-badge-letter">C</span>
                  <span class="option-text-label"><?= htmlspecialchars($s['opsi_c']) ?></span>
                </label>

                <!-- Opsi D -->
                <label class="option-radio-card" for="opt_d_<?= $s['id'] ?>">
                  <input type="radio" name="jawaban[<?= $s['id'] ?>]" id="opt_d_<?= $s['id'] ?>" value="D">
                  <span class="option-badge-letter">D</span>
                  <span class="option-text-label"><?= htmlspecialchars($s['opsi_d']) ?></span>
                </label>

                <!-- Opsi E -->
                <label class="option-radio-card" for="opt_e_<?= $s['id'] ?>">
                  <input type="radio" name="jawaban[<?= $s['id'] ?>]" id="opt_e_<?= $s['id'] ?>" value="E">
                  <span class="option-badge-letter">E</span>
                  <span class="option-text-label"><?= htmlspecialchars($s['opsi_e']) ?></span>
                </label>
              </div>

              <div class="question-controls">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3" data-question-previous>
                  <i class="bi bi-arrow-left me-1"></i><span>Sebelumnya</span>
                </button>
                <button type="button" class="btn doubtful-button rounded-3 px-3" data-toggle-doubt>
                  <i class="bi bi-flag me-1"></i><span>Ragu-ragu &amp; Berikutnya</span>
                </button>
                <button type="button" class="btn btn-primary rounded-3 px-3 fw-semibold" data-question-next>
                  <span>Berikutnya</span><i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Tombol Selesaikan Ujian -->
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 mb-5 pb-4">
            <small class="text-secondary">Pastikan semua jawaban dan tanda ragu-ragu sudah diperiksa.</small>
            <button type="button" class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiSelesai">
              <i class="bi bi-check2-circle fs-4"></i>
              <span>Selesaikan & Kumpulkan</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Modal Konfirmasi Selesai Ujian -->
  <div class="modal fade" id="modalKonfirmasiSelesai" tabindex="-1" aria-labelledby="modalSelesaiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content border-0 rounded-4 shadow-lg text-center p-4 bg-white" style="border-radius: 1.35rem !important;">
        <div class="modal-body p-0">
          <div class="mb-3 mx-auto d-inline-flex align-items-center justify-content-center rounded-circle"
            style="width: 74px; height: 74px; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 2px solid rgba(16, 185, 129, 0.25); box-shadow: 0 0 24px rgba(16, 185, 129, 0.15);">
            <i class="bi bi-send-check-fill fs-2"></i>
          </div>

          <h5 class="fw-bold text-dark mb-1" id="modalSelesaiLabel" style="letter-spacing: -0.02em;">Selesaikan Ujian?</h5>
          <p class="text-secondary small mb-2" style="line-height: 1.55;">
            Setelah dikumpulkan, jawaban akan dinilai otomatis dan tidak dapat diubah kembali.
          </p>
          <p id="finishSummary" class="small fw-semibold text-secondary mb-4" aria-live="polite"></p>

          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light rounded-3 w-50 py-2 fw-semibold text-secondary border" data-bs-dismiss="modal">
              Periksa Lagi
            </button>
            <button type="button" id="btnSubmitConfirmed" class="btn btn-success rounded-3 w-50 py-2 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
              <i class="bi bi-check2"></i>
              <span>Ya, Kumpulkan</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Countdown Script & Interactive Radio Highlight -->
  <script>
    let sisaDetik = <?= (int)$sisa_detik ?>;
    const timerEl = document.getElementById('countdown');
    const formUjian = document.getElementById('formUjian');
    const questionCards = Array.from(document.querySelectorAll('.exam-question-card'));
    const questionNavButtons = Array.from(document.querySelectorAll('[data-question-target]'));
    const doubtfulQuestions = [];
    let activeQuestionIndex = 0;

    function formatWaktu(totalDetik) {
      let jam = Math.floor(totalDetik / 3600);
      let menit = Math.floor((totalDetik % 3600) / 60);
      let detik = totalDetik % 60;

      return `${jam.toString().padStart(2, '0')}:${menit.toString().padStart(2, '0')}:${detik.toString().padStart(2, '0')}`;
    }

    const intervalTimer = setInterval(() => {
      if (sisaDetik <= 0) {
        clearInterval(intervalTimer);
        timerEl.innerText = "00:00:00";
        alert("Waktu ujian telah berakhir! Lembar jawaban Anda akan otomatis dikumpulkan ke sistem.");
        formUjian.submit();
      } else {
        timerEl.innerText = formatWaktu(sisaDetik);
        // Highlight jika waktu tinggal 5 menit
        if (sisaDetik <= 300) {
          timerEl.style.color = "#f87171";
          timerEl.style.backgroundColor = "rgba(239, 68, 68, 0.15)";
          timerEl.style.borderColor = "rgba(239, 68, 68, 0.35)";
        }
        sisaDetik--;
      }
    }, 1000);

    function getQuestionStatus(card, index) {
      const isAnswered = Boolean(card.querySelector('input[type="radio"]:checked'));
      if (doubtfulQuestions[index]) return 'doubtful';
      return isAnswered ? 'answered' : 'unanswered';
    }

    function updateQuestionStatuses() {
      let answered = 0;
      let doubtful = 0;
      let unanswered = 0;

      questionCards.forEach((card, index) => {
        const status = getQuestionStatus(card, index);
        const navButton = questionNavButtons[index];
        const stateLabel = card.querySelector('[data-question-state]');
        const doubtButton = card.querySelector('[data-toggle-doubt]');
        const previousButton = card.querySelector('[data-question-previous]');
        const nextButton = card.querySelector('[data-question-next]');
        const hasAnswer = Boolean(card.querySelector('input[type="radio"]:checked'));

        if (hasAnswer) answered++;
        if (doubtfulQuestions[index]) doubtful++;
        if (!hasAnswer) unanswered++;

        navButton.classList.remove('is-answered', 'is-doubtful', 'is-current');
        if (status !== 'unanswered') navButton.classList.add(`is-${status}`);
        if (index === activeQuestionIndex) {
          navButton.classList.add('is-current');
          navButton.setAttribute('aria-current', 'step');
        } else {
          navButton.removeAttribute('aria-current');
        }
        navButton.setAttribute('aria-label', `Soal nomor ${index + 1}, ${status === 'doubtful' ? 'ragu-ragu' : status === 'answered' ? 'sudah dijawab' : 'belum dijawab'}`);

        stateLabel.textContent = status === 'doubtful' ? 'Ditandai ragu-ragu' : status === 'answered' ? 'Sudah dijawab' : 'Belum dijawab';
        stateLabel.className = `question-state-badge ${status === 'doubtful' ? 'text-warning-emphasis' : status === 'answered' ? 'text-success' : 'text-secondary'}`;
        doubtButton.classList.toggle('is-marked', Boolean(doubtfulQuestions[index]));
        doubtButton.querySelector('span').textContent = doubtfulQuestions[index] ? 'Hapus tanda ragu' : 'Ragu-ragu & Berikutnya';
        doubtButton.setAttribute('aria-pressed', doubtfulQuestions[index] ? 'true' : 'false');
        previousButton.disabled = index === 0;
        nextButton.disabled = index === questionCards.length - 1;
      });

      document.getElementById('answeredCount').textContent = answered;
      document.getElementById('answerProgress').style.width = `${questionCards.length ? (answered / questionCards.length) * 100 : 0}%`;
      document.getElementById('finishSummary').textContent = `${answered} dijawab · ${doubtful} ragu-ragu · ${unanswered} belum dijawab`;
    }

    function showQuestion(index, shouldScroll = true) {
      if (!questionCards.length) return;
      activeQuestionIndex = Math.max(0, Math.min(index, questionCards.length - 1));
      questionCards.forEach((card, cardIndex) => card.classList.toggle('is-active', cardIndex === activeQuestionIndex));
      updateQuestionStatuses();
      if (shouldScroll) questionCards[activeQuestionIndex].scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }

    questionNavButtons.forEach(button => {
      button.addEventListener('click', () => showQuestion(Number(button.dataset.questionTarget)));
    });

    questionCards.forEach((card, index) => {
      card.querySelector('[data-question-previous]').addEventListener('click', () => showQuestion(index - 1));
      card.querySelector('[data-question-next]').addEventListener('click', () => showQuestion(index + 1));
      card.querySelector('[data-toggle-doubt]').addEventListener('click', () => {
        if (doubtfulQuestions[index]) {
          doubtfulQuestions[index] = false;
          updateQuestionStatuses();
        } else {
          doubtfulQuestions[index] = true;
          updateQuestionStatuses();
          if (index < questionCards.length - 1) showQuestion(index + 1);
        }
      });
    });

    // Highlight aktif pada kartu opsi yang dipilih
    document.querySelectorAll('.option-radio-card input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const container = this.closest('.options-container');
        container.querySelectorAll('.option-radio-card').forEach(card => card.classList.remove('selected-active'));
        if (this.checked) {
          this.closest('.option-radio-card').classList.add('selected-active');
        }
        updateQuestionStatuses();
      });
    });

    showQuestion(0, false);

    document.getElementById('btnSubmitConfirmed').addEventListener('click', () => formUjian.submit());
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme-toggle.js?v=1"></script>
</body>

</html>