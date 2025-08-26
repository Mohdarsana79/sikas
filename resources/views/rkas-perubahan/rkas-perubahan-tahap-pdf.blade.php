<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RKAS TAHAPAN PERUBAHAN</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
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
            font-size: 7pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .main-category {
            font-weight: bold;
            background-color: #e6f2ff;
        }

        .sub-category {
            font-weight: bold;
            background-color: #f0f8ff;
        }

        .uraian-category {
            font-weight: bold;
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

        .section-title {
            font-weight: bold;
            font-size: 9pt;
            margin: 5px 0;
        }

        .subsection-title {
            font-weight: bold;
            font-size: 8pt;
            margin: 3px 0;
        }

        .total-row {
            font-weight: bold;
            background-color: #d9edf7;
        }

        .no-border {
            border: none;
        }

        .no-padding {
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>RENCANA KEGIATAN DAN ANGGARAN SEKOLAH (RKAS) PERUBAHAN PER TAHAP</h1>
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
                <thead>
                    <tr>
                        <th rowspan="3" style="width: 3%;">No.</th>
                        <th rowspan="3" style="width: 8%;">Kode Rekening</th>
                        <th rowspan="3" style="width: 8%;">Kode Program</th>
                        <th rowspan="3" style="width: 20%;">Uraian</th>
                        <th colspan="4" style="width: 20%;">Rincian Perhitungan Sebelum Perubahan</th>
                        <th colspan="4" style="width: 20%;">Rincian Perhitungan Sesudah Perubahan</th>
                        <th colspan="2" style="width: 8%;">Perubahan</th>
                        <th colspan="2" style="width: 8%;">Tahap</th>
                    </tr>
                    <tr>
                        <th colspan="4">Sebelum Perubahan</th>
                        <th colspan="4">Sesudah Perubahan</th>
                        <th rowspan="2" style="width: 4%;">Bertambah</th>
                        <th rowspan="2" style="width: 4%;">Berkurang</th>
                        <th rowspan="2" style="width: 4%;">Tahap 1</th>
                        <th rowspan="2" style="width: 4%;">Tahap 2</th>
                    </tr>
                    <tr>
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
                    <tr class="main-category">
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
                    <tr class="sub-category">
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
                    <tr class="uraian-category">
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
                    <tr class="total-row">
                        <td colspan="12" class="text-center"><strong>JUMLAH</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalBertambah, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalBerkurang, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalTahap1, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalTahap2, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <footer>
            <table style="width: 100%; margin-top: 20px; border: none;">
                <tr>
                    <!-- Kolom Komite -->
                    <td style="width: 33%; text-align: center; border: none;">
                        <p>Ketua Komite,</p>
                        <div style="margin-top: 60px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold;">{{ $dataSekolah['komite']
                            }}</p>
                    </td>

                    <!-- Kolom Kepala Sekolah -->
                    <td style="width: 33%; text-align: center; border: none;">
                        <p>Mengetahui,</p>
                        <p>Kepala Sekolah,</p>
                        <div style="margin-top: 60px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold;">{{
                            $dataSekolah['kepala_sekolah'] }}</p>
                        <p style="margin: 0;">NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
                    </td>

                    <!-- Kolom Bendahara -->
                    <td style="width: 33%; text-align: center; border: none;">
                        <p>{{ $dataSekolah['kabupaten'] }}, {{ $penganggaran->format_tanggal_perubahan ?? date('d/m/Y') }}
                        </p>
                        <p>Bendahara,</p>
                        <div style="margin-top: 60px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold;">{{
                            $dataSekolah['bendahara'] }}</p>
                        <p style="margin: 0;">NIP. {{ $dataSekolah['nip_bendahara'] }}</p>
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>