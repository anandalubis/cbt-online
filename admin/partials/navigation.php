<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$adminNavigation = [
  [
    'file' => 'index.php',
    'label' => 'Dashboard',
    'icon' => 'bi-grid-1x2-fill',
    'badge' => null
  ],
  [
    'file' => 'ujian.php',
    'label' => 'Paket Ujian',
    'icon' => 'bi-layers-half',
    'badge' => null
  ],
  [
    'file' => 'soal.php',
    'label' => 'Bank Soal',
    'icon' => 'bi-patch-question-fill',
    'badge' => null
  ],
  [
    'file' => 'siswa.php',
    'label' => 'Data Siswa',
    'icon' => 'bi-person-bounding-box',
    'badge' => null
  ],
  [
    'file' => 'nilai.php',
    'label' => 'Rekap Nilai',
    'icon' => 'bi-award-fill',
    'badge' => null
  ],
];

$adminNama = $_SESSION['nama_lengkap'] ?? 'Administrator';
$adminInisial = mb_strtoupper(mb_substr($adminNama, 0, 1));
?>

<!-- Mobile Topbar Navbar -->
<header class="admin-mobile-header no-print">
  <div class="d-flex align-items-center gap-2">
    <button class="admin-menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileMenu" aria-controls="adminMobileMenu" aria-label="Buka menu navigasi">
      <i class="bi bi-text-indent-left"></i>
    </button>
    <a class="admin-mobile-brand" href="index.php">
      <span class="admin-brand-icon">
        <i class="bi bi-mortarboard-fill"></i>
      </span>
      <div class="brand-text-wrap">
        <strong>CBT Portal</strong>
        <small>Administrator Pro</small>
      </div>
    </a>
  </div>

  <div class="d-flex align-items-center gap-2">
    <button type="button" class="theme-toggle theme-toggle-compact" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
      <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
    </button>
    <div class="admin-topbar-profile" title="<?= htmlspecialchars($adminNama) ?>">
      <span class="admin-user-avatar">
        <?= htmlspecialchars($adminInisial) ?>
        <span class="status-indicator-dot"></span>
      </span>
    </div>
  </div>
</header>

<!-- Desktop Sidebar Navbar -->
<aside class="sidebar admin-sidebar d-none d-md-flex flex-column text-white flex-shrink-0 no-print">
  <!-- Brand Logo Header -->
  <div class="admin-sidebar-header">
    <a class="admin-sidebar-brand" href="index.php">
      <div class="admin-brand-icon">
        <i class="bi bi-mortarboard-fill"></i>
      </div>
      <div class="brand-text-wrap">
        <strong>CBT Portal</strong>
        <span class="brand-badge"><i class="bi bi-patch-check-fill me-1"></i>PRO EDITION</span>
      </div>
    </a>
  </div>

  <!-- Navigation Links -->
  <div class="admin-nav-scroll flex-grow-1">
    <div class="admin-nav-caption">
      <span>NAVIGASI UTAMA</span>
    </div>
    <ul class="nav nav-pills flex-column admin-nav-list mb-auto">
      <?php foreach ($adminNavigation as $item):
        $isActive = ($currentPage === $item['file']);
      ?>
        <li class="nav-item">
          <a href="<?= htmlspecialchars($item['file']) ?>"
            class="nav-link <?= $isActive ? 'active' : '' ?>"
            <?= $isActive ? 'aria-current="page"' : '' ?>>
            <div class="nav-icon-wrapper">
              <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
            </div>
            <span class="nav-item-title"><?= htmlspecialchars($item['label']) ?></span>
            <?php if ($isActive): ?>
              <span class="nav-active-pill"></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Profile & Logout Card Footer -->
  <div class="admin-sidebar-footer">
    <div class="admin-user-card">
      <div class="position-relative">
        <span class="admin-user-avatar">
          <?= htmlspecialchars($adminInisial) ?>
        </span>
        <span class="status-indicator-dot"></span>
      </div>
      <div class="admin-user-details">
        <strong class="text-truncate"><?= htmlspecialchars($adminNama) ?></strong>
        <small><i class="bi bi-shield-check me-1 text-success"></i>Administrator</small>
      </div>
    </div>
    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
      <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
      <span data-theme-label>Mode gelap</span>
    </button>
    <button type="button" class="admin-logout-button" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiLogout">
      <i class="bi bi-box-arrow-right"></i>
      <span>Keluar Sistem</span>
    </button>
  </div>
</aside>

<!-- Mobile Slide Drawer -->
<div class="offcanvas offcanvas-start admin-drawer no-print" tabindex="-1" id="adminMobileMenu" aria-labelledby="adminMobileMenuLabel">
  <div class="offcanvas-header admin-drawer-header">
    <a class="admin-sidebar-brand" href="index.php" id="adminMobileMenuLabel">
      <div class="admin-brand-icon">
        <i class="bi bi-mortarboard-fill"></i>
      </div>
      <div class="brand-text-wrap">
        <strong>CBT Portal</strong>
        <span class="brand-badge"><i class="bi bi-patch-check-fill me-1"></i>PRO EDITION</span>
      </div>
    </a>
    <button type="button" class="btn-close-modern" data-bs-dismiss="offcanvas" aria-label="Tutup menu">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <div class="offcanvas-body d-flex flex-column p-0">
    <div class="admin-nav-scroll flex-grow-1 px-3 pt-3">
      <div class="admin-nav-caption">
        <span>NAVIGASI UTAMA</span>
      </div>
      <ul class="nav nav-pills flex-column admin-nav-list mb-auto">
        <?php foreach ($adminNavigation as $item):
          $isActive = ($currentPage === $item['file']);
        ?>
          <li class="nav-item">
            <a href="<?= htmlspecialchars($item['file']) ?>"
              class="nav-link <?= $isActive ? 'active' : '' ?>"
              <?= $isActive ? 'aria-current="page"' : '' ?>>
              <div class="nav-icon-wrapper">
                <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
              </div>
              <span class="nav-item-title"><?= htmlspecialchars($item['label']) ?></span>
              <?php if ($isActive): ?>
                <span class="nav-active-pill"></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="admin-sidebar-footer m-3">
      <div class="admin-user-card">
        <div class="position-relative">
          <span class="admin-user-avatar">
            <?= htmlspecialchars($adminInisial) ?>
          </span>
          <span class="status-indicator-dot"></span>
        </div>
        <div class="admin-user-details">
          <strong class="text-truncate"><?= htmlspecialchars($adminNama) ?></strong>
          <small><i class="bi bi-shield-check me-1 text-success"></i>Administrator</small>
        </div>
      </div>
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="Beralih tema" title="Beralih tema">
        <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
        <span data-theme-label>Mode gelap</span>
      </button>
      <button type="button" class="admin-logout-button" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiLogout" data-bs-dismiss="offcanvas">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar Sistem</span>
      </button>
    </div>
  </div>
</div>