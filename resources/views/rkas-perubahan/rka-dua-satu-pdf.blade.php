<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RKA 221 - {{ $sekolah->nama_sekolah }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h4 {
            margin-bottom: 5px;
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
            margin-top: 50px;
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

<body>
    <!-- Header -->
    <div class="header">
        <h4>LEMBAR KERTAS KERJA</h4>
        <p>UNIT KERJA</p>
        <p>PEMERINTAH KOTA BOGOR</p>
        <p>TAHUN ANGGARAN {{ $penganggaran->tahun_anggaran }}</p>
        <p>Pemerintahan : 1.01 - PENDIDIKAN</p>
        <p>Organisasi : {{ $sekolah->nama_sekolah }}</p>
        <p>Lokasi Kegiatan : {{ $sekolah->alamat }}</p>
    </div>

    <!-- Tabel Indikator Kinerja -->
    <h5>Indikator & Tolok Ukur Kinerja Belanja Langsung</h5>
    <table>
        <thead>
            <tr>
                <th>Indikator</th>
                <th>Tolok Ukur Kinerja</th>
                <th>Target Kinerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($indikatorKinerja as $indikator)
            <tr>
                <td>{{ $indikator['indikator'] }}</td>
                <td>{{ $indikator['tolok_ukur'] }}</td>
                <td class="text-right">
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
    <h5>Rincian Anggaran Belanja Langsung Menurut Program dan Per Kegiatan Unit Kerja</h5>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 10%">Kode Rekening</th>
                <th rowspan="2" style="width: 30%">Uraian</th>
                <th colspan="3">Rincian Perhitungan</th>
                <th rowspan="2" style="width: 15%">Jumlah (Rp)</th>
            </tr>
            <tr>
                <th style="width: 10%">Volume</th>
                <th style="width: 10%">Satuan</th>
                <th style="width: 15%">Harga Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mainStructure as $kode => $uraian)
            <!-- Tampilkan kode utama -->
            <tr class="main-code">
                <td>{{ $kode }}</td>
                <td>{{ $uraian }}</td>
                <td colspan="3"></td>
                <td class="text-right">
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
                <td class="sub-code">{{ $item['kode_rekening'] }}</td>
                <td>{{ $item['uraian'] }}</td>
                <td class="text-center">{{ $item['volume'] }}</td>
                <td class="text-center">{{ $item['satuan'] }}</td>
                <td class="text-right">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif
            @endforeach

            <!-- Jumlah Total -->
            <tr>
                <td colspan="5" class="text-center"><strong>Jumlah</strong></td>
                <td class="text-right"><strong>{{ number_format($total_anggaran, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="footer">
        <div class="signature">
            <p>{{ $sekolah->kabupaten_kota }}, {{ $penganggaran->format_tanggal_perubahan }}</p>
            <p>Mengetahui,</p>
            <p>Kepala Sekolah</p>
            <br><br><br>
            <p><strong>{{ $penganggaran->kepala_sekolah }}</strong></p>
            <p>NIP. {{ $penganggaran->nip_kepala_sekolah }}</p>
        </div>
    </div>
</body>

</html>