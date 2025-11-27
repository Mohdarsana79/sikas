<!-- Modal Tambah RKAS -->
<div class="modal fade" id="tambahRkasModal" tabindex="-1" aria-labelledby="tambahRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tambahRkasModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Data RKAS Perubahan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="tambahRkasForm" method="POST">
                @csrf
                <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                <!-- Hidden field untuk harga satuan numeric -->
                <input type="hidden" name="harga_satuan" id="harga_satuan_actual">

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="kegiatan" class="form-label">Kegiatan <span class="text-danger">*</span></label>
                        <select class="form-select select2-kegiatan" id="kegiatan" name="kode_id" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach ($kodeKegiatans as $kode)
                            <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                data-program="{{ $kode->program }}" data-sub-program="{{ $kode->sub_program }}"
                                data-uraian="{{ $kode->uraian }}" {{ old('kode_id')==$kode->id ? 'selected' : '' }}>
                                {{ $kode->kode }} - {{ $kode->uraian }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="rekening_belanja" class="form-label">Rekening Belanja <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2-rekening" id="rekening_belanja" name="kode_rekening_id"
                            required>
                            <option value="">-- Pilih Rekening Belanja --</option>
                            @foreach ($rekeningBelanjas as $rekening)
                            <option value="{{ $rekening->id }}" data-kode-rekening="{{ $rekening->kode_rekening }}"
                                data-rincian-objek="{{ $rekening->rincian_objek }}" {{
                                old('kode_rekening_id')==$rekening->id ? 'selected' : '' }}>
                                {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="uraian" class="form-label">Uraian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="uraian" name="uraian" rows="1"
                                    placeholder="Jelaskan detail barang atau jasa yang akan diadakan..."
                                    required>{{ old('uraian') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="harga_satuan" class="form-label">Harga Satuan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <!-- Input untuk tampilan format rupiah -->
                                    <input type="text" class="form-control" id="harga_satuan" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="anggaran-box">
                        <div class="anggaran-info">
                            <span class="text-white">Di Anggarkan untuk bulan</span>
                            <span class="text-white">Total Anggaran : <span class="text-white" id="total-anggaran">Rp. 0</span></span>
                        </div>

                        <!-- Container untuk card bulan -->
                        <div class="anggaran-bulan-list-container" id="bulanContainer">
                            <!-- Card bulan akan ditambahkan secara dinamis oleh JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-circle me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(90deg, #5e72e4, #8b5cf6, #d946ef);
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
        font-size: 10pt;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #4b5563;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease-in-out;
    }

    .form-control:focus {
        outline: none;
        border-color: #5e72e4;
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.25);
    }

    .anggaran-box {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
        background-color: #f9fafb;
    }

    .anggaran-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .anggaran-info span {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .anggaran-bulan-list-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .anggaran-bulan-card {
        position: relative;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        background-color: #fff;
        flex: 1 1 calc(50% - 0.5rem);
        min-width: 300px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
        margin-bottom: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .anggaran-bulan-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-color: #5e72e4;
    }

    .anggaran-bulan-card.selected {
        border-color: #5e72e4;
        box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.1);
    }

    .delete-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #ef4444;
        color: #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.2s ease;
        border: none;
        font-size: 12px;
        z-index: 10;
    }

    .delete-btn:hover {
        background-color: #dc2626;
    }

    .bulan-input-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .bulan-input-group select {
        flex: 2;
    }

    .month-total {
        flex: 1;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 0.5rem;
        text-align: center;
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        min-width: 100px;
    }

    .jumlah-satuan-group {
        display: flex;
        gap: 0.5rem;
    }

    .jumlah-satuan-group .form-control {
        flex: 1;
    }

    .input-icon-left {
        position: relative;
        flex: 1;
    }

    .input-icon-left svg {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6B7280;
        z-index: 1;
    }

    .input-icon-left .form-control {
        padding-left: 35px;
    }

    .btn-tambah-card {
        flex: 1 1 calc(50% - 0.5rem);
        min-width: 300px;
        background-color: transparent;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        color: #6b7280;
        font-size: 0.875rem;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        text-align: center;
        padding: 2rem 1rem;
        margin-bottom: 1rem;
    }

    .btn-tambah-card:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
        color: #4b5563;
    }

    .btn-tambah {
        background: none;
        border: none;
        color: inherit;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 0.875rem;
    }

    /* Select2 Custom Styles */
    .select2-container {
        width: 100% !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-selection__rendered {
        line-height: calc(2.25rem) !important;
        padding-left: 12px !important;
        color: #495057 !important;
    }

    .select2-selection__arrow {
        height: calc(2.25rem) !important;
        right: 10px !important;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
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

    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .satuan-input:read-only {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    /* Select2 Custom Table Styles */
    .select2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .select2-table th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 2px solid #dee2e6;
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .select2-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
        word-wrap: break-word;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .select2-table tr:hover {
        background-color: #f8f9fa;
    }

    .select2-results__option--highlighted .select2-table tr {
        background-color: #007bff !important;
        color: white;
    }

    .select2-results__option--highlighted .select2-table th {
        background-color: #0056b3 !important;
        color: white;
    }

    .select2-table-kode {
        width: 80px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #000000;
    }

    .select2-table-program {
        width: 150px;
        font-weight: 500;
    }

    .select2-table-sub-program {
        width: 150px;
    }

    .select2-table-uraian {
        min-width: 200px;
        white-space: normal;
        line-height: 1.4;
    }

    .select2-table-rekening {
        width: 100px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #000000;
    }

    .select2-table-rincian {
        min-width: 250px;
        white-space: normal;
        line-height: 1.4;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .anggaran-bulan-card {
            flex: 1 1 100%;
            min-width: auto;
        }

        .btn-tambah-card {
            flex: 1 1 100%;
            min-width: auto;
        }

        .bulan-input-group {
            flex-direction: column;
        }

        .month-total {
            width: 100%;
            margin-top: 0.5rem;
        }

        .select2-table th,
        .select2-table td {
            padding: 6px 8px;
            font-size: 0.8rem;
        }

        .select2-table-kode {
            width: 60px;
        }

        .select2-table-program,
        .select2-table-sub-program {
            width: 120px;
        }

        .select2-table-uraian,
        .select2-table-rincian {
            min-width: 150px;
        }
    }

    /* Custom select styling */
    select.form-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
</style>
