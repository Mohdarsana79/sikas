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
        <p><strong>NPSN</strong> : {{ $sekolah->npsn ?? '40202255' }}</p>
        <p><strong>Nama Sekolah</strong> : {{ $sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI' }}</p>
        <p><strong>Desa/Kecamatan</strong> : {{ $sekolah->alamat ?? 'Jl. Santa No. 150, Kec. Dampal Selatan' }}</p>
        <p><strong>Kabupaten / Kota</strong> : {{ $sekolah->kabupaten_kota ?? 'Kab. Tolitoli' }}</p>
        <p><strong>Provinsi</strong> : {{ $sekolah->provinsi ?? 'Prov. Sulawesi Tengah' }}</p>
        <p><strong>Sumber Dana</strong> : {{ $penganggaran->sumber_dana ?? 'BOSP Reguler Perubahan' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%">TANGGAL</th>
                <th style="width: 12%">KODE KEGIATAN</th>
                <th style="width: 12%">KODE REKENING</th>
                <th style="width: 12%">NO. BUKTI</th>
                <th style="width: 24%">URAIAN</th>
                <th style="width: 10%">PENERIMAAN</th>
                <th style="width: 10%">PENGELUARAN</th>
                <th style="width: 10%">SALDO</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
            </tr>
        </thead>
        <tbody>
            @php
            $saldo = $saldoAwal;
            $totalPenerimaan = $saldoAwal;
            $totalPengeluaran = 0;
            @endphp

            <!-- Baris Saldo Awal -->
            <tr>
                <td class="text-center">
                    @php
                    $bulanAngkaFormatted = str_pad($bulanAngka, 2, '0', STR_PAD_LEFT);
                    @endphp
                    01-{{ $bulanAngkaFormatted }}-{{ $tahun }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    @if($bulanAngka == 1)
                    Saldo Awal Tunai {{ $tahun }}
                    @else
                    Saldo Tunai Bulan {{ $convertNumberToBulan($bulanAngka-1) }} {{ $tahun }}
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-right">0</td>
                <td class="text-right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>

            <!-- Baris Penarikan Tunai -->
            @foreach($penarikanTunais as $penarikan)
            @php
            $totalPenerimaan += $penarikan->jumlah_penarikan;
            $saldo += $penarikan->jumlah_penarikan;
            @endphp
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Tarik Tunai</td>
                <td class="text-right">Rp {{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-right">0</td>
                <td class="text-right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach

            <!-- Baris Transaksi BKU Tunai -->
            @foreach($bkuData as $transaksi)
            @if($transaksi->jenis_transaksi === 'tunai')
            @php
            $totalPengeluaran += $transaksi->total_transaksi_kotor;
            $saldo -= $transaksi->total_transaksi_kotor;
            @endphp
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>{{ $transaksi->kodeKegiatan->kode ?? '' }}</td>
                <td>{{ $transaksi->rekeningBelanja->kode_rekening ?? '' }}</td>
                <td>{{ $transaksi->id_transaksi }}</td>
                @if ($transaksi->uraian_opsional)
                    <td>{{ $transaksi->uraian_opsional }}</td>
                @else
                    <td>{{ $transaksi->uraian }}</td>
                @endif
                <td class="text-right">0</td>
                <td class="text-right">Rp {{ number_format($transaksi->total_transaksi_kotor, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endif
            @endforeach

            <!-- Baris Setor Tunai -->
            @foreach($setorTunais as $setor)
            @php
            $totalPengeluaran += $setor->jumlah_setor;
            $saldo -= $setor->jumlah_setor;
            @endphp
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($setor->tanggal_setor)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Setor Tunai</td>
                <td class="text-right">0</td>
                <td class="text-right">Rp {{ number_format($setor->jumlah_setor, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach

            <!-- Baris Jumlah -->
            <tr class="table-footer">
                <td colspan="5" class="text-center"><strong>Jumlah</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($saldo, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Tanggal Cetak: {{ $tanggal_cetak }}</p>
    </div>

    <div class="ttd-section">
        <p>Pada hari ini <strong>{{ $namaHariAkhirBulan }}</strong> tanggal <strong>{{ $formatTanggalAkhirBulanLengkap
                }}</strong> Buku Kas Umum Ditutup dengan keadaan/posisi buku sebagai berikut:</p>

        <p><strong>Saldo Buku Kas Pembantu Tunai : Rp {{ number_format($currentSaldo, 0, ',', '.') }}</strong></p>

        <div class="ttd-box">
            <p>Menyetujui,</p>
            <p>Kepala Sekolah</p>
            <div class="ttd-space"></div>
            <p><strong>Dra. MASTTAH ABDULLAH</strong></p>
            <p>NIP. 196909172007012017</p>
        </div>

        <div class="ttd-box">
            <p>Kec. Dampal Selatan, {{ $formatTanggalAkhirBulanLengkap }}</p>
            <p>Bendahara,</p>
            <div class="ttd-space"></div>
            <p><strong>Dra. MASTTAH ABDULLAH</strong></p>
            <p>NIP. 196909172007012017</p>
        </div>
    </div>
</body>

</html>