<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>RKAS Bulanan - {{ $bulan }} {{ $dataSekolah['tahun_anggaran'] }}</title>
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
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;

            font-size: {
                    {
                    $printSettings['font_size']
                }
            }

            ;
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
            page-break-inside: always;
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

        .bg-coklat-cyan{
            background-color: #fcda8b;
        }

        .bg-green-cyan{
            background-color: #bffa98;
        }

        .bg-blue-cyan{
            background-color: #acfad7;
        }

        .bg-gray-cyan{
            background-color: #eeeded;
        }

        .bold{
            font-weight: bold;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] }};">
    <div class="header" style="font-size: {{ $printSettings['font_size'] }};">
        <h1 style="font-size: {{ $printSettings['font_size'] }};">RENCANA KERTAS KERJA PER BULAN {{ strtoupper($bulan) }}</h1>
        <p style="font-size: {{ $printSettings['font_size'] }};">TAHUN ANGGARAN : {{ $dataSekolah['tahun_anggaran'] }}</p>
    </div>

    <table class="no-border" style="font-size: {{ $printSettings['font_size'] }};">
        <tr>
            <td class="no-border" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">NPSN</td>
            <td class="no-border" style="width: 2%; font-size: {{ $printSettings['font_size'] }};">:</td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['npsn'] }}</td>
        </tr>
        <tr>
            <td class="no-border" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Nama Sekolah</td>
            <td class="no-border" style="width: 2%; font-size: {{ $printSettings['font_size'] }};">:</td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['nama'] }}</td>
        </tr>
        <tr>
            <td class="no-border" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Alamat</td>
            <td class="no-border" style="width: 2%; font-size: {{ $printSettings['font_size'] }};">:</td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['alamat'] }}</td>
        </tr>
        <tr>
            <td class="no-border" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Kabupaten</td>
            <td class="no-border" style="width: 2%; font-size: {{ $printSettings['font_size'] }};">:</td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['kabupaten'] }}</td>
        </tr>
        <tr>
            <td class="no-border" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Provinsi</td>
            <td class="no-border" style="width: 2%; font-size: {{ $printSettings['font_size'] }};">:</td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['provinsi'] }}</td>
        </tr>
    </table>

    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">A. PENERIMAAN</div>
    <table style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th class="bg-gray-cyan bold" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">No Kode</th>
                <th class="bg-gray-cyan bold" style="width: 60%; font-size: {{ $printSettings['font_size'] }};">Penerimaan</th>
                <th class="bg-gray-cyan bold" style="width: 25%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penerimaan['items'] as $item)
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="bold" style="font-size: {{ $printSettings['font_size'] }};">Total Penerimaan</td>
                <td class="text-right bold" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($penerimaan['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">B. BELANJA</div>
    <table style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th class="bg-gray-cyan" style="width: 5%; font-size: {{ $printSettings['font_size'] }};">No.</th>
                <th class="bg-gray-cyan" style="width: 10%; font-size: {{ $printSettings['font_size'] }};">Kode Rekening</th>
                <th class="bg-gray-cyan" style="width: 10%; font-size: {{ $printSettings['font_size'] }};">Kode Program</th>
                <th class="bg-gray-cyan" style="width: 25%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th class="bg-gray-cyan" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Volume</th>
                <th class="bg-gray-cyan" style="width: 8%; font-size: {{ $printSettings['font_size'] }};">Satuan</th>
                <th class="bg-gray-cyan" style="width: 12%; font-size: {{ $printSettings['font_size'] }};">Tarif Harga</th>
                <th class="bg-gray-cyan" style="width: 12%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @if(!empty($belanja))
            @foreach($belanja as $kodeProgram => $program)
            <!-- Program Row -->
            <tr>
                <td class="text-center bg-coklat-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                <td class="bg-coklat-cyan"></td>
                <td class="bg-coklat-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeProgram }}</td>
                <td class="bg-coklat-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $program['uraian'] }}</td>
                <td class="text-center bg-coklat-cyan">-</td>
                <td class="text-center bg-coklat-cyan">-</td>
                <td class="text-center bg-coklat-cyan">-</td>
                <td class="text-right bg-coklat-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($program['total'], 0, ',', '.') }}</td>
            </tr>

            @if(!empty($program['sub_programs']))
            @foreach($program['sub_programs'] as $kodeSubProgram => $subProgram)
            <!-- Sub Program Row -->
            <tr>
                <td class="text-center bg-green-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                <td class="bg-green-cyan bold"></td>
                <td class="bg-green-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeSubProgram }}</td>
                <td class="bg-green-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $subProgram['uraian'] }}</td>
                <td class="bg-green-cyan"></td>
                <td class="bg-green-cyan"></td>
                <td class="bg-green-cyan"></td>
                <td class="text-right bg-green-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($subProgram['total'], 0, ',', '.') }}</td>
            </tr>

            @if(!empty($subProgram['uraian_programs']))
            @foreach($subProgram['uraian_programs'] as $kodeUraian => $uraian)
            <!-- Uraian Program Row -->
            <tr>
                <td class="text-center bg-blue-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                <td class="bg-blue-cyan bold"></td>
                <td class="bg-blue-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeUraian }}</td>
                <td class="bg-blue-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ $uraian['uraian'] }}</td>
                <td class="bg-blue-cyan"></td>
                <td class="bg-blue-cyan"></td>
                <td class="bg-blue-cyan"></td>
                <td class="text-right bg-blue-cyan bold" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($uraian['total'], 0, ',', '.') }}</td>
            </tr>

            @if(!empty($uraian['items']))
            @foreach($uraian['items'] as $item)
            <!-- Item Detail Row -->
            <tr>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $counter++ }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode_rekening'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $kodeUraian }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['volume'] }}</td>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['satuan'] }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
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
                <td colspan="8" class="text-center" style="font-size: {{ $printSettings['font_size'] }};">Tidak ada data belanja untuk bulan {{ $bulan }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="bold" style="font-size: {{ $printSettings['font_size'] }};">Jumlah</td>
                <td class="text-right bold" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalBelanja, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature no-border" style="font-size: {{ $printSettings['font_size'] }};">
        <tr>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">
                <p style="padding-bottom: 60px">Komite Sekolah,</p>
                <p style="padding-bottom: 10px; font-weight: bold;">{{ $dataSekolah['komite'] }}</p>
                
            </td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">
                <p>Mengetahui,</p>
                <p>Kepala Sekolah,</p>
                <p class="nama">{{ $dataSekolah['kepala_sekolah'] }}</p>
                <p>NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
            </td>
            <td class="no-border" style="font-size: {{ $printSettings['font_size'] }};">
                <p>{{ $dataSekolah['kabupaten'] }}, {{ $penganggaran->format_tanggal_cetak }}</p>
                <p>Bendahara,</p>
                <p class="nama">{{ $dataSekolah['bendahara'] }}</p>
                <p>NIP. {{ $dataSekolah['nip_bendahara'] }}</p>
            </td>
        </tr>
    </table>
</body>

</html>