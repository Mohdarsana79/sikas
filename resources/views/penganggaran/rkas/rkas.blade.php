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
                        <p class="rkas-subtitle">BOSP REGULER 2025</p>
                    </div>
                    <!-- Action Buttons -->
                    <div class="rkas-actions mb-4 fs-6 btn btn-sm">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahRkasModal"
                            style="font-size: 9pt;">
                            <i class="bi bi-plus me-2"></i>Tambah
                        </button>
                        <button class="btn btn-outline-secondary" style="font-size: 9pt;">
                            <i class="bi bi-pencil me-2"></i>Perubahan
                        </button>
                        <button class="btn btn-outline-secondary" style="font-size: 9pt;">
                            <i class="bi bi-plus me-2"></i>Pergeseran
                        </button>
                        <button class="btn btn-outline-secondary" style="font-size: 9pt;">
                            <i class="bi bi-printer me-2"></i>Cetak
                        </button>
                        <a class="btn btn-primary" target="_blank" href="{{ route('penganggaran.rkas.rekapan') }}" style="font-size: 9pt;">Review</a>
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
                                            style="font-size: 10px; padding: 4px 8px;">2025</span>
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

                                <div class="budget-details mb-3">
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
                                </div>

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

                                <div class="budget-details mb-3">
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
                                </div>

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
                <h3 class="period-title">Periode Anggaran 2025</h3>
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
                        <div class="rkas-table-container">
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

                <!-- Display Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
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

                <form id="tambahRkasForm" method="POST" action="{{ route('penganggaran.rkas.store') }}">
                    @csrf
                    <div class="modal-body">
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
                                            name="harga_satuan" placeholder="0" step="0.01" min="0" required>
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

                        <div class="alert alert-light border mt-3">
                            <div class="row">
                                <div class="col-6 text-start">
                                    <strong>Kegiatan:</strong><br>
                                    <span id="delete-kegiatan-info"></span>
                                </div>
                                <div class="col-6 text-start">
                                    <strong>Bulan:</strong><br>
                                    <span id="delete-bulan-info"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
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
    </style>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            let monthIndex = 1;
            let currentRkasId = null;

            console.log('RKAS Page with Select2 Table Templates Initialized');

            // Initialize Select2 with custom templates
            initializeSelect2();

            // Get DOM elements
            const nextBtn = document.getElementById('nextStepBtn');
            const prevBtn = document.getElementById('prevStepBtn');
            const submitBtn = document.getElementById('submitBtn');
            const addMonthBtn = document.getElementById('addMonthBtn');

            // Next button click handler
            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (validateCurrentStep()) {
                        if (currentStep < 3) {
                            currentStep++;
                            showStep(currentStep);
                        }
                    }
                });
            }

            // Previous button click handler
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (currentStep > 1) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            }

            // Add month button
            if (addMonthBtn) {
                addMonthBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    addMonthEntry();
                });
            }

            // Remove month functionality (event delegation)
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-month') || e.target.closest(
                        '.btn-remove-month')) {
                    e.preventDefault();
                    const monthEntry = e.target.closest('.month-entry');
                    if (monthEntry) {
                        monthEntry.remove();
                        updateTotalAnggaran();
                    }
                }
            });

            // Calculate totals when inputs change
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('jumlah-input') || e.target.id === 'harga_satuan') {
                    updateTotalAnggaran();
                }

                // For edit modal
                if (e.target.id === 'edit-jumlah' || e.target.id === 'edit-harga-satuan') {
                    updateEditTotal();
                }
            });

            // Month tab click handler
            document.querySelectorAll('.nav-link[data-month]').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const month = this.getAttribute('data-month');
                    refreshMonthData(month);
                });
            });

            // Form submission
            const form = document.getElementById('tambahRkasForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateAllSteps()) {
                        e.preventDefault();
                        return false;
                    }

                    // Show loading
                    if (submitBtn) {
                        submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Edit form submission
            const editForm = document.getElementById('editRkasForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitButton = this.querySelector('button[type="submit"]');

                    // Show loading
                    submitButton.innerHTML = '<span class="loading-spinner me-2"></span>Updating...';
                    submitButton.disabled = true;

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Data berhasil diupdate');
                                location.reload();
                            } else {
                                alert('Gagal mengupdate data: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengupdate data');
                        })
                        .finally(() => {
                            submitButton.innerHTML =
                                '<i class="bi bi-check-circle me-2"></i>Update Data';
                            submitButton.disabled = false;
                        });
                });
            }

            // Delete confirmation
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (currentRkasId) {
                        deleteRkas(currentRkasId);
                    }
                });
            }

            // Modal reset on close
            const modal = document.getElementById('tambahRkasModal');
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function() {
                    resetModal();
                });
            }

            // Initialize Select2 function
            function initializeSelect2() {
                // Kegiatan Select2 with table template
                $('.select2-kegiatan').select2({
                    placeholder: '-- Pilih Kegiatan --',
                    allowClear: true,
                    dropdownParent: $('#tambahRkasModal'),
                    templateResult: formatKegiatanOption,
                    templateSelection: formatKegiatanSelection,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });

                // Rekening Belanja Select2 with table template
                $('.select2-rekening').select2({
                    placeholder: '-- Pilih Rekening Belanja --',
                    allowClear: true,
                    dropdownParent: $('#tambahRkasModal'),
                    templateResult: formatRekeningOption,
                    templateSelection: formatRekeningSelection,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });

                // Edit modal Select2
                $('.select2-kegiatan-edit').select2({
                    placeholder: '-- Pilih Kegiatan --',
                    allowClear: true,
                    dropdownParent: $('#editRkasModal'),
                    templateResult: formatKegiatanOption,
                    templateSelection: formatKegiatanSelection,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });

                $('.select2-rekening-edit').select2({
                    placeholder: '-- Pilih Rekening Belanja --',
                    allowClear: true,
                    dropdownParent: $('#editRkasModal'),
                    templateResult: formatRekeningOption,
                    templateSelection: formatRekeningSelection,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
            }

            // Format Kegiatan option with table
            function formatKegiatanOption(option) {
                if (!option.id) {
                    return option.text;
                }

                const element = option.element;
                const kode = $(element).data('kode') || '';
                const program = $(element).data('program') || '';
                const subProgram = $(element).data('sub-program') || '';
                const uraian = $(element).data('uraian') || '';

                if (!kode && !program && !subProgram && !uraian) {
                    return option.text;
                }

                return $(`
                    <table class="select2-table">
                        <thead>
                            <tr>
                                <th class="select2-table-kode">Kode</th>
                                <th class="select2-table-program">Program</th>
                                <th class="select2-table-sub-program">Sub Program</th>
                                <th class="select2-table-uraian">Uraian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="select2-table-kode">${kode}</td>
                                <td class="select2-table-program">${program}</td>
                                <td class="select2-table-sub-program">${subProgram}</td>
                                <td class="select2-table-uraian">${uraian}</td>
                            </tr>
                        </tbody>
                    </table>
                `);
            }

            // Format Kegiatan selection
            function formatKegiatanSelection(option) {
                if (!option.id) {
                    return option.text;
                }

                const element = option.element;
                const kode = $(element).data('kode') || '';
                const uraian = $(element).data('uraian') || '';

                return kode + ' - ' + uraian;
            }

            // Format Rekening option with table
            function formatRekeningOption(option) {
                if (!option.id) {
                    return option.text;
                }

                const element = option.element;
                const kodeRekening = $(element).data('kode-rekening') || '';
                const rincianObjek = $(element).data('rincian-objek') || '';

                if (!kodeRekening && !rincianObjek) {
                    return option.text;
                }

                return $(`
                    <table class="select2-table">
                        <thead>
                            <tr>
                                <th class="select2-table-rekening">Kode Rekening</th>
                                <th class="select2-table-rincian">Rekening Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="select2-table-rekening">${kodeRekening}</td>
                                <td class="select2-table-rincian">${rincianObjek}</td>
                            </tr>
                        </tbody>
                    </table>
                `);
            }

            // Format Rekening selection
            function formatRekeningSelection(option) {
                if (!option.id) {
                    return option.text;
                }

                const element = option.element;
                const kodeRekening = $(element).data('kode-rekening') || '';
                const rincianObjek = $(element).data('rincian-objek') || '';

                return kodeRekening + ' - ' + rincianObjek;
            }

            function showStep(step) {
                // Update progress indicator
                document.querySelectorAll('.step').forEach(function(stepEl) {
                    stepEl.classList.remove('active', 'completed');
                });

                for (let i = 1; i < step; i++) {
                    const stepEl = document.getElementById(`step-${i}`);
                    if (stepEl) {
                        stepEl.classList.add('completed');
                    }
                }

                const activeStepEl = document.getElementById(`step-${step}`);
                if (activeStepEl) {
                    activeStepEl.classList.add('active');
                }

                // Show/hide form steps
                document.querySelectorAll('.form-step').forEach(function(formStep) {
                    formStep.style.display = 'none';
                });

                const currentFormStep = document.getElementById(`form-step-${step}`);
                if (currentFormStep) {
                    currentFormStep.style.display = 'block';
                }

                // Update buttons
                if (prevBtn) {
                    prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
                }
                if (nextBtn) {
                    nextBtn.style.display = step < 3 ? 'inline-block' : 'none';
                }
                if (submitBtn) {
                    submitBtn.style.display = step === 3 ? 'inline-block' : 'none';
                }
            }

            function validateCurrentStep() {
                let isValid = true;
                const currentStepElement = document.getElementById(`form-step-${currentStep}`);

                if (currentStepElement) {
                    const requiredFields = currentStepElement.querySelectorAll(
                        'input[required], select[required], textarea[required]');

                    requiredFields.forEach(function(field) {
                        field.classList.remove('is-invalid');
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        }
                    });
                }

                if (!isValid) {
                    alert('Mohon lengkapi semua field yang diperlukan.');
                }

                return isValid;
            }

            function validateAllSteps() {
                let isValid = true;

                // Validate all required fields
                const requiredFields = document.querySelectorAll(
                    '#tambahRkasForm input[required], #tambahRkasForm select[required], #tambahRkasForm textarea[required]'
                );

                requiredFields.forEach(function(field) {
                    field.classList.remove('is-invalid');
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    }
                });

                // Validate month uniqueness
                const selectedMonths = [];
                const monthSelects = document.querySelectorAll('.month-select');

                monthSelects.forEach(function(select) {
                    const month = select.value;
                    if (month) {
                        if (selectedMonths.includes(month)) {
                            isValid = false;
                            alert('Tidak boleh ada bulan yang sama dalam satu kegiatan.');
                            return false;
                        }
                        selectedMonths.push(month);
                    }
                });

                return isValid;
            }

            function addMonthEntry() {
                const monthTemplate = `
                    <div class="month-entry border rounded p-3 mb-3" data-index="${monthIndex}">
                        <div class="row align-items-center mb-2">
                            <div class="col-md-3">
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
                                <input type="number" class="form-control jumlah-input"
                                    name="jumlah[]" placeholder="0" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control satuan-input"
                                    name="satuan[]" placeholder="pcs, unit, buah" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total</label>
                                <div class="month-total badge bg-info w-100 mb-2">Rp 0</div>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-month">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                const container = document.getElementById('bulanContainer');
                if (container) {
                    container.insertAdjacentHTML('beforeend', monthTemplate);
                    monthIndex++;
                }
            }

            function updateTotalAnggaran() {
                let total = 0;
                const hargaSatuan = parseFloat(document.getElementById('harga_satuan')?.value) || 0;

                document.querySelectorAll('.month-entry').forEach(function(entry) {
                    const jumlah = parseFloat(entry.querySelector('.jumlah-input')?.value) || 0;
                    const monthTotal = hargaSatuan * jumlah;

                    const monthTotalEl = entry.querySelector('.month-total');
                    if (monthTotalEl) {
                        monthTotalEl.textContent = 'Rp ' + formatNumber(monthTotal);
                    }
                    total += monthTotal;
                });

                const totalDisplay = document.getElementById('totalAnggaranDisplay');
                if (totalDisplay) {
                    totalDisplay.textContent = 'Total: Rp ' + formatNumber(total);
                }
            }

            function updateEditTotal() {
                const jumlah = parseFloat(document.getElementById('edit-jumlah')?.value) || 0;
                const hargaSatuan = parseFloat(document.getElementById('edit-harga-satuan')?.value) || 0;
                const total = jumlah * hargaSatuan;

                const totalDisplay = document.getElementById('edit-total-display');
                if (totalDisplay) {
                    totalDisplay.textContent = 'Rp ' + formatNumber(total);
                }
            }

            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function refreshMonthData(month) {
                console.log('Refreshing data for month:', month);

                // Show loading in the table
                const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="11" class="text-center">
                                <div class="py-4">
                                    <div class="loading-spinner me-2"></div>
                                    Memuat data...
                                </div>
                            </td>
                        </tr>
                    `;

                    // Fetch data via AJAX
                    fetch(`{{ url('penganggaran/rkas/bulan') }}/${month}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                populateTable(month, data.data);
                            } else {
                                showNoDataMessage(month);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            tableBody.innerHTML = `
                            <tr>
                                <td colspan="11" class="text-center text-danger">
                                    <div class="py-4">
                                        <i class="bi bi-exclamation-triangle display-4"></i>
                                        <p class="mt-2">Gagal memuat data</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        });
                }
            }

            function populateTable(month, data) {
                const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
                let html = '';
                let total = 0;

                data.forEach((item, index) => {
                    const itemTotal = parseFloat(item.total.replace(/[Rp\s.]/g, ''));
                    total += itemTotal;

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.program_kegiatan}</td>
                            <td>${item.kegiatan}</td>
                            <td>${item.rekening_belanja}</td>
                            <td>${item.uraian}</td>
                            <td>${item.dianggaran}</td>
                            <td>${item.dibelanjakan}</td>
                            <td>${item.satuan}</td>
                            <td>${item.harga_satuan}</td>
                            <td><strong>${item.total}</strong></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            type="button" 
                                            id="actionDropdown${item.id}" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown${item.id}">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="showDetailModal(${item.id})" style="font-size: 8pt;">
                                                <i class="bi bi-eye me-2"></i>Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="showEditModal(${item.id})" style="font-size: 8pt;">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="showDeleteModal(${item.id})" style="font-size: 8pt;">
                                                <i class="bi bi-trash me-2"></i>Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                // Add total row
                html += `
                    <tr class="table-info">
                        <td colspan="9" class="text-end"><strong>Total ${month}:</strong></td>
                        <td><strong>Rp ${formatNumber(total)}</strong></td>
                        <td></td>
                    </tr>
                `;

                tableBody.innerHTML = html;
            }

            function showNoDataMessage(month) {
                const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
                tableBody.innerHTML = `
                    <tr class="no-data-row">
                        <td colspan="11" class="text-center text-muted">
                            <div class="py-4">
                                <i class="bi bi-inbox display-4"></i>
                                <p class="mt-2">Belum ada data untuk bulan ${month}</p>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#tambahRkasModal">
                                    <i class="bi bi-plus me-2"></i>Tambah Data
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function resetModal() {
                currentStep = 1;
                showStep(1);

                const form = document.getElementById('tambahRkasForm');
                if (form) {
                    form.reset();
                }

                // Reset Select2
                $('.select2-kegiatan').val(null).trigger('change');
                $('.select2-rekening').val(null).trigger('change');

                document.querySelectorAll('.form-control').forEach(function(field) {
                    field.classList.remove('is-invalid');
                });

                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
                    submitBtn.disabled = false;
                }

                // Reset to single month entry
                const container = document.getElementById('bulanContainer');
                if (container) {
                    container.innerHTML = `
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
                                    <input type="number" class="form-control jumlah-input"
                                        name="jumlah[]" placeholder="0" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control satuan-input"
                                        name="satuan[]" placeholder="pcs, unit, buah" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Total</label>
                                    <div class="month-total badge bg-info w-100">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                monthIndex = 1;
                updateTotalAnggaran();
            }

            // Global functions for modal actions
            window.showDetailModal = function(id) {
                currentRkasId = id;

                // Fetch RKAS data
                fetch(`{{ url('penganggaran/rkas') }}/${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const rkas = data.data;

                            // Populate detail modal
                            document.getElementById('detail-program').textContent = rkas.program_kegiatan ||
                                '-';
                            document.getElementById('detail-kegiatan').textContent = rkas.kegiatan || '-';
                            document.getElementById('detail-rekening').textContent = rkas
                                .rekening_belanja || '-';
                            document.getElementById('detail-bulan').textContent = rkas.bulan || '-';
                            document.getElementById('detail-uraian').textContent = rkas.uraian || '-';
                            document.getElementById('detail-jumlah').textContent = rkas.dianggaran || '-';
                            document.getElementById('detail-satuan').textContent = rkas.satuan || '-';
                            document.getElementById('detail-harga-satuan').textContent = rkas
                                .harga_satuan || '-';
                            document.getElementById('detail-total').textContent = rkas.total || '-';

                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('detailRkasModal'));
                            modal.show();
                        } else {
                            alert('Gagal memuat detail data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat detail data');
                    });
            };

            window.showEditModal = function(id) {
                currentRkasId = id;

                // Fetch RKAS data
                fetch(`{{ url('penganggaran/rkas') }}/${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const rkas = data.data;

                            // Populate edit form
                            $('.select2-kegiatan-edit').val(rkas.kode_id).trigger('change');
                            $('.select2-rekening-edit').val(rkas.kode_rekening_id).trigger('change');
                            document.getElementById('edit-uraian').value = rkas.uraian || '';
                            document.getElementById('edit-bulan').value = rkas.bulan || '';
                            document.getElementById('edit-harga-satuan').value = rkas.harga_satuan_raw ||
                                '';
                            document.getElementById('edit-jumlah').value = rkas.dianggaran || '';
                            document.getElementById('edit-satuan').value = rkas.satuan || '';

                            // Set form action
                            document.getElementById('editRkasForm').action =
                                `{{ url('penganggaran/rkas') }}/${id}`;

                            // Update total
                            updateEditTotal();

                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('editRkasModal'));
                            modal.show();
                        } else {
                            alert('Gagal memuat data untuk edit');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data untuk edit');
                    });
            };

            window.showDeleteModal = function(id) {
                currentRkasId = id;

                // Fetch RKAS data
                fetch(`{{ url('penganggaran/rkas') }}/${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const rkas = data.data;

                            // Populate delete confirmation
                            document.getElementById('delete-kegiatan-info').textContent = rkas.kegiatan ||
                                '-';
                            document.getElementById('delete-bulan-info').textContent = rkas.bulan || '-';
                            document.getElementById('delete-total-info').textContent = rkas.total || '-';

                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('deleteRkasModal'));
                            modal.show();
                        } else {
                            alert('Gagal memuat data untuk hapus');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data untuk hapus');
                    });
            };

            window.editFromDetail = function() {
                // Close detail modal
                const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailRkasModal'));
                detailModal.hide();

                // Show edit modal
                setTimeout(() => {
                    showEditModal(currentRkasId);
                }, 300);
            };

            function deleteRkas(id) {
                const confirmBtn = document.getElementById('confirmDeleteBtn');
                confirmBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menghapus...';
                confirmBtn.disabled = true;

                fetch(`{{ url('penganggaran/rkas') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Data berhasil dihapus');
                            location.reload();
                        } else {
                            alert('Gagal menghapus data: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus data');
                    })
                    .finally(() => {
                        confirmBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Ya, Hapus Data';
                        confirmBtn.disabled = false;

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRkasModal'));
                        modal.hide();
                    });
            }

            // Initialize first step
            showStep(1);

            // Function to update Tahap cards with modern styling
            function updateTahapCards() {
                // Update Tahap 1
                fetch('{{ url('penganggaran/rkas/total-tahap1') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tahap1Data = data.data;
                            const totalTahap1Element = document.getElementById('totalTahap1');
                            const sisaTahap1Element = document.getElementById('sisaTahap1');
                            const percentTahap1Element = document.getElementById('percentTahap1');

                            if (totalTahap1Element) {
                                totalTahap1Element.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                                    tahap1Data.total_anggaran);
                            }
                            if (sisaTahap1Element) {
                                sisaTahap1Element.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                                    tahap1Data.sisa_anggaran);
                            }
                            if (percentTahap1Element) {
                                percentTahap1Element.textContent = Math.round(tahap1Data.persentase_terpakai) +
                                    '%';
                            }

                            // Update progress bar with animation
                            const progressBar1 = document.querySelector('.col-lg-4:nth-child(2) .progress-bar');
                            if (progressBar1) {
                                setTimeout(() => {
                                    progressBar1.style.width = tahap1Data.persentase_terpakai + '%';
                                }, 300);
                            }
                        }
                    })
                    .catch(error => console.error('Error updating Tahap 1:', error));

                // Update Tahap 2
                fetch('{{ url('penganggaran/rkas/total-tahap2') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tahap2Data = data.data;
                            const totalTahap2Element = document.getElementById('totalTahap2');
                            const sisaTahap2Element = document.getElementById('sisaTahap2');
                            const percentTahap2Element = document.getElementById('percentTahap2');

                            if (totalTahap2Element) {
                                totalTahap2Element.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                                    tahap2Data.total_anggaran);
                            }
                            if (sisaTahap2Element) {
                                sisaTahap2Element.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                                    tahap2Data.sisa_anggaran);
                            }
                            if (percentTahap2Element) {
                                percentTahap2Element.textContent = Math.round(tahap2Data.persentase_terpakai) +
                                    '%';
                            }

                            // Update progress bar with animation
                            const progressBar2 = document.querySelector('.col-lg-4:nth-child(3) .progress-bar');
                            if (progressBar2) {
                                setTimeout(() => {
                                    progressBar2.style.width = tahap2Data.persentase_terpakai + '%';
                                }, 300);
                            }
                        }
                    })
                    .catch(error => console.error('Error updating Tahap 2:', error));
            }

            // Function to show Tahap detail
            window.showTahapDetail = function(tahap) {
                const tahapName = tahap === 1 ? 'Tahap 1 (Januari - Juni)' : 'Tahap 2 (Juli - Desember)';
                const headerClass = tahap === 1 ? 'bg-primary' : 'bg-success';
                const tableHeaderClass = tahap === 1 ? 'table-primary' : 'table-success';

                fetch(`{{ url('penganggaran/rkas/data-tahap') }}/${tahap}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let tableContent = `
                                <div class="modal fade" id="tahapDetailModal" tabindex="-1" aria-labelledby="tahapDetailModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" style="max-width: 95%;">
                                        <div class="modal-content" style="max-height: 95vh; overflow: hidden;">
                                            <div class="modal-header ${headerClass} text-white">
                                                <h5 class="modal-title" id="tahapDetailModalLabel">
                                                    <i class="bi bi-calendar-event me-2"></i>Detail ${tahapName}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body" style="padding: 15px; max-height: 70vh; overflow-y: auto;">
                                                <div class="table-responsive" style="max-height: 60vh; overflow-x: auto; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; background: white;">
                                                    <table class="table table-striped table-hover" style="font-size: 8pt; min-width: 100%; margin-bottom: 0;">
                                                        <thead class="${tableHeaderClass}" style="position: sticky; top: 0; z-index: 10;">
                                                            <tr>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">No</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Program Kegiatan</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Kegiatan</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Rekening Belanja</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Uraian</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Bulan</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Dianggaran</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Satuan</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Harga Satuan</th>
                                                                <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                            `;

                            if (data.data.length > 0) {
                                data.data.forEach((item, index) => {
                                    tableContent += `
                                        <tr>
                                            <td style="font-size: 8pt; padding: 6px;">${index + 1}</td>
                                            <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">${item.program_kegiatan}</td>
                                            <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">${item.kegiatan}</td>
                                            <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">${item.rekening_belanja}</td>
                                            <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 200px;">${item.uraian}</td>
                                            <td style="font-size: 8pt; padding: 6px;"><span class="badge ${tahap === 1 ? 'bg-info' : 'bg-success'}" style="font-size: 7pt;">${item.bulan}</span></td>
                                            <td style="font-size: 8pt; padding: 6px;">${item.dianggaran}</td>
                                            <td style="font-size: 8pt; padding: 6px;">${item.satuan}</td>
                                            <td style="font-size: 8pt; padding: 6px;">${item.harga_satuan}</td>
                                            <td style="font-size: 8pt; padding: 6px;"><strong>${item.total}</strong></td>
                                        </tr>
                                    `;
                                });
                            } else {
                                tableContent += `
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4" style="font-size: 8pt;">
                                            <i class="bi bi-inbox display-4"></i>
                                            <p class="mt-2" style="font-size: 8pt;">Belum ada data untuk ${tahapName}</p>
                                        </td>
                                    </tr>
                                `;
                            }

                            tableContent += `
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size: 8pt;">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Remove existing modal if any
                            const existingModal = document.getElementById('tahapDetailModal');
                            if (existingModal) {
                                existingModal.remove();
                            }

                            // Add new modal to body
                            document.body.insertAdjacentHTML('beforeend', tableContent);

                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('tahapDetailModal'));
                            modal.show();

                            // Remove modal from DOM when hidden
                            document.getElementById('tahapDetailModal').addEventListener('hidden.bs.modal',
                                function() {
                                    this.remove();
                                });
                        } else {
                            alert('Gagal memuat data detail tahap');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data detail tahap');
                    });
            };

            // Add smooth animations for cards
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.tahap-card');
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';

                    setTimeout(() => {
                        card.style.transition = 'all 0.6s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 200);
                });
            });

            // Update Tahap cards on page load
            setTimeout(updateTahapCards, 500);

            // Update Tahap cards when data changes (after add/edit/delete)
            window.updateTahapCards = updateTahapCards;
        });
    </script>

    <style>
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
    </style>
@endsection
