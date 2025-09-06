@extends('layouts.app')
@include('layouts.navbar')
@include('layouts.sidebar')
@section('content')
<div class="container-fluid p-4">
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- Top Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-arrow-left text-blue me-2"></i>
                    <span class="text-blue small">Kembali ke Pengelolaan</span>
                </div>
                <div class="d-flex align-items-center small text-muted">
                    <i class="bi bi-question-circle me-2"></i>
                    <span>Butuh panduan mengisi?</span>
                    <a href="#" class="text-blue text-decoration-underline ms-2">Baca Panduan Selengkapnya</a>
                </div>
            </div>

            <!-- Main Header -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-semibold text-dark mb-1">BKU {{ $bulan }} {{$tahun}}</h1>
                    <div class="d-flex align-items-center small text-muted">
                        <span>BKSP REGULER {{$tahun}}</span>
                        <span class="mx-2">|</span>
                        <a href="#" class="btn btn-danger" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .55rem;">Hapus BKU</a>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm-custom" data-bs-toggle="modal" data-bs-target="#transactionModal">
                        <i class="bi bi-plus me-1"></i>
                        Tambah Pembelanjaan
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm-custom">
                        <i class="bi bi-search me-1"></i>
                        Cari
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm-custom">
                        <i class="bi bi-printer me-1"></i>
                        Cetak
                    </button>
                    <button type="button" class="btn btn-dark btn-sm-custom">
                        Tutup BKU
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column - Dana Tersedia -->
        <div class="col-lg-6">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-inline-flex mb-1 justify-content-between w-100 align-items-center">
                        <h5 class="small fw-bold text-muted me-3">TOTAL DANA TERSEDIA</h5>
                        <div class="btn small-group" role="group" aria-label="Basic example">
                            <button class="btn btn-sm btn-dark me-2" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" data-bs-toggle="modal" data-bs-target="#tarikTunai">Tarik Tunai</button>
                            <button class="btn btn-sm btn-light" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" data-bs-toggle="modal" data-bs-target="#setorTunai">Setor Tunai</button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h4 class="fw-semibold text-dark">Rp 0</h4>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                <input type="text" class="form-control" value="0" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                <input type="text" class="form-control" value="0" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                    disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Anggaran -->
        <div class="col-lg-6">
            <!-- Anggaran Card -->
            <div class="card anggaran-card">
                <div class="card-body">
                    <h6 class="small fw-medium text-blue mt-3 mb-4">ANGGARAN DIBELANJAKAN SAMPAI BULAN INI</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Bisa dibelanjakan</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control" value="3.677.000" aria-label="Sizing example input"
                                        aria-describedby="inputGroup-sizing-sm" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Sudah Dibelanjakan</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control" value="15.660.000" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Bisa dianggarkan ulang</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control" value="0" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row col">
            <!-- Table Card -->
            <div class="card mt-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-3">ID</th>
                                    <th scope="col" class="px-4 py-3">Tanggal</th>
                                    <th scope="col" class="px-4 py-3">Kegiatan</th>
                                    <th scope="col" class="px-4 py-3">Rekening Belanja</th>
                                    <th scope="col" class="px-4 py-3">Jenis Transaksi</th>
                                    <th scope="col" class="px-4 py-3">Anggaran</th>
                                    <th scope="col" class="px-4 py-3">Dibelanjakan</th>
                                    <th scope="col" class="px-4 py-3">Pajak Wajib Lapor</th>
                                    <th scope="col" class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        Tidak ada data transaksi
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('bku.modal.tambah-modal')
@include('bku.modal.tarik-tunai-modal')
@include('bku.modal.setor-tunai-modal')
<style>
    body {
        background-color: #f8f9fa;
        font-size: 14px;
    }

    .card {
        border: 1px solid #e9ecef;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .text-muted-custom {
        color: #6c757d !important;
        font-size: 12px;
    }

    .text-blue {
        color: #0d6efd !important;
    }

    .btn-sm-custom {
        padding: 0.375rem 0.75rem;
        font-size: 13px;
    }

    .input-group-text-rp {
        background-color: transparent;
        border-right: none;
        color: #6c757d;
    }

    .form-control-rp {
        border-left: none;
        color: #0d6efd;
        font-weight: 500;
    }

    .table th {
        background-color: #f8f9fa;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 500;
        color: #6c757d;
    }

    .nav-tabs .nav-link {
        border: 1px solid #dee2e6;
        border-bottom: none;
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .anggaran-card {
        border-left: 4px solid #0d6efd;
    }

    .help-icon {
        width: 12px;
        height: 12px;
        color: #0d6efd;
    }
</style>
@endsection
@include('layouts.footer')