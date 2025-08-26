<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RKA Rekap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
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

        .text-right {
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .footer {
            margin-top: 50px;
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

<body>
    <!-- Header -->
    <div class="header">
        <h2>LEMBAR KERTAS KERJA</h2>
        <p>UNIT KERJA</p>
        <p>PEMERINTAH KOTA BOGOR</p>
        <p>TAHUN ANGGARAN {{ $dataSekolah['tahun_anggaran'] }}</p>
        <p>Urusan Pemerintahan : 1.01 - PENDIDIKAN</p>
        <p>Organisasi : {{ $dataSekolah['nama'] }}</p>
    </div>

    <!-- Penerimaan Section -->
    <div class="section-title">A. PENERIMAAN</div>
    <div class="subsection-title"><strong>Sumber Dana :</strong></div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">No Kode</th>
                <th style="width: 60%;">Penerimaan</th>
                <th style="width: 25%;">Jumlah (Rp)</th>
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
            <tr>
                <td colspan="2"><strong>Total Penerimaan</strong></td>
                <td class="text-right"><strong>{{ number_format($penerimaan['total'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Belanja Section -->
    <div class="section-title">B. REKAPITULASI ANGGARAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Kode Rekening</th>
                <th style="width: 65%;">Uraian</th>
                <th style="width: 20%;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapData as $item)
            <tr>
                <td>{{ $item['kode'] }}</td>
                <td>{{ $item['uraian'] }}</td>
                <td class="text-right">
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
    <div class="section-title">C. RENCANA PELAKSANAAN ANGGARAN PER TAHAP</div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No</th>
                <th rowspan="2" style="width: 50%;">Uraian</th>
                <th colspan="2" style="width: 30%;">Tahap</th>
                <th rowspan="2" style="width: 15%;">Jumlah</th>
            </tr>
            <tr>
                <th style="width: 15%;">I</th>
                <th style="width: 15%;">II</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Pendapatan</td>
                <td class="text-right">{{ number_format($penerimaan['total'] / 2, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($penerimaan['total'] / 2, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($penerimaan['total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2.1</td>
                <td>Belanja Operasi</td>
                <td class="text-right">{{ number_format($totalTahap1, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalTahap2, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalTahap1 + $totalTahap2, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2.2</td>
                <td>Belanja Modal</td>
                <td class="text-right">0</td>
                <td class="text-right">0</td>
                <td class="text-right">0</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Signature -->
    <div class="footer">
        <div></div>
        <div class="signature">
            {{ $dataSekolah['kabupaten'] }}, {{ $tanggalPerubahan['tanggal_perubahan'] }}
            <br><br><br><br>
            <strong>{{ $dataSekolah['kepala_sekolah'] }}</strong><br>
            NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}
        </div>
    </div>
</body>

</html>