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
            box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
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
        let monthlyRealizationChart, programDistributionChart, realizationProgressChart, budgetUtilizationChart, fiveYearComparisonChart;
    
            // Format number helper
            function formatNumber(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }
    
            // Format currency helper - 31320000 -> Rp 31,320
            function formatCurrencyIDR(amount) {
                // Konversi ke format ribuan: 31320000 -> 31,320
                const amountInThousands = Math.round(amount / 1000);
                return 'Rp ' + formatNumber(amountInThousands);
            }
    
            // Format untuk chart tooltip
            function formatCurrencyChart(amount) {
                return 'Rp ' + formatNumber(Math.round(amount));
            }
    
            // Initialize Charts dengan data dari Laravel
            function initializeCharts() {
                try {
                    console.log('initializin charts....');
                    // Data dari PHP
                    const monthlyData = @json($grafikRealisasiTahunan);
                    const programData = @json($chartRealisasiProgram);
                    const pemanfaatanData = @json($pemanfaatanAnggaran);
                    const perbandinganData = @json($perbandinganLimaTahun);
                    const statistik = @json($statistik);
        
                    // CHART 1: Realisasi Anggaran Per Bulan - TANPA TARGET
                    if (monthlyData.realisasi_data && monthlyData.realisasi_data.length > 0) {
                        const monthlyRealizationOptions = {
                            series: [{
                                name: 'Realisasi',
                                type: 'area',
                                data: monthlyData.realisasi_data
                            }],
                            chart: {
                                height: 400,
                                type: 'line',
                                toolbar: { show: true },
                                zoom: { enabled: true }
                            },
                            dataLabels: { enabled: false },
                            stroke: { 
                                curve: 'smooth', 
                                width: 3 
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.7,
                                    opacityTo: 0.9,
                                    stops: [0, 100]
                                }
                            },
                            markers: { size: 4 },
                            title: { text: 'Realisasi Anggaran Per Bulan', align: 'left' },
                            xaxis: { 
                                categories: monthlyData.categories || [], 
                                title: { text: 'Bulan' } 
                            },
                            yaxis: { 
                                title: { text: 'Jumlah (Ribuan Rupiah)' },
                                labels: {
                                    formatter: function(value) {
                                        return formatCurrencyChart(value);
                                    }
                                }
                            },
                            colors: ['#007bff'],
                            tooltip: {
                                y: {
                                    formatter: function(value) {
                                        return formatCurrencyChart(value);
                                    }
                                }
                            }
                        };
        
                        monthlyRealizationChart = new ApexCharts(document.querySelector("#monthlyRealizationChart"), monthlyRealizationOptions);
                        monthlyRealizationChart.render();
                    }
        
                    // CHART 2: Distribusi Realisasi Program
                    if (programData && programData.length > 0) {
                        const programSeries = programData.map(item => parseFloat(item.total) || 0);
                        const programLabels = programData.map(item => item.nama_program);
        
                        const programDistributionOptions = {
                            series: programSeries,
                            chart: {
                                height: 450, 
                                type: 'donut',
                            },
                            labels: programLabels,
                            responsive: [{
                                breakpoint: 480,
                                options: { chart: { width: 200 }, legend: { position: 'bottom' } }
                            }],
                            legend: { position: 'bottom' },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '70%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total Realisasi',
                                                formatter: function (w) {
                                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    return formatCurrencyIDR(total);
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            colors: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#6610f2', '#e83e8c'],
                            tooltip: {
                                y: {
                                    formatter: function(value) {
                                        return formatCurrencyIDR(value);
                                    }
                                }
                            }
                        };
        
                        programDistributionChart = new ApexCharts(document.querySelector("#programDistributionChart"), programDistributionOptions);
                        programDistributionChart.render();
                    }
        
                    // CHART 3: Progress Realisasi Anggaran
                    if (statistik.persentase_realisasi > 0) {
                        const progressValue = parseFloat(statistik.persentase_realisasi) || 0;
                        const realizationProgressOptions = {
                            series: [progressValue],
                            chart: {
                                height: 250,
                                type: 'radialBar',
                                toolbar: { show: false }
                            },
                            plotOptions: {
                                radialBar: {
                                    startAngle: -135,
                                    endAngle: 225,
                                    hollow: {
                                        margin: 0,
                                        size: '70%',
                                        background: '#fff',
                                        dropShadow: { enabled: true, top: 3, left: 0, blur: 4, opacity: 0.24 }
                                    },
                                    track: {
                                        background: '#f1f1f1',
                                        strokeWidth: '67%',
                                        margin: 0,
                                        dropShadow: { enabled: true, top: -3, left: 0, blur: 4, opacity: 0.35 }
                                    },
                                    dataLabels: {
                                        show: true,
                                        name: {
                                            offsetY: -10,
                                            show: true,
                                            color: '#888',
                                            fontSize: '17px'
                                        },
                                        value: {
                                            formatter: function(val) { return parseInt(val) + '%'; },
                                            color: '#111',
                                            fontSize: '36px',
                                            show: true,
                                        }
                                    }
                                }
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'dark',
                                    type: 'horizontal',
                                    shadeIntensity: 0.5,
                                    gradientToColors: ['#00E396'],
                                    inverseColors: true,
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                }
                            },
                            stroke: { lineCap: 'round' },
                            labels: ['Realisasi'],
                        };
        
                        realizationProgressChart = new ApexCharts(document.querySelector("#realizationProgress"), realizationProgressOptions);
                        realizationProgressChart.render();
                    }
        
                    // CHART 4: Pemanfaatan Anggaran 8 SNP - PIE CHART DENGAN LEGEND DI BAWAH
                    if (pemanfaatanData && pemanfaatanData.length > 0) {
                        // Siapkan data untuk pie chart
                        const seriesData = pemanfaatanData.map(item => item.persentase);
                        const labels = pemanfaatanData.map(item => item.program);
                        
                        // Warna untuk setiap program SNP
                        const programColors = [
                            '#4e73df', // Biru - Pengembangan Kompetensi Lulusan
                            '#1cc88a', // Hijau - Pengembangan Standar Isi
                            '#36b9cc', // Cyan - Pengembangan Standar Proses
                            '#f6c23e', // Kuning - Pengembangan Pendidik dan Tenaga Kependidikan
                            '#e74a3b', // Merah - Pengembangan Sarana dan Prasarana Sekolah
                            '#858796', // Abu-abu - Pengembangan Standar Pengelolaan
                            '#6f42c1', // Ungu - Pengembangan Standar Pembiayaan
                            '#fd7e14'  // Orange - Pengembangan dan Implementasi Sistem Penilaian
                        ];

                        const budgetUtilizationOptions = {
                            series: seriesData,
                            chart: {
                                type: 'pie',
                                height: 500,
                                toolbar: {
                                    show: true,
                                    tools: {
                                        download: true,
                                        selection: false,
                                        zoom: false,
                                        zoomin: false,
                                        zoomout: false,
                                        pan: false,
                                        reset: true
                                    }
                                },
                                animations: {
                                    enabled: true,
                                    easing: 'easeinout',
                                    speed: 800
                                }
                            },
                            labels: labels,
                            colors: programColors,
                            dataLabels: {
                                enabled: true,
                                formatter: function(val, { seriesIndex, w }) {
                                    const data = pemanfaatanData[seriesIndex];
                                    return data.persentase.toFixed(1) + "%\nRp " + data.realisasi_display;
                                },
                                style: {
                                    colors: ['#fff'],
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    fontFamily: 'Inter, sans-serif'
                                },
                                background: {
                                    enabled: true,
                                    foreColor: '#333',
                                    padding: 4,
                                    borderRadius: 4,
                                    borderWidth: 1,
                                    borderColor: '#ddd',
                                    opacity: 0.9
                                },
                                dropShadow: {
                                    enabled: true,
                                    top: 1,
                                    left: 1,
                                    blur: 1,
                                    color: '#000',
                                    opacity: 0.45
                                }
                            },
                            plotOptions: {
                                pie: {
                                    size: '80%',
                                    donut: {
                                        size: '60%',
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true,
                                                fontSize: '16px',
                                                fontFamily: 'Inter, sans-serif',
                                                fontWeight: 600,
                                                color: '#333'
                                            },
                                            value: {
                                                show: true,
                                                fontSize: '24px',
                                                fontFamily: 'Inter, sans-serif',
                                                fontWeight: 700,
                                                color: '#333',
                                                formatter: function (val) {
                                                    return val + "%";
                                                }
                                            },
                                            total: {
                                                show: true,
                                                showAlways: true,
                                                label: 'Total Realisasi',
                                                fontSize: '14px',
                                                fontFamily: 'Inter, sans-serif',
                                                fontWeight: 600,
                                                color: '#333',
                                                formatter: function (w) {
                                                    const totalRealisasi = pemanfaatanData.reduce((sum, item) => sum + item.realisasi, 0);
                                                    return 'Rp ' + formatNumber(Math.round(totalRealisasi / 1000));
                                                }
                                            }
                                        }
                                    },
                                    expandOnClick: true,
                                    customScale: 1
                                }
                            },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: ['#fff']
                            },
                            fill: {
                                opacity: 0.9,
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'horizontal',
                                    shadeIntensity: 0.25,
                                    gradientToColors: undefined,
                                    inverseColors: false,
                                    opacityFrom: 0.9,
                                    opacityTo: 0.9,
                                    stops: [0, 100]
                                }
                            },
                            legend: {
                                show: true,
                                position: 'bottom',
                                horizontalAlign: 'center',
                                floating: false,
                                fontSize: '12px',
                                fontFamily: 'Inter, sans-serif',
                                fontWeight: 400,
                                formatter: function(seriesName, opts) {
                                    const data = pemanfaatanData[opts.seriesIndex];
                                    return seriesName + ': ' + data.persentase.toFixed(1) + '% (Rp ' + data.realisasi_display + ')';
                                },
                                markers: {
                                    width: 12,
                                    height: 12,
                                    strokeWidth: 0,
                                    strokeColor: '#fff',
                                    radius: 12,
                                    offsetX: -5,
                                    offsetY: 2
                                },
                                itemMargin: {
                                    horizontal: 10,
                                    vertical: 5
                                },
                                onItemClick: {
                                    toggleDataSeries: true
                                },
                                onItemHover: {
                                    highlightDataSeries: true
                                },
                                containerMargin: {
                                    top: 10,
                                    bottom: 0
                                }
                            },
                            tooltip: {
                                enabled: true,
                                shared: false,
                                followCursor: false,
                                fillSeriesColor: false,
                                theme: 'light',
                                style: {
                                    fontSize: '12px',
                                    fontFamily: 'Inter, sans-serif'
                                },
                                y: {
                                    formatter: function(val, { series, seriesIndex, w }) {
                                        const data = pemanfaatanData[seriesIndex];
                                        return `
                                        <div style="text-align: left; min-width: 200px;">
                                            <div style="font-weight: bold; margin-bottom: 6px; color: #333; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px;">
                                                ${data.program}
                                            </div>
                                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                                <div style="display: flex; justify-content: space-between;">
                                                    <span style="color: #666;">Persentase:</span>
                                                    <span style="font-weight: bold; color: ${programColors[seriesIndex]};">${data.persentase.toFixed(1)}%</span>
                                                </div>
                                                <div style="display: flex; justify-content: space-between;">
                                                    <span style="color: #666;">Realisasi:</span>
                                                    <span style="font-weight: bold; color: #4caf50;">Rp ${data.realisasi_display}</span>
                                                </div>
                                                <div style="display: flex; justify-content: space-between; margin-top: 3px; padding-top: 3px; border-top: 1px dashed #f0f0f0;">
                                                    <span style="color: #666;">Dari Total:</span>
                                                    <span style="font-weight: bold; color: #2196f3;">${((data.realisasi / data.total_realisasi) * 100).toFixed(1)}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        `;
                                    }
                                }
                            },
                            responsive: [{
                                breakpoint: 768,
                                options: {
                                    chart: {
                                        height: 450
                                    },
                                    legend: {
                                        position: 'bottom',
                                        horizontalAlign: 'center',
                                        fontSize: '11px',
                                        itemMargin: {
                                            horizontal: 5,
                                            vertical: 3
                                        }
                                    },
                                    plotOptions: {
                                        pie: {
                                            size: '75%',
                                            donut: {
                                                size: '50%'
                                            }
                                        }
                                    },
                                    dataLabels: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    }
                                }
                            }],
                            states: {
                                hover: {
                                    filter: {
                                        type: 'darken',
                                        value: 0.1
                                    }
                                },
                                active: {
                                    filter: {
                                        type: 'darken',
                                        value: 0.2
                                    }
                                }
                            }
                        };

                        // Hancurkan chart sebelumnya jika ada
                        if (budgetUtilizationChart) {
                            budgetUtilizationChart.destroy();
                        }

                        budgetUtilizationChart = new ApexCharts(document.querySelector("#budgetUtilization"), budgetUtilizationOptions);
                        budgetUtilizationChart.render();
                        
                    } else {
                        // Tampilkan pesan jika tidak ada data
                        document.querySelector("#budgetUtilization").innerHTML = `
                            <div class="error-state">
                                <i class="bi bi-pie-chart"></i>
                                <p>Tidak ada data pemanfaatan anggaran</p>
                            </div>
                        `;
                    }
        
                    // CHART 5: Perbandingan 5 Tahun Terakhir - FIXED
                    if (perbandinganData && perbandinganData.length > 0) {
                        console.log('Data perbandingan 5 tahun:', perbandinganData);
                        
                        // Siapkan data untuk chart dengan error handling
                        const tahunCategories = perbandinganData.map(item => {
                            if (!item || !item.tahun) return 'N/A';
                            return item.tahun.toString();
                        });
                        
                        const persentaseData = perbandinganData.map(item => item.realisasi || 0);
                        const realisasiData = perbandinganData.map(item => item.realisasi_rupiah || 0);

                        // Hitung range tahun untuk title
                        const tahunPertama = perbandinganData[0]?.tahun || '';
                        const tahunTerakhir = perbandinganData[perbandinganData.length - 1]?.tahun || '';
                        const chartTitle = `Perbandingan Realisasi ${tahunPertama} - ${tahunTerakhir}`;

                        const fiveYearComparisonOptions = {
                            series: [{
                                name: 'Persentase Realisasi',
                                type: 'column',
                                data: persentaseData
                            }, {
                                name: 'Realisasi (Rupiah)',
                                type: 'line',
                                data: realisasiData
                            }],
                            chart: {
                                height: 400,
                                type: 'line',
                                toolbar: {
                                    show: true
                                }
                            },
                            title: {
                                text: chartTitle,
                                align: 'left',
                                style: {
                                    fontSize: '16px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            },
                            stroke: {
                                width: [0, 3],
                                curve: 'smooth'
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '60%',
                                    borderRadius: 5,
                                    dataLabels: {
                                        position: 'top'
                                    }
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                enabledOnSeries: [0],
                                formatter: function(val) {
                                    return typeof val === 'number' ? val.toFixed(1) + "%" : "0%";
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    colors: ['#333']
                                },
                                background: {
                                    enabled: true,
                                    foreColor: '#fff',
                                    padding: 4,
                                    borderRadius: 4,
                                    borderWidth: 0,
                                    opacity: 0.8
                                },
                                offsetY: -20
                            },
                            markers: {
                                size: 5,
                                hover: {
                                    size: 7
                                }
                            },
                            xaxis: {
                                categories: tahunCategories,
                                title: {
                                    text: 'Tahun Anggaran'
                                },
                                labels: {
                                    style: {
                                        fontSize: '11px'
                                    }
                                }
                            },
                            yaxis: [
                                {
                                    seriesName: 'Persentase Realisasi',
                                    title: {
                                        text: 'Persentase Realisasi (%)'
                                    },
                                    min: 0,
                                    max: 100,
                                    labels: {
                                        formatter: function(val) {
                                            return typeof val === 'number' ? val.toFixed(0) + "%" : "0%";
                                        }
                                    }
                                },
                                {
                                    seriesName: 'Realisasi (Rupiah)',
                                    opposite: true,
                                    title: {
                                        text: 'Realisasi (Rupiah)'
                                    },
                                    labels: {
                                        formatter: function(val) {
                                            return 'Rp ' + formatNumber(Math.round((val || 0) / 1000));
                                        }
                                    }
                                }
                            ],
                            grid: {
                                borderColor: '#f0f0f0',
                                strokeDashArray: 3
                            },
                            colors: ['#4e73df', '#e74a3b'],
                            fill: {
                                opacity: [0.8, 1]
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    const data = perbandinganData[dataPointIndex];
                                    
                                    if (!data) {
                                        return `<div style="padding: 10px;">Data tidak tersedia</div>`;
                                    }
                                    
                                    return `
                                    <div style="background: #fff; padding: 12px; border-radius: 6px; border: 1px solid #e0e0e0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 250px;">
                                        <div style="font-weight: bold; margin-bottom: 8px; color: #333; border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; text-align: center;">
                                            Tahun ${data.tahun || 'N/A'}
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <span style="color: #666;">Persentase Realisasi:</span>
                                                <span style="font-weight: bold; color: #4e73df;">${data.realisasi || 0}%</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between;">
                                                <span style="color: #666;">Realisasi:</span>
                                                <span style="font-weight: bold; color: #e74a3b;">Rp ${data.realisasi_display || '0'}</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between;">
                                                <span style="color: #666;">Pagu Anggaran:</span>
                                                <span style="font-weight: bold; color: #1cc88a;">Rp ${data.pagu_display || '0'}</span>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'center',
                                fontSize: '12px'
                            }
                        };

                        // Hancurkan chart sebelumnya jika ada
                        if (fiveYearComparisonChart) {
                            try {
                                fiveYearComparisonChart.destroy();
                            } catch (e) {
                                console.log('Chart sebelumnya sudah dihancurkan');
                            }
                        }

                        try {
                            fiveYearComparisonChart = new ApexCharts(document.querySelector("#fiveYearComparisonChart"), fiveYearComparisonOptions);
                            fiveYearComparisonChart.render();
                            console.log('Chart perbandingan 5 tahun berhasil dirender');
                        } catch (error) {
                            console.error('Error rendering 5-year chart:', error);
                            document.querySelector("#fiveYearComparisonChart").innerHTML = `
                                <div class="error-state">
                                    <i class="bi bi-graph-up"></i>
                                    <p>Gagal memuat chart perbandingan 5 tahun</p>
                                </div>
                            `;
                        }
                        
                    } else {
                        console.log('Tidak ada data perbandingan 5 tahun:', perbandinganData);
                        document.querySelector("#fiveYearComparisonChart").innerHTML = `
                            <div class="error-state">
                                <i class="bi bi-graph-up"></i>
                                <p>Tidak ada data perbandingan untuk 5 tahun terakhir</p>
                            </div>
                        `;
                    }
                    console.log('Charts initialized successfully');
                } catch(error) {
                    console.error('Error initializing charts:', error);
                    // Tampilkan fallback message
                    document.querySelectorAll('#monthlyRealizationChart, #programDistributionChart, #realizationProgress, #budgetUtilization, #fiveYearComparisonChart').forEach(container => {
                        if (container.innerHTML === '') {
                            container.innerHTML = `<div class="error-state"><p>Gagal memuat chart</p></div>`;
                        }
                    });
                }
            }
    
            // Fungsi untuk update dashboard berdasarkan tahun - DIPERBAIKI
            async function updateDashboardYear(year) {
                try {
                    // Show loading state
                    document.getElementById('dashboardContent').classList.add('loading');
                    
                    console.log('Mengambil data untuk tahun:', year);

                    const response = await fetch(`/dashboard/api/dashboard-new-data?tahun=${year}`);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('Data diterima:', data);

                    if (data.success) {
                        // Update dashboard title
                        document.getElementById('dashboardTitle').textContent = `Dashboard Kinerja Anggaran ${year}`;
                        document.getElementById('monthlyChartYear').textContent = year;

                        // Update metric cards dengan format baru
                        document.getElementById('paguAnggaran').textContent = `Rp ${data.statistik.pagu_anggaran_display}`;
                        document.getElementById('totalRealisasi').textContent = `Rp ${data.statistik.total_realisasi_display}`;
                        document.getElementById('realizationPercentage').textContent = `${data.statistik.persentase_realisasi.toFixed(1)}%`;
                        document.getElementById('sisaAnggaran').textContent = `Rp ${data.statistik.sisa_anggaran_display}`;
                        document.getElementById('totalPenerimaan').textContent = `Rp ${data.statistik.total_penerimaan_display}`;
                        document.getElementById('totalBelanja').textContent = `Rp ${data.statistik.total_belanja_display}`;
                        document.getElementById('surplusPenerimaan').textContent = `Rp ${data.statistik.surplus_penerimaan_display}`;
                        document.getElementById('efisiensiAnggaran').textContent = `${data.statistik.efisiensi_anggaran.toFixed(1)}%`;

                        // Update surplus/defisit card styling
                        const surplusCard = document.querySelector('.col-xl-3:nth-child(7) .card-custom');
                        if (surplusCard) {
                            if (data.statistik.surplus_penerimaan >= 0) {
                                surplusCard.className = 'card card-custom p-3 card-surplus';
                                const titleElement = surplusCard.querySelector('.card-title-lg');
                                if (titleElement) {
                                    titleElement.textContent = 'Surplus (Penerimaan)';
                                }
                            } else {
                                surplusCard.className = 'card card-custom p-3 card-spending';
                                const titleElement = surplusCard.querySelector('.card-title-lg');
                                if (titleElement) {
                                    titleElement.textContent = 'Defisit (Penerimaan)';
                                }
                            }
                        }

                        // Update charts dengan error handling
                        try {
                            updateCharts(data);
                            console.log('Charts berhasil diupdate');
                        } catch (chartError) {
                            console.error('Error updating charts:', chartError);
                            // Tetap lanjut meski chart error
                        }

                    } else {
                        console.error('Failed to load dashboard data:', data.message);
                        showError('Gagal memuat data dashboard: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error updating dashboard:', error);
                    showError('Terjadi kesalahan saat memuat data dashboard: ' + error.message);
                } finally {
                    document.getElementById('dashboardContent').classList.remove('loading');
                }
            }

            // Fungsi untuk menampilkan error
            function showError(message) {
                // Buat atau update error message
                let errorDiv = document.getElementById('dashboardError');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'dashboardError';
                    errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                    errorDiv.innerHTML = `
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.getElementById('dashboardContent').prepend(errorDiv);
                } else {
                    errorDiv.innerHTML = `
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    errorDiv.style.display = 'block';
                }
            }
    
            // Fungsi untuk update semua charts dengan error handling yang lebih baik
            function updateCharts(data) {
                console.log('Updating charts dengan data:', data);
                
                try {
                    // Update Monthly Realization Chart
                    if (monthlyRealizationChart && data.grafik_realisasi_tahunan) {
                        const monthlyData = data.grafik_realisasi_tahunan.realisasi_data || [];
                        const categories = data.grafik_realisasi_tahunan.categories || [];
                        
                        if (monthlyData.length > 0) {
                            monthlyRealizationChart.updateSeries([{
                                name: 'Realisasi',
                                type: 'area',
                                data: monthlyData
                            }]);
                            monthlyRealizationChart.updateOptions({
                                xaxis: { categories: categories }
                            });
                        }
                    }

                    // Update Program Distribution Chart
                    if (programDistributionChart && data.chart_realisasi_program) {
                        const programData = Array.isArray(data.chart_realisasi_program) ? data.chart_realisasi_program : [];
                        const programSeries = programData.map(item => parseFloat(item.total) || 0);
                        const programLabels = programData.map(item => item.nama_program || 'Program');
                        
                        if (programSeries.length > 0) {
                            programDistributionChart.updateSeries(programSeries);
                            programDistributionChart.updateOptions({ labels: programLabels });
                        }
                    }

                    // Update Progress Chart
                    if (realizationProgressChart && data.statistik) {
                        realizationProgressChart.updateSeries([data.statistik.persentase_realisasi || 0]);
                    }

                    // Update Budget Utilization Chart (Pie Chart)
                    if (budgetUtilizationChart && data.pemanfaatan_anggaran) {
                        const utilizationData = Array.isArray(data.pemanfaatan_anggaran) ? data.pemanfaatan_anggaran : [];
                        const utilizationSeries = utilizationData.map(item => item.persentase || 0);
                        const utilizationLabels = utilizationData.map(item => item.program || 'Program');
                        
                        if (utilizationSeries.length > 0) {
                            budgetUtilizationChart.updateSeries(utilizationSeries);
                            budgetUtilizationChart.updateOptions({ labels: utilizationLabels });
                        }
                    }

                    // Update 5-Year Comparison Chart
                    if (fiveYearComparisonChart && data.perbandingan_lima_tahun) {
                        const comparisonData = Array.isArray(data.perbandingan_lima_tahun) ? data.perbandingan_lima_tahun : [];
                        const fiveYearSeries = [{
                            name: 'Persentase Realisasi',
                            type: 'column',
                            data: comparisonData.map(item => parseFloat(item.realisasi) || 0)
                        }, {
                            name: 'Realisasi (Rupiah)',
                            type: 'line',
                            data: comparisonData.map(item => parseFloat(item.realisasi_rupiah) || 0)
                        }];

                        const fiveYearCategories = comparisonData.map(item => {
                            return item && item.tahun ? item.tahun.toString() : 'N/A';
                        });

                        fiveYearComparisonChart.updateSeries(fiveYearSeries);
                        fiveYearComparisonChart.updateOptions({
                            xaxis: { categories: fiveYearCategories }
                        });
                    }

                    console.log('Semua charts berhasil diupdate');
                } catch (error) {
                    console.error('Error in updateCharts:', error);
                }
            }
    
            // Event listener untuk select year dengan error handling
            document.getElementById('selectYear').addEventListener('change', function(e) {
                const year = e.target.value;
                console.log('Tahun dipilih:', year);
                updateDashboardYear(year);
            });
    
            // Initialize charts ketika page loads dengan error handling
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    initializeCharts();
                    console.log('Charts initialized successfully');
                } catch (error) {
                    console.error('Error initializing charts:', error);
                    showError('Gagal memuat beberapa chart. Data mungkin tidak tersedia.');
                }
            });
    </script>
@endpush
@endsection