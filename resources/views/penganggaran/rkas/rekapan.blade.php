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
                    <h1 class="h3 mb-1 text-gradient">Rekapan</h1>
                    <p class="mb-0 text-muted">Kelola Rekapan Anda</p>
                </div>
                <div class="btn">
                    <button class="btn btn-primary" id="btnCetak" data-bs-toggle="modal" data-bs-target="#cetakModal"
                        style="font-size: 9pt;">
                        <i class="bi bi-printer me-2"></i>
                        Tanggal Cetak
                    </button>
                </div>
            </div>

            <!-- Card Penganggaran -->
            <div class="card">
                <div class="card-body p-0">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs border-bottom-0" id="rkasTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rka-tahapan-tab" data-bs-toggle="tab"
                                data-bs-target="#rka-tahapan" type="button" role="tab">
                                Rka Tahapan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rka-rekap-tab" data-bs-toggle="tab" data-bs-target="#rka-rekap"
                                type="button" role="tab">
                                Rka Rekap
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rka-221-tab" data-bs-toggle="tab" data-bs-target="#rka-221"
                                type="button" role="tab">
                                Lembar Kerja 221
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rka-bulan-tab" data-bs-toggle="tab" data-bs-target="#rka-bulan"
                                type="button" role="tab">
                                Rka Bulanan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lainnya-tab" data-bs-toggle="tab" data-bs-target="#lainnya"
                                type="button" role="tab">
                                Lainnya
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="rkasTabsContent">
                        <!-- Rka Tahapan Tab -->
                        <div class="tab-pane fade show active" id="rka-tahapan" role="tabpanel">
                            <div class="p-4">
                                <!-- Card Hasil PDF Rkas Tahapan -->
                                @if(isset($pdfData)
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="bi bi-file-earmark-pdf me-2"></i>
                                            Hasil Cetak RKAS Tahapan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fw-bold">Tanggal Cetak:</label>
                                                    <p>{{ $pdfData->tanggal_cetak ?? 'Belum dicetak' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fw-bold">Total Anggaran Tahap 1:</label>
                                                    <p>Rp {{ number_format($pdfData->total_tahap1, 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fw-bold">Status:</label>
                                                    <p>
                                                        <span class="badge bg-success">Tercetak</span>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fw-bold">Total Anggaran Tahap 2:</label>
                                                    <p>Rp {{ number_format($pdfData->total_tahap2, 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="{{ route('penganggaran.rkas.generate-pdf') }}" target="_blank" class="btn btn-primary me-2">
                                                <i class="bi bi-eye me-2"></i>Lihat PDF
                                            </a>
                                            <a href="{{ route('penganggaran.rkas.generate-pdf') }}" download class="btn btn-success">
                                                <i class="bi bi-download me-2"></i>Download PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Belum Ada RKAS Tahapan</p>
                                    <a href="{{ route('penganggaran.rkas.generate-pdf') }}" target="_blank" class="btn btn-primary">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate PDF
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tab lainnya tetap sama -->
                        <!-- ... -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cetak -->
    <div class="modal fade" id="cetakModal" tabindex="-1" aria-labelledby="cetakModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cetakModalLabel">
                        <i class="bi bi-calendar-date me-2"></i>Tanggal Cetak
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCetak" method="POST" action="{{ route('penganggaran.rkas.simpan-tanggal-cetak') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tanggal_cetak" class="form-label">Pilih Tanggal Cetak</label>
                            <input type="date" class="form-control" id="tanggal_cetak" name="tanggal_cetak" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .card-header {
            border-radius: 0.375rem 0.375rem 0 0 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi datepicker dengan tanggal hari ini
            document.getElementById('tanggal_cetak').valueAsDate = new Date();
            
            // Handle form submission
            document.getElementById('formCetak').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                // Show loading
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                submitButton.disabled = true;
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('cetakModal'));
                        modal.hide();
                        
                        // Show success message
                        alert('Tanggal cetak berhasil disimpan');
                        
                        // Reload page to show updated data
                        window.location.reload();
                    } else {
                        alert('Gagal menyimpan tanggal cetak: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data');
                })
                .finally(() => {
                    submitButton.innerHTML = '<i class="bi bi-save me-2"></i>Simpan';
                    submitButton.disabled = false;
                });
            });
        });
    </script>
@endsection