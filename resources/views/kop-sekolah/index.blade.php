@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-school me-2"></i>Kop Sekolah
                        </h4>
                        <span class="badge bg-light text-primary fs-6">Pengaturan</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Preview Kop Sekolah -->
                    <div class="text-center mb-4">
                        <div class="preview-container bg-light rounded p-4 mb-3 border">
                            <h6 class="text-muted mb-3">Preview Kop Sekolah</h6>
                            @if ($kopSekolah && $kopSekolah->file_path)
                            @php
                            $filePath = public_path('storage/kop_sekolah/' . $kopSekolah->file_path);
                            @endphp
                            @if (file_exists($filePath))
                            <img src="{{ asset('storage/kop_sekolah/' . $kopSekolah->file_path) }}" alt="Kop Sekolah"
                                class="img-fluid rounded shadow-sm preview-image"
                                style="max-width: 100%; max-height: 180px; object-fit: contain;">
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Gambar default akan digunakan</p>
                                <img src="{{ asset('storage/kop_sekolah/kop-default.jpg') }}" alt="Logo Sekolah Default"
                                    class="img-fluid mt-2 rounded" style="max-width: 200px; max-height: 150px;">
                            </div>
                            @endif
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Belum ada kop sekolah yang diupload</p>
                                <p class="text-muted small">Gambar default akan ditampilkan</p>
                                <img src="{{ asset('storage/kop_sekolah/kop-default.jpg') }}" alt="Logo Sekolah Default"
                                    class="img-fluid mt-2 rounded" style="max-width: 200px; max-height: 150px;">
                            </div>
                            @endif
                        </div>

                        @if ($kopSekolah)
                        <div class="file-info bg-success text-white rounded p-2 mb-3">
                            <small>
                                <i class="fas fa-check-circle me-1"></i>
                                Kop sekolah sudah diupload
                                @if ($kopSekolah->created_at)
                                - {{ $kopSekolah->created_at->format('d M Y H:i') }}
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>

                    <!-- Upload Form -->
                    <div class="upload-section">
                        <form action="{{ route('kop-sekolah.store') }}" method="POST" enctype="multipart/form-data"
                            id="uploadForm">
                            @csrf

                            <div class="mb-4">
                                <label for="kop_sekolah" class="form-label fw-semibold">
                                    <i class="fas fa-upload me-1"></i>Upload File Kop Sekolah
                                </label>
                                <input type="file" class="form-control form-control-lg" id="kop_sekolah"
                                    name="kop_sekolah" accept="image/*" required onchange="previewImage(this)">
                                <div class="form-text">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <i class="fas fa-info-circle me-1 text-primary"></i>
                                            Format: JPEG, PNG, JPG
                                        </div>
                                        <div class="col-md-6">
                                            <i class="fas fa-weight-hanging me-1 text-primary"></i>
                                            Maksimal: 2MB
                                        </div>
                                    </div>
                                    <div class="row mt-1">
                                        <div class="col-12">
                                            <i class="fas fa-ruler-combined me-1 text-primary"></i>
                                            Rekomendasi Ukuran: 2480 x 588 pixel
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                @if ($kopSekolah)
                                <button type="button" class="btn btn-outline-danger btn-lg px-4" id="deleteBtn">
                                    <i class="fas fa-trash me-2"></i>Hapus
                                </button>
                                @endif

                                <button type="submit" class="btn btn-primary btn-lg px-4" id="uploadBtn">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload Kop Sekolah
                                </button>
                            </div>
                        </form>

                        @if ($kopSekolah)
                        <form id="delete-form" action="{{ route('kop-sekolah.destroy', $kopSekolah->id) }}"
                            method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </div>

                    <!-- Informasi -->
                    <div class="alert alert-info mt-4" role="alert">
                        <h6 class="alert-heading fw-bold">
                            <i class="fas fa-lightbulb me-2"></i>Informasi Penting
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li>Kop sekolah akan digunakan pada semua dokumen dan laporan</li>
                            <li>Pastikan gambar memiliki kualitas yang baik dan resolusi yang sesuai</li>
                            <li>Format landscape direkomendasikan untuk tampilan yang optimal</li>
                            <li>Ukuran file maksimal 2MB untuk performa terbaik</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .preview-container {
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .preview-image {
        transition: transform 0.3s ease;
        border: 2px solid #dee2e6;
    }

    .preview-image:hover {
        transform: scale(1.02);
    }

    .file-info {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .upload-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 2rem;
        border: 1px dashed #dee2e6;
    }

    .form-control-lg {
        border-radius: 10px;
        border: 2px dashed #ced4da;
        transition: all 0.3s ease;
    }

    .form-control-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .btn-lg {
        border-radius: 10px;
        font-weight: 600;
    }

    .card {
        border-radius: 15px;
        border: none;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewImage(input) {
    const preview = document.querySelector('.preview-image');
    const previewContainer = document.querySelector('.preview-container');
    const defaultContent = `
        <div class="text-center py-4">
            <i class="fas fa-image fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">Belum ada kop sekolah yang diupload</p>
            <p class="text-muted small">Gambar default akan ditampilkan</p>
            <img src="{{ asset('storage/kop_sekolah/kop-default.jpg') }}" 
                 alt="Logo Sekolah Default"
                 class="img-fluid mt-2 rounded"
                 style="max-width: 200px; max-height: 150px;">
        </div>
    `;

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = file.size / 1024 / 1024; // MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        // Validasi tipe file
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format File Tidak Valid',
                text: 'Hanya file JPEG, PNG, dan JPG yang diizinkan.'
            });
            input.value = '';
            return;
        }

        // Validasi ukuran file
        if (fileSize > 2) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 2MB.'
            });
            input.value = '';
            return;
        }

        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewContainer.innerHTML = `
                <h6 class="text-muted mb-3">Preview Kop Sekolah</h6>
                <img src="${e.target.result}" 
                     alt="Preview Kop Sekolah" 
                     class="img-fluid rounded shadow-sm preview-image"
                     style="max-width: 100%; max-height: 180px; object-fit: contain;">
                <div class="mt-2">
                    <small class="text-muted">${file.name} (${(fileSize).toFixed(2)} MB)</small>
                </div>
            `;
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.innerHTML = defaultContent;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert untuk upload
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('kop_sekolah');
            if (!fileInput.files[0]) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih file kop sekolah terlebih dahulu!',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            Swal.fire({
                title: 'Upload Kop Sekolah?',
                html: `
                    <div class="text-start">
                        <p>Apakah Anda yakin ingin mengupload kop sekolah ini?</p>
                        <div class="alert alert-warning small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Kop sekolah lama akan diganti dengan yang baru
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-cloud-upload-alt me-2"></i>Ya, Upload!',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary btn-lg px-4',
                    cancelButton: 'btn btn-secondary btn-lg px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const uploadBtn = document.getElementById('uploadBtn');
                    const originalText = uploadBtn.innerHTML;
                    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...';
                    uploadBtn.disabled = true;
                    
                    this.submit();
                }
            });
        });
    }

    // SweetAlert untuk delete
    const deleteBtn = document.getElementById('deleteBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Hapus Kop Sekolah?',
                html: `
                    <div class="text-start">
                        <p>Anda akan menghapus kop sekolah yang sedang aktif.</p>
                        <div class="alert alert-danger small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Tindakan ini tidak dapat dibatalkan! Dokumen akan menggunakan kop default.
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-trash me-2"></i>Ya, Hapus!',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger btn-lg px-4',
                    cancelButton: 'btn btn-secondary btn-lg px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...';
                    deleteBtn.disabled = true;
                    
                    document.getElementById('delete-form').submit();
                }
            });
        });
    }

    // SweetAlert untuk notifikasi success/error
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false,
            background: '#f8f9fa',
            position: 'top-end',
            toast: true
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#3085d6',
            background: '#f8f9fa'
        });
    @endif
});
</script>
@endpush