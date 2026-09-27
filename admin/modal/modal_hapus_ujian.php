<!-- Modal Konfirmasi Hapus Ujian Modern -->
<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content border-0 rounded-4 shadow-lg text-center p-4 bg-white" style="border-radius: 1.35rem !important;">
      <div class="modal-body p-0">
        <!-- Danger Warning Icon Badge -->
        <div class="mb-3 mx-auto d-inline-flex align-items-center justify-content-center rounded-circle"
          style="width: 74px; height: 74px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 2px solid rgba(239, 68, 68, 0.25); box-shadow: 0 0 24px rgba(239, 68, 68, 0.15);">
          <i class="bi bi-trash3-fill fs-2"></i>
        </div>

        <h5 class="fw-bold text-dark mb-1" id="modalHapusLabel" style="letter-spacing: -0.02em;">Hapus Paket Ujian?</h5>
        <p class="text-secondary small mb-3">
          Apakah Anda yakin ingin menghapus paket evaluasi berikut dari sistem?
        </p>

        <!-- Box Preview Target Ujian -->
        <div class="p-3 rounded-3 border mb-3 text-start d-flex align-items-center gap-2" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
          <div class="p-2 rounded-2 bg-white border text-primary" style="font-size: 1.1rem; flex-shrink: 0;">
            <i class="bi bi-journal-x"></i>
          </div>
          <div class="text-truncate">
            <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Paket Ujian Terpilih</small>
            <strong class="text-dark small text-truncate d-block" id="teksNamaUjian">-</strong>
          </div>
        </div>

        <!-- Warning Callout Box -->
        <div class="p-2 rounded-3 text-start mb-4 d-flex align-items-start gap-2" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18);">
          <i class="bi bi-exclamation-octagon-fill text-danger mt-1" style="font-size: 0.85rem;"></i>
          <span class="text-danger" style="font-size: 0.75rem; line-height: 1.45;">
            <strong>Perhatian:</strong> Tindakan ini permanen. Seluruh bank butir soal dan catatan rekapitulasi nilai siswa terkait paket ini akan terhapus.
          </span>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light rounded-3 w-50 py-2 fw-semibold text-secondary border" data-bs-dismiss="modal">
            Batal
          </button>
          <a href="#" id="btnEksekusiHapus" class="btn btn-danger rounded-3 w-50 py-2 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-trash3"></i>
            <span>Ya, Hapus Data</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>