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
                <h1 class="h3 mb-1 text-gradient">Buku Kas Umum (BKU)</h1>
                <p class="mb-0 text-muted">Kelola buku kas umum sekolah Anda.</p>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Card BKU -->
        <div class="card">
            <div class="card-body p-0">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs border-bottom-0" id="bkuTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bosp-reguler-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-reguler" type="button" role="tab">
                            BOSP Reguler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-daerah-tab" data-bs-toggle="tab" data-bs-target="#bosp-daerah"
                            type="button" role="tab">
                            BOSP Daerah
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-kinerja-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-kinerja" type="button" role="tab">
                            BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="silpa-bosp-tab" data-bs-toggle="tab" data-bs-target="#silpa-bosp"
                            type="button" role="tab">
                            SiLPA BOSP Kinerja
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
                <div class="tab-content" id="bkuTabsContent">
                    <!-- BOSP Reguler Tab -->
                    <div class="tab-pane fade show active" id="bosp-reguler" role="tabpanel">
                        <div class="card bg-body-secondary ms-3 me-3 mt-3 mb-3">
                            <div class="p-4">
                                <div class="mb-4">
                                    <h5 class="fw-bold">BKU BOSP Reguler 2025</h5>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        Laporkan minimal 50% belanja dana tahap 2 paling lambat 31 Agustus 2025
                                    </div>
                                </div>
                            
                                <div class="d-flex justify-content-between mb-4">
                                    <div>
                                        <button class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-printer me-2"></i>Cetak
                                        </button>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Penerimaan Dana
                                        </button>
                                    </div>
                                </div>
                            
                                <div class="row">
                                    <!-- Month Cards -->
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Januari</h6>
                                                <span class="badge bg-success">Sudah Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Februari</h6>
                                                <span class="badge bg-success">Sudah Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Maret</h6>
                                                <span class="badge bg-success">Sudah Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">April</h6>
                                                <span class="badge bg-success">Sudah Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Mei</h6>
                                                <span class="badge bg-success">Sudah Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Juni</h6>
                                                <span class="badge bg-success">Sudah Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Juli</h6>
                                                <span class="badge bg-warning text-dark">Draf</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Agustus</h6>
                                                <span class="badge bg-secondary">Belum Dibuat</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">September</h6>
                                                <span class="badge bg-secondary">Belum Dibuat</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Oktober</h6>
                                                <span class="badge bg-secondary">Belum Dibuat</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">November</h6>
                                                <span class="badge bg-secondary">Belum Dibuat</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Desember</h6>
                                                <span class="badge bg-secondary">Belum Dibuat</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="text-center mt-4">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat me-2"></i>Perbarui Data BKU 2025
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOSP Daerah Tab -->
                    <div class="tab-pane fade" id="bosp-daerah" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data BOSP Daerah</p>
                            </div>
                        </div>
                    </div>

                    <!-- BOSP Kinerja Tab -->
                    <div class="tab-pane fade" id="bosp-kinerja" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data BOSP Kinerja</p>
                            </div>
                        </div>
                    </div>

                    <!-- SiLPA BOSP Kinerja Tab -->
                    <div class="tab-pane fade" id="silpa-bosp" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data SiLPA BOSP Kinerja</p>
                            </div>
                        </div>
                    </div>

                    <!-- Lainnya Tab -->
                    <div class="tab-pane fade" id="lainnya" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@include('layouts.footer')