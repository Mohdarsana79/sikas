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
                                    <input type="date" class="form-control" aria-label="Sizing example input"
                                        aria-describedby="inputGroup-sizing-sm">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Transaksi</label>
                                <select name="" id="select" class="form-select form-select-sm"
                                    aria-label="Small select example">
                                    <option value="">Pilih Tunai/Nontunai</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="non tunai">Non Tunai</option>
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
                                            <span class="input-group-text" id="inputGroup-sizing-sm"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" aria-label="Sizing example input"
                                                aria-describedby="inputGroup-sizing-sm" id="nama_toko">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Form Nama Penerima/Penyedia -->
                                <div id="formPenerima" class="form-transition d-none">
                                    <div class="mb-3">
                                        <label for="nama_penerima" class="form-label">Nama Penerima / Penyedia</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control" aria-label="Sizing example input"
                                                aria-describedby="inputGroup-sizing-sm" id="nama_penerima">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat_toko" class="form-label">Alamat Toko/Badan Usaha/Penerima</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="text" class="form-control" aria-label="Sizing example input"
                                            aria-describedby="inputGroup-sizing-sm" id="alamat_toko">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control" aria-label="Sizing example input"
                                            aria-describedby="inputGroup-sizing-sm" id="nomor_telepon">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="npwp" class="form-label">NPWP Toko</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="text" class="form-control" aria-label="Sizing example input"
                                            aria-describedby="inputGroup-sizing-sm" id="npwp">
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
                                    <input type="text" class="form-control" aria-label="Sizing example input"
                                        aria-describedby="inputGroup-sizing-sm" id="nomor_nota">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_nota" class="form-label">Tanggal Belanja/Nota</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" class="form-control" aria-label="Sizing example input"
                                        aria-describedby="inputGroup-sizing-sm" id="tanggal_nota">
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">Kegiatan 1</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Kegiatan</label>
                                            <select name="" id="select" class="form-select form-select-sm"
                                                aria-label="Small select example">
                                                <option value="">Pilih Jenis Kegiatan</option>
                                                <option value="tunai">Pembayaran Listrik</option>
                                                <option value="non tunai">Pembayaran Air</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Rekening Belanja</label>
                                            <select name="" id="select" class="form-select form-select-sm"
                                                aria-label="Small select example">
                                                <option value="">Pilih Rekening Belanja</option>
                                                <option value="tunai">Belanja Listrik</option>
                                                <option value="non tunai">Belanja Air</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-12 justify-content-between align-content-between d-flex">
                                            <div class="label">
                                                <p>Uraian</p>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="checkUraianAll">
                                                <label class="form-check-label" for="checkUraianAll">
                                                    <strong>Pilih</strong> semua uraian
                                                </label>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row justify-content-center align-content-center">
                                            <div class="col-md-4 mb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" id="checkUraian">
                                                    <label class="form-check-label" for="checkUraian">
                                                        Belanja Listrik
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <div class="input-group input-group-sm mb-3">
                                                    <input type="text" class="form-control" aria-label="Sizing example input"
                                                        aria-describedby="inputGroup-sizing-sm">
                                                    <span class="input-group-text" id="inputGroup-sizing-sm">Max. 3</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp. </span>
                                                    <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row justify-content-center align-content-center">
                                            <div class="col-md-4 mb-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" id="checkUraian">
                                                    <label class="form-check-label" for="checkUraian">
                                                        Belanja Listrik
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <div class="input-group input-group-sm mb-3">
                                                    <input type="text" class="form-control" aria-label="Sizing example input"
                                                        aria-describedby="inputGroup-sizing-sm">
                                                    <span class="input-group-text" id="inputGroup-sizing-sm">Max. 3</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp. </span>
                                                    <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="justify-content-end align-content-end">
                                    <button class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-plus-circle me-1"></i>Tambah Kegiatan
                                    </button>
                                </div>
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
                                                <strong>Total Transaksi</strong>
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <span>Rp.500.000</span>
                                            </div>

                                            <!-- Form Pajak (Awalnya Disembunyikan) -->
                                            <div id="pajakForm" class="d-none">
                                                <div class="pajak-card">
                                                    <h6 class="mb-3">Form Pajak</h6>
                                                    <div class="pajak-form-row">
                                                        <div class="pajak-form-col">
                                                            <label class="form-label">Pajak PPh/PPn</label>
                                                            <select name="" id="selectPajak"
                                                                class="form-select form-select-sm"
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
                                                                <input type="text" class="form-control" id="persenPajak"
                                                                    aria-label="Sizing example input"
                                                                    aria-describedby="inputGroup-sizing-sm">
                                                            </div>
                                                        </div>
                                                        <div class="pajak-form-col">
                                                            <label class="form-label">Total Pajak</label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text"
                                                                    id="inputGroup-sizing-sm">Rp</span>
                                                                <input type="text" class="form-control" id="totalPajak"
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
                                                                <select name="" id="selectPb1"
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
                                                                        id="persenPb1" aria-label="Sizing example input"
                                                                        aria-describedby="inputGroup-sizing-sm">
                                                                </div>
                                                            </div>
                                                            <div class="pajak-form-col">
                                                                <label class="form-label">Total Pajak</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text"
                                                                        id="inputGroup-sizing-sm">Rp</span>
                                                                    <input type="text" class="form-control"
                                                                        id="totalPb1" aria-label="Sizing example input"
                                                                        aria-describedby="inputGroup-sizing-sm"
                                                                        disabled>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <strong>Total Bersih</strong>
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <span id="totalBersih">Rp.500.000</span>
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

        // Initialize modal
        $('#transactionModal').on('show.bs.modal', function() {
            resetSteps();
        });

        // Next button click
        $('#nextBtn').click(function() {
            if (currentStep < totalSteps) {
                currentStep++;
                updateSteps();
            }
        });

        // Previous button click
        $('#prevBtn').click(function() {
            if (currentStep > 1) {
                currentStep--;
                updateSteps();
            }
        });

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

        // Save button click
        $('#saveBtn').click(function() {
            // Simulate save process
            const modal = $('#transactionModal');
            modal.modal('hide');
            
            // Show success message (you can replace this with actual save logic)
            setTimeout(function() {
                alert('Transaksi berhasil disimpan!');
            }, 500);
        });

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
            } else {
                $('#formToko').removeClass('d-none');
                $('#formPenerima').addClass('d-none');
            }
        });

        // Hitung total pajak secara real-time
        $('#persenPajak').on('input', function() {
            calculateTax('pajak');
        });

        $('#persenPb1').on('input', function() {
            calculateTax('pb1');
        });

        function calculateTax(type) {
            const totalTransaksi = 500000; // Ganti dengan nilai sebenarnya dari transaksi
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
            const totalTransaksi = 500000; // Ganti dengan nilai sebenarnya dari transaksi
            const totalPajak = parseFloat($('#totalPajak').val().replace(/\./g, '')) || 0;
            const totalPb1 = parseFloat($('#totalPb1').val().replace(/\./g, '')) || 0;
            
            const totalBersih = totalTransaksi - totalPajak - totalPb1;
            
            // Update tampilan total bersih
            $('#totalBersih').text('Rp.' + totalBersih.toLocaleString('id-ID'));
        }

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

        // Tombol info format NPWP
        $('#btnFormatNpwp').click(function() {
            alert('Format NPWP yang benar: XX.XXX.XXX.X-XXX.XXX\n\nContoh: 12.345.678.9-012.345');
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
            
            // Reset total bersih
            $('#totalBersih').text('Rp.500.000');
        });
    });
</script>
@endpush

