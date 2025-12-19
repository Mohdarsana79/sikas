<!-- Modal Edit Bunga -->
<div class="modal fade" id="editBungaModal" tabindex="-1" aria-labelledby="editBungaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editBungaModalLabel">
                    <i class="bi bi-currency-exchange me-2"></i>Edit Bunga Bank
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formEditBunga">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Edit data bunga bank dan pajak bunga bank untuk bulan {{ $bulan }} {{ $tahun }}
                    </div>

                    <div class="mb-3">
                        <label for="edit_bunga_bank" class="form-label fw-semibold">
                            <i class="bi bi-cash-coin me-1"></i> Bunga Bank
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <!-- PERBAIKAN SEDERHANA: Gunakan type="number" dengan step="any" -->
                            <input type="number" class="form-control" id="edit_bunga_bank" name="bunga_bank"
                                value="{{ $bunga_bank ?? 0 }}" placeholder="0" autocomplete="off" step="0.01"
                            min="0">
                        </div>
                        <div class="invalid-feedback" id="bunga_bank_error"></div>
                        <small class="text-muted">Contoh: 1275 atau 1275.50</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_pajak_bunga_bank" class="form-label fw-semibold">
                            <i class="bi bi-receipt me-1"></i> Pajak Bunga Bank
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <!-- PERBAIKAN SEDERHANA: Gunakan type="number" dengan step="any" -->
                            <input type="number" class="form-control" id="edit_pajak_bunga_bank" name="pajak_bunga_bank"
                                value="{{ $pajak_bunga_bank ?? 0 }}" placeholder="0" autocomplete="off" step="0.01" 
                            min="0">
                        </div>
                        <div class="invalid-feedback" id="pajak_bunga_bank_error"></div>
                        <small class="text-muted">Contoh: 1275 atau 1275.50</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanEditBunga">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>