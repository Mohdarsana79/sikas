<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambahSTS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sts-modal-content-custom">
            <div class="modal-header sts-modal-header-custom d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="modal-title fw-bold m-0">Tambah STS Baru</h4>
                    <small class="opacity-75">Lengkapi formulir di bawah ini</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body sts-modal-body-custom">
                <form id="formTambahSTS">
                    @csrf
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Tahun Anggaran</label>
                        <select name="penganggaran_id" class="form-select sts-form-control-custom" required>
                            <option value="" selected disabled>Loading data penganggaran...</option>
                        </select>
                        <small class="text-muted">Pilih tahun anggaran dari data penganggaran yang tersedia</small>
                    </div>
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Nomor STS</label>
                        <input type="text" name="nomor_sts" class="form-control sts-form-control-custom"
                            placeholder="STS-2024-XXX" required>
                        <small class="text-muted">Format: STS-TAHUN-NOMOR (contoh: STS-2024-001)</small>
                    </div>
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Jumlah STS</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="jumlah_sts" class="form-control sts-form-control-custom rupiah-input"
                                placeholder="Masukkan jumlah" required>
                        </div>
                        <small class="text-muted">Masukkan jumlah dalam rupiah (tanpa titik/koma)</small>
                    </div>
                    <button type="submit" class="btn sts-btn-submit-custom"
                        style="background: var(--primary-color);">Simpan STS</button>
                </form>
            </div>
        </div>
    </div>
</div>