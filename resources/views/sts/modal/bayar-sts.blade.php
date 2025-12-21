<!-- MODAL BAYAR -->
<div class="modal fade" id="modalBayarSTS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sts-modal-content-custom">
            <div class="modal-header sts-modal-header-custom d-flex justify-content-between align-items-center"
                style="background: var(--success-color);">
                <div>
                    <h4 class="modal-title fw-bold m-0">Pembayaran STS</h4>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body sts-modal-body-custom">
                <div class="sts-info-card">
                    <span class="d-block text-muted">Nomor: <span id="payInfoNoSts"
                            class="fw-bold text-dark"></span></span>
                    <span class="d-block text-muted">Tagihan: <span id="payInfoSisa"
                            class="fw-bold text-danger"></span></span>
                </div>
                <form id="formBayarSTS">
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Tanggal Bayar</label>
                        <input type="date" class="form-control sts-form-control-custom" id="payTanggal" required>
                    </div>
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Nominal</label>
                        <input type="text" class="form-control sts-form-control-custom rupiah-input" id="payNominal"
                            required>
                    </div>
                    <button type="submit" class="btn sts-btn-submit-custom"
                        style="background: var(--success-color);">Proses Bayar</button>
                </form>
            </div>
        </div>
    </div>
</div>