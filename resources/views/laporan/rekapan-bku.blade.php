@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')

@section('content')
<div class="content-area" id="contentArea">
    <!-- Dashboard Content -->
    <div class="fade-in">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gradient">Rekapan Buku Kas</h1>
                <p class="mb-0 text-muted">Rekap Buku Kas Pembantu</p>
            </div>
        </div>

        <!-- Card Penganggaran -->
        <div class="card">
            <div class="card-body p-0">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs border-bottom-0" id="bkuTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bkp-bank-tab" data-bs-toggle="tab"
                            data-bs-target="#bkp-bank" type="button" role="tab">
                            BKP Bank
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bkp-pembantu-tab" data-bs-toggle="tab"
                            data-bs-target="#bkp-pembantu" type="button" role="tab">
                            BKP Pembantu
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bkp-umum-tab" data-bs-toggle="tab" data-bs-target="#bkp-umum"
                            type="button" role="tab">
                            BKP Umum
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bkp-pajak-tab" data-bs-toggle="tab" data-bs-target="#bkp-pajak"
                            type="button" role="tab">
                            BKP Pajak
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="Rob-tab" data-bs-toggle="tab" data-bs-target="#Rob" type="button"
                            role="tab">
                            ROB
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="Reg-tab" data-bs-toggle="tab" data-bs-target="#Reg" type="button"
                            role="tab">
                            REG
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="Ba-tab" data-bs-toggle="tab" data-bs-target="#Ba" type="button"
                            role="tab">
                            Berita Acara
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="Realisasi-tab" data-bs-toggle="tab" data-bs-target="#Realisasi" type="button"
                            role="tab">
                            Realisasi
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="bkuTabsContent">
                    <!-- BKP Bank Tab -->
                    <div class="tab-pane fade show active" id="bkp-bank" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-content-center mb-3">
                                <div>
                                    <select name="bulan" id="bulanSelect" class="form-select form-select-sm" style="width: 150px;">
                                        @foreach($months as $month)
                                        <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                            $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pengaturanKertasModalBkpBank"
                                        style="font-size: 9pt;">
                                        <i class="bi bi-printer me-2"></i>
                                        Cetak BKP Bank
                                    </button>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorBank" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>

                            <!-- Tabel BKP Bank -->
                            <div id="bkpBankTable">
                                @include('laporan.partials.bkp-bank-table', [
                                'bulan' => $bulan,
                                'tahun' => $tahun,
                                'bulanAngka' => $bulanAngka,
                                'penarikanTunais' => $penarikanTunais,
                                'bungaRecord' => $bungaRecord,
                                'saldoAwal' => $saldoAwal
                                ])
                            </div>
                        </div>
                    </div>

                    <!-- BKP Pembantu Tab -->
                    <div class="tab-pane fade" id="bkp-pembantu" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-content-center mb-3">
                                <div>
                                    <select name="bulan" id="bulanSelectPembantu" class="form-select form-select-sm" style="width: 150px;">
                                        @foreach($months as $month)
                                        <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                            $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pengaturanKertasModalBkpPembantu"
                                        style="font-size: 9pt;">
                                        <i class="bi bi-printer me-2"></i>
                                        Cetak BKP Bank
                                    </button>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorPembantu" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>

                            <!-- Tabel BKP Pembantu Tunai -->
                            <div id="bkpPembantuTable">
                                @include('laporan.partials.bkp-pembantu-table', [
                                'bulan' => $bulan,
                                'tahun' => $tahun,
                                'bulanAngka' => $bulanAngka,
                                'penarikanTunais' => $penarikanTunais,
                                'setorTunais' => $setorTunais,
                                'bkuDataTunai' => $bkuDataTunai,
                                'saldoAwalTunai' => $saldoAwalTunai
                                ])
                            </div>
                        </div>
                    </div>

                    <!-- BKP Umum Tab -->
                    <div class="tab-pane fade" id="bkp-umum" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-content-center mb-3">
                                <div>
                                    <select name="bulan" id="bulanSelectUmum" class="form-select form-select-sm" style="width: 150px;">
                                        @foreach($months as $month)
                                        <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                            $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pengaturanKertasModalBkpUmum"
                                        style="font-size: 9pt;">
                                        <i class="bi bi-printer me-2"></i>
                                        Cetak BKP Bank
                                    </button>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorUmum" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>

                            <!-- Tabel BKP Umum -->
                            <div id="bkpUmumTable">
                                @include('laporan.partials.bkp-umum-table', [
                                'bulan' => $bulan,
                                'tahun' => $tahun,
                                'bulanAngka' => $bulanAngka,
                                'penerimaanDanas' => $penerimaanDanas ?? collect(),
                                'penarikanTunais' => $penarikanTunais,
                                'terimaTunais' => $terimaTunais,
                                'setorTunais' => $setorTunais,
                                'bkuData' => $bkuData ?? collect(),
                                'bungaRecord' => $bungaRecord,
                                'saldoAwal' => $saldoAwal ?? 0,
                                'saldoAwalTunai' => $saldoAwalTunai ?? 0,
                                'totalPenerimaan' => $totalPenerimaan ?? 0,
                                'totalPengeluaran' => $totalPengeluaran ?? 0,
                                'currentSaldo' => $currentSaldo ?? 0,
                                'saldoBank' => $saldoBank ?? 0,
                                'saldoTunai' => $saldoTunai ?? 0,
                                'danaSekolah' => $danaSekolah ?? 0,
                                'danaBosp' => $danaBosp ?? 0
                                ])
                            </div>
                        </div>
                    </div>

                    <!-- BKP Pajak Tab -->
                    <div class="tab-pane fade" id="bkp-pajak" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <select name="bulan" id="bulanSelectPajak" class="form-select form-select-sm"
                                        style="width: 150px;">
                                        @foreach($months as $month)
                                        <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                        $month }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#pengaturanKertasModalBkpPajak" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i>
                                            Cetak BKP Pajak
                                        </button>
                                    </div>
                                </div>

                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorPajak" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>

                            <!-- Tabel BKP Pajak -->
                            <div id="bkpPajakTable">
                                @include('laporan.partials.bkp-pajak-table', [
                                'bulan' => $bulan,
                                'tahun' => $tahun,
                                'bulanAngka' => $bulanAngka,
                                'bkuData' => $bkuData ?? collect(),
                                'totalPph21' => $totalPph21 ?? 0,
                                'totalPph22' => $totalPph22 ?? 0,
                                'totalPph23' => $totalPph23 ?? 0,
                                'totalPphFinal' => $totalPphFinal ?? 0,
                                'totalPpn' => $totalPpn ?? 0,
                                'totalPenerimaan' => $totalPenerimaan ?? 0,
                                'totalPengeluaran' => $totalPengeluaran ?? 0,
                                'currentSaldo' => $currentSaldo ?? 0
                                ])
                            </div>
                        </div>
                    </div>

                    <!-- ROB Tab -->
                    <div class="tab-pane fade" id="Rob" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div>
                                        <select name="bulan" id="bulanSelectRob" class="form-select form-select-sm"
                                            style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                            $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#pengaturanKertasModalRob" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i>
                                            Cetak Rob
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorRob" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>

                            <!-- Tabel ROB -->
                            <div id="bkpRobTable">
                                @include('laporan.partials.bkp-rob-table', [
                                'robData' => $robData ?? []
                                ])
                            </div>
                        </div>
                    </div>

                    <!-- REGISTRASI Tab -->
                    <div class="tab-pane fade" id="Reg" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <select name="bulan" id="bulanSelectReg" class="form-select form-select-sm" style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                                $month }}</option>
                                            @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pengaturanKertasModalRegistrasi"
                                            style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i>
                                            Cetak Registrasi
                                    </button>
                                </div>
                            </div>
                    
                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorReg" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>
                    
                            <!-- Tabel Registrasi -->
                            <div id="bkpRegTable">
                                @if(isset($registrasiPenutupaKasData))
                                @include('laporan.partials.bkp-registrasi-table', $registrasiPenutupanKasData)
                                @else
                                <div class="alert alert-warning">
                                    Data Registrasi Penutupan Kas belum tersedia untuk bulan {{ $bulan }} {{ $tahun }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Berita Acara Tab -->
                    <div class="tab-pane fade" id="Ba" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <select name="bulan" id="bulanSelectBa" class="form-select form-select-sm" style="width: 150px;">
                                        @foreach($months as $month)
                                        <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pengaturanKertasModalBa"
                                        style="font-size: 9pt;">
                                        <i class="bi bi-printer me-2"></i>
                                        Cetak Berita Acara
                                    </button>
                                </div>
                            </div>
                    
                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorBa" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>
                    
                            <!-- Tabel Berita Acara -->
                            <div id="beritaAcaraTable">
                                @if(isset($beritaAcaraData))
                                @include('laporan.partials.ba-pemeriksaan-kas-table', $beritaAcaraData)
                                @else
                                <div class="alert alert-warning">
                                    Data Berita Acara belum tersedia untuk bulan {{ $bulan }} {{ $tahun }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- REALISASI TAB -->
                    <div class="tab-pane fade" id="Realisasi" role="tabpanel">
                        <div class="p-4">
                            <!-- Filter Section yang Disederhanakan -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">
                                        <!-- Tahun Anggaran (HIDDEN - diambil otomatis) -->
                                        <input type="hidden" id="tahunAnggaranRealisasi" value="{{ $tahun }}">
                    
                                        <div class="col-md-6">
                                            <label class="form-label">Periode Laporan</label>
                                            <select name="periode" id="periodeSelectRealisasi" class="form-select form-select-sm">
                                                <!-- Bulan -->
                                                <option value="Januari">Januari</option>
                                                <option value="Februari">Februari</option>
                                                <option value="Maret">Maret</option>
                                                <option value="April">April</option>
                                                <option value="Mei">Mei</option>
                                                <option value="Juni">Juni</option>
                                                <option value="Juli">Juli</option>
                                                <option value="Agustus">Agustus</option>
                                                <option value="September">September</option>
                                                <option value="Oktober">Oktober</option>
                                                <option value="November">November</option>
                                                <option value="Desember">Desember</option>
                                                <!-- Tahap -->
                                                <option value="Tahap 1">Tahap 1</option>
                                                <option value="Tahap 2">Tahap 2</option>
                                                <!-- Tahunan -->
                                                <option value="Tahunan">Tahunan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-primary btn-sm me-2" id="btnLoadRealisasi">
                                                    <i class="bi bi-arrow-clockwise me-2"></i>Muat Data
                                                </button>
                                                <button class="btn btn-success btn-sm" id="btnCetakRealisasi" data-bs-toggle="modal"
                                                    data-bs-target="#pengaturanCetakModalRealisasi">
                                                    <i class="bi bi-printer me-2"></i>Cetak Laporan
                                                </button>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-lightbulb me-1"></i>
                                            Tahun anggaran diambil otomatis dari tahun yang sedang aktif: <strong>{{ $tahun }}</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorRealisasi" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data realisasi...</span>
                            </div>
                    
                            <!-- Table Container -->
                            <div id="realisasiTableContainer">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-table display-4"></i>
                                    <p class="mt-3">Pilih periode, lalu klik "Muat Data" untuk menampilkan rekapitulasi realisasi<br>
                                        <small>Tahun Anggaran: <strong>{{ $tahun }}</strong></small>
                                    </p>
                                </div>
                            </div>
                    
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('laporan.modal.pengaturan-cetak-rob')
@include('laporan.modal.pengaturan-cetak-bkp-bank')
@include('laporan.modal.pengaturan-cetak-bkp-pembantu')
@include('laporan.modal.pengaturan-cetak-bkp-umum')
@include('laporan.modal.pengaturan-cetak-bkp-pajak')
@include('laporan.modal.pengaturan-cetak-registrasi')
@include('laporan.modal.pengaturan-cetak-ba')
@include('laporan.modal.pengaturan-cetak-rekapitulasi')

<style>
    /* Animasi loading */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    #rkasTableBody {
        animation: fadeIn 0.3s ease-in;
    }

    #loadingIndicator {
        transition: all 0.3s ease;
    }

    /* Animasi tombol cetak */
    .btn-pulse {
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .display-bulan {
        font-weight: bold;
        color: #0d6efd;
    }

    .section-title {
        font-weight: bold;
        font-size: 1.1rem;
        margin: 15px 0 10px 0;
    }

    .subsection-title {
        font-weight: bold;
        font-size: 1rem;
        margin: 10px 0;
    }

    .detail-item {
        padding-left: 30px;
    }

    .text-right {
        text-align: right;
    }

    @media print {
        body {
            font-size: 10pt;
            background: none;
            color: #000;
        }

        .nav-tabs,
        .card-header,
        .btn {
            display: none !important;
        }

        .tab-content>.tab-pane {
            display: block !important;
            opacity: 1 !important;
        }

        .card {
            border: none;
            box-shadow: none;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }
    }

    .opacity-50 {
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }

    #bkpBankTable,
    #bkpPembantuTable,
    #bkpUmumTable,
    #bkpPajakTable,
    #bkpRobTable,
    #bkpRegTable,
    #realisasiTableContainer {
        transition: all 0.3s ease;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Style untuk ROB table */
    .table-rob {
        border: 1px solid #000 !important;
    }

    .rekening-header-row {
        background-color: #e8f4fd !important;
        font-weight: bold !important;
    }

    .total-row-rob {
        background-color: #d0d0d0 !important;
        font-weight: bold !important;
    }

    /* Style untuk tab */
    .nav-tabs .nav-link {
        font-size: 9pt;
        padding: 8px 12px;
    }

    /* Style khusus untuk tab Realisasi */
    #realisasiTableContainer {
        transition: all 0.3s ease;
    }

    .table-realisasi th {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        font-size: 9pt;
    }

    .table-realisasi td {
        vertical-align: middle;
        font-size: 9pt;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .bg-total {
        background-color: #e3f2fd !important;
        font-weight: bold;
    }

    .bg-grand-total {
        background-color: #d1ecf1 !important;
        font-weight: bold;
    }

    .persentase-cell {
        font-weight: bold;
        color: #198754;
    }

    .program-header {
        background-color: #f8f8f8;
        font-weight: bold;
    }

    /* Notifikasi */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .table-realisasi {
            font-size: 8pt;
        }

        #tahunSelectRealisasi,
        #periodeSelectRealisasi {
            margin-bottom: 10px;
        }

        .nav-tabs .nav-link {
            font-size: 8pt;
            padding: 6px 8px;
        }

        .btn-sm {
            font-size: 8pt;
            padding: 4px 8px;
        }

        /* Stack selects on mobile */
        .row.g-3>.col-md-4 {
            margin-bottom: 10px;
        }
    }

    /* Small table untuk mobile */
    .small-table table {
        font-size: 8pt;
    }

    .small-table .btn {
        font-size: 8pt;
        padding: 2px 6px;
    }

    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    /* Custom scrollbar untuk table */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Hover effects */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.075);
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Focus states untuk aksesibilitas */
    .btn:focus,
    .form-select:focus,
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        border-color: #80bdff;
    }

    /* Transition untuk smooth interactions */
    .btn,
    .form-select,
    .nav-link,
    .tab-pane {
        transition: all 0.3s ease;
    }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
    // ==================== FUNGSI UMUM ====================
    
    // Fungsi untuk update URL cetak PDF semua tab
    function updatePdfUrls(selectedBulan) {
        // Update URL cetak BKP Bank
        var bankPdfUrl = '{{ route("laporan.bkp-bank-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        bankPdfUrl = bankPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButton').attr('href', bankPdfUrl);

        // Update URL cetak BKP Pembantu Tunai
        var pembantuPdfUrl = '{{ route("laporan.bku-pembantu-tunai-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        pembantuPdfUrl = pembantuPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButtonPembantu').attr('href', pembantuPdfUrl);

        // Update URL cetak BKP Umum
        var umumPdfUrl = '{{ route("laporan.bkp-umum-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        umumPdfUrl = umumPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButtonUmum').attr('href', umumPdfUrl);

        // Update URL cetak BKP Pajak
        var pajakPdfUrl = '{{ route("laporan.bkp-pajak-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        pajakPdfUrl = pajakPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButtonPajak').attr('href', pajakPdfUrl);

        // Update URL cetak ROB
        var robPdfUrl = '{{ route("laporan.bkp-rob-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        robPdfUrl = robPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButtonRob').attr('href', robPdfUrl);

        // Update URL cetak Registrasi
        var regPdfUrl = '{{ route("laporan.bkp-reg-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        regPdfUrl = regPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButtonReg').attr('href', regPdfUrl);

        // Update URL cetak Berita Acara
        var baPdfUrl = '{{ route("laporan.berita-acara-pdf", ["tahun" => $tahun, "bulan" => ":bulan"]) }}';
        baPdfUrl = baPdfUrl.replace(':bulan', selectedBulan);
        $('#cetakPdfButtonBa').attr('href', baPdfUrl);

        // Update URL cetak Realisasi
        var realisasiPdfUrl = '{{ route("laporan.realisasi-pdf", ["tahun" => $tahun, "periode" => ":periode"]) }}';
        realisasiPdfUrl = realisasiPdfUrl.replace(':periode', selectedBulan);
        $('#btnCetakRealisasi').data('pdf-url', realisasiPdfUrl);
    }

    // Fungsi untuk load table data berdasarkan tab
    function loadTableData(selectedBulan, tabType) {
        // Tentukan container ID berdasarkan tab type
        var containerId = '';
        var loadingIndicatorId = '';
        
        // Mapping container ID untuk setiap tab
        switch(tabType) {
            case 'Bank':
                containerId = '#bkpBankTable';
                loadingIndicatorId = '#loadingIndicatorBank';
                break;
            case 'Pembantu':
                containerId = '#bkpPembantuTable';
                loadingIndicatorId = '#loadingIndicatorPembantu';
                break;
            case 'Umum':
                containerId = '#bkpUmumTable';
                loadingIndicatorId = '#loadingIndicatorUmum';
                break;
            case 'Pajak':
                containerId = '#bkpPajakTable';
                loadingIndicatorId = '#loadingIndicatorPajak';
                break;
            case 'Rob':
                containerId = '#bkpRobTable';
                loadingIndicatorId = '#loadingIndicatorRob';
                break;
            case 'Reg':
                containerId = '#bkpRegTable';
                loadingIndicatorId = '#loadingIndicatorReg';
                break;
            case 'Ba':
                containerId = '#beritaAcaraTable';
                loadingIndicatorId = '#loadingIndicatorBa';
                break;
            case 'Realisasi':
                containerId = '#realisasiTableContainer';
                loadingIndicatorId = '#loadingIndicatorRealisasi';
                break;
            default:
                containerId = '#bkpBankTable';
                loadingIndicatorId = '#loadingIndicatorBank';
        }

        var loadingIndicator = $(loadingIndicatorId);
        var tableContainer = $(containerId);

        // Show loading
        loadingIndicator.removeClass('d-none');
        tableContainer.addClass('opacity-50');

        $.ajax({
            url: '{{ route("laporan.rekapan-bku.ajax") }}',
            type: 'GET',
            data: {
                tahun: '{{ $tahun }}',
                bulan: selectedBulan,
                tab_type: tabType
            },
            success: function(response) {
                if (response.success) {
                    tableContainer.html(response.html);
                    updatePdfUrls(selectedBulan);
                    
                    // Animasi untuk feedback visual
                    tableContainer.addClass('btn-pulse');
                    setTimeout(function() {
                        tableContainer.removeClass('btn-pulse');
                    }, 500);
                    
                    console.log('Data loaded for tab:', tabType, 'bulan:', selectedBulan, 'container:', containerId);
                } else {
                    alert('Gagal memuat data: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat memuat data');
                console.error('AJAX Error:', xhr);
            },
            complete: function() {
                loadingIndicator.addClass('d-none');
                tableContainer.removeClass('opacity-50');
            }
        });
    }

    // ==================== EVENT HANDLERS UNTUK SEMUA TAB ====================

    // Event untuk BKP Bank
    $('#bulanSelect').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Bank');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelectPembantu').val(selectedBulan);
        $('#bulanSelectUmum').val(selectedBulan);
        $('#bulanSelectPajak').val(selectedBulan);
        $('#bulanSelectRob').val(selectedBulan);
        $('#bulanSelectReg').val(selectedBulan);
        $('#bulanSelectBa').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event untuk BKP Pembantu
    $('#bulanSelectPembantu').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Pembantu');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelect').val(selectedBulan);
        $('#bulanSelectUmum').val(selectedBulan);
        $('#bulanSelectPajak').val(selectedBulan);
        $('#bulanSelectRob').val(selectedBulan);
        $('#bulanSelectReg').val(selectedBulan);
        $('#bulanSelectBa').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event untuk BKP Umum
    $('#bulanSelectUmum').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Umum');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelect').val(selectedBulan);
        $('#bulanSelectPembantu').val(selectedBulan);
        $('#bulanSelectPajak').val(selectedBulan);
        $('#bulanSelectRob').val(selectedBulan);
        $('#bulanSelectReg').val(selectedBulan);
        $('#bulanSelectBa').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event untuk BKP Pajak
    $('#bulanSelectPajak').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Pajak');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelect').val(selectedBulan);
        $('#bulanSelectPembantu').val(selectedBulan);
        $('#bulanSelectUmum').val(selectedBulan);
        $('#bulanSelectRob').val(selectedBulan);
        $('#bulanSelectReg').val(selectedBulan);
        $('#bulanSelectBa').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event untuk ROB
    $('#bulanSelectRob').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Rob');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelect').val(selectedBulan);
        $('#bulanSelectPembantu').val(selectedBulan);
        $('#bulanSelectUmum').val(selectedBulan);
        $('#bulanSelectPajak').val(selectedBulan);
        $('#bulanSelectReg').val(selectedBulan);
        $('#bulanSelectBa').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event untuk Registrasi
    $('#bulanSelectReg').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Reg');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelect').val(selectedBulan);
        $('#bulanSelectPembantu').val(selectedBulan);
        $('#bulanSelectUmum').val(selectedBulan);
        $('#bulanSelectPajak').val(selectedBulan);
        $('#bulanSelectRob').val(selectedBulan);
        $('#bulanSelectBa').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event untuk Berita Acara
    $('#bulanSelectBa').change(function() {
        var selectedBulan = $(this).val();
        loadTableData(selectedBulan, 'Ba');
        
        // Update select di tab lain agar konsisten
        $('#bulanSelect').val(selectedBulan);
        $('#bulanSelectPembantu').val(selectedBulan);
        $('#bulanSelectUmum').val(selectedBulan);
        $('#bulanSelectPajak').val(selectedBulan);
        $('#bulanSelectRob').val(selectedBulan);
        $('#bulanSelectReg').val(selectedBulan);
        $('#periodeSelectRealisasi').val(selectedBulan);
    });

    // Event ketika tab diubah
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('data-bs-target');
        var selectedBulan = $('#bulanSelect').val();
        
        console.log('Tab changed to:', target, 'selectedBulan:', selectedBulan);
        
        switch(target) {
            case '#bkp-pembantu':
                loadTableData(selectedBulan, 'Pembantu');
                break;
            case '#bkp-bank':
                loadTableData(selectedBulan, 'Bank');
                break;
            case '#bkp-umum':
                loadTableData(selectedBulan, 'Umum');
                break;
            case '#bkp-pajak':
                loadTableData(selectedBulan, 'Pajak');
                break;
            case '#Rob':
                loadTableData(selectedBulan, 'Rob');
                break;
            case '#Reg':
                loadTableData(selectedBulan, 'Reg');
                break;
            case '#Ba':
                loadTableData(selectedBulan, 'Ba');
                break;
            case '#Realisasi':
                // Untuk tab Realisasi, gunakan logika khusus
                initRealisasiTab();
                break;
        }
    });

    // ==================== FUNGSI KHUSUS UNTUK TAB REALISASI ====================

    function initRealisasiTab() {
        // Inisialisasi nilai default untuk periode
        var currentBulan = $('#bulanSelect').val();
        $('#periodeSelectRealisasi').val(currentBulan);

        // Auto load data jika belum ada
        if ($('#realisasiTableContainer').find('.table').length === 0) {
            loadRealisasiData();
        }
    }

    // Event untuk tombol Muat Data Realisasi
    $('#btnLoadRealisasi').click(function() {
        loadRealisasiData();
    });

    // Event untuk tombol Cetak PDF Realisasi
    $('#btnCetakRealisasiPDF').click(function() {
        cetakRealisasiPDF();
    });

    // Event untuk Enter key pada select Realisasi
    $('#periodeSelectRealisasi').keypress(function(e) {
        if (e.which == 13) {
            loadRealisasiData();
        }
    });

    // Fungsi untuk load data Realisasi
    function loadRealisasiData() {
        // Ambil tahun dari hidden input atau dari variabel PHP
        var tahun = $('#tahunAnggaranRealisasi').val();
        var periode = $('#periodeSelectRealisasi').val();
        
        if (!tahun || !periode) {
            alert('Tahun anggaran atau periode tidak ditemukan');
            return;
        }

        var loadingIndicator = $('#loadingIndicatorRealisasi');
        var tableContainer = $('#realisasiTableContainer');

        // Show loading
        loadingIndicator.removeClass('d-none');
        tableContainer.addClass('opacity-50');

        $.ajax({
            url: '{{ route("laporan.realisasi.data") }}',
            type: 'GET',
            data: {
                tahun: tahun,
                periode: periode,
                jenis_laporan: getJenisLaporan(periode)
            },
            success: function(response) {
                if (response.success) {
                    if (response.periode_info) {
                        response.html = response.html.replace('__PERIODE_INFO__', JSON.stringify(response.periode_info));
                    }
                    tableContainer.html(response.html);
                    
                    // Enable tombol cetak
                    $('#btnCetakRealisasi').prop('disabled', false);
                    
                    // Update URL PDF
                    updateRealisasiPdfUrl(tahun, periode);
                    
                    console.log('Data realisasi loaded successfully', {
                        tahun: tahun,
                        periode: periode,
                        totalRealisasi: response.realisasiData?.total_realisasi,
                        periodeInfo: response.periode_info
                    });
                } else {
                    alert('Gagal memuat data: ' + response.message);
                    tableContainer.html('<div class="alert alert-danger">Gagal memuat data realisasi: ' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Terjadi kesalahan saat memuat data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
                tableContainer.html('<div class="alert alert-danger">' + errorMessage + '</div>');
                console.error('AJAX Error:', error);
            },
            complete: function() {
                loadingIndicator.addClass('d-none');
                tableContainer.removeClass('opacity-50');
            }
        });
    }

    // Fungsi untuk cetak PDF Realisasi
    function cetakRealisasiPDF() {
        var tahun = $('#tahunAnggaranRealisasi').val();
        var periode = $('#periodeSelectRealisasi').val();
        var ukuranKertas = $('#ukuran_kertas_realisasi').val();
        var orientasi = $('#orientasi_realisasi').val();
        var fontSize = $('#font_size_realisasi').val();

        if (!tahun || !periode) {
            alert('Tahun anggaran atau periode tidak ditemukan');
            return;
        }

        var pdfUrl = "{{ route('laporan.realisasi-pdf', ['tahun' => ':tahun', 'periode' => ':periode']) }}";
        pdfUrl = pdfUrl.replace(':tahun', tahun).replace(':periode', encodeURIComponent(periode));
        
        // Tambahkan parameter cetak
        pdfUrl += `?ukuran_kertas=${encodeURIComponent(ukuranKertas)}`;
        pdfUrl += `&orientasi=${encodeURIComponent(orientasi)}`;
        pdfUrl += `&font_size=${encodeURIComponent(fontSize)}`;
        pdfUrl += `&jenis_laporan=${encodeURIComponent(getJenisLaporan(periode))}`;

        console.log('PDF Realisasi URL:', pdfUrl);

        // Buka PDF di tab baru
        window.open(pdfUrl, '_blank');
        
        // Tutup modal
        $('#pengaturanCetakModalRealisasi').modal('hide');
    }

    // Fungsi untuk update URL PDF Realisasi
    function updateRealisasiPdfUrl(tahun, periode) {
        var pdfUrl = "{{ route('laporan.realisasi-pdf', ['tahun' => ':tahun', 'periode' => ':periode']) }}";
        pdfUrl = pdfUrl.replace(':tahun', tahun).replace(':periode', encodeURIComponent(periode));
        $('#btnCetakRealisasi').data('pdf-url', pdfUrl);
    }

    // Fungsi untuk menentukan jenis laporan berdasarkan periode
    function getJenisLaporan(periode) {
        if (periode === 'Tahap 1' || periode === 'Tahap 2') {
            return 'tahap';
        } else if (periode === 'Tahunan') {
            return 'tahunan';
        } else {
            return 'bulanan';
        }
    }

    // ==================== INISIALISASI AWAL ====================

    // Load data untuk tab aktif saat pertama kali load
    var activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
    var selectedBulan = $('#bulanSelect').val();
    
    switch(activeTab) {
        case '#bkp-pembantu':
            loadTableData(selectedBulan, 'Pembantu');
            break;
        case '#bkp-umum':
            loadTableData(selectedBulan, 'Umum');
            break;
        case '#bkp-pajak':
            loadTableData(selectedBulan, 'Pajak');
            break;
        case '#Rob':
            loadTableData(selectedBulan, 'Rob');
            break;
        case '#Reg':
            loadTableData(selectedBulan, 'Reg');
            break;
        case '#Ba':
            loadTableData(selectedBulan, 'Ba');
            break;
        case '#Realisasi':
            initRealisasiTab();
            break;
        default:
            // Default load BKP Bank
            loadTableData(selectedBulan, 'Bank');
    }

    // Update URLs saat pertama kali load
    updatePdfUrls('{{ $bulan }}');

    // Inisialisasi modal pengaturan cetak Realisasi
    $('#pengaturanCetakModalRealisasi').on('show.bs.modal', function() {
        // Set nilai default untuk modal Realisasi
        $('#ukuran_kertas_realisasi').val('A4');
        $('#orientasi_realisasi').val('landscape');
        $('#font_size_realisasi').val('9pt');
    });

    // ==================== FUNGSI TAMBAHAN UNTUK FEEDBACK VISUAL ====================

    // Animasi untuk tombol yang diklik
    $(document).on('click', '.btn', function() {
        $(this).addClass('btn-pulse');
        setTimeout(function() {
            $('.btn').removeClass('btn-pulse');
        }, 500);
    });

    // Handle error pada AJAX calls
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        console.error('AJAX Error:', {
            url: ajaxSettings.url,
            status: jqXHR.status,
            error: thrownError,
            response: jqXHR.responseText
        });
        
        // Tampilkan notifikasi error
        if (jqXHR.status === 500) {
            showNotification('Terjadi kesalahan server. Silakan coba lagi.', 'danger');
        } else if (jqXHR.status === 404) {
            showNotification('Data tidak ditemukan.', 'warning');
        } else if (jqXHR.status === 403) {
            showNotification('Anda tidak memiliki akses.', 'danger');
        }
    });

    // Fungsi untuk menampilkan notifikasi
    function showNotification(message, type) {
        var alertClass = 'alert-' + type;
        var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                            message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>');
        
        $('.content-area').prepend(notification);
        
        // Auto hide setelah 5 detik
        setTimeout(function() {
            notification.alert('close');
        }, 5000);
    }

    // ==================== FUNGSI UNTUK RESPONSIVE BEHAVIOR ====================

    // Handle window resize
    $(window).resize(function() {
        // Adjust table responsiveness
        $('.table-responsive').each(function() {
            if ($(this).width() < 768) {
                $(this).addClass('small-table');
            } else {
                $(this).removeClass('small-table');
            }
        });
    });

    // Trigger initial resize check
    $(window).trigger('resize');

    // ==================== KEYBOARD SHORTCUTS ====================

    $(document).keydown(function(e) {
        // Ctrl + P untuk cetak (jika di tab yang mendukung)
        if (e.ctrlKey && e.keyCode === 80) {
            e.preventDefault();
            var activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
            
            switch(activeTab) {
                case '#Realisasi':
                    $('#btnCetakRealisasi').click();
                    break;
                case '#bkp-bank':
                    $('#cetakPdfButton').click();
                    break;
                // Tambahkan case untuk tab lainnya sesuai kebutuhan
            }
        }

        // F5 untuk refresh data tab aktif
        if (e.keyCode === 116) {
            e.preventDefault();
            var activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
            var selectedBulan = $('#bulanSelect').val();
            
            switch(activeTab) {
                case '#Realisasi':
                    loadRealisasiData();
                    break;
                case '#bkp-bank':
                    loadTableData(selectedBulan, 'Bank');
                    break;
                case '#bkp-pembantu':
                    loadTableData(selectedBulan, 'Pembantu');
                    break;
                case '#bkp-umum':
                    loadTableData(selectedBulan, 'Umum');
                    break;
                case '#bkp-pajak':
                    loadTableData(selectedBulan, 'Pajak');
                    break;
                case '#Rob':
                    loadTableData(selectedBulan, 'Rob');
                    break;
                case '#Reg':
                    loadTableData(selectedBulan, 'Reg');
                    break;
                case '#Ba':
                    loadTableData(selectedBulan, 'Ba');
                    break;
            }
        }
    });

    console.log('Rekapan BKU JavaScript initialized successfully');
});
</script>
@endpush
@endsection