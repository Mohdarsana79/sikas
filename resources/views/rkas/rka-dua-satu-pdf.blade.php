<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RKA 221 - {{ $sekolah->nama_sekolah }}</title>
    <style>
        @page {
            size: {
                    {
                    $printSettings['ukuran_kertas']
                }
            }

                {
                    {
                    $printSettings['orientasi']
                }
            }

            ;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h4 {
            margin-bottom: 5px;
        }

        .section-header, .item-header {
            border: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 5px;
        }

        .signature {
            float: right;
            text-align: center;
            width: 300px;
        }

        .main-code {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .sub-code {
            padding-left: 20px;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] }};">
    <!-- Header -->
    <div class="header" style="font-size: {{ $printSettings['font_size'] }};">
        <h4 style="font-size: {{ $printSettings['font_size'] }};">LEMBAR KERTAS KERJA</h4>
        <p style="font-size: {{ $printSettings['font_size'] }};">TAHUN ANGGARAN {{ $penganggaran->tahun_anggaran }}</p>
    </div>

    <table class="section-header">
        <tr>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 20%;">Urusan Pemerintahan
            </td>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 80%;">: 1.01 - PENDIDIKAN
            </td>
        </tr>
        <tr>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 20%;">Organisasi</td>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 80%;">:
                {{ $sekolah->nama_sekolah }}</td>
        </tr>
    </table>

    <!-- Tabel Indikator Kinerja -->
    <h5 style="font-size: {{ $printSettings['font_size'] }};">Indikator & Tolok Ukur Kinerja Belanja Langsung</h5>
    <table style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th style="font-size: {{ $printSettings['font_size'] }};">Indikator</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">Tolok Ukur Kinerja</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">Target Kinerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($indikatorKinerja as $indikator)
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $indikator['indikator'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $indikator['tolok_ukur'] }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                    @if($indikator['target'] > 0)
                    {{ number_format($indikator['target'], 0, ',', '.') }}
                    @else
                    -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tabel Rincian Anggaran -->
    <h5 style="font-size: {{ $printSettings['font_size'] }};">Rincian Anggaran Belanja Langsung Menurut Program dan Per Kegiatan Unit Kerja</h5>
    <table style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th rowspan="2" style="width: 10%; font-size: {{ $printSettings['font_size'] }};">Kode Rekening</th>
                <th rowspan="2" style="width: 30%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th colspan="3">Rincian Perhitungan</th>
                <th rowspan="2" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">Jumlah (Rp)</th>
            </tr>
            <tr>
                <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">Volume</th>
                <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">Satuan</th>
                <th style="width: 15%; font-size: {{ $printSettings['font_size'] }};">Harga Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mainStructure as $kode => $uraian)
            <!-- Tampilkan kode utama -->
            <tr class="main-code">
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kode }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $uraian }}</td>
                <td colspan="3"></td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                    @if($totals[$kode] > 0)
                    {{ number_format($totals[$kode], 0, ',', '.') }}
                    @else
                    -
                    @endif
                </td>
            </tr>

            <!-- Tampilkan detail item yang termasuk dalam kode ini -->
            @if(isset($groupedItems[$kode]))
            @foreach($groupedItems[$kode] as $item)
            <tr>
                <td class="sub-code" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode_rekening'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['volume'] }}</td>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['satuan'] }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif
            @endforeach

            <!-- Jumlah Total -->
            <tr>
                <td colspan="5" class="text-center" style="font-size: {{ $printSettings['font_size'] }};"><strong>Jumlah</strong></td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ number_format($total_anggaran, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="footer" style="font-size: {{ $printSettings['font_size'] }};">
        <div class="signature" style="font-size: {{ $printSettings['font_size'] }};">
            <p style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->kabupaten_kota }}, {{ $penganggaran->format_tanggal_cetak }}</p>
            <p style="font-size: {{ $printSettings['font_size'] }};">Mengetahui,</p>
            <p style="font-size: {{ $printSettings['font_size'] }};">Kepala Sekolah</p>
            <br><br><br>
            <p style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ $penganggaran->kepala_sekolah }}</strong></p>
            <p style="font-size: {{ $printSettings['font_size'] }};">NIP. {{ $penganggaran->nip_kepala_sekolah }}</p>
        </div>
    </div>
</body>

</html>