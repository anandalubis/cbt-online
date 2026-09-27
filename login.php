<?php
session_start();
require_once 'config/database.php';
global $koneksi;

// Jika sudah login, langsung alihkan sesuai role
if (isset($_SESSION['login'])) {
  if ($_SESSION['role'] === 'admin') {
    header("Location: admin/index.php");
  } else {
    header("Location: siswa/index.php");
  }
  exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
  $password = md5(trim($_POST['password']));

  $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' AND password = '$password'");

  if (mysqli_num_rows($query) === 1) {
    $user = mysqli_fetch_assoc($query);
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
      header("Location: admin/index.php");
    } else {
      header("Location: siswa/index.php");
    }
    exit;
  } else {
    $pesan_error = 'Username atau kata sandi tidak sesuai!';
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - CBT Portal Pro</title>
  <!-- Google Fonts & Bootstrap 5 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    :root {
      --primary: #2563eb;
      --primary-glow: rgba(37, 99, 235, 0.28);
      --dark-base: #0b1120;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      color: #0f172a;
      background-color: #0b1120;
      background-image:
        radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.18) 0px, transparent 60%),
        radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.9) 0px, transparent 50%);
    }

    .login-shell {
      width: min(100%, 1040px);
      min-height: 640px;
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 28px;
      background: #ffffff;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
    }

    /* Aside Branding Banner */
    .login-aside {
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
      padding: 48px;
      color: #ffffff;
      background: linear-gradient(145deg, #090e1a 0%, #111c38 55%, #1e3a8a 100%);
    }

    .login-aside::before {
      content: "";
      position: absolute;
      top: -30%;
      right: -20%;
      width: 420px;
      height: 420px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(59, 130, 246, 0) 70%);
      pointer-events: none;
    }

    .brand-mark {
      width: 44px;
      height: 44px;
      display: inline-grid;
      place-items: center;
      margin-right: 12px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      color: #ffffff;
      font-size: 1.35rem;
      box-shadow: 0 4px 14px var(--primary-glow);
    }

    .brand-badge {
      display: inline-block;
      font-size: 0.64rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      color: #93c5fd;
      background: rgba(59, 130, 246, 0.15);
      border: 1px solid rgba(96, 165, 250, 0.25);
      padding: 0.12rem 0.45rem;
      border-radius: 9999px;
    }

    .aside-copy h1 {
      margin-top: 14px;
      margin-bottom: 16px;
      font-size: clamp(2rem, 3.8vw, 3rem);
      font-weight: 800;
      letter-spacing: -0.04em;
      line-height: 1.15;
      background: linear-gradient(180deg, #ffffff 40%, #cbd5e1 100%);
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .aside-copy>p {
      color: #94a3b8;
      font-size: 0.92rem;
      line-height: 1.7;
    }

    .feature-list {
      display: grid;
      gap: 12px;
      margin-top: 28px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #e2e8f0;
      font-size: 0.85rem;
      background: rgba(255, 255, 255, 0.04);
      padding: 0.65rem 0.95rem;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }

    .feature-item i {
      color: #60a5fa;
      font-size: 1.1rem;
    }

    .online-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #94a3b8;
      font-size: 0.74rem;
    }

    .online-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 10px #10b981;
    }

    /* Right Form Area */
    .login-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 52px clamp(28px, 5vw, 68px);
      background: #ffffff;
    }

    .login-content {
      width: 100%;
      max-width: 380px;
    }

    .field-box {
      position: relative;
      display: flex;
      align-items: center;
      padding-right: 2px;
      overflow: hidden;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      background: #ffffff;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .field-box:focus-within {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.14);
    }

    .field-icon-left {
      flex: 0 0 44px;
      width: 44px;
      text-align: center;
      color: #94a3b8;
      font-size: 1.15rem;
    }

    .field-box .form-control {
      flex: 1 1 0%;
      width: 0;
      min-width: 0;
      border: none;
      box-shadow: none;
      font-size: 0.92rem;
      padding: 0.85rem 0.75rem 0.85rem 0;
      font-weight: 500;
    }

    .field-box .form-control:focus {
      outline: none;
    }

    .btn-toggle-eye {
      flex: 0 0 auto;
      border: none;
      background: transparent;
      padding: 0.5rem 0.85rem;
      color: #94a3b8;
      cursor: pointer;
      transition: color 0.15s ease;
    }

    .btn-toggle-eye:hover {
      color: #334155;
    }

    .btn-submit-modern {
      min-height: 52px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
      box-shadow: 0 8px 20px -3px var(--primary-glow);
      font-weight: 700;
      font-size: 0.95rem;
      transition: all 0.2s ease;
    }

    .btn-submit-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px -3px rgba(37, 99, 235, 0.4);
    }

    /* Test Credentials Box */
    .credentials-card {
      margin-top: 26px;
      padding: 16px;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      background: #f8fafc;
    }

    .credential-chip {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.76rem;
      background: #ffffff;
      padding: 0.25rem 0.55rem;
      border-radius: 6px;
      border: 1px solid #e2e8f0;
      color: #1e293b;
    }

    @media (max-width: 767.98px) {
      .login-shell {
        grid-template-columns: 1fr;
        border-radius: 20px;
        max-width: 480px;
      }

      .login-aside {
        display: none;
      }

      .login-panel {
        padding: 36px 24px;
      }
    }
  </style>
