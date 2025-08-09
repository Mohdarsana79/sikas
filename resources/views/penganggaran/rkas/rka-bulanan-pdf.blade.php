<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>RKAS Bulanan - {{ $bulan }} {{ $dataSekolah['tahun_anggaran'] }}</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 14pt;
            text-decoration: underline;
        }

        .header p {
            margin: 2px 0;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 3px;
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
            margin: 10px 0 5px 0;
            font-size: 11pt;
        }

        .footer {
            margin-top: 30px;
        }

        .signature {
            width: 100%;
            margin-top: 30px;
        }

        .signature-table {
            width: 100%;
            border: none;
        }

        .signature td {
            border: none;
            text-align: center;
            vertical-align: bottom;
            padding: 0;
        }

        .signature p {
            margin: 0;
            padding: 0;
        }

        .signature .nama {
            margin-top: 60px;
            font-weight: bold;
        }

        .no-border {
            border: none !important;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RENCANA KERTAS KERJA PER BULAN {{ strtoupper($bulan) }}</h1>
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
                <th style="width: 15%">No Kode</th>
                <th style="width: 60%">Penerimaan</th>
                <th style="width: 25%">Jumlah</th>
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
                <td colspan="2" class="bold">Total Penerimaan</td>
                <td class="text-right bold">{{ number_format($penerimaan['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">B. BELANJA</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No.</th>
                <th style="width: 10%">Kode Rekening</th>
                <th style="width: 10%">Kode Program</th>
                <th style="width: 25%">Uraian</th>
                <th style="width: 8%">Volume</th>
                <th style="width: 8%">Satuan</th>
                <th style="width: 12%">Tarif Harga</th>
                <th style="width: 12%">Jumlah</th>
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
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
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
                <td colspan="7" class="bold">Jumlah</td>
                <td class="text-right bold">{{ number_format($totalBelanja, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature">
        <tr>
            <td>
                <p>Komite Sekolah,</p>
                <p class="nama">{{ $dataSekolah['komite'] }}</p>
            </td>
            <td>
                <p>Mengetahui,</p>
                <p>Kepala Sekolah,</p>
                <p class="nama">{{ $dataSekolah['kepala_sekolah'] }}</p>
                <p>NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
            </td>
            <td>
                <p>{{ $dataSekolah['kabupaten'] }}, {{ $penganggaran->format_tanggal_cetak }}</p>
                <p>Bendahara,</p>
                <p class="nama">{{ $dataSekolah['bendahara'] }}</p>
                <p>NIP. {{ $dataSekolah['nip_bendahara'] }}</p>
            </td>
        </tr>
    </table>
</body>

</html>