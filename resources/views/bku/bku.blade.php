@extends('layouts.app')
@include('layouts.navbar')
@include('layouts.sidebar')
@section('content')
<div class="container-fluid p-4" data-tahun="{{ $tahun }}" data-bulan="{{ $bulan }}" data-penganggaran-id="{{ $penganggaran->id ?? '' }}">
    @section('meta')
    <meta name="tahun" content="{{ $tahun }}">
    <meta name="bulan" content="{{ $bulan }}">
    <meta name="penganggaran-id" content="{{ $penganggaran->id ?? '' }}">
    @endsection
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- Top Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-arrow-left text-blue me-2"></i>
                    <a href="{{ route('penatausahaan.penatausahaan') }}" class="text-decoration-none"><span
                            class="text-blue small">Kembali ke Pengelolaan</span></a>
                </div>
                <div class="d-flex align-items-center small text-muted">
                    <i class="bi bi-question-circle me-2"></i>
                    <span>Butuh panduan mengisi?</span>
                    <a href="#" class="text-blue text-decoration-underline ms-2">Baca Panduan Selengkapnya</a>
                </div>
            </div>

            <!-- Main Header -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-semibold text-dark mb-1">BKU {{ $bulan }} {{$tahun}}</h1>
                    <div class="d-flex align-items-center small text-muted">
                        <span>BKSP REGULER {{$tahun}}</span>
                        <span class="mx-2">|</span>
                        @if($is_closed)
                        <span class="badge bg-danger me-2">Terkunci</span>
                        @if(!$has_transactions)
                        <span class="badge bg-info">BKU Kosong - Terkunci</span>
                        @endif
                        @else
                        <span class="badge bg-success me-2">Terbuka</span>
                        @if(count($bkuData) > 0)
                        <button href="#" class="btn btn-danger btn-sm ms-2" id="hapusSemuaBulan" data-bulan="{{ $bulan }}" data-tahun="{{ $tahun }}" data-jumlah-data="{{ count($bkuData) }}" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .55rem;">
                            Hapus BKU
                        </button>
                        @endif
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    @if(!$is_closed)
                    <button type="button" class="btn btn-outline-secondary btn-sm-custom" data-bs-toggle="modal"
                        data-bs-target="#transactionModal" id="btnTambahTransaksi">
                        <i class="bi bi-plus me-1"></i>
                        Tambah Pembelanjaan
                    </button>
                    {{-- TOMBOL AUDIT --}}
                    <button type="button" class="btn btn-outline-info btn-sm-custom" id="btnAudit">
                        <i class="bi bi-search me-1"></i>
                        Audit Data
                    </button>
                    <a href="{{ route('laporan.rekapan-bku') }}?tahun={{ $tahun }}"
                        class="btn btn-outline-secondary btn-sm-custom" id="btnCetak">
                        <i class="bi bi-printer me-1"></i>
                        Cetak
                    </a>
                    <button type="button" class="btn btn-dark btn-sm-custom" data-bs-toggle="modal"
                        data-bs-target="#tutupBku" id="btnTutupBku">
                        Tutup BKU
                    </button>
                    @else
                    <button type="button" class="btn btn-success btn-sm-custom" id="btnBukaBku" data-tahun="{{ $tahun }}" data-bulan="{{ $bulan }}" data-loading-text="Membuka...">
                        <i class="bi bi-unlock me-1"></i>
                        Buka BKU
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm-custom" id="btnEditBunga" data-tahun="{{ $tahun }}" data-bulan="{{ $bulan }}">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Bunga Bank
                    </button>
                    {{-- TOMBOL AUDIT UNTUK BKU TERTUTUP --}}
                    <button type="button" class="btn btn-outline-info btn-sm-custom" id="btnAudit">
                        <i class="bi bi-search me-1"></i>
                        Audit Data
                    </button>
                    <a href="{{ route('laporan.rekapan-bku') }}?tahun={{ $tahun }}"
                        class="btn btn-outline-secondary btn-sm-custom" id="btnCetak">
                        <i class="bi bi-printer me-1"></i>
                        Cetak
                    </a>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column - Dana Tersedia -->
        <div class="col-lg-6">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-inline-flex mb-1 justify-content-between w-100 align-items-center">
                        <h5 class="small fw-bold text-muted me-3">TOTAL DANA TERSEDIA</h5>
                        <div class="btn small-group" role="group" aria-label="Basic example">
                            <button class="btn btn-sm btn-dark me-2" id="btnTarikTunai"
                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                data-bs-toggle="modal" data-bs-target="#tarikTunai">Tarik Tunai</button>
                            <button class="btn btn-sm btn-light" id="btnSetorTunai"
                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                data-bs-toggle="modal" data-bs-target="#setorTunai">Setor Tunai</button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h4 class="fw-semibold text-dark">Rp {{ number_format($totalDanaTersedia -
                            $totalDibelanjakanSampaiBulanIni, 0, ',', '.') }}</h4>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <span class="form-label">Non Tunai</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                <input type="text" class="form-control" id="saldoNonTunaiDisplay"
                                    value="{{ number_format($saldoNonTunai, 0, ',', '.') }}"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                            </div>
                        </div>
                        <div class="col-6">
                            <span class="form-label">Tunai</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                <input type="text" class="form-control" id="saldoTunaiDisplay"
                                    value="{{ number_format($saldoTunai, 0, ',', '.') }}"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Anggaran -->
        <div class="col-lg-6">
            <!-- Anggaran Card -->
            <div class="card anggaran-card">
                <div class="card-body">
                    <h6 class="small fw-medium text-blue mt-3 mb-4">ANGGARAN DIBELANJAKAN SAMPAI BULAN INI</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Bisa dibelanjakan</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($anggaranBulanIni, 0, ',', '.') }}"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Sudah Dibelanjakan</span>
                                    <i class="bi bi-question-circle help-icon ms-1"
                                        title="Total belanja yang sudah dilakukan pada bulan {{ $bulan }}"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($totalDibelanjakanBulanIni, 0, ',', '.') }}"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Bisa dianggarkan ulang</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($anggaranBelumDibelanjakan, 0, ',', '.') }}"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row col">
            <!-- Table Card -->
            <div class="card mt-4">
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-3">ID</th>
                                    <th scope="col" class="px-4 py-3">Tanggal</th>
                                    <th scope="col" class="px-4 py-3">Kegiatan</th>
                                    <th scope="col" class="px-4 py-3">Rekening Belanja</th>
                                    <th scope="col" class="px-4 py-3">Jenis Transaksi</th>
                                    <th scope="col" class="px-4 py-3">Anggaran</th>
                                    <th scope="col" class="px-4 py-3">Dibelanjakan</th>
                                    <th scope="col" class="px-4 py-3">Pajak Wajib Lapor</th>
                                    <th scope="col" class="px-4 py-3 text-center" style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="bkuTableBody">
                                <!-- Data akan dimuat via JavaScript saat halaman pertama kali dibuka -->
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Memuat data transaksi...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('bku.modal.tambah-modal')
@include('bku.modal.tarik-tunai-modal')
@include('bku.modal.setor-tunai-modal')
@include('bku.modal.tutup-bku-modal')
@include('bku.modal.lapor-pajak-modal')

