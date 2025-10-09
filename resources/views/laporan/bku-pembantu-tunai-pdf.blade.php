<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKU KAS PEMBANTU TUNAI - {{ strtoupper($bulan) }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
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

        .info-sekolah {
            margin-bottom: 10px;
            text-align: center;
        }

        .info-sekolah p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .table-footer {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
        }

        .ttd-section {
            margin-top: 30px;
            text-align: center;
        }

        .ttd-box {
            display: inline-block;
            margin: 0 50px;
        }

        .ttd-space {
            height: 60px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BUKU KAS PEMBANTU TUNAI</h1>
        <h2>BULAN : {{ strtoupper($bulan) }} TAHUN : {{ $tahun }}</h2>
    </div>

    <div class="info-sekolah">
        <p><strong>NPSN</strong> : {{ $sekolah->npsn ?? '..........' }}</p>
        <p><strong>Nama Sekolah</strong> : {{ $sekolah->nama_sekolah ?? '..........' }}</p>
        <p><strong>Desa/Kecamatan</strong> : {{ $sekolah->alamat ?? '..........' }}</p>
        <p><strong>Kabupaten / Kota</strong> : {{ $sekolah->kabupaten_kota ?? '..........' }}</p>
        <p><strong>Provinsi</strong> : {{ $sekolah->provinsi ?? '..........' }}</p>
        <p><strong>Sumber Dana</strong> : {{ $penganggaran->sumber_dana ?? '..........' }}</p>
    </div>

    <table>
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
            $currentSaldo = $saldoAwalTunai;
            @endphp

            <!-- Baris Saldo Kas Tunai -->
            <tr>
                <td>1/{{ $bulanAngka }}/{{ $tahun }}</td>
                <td>-</td>
                <td>-</td>
                <td>Saldo Kas Tunai</td>
                <td class="text-right">{{ number_format($saldoAwalTunai, 0, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>

            <!-- Data Penarikan Tunai -->
            @foreach($penarikanTunais as $penarikan)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Penarikan Tunai</td>
                <td class="text-right">{{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">
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
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($setor->jumlah_setor, 0, ',', '.') }}</td>
                <td class="text-right">
                    @php
                    $currentSaldo -= $setor->jumlah_setor;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endforeach

            <!-- Data Transaksi BKU Tunai -->
            @foreach($bkuDataTunai as $transaksi)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>{{ $transaksi->rekeningBelanja->kode_rekening ?? '-' }}</td>
                <td>{{ $transaksi->id_transaksi }}</td>
                <td>{{ $transaksi->uraian_opsional }}</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($transaksi->total_transaksi_kotor, 0, ',', '.') }}</td>
                <td class="text-right">
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
                <td>Terima Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional }}
                </td>
                <td class="text-right">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">
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
                <td>Setor Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional }}
                </td>
                <td class="text-right">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-right">
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
                <td>-</td>
                <td>Terima Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional }}</td>
                <td class="text-right">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">
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
                <td>-</td>
                <td>Setor Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional }}</td>
                <td class="text-right">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-right">
                    @php
                    // Saldo tetap karena penerimaan dan pengeluaran sama
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif
            @endif
            @endforeach

            <!-- Baris Jumlah Penutupan -->
            <tr class="table-footer">
                <td colspan="4" class="text-center">Jumlah Penutupan</td>
                <td class="text-right">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Informasi Penutupan -->
    <div class="ttd-section">
        <p>Pada hari ini, {{ $namaHariAkhirBulan }},
            tanggal {{ $formatTanggalAkhirBulanLengkap }}
            Buku Kas Pembantu Tunai ditutup dengan keadaan/posisi sebagai berikut :</p>
        <p>Saldo Kas Tunai : <strong>{{ number_format($currentSaldo, 0, ',', '.') }}</strong></p>

        <div class="ttd-box">
            <p>Menyetujui,</p>
            <p>Kepala Sekolah</p>
            <div class="ttd-space"></div>
            <p><strong>{{ $penganggaran->kepala_sekolah ?? '..........' }}</strong></p>
            <p>NIP. {{ $penganggaran->nip_kepala_sekolah ?? '..........' }}</p>
        </div>

        <div class="ttd-box">
            <p>{{ $sekolah->kabupaten_kota ?? '..........' }}, {{ $formatTanggalAkhirBulanLengkap }}</p>
            <p>Bendahara,</p>
            <div class="ttd-space"></div>
            <p><strong>{{ $penganggaran->bendahara ?? '..........' }}</strong></p>
            <p>NIP. {{ $penganggaran->nip_bendahara ?? '..........' }}</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Tanggal Cetak: {{ $tanggal_cetak }}</p>
    </div>
</body>

</html>