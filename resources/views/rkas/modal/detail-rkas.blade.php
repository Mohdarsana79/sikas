<!-- Modal Detail RKAS -->
<div class="modal fade" id="detailRkasModal" tabindex="-1" aria-labelledby="detailRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailRkasModalLabel">
                    <i class="bi bi-eye me-2"></i>Detail Data RKAS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Informasi Kegiatan -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Kegiatan</h6>
                            </div>
                            <div class="card-body bg-light">
                                <!-- Tambahkan bg-light di sini -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Program Kegiatan</label>
                                        <div class="form-control bg-white" id="detail_program_kegiatan">-</div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Kegiatan</label>
                                        <div class="form-control bg-white" id="detail_kegiatan">-</div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Rekening Belanja</label>
                                        <div class="form-control bg-white" id="detail_rekening_belanja">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Informasi Anggaran</h6>
                            </div>
                            <div class="card-body bg-light">
                                <!-- Tambahkan bg-light di sini -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Uraian</label>
                                        <div class="form-control bg-white" id="detail_uraian">-</div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Harga Satuan</label>
                                        <div class="form-control bg-white" id="detail_harga_satuan">Rp 0</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Total Anggaran</label>
                                        <div class="form-control bg-white fw-bold text-primary"
                                            id="detail_total_anggaran">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Bulan -->
                <div class="anggaran-box">
                    <div class="anggaran-info text-white">
                        <span class="text-white"><i class="bi bi-calendar-month me-2"></i>Di Anggarkan untuk bulan</span>
                        <span class="text-white">Total Anggaran : <span class="text-white" id="detail_total_anggaran_display">Rp. 0</span></span>
                    </div>

                    <!-- Container untuk card bulan -->
                    <div class="anggaran-bulan-list-container" id="detail_bulanContainer">
                        <!-- Card bulan akan ditambahkan secara dinamis oleh JavaScript -->
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Tutup
                </button>
                <!-- TOMBOL EDIT DIHAPUS DARI SINI -->
            </div>
        </div>
    </div>
</div>

<style>
    .detail-bulan-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .detail-bulan-card .bulan-input-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .detail-bulan-card .bulan-display {
        flex: 1;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        font-weight: 500;
    }

    .detail-bulan-card .month-total {
        font-weight: 600;
        color: #198754;
        min-width: 120px;
        text-align: right;
    }

    .detail-bulan-card .jumlah-satuan-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .detail-bulan-card .input-display {
        background: white;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
    }

    .anggaran-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px 8px 0 0;
        margin-bottom: 0;
    }

    .anggaran-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .anggaran-bulan-list-container {
        padding: 20px;
        background: linear-gradient(90deg,rgba(185, 194, 199, 1) 0%, rgba(167, 204, 185, 1) 0%, rgba(141, 150, 146, 1) 50%,
        rgba(217, 217, 208, 1) 100%);
    }

    /* Style untuk card body yang lebih gelap */
    .card-body.bg-light {
        background-color: #f8f9fa !important;
    }

    /* Style untuk form-control di card body */
    .card-body.bg-light .form-control {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
    }

    /* Alternatif warna yang lebih gelap */
    .card-body.bg-dark-light {
    background-color: #e9ecef !important;
    }
    
    .card-header.bg-dark-light {
    background-color: #dee2e6 !important;
    border-bottom: 1px solid #ced4da;
    }
    
    .detail-bulan-card.dark-mode {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border: 1px solid #ced4da;
    }
    
    .anggaran-bulan-list-container.dark-container {
    background: #e9ecef;
    }
</style>