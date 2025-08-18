@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<div class="content-area" id="contentArea">
    <div class="fade-in">
        <div class="rkas-header">
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('penganggaran.rkas-perubahan.index', ['tahun' => $tahun]) }}"
                    class="btn btn-info text-white fw-bold me-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke RKAS Perubahan
                </a>
            </div>

            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="rkas-title fs-5">Rekaman Perubahan RKAS</h6>
                    <p class="rkas-subtitle">BOSP REGULER {{ $penganggaran->tahun_anggaran }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($rekaman as $item)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            @if($item->action == 'update')
                            <i class="bi bi-pencil-square text-warning"></i> Perubahan Data
                            @else
                            <i class="bi bi-trash text-danger"></i> Penghapusan Data
                            @endif
                        </h6>
                        <span class="badge bg-secondary">{{ $item->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Kode Kegiatan:</strong>
                            {{ $item->rkasPerubahan->rkas->kodeKegiatan->kode ?? '-' }} -
                            {{ $item->rkasPerubahan->rkas->kodeKegiatan->uraian ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>Rekening Belanja:</strong>
                            {{ $item->rkasPerubahan->rkas->rekeningBelanja->kode_rekening ?? '-' }} -
                            {{ $item->rkasPerubahan->rkas->rekeningBelanja->rincian_objek ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>Uraian:</strong> {{ $item->rkasPerubahan->rkas->uraian }}
                        </div>

                        @if($item->action == 'update')
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Sebelum</th>
                                        <th>Sesudah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->changes['from'] as $key => $value)
                                    @if($value != $item->changes['to'][$key])
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                        <td>
                                            @if($key == 'harga_satuan')
                                            Rp {{ number_format($value, 0, ',', '.') }}
                                            @else
                                            {{ $value ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($key == 'harga_satuan')
                                            Rp {{ number_format($item->changes['to'][$key], 0, ',', '.') }}
                                            @else
                                            {{ $item->changes['to'][$key] ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-danger">
                            Data ini telah dihapus dari RKAS Perubahan
                        </div>
                        @endif

                        <div class="text-end text-muted small">
                            Diubah oleh: {{ $item->user->name }} pada {{ $item->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection