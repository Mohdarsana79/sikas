<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAPITULASI REALISASI PENGGUNAAN DANA BOSP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            padding: 20px;
            font-size: {
                    {
                        $printSettings['font_size'] ?? '9pt'
                    }
                }
            line-height: 1.2;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header .subtitle {
            font-size: 12px;
            margin-bottom: 15px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            margin-bottom: 3px;
        }

        .info-label {
            width: 180px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        .table-container {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .col-no {
            width: 30px;
        }

        .col-standard {
            width: 150px;
        }

        .col-program {
            width: 70px;
        }

        .col-total {
            width: 80px;
        }

        .col-honor {
            width: 70px;
        }

        .footer-section {
            margin-top: 20px;
        }

        .footer-row {
            display: flex;
            margin-bottom: 5px;
        }

        .footer-label {
            width: 200px;
            font-weight: bold;
        }

        .footer-value {
            flex: 1;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-name {
            margin-top: 60px;
            font-weight: bold;
        }

        .signature-nip {
            margin-top: 5px;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] ?? '9pt' }};">
    <div class="container">
        <div class="header">
            <h1>REKAPITULASI REALISASI PENGGUNAAN DANA BOSP</h1>
            <div class="subtitle">{{ $jenisLaporan }} {{$tahun}}</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Nama Sekolah</div>
                <div class="info-value">: {{$sekolah->nama_sekolah}}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kecamatan</div>
                <div class="info-value">: {{$sekolah->kecamatan}}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kabupaten/Kota</div>
                <div class="info-value">: {{$sekolah->kabupaten_kota}}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Provinsi</div>
                <div class="info-value">: {{$sekolah->provinsi}}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NPSN</div>
                <div class="info-value">: {{$sekolah->npsn}}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sumber Dana</div>
                <div class="info-value">: BOS Reguler</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th rowspan="3" class="col-no">No. Urut</th>
                        <th rowspan="3" class="col-standard">8 STANDAR SUB PROGRAM</th>
                        <th colspan="{{ count($realisasiData['komponen_bos']) }}" class="text-center">SUB PROGRAM</th>
                        <th rowspan="3" class="col-total">Jumlah</th>
                    </tr>
                    <tr>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <th>{{ $namaKomponen }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <th>{{ $komponenId }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($realisasiData['realisasi_data'] as $data)
                    <tr>
                        <td>{{ $data['no_urut'] }}</td>
                        <td>{{ $data['program_kegiatan'] }}</td>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <td>
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
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>JUMLAH TOTAL</strong></td>
                        @foreach($realisasiData['komponen_bos'] as $komponenId => $namaKomponen)
                        <td>
                            <strong>
                                @if($realisasiData['realisasi_per_komponen'][$komponenId] > 0)
                                {{ number_format($realisasiData['realisasi_per_komponen'][$komponenId], 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </strong>
                        </td>
                        @endforeach
                        <td>
                            <strong>{{ number_format($totalRealisasi, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer-section">
            <div class="footer-row">
                <div class="footer-label">Saldo periode sebelumnya</div>
                <div class="footer-value">: Rp. {{
                number_format($realisasiData['ringkasan_keuangan']['saldo_periode_sebelumnya'] ?? 0, 0, ',',
                '.') }}</div>
            </div>
            <div class="footer-row">
                <div class="footer-label">Total penerimaan dana BOSP periode ini</div>
                <div class="footer-value">: Rp. {{
                number_format($realisasiData['ringkasan_keuangan']['total_penerimaan_periode_ini'] ?? 0, 0,
                ',', '.') }}</div>
            </div>
            <div class="footer-row">
                <div class="footer-label">Akhir saldo BOSP periode ini</div>
                <div class="footer-value">: Rp. {{
                    number_format($realisasiData['ringkasan_keuangan']['akhir_saldo_periode_ini'] ?? 0, 0, ',', '.')
                    }}</div>
            </div>
            <div class="footer-row">
                <div class="footer-label">Total penggunaan dana BOSP periode ini</div>
                <div class="footer-value">: Rp. {{
                    number_format($realisasiData['ringkasan_keuangan']['total_penggunaan_periode_ini'] ?? 0, 0, ',',
                    '.') }}</div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div>Menyetujui,</div>
                <div class="signature-name">Kepala Sekolah</div>
                <div class="signature-name">{{$penganggaran->nama_kepala_sekolah}}</div>
                <div class="signature-nip">NIP. {{$penganggaran->nip_kepala_sekolah}}</div>
            </div>
            <div class="signature-box">
                <div>&nbsp;</div>
                <div class="signature-name">Bendahara / Penanggungjawab Kegiatan</div>
                <div class="signature-name">{{ $penganggaran->nama_bendahara }}</div>
                <div class="signature-nip">NIP. {{$penganggaran->nip_bendahara}}</div>
            </div>
        </div>
    </div>
</body>

</html>