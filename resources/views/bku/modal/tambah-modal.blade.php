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
</style>
@php
$tahun = $tahun ?? date('Y');
$bulan = $bulan ?? 'Januari';
$bulanList = [
'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
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
                                    <input type="date" class="form-control" name="tanggal_transaksi" id="tanggal_transaksi"
                                        aria-label="Sizing example input" aria-describedby="dateHelp1"
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
                                        <!-- Tambahkan field hidden untuk nama_penerima ketika form toko aktif -->
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
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_nota" class="form-label">Tanggal Belanja/Nota</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" class="form-control" id="tanggal_nota" aria-label="Sizing example input"
                                        aria-describedby="dateHelp2"
                                        min="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-01"
                                        max="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($lastDay, 2, '0', STR_PAD_LEFT) }}"
                                        required>
                                </div>
                            </div>
                            <!-- Container untuk kegiatan -->
                            <div class="col-md-12 mb-3" id="kegiatanContainer">
                                <!-- Kegiatan akan dimuat secara dinamis -->
                            </div>
                            <div class="justify-content-end align-content-end">
                                <button class="btn btn-sm btn-primary mt-2" id="tambahKegiatan">
                                    <i class="bi bi-plus-circle me-1"></i>Tambah Kegiatan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Perhitungan Pajak -->
                    <div class="step-pane d-none" id="step3">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="card bg-info d-flex justify-content-center align-content-center"
                                    style="height: 50px;">
                                    <div class="form-check mt-2 ms-2 mb-2 align-content-center">
                                        <input class="form-check-input" type="checkbox" value="" id="checkPajak">
                                        <label class="form-check-label" for="checkPajak">
                                            <strong>Centang</strong> jika belanja ini mempunyai pajak
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-info-subtle" style="height: 50px;">
                                        KEGIATAN 1
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <strong>Kegiatan</strong>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <span>Pembayaran Listrik</span>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <strong>Rekening Belanja</strong>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <span>Belanja Listrik</span>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <strong>Uraian</strong>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <span>Lunas Bayar Belanja Listrik</span>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <strong>Total Transaksi (Kotor)</strong>
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <span id="totalTransaksiKotor">Rp. 0</span>
                                                <!-- Input hidden untuk menyimpan total transaksi kotor -->
                                                <input type="hidden" id="totalTransaksiKotorValue" value="0">
                                            </div>

                                            <!-- Form Pajak (Awalnya Disembunyikan) -->
                                            <div id="pajakForm" class="d-none">
                                                <div class="pajak-card">
                                                    <h6 class="mb-3">Form Pajak</h6>
                                                    <div class="pajak-form-row">
                                                        <div class="pajak-form-col">
                                                            <label class="form-label">Pajak PPh/PPn</label>
                                                            <select name="" id="selectPajak"
                                                                class="form-select form-select-sm" name="pajak"
                                                                aria-label="Small select example">
                                                                <option value="">Pilih Pajak PPh/PPn</option>
                                                                <option value="pph 21">PPh 21</option>
                                                                <option value="pph 22">PPh 22</option>
                                                                <option value="pph 23">PPh 23</option>
                                                                <option value="pph 24">PPh 25</option>
                                                                <option value="ppn">PPn</option>
                                                            </select>
                                                        </div>
                                                        <div class="pajak-form-col">
                                                            <label class="form-label">Persen</label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text"
                                                                    id="inputGroup-sizing-sm">%</span>
                                                                <input type="text" class="form-control"
                                                                    name="persen_pajak" id="persenPajak"
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
                                                                    name="total_pajak" id="totalPajak"
                                                                    aria-label="Sizing example input"
                                                                    aria-describedby="inputGroup-sizing-sm" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section PB1 (Awalnya Disembunyikan) -->
                                            <div id="pb1Section" class="pb1-section d-none">
                                                <div class="col-md-12">
                                                    <div class="form-check mb-3 align-content-center">
                                                        <input class="form-check-input" type="checkbox" value=""
                                                            id="checkPajakPb1">
                                                        <label class="form-check-label" for="checkPajakPb1">
                                                            <strong>Centang</strong> jika belanja ini mempunyai Pajak PB
                                                            1
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Form PB1 (Awalnya Disembunyikan) -->
                                                <div id="pb1Form" class="d-none">
                                                    <div class="pajak-card">
                                                        <h6 class="mb-3">Form Pajak PB 1</h6>
                                                        <div class="pajak-form-row">
                                                            <div class="pajak-form-col">
                                                                <label class="form-label">Pajak PB 1</label>
                                                                <select name="pajak_daerah" id="selectPb1"
                                                                    class="form-select form-select-sm"
                                                                    aria-label="Small select example">
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
                                                                        aria-describedby="inputGroup-sizing-sm"
                                                                        disabled>
                                                                </div>
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
        let currentStep = 1;
        const totalSteps = 3;
        const tahun = '{{ $tahun }}';
        const bulan = '{{ $bulan }}';
        
        // Variabel global untuk menyimpan total transaksi
        window.currentTotalTransaksi = 0;

        // Fungsi untuk mendapatkan range tanggal berdasarkan bulan dan tahun
        function getDateRangeForMonth(bulan, tahun) {
        const bulanAngka = {
        'Januari': 1, 'Februari': 2, 'Maret': 3, 'April': 4,
        'Mei': 5, 'Juni': 6, 'Juli': 7, 'Agustus': 8,
        'September': 9, 'Oktober': 10, 'November': 11, 'Desember': 12
        }[bulan] || 1;
        
        // Hitung hari terakhir dalam bulan
        const lastDay = new Date(tahun, bulanAngka, 0).getDate();
        
        return {
        min: `${tahun}-${String(bulanAngka).padStart(2, '0')}-01`,
        max: `${tahun}-${String(bulanAngka).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
        };
        }

        // Fungsi untuk memvalidasi tanggal
        function validateDate(inputElement, bulan, tahun) {
            const dateRange = getDateRangeForMonth(bulan, tahun);
            const selectedDate = new Date(inputElement.value);
            const minDate = new Date(dateRange.min);
            const maxDate = new Date(dateRange.max);

            if (inputElement.value && (selectedDate < minDate || selectedDate > maxDate)) {
                // Reset ke tanggal yang valid
                inputElement.value = dateRange.min;
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: `Tanggal harus dalam bulan ${bulan} ${tahun}`,
                    confirmButtonColor: '#0d6efd',
                });
                return false;
            }
            return true;
        }

        // Fungsi untuk menonaktifkan tanggal di luar range
        function disableOutOfRangeDates() {
        const dateRange = getDateRangeForMonth(bulan, tahun);
        
        // Set min dan max attributes
        $('#tanggal_transaksi, #tanggal_nota').attr({
        'min': dateRange.min,
        'max': dateRange.max
        });
        }

        // Initialize modal
        $('#transactionModal').on('show.bs.modal', function() {
            resetSteps();
            loadKegiatanDanRekening();
            window.currentTotalTransaksi = 0;

            // Set tanggal range dan default values
            disableOutOfRangeDates();
            const dateRange = getDateRangeForMonth(bulan, tahun);
            $('#tanggal_transaksi, #tanggal_nota').val(dateRange.min);
        });

        // Validasi tanggal saat input berubah
        $('#tanggal_transaksi, #tanggal_nota').on('change', function() {
        validateDate(this, bulan, tahun);
        });

        // Next button click
        $('#nextBtn').click(function() {
            if (currentStep < totalSteps) {
                // Validasi sebelum pindah ke step berikutnya
                if (currentStep === 1) {
                    if (!validateStep1()) {
                        return;
                    }
                } else if (currentStep === 2) {
                    if (!validateStep2()) {
                        return;
                    }
                }
                
                currentStep++;
                updateSteps();
                
                // Jika pindah ke step 3, hitung total transaksi
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
        
        // Validasi field yang wajib diisi
        if (!tanggalTransaksi) {
        showValidationError('Tanggal transaksi harus diisi');
        $('#tanggal_transaksi').focus();
        return false;
        }

        // Validasi tanggal sesuai range
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
        
        // Validasi field yang wajib diisi
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

        // Validasi tanggal sesuai range
        if (!validateDate(document.getElementById('tanggal_nota'), bulan, tahun)) {
        return false;
        }
        
        // Validasi tambahan: pastikan minimal satu uraian dipilih
        const uraianDipilih = $('.uraian-checkbox:checked').length > 0;
        if (!uraianDipilih) {
        showValidationError('Minimal satu uraian harus dipilih');
        return false;
        }
        
        return true;
        }
        
        // Fungsi untuk menampilkan pesan error validasi dengan SweetAlert
        function showValidationError(message) {
        Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonColor: '#0d6efd',
        });
        }

        function updateSteps() {
            // Hide all panes
            $('.step-pane').addClass('d-none');
            
            // Show current pane
            $(`#step${currentStep}`).removeClass('d-none');
            
            // Update progress indicators
            $('.step-item').removeClass('active completed');
            
            $('.step-item').each(function() {
                const step = parseInt($(this).data('step'));
                if (step < currentStep) {
                    $(this).addClass('completed');
                } else if (step === currentStep) {
                    $(this).addClass('active');
                }
            });
            
            // Update buttons
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
                // Reset nilai form pajak
                $('#selectPajak').val('');
                $('#persenPajak').val('');
                $('#totalPajak').val('');
                // Reset nilai form PB1
                $('#checkPajakPb1').prop('checked', false);
                $('#selectPb1').val('pb 1');
                $('#persenPb1').val('');
                $('#totalPb1').val('');
                // Hitung ulang total bersih
                calculateTotalBersih();
            }
        });

        // Toggle form PB1 berdasarkan checkbox
        $('#checkPajakPb1').change(function() {
            if ($(this).is(':checked')) {
                $('#pb1Form').removeClass('d-none');
            } else {
                $('#pb1Form').addClass('d-none');
                // Reset nilai form PB1
                $('#selectPb1').val('pb 1');
                $('#persenPb1').val('');
                $('#totalPb1').val('');
                // Hitung ulang total bersih
                calculateTotalBersih();
            }
        });

        // Toggle form Toko dan Penerima berdasarkan checkbox
        $('#checkForm').change(function() {
            if ($(this).is(':checked')) {
                $('#formToko').addClass('d-none');
                $('#formPenerima').removeClass('d-none');
                // Isi nilai hidden field dengan nilai dari form toko
                $('#nama_penerima_hidden').val($('#nama_toko').val());
            } else {
                $('#formToko').removeClass('d-none');
                $('#formPenerima').addClass('d-none');
                // Isi nilai hidden field dengan nilai dari form penerima
                $('#nama_penerima_hidden').val($('#nama_penerima').val());
            }
        });

        // Update hidden field ketika nilai berubah
        $('#nama_toko').on('input', function() {
            if (!$('#checkForm').is(':checked')) {
                $('#nama_penerima_hidden').val($(this).val());
            }
        });

        $('#nama_penerima').on('input', function() {
            if ($('#checkForm').is(':checked')) {
                $('#nama_penerima_hidden').val($(this).val());
            }
        });

        // Hitung total pajak secara real-time
        $('#persenPajak').on('input', function() {
            calculateTax('pajak');
        });

        $('#persenPb1').on('input', function() {
            calculateTax('pb1');
        });

        // Fungsi untuk menghitung total transaksi dari uraian yang dipilih
        function calculateTotalTransaksi() {
            let totalTransaksi = 0;
            
            // Hitung total dari semua uraian yang dipilih
            $('.uraian-item').each(function() {
                const checkbox = $(this).find('.uraian-checkbox');
                if (checkbox.is(':checked')) {
                    const jumlah = parseFloat($(this).find('.jumlah-input').val()) || 0;
                    const hargaText = $(this).find('.harga-input').val();
                    
                    // Bersihkan format angka (hapus titik dan koma)
                    const harga = parseFloat(hargaText.replace(/\./g, '').replace(',', '.')) || 0;
                    
                    totalTransaksi += jumlah * harga;
                }
            });
            
            return totalTransaksi;
        }

        // Fungsi untuk update tampilan total transaksi
        function updateTotalTransaksiDisplay() {
            const totalTransaksi = calculateTotalTransaksi();
            
            // Update tampilan
            $('#totalTransaksiKotor').text('Rp ' + totalTransaksi.toLocaleString('id-ID'));
            $('#totalTransaksiKotorValue').val(totalTransaksi);
            
            // Update total bersih (awalnya sama dengan total kotor)
            $('#totalBersih').text('Rp ' + totalTransaksi.toLocaleString('id-ID'));
            
            // Simpan nilai total transaksi untuk perhitungan pajak
            window.currentTotalTransaksi = totalTransaksi;
            
            // Hitung ulang pajak jika sudah ada nilai pajak
            if ($('#checkPajak').is(':checked')) {
                calculateTax('pajak');
                if ($('#checkPajakPb1').is(':checked')) {
                    calculateTax('pb1');
                }
            }
        }

        function calculateTax(type) {
            const totalTransaksi = window.currentTotalTransaksi || 0;
            let persen, totalElement;
            
            if (type === 'pajak') {
                persen = parseFloat($('#persenPajak').val()) || 0;
                totalElement = $('#totalPajak');
            } else {
                persen = parseFloat($('#persenPb1').val()) || 0;
                totalElement = $('#totalPb1');
            }
            
            const totalPajak = (totalTransaksi * persen) / 100;
            totalElement.val(totalPajak.toLocaleString('id-ID'));
            
            // Hitung ulang total bersih
            calculateTotalBersih();
        }

        function calculateTotalBersih() {
            const totalTransaksi = window.currentTotalTransaksi || 0;
            const totalPajak = parseFloat($('#totalPajak').val().replace(/\./g, '')) || 0;
            const totalPb1 = parseFloat($('#totalPb1').val().replace(/\./g, '')) || 0;
            
            const totalBersih = totalTransaksi - totalPajak - totalPb1;
            
            // Update tampilan total bersih
            $('#totalBersih').text('Rp ' + totalBersih.toLocaleString('id-ID'));
        }

        // Event listener untuk perubahan checkbox uraian
        $(document).on('change', '.uraian-checkbox, .check-all-uraian', function() {
            updateTotalTransaksiDisplay();
        });

        // Event listener untuk perubahan jumlah
        $(document).on('input', '.jumlah-input', function() {
            updateTotalTransaksiDisplay();
        });

        // Toggle disable form NPWP berdasarkan checkbox
        $('#checkNpwp').change(function() {
            if ($(this).is(':checked')) {
                $('#npwp').prop('disabled', true);
                $('#npwp').val('');
                $('#npwp').removeClass('is-invalid');
                $('#npwpFeedback').hide();
            } else {
                $('#npwp').prop('disabled', false);
            }
        });

        // Validasi format NPWP
        function validateNPWP(npwp) {
            // Regex untuk format NPWP Indonesia: XX.XXX.XXX.X-XXX.XXX
            const npwpRegex = /^\d{2}\.\d{3}\.\d{3}\.\d{1}\-\d{3}\.\d{3}$/;
            return npwpRegex.test(npwp);
        }

        // Event listener untuk validasi NPWP
        $('#npwp').on('blur', function() {
            const npwpValue = $(this).val().trim();
            
            if (npwpValue !== '' && !validateNPWP(npwpValue)) {
                $(this).addClass('is-invalid');
                $('#npwpFeedback').show();
            } else {
                $(this).removeClass('is-invalid');
                $('#npwpFeedback').hide();
            }
        });

        // Format otomatis NPWP
        $('#npwp').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            
            if (value.length > 0) {
                value = value.match(/.{1,16}/)[0];
                
                // Format: XX.XXX.XXX.X-XXX.XXX
                let formattedValue = '';
                for (let i = 0; i < value.length; i++) {
                    if (i === 2 || i === 5 || i === 8 || i === 12) {
                        formattedValue += '.';
                    }
                    if (i === 9) {
                        formattedValue += '-';
                    }
                    formattedValue += value[i];
                }
                
                $(this).val(formattedValue);
            }
        });

        // Reset form ketika modal ditutup
        $('#transactionModal').on('hidden.bs.modal', function() {
            // Reset checkbox dan form pajak
            $('#checkPajak').prop('checked', false);
            $('#pajakForm').addClass('d-none');
            $('#selectPajak').val('');
            $('#persenPajak').val('');
            $('#totalPajak').val('');
            
            // Reset checkbox dan form PB1
            $('#pb1Section').addClass('d-none');
            $('#checkPajakPb1').prop('checked', false);
            $('#pb1Form').addClass('d-none');
            $('#selectPb1').val('pb 1');
            $('#persenPb1').val('');
            $('#totalPb1').val('');
            
            // Reset NPWP
            $('#checkNpwp').prop('checked', false);
            $('#npwp').prop('disabled', false);
            $('#npwp').val('');
            $('#npwp').removeClass('is-invalid');
            $('#npwpFeedback').hide();
            
            // Reset form toko/penerima
            $('#checkForm').prop('checked', false);
            $('#formToko').removeClass('d-none');
            $('#formPenerima').addClass('d-none');
            
            // Reset total transaksi
            $('#totalTransaksiKotor').text('Rp. 0');
            $('#totalBersih').text('Rp. 0');
            window.currentTotalTransaksi = 0;
        });

        // Fungsi untuk memuat kegiatan dan rekening belanja
        function loadKegiatanDanRekening() {
            const tahun = '{{ $tahun }}';
            const bulan = '{{ $bulan }}';
            
            $.ajax({
                url: '{{ route("bku.kegiatan-rekening", ["tahun" => ":tahun", "bulan" => ":bulan"]) }}'
                    .replace(':tahun', tahun)
                    .replace(':bulan', bulan),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderKegiatanOptions(response.data);
                    } else {
                        alert('Gagal memuat data kegiatan: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat memuat data kegiatan');
                    console.error(xhr);
                }
            });
        }

        // Fungsi untuk merender opsi kegiatan
        function renderKegiatanOptions(data) {
            const container = $('#kegiatanContainer');
            container.empty();
            
            if (data.length === 0) {
                container.html('<div class="alert alert-info">Tidak ada data kegiatan untuk bulan ini</div>');
                return;
            }
            
            // Buat card untuk setiap kegiatan
            data.forEach((kegiatanData, index) => {
                const cardHtml = `
                    <div class="card mb-3 kegiatan-card" data-kegiatan-id="${kegiatanData.kegiatan.id}">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Kegiatan ${index + 1}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Kegiatan</label>
                                    <select class="form-select form-select-sm kegiatan-select" name="kode_kegiatan_id" 
                                            data-kegiatan-id="${kegiatanData.kegiatan.id}">
                                        <option value="">Pilih Jenis Kegiatan</option>
                                        <option value="${kegiatanData.kegiatan.id}" selected>
                                            ${kegiatanData.kegiatan.kode} - ${kegiatanData.kegiatan.uraian}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Rekening Belanja</label>
                                    <select class="form-select form-select-sm rekening-select" name="kode_rekening_id" 
                                            data-kegiatan-id="${kegiatanData.kegiatan.id}">
                                        <option value="">Pilih Rekening Belanja</option>
                                        ${kegiatanData.rekening_belanja.map(rekening => `
                                            <option value="${rekening.id}" data-anggaran="${rekening.anggaran}">
                                                ${rekening.rekening.kode_rekening} - ${rekening.rekening.rincian_objek}
                                            </option>
                                        `).join('')}
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Container untuk uraian -->
                            <div class="uraian-container" id="uraianContainer-${kegiatanData.kegiatan.id}">
                                <!-- Uraian akan dimuat ketika rekening dipilih -->
                            </div>
                        </div>
                    </div>
                `;
                
                container.append(cardHtml);
            });
            
            // Event listener untuk perubahan rekening belanja
            $('.rekening-select').change(function() {
                const rekeningId = $(this).val();
                const kegiatanId = $(this).data('kegiatan-id');
                const tahun = '{{ $tahun }}';
                const bulan = '{{ $bulan }}';
                
                if (rekeningId) {
                    loadUraianByRekening(tahun, bulan, rekeningId, kegiatanId);
                } else {
                    $(`#uraianContainer-${kegiatanId}`).empty();
                }
            });
        }

        // Fungsi untuk memuat uraian berdasarkan rekening
        function loadUraianByRekening(tahun, bulan, rekeningId, kegiatanId) {
            $.ajax({
                url: '{{ route("bku.uraian", ["tahun" => ":tahun", "bulan" => ":bulan", "rekeningId" => ":rekeningId"]) }}'
                    .replace(':tahun', tahun)
                    .replace(':bulan', bulan)
                    .replace(':rekeningId', rekeningId),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderUraianOptions(response.data, kegiatanId);
                    } else {
                        alert('Gagal memuat data uraian: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat memuat data uraian');
                    console.error(xhr);
                }
            });
        }

        // Fungsi untuk merender opsi uraian
        function renderUraianOptions(data, kegiatanId) {
        const container = $(`#uraianContainer-${kegiatanId}`);
        container.empty();
        
        if (data.length === 0) {
        container.html('<div class="alert alert-info">Tidak ada data uraian untuk rekening ini</div>');
        return;
        }
        
        const uraianHtml = `
        <div class="col-sm-12 justify-content-between align-content-between d-flex">
            <div class="label">
                <p>Uraian</p>
            </div>
            <div class="form-check">
                <input class="form-check-input check-all-uraian" type="checkbox" data-kegiatan-id="${kegiatanId}">
                <label class="form-check-label">
                    <strong>Pilih</strong> semua uraian
                </label>
            </div>
        </div>
        <hr>
        ${data.map((uraian, index) => `
        <div class="row justify-content-center align-content-center uraian-item" data-uraian-id="${uraian.id}">
            <div class="col-md-4 mb-1">
                <div class="form-check">
                    <input class="form-check-input uraian-checkbox" type="checkbox" name="uraian" value="${uraian.id}"
                        data-anggaran="${uraian.anggaran}">
                    <label class="form-check-label">
                        ${uraian.uraian}
                    </label>
                </div>
            </div>
            <div class="col-md-4 mb-1">
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control jumlah-input" value="${uraian.jumlah}" aria-label="Jumlah"
                        maxlength="3">
                    <span class="input-group-text">Max. 3</span>
                </div>
            </div>
            <div class="col-md-4 mb-1">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text">Rp.</span>
                    <input type="text" class="form-control harga-input"
                        value="${parseFloat(uraian.harga_satuan).toLocaleString('id-ID')}" aria-label="Harga" readonly>
                </div>
            </div>
        </div>
        `).join('')}
        `;
        
        container.html(uraianHtml);
        
        // Event listener untuk checkbox "pilih semua"
        $(`.check-all-uraian[data-kegiatan-id="${kegiatanId}"]`).change(function() {
        const isChecked = $(this).is(':checked');
        $(this).closest('.uraian-container').find('.uraian-checkbox').prop('checked', isChecked);
        updateTotalTransaksiDisplay();
        });
        
        // Event listener untuk checkbox uraian individual
        $(`.uraian-checkbox`).change(function() {
        updateTotalTransaksiDisplay();
        });
        
        // Event listener untuk input jumlah
        $(`.jumlah-input`).on('input', function() {
        updateTotalTransaksiDisplay();
        });
        }

        // Fungsi untuk mengumpulkan data dari form
        function collectFormData() {
        const totalTransaksiKotor = window.currentTotalTransaksi || 0;
        
        const formData = {
        penganggaran_id: '{{ $penganggaran->id }}',
        kode_kegiatan_id: $('.kegiatan-select:first').val(),
        kode_rekening_id: $('.rekening-select:first').val(),
        tanggal_nota: $('#tanggal_nota').val(),
        jenis_transaksi: $('#jenis_transaksi').val(),
        nomor_nota: $('#nomor_nota').val(),
        nama_penyedia: $('#checkForm').is(':checked') ? $('#nama_penerima').val() : $('#nama_toko').val(),
        nama_penerima: $('#nama_penerima_hidden').val(),
        alamat: $('#alamat_toko').val(),
        nomor_telepon: $('#nomor_telepon').val(),
        npwp: $('#checkNpwp').is(':checked') ? null : $('#npwp').val(),
        uraian_items: [],
        pajak_items: [],
        bulan: '{{ $bulan }}',
        total_transaksi_kotor: totalTransaksiKotor
        };
        
        // Kumpulkan data uraian yang dipilih
        $('.uraian-item').each(function() {
        const checkbox = $(this).find('.uraian-checkbox');
        if (checkbox.is(':checked')) {
        const jumlah = parseFloat($(this).find('.jumlah-input').val()) || 0;
        const hargaText = $(this).find('.harga-input').val();
        
        // Bersihkan format angka (hapus titik dan koma)
        const harga = parseFloat(hargaText.replace(/\./g, '').replace(',', '.')) || 0;
        
        formData.uraian_items.push({
        id: checkbox.val(),
        jumlah_belanja: jumlah * harga
        });
        }
        });

        // Fungsi untuk memformat angka ke format Rupiah
        function formatRupiah(angka) {
        return angka.toLocaleString('id-ID');
        }
        
        // Event listener untuk memformat harga input
        $(document).on('blur', '.harga-input', function() {
        const value = $(this).val();
        if (value) {
        const numericValue = parseFloat(value.replace(/\./g, '').replace(',', '.'));
        if (!isNaN(numericValue)) {
        $(this).val(formatRupiah(numericValue));
        updateTotalTransaksiDisplay();
        }
        }
        });
        
        // Event listener untuk input jumlah
        $(document).on('input', '.jumlah-input', function() {
        // Validasi hanya angka dan maksimal 3 digit
        $(this).val($(this).val().replace(/[^0-9]/g, '').substring(0, 3));
        updateTotalTransaksiDisplay();
        });
        
        // Kumpulkan data pajak jika ada
        if ($('#checkPajak').is(':checked')) {
        const pajakData = {
        jenis_pajak: $('#selectPajak').val(),
        persen_pajak: parseFloat($('#persenPajak').val()) || 0,
        total_pajak: parseFloat($('#totalPajak').val().replace(/\./g, '')) || 0
        };
        formData.pajak_items.push(pajakData);
        
        if ($('#checkPajakPb1').is(':checked')) {
        const pb1Data = {
        jenis_pajak: $('#selectPb1').val(),
        persen_pajak: parseFloat($('#persenPb1').val()) || 0,
        total_pajak: parseFloat($('#totalPb1').val().replace(/\./g, '')) || 0
        };
        formData.pajak_items.push(pb1Data);
        }
        }
        
        return formData;
        }
        
        // Event listener untuk tombol simpan
        // Event listener untuk tombol simpan
        $('#saveBtn').click(function() {
        const formData = collectFormData();
        
        console.log('Data yang akan dikirim:', formData);
        
        // Tampilkan loading SweetAlert
        Swal.fire({
        title: 'Menyimpan Data',
        text: 'Sedang memproses data transaksi...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
        Swal.showLoading();
        }
        });
        
        // Kirim data ke server
        $.ajax({
        url: '{{ route("bku.store") }}',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
        },
        success: function(response) {
        Swal.close();
        
        if (response.success) {
        Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: response.message,
        confirmButtonColor: '#0d6efd',
        }).then((result) => {
        // Trigger custom event untuk update saldo
        $(document).trigger('bkuSaved', [response]);
        
        if (result.isConfirmed) {
        $('#transactionModal').modal('hide');
        location.reload(); // atau gunakan AJAX untuk update table
        }
        });
        } else {
        Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: response.message,
        confirmButtonColor: '#0d6efd',
        });
        }
        },
        error: function(xhr) {
        Swal.close();
        console.error('Error response:', xhr.responseJSON);
        
        if (xhr.status === 422) {
        // Validasi error
        const errors = xhr.responseJSON.errors;
        let errorMessage = 'Terjadi kesalahan validasi:\n';
        
        for (const field in errors) {
        errorMessage += `- ${errors[field][0]}\n`;
        }
        
        Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: errorMessage.replace(/\n/g, '<br>'),
        confirmButtonColor: '#0d6efd',
        });
        } else if (xhr.status === 500) {
        Swal.fire({
        icon: 'error',
        title: 'Kesalahan Server',
        text: 'Terjadi kesalahan server: ' + xhr.responseJSON.message,
        confirmButtonColor: '#0d6efd',
        });
        } else {
        Swal.fire({
        icon: 'error',
        title: 'Kesalahan',
        text: 'Terjadi kesalahan saat menyimpan data',
        confirmButtonColor: '#0d6efd',
        });
        }
        }
        });
        });
    });
</script>
@endpush