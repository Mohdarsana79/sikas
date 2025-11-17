@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<style>
    .card {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
    }

    .card:not(.bg-light):hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-color: #0d6efd;
    }

    .bi {
        transition: all 0.3s ease;
    }

    .card:not(.bg-light):hover .bi {
        color: #0d6efd !important;
    }

    /* Style untuk tombol yang disabled */
    .btn-disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
<div class="content-area" id="contentArea">
    <!-- Dashboard Content -->
    <div class="fade-in">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gradient">Buku Kas Umum (BKU)</h1>
                <p class="mb-0 text-muted">Kelola buku kas umum sekolah Anda.</p>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Card BKU -->
        <div class="card">
            <div class="card-body p-0">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs border-bottom-0" id="bkuTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bosp-reguler-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-reguler" type="button" role="tab">
                            BOSP Reguler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-daerah-tab" data-bs-toggle="tab" data-bs-target="#bosp-daerah"
                            type="button" role="tab">
                            BOSP Daerah
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-kinerja-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-kinerja" type="button" role="tab">
                            BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="silpa-bosp-tab" data-bs-toggle="tab" data-bs-target="#silpa-bosp"
                            type="button" role="tab">
                            SiLPA BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lainnya-tab" data-bs-toggle="tab" data-bs-target="#lainnya"
                            type="button" role="tab">
                            Lainnya
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="bkuTabsContent">
                    <!-- BOSP Reguler Tab -->
                    <div class="tab-pane fade show active" id="bosp-reguler" role="tabpanel">
                        <div class="card bg-body-secondary ms-3 me-3 mt-3 mb-3">
                            <div class="p-4">
                                @if($penganggaranList->count() > 0)
                                <div class="mb-4">
                                    <h5 class="fw-bold">BKU BOSP Reguler</h5>
                                    <div class="card bg-info bg-opacity-10 p-3">
                                        <span><i class="bi bi-exclamation-circle me-1"></i>Laporkan minimal 50% belanja dana tahap 2 paling lambat 31 Agustus setiap tahun</span>
                                    </div>
                                </div>

                                <!-- Accordion untuk setiap tahun -->
                                <div class="accordion" id="accordionTahun">
                                    @foreach($penganggaranList as $index => $penganggaran)
                                    @php
                                    $tahun = $penganggaran->tahun_anggaran;
                                    $statusBulan = $statusPerTahun[$tahun] ?? [];
                                    $isFirst = $index === 0;

                                    // Cek status penerimaan dana untuk tahun ini
                                    $penerimaanTahunIni = $penganggaran->penerimaanDanas;
                                    $hasTahap1 = $penerimaanTahunIni->where('sumber_dana', 'Bosp Reguler Tahap
                                    1')->count() > 0;
                                    $hasTahap2 = $penerimaanTahunIni->where('sumber_dana', 'Bosp Reguler Tahap
                                    2')->count() > 0;
                                    $isMaximal = $hasTahap1 && $hasTahap2;
                                    @endphp

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $tahun }}">
                                            <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse{{ $tahun }}"
                                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                                aria-controls="collapse{{ $tahun }}">
                                                <i class="bi bi-calendar3 me-2"></i>
                                                <strong>BKU Tahun Anggaran {{ $tahun }}</strong>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $tahun }}"
                                            class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                            aria-labelledby="heading{{ $tahun }}" data-bs-parent="#accordionTahun">
                                            <div class="accordion-body">
                                                <div class="d-flex justify-content-between mb-4">
                                                    <div>
                                                        <a href="{{ route('laporan.rekapan-bku') }}?tahun={{ $tahun }}" class="btn btn-outline-secondary btn-sm-custom"
                                                            id="btnCetak" target="_blank">
                                                            <i class="bi bi-printer me-1"></i>
                                                            Cetak
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <button
                                                            class="btn btn-primary {{ $isMaximal ? 'btn-disabled' : '' }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#tambahPenerimaanModal"
                                                            onclick="setActiveTahun({{ $tahun }}, {{ $penganggaran->id }}, {{ $isMaximal ? 'true' : 'false' }})"
                                                            id="tambahButton{{ $tahun }}" {{ $isMaximal ? 'disabled'
                                                            : '' }}>
                                                            <i class="bi bi-plus-circle me-2"></i>Tambah Penerimaan Dana
                                                            @if($isMaximal)
                                                            <small class="d-block">(Sudah lengkap)</small>
                                                            @endif
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Card bulanan -->
                                                <div class="row">
                                                    @php
                                                    $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei',
                                                    'Juni', 'Juli', 'Agustus',
                                                    'September', 'Oktober',
                                                    'November', 'Desember'];
                                                    @endphp

                                                    @foreach($bulanList as $bulan)
                                                    @php
                                                    $status = $statusBulan[$bulan] ?? 'disabled';
                                                    $isDisabled = $status === 'disabled';
                                                    $cardClass = $isDisabled ? 'bg-light text-muted' : 'bg-white';
                                                    $textClass = $isDisabled ? 'text-muted' : 'text-dark';

                                                    // Tentukan icon dan badge
                                                    $icon = '';
                                                    $badgeClass = '';
                                                    $badgeText = '';

                                                    switch($status) {
                                                    case 'belum_diisi':
                                                    $icon = 'bi-circle';
                                                    $badgeClass = 'bg-secondary';
                                                    $badgeText = 'Belum Diisi';
                                                    break;
                                                    case 'draft':
                                                    $icon = 'bi-pencil';
                                                    $badgeClass = 'bg-warning text-dark';
                                                    $badgeText = 'Draft';
                                                    break;
                                                    case 'selesai':
                                                    $icon = 'bi-check-circle';
                                                    $badgeClass = 'bg-success';
                                                    $badgeText = 'Selesai';
                                                    break;
                                                    default:
                                                    $icon = 'bi-lock';
                                                    $badgeClass = 'bg-light text-muted';
                                                    $badgeText = 'Terkunci';
                                                    }

                                                    // Tentukan link berdasarkan status
                                                    $link = $isDisabled ? '#' : route('bku.showByBulan', ['tahun' =>
                                                    $tahun, 'bulan' =>
                                                    $bulan]);
                                                    @endphp

                                                    <div class="col-md-3 mb-3">
                                                        <div class="card {{ $cardClass }} h-100" @if(!$isDisabled)
                                                            onclick="window.location='{{ $link }}'"
                                                            style="cursor: pointer;" @endif>
                                                            <div class="card-body text-center">
                                                                <div class="mb-2">
                                                                    <i class="bi {{ $icon }} {{ $textClass }}"
                                                                        style="font-size: 2rem;"></i>
                                                                </div>
                                                                <h5 class="card-title {{ $textClass }}">{{ $bulan }}
                                                                </h5>
                                                                <span class="badge {{ $badgeClass }}">{{ $badgeText
                                                                    }}</span>

                                                                @if($status === 'disabled' && !$penganggaran)
                                                                <small class="d-block mt-2 text-muted">Data penganggaran
                                                                    tidak
                                                                    ditemukan</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Belum ada data RKAS</p>
                                    <a href="{{ route('rkas.index') }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-plus-circle me-2"></i>Buat RKAS
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tab lainnya tetap sama -->
                    <!-- ... -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Penerimaan Dana -->
