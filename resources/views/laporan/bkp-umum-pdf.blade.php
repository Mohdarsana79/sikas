<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>BUKU KAS UMUM - {{ $bulan }} {{ $tahun }}</title>
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
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
        }

        .header h2 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
        }

        .school-info {
            margin-bottom: 15px;
        }

        .school-info table {
            width: 100%;
        }

        .school-info td {
            vertical-align: top;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
        }

        .table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .table-secondary{
            background-color: #c4c3c2;
        }

        .text-end td {
            text-align: right;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .table-secondary .text-center {
            text-align: center;
            font-weight: bold;
        }

        .table-secondary .text-end {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
        }

        .signature {
            width: 100%;
            margin-top: 10px;
            line-height: 0.5;
        }

        .signature td {
            width: 50%;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body style="font-size: {{$printSettings['font_size']}};">
    <div class="header">
        <h1>BUKU KAS UMUM</h1>
        <h2>BULAN : {{ strtoupper($bulan) }} {{ $tahun }}</h2>
    </div>

    <div class="school-info">
        <table>
            <tr>
                <td width="10%">Nama Sekolah</td>
                <td width="1%">:</td>
                <td width="89%">{{ $sekolah->nama_sekolah ?? '.......................' }}</td>
            </tr>
            <tr>
                <td>Desa/Kecamatan</td>
                <td>:</td>
                <td>{{ $sekolah->kelurahan_desa ?? '...................' }} / {{ $sekolah->kecamatan ??
                    '.........................' }}</td>
            </tr>
            <tr>
                <td>Kabupaten</td>
                <td>:</td>
                <td>{{ $sekolah->kabupaten_kota ?? '.......................' }}</td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td>:</td>
                <td>{{ $sekolah->provinsi ?? '.......................' }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="8%">Tanggal</th>
                <th width="12%">Kode Rekening</th>
                <th width="8%">No. Bukti</th>
                <th width="30%">Uraian</th>
                <th width="12%">Penerimaan (Kredit)</th>
                <th width="12%">Pengeluaran (Debet)</th>
                <th width="12%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
            $runningPenerimaan = 0;
            $runningPengeluaran = 0;
            $currentSaldo = 0;
            @endphp
    
            @foreach($rowsData as $index => $row)
            @php
            // IMPLEMENTASI RUMUS EXCEL: =IF(OR(G11<>0,H11<>0),SUM(G$11:G11)-SUM(H$11:H11),0)
                    $runningPenerimaan += $row['penerimaan'];
                    $runningPengeluaran += $row['pengeluaran'];
    
                    // Hanya hitung saldo jika ada penerimaan atau pengeluaran di baris ini
                    if($row['penerimaan'] != 0 || $row['pengeluaran'] != 0) {
                    $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }
                    // Untuk baris saldo awal, tetap gunakan nilai saldo awal
                    elseif(isset($row['is_saldo_awal']) && $row['is_saldo_awal']) {
                    $currentSaldo = $row['penerimaan'];
                    }
                    @endphp
    
                    <tr>
                        <td style="text-align:center;">{{ $row['tanggal'] }}</td>
                        <td style="text-align:center;">{{ $row['kode_rekening'] }}</td>
                        <td>{{ $row['no_bukti'] }}</td>
                        <td>{{ $row['uraian'] }}</td>
                        <td class="text-end" style="text-align: right;">
                            @if($row['penerimaan'] > 0)
                            {{ number_format($row['penerimaan'], 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-end" style="text-align: right;">
                            @if($row['pengeluaran'] > 0)
                            {{ number_format($row['pengeluaran'], 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-end" style="text-align: right;">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
    
                    <!-- Baris Jumlah Penutupan -->
                    <tr class="table-secondary">
                        <td colspan="4" class="text-center">Jumlah Penutupan</td>
                        <td class="text-end">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
                    </tr>
        </tbody>
    </table>

    <!-- Informasi Penutupan -->
    <div style="margin-top: 15px;">
        <p>Pada hari ini, {{ $namaHariAkhirBulan }},
            tanggal {{ $formatTanggalAkhirBulanLengkap }}
            Buku Kas Umum ditutup dengan keadaan/posisi sebagai berikut :</p>

        <table>
            <tr>
                <td width="30%">Saldo Buku Kas Umum</td>
                <td width="2%">:</td>
                <td>...............................................................................................................................................................................<strong>Rp. {{ number_format($currentSaldo, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td width="30%">Saldo Bank</td>
                <td width="2%">:</td>
                <td>...............................................................................................................................................................................Rp. {{ number_format($saldoBank, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">1. Dana Sekolah</td>
                <td>:</td>
                <td>...............................................................................................................................................................................Rp. {{ number_format($danaSekolah, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">2. Dana BOSP</td>
                <td>:</td>
                <td>...............................................................................................................................................................................Rp. {{ number_format($danaBosp, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Saldo Kas Tunai</td>
                <td>:</td>
                <td>...............................................................................................................................................................................Rp. {{ number_format($saldoTunai, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Jumlah</td>
                <td>:</td>
                <td>...............................................................................................................................................................................<strong>Rp. {{ number_format($currentSaldo, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <table class="signature">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <p>Kepala Sekolah</p>
                <br><br><br><br><br><br><br><br><br><br><br>
                <p><strong>{{ $penganggaran->kepala_sekolah }}</strong></p>
                <p>NIP. {{ $penganggaran->nip_kepala_sekolah }}</p>
            </td>
            <td>
                <p>{{ $sekolah->kecamatan ?? '................' }}, {{ $formatAkhirBulanSingkat }}</p>
                <p>Bendahara</p>
                <br><br><br><br><br><br><br><br><br><br><br>
                <p><strong>{{ $penganggaran->bendahara }}</strong></p>
                <p>NIP. {{ $penganggaran->nip_bendahara }}</p>
            </td>
        </tr>
    </table>
</body>

</html>