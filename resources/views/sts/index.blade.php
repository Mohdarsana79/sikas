@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/sts.css') }}">
@endpush
<div class="container-fluid px-0">
    <!-- Page Header -->
    <header class="sts-page-header">
        <div class="container text-center">
            <h1 class="fw-bold mb-2">Manajemen STS</h1>
            <p class="opacity-75 mb-4">Catatkan STS Pembayaran Jasa Giro Anda Untuk Mempermudah Menejemen BKU</p>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mx-auto sts-btn-group-header"
                style="max-width: 1000px; width: 95%;">
                <button class="sts-btn-header sts-btn-info" id="btnInfo" data-bs-toggle="modal" data-bs-target="#modalInfoSTS">
                    <i class="bi bi-info-circle"></i>Informasi
                </button>
            
                <div class="sts-action-buttons">
                    <button class="sts-btn-header sts-btn-pay" id="btnBayar">
                        <i class="bi bi-cash-stack"></i>Bayar
                    </button>
                    <button class="sts-btn-header sts-btn-edit-pay" id="btnEditBayar">
                        <i class="bi bi-currency-exchange"></i>Edit Bayar
                    </button>
                    <button class="sts-btn-header sts-btn-edit" id="btnEdit">
                        <i class="bi bi-pencil-square"></i>Edit STS
                    </button>
                    <button class="sts-btn-header sts-btn-delete" id="btnHapus">
                        <i class="bi bi-trash"></i>Hapus
                    </button>
                    <button class="sts-btn-header sts-btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahSTS"
                        id="btnTambah">
                        <i class="bi bi-plus-lg"></i>Tambah STS
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container sts-main-content pb-5">
        <div class="card sts-card-custom">
            <div class="card-body sts-table-container">
                <div class="table-responsive">
                    <table class="table align-middle" id="stsTable">
                        <thead>
                            <tr>
                                <th class="col-selection" style="width: 50px;">Pilih</th>
                                <th class="text-center" style="width: 60px;">No</th>
                                <th>STS</th>
                                <th>Tahun</th>
                                <th>Jml STS</th>
                                <th>Bayar</th>
                                <th>Tgl Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formattedSts as $index => $item)
                            <tr class="sts-row" data-id="{{ $item['id'] }}">
                                <td class="col-selection">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="{{ $item['id'] }}">
                                </td>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td>{{ $item['nomor_sts'] }}</td>
                                <td>{{ $item['tahun'] }}</td>
                                <td>Rp {{ $item['jumlah_sts'] }}</td>
                                <td>Rp {{ $item['jumlah_bayar'] }}</td>
                                <td class="text-muted">
                                    @if($item['tanggal_bayar'] != '-')
                                    <i class="bi bi-calendar-check me-1"></i> {{ $item['tanggal_bayar'] }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $item['badge'] }}">{{ $item['status'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    @include('sts.modal.tambah-sts')
    @include('sts.modal.edit-sts')
    @include('sts.modal.bayar-sts')
    @include('sts.modal.edit-bayar-sts')
    @include('sts.modal.informasi-sts')
</div>

{{-- @push('scripts')
<script type="module">
    console.log('🔄 STS Module script loaded');
    
    // Debug: cek elemen penting
    console.log('🔍 Checking important elements:');
    console.log('- Modal Tambah STS:', document.getElementById('modalTambahSTS'));
    console.log('- Select Tambah:', document.querySelector('#formTambahSTS select[name="penganggaran_id"]'));
    console.log('- Modal Edit STS:', document.getElementById('modalEditSTS'));
    console.log('- Select Edit:', document.querySelector('#formEditSTS select[name="penganggaran_id"]'));
    console.log('- STS Table:', document.getElementById('stsTable'));
    
    // Test API endpoint
    console.log('🔧 Testing API endpoint...');
    fetch('/sts/api/penganggaran')
        .then(response => {
            console.log('API Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response data:', data);
        })
        .catch(error => {
            console.error('API Error:', error);
        });
</script>
@endpush --}}
@endsection
@include('layouts.footer')