@if(isset($penganggaran) && $penganggaran->id)
@include('bku-audit.audit-modal', ['penganggaran' => $penganggaran])
@endif

@foreach ($bkuData as $bku)
@include('bku.modal.detail-modal')
@endforeach

<!-- Modal untuk Edit Bunga Bank -->
<div class="modal fade" id="editBungaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bunga Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditBunga">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_bunga_bank" class="form-label">Bunga Bank</label>
                        <input type="number" class="form-control" id="edit_bunga_bank" name="bunga_bank"
                            value="{{ $bunga_bank }}" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_pajak_bunga_bank" class="form-label">Pajak Bunga Bank</label>
                        <input type="number" class="form-control" id="edit_pajak_bunga_bank" name="pajak_bunga_bank"
                            value="{{ $pajak_bunga_bank }}" step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
        font-size: 14px;
    }

    .card {
        border: 1px solid #e9ecef;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .text-muted-custom {
        color: #6c757d !important;
        font-size: 12px;
    }

    .text-blue {
        color: #0d6efd !important;
    }

    .btn-sm-custom {
        padding: 0.375rem 0.75rem;
        font-size: 13px;
    }

    .input-group-text-rp {
        background-color: transparent;
        border-right: none;
        color: #6c757d;
    }

    .form-control-rp {
        border-left: none;
        color: #0d6efd;
        font-weight: 500;
    }

    .table th {
        background-color: #f8f9fa;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 500;
        color: #6c757d;
    }

    .table td {
        font-size: 12px;
        font-weight: 400;
        color: #212529;
        vertical-align: middle;
    }

    .nav-tabs .nav-link {
        border: 1px solid #dee2e6;
        border-bottom: none;
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .anggaran-card {
        border-left: 4px solid #0d6efd;
    }

    .help-icon {
        width: 12px;
        height: 12px;
        color: #0d6efd;
    }

    /* Dropdown styles - FIXED */
    .dropdown-toggle-simple {
        border: none;
        background: transparent;
        padding: 0.25rem;
        display: inline-block;
    }

    .dropdown-toggle-simple:focus {
        box-shadow: none;
    }

    .dropdown-menu {
        font-size: 12px;
        min-width: 120px;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid #dee2e6;
        z-index: 1060;
    }

    .dropdown-item {
        padding: 0.375rem 0.75rem;
        display: flex;
        align-items: center;
    }

    .dropdown-item i {
        font-size: 14px;
        width: 16px;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    /* Fix for dropdown positioning */
    .table-responsive {
        position: relative;
        overflow: visible !important;
    }

    /* Ensure dropdown is not clipped by table */
    .table {
        position: relative;
        z-index: 1;
    }

    /* Action column styles */
    td:last-child {
        position: relative;
        z-index: 2;
    }

    /* Specific fix for dropdown in table */
    .dropdown {
        position: static;
    }

    .dropdown-menu {
        position: absolute;
        inset: 0px auto auto 0px;
        margin: 0px;
        transform: translate(-120px, 30px);
    }

    /* Ensure dropdown appears above table */
    .table-responsive>.table>tbody>tr>td:last-child {
        overflow: visible;
    }

    /* Style for disabled state */
    .disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    /* Style khusus untuk baris penerimaan dana */
    .table-hover .bg-light:hover {
        background-color: #e9ecef !important;
    }

    /* Style untuk ikon panah */
    .bi-arrow-left-circle {
        color: #198754;
        /* Hijau untuk penerimaan/setor */
    }

    .bi-arrow-right-circle {
        color: #dc3545;
        /* Merah untuk penarikan */
    }

    /* Style untuk status pajak */
    .text-pajak-belum-lapor {
        color: #dc3545 !important;
        font-weight: 600;
    }

    .text-pajak-sudah-lapor {
        color: #212529 !important;
        font-weight: 500;
    }

    .status-pajak-badge {
        font-size: 0.7rem;
        padding: 0.1rem 0.3rem;
        border-radius: 0.25rem;
    }

    .badge-belum-lapor {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .badge-sudah-lapor {
        background-color: #d1edff;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Debug section styles */
    .debug-table {
        font-size: 12px;
    }

    .debug-table th {
        background-color: #fff3cd;
    }

    .debug-positive {
        color: #198754;
        font-weight: bold;
    }

    .debug-negative {
        color: #dc3545;
        font-weight: bold;
    }

    /* Search result styling */
    .search-result-info {
        font-size: 0.875rem;
        border-left: 4px solid #0dcaf0;
    }

    .search-result-row {
        background-color: #f8f9fa !important;
        border-left: 3px solid #0d6efd;
    }

    .search-result-row:hover {
        background-color: #e9ecef !important;
    }

    /* Tambahkan di CSS atau style tag */
    .btn-loading {
        position: relative;
        min-width: 120px; /* Beri lebar minimum agar tidak berubah */
    }

    .btn-loading .spinner-border {
        position: relative;
        margin-right: 8px;
        vertical-align: middle;
    }

    /* Animation untuk button loading */
    @keyframes button-loading {
        0% { opacity: 0.7; }
        50% { opacity: 1; }
        100% { opacity: 0.7; }
    }

    .btn-loading {
        animation: button-loading 1.5s ease-in-out infinite;
    }

    /* Hover effect untuk button loading */
    .btn-loading:hover {
        cursor: not-allowed;
        opacity: 0.8;
    }

    /* Tambahkan di app.css atau dalam <style> tag */
    .input-error {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .input-error:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .max-limit-exceeded {
        color: #dc3545 !important;
        font-weight: bold !important;
    }
</style>

<script>
    // Simpan nilai untuk BkuManager - SANGAT SEDERHANA
    window.bungaBankValue = {{ $bunga_bank ?? 0 }};
    window.pajakBungaBankValue = {{ $pajak_bunga_bank ?? 0 }};
    
    console.log('BKU values loaded:', {
        bunga: window.bungaBankValue,
        pajak: window.pajakBungaBankValue
    });
</script>
@endsection
@include('layouts.footer')