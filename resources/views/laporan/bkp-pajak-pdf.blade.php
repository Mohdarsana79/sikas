<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>BKP Pajak - {{ $bulan }} {{ $tahun }}</title>
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
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .school-info {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 9pt;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
        }

        .signature {
            width: 100%;
            margin-top: 50px;
        }

        .signature-table {
            width: 100%;
            border: none;
        }

        .signature-table td {
            border: none;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 200px;
            display: inline-block;
        }

        .page-break {
            page-break-after: always;
        }

        .total-row {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body style="font-size: {{ $printSettings['font_size'] }};">
    <div class="header">
        <div class="report-title">
            BUKU PEMBANTU PAJAK<br>
            BULAN : {{ strtoupper($bulan) }} {{ $tahun }}
        </div>
    </div>

    <div class="section-info">
        <table style="border: none;">
            <tr>
                <td style="width: 10%; border: none;">NPSN</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">{{$sekolah->npsn}}</td>
            </tr>
            <tr>
                <td style="width: 10%; border: none;">Nama Sekolah</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">{{$sekolah->nama_sekolah}}</td>
            </tr>
            <tr>
                <td style="width: 10%; border: none;">Desa / Kelurahan</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">{{$sekolah->kelurahan_desa}}</td>
            </tr>
            <tr>
                <td style="width: 10%; border: none;">Kecamatan</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">{{$sekolah->kecamatan}}</td>
            </tr>
            <tr>
                <td style="width: 10%; border: none;">Kabupaten</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">{{$sekolah->kabupaten_kota}}</td>
            </tr>
            <tr>
                <td style="width: 10%; border: none;">Provinsi</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">{{$sekolah->provinsi}}</td>
            </tr>
            <tr>
                <td style="width: 10%; border: none;">Sumber Dana</td>
                <td style="width: 1%; border: none;">:</td>
                <td style="width: 89%; border: none;">BOSP Reguler</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%">Tanggal</th>
                <th width="8%">No. Kode</th>
                <th width="8%">No. Buku</th>
                <th width="30%">Uraian</th>
                <th width="8%">PPN</th>
                <th width="8%">PPh 21</th>
                <th width="8%">PPh 22</th>
                <th width="8%">PPh 23</th>
                <th width="8%">PB 1</th>
                <th width="8%">JML</th>
                <th width="8%">Pengeluaran (Kredit)</th>
                <th width="8%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
            // Safety check untuk $pajakRows
            $pajakRows = $pajakRows ?? [];
            @endphp

            <!-- Baris Saldo Awal -->
            <tr>
                <td>1-{{ $bulanAngka }}-{{ $tahun }}</td>
                <td>-</td>
                <td>-</td>
                <td>Saldo awal bulan</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">0</td>
            </tr>

            <!-- Data Transaksi Pajak -->
            @if(count($pajakRows) > 0)
            @foreach($pajakRows as $row)
            @php
            $transaksi = $row['transaksi'];
            @endphp

            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>{{ $transaksi->kode_masa_pajak ?? '-' }}</td>
                <td>{{ $transaksi->id_transaksi }}</td>
                <td>{{ $row['uraian'] }}</td>
                <td class="text-end">{{ $row['ppn'] > 0 ? number_format($row['ppn'], 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $row['pph21'] > 0 ? number_format($row['pph21'], 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $row['pph22'] > 0 ? number_format($row['pph22'], 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $row['pph23'] > 0 ? number_format($row['pph23'], 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $row['pb1'] > 0 ? number_format($row['pb1'], 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                <td class="text-end">{{ $row['pengeluaran'] > 0 ? number_format($row['pengeluaran'], 0, ',', '.') : '-'
                    }}</td>
                <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @else
            <!-- Jika tidak ada data pajak -->
            <tr>
                <td colspan="12" class="no-data">
                    Tidak ada data pajak untuk bulan {{ $bulan }} {{ $tahun }}
                </td>
            </tr>
            @endif

            <!-- Baris Jumlah Penutupan -->
            @if(count($pajakRows) > 0)
            <tr class="total-row">
                <td colspan="4" class="text-center">Jumlah Penutupan</td>
                <td class="text-end">{{ number_format($totalPpn ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPph21 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPph22 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPph23 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPb1 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPenerimaan ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPengeluaran ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($currentSaldo ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui<br>
                    Kepala Sekolah<br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <strong>{{ $penganggaran->kepala_sekolah ?? '_____________________' }}</strong><br>
                    NIP: {{ $penganggaran->nip_kepala_sekolah ?? '_____________________' }}
                </td>
                <td>
                    {{ $sekolah->kecamatan ?? '-' }}, {{ $formatAkhirBulanLengkapHari ?? '_____________________'
                    }}<br>
                    Bendahara<br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <strong>{{ $penganggaran->bendahara ?? '_____________________' }}</strong><br>
                    NIP: {{ $penganggaran->nip_bendahara ?? '_____________________' }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>