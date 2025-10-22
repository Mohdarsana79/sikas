<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAPITULASI REALISASI PENGGUNAAN DANA BOS</title>
    <style>
        /* Reset dan base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;

            font-size: {
                    {
                    $printSettings['font_size'] ?? '9pt'
                }
            }

            ;
            line-height: 1.4;
            color: #000;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .periode {
            font-size: 10pt;
            font-weight: bold;
        }

        /* Info Lembaga Styles */
        .info-lembaga {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 5px;
        }

        .info-item {
            flex: 1 0 50%;
            margin-bottom: 5px;
        }

        .info-badge {
            font-weight: 500;
        }

        /* Table Styles */
        .table-container {
            width: 100%;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;

            font-size: {
                    {
                    $printSettings['font_size'] ?? '9pt'
                }
            }

            ;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* Table Header Complex */
        .table-header-complex {
            background-color: #e9ecef;
        }

        .table-header-complex th {
            border: 1px solid #000;
            font-weight: bold;
        }

        /* Program Rows */
        .program-row td {
            font-weight: 500;
        }

        /* Total Row */
        .total-row {
            background-color: #d1ecf1 !important;
            font-weight: bold;
        }

        .total-row td {
            border: 1px solid #000;
        }

        /* Summary Section */
        .summary-section {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .summary-card {
            border: 1px solid #007bff;
            border-radius: 5px;
            padding: 15px;
            background: #f8f9fa;
        }

        .summary-header {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            margin: -15px -15px 15px -15px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
        }

        .summary-list {
            list-style: none;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }

        .signature-date {
            margin-bottom: 10px;
        }

        .signature-name {
            font-weight: bold;
            margin-top: 60px;
        }

        .signature-nip {
            font-size: 8pt;
            color: #666;
        }

        /* Page Break */
        .page-break {
            page-break-before: always;
        }

        /* No Data */
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        /* Komponen Number */
        .komponen-number {
            font-size: 8pt;
            color: #666;
        }

        /* Caption */
        .table-caption {
            caption-side: top;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        /* Print Optimizations */
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 9pt;
            }

            .container {
                padding: 0;
            }

            .header {
                margin-bottom: 15px;
            }

            .info-lembaga {
                margin-bottom: 15px;
            }

            .table-container {
                margin-bottom: 15px;
            }

            .summary-section {
                margin-top: 20px;
                margin-bottom: 20px;
            }

            .signature-section {
                margin-top: 30px;
            }

            /* Ensure tables break properly */
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

            /* No background color in print */
            th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .total-row {
                background-color: #d1ecf1 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Dokumen -->
        <div class="header">
            <h1>REKAPITULASI REALISASI PENGGUNAAN DANA BOS</h1>
            <h2>SMP MUHAMMADIYAH SONI</h2>
            <p class="periode">PERIODE: {{ strtoupper($periode) }} | TAHUN ANGGARAN {{ $tahun }}</p>
        </div>

        <!-- Informasi Lembaga -->
        <div class="info-lembaga">
            <div class="info-row">
                <div class="info-item">
                    <span class="info-badge">LEMBAGA: SMP MUHAMMADIYAH SONI</span>
                </div>
                <div class="info-item">
                    <span class="info-badge">ALAMAT: Jl. Santa No. 150 Desa Paddumpu</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-badge">KABUPATEN: TOLITOLI</span>
                </div>
                <div class="info-item">
                    <span class="info-badge">PROVINSI: SULAWESI TENGAH</span>
                </div>
            </div>
        </div>

        <!-- Tabel Utama Realisasi Dana -->
        <div class="table-container">
            <table class="table-realisasi">
                <caption class="table-caption">Rincian Penggunaan Dana per Komponen Standar</caption>
                <thead>
                    <tr class="table-header-complex">
                        <th rowspan="3" style="width: 40px;">No Urut</th>
                        <th rowspan="3" style="width: 180px;">PROGRAM/KEGIATAN</th>
                        <th colspan="{{ count($realisasiData['komponen_bos']) }}">KOMPONEN PENGGUNAAN DANA BOS</th>
                        <th rowspan="3" style="width: 100px;">JUMLAH (Rp)</th>
                    </tr>
                    <tr class="table-header-complex">
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <th style="width: 80px; font-size: 7pt;">{{ $namaKomponen }}</th>
                        @endforeach
                    </tr>
                    <tr class="table-header-complex">
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <th style="width: 80px; font-size: 8pt;">{{ $komponenId }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($realisasiData['realisasi_data'] as $data)
                    <tr class="program-row">
                        <td class="text-center">{{ $data['no_urut'] }}</td>
                        <td class="text-left">{{ $data['program_kegiatan'] }}</td>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <td class="text-right">
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
                        <td class="text-right">
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
                    <tr class="total-row">
                        <td colspan="2" class="text-center"><strong>JUMLAH TOTAL</strong></td>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <td class="text-right">
                            <strong>
                                @if($realisasiData['realisasi_per_komponen'][$komponenId] > 0)
                                {{ number_format($realisasiData['realisasi_per_komponen'][$komponenId], 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </strong>
                        </td>
                        @endforeach
                        <td class="text-right">
                            <strong>{{ number_format($totalRealisasi, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Bagian Saldo dan Tanda Tangan -->
        <div style="display: flex; justify-content: space-between; margin-top: 30px;">
            <!-- Informasi Saldo -->
            <div style="width: 48%;">
                <div class="summary-card">
                    <div class="summary-header">RINGKASAN KEUANGAN</div>
                    <ul class="summary-list">
                        <li class="summary-item">
                            <span>Saldo periode sebelumnya</span>
                            <span class="text-right"><strong>Rp. {{
                                    number_format($realisasiData['ringkasan_keuangan']['saldo_periode_sebelumnya'] ?? 0, 0, ',',
                                    '.') }}</strong></span>
                        </li>
                        <li class="summary-item">
                            <span>Total penerimaan dana BOSP periode ini</span>
                            <span class="text-right" style="color: #28a745;"><strong>Rp. {{
                                    number_format($realisasiData['ringkasan_keuangan']['total_penerimaan_periode_ini'] ?? 0, 0,
                                    ',', '.') }}</strong></span>
                        </li>
                        <li class="summary-item">
                            <span>Total penggunaan dana BOSP periode ini</span>
                            <span class="text-right" style="color: #17a2b8;"><strong>Rp. {{
                                    number_format($realisasiData['ringkasan_keuangan']['total_penggunaan_periode_ini'] ?? 0, 0,
                                    ',', '.') }}</strong></span>
                        </li>
                        <li class="summary-item">
                            <span>Akhir saldo BOSP periode ini</span>
                            <span class="text-right" style="color: #dc3545;"><strong>Rp. {{
                                    number_format($realisasiData['ringkasan_keuangan']['akhir_saldo_periode_ini'] ?? 0, 0, ',',
                                    '.') }}</strong></span>
                        </li>
                    </ul>
        
                    <!-- Informasi Tambahan -->
                    <div style="margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                        <small style="color: #6c757d;">
                            <strong>Periode:</strong> {{ strtoupper($periode) }} {{ $tahun }}
                        </small>
                    </div>
                </div>
            </div>
        
            <!-- Tanda Tangan -->
            <div style="width: 48%; text-align: right;">
                <div class="signature-section">
                    <p class="signature-date">Paddumpu, {{ $tanggal_cetak ?? \Carbon\Carbon::now()->format('d/m/Y') }}</p>
                    <p style="font-weight: bold; margin-bottom: 60px;">Kepala Sekolah,</p>
                    <p class="signature-name">Dra. Masitah Abdullah</p>
                    <p class="signature-nip">NIP. 19690917 200701 2 017</p>
                </div>
            </div>
        </div>

        <!-- Debug Information (Hanya untuk development) -->
        @if(env('APP_DEBUG', false) && isset($realisasiData['debug_mapping']) && count($realisasiData['debug_mapping'])
        > 0)
        <div class="page-break"></div>
        <div style="margin-top: 20px;">
            <h3 style="text-align: center; margin-bottom: 15px;">DEBUG INFORMATION</h3>
            <table style="width: 100%; font-size: 8pt; border: 1px solid #ccc;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #ccc; padding: 4px;">Kode Kegiatan</th>
                        <th style="border: 1px solid #ccc; padding: 4px;">Program</th>
                        <th style="border: 1px solid #ccc; padding: 4px;">Komponen</th>
                        <th style="border: 1px solid #ccc; padding: 4px;">Nilai (Rp)</th>
                        <th style="border: 1px solid #ccc; padding: 4px;">Uraian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($realisasiData['debug_mapping'] as $debug)
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 4px;">{{ $debug['kode_kegiatan'] }}</td>
                        <td style="border: 1px solid #ccc; padding: 4px;">{{ $debug['program'] }}</td>
                        <td style="border: 1px solid #ccc; padding: 4px;">{{ $debug['komponen'] }}</td>
                        <td style="border: 1px solid #ccc; padding: 4px; text-align: right;">{{
                            number_format($debug['nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #ccc; padding: 4px;">{{ $debug['uraian'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</body>

</html>