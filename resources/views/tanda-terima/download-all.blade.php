<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Semua Tanda Terima</title>
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

        .info-header {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8f8f8;
            border: 1px solid #000;
        }

        .info-row {
            display: flex;
            margin-bottom: 3px;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        .table-container {
            margin: 15px 0;
            page-break-inside: avoid;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 10pt;
            margin-bottom: 20px;
        }

        .table th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: left;
        }

        .table td:first-child,
        .table th:first-child {
            text-align: center;
            width: 4%;
        }

        .table td:nth-child(2) {
            width: 20%;
        }

        .table td:nth-child(3),
        .table th:nth-child(3) {
            text-align: center;
            width: 7%;
        }

        .table td:nth-child(4),
        .table th:nth-child(4) {
            text-align: center;
            width: 7%;
        }

        .table td:nth-child(5),
        .table th:nth-child(5) {
            text-align: right;
            width: 10%;
        }

        .table td:nth-child(6),
        .table th:nth-child(6) {
            text-align: right;
            width: 10%;
        }

        .table td:nth-child(7),
        .table th:nth-child(7) {
            text-align: right;
            width: 10%;
        }

        .table td:nth-child(8),
        .table th:nth-child(8) {
            text-align: right;
            width: 10%;
        }

        .table td:last-child,
        .table th:last-child {
            text-align: center;
            width: 5%;
        }

        .terbilang-section {
            margin: 8px 0;
            padding: 6px;
            border: 1px solid #000;
            background-color: #f8f8f8;
            font-size: 10pt;
        }

        .footer {
            margin-top: 20px;
        }

        .signature-table {
            width: 100%;
            margin-top: 40px;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
        }

        .signature-name {
            margin-top: 40px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-nip {
            margin-top: 3px;
            font-size: 10pt;
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

        .currency {
            text-align: right;
            padding-right: 8px;
        }

        .page-break {
            page-break-after: always;
        }

        .separator {
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        .document-info {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 10px;
            color: #666;
        }

        /* Untuk multiple pages */
        .tanda-terima-item {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .item-counter {
            background-color: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Document Info -->
    <div class="document-info">
        Dicetak pada: {{ $tanggalDownload }} | Total: {{ $totalTandaTerima }} tanda terima
    </div>

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

    <!-- Loop melalui semua tanda terima -->
    @foreach($tandaTerimas as $index => $item)
    <div class="tanda-terima-item">
        <!-- Item Counter -->
        <div class="item-counter">
            Tanda Terima #{{ $index + 1 }} dari {{ $totalTandaTerima }}
        </div>

        <!-- Information Section -->
        <div class="info-header">
            <div class="info-row">
                <div class="info-label">Kode Kegiatan</div>
                <div class="info-value">: {{ $item['kodeKegiatan']->kode ?? '06.05.03' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kode Rekening</div>
                <div class="info-value">: {{ $item['rekeningBelanja']->kode_rekening ?? '5.1.02.02.01.0027' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Uraian Kegiatan</div>
                <div class="info-value">: {{ $item['bukuKasUmum']->uraian ?? 'Lunas Bayar Insentif tenaga Operator
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
                        <td>{{ $item['bukuKasUmum']->nama_penerima_pembayaran ?? 'Divi Ivana Rlandini, S.Pd' }}</td>
                        <td class="text-center">{{ $item['bukuKasUmum']->uraianDetails->first()->volume ?? '3' }}</td>
                        <td class="text-center">{{ $item['bukuKasUmum']->uraianDetails->first()->satuan ?? 'OK' }}</td>
                        <td class="currency">Rp {{
                            number_format($item['bukuKasUmum']->uraianDetails->first()->harga_satuan ?? 500000, 0, ',',
                            '.') }}</td>
                        <td class="currency">Rp {{ number_format($item['totalAmount'], 0, ',', '.') }}</td>
                        <td class="currency">
                            @if($item['pajakPusat'] > 0)
                            Rp {{ number_format($item['pajakPusat'], 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="currency">Rp {{ number_format($item['jumlahTerima'], 0, ',', '.') }}</td>
                        <td class="text-center">1</td>
                    </tr>

                    <!-- Empty rows 2-5 -->
                    @for($i = 2; $i <= 5; $i++) <tr>
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
            <div class="text-bold">Terbilang: {{ $item['jumlahUangText'] }}</div>
            @if($item['pajakPusat'] > 0)
            <div style="margin-top: 3px;">
                <strong>Keterangan Pajak:</strong> Telah dipotong Pajak Pusat sebesar Rp {{
                number_format($item['pajakPusat'], 0, ',', '.') }}
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
                        {{ $sekolah->kota ?? 'Tolitoli' }}, {{ $item['tanggalLunas'] }}<br>
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

        <!-- Page break kecuali untuk item terakhir -->
        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
    </div>
    @endforeach
</body>

</html>