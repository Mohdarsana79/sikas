<!-- MODAL EDIT BAYAR -->
<div class="modal fade" id="modalEditBayarSTS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sts-modal-content-custom">
            <div class="modal-header sts-modal-header-custom d-flex justify-content-between align-items-center"
                style="background: var(--info-color);">
                <div>
                    <h4 class="modal-title fw-bold m-0">Edit Pembayaran</h4>
                    <small class="opacity-75">Sesuaikan nominal bayar</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body sts-modal-body-custom">
                <div class="sts-info-card" style="border-left-color: var(--info-color);">
                    <span class="d-block text-muted">Nomor STS: <span id="editPayInfoNoSts"
                            class="fw-bold text-dark"></span></span>
                </div>
                <form id="formEditBayarSTS">
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Tanggal Bayar</label>
                        <input type="date" class="form-control sts-form-control-custom" id="editPayTanggal" required>
                    </div>
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Nominal Bayar (Update)</label>
                        <input type="text" class="form-control sts-form-control-custom rupiah-input" id="editPayNominal"
                            required>
                    </div>
                    <button type="submit" class="btn sts-btn-submit-custom"
                        style="background: var(--info-color);">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>