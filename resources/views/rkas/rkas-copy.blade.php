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
                        <button class="btn btn-success" id="btnTambah" data-bs-toggle="modal" data-bs-target="#tambahRkasModal"
                            style="font-size: 9pt;">
                            <i class="bi bi-plus me-2"></i>Tambah
                        </button>
                        @endif
                        <!-- Di rkas.blade.php -->
                        <form action="{{ route('rkas-perubahan.salin') }}" method="POST" class="d-inline" id="perubahanForm">
                            @csrf
                            <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                            <button type="submit" class="btn btn-outline-secondary" id="btnPerubahan" style="font-size: 9pt;">
                                <i class="bi bi-pencil me-2"></i>Perubahan
                            </button>
                        </form>
                        <button class="btn btn-outline-secondary" style="font-size: 9pt;">
                            <i class="bi bi-plus me-2"></i>Pergeseran
                        </button>
                        <a class="btn btn-primary" href="{{ route('rkas.rekapan', ['tahun' => $tahun]) }}" style="font-size: 9pt;">
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
                                        {{ isset($penganggaran) ? number_format($penganggaran->pagu_anggaran, 0, ',', '.') : '0' }}
                                    </h3>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-white-50" style="font-size: 11px;">Status: Aktif</span>
                                        <span class="badge bg-light text-dark"
                                            style="font-size: 10px; padding: 4px 8px;">{{ $penganggaran->tahun_anggaran }}</span>
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
                                            id="percentTahap1">{{ number_format(isset($paguAnggaranTahap1) && $paguAnggaranTahap1 > 0 && isset($totalTahap1) ? ($totalTahap1 / $paguAnggaranTahap1) * 100 : 0, 0) }}%</span>
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
                                                    {{ isset($paguAnggaranTahap1) ? number_format($paguAnggaranTahap1, 0, ',', '.') : '0' }}
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
                                                    {{ isset($totalTahap1) ? number_format($totalTahap1, 0, ',', '.') : '0' }}
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
                                            {{ isset($paguAnggaranTahap1) && isset($totalTahap1) ? number_format($paguAnggaranTahap1 - $totalTahap1, 0, ',', '.') : '0' }}</span>
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
                                            aria-valuenow="{{ $persentaseTahap1 }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
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
                                            id="percentTahap2">{{ number_format(isset($paguAnggaranTahap2) && $paguAnggaranTahap2 > 0 && isset($totalTahap2) ? ($totalTahap2 / $paguAnggaranTahap2) * 100 : 0, 0) }}%</span>
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
                                                    {{ isset($paguAnggaranTahap2) ? number_format($paguAnggaranTahap2, 0, ',', '.') : '0' }}
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
                                                    {{ isset($totalTahap2) ? number_format($totalTahap2, 0, ',', '.') : '0' }}
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
                                            {{ isset($paguAnggaranTahap2) && isset($totalTahap2) ? number_format($paguAnggaranTahap2 - $totalTahap2, 0, ',', '.') : '0' }}</span>
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
                                            aria-valuenow="{{ $persentaseTahap2 }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
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
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ strtolower($month) }}-tab"
                            data-bs-toggle="tab" data-bs-target="#{{ strtolower($month) }}" type="button"
                            role="tab" data-month="{{ $month }}">
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
                        role="tabpanel">
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
                                    <tbody id="table-body-{{ strtolower($month) }}">
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
                                                            {{ number_format($rkas->jumlah * $rkas->harga_satuan, 0, ',', '.') }}</strong>
                                                    </td>
                                                    <td style="font-size: 8pt; position: relative;">
                                                        <!-- Dropdown Action Button -->
                                                        <div class="dropdown" style="position: relative; z-index: 1050;">
                                                            <button
                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                type="button" id="actionDropdown{{ $rkas->id }}"
                                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                                style="border: 1px solid #dee2e6; background: white; color: #6c757d; font-size: 8pt; padding: 4px 8px; min-width: 40px;">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0"
                                                                aria-labelledby="actionDropdown{{ $rkas->id }}"
                                                                style="z-index: 1060; min-width: 120px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;">
                                                                <li>
                                                                    <a class="dropdown-item d-flex align-items-center"
                                                                        href="#"
                                                                        onclick="showDetailModal({{ $rkas->id }})"
                                                                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                                        <i class="bi bi-eye me-2 text-primary"></i>Detail
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item d-flex align-items-center" href="#" onclick="showSisipkanModal(
                                                                            {{ $rkas->kode_id }}, 
                                                                            '{{ $rkas->kodeKegiatan ? $rkas->kodeKegiatan->program : '-' }}', 
                                                                            '{{ $rkas->kodeKegiatan ? $rkas->kodeKegiatan->sub_program : '-' }}',
                                                                            {{ $rkas->kode_rekening_id }},
                                                                            '{{ $rkas->rekeningBelanja ? $rkas->rekeningBelanja->kode_rekening.' - '.$rkas->rekeningBelanja->rincian_objek : '-' }}'
                                                                        )" style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                                        <i class="bi bi-archive-fill me-2 text-warning"></i>Sisipkan
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item d-flex align-items-center"
                                                                        href="#"
                                                                        onclick="showEditModal({{ $rkas->id }})"
                                                                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                                        <i class="bi bi-pencil me-2 text-warning"></i>Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item d-flex align-items-center text-danger"
                                                                        href="#"
                                                                        onclick="showDeleteModal({{ $rkas->id }})"
                                                                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                                                                        <i class="bi bi-trash me-2"></i>Hapus
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
                                                        {{ number_format($rkasData[$month]->sum(function ($item) {return $item->jumlah * $item->harga_satuan;}),0,',','.') }}</strong>
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

    <!-- Modal Tambah RKAS -->
    <div class="modal fade" id="tambahRkasModal" tabindex="-1" aria-labelledby="tambahRkasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="tambahRkasModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Data RKAS
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="tambahRkasForm" method="POST" action="{{ route('rkas.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                        <!-- Progress Steps -->
                        <div class="progress-steps mb-4">
                            <div class="step-indicator">
                                <div class="step active" id="step-1">
                                    <span class="step-number">1</span>
                                    <span class="step-title">Kegiatan</span>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" id="step-2">
                                    <span class="step-number">2</span>
                                    <span class="step-title">Detail</span>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" id="step-3">
                                    <span class="step-number">3</span>
                                    <span class="step-title">Anggaran</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Kegiatan Selection -->
                        <div class="form-step" id="form-step-1">
                            <h6 class="mb-3"><i class="bi bi-bookmark me-2"></i>Pilih Kegiatan dan Rekening</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kegiatan" class="form-label">Kegiatan <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select select2-kegiatan" id="kegiatan" name="kode_id"
                                            required>
                                            <option value="">-- Pilih Kegiatan --</option>
                                            @foreach ($kodeKegiatans as $kode)
                                                <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                                    data-program="{{ $kode->program }}"
                                                    data-sub-program="{{ $kode->sub_program }}"
                                                    data-uraian="{{ $kode->uraian }}"
                                                    {{ old('kode_id') == $kode->id ? 'selected' : '' }}>
                                                    {{ $kode->kode }} - {{ $kode->uraian }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rekening_belanja" class="form-label">Rekening Belanja <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select select2-rekening" id="rekening_belanja"
                                            name="kode_rekening_id" required>
                                            <option value="">-- Pilih Rekening Belanja --</option>
                                            @foreach ($rekeningBelanjas as $rekening)
                                                <option value="{{ $rekening->id }}"
                                                    data-kode-rekening="{{ $rekening->kode_rekening }}"
                                                    data-rincian-objek="{{ $rekening->rincian_objek }}"
                                                    {{ old('kode_rekening_id') == $rekening->id ? 'selected' : '' }}>
                                                    {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Detail Information -->
                        <div class="form-step" id="form-step-2" style="display: none;">
                            <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>Detail Uraian dan Harga</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="uraian" class="form-label">Uraian <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" id="uraian" name="uraian" rows="4"
                                            placeholder="Jelaskan detail barang atau jasa yang akan diadakan..." required>{{ old('uraian') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="harga_satuan" class="form-label">Harga Satuan <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="harga_satuan"
                                                name="harga_satuan" placeholder="0" step="0.01" min="0"
                                                value="{{ old('harga_satuan') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Budget Planning -->
                        <div class="form-step" id="form-step-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Perencanaan Anggaran Bulanan
                                </h6>
                                <span class="badge bg-success fs-6" id="totalAnggaranDisplay">Total: Rp 0</span>
                            </div>

                            <!-- Dynamic Month Entries -->
                            <div id="bulanContainer">
                                <div class="month-entry border rounded p-3 mb-3" data-index="0">
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Bulan <span class="text-danger">*</span></label>
                                            <select class="form-select month-select" name="bulan[]" required>
                                                <option value="">Pilih Bulan</option>
                                                @foreach ($months as $month)
                                                    <option value="{{ $month }}">{{ $month }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control jumlah-input" name="jumlah[]"
                                                placeholder="0" min="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control satuan-input" name="satuan[]"
                                                placeholder="pcs, unit, buah" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Total</label>
                                            <div class="month-total badge bg-info w-100">Rp 0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Month Button -->
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary" id="addMonthBtn">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="prevStepBtn" style="display: none;">
                            <i class="bi bi-arrow-left me-2"></i>Sebelumnya
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="nextStepBtn">
                            Selanjutnya<i class="bi bi-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                            <i class="bi bi-check-circle me-2"></i>Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail RKAS -->
    <div class="modal fade" id="detailRkasModal" tabindex="-1" aria-labelledby="detailRkasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="detailRkasModalLabel">
                        <i class="bi bi-eye me-2"></i>Detail Data RKAS
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-group mb-3">
                                <label class="detail-label">Program Kegiatan:</label>
                                <div class="detail-value" id="detail-program"></div>
                            </div>
                            <div class="detail-group mb-3">
                                <label class="detail-label">Kegiatan:</label>
                                <div class="detail-value" id="detail-kegiatan"></div>
                            </div>
                            <div class="detail-group mb-3">
                                <label class="detail-label">Rekening Belanja:</label>
                                <div class="detail-value" id="detail-rekening"></div>
                            </div>
                            <div class="detail-group mb-3">
                                <label class="detail-label">Bulan:</label>
                                <div class="detail-value" id="detail-bulan"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-group mb-3">
                                <label class="detail-label">Uraian:</label>
                                <div class="detail-value" id="detail-uraian"></div>
                            </div>
                            <div class="detail-group mb-3">
                                <label class="detail-label">Jumlah:</label>
                                <div class="detail-value" id="detail-jumlah"></div>
                            </div>
                            <div class="detail-group mb-3">
                                <label class="detail-label">Satuan:</label>
                                <div class="detail-value" id="detail-satuan"></div>
                            </div>
                            <div class="detail-group mb-3">
                                <label class="detail-label">Harga Satuan:</label>
                                <div class="detail-value" id="detail-harga-satuan"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="detail-group mb-3">
                                <label class="detail-label">Total Anggaran:</label>
                                <div class="detail-value text-success fw-bold fs-5" id="detail-total"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="editFromDetail()">
                        <i class="bi bi-pencil me-2"></i>Edit Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit RKAS -->
    <div class="modal fade" id="editRkasModal" tabindex="-1" aria-labelledby="editRkasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editRkasModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit Data RKAS
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRkasForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-kegiatan" class="form-label">Kegiatan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2-kegiatan-edit" id="edit-kegiatan" name="kode_id"
                                        required>
                                        <option value="">-- Pilih Kegiatan --</option>
                                        @foreach ($kodeKegiatans as $kode)
                                            <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                                data-program="{{ $kode->program }}"
                                                data-sub-program="{{ $kode->sub_program }}"
                                                data-uraian="{{ $kode->uraian }}">
                                                {{ $kode->kode }} - {{ $kode->uraian }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-rekening-belanja" class="form-label">Rekening Belanja <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2-rekening-edit" id="edit-rekening-belanja"
                                        name="kode_rekening_id" required>
                                        <option value="">-- Pilih Rekening Belanja --</option>
                                        @foreach ($rekeningBelanjas as $rekening)
                                            <option value="{{ $rekening->id }}"
                                                data-kode-rekening="{{ $rekening->kode_rekening }}"
                                                data-rincian-objek="{{ $rekening->rincian_objek }}">
                                                {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-uraian" class="form-label">Uraian <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="edit-uraian" name="uraian" rows="4" required></textarea>
                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-bulan" class="form-label">Bulan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="edit-bulan" name="bulan" required>
                                        <option value="">-- Pilih Bulan --</option>
                                        @foreach ($months as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-harga-satuan" class="form-label">Harga Satuan <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="edit-harga-satuan"
                                            name="harga_satuan" placeholder="0" step="0.01" min="0" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-jumlah" class="form-label">Jumlah <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit-jumlah" name="jumlah"
                                        placeholder="0" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-satuan" class="form-label">Satuan <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-satuan" name="satuan"
                                        placeholder="pcs, unit, buah" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Total Anggaran:</strong> <span id="edit-total-display">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sisipkan RKAS -->
    <div class="modal fade" id="sisipkanRkasModal" tabindex="-1" aria-labelledby="sisipkanRkasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="sisipkanRkasModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Sisipkan Data RKAS
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
    
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
    
                @if ($errors->any()))
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
    
                <form id="sisipkanRkasForm" method="POST" action="{{ route('rkas.sisipkan') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                        <input type="hidden" id="sisipkan_kode_id" name="kode_id">
                        <input type="hidden" id="sisipkan_kode_rekening_id" name="kode_rekening_id">
                        <input type="hidden" id="sisipkan_bulan" name="bulan">
    
                        <!-- Program Kegiatan yang Disisipkan -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Program Kegiatan</label>
                                        <input type="text" class="form-control" id="sisipkan_program" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Kegiatan</label>
                                        <input type="text" class="form-control" id="sisipkan_kegiatan" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Rekening Belanja</label>
                                        <input type="text" class="form-control" id="sisipkan_rekening_belanja_display"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- Uraian -->
                        <div class="mb-3">
                            <label for="sisipkan_uraian" class="form-label">Uraian <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="sisipkan_uraian" name="uraian" rows="3"
                                required>{{ old('uraian') }}</textarea>
                        </div>
    
                        <!-- Jumlah dan Satuan -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sisipkan_jumlah" class="form-label">Jumlah <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="sisipkan_jumlah" name="jumlah" placeholder="0"
                                    min="1" value="{{ old('jumlah') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sisipkan_satuan" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sisipkan_satuan" name="satuan"
                                    placeholder="pcs, unit, buah" value="{{ old('satuan') }}" required>
                            </div>
                        </div>
    
                        <!-- Harga Satuan -->
                        <div class="mb-3">
                            <label for="sisipkan_harga_satuan" class="form-label">Harga Satuan <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="sisipkan_harga_satuan" name="harga_satuan"
                                    placeholder="0" step="0.01" min="0" value="{{ old('harga_satuan') }}" required>
                            </div>
                        </div>
    
                        <!-- Total Anggaran -->
                        <div class="alert alert-info">
                            <strong>Total Anggaran:</strong> <span id="sisipkan_total_display">Rp 0</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus RKAS -->
    <div class="modal fade" id="deleteRkasModal" tabindex="-1" aria-labelledby="deleteRkasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteRkasModalLabel">
                        <i class="bi bi-trash me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Apakah Anda yakin?</h4>
                        <p class="text-muted">Data RKAS yang dihapus tidak dapat dikembalikan!</p>

                        <div class="card bg-body-secondary border mt-3">
                            <div class="row ms-2 me-2">
                                <div class="col-6 text-start">
                                    <strong>Kegiatan:</strong><br>
                                    <span id="delete-kegiatan-info"></span>
                                </div>
                                <div class="col-6 text-start">
                                    <strong>Bulan:</strong><br>
                                    <span id="delete-bulan-info"></span>
                                </div>
                            </div>
                            <div class="row ms-2 me-2 mt-2">
                                <div class="col-12 text-start">
                                    <strong>Total Anggaran:</strong><br>
                                    <span class="text-danger fw-bold" id="delete-total-info"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-2"></i>Ya, Hapus Data
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    </style>
    <!-- JavaScript -->
    @push('scripts')
    <script src="{{ asset('assets/js/rkas.js') }}"></script>
    @endpush

    @push('scripts')
        <script>
            // Fungsi untuk menonaktifkan tabel dan tombol setelah perubahan
            function disableRkasAfterPerubahan() {
            // Nonaktifkan semua tabel
            const tableContainers = document.querySelectorAll('.rkas-table-container');
            tableContainers.forEach(container => {
            container.classList.add('table-disabled');
            });
            
            // Sembunyikan tombol tambah
            const btnTambah = document.getElementById('btnTambah');
            if (btnTambah) {
            btnTambah.style.display = 'none';
            }
            
            // Nonaktifkan semua dropdown aksi
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
            toggle.disabled = true;
            toggle.style.pointerEvents = 'none';
            toggle.style.opacity = '0.6';
            });
            }
            
            // Fungsi untuk mengecek status perubahan saat halaman dimuat
            function checkPerubahanStatusOnLoad() {
            const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
            
            if (!tahun) return;
            
            fetch(`/rkas-perubahan/check-status?tahun=${tahun}`, {
            method: 'GET',
            headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
            }
            })
            .then(response => response.json())
            .then(data => {
            if (data.success && data.has_perubahan) {
            disableRkasAfterPerubahan();
            }
            })
            .catch(error => {
            console.error('Error checking perubahan status:', error);
            });
            }
            
            // Panggil fungsi saat halaman dimuat
            document.addEventListener('DOMContentLoaded', function() {
            checkPerubahanStatusOnLoad();
            
            // Jika sudah ada status perubahan dari server, nonaktifkan langsung
            @if($hasPerubahan)
            disableRkasAfterPerubahan();
            @endif
            });
        // Fungsi untuk menonaktifkan tombol perubahan
        function disablePerubahanButton() {
        const btnPerubahan = document.getElementById('btnPerubahan');
        if (btnPerubahan && !btnPerubahan.disabled) {
        btnPerubahan.disabled = true;
        btnPerubahan.classList.add('disabled');
        btnPerubahan.title = 'RKAS Perubahan sudah dilakukan';
        
        // Tambahkan tooltip
        btnPerubahan.setAttribute('data-bs-toggle', 'tooltip');
        btnPerubahan.setAttribute('data-bs-placement', 'top');
        btnPerubahan.setAttribute('data-bs-title', 'Mohon Maaf Anda Telah Melakukan Perubahan, RKAS Perubahan hanya dapat dilakukan sekali, Terima Kasih Atas Antusias Anda');
        
        // Inisialisasi tooltip Bootstrap
        const existingTooltip = bootstrap.Tooltip.getInstance(btnPerubahan);
        if (existingTooltip) {
        existingTooltip.dispose();
        }
        new bootstrap.Tooltip(btnPerubahan);
        
        // Simpan status di localStorage
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        if (tahun) {
        localStorage.setItem('rkas_perubahan_done_' + tahun, 'true');
        }
        
        console.log('Tombol perubahan dinonaktifkan');
        }
        }
        
        // Fungsi untuk mengaktifkan tombol perubahan
        function enablePerubahanButton() {
            const btnPerubahan = document.getElementById('btnPerubahan');
            if (btnPerubahan && btnPerubahan.disabled) {
                btnPerubahan.disabled = false;
                btnPerubahan.classList.remove('disabled');
                btnPerubahan.removeAttribute('title');
                btnPerubahan.removeAttribute('data-bs-toggle');
                btnPerubahan.removeAttribute('data-bs-placement');
                btnPerubahan.removeAttribute('data-bs-title');
                
                // Hapus tooltip jika ada
                const tooltip = bootstrap.Tooltip.getInstance(btnPerubahan);
                if (tooltip) {
                    tooltip.dispose();
                }
                
                // Hapus status dari localStorage
                const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
                if (tahun) {
                    localStorage.removeItem('rkas_perubahan_done_' + tahun);
                }
            }
        }
        
        // Fungsi untuk sync status dengan server
        function syncPerubahanStatus() {
            const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
            
            if (!tahun) return;
        
            // Cek status dari server untuk memastikan sync
            fetch(`/rkas-perubahan/check-status?tahun=${tahun}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.has_perubahan) {
                        // Jika server mengatakan sudah ada perubahan, disable tombol
                        disablePerubahanButton();
                    } else {
                        // Jika server mengatakan belum ada perubahan, enable tombol
                        enablePerubahanButton();
                    }
                }
            })
            .catch(error => {
                console.error('Error syncing perubahan status:', error);
                // Fallback: gunakan localStorage status
                const isDoneInLocalStorage = localStorage.getItem('rkas_perubahan_done_' + tahun) === 'true';
                if (isDoneInLocalStorage) {
                    disablePerubahanButton();
                } else {
                    enablePerubahanButton();
                }
            });
        }

       // Fungsi untuk handle form submission dengan AJAX
    function handlePerubahanSubmission(event) {
    event.preventDefault();
    
    const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
    
    if (!tahun) {
    Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'Tahun anggaran tidak ditemukan',
    confirmButtonText: 'OK'
    });
    return;
    }
    
    console.log('Memeriksa status perubahan untuk tahun:', tahun);
    
    // Cek status perubahan terlebih dahulu sebelum menampilkan konfirmasi
    fetch(`{{ route('rkas-perubahan.check-status') }}?tahun=${tahun}`, {
    method: 'GET',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'X-Requested-With': 'XMLHttpRequest'
    }
    })
    .then(response => {
    console.log('Response status:', response.status);
    
    if (!response.ok) {
    // Jika response tidak ok, coba parse error message
    return response.json().then(errorData => {
    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }).catch(() => {
    throw new Error(`HTTP error! status: ${response.status}`);
    });
    }
    return response.json();
    })
    .then(data => {
    console.log('Response data:', data);
    
    if (!data.success) {
    throw new Error(data.message || 'Gagal memeriksa status perubahan');
    }
    
    if (data.has_perubahan) {
    // Jika sudah ada perubahan, tampilkan warning langsung
    Swal.fire({
    icon: 'warning',
    title: 'Perubahan Sudah Dilakukan',
    text: 'Mohon Maaf Anda Telah Melakukan Perubahan, RKAS Perubahan hanya dapat dilakukan sekali, Terima Kasih Atas Antusias Anda',
    confirmButtonText: 'Mengerti'
    });
    
    } else {
    // Jika belum ada perubahan, tampilkan konfirmasi
    showPerubahanConfirmation();
    }
    })
    .catch(error => {
    console.error('Error checking perubahan status:', error);
    Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'Terjadi kesalahan saat memeriksa status perubahan: ' + error.message,
    confirmButtonText: 'OK'
    });
    });
    }
        
        // Fungsi untuk menampilkan konfirmasi perubahan
        function showPerubahanConfirmation() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        
        Swal.fire({
        title: 'Konfirmasi Perubahan',
        text: 'Apakah Anda yakin ingin membuat RKAS Perubahan? Data RKAS saat ini akan disalin.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buat Perubahan',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: async () => {
        try {
        // Submit form menggunakan AJAX
        const formData = new FormData(document.getElementById('perubahanForm'));
        
        const response = await fetch('{{ route('rkas-perubahan.salin') }}', {
        method: 'POST',
        body: formData,
        headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
        }
        });
        
        // Handle response properly
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
        const data = await response.json();
        
        if (data.success) {
        return data;
        } else {
        // Tampilkan error message dari server
        throw new Error(data.message || 'Gagal membuat perubahan');
        }
        } else {
        // Handle non-JSON response
        const text = await response.text();
        throw new Error(`Unexpected response: ${text}`);
        }
        } catch (error) {
        Swal.showValidationMessage(
        `Error: ${error.message}`
        );
        return false;
        }
        },
        allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
        if (result.isConfirmed) {
        if (result.value && result.value.success) {
            // Nonaktifkan tombol dan tabel setelah berhasil
            disableRkasAfterPerubahan();
        
        Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: result.value.message || 'Data RKAS berhasil disalin ke RKAS Perubahan.',
        confirmButtonText: 'OK'
        }).then(() => {
        // Redirect ke halaman RKAS Perubahan
        if (result.value.redirect) {
        window.location.href = result.value.redirect;
        }
        });
        }
        }
        });
        }

        
        
        // Event listener untuk halaman RKAS
        document.addEventListener('DOMContentLoaded', function() {
            // Cek apakah kita berada di halaman RKAS
            const isRkasPage = window.location.pathname.includes('/rkas') && 
                              !window.location.pathname.includes('/rkas-perubahan');
            
            if (isRkasPage) {
                // Setup event listener untuk form perubahan
                const perubahanForm = document.getElementById('perubahanForm');
                if (perubahanForm) {
                    perubahanForm.addEventListener('submit', handlePerubahanSubmission);
                }
                
                // Sync status dengan server saat halaman dimuat
                syncPerubahanStatus();
                
                // Tangani pesan flash dari server
                @if(session('warning'))
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perubahan Sudah Dilakukan',
                        text: '{{ session('warning') }}',
                        confirmButtonText: 'Mengerti'
                    });
                @endif
        
                @if(session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: '{{ session('success') }}',
                        confirmButtonText: 'OK'
                    });
                @endif
                
            }
        });
        
        window.syncPerubahanStatus = syncPerubahanStatus;
        </script>
    @endpush
@endsection
