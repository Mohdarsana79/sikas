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
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 4px 6px;
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

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .table {
                font-size: 9pt;
            }
        }
    </style>
</head>

<body>
    @include('laporan.partials.bkp-registrasi-table', [
    'tahun' => $tahun,
    'bulan' => $bulan,
    'bulanAngka' => $bulanAngka,
    'penganggaran' => $penganggaran,
    'sekolah' => $sekolah,
    'totalPenerimaan' => $totalPenerimaan,
    'totalPengeluaran' => $totalPengeluaran,
    'saldoBuku' => $saldoBuku,
    'saldoKas' => $saldoKas,
    'saldoBank' => $saldoBank,
    'uangKertas' => $uangKertas,
    'uangLogam' => $uangLogam,
    'perbedaan' => $perbedaan,
    'penjelasanPerbedaan' => $penjelasanPerbedaan,
    'tanggalPenutupan' => $tanggalPenutupan,
    'tanggalPenutupanLalu' => $tanggalPenutupanLalu,
    'namaBendahara' => $namaBendahara,
    'namaKepalaSekolah' => $namaKepalaSekolah,
    'nipBendahara' => $nipBendahara,
    'nipKepalaSekolah' => $nipKepalaSekolah
    ])

    <!-- Footer dengan informasi cetak -->
    <div style="position: fixed; bottom: 10px; right: 10px; font-size: 8pt; color: #666;">
        Dicetak pada: {{ $tanggal_cetak }}
    </div>
</body>

</html>