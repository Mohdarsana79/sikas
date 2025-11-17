@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<style>
    body {
        background-color: #f4f7f9;
        font-family: 'Inter', sans-serif;
        color: #333;
    }

    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
        background-color: #ffffff;
    }

    .card-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .metric-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .card-title-lg {
        font-size: 1.1rem;
        font-weight: 600;
        color: #555;
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .chart-card {
        min-height: 450px;
    }

    #progressCardFull {
        min-height: 250px;
    }

    /* Specific Color for Cards */
    .card-realization {
        background-color: #e6f7ff;
        color: #007bff;
    }

    .card-budget {
        background-color: #fff4e6;
        color: #ff9800;
    }

    .card-spending {
        background-color: #ffe6e6;
        color: #e91e63;
    }

    .card-revenue {
        background-color: #e6ffed;
        color: #4caf50;
    }

    .card-surplus {
        background-color: #e8e6ff;
        color: #673ab7;
    }

    .progress-sm {
        height: 8px;
        border-radius: 4px;
    }

    .progress-bar-animated {
        animation: progress-bar-stripes 1s linear infinite;
    }

    /* Loading state */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Error state */
    .error-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .error-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Custom badge untuk surplus/defisit */
    .badge-surplus {
        background-color: #28a745;
        color: white;
    }

    .badge-defisit {
        background-color: #dc3545;
        color: white;
    }

    /* Style untuk chart dengan multiple colors */
    .apexcharts-bar-series .apexcharts-bar-area {
        transition: all 0.3s ease;
    }

    .apexcharts-bar-series .apexcharts-bar-area:hover {
        opacity: 0.8;
        transform: translateY(-2px);
    }

    /* Style untuk tooltip yang lebih baik */
    .apexcharts-tooltip {
        border-radius: 8px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        #budgetUtilization {
            overflow-x: auto;
        }

        .apexcharts-canvas {
            min-width: 600px;
        }
    }

    /* Loading state untuk dashboard */
    .loading {
        opacity: 0.7;
        pointer-events: none;
        position: relative;
    }

    .loading::after {
        content: "Memuat data...";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        font-weight: 600;
        color: #333;
    }

    /* Style untuk error message */
    .alert {
        margin-bottom: 1rem;
        border-radius: 8px;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .error-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }

    .error-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .error-state p {
        margin: 0;
        font-size: 1.1rem;
    }
</style>