<div class="modal fade" id="tambahPenerimaanModal" tabindex="-1" aria-labelledby="tambahPenerimaanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahPenerimaanModalLabel">Tambah Penerimaan Dana - Tahun <span
                        id="modalTahun"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informasi status tahun aktif -->
                <div id="modalStatusInfo" class="alert alert-info">
                    <!-- Status akan diisi oleh JavaScript -->
                </div>

                <!-- Container untuk daftar penerimaan dana -->
                <div id="daftarPenerimaanModal">
                    <!-- Data akan diisi melalui JavaScript -->
                </div>

                <!-- Form untuk tambah penerimaan baru -->
                <div id="formTambahPenerimaan" style="display: none;">
                    <hr>
                    <h5 class="mb-3">Tambah Penerimaan</h5>
                    <form id="formPenerimaanDana">
                        <input type="hidden" name="penganggaran_id" id="modalPenganggaranId" value="">
                        <input type="hidden" name="tahun_anggaran" id="modalTahunAnggaran" value="">

                        <div class="mb-3">
                            <label class="form-label">Sumber Dana</label>
                            <select class="form-select" name="sumber_dana" id="sumberDana" required>
                                <option value="">Pilih Sumber Dana</option>
                                <option value="Bosp Reguler Tahap 1">BOSP Reguler Tahap 1</option>
                                <option value="Bosp Reguler Tahap 2">BOSP Reguler Tahap 2</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Terima</label>
                            <input type="date" class="form-control" name="tanggal_terima" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Dana</label>
                            <input type="text" class="form-control" name="jumlah_dana" id="jumlahDana" required>
                        </div>

                        <div class="mb-3" id="tanggalSaldoAwalContainer">
                            <label class="form-label">Tanggal Saldo Awal</label>
                            <input type="date" class="form-control" name="tanggal_saldo_awal" id="tanggal_saldo_awal">
                        </div>

                        <div class="mb-3" id="saldoAwalContainer">
                            <label class="form-label">Saldo Awal</label>
                            <input type="text" class="form-control" name="saldo_awal" id="saldoAwal">
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="btnBatalTambah">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah Penerimaan -->
                <div id="tombolTambahContainer" class="text-center mt-3">
                    <button type="button" class="btn btn-outline-primary" id="btnTampilkanForm">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Penerimaan
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .penerimaan-item {
        padding: 12px;
        border-radius: 8px;
        background-color: #f8f9fa;
        margin-bottom: 15px;
    }

    .penerimaan-item strong {
        font-size: 16px;
        color: #333;
        display: block;
        margin-bottom: 5px;
    }

    .penerimaan-item p {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .penerimaan-item h5 {
        font-size: 18px;
        font-weight: bold;
        color: #198754;
        margin-bottom: 0;
    }

    #daftarPenerimaanModal {
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
    }

    #tombolTambahContainer {
        margin-top: 20px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    #formTambahPenerimaan {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    #saldoAwalContainer {
        transition: all 0.3s ease;
    }

    #btnTampilkanForm {
        transition: all 0.3s ease;
    }

    .btn-disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@push('scripts')
