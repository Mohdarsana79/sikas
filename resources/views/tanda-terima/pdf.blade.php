<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Tanda Terima Honor</title>
    <style>
        /* Folio Landscape */
        @page {
            size: 33cm 21.6cm;
            /* Folio Landscape */
            margin: 1.5cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.3;
        }

        .header p {
            margin: 2px 0;
            font-size: 11pt;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            width: 150px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        .table-container {
            margin: 15px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 11pt;
        }

        .table th {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
        }

        .table td:first-child,
        .table th:first-child {
            text-align: center;
            width: 5%;
        }

        .table td:nth-child(2) {
            width: 25%;
        }

        .table td:nth-child(3),
        .table th:nth-child(3) {
            text-align: center;
            width: 8%;
        }

        .table td:nth-child(4),
        .table th:nth-child(4) {
            text-align: center;
            width: 8%;
        }

        .table td:nth-child(5),
        .table th:nth-child(5) {
            text-align: right;
            width: 12%;
        }

        .table td:nth-child(6),
        .table th:nth-child(6) {
            text-align: right;
            width: 12%;
        }

        .table td:nth-child(7),
        .table th:nth-child(7) {
            text-align: right;
            width: 12%;
        }

        .table td:nth-child(8),
        .table th:nth-child(8) {
            text-align: right;
            width: 12%;
        }

        .table td:last-child,
        .table th:last-child {
            text-align: center;
            width: 6%;
        }

        .terbilang-section {
            margin: 10px 0;
            padding: 8px;
            border: 1px solid #000;
            background-color: #f8f8f8;
        }

        .footer {
            margin-top: 30px;
        }

        .signature-table {
            width: 100%;
            margin-top: 60px;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
        }

        .signature-name {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-nip {
            margin-top: 5px;
            font-size: 11pt;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .empty-row {
            height: 40px;
        }

        /* Currency formatting */
        .currency {
            text-align: right;
            padding-right: 10px;
        }

        /* Ensure proper page breaks */
        .page-break {
            page-break-after: always;
        }

        /* Line separator */
        .separator {
            border-top: 2px solid #000;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h2>PEMERINTAH KABUPATEN TOLITOLI</h2>
        <h2>DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
        <h2>{{ $sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI' }}</h2>
        <p>Alamat : {{ $sekolah->alamat ?? 'Jl. Santa No. 150 Desa Paddumpu Kec. Damsel' }} NPSN : {{ $sekolah->npsn ??
            '40202255' }} Kode Pos : {{ $sekolah->kode_pos ?? '94554' }}</p>
        <p>Email : {{ $sekolah->email ?? 'smpmuhammadiyahsoni@yahoo.com' }}</p>
    </div>

    <div class="separator"></div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Kode Kegiatan</div>
            <div class="info-value">: {{ $kodeKegiatan->kode ?? '06.05.03' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kode Rekening</div>
            <div class="info-value">: {{ $rekeningBelanja->kode_rekening ?? '5.1.02.02.01.0027' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Uraian Kegiatan</div>
            <div class="info-value">: {{ $tandaTerima->bukuKasUmum->uraian ?? 'Lunas Bayar Insentif tenaga Operator
                Komputer / Dagodik' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sumber Dana</div>
            <div class="info-value">: BOSP Reguler</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Volume</th>
                    <th>Satuan</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                    <th>Potongan Pajak</th>
                    <th>Jumlah yang diterima</th>
                    <th>Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row 1 - Data utama -->
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $tandaTerima->bukuKasUmum->nama_penerima_pembayaran ?? 'Divi Ivana Rlandini, S.Pd' }}</td>
                    <td class="text-center">{{ $tandaTerima->bukuKasUmum->uraianDetails->first()->volume ?? '3' }}</td>
                    <td class="text-center">{{ $tandaTerima->bukuKasUmum->uraianDetails->first()->satuan ?? 'OK' }}</td>
                    <td class="currency">Rp {{
                        number_format($tandaTerima->bukuKasUmum->uraianDetails->first()->harga_satuan ?? 500000, 0, ',',
                        '.') }}</td>
                    <td class="currency">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td class="currency">
                        @if($pajakPusat > 0)
                        Rp {{ number_format($pajakPusat, 0, ',', '.') }}
                        @else
                        -
                        @endif
                    </td>
                    <td class="currency">Rp {{ number_format($jumlahTerima, 0, ',', '.') }}</td>
                    <td class="text-center">1</td>
                </tr>

                <!-- Empty rows 2-5 -->
                @for($i = 2; $i <= 5; $i++) <tr class="empty-row">
                    <td class="text-center">{{ $i }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="currency"></td>
                    <td class="currency"></td>
                    <td class="currency"></td>
                    <td class="currency"></td>
                    <td class="text-center">{{ $i }}</td>
                    </tr>
                    @endfor
            </tbody>
        </table>
    </div>

    <!-- Terbilang Section -->
    <div class="terbilang-section">
        <div class="text-bold">Terbilang: {{ $jumlahUangText }}</div>
        @if($pajakPusat > 0)
        <div style="margin-top: 5px;">
            <strong>Keterangan Pajak:</strong> Telah dipotong Pajak Pusat sebesar Rp {{ number_format($pajakPusat, 0,
            ',', '.') }}
        </div>
        @endif
    </div>

    <!-- Footer Signature -->
    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah
                    <div class="signature-name">
                        {{ $sekolah->kepala_sekolah ?? 'Dra. MASTIAH ABDULLAH' }}
                    </div>
                    <div class="signature-nip">
                        NIP. {{ $sekolah->nip_kepala_sekolah ?? '196909172007012017' }}
                    </div>
                </td>
                <td>
                    {{ $sekolah->kota ?? 'Tolitoli' }}, {{ $tanggalLunas }}<br>
                    Bendahara BOSP
                    <div class="signature-name">
                        {{ $sekolah->bendahara ?? 'Dra. MASTIAH ABDULLAH' }}
                    </div>
                    <div class="signature-nip">
                        NIP. {{ $sekolah->nip_bendahara ?? '196909172007012017' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>