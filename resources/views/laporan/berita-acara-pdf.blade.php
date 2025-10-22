<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>BERITA ACARA PEMERIKSAAN KAS - {{ $bulan }} {{ $tahun }}</title>
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
        margin-left: 2.5cm;
        margin-right: 2.5cm;
        margin-top: 2cm;
        margin-bottom: 2cm;
        }
        
        body {
        font-family: Arial, sans-serif;
        
            font-size: {
                {
                $printSettings['font_size']
                }
            };
        margin: 2.5cm;
        padding: 2.5cm;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            
        }

        .table td {
            padding: 5px;
            border: none;
            line-height: 1.5;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right{
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .signature-area {
            margin-top: 50px;
        }

        .signature-table{
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        .signature-table td {
            border : none;
        }
    </style>
</head>

<body style="font-size: {{$printSettings['font_size']}};">
    <div class="container">
        <table class="table" style="font-size: {{$printSettings['font_size']}};">
            <tbody>
                <tr>
                    <td style="font-size{{$printSettings['font_size']}};" colspan="5" class="text-center fw-bold">BERITA ACARA PEMERIKSAAN KAS</td>
                </tr>
                <tr>
                    <td colspan="5">
                        Pada hari ini, <span>{{ $namaHariAkhirBulan }}</span> tanggal
                        <span>{{ $formatTanggalAkhirBulan }}</span>
                        yang bertanda tangan di bawah ini, kami Kepala Sekolah yang ditunjuk berdasarkan 
                        Surat Keputusan No. <span>{{ $penganggaran->sk_kepala_sekolah ?? '-' }} Tanggal {{ $penganggaran->formatTanggalSkKepsek ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td width="40%" style="padding-left: 30px;">Nama</td>
                    <td width="1%">:</td>
                    <td colspan="3" width="25%">{{ $namaKepalaSekolah }}</td>
                </tr>
                <tr>
                    <td width="40%" style="padding-left: 30px;">Jabatan</td>
                    <td width="1%">:</td>
                    <td colspan="3" width="25%">Kepala Sekolah</td>
                </tr>
                <tr>
                    <td colspan="5">
                        Melakukan pemeriksaan kas kepada :
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 30px;">Nama</td>
                    <td>:</td>
                    <td colspan="3">{{ $namaBendahara }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 30px;">Jabatan</td>
                    <td>:</td>
                    <td colspan="3">Bendahara BOS</td>
                </tr>
                <tr>
                    <td colspan="5">
                        Yang berdasarkan Surat Keputusan Nomor :
                        <span>{{ $penganggaran->sk_bendahara ?? '-' }} Tanggal {{ $penganggaran->formatTanggalSkBendahara ?? '-' }}</span> ditugaskan
                        dengan pengurusan uang Bantuan Operasional Sekolah (BOS).
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        Berdasarkan pemeriksaan kas serta bukti-bukti dalam pengurusan itu, kami menemui
                        kenyataan sebagai berikut :
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        Jumlah uang yang dihitung di hadapan Bendahara / Pemegang Kas adalah :
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 30px;">a) Uang kertas bank, uang logam</td>
                    <td>:</td>
                    <td class="text-right" width="2%">Rp.</td>
                    <td>{{ number_format($totalUangKertasLogam, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="padding-left: 30px;">b) Saldo Bank</td>
                    <td>:</td>
                    <td class="text-right" width="2%">Rp.</td>
                    <td>{{ number_format($saldoBank, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="padding-left: 30px;">c) Surat Berharga dil</td>
                    <td>:</td>
                    <td class="text-right" width="2%">Rp.</td>
                    <td>-</td>
                    <td></td>
                </tr>
                <tr class="fw-bold">
                    <td style="padding-left: 30px;">Jumlah</td>
                    <td>:</td>
                    <td class="text-right" width="2%">Rp.</td>
                    <td>{{ number_format($totalKas, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Saldo uang menurut Buku Kas Umum</td>
                    <td>:</td>
                    <td class="text-right" width="2%">Rp.</td>
                    <td>{{ number_format($saldoBuku, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr class="fw-bold">
                    <td>Perbedaan antara Saldo Kas dan Saldo buku</td>
                    <td>:</td>
                    <td class="text-right" width="2%">Rp.</td>
                    <td>{{ number_format($perbedaan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        {{-- ttd --}}
        <div class="signature">
            <table class="signature-table">
                <tbody>
                    <tr>
                        <td colspan="2" class="text-center">
                            {{ $sekolah->kecamatan }}, {{ $formatTanggalAkhirBulan }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center signature-area">
                            <div>Bendahara BOSP</div>
                            <br><br><br><br><br><br>
                            <div class="fw-bold">{{ $namaBendahara }}</div>
                            <div>NIP. {{ $nipBendahara }}</div>
                        </td>
                        <td class="text-center signature-area">
                            <div>Kepala Sekolah</div>
                            <br><br><br><br><br><br>
                            <div class="fw-bold">{{ $namaKepalaSekolah }}</div>
                            <div>NIP. {{ $nipKepalaSekolah }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>