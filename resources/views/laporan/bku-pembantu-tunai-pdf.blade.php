<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKU KAS PEMBANTU TUNAI - {{ strtoupper($bulan) }} {{ $tahun }}</title>
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

        .no-border {
            border: none;
        }

        .info-sekolah {
            margin-bottom: 10px;
            text-align: left;
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

        .closed {
            margin-top: 5px;
            text-align: left;
        }

        .ttd-box {
            margin-top: 30px;
            justify-content: center;
            text-align: center;
            line-height: 0.5;
            margin: 0px 50px;
        }

        .ttd-space {
            height: 60px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body style="font-size: {{$printSettings['font_size']}};">
    <div class="header" style="font-size: {{$printSettings['font_size']}};">
        <h1>BUKU KAS PEMBANTU TUNAI</h1>
        <h2>BULAN : {{ strtoupper($bulan) }} TAHUN : {{ $tahun }}</h2>
    </div>

    <div class="info-sekolah" style="font-size: {{$printSettings['font_size']}};">
        <table style="font-size: {{$printSettings['font_size']}}; border: none;">
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none; width: 10%;">NPSN</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none; width: 1%;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none; width: 89%;">{{ $sekolah->npsn ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">Nama Sekolah</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">{{ $sekolah->nama_sekolah ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">Kelurahan / Desa</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">{{ $sekolah->kelurahan_desa ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">Kecamatan</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">{{ $sekolah->kecamatan ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">Kabupaten / Kota</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">{{ $sekolah->kabupaten_kota ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">Provinsi</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">{{ $sekolah->provinsi ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">Sumber Dana</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">:</td>
                <td style="font-size: {{$printSettings['font_size']}}; border: none;">BOSP Reguler</td>
            </tr>
        </table>
    </div>

    <table style="font-size: {{$printSettings['font_size']}};">
        <thead>
            <tr>
                <th width="8%" style="font-size: {{$printSettings['font_size']}};">Tanggal</th>
                <th width="12%" style="font-size: {{$printSettings['font_size']}};">Kode Rekening</th>
                <th width="8%" style="font-size: {{$printSettings['font_size']}};">No. Bukti</th>
                <th width="30%" style="font-size: {{$printSettings['font_size']}};">Uraian</th>
                <th width="12%" style="font-size: {{$printSettings['font_size']}};">Penerimaan (Kredit)</th>
                <th width="12%" style="font-size: {{$printSettings['font_size']}};">Pengeluaran (Debet)</th>
                <th width="12%" style="font-size: {{$printSettings['font_size']}};">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
            $currentSaldo = $saldoAwalTunai;
            @endphp

            <!-- Baris Saldo Kas Tunai -->
            <tr>
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">1-{{ $bulanAngka }}-{{ $tahun }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">Saldo Kas Tunai</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($saldoAwalTunai, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">-</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>

            <!-- Data Penarikan Tunai -->
            @foreach($penarikanTunais as $penarikan)
            <tr>
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">Penarikan Tunai</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">-</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
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
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($setor->tanggal_setor)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">Setor Tunai</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">-</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($setor->jumlah_setor, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
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
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">{{ $transaksi->rekeningBelanja->kode_rekening ?? '-' }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">{{ $transaksi->id_transaksi }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">{{ $transaksi->uraian_opsional }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">-</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($transaksi->total_transaksi_kotor, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
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
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">{{ $transaksi->kode_masa_pajak }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">Terima Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional }}
                </td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">-</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
                    @php
                    $currentSaldo += $transaksi->total_pajak;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @else
            <!-- Jika NTPN sudah ada (Setor Pajak) - tampil di KEDUA KOLOM -->
            <tr>
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">{{ $transaksi->kode_masa_pajak }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">Setor Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional }}
                </td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
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
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">Terima Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">-</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
                    @php
                    $currentSaldo += $transaksi->total_pajak_daerah;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @else
            <!-- Jika NTPN sudah ada (Setor Pajak Daerah) - tampil di KEDUA KOLOM -->
            <tr>
                <td class="text-center" style="font-size: {{$printSettings['font_size']}};">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">-</td>
                <td style="font-size: {{$printSettings['font_size']}};">Setor Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional }}</td>
                <td class="text-right">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">
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
            <tr class="table-footer" style="font-size: {{$printSettings['font_size']}};">
                <td colspan="4" class="text-center" style="font-size: {{$printSettings['font_size']}};">Jumlah Penutupan</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{$printSettings['font_size']}};">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Informasi Penutupan -->
    <div class="closed">
        <p>Pada hari ini, {{ $namaHariAkhirBulan }},
            tanggal {{ $formatTanggalAkhirBulanLengkap }}
            Buku Kas Pembantu Tunai ditutup dengan keadaan/posisi sebagai berikut :</p>
        <p>Saldo Kas Tunai : <strong>{{ number_format($currentSaldo, 0, ',', '.') }}</strong></p>
    </div>
    <table style="font-size: {{$printSettings['font_size']}};">
        <tr>
            <td style="font-size: {{$printSettings['font_size']}}; border: none;">
                <div class="ttd-box" style="font-size: {{$printSettings['font_size']}};">
                    <p>Menyetujui,</p>
                    <p>Kepala Sekolah</p>
                    <div class="ttd-space"></div>
                    <p><strong>{{ $penganggaran->kepala_sekolah ?? '..........' }}</strong></p>
                    <p>NIP. {{ $penganggaran->nip_kepala_sekolah ?? '..........' }}</p>
                </div>
            </td>
            <td style="font-size: {{$printSettings['font_size']}}; border: none;">
                <div class="ttd-box" style="font-size: {{$printSettings['font_size']}};">
                    <p>{{ $sekolah->kabupaten_kota ?? '..........' }}, {{ $formatTanggalAkhirBulanLengkap }}</p>
                    <p>Bendahara,</p>
                    <div class="ttd-space"></div>
                    <p><strong>{{ $penganggaran->bendahara ?? '..........' }}</strong></p>
                    <p>NIP. {{ $penganggaran->nip_bendahara ?? '..........' }}</p>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>