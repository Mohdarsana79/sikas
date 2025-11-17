<div class="container-fluid my-5 p-0 grafik-proporsi-container">
    <h4 class="h4 fw-bold text-center mb-2 p-4 bg-light rounded-top-3 text-primary">Analisis Proporsi Anggaran Perubahan
    </h4>

    <!-- BARIS PERTAMA: Grafik 1 dan Grafik 2 -->
    <div class="row g-4 p-4 p-md-5">
        <!-- KOLOM GRAFIK 1: Proporsi Anggaran BUKU -->
        <div class="col-12 col-lg-6 col-md-6">
            <div class="card h-100 shadow-lg border-0">
                <div class="card-body d-flex flex-column p-4">
                    <h5 class="card-title-chart fw-bold text-center mb-2">Proporsi Anggaran BUKU</h5>
                    <div class="d-flex flex-column align-items-center flex-grow-1 justify-content-center">
                        <div id="chart1" class="apexchart-container"></div>
                    </div>

                    <!-- CARD DESKRIPSI UNTUK GRAFIK 1 -->
                    <div class="card mt-2 shadow-sm border-0" id="infoCard1">
                        <div class="card-header bg-success text-white fw-bold py-3">
                            INFORMASI ALOKASI BUKU
                        </div>
                        <div class="card-body bg-success-subtle text-success-emphasis py-3">
                            <div class="d-flex align-items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1" viewBox="0 0 16 16">
                                    <path
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.497 5.354 7.374a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5-5a.75.75 0 0 0-.022-1.08z" />
                                </svg>
                                <div>
                                    <p class="fw-semibold mb-2">Alokasi belanja Anda sudah sesuai juknis</p>
                                    <p class="small mb-0">Anggaran penyediaan buku Anda adalah <span
                                            id="bukuPercentage">0%</span> dan sudah sesuai dengan proporsi minimal 10%
                                        dari total pagu anggaran.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM GRAFIK 2: Proporsi Anggaran Honor -->
        <div class="col-12 col-lg-6 col-md-6">
            <div class="card h-100 shadow-lg border-0">
                <div class="card-body d-flex flex-column p-4">
                    <h5 class="card-title-chart fw-bold text-center mb-2">Proporsi Anggaran Honor dari Total Pagu</h5>
                    <div class="d-flex flex-column align-items-center flex-grow-1 justify-content-center">
                        <div id="chart2" class="apexchart-container"></div>
                    </div>

                    <!-- CARD DESKRIPSI UNTUK GRAFIK 2 -->
                    <div class="card mt-2 shadow-sm border-0" id="infoCard2">
                        <div class="card-header bg-success text-white fw-bold py-3">
                            ALOKASI ANGGARAN HONOR
                        </div>
                        <div class="card-body bg-success-subtle text-success-emphasis py-3">
                            <div class="d-flex align-items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1" viewBox="0 0 16 16">
                                    <path
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.497 5.354 7.374a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5-5a.75.75 0 0 0-.022-1.08z" />
                                </svg>
                                <div>
                                    <p class="fw-semibold mb-2">Alokasi anggaran Anda sudah sesuai juknis</p>
                                    <p class="small mb-0">Anggaran honor Anda adalah <span
                                            id="honorPercentage">0%</span> dari total pagu anggaran. Anggaran sudah
                                        sesuai dengan proporsi maksimal <span id="maxHonorPercentage">0%</span> untuk
                                        sekolah.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS KEDUA: Grafik 3 dan Grafik 4 -->
    <div class="row g-4 p-4 p-md-5 pt-0 pt-lg-5">
        <!-- KOLOM GRAFIK 3: Proporsi Anggaran Pemeliharaan Sarpras -->
        <div class="col-12 col-lg-6 col-md-6">
            <div class="card h-100 shadow-lg border-0">
                <div class="card-body d-flex flex-column p-4">
                    <h5 class="card-title-chart fw-bold text-center mb-2">Proporsi Anggaran Pemeliharaan Sarpras</h5>
                    <div class="d-flex flex-column align-items-center flex-grow-1 justify-content-center">
                        <div id="chart3" class="apexchart-container"></div>
                    </div>

                    <!-- CARD DESKRIPSI UNTUK GRAFIK 3 -->
                    <div class="card mt-2 shadow-sm border-0" id="infoCard3">
                        <div class="card-header bg-success text-white fw-bold py-3">
                            INFORMASI ALOKASI SARPRAS
                        </div>
                        <div class="card-body bg-success-subtle text-success-emphasis py-3">
                            <div class="d-flex align-items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1" viewBox="0 0 16 16">
                                    <path
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.497 5.354 7.374a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5-5a.75.75 0 0 0-.022-1.08z" />
                                </svg>
                                <div>
                                    <p class="fw-semibold mb-2">Alokasi anggaran Anda sudah sesuai juknis</p>
                                    <p class="small mb-0">Anggaran pemeliharaan sarpras Anda adalah <span
                                            id="sarprasPercentage">0%</span> dan sudah sesuai dengan proporsi maksimal
                                        20% dari total pagu anggaran.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM GRAFIK 4: Proporsi Antar Jenis Belanja Lainnya -->
        <div class="col-12 col-lg-6 col-md-6">
            <div class="card h-100 shadow-lg border-0">
                <div class="card-body d-flex flex-column p-4">
                    <h5 class="card-title-chart fw-bold text-center mb-2">Proporsi Antar Jenis Belanja Lainnya dari Total Pagu</h5>
                    <div class="d-flex flex-column align-items-center flex-grow-1 justify-content-center">
                        <div id="chart4" class="apexchart-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .grafik-proporsi-container {
        background-color: #f8f9fa !important;
        min-height: 100vh;
    }

    .grafik-proporsi-container .card {
        min-height: 650px !important;
        border-radius: 20px !important;
        overflow: hidden;
    }

    .grafik-proporsi-container .card-body {
        padding: 2rem !important;
    }

    /* Container ApexCharts yang besar */
    .apexchart-container {
        width: 100%;
        height: 350px;
        margin: 0 auto;
    }

    .card-title-chart {
        font-size: 1.4rem;
        margin-bottom: 1.5rem;
        color: #2c3e50;
    }

    /* Style untuk card info */
    .card-warning {
        border-color: #ffc107 !important;
    }

    .card-header-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }

    .card-body-warning {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }

    .card-danger {
        border-color: #dc3545 !important;
    }

    .card-header-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .card-body-danger {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }

    /* Responsive adjustments */
    @media (min-width: 1200px) {
        .apexchart-container {
            height: 380px;
        }
    }

    @media (max-width: 1199px) and (min-width: 992px) {
        .apexchart-container {
            height: 350px;
        }
    }

    @media (max-width: 991px) and (min-width: 768px) {
        .apexchart-container {
            height: 320px;
        }
    }

    @media (max-width: 767px) {
        .apexchart-container {
            height: 300px;
        }

        .grafik-proporsi-container .card {
            min-height: 600px !important;
        }
    }

    @media (max-width: 576px) {
        .apexchart-container {
            height: 280px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 [APEXCHARTS] DOM Content Loaded - Initializing ApexCharts');
        
        // Cek jika ApexCharts sudah terload
        if (typeof ApexCharts === 'undefined') {
            console.error('❌ [APEXCHARTS] ApexCharts is not loaded!');
            // Tampilkan pesan error
            const chartContainers = document.querySelectorAll('.apexchart-container');
            chartContainers.forEach(container => {
                container.innerHTML = '<div class="alert alert-danger text-center">ApexCharts tidak terload. Pastikan file asset tersedia.</div>';
            });
            return;
        }

        console.log('✅ [APEXCHARTS] ApexCharts loaded successfully');

        // Data dari controller
        const grafikData = @json($grafikData ?? []);
        const sekolahStatus = @json($sekolah->status_sekolah ?? 'Negeri');
        const totalPagu = @json($penganggaran->pagu_anggaran ?? 0);

        console.log('📊 [APEXCHARTS] Grafik data received:', grafikData);

        // Fungsi untuk menghitung persentase
        function calculatePercentage(nilai, total) {
            return total > 0 ? (nilai / total) * 100 : 0;
        }

        // Fungsi untuk memformat persentase
        function formatPercentage(num) {
            return num.toFixed(2) + '%';
        }

        // Fungsi untuk update card info
        function updateCardInfo(cardId, isWarning, title, message) {
            const card = document.getElementById(cardId);
            if (!card) {
                console.error('❌ [CARD] Card not found:', cardId);
                return;
            }

            const header = card.querySelector('.card-header');
            const body = card.querySelector('.card-body');
            const svg = card.querySelector('svg');
            const titleEl = card.querySelector('.fw-semibold');
            const messageEl = card.querySelector('.small');

            // Reset semua kelas
            header.className = 'card-header fw-bold py-3';
            body.className = 'card-body py-3';

            if (isWarning) {
                // PERBAIKAN: Gunakan class Bootstrap untuk warning (kuning)
                header.classList.add('bg-warning', 'text-dark');
                body.classList.add('bg-warning-subtle', 'text-warning-emphasis');
                if (svg) {
                    svg.className = 'bi bi-exclamation-triangle-fill text-warning flex-shrink-0 mt-1';
                    svg.innerHTML = '<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>';
                }
            } else {
                header.classList.add('bg-success', 'text-white');
                body.classList.add('bg-success-subtle', 'text-success-emphasis');
                if (svg) {
                    svg.className = 'bi bi-check-circle-fill text-success flex-shrink-0 mt-1';
                    svg.innerHTML = '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.497 5.354 7.374a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5-5a.75.75 0 0 0-.022-1.08z"/>';
                }
            }

            if (titleEl) titleEl.textContent = title;
            if (messageEl) messageEl.textContent = message;

            console.log('✅ [CARD] Updated card:', cardId, { 
                status: isWarning ? 'WARNING' : 'SUCCESS',
                title: title 
            });
        }

        // 1. Grafik Proporsi Anggaran BUKU
        function initChart1() {
            console.log('📈 [APEXCHARTS] Initializing Chart 1 - Buku');
            
            try {
                const bukuAnggaran = grafikData.buku_anggaran || 0;
                const bukuPercentage = calculatePercentage(bukuAnggaran, totalPagu);
                const lainnyaPercentage = Math.max(0, 100 - bukuPercentage);

                const series = [bukuPercentage, lainnyaPercentage];
                const labels = ['Penyediaan Buku', 'Komponen Anggaran Lainnya'];
                const colors = ['#4DB6AC', '#fcd056'];

                // Update card info
                const bukuPercentageElement = document.getElementById('bukuPercentage');
                if (bukuPercentageElement) {
                    bukuPercentageElement.textContent = formatPercentage(bukuPercentage);
                }

                // PERBAIKAN: Logika pengecekan kondisi buku
                let isWarning = false;
                let title = '';
                let message = '';

                console.log('📚 [BUKU_DEBUG] Current buku:', formatPercentage(bukuPercentage), 'Minimal: 10%');

                if (bukuPercentage < 10) {
                    isWarning = true;
                    title = 'Perhatian: Alokasi belanja buku belum memenuhi syarat';
                    message = `Anggaran penyediaan buku Anda adalah ${formatPercentage(bukuPercentage)} dan belum memenuhi proporsi minimal 10% dari total pagu anggaran.`;
                } else {
                    isWarning = false;
                    title = 'Alokasi belanja Anda sudah sesuai juknis';
                    message = `Anggaran penyediaan buku Anda adalah ${formatPercentage(bukuPercentage)} dan sudah sesuai dengan proporsi minimal 10% dari total pagu anggaran.`;
                }

                // Panggil fungsi updateCardInfo
                updateCardInfo('infoCard1', isWarning, title, message);

                const options = {
                    series: series,
                    chart: {
                        type: 'donut',
                        height: 350,
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    colors: colors,
                    labels: labels,
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '12px',
                                        fontWeight: 'bold'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '16px',
                                        fontWeight: 'bold',
                                        formatter: function (val) {
                                            return val + '%'
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(1) + '%'
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val, opts) {
                            return opts.w.config.series[opts.seriesIndex].toFixed(1) + '%'
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        fontWeight: 'bold'
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toFixed(2) + '%'
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 300
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                const chart = new ApexCharts(document.querySelector("#chart1"), options);
                chart.render();
                
                console.log('✅ [APEXCHARTS] Chart 1 initialized successfully');
            } catch (error) {
                console.error('❌ [APEXCHARTS] Error initializing chart 1:', error);
            }
        }

        // 2. Grafik Proporsi Anggaran Honor
        function initChart2() {
            console.log('📈 [APEXCHARTS] Initializing Chart 2 - Honor');
            
            try {
                const honorAnggaran = grafikData.honor_anggaran || 0;
                const maxHonorPercentage = sekolahStatus === 'Negeri' ? 20 : 40;
                const honorPercentage = calculatePercentage(honorAnggaran, totalPagu);
                const lainnyaPercentage = Math.max(0, 100 - honorPercentage);

                const series = [honorPercentage, lainnyaPercentage];
                const labels = ['Pembayaran Honor', 'Komponen Anggaran Lainnya'];
                const colors = ['#F48FB1', '#6beefa'];

                // Update card info
                const honorPercentageElement = document.getElementById('honorPercentage');
                const maxHonorPercentageElement = document.getElementById('maxHonorPercentage');
                
                if (honorPercentageElement) {
                    honorPercentageElement.textContent = formatPercentage(honorPercentage);
                }
                if (maxHonorPercentageElement) {
                    maxHonorPercentageElement.textContent = maxHonorPercentage + '%';
                }

                // PERBAIKAN: Logika pengecekan kondisi honor berdasarkan status sekolah
                let isWarning = false;
                let title = '';
                let message = '';

                console.log('🏫 [HONOR_DEBUG] Status sekolah:', sekolahStatus, 'Max honor:', maxHonorPercentage + '%', 'Current honor:', formatPercentage(honorPercentage));

                if (honorPercentage > maxHonorPercentage) {
                    isWarning = true;
                    title = 'Perhatian: Alokasi anggaran honor melebihi batas';
                    message = `Anggaran honor Anda adalah ${formatPercentage(honorPercentage)} dari total pagu anggaran dan melebihi proporsi maksimal ${maxHonorPercentage}% untuk sekolah ${sekolahStatus}.`;
                } else {
                    isWarning = false;
                    title = 'Alokasi anggaran Anda sudah sesuai juknis';
                    message = `Anggaran honor Anda adalah ${formatPercentage(honorPercentage)} dari total pagu anggaran. Anggaran sudah sesuai dengan proporsi maksimal ${maxHonorPercentage}% untuk sekolah ${sekolahStatus}.`;
                }

                // PERBAIKAN: Panggil fungsi updateCardInfo tanpa parameter isDanger
                updateCardInfo('infoCard2', isWarning, title, message);

                const options = {
                    series: series,
                    chart: {
                        type: 'donut',
                        height: 350,
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    colors: colors,
                    labels: labels,
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '12px',
                                        fontWeight: 'bold'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        formatter: function (val) {
                                            return val + '%'
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(1) + '%'
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val, opts) {
                            return opts.w.config.series[opts.seriesIndex].toFixed(1) + '%'
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        fontWeight: 'bold'
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toFixed(2) + '%'
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart2"), options);
                chart.render();
                
                console.log('✅ [APEXCHARTS] Chart 2 initialized successfully');
            } catch (error) {
                console.error('❌ [APEXCHARTS] Error initializing chart 2:', error);
            }
        }

        // 3. Grafik Proporsi Anggaran Pemeliharaan Sarpras
        function initChart3() {
            console.log('📈 [APEXCHARTS] Initializing Chart 3 - Sarpras');
            
            try {
                const sarprasAnggaran = grafikData.sarpras_anggaran || 0;
                const sarprasPercentage = calculatePercentage(sarprasAnggaran, totalPagu);
                const lainnyaPercentage = Math.max(0, 100 - sarprasPercentage);

                const series = [sarprasPercentage, lainnyaPercentage];
                const labels = ['Pemeliharaan Sarpras', 'Komponen Anggaran Lainnya'];
                const colors = ['#7986CB', '#bdf797'];

                // Update card info
                const sarprasPercentageElement = document.getElementById('sarprasPercentage');
                if (sarprasPercentageElement) {
                    sarprasPercentageElement.textContent = formatPercentage(sarprasPercentage);
                }

                // PERBAIKAN: Logika pengecekan kondisi sarpras
                let isWarning = false;
                let title = '';
                let message = '';

                console.log('🏫 [SARPRAS_DEBUG] Current sarpras:', formatPercentage(sarprasPercentage), 'Maksimal: 20%');

                if (sarprasPercentage > 20) {
                    isWarning = true;
                    title = 'Perhatian: Alokasi anggaran sarpras melebihi batas';
                    message = `Anggaran pemeliharaan sarpras Anda adalah ${formatPercentage(sarprasPercentage)} dan melebihi proporsi maksimal 20% dari total pagu anggaran.`;
                } else {
                    isWarning = false;
                    title = 'Alokasi anggaran Anda sudah sesuai juknis';
                    message = `Anggaran pemeliharaan sarpras Anda adalah ${formatPercentage(sarprasPercentage)} dan sudah sesuai dengan proporsi maksimal 20% dari total pagu anggaran.`;
                }

                // Panggil fungsi updateCardInfo
                updateCardInfo('infoCard3', isWarning, title, message);

                const options = {
                    series: series,
                    chart: {
                        type: 'donut',
                        height: 350,
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    colors: colors,
                    labels: labels,
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '12px',
                                        fontWeight: 'bold'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        formatter: function (val) {
                                            return val + '%'
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(1) + '%'
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val, opts) {
                            return opts.w.config.series[opts.seriesIndex].toFixed(1) + '%'
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        fontWeight: 'bold'
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toFixed(2) + '%'
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart3"), options);
                chart.render();
                
                console.log('✅ [APEXCHARTS] Chart 3 initialized successfully');
            } catch (error) {
                console.error('❌ [APEXCHARTS] Error initializing chart 3:', error);
            }
        }

        // 4. Grafik Proporsi Antar Jenis Belanja Lainnya
        function initChart4() {
            console.log('📈 [APEXCHARTS] Initializing Chart 4 - Jenis Belanja');
            
            try {
                const jenisBelanjaData = grafikData.jenis_belanja || [];
                
                // Jika tidak ada data, gunakan data default
                const data = jenisBelanjaData.length > 0 
                    ? jenisBelanjaData.map(item => ({
                        value: item.value,
                        label: item.label,
                        color: item.color || getRandomColor()
                    }))
                    : [
                        { value: 25, label: 'Sarana Prasarana', color: '#4DB6AC' },
                        { value: 20, label: 'Pengembangan Guru', color: '#F48FB1' },
                        { value: 15, label: 'Proses Pembelajaran', color: '#7986CB' },
                        { value: 10, label: 'Pembiayaan', color: '#FFB74D' },
                        { value: 30, label: 'Lainnya', color: '#E0E0E0' }
                    ];

                const series = data.map(item => item.value);
                const labels = data.map(item => item.label);
                const colors = data.map(item => item.color);

                const options = {
                    series: series,
                    chart: {
                        type: 'pie',
                        height: 400,
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    colors: colors,
                    labels: labels,
                    plotOptions: {
                        pie: {
                            size: '70%',
                            donut: {
                                size: '50%'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val, opts) {
                            return opts.w.config.series[opts.seriesIndex].toFixed(1) + '%'
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        fontWeight: 'bold'
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toFixed(2) + '%'
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 350
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                const chart = new ApexCharts(document.querySelector("#chart4"), options);
                chart.render();
                
                console.log('✅ [APEXCHARTS] Chart 4 initialized successfully');
            } catch (error) {
                console.error('❌ [APEXCHARTS] Error initializing chart 4:', error);
            }
        }

        // Fungsi untuk generate random color
        function getRandomColor() {
            const colors = [
                "#4DB6AC", "#F48FB1", "#7986CB", "#FFB74D", 
                "#9575CD", "#4FC3F7", "#BA68C8", "#81D4FA",
                "#4DD0E1", "#7986CB", "#AED581", "#FFD54F"
            ];
            return colors[Math.floor(Math.random() * colors.length)];
        }

        // Initialize all charts
        function initializeAllCharts() {
            console.log('🎯 [APEXCHARTS] Starting initialization of all charts');
            
            try {
                initChart1();
                initChart2();
                initChart3();
                initChart4();
                
                console.log('✅ [APEXCHARTS] All charts initialized successfully');
            } catch (error) {
                console.error('❌ [APEXCHARTS] Error during chart initialization:', error);
            }
        }

        // Start initialization
        initializeAllCharts();

        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                console.log('🔄 [APEXCHARTS] Window resized');
                // ApexCharts automatically handles resize
            }, 300);
        });

    });
</script>
@endpush