</head>

<body>

  <main class="login-shell">

    <!-- Hero Left Panel -->
    <section class="login-aside" aria-label="Informasi CBT Portal">
      <div class="brand d-flex align-items-center">
        <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
        <div>
          <span class="fw-bold d-block letter-spacing-tight">CBT Portal</span>
          <span class="brand-badge"><i class="bi bi-patch-check-fill me-1"></i>PRO EDITION</span>
        </div>
      </div>

      <div class="aside-copy my-auto py-4">
        <span class="badge bg-white bg-opacity-10 text-white-50 px-3 py-1 rounded-pill small">
          <i class="bi bi-stars text-warning me-1"></i> Asesmen Digital Terpadu
        </span>
        <h1>Evaluasi Cerdas, Hasil Presisi.</h1>
        <p>Platform Computer Based Test modern untuk menunjang proses asesmen akademik yang jujur, cepat, dan transparan.</p>

        <div class="feature-list">
          <div class="feature-item">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Autentikasi terenkripsi & sesi ujian terlindungi</span>
          </div>
          <div class="feature-item">
            <i class="bi bi-stopwatch-fill"></i>
            <span>Sistem auto-submit dan timer real-time</span>
          </div>
          <div class="feature-item">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Penilaian otomatis & rekapitulasi nilai instan</span>
          </div>
        </div>
      </div>

      <div class="online-indicator">
        <span class="online-dot"></span>
        <span>Node Server Online &bull; Siap Melayani Evaluasi</span>
      </div>
    </section>

    <!-- Right Form Login -->
    <section class="login-panel" aria-label="Form Masuk Akun">
      <div class="login-content">

        <!-- Mobile Logo Icon -->
        <div class="d-flex d-md-none align-items-center gap-2 mb-4">
          <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
          <div>
            <h5 class="fw-bold mb-0 text-dark">CBT Portal</h5>
            <small class="text-secondary">Next-Gen Assessment</small>
          </div>
        </div>

        <h3 class="fw-bold text-dark mb-1 tracking-tight">Selamat Datang</h3>
        <p class="text-secondary small mb-4">Silakan masuk menggunakan akun kredensial terdaftar.</p>

        <!-- Pesan Error Feedback -->
        <?php if (!empty($pesan_error)): ?>
          <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5 flex-shrink-0"></i>
            <div><?= htmlspecialchars($pesan_error) ?></div>
          </div>
        <?php endif; ?>

        <!-- Form Autentikasi -->
        <form action="" method="POST" autocomplete="on">
          <div class="mb-3">
            <label class="form-label small fw-bold text-secondary mb-1" for="username">Username / NISN</label>
            <div class="field-box">
              <span class="field-icon-left"><i class="bi bi-person-fill"></i></span>
              <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username atau NISN" autocomplete="username" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold text-secondary mb-1" for="password">Kata Sandi</label>
            <div class="field-box">
              <span class="field-icon-left"><i class="bi bi-lock-fill"></i></span>
              <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
              <button type="button" class="btn-toggle-eye" id="togglePassword" aria-label="Tampilkan kata sandi">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-submit-modern text-white w-100 d-flex align-items-center justify-content-center gap-2">
            <span>Masuk ke Sistem</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </form>

      </div>
    </section>

  </main>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
      const isShowing = passwordInput.type === 'password';
      passwordInput.type = isShowing ? 'text' : 'password';
      togglePassword.innerHTML = isShowing ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      togglePassword.setAttribute('aria-label', isShowing ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>