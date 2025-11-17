// public/js/dashboard.js

class DashboardCharts {
    constructor() {
        this.monthlyRealizationChart = null;
        this.programDistributionChart = null;
        this.realizationProgressChart = null;
        this.budgetUtilizationChart = null;
        this.fiveYearComparisonChart = null;
        
        this.init();
    }

    init() {
        this.initializeEventListeners();
        this.initializeCharts();
    }

    // Format number helper
    formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Format currency helper - 31320000 -> Rp 31,320
    formatCurrencyIDR(amount) {
        const amountInThousands = Math.round(amount / 1000);
        return 'Rp ' + this.formatNumber(amountInThousands);
    }

    // Format untuk chart tooltip
    formatCurrencyChart(amount) {
        return 'Rp ' + this.formatNumber(Math.round(amount));
    }

    initializeEventListeners() {
        // Event listener untuk select year
        const selectYear = document.getElementById('selectYear');
        if (selectYear) {
            selectYear.addEventListener('change', (e) => {
                const year = e.target.value;
                console.log('Tahun dipilih:', year);
                this.updateDashboardYear(year);
            });
        }
    }

    // Fungsi untuk menghancurkan charts sebelum membuat yang baru
    destroyCharts() {
        try {
            if (this.monthlyRealizationChart) {
                this.monthlyRealizationChart.destroy();
                this.monthlyRealizationChart = null;
            }
            if (this.programDistributionChart) {
                this.programDistributionChart.destroy();
                this.programDistributionChart = null;
            }
            if (this.realizationProgressChart) {
                this.realizationProgressChart.destroy();
                this.realizationProgressChart = null;
            }
            if (this.budgetUtilizationChart) {
                this.budgetUtilizationChart.destroy();
                this.budgetUtilizationChart = null;
            }
            if (this.fiveYearComparisonChart) {
                this.fiveYearComparisonChart.destroy();
                this.fiveYearComparisonChart = null;
            }
        } catch (error) {
            console.error('Error destroying charts:', error);
        }
    }

    // Initialize Charts dengan data dari Laravel
    initializeCharts() {
        try {
            console.log('Initializing charts....');
            
            // Data dari PHP (tersedia di global window object)
            const monthlyData = window.dashboardData?.grafikRealisasiTahunan || {};
            const programData = window.dashboardData?.chartRealisasiProgram || [];
            const pemanfaatanData = window.dashboardData?.pemanfaatanAnggaran || [];
            const perbandinganData = window.dashboardData?.perbandinganLimaTahun || [];
            const statistik = window.dashboardData?.statistik || {};

            // CHART 1: Realisasi Anggaran Per Bulan
            if (monthlyData.realisasi_data && monthlyData.realisasi_data.length > 0) {
                this.renderMonthlyRealizationChart(monthlyData);
            }

            // CHART 2: Distribusi Realisasi Program
            if (programData && programData.length > 0) {
                this.renderProgramDistributionChart(programData);
            }

            // CHART 3: Progress Realisasi Anggaran
            if (statistik.persentase_realisasi > 0) {
                this.renderRealizationProgressChart(statistik.persentase_realisasi);
            }

            // CHART 4: Pemanfaatan Anggaran 8 SNP
            if (pemanfaatanData && pemanfaatanData.length > 0) {
                this.renderBudgetUtilizationChart(pemanfaatanData);
            }

            // CHART 5: Perbandingan 5 Tahun Terakhir
            if (perbandinganData && perbandinganData.length > 0) {
                this.renderFiveYearComparisonChart(perbandinganData);
            }

            console.log('Charts initialized successfully');
        } catch(error) {
            console.error('Error initializing charts:', error);
            this.showFallbackMessages();
        }
    }

