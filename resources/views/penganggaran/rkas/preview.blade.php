@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (!$hasData)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada data RKAS.
                        <a href="{{ route('penganggaran.rkas.index') }}" class="alert-link">
                            Klik di sini untuk menambahkan data
                        </a>
                    </div>
                @endif

                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('penganggaran.rkas.index') }}" class="btn btn-link text-primary p-0 me-3">
                            <i class="fas fa-arrow-left"></i> Kembali ke Penganggaran
                        </a>
                    </div>
                </div>

                <!-- Title Section -->
                <div class="mb-4">
                    <h2 class="fw-bold mb-2">Mencetak RKAS {{ $dataSekolah['tahun_anggaran'] ?? date('Y') }}</h2>
                    <p class="text-muted mb-0">BOSP REGULER {{ $dataSekolah['tahun_anggaran'] ?? date('Y') }}</p>
                </div>

                @if ($hasData)
                    <!-- PDF Preview Frame -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="pdf-viewer-container" style="height: 80vh; background-color: #f8f9fa;">
                                <iframe id="pdfFrame" src="{{ route('penganggaran.rkas.generate-pdf') }}" width="100%"
                                    height="100%" style="border: none; border-radius: 8px;" title="Preview RKAS PDF">
                                </iframe>
                            </div>
                        </div>

                        <!-- PDF Controls -->
                        <div
                            class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted small">RKAS Preview</span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('penganggaran.rkas.download.pdf') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
