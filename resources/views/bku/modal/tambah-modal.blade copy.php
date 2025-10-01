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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                                <label for="uraian_opsional" class="form-label">Uraian Belanja (Opsional)</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <textarea class="form-control" placeholder="Lunas Bayar Belanja Honorarium Guru" id="uraian_opsional"
                                                        style="height: 10px"></textarea>
                                                </div>
                                                <small class="text-danger">
                                                    <i class="bi bi-info-circle"></i> Uraian akan otomatis oleh sistem menggunakan uraian rekening belanja jika uraian belanja tidak diisi
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Debug info
        console.log('=== TRANSACTION MODAL SCRIPT LOADED ===');
        console.log('jQuery version:', $.fn.jquery);
        console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');

        let currentStep = 1;
        const totalSteps = 3;
        const tahun = '{{ $tahun }}';
        const bulan = '{{ $bulan }}';

        // Variabel global untuk menyimpan data
        window.currentTotalTransaksi = 0;
        window.kegiatanData = [];
        window.rekeningData = [];

        // PERBAIKAN: Fungsi inisialisasi Select2 yang lebih robust
        function initializeSelect2() {
            try {
                console.log('Initializing Select2...');

                // Destroy existing Select2 instances
                $('.kegiatan-select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                        console.log('Destroyed existing Select2 on kegiatan-select');
                    }
                });

                $('.rekening-select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                        console.log('Destroyed existing Select2 on rekening-select');
                    }
                });

                // Initialize Select2 dengan konfigurasi yang sederhana
                $('.kegiatan-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#transactionModal'),
                    minimumResultsForSearch: 1
                });

                $('.rekening-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#transactionModal'),
                    minimumResultsForSearch: 1
                });

                console.log('Select2 initialized successfully');
            } catch (error) {
                console.error('Error initializing Select2:', error);
            }
        }

        // Fungsi untuk scroll ke atas
        function scrollToTop() {
            $('.modal-body').animate({
                scrollTop: 0
            }, 300);
        }

        // Fungsi untuk mendapatkan range tanggal
        function getDateRangeForMonth(bulan, tahun) {
            const bulanAngka = {
                'Januari': 1,
                'Februari': 2,
                'Maret': 3,
                'April': 4,
                'Mei': 5,
                'Juni': 6,
                'Juli': 7,
                'Agustus': 8,
                'September': 9,
                'Oktober': 10,
                'November': 11,
                'Desember': 12
            } [bulan] || 1;

            const lastDay = new Date(tahun, bulanAngka, 0).getDate();

            return {
                min: `${tahun}-${String(bulanAngka).padStart(2, '0')}-01`,
                max: `${tahun}-${String(bulanAngka).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
            };
        }

        // Fungsi untuk memformat angka ke format Rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // PERBAIKAN: Update fungsi validasi tanggal dengan logging
        function validateDate(inputElement, bulan, tahun) {
            const selectedDate = new Date(inputElement.value);
            const minDate = new Date(inputElement.min);
            const maxDate = new Date(inputElement.max);

            console.log('Validating date:', {
                selected: inputElement.value,
                min: inputElement.min,
                max: inputElement.max,
                selectedDate: selectedDate,
                minDate: minDate,
                maxDate: maxDate
            });

            if (inputElement.value && (selectedDate < minDate || selectedDate > maxDate)) {
                // Format tanggal untuk pesan error
                const minFormatted = new Date(minDate).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                const maxFormatted = new Date(maxDate).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                let errorMessage = `Tanggal harus antara ${minFormatted} dan ${maxFormatted}`;

                // Jika ada penarikan tunai, tambahkan informasi khusus
                const defaultMin = getDateRangeForMonth(bulan, tahun).min;
                if (minDate > new Date(defaultMin)) {
                    errorMessage += ` (dibatasi oleh tanggal penarikan tunai)`;
                }

                inputElement.value = inputElement.min;
                console.log('Date validation failed, setting to min:', inputElement.min);

                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: errorMessage,
                    confirmButtonColor: '#0d6efd',
                });
                return false;
            }

            console.log('Date validation passed');
            return true;
        }

        // Fungsi untuk menonaktifkan tanggal di luar range
        function disableOutOfRangeDates() {
            const dateRange = getDateRangeForMonth(bulan, tahun);
            $('#tanggal_transaksi, #tanggal_nota').attr({
                'min': dateRange.min,
                'max': dateRange.max
            });
        }

        // PERBAIKAN: Update modal show event - Urutkan dengan benar
        $('#transactionModal').on('show.bs.modal', function() {
            console.log('=== MODAL SHOW EVENT TRIGGERED ===');

            resetSteps();

            // Reset data
            window.kegiatanData = [];
            window.rekeningData = [];
            window.currentTotalTransaksi = 0;

            // Pertama: Set default dates dulu
            const dateRange = getDateRangeForMonth(bulan, tahun);
            console.log('Initial date range:', dateRange);

            $('#tanggal_transaksi, #tanggal_nota').attr({
                'min': dateRange.min,
                'max': dateRange.max
            }).val(dateRange.min);

            // PERBAIKAN: Set state awal untuk select elements
            $('.kegiatan-select').html('<option value="">Memuat data kegiatan...</option>').prop('disabled', true);
            $('.rekening-select').html('<option value="">Pilih kegiatan terlebih dahulu</option>').prop('disabled', true);

            // KETIGA: Load data lainnya
            loadKegiatanDanRekening();
            loadLastNotaNumber();
        });

        // PERBAIKAN: Fungsi untuk memuat data kegiatan dan rekening
        function loadKegiatanDanRekening() {
            console.log('Loading kegiatan dan rekening untuk:', tahun, bulan);

            // Tampilkan loading state
            $('.kegiatan-select').html('<option value="">Memuat data...</option>').prop('disabled', true);

            $.ajax({
                url: '/bku/kegiatan-rekening/' + tahun + '/' + bulan,
                method: 'GET',
                success: function(response) {
                    console.log('API Response received:', response);

                    if (response.success) {
                        window.kegiatanData = response.kegiatan_list || [];
                        window.rekeningData = response.rekening_list || [];

                        console.log('Data loaded successfully:', {
                            kegiatan: window.kegiatanData.length,
                            rekening: window.rekeningData.length
                        });

                        // Populate select kegiatan
                        populateKegiatanSelect();

                    } else {
                        console.error('Error in API response:', response.message);
                        showError('Gagal memuat data: ' + (response.message || 'Unknown error'));
                        setErrorState();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });

                    showError('Gagal terhubung ke server. Silakan coba lagi.');
                    setErrorState();
                }
            });
        }

        // PERBAIKAN: Fungsi untuk mengisi select kegiatan
        function populateKegiatanSelect() {
            const kegiatanSelects = $('.kegiatan-select');

            kegiatanSelects.each(function() {
                const $select = $(this);
                $select.empty();

                if (window.kegiatanData && window.kegiatanData.length > 0) {
                    $select.append('<option value="">Pilih Jenis Kegiatan</option>');

                    window.kegiatanData.forEach(function(kegiatan) {
                        $select.append(
                            `<option value="${kegiatan.id}">${kegiatan.kode} - ${kegiatan.uraian}</option>`
                        );
                    });

                    $select.prop('disabled', false);
                    console.log('Kegiatan select populated with', window.kegiatanData.length, 'items');

                } else {
                    $select.append('<option value="" disabled>Tidak ada kegiatan tersedia untuk bulan ini</option>');
                    $select.prop('disabled', true);
                    console.warn('No kegiatan data available for selection');
                }
            });

            // PERBAIKAN: Initialize Select2 setelah data dimuat
            setTimeout(() => {
                try {
                    initializeSelect2();
                    console.log('Select2 initialized after data load');
                } catch (error) {
                    console.error('Error initializing Select2 after data load:', error);
                }
            }, 200);

            // Update tombol next
            if (window.kegiatanData.length === 0) {
                $('#nextBtn').prop('disabled', true);
                console.log('Next button disabled - no kegiatan data');
            } else {
                $('#nextBtn').prop('disabled', false);
                console.log('Next button enabled - kegiatan data available');
            }
        }

        // Fungsi untuk reload data ketika bulan berubah
        function reloadKegiatanDanRekening() {
            console.log('Reloading data untuk bulan:', bulan);

            // Reset data
            window.kegiatanData = [];
            window.rekeningData = [];

            // Tampilkan loading state
            $('.kegiatan-select').html('<option value="">Memuat data...</option>').prop('disabled', true);
            $('.rekening-select').html('<option value="">Pilih kegiatan terlebih dahulu</option>').prop('disabled', true);

            // Load data baru
            loadKegiatanDanRekening();
        }

        // Event handler untuk perubahan bulan (jika ada selector bulan di modal)
        $('#selectBulan').change(function() {
            bulan = $(this).val();
            reloadKegiatanDanRekening();
        });

        // PERBAIKAN: Event handler untuk perubahan pilihan kegiatan
        $(document).on('change', '.kegiatan-select', function() {
            const kegiatanId = $(this).val();
            const card = $(this).closest('.kegiatan-card');
            const rekeningSelect = card.find('.rekening-select');
            const uraianContainer = card.find('.uraian-container');

            console.log('Kegiatan changed:', kegiatanId);

            // Reset rekening dan uraian
            rekeningSelect.empty();
            uraianContainer.empty();

            if (kegiatanId) {
                // Filter rekening berdasarkan kegiatan yang dipilih
                const rekeningForKegiatan = window.rekeningData.filter(function(rekening) {
                    return rekening.kegiatan_id == kegiatanId;
                });

                console.log('Rekening for kegiatan', kegiatanId, ':', rekeningForKegiatan.length);

                if (rekeningForKegiatan.length === 0) {
                    rekeningSelect.append('<option value="" disabled>Tidak ada rekening tersedia untuk kegiatan ini</option>');
                    rekeningSelect.prop('disabled', true);

                    uraianContainer.html('<div class="alert alert-warning mt-2">Tidak ada rekening belanja yang tersedia untuk kegiatan ini.</div>');
                } else {
                    rekeningSelect.append('<option value="">Pilih Rekening Belanja</option>');
                    rekeningSelect.prop('disabled', false);

                    rekeningForKegiatan.forEach(function(rekening) {
                        rekeningSelect.append(
                            `<option value="${rekening.id}">${rekening.kode_rekening} - ${rekening.rincian_objek}</option>`
                        );
                    });
                }

                // PERBAIKAN: Reinitialize Select2 untuk rekening
                setTimeout(() => {
                    rekeningSelect.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('#transactionModal'),
                        minimumResultsForSearch: 10
                    });
                }, 100);

            } else {
                rekeningSelect.append('<option value="">Pilih kegiatan terlebih dahulu</option>');
                rekeningSelect.prop('disabled', true);

                setTimeout(() => {
                    rekeningSelect.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('#transactionModal'),
                        minimumResultsForSearch: 10
                    });
                }, 100);
            }
        });

        // Event handler untuk perubahan pilihan rekening
        $(document).on('change', '.rekening-select', function() {
            const rekeningId = $(this).val();
            const card = $(this).closest('.kegiatan-card');
            const kegiatanSelect = card.find('.kegiatan-select');
            const kegiatanId = kegiatanSelect.val();
            const uraianContainer = card.find('.uraian-container');
            const kegiatanIndex = card.data('kegiatan-index');

            console.log('Rekening changed:', rekeningId, 'for kegiatan:', kegiatanId);

            if (rekeningId && kegiatanId) {
                uraianContainer.html(
                    '<div class="text-center py-3"> <div class="spinner-border spinner-border-sm" role="status"></div> Memuat uraian... </div>'
                );

                $.ajax({
                    url: '/bku/uraian/' + tahun + '/' + bulan + '/' + rekeningId + '?kegiatan_id=' + kegiatanId,
                    method: 'GET',
                    success: function(response) {
                        uraianContainer.empty();

                        if (response.success) {
                            if (response.data && response.data.length > 0) {
                                renderUraianOptions(response.data, kegiatanIndex, uraianContainer);
                            } else {
                                uraianContainer.html(
                                    '<p class="text-muted">Tidak ada uraian untuk kombinasi kegiatan dan rekening ini.</p>'
                                );
                            }
                        } else {
                            uraianContainer.html(
                                '<p class="text-danger">Error: ' + (response.message || 'Gagal memuat uraian') + '</p>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading uraian:', xhr);
                        uraianContainer.html(
                            '<p class="text-danger">Error loading uraian. Silakan coba lagi.</p>'
                        );
                    }
                });
            } else {
                uraianContainer.empty();
                if (!kegiatanId) {
                    uraianContainer.html('<p class="text-warning">Pilih kegiatan terlebih dahulu</p>');
                }
            }
        });

        // Fungsi untuk menampilkan error
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#0d6efd',
            });
        }

        // Fungsi untuk set error state
        function setErrorState() {
            $('.kegiatan-select').html('<option value="" disabled>Gagal memuat data</option>').prop('disabled', true);
            $('.rekening-select').html('<option value="" disabled>Pilih kegiatan terlebih dahulu</option>').prop('disabled', true);

            setTimeout(() => {
                initializeSelect2();
            }, 100);
        }

        // Validasi tanggal saat input berubah
        $('#tanggal_transaksi, #tanggal_nota').on('change', function() {
            validateDate(this, bulan, tahun);
        });

        // Next button click
        $('#nextBtn').click(function() {
            console.log('Next button clicked, current step:', currentStep);
            if (currentStep < totalSteps) {
                if (currentStep === 1) {
                    if (!validateStep1()) {
                        console.log('Step 1 validation failed');
                        return;
                    }
                } else if (currentStep === 2) {
                    if (!validateStep2()) {
                        console.log('Step 2 validation failed');
                        return;
                    }

                    if (!validateAllVolumes()) {
                        console.log('Volume validation failed');
                        return;
                    }
                }

                currentStep++;
                console.log('Moving to step:', currentStep);
                updateSteps();
                scrollToTop();

                if (currentStep === 3) {
                    updateTotalTransaksiDisplay();
                }
            }
        });

        // Previous button click
        $('#prevBtn').click(function() {
            if (currentStep > 1) {
                currentStep--;
                updateSteps();
                scrollToTop();
            }
        });

        // Fungsi untuk validasi step 1
        function validateStep1() {
            const tanggalTransaksi = $('#tanggal_transaksi').val();
            const jenisTransaksi = $('#jenis_transaksi').val();
            const namaToko = $('#nama_toko').val();
            const namaPenerima = $('#nama_penerima').val();
            const alamat = $('#alamat_toko').val();
            const isTokoChecked = $('#checkForm').is(':checked');

            if (!tanggalTransaksi) {
                showValidationError('Tanggal transaksi harus diisi');
                $('#tanggal_transaksi').focus();
                return false;
            }

            if (!validateDate(document.getElementById('tanggal_transaksi'), bulan, tahun)) {
                return false;
            }

            if (!jenisTransaksi) {
                showValidationError('Jenis transaksi harus dipilih');
                $('#jenis_transaksi').focus();
                return false;
            }

            if (!isTokoChecked && !namaToko) {
                showValidationError('Nama toko/badan usaha harus diisi');
                $('#nama_toko').focus();
                return false;
            }

            if (isTokoChecked && !namaPenerima) {
                showValidationError('Nama penerima/penyedia harus diisi');
                $('#nama_penerima').focus();
                return false;
            }

            if (!alamat) {
                showValidationError('Alamat harus diisi');
                $('#alamat_toko').focus();
                return false;
            }

            return true;
        }

        // Fungsi untuk validasi step 2
        function validateStep2() {
            const nomorNota = $('#nomor_nota').val();
            const tanggalNota = $('#tanggal_nota').val();

            console.log('Validating step 2:', {
                nomorNota,
                tanggalNota
            });

            // Validasi jika tidak ada kegiatan yang tersedia
            if (window.kegiatanData && window.kegiatanData.length === 0) {
                showValidationError('Tidak ada kegiatan yang tersedia untuk bulan ' + bulan + '. Silakan pilih bulan lain.');
                return false;
            }

            if (!nomorNota) {
                showValidationError('Nomor nota harus diisi');
                $('#nomor_nota').focus();
                return false;
            }

            if (!tanggalNota) {
                showValidationError('Tanggal belanja/nota harus diisi');
                $('#tanggal_nota').focus();
                return false;
            }

            if (!validateDate(document.getElementById('tanggal_nota'), bulan, tahun)) {
                return false;
            }

            const kegiatanDipilih = $('.kegiatan-select').filter(function() {
                return $(this).val() !== '';
            }).length > 0;

            if (!kegiatanDipilih) {
                showValidationError('Minimal satu kegiatan harus dipilih');
                return false;
            }

            const uraianDipilih = $('.uraian-checkbox:checked').length > 0;
            if (!uraianDipilih) {
                showValidationError('Minimal satu uraian harus dipilih');
                return false;
            }

            let volumeMelebihiMaksimal = false;
            let uraianMelebihi = '';

            $('.uraian-checkbox:checked').each(function() {
                const uraianItem = $(this).closest('.uraian-item');
                const jumlahInput = uraianItem.find('.jumlah-input');
                const maxVolume = parseFloat(jumlahInput.attr('max')) || 0;
                const volume = parseFloat(jumlahInput.val()) || 0;
                const uraianText = uraianItem.find('.form-check-label').text();

                if (volume > maxVolume) {
                    volumeMelebihiMaksimal = true;
                    uraianMelebihi = uraianText;
                    return false;
                }
            });

            if (volumeMelebihiMaksimal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Volume Melebihi Maksimal',
                    html: `Maaf, jumlah volume melebihi jumlah maksimal untuk uraian:<br><strong>${uraianMelebihi}</strong>`,
                    confirmButtonColor: '#0d6efd',
                });
                return false;
            }

            console.log('Step 2 validation passed');
            return true;
        }

        // Fungsi untuk menampilkan pesan error validasi
        function showValidationError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
                confirmButtonColor: '#0d6efd',
            });
        }

        function updateSteps() {
            $('.step-pane').addClass('d-none');
            $(`#step${currentStep}`).removeClass('d-none');

            $('.step-item').removeClass('active completed');
            $('.step-item').each(function() {
                const step = parseInt($(this).data('step'));
                if (step < currentStep) {
                    $(this).addClass('completed');
                } else if (step === currentStep) {
                    $(this).addClass('active');
                }
            });

            if (currentStep === 3) {
                renderKegiatanCardsStep3();
            }

            $('#prevBtn').prop('disabled', currentStep === 1);

            if (currentStep === totalSteps) {
                $('#nextBtn').addClass('d-none');
                $('#saveBtn').removeClass('d-none');
            } else {
                $('#nextBtn').removeClass('d-none');
                $('#saveBtn').addClass('d-none');
            }
        }

        function resetSteps() {
            currentStep = 1;
            $('.step-item').removeClass('active completed');
            $('.step-item:first').addClass('active');
            $('.step-pane').addClass('d-none');
            $('#step1').removeClass('d-none');
            $('#prevBtn').prop('disabled', true);
            $('#nextBtn').removeClass('d-none');
            $('#saveBtn').addClass('d-none');
        }

        // Toggle form pajak berdasarkan checkbox
        $('#checkPajak').change(function() {
            if ($(this).is(':checked')) {
                $('#pajakForm').removeClass('d-none');
                $('#pb1Section').removeClass('d-none');
            } else {
                $('#pajakForm').addClass('d-none');
                $('#pb1Section').addClass('d-none');
                $('#pb1Form').addClass('d-none');
                $('#selectPajak').val('');
                $('#persenPajak').val('');
                $('#totalPajak').val('');
                $('#checkPajakPb1').prop('checked', false);
                $('#selectPb1').val('pb 1');
                $('#persenPb1').val('');
                $('#totalPb1').val('');
                calculateTotalBersih();
            }
        });

        // Toggle form PB1 berdasarkan checkbox
        $('#checkPajakPb1').change(function() {
            if ($(this).is(':checked')) {
                $('#pb1Form').removeClass('d-none');
            } else {
                $('#pb1Form').addClass('d-none');
                $('#selectPb1').val('pb 1');
                $('#persenPb1').val('');
                $('#totalPb1').val('');
                calculateTotalBersih();
            }
        });

        // Event handler untuk perubahan persen pajak
        $('#persenPajak, #persenPb1').on('input', function() {
            calculateTotalBersih();
        });

        // Fungsi untuk menghitung total bersih setelah pajak
        function calculateTotalBersih() {
            const totalTransaksi = window.currentTotalTransaksi || 0;
            let totalPajak = 0;
            let totalPb1 = 0;

            if ($('#checkPajak').is(':checked') && $('#selectPajak').val()) {
                const persenPajak = parseFloat($('#persenPajak').val()) || 0;
                totalPajak = (totalTransaksi * persenPajak) / 100;
                $('#totalPajak').val(formatRupiah(totalPajak));
            }

            if ($('#checkPajakPb1').is(':checked')) {
                const persenPb1 = parseFloat($('#persenPb1').val()) || 0;
                totalPb1 = (totalTransaksi * persenPb1) / 100;
                $('#totalPb1').val(formatRupiah(totalPb1));
            }

            const totalBersih = totalTransaksi - totalPajak - totalPb1;
            $('#totalBersih').text(`Rp ${formatRupiah(totalBersih)}`);
        }

        // Fungsi untuk validasi semua volume
        function validateAllVolumes() {
            let volumeMelebihiMaksimal = false;
            let uraianDetails = [];

            $('.jumlah-input').each(function() {
                const value = parseInt($(this).val()) || 0;
                const maxVolume = parseFloat($(this).attr('max')) || 0;
                const uraianText = $(this).data('uraian-text') || $(this).closest('.card-body').find('.form-check-label').text();
                const isChecked = $(this).closest('.uraian-item').find('.uraian-checkbox').is(':checked');

                if (isChecked && value > maxVolume) {
                    volumeMelebihiMaksimal = true;
                    uraianDetails.push({
                        uraian: uraianText,
                        volume: value,
                        max_volume: maxVolume
                    });
                }
            });

            if (volumeMelebihiMaksimal) {
                let errorMessage = 'Maaf, jumlah volume melebihi jumlah maksimal untuk uraian berikut:<br><br>';

                uraianDetails.forEach((detail, index) => {
                    errorMessage += `<strong>${index + 1}. ${detail.uraian}</strong><br>`;
                    errorMessage += `Volume: ${detail.volume} (Maks: ${detail.max_volume})<br><br>`;
                });

                errorMessage += 'Silakan perbaiki volume sebelum melanjutkan.';

                Swal.fire({
                    icon: 'error',
                    title: 'Volume Melebihi Maksimal',
                    html: errorMessage,
                    confirmButtonColor: '#0d6efd',
                });

                return false;
            }

            return true;
        }

        // PERBAIKAN: Fungsi untuk merender opsi uraian dengan informasi yang lebih detail
        function renderUraianOptions(data, kegiatanIndex, container) {
            container.empty();

            if (data.length === 0) {
                container.html('<div class="alert alert-info">Tidak ada data uraian untuk rekening ini</div>');
                return;
            }

            // Hitung uraian yang dapat digunakan
            const uraianDapatDigunakan = data.filter(uraian => uraian.dapat_digunakan).length;

            let uraianHtml = `
            <div class="col-sm-12 justify-content-between align-content-between d-flex mb-3">
                <div class="label">
                    <p class="mb-0 fw-bold">Uraian</p>
                    <small class="text-muted">
                        Total RKAS: ${data.reduce((sum, u) => sum + u.total_volume, 0)} | 
                        Sudah: ${data.reduce((sum, u) => sum + u.volume_sudah_dibelanjakan, 0)} | 
                        Sisa: ${data.reduce((sum, u) => sum + u.sisa_volume, 0)}
                    </small>
                    ${uraianDapatDigunakan === 0 ? '<div class="text-danger mt-1">Semua uraian sudah habis dibelanjakan</div>' : ''}
                </div>
                ${uraianDapatDigunakan > 0 ? `
                <div class="form-check">
                    <input class="form-check-input check-all-uraian" type="checkbox" data-kegiatan-index="${kegiatanIndex}">
                    <label class="form-check-label">
                        <strong>Pilih</strong> semua uraian
                    </label>
                </div>
                ` : ''}
            </div>
            <hr class="mt-2 mb-3">
            `;

            data.forEach((uraian, index) => {
                const isDisabled = !uraian.dapat_digunakan;
                const disabledClass = isDisabled ? 'text-muted' : '';
                const disabledAttr = isDisabled ? 'disabled' : '';

                uraianHtml += `
                <div class="card mb-3 uraian-item ${isDisabled ? 'bg-light' : ''}" data-uraian-id="${uraian.id}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input uraian-checkbox" type="checkbox" name="uraian" value="${uraian.id}"
                                        ${isDisabled ? 'disabled' : ''}>
                                    <label class="form-check-label fw-bold ${disabledClass}">
                                        ${uraian.uraian}
                                    </label>
                                    <input type="hidden" class="uraian-text-input" value="${uraian.uraian}">
                                    <span class="satuan-text d-none">${uraian.satuan || ''}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-sm alert-${isDisabled ? 'warning' : 'info'} mb-2">
                                    <small>
                                        <strong>Informasi Volume:</strong><br>
                                        • Total RKAS: <strong>${uraian.total_volume}</strong><br>
                                        • Sudah dibelanjakan: <strong>${uraian.volume_sudah_dibelanjakan}</strong><br>
                                        • Sisa tersedia: <strong class="${uraian.sisa_volume > 0 ? 'text-success' : 'text-danger'}">${uraian.sisa_volume}</strong><br>
                                        • Bulan: ${uraian.bulan_asal.join(', ')}
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="alert alert-sm alert-secondary mb-2">
                                    <small>
                                        <strong>Informasi Anggaran:</strong><br>
                                        • Harga satuan: <strong>Rp ${formatRupiah(uraian.harga_satuan)}</strong><br>
                                        • Total anggaran: <strong>Rp ${formatRupiah(uraian.total_anggaran)}</strong><br>
                                        • Sudah dibelanjakan: <strong>Rp ${formatRupiah(uraian.sudah_dibelanjakan)}</strong><br>
                                        • Sisa anggaran: <strong>Rp ${formatRupiah(uraian.sisa_anggaran)}</strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        ${!isDisabled ? `
                        <div class="row mt-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Jumlah yang akan dibelanjakan</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control jumlah-input" value="${uraian.sisa_volume}" min="1" 
                                        max="${uraian.sisa_volume}" aria-label="Jumlah" 
                                        oninput="validateVolumeInput(this, ${uraian.sisa_volume}, '${uraian.uraian.replace(/'/g, "\\'")}')">
                                    <span class="input-group-text">Maks. ${uraian.sisa_volume}</span>
                                </div>
                                <small class="text-muted">Sisa volume: ${uraian.sisa_volume} ${uraian.satuan}</small>
                                <div class="text-danger volume-error" style="display: none;">
                                    <small><i class="bi bi-exclamation-circle"></i> Jumlah melebihi sisa volume</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Satuan</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control harga-input" value="${formatRupiah(uraian.harga_satuan)}"
                                        aria-label="Harga" readonly>
                                </div>
                                <small class="text-muted">Harga tetap sesuai RKAS</small>
                            </div>
                        </div>
                        ` : `
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Uraian tidak dapat digunakan - Volume sudah habis</strong>
                        </div>
                        `}
                    </div>
                </div>
                `;
            });

            container.html(uraianHtml);

            // Hanya tambahkan event listener jika ada uraian yang dapat digunakan
            if (uraianDapatDigunakan > 0) {
                $(`.check-all-uraian[data-kegiatan-index="${kegiatanIndex}"]`).change(function() {
                    const isChecked = $(this).is(':checked');
                    container.find('.uraian-checkbox:not(:disabled)').prop('checked', isChecked);
                    container.find('.uraian-item:not(.bg-light)').each(function() {
                        updateUraianSubtotal($(this));
                    });
                    updateTotalTransaksiDisplay();
                });

                container.find('.uraian-checkbox:not(:disabled)').change(function() {
                    updateUraianSubtotal($(this).closest('.uraian-item'));
                    updateTotalTransaksiDisplay();
                });

                container.find('.jumlah-input').on('input', function() {
                    const maxVolume = parseFloat($(this).attr('max')) || 0;
                    const uraianText = $(this).closest('.card-body').find('.form-check-label').text();
                    validateVolumeInput(this, maxVolume, uraianText);
                    updateUraianSubtotal($(this).closest('.uraian-item'));
                    updateTotalTransaksiDisplay();
                });
            }
        }

        // Fungsi untuk validasi input volume
        function validateVolumeInput(inputElement, maxVolume, uraianText) {
            const value = parseInt(inputElement.value) || 0;
            const errorElement = $(inputElement).closest('.mb-2').find('.volume-error');

            if (value > maxVolume) {
                errorElement.show();
                inputElement.setCustomValidity('Jumlah volume melebihi maksimal');
                $(inputElement).data('exceeds-limit', true);
                $(inputElement).data('uraian-text', uraianText);
            } else {
                errorElement.hide();
                inputElement.setCustomValidity('');
                $(inputElement).data('exceeds-limit', false);
                $(inputElement).removeData('uraian-text');
            }
        }

        // Fungsi untuk memperbarui subtotal uraian
        function updateUraianSubtotal(uraianItem) {
            const checkbox = uraianItem.find('.uraian-checkbox');
            const jumlahInput = uraianItem.find('.jumlah-input');
            const hargaInput = uraianItem.find('.harga-input');
            const errorElement = uraianItem.find('.volume-error');

            if (checkbox.is(':checked')) {
                const jumlah = parseFloat(jumlahInput.val()) || 0;
                const maxVolume = parseFloat(jumlahInput.attr('max')) || 0;
                const hargaText = hargaInput.val().replace(/[^\d]/g, '');
                const harga = parseFloat(hargaText) || 0;

                if (jumlah > maxVolume) {
                    errorElement.show();
                    jumlahInput[0].setCustomValidity('Jumlah volume melebihi maksimal');
                } else {
                    errorElement.hide();
                    jumlahInput[0].setCustomValidity('');
                }
            } else {
                errorElement.hide();
                jumlahInput[0].setCustomValidity('');
            }
        }

        // Event handler untuk menghapus kegiatan
        $(document).on('click', '.remove-kegiatan', function() {
            const card = $(this).closest('.kegiatan-card');
            const cardCount = $('.kegiatan-card').length;

            if (cardCount > 1) {
                card.remove();

                $('.kegiatan-card').each(function(index) {
                    $(this).find('.card-title').text(`Kegiatan ${index + 1}`);
                    $(this).attr('data-kegiatan-index', index + 1);
                });

                if ($('.kegiatan-card').length === 1) {
                    $('.remove-kegiatan').addClass('d-none');
                }
            }
        });

        // Fungsi untuk merender card kegiatan di step 3
        function renderKegiatanCardsStep3() {
            try {
                console.log('Rendering kegiatan cards for step 3');
                const kegiatanCardsStep3 = $('#kegiatanCardsStep3');
                kegiatanCardsStep3.empty();

                const kegiatanCards = $('.kegiatan-card');
                console.log('Found', kegiatanCards.length, 'kegiatan cards');

                if (kegiatanCards.length === 0) {
                    kegiatanCardsStep3.html('<div class="col-12"><p class="text-muted">Tidak ada data kegiatan</p></div>');
                    return;
                }

                $('.kegiatan-card').each(function(index) {
                    const kegiatanIndex = $(this).data('kegiatan-index');
                    const kegiatanSelect = $(this).find('.kegiatan-select');
                    const rekeningSelect = $(this).find('.rekening-select');
                    const kegiatanText = kegiatanSelect.find('option:selected').text() || 'Belum dipilih';
                    const rekeningText = rekeningSelect.find('option:selected').text() || 'Belum dipilih';

                    let uraianList = '';
                    $(this).find('.uraian-item').each(function() {
                        const checkbox = $(this).find('.uraian-checkbox');
                        if (checkbox.is(':checked')) {
                            const uraianLabel = $(this).find('.form-check-label').text();
                            const jumlah = $(this).find('.jumlah-input').val() || 0;
                            const hargaText = $(this).find('.harga-input').val().replace(/[^\d]/g, '');
                            const harga = parseFloat(hargaText) || 0;
                            const subtotal = jumlah * harga;

                            uraianList += `
                            <div class="mb-2">
                                <div class="fw-bold">${uraianLabel}</div>
                                <div class="small">Jumlah: ${jumlah} | Harga: Rp ${formatRupiah(harga)} | Subtotal: Rp ${formatRupiah(subtotal)}</div>
                            </div>
                        `;
                        }
                    });

                    if (!uraianList) {
                        uraianList = '<div class="text-muted">Tidak ada uraian yang dipilih</div>';
                    }

                    kegiatanCardsStep3.append(`
                    <div class="col-md-12 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-info-subtle">
                                <strong>Kegiatan ${kegiatanIndex}</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Kegiatan:</strong> ${kegiatanText}
                                </div>
                                <div class="mb-2">
                                    <strong>Rekening:</strong> ${rekeningText}
                                </div>
                                <div class="mt-3">
                                    <strong>Uraian yang dipilih:</strong>
                                    ${uraianList}
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                });

                console.log('Kegiatan cards rendered successfully');

            } catch (error) {
                console.error('Error rendering kegiatan cards:', error);
                $('#kegiatanCardsStep3').html('<div class="col-12"><p class="text-danger">Error rendering data</p></div>');
            }
        }

        // Fungsi untuk menghitung dan menampilkan total transaksi
        function updateTotalTransaksiDisplay() {
            let totalTransaksi = 0;

            $('.uraian-item').each(function() {
                const checkbox = $(this).find('.uraian-checkbox');
                if (checkbox.is(':checked')) {
                    const jumlah = parseFloat($(this).find('.jumlah-input').val()) || 0;
                    const hargaText = $(this).find('.harga-input').val().replace(/[^\d]/g, '');
                    const harga = parseFloat(hargaText) || 0;
                    totalTransaksi += jumlah * harga;
                }
            });

            window.currentTotalTransaksi = totalTransaksi;
            calculateTotalBersih();
        }

        // Event handler untuk tombol simpan
        $('#saveBtn').click(function() {
            if ($('#checkPajak').is(':checked')) {
                const selectPajak = $('#selectPajak').val();
                const persenPajak = $('#persenPajak').val();

                if (!selectPajak) {
                    showValidationError('Jenis pajak harus dipilih');
                    return;
                }

                if (!persenPajak || parseFloat(persenPajak) <= 0) {
                    showValidationError('Persen pajak harus diisi dengan nilai yang valid');
                    return;
                }
            }

            if ($('#checkPajakPb1').is(':checked')) {
                const persenPb1 = $('#persenPb1').val();

                if (!persenPb1 || parseFloat(persenPb1) <= 0) {
                    showValidationError('Persen pajak daerah harus diisi dengan nilai yang valid');
                    return;
                }
            }

            const formData = collectFormData();
            submitFormData(formData);
        });

        // Fungsi untuk mengumpulkan data form
        function collectFormData() {
            const formData = {
                penganggaran_id: '{{ $penganggaran->id }}',
                tanggal_transaksi: $('#tanggal_transaksi').val(),
                jenis_transaksi: $('#jenis_transaksi').val(),
                nama_penyedia: $('#checkForm').is(':checked') ? $('#nama_penerima').val() : $('#nama_toko').val(),
                nama_penerima: $('#checkForm').is(':checked') ? $('#nama_penerima').val() : $('#nama_toko').val(),
                alamat: $('#alamat_toko').val(),
                nomor_telepon: $('#nomor_telepon').val(),
                npwp: $('#checkNpwp').is(':checked') ? null : $('#npwp').val(),
                nomor_nota: $('#nomor_nota').val(),
                tanggal_nota: $('#tanggal_nota').val(),
                uraian_opsional: $('#uraian_opsional').val(),
                bulan: bulan,
                kode_kegiatan_id: [],
                kode_rekening_id: [],
                uraian_items: [],
                pajak_items: [],
                total_transaksi_kotor: window.currentTotalTransaksi
            };

            $('.kegiatan-card').each(function() {
                const kegiatanId = $(this).find('.kegiatan-select').val();
                const rekeningId = $(this).find('.rekening-select').val();

                if (kegiatanId && rekeningId) {
                    formData.kode_kegiatan_id.push(kegiatanId);
                    formData.kode_rekening_id.push(rekeningId);

                    $(this).find('.uraian-item').each(function() {
                        const checkbox = $(this).find('.uraian-checkbox');
                        if (checkbox.is(':checked')) {
                            const jumlah = parseFloat($(this).find('.jumlah-input').val()) || 0;
                            const hargaText = $(this).find('.harga-input').val().replace(/[^\d]/g, '');
                            const harga = parseFloat(hargaText) || 0;
                            const uraianText = $(this).find('.uraian-text-input').val();

                            formData.uraian_items.push({
                                id: checkbox.val(),
                                uraian_text: uraianText,
                                satuan: $(this).find('.satuan-text').text().trim(),
                                kegiatan_id: kegiatanId,
                                rekening_id: rekeningId,
                                volume: jumlah,
                                harga_satuan: harga,
                                jumlah_belanja: jumlah * harga
                            });
                        }
                    });
                }
            });

            if ($('#checkPajak').is(':checked') && $('#selectPajak').val()) {
                formData.pajak_items.push({
                    jenis_pajak: $('#selectPajak').val(),
                    persen_pajak: parseFloat($('#persenPajak').val()) || 0,
                    total_pajak: parseFloat($('#totalPajak').val().replace(/[^\d]/g, '')) || 0
                });
            }

            if ($('#checkPajakPb1').is(':checked')) {
                formData.pajak_items.push({
                    jenis_pajak: $('#selectPb1').val(),
                    persen_pajak: parseFloat($('#persenPb1').val()) || 0,
                    total_pajak: parseFloat($('#totalPb1').val().replace(/[^\d]/g, '')) || 0
                });
            }

            console.log('Form data collected:', formData);
            return formData;
        }

        // Fungsi untuk mengirim data ke server
        function submitFormData(formData) {
            Swal.fire({
                title: 'Menyimpan Data...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/bku/store',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            confirmButtonColor: '#0d6efd',
                        }).then(() => {
                            $('#transactionModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message,
                            confirmButtonColor: '#0d6efd',
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    console.error('Error submitting form:', xhr);

                    let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#0d6efd',
                    });
                }
            });
        }

        // Toggle form berdasarkan checkbox
        $('#checkForm').change(function() {
            if ($(this).is(':checked')) {
                $('#formToko').addClass('d-none');
                $('#formPenerima').removeClass('d-none');
            } else {
                $('#formToko').removeClass('d-none');
                $('#formPenerima').addClass('d-none');
            }
        });

        // Toggle NPWP field
        $('#checkNpwp').change(function() {
            if ($(this).is(':checked')) {
                $('#npwp').prop('disabled', true).val('');
            } else {
                $('#npwp').prop('disabled', false);
            }
        });

        // Fungsi untuk memuat nomor nota terakhir
        function loadLastNotaNumber() {
            const tahun = '{{ $tahun }}';
            const bulan = '{{ $bulan }}';

            $.ajax({
                url: '/bku/last-nota-number/' + tahun + '/' + bulan,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const lastNota = response.last_nota_number;
                        const suggestedNext = response.suggested_next_number;

                        let infoText = '';
                        if (lastNota) {
                            infoText = `Nomor nota terakhir: <strong>${lastNota}</strong>`;
                        } else {
                            infoText = 'Belum ada nomor nota untuk bulan ini';
                        }

                        $('#lastNotaInfo').html(`<i class="bi bi-info-circle"></i> ${infoText}`);

                    } else {
                        $('#lastNotaInfo').html('<i class="bi bi-exclamation-triangle"></i> Gagal memuat informasi');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading last nota number:', xhr);
                    $('#lastNotaInfo').html('<i class="bi bi-exclamation-triangle"></i> Error memuat informasi');
                }
            });
        }

        // Event handler untuk input nomor nota
        $('#nomor_nota').on('blur', function() {
            const notaValue = $(this).val().trim();

            if (notaValue && !validateNotaFormat(notaValue)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Format Nomor Nota',
                    text: 'Nomor nota harus mengandung angka. Contoh: 001, BP001, NOTA-001, dll.',
                    confirmButtonColor: '#0d6efd',
                });

                $(this).focus();
                return;
            }

            if (/^\d+$/.test(notaValue)) {
                const number = parseInt(notaValue) || 1;
                $(this).val(String(number).padStart(3, '0'));
            }
        });

        // Event handler untuk memberikan contoh format
        $('#nomor_nota').on('focus', function() {
            const currentValue = $(this).val();
            if (!currentValue) {
                $(this).attr('placeholder', 'Contoh: 001, BP001, NOTA-001, INV-2024-001');
            }
        });

        // Fungsi untuk validasi format nomor nota
        function validateNotaFormat(notaNumber) {
            if (!notaNumber.trim()) {
                return false;
            }

            const hasNumber = /\d/.test(notaNumber);
            return hasNumber;
        }

        console.log('=== TRANSACTION MODAL SCRIPT READY ===');
    });
</script>
@endpush