    renderMonthlyRealizationChart(monthlyData) {
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
                    formatter: (value) => {
                        return this.formatCurrencyChart(value);
                    }
                }
            },
            colors: ['#007bff'],
            tooltip: {
                y: {
                    formatter: (value) => {
                        return this.formatCurrencyChart(value);
                    }
                }
            }
        };

        this.monthlyRealizationChart = new ApexCharts(document.querySelector("#monthlyRealizationChart"), monthlyRealizationOptions);
        this.monthlyRealizationChart.render();
    }

    renderProgramDistributionChart(programData) {
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
                                formatter: (w) => {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return this.formatCurrencyIDR(total);
                                }
                            }
                        }
                    }
                }
            },
            colors: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#6610f2', '#e83e8c'],
            tooltip: {
                y: {
                    formatter: (value) => {
                        return this.formatCurrencyIDR(value);
                    }
                }
            }
        };

        this.programDistributionChart = new ApexCharts(document.querySelector("#programDistributionChart"), programDistributionOptions);
        this.programDistributionChart.render();
    }

    renderRealizationProgressChart(persentaseRealisasi) {
        const progressValue = parseFloat(persentaseRealisasi) || 0;
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
                            formatter: (val) => { return parseInt(val) + '%'; },
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

        this.realizationProgressChart = new ApexCharts(document.querySelector("#realizationProgress"), realizationProgressOptions);
        this.realizationProgressChart.render();
    }

    renderBudgetUtilizationChart(pemanfaatanData) {
        const seriesData = pemanfaatanData.map(item => item.persentase);
        const labels = pemanfaatanData.map(item => item.program);
        
        const programColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', 
            '#e74a3b', '#858796', '#6f42c1', '#fd7e14'
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
                formatter: (val, { seriesIndex, w }) => {
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
                                formatter: (val) => {
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
                                formatter: (w) => {
                                    const totalRealisasi = pemanfaatanData.reduce((sum, item) => sum + item.realisasi, 0);
                                    return 'Rp ' + this.formatNumber(Math.round(totalRealisasi / 1000));
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
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                floating: false,
                fontSize: '12px',
                fontFamily: 'Inter, sans-serif',
                fontWeight: 400,
                formatter: (seriesName, opts) => {
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
                    formatter: (val, { series, seriesIndex, w }) => {
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
                            </div>
                        </div>
                        `;
                    }
                }
            }
        };

        this.budgetUtilizationChart = new ApexCharts(document.querySelector("#budgetUtilization"), budgetUtilizationOptions);
        this.budgetUtilizationChart.render();
    }

    renderFiveYearComparisonChart(perbandinganData) {
        const tahunCategories = perbandinganData.map(item => item.tahun.toString());
        const persentaseData = perbandinganData.map(item => item.realisasi || 0);
        const realisasiData = perbandinganData.map(item => item.realisasi_rupiah || 0);

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
                toolbar: { show: true }
            },
            title: {
                text: `Perbandingan Realisasi ${tahunCategories[0]} - ${tahunCategories[tahunCategories.length - 1]}`,
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
                formatter: (val) => {
                    return typeof val === 'number' ? val.toFixed(1) + "%" : "0%";
                }
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
                        formatter: (val) => {
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
                        formatter: (val) => {
                            return 'Rp ' + this.formatNumber(Math.round((val || 0) / 1000));
                        }
                    }
                }
            ],
            colors: ['#4e73df', '#e74a3b'],
            fill: {
                opacity: [0.8, 1]
            },
            tooltip: {
                shared: true,
                intersect: false,
                custom: ({ series, seriesIndex, dataPointIndex, w }) => {
                    const data = perbandinganData[dataPointIndex];
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
                        </div>
                    </div>
                    `;
                }
            }
        };

        this.fiveYearComparisonChart = new ApexCharts(document.querySelector("#fiveYearComparisonChart"), fiveYearComparisonOptions);
        this.fiveYearComparisonChart.render();
    }

    // Fungsi untuk update dashboard berdasarkan tahun
    async updateDashboardYear(year) {
        try {
            // Show loading state
            document.getElementById('dashboardContent').classList.add('loading');
            
            console.log('Mengambil data untuk tahun:', year);

            const response = await fetch(`/dashboard/api/dashboard-new-data?tahun=${year}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Data diterima untuk tahun', year, ':', data);

            if (data.success) {
                this.updateDashboardContent(data, year);
                this.updateCharts(data);
                console.log('Dashboard berhasil diupdate untuk tahun', year);
            } else {
                console.error('Failed to load dashboard data:', data.message);
                this.showError('Gagal memuat data dashboard: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating dashboard:', error);
            this.showError('Terjadi kesalahan saat memuat data dashboard: ' + error.message);
        } finally {
            document.getElementById('dashboardContent').classList.remove('loading');
        }
    }

    updateDashboardContent(data, year) {
        // Update dashboard title
        document.getElementById('dashboardTitle').textContent = `Dashboard Kinerja Anggaran ${year}`;
        document.getElementById('monthlyChartYear').textContent = year;

        // Update metric cards
        document.getElementById('paguAnggaran').textContent = `Rp ${data.statistik.pagu_anggaran_display}`;
        document.getElementById('totalRealisasi').textContent = `Rp ${data.statistik.total_realisasi_display}`;
        document.getElementById('realizationPercentage').textContent = `${data.statistik.persentase_realisasi.toFixed(1)}%`;
        document.getElementById('sisaAnggaran').textContent = `Rp ${data.statistik.sisa_anggaran_display}`;
        document.getElementById('totalPenerimaan').textContent = `Rp ${data.statistik.total_penerimaan_display}`;
        document.getElementById('totalBelanja').textContent = `Rp ${data.statistik.total_belanja_display}`;
        document.getElementById('surplusPenerimaan').textContent = `Rp ${data.statistik.surplus_penerimaan_display}`;
        document.getElementById('efisiensiAnggaran').textContent = `${data.statistik.efisiensi_anggaran.toFixed(1)}%`;

        // Update surplus/defisit card styling
        this.updateSurplusCardStyle(data.statistik.surplus_penerimaan);
    }

    updateSurplusCardStyle(surplusPenerimaan) {
        const surplusCard = document.querySelector('.col-xl-3:nth-child(7) .card-custom');
        if (surplusCard) {
            if (surplusPenerimaan >= 0) {
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
    }

    // Fungsi untuk update semua charts dengan data baru
    updateCharts(data) {
        console.log('Updating charts dengan data tahun baru:', data);
        
        try {
            // Hancurkan charts lama terlebih dahulu
            this.destroyCharts();

            // Render charts baru dengan data baru
            if (data.grafik_realisasi_tahunan && data.grafik_realisasi_tahunan.realisasi_data && data.grafik_realisasi_tahunan.realisasi_data.length > 0) {
                this.renderMonthlyRealizationChart(data.grafik_realisasi_tahunan);
            } else {
                document.querySelector("#monthlyRealizationChart").innerHTML = this.getErrorMessage('Tidak ada data realisasi bulanan', 'bar-chart');
            }

            if (data.chart_realisasi_program && data.chart_realisasi_program.length > 0) {
                this.renderProgramDistributionChart(data.chart_realisasi_program);
            } else {
                document.querySelector("#programDistributionChart").innerHTML = this.getErrorMessage('Tidak ada data distribusi program', 'pie-chart');
            }

            if (data.statistik && data.statistik.persentase_realisasi > 0) {
                this.renderRealizationProgressChart(data.statistik.persentase_realisasi);
            } else {
                document.querySelector("#realizationProgress").innerHTML = this.getErrorMessage('Tidak ada data progress realisasi', 'speedometer2');
            }

            if (data.pemanfaatan_anggaran && data.pemanfaatan_anggaran.length > 0) {
                this.renderBudgetUtilizationChart(data.pemanfaatan_anggaran);
            } else {
                document.querySelector("#budgetUtilization").innerHTML = this.getErrorMessage('Tidak ada data pemanfaatan anggaran', 'pie-chart');
            }

            if (data.perbandingan_lima_tahun && data.perbandingan_lima_tahun.length > 0) {
                this.renderFiveYearComparisonChart(data.perbandingan_lima_tahun);
            } else {
                document.querySelector("#fiveYearComparisonChart").innerHTML = this.getErrorMessage('Tidak ada data perbandingan untuk 5 tahun terakhir', 'graph-up');
            }

            console.log('Semua charts berhasil diupdate dengan data tahun baru');
        } catch (error) {
            console.error('Error in updateCharts:', error);
            this.showError('Gagal memperbarui beberapa chart dengan data tahun baru');
        }
    }

    getErrorMessage(message, icon) {
        return `
            <div class="error-state">
                <i class="bi bi-${icon}"></i>
                <p>${message}</p>
            </div>
        `;
    }

    showFallbackMessages() {
        const containers = [
            '#monthlyRealizationChart', 
            '#programDistributionChart', 
            '#realizationProgress', 
            '#budgetUtilization', 
            '#fiveYearComparisonChart'
        ];
        
        containers.forEach(container => {
            const element = document.querySelector(container);
            if (element && element.innerHTML === '') {
                element.innerHTML = `<div class="error-state"><p>Gagal memuat chart</p></div>`;
            }
        });
    }

    showError(message) {
        let errorDiv = document.getElementById('dashboardError');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'dashboardError';
            errorDiv.className = 'alert alert-danger alert-dismissible fade show';
            document.getElementById('dashboardContent').prepend(errorDiv);
        }
        
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        errorDiv.style.display = 'block';
    }
}

// Initialize dashboard ketika DOM siap
document.addEventListener('DOMContentLoaded', function() {
    window.dashboard = new DashboardCharts();
});