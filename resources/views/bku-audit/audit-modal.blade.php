{{-- Modal Audit --}}
<div class="modal fade" id="auditModal" tabindex="-1" aria-labelledby="auditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="auditModalLabel">
                    <i class="bi bi-search me-2"></i>Audit Data BKU
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn btn-success btn-sm" id="btnRunQuickAudit">
                            <i class="bi bi-play-circle me-1"></i>Jalankan Audit Cepat
                        </button>
                        <button class="btn btn-warning btn-sm ms-2" id="btnFixData">
                            <i class="bi bi-tools me-1"></i>Perbaiki Data Otomatis
                        </button>
                        <a href="{{ route('bku.audit.page', ['penganggaran_id' => $penganggaran->id]) }}"
                            class="btn btn-info btn-sm ms-2">
                            <i class="bi bi-bar-chart me-1"></i>Audit Detail
                        </a>
                    </div>
                </div>

                <div id="quickAuditResults">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Klik "Jalankan Audit Cepat" untuk memeriksa kesehatan data BKU.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function formatRupiah(angka) {
    if (!angka) return '0';
    return new Intl.NumberFormat('id-ID').format(angka);
}

// Event handler untuk tombol audit
$(document).on('click', '#btnAudit', function() {
    $('#auditModal').modal('show');
});

// Audit cepat
$('#btnRunQuickAudit').click(function() {
    runQuickAudit();
});

// Perbaiki data
$('#btnFixData').click(function() {
    if (!confirm('Perbaikan data akan mengubah data BKU. Lanjutkan?')) {
        return;
    }
    fixData();
});

function runQuickAudit() {
    const penganggaranId = {{ $penganggaran->id }};
    
    $('#quickAuditResults').html(`
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span class="ms-2">Memeriksa data...</span>
        </div>
    `);
    
    $.ajax({
        url: '/bku-audit/data/' + penganggaranId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderQuickAuditResults(response.audit_data);
            } else {
                $('#quickAuditResults').html(`
                    <div class="alert alert-danger">
                        <p class="mb-0">Error: ${response.message}</p>
                        ${response.debug ? '<small>Debug: ' + JSON.stringify(response.debug) + '</small>' : ''}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Audit error:', xhr);
            $('#quickAuditResults').html(`
                <div class="alert alert-danger">
                    <p class="mb-0">Terjadi kesalahan saat melakukan audit.</p>
                    <small>Status: ${xhr.status} - ${xhr.statusText}</small>
                </div>
            `);
        }
    });
}

function fixData() {
    const penganggaranId = {{ $penganggaran->id }};
    
    $('#quickAuditResults').html(`
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
            <span class="ms-2">Memperbaiki data...</span>
        </div>
    `);
    
    $.ajax({
        url: '/bku-audit/fix/' + penganggaranId,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#quickAuditResults').html(`
                    <div class="alert alert-success">
                        <p class="mb-0">✅ Data berhasil diperbaiki!</p>
                        <small>${response.message}</small>
                        ${response.fix_results ? '<small><br>Data yang diperbaiki: ' + response.fix_results.bku_fixed_count + '</small>' : ''}
                    </div>
                `);
                setTimeout(runQuickAudit, 1000);
            } else {
                $('#quickAuditResults').html(`
                    <div class="alert alert-danger">
                        <p class="mb-0">Error: ${response.message}</p>
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Fix data error:', xhr);
            $('#quickAuditResults').html(`
                <div class="alert alert-danger">
                    <p class="mb-0">Terjadi kesalahan saat memperbaiki data.</p>
                </div>
            `);
        }
    });
}

function renderQuickAuditResults(auditData) {
    if (auditData.error) {
        $('#quickAuditResults').html(`
            <div class="alert alert-danger">
                <p class="mb-0">Error: ${auditData.error}</p>
            </div>
        `);
        return;
    }
    
    const selisih = auditData.saldo?.selisih || 0;
    const isHealthy = Math.abs(selisih) <= 1000;
    const problemCount = auditData.problematic_data ? Object.keys(auditData.problematic_data).length : 0;
    
    let html = `
        <div class="alert ${isHealthy ? 'alert-success' : 'alert-danger'}">
            <h6>${isHealthy ? '✅ Data Sehat' : '⚠️ Data Bermasalah'}</h6>
            <p class="mb-1">Selisih: <strong>Rp ${formatRupiah(selisih)}</strong></p>
            <p class="mb-1">Total Penerimaan: Rp ${formatRupiah(auditData.penerimaan?.total || 0)}</p>
            <p class="mb-1">Total Belanja: Rp ${formatRupiah(auditData.belanja?.total || 0)}</p>
            <p class="mb-0">Masalah ditemukan: <strong>${problemCount}</strong></p>
        </div>
    `;
    
    if (problemCount > 0 && auditData.problematic_data) {
        html += `
            <div class="mt-3">
                <h6>Masalah yang Ditemukan:</h6>
                <ul class="small">
                    ${Object.entries(auditData.problematic_data).map(([type, data]) => `
                        <li>${type.replace(/_/g, ' ')}: ${data.length} data</li>
                    `).join('')}
                </ul>
            </div>
        `;
    }
    
    if (auditData.recommendations && auditData.recommendations.length > 0) {
        html += `
            <div class="mt-3">
                <h6>Rekomendasi:</h6>
                <ul class="small">
                    ${auditData.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                </ul>
            </div>
        `;
    }
    
    $('#quickAuditResults').html(html);
}
</script>
@endpush