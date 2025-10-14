<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RKA Rekap - {{ $penganggaran->tahun_anggaran }}</title>
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

            font-size: {
                    {
                    $printSettings['font_size']
                }
            }

            ;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin-bottom: 5px;
        }

        .header p {
            margin: 0;
        }

        .section-header, .item-header {
            border: none;
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
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .footer {
            margin-top: 5px;
            margin-left: 60%;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            text-align: center;
            width: 200px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] }};">
    <!-- Header -->
    <div class="header" style="font-size: {{ $printSettings['font_size'] }};">
        <h2 style="font-size: {{ $printSettings['font_size'] }};">LEMBAR KERTAS KERJA</h2>
        <p style="font-size: {{ $printSettings['font_size'] }};">TAHUN ANGGARAN {{ $dataSekolah['tahun_anggaran'] }}</p>
    </div>

    <table class="section-header">
        <tr>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 20%;">Urusan Pemerintahan</td>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 80%;">: 1.01 - PENDIDIKAN</td>
        </tr>
        <tr>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 20%;">Organisasi</td>
            <td class="item-header" style="font-size: {{ $printSettings['font_size'] }}; width: 80%;">: {{$dataSekolah['nama']}}</td>
        </tr>
    </table>

    <!-- Penerimaan Section -->
    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">A. PENERIMAAN</div>
    <div class="subsection-title" style="font-size: {{ $printSettings['font_size'] }};"><strong>Sumber Dana :</strong></div>
    <table class="sub-section" style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th class="text-center" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">No Kode</th>
                <th class="text-center" style="width: 60%; font-size: {{ $printSettings['font_size'] }};">Penerimaan</th>
                <th class="text-center" style="width: 25%; font-size: {{ $printSettings['font_size'] }};">Jumlah (Rp)</th>
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
            <tr>
                <td colspan="2" style="font-size: {{ $printSettings['font_size'] }};"><strong>Total Penerimaan</strong></td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{ number_format($penerimaan['total'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Belanja Section -->
    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">B. REKAPITULASI ANGGARAN</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 15%;" style="font-size: {{ $printSettings['font_size'] }};">Kode Rekening</th>
                <th class="text-center" style="width: 65%;" style="font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th class="text-center" style="width: 20%;" style="font-size: {{ $printSettings['font_size'] }};">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapData as $item)
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">
                    @if($item['jumlah'] === '-')
                    -
                    @else
                    {{ number_format($item['jumlah'], 0, ',', '.') }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tahapan Section -->
    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">C. RENCANA PELAKSANAAN ANGGARAN PER TAHAP</div>
    <table style="font-size: {{ $printSettings['font_size'] }};">
        <thead style="font-size: {{ $printSettings['font_size'] }};">
            <tr>
                <th class="text-center" rowspan="2" style="width: 5%; font-size: {{ $printSettings['font_size'] }};">No</th>
                <th class="text-center" rowspan="2" style="width: 50%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th class="text-center" colspan="2" style="width: 30%; font-size: {{ $printSettings['font_size'] }};">Tahap</th>
                <th class="text-center" rowspan="2" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
            </tr>
            <tr>
                <th class="text-center" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">I</th>
                <th class="text-center" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">II</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">1</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Pendapatan</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($penerimaan['total'] / 2, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($penerimaan['total'] / 2, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($penerimaan['total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">2.1</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Belanja Operasi</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalTahap1, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalTahap2, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">{{ number_format($totalTahap1 + $totalTahap2, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">2.2</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Belanja Modal</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">0</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">0</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">0</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Signature -->
    <div class="footer" style="font-size: {{ $printSettings['font_size'] }};">
        <div></div>
        <div class="signature" style="font-size: {{ $printSettings['font_size'] }};">
            {{ $dataSekolah['kabupaten'] }}, {{ $tanggalCetak['tanggal_cetak'] }}
            <br>
            <span>Kepala Sekolah</span>
            <br><br><br><br>
            <strong style="font-size: {{ $printSettings['font_size'] }};">{{ $dataSekolah['kepala_sekolah'] }}</strong><br>
            NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}
        </div>
    </div>
</body>

</html>