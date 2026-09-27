<!-- Modal Konfirmasi Hapus Soal Modern -->
<div class="modal fade" id="modalHapusSoal" tabindex="-1" aria-labelledby="modalHapusSoalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content border-0 rounded-4 shadow-lg text-center p-4 bg-white" style="border-radius: 1.35rem !important;">
      <div class="modal-body p-0">
        <!-- Danger Warning Icon Badge -->
        <div class="mb-3 mx-auto d-inline-flex align-items-center justify-content-center rounded-circle"
          style="width: 74px; height: 74px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 2px solid rgba(239, 68, 68, 0.25); box-shadow: 0 0 24px rgba(239, 68, 68, 0.15);">
          <i class="bi bi-trash3-fill fs-2"></i>
        </div>

        <h5 class="fw-bold text-dark mb-1" id="modalHapusSoalLabel" style="letter-spacing: -0.02em;">Hapus Butir Soal?</h5>
        <p class="text-secondary small mb-3">
          Apakah Anda yakin ingin menghapus butir pertanyaan ini dari bank soal?
        </p>

        <!-- Cuplikan Soal yang Dipilih -->
        <div class="p-3 rounded-3 border text-start mb-3" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 rounded-pill small" id="labelNomorSoal">
              Soal #
            </span>
            <small class="text-muted" style="font-size: 0.72rem;">Pratinjau Pertanyaan</small>
          </div>
          <p class="text-dark small mb-0 fw-medium fst-italic" id="teksCuplikanSoal" style="line-height: 1.5; max-height: 80px; overflow-y: auto;">
            "..."
          </p>
        </div>

        <!-- Warning Note -->
        <p class="text-danger small mb-4 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.78rem;">
          <i class="bi bi-info-circle-fill"></i> Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
        </p>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light rounded-3 w-50 py-2 fw-semibold text-secondary border" data-bs-dismiss="modal">
            Batal
          </button>
          <a href="#" id="btnEksekusiHapusSoal" class="btn btn-danger rounded-3 w-50 py-2 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-trash3"></i>
            <span>Ya, Hapus</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>