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
                    <h1 class="h3 mb-1 text-gradient">Rekapan RKAS</h1>
                    <p class="mb-0 text-muted">Rekap Rencana Kegiatan dan Anggaran Sekolah</p>
                </div>
                <div class="tanggal-c">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTanggalCetak">
                        <i class="bi bi-plus-circle me-2"></i>Tanggal Cetak
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
                                <div class="d-flex justify-content-end">
                                    <a class="btn btn-primary" target="_blank" href="{{ route('penganggaran.rkas.generate-pdf') }}" style="font-size: 9pt;">
                                        <i class="bi bi-printer me-2"></i>
                                        Cetak
                                    </a>
                                </div>
                                <section class="penerimaan mb-4">
                                    <p class="section-title"><strong>A. PENERIMAAN</strong></p>
                                    <p class="subsection-title"><strong>Sumber Dana :</strong></p>
                                    <table class="table table-bordered" style="font-size: 10pt;">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;">No Kode</th>
                                                <th style="width: 60%;">Penerimaan</th>
                                                <th style="width: 25%;">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($penerimaan['items'] as $item)
                                                <tr>
                                                    <td>{{ $item['kode'] }}</td>
                                                    <td>{{ $item['uraian'] }}</td>
                                                    <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-secondary">
                                                <td colspan="2"><strong>Total Penerimaan</strong></td>
                                                <td class="text-right"><strong>{{ number_format($penerimaan['total'], 0, ',', '.') }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </section>

                                <section class="belanja">
                                    <p class="section-title"><strong>B. BELANJA</strong></p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" style="font-size: 10pt;">
                                            <thead>
                                                <tr class="justify-content-center align-items-center">
                                                    <th rowspan="2" style="width: 3%;">No.</th>
                                                    <th rowspan="2" style="width: 8%;">Kode Rekening</th>
                                                    <th rowspan="2" style="width: 8%;">Kode Program</th>
                                                    <th rowspan="2" style="width: 22%;">Uraian</th>
                                                    <th colspan="3" style="width: 20%;">Rincian Perhitungan</th>
                                                    <th rowspan="2" style="width: 10%;">Jumlah</th>
                                                    <th colspan="2" style="width: 20%;">Tahap</th>
                                                </tr>
                                                <tr>
                                                    <th style="width: 6%;">Volume</th>
                                                    <th style="width: 6%;">Satuan</th>
                                                    <th style="width: 8%;">Tarif Harga</th>
                                                    <th style="width: 10%;">1</th>
                                                    <th style="width: 10%;">2</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $counter = 1; @endphp
                                                @foreach ($belanja as $kodeProgram => $program)
                                                    <!-- Baris Program -->
                                                    <tr class="table-primary">
                                                        <td class="text-center">{{ $counter++ }}</td>
                                                        <td></td>
                                                        <td>{{ $kodeProgram }}</td>
                                                        <td>{{ $program['uraian'] }}</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td class="text-right">
                                                            {{ number_format($program['tahap1'] + $program['tahap2'], 0, ',', '.') }}</td>
                                                        <td class="text-right">{{ number_format($program['tahap1'], 0, ',', '.') }}</td>
                                                        <td class="text-right">{{ number_format($program['tahap2'], 0, ',', '.') }}</td>
                                                    </tr>

                                                    @foreach ($program['sub_programs'] as $kodeSubProgram => $subProgram)
                                                        <!-- Baris Sub Program -->
                                                        <tr class="table-secondary">
                                                            <td class="text-center">{{ $counter++ }}</td>
                                                            <td></td>
                                                            <td>{{ $kodeSubProgram }}</td>
                                                            <td>{{ $subProgram['uraian'] }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-right">
                                                                {{ number_format($subProgram['tahap1'] + $subProgram['tahap2'], 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-right">{{ number_format($subProgram['tahap1'], 0, ',', '.') }}</td>
                                                            <td class="text-right">{{ number_format($subProgram['tahap2'], 0, ',', '.') }}</td>
                                                        </tr>

                                                        @foreach ($subProgram['uraian_programs'] as $kodeUraian => $uraian)
                                                            <!-- Baris Uraian Program -->
                                                            <tr class="table-light">
                                                                <td class="text-center">{{ $counter++ }}</td>
                                                                <td></td>
                                                                <td>{{ $kodeUraian }}</td>
                                                                <td>{{ $uraian['uraian'] }}</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text-right">
                                                                    {{ number_format($uraian['tahap1'] + $uraian['tahap2'], 0, ',', '.') }}</td>
                                                                <td class="text-right">{{ number_format($uraian['tahap1'], 0, ',', '.') }}</td>
                                                                <td class="text-right">{{ number_format($uraian['tahap2'], 0, ',', '.') }}</td>
                                                            </tr>

                                                            @foreach ($uraian['items'] as $item)
                                                                <!-- Baris Item Detail -->
                                                                <tr>
                                                                    <td class="text-center">{{ $counter++ }}</td>
                                                                    <td>{{ $item['kode_rekening'] }}</td>
                                                                    <td>{{ $kodeUraian }}</td>
                                                                    <td class="detail-item">{{ $item['uraian'] }}</td>
                                                                    <td class="text-center">{{ $item['volume'] }}</td>
                                                                    <td class="text-center">{{ $item['satuan'] }}</td>
                                                                    <td class="text-right">{{ number_format($item['harga_satuan'], 0, ',', '.') }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{ number_format($item['tahap1'] + $item['tahap2'], 0, ',', '.') }}</td>
                                                                    <td class="text-right">{{ number_format($item['tahap1'], 0, ',', '.') }}</td>
                                                                    <td class="text-right">{{ number_format($item['tahap2'], 0, ',', '.') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-secondary">
                                                    <td colspan="7"><strong>Jumlah</strong></td>
                                                    <td class="text-right"><strong>{{ number_format($totalTahap1 + $totalTahap2, 0, ',', '.') }}</strong></td>
                                                    <td class="text-right"><strong>{{ number_format($totalTahap1, 0, ',', '.') }}</strong></td>
                                                    <td class="text-right"><strong>{{ number_format($totalTahap2, 0, ',', '.') }}</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <!-- Rka Rekap Tab -->
                        <div class="tab-pane fade" id="rka-rekap" role="tabpanel">
                            <div class="p-4">
                                <div class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Belum Ada RKA Rekap</p>
                                </div>
                            </div>
                        </div>

                        <!-- RKA 221 Tab -->
                        <div class="tab-pane fade" id="rka-221" role="tabpanel">
                            <div class="p-4">
                                <div class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Belum Ada Lembar RKA 221</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rka Bulanan Tab -->
                        <div class="tab-pane fade" id="rka-bulan" role="tabpanel">
                            <div class="p-4">
                                <div class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Belum Ada Rekap Bulanan</p>
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
    {{-- modal Tambah Tanggal Cetak --}}
    <!-- Modal Tambah Anggaran -->
            <div class="modal fade" id="tambahTanggalCetak" tabindex="-1" aria-labelledby="tambahTanggalCetakLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tambahTanggalCetakLabel">Tanggal Cetak</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="tanggal_cetak" class="form-label">Tanggal Cetak</label>
                                    <input type="date" class="form-control" id="tanggal_cetak" name="tanggal_cetak" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    <style>
        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin: 15px 0 10px 0;
        }
        
        .subsection-title {
            font-weight: bold;
            font-size: 1rem;
            margin: 10px 0;
        }
        
        .detail-item {
            padding-left: 30px;
        }
        
        .text-right {
            text-align: right;
        }
        
        @media print {
            body {
                font-size: 10pt;
                background: none;
                color: #000;
            }
            
            .nav-tabs, .card-header, .btn {
                display: none !important;
            }
            
            .tab-content > .tab-pane {
                display: block !important;
                opacity: 1 !important;
            }
            
            .card {
                border: none;
                box-shadow: none;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
@endsection