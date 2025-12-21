<!-- MODAL EDIT STS -->
<div class="modal fade" id="modalEditSTS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sts-modal-content-custom">
            <div class="modal-header sts-modal-header-custom d-flex justify-content-between align-items-center"
                style="background: var(--warning-color);">
                <div>
                    <h4 class="modal-title fw-bold m-0">Edit Data STS</h4>
                    <small class="opacity-75">Update data STS yang dipilih</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body sts-modal-body-custom">
                <form id="formEditSTS">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Tahun Anggaran</label>
                        <select name="penganggaran_id" id="editPenganggaranId"
                            class="form-select sts-form-control-custom" required>
                            <option value="" selected disabled>Loading data penganggaran...</option>
                        </select>
                        <small class="text-muted">Tahun tidak dapat diubah karena terkait dengan pembayaran</small>
                    </div>
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Nomor STS</label>
                        <input type="text" name="nomor_sts" id="editNoSts" class="form-control sts-form-control-custom"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="sts-form-label-custom">Jumlah STS</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="jumlah_sts" id="editJmlSts" class="form-control sts-form-control-custom rupiah-input" required>
                        </div>
                        <small class="text-muted">Perhatikan: Jumlah tidak boleh kurang dari yang sudah dibayar</small>
                    </div>
                    <button type="submit" class="btn sts-btn-submit-custom"
                        style="background: var(--warning-color);">Update Data</button>
                </form>
            </div>
        </div>
    </div>
</div>