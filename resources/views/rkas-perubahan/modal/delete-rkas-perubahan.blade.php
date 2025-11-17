<!-- Modal Hapus RKAS -->
<div class="modal fade" id="deleteRkasModal" tabindex="-1" aria-labelledby="deleteRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteRkasModalLabel">
                    <i class="bi bi-trash me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Apakah Anda yakin?</h4>
                    <p class="text-muted">Data RKAS yang dihapus tidak dapat dikembalikan!</p>

                    <div class="card bg-body-secondary border mt-3">
                        <div class="row ms-2 me-2">
                            <div class="col-6 text-start">
                                <strong>Kegiatan:</strong><br>
                                <span id="delete-kegiatan-info"></span>
                            </div>
                            <div class="col-6 text-start">
                                <strong>Bulan:</strong><br>
                                <span id="delete-bulan-info"></span>
                            </div>
                        </div>
                        <div class="row ms-2 me-2 mt-2">
                            <div class="col-12 text-start">
                                <strong>Total Anggaran:</strong><br>
                                <span class="text-danger fw-bold" id="delete-total-info"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-2"></i>Ya, Hapus Data
                </button>
            </div>
        </div>
    </div>
</div>