@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<style>

    .animate__animated {
    animation-duration: 0.5s;
}

.border-dashed {
    border: 2px dashed #dee2e6 !important;
    transition: all 0.3s ease;
}

.border-dashed:hover {
    border-color: #667eea !important;
    background-color: rgba(102, 126, 234, 0.05) !important;
}

.bg-primary.bg-opacity-10 {
    border-color: #667eea !important;
    background-color: rgba(102, 126, 234, 0.1) !important;
}
    /* Modal Header Gradient */
    .modal-header.bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .modal-header.bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .modal-header.bg-gradient-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    /* Form Floating Enhancement */
    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        color: #6c757d;
        font-weight: 500;
    }

    .form-floating>.form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* File Upload Enhancement */
.file-upload-area {
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    transform: translateY(-2px);
}

.upload-preview {
    animation: slideInUp 0.5s ease;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.file-info-summary {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* File Status Alert */
.file-status {
    border-left: 4px solid #198754 !important;
    background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
}

/* File Actions */
.file-actions .btn {
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.file-actions .btn:hover {
    transform: scale(1.05);
}

/* Info Summary Cards */
.file-info-summary .col-md-4 > div {
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.file-info-summary .col-md-4 > div:hover {
    transform: translateY(-2px);
    border-color: #e9ecef;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Drag and Drop States */
.border-dashed.drag-over {
    border-color: #667eea !important;
    background-color: rgba(102, 126, 234, 0.1) !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .upload-preview .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .upload-preview .file-actions {
        margin-top: 1rem;
        justify-content: center;
    }
    
    .file-info-summary .row {
        flex-direction: column;
    }
    
    .file-info-summary .col-md-4 {
        margin-bottom: 0.5rem;
    }
}

    .border-dashed {
        border-style: dashed !important;
    }

    .upload-placeholder,
    .upload-preview {
        transition: all 0.3s ease;
    }

    /* Modal Icon */
    .modal-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Button Enhancement */
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Alert Enhancement */
    .alert {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    /* Card Enhancement */
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    /* Form Text Enhancement */
    .form-text {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Invalid Feedback Enhancement */
    .invalid-feedback {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Modal Content Enhancement */
    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
    }

    .modal-body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    /* Loading State Enhancement */
    .btn-loading {
        position: relative;
        overflow: hidden;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                transparent);
        animation: loading-shimmer 1.5s infinite;
    }

    @keyframes loading-shimmer {
        0% {
            left: -100%;
        }

        100% {
            left: 100%;
        }
    }

    /* SweetAlert Customization */
    .swal2-popup {
        border-radius: 1rem !important;
    }

    .swal2-title {
        font-weight: 600 !important;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 1rem;
        }

        .modal-header {
            padding: 1rem;
        }

        .modal-body {
            padding: 1rem;
        }
    }
</style>
<div class="content-area" id="contentArea">
    <div class="fade-in">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gradient">Kode Kegiatan</h1>
                <p class="text-muted">Kelola data Program Kegiatan Anda.</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Baru
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-2"></i>Import Excel
                </button>
                <a href="{{ route('referensi.kode-kegiatan.download-template') }}"
                    class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-download me-2"></i>Download Template
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Kode</th>
                                <th>Program</th>
                                <th>Sub Program</th>
                                <th>Uraian</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kegiatanTableBody">
                            @foreach ($kodeKegiatans as $key => $kegiatan)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $kegiatan->kode }}</td>
                                <td>{{ $kegiatan->program }}</td>
                                <td>{{ $kegiatan->sub_program }}</td>
                                <td>{{ $kegiatan->uraian }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $kegiatan->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $kegiatan->id }}" data-kode="{{ $kegiatan->kode }}"
                                        data-program="{{ $kegiatan->program }}" data-sub-program="{{ $kegiatan->sub_program }}"
                                        data-uraian="{{ $kegiatan->uraian }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Menampilkan <span class="fw-semibold">{{ $kodeKegiatans->firstItem() }}</span> sampai
                            <span class="fw-semibold">{{ $kodeKegiatans->lastItem() }}</span> dari
                            <span class="fw-semibold">{{ $kodeKegiatans->total() }}</span> data
                        </div>
                        <div class="pagination-container">
                            {{ $kodeKegiatans->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tambah Modal -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="modal-icon bg-white bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-plus-circle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="tambahModalLabel">Tambah Program Kegiatan</h5>
                        <p class="mb-0 small opacity-75">Tambahkan data program kegiatan baru</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{ route('referensi.kode-kegiatan.store') }}" method="POST" id="tambahForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('kode') is-invalid @enderror" id="kode"
                                    name="kode" value="{{ old('kode') }}" placeholder="Kode Kegiatan" required>
                                <label for="kode" class="form-label">
                                    <i class="bi bi-hash me-2 text-primary"></i>Kode Kegiatan
                                </label>
                                @error('kode')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>Masukkan kode unik kegiatan (max: 10 karakter)
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('program') is-invalid @enderror"
                                    id="program" name="program" value="{{ old('program') }}" placeholder="Nama Program"
                                    required>
                                <label for="program" class="form-label">
                                    <i class="bi bi-journal-text me-2 text-success"></i>Nama Program
                                </label>
                                @error('program')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control @error('sub_program') is-invalid @enderror"
                                    id="sub_program" name="sub_program" placeholder="Sub Program" style="height: 100px"
                                    required>{{ old('sub_program') }}</textarea>
                                <label for="sub_program" class="form-label">
                                    <i class="bi bi-list-check me-2 text-warning"></i>Sub Program
                                </label>
                                @error('sub_program')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control @error('uraian') is-invalid @enderror" id="uraian"
                                    name="uraian" placeholder="Uraian Kegiatan" style="height: 120px"
                                    required>{{ old('uraian') }}</textarea>
                                <label for="uraian" class="form-label">
                                    <i class="bi bi-card-text me-2 text-info"></i>Uraian Kegiatan
                                </label>
                                @error('uraian')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Jelaskan detail kegiatan secara lengkap
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <i class="bi bi-check-circle me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach ($kodeKegiatans as $kegiatan)
<div class="modal fade" id="editModal{{ $kegiatan->id }}" tabindex="-1"
    aria-labelledby="editModalLabel{{ $kegiatan->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warning text-dark">
                <div class="d-flex align-items-center">
                    <div class="modal-icon bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="bi bi-pencil-square text-dark fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="editModalLabel{{ $kegiatan->id }}">Edit Program Kegiatan</h5>
                        <p class="mb-0 small opacity-75">Perbarui data program kegiatan</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('referensi.kode-kegiatan.update', $kegiatan->id) }}" method="POST" class="edit-form">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div class="small">Anda sedang mengedit data dengan kode: <strong>{{ $kegiatan->kode }}</strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('kode') is-invalid @enderror"
                                    id="kode_edit{{ $kegiatan->id }}" name="kode"
                                    value="{{ old('kode', $kegiatan->kode) }}" placeholder="Kode Kegiatan" required>
                                <label for="kode_edit{{ $kegiatan->id }}" class="form-label">
                                    <i class="bi bi-hash me-2 text-primary"></i>Kode Kegiatan
                                </label>
                                @error('kode')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('program') is-invalid @enderror"
                                    id="program_edit{{ $kegiatan->id }}" name="program"
                                    value="{{ old('program', $kegiatan->program) }}" placeholder="Nama Program"
                                    required>
                                <label for="program_edit{{ $kegiatan->id }}" class="form-label">
                                    <i class="bi bi-journal-text me-2 text-success"></i>Nama Program
                                </label>
                                @error('program')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control @error('sub_program') is-invalid @enderror"
                                    id="sub_program_edit{{ $kegiatan->id }}" name="sub_program"
                                    placeholder="Sub Program" style="height: 100px"
                                    required>{{ old('sub_program', $kegiatan->sub_program) }}</textarea>
                                <label for="sub_program_edit{{ $kegiatan->id }}" class="form-label">
                                    <i class="bi bi-list-check me-2 text-warning"></i>Sub Program
                                </label>
                                @error('sub_program')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control @error('uraian') is-invalid @enderror"
                                    id="uraian_edit{{ $kegiatan->id }}" name="uraian" placeholder="Uraian Kegiatan"
                                    style="height: 120px" required>{{ old('uraian', $kegiatan->uraian) }}</textarea>
                                <label for="uraian_edit{{ $kegiatan->id }}" class="form-label">
                                    <i class="bi bi-card-text me-2 text-info"></i>Uraian Kegiatan
                                </label>
                                @error('uraian')
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="bi bi-check-circle me-2"></i>Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <div class="d-flex align-items-center">
                    <div class="modal-icon bg-white bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-cloud-upload-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="importModalLabel">Import Data Excel</h5>
                        <p class="mb-0 small opacity-75">Unggah file Excel berisi data kegiatan</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{ route('referensi.kode-kegiatan.import') }}" method="POST" enctype="multipart/form-data"
                id="importForm">
                @csrf
                <div class="modal-body p-4">
                    <!-- File Upload Area -->
                    <div class="file-upload-area mb-4">
                        <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light bg-opacity-25 position-relative">
                            <input type="file" class="file-input" id="file" name="file" accept=".xlsx,.xls,.csv" required hidden>
                    
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                                <h5 class="text-muted mb-2">Klik untuk memilih file</h5>
                                <p class="text-muted small mb-3">Format: .xlsx, .xls, .csv (Max: 2MB)</p>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="document.getElementById('file').click()">
                                    <i class="bi bi-folder2-open me-2"></i>Pilih File
                                </button>
                            </div>
                    
                            <div class="upload-preview d-none" id="uploadPreview">
                                <div class="d-flex align-items-center justify-content-between bg-white rounded p-3 mb-3 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-excel text-success fs-3 me-3"></i>
                                        <div class="text-start">
                                            <h6 class="mb-1 text-dark" id="fileName">Nama File</h6>
                                            <p class="text-muted small mb-0" id="fileSize">Ukuran file</p>
                                            <p class="text-info small mb-0" id="fileType">Tipe file</p>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearFile()"
                                            title="Hapus file">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                    
                                <div class="file-status alert alert-success alert-dismissible fade show mb-0" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <div class="small">
                                            <strong>File siap diimport!</strong>
                                            <span id="fileReadyText">Klik tombol Import Data untuk melanjutkan</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                    
                        <!-- File Info Summary -->
                        <div class="file-info-summary mt-3 d-none" id="fileInfoSummary">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="bg-primary bg-opacity-10 rounded p-2 text-center">
                                        <i class="bi bi-file-text text-primary fs-5"></i>
                                        <div class="small">
                                            <div class="fw-bold" id="infoFileName">-</div>
                                            <small class="text-muted">Nama File</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-success bg-opacity-10 rounded p-2 text-center">
                                        <i class="bi bi-hdd text-success fs-5"></i>
                                        <div class="small">
                                            <div class="fw-bold" id="infoFileSize">-</div>
                                            <small class="text-muted">Ukuran</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-info bg-opacity-10 rounded p-2 text-center">
                                        <i class="bi bi-card-text text-info fs-5"></i>
                                        <div class="small">
                                            <div class="fw-bold" id="infoFileType">-</div>
                                            <small class="text-muted">Tipe</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        @error('file')
                        <div class="invalid-feedback d-block d-flex align-items-center mt-2">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Petunjuk Import -->
                    <div class="import-guide">
                        <div class="card border-0 bg-info bg-opacity-10">
                            <div class="card-body">
                                <h6 class="card-title d-flex align-items-center text-info mb-3">
                                    <i class="bi bi-lightbulb-fill me-2"></i>Petunjuk Import
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled small mb-0">
                                            <li class="mb-2">
                                                <i class="bi bi-download text-primary me-2"></i>
                                                <strong>Download template</strong> terlebih dahulu
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-check2-square text-success me-2"></i>
                                                Pastikan format kolom sesuai template
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                                Data duplikat akan diabaikan
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled small mb-0">
                                            <li class="mb-2">
                                                <i class="bi bi-fonts text-info me-2"></i>
                                                Kegiatan wajib diawali huruf kapital
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-table text-secondary me-2"></i>
                                                Pastikan tidak ada baris kosong
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-save text-danger me-2"></i>
                                                Simpan file dalam format .xlsx
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Template Download -->
                    <div class="template-download mt-3">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-excel-fill text-success fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-0">Template Import Excel</h6>
                                    <small class="text-muted">Download template untuk memudahkan input data</small>
                                </div>
                            </div>
                            <a href="{{ route('referensi.kode-kegiatan.download-template') }}"
                                class="btn btn-success btn-sm">
                                <i class="bi bi-download me-2"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success" id="btnImport">
                        <i class="bi bi-upload me-2"></i>Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/js/kode-kegiatan.js') }}"></script>
@endpush
@endsection