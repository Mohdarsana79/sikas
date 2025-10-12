<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RKA Rekap</title>
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
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .sumber-dana,
        .item-sumber-dana,
        {
            border: 1px solid black;
        }

        .rekapitulasi-anggaran,
        .item-rekapitulasi,
        {
            border: 1px solid black;
        }

        .anggaran-pertahap,
        .item-pertahap,
        {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .urusan-pemerintahan{
            border: none;
        }

        .text-right {
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .subsection-title {
            font-weight: bold;
            margin: 10px 0;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            text-align: center;
            margin-left: 50%;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] }};">
    <!-- Header -->
    <div class="header">
        <h2 style="margin-bottom: 5px; font-size: {{ $printSettings['font_size'] }};">LEMBAR KERTAS KERJA</h2>
        <p style="margin: 0; font-size: {{ $printSettings['font_size'] }};">REKAPAN</p>
        <p style="margin: 0; font-size: {{ $printSettings['font_size'] }};">TAHUN ANGGARAN {{
            $dataSekolah['tahun_anggaran'] }}</p>
    </div>

    <table border="0" clas="urusan-pemerintahan" style="font-size: {{ $printSettings['font_size'] }};">
        <tr>
            <td>Urusan Pemerintahan</td>
            <td>: 1.01 - PENDIDIKAN</td>
        </tr>
        <tr>
            <td>Organisasi</td>
            <td>: {{ $dataSekolah['nama'] }}</td>
        </tr>
    </table>

    <!-- Penerimaan Section -->
    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">A. PENERIMAAN</div>
    <div class="subsection-title" style="font-size: {{ $printSettings['font_size'] }};"><strong>Sumber Dana :</strong>
    </div>
    <table class="sumber-Dana" style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th class="item-sumber-dana" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">No Kode</th>
                <th class="item-sumber-dana" style="width: 60%; font-size: {{ $printSettings['font_size'] }};">Penerimaan</th>
                <th class="item-sumber-dana" style="width: 25%; font-size: {{ $printSettings['font_size'] }};">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penerimaan['items'] as $item)
            <tr>
                <td class="item-sumber-dana" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode'] }}</td>
                <td class="item-sumber-dana" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                <td class="item-sumber-dana text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="item-sumber-dana" colspan="2" style="font-size: {{ $printSettings['font_size'] }};"><strong>Total Penerimaan</strong>
                </td>
                <td class="item-sumber-dana text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>{{
                        number_format($penerimaan['total'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Belanja Section -->
    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">B. REKAPITULASI ANGGARAN</div>
    <table class="rekapitulasi-anggaran" style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th class="item-rekapitulasi" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">Kode Rekening</th>
                <th class="item-rekapitulasi" style="width: 65%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th class="item-rekapitulasi" style="width: 20%; font-size: {{ $printSettings['font_size'] }};">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapData as $item)
            <tr>
                <td class="item-rekapitulasi" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['kode'] }}</td>
                <td class="item-rekapitulasi" style="font-size: {{ $printSettings['font_size'] }};">{{ $item['uraian'] }}</td>
                <td class="item-rekapitulasi text-right" style="font-size: {{ $printSettings['font_size'] }};">
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
    <div class="section-title" style="font-size: {{ $printSettings['font_size'] }};">C. RENCANA PELAKSANAAN ANGGARAN PER
        TAHAP</div>
    <table class="anggaran-pertahap" style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th class="item-pertahap" rowspan="2" style="width: 5%; font-size: {{ $printSettings['font_size'] }};">No</th>
                <th class="item-pertahap" rowspan="2" style="width: 50%; font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th class="item-pertahap" colspan="2" style="width: 30%; font-size: {{ $printSettings['font_size'] }};">Tahap</th>
                <th class="item-pertahap" rowspan="2" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
            </tr>
            <tr>
                <th class="item-pertahap text-center" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">I</th>
                <th class="item-pertahap text-center" style="width: 15%; font-size: {{ $printSettings['font_size'] }};">II</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="item-pertahap" style="font-size: {{ $printSettings['font_size'] }};">1</td>
                <td class="item-pertahap" style="font-size: {{ $printSettings['font_size'] }};">Pendapatan</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($penerimaan['total'] / 2, 0, ',', '.') }}</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($penerimaan['total'] / 2, 0, ',', '.') }}</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($penerimaan['total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="item-pertahap" style="font-size: {{ $printSettings['font_size'] }};">2.1</td>
                <td class="item-pertahap" style="font-size: {{ $printSettings['font_size'] }};">Belanja Operasi</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($totalTahap1, 0, ',', '.') }}</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($totalTahap2, 0, ',', '.') }}</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">{{
                    number_format($totalTahap1 + $totalTahap2, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="item-pertahap" style="font-size: {{ $printSettings['font_size'] }};">2.2</td>
                <td class="item-pertahap" style="font-size: {{ $printSettings['font_size'] }};">Belanja Modal</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">0</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">0</td>
                <td class="item-pertahap text-right" style="font-size: {{ $printSettings['font_size'] }};">0</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Signature -->
    <div class="footer">
        <div></div>
        <div class="signature" style="font-size: {{ $printSettings['font_size'] }};">
            {{ $dataSekolah['kabupaten'] }}, {{ $tanggalPerubahan['tanggal_perubahan'] }}
            <br><br><br><br>
            <strong>{{ $dataSekolah['kepala_sekolah'] }}</strong><br>
            <p>NIP. {{ $dataSekolah['nip_kepala_sekolah'] }}</p>
        </div>
    </div>
</body>

</html>