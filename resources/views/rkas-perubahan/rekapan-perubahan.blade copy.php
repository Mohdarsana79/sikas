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
            <div class="tanggal-perubahan" style="font-size: 10pt;">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editTanggalPerubahan"
                    style="font-size: 10pt;">
                    <i class="bi bi-calendar-heart me-2"></i>Tanggal Cetak
                </button>
                <span class="ms-2">Tanggal Cetak:
                    @if($penganggaran->tanggal_perubahan)
                    {{ \Carbon\Carbon::parse($penganggaran->tanggal_perubahan)->format('d/m/Y') }}
                    @else
                    Belum diisi
                    @endif
                </span>
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
                            <div class="d-flex justify-content-end mb-3">
                                <a class="btn btn-primary" target="_blank"
                                    href="{{ route('rkas-perubahan.generate-tahapan-pdf', ['tahun' => $tahun]) }}"
                                    style="font-size: 9pt;">
                                    <i class="bi bi-printer me-2"></i>
                                    Cetak
                                </a>
                            </div>

                            <section class="penerimaan mb-4">
                                <p class="section-title"><strong>A. PENERIMAAN</strong></p>
                                <p class="subsection-title"><strong>Sumber Dana :</strong></p>
                                <table class="table table-bordered" style="font-size: 9pt;">
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
                                            <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-secondary">
                                            <td colspan="2"><strong>Total Penerimaan</strong></td>
                                            <td class="text-right"><strong>{{ number_format($penerimaan['total'], 0,
                                                    ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </section>

                            <!-- Di dalam section belanja -->
                            <section class="belanja">
                                <p class="section-title"><strong>B. BELANJA</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered" style="font-size: 8pt;">
                                        <thead>
                                            <tr class="text-center align-middle">
                                                <th rowspan="3" style="width: 3%;">No.</th>
                                                <th rowspan="3" style="width: 8%;">Kode Rekening</th>
                                                <th rowspan="3" style="width: 8%;">Kode Program</th>
                                                <th rowspan="3" style="width: 20%;">Uraian</th>
                                                <th colspan="4" style="width: 20%;">Rincian Perhitungan Sebelum Perubahan</th>
                                                <th colspan="4" style="width: 20%;">Rincian Perhitungan Sesudah Perubahan</th>
                                                <th colspan="2" style="width: 8%;">Perubahan</th>
                                                <th colspan="2" style="width: 8%;">Tahap</th>
                                            </tr>
                                            <tr class="text-center">
                                                <th colspan="4">Sebelum Perubahan</th>
                                                <th colspan="4">Sesudah Perubahan</th>
                                                <th rowspan="2" style="width: 4%;">Bertambah</th>
                                                <th rowspan="2" style="width: 4%;">Berkurang</th>
                                                <th rowspan="2" style="width: 4%;">Tahap 1</th>
                                                <th rowspan="2" style="width: 4%;">Tahap 2</th>
                                            </tr>
                                            <tr class="text-center">
                                                <!-- Sebelum Perubahan -->
                                                <th style="width: 5%;">Volume</th>
                                                <th style="width: 5%;">Satuan</th>
                                                <th style="width: 5%;">Tarif</th>
                                                <th style="width: 5%;">Jumlah</th>
                            
                                                <!-- Sesudah Perubahan -->
                                                <th style="width: 5%;">Volume</th>
                                                <th style="width: 5%;">Satuan</th>
                                                <th style="width: 5%;">Tarif</th>
                                                <th style="width: 5%;">Jumlah</th>
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
                                                <td><strong>{{ $program['uraian'] }}</strong></td>
                            
                                                <!-- Sebelum Perubahan - Program -->
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ number_format($program['total_asli'], 0, ',', '.') }}</td>
                            
                                                <!-- Sesudah Perubahan - Program -->
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ number_format($program['total_perubahan'], 0, ',', '.') }}</td>
                            
                                                <!-- Perubahan -->
                                                <td class="text-right">-</td>
                                                <td class="text-right">-</td>
                            
                                                <!-- Tahap -->
                                                <td class="text-right">{{ number_format($program['tahap1'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($program['tahap2'], 0, ',', '.') }}</td>
                                            </tr>
                            
                                            @foreach ($program['sub_programs'] as $kodeSubProgram => $subProgram)
                                            <!-- Baris Sub Program -->
                                            <tr class="table-secondary">
                                                <td class="text-center">{{ $counter++ }}</td>
                                                <td></td>
                                                <td>{{ $kodeSubProgram }}</td>
                                                <td><strong>{{ $subProgram['uraian'] }}</strong></td>
                            
                                                <!-- Sebelum Perubahan - Sub Program -->
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ number_format($subProgram['total_asli'], 0, ',', '.') }}</td>
                            
                                                <!-- Sesudah Perubahan - Sub Program -->
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ number_format($subProgram['total_perubahan'], 0, ',', '.') }}</td>
                            
                                                <!-- Perubahan -->
                                                <td class="text-right">-</td>
                                                <td class="text-right">-</td>
                            
                                                <!-- Tahap -->
                                                <td class="text-right">{{ number_format($subProgram['tahap1'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($subProgram['tahap2'], 0, ',', '.') }}</td>
                                            </tr>
                            
                                            @foreach ($subProgram['uraian_programs'] as $kodeUraian => $uraian)
                                            <!-- Baris Uraian Program -->
                                            <tr class="table-light">
                                                <td class="text-center">{{ $counter++ }}</td>
                                                <td></td>
                                                <td>{{ $kodeUraian }}</td>
                                                <td><strong>{{ $uraian['uraian'] }}</strong></td>
                            
                                                <!-- Sebelum Perubahan - Uraian -->
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ number_format($uraian['total_asli'], 0, ',', '.') }}</td>
                            
                                                <!-- Sesudah Perubahan - Uraian -->
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ number_format($uraian['total_perubahan'], 0, ',', '.') }}</td>
                            
                                                <!-- Perubahan -->
                                                <td class="text-right">-</td>
                                                <td class="text-right">-</td>
                            
                                                <!-- Tahap -->
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
                            
                                                <!-- Sebelum Perubahan - Detail -->
                                                <td class="text-center">{{ $item['volume_asli'] }}</td>
                                                <td class="text-center">{{ $item['satuan'] }}</td>
                                                <td class="text-right">{{ number_format($item['harga_satuan_asli'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($item['jumlah_asli'], 0, ',', '.') }}</td>
                            
                                                <!-- Sesudah Perubahan - Detail -->
                                                <td class="text-center">{{ $item['volume_perubahan'] }}</td>
                                                <td class="text-center">{{ $item['satuan'] }}</td>
                                                <td class="text-right">{{ number_format($item['harga_satuan_perubahan'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($item['jumlah_perubahan'], 0, ',', '.') }}</td>
                            
                                                <!-- Perubahan -->
                                                <td class="text-right">{{ number_format($item['bertambah'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($item['berkurang'], 0, ',', '.') }}</td>
                            
                                                <!-- Tahap -->
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
                                                <td colspan="12" class="text-center"><strong>JUMLAH</strong></td>
                                                <td class="text-right"><strong>{{ number_format($totalBertambah, 0, ',', '.') }}</strong></td>
                                                <td class="text-right"><strong>{{ number_format($totalBerkurang, 0, ',', '.') }}</strong></td>
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
                            <div class="d-flex justify-content-end mb-3">
                                <div>
                                    <button class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#pengaturanKertasModal"
                                        style="font-size: 9pt;">
                                        <i class="bi bi-gear me-2"></i>
                                        Pengaturan Cetak
                                    </button>
                                </div>
                                <div>
                                    <a class="btn btn-primary" target="_window" href="{{ route('rkas-perubahan.generate-pdf-rekap', ['tahun' => $tahun]) }}" style="font-size: 9pt;"
                                        id="tombolCetakRekap">
                                        <i class="bi bi-printer me-2"></i>
                                        Cetak RKA Rekap
                                    </a>
                                </div>
                            </div>

                            <!-- Penerimaan Section -->
                            <section class="penerimaan mb-4">
                                <p class="section-title"><strong>A. PENERIMAAN</strong></p>
                                <p class="subsection-title"><strong>Sumber Dana :</strong></p>
                                <table class="table table-bordered" id="rka-rekap" style="font-size: 10pt;">
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
                                            <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-secondary">
                                            <td colspan="2"><strong>Total Penerimaan</strong></td>
                                            <td class="text-right"><strong>{{ number_format($penerimaan['total'], 0,
                                                    ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </section>

                            <!-- Belanja Section -->
                            <section class="belanja">
                                <p class="section-title"><strong>B. REKAPITULASI ANGGARAN</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered" style="font-size: 10pt;">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;">Kode Rekening</th>
                                                <th style="width: 65%;">Uraian</th>
                                                <th style="width: 20%;">Jumlah (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rekapData as $item)
                                            <tr
                                                class="{{ in_array($item['uraian'], ['JUMLAH PENDAPATAN', 'JUMLAH BELANJA', 'DEFISIT']) ? 'table-secondary' : '' }}">
                                                <td>{{ $item['kode'] }}</td>
                                                <td>{{ $item['uraian'] }}</td>
                                                <td class="text-right">
                                                    @if($item['jumlah'] === '-')
                                                    -
                                                    @else
                                                    {{ number_format($item['jumlah'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <!-- Tahapan Section -->
                            <section class="tahapan mt-4">
                                <p class="section-title"><strong>C. RENCANA PELAKSANAAN ANGGARAN PER TAHAP</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="rka-rekap" style="font-size: 10pt;">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 5%;">No</th>
                                                <th rowspan="2" style="width: 50%;">Uraian</th>
                                                <th colspan="2" style="width: 30%;">Tahap</th>
                                                <th rowspan="2" style="width: 15%;">Jumlah</th>
                                            </tr>
                                            <tr>
                                                <th style="width: 15%;">I</th>
                                                <th style="width: 15%;">II</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>Pendapatan</td>
                                                <td class="text-right">{{ number_format($penerimaan['total'] / 2, 0,
                                                    ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($penerimaan['total'] / 2, 0,
                                                    ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($penerimaan['total'], 0, ',',
                                                    '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>2.1</td>
                                                <td>Belanja Operasi</td>
                                                <td class="text-right">{{ number_format($totalTahap1, 0, ',', '.') }}
                                                </td>
                                                <td class="text-right">{{ number_format($totalTahap2, 0, ',', '.') }}
                                                </td>
                                                <td class="text-right">{{ number_format($totalTahap1 + $totalTahap2, 0,
                                                    ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>2.2</td>
                                                <td>Belanja Modal</td>
                                                <td class="text-right">0</td>
                                                <td class="text-right">0</td>
                                                <td class="text-right">0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- RKA 221 Tab -->
                    <div class="tab-pane fade" id="rka-221" role="tabpanel">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">LEMBAR KERTAS KERJA</h5>
                                <a class="btn btn-primary btn-sm"
                                    href="{{ route('rkas-perubahan.generate-rka-221-pdf', ['tahun' => $tahun]) }}"
                                    target="_blank">
                                    <i class="bi bi-printer me-2"></i>Cetak
                                </a>
                            </div>
                            <!-- Tabel Indikator Kinerja -->
                            <table class="table table-bordered mb-4" id="rka-221" style="font-size: 10pt;">
                                <thead>
                                    <tr>
                                        <th colspan="3" class="text-center">Indikator & Tolok Ukur Kinerja Belanja
                                            Langsung</th>
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Indikator</th>
                                        <th style="width: 40%">Tolok Ukur Kinerja</th>
                                        <th style="width: 20%">Target Kinerja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($indikatorKinerja as $indikator)
                                    <tr>
                                        <td>{{ $indikator['indikator'] }}</td>
                                        <td>{{ $indikator['tolok_ukur'] }}</td>
                                        <td class="text-right">{{ $indikator['target'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Tabel Rincian Anggaran -->
                            <table class="table table-bordered" id="rka-221" style="font-size: 10pt;">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width: 15%">Kode Rekening</th>
                                        <th rowspan="2" style="width: 35%">Uraian</th>
                                        <th colspan="3" style="width: 30%">Rincian Perhitungan</th>
                                        <th rowspan="2" style="width: 20%">Jumlah (Rp)</th>
                                    </tr>
                                    <tr>
                                        <th style="width: 10%">Volume</th>
                                        <th style="width: 10%">Satuan</th>
                                        <th style="width: 10%">Harga Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Di dalam tab RKA 221 -->
                                    @foreach($mainStructure as $kode => $uraian)
                                    <!-- Baris Kode Utama -->
                                    <tr style="background-color: #f8f9fa;">
                                        <td>{{ $kode }}</td>
                                        <td>{{ $uraian }}</td>
                                        <td colspan="3"></td>
                                        <td class="text-right">{{ number_format($totals[$kode] ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    <!-- Detail Item -->
                                    @if(isset($groupedItems[$kode]))
                                    @foreach($groupedItems[$kode] as $item)
                                    <tr>
                                        <td>{{ $item['kode_rekening'] }}</td>
                                        <td>{{ $item['uraian'] }}</td>
                                        <td class="text-center">{{ $item['volume'] }}</td>
                                        <td class="text-center">{{ $item['satuan'] }}</td>
                                        <td class="text-right">{{ number_format($item['harga_satuan'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #e9ecef;">
                                        <td colspan="5" class="text-center"><strong>Jumlah</strong></td>
                                        <td class="text-right"><strong>{{ number_format($total_anggaran ?? 0, 0, ',',
                                                '.') }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Rka Bulanan --}}
                    <div class="tab-pane fade" id="rka-bulan" role="tabpanel">
                        <div class="p-4">
                            <form method="GET" action="{{ route('rkas-perubahan.rekapan-perubahan') }}" id="bulanForm">
                                <input type="hidden" name="tahun" value="{{ $tahun }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <select name="bulan" id="bulanSelect" class="form-select form-select-sm"
                                            style="width: 150px;">
                                            @foreach($months as $month)
                                            <option value="{{ $month }}" {{ $bulan==$month ? 'selected' : '' }}>{{
                                                $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <a class="btn btn-primary btn-sm" id="cetakPdfButton"
                                            href="{{ route('rkas-perubahan.rka-bulanan-pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                                            target="_blank" style="font-size: 9pt;">
                                            <i class="bi bi-printer me-2"></i> Cetak
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabel-bulanan" style="font-size: 10pt;">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">No.</th>
                                            <th style="width: 15%">Kode Rekening</th>
                                            <th style="width: 15%">Kode Program</th>
                                            <th style="width: 30%">Uraian</th>
                                            <th style="width: 10%">Volume</th>
                                            <th style="width: 10%">Satuan</th>
                                            <th style="width: 10%">Tarif Harga</th>
                                            <th style="width: 15%">Jumlah</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <div id="loadingIndicator" class="text-center py-3" style="display:none;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p>Memuat data...</p>
                                        </div>
                                        @include('rkas-perubahan.partials.monthly-data-perubahan', [
                                        'rkasBulanan' => $rkasBulanan,
                                        'bulan' => $bulan,
                                        'totalBulanan' => $totalBulanan
                                        ])
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-secondary">
                                            <td colspan="7"><strong>Jumlah</strong></td>
                                            <td class="text-right"><strong>{{ number_format($totalBulanan, 0, ',', '.')
                                                    }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
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
@include('rkas-perubahan.modal.pengaturan-margin-perubahan')
{{-- modal Edit Tanggal Cetak --}}
<div class="modal fade" id="editTanggalPerubahan" tabindex="-1" aria-labelledby="editTanggalPerubahanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTanggalPerubahanLabel">Tanggal Cetak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('penganggaran.update-tanggal-perubahan', $penganggaran->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_tanggal_perubahan" class="form-label">Tanggal Cetak</label>
                        <input type="date" class="form-control" id="edit_tanggal_perubahan" name="tanggal_perubahan"
                            value="{{ $penganggaran->tanggal_perubahan ? \Carbon\Carbon::parse($penganggaran->tanggal_perubahan)->format('Y-m-d') : '' }}"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<style>
    /* Animasi loading */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    #rkasTableBody {
        animation: fadeIn 0.3s ease-in;
    }

    #loadingIndicator {
        transition: all 0.3s ease;
    }

    /* Animasi tombol cetak */
    .btn-pulse {
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .display-bulan {
        font-weight: bold;
        color: #0d6efd;
    }

    /* Tambahkan di bagian CSS Anda */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    #rkasTableBody {
        animation: fadeIn 0.3s ease-in;
    }

    #loadingIndicator {
        transition: all 0.3s ease;
    }

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

        .nav-tabs,
        .card-header,
        .btn {
            display: none !important;
        }

        .tab-content>.tab-pane {
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
<script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#bulanSelect').change(function() {
        const bulan = $(this).val();
        const tahun = "{{ $tahun }}";

        if (!bulan) return;
        
        // Update URL cetak PDF secara langsung
        const pdfUrl = "{{ route('rkas-perubahan.rka-bulanan-pdf', ['tahun' => ':tahun', 'bulan' => ':bulan']) }}"
        .replace(':tahun', tahun)
        .replace(':bulan', bulan);
        $('#cetakPdfButton').attr('href', pdfUrl);
        
        // AJAX request untuk load data tabel
        $('#loadingIndicator').show();
        $('#rka-bulan table tbody').hide();
        
        $.ajax({
        url: "{{ route('rkas-perubahan.get-monthly-data') }}",
        type: "GET",
        data: {
        bulan: bulan,
        tahun: tahun,
        _token: "{{ csrf_token() }}"
        },
        success: function(response) {
        if (response.success) {
        $('#rka-bulan table tbody').html(response.html);
        $('#rka-bulan table tfoot td.text-right').html(
        '<strong>' + new Intl.NumberFormat('id-ID').format(response.totalBulanan) + '</strong>'
        );
        }
        },
        complete: function() {
        $('#rka-bulan table tbody').fadeIn();
        $('#loadingIndicator').hide();
        }
        });
        });
        });
</script>
@endsection