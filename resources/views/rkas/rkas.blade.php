@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<!-- Content Area -->
<div class="content-area" id="contentArea">
    <div class="fade-in">
        <!-- Header Section -->
        <div class="rkas-header">
            <!-- Back Button -->
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('penganggaran.index') }}" class="btn btn-info text-white fw-bold me-2"
                    style="font-size: 9pt;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Penganggaran
                </a>
            </div>

            <!-- Hidden input untuk status perubahan -->
            {{-- <input type="hidden" id="perubahan-status" value="{{ $hasPerubahan ? 'done' : 'pending' }}"> --}}

            <!-- Title and Status -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="rkas-title fs-5" style="font-size: 9pt;">Rencana Kegiatan Anggaran Sekolah (RKAS)</h6>
                    <p class="rkas-subtitle">BOSP REGULER {{ $penganggaran->tahun_anggaran }}</p>
                </div>
                <!-- Action Buttons -->
                @php
                $hasPerubahan = isset($hasPerubahan) ? $hasPerubahan : false;
                @endphp
                <div class="rkas-actions mb-4 fs-6 btn btn-sm">
                    @if (!$hasPerubahan)
                    <button class="btn btn-success" id="btnTambah" data-bs-toggle="modal"
                        data-bs-target="#tambahRkasModal" style="font-size: 9pt;">
                        <i class="bi bi-plus me-2"></i>Tambah
                    </button>
                    @endif
                    <!-- Di rkas.blade.php -->
                    <form action="{{ route('rkas-perubahan.salin') }}" method="POST" class="d-inline"
                        id="perubahanForm">
                        @csrf
                        <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                        <button type="submit" class="btn btn-outline-secondary" id="btnPerubahan"
                            style="font-size: 9pt;">
                            <i class="bi bi-pencil me-2"></i>Perubahan
                        </button>
                    </form>
                    
                    <!-- Di bagian Action Buttons - setelah tombol Perubahan -->
                    @if (!$hasPerubahan)
                    @php
                    // Cek apakah ada data RKAS untuk tahun ini
                    $hasRkasData = isset($rkasData) && collect($rkasData)->flatten()->count() > 0;
                    @endphp
                    
                    @if (!$hasRkasData)
                    <button class="btn btn-info" id="btnSalinData" style="font-size: 9pt;" data-bs-toggle="tooltip"
                        data-bs-title="Salin data dari RKAS Perubahan tahun sebelumnya">
                        <i class="bi bi-copy me-2"></i>Salin Data
                    </button>
                    @endif
                    @endif

                    <button class="btn btn-outline-secondary" style="font-size: 9pt;">
                        <i class="bi bi-plus me-2"></i>Pergeseran
                    </button>
                    <a class="btn btn-primary" href="{{ route('rkas.rekapan', ['tahun' => $tahun]) }}"
                        style="font-size: 9pt;">
                        <i class="bi bi-printer"></i> Cetak
                    </a>
                </div>
            </div>

            <!-- Budget Info -->
            <div class="rkas-budget-info">
                <div class="row g-4">
                    <!-- Pagu Dana BOSP Reguler Card -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="budget-card-modern shadow-sm border-0"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 25px; position: relative; overflow: hidden; transition: all 0.3s ease;">
                            <div class="position-absolute"
                                style="top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.5;">
                            </div>
                            <div class="position-absolute"
                                style="bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%;">
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-wrapper me-3"
                                    style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-wallet2 text-white" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h6 class="text-white mb-1" style="font-size: 14px; font-weight: 600;">Pagu Dana
                                        BOSP</h6>
                                    <small class="text-white-50" style="font-size: 12px;">Total Anggaran Tahunan</small>
                                </div>
                            </div>
                            <div class="budget-amount-wrapper">
                                <h3 class="text-white mb-0"
                                    style="font-size: 20px; font-weight: 700; letter-spacing: -0.5px;">
                                    Rp
                                    {{ isset($penganggaran) ? number_format($penganggaran->pagu_anggaran, 0, ',', '.') :
                                    '0' }}
                                </h3>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-white-50" style="font-size: 11px;">Status: Aktif</span>
                                    <span class="badge bg-light text-dark" style="font-size: 10px; padding: 4px 8px;">{{
                                        $penganggaran->tahun_anggaran }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 1 Card -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="budget-card-modern shadow border tahap-card"
                            style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-radius: 20px; padding: 25px; position: relative; overflow: hidden; transition: all 0.3s ease; cursor: pointer; border: 2px solid #e3f2fd;"
                            onmouseover="this.style.transform='translateY(-5px) scale(1.02)'; this.style.boxShadow='0 15px 40px rgba(33, 150, 243, 0.2)'"
                            onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">
                            <div class="position-absolute"
                                style="top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, #2196f3, #03a9f4); border-radius: 50%; opacity: 0.1;">
                            </div>
                            <div class="position-absolute"
                                style="bottom: -30px; left: -30px; width: 100px; height: 100px; background: linear-gradient(135deg, #2196f3, #03a9f4); border-radius: 50%; opacity: 0.05;">
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper me-3"
                                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #2196f3, #03a9f4); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);">
                                        <i class="bi bi-calendar-range text-white" style="font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-dark mb-1" style="font-size: 16px; font-weight: 700;">Anggaran
                                            Tahap 1</h6>
                                        <small class="text-muted" style="font-size: 13px; font-weight: 500;">Januari -
                                            Juni (Semester
                                            1)</small>
                                    </div>
                                </div>
                                <div class="percentage-circle"
                                    style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #2196f3, #03a9f4); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);">
                                    <span class="text-white" style="font-size: 12px; font-weight: 700;"
                                        id="percentTahap1">{{ number_format(isset($paguAnggaranTahap1) &&
                                        $paguAnggaranTahap1 > 0 && isset($totalTahap1) ? ($totalTahap1 /
                                        $paguAnggaranTahap1) * 100 : 0, 0) }}%</span>
                                </div>
                            </div>

                            {{-- <div class="budget-details mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="detail-item p-3"
                                            style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; transition: all 0.2s ease;">
                                            <small class="text-muted"
                                                style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pagu
                                                Anggaran</small>
                                            <div class="text-dark"
                                                style="font-size: 13px; font-weight: 700; margin-top: 4px;">Rp
                                                {{ isset($paguAnggaranTahap1) ? number_format($paguAnggaranTahap1, 0,
                                                ',', '.') : '0' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-item p-3"
                                            style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; transition: all 0.2s ease;">
                                            <small class="text-muted"
                                                style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Dianggaran</small>
                                            <div class="text-dark"
                                                style="font-size: 13px; font-weight: 700; margin-top: 4px;"
                                                id="totalTahap1">Rp
                                                {{ isset($totalTahap1) ? number_format($totalTahap1, 0, ',', '.') : '0'
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="sisa-anggaran mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-dark" style="font-size: 14px; font-weight: 600;">Sisa
                                        Anggaran</span>
                                    <span class="text-dark" style="font-size: 14px; font-weight: 700;"
                                        id="sisaTahap1">Rp
                                        {{ isset($paguAnggaranTahap1) && isset($totalTahap1) ?
                                        number_format($paguAnggaranTahap1 - $totalTahap1, 0, ',', '.') : '0' }}</span>
                                </div>
                                @php
                                $persentaseTahap1 =
                                isset($paguAnggaranTahap1) && $paguAnggaranTahap1 > 0 && isset($totalTahap1)
                                ? ($totalTahap1 / $paguAnggaranTahap1) * 100
                                : 0;
                                @endphp
                                <div class="progress" style="height: 10px; border-radius: 10px; background: #e9ecef;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $persentaseTahap1 }}%; background: linear-gradient(90deg, #2196f3 0%, #03a9f4 100%); border-radius: 10px; transition: width 0.6s ease;"
                                        aria-valuenow="{{ $persentaseTahap1 }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button class="btn btn-sm"
                                    style="background: linear-gradient(135deg, #2196f3, #03a9f4); color: white; border: none; border-radius: 10px; font-size: 12px; font-weight: 600; padding: 8px 16px; box-shadow: 0 2px 10px rgba(33, 150, 243, 0.3);"
                                    onclick="showTahapDetail(1)">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tahap 2 Card -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="budget-card-modern shadow border tahap-card"
                            style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-radius: 20px; padding: 25px; position: relative; overflow: hidden; transition: all 0.3s ease; cursor: pointer; border: 2px solid #e8f5e8;"
                            onmouseover="this.style.transform='translateY(-5px) scale(1.02)'; this.style.boxShadow='0 15px 40px rgba(76, 175, 80, 0.2)'"
                            onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">
                            <div class="position-absolute"
                                style="top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, #4caf50, #66bb6a); border-radius: 50%; opacity: 0.1;">
                            </div>
                            <div class="position-absolute"
                                style="bottom: -30px; left: -30px; width: 100px; height: 100px; background: linear-gradient(135deg, #4caf50, #66bb6a); border-radius: 50%; opacity: 0.05;">
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper me-3"
                                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #4caf50, #66bb6a); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);">
                                        <i class="bi bi-calendar-check text-white" style="font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-dark mb-1" style="font-size: 16px; font-weight: 700;">
                                            Anggaran Tahap 2</h6>
                                        <small class="text-muted" style="font-size: 13px; font-weight: 500;">Juli -
                                            Desember
                                            (Semester 2)</small>
                                    </div>
                                </div>
                                <div class="percentage-circle"
                                    style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #4caf50, #66bb6a); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);">
                                    <span class="text-white" style="font-size: 12px; font-weight: 700;"
                                        id="percentTahap2">{{ number_format(isset($paguAnggaranTahap2) &&
                                        $paguAnggaranTahap2 > 0 && isset($totalTahap2) ? ($totalTahap2 /
                                        $paguAnggaranTahap2) * 100 : 0, 0) }}%</span>
                                </div>
                            </div>

                            {{-- <div class="budget-details mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="detail-item p-3"
                                            style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; transition: all 0.2s ease;">
                                            <small class="text-muted"
                                                style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pagu
                                                Anggaran</small>
                                            <div class="text-dark"
                                                style="font-size: 13px; font-weight: 700; margin-top: 4px;">Rp
                                                {{ isset($paguAnggaranTahap2) ? number_format($paguAnggaranTahap2, 0,
                                                ',', '.') : '0' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-item p-3"
                                            style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; transition: all 0.2s ease;">
                                            <small class="text-muted"
                                                style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Dianggaran</small>
                                            <div class="text-dark"
                                                style="font-size: 13px; font-weight: 700; margin-top: 4px;"
                                                id="totalTahap2">Rp
                                                {{ isset($totalTahap2) ? number_format($totalTahap2, 0, ',', '.') : '0'
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="sisa-anggaran mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-dark" style="font-size: 14px; font-weight: 600;">Sisa
                                        Anggaran</span>
                                    <span class="text-dark" style="font-size: 14px; font-weight: 700;"
                                        id="sisaTahap2">Rp
                                        {{ isset($paguAnggaranTahap2) && isset($totalTahap2) ?
                                        number_format($paguAnggaranTahap2 - $totalTahap2, 0, ',', '.') : '0' }}</span>
                                </div>
                                @php
                                $persentaseTahap2 =
                                isset($paguAnggaranTahap2) && $paguAnggaranTahap2 > 0 && isset($totalTahap2)
                                ? ($totalTahap2 / $paguAnggaranTahap2) * 100
                                : 0;
                                @endphp
                                <div class="progress" style="height: 10px; border-radius: 10px; background: #e9ecef;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $persentaseTahap2 }}%; background: linear-gradient(90deg, #4caf50 0%, #66bb6a 100%); border-radius: 10px; transition: width 0.6s ease;"
                                        aria-valuenow="{{ $persentaseTahap2 }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button class="btn btn-sm"
                                    style="background: linear-gradient(135deg, #4caf50, #66bb6a); color: white; border: none; border-radius: 10px; font-size: 12px; font-weight: 600; padding: 8px 16px; box-shadow: 0 2px 10px rgba(76, 175, 80, 0.3);"
                                    onclick="showTahapDetail(2)">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Section -->
    <div class="rkas-period-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="period-title">Periode Anggaran {{ $penganggaran->tahun_anggaran }}</h3>
            <!-- Pastikan form search ini ada -->
            <form method="GET" action="#" class="ms-3" id="SearchForm">
                <div class="search-box position-relative">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control" id="SearchInput" name="search" placeholder="Cari..."
                        value="{{ request('search') }}" autocomplete="off" title="Cari data berdasarkan kata kunci">
                    <div class="search-loading d-none position-absolute"
                        style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </form>
            <div class="period-budget">
                <span class="period-label">Dianggaran</span>
                <span class="period-amount" id="totalDianggaran">
                    Rp {{ isset($totalBudget) ? number_format($totalBudget, 0, ',', '.') : '0' }}
                </span>
            </div>
        </div>

        <!-- Month Tabs -->
        <ul class="nav nav-tabs rkas-month-tabs" id="monthTabs" role="tablist" style="font-size: 9pt;">
            @php
            $months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
            ];
            @endphp
            @foreach ($months as $index => $month)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ strtolower($month) }}-tab" data-bs-toggle="tab"
                    data-bs-target="#{{ strtolower($month) }}" type="button" role="tab" data-month="{{ $month }}"
                    aria-controls="{{ strtolower($month) }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                    {{ $month }}
                    @if (isset($rkasData[$month]) && $rkasData[$month]->count() > 0)
                    <span class="badge bg-success ms-2">{{ $rkasData[$month]->count() }}</span>
                    @endif
                </button>
            </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="monthTabsContent">
            @foreach ($months as $index => $month)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ strtolower($month) }}"
                role="tabpanel" aria-labelledby="{{ strtolower($month) }}-tab">
                <!-- RKAS Table -->
                <div class="rkas-table-container {{ $hasPerubahan ? 'table-disabled' : '' }}">
                    <div class="table-responsive">
                        <table class="table rkas-table" style="font-size: 8pt;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Program Kegiatan</th>
                                    <th>Kegiatan</th>
                                    <th>Rekening Belanja</th>
                                    <th>Uraian</th>
                                    <th>Dianggaran</th>
                                    <th>Dibelanjakan</th>
                                    <th>Satuan</th>
                                    <th>Harga Satuan</th>
                                    <th>Total</th>
                                    <th width="80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body-{{ strtolower($month) }}" class="rkas-table-body">
                                @if (isset($rkasData[$month]) && $rkasData[$month]->count() > 0)
                                @foreach ($rkasData[$month] as $key => $rkas)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $rkas->kodeKegiatan ? $rkas->kodeKegiatan->program : '-' }}
                                    </td>
                                    <td>{{ $rkas->kodeKegiatan ? $rkas->kodeKegiatan->sub_program : '-' }}
                                    </td>
                                    <td>
                                        @if ($rkas->rekeningBelanja)
                                        {{ $rkas->rekeningBelanja->rincian_objek }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>{{ $rkas->uraian }}</td>
                                    <td>{{ $rkas->jumlah }}</td>
                                    <td>0</td>
                                    <td>{{ $rkas->satuan }}</td>
                                    <td>Rp {{ number_format($rkas->harga_satuan, 0, ',', '.') }}</td>
                                    <td><strong>Rp
                                            {{ number_format($rkas->jumlah * $rkas->harga_satuan, 0, ',', '.')
                                            }}</strong>
                                    </td>
                                    <td style="font-size: 8pt; position: relative;">
                                        <!-- Dropdown Action Button -->
                                        <div class="dropdown" style="position: relative; z-index: 1050;">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button" id="actionDropdown{{ $rkas->id }}"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                style="border: 1px solid #dee2e6; background: white; color: #6c757d; font-size: 8pt; padding: 4px 8px; min-width: 40px;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0"
                                                aria-labelledby="actionDropdown{{ $rkas->id }}"
                                                style="z-index: 1060; min-width: 120px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="#"
                                                        onclick="showDetailModal({{ $rkas->id }})"
                                                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                        <i class="bi bi-eye me-2 text-primary"></i>Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <!-- Di bagian dropdown menu action - GANTI SEMUA TOMBOL SISIPKAN -->
                                                    <a class="dropdown-item d-flex align-items-center sisipkan-btn"
                                                        href="#" data-kode-id="{{ $rkas->kode_id }}"
                                                        data-program="{{ $rkas->kodeKegiatan ? $rkas->kodeKegiatan->program : '-' }}"
                                                        data-kegiatan="{{ $rkas->kodeKegiatan ? $rkas->kodeKegiatan->sub_program : '-' }}"
                                                        data-rekening-id="{{ $rkas->kode_rekening_id }}"
                                                        data-rekening-display="{{ $rkas->rekeningBelanja ? $rkas->rekeningBelanja->kode_rekening.' - '.$rkas->rekeningBelanja->rincian_objek : '-' }}"
                                                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                        <i class="bi bi-archive-fill me-2 text-warning"></i>Sisipkan
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="#"
                                                        onclick="showEditModal({{ $rkas->id }})"
                                                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                        <i class="bi bi-pencil me-2 text-warning"></i>Edit
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-info">
                                    <td colspan="9" class="text-end"><strong>Total
                                            {{ $month }}:</strong></td>
                                    <td><strong>Rp
                                            {{ number_format($rkasData[$month]->sum(function ($item) {return
                                            $item->jumlah * $item->harga_satuan;}),0,',','.') }}</strong>
                                    </td>
                                    <td></td>
                                </tr>
                                @else
                                <tr class="no-data-row">
                                    <td colspan="11" class="text-center text-muted">
                                        <div class="py-4">
                                            <i class="bi bi-inbox display-4"></i>
                                            <p class="mt-2">Belum ada data untuk bulan
                                                {{ $month }}</p>
                                            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#tambahRkasModal">
                                                <i class="bi bi-plus me-2"></i>Tambah Data
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>

@include('rkas.modal.tambah-rkas')
@include('rkas.modal.detail-rkas')
@include('rkas.modal.edit-rkas')
@include('rkas.modal.sisipkan-rkas')
@include('rkas.modal.delete-rkas')

<!-- CSS Styles -->
<style>
    /* Fix untuk tabel RKAS dan dropdown aksi */
    .rkas-table-container {
        overflow: visible !important;
    }

    .rkas-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 1rem;
        background-color: white;
        border-radius: 8px;
        overflow: visible !important;
    }

    .rkas-table thead th {
        position: sticky;
        top: 0;
        background: #007bff;
        color: white;
        font-weight: 600;
        border: none;
        padding: 8px 12px;
        font-size: 8pt;

    }

    .rkas-table tbody td {
        padding: 8px 12px;
        vertical-align: middle;
        font-size: 8pt;
        position: relative;
        overflow: visible !important;
    }

    .rkas-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Fix untuk dropdown aksi */
    .dropdown {
        position: static !important;
    }

    .dropdown-menu {
        position: absolute !important;
        right: 0;
        left: auto;
        z-index: 1000 !important;
        min-width: 120px;
        margin-top: 0;
        border: 1px solid rgba(0, 0, 0, .15);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .dropdown-toggle::after {
        display: none;
    }

    /* Memastikan kolom aksi cukup lebar */
    .rkas-table th:nth-child(11),
    .rkas-table td:nth-child(11) {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
        white-space: nowrap;
    }

    /* Memastikan konten tabel tidak overflow */
    .rkas-table td {
        max-width: 200px;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Responsive table */
    @media (max-width: 768px) {
        .rkas-table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .rkas-table {
            min-width: 800px;
        }
    }

    /* Progress Steps Styling */
    .progress-steps {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: #007bff;
        color: white;
    }

    .step.completed .step-number {
        background: #28a745;
        color: white;
    }

    .step-title {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
    }

    .step.active .step-title {
        color: #007bff;
        font-weight: 600;
    }

    .step-line {
        width: 100px;
        height: 2px;
        background: #dee2e6;
        margin: 0 10px;
    }

    .form-step {
        min-height: 300px;
    }

    .month-entry {
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .month-entry:hover {
        background: #e9ecef;
    }

    .btn-remove-month {
        margin-top: 25px;
    }

    .month-total {
        padding: 8px;
        font-size: 12px;
    }

    /* Table Styling */
    .rkas-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .rkas-table thead th {
        background: #007bff;
        color: white;
        font-weight: 600;
        border: none;
    }

    .rkas-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Budget Card Styling */
    .budget-card {
        /* background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); */
        color: white;
        padding: 1rem;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .budget-label {
        font-size: 0.9rem;
        /* opacity: 0.9; */
    }

    .budget-amount {
        font-size: 1.2rem;
        font-weight: bold;
    }

    /* Tab Styling */
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        background: #007bff;
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .nav-tabs .nav-link:hover {
        border: none;
        background: #e9ecef;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Detail Modal Styles */
    .detail-group {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .detail-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 5px;
        display: block;
    }

    .detail-value {
        color: #212529;
        font-size: 1rem;
        min-height: 20px;
    }

    /* Dropdown Styles */
    .dropdown-toggle::after {
        display: none;
    }

    .dropdown-menu {
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
        color: #721c24 !important;
    }

    /* Select2 Custom Table Styles */
    .select2-container {
        width: 100% !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .select2-results__option {
        padding: 0 !important;
        margin: 0 !important;
    }

    .select2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .select2-table th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 2px solid #dee2e6;
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .select2-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
        word-wrap: break-word;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .select2-table tr:hover {
        background-color: #f8f9fa;
    }

    .select2-results__option--highlighted .select2-table tr {
        background-color: #007bff !important;
        color: white;
    }

    .select2-results__option--highlighted .select2-table th {
        background-color: #0056b3 !important;
        color: white;
    }

    .select2-table-kode {
        width: 80px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #000000;
    }

    .select2-table-program {
        width: 150px;
        font-weight: 500;
    }

    .select2-table-sub-program {
        width: 150px;
    }

    .select2-table-uraian {
        min-width: 200px;
        white-space: normal;
        line-height: 1.4;
    }

    .select2-table-rekening {
        width: 100px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #000000;
    }

    .select2-table-rincian {
        min-width: 250px;
        white-space: normal;
        line-height: 1.4;
    }

    /* Select2 Search */
    .select2-search--dropdown {
        padding: 8px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px 12px;
        width: 100%;
    }

    /* Select2 Selection */
    .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-selection__rendered {
        line-height: calc(2.25rem) !important;
        padding-left: 12px !important;
        color: #495057 !important;
    }

    .select2-selection__arrow {
        height: calc(2.25rem) !important;
        right: 10px !important;
    }

    /* Focus states */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {

        .select2-table th,
        .select2-table td {
            padding: 6px 8px;
            font-size: 0.8rem;
        }

        .select2-table-kode {
            width: 60px;
        }

        .select2-table-program,
        .select2-table-sub-program {
            width: 120px;
        }

        .select2-table-uraian,
        .select2-table-rincian {
            min-width: 150px;
        }
    }

    /* Fix dropdown menu z-index and overflow issues */
    .table-responsive {
        overflow: visible !important;
    }

    .rkas-table-container {
        overflow: visible !important;
    }

    .dropdown-menu {
        z-index: 1 !important;
        position: absolute !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
        border-radius: 8px !important;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa !important;
        transform: translateX(2px);
    }

    .dropdown-toggle::after {
        display: none;
    }

    /* Ensure table cells don't clip dropdown */
    .rkas-table td {
        overflow: visible !important;
        position: relative;
    }

    /* Fix card hover effects */
    .detail-item:hover {
        background: #e9ecef !important;
        transform: translateY(-1px);
    }

    /* Smooth transitions for all interactive elements */
    .btn,
    .dropdown-item,
    .detail-item {
        transition: all 0.2s ease !important;
    }

    /* Ensure dropdown stays on top of everything */
    .dropdown.show .dropdown-menu {
        z-index: 1070 !important;
    }

    /* Style untuk tombol yang dinonaktifkan */
    .btn.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Style untuk tooltip */
    .tooltip {
        font-size: 9pt;
        max-width: 300px;
    }

    .tooltip-inner {
        text-align: center;
        padding: 8px 12px;
        background-color: #ffc107;
        color: #000;
        border-radius: 6px;
    }

    .bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow::before,
    .bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #ffc107;
    }

    /* Di bagian CSS yang sudah ada, tambahkan: */
    .table-disabled {
        opacity: 0.6;
        pointer-events: none;
        user-select: none;
    }

    .table-disabled .btn {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .table-disabled .dropdown-menu {
        display: none !important;
    }

    .btn.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }

    .satuan-input:read-only {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }

    .satuan-input:read-only:focus {
        border-color: #e9ecef;
        box-shadow: none;
    }

    .search-loading {
    z-index: 10;
    }
    
    #SearchInput:read-only {
    background-color: #f8f9fa;
    opacity: 0.7;
    }
    
    .search-result-row {
    animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
    }

    /* Search Loading Styles */
.search-loading .search-icon {
    opacity: 0.5 !important;
}

.search-loading .form-control {
    background-color: #f8f9fa !important;
    opacity: 0.7;
    cursor: not-allowed !important;
}

.search-table-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* Skeleton Loading Styles */
.skeleton-row {
    animation: pulse 1.5s ease-in-out infinite;
}

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
}

.skeleton-text {
    height: 12px;
    margin: 4px 0;
}

.skeleton-circle {
    height: 30px;
    width: 30px;
    border-radius: 50%;
    margin: 0 auto;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Search result animation */
.search-result-row {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading spinner customization */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Pastikan hanya satu tab yang aktif */
#monthTabsContent .tab-pane {
    display: none;
}

#monthTabsContent .tab-pane.show.active {
    display: block;
}

/* Force hide inactive tabs */
#monthTabsContent .tab-pane:not(.show):not(.active) {
    display: none !important;
}
</style>
<!-- JavaScript -->
@push('scripts')
<script src="{{ asset('assets/js/RkasManager.js') }}"></script>
<script src="{{ asset('assets/js/ButtonRkasPerubahan.js') }}"></script>
<script src="{{ asset('assets/js/rkas.js') }}"></script>
<script>
    // Inisialisasi ButtonRkasPerubahan Manager
    document.addEventListener('DOMContentLoaded', function() {
        // Buat instance ButtonRkasPerubahan
        window.buttonRkasPerubahan = new ButtonRkasPerubahan();
        
        // Inisialisasi semua komponen
        window.buttonRkasPerubahan.initialize();
        
        // Export fungsi global untuk kompatibilitas
        window.syncPerubahanStatus = function() {
            if (window.buttonRkasPerubahan) {
                window.buttonRkasPerubahan.syncStatus();
            }
        };
    });

    // Handle SweetAlert untuk flash messages
    @if(session('warning'))
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Perubahan Sudah Dilakukan',
            text: '{{ session('warning') }}',
            confirmButtonText: 'Mengerti'
        });
    });
    @endif

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            confirmButtonText: 'OK'
        });
    });
    @endif
</script>
@endpush
@endsection