@extends('layouts.app')
@include('layouts.navbar')
@include('layouts.sidebar')
@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-semibold text-dark mb-1">
                        <i class="bi bi-search me-2"></i>Audit Data BKU
                    </h1>
                    <div class="d-flex align-items-center small text-muted">
                        <span>Tahun: {{ $tahun }}</span>
                        <span class="mx-2">|</span>
                        <span>Bulan: {{ $bulan }}</span>
                        <span class="mx-2">|</span>
                        <span>Penganggaran: {{ $penganggaran->nama }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('bku.showByBulan', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke BKU
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-play-circle display-6 mb-3"></i>
                    <h5>Jalankan Audit</h5>
                    <p class="mb-3">Periksa konsistensi data BKU</p>
                    <button class="btn btn-light btn-sm" id="btnRunAudit">
                        <i class="bi bi-play me-1"></i>Jalankan
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="bi bi-tools display-6 mb-3"></i>
                    <h5>Perbaiki Data</h5>
                    <p class="mb-3">Perbaiki masalah data otomatis</p>
                    <button class="btn btn-dark btn-sm" id="btnFixData">
                        <i class="bi bi-wrench me-1"></i>Perbaiki
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-file-pdf display-6 mb-3"></i>
                    <h5>Export Laporan</h5>
                    <p class="mb-3">Download laporan audit lengkap</p>
                    <button class="btn btn-light btn-sm" id="btnExportReport">
                        <i class="bi bi-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Hasil Audit</h5>
        </div>
        <div class="card-body">
            <div id="auditResults">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-graph-up display-1"></i>
                    <p class="mt-3">Belum ada data audit. Klik "Jalankan Audit" untuk memulai.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="mb-0" id="loadingText">Memproses...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .audit-summary-card {
        transition: transform 0.2s;
    }

    .audit-summary-card:hover {
        transform: translateY(-2px);
    }

    .problem-item {
        border-left: 4px solid #dc3545;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .healthy-item {
        border-left: 4px solid #198754;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .data-table {
        font-size: 0.875rem;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function formatRupiah(angka) {
    if (!angka) return '0';
    return new Intl.NumberFormat('id-ID').format(angka);
}

function formatTanggal(tanggal) {
    if (!tanggal) return '-';
    return new Date(tanggal).toLocaleDateString('id-ID');
}

function showLoading(message = 'Memproses...') {
    $('#loadingText').text(message);
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

function showAlert(type, title, message) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';

    const icon = {
        'success': 'bi-check-circle',
        'error': 'bi-exclamation-triangle',
        'warning': 'bi-exclamation-circle',
        'info': 'bi-info-circle'
    }[type] || 'bi-info-circle';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show">
            <i class="bi ${icon} me-2"></i>
            <strong>${title}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#auditResults').prepend(alertHtml);
}

$(document).ready(function() {
    const penganggaranId = {{ $penganggaran->id }};
    const tahun = '{{ $tahun }}';
    const bulan = '{{ $bulan }}';

    // Jalankan Audit
    $('#btnRunAudit').click(function() {
        runAudit();
    });

    // Perbaiki Data
    $('#btnFixData').click(function() {
        if (!confirm('Apakah Anda yakin ingin memperbaiki data secara otomatis? Tindakan ini tidak dapat dibatalkan.')) {
            return;
        }
        fixData();
    });

    // Export Laporan
    $('#btnExportReport').click(function() {
        exportReport();
    });

    function runAudit() {
        showLoading('Sedang melakukan audit data...');
        
        $('#auditResults').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Sedang menganalisis data BKU...</p>
            </div>
        `);

        $.ajax({
            url: '/bku-audit/data/' + penganggaranId,
            method: 'GET',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    renderAuditResults(response.audit_data);
                    showAlert('success', 'Berhasil!', 'Audit data telah selesai.');
                } else {
                    showAlert('error', 'Error!', response.message);
                    $('#auditResults').html(`
                        <div class="alert alert-danger">
                            <h5>Terjadi Kesalahan</h5>
                            <p>${response.message}</p>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                hideLoading();
                showAlert('error', 'Error!', 'Terjadi kesalahan saat melakukan audit.');
                $('#auditResults').html(`
                    <div class="alert alert-danger">
                        <h5>Error</h5>
                        <p>Terjadi kesalahan saat terhubung ke server.</p>
                        <small>${xhr.responseJSON?.message || 'Unknown error'}</small>
                    </div>
                `);
            }
        });
    }

    function fixData() {
        showLoading('Sedang memperbaiki data...');

        $.ajax({
            url: '/bku-audit/fix/' + penganggaranId,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', 'Berhasil!', 'Data telah diperbaiki.');
                    // Jalankan audit lagi untuk melihat hasil perbaikan
                    runAudit();
                } else {
                    showAlert('error', 'Error!', response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showAlert('error', 'Error!', 'Terjadi kesalahan saat memperbaiki data.');
            }
        });
    }

    function exportReport() {
        showLoading('Menyiapkan laporan...');
        
        // Simulasikan proses export
        setTimeout(() => {
            hideLoading();
            showAlert('info', 'Info', 'Fitur export akan segera tersedia.');
        }, 2000);
    }

    function renderAuditResults(auditData) {
        let html = '';

        if (auditData.error) {
            html = `<div class="alert alert-danger">${auditData.error}</div>`;
        } else {
            // Summary Cards
            const selisih = auditData.saldo?.selisih || 0;
            const isHealthy = Math.abs(selisih) <= 1000;
            
            html += `
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card audit-summary-card ${isHealthy ? 'border-success' : 'border-danger'}">
                            <div class="card-body text-center">
                                <h3 class="${isHealthy ? 'text-success' : 'text-danger'}">Rp ${formatRupiah(selisih)}</h3>
                                <p class="mb-0">Selisih</p>
                                <small class="${isHealthy ? 'text-success' : 'text-danger'}">
                                    <i class="bi ${isHealthy ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-1"></i>
                                    ${isHealthy ? 'Sehat' : 'Bermasalah'}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card audit-summary-card border-primary">
                            <div class="card-body text-center">
                                <h3 class="text-primary">Rp ${formatRupiah(auditData.penerimaan?.total || 0)}</h3>
                                <p class="mb-0">Total Penerimaan</p>
                                <small class="text-muted">${auditData.penerimaan?.count || 0} transaksi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card audit-summary-card border-info">
                            <div class="card-body text-center">
                                <h3 class="text-info">Rp ${formatRupiah(auditData.belanja?.total || 0)}</h3>
                                <p class="mb-0">Total Belanja</p>
                                <small class="text-muted">${auditData.belanja?.count || 0} transaksi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card audit-summary-card border-warning">
                            <div class="card-body text-center">
                                <h3 class="text-warning">${auditData.problematic_data ? Object.keys(auditData.problematic_data).length : 0}</h3>
                                <p class="mb-0">Masalah Ditemukan</p>
                                <small class="text-muted">Perlu perhatian</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Detail Penerimaan
            if (auditData.penerimaan && auditData.penerimaan.detail) {
                html += `
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Detail Penerimaan Dana</h6>
                            <span class="badge bg-light text-primary">${auditData.penerimaan.count} transaksi</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table data-table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sumber Dana</th>
                                            <th>Jumlah Dana</th>
                                            <th>Saldo Awal</th>
                                            <th>Total</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${auditData.penerimaan.detail.map(p => `
                                            <tr>
                                                <td>${p.sumber || '-'}</td>
                                                <td>Rp ${formatRupiah(p.jumlah_dana || 0)}</td>
                                                <td>${p.saldo_awal ? 'Rp ' + formatRupiah(p.saldo_awal) : '-'}</td>
                                                <td><strong>Rp ${formatRupiah(p.total || 0)}</strong></td>
                                                <td>${formatTanggal(p.tanggal_terima)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Belanja per Bulan
            if (auditData.belanja && auditData.belanja.per_bulan) {
                html += `
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Belanja per Bulan</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table data-table">
                                    <thead>
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Total Belanja</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${Object.entries(auditData.belanja.per_bulan).map(([bulan, total]) => `
                                            <tr>
                                                <td>${bulan}</td>
                                                <td>Rp ${formatRupiah(total)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <td><strong>Total</strong></td>
                                            <td><strong>Rp ${formatRupiah(auditData.belanja.total || 0)}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Data Bermasalah
            if (auditData.problematic_data && Object.keys(auditData.problematic_data).length > 0) {
                html += `
                    <div class="card mb-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Data Bermasalah
                            </h6>
                        </div>
                        <div class="card-body">
                            ${Object.entries(auditData.problematic_data).map(([type, data]) => `
                                <div class="problem-item mb-3">
                                    <h6 class="text-danger">${type.replace(/_/g, ' ').toUpperCase()} (${data.length} data)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    ${Object.keys(data[0] || {}).map(key => 
                                                        `<th class="text-capitalize">${key.replace(/_/g, ' ')}</th>`
                                                    ).join('')}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.slice(0, 10).map(item => `
                                                    <tr>
                                                        ${Object.values(item).map(val => 
                                                            `<td>${typeof val === 'number' && val > 1000 ? 'Rp ' + formatRupiah(val) : val}</td>`
                                                        ).join('')}
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                    ${data.length > 10 ? `<p class="text-muted">... dan ${data.length - 10} data lainnya</p>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="healthy-item">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Tidak ada data bermasalah yang ditemukan.</strong> Data BKU dalam keadaan sehat.
                        </div>
                    </div>
                `;
            }

            // Rekomendasi
            if (auditData.recommendations && auditData.recommendations.length > 0) {
                html += `
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="bi bi-lightbulb me-2"></i>
                                Rekomendasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                ${auditData.recommendations.map(rec => `
                                    <li>${rec}</li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                `;
            }
        }

        $('#auditResults').html(html);
    }
});
</script>
@endpush