<!-- Modal Edit RKAS -->
<!-- Modal Edit RKAS -->
<div class="modal fade" id="editRkasModal" tabindex="-1" aria-labelledby="editRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editRkasModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data RKAS
                </h5>

                <!-- TOMBOL DELETE DI HEADER -->
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-danger btn-sm me-2" id="btnDeleteAllData"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus semua data kegiatan ini">
                        <i class="bi bi-trash me-1"></i>Hapus Semua
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>

            <form id="editRkasForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                <!-- Hidden field untuk harga satuan numeric -->
                <input type="hidden" name="harga_satuan" id="edit_harga_satuan_actual">
                <!-- Hidden field untuk ID data utama -->
                <input type="hidden" id="edit_main_data_id" name="main_data_id">

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_kegiatan" class="form-label">Kegiatan <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2-kegiatan-edit" id="edit_kegiatan" name="kode_id" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach ($kodeKegiatans as $kode)
                            <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                data-program="{{ $kode->program }}" data-sub-program="{{ $kode->sub_program }}"
                                data-uraian="{{ $kode->uraian }}">
                                {{ $kode->kode }} - {{ $kode->uraian }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_rekening_belanja" class="form-label">Rekening Belanja <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2-rekening-edit" id="edit_rekening_belanja"
                            name="kode_rekening_id" required>
                            <option value="">-- Pilih Rekening Belanja --</option>
                            @foreach ($rekeningBelanjas as $rekening)
                            <option value="{{ $rekening->id }}" data-kode-rekening="{{ $rekening->kode_rekening }}"
                                data-rincian-objek="{{ $rekening->rincian_objek }}">
                                {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit_uraian" class="form-label">Uraian <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_uraian" name="uraian" rows="1"
                                    placeholder="Jelaskan detail barang atau jasa yang akan diadakan..."
                                    required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_harga_satuan" class="form-label">Harga Satuan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <!-- Input untuk tampilan format rupiah -->
                                    <input type="text" class="form-control" id="edit_harga_satuan" placeholder="0"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="anggaran-box">
                        <div class="anggaran-info">
                            <span class="text-white">Di Anggarkan untuk bulan</span>
                            <span class="text-white">Total Anggaran : <span class="text-white" id="edit_total-anggaran">Rp. 0</span></span>
                        </div>

                        <!-- Container untuk card bulan -->
                        <div class="anggaran-bulan-list-container" id="edit_bulanContainer">
                            <!-- Card bulan akan ditambahkan secara dinamis oleh JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="edit_submitBtn">
                        <i class="bi bi-check-circle me-2"></i>Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Style untuk tombol delete di header */
    #btnDeleteAllData {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    #btnDeleteAllData:hover {
        background-color: #c82333;
        border-color: #bd2130;
        transform: translateY(-1px);
    }

    #btnDeleteAllData:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .loading-spinner {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #ffffff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Style untuk input satuan yang readonly */
    .satuan-input:read-only {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
    }
    
    .satuan-input:read-only:focus {
    border-color: #e9ecef;
    box-shadow: none;
    }
    
    /* Style untuk card pertama */
    .anggaran-bulan-card:first-child .satuan-input {
    background-color: #ffffff;
    border-color: #ced4da;
    color: #212529;
    cursor: text;
    }
</style>