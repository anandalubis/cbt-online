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
$total_soal_tampil = mysqli_num_rows($soal_list);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</head>

<body>

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
          <?php
          $no = 1;
          while ($s = mysqli_fetch_assoc($soal_list)):
          ?>
            <div class="exam-question-card" id="soal-block-<?= $s['id'] ?>">
              <!-- Nomor Soal Pill -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold">
                  <i class="bi bi-question-circle-fill me-1"></i> Soal Nomor <?= $no++ ?>
                </span>
                <span class="text-secondary small">Pilihan Ganda</span>
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
            </div>
          <?php endwhile; ?>

          <!-- Tombol Selesaikan Ujian -->
          <div class="text-end mt-4 mb-5 pb-4">
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
          <p class="text-secondary small mb-4" style="line-height: 1.55;">
            Apakah Anda yakin ingin mengumpulkan seluruh lembar jawaban? Setelah dikonfirmasi, jawaban akan dinilai secara otomatis dan tidak dapat diubah kembali.
          </p>

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

    // Highlight aktif pada kartu opsi yang dipilih
    document.querySelectorAll('.option-radio-card input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const container = this.closest('.options-container');
        container.querySelectorAll('.option-radio-card').forEach(card => card.classList.remove('selected-active'));
        if (this.checked) {
          this.closest('.option-radio-card').classList.add('selected-active');
        }
      });
    });

    document.getElementById('btnSubmitConfirmed').addEventListener('click', () => formUjian.submit());
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>