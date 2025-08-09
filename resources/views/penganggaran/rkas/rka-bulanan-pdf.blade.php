<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>RKAS Bulanan - {{ $bulan }} {{ $dataSekolah['tahun_anggaran'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin-bottom: 5px;
        }

        .header p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .section-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
        }

        .footer {
            margin-top: 50px;
        }

        .signature {
            width: 100%;
            margin-top: 50px;
        }

        .signature td {
            border: none;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RENCANA KERTAS KERJA BULAN {{ strtoupper($bulan) }}</h1>
        <p>TAHUN ANGGARAN : {{ $dataSekolah['tahun_anggaran'] }}</p>
        <p>NPSN : {{ $dataSekolah['npsn'] }}</p>
        <p>Nama Sekolah : {{ $dataSekolah['nama'] }}</p>
        <p>Alamat : {{ $dataSekolah['alamat'] }}</p>
        <p>Kabupaten : {{ $dataSekolah['kabupaten'] }}</p>
        <p>Provinsi : {{ $dataSekolah['provinsi'] }}</p>
    </div>

    <div class="section-title">A. PENERIMAAN</div>
    <table>
        <thead>
            <tr>
                <th>No Kode</th>
                <th>Penerimaan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penerimaan['items'] as $item)
            <tr>
                <td>{{ $item['kode'] }}</td>
                <td>{{ $item['uraian'] }}</td>
                <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>Total Penerimaan</strong></td>
                <td class="text-right"><strong>{{ number_format($penerimaan['total'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">B. BELANJA</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No.</th>
                <th style="width: 15%">Kode Rekening</th>
                <th style="width: 15%">Kode Program</th>
                <th style="width: 30%">Uraian</th>
                <th style="width: 10%">Volume</th>
                <th style="width: 10%">Satuan</th>
                <th style="width: 10%">Tarif Harga</th>
                <th style="width: 15%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @if(!empty($belanja))
            @foreach($belanja as $kodeProgram => $program)
            <!-- Program Row -->
            <tr>
                <td class="text-center">{{ $counter++ }}</td>
                <td></td>
                <td>{{ $kodeProgram }}</td>
                <td>{{ $program['uraian'] }}</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td class="text-right">{{ number_format($program['total'], 0, ',', '.') }}</td>
            </tr>

            @if(!empty($program['sub_programs']))
            @foreach($program['sub_programs'] as $kodeSubProgram => $subProgram)
            <!-- Sub Program Row -->
            <tr>
                <td class="text-center">{{ $counter++ }}</td>
                <td></td>
                <td>{{ $kodeSubProgram }}</td>
                <td>{{ $subProgram['uraian'] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">{{ number_format($subProgram['total'], 0, ',', '.') }}</td>
            </tr>

            @if(!empty($subProgram['uraian_programs']))
            @foreach($subProgram['uraian_programs'] as $kodeUraian => $uraian)
            <!-- Uraian Program Row -->
            <tr>
                <td class="text-center">{{ $counter++ }}</td>
                <td></td>
                <td>{{ $kodeUraian }}</td>
                <td>{{ $uraian['uraian'] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">{{ number_format($uraian['total'], 0, ',', '.') }}</td>
            </tr>

            @if(!empty($uraian['items']))
            @foreach($uraian['items'] as $item)
            <!-- Item Detail Row -->
            <tr>
                <td class="text-center">{{ $counter++ }}</td>
                <td>{{ $item['kode_rekening'] }}</td>
                <td>{{ $kodeUraian }}</td>
                <td>{{ $item['uraian'] }}</td>
                <td class="text-center">{{ $item['volume'] }}</td>
                <td class="text-center">{{ $item['satuan'] }}</td>
                <td class="text-right">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif
            @endforeach
            @endif
            @endforeach
            @endif
            @endforeach
            @else
            <tr>
                <td colspan="8" class="text-center">Tidak ada data belanja untuk bulan {{ $bulan }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7"><strong>Jumlah</strong></td>
                <td class="text-right"><strong>{{ number_format($totalBelanja, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah,<br><br><br><br>
                <strong>{{ $dataSekolah['kepala_sekolah'] }}</strong><br>
                NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}
            </td>
            <td>
                {{ $dataSekolah['kabupaten'] }}, {{ $penganggaran->format_tanggal_cetak }}<br>
                Bendahara,<br><br><br><br>
                <strong>{{ $dataSekolah['bendahara'] }}</strong><br>
                NIP. {{ $dataSekolah['nip_bendahara'] }}
            </td>
        </tr>
    </table>
</body>

</html>