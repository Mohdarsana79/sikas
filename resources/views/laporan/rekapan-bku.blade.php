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
            <div class="tanggal-cetak" style="font-size: 10pt;">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editTanggalCetak"
                    style="font-size: 10pt;">
                    <i class="bi bi-calendar-heart me-2"></i>Tanggal Cetak
                </button>
                <span class="ms-2">Tanggal Cetak:
                    @if($penganggaran->tanggal_cetak)
                    {{ \Carbon\Carbon::parse($penganggaran->tanggal_cetak)->format('d/m/Y') }}
                    @else
                    Belum diisi
                    @endif
                </span>
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
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="bkuTabsContent">
                    <!-- BKP Bank Tab -->
                    <div class="tab-pane fade show active" id="bkp-bank" role="tabpanel">
                        <div class="p-4">
                            <form method="GET" action="{{ route('laporan.rekapan-bku', ['tahun' => $tahun]) }}"
                                id="bulanForm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <select name="bulan" id="bulanSelect" class="form-select form-select-sm"
                                            style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                                $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButton"
                                            href="{{ route('laporan.bkp-bank-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak BKP Bank
                                        </a>
                                    </div>
                                </div>
                            </form>

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
                            <form method="GET" action="{{ route('laporan.rekapan-bku', ['tahun' => $tahun]) }}"
                                id="bulanFormPembantu">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <select name="bulan" id="bulanSelectPembantu" class="form-select form-select-sm"
                                            style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                                $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButtonPembantu"
                                            href="{{ route('laporan.bku-pembantu-tunai-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak BKP Pembantu Tunai
                                        </a>
                                    </div>
                                </div>
                            </form>

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
                            <form method="GET" action="{{ route('laporan.rekapan-bku', ['tahun' => $tahun]) }}"
                                id="bulanFormUmum">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <select name="bulan" id="bulanSelectUmum" class="form-select form-select-sm"
                                            style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                                $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButtonUmum"
                                            href="{{ route('laporan.bkp-umum-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak BKP Umum
                                        </a>
                                    </div>
                                </div>
                            </form>

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
                            <form method="GET" action="{{ route('laporan.rekapan-bku', ['tahun' => $tahun]) }}"
                                id="bulanFormPajak">
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
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButtonPajak"
                                            href="{{ route('laporan.bkp-pajak-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak BKP Pajak
                                        </a>
                                    </div>
                                </div>
                            </form>

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
                            <form method="GET" action="{{ route('laporan.rekapan-bku', ['tahun' => $tahun]) }}"
                                id="bulanFormRob">
                                <div class="d-flex justify-content-between align-items-center mb-3">
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
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButtonRob"
                                            href="{{ route('laporan.bkp-rob-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak Rincian Objek Belanja
                                        </a>
                                    </div>
                                </div>
                            </form>

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
                            <form method="GET" action="{{ route('laporan.rekapan-bku', ['tahun' => $tahun]) }}" id="bulanFormReg">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <select name="bulan" id="bulanSelectReg" class="form-select form-select-sm" style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{ $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButtonReg"
                                            href="{{ route('laporan.bkp-reg-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak Registrasi
                                        </a>
                                    </div>
                                </div>
                            </form>
                    
                            <!-- Loading Indicator -->
                            <div id="loadingIndicatorReg" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Memuat data...</span>
                            </div>
                    
                            <!-- Tabel Registrasi -->
                            <div id="bkpRegTable">
                                @include('laporan.partials.bkp-registrasi-table', [
                                'tahun' => $tahun,
                                'bulan' => $bulan,
                                'bulanAngka' => $bulanAngka,
                                'penganggaran' => $penganggaran,
                                'sekolah' => $sekolah ?? null,
                                'totalPenerimaan' => 0,
                                'totalPengeluaran' => 0,
                                'saldoBuku' => 0,
                                'saldoKas' => 0,
                                'saldoBank' => 0,
                                'uangKertas' => $uangKertas ?? [],
                                'uangLogam' => $uangLogam ?? [],
                                'totalUangKertas' => $totalUangKertas ?? 0,
                                'totalUangLogam' => $totalUangLogam ?? 0,
                                'saldoAkhirBuku' => $saldoAkhirBuku ?? 0,
                                'perbedaan' => 0,
                                'penjelasanPerbedaan' => '',
                                'tanggalPenutupan' => \Carbon\Carbon::now()->format('d F Y'),
                                'tanggalPenutupanLalu' => '-',
                                'namaBendahara' => 'Dra. MASITAH ABDULLAH',
                                'namaKepalaSekolah' => 'Dra. MASITAH ABDULLAH',
                                'nipBendahara' => '19690917 200701 2 017',
                                'nipKepalaSekolah' => '19690917 200701 2 017'
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Tanggal Cetak -->
<div class="modal fade" id="editTanggalCetak" tabindex="-1" aria-labelledby="editTanggalCetakLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTanggalCetakLabel">Tanggal Cetak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('penganggaran.update-tanggal-cetak', $penganggaran->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_tanggal_cetak" class="form-label">Tanggal Cetak</label>
                        <input type="date" class="form-control" id="edit_tanggal_cetak" name="tanggal_cetak"
                            value="{{ $penganggaran->tanggal_cetak ? \Carbon\Carbon::parse($penganggaran->tanggal_cetak)->format('Y-m-d') : '' }}"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    #bkpRegTable {
        transition: all 0.3s ease;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Tambahan style untuk ROB table */
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

    /* Style untuk tab Registrasi */
    .nav-tabs .nav-link {
        font-size: 9pt;
        padding: 8px 12px;
    }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        // Fungsi untuk update URL cetak PDF
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
        }

        // Fungsi untuk memuat data tabel
        function loadTableData(selectedBulan, tabType) {
            var loadingIndicator = $('#loadingIndicator' + tabType);
            var tableContainer = $('#bkp' + tabType + 'Table');

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
                        
                        console.log('Data loaded for tab:', tabType, 'bulan:', selectedBulan);
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
        });

        // Event ketika tab diubah
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('data-bs-target');
            var selectedBulan = $('#bulanSelect').val();
            
            console.log('Tab changed to:', target, 'selectedBulan:', selectedBulan);
            
            if (target === '#bkp-pembantu') {
                // Load data BKP Pembantu ketika tab diaktifkan
                loadTableData(selectedBulan, 'Pembantu');
            } else if (target === '#bkp-bank') {
                // Load data BKP Bank ketika tab diaktifkan
                loadTableData(selectedBulan, 'Bank');
            } else if (target === '#bkp-umum') {
                // Load data BKP Umum ketika tab diaktifkan
                loadTableData(selectedBulan, 'Umum');
            } else if (target === '#bkp-pajak') {
                // Load data BKP Pajak ketika tab diaktifkan
                loadTableData(selectedBulan, 'Pajak');
            } else if (target === '#Rob') {
                // Load data ROB ketika tab diaktifkan
                loadTableData(selectedBulan, 'Rob');
            } else if (target === '#Reg') {
                // Load data Registrasi ketika tab diaktifkan
                loadTableData(selectedBulan, 'Reg');
            }
        });

        // Load data untuk tab aktif saat pertama kali load
        var activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
        var selectedBulan = $('#bulanSelect').val();
        
        if (activeTab === '#bkp-pembantu') {
            loadTableData(selectedBulan, 'Pembantu');
        } else if (activeTab === '#bkp-umum') {
            loadTableData(selectedBulan, 'Umum');
        } else if (activeTab === '#bkp-pajak') {
            loadTableData(selectedBulan, 'Pajak');
        } else if (activeTab === '#Rob') {
            loadTableData(selectedBulan, 'Rob');
        } else if (activeTab === '#Reg') {
            loadTableData(selectedBulan, 'Reg');
        }

        // Update URLs saat pertama kali load
        updatePdfUrls('{{ $bulan }}');
    });
</script>
@endpush
@endsection