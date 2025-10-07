<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKP Registrasi {{ $bulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            margin: 0;
            padding: 15px;
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

<body>
    <!-- Header -->
    <div class="header-title">Formulir BOS-K7B</div>
    <div class="sub-header">REGISTER PENUTUPAN KAS</div>

    <!-- Content -->
    <table class="table">
        <tbody>
            <!-- Informasi Header -->
            <tr>
                <td width="40%">Tanggal Penutupan Kas :</td>
                <td width="60%"><strong>{{ $tanggalPenutupan }}</strong></td>
            </tr>
            <tr>
                <td>Nama Penutup Kas (Pemegang Kas) :</td>
                <td><strong>{{ $namaBendahara }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Penutupan Kas Yang Lalu :</td>
                <td><strong>{{ $tanggalPenutupanLalu }}</strong></td>
            </tr>
            <tr>
                <td>Jumlah Total Penerimaan (D) :</td>
                <td><strong>Rp. {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Jumlah Total Pengeluaran (K) :</td>
                <td><strong>Rp. {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Saldo Buku (A = D - K) :</td>
                <td><strong>Rp. {{ number_format($saldoBuku, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Saldo Kas (B) :</td>
                <td><strong>Rp. {{ number_format($saldoKas, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Spacer -->
            <tr>
                <td colspan="2" class="bg-light"><strong>Saldo Kas B terdiri dari :</strong></td>
            </tr>

            <!-- Uang Kertas -->
            <tr>
                <td colspan="2"><strong>1. Lembaran uang kertas</strong></td>
            </tr>
            @foreach($uangKertas as $uang)
            <tr>
                <td width="5%"></td>
                <td>
                    Lembaran uang kertas Rp. {{ number_format($uang['denominasi'], 0, ',', '.') }}
                    {{ $uang['lembar'] }} Lembar
                    <span class="text-end" style="float: right;">
                        Rp. {{ number_format($uang['jumlah'], 0, ',', '.') }}
                    </span>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="1" class="text-end"><strong>Sub Jumlah (1)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($totalUangKertas, 0, ',', '.') }}</strong></td>
            </tr>
            
            <!-- Uang Logam -->
            <tr>
                <td colspan="2"><strong>2. Keping uang logam</strong></td>
            </tr>
            @foreach($uangLogam as $logam)
            <tr>
                <td width="5%"></td>
                <td>
                    Keping uang logam Rp. {{ number_format($logam['denominasi'], 0, ',', '.') }}
                    {{ $logam['keping'] }} Keping
                    <span class="text-end" style="float: right;">
                        Rp. {{ number_format($logam['jumlah'], 0, ',', '.') }}
                    </span>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="1" class="text-end"><strong>Sub Jumlah (2)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($totalUangLogam, 0, ',', '.') }}</strong></td>
            </tr>
            
            <!-- Saldo Bank -->
            <tr>
                <td colspan="2"><strong>3. Saldo Bank, Surat Berharga, dll</strong></td>
            </tr>
            <tr>
                <td colspan="1" class="text-end"><strong>Sub Jumlah (3)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($saldoBank, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Total -->
            <tr>
                <td colspan="1" class="text-end"><strong>Jumlah (1 + 2 + 3)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($saldoBuku, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Perbedaan -->
            <tr>
                <td colspan="1" class="text-end"><strong>Perbedaan (A-B)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($perbedaan, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Penjelasan Perbedaan -->
            <tr>
                <td colspan="2">
                    <strong>Penjelasan Perbedaan :</strong><br>
                    <em>{{ $penjelasanPerbedaan }}</em>
                </td>
            </tr>

            <!-- Tanda Tangan -->
            <tr>
                <td class="text-center" style="height: 100px;">
                    <strong>Tanggal, {{ $tanggalPenutupan }}</strong><br>
                    <strong>Yang diperiksa,</strong><br>
                    <br><br><br>
                    <strong>{{ $namaBendahara }}</strong><br>
                    NIP. {{ $nipBendahara }}
                </td>
                <td class="text-center" style="height: 100px;">
                    <strong>&nbsp;</strong><br>
                    <strong>Yang Memeriksa,</strong><br>
                    <br><br><br>
                    <strong>{{ $namaKepalaSekolah }}</strong><br>
                    NIP. {{ $nipKepalaSekolah }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Footer dengan informasi cetak -->
    <div class="footer">
        Dicetak pada: {{ $tanggal_cetak }}
    </div>
</body>

</html>