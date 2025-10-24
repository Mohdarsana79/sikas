<div class="container container-custom">
    @php
    $periode_info = $periode_info ?? [
    'periode_awal' => '01 Januari ' . $tahun,
    'periode_akhir' => '31 Desember ' . $tahun,
    'tahap' => 'Tahunan'
    ];
    @endphp
    <!-- Tabel Utama Realisasi Dana -->
    <div class="table-responsive table-responsive-custom">
        <table class="table table-bordered table-striped align-middle caption-top table-realisasi">
            <caption>Rincian Penggunaan Dana per Komponen Standar</caption>
            <thead>
                <tr>
                    <th rowspan="3" style="width: 50px;">No Urut</th>
                    <th rowspan="3" style="width: 200px;">PROGRAM/KEGIATAN</th>
                    <th colspan="{{ count($realisasiData['komponen_bos']) }}">KOMPONEN PENGGUNAAN DANA BOS</th>
                    <th rowspan="3" style="width: 120px;">JUMLAH (Rp)</th>
                </tr>
                <tr>
                    @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                    <th style="width: 100px;">{{ $namaKomponen }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                    <th style="width: 100px;">{{ $komponenId }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($realisasiData['realisasi_data'] as $data)
                <tr>
                    <td class="text-center">{{ $data['no_urut'] }}</td>
                    <td>{{ $data['program_kegiatan'] }}</td>
                    @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                    <td class="text-end">
                        @if(isset($realisasiData['debug_mapping']) && $realisasiData['debug_mapping'])
                        @php
                        $nilaiKomponen = 0;
                        foreach($realisasiData['debug_mapping'] as $mapping) {
                        if ($mapping['program'] == $data['no_urut'] && $mapping['komponen'] == $komponenId) {
                        $nilaiKomponen += $mapping['nilai'];
                        }
                        }
                        @endphp
                        @if($nilaiKomponen > 0)
                        {{ number_format($nilaiKomponen, 0, ',', '.') }}
                        @else
                        -
                        @endif
                        @else
                        @if($data['realisasi_komponen'][$komponenId] > 0)
                        {{ number_format($data['realisasi_komponen'][$komponenId], 0, ',', '.') }}
                        @else
                        -
                        @endif
                        @endif
                    </td>
                    @endforeach
                    <td class="text-end fw-bold">
                        @php
                        $totalProgram = 0;
                        if(isset($realisasiData['debug_mapping'])) {
                        foreach($realisasiData['debug_mapping'] as $mapping) {
                        if ($mapping['program'] == $data['no_urut']) {
                        $totalProgram += $mapping['nilai'];
                        }
                        }
                        } else {
                        $totalProgram = $data['total_kegiatan'];
                        }
                        @endphp
                        @if($totalProgram > 0)
                        {{ number_format($totalProgram, 0, ',', '.') }}
                        @else
                        -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-grand-total">
                    <td colspan="2" class="text-center fw-bold">JUMLAH TOTAL</td>
                    @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                    <td class="text-end fw-bold">
                        @if($realisasiData['realisasi_per_komponen'][$komponenId] > 0)
                        {{ number_format($realisasiData['realisasi_per_komponen'][$komponenId], 0, ',', '.') }}
                        @else
                        -
                        @endif
                    </td>
                    @endforeach
                    <td class="text-end text-danger fw-bold">{{ number_format($totalRealisasi, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- <!-- Debug Information (Hanya tampil di development) -->
    @if(env('APP_DEBUG') && isset($realisasiData['debug_mapping']))
    <div class="card mt-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Debug Information</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Kode Kegiatan</th>
                            <th>Program</th>
                            <th>Komponen</th>
                            <th>Nilai</th>
                            <th>Uraian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($realisasiData['debug_mapping'] as $debug)
                        <tr>
                            <td>{{ $debug['kode_kegiatan'] }}</td>
                            <td>{{ $debug['program'] }}</td>
                            <td>{{ $debug['komponen'] }}</td>
                            <td class="text-end">{{ number_format($debug['nilai'], 0, ',', '.') }}</td>
                            <td>{{ $debug['uraian'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif --}}

    <!-- Bagian Saldo dan Tanda Tangan -->
    <div class="row mt-4">
        <!-- Informasi Saldo -->
        <div class="col-md-12 signiture-section">
            <div class="card border-primary shadow-sm rounded-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white text-sm">RINGKASAN KEUANGAN</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Saldo periode sebelumnya
                            <span class="fw-bold">Rp. {{
                                number_format($realisasiData['ringkasan_keuangan']['saldo_periode_sebelumnya'] ?? 0, 0, ',',
                                '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total penerimaan dana BOSP periode ini
                            <span class="fw-bold text-success">Rp. {{
                                number_format($realisasiData['ringkasan_keuangan']['total_penerimaan_periode_ini'] ?? 0, 0,
                                ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total penggunaan dana BOSP periode ini
                            <span class="fw-bold text-info">Rp. {{
                                number_format($realisasiData['ringkasan_keuangan']['total_penggunaan_periode_ini'] ?? 0, 0,
                                ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Akhir saldo BOSP periode ini
                            <span class="fw-bold text-danger">Rp. {{
                                number_format($realisasiData['ringkasan_keuangan']['akhir_saldo_periode_ini'] ?? 0, 0, ',',
                                '.') }}</span>
                        </li>
                    </ul>
    
                    <!-- Informasi Tambahan -->
                    <div class="mt-3 p-2 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Periode: <strong>{{ strtoupper($periode) }} {{ $tahun }}</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .container-custom {
        max-width: 100%;
    }

    .table-responsive-custom {
        font-size: 9pt;
    }

    .signiture-section, h5 {
        font-size: 9pt;
    }

    .info-badge {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 500;
        display: block;
    }

    .text-end {
        text-align: right !important;
    }

    .table-realisasi th {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }

    .table-realisasi td {
        vertical-align: middle;
    }

    .bg-grand-total {
        background-color: #d1ecf1 !important;
        font-weight: bold;
    }
</style>