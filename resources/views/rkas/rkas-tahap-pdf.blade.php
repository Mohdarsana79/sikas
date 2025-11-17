<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RKAS Tahap</title>
    <style>
        @page {
            size: {{ $printSettings['ukuran_kertas'] }} {{ $printSettings['orientasi'] }};
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: {{ $printSettings['font_size'] }};
            line-height: 1.3;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 5px;
        }

        header {
            text-align: center;
            margin-bottom: 10px;
        }

        header h1 {
            font-size: 12pt;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 3px 5px;
            vertical-align: middle;
        }

        th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
            font-size: 8pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .main-category {
            font-weight: bold;
            background-color: #fcda8b;
        }

        .sub-category {
            font-weight: bold;
            padding-left: 5px;
            background-color: #bffa98;
        }

        .detail-item {
            padding-left: 10px;
        }

        footer {
            margin-top: 15px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-block {
            text-align: center;
            width: 30%;
        }

        .signature-block p {
            margin: 0;
            font-size: 9pt;
        }

        .signature-block p:first-child {
            margin-bottom: 40px;
        }

        .no-border {
            border: none;
        }

        .no-padding {
            padding: 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 10pt;
            margin: 5px 0;
        }

        .subsection-title {
            font-weight: bold;
            font-size: 9pt;
            margin: 3px 0;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .uraian-category {
            padding-left: 15px;
            background-color: #acfad7;
            font-weight: bold;
        }

        .detail-item {
            padding-left: 30px;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] }};">
    <div class="container">
        <header>
            <h1 style="font-size: {{ $printSettings['font_size'] }};">KERTAS KERJA RENCANA KEGIATAN DAN ANGGARAN SEKOLAH (RKAS) PER TAHAP</h1>
            <p style="font-size: {{ $printSettings['font_size'] }};">TAHUN ANGGARAN : {{ $dataSekolah['tahun_anggaran'] }}</p>
            <table class="no-border" style="width: auto; border: none; font-size: {{ $printSettings['font_size'] }};" >
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; width: 200px; font-size: {{ $printSettings['font_size'] }};">NPSN</td>
                    <td class="no-border no-padding" style="text-align: left; width: 10px; font-size: {{ $printSettings['font_size'] }};">:</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['npsn'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">Nama Sekolah</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">:</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['nama'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">Alamat</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">:</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['alamat'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">Kabupaten</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">:</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['kabupaten'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">Provinsi</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">:</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['provinsi'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">Tahap</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">:</td>
                    <td class="no-border no-padding" style="text-align: left; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['tahap'] }}</td>
                </tr>
            </table>
        </header>

        <section class="penerimaan">
            <p class="section-title" style="font-size: {{ $printSettings['font_size'] }};">A. PENERIMAAN</p>
            <p class="subsection-title" style="font-size: {{ $printSettings['font_size'] }};">Sumber Dana :</p>
            <table style="font-size: {{ $printSettings['font_size'] }};">
                <thead>
                    <tr>
                        <th style="width: 15%; font-size: {{ $printSettings['font_size'] }};">No Kode</th>
                        <th style="width: 60%; font-size: {{ $printSettings['font_size'] }};">Penerimaan</th>
                        <th style="width: 25%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penerimaan['items'] as $item)
                        <tr>
                            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode'] }}</td>
                            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                            <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" style="font-size: {{ $printSettings['font_size'] }};">Total Penerimaan</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($penerimaan['total'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="belanja">
            <p class="section-title" style="font-size: {{ $printSettings['font_size'] }};">B. BELANJA</p>
            <table style="font-size: {{ $printSettings['font_size'] }};">
                <!-- Di bagian thead tabel belanja, ubah struktur kolomnya -->
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 3%; font-size: {{ $printSettings['font_size'] }};">No.</th>
                        <th rowspan="2" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Kode Rekening</th>
                        <th rowspan="2" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Kode Program</th>
                        <th rowspan="2" style="width: 22%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                        <th colspan="3" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Rincian Perhitungan</th>
                        <th rowspan="2" style="width: 10%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th> <!-- Gabungkan kolom jumlah -->
                        <th colspan="2" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Tahap</th>
                    </tr>
                    <tr>
                        <th style="width: 6%; font-size: {{ $printSettings['font_size'] }};">Volume</th>
                        <th style="width: 6%; font-size: {{ $printSettings['font_size'] }};">Satuan</th>
                        <th style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Tarif Harga</th>
                        <!-- Hapus kolom jumlah yang kedua -->
                        <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">1</th>
                        <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">2</th>
                    </tr>
                </thead>

                <!-- Di bagian tbody -->
                <!-- Di bagian tbody -->
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach ($belanja as $kodeProgram => $program)
                        <!-- Baris Program -->
                        <tr class="main-category">
                            <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                            <td></td>
                            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeProgram }}</td>
                            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $program['uraian'] }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                                {{ number_format($program['tahap1'] + $program['tahap2'], 0, ',', '.') }}</td>
                            <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['tahap1'], 0, ',', '.') }}</td>
                            <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['tahap2'], 0, ',', '.') }}</td>
                        </tr>

                        @foreach ($program['sub_programs'] as $kodeSubProgram => $subProgram)
                            <!-- Baris Sub Program -->
                            <tr class="sub-category">
                                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                                <td></td>
                                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeSubProgram }}</td>
                                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $subProgram['uraian'] }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                                    {{ number_format($subProgram['tahap1'] + $subProgram['tahap2'], 0, ',', '.') }}
                                </td>
                                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['tahap1'], 0, ',', '.') }}</td>
                                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['tahap2'], 0, ',', '.') }}</td>
                            </tr>

                            @foreach ($subProgram['uraian_programs'] as $kodeUraian => $uraian)
                                <!-- Baris Uraian Program -->
                                <tr class="uraian-category">
                                    <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                                    <td></td>
                                    <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeUraian }}</td>
                                    <td style="font-size: {{ $printSettings['font_size'] }};">{{ $uraian['uraian'] }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                                        {{ number_format($uraian['tahap1'] + $uraian['tahap2'], 0, ',', '.') }}</td>
                                    <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($uraian['tahap1'], 0, ',', '.') }}</td>
                                    <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($uraian['tahap2'], 0, ',', '.') }}</td>
                                </tr>

                                @foreach ($uraian['items'] as $item)
                                    <!-- Baris Item Detail -->
                                    <tr>
                                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                                        <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode_rekening'] }}</td>
                                        <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeUraian }}</td>
                                        <td class="detail-item" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['volume'] }}</td>
                                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['satuan'] }}</td>
                                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['harga_satuan'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                                            {{ number_format($item['tahap1'] + $item['tahap2'], 0, ',', '.') }}</td>
                                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['tahap1'], 0, ',', '.') }}</td>
                                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['tahap2'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="7" style="font-size: {{ $printSettings['font_size'] }};">Jumlah</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalTahap1 + $totalTahap2, 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalTahap1, 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalTahap2, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <footer>
            <table style="width: 100%; margin-top: 0; border: none; font-size: {{ $printSettings['font_size'] }};">
                <tr>
                    <!-- Kolom Komite -->
                    <td style="width: 33%; text-align: center; border: none; font-size: {{ $printSettings['font_size'] }};">
                        <p><br></p>
                        <p>Ketua Komite,</p>
                        <div style="margin-top: 80px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold; font-size: {{ $printSettings['font_size'] }};"><u>{{ $dataSekolah['komite'] }}</u></p>
                        <p style="margin: 0;"><br></p>
                    </td>
        
                    <!-- Kolom Kepala Sekolah -->
                    <td style="width: 33%; text-align: center; border: none; font-size: {{ $printSettings['font_size'] }};">
                        <p>Mengetahui,</p>
                        <p>Kepala Sekolah,</p>
                        <div style="margin-top: 80px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold; font-size: {{ $printSettings['font_size'] }};"><u>{{ $dataSekolah['kepala_sekolah'] }}</u></p>
                        <p style="margin: 0; font-size: {{ $printSettings['font_size'] }};">NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
                    </td>
        
                    <!-- Kolom Bendahara -->
                    <td style="width: 33%; text-align: center; border: none; font-size: {{ $printSettings['font_size'] }};">
                        <p>{{ $dataSekolah['kabupaten'] }}, {{ $penganggaran->format_tanggal_cetak }}</p>
                        <p>Bendahara,</p>
                        <div style="margin-top: 80px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold; font-size: {{ $printSettings['font_size'] }};"><u>{{ $dataSekolah['bendahara'] }}</u></p>
                        <p style="margin: 0; font-size: {{ $printSettings['font_size'] }};">NIP. {{ $dataSekolah['nip_bendahara'] }}</p>
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>
