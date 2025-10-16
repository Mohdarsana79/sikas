<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKP Registrasi {{ $bulan }} {{ $tahun }}</title>
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table th {
            background-color: #f8f9fa;
            text-align: center;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .signature-area {
            margin-top: 40px;
        }

        .page-break {
            page-break-after: always;
        }

        .header-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .sub-header {
            font-size: 10pt;
            text-align: center;
            margin-bottom: 15px;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 9pt;
            }

            .table {
                font-size: 9pt;
            }

            .header-title {
                font-size: 11pt;
            }

            .sub-header {
                font-size: 9pt;
            }
        }

        .footer {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>

<body style="font-size: {{$printSettings['font_size']}};">
    <!-- Header -->
    <div class="header-title">Formulir BOS-K7B</div>
    <div class="sub-header">REGISTER PENUTUPAN KAS</div>

    <!-- Content -->
    <table class="table" style="border: none;">
        <tbody>
            <!-- Informasi Header -->
            <tr>
                <td width="30%" style="border: none;">Tanggal Penutupan Kas</td>
                <td width="70%" colspan="6" style="border: none;"><strong>: {{ $tanggalPenutupan }}</strong></td>
            </tr>
            <tr>
                <td style="border: none;">Nama Penutup Kas (Pemegang Kas)</td>
                <td colspan="6" style="border: none;"><strong>: {{ $namaBendahara }}</strong></td>
            </tr>
            <tr>
                <td style="border: none;">Tanggal Penutupan Kas Yang Lalu</td>
                <td colspan="6" style="border: none;"><strong>: {{ $tanggalPenutupanLalu }}</strong></td>
            </tr>
            <tr>
                <td style="border: none;">Jumlah Total Penerimaan (D)</td>
                <td colspan="6" style="border: none;"><strong>: Rp. {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="border: none;">Jumlah Total Pengeluaran (K)</td>
                <td colspan="6" style="border: none;"><strong>: Rp. {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td class="text-end" style="border: none;">Saldo Buku (A = D - K)</td>
                <td colspan="6" style="border: none;"><strong>: Rp. {{ number_format($saldoBuku, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td class="text-end" style="border: none;">Saldo Kas (B) :</td>
                <td colspan="6" style="border: none;"><strong>: Rp. {{ number_format($saldoKas, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Spacer -->
            <tr>
                <td colspan="7" class="bg-light" style="border: none;"><strong>Saldo Kas B terdiri dari :</strong></td>
            </tr>

            <!-- Uang Kertas -->
            <tr>
                <td colspan="7" style="border: none;"><strong>1. Lembaran uang kertas</strong></td>
            </tr>
            @foreach($uangKertas as $uang)
            <tr>
                <td style="padding-left: 20px; border: none;">
                    Lembaran uang kertas
                </td>
                <td witdh="1%" class="text-end" style="border: none;">Rp.</td>
                <td style="border: none;">{{ number_format($uang['denominasi'], 0, ',', '.') }}</td>
                <td class="text-end" style="border: none;">{{ $uang['lembar'] }}</td>
                <td class="text-center" style="border: none;">Lembar</td>
                <td class="text-end" style="border: none;">Rp.</td>
                <td style="border: none;">
                    <span class="text-end" style="float: right;">
                        {{ number_format($uang['jumlah'], 0, ',', '.') }}
                    </span>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-end" style="border: none;"><strong>Sub Jumlah (1)</strong></td>
                <td class="text-end" style="border: none;">Rp.</td>
                <td colspan="1" class="text-end" style="border: none;"><strong>{{ number_format($totalUangKertas, 0, ',', '.') }}</strong></td>
            </tr>
            
            <!-- Uang Logam -->
            <tr>
                <td colspan="7" style="border: none;"><strong>2. Keping uang logam</strong></td>
            </tr>
            @foreach($uangLogam as $logam)
            <tr>
                <td style="padding-left: 20px; border: none;">
                    Keping uang logam
                </td>
                <td witdh="1%" class="text-end" style="border: none;">Rp.</td>
                <td style="border: none;">{{ number_format($logam['denominasi'], 0, ',', '.') }}</td>
                <td class="text-end" style="border: none;">
                    {{ $logam['keping'] }}
                </td>
                <td class="text-center" style="border: none;">Keping</td>
                <td class="text-end" style="border: none;">Rp.</td>
                <td style="border: none;">
                    <span class="text-end" style="float: right;">
                        {{ number_format($logam['jumlah'], 0, ',', '.') }}
                    </span>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-end" style="border: none;"><strong>Sub Jumlah (2)</strong></td>
                <td class="text-end" style="border: none;"><strong>Rp. </strong></td>
                <td colspan="1" class="text-end" style="border: none;"><strong>{{ number_format($totalUangLogam, 0, ',', '.') }}</strong></td>
            </tr>
            
            <!-- Saldo Bank -->
            <tr>
                <td colspan="7" style="border: none;"><strong>3. Saldo Bank, Surat Berharga, dll</strong></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end" style="border: none;"><strong>Sub Jumlah (3)</strong></td>
                <td colspan="1" class="text-end" style="border: none;"><strong>Rp.</strong></td>
                <td class="text-end" colspan="1" style="border: none;"><strong>{{ number_format($saldoBank, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Total -->
            <tr>
                <td colspan="5" class="text-end" style="border: none;"><strong>Jumlah (1 + 2 + 3)</strong></td>
                <td colspan="1" class="text-end" style="border: none;"><strong>Rp.</strong></td>
                <td colspan="1" class="text-end" style="border: none;"><strong>{{ number_format($saldoBuku, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Perbedaan -->
            <tr>
                <td colspan="5" class="text-end" style="border: none;"><strong>Perbedaan (A-B)</strong></td>
                <td colspan="1" class="text-end" style="border: none;"><strong>Rp.</strong></td>
                <td class="text-end" colspan="1" style="border: none;"><strong>{{ number_format($perbedaan, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Penjelasan Perbedaan -->
            <tr>
                <td colspan="7" style="border: none;">
                    <strong>Penjelasan Perbedaan :</strong><br>
                    <em>{{ $penjelasanPerbedaan }}</em>
                </td>
            </tr>

            <!-- Tanda Tangan -->
            <tr>
                <td class="text-center" style="height: 100px; border: none;">
                    <strong>&nbsp;</strong><br>
                    <strong>Yang diperiksa,</strong><br>
                    <br><br><br><br><br>
                    <strong>{{ $namaBendahara }}</strong><br>
                    NIP. {{ $nipBendahara }}
                </td>
                <td colspan="3" style="border: none;"></td>
                <td colspan="3" class="text-center" style="height: 100px; border: none;">
                    <strong><strong>Tanggal, {{ $tanggalPenutupan }}</strong><br>
                    <strong>Yang Memeriksa,</strong><br>
                    <br><br><br><br><br>
                    <strong>{{ $namaKepalaSekolah }}</strong><br>
                    <span>NIP. {{ $nipKepalaSekolah }}</span>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>