<div class="container-fluid py-4" id="dashboardContent">

    <!-- Header dengan Select Tahun -->
    <header class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold text-dark-emphasis" id="dashboardTitle">Dashboard Kinerja Anggaran {{ $tahunAktif
                }}</h1>
            <p class="text-muted">Analisis Real-Time Pagu dan Realisasi Anggaran</p>
        </div>
        <!-- Selektor Tahun -->
        <div class="d-flex align-items-center">
            <label for="selectYear" class="form-label me-2 mb-0 fw-semibold text-secondary">Tahun:</label>
            <select id="selectYear" class="form-select w-auto">
                @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $tahunAktif==$year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </header>

    <!-- Bagian 1: Cards Metrik Utama (8 Kolom) -->
    <div class="row g-4 mb-4">

        <!-- Pagu Anggaran -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 card-budget">
                <div class="d-flex align-items-center">
                    <i class="bi bi-wallet2 metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-secondary">Pagu Anggaran</div>
                        <div class="metric-value" id="paguAnggaran">
                            Rp {{ $statistik['pagu_anggaran_display'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Realisasi -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 card-realization">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check2-circle metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-secondary">Total Realisasi</div>
                        <div class="metric-value" id="totalRealisasi">
                            Rp {{ $statistik['total_realisasi_display'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presentase Realisasi -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 text-white bg-primary">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up-arrow metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-light">Presentase Realisasi</div>
                        <div class="metric-value text-white" id="realizationPercentage">
                            {{ number_format($statistik['persentase_realisasi'], 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisa Anggaran -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 text-white bg-danger">
                <div class="d-flex align-items-center">
                    <i class="bi bi-piggy-bank-fill metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-light">Sisa Anggaran</div>
                        <div class="metric-value text-white" id="sisaAnggaran">
                            Rp {{ $statistik['sisa_anggaran_display'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Penerimaan -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 card-revenue">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cash-stack metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-secondary">Total Penerimaan</div>
                        <div class="metric-value" id="totalPenerimaan">
                            Rp {{ $statistik['total_penerimaan_display'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Belanja -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 card-spending">
                <div class="d-flex align-items-center">
                    <i class="bi bi-basket metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-secondary">Total Belanja</div>
                        <div class="metric-value" id="totalBelanja">
                            Rp {{ $statistik['total_belanja_display'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Surplus/Defisit -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div
                class="card card-custom p-3 {{ $statistik['surplus_penerimaan'] >= 0 ? 'card-surplus' : 'card-spending' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-currency-dollar metric-icon me-3"></i>
                    <div>
                        <div class="card-title-lg text-secondary">
                            {{ $statistik['surplus_penerimaan'] >= 0 ? 'Surplus' : 'Defisit' }} (Penerimaan)
                        </div>
                        <div class="metric-value" id="surplusPenerimaan">
                            Rp {{ $statistik['surplus_penerimaan_display'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Efisiensi Card -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card card-custom p-3 text-dark bg-warning-subtle">
                <div class="d-flex align-items-center">
                    <i class="bi bi-lightning-charge-fill metric-icon me-3 text-warning"></i>
                    <div>
                        <div class="card-title-lg text-secondary">Efisiensi Anggaran</div>
                        <div class="metric-value" id="efisiensiAnggaran">
                            {{ number_format($statistik['efisiensi_anggaran'], 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bagian 2: Visualisasi Chart (Baris 1 - Realisasi Waktu, COL-12) -->
    <div class="row g-4 mb-4">
        <div class="col-xl-12 col-lg-12">
            <div class="card card-custom chart-card p-4">
                <h5 class="card-title-lg mb-3">Realisasi Anggaran Per Bulan (<span id="monthlyChartYear">{{
                        $tahunAktif }}</span>)</h5>
                <div id="monthlyRealizationChart">
                    @if(empty($grafikRealisasiTahunan['realisasi_data']))
                    <div class="error-state">
                        <i class="bi bi-bar-chart"></i>
                        <p>Tidak ada data realisasi bulanan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 3: Visualisasi Chart (Baris 2 - Perbandingan 5 Tahun, COL-12) -->
    <div class="row g-4 mb-4">
        <div class="col-xl-12 col-lg-12">
            <div class="card card-custom chart-card p-4">
                <h5 class="card-title-lg mb-3">Perbandingan Realisasi 5 Tahun Terakhir</h5>
                <div id="fiveYearComparisonChart">
                    @if(empty($perbandinganLimaTahun))
                    <div class="error-state">
                        <i class="bi bi-graph-up"></i>
                        <p>Tidak ada data perbandingan tahunan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 4: Visualisasi Chart (Baris 3 - Progress - COL-12) -->
    <div class="row g-4 mb-4">
        <div class="col-xl-12 col-lg-12">
            <div class="card card-custom p-4 text-center" id="progressCardFull">
                <h5 class="card-title-lg mb-3">Progress Realisasi Anggaran</h5>
                <div id="realizationProgress">
                    @if($statistik['persentase_realisasi'] <= 0) <div class="error-state">
                        <i class="bi bi-speedometer2"></i>
                        <p>Tidak ada data progress realisasi</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bagian 5: Visualisasi Chart (Baris 4 - Pemanfaatan - COL-12) -->
<div class="row g-4 mb-4">
    <div class="col-xl-12 col-lg-12">
        <div class="card card-custom p-4 text-center chart-card" style="min-height: 500px;">
            <h5 class="card-title-lg mb-3">Pemanfaatan Anggaran Pada Delapan Standar Nasional Pendidikan</h5>
            <div id="budgetUtilization">
                @if(empty($pemanfaatanAnggaran))
                <div class="error-state">
                    <i class="bi bi-pie-chart"></i>
                    <p>Tidak ada data pemanfaatan anggaran</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bagian 6: Visualisasi Chart (Baris 5 - Distribusi - COL-12) -->
<div class="row g-4 mb-4">
    <div class="col-xl-12 col-lg-12">
        <div class="card card-custom chart-card p-4">
            <h5 class="card-title-lg mb-3">Distribusi Realisasi Program</h5>
            <div id="programDistributionChart">
                @if($chartRealisasiProgram->isEmpty())
                <div class="error-state">
                    <i class="bi bi-pie-chart"></i>
                    <p>Tidak ada data distribusi program</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
    // Pass PHP data to JavaScript
    window.dashboardData = {
        grafikRealisasiTahunan: @json($grafikRealisasiTahunan),
        chartRealisasiProgram: @json($chartRealisasiProgram),
        pemanfaatanAnggaran: @json($pemanfaatanAnggaran),
        perbandinganLimaTahun: @json($perbandinganLimaTahun),
        statistik: @json($statistik)
    };
</script>
<script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush
@endsection