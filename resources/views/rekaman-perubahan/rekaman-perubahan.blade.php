@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<div class="content-area" id="contentArea">
    <div class="fade-in">
        {{-- Header Section --}}
        <div class="glass-card header-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('rkas-perubahan.index', ['tahun' => $tahun]) }}"
                            class="btn btn-back me-3">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <p class="subtitle-sm text-white fs-5 mt-3"><i class="bi bi-clock-history me-2"></i> Rekaman Rkas Perubahan BOSP REGULER {{ $penganggaran->tahun_anggaran }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <span class="badge badge-date">
                            <i class="bi bi-calendar-event me-1"></i>
                            {{ now()->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card card-total">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Total Perubahan</h6>
                            <h3>{{ $rekaman->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-create">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Penambahan</h6>
                            <h3>{{ $rekaman->where('action', 'create')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-update">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Perubahan</h6>
                            <h3>{{ $rekaman->where('action', 'update')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-delete">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="bi bi-trash"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Penghapusan</h6>
                            <h3>{{ $rekaman->where('action', 'delete')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($rekaman->isEmpty())
        {{-- Empty State --}}
        <div class="empty-state text-center py-5">
            <div class="empty-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h4 class="empty-title">Belum Ada Rekaman Perubahan</h4>
            <p class="empty-subtitle">Tidak ada perubahan yang tercatat untuk tahun {{ $tahun }}.</p>
        </div>
        @else
        {{-- Timeline Section --}}
        <div class="modern-timeline">
            @foreach($rekaman as $item)
            <div class="timeline-item">
                <div class="timeline-indicator 
                        @if($item->action == 'create') indicator-create
                        @elseif($item->action == 'update') indicator-update
                        @else indicator-delete @endif">
                    <i class="bi 
                            @if($item->action == 'create') bi-plus-lg
                            @elseif($item->action == 'update') bi-pencil
                            @else bi-trash @endif"></i>
                </div>
            
                <div class="timeline-card 
                        @if($item->action == 'create') card-create
                        @elseif($item->action == 'update') card-update
                        @else card-delete @endif">
            
                    {{-- Card Header --}}
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="action-badge">
                                @if($item->action == 'create')
                                <span class="badge bg-success">
                                    <i class="bi bi-plus-circle me-1"></i>Penambahan Data
                                </span>
                                @elseif($item->action == 'update')
                                <span class="badge bg-warning">
                                    <i class="bi bi-pencil-square me-1"></i>Perubahan Data
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-trash me-1"></i>Penghapusan Data
                                </span>
                                @endif
                            </div>
                            <span class="timestamp">
                                <i class="bi bi-clock me-1"></i>{{ $item->created_at->format('d M Y H:i') }}
                            </span>
                        </div>
                    </div>
            
                    {{-- Card Body --}}
                    <div class="card-body">
                        @if($item->action == 'create' || $item->action == 'delete')
                        {{-- Create/Delete View --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-field">
                                    <label><i class="bi bi-tag me-1"></i>Kode Kegiatan</label>
                                    <span class="value text-white">{{ $item->changes['kode_kegiatan'] ?? '-' }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-card-list me-1"></i>Program</label>
                                    <span class="value text-white">{{ $item->changes['program'] ?? '-' }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-list-check me-1"></i>Sub Program</label>
                                    <span class="value text-white">{{ $item->changes['sub_program'] ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field">
                                    <label><i class="bi bi-credit-card me-1"></i>Kode Rekening</label>
                                    <span class="value text-white">{{ $item->changes['kode_rekening'] ?? '-' }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-text-paragraph me-1"></i>Rincian Objek</label>
                                    <span class="value text-white">{{ $item->changes['rincian_objek'] ?? '-' }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-calendar me-1"></i>Bulan</label>
                                    <span class="badge bg-secondary">{{ $item->changes['bulan'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
            
                        <div class="info-field mt-3">
                            <label><i class="bi bi-chat-text me-1"></i>Uraian</label>
                            <p class="value mb-0 text-white">{{ $item->changes['uraian'] ?? '-' }}</p>
                        </div>
            
                        <div class="row g-2 mt-3">
                            <div class="col-md-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="bi bi-123"></i>
                                    </div>
                                    <div class="metric-info">
                                        <label>Jumlah</label>
                                        <span>{{ $item->changes['jumlah'] ?? '0' }} {{ $item->changes['satuan'] ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div class="metric-info">
                                        <label>Harga Satuan</label>
                                        <span>Rp {{ number_format($item->changes['harga_satuan'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card total-metric">
                                    <div class="metric-icon">
                                        <i class="bi bi-calculator"></i>
                                    </div>
                                    <div class="metric-info">
                                        <label>Total</label>
                                        <span>Rp {{ number_format($item->changes['total'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($item->action == 'update')
                        {{-- Update View --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="info-field">
                                    <label><i class="bi bi-tag me-1"></i>Kode Kegiatan</label>
                                    <span class="value text-dark">{{ $item->rkasPerubahan->kodeKegiatan->kode ??
                                        ($item->changes['from']['kode_kegiatan'] ?? '-') }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-card-list me-1"></i>Program</label>
                                    <span class="value text-dark">{{ $item->rkasPerubahan->kodeKegiatan->program ??
                                        ($item->changes['from']['program'] ?? '-') }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-list-check me-1"></i>Sub Program</label>
                                    <span class="value text-dark">{{ $item->rkasPerubahan->kodeKegiatan->sub_program ??
                                        ($item->changes['from']['sub_program'] ?? '-') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field">
                                    <label><i class="bi bi-credit-card me-1"></i>Kode Rekening</label>
                                    <span class="value text-dark">{{ $item->rkasPerubahan->rekeningBelanja->kode_rekening ??
                                        ($item->changes['from']['kode_rekening'] ?? '-') }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-text-paragraph me-1"></i>Rincian Objek</label>
                                    <span class="value text-dark">{{ $item->rkasPerubahan->rekeningBelanja->rincian_objek ??
                                        ($item->changes['from']['rincian_objek'] ?? '-') }}</span>
                                </div>
                                <div class="info-field">
                                    <label><i class="bi bi-calendar me-1"></i>Bulan</label>
                                    <span class="badge bg-secondary">{{ $item->rkasPerubahan->bulan ?? ($item->changes['from']['bulan'] ?? '-')
                                        }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-field mt-3">
                            <label><i class="bi bi-chat-text me-1"></i>Uraian</label>
                            <p class="value mb-0 text-white">{{ $item->rkasPerubahan->uraian ?? ($item->changes['from']['uraian'] ?? '-') }}</p>
                        </div>
                        
                        <div class="comparison-table">
                            <div class="table-header">
                                <i class="bi bi-arrow-left-right me-2"></i>
                                Perbandingan Perubahan
                            </div>
                            <div class="table-content">
                                <div class="comparison-row header">
                                    <div class="comparison-item">Item</div>
                                    <div class="comparison-value old-value">Sebelum</div>
                                    <div class="comparison-value new-value">Sesudah</div>
                                </div>
                        
                                {{-- Kode Kegiatan --}}
                                @if(($item->changes['from']['kode_kegiatan'] ?? null) != ($item->changes['to']['kode_kegiatan'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Kode Kegiatan</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['kode_kegiatan'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['kode_kegiatan'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Program --}}
                                @if(($item->changes['from']['program'] ?? null) != ($item->changes['to']['program'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Program</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['program'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['program'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Sub Program --}}
                                @if(($item->changes['from']['sub_program'] ?? null) != ($item->changes['to']['sub_program'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Sub Program</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['sub_program'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['sub_program'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Kode Rekening --}}
                                @if(($item->changes['from']['kode_rekening'] ?? null) != ($item->changes['to']['kode_rekening'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Kode Rekening</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['kode_rekening'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['kode_rekening'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Rincian Objek --}}
                                @if(($item->changes['from']['rincian_objek'] ?? null) != ($item->changes['to']['rincian_objek'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Rincian Objek</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['rincian_objek'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['rincian_objek'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Uraian --}}
                                @if(($item->changes['from']['uraian'] ?? null) != ($item->changes['to']['uraian'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Uraian</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['uraian'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['uraian'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Jumlah --}}
                                @if(($item->changes['from']['jumlah'] ?? null) != ($item->changes['to']['jumlah'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Jumlah</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['jumlah'] ?? '0' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['jumlah'] ?? '0' }}</div>
                                </div>
                                @endif
                        
                                {{-- Satuan --}}
                                @if(($item->changes['from']['satuan'] ?? null) != ($item->changes['to']['satuan'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Satuan</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['satuan'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['satuan'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Harga Satuan --}}
                                @if(($item->changes['from']['harga_satuan'] ?? null) != ($item->changes['to']['harga_satuan'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Harga Satuan</div>
                                    <div class="comparison-value old-value">
                                        Rp {{ number_format($item->changes['from']['harga_satuan'] ?? 0, 0, ',', '.') }}
                                    </div>
                                    <div class="comparison-value new-value">
                                        Rp {{ number_format($item->changes['to']['harga_satuan'] ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                        
                                {{-- Bulan --}}
                                @if(($item->changes['from']['bulan'] ?? null) != ($item->changes['to']['bulan'] ?? null))
                                <div class="comparison-row">
                                    <div class="comparison-item">Bulan</div>
                                    <div class="comparison-value old-value">{{ $item->changes['from']['bulan'] ?? '-' }}</div>
                                    <div class="comparison-value new-value">{{ $item->changes['to']['bulan'] ?? '-' }}</div>
                                </div>
                                @endif
                        
                                {{-- Total --}}
                                @php
                                $totalBefore = ($item->changes['from']['jumlah'] ?? 0) * ($item->changes['from']['harga_satuan'] ?? 0);
                                $totalAfter = ($item->changes['to']['jumlah'] ?? 0) * ($item->changes['to']['harga_satuan'] ?? 0);
                                @endphp
                                @if($totalBefore != $totalAfter)
                                <div class="comparison-row">
                                    <div class="comparison-item">Total</div>
                                    <div class="comparison-value old-value">
                                        Rp {{ number_format($totalBefore, 0, ',', '.') }}
                                    </div>
                                    <div class="comparison-value new-value">
                                        Rp {{ number_format($totalAfter, 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
            
                        {{-- Footer --}}
                        <div class="card-footer mt-3">
                            <div class="user-info">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="text-white">Diubah oleh <strong class="text-white">{{ $item->user->name
                                        }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<style>
    /* Global Styles */
    body {
        font-size: 10pt !important;
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    /* Glass Card Effect */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .title-gradient {
        background: linear-gradient(135deg, #fff 0%, #e0e7ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 12px;
        padding: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateX(-2px);
    }

    .badge-date {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    /* Stat Cards */
    .stat-card {
        border-radius: 16px;
        padding: 1.5rem;
        color: white;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .card-total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card-create {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .card-update {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .card-delete {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    }

    .stat-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }

    .stat-info h6 {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .stat-info h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    /* Timeline */
    .modern-timeline {
        position: relative;
        padding-left: 3rem;
    }

    .modern-timeline::before {
        content: '';
        position: absolute;
        left: 1.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #667eea, #764ba2, #4facfe, #fa709a, #ff6b6b);
        opacity: 0.3;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }

    .timeline-indicator {
        position: absolute;
        left: -3rem;
        top: 1rem;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        z-index: 2;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .indicator-create {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .indicator-update {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .indicator-delete {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    }

    .timeline-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border: none;
        transition: all 0.3s ease;
    }

    .timeline-card:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
    }

    .action-badge .badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    .timestamp {
        font-size: 0.9rem;
        color: #64748b;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Info Fields */
    .info-field {
        margin-bottom: 1rem;
    }

    .info-field label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .info-field .value {
        color: #6b7280;
        font-size: 0.95rem;
    }

    /* Metric Cards */
    .metric-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        height: 100%;
    }

    .total-metric {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .metric-icon {
        font-size: 1.5rem;
        opacity: 0.8;
    }

    .metric-info {
        flex: 1;
    }

    .metric-info label {
        display: block;
        font-size: 0.8rem;
        opacity: 0.8;
        margin-bottom: 0.25rem;
    }

    .metric-info span {
        font-weight: 600;
        font-size: 0.95rem;
    }

    /* Comparison Table */
    .comparison-table {
        background: #f8fafc;
        border-radius: 12px;
        overflow: hidden;
    }

    .table-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .table-content {
        padding: 0;
    }

    .comparison-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1px;
        background: #e2e8f0;
    }

    .comparison-row.header {
        background: #94a3b8;
    }

    .comparison-item,
    .comparison-value {
        padding: 0.75rem;
        background: white;
        font-size: 0.9rem;
    }

    .comparison-row.header .comparison-item,
    .comparison-row.header .comparison-value {
        background: #cbd5e1;
        font-weight: 600;
    }

    .old-value {
        color: #ef4444;
    }

    .new-value {
        color: #10b981;
    }

    /* Card Footer */
    .card-footer {
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
        margin-top: 1rem;
    }

    .user-info {
        display: flex;
        align-items: center;
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
    }

    .empty-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .empty-title {
        color: #374151;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modern-timeline {
            padding-left: 2rem;
        }

        .timeline-indicator {
            left: -2.5rem;
            width: 2rem;
            height: 2rem;
            font-size: 0.8rem;
        }

        .stat-content {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .comparison-row {
            grid-template-columns: 1fr;
        }

        .glass-card {
            padding: 1rem;
        }
    }
</style>
@endsection