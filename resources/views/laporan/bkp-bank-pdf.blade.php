<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKP Bank {{ $bulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 11pt;
        }

        .info-sekolah {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .info-sekolah td {
            padding: 2px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        .table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            font-size: 9pt;
        }

        .footer-table {
            width: 100%;
            margin-top: 20px;
        }

        .footer-table td {
            padding: 10px;
            vertical-align: top;
        }

        .page-break {
            page-break-after: always;
        }

        .signature {
            margin-top: 50px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }

        /* Tambahan untuk memperbaiki tampilan */
        .table td {
            height: 20px;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BUKU KAS PEMBANTU BANK</h1>
        <h2>BULAN : {{ strtoupper($bulan) }} TAHUN : {{ $tahun }}</h2>
    </div>

    <table class="info-sekolah">
        <tr>
            <td style="width: 20%">NPSN</td>
            <td style="width: 5%">:</td>
            <td style="width: 75%">{{ $sekolah->npsn ?? '40202255' }}</td>
        </tr>
        <tr>
            <td>Nama Sekolah</td>
            <td>:</td>
            <td>{{ $sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI' }}</td>
        </tr>
        <tr>
            <td>Desa/Kecamatan</td>
            <td>:</td>
            <td>{{ $sekolah->alamat ?? 'Jl. Santa No. 150, Kec. Dampal Selatan' }}</td>
        </tr>
        <tr>
            <td>Kabupaten / Kota</td>
            <td>:</td>
            <td>{{ $sekolah->kabupaten ?? 'Kab. Tolitoli' }}</td>
        </tr>
        <tr>
            <td>Provinsi</td>
            <td>:</td>
            <td>{{ $sekolah->provinsi ?? 'Prov. Sulawesi Tengah' }}</td>
        </tr>
        <tr>
            <td>Sumber Dana</td>
            <td>:</td>
            <td>BOSP Reguler</td>
        </tr>
    </table>

    <table class="table table-primary table-bordered table-sm mb-0" style="font-size: 10pt; border-collapse: collapse;">
        <thead class="table-light">
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
            $totalPenerimaan = $saldoAwal; // Saldo awal termasuk dalam penerimaan
            $totalPengeluaran = 0;
            $totalPenarikan = 0;
            $totalPenerimaanDana = 0;
            @endphp
    
            <!-- Baris Saldo Awal -->
            <tr>
                <td>
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
                    Saldo Awal Bank {{ $tahun }}
                    @else
                    Saldo Bank Bulan {{ \App\Models\BukuKasUmum::convertNumberToBulan($bulanAngka-1) }} {{
                    $tahun }}
                    @endif
                </td>
                <td class="text-end">@if($saldoAwal > 0) Rp {{ number_format($saldoAwal, 0, ',', '.') }} @else 0
                    @endif</td>
                <td class="text-end">0</td>
                <td class="text-end">@if($saldo > 0) Rp {{ number_format($saldo, 0, ',', '.') }} @else 0 @endif
                </td>
            </tr>
    
            <!-- Baris Penerimaan Dana -->
            @foreach($penerimaanDanas as $penerimaan)
            @php
            $totalPenerimaanDana += $penerimaan->jumlah_dana;
            $totalPenerimaan += $penerimaan->jumlah_dana;
            $saldo += $penerimaan->jumlah_dana;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Terima Dana {{ $penerimaan->sumber_dana }}</td>
                <td class="text-end">Rp {{ number_format($penerimaan->jumlah_dana, 0, ',', '.') }}</td>
                <td class="text-end">0</td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach
    
            <!-- Baris Penarikan Tunai -->
            @foreach($penarikanTunais as $penarikan)
            @php
            $totalPenarikan += $penarikan->jumlah_penarikan;
            $totalPengeluaran += $penarikan->jumlah_penarikan;
            $saldo -= $penarikan->jumlah_penarikan;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Tarik Tunai</td>
                <td class="text-end">0</td>
                <td class="text-end">Rp {{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach
    
            <!-- Baris Bunga Bank -->
            @if($bungaRecord && $bungaRecord->bunga_bank > 0)
            @php
            $totalPenerimaan += $bungaRecord->bunga_bank;
            $saldo += $bungaRecord->bunga_bank;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Bunga Bank</td>
                <td class="text-end">Rp {{ number_format($bungaRecord->bunga_bank, 0, ',', '.') }}</td>
                <td class="text-end">0</td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endif
    
            <!-- Baris Pajak Bunga -->
            @if($bungaRecord && $bungaRecord->pajak_bunga_bank > 0)
            @php
            $totalPengeluaran += $bungaRecord->pajak_bunga_bank;
            $saldo -= $bungaRecord->pajak_bunga_bank;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Pajak Bunga</td>
                <td class="text-end">0</td>
                <td class="text-end">Rp {{ number_format($bungaRecord->pajak_bunga_bank, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endif
    
            <!-- Baris Jumlah -->
            <tr class="table-active fw-bold">
                <td colspan="5" class="text-center">Jumlah</td>
                <td class="text-end">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Pada hari ini {{ $formatAkhirBulanLengkapHari }}, Buku Kas Umum Ditutup dengan
            keadaan/posisi buku sebagai berikut :</p>

        <p><strong>Saldo Bank : Rp. {{ number_format($saldo, 0, ',', '.') }}</strong></p>

        <table class="footer-table">
            <tr>
                <td style="width: 50%">
                    <div class="signature">
                        <p>Menyetujui,</p>
                        <p>Kepala Sekolah</p>
                        <div class="signature-line">
                            <strong>{{ $penganggaran->kepala_sekolah ?? 'Dra. Masitah Abdullah' }}</strong><br>
                            NIP: {{ $penganggaran->nip_kepala_sekolah ?? '196909172007012017' }}
                        </div>
                    </div>
                </td>
                <td style="width: 50%">
                    <div class="signature">
                        <p>{{ $sekolah->kabupaten_kota ?? 'Tolitoli' }}, {{ $formatTanggalAkhirBulanLengkap }}</p>
                        <p>Bendahara,</p>
                        <div class="signature-line">
                            <strong>{{ $penganggaran->bendahara ?? 'Dra. Masitah Abdullah' }}</strong><br>
                            NIP: {{ $penganggaran->nip_bendahara ?? '196909172007012017' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-top: 20px; font-size: 7pt;">
            BKP Bank {{ $bulan }} {{ $tahun }} - NPSN : {{ $sekolah->npsn ?? '40202255' }}, Nama Sekolah : {{
            $sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI' }}<br>
            Halaman 1 dari 1
        </div>
    </div>
</body>

</html>