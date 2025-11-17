<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKP Bank {{ $bulan }} {{ $tahun }}</title>
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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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

        .footer {
            margin-top: 5px;
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
            margin-top: 5px;
            text-align:center;
        }

        .signature-line {
            margin-top: 60px;

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

<body style="font-size: {{ $printSettings['font_size'] }};">
    <div class="header" style="font-size: {{ $printSettings['font_size'] }};">
        <h1 style="font-size: {{ $printSettings['font_size'] }};">BUKU KAS PEMBANTU BANK</h1>
        <h2 style="font-size: {{ $printSettings['font_size'] }};">BULAN : {{ strtoupper($bulan) }} TAHUN : {{ $tahun }}</h2>
    </div>

    <table class="info-sekolah" style="font-size: {{ $printSettings['font_size'] }};">
        <tr>
            <td style="width: 10%; font-size: {{$printSettings['font_size']}};">NPSN</td>
            <td style="width: 2%; font-size: {{$printSettings['font_size']}};">:</td>
            <td style="width: 88%; font-size: {{$printSettings['font_size']}};">{{ $sekolah->npsn ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-size: {{ $printSettings['font_size'] }};">Nama Sekolah</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->nama_sekolah ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-size: {{ $printSettings['font_size'] }};">Desa/Kecamatan</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-size: {{ $printSettings['font_size'] }};">Kabupaten / Kota</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->kabupaten_kota ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-size: {{ $printSettings['font_size'] }};">Provinsi</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->provinsi ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-size: {{ $printSettings['font_size'] }};">Sumber Dana</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
            <td style="font-size: {{ $printSettings['font_size'] }};">BOSP Reguler</td>
        </tr>
    </table>

    <table class="table table-primary table-bordered table-sm mb-0" style="font-size: {{$printSettings['font_size']}}; border-collapse: collapse;">
        <thead class="table-light">
            <tr>
                <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">TANGGAL</th>
                <th style="width: 12%; font-size: {{ $printSettings['font_size'] }};">KODE KEGIATAN</th>
                <th style="width: 12%; font-size: {{ $printSettings['font_size'] }};">KODE REKENING</th>
                <th style="width: 12%; font-size: {{ $printSettings['font_size'] }};">NO. BUKTI</th>
                <th style="width: 24%; font-size: {{ $printSettings['font_size'] }};">URAIAN</th>
                <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">PENERIMAAN</th>
                <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">PENGELUARAN</th>
                <th style="width: 10%; font-size: {{ $printSettings['font_size'] }};">SALDO</th>
            </tr>
            <tr>
                <th style="font-size: {{ $printSettings['font_size'] }};">1</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">2</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">3</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">4</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">5</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">6</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">7</th>
                <th style="font-size: {{ $printSettings['font_size'] }};">8</th>
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
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }}; text-align: center;">
                    @php
                    $bulanAngkaFormatted = str_pad($bulanAngka, 2, '0', STR_PAD_LEFT);
                    @endphp
                    01-{{ $bulanAngkaFormatted }}-{{ $tahun }}
                </td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};">
                    @if($bulanAngka == 1)
                    Saldo Awal Bank {{ $tahun }}
                    @else
                    Saldo Bank Bulan {{ \App\Models\BukuKasUmum::convertNumberToBulan($bulanAngka-1) }} {{
                    $tahun }}
                    @endif
                </td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">@if($saldoAwal > 0) Rp {{ number_format($saldoAwal, 0, ',', '.') }} @else 0
                    @endif</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">0</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">@if($saldo > 0) Rp {{ number_format($saldo, 0, ',', '.') }} @else 0 @endif
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
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }}; text-align: center;">{{ \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d-m-Y') }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Terima Dana {{ $penerimaan->sumber_dana }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($penerimaan->jumlah_dana, 0, ',', '.') }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">0</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
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
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }}; text-align: center;">{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Tarik Tunai</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">0</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach
    
            <!-- Baris Bunga Bank -->
            @if($bungaRecord && $bungaRecord->bunga_bank > 0)
            @php
            $totalPenerimaan += $bungaRecord->bunga_bank;
            $saldo += $bungaRecord->bunga_bank;
            @endphp
            <tr>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }}; text-align: center;">{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Bunga Bank</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($bungaRecord->bunga_bank, 0, ',', '.') }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">0</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endif
    
            <!-- Baris Pajak Bunga -->
            @if($bungaRecord && $bungaRecord->pajak_bunga_bank > 0)
            @php
            $totalPengeluaran += $bungaRecord->pajak_bunga_bank;
            $saldo -= $bungaRecord->pajak_bunga_bank;
            @endphp
            <tr>
                <td class="text-center" style="font-size: {{ $printSettings['font_size'] }}; text-align: center;">{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};"></td>
                <td style="font-size: {{ $printSettings['font_size'] }};">Pajak Bunga</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">0</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($bungaRecord->pajak_bunga_bank, 0, ',', '.') }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endif
    
            <!-- Baris Jumlah -->
            <tr class="table-active fw-bold">
                <td colspan="5" class="text-center" style="font-size: {{ $printSettings['font_size'] }};">Jumlah</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;"">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;"">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                <td class="text-end" style="font-size: {{ $printSettings['font_size'] }}; text-align: right;">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer" style="font-size: {{ $printSettings['font_size'] }};">
        <p>Pada hari ini {{ $formatAkhirBulanLengkapHari }}, Buku Kas Umum Ditutup dengan
            keadaan/posisi buku sebagai berikut :</p>

        <p><strong>Saldo Bank : Rp. {{ number_format($saldo, 0, ',', '.') }}</strong></p>

        <table class="footer-table" style="font-size: {{ $printSettings['font_size'] }};">
            <tr>
                <td style="width: 50%; font-size: {{$printSettings['font_size']}};">
                    <div class="signature" style="font-size: {{ $printSettings['font_size'] }};">
                        <p>Menyetujui,</p>
                        <p>Kepala Sekolah</p>
                        <div class="signature-line">
                            <strong>{{ $penganggaran->kepala_sekolah ?? '-' }}</strong><br>
                            NIP: {{ $penganggaran->nip_kepala_sekolah ?? '-' }}
                        </div>
                    </div>
                </td>
                <td style="width: 50%; font-size: {{$printSettings['font_size']}};">
                    <div class="signature" style="font-size: {{ $printSettings['font_size'] }};">
                        <p>{{ $sekolah->kabupaten_kota ?? 'Tolitoli' }}, {{ $formatTanggalAkhirBulanLengkap }}</p>
                        <p>Bendahara,</p>
                        <div class="signature-line" style="font-size: {{ $printSettings['font_size'] }};">
                            <strong>{{ $penganggaran->bendahara ?? '-' }}</strong><br>
                            NIP: {{ $penganggaran->nip_bendahara ?? '-' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>