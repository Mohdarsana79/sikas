<style>
    .step-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }

    .step-progress::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #dee2e6;
        transform: translateY(-50%);
        z-index: 1;
    }

    .step-item {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        transition: all 0.3s ease;
    }

    .step-item.active .step-circle {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }

    .step-item.completed .step-circle {
        background-color: #198754;
        border-color: #198754;
        color: white;
    }

    .step-label {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .step-item.active .step-label,
    .step-item.completed .step-label {
        color: #212529;
        font-weight: 500;
    }

    .step-content {
        min-height: 200px;
    }

    .btn-step {
        min-width: 100px;
    }

    .modal-content {
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        border-radius: 12px 12px 0 0;
    }

    .btn-close {
        filter: invert(1);
    }

    .card-header.bg-info-subtle {
        background-color: #d1ecf1 !important;
    }

    .pajak-form-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .pajak-form-col {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
        padding: 0 5px;
    }

    .pajak-card {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
    }

    .pb1-section {
        transition: all 0.3s ease;
    }

    .remove-kegiatan {
        transition: opacity 0.3s ease;
    }

    .remove-kegiatan:hover {
        opacity: 0.8;
    }

    .select2-container {
        z-index: 1060 !important;
    }

    .select2-dropdown {
        z-index: 1061 !important;
    }

    .modal .select2-container {
        z-index: 1060;
    }

    .modal .select2-dropdown {
        z-index: 1061;
    }

    .select2-selection--single {
        height: 38px !important;
        padding: 4px 12px !important;
    }

    .select2-selection__rendered {
        line-height: 30px !important;
        font-size: 0.875rem !important;
    }

    .select2-selection__arrow {
        height: 36px !important;
    }

    /* Style untuk select yang disabled */
    select:disabled {
        background-color: #e9ecef;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .select2-container--disabled .select2-selection {
        background-color: #e9ecef;
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* PERBAIKAN: Style untuk select yang disabled */
    select:disabled {
        background-color: #e9ecef;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .select2-container--disabled .select2-selection {
        background-color: #e9ecef;
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Loading indicator */
    .loading-text {
        color: #6c757d;
        font-style: italic;
    }

    /* Error state */
    .error-text {
        color: #dc3545;
        font-style: italic;
    }

    /* Style untuk informasi penarikan tunai */
    .penarikan-info {
        font-size: 0.875rem;
        padding: 0.75rem;
        border-left: 4px solid #0dcaf0;
    }

    .penarikan-info .bi {
        margin-right: 0.5rem;
    }
</style>

@php
$tahun = $tahun ?? date('Y');
$bulan = $bulan ?? 'Januari';
$bulanList = [
'Januari' => 1,
'Februari' => 2,
'Maret' => 3,
'April' => 4,
'Mei' => 5,
'Juni' => 6,
'Juli' => 7,
'Agustus' => 8,
'September' => 9,
'Oktober' => 10,
'November' => 11,
'Desember' => 12,
];
$bulanAngka = $bulanList[$bulan] ?? 1;
$lastDay = cal_days_in_month(CAL_GREGORIAN, $bulanAngka, $tahun);
@endphp

<!-- Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">
                    <i class="bi bi-receipt me-2"></i>Isi Detail Pembelanjaan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnBatal"></button>
            </div>
            <div class="modal-body bg-light">
                <!-- Step Progress -->
                <div class="step-progress mb-4">
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Detail Transaksi</div>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Detail Barang/Jasa</div>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Perhitungan Pajak</div>
                    </div>
                </div>

                <!-- Step Content -->
                <div class="step-content">
                    <!-- Step 1: Detail Transaksi -->
                    <div class="step-pane active" id="step1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Transaksi</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" class="form-control" name="tanggal_transaksi"
                                        id="tanggal_transaksi" aria-label="Sizing example input"
                                        aria-describedby="dateHelp1"
                                        min="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-01"
                                        max="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($lastDay, 2, '0', STR_PAD_LEFT) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Transaksi</label>
                                <select name="jenis_transaksi" id="jenis_transaksi" class="form-select form-select-sm"
                                    aria-label="Small select example">
                                    <option value="">Pilih Tunai/Nontunai</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="non-tunai">Non Tunai</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="" id="checkForm">
                                    <label class="form-check-label" for="checkForm">
                                        <strong>Centang</strong> jika pembelanjaan ini tidak memiliki toko/badan usaha
                                        (perusahaan, PT, CV, UD, firma, dll.)
                                    </label>
                                </div>
                                <!-- Form Nama Toko/Badan Usaha -->
                                <div id="formToko" class="form-transition">
                                    <div class="mb-3">
                                        <label for="nama_toko" class="form-label">Nama Toko/Badan Usaha</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"><i
                                                    class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" name="nama_penyedia_barang_jasa"
                                                aria-label="Sizing example input"
                                                aria-describedby="inputGroup-sizing-sm" id="nama_toko">
                                        </div>
                                        <input type="hidden" name="nama_penerima_pembayaran" id="nama_penerima_hidden"
                                            value="">
                                    </div>
                                </div>

                                <!-- Form Nama Penerima/Penyedia -->
                                <div id="formPenerima" class="form-transition d-none">
                                    <div class="mb-3">
                                        <label for="nama_penerima" class="form-label">Nama Penerima / Penyedia</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control" name="nama_penerima_pembayaran"
                                                aria-label="Sizing example input"
                                                aria-describedby="inputGroup-sizing-sm" id="nama_penerima">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat_toko" class="form-label">Alamat</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="text" class="form-control" name="alamat"
                                            aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                            id="alamat_toko">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control" name="nomor_telepon"
                                            aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                            id="nomor_telepon">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="npwp" class="form-label">NPWP Toko</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="text" class="form-control" aria-label="Sizing example input"
                                            name="npwp" aria-describedby="inputGroup-sizing-sm" id="npwp">
                                    </div>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="" id="checkNpwp">
                                    <label class="form-check-label" for="checkNpwp">
                                        <strong>Centang</strong> Jika pembelanjaan ini tidak memiliki NPWP
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Detail Barang/Jasa -->
                    <div class="step-pane d-none" id="step2">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nomor_nota" class="form-label">Nomor Nota</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" class="form-control" name="id_transaksi"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        id="nomor_nota">
                                </div>
                                <small class="text-muted" id="lastNotaInfo">
                                    <i class="bi bi-info-circle"></i>
                                    Memuat informasi nomor nota terakhir...
                                </small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_nota" class="form-label">Tanggal Belanja/Nota</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" class="form-control" id="tanggal_nota"
                                        aria-label="Sizing example input" aria-describedby="dateHelp2"
                                        min="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-01"
                                        max="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($lastDay, 2, '0', STR_PAD_LEFT) }}"
                                        required>
                                </div>
                            </div>
                            <!-- Container untuk kegiatan -->
                            <div class="col-md-12 mb-3" id="kegiatanContainer">
                                <!-- Card kegiatan pertama -->
                                <div class="card mb-3 kegiatan-card" data-kegiatan-index="1">
                                    <div
                                        class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">Kegiatan 1</h5>
                                        <button type="button" class="btn btn-sm btn-danger remove-kegiatan d-none">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Kegiatan</label>
                                                <select class="form-select form-select-sm kegiatan-select"
                                                    name="kode_kegiatan_id[]" required>
                                                    <option value="">Memuat data kegiatan...</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Rekening Belanja</label>
                                                <select class="form-select form-select-sm rekening-select"
                                                    name="kode_rekening_id[]" required disabled>
                                                    <option value="">Pilih kegiatan terlebih dahulu</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="uraian_opsional" class="form-label">Uraian Belanja
                                                    (Opsional)</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <textarea class="form-control"
                                                        placeholder="Lunas Bayar Belanja Honorarium Guru"
                                                        id="uraian_opsional" style="height: 10px"></textarea>
                                                </div>
                                                <small class="text-danger">
                                                    <i class="bi bi-info-circle"></i> Uraian akan otomatis oleh sistem
                                                    menggunakan uraian rekening belanja jika uraian belanja tidak diisi
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Container untuk uraian -->
                                        <div class="uraian-container" id="uraianContainer-1">
                                            <!-- Uraian akan dimuat ketika rekening dipilih -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Perhitungan Pajak -->
                    <div class="step-pane d-none" id="step3">
                        <div class="row" id="kegiatanCardsStep3">
                            <!-- Cards kegiatan akan dirender di sini -->
                        </div>
                        <div class="row">
                            <div class="col-md-12 me-2 ms-2 mb-3 card bg-success bg-opacity-10 p-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="checkPajak">
                                    <label class="form-check-label" for="checkPajak">
                                        <strong>Centang</strong> jika pembelanjaan ini dikenakan pajak
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3 d-none" id="pajakForm">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <strong>Form Pajak</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="pajak-form-row">
                                            <div class="pajak-form-col">
                                                <label class="form-label">Jenis Pajak</label>
                                                <select class="form-select form-select-sm" name="jenis_pajak"
                                                    id="selectPajak">
                                                    <option value="">Pilih Jenis Pajak</option>
                                                    <option value="pph 21">PPh 21</option>
                                                    <option value="pph 22">PPh 22</option>
                                                    <option value="pph 23">PPh 23</option>
                                                    <option value="ppn">PPN</option>
                                                </select>
                                            </div>
                                            <div class="pajak-form-col">
                                                <label class="form-label">Persen</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                    <input type="text" class="form-control" name="persen_pajak"
                                                        id="persenPajak" aria-label="Sizing example input"
                                                        aria-describedby="inputGroup-sizing-sm">
                                                </div>
                                            </div>
                                            <div class="pajak-form-col">
                                                <label class="form-label">Total Pajak</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                                    <input type="text" class="form-control" name="total_pajak"
                                                        id="totalPajak" aria-label="Sizing example input"
                                                        aria-describedby="inputGroup-sizing-sm" disabled>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pb1-section d-none" id="pb1Section">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="checkPajakPb1">
                                                <label class="form-check-label" for="checkPajakPb1">
                                                    <strong>Centang</strong> jika pembelanjaan ini dikenakan pajak
                                                    daerah
                                                </label>
                                            </div>
                                            <div class="pajak-card d-none" id="pb1Form">
                                                <div class="pajak-form-row">
                                                    <div class="pajak-form-col">
                                                        <label class="form-label">Jenis Pajak Daerah</label>
                                                        <select class="form-select form-select-sm"
                                                            name="jenis_pajak_daerah" id="selectPb1">
                                                            <option value="pb 1">PB 1</option>
                                                        </select>
                                                    </div>
                                                    <div class="pajak-form-col">
                                                        <label class="form-label">Persen</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"
                                                                id="inputGroup-sizing-sm">%</span>
                                                            <input type="text" class="form-control"
                                                                name="persen_pajak_daerah" id="persenPb1"
                                                                aria-label="Sizing example input"
                                                                aria-describedby="inputGroup-sizing-sm">
                                                        </div>
                                                    </div>
                                                    <div class="pajak-form-col">
                                                        <label class="form-label">Total Pajak</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"
                                                                id="inputGroup-sizing-sm">Rp</span>
                                                            <input type="text" class="form-control"
                                                                name="total_pajak_daerah" id="totalPb1"
                                                                aria-label="Sizing example input"
                                                                aria-describedby="inputGroup-sizing-sm" disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <strong>Total Bersih (Setelah Pajak)</strong>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <span id="totalBersih">Rp. 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnBatal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-outline-primary" id="prevBtn" disabled>
                    <i class="bi bi-arrow-left me-1"></i>Sebelumnya
                </button>
                <button type="button" class="btn btn-primary" id="nextBtn">
                    Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-success d-none" id="saveBtn">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>
