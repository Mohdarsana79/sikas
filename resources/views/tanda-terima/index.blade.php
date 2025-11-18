@extends('layouts.app')
@include('layouts.navbar')
@include('layouts.sidebar')
@section('content')
<style>
    /* Menggunakan font Inter dari CDN */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f1f5f9;
    }

    .container-main {
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .card-shadow {
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
    }

    .table thead {
        background-color: transparent;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn-select {
        background-color: #f0f4f8;
        color: #495057;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.2rem 0.5rem;
        border: 1px solid #e2e8f0;
    }

    .process-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        color: #475569;
        background-color: #f0f4f8;
        font-weight: 600;
    }

    .header-title {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .summary-card {
        background-color: #fff;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        padding: 25px;
        height: 100%;
        box-shadow: 0 0.1rem 0.3rem rgba(0, 0, 0, 0.03);
    }

    .total-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #212529;
        line-height: 1;
    }

    .text-green-success-custom {
        color: #16a34a;
    }

    .icon-lg {
        font-size: 3rem;
        color: #cbd5e1;
    }

    .search-input-group .form-control {
        border-right: 0;
        border-top-right-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        padding-left: 2.5rem;
    }

    .search-input-group .input-group-text {
        border-radius: 0.5rem;
        border-left: 0;
        position: absolute;
        left: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        background-color: transparent;
        border: none;
        z-index: 10;
    }

    .search-input-group {
        position: relative;
    }

    .custom-action-button {
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .table-action-btn {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        color: #64748b;
        padding: 0.3rem 0.5rem;
    }

    .table-action-btn:hover {
        background-color: #f1f5f9;
    }

    .table-min-width {
        min-width: 800px;
    }

    .badge-code {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .loading-pulse {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .header-title {
            font-size: 1.5rem;
        }

        .summary-card {
            padding: 15px;
        }

        .total-number {
            font-size: 1.8rem;
        }

        .icon-lg {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 576px) {
        .container-main {
            padding-left: 15px;
            padding-right: 15px;
        }

        .header-title {
            margin-bottom: 0.25rem !important;
        }

        .col-md-4 {
            width: 100%;
        }

        .card-body.py-4 {
            padding: 1rem !important;
        }

        .custom-action-button {
            flex-grow: 1;
            margin-bottom: 0.5rem;
        }
    }

    /* Modal Styles untuk Landscape PDF */
.modal-pdf-container {
    height: 70vh;
    width: 100%;
}

.modal-pdf-container object {
    width: 100%;
    height: 100%;
    border: none;
}

.modal-xl {
    max-width: 95%;
    height: 80vh;
}

.modal-fullscreen {
    max-width: 100%;
    height: 100vh;
    margin: 0;
}

.modal-fullscreen .modal-content {
    height: 100vh;
    border-radius: 0;
    display: flex;
    flex-direction: column;
}

.modal-fullscreen .modal-header {
    flex-shrink: 0;
}

.modal-fullscreen .modal-body {
    padding: 0;
    flex: 1;
    overflow: hidden;
}

/* Untuk landscape PDF, kita perlu menyesuaikan aspect ratio */
@media (min-width: 1200px) {
    .modal-xl {
        max-width: 1200px;
    }
}

.pdf-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    flex-direction: column;
}

/* Loading Styles */
#global-loading {
    background: rgba(0, 0, 0, 0.5);
    border-radius: 10px;
}

.loading-pulse {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1;
    }
}

/* Download Progress Styles */
.download-progress {
    width: 100%;
    height: 20px;
    background-color: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 10px 0;
}

.download-progress-bar {
    height: 100%;
    background: linear-gradient(45deg, #17a2b8, #20c997);
    transition: width 0.3s ease;
}
</style>

<div class="container container-main">
    <!-- HEADER -->
    <div class="row mb-5">
        <div class="col-12 col-md-6">
            <h1 class="header-title mb-0">Tanda Terima</h1>
            <p class="text-muted mt-1 mb-0">Kelola tanda terima pembayaran dan pengeluaran</p>
        </div>
    </div>

    <!-- STATISTIK RINGKASAN -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="summary-card d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold">Total Item</small>
                    <div class="total-number" id="total-tanda-terima">{{ $tandaTerimas->total() }}</div>
                </div>
                <i class="bi bi-file-earmark-text icon-lg"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold">Siap Generate</small>
                    <div class="total-number" id="ready-generate">0</div>
                </div>
                <i class="bi bi-lightning-charge icon-lg text-green-success-custom"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold">Terakhir Update</small>
                    <div class="total-number" style="font-size: 1.2rem;" id="last-updated">{{ now()->format('d/m/Y H:i')
                        }}</div>
                </div>
                <i class="bi bi-clock-history icon-lg"></i>
            </div>
        </div>
    </div>

    <!-- AKSI CEPAT -->
    <div class="card card-shadow mb-5">
        <div class="card-body py-4">
            <h5 class="card-title d-flex align-items-center mb-3 fw-bold" style="font-size: 1.1rem; color: #475569;">
                <i class="bi bi-lightning-charge me-2"></i> Aksi Cepat
            </h5>

            <!-- Search Bar dan Filter -->
            <div class="d-flex align-items-center w-100 mb-4">
                <div class="search-input-group flex-grow-1 me-2">
                    <span class="input-group-text">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm" id="searchInput"
                        placeholder="Cari tanda terima..." aria-label="Cari tanda terima">
                </div>
                <!-- Filter Button -->
                <select class="form-select w-auto" id="tahunFilter">
                    <option selected value="">Pilih Tahun</option>
                    @foreach($tahunAnggarans as $tahun)
                    <option value="{{ $tahun['id'] }}" {{ $selectedTahun==$tahun['id'] ? 'selected' : '' }}>
                        {{ $tahun['tahun'] }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi Cepat -->
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary custom-action-button d-flex align-items-center" id="generate-all-btn">
                    <i class="bi bi-plus-lg me-1"></i> Generate
                    <span class="badge bg-light text-dark ms-2" id="ready-generate-badge">0</span>
                </button>
                <button class="btn btn-success custom-action-button d-flex align-items-center" id="download-all-btn">
                    <i class="bi bi-download me-1"></i> Download All
                </button>
                <button class="btn btn-danger custom-action-button d-flex align-items-center" id="delete-all-btn">
                    <i class="bi bi-trash3 me-1"></i> Hapus All
                </button>
            </div>
        </div>
    </div>

    <!-- DAFTAR TANDA TERIMA -->
    <div class="card card-shadow mb-5">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom-0 pt-4 pb-2">
            <h5 class="mb-0 fw-bold" style="color: #475569;">Daftar Tanda Terima</h5>
            <span class="text-muted">Total: <span id="table-count">{{ $tandaTerimas->total() }}</span> item</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-min-width" style="font-size: 10pt;">
                    <thead>
                        <tr class="text-muted" style="font-size: 0.9rem;">
                            <th scope="col" class="py-3 ps-4" style="width: 5%;">No</th>
                            <th scope="col" class="py-3" style="width: 15%;">Kode Rekening</th>
                            <th scope="col" class="py-3" style="width: 30%;">Uraian</th>
                            <th scope="col" class="py-3" style="width: 15%;">Tanggal</th>
                            <th scope="col" class="py-3 text-end" style="width: 15%;">Jumlah</th>
                            <th scope="col" class="py-3 text-center pe-4" style="width: 20%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tanda-terima-tbody">
                        @forelse($tandaTerimas as $index => $tandaTerima)
                        <tr id="tanda-terima-row-{{ $tandaTerima->id }}">
                            <td class="py-3 ps-4">
                                <div class="fw-bold">{{ ($tandaTerimas->currentPage() - 1) * $tandaTerimas->perPage() +
                                    $index + 1 }}</div>
                            </td>
                            <td>
                                <span class="badge badge-code">{{ $tandaTerima->rekeningBelanja->kode_rekening ?? '-'
                                    }}</span>
                            </td>
                            <td class="text-dark">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-text me-2 text-muted"></i>
                                    <span class="text-truncate" style="max-width: 300px;">
                                        {{ $tandaTerima->bukuKasUmum->uraian_opsional ??
                                        $tandaTerima->bukuKasUmum->uraian ?? '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted">
                                <i class="bi bi-calendar me-2"></i>
                                {{ \Carbon\Carbon::parse($tandaTerima->bukuKasUmum->tanggal_transaksi)->format('d/m/Y')
                                }}
                            </td>
                            <td class="fw-bold text-success text-end">
                                <i class="bi bi-currency-dollar me-2"></i>
                                Rp {{ number_format($tandaTerima->bukuKasUmum->total_transaksi_kotor ?? 0, 0, ',', '.')
                                }}
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- Preview Button - Modal Trigger -->
                                    <button class="btn table-action-btn preview-tanda-terima" title="Lihat Preview" data-id="{{ $tandaTerima->id }}"
                                        data-bs-toggle="modal" data-bs-target="#previewModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('tanda-terima.pdf', $tandaTerima->id) }}"
                                        class="btn table-action-btn" title="Download PDF" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button class="btn table-action-btn delete-tanda-terima" title="Hapus Tanda Terima"
                                        data-id="{{ $tandaTerima->id }}"
                                        data-uraian="{{ $tandaTerima->bukuKasUmum->uraian_opsional ?? $tandaTerima->bukuKasUmum->uraian }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state" style="padding: 2rem; background: transparent; border: none;">
                                    <i class="bi bi-receipt empty-state-icon"
                                        style="font-size: 3rem; color: #cbd5e1;"></i>
                                    <h5 class="text-dark mb-2">Belum ada data tanda terima</h5>
                                    <p class="text-muted">Mulai dengan generate tanda terima otomatis dari data Buku Kas
                                        Umum</p>
                                    <button class="btn btn-primary mt-3" id="generate-empty-btn">
                                        <i class="bi bi-plus-lg me-1"></i> Generate Tanda Terima
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($tandaTerimas->hasPages())
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top border-light">
                <div class="text-muted small mb-3 mb-md-0" id="pagination-info">
                    Menampilkan {{ $tandaTerimas->firstItem() }} sampai {{ $tandaTerimas->lastItem() }} dari {{
                    $tandaTerimas->total() }} data
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-simple mb-0" id="pagination-container">
                        {{ $tandaTerimas->links() }}
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Tanda Terima</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="fullscreenToggle">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="modal-pdf-container">
                    <div id="pdfLoading" class="pdf-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Memuat PDF...</p>
                    </div>
                    <iframe id="pdfIframe" src="about:blank" width="100%" height="100%"
                        style="border: none; display: none;"
                        onload="document.getElementById('pdfLoading').style.display='none'; this.style.display='block';"
                        onerror="document.getElementById('pdfLoading').style.display='none'; this.style.display='none'; document.getElementById('pdfFallback').style.display='block';">
                    </iframe>
                    <div id="pdfFallback" class="d-flex justify-content-center align-items-center h-100"
                        style="display: none;">
                        <div class="text-center">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <p class="mt-3">Browser tidak mendukung preview PDF.</p>
                            <a href="#" id="fallbackDownload" class="btn btn-primary" target="_blank">
                                <i class="bi bi-download me-2"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="position-fixed top-50 start-50 translate-middle" id="global-loading" style="display: none; z-index: 9999;">
    <div class="d-flex align-items-center bg-white rounded p-3 shadow">
        <div class="spinner-border text-primary me-3" role="status"></div>
        <span class="text-dark">Memproses...</span>
    </div>
</div>

@push('scripts')
<!-- Include Kwitansi JavaScript -->
<script src="{{ asset('assets/js/tandaterima.js') }}"></script>
@endpush
@endsection