<script>
    // Variabel global untuk menyimpan tahun dan penganggaran_id yang aktif
    let activeTahun = null;
    let activePenganggaranId = null;
    let activeIsMaximal = false;
    let isLoading = false; // Flag untuk mencegah multiple requests

    // Fungsi untuk mengatur tahun aktif ketika tombol diklik
    function setActiveTahun(tahun, penganggaranId, isMaximal = false) {
        activeTahun = tahun;
        activePenganggaranId = penganggaranId;
        activeIsMaximal = isMaximal;
        
        // Update judul modal
        document.getElementById('modalTahun').textContent = tahun;
        document.getElementById('modalPenganggaranId').value = penganggaranId;
        document.getElementById('modalTahunAnggaran').value = tahun;
        
        // Update status info
        updateModalStatusInfo();
        
        console.log('Tahun aktif:', tahun, 'Penganggaran ID:', penganggaranId, 'Maximal:', isMaximal);
    }

    // Update informasi status di modal
    function updateModalStatusInfo() {
        const statusInfo = document.getElementById('modalStatusInfo');
        if (!statusInfo) return;
        
        if (activeIsMaximal) {
            statusInfo.innerHTML = `
                <h6 class="alert-heading">Penerimaan Dana Tahun ${activeTahun} Sudah Lengkap</h6>
                <p class="mb-0">Penerimaan dana untuk tahun ${activeTahun} sudah mencapai tahap maksimal (Tahap 1 dan Tahap 2). Tidak dapat menambah data lagi.</p>
            `;
            statusInfo.className = 'alert alert-warning';
            
            // Sembunyikan form dan tombol tambah
            document.getElementById('tombolTambahContainer').style.display = 'none';
            document.getElementById('formTambahPenerimaan').style.display = 'none';
            
            // Disable tombol tambah di accordion
            const tambahButton = document.getElementById('tambahButton' + activeTahun);
            if (tambahButton) {
                tambahButton.disabled = true;
                tambahButton.classList.add('btn-disabled');
            }
        } else {
            statusInfo.innerHTML = `
                <h6 class="alert-heading">Penerimaan Dana Tahun ${activeTahun}</h6>
                <p class="mb-0">Anda dapat menambah penerimaan dana untuk tahun anggaran ${activeTahun}.</p>
            `;
            statusInfo.className = 'alert alert-info';
            
            // Tampilkan tombol tambah
            document.getElementById('tombolTambahContainer').style.display = 'block';
            
            // Enable tombol tambah di accordion
            const tambahButton = document.getElementById('tambahButton' + activeTahun);
            if (tambahButton) {
                tambahButton.disabled = false;
                tambahButton.classList.remove('btn-disabled');
            }
        }
    }

    // Fungsi untuk menampilkan SweetAlert
    function showSweetAlert(icon, title, text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                timer: 3000,
                showConfirmButton: true
            });
        } else {
            // Fallback ke alert biasa jika SweetAlert tidak tersedia
            alert(title + ': ' + text);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Format currency functions
        function formatRupiah(angka) {
            if (!angka) return '';
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }
        
        function unformatRupiah(rupiah) {
            return parseInt(rupiah.replace(/[^0-9]/g, ''));
        }

        // Format input fields
        const jumlahDanaInput = document.getElementById('jumlahDana');
        if (jumlahDanaInput) {
            jumlahDanaInput.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }
        
        const saldoAwalInput = document.getElementById('saldoAwal');
        if (saldoAwalInput) {
            saldoAwalInput.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }
        
        // Fungsi untuk menampilkan/menyembunyikan form saldo awal
        function toggleSaldoAwalField() {
            const sumberDanaSelect = document.getElementById('sumberDana');
            const saldoAwalContainer = document.getElementById('saldoAwalContainer');
            
            if (sumberDanaSelect && saldoAwalContainer && tanggalSaldoAwalContainer) {
                // Tampilkan saldo awal hanya untuk BOSP Reguler Tahap 1
                if (sumberDanaSelect.value === 'Bosp Reguler Tahap 1') {
                    saldoAwalContainer.style.display = 'block';
                    tanggalSaldoAwalContainer.style.display = 'block';
                } else {
                    saldoAwalContainer.style.display = 'none';
                    tanggalSaldoAwalContainer.style.display = 'none';
                    // Reset nilai saldo awal jika bukan tahap 1
                    const saldoAwalInput = document.getElementById('saldoAwal');
                    if (saldoAwalInput) {
                        saldoAwalInput.value = '';
                    }
                    // reset nilai tanggal saldo awal
                    const tanggalSaldoAwalInput = document.getElementById('tanggal_saldo_awal');
                    if (tanggalSaldoAwalInput) {
                        tanggalSaldoAwalInput.value = '';
                    }
                }
            }
        }
        
        const sumberDanaSelect = document.getElementById('sumberDana');
        if (sumberDanaSelect) {
            sumberDanaSelect.addEventListener('change', toggleSaldoAwalField);
            toggleSaldoAwalField();
        }
        
        // Modal form controls
        const btnTampilkanForm = document.getElementById('btnTampilkanForm');
        if (btnTampilkanForm) {
            btnTampilkanForm.addEventListener('click', function() {
                if (activeIsMaximal) {
                    showSweetAlert('warning', 'Peringatan', 'Penerimaan dana untuk tahun ini sudah lengkap. Tidak dapat menambah data lagi.');
                    return;
                }
                document.getElementById('formTambahPenerimaan').style.display = 'block';
                document.getElementById('tombolTambahContainer').style.display = 'none';
            });
        }
        
        // Batal tambah penerimaan
        const btnBatalTambah = document.getElementById('btnBatalTambah');
        if (btnBatalTambah) {
            btnBatalTambah.addEventListener('click', function() {
                document.getElementById('formTambahPenerimaan').style.display = 'none';
                document.getElementById('tombolTambahContainer').style.display = 'block';
                document.getElementById('formPenerimaanDana').reset();
                toggleSaldoAwalField(); // Reset tampilan saldo awal
            });
        }

        // ========== CORE FUNCTIONS ==========
        
        // Load data penerimaan dana
        function loadPenerimaanDana() {
            if (!activePenganggaranId) {
                console.error('Tidak ada penganggaran ID yang aktif');
                const container = document.getElementById('daftarPenerimaanModal');
                if (container) {
                    container.innerHTML = '<div class="alert alert-warning">Silakan pilih tahun terlebih dahulu</div>';
                }
                return;
            }
            
            if (isLoading) {
                console.log('Request sedang berjalan, tunggu...');
                return;
            }
            
            const container = document.getElementById('daftarPenerimaanModal');
            
            if (!container) {
                console.error('Container daftarPenerimaanModal tidak ditemukan');
                return;
            }
            
            console.log('Loading data for penganggaran ID:', activePenganggaranId);
            
            isLoading = true;
            
            // Tampilkan loading indicator
            container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p>Memuat data...</p></div>';
            
            // Gunakan route dengan parameter yang dinamis
            const url = `{{ url('penatausahaan/penerimaan-dana') }}/${activePenganggaranId}`;
            
            fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                updateTampilanPenerimaanDana(data);
                isLoading = false;
            })
            .catch(error => {
                console.error('Error loading penerimaan dana:', error);
                container.innerHTML = 
                    '<div class="alert alert-danger">Error memuat data penerimaan dana. Silakan refresh halaman dan coba lagi.</div>';
                isLoading = false;
            });
        }
        
        // Update tampilan daftar penerimaan dana
        function updateTampilanPenerimaanDana(data) {
            const container = document.getElementById('daftarPenerimaanModal');
            
            if (!container) {
                console.error('Container daftarPenerimaanModal tidak ditemukan');
                return;
            }
            
            // Cek jika data adalah error response
            if (data && data.error) {
                container.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Belum ada penerimaan dana untuk tahun ' + activeTahun + '</p>';
                
                // Tampilkan tombol tambah jika belum ada data dan belum maximal
                if (!activeIsMaximal) {
                    const tombolTambahContainer = document.getElementById('tombolTambahContainer');
                    if (tombolTambahContainer) {
                        tombolTambahContainer.style.display = 'block';
                    }
                }
                return;
            }
            
            let html = '<h6>Daftar Penerimaan Dana Tahun ' + activeTahun + '</h6>';
            
            data.forEach((item, index) => {
                // Pastikan properti yang diakses ada
                const sumberDana = item.sumber_dana || 'Sumber Dana Tidak Diketahui';
                const tanggalTerima = item.tanggal_terima ? new Date(item.tanggal_terima) : new Date();
                const jumlahDana = item.jumlah_dana || 0;
                
                // Format tanggal
                const formattedDate = tanggalTerima.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
                
                // Format jumlah dana
                const formattedAmount = 'Rp. ' + parseInt(jumlahDana).toLocaleString('id-ID');
                
                // Buat card untuk setiap penerimaan dana
                html += `
                <div class="penerimaan-item mb-3">
                    <strong>${sumberDana}</strong>
                    <p class="mb-1">${formattedDate}</p>
                    <h5 class="text-success">${formattedAmount}</h5>
                </div>`;
                
                // Tambahkan garis pemisah kecuali untuk item terakhir
                if (index < data.length - 1) {
                    html += '<hr>';
                }
            });
            
            container.innerHTML = html;
            
            // Update status info berdasarkan data terbaru
            updateModalStatusInfo();
            
            // Cek apakah sudah lengkap (Tahap 1 dan Tahap 2)
            const hasTahap1 = data.some(item => item.sumber_dana === 'Bosp Reguler Tahap 1');
            const hasTahap2 = data.some(item => item.sumber_dana === 'Bosp Reguler Tahap 2');
            const isMaximal = hasTahap1 && hasTahap2;
            
            if (isMaximal) {
                activeIsMaximal = true;
                updateModalStatusInfo();
            }
        }

        // Form submission
        const formPenerimaanDana = document.getElementById('formPenerimaanDana');
        if (formPenerimaanDana) {
            formPenerimaanDana.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!activePenganggaranId) {
                    showSweetAlert('error', 'Error', 'Silakan pilih tahun terlebih dahulu');
                    return;
                }
                
                if (activeIsMaximal) {
                    showSweetAlert('warning', 'Peringatan', 'Penerimaan dana untuk tahun ini sudah lengkap. Tidak dapat menambah data lagi.');
                    return;
                }
                
                const formData = new FormData(this);
                
                // Jika saldo_awal disembunyikan (Tahap 2), set nilai ke string kosong
                const saldoAwalContainer = document.getElementById('saldoAwalContainer');
                if (saldoAwalContainer && saldoAwalContainer.style.display === 'none') {
                    formData.set('saldo_awal', '');
                }
                
                // Konversi nilai ke format angka
                const jumlahDanaValue = formData.get('jumlah_dana');
                if (jumlahDanaValue) {
                    formData.set('jumlah_dana', unformatRupiah(jumlahDanaValue));
                }
                
                // Hanya proses saldo_awal jika tidak disembunyikan
                if (saldoAwalContainer && saldoAwalContainer.style.display !== 'none') {
                    const saldoAwalValue = formData.get('saldo_awal');
                    if (saldoAwalValue) {
                        formData.set('saldo_awal', unformatRupiah(saldoAwalValue));
                    } else {
                        formData.set('saldo_awal', '');
                    }
                }
                
                // Kirim data ke server
                fetch('{{ route("penerimaan-dana.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh daftar penerimaan dana
                        loadPenerimaanDana();
                        
                        // Reset dan sembunyikan form
                        document.getElementById('formPenerimaanDana').reset();
                        document.getElementById('formTambahPenerimaan').style.display = 'none';
                        document.getElementById('tombolTambahContainer').style.display = 'block';
                        
                        // Reset tampilan saldo awal
                        toggleSaldoAwalField();
                        
                        // Tampilkan notifikasi sukses dengan SweetAlert
                        showSweetAlert('success', 'Sukses', 'Penerimaan dana berhasil disimpan');
                        
                        // Update status tombol di accordion
                        updateModalStatusInfo();
                        
                        // Refresh halaman setelah 2 detik untuk update status
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showSweetAlert('error', 'Error', 'Gagal menyimpan data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showSweetAlert('error', 'Error', 'Terjadi kesalahan saat menyimpan data');
                });
            });
        }
        
        // Modal event handlers
        // Reset modal ketika ditutup
        const modal = document.getElementById('tambahPenerimaanModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                const form = document.getElementById('formPenerimaanDana');
                const formTambah = document.getElementById('formTambahPenerimaan');
                const tombolTambah = document.getElementById('tombolTambahContainer');
                
                if (form) form.reset();
                if (formTambah) formTambah.style.display = 'none';
                if (tombolTambah) tombolTambah.style.display = 'block';
                
                // Reset tampilan saldo awal
                toggleSaldoAwalField();
            });
            
            // Load data saat modal dibuka
            modal.addEventListener('show.bs.modal', function() {
                if (activePenganggaranId) {
                    loadPenerimaanDana();
                } else {
                    const container = document.getElementById('daftarPenerimaanModal');
                    if (container) {
                        container.innerHTML = '<div class="alert alert-warning">Silakan pilih tahun terlebih dahulu</div>';
                    }
                }
            });
        }
        
        // Set tahun pertama sebagai aktif secara default
        @if($penganggaranList->count() > 0)
            const firstPenganggaran = @json($penganggaranList->first());
            if (firstPenganggaran) {
                const penerimaanTahunIni = firstPenganggaran.penerimaanDanas || [];
                const hasTahap1 = penerimaanTahunIni.some(item => item.sumber_dana === 'Bosp Reguler Tahap 1');
                const hasTahap2 = penerimaanTahunIni.some(item => item.sumber_dana === 'Bosp Reguler Tahap 2');
                const isMaximal = hasTahap1 && hasTahap2;
                
                activeTahun = firstPenganggaran.tahun_anggaran;
                activePenganggaranId = firstPenganggaran.id;
                activeIsMaximal = isMaximal;
            }
        @endif
        
        // Expose function untuk retry button
        window.loadPenerimaanDana = loadPenerimaanDana;
        window.setActiveTahun = setActiveTahun;
    });
</script>
@endpush
@endsection
@include('layouts.footer')