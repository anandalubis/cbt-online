<!-- Modal Konfirmasi Hapus Siswa Modern -->
<div class="modal fade" id="modalHapusSiswa" tabindex="-1" aria-labelledby="modalHapusSiswaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content delete-student-modal border-0 rounded-4 shadow-lg text-center p-4 bg-white">
      <div class="modal-body p-0">
        <!-- Danger Warning Icon Badge -->
        <div class="delete-student-icon mb-3 mx-auto d-inline-flex align-items-center justify-content-center rounded-circle">
          <i class="bi bi-person-x-fill fs-2"></i>
        </div>

        <h5 class="fw-bold text-dark mb-1" id="modalHapusSiswaLabel" style="letter-spacing: -0.02em;">Hapus Akun Siswa?</h5>
        <p class="text-secondary small mb-3">
          Apakah Anda yakin ingin menghapus data akun peserta ujian berikut?
        </p>

        <!-- Preview Kartu Identitas Siswa -->
        <div class="delete-student-preview p-3 rounded-3 border text-start mb-3">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1 rounded-pill small">
              <i class="bi bi-person-badge me-1"></i>Akun Peserta
            </span>
          </div>
          <div class="mb-1">
            <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Nama Lengkap</small>
            <strong class="text-dark d-block" id="teksNamaSiswa">-</strong>
          </div>
          <div>
            <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">NISN / Username</small>
            <span class="delete-student-username font-monospace text-dark fw-semibold" id="teksUsernameSiswa">-</span>
          </div>
        </div>

        <!-- Warning Callout Box -->
        <div class="delete-student-warning p-2 rounded-3 text-start mb-4 d-flex align-items-start gap-2">
          <i class="bi bi-exclamation-triangle-fill text-danger mt-1" style="font-size: 0.85rem;"></i>
          <span class="text-danger" style="font-size: 0.75rem; line-height: 1.45;">
            <strong>Catatan:</strong> Seluruh riwayat pengerjaan dan rekapitulasi nilai ujian milik siswa ini akan ikut terhapus dari sistem.
          </span>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light rounded-3 w-50 py-2 fw-semibold text-secondary border" data-bs-dismiss="modal">
            Batal
          </button>
          <a href="#" id="btnEksekusiHapusSiswa" class="btn btn-danger rounded-3 w-50 py-2 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-trash3"></i>
            <span>Ya, Hapus</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>