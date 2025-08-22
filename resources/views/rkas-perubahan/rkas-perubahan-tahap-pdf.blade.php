<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RKAS Tahap</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
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
            background-color: #f2f2f2;
        }

        .sub-category {
            font-weight: bold;
            padding-left: 5px;
            background-color: #f9f9f9;
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
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .detail-item {
            padding-left: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>KERTAS KERJA RENCANA KEGIATAN DAN ANGGARAN SEKOLAH (RKAS) PERUBAHAN PER TAHAP</h1>
            <p>TAHUN ANGGARAN : {{ $dataSekolah['tahun_anggaran'] }}</p>
            <table class="no-border" style="width: auto; border: none;">
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left; width: 200px;">NPSN</td>
                    <td class="no-border no-padding" style="text-align: left; width: 10px;">:</td>
                    <td class="no-border no-padding" style="text-align: left;">{{ $dataSekolah['npsn'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left;">Nama Sekolah</td>
                    <td class="no-border no-padding" style="text-align: left;">:</td>
                    <td class="no-border no-padding" style="text-align: left;">{{ $dataSekolah['nama'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left;">Alamat</td>
                    <td class="no-border no-padding" style="text-align: left;">:</td>
                    <td class="no-border no-padding" style="text-align: left;">{{ $dataSekolah['alamat'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left;">Kabupaten</td>
                    <td class="no-border no-padding" style="text-align: left;">:</td>
                    <td class="no-border no-padding" style="text-align: left;">{{ $dataSekolah['kabupaten'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left;">Provinsi</td>
                    <td class="no-border no-padding" style="text-align: left;">:</td>
                    <td class="no-border no-padding" style="text-align: left;">{{ $dataSekolah['provinsi'] }}</td>
                </tr>
                <tr class="no-border">
                    <td class="no-border no-padding" style="text-align: left;">Tahap</td>
                    <td class="no-border no-padding" style="text-align: left;">:</td>
                    <td class="no-border no-padding" style="text-align: left;">{{ $dataSekolah['tahap'] }}</td>
                </tr>
            </table>
        </header>

        <section class="penerimaan">
            <p class="section-title">A. PENERIMAAN</p>
            <p class="subsection-title">Sumber Dana :</p>
            <table>
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
                    <tr class="total-row">
                        <td colspan="2">Total Penerimaan</td>
                        <td class="text-right">{{ number_format($penerimaan['total'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="belanja">
            <p class="section-title">B. BELANJA</p>
            <table>
                <!-- Di bagian thead tabel belanja, ubah struktur kolomnya -->
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 3%;">No.</th>
                        <th rowspan="2" style="width: 8%;">Kode Rekening</th>
                        <th rowspan="2" style="width: 8%;">Kode Program</th>
                        <th rowspan="2" style="width: 22%;">Uraian</th>
                        <th colspan="3" style="width: 20%;">Rincian Perhitungan</th>
                        <th rowspan="2" style="width: 10%;">Jumlah</th> <!-- Gabungkan kolom jumlah -->
                        <th colspan="2" style="width: 20%;">Tahap</th>
                    </tr>
                    <tr>
                        <th style="width: 6%;">Volume</th>
                        <th style="width: 6%;">Satuan</th>
                        <th style="width: 8%;">Tarif Harga</th>
                        <!-- Hapus kolom jumlah yang kedua -->
                        <th style="width: 10%;">1</th>
                        <th style="width: 10%;">2</th>
                    </tr>
                </thead>

                <!-- Di bagian tbody -->
                <!-- Di bagian tbody -->
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach ($belanja as $kodeProgram => $program)
                        <!-- Baris Program -->
                        <tr class="main-category">
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
                            <tr class="sub-category">
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
                                <tr class="uraian-category">
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
                    <tr class="total-row">
                        <td colspan="7">Jumlah</td>
                        <td class="text-right">{{ number_format($totalTahap1 + $totalTahap2, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($totalTahap1, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($totalTahap2, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <footer>
            <table style="width: 100%; margin-top: 0; border: none;">
                <tr>
                    <!-- Kolom Komite -->
                    <td style="width: 33%; text-align: center; border: none; ">
                        <p><br></p>
                        <p>Ketua Komite,</p>
                        <div style="margin-top: 80px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold;"><u>{{ $dataSekolah['komite'] }}</u></p>
                        <p style="margin: 0;"><br></p>
                    </td>
        
                    <!-- Kolom Kepala Sekolah -->
                    <td style="width: 33%; text-align: center; border: none;">
                        <p>Mengetahui,</p>
                        <p>Kepala Sekolah,</p>
                        <div style="margin-top: 80px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold;"><u>{{ $dataSekolah['kepala_sekolah'] }}</u></p>
                        <p style="margin: 0;">NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
                    </td>
        
                    <!-- Kolom Bendahara -->
                    <td style="width: 33%; text-align: center; border: none;">
                        <p>{{ $dataSekolah['kabupaten'] }}, {{ $penganggaran->format_tanggal_cetak }}</p>
                        <p>Bendahara,</p>
                        <div style="margin-top: 80px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold;"><u>{{ $dataSekolah['bendahara'] }}</u></p>
                        <p style="margin: 0;">NIP. {{ $dataSekolah['nip_bendahara'] }}</p>
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>
