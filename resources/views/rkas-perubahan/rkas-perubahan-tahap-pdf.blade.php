<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RKAS TAHAPAN PERUBAHAN</title>
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
            background-color: #fcda8b;
        }

        .sub-category {
            font-weight: bold;
            background-color: #bffa98;
        }

        .uraian-category {
            font-weight: bold;
            background-color: #acfad7;
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
            <h1 style="font-size: {{ $printSettings['font_size'] }};">RENCANA KEGIATAN DAN ANGGARAN SEKOLAH (RKAS) PERUBAHAN PER TAHAP</h1>
            <p style="font-size: {{ $printSettings['font_size'] }};">TAHUN ANGGARAN : {{ $dataSekolah['tahun_anggaran'] }}</p>
            <table class="no-border" style="width: auto; border: none; font-size: {{ $printSettings['font_size'] }};">
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
                <thead>
                    <tr>
                        <th rowspan="3" style="width: 3%; font-size: {{ $printSettings['font_size'] }};">No.</th>
                        <th rowspan="3" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Kode Rekening</th>
                        <th rowspan="3" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Kode Program</th>
                        <th rowspan="3" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                        <th colspan="4" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Rincian Perhitungan Sebelum Perubahan</th>
                        <th colspan="4" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Rincian Perhitungan Sesudah Perubahan</th>
                        <th colspan="2" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Perubahan</th>
                        <th colspan="2" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Tahap</th>
                    </tr>
                    <tr>
                        <th colspan="4" style="font-size: {{ $printSettings['font_size'] }};">Sebelum Perubahan</th>
                        <th colspan="4" style="font-size: {{ $printSettings['font_size'] }};">Sesudah Perubahan</th>
                        <th rowspan="2" style="width: 4%; font-size: {{ $printSettings['font_size'] }};">Bertambah</th>
                        <th rowspan="2" style="width: 4%; font-size: {{ $printSettings['font_size'] }};">Berkurang</th>
                        <th rowspan="2" style="width: 4%; font-size: {{ $printSettings['font_size'] }};">Tahap 1</th>
                        <th rowspan="2" style="width: 4%; font-size: {{ $printSettings['font_size'] }};">Tahap 2</th>
                    </tr>
                    <tr>
                        <!-- Sebelum Perubahan -->
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Volume</th>
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Satuan</th>
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Tarif</th>
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>

                        <!-- Sesudah Perubahan -->
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Volume</th>
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Satuan</th>
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Tarif</th>
                        <th style="width: 5%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach ($belanja as $kodeProgram => $program)
                    <!-- Baris Program -->
                    <tr class="main-category">
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                        <td></td>
                        <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeProgram }}</td>
                        <td style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ $program['uraian'] }}</strong></td>

                        <!-- Sebelum Perubahan - Program -->
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['total_asli'], 0, ',', '.') }}</td>

                        <!-- Sesudah Perubahan - Program -->
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['total_perubahan'], 0, ',', '.') }}</td>

                        <!-- Perubahan -->
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>

                        <!-- Tahap -->
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['tahap1'], 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['tahap2'], 0, ',', '.') }}</td>
                    </tr>

                    @foreach ($program['sub_programs'] as $kodeSubProgram => $subProgram)
                    <!-- Baris Sub Program -->
                    <tr class="sub-category">
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                        <td></td>
                        <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeSubProgram }}</td>
                        <td style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ $subProgram['uraian'] }}</strong></td>

                        <!-- Sebelum Perubahan - Sub Program -->
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['total_asli'], 0, ',', '.') }}</td>

                        <!-- Sesudah Perubahan - Sub Program -->
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['total_perubahan'], 0, ',', '.') }}</td>

                        <!-- Perubahan -->
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>

                        <!-- Tahap -->
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['tahap1'], 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['tahap2'], 0, ',', '.') }}</td>
                    </tr>

                    @foreach ($subProgram['uraian_programs'] as $kodeUraian => $uraian)
                    <!-- Baris Uraian Program -->
                    <tr class="uraian-category">
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                        <td></td>
                        <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeUraian }}</td>
                        <td style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ $uraian['uraian'] }}</strong></td>

                        <!-- Sebelum Perubahan - Uraian -->
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($uraian['total_asli'], 0, ',', '.') }}</td>

                        <!-- Sesudah Perubahan - Uraian -->
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($uraian['total_perubahan'], 0, ',', '.') }}</td>

                        <!-- Perubahan -->
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>

                        <!-- Tahap -->
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

                        <!-- Sebelum Perubahan - Detail -->
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['volume_asli'] }}</td>
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['satuan'] }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['harga_satuan_asli'], 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['jumlah_asli'], 0, ',', '.') }}</td>

                        <!-- Sesudah Perubahan - Detail -->
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['volume_perubahan'] }}</td>
                        <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['satuan'] }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['harga_satuan_perubahan'], 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['jumlah_perubahan'], 0, ',', '.') }}</td>

                        <!-- Perubahan -->
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['bertambah'], 0, ',', '.') }}</td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['berkurang'], 0, ',', '.') }}</td>

                        <!-- Tahap -->
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
                        <td colspan="12" class="text-center"><strong>JUMLAH</strong></td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ number_format($totalBertambah, 0, ',', '.') }}</strong></td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ number_format($totalBerkurang, 0, ',', '.') }}</strong></td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ number_format($totalTahap1, 0, ',', '.') }}</strong></td>
                        <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ number_format($totalTahap2, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <footer>
            <table style="width: 100%; margin-top: 20px; border: none; font-size: {{ $printSettings['font_size'] }};">
                <tr>
                    <!-- Kolom Komite -->
                    <td style="width: 33%; text-align: center; border: none; font-size: {{ $printSettings['font_size'] }};">
                        <p style="font-size: {{ $printSettings['font_size'] }};">Ketua Komite,</p>
                        <div style="margin-top: 60px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold; font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['komite']
                            }}</p>
                    </td>

                    <!-- Kolom Kepala Sekolah -->
                    <td style="width: 33%; text-align: center; border: none; font-size: {{ $printSettings['font_size'] }};">
                        <p style="font-size: {{ $printSettings['font_size'] }};">Mengetahui,</p>
                        <p style="font-size: {{ $printSettings['font_size'] }};">Kepala Sekolah,</p>
                        <div style="margin-top: 60px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold; font-size: {{ $printSettings['font_size'] }};">{{
                            $dataSekolah['kepala_sekolah'] }}</p>
                        <p style="margin: 0; font-size: {{ $printSettings['font_size'] }};">NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
                    </td>

                    <!-- Kolom Bendahara -->
                    <td style="width: 33%; text-align: center; border: none; font-size: {{ $printSettings['font_size'] }};">
                        <p style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['kecamatan'] }}, {{ $penganggaran->format_tanggal_perubahan ?? date('d/m/Y') }}
                        </p>
                        <p style="font-size: {{ $printSettings['font_size'] }};">Bendahara,</p>
                        <div style="margin-top: 60px;"></div>
                        <p style="text-decoration: underline; margin: 0; font-weight: bold; font-size: {{ $printSettings['font_size'] }};">{{
                            $dataSekolah['bendahara'] }}</p>
                        <p style="margin: 0; font-size: {{ $printSettings['font_size'] }};">NIP. {{ $dataSekolah['nip_bendahara'] }}</p>
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>