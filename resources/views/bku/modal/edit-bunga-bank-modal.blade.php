<!-- Tambahkan modal untuk edit bunga bank -->
<div class="modal fade" id="editBungaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bunga Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditBunga">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_bunga_bank" class="form-label">Bunga Bank</label>
                        <input type="number" class="form-control" id="edit_bunga_bank" name="bunga_bank"
                            value="{{ $bunga_bank }}" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_pajak_bunga_bank" class="form-label">Pajak Bunga Bank</label>
                        <input type="number" class="form-control" id="edit_pajak_bunga_bank" name="pajak_bunga_bank"
                            value="{{ $pajak_bunga_bank }}" step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>