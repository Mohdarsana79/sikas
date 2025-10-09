<!-- Modal Detail RKAS -->
<div class="modal fade" id="detailRkasModal" tabindex="-1" aria-labelledby="detailRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailRkasModalLabel">
                    <i class="bi bi-eye me-2"></i>Detail Data RKAS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-group mb-3">
                            <label class="detail-label">Program Kegiatan:</label>
                            <div class="detail-value" id="detail-program"></div>
                        </div>
                        <div class="detail-group mb-3">
                            <label class="detail-label">Kegiatan:</label>
                            <div class="detail-value" id="detail-kegiatan"></div>
                        </div>
                        <div class="detail-group mb-3">
                            <label class="detail-label">Rekening Belanja:</label>
                            <div class="detail-value" id="detail-rekening"></div>
                        </div>
                        <div class="detail-group mb-3">
                            <label class="detail-label">Bulan:</label>
                            <div class="detail-value" id="detail-bulan"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-group mb-3">
                            <label class="detail-label">Uraian:</label>
                            <div class="detail-value" id="detail-uraian"></div>
                        </div>
                        <div class="detail-group mb-3">
                            <label class="detail-label">Jumlah:</label>
                            <div class="detail-value" id="detail-jumlah"></div>
                        </div>
                        <div class="detail-group mb-3">
                            <label class="detail-label">Satuan:</label>
                            <div class="detail-value" id="detail-satuan"></div>
                        </div>
                        <div class="detail-group mb-3">
                            <label class="detail-label">Harga Satuan:</label>
                            <div class="detail-value" id="detail-harga-satuan"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="detail-group mb-3">
                            <label class="detail-label">Total Anggaran:</label>
                            <div class="detail-value text-success fw-bold fs-5" id="detail-total"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>