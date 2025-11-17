<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAPITULASI REALISASI PENGGUNAAN DANA BOS</title>
    <style>
        @page {
            page: {
                { $printSettings['orientasi']}
            }
        };
        /* GLOBAL STYLES */
        body {
            font-family: 'Arial-Narow', sans-serif;

            font-size: {
                    {
                    $printSettings['font_size'] ?? '9pt'
                }
            }

            ;
            line-height: 2;
            color: #000;
            background: #fff;
            margin: 15px;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        /* HEADER STYLES */
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: none;
            padding-bottom: 0;
        }

        .header h1 {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .header .periode {
            font-size: 9pt;
            font-weight: bold;
            line-height: 2;
        }

        /* INFO LEMBAGA STYLES (Disesuaikan agar lebih rapat) */
        .info-lembaga-table {
            width: 50%;
            border: none;
            margin-bottom: 15px;
            /* font-size: 8.5pt; */
        }

        .info-lembaga-table td {
            border: none;
            padding: 5px 5px 0px 0;
            /* Mengurangi padding vertikal */
            text-align: left;
            vertical-align: top;
            line-height: 1.5;
            /* Lebih rapat */
        }

        .info-lembaga-table td:nth-child(2) {
            /* width: 10px; */
            padding-right: 2px;
        }

        .info-lembaga-table td:nth-child(1) {
            font-weight: normal;
            width: 30%;
        }


        /* TABLE STYLES (UTAMA) */
        .table-container {
            width: 100%;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* table-layout: fixed; */
        }

        th,
        td {
            border: 1px solid #000;
            padding: 10px 3px;
            text-align: center;
            /* vertical-align: middle; */
            line-height: 1.15;
            /* Kerapatan baris yang disesuaikan */
            /* overflow: hidden; */
            /* word-wrap: break-word; */
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 7.5pt;
        }

        /* Header Specific Styles */
        .table-header-complex th {
            /* padding: 6px 3px; */
            /* font-size: 8pt; */
            /* height: 40px; */
            /* vertical-align: middle; */
            line-height: 2;
        }

        .program-name {
            /* font-size: 8pt; */
            text-align: left;
            padding-left: 5px;
            line-height: 2;
        }

        /* Number Formatting */
        .number-cell {
            text-align: right;
            font-family: 'Arial', sans-serif;
            font-size: 8pt;
            padding-right: 5px;
            white-space: nowrap;
        }

        /* Total Row */
        .total-row {
            background-color: #d1ecf1;
            font-weight: bold;
        }

        /* SUMMARY SECTION (Ringkasan Keuangan) */
        .summary-section {
            width: 55%;
            margin-bottom: 30px;
            text-align:left;
        }

        .summary-item {
            padding: 2px 0;
            /* Lebih rapat */
            line-height: 1.5;
        }

        .summary-label {
            float: left;
            width: 60%;
            font-weight: normal;
        }

        .summary-value {
            float: right;
            width: 38%;
            font-weight: bold;
            text-align: right;
        }

        /* TANDA TANGAN SECTION (Disesuaikan menggunakan float untuk kompatibilitas PDF) */
        .signature-area {
            width: 100%;
            margin-top: 15px;
            /* Clearfix */
        }

        .signature-date {
            text-align: center;
            margin-bottom: 20px;
            
        }

        .signature-column {
            width: 45%;
            text-align: center;
            float: left;
            /* Menggunakan float untuk tata letak kolom */
            margin-left: 5%;
            /* Jarak di tengah */
        }

        /* Kolom Bendahara di kanan */
        .signature-column:nth-child(2) {
            float: right;
            margin-left: 0;
            margin-right: 5%;
        }

        .signature-column p {
            margin: 0;
            padding: 0;
            line-height: 2;
            /* Merapatkan baris TTD */
        }

        .signature-title {
            margin-top: 5px;
            margin-bottom: 50px;
            /* Jarak untuk tanda tangan */
            font-weight: normal;
        }

        .signature-name {
            font-weight: bold;
            padding: 0 5px;
            /* margin-bottom: 60px; */
        }

        .signature-nip {
            margin-top: 2px;
        }

        /* Print Optimizations */
        @media print {

            th,
            .total-row {
                background-color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] ?? '9pt' }};">
    <div class="container">

        <!-- HEADER DOKUMEN -->
        <div class="header">
            <h1>REKAPITULASI REALISASI PENGGUNAAN DANA BOSP</h1>
            <p class="periode">PERIODE TANGGAL: {{ $periode_info['periode_awal'] ?? '01 Januari 2025' }} s/d {{
                $periode_info['periode_akhir'] ?? '31 Desember 2025' }}
                @if(($periode_info['tahap'] ?? 'Tahunan') !== 'Tahunan')
                <br>TAHAP {{ $periode_info['tahap'] ?? '1' }}
                @endif
                TAHUN {{ $tahun }}
            </p>
            <div style="text-align: left;">
                <table class="info-lembaga-table">
                    <tr>
                        <td>NPSN</td>
                        <td width="2%">:</td>
                        <td>{{ $sekolah->npsn ?? '40202255' }}</td>
                    </tr>
                    <tr>
                        <td>Nama Sekolah</td>
                        <td>:</td>
                        <td>{{ strtoupper($sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI') }}</td>
                    </tr>
                    <tr>
                        <td>Kecamatan</td>
                        <td>:</td>
                        <td>{{ $sekolah->kecamatan ?? 'Kec. Dampal Selatan' }}</td>
                    </tr>
                    <tr>
                        <td>Kabupaten/Kota</td>
                        <td>:</td>
                        <td>{{ $sekolah->kabupaten_kota ?? 'Kab. Tolitoli' }}</td>
                    </tr>
                    <tr>
                        <td>Provinsi</td>
                        <td>:</td>
                        <td>{{ $sekolah->provinsi ?? 'Prov. Sulawesi Tengah' }}</td>
                    </tr>
                    <tr>
                        <td>Sumber Dana</td>
                        <td>:</td>
                        <td>{{ $sekolah->sumber_dana ?? 'BOS Reguler' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- TABEL UTAMA REALISASI DANA -->
        <div class="table-container">
            <table class="table-realisasi">
                <thead>
                    <tr class="table-header-complex">
                        <th rowspan="3" class="col-no text-center" style="width: 3%;">No<br>Urut</th>
                        <th rowspan="3" class="col-program text-center" style="width: 15%;">8 STANDAR</th>
                        <th colspan="{{ count($realisasiData['komponen_bos']) }}" class="text-center">SUB PROGRAM</th>
                        <th rowspan="3" class="col-jumlah text-center" style="width: 8%;">Jumlah</th>
                    </tr>
                    <tr class="table-header-complex">
                        {{-- Nama Komponen --}}
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <th class="col-komponen" style="width: {{ 74 / count($realisasiData['komponen_bos']) }}%;">{!!
                            str_replace(' ', '<br>', $namaKomponen) !!}</th>
                        @endforeach
                    </tr>
                    <tr class="table-header-complex">
                        {{-- Kode Komponen --}}
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <th class="col-komponen">{{ $komponenId }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($realisasiData['realisasi_data'] as $data)
                    <tr class="program-row">
                        <td class="col-no text-center">{{ $data['no_urut'] }}</td>
                        <td class="col-program program-name">{{ $data['program_kegiatan'] }}</td>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <td class="col-komponen number-cell">
                            @php
                            $nilaiKomponen = $data['realisasi_komponen'][$komponenId] ?? 0;
                            if(isset($realisasiData['debug_mapping']) && $realisasiData['debug_mapping']) {
                            $nilaiKomponen = 0;
                            foreach($realisasiData['debug_mapping'] as $mapping) {
                            if ($mapping['program'] == $data['no_urut'] && $mapping['komponen'] == $komponenId) {
                            $nilaiKomponen += $mapping['nilai'];
                            }
                            }
                            }
                            @endphp
                            @if($nilaiKomponen > 0)
                            {{ number_format($nilaiKomponen, 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        @endforeach
                        <td class="col-jumlah number-cell">
                            @php
                            $totalProgram = $data['total_kegiatan'] ?? 0;
                            if(isset($realisasiData['debug_mapping'])) {
                            $totalProgram = 0;
                            foreach($realisasiData['debug_mapping'] as $mapping) {
                            if ($mapping['program'] == $data['no_urut']) {
                            $totalProgram += $mapping['nilai'];
                            }
                            }
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
                        <td colspan="2" class="program-name" style="text-align: center !important;">
                            <strong>JUMLAH</strong></td>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <td class="col-komponen number-cell">
                            <strong>
                                @if(isset($realisasiData['realisasi_per_komponen'][$komponenId]) &&
                                $realisasiData['realisasi_per_komponen'][$komponenId] > 0)
                                {{ number_format($realisasiData['realisasi_per_komponen'][$komponenId], 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </strong>
                        </td>
                        @endforeach
                        <td class="col-jumlah number-cell">
                            <strong>{{ number_format($totalRealisasi, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BAGIAN SALDO (Ringkasan Keuangan) -->
        <div>
            <table class="summary-table" style="border:none; border-collapse:collapse; !important;">
                <tr>
                    <td>Saldo periode sebelumnya</td>
                    <td width="2%">:</td>
                    <td>Rp. {{
                        number_format($realisasiData['ringkasan_keuangan']['saldo_periode_sebelumnya'] ?? 0, 0, ',', '.')
                        }}</td>
                </tr>
                <tr>
                    <td>Total penerimaan dana BOSP periode ini</td>
                    <td>:</td>
                    <td>Rp. {{
                        number_format($realisasiData['ringkasan_keuangan']['total_penerimaan_periode_ini'] ?? 0, 0, ',',
                        '.') }}</td>
                </tr>
                <tr>
                    <td>Total penggunaan dana BOSP periode ini</td>
                    <td>:</td>
                    <td>Rp. {{
                        number_format($realisasiData['ringkasan_keuangan']['total_penggunaan_periode_ini'] ?? 0, 0, ',',
                        '.') }}</td>
                </tr>
                <tr>
                    <td>Akhir saldo BOSP periode ini</td>
                    <td>:</td>
                    <td>Rp. {{
                        number_format($realisasiData['ringkasan_keuangan']['akhir_saldo_periode_ini'] ?? 0, 0, ',', '.')
                        }}</td>
                </tr>
            </table>
        </div>

        <!-- TANDA TANGAN SECTION (Menggunakan Float untuk kerapihan dan kompatibilitas) -->
        <div class="signature-area">
            {{-- Kolom Kepala Sekolah --}}
            <div class="signature-column" style="margin-left: 0;">
                <p>Menyetujui,</p>
                <p class="signature-title">Kepala Sekolah</p>
                <br><br><br><br><br><br>
                <p class="signature-name">{{ $penganggaran->kepala_sekolah ?? '-' }}</p>
                <p class="signature-nip">NIP. {{ $penganggaran->nip_kepala_sekolah ?? '-' }}</p>
            </div>

            {{-- Kolom Bendahara/Penanggungjawab --}}
            <div class="signature-column">
                <br><br>
                <p class="signature-title">Bendahara / Penanggungjawab Kegiatan</p>
                <br><br><br><br><br><br>
                <p class="signature-name">{{ $penganggaran->bendahara ?? '-' }}</p>
                <p class="signature-nip">NIP. {{ $penganggaran->nip_bendahara ?? '-' }}</p>
            </div>
        </div>

    </div>
</body>

</html>