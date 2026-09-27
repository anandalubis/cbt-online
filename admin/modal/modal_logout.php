<!-- admin/modal/modal_logout.php -->
<div class="modal fade" id="modalKonfirmasiLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 410px;">
    <div class="modal-content border-0 rounded-4 shadow-lg text-center p-4 bg-white" style="border-radius: 1.35rem !important;">
      <div class="modal-body p-0">
        <!-- Modern Danger Icon Badge with Soft Glow -->
        <div class="mb-3 mx-auto d-inline-flex align-items-center justify-content-center rounded-circle"
          style="width: 72px; height: 72px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); box-shadow: 0 0 20px rgba(239, 68, 68, 0.15);">
          <i class="bi bi-box-arrow-right fs-2"></i>
        </div>

        <h5 class="fw-bold text-dark mb-2" id="modalLogoutLabel" style="letter-spacing: -0.02em;">Konfirmasi Keluar</h5>
        <p class="text-secondary small mb-4" style="line-height: 1.55;">
          Apakah Anda yakin ingin mengakhiri sesi administrator ini? Anda perlu melakukan autentikasi ulang untuk mengakses kembali panel kendali.
        </p>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light rounded-3 w-50 py-2 fw-semibold text-secondary border" data-bs-dismiss="modal">
            Batal
          </button>
          <a href="../logout.php" class="btn btn-danger rounded-3 w-50 py-2 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-check2"></i>
            <span>Ya, Keluar</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>