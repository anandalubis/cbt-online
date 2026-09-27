<?php
session_start();
require_once '../config/database.php';
global $koneksi;

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$exam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM exams WHERE id = $exam_id"));

if (!$exam) {
  die("Paket ujian tidak ditemukan!");
}

$questions = mysqli_query($koneksi, "SELECT * FROM questions WHERE exam_id = $exam_id ORDER BY id ASC");
$total_soal = mysqli_num_rows($questions);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Naskah Soal - <?= htmlspecialchars($exam['judul_ujian']) ?></title>
  <!-- Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    :root {
      --doc-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    body {
      font-family: var(--doc-font);
      background-color: #f1f5f9;
      color: #0f172a;
      -webkit-font-smoothing: antialiased;
    }

    /* Action Bar Toolbar */
    .print-toolbar {
      max-width: 860px;
      margin: 1.5rem auto 1rem;
    }

    /* A4 Paper Canvas View */
    .paper-sheet {
      max-width: 860px;
      margin: 0 auto 3rem;
      background: #ffffff;
      padding: 3rem 3.5rem;
      border-radius: 1rem;
      box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.05);
    }

    /* Kop Dokumen Ujian Formal */
    .doc-header {
      border-bottom: 3px double #1e293b;
      padding-bottom: 1.25rem;
      margin-bottom: 2rem;
      position: relative;
    }

    .doc-meta-table td {
      padding: 0.25rem 0.6rem;
      font-size: 0.88rem;
    }

    /* Soal & Pilihan */
    .question-item {
      page-break-inside: avoid;
      break-inside: avoid;
      margin-bottom: 1.75rem;
    }

    .question-num {
      width: 32px;
      font-weight: 700;
      color: #0f172a;
      font-size: 0.95rem;
      flex-shrink: 0;
    }

    .question-body {
      flex: 1;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    .option-row {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem 1.5rem;
      margin-top: 0.65rem;
      padding-left: 0.25rem;
    }

    .option-item {
      display: flex;
      align-items: baseline;
      gap: 0.5rem;
      font-size: 0.9rem;
    }

    .option-key {
      font-weight: 700;
      color: #334155;
      min-width: 18px;
    }

    /* Print Specific Media Styles */
    @media print {
      body {
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
      }

      .no-print {
        display: none !important;
      }

      .paper-sheet {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
      }

      .doc-header {
        border-bottom: 3px double #000 !important;
      }

      .question-body,
      .option-item,
      .question-num {
        color: #000 !important;
      }
    }
  </style>
</head>

<body>

  <!-- Floating Print Toolbar (Screen Only) -->
  <div class="print-toolbar no-print d-flex align-items-center justify-content-between">
    <a href="soal.php?exam_id=<?= $exam_id ?>" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-medium shadow-sm">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Bank Soal
    </a>
    <button onclick="window.print()" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm">
      <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
    </button>
  </div>

  <!-- Sheet Paper Container -->
  <div class="paper-sheet">
    <!-- Kop Naskah Resmi -->
    <div class="doc-header">
      <div class="text-center mb-3">
        <h5 class="fw-bold mb-1 letter-spacing-tight text-uppercase">NASKAH SOAL EVALUASI COMPUTER BASED TEST (CBT)</h5>
        <h4 class="fw-bold text-uppercase mb-0 text-primary d-print-none"><?= htmlspecialchars($exam['judul_ujian']) ?></h4>
        <h4 class="fw-bold text-uppercase mb-0 d-none d-print-block"><?= htmlspecialchars($exam['judul_ujian']) ?></h4>
      </div>

      <!-- Metadata Box -->
      <div class="border rounded-3 p-2 bg-light d-print-table w-100" style="background-color: #f8fafc !important;">
        <table class="w-100 doc-meta-table">
          <tr>
            <td style="width: 18%;" class="fw-semibold text-secondary">Mata Pelajaran</td>
            <td style="width: 32%;" class="fw-bold text-dark">: <?= htmlspecialchars($exam['judul_ujian']) ?></td>
            <td style="width: 18%;" class="fw-semibold text-secondary">Alokasi Waktu</td>
            <td style="width: 32%;" class="fw-bold text-dark">: <?= $exam['durasi_menit'] ?> Menit</td>
          </tr>
          <tr>
            <td class="fw-semibold text-secondary">Jumlah Soal</td>
            <td class="fw-bold text-dark">: <?= $total_soal ?> Butir Pilihan Ganda</td>
            <td class="fw-semibold text-secondary">Sifat Evaluasi</td>
            <td class="fw-bold text-dark">: Daring / Mandiri</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Petunjuk Singkat -->
    <div class="mb-4 small text-secondary fst-italic">
      <strong>Petunjuk Pengerjaan:</strong> Pilihlah salah satu jawaban yang paling tepat pada huruf A, B, C, D, atau E.
    </div>

    <!-- Butir-Butir Soal -->
    <div class="questions-list">
      <?php if ($total_soal > 0): ?>
        <?php
        $no = 1;
        while ($q = mysqli_fetch_assoc($questions)):
        ?>
          <div class="question-item">
            <div class="d-flex">
              <span class="question-num"><?= $no++ ?>.</span>
              <div class="question-body">
                <div class="mb-2 fw-medium text-dark"><?= nl2br(htmlspecialchars($q['pertanyaan'])) ?></div>
                <div class="option-row">
                  <div class="option-item">
                    <span class="option-key">A.</span>
                    <span><?= htmlspecialchars($q['opsi_a']) ?></span>
                  </div>
                  <div class="option-item">
                    <span class="option-key">B.</span>
                    <span><?= htmlspecialchars($q['opsi_b']) ?></span>
                  </div>
                  <div class="option-item">
                    <span class="option-key">C.</span>
                    <span><?= htmlspecialchars($q['opsi_c']) ?></span>
                  </div>
                  <div class="option-item">
                    <span class="option-key">D.</span>
                    <span><?= htmlspecialchars($q['opsi_d']) ?></span>
                  </div>
                  <div class="option-item" style="grid-column: span 2;">
                    <span class="option-key">E.</span>
                    <span><?= htmlspecialchars($q['opsi_e']) ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-center py-5 text-muted">
          Belum ada butir soal yang ditambahkan pada paket ujian ini.
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>