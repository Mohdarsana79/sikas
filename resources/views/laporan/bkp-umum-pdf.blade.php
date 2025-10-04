<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>BUKU KAS UMUM - {{ $bulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 10px;
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

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
        }

        .signature {
            width: 100%;
            margin-top: 40px;
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

<body>
    <div class="header">
        <h1>BUKU KAS UMUM</h1>
        <h2>BULAN : {{ strtoupper($bulan) }} {{ $tahun }}</h2>
    </div>

    <div class="school-info">
        <table>
            <tr>
                <td width="30%">Nama Sekolah</td>
                <td width="2%">:</td>
                <td>{{ $sekolah->nama_sekolah ?? '.......................' }}</td>
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
            $currentSaldo = $saldoAwal + $saldoAwalTunai;
            @endphp

            <!-- Baris Saldo Awal -->
            <tr>
                <td>1/{{ $bulanAngka }}/{{ $tahun }}</td>
                <td>-</td>
                <td>-</td>
                <td>Saldo Awal bulan</td>
                <td class="text-end">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>

            @if($saldoAwalTunai > 0)
            <tr>
                <td>1/{{ $bulanAngka }}/{{ $tahun }}</td>
                <td>-</td>
                <td>-</td>
                <td>Saldo Kas Tunai</td>
                <td class="text-end">{{ number_format($saldoAwalTunai, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $saldoAwalTunai;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif

            <!-- Data Penerimaan Dana -->
            @foreach($penerimaanDanas as $penerimaan)
            @if(\Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d-m-Y') }}</td>
                <td>299</td>
                <td>-</td>
                <td>Terima Dana {{ $penerimaan->sumber_dana }} T.A {{ $tahun }}</td>
                <td class="text-end">{{ number_format($penerimaan->jumlah_dana, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $penerimaan->jumlah_dana;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif
            @endforeach

            <!-- Data Penarikan Tunai -->
            @foreach($penarikanTunais as $penarikan)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Penarikan Tunai</td>
                <td class="text-end">{{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $penarikan->jumlah_penarikan;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endforeach

            <!-- Data Setor Tunai -->
            @foreach($setorTunais as $setor)
            <tr>
                <td>{{ \Carbon\Carbon::parse($setor->tanggal_setor)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Setor Tunai</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($setor->jumlah_setor, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    $currentSaldo -= $setor->jumlah_setor;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endforeach

            <!-- Data Transaksi BKU -->
            @foreach($bkuData as $transaksi)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>{{ $transaksi->rekeningBelanja->kode_rekening ?? '-' }}</td>
                <td>{{ $transaksi->id_transaksi }}</td>
                <td>{{ $transaksi->uraian_opsional ?? $transaksi->uraian }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($transaksi->total_transaksi_kotor, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    $currentSaldo -= $transaksi->total_transaksi_kotor;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>

            <!-- Baris Pajak Pusat jika ada -->
            @if($transaksi->total_pajak > 0)
            @if(empty($transaksi->ntpn))
            <!-- Jika NTPN belum ada (Terima Pajak) - di kolom PENERIMAAN -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>{{ $transaksi->kode_masa_pajak }}</td>
                <td>Terima Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional ?? $transaksi->uraian }}
                </td>
                <td class="text-end">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $transaksi->total_pajak;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @else
            <!-- Jika NTPN sudah ada (Setor Pajak) - tampil di KEDUA KOLOM -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>{{ $transaksi->kode_masa_pajak }}</td>
                <td>Setor Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional ?? $transaksi->uraian }}
                </td>
                <td class="text-end">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    // Saldo tetap karena penerimaan dan pengeluaran sama
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif
            @endif

            <!-- Baris Pajak Daerah jika ada -->
            @if($transaksi->total_pajak_daerah > 0)
            @if(empty($transaksi->ntpn))
            <!-- Jika NTPN belum ada (Terima Pajak Daerah) - di kolom PENERIMAAN -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>{{ $transaksi->kode_masa_pajak }}</td>
                <td>Terima Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional ?? $transaksi->uraian }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $transaksi->total_pajak_daerah;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @else
            <!-- Jika NTPN sudah ada (Setor Pajak Daerah) - tampil di KEDUA KOLOM -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>{{ $transaksi->kode_masa_pajak }}</td>
                <td>Setor Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional ?? $transaksi->uraian }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    // Saldo tetap karena penerimaan dan pengeluaran sama
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif
            @endif
            @endforeach

            <!-- Bunga Bank -->
            @if($bungaRecord && $bungaRecord->bunga_bank > 0)
            <tr>
                <td>{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>299</td>
                <td>-</td>
                <td>Bunga Bank</td>
                <td class="text-end">{{ number_format($bungaRecord->bunga_bank, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $bungaRecord->bunga_bank;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif

            <!-- Pajak Bunga Bank -->
            @if($bungaRecord && $bungaRecord->pajak_bunga_bank > 0)
            <!-- Jika NTPN belum ada (Terima Pajak Bunga) - di kolom PENERIMAAN -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>199</td>
                <td>-</td>
                <td>Pajak Bunga Bank</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($bungaRecord->pajak_bunga_bank, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    $currentSaldo -= $bungaRecord->pajak_bunga_bank;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif

            <!-- Baris Jumlah Penutupan -->
            <tr style="background-color: #f0f0f0; font-weight: bold;">
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
        <p>Saldo Buku Kas Umum : <strong>{{ number_format($currentSaldo, 0, ',', '.') }}</strong></p>

        <p>Dengan rincian sebagai berikut :</p>
        <p>Saldo Bank : <strong>{{ number_format($saldoBank, 0, ',', '.') }}</strong></p>
        <p style="margin-left: 20px;">1. Dana Sekolah : {{ number_format($danaSekolah, 0, ',', '.') }}</p>
        <p style="margin-left: 20px;">2. Dana BOSP : {{ number_format($danaBosp, 0, ',', '.') }}</p>
        <p>Saldo Kas Tunai : <strong>{{ number_format($saldoTunai, 0, ',', '.') }}</strong></p>
        <p>Jumlah : <strong>{{ number_format($currentSaldo, 0, ',', '.') }}</strong></p>
    </div>

    <!-- Tanda Tangan -->
    <table class="signature">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <p>Kepala Sekolah</p>
                <br><br><br>
                <p><strong>___________________</strong></p>
            </td>
            <td>
                <p>{{ $sekolah->kecamatan ?? '................' }}, {{ $formatAkhirBulanSingkat }}</p>
                <p>Bendahara</p>
                <br><br><br>
                <p><strong>___________________</strong></p>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p style="text-align: right; font-size: 8pt;">Dicetak pada: {{ $tanggal_cetak }}</p>
    </div>
</body>

</html>