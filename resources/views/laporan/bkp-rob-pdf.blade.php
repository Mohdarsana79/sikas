<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKP ROB {{ $bulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
        }

        .header h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
        }

        .info-sekolah {
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .table-rob {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .table-rob th,
        .table-rob td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .table-rob thead th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .rekening-header-row {
            background-color: #e8f4fd;
            font-weight: bold;
        }

        .total-row-rob {
            background-color: #d0d0d0;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .page-break {
            page-break-after: always;
        }

        .ttd {
            margin-top: 30px;
            width: 100%;
        }

        .ttd-table {
            width: 100%;
            border: none;
        }

        .ttd-table td {
            padding: 20px;
            text-align: center;
            border: none;
        }

        .ttd-space {
            height: 60px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>BUKU PEMBANTU RINCIAN OBJEK BELANJA</h2>
        <h3>Bulan {{ $bulan }} Tahun {{ $tahun }}</h3>
    </div>

    <div class="info-sekolah">
        <table style="width: 100%; border: none; font-size: 9pt;">
            <tr>
                <td style="width: 30%;">NPSN</td>
                <td style="width: 2%;">:</td>
                <td style="width: 68%;">{{ $sekolah->npsn ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Sekolah</td>
                <td>:</td>
                <td>{{ $sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI' }}</td>
            </tr>
            <tr>
                <td>Desa / Kelurahan</td>
                <td>:</td>
                <td>{{ $sekolah->desa_kelurahan ?? 'Paddumpu' }}</td>
            </tr>
            <tr>
                <td>Kecamatan</td>
                <td>:</td>
                <td>{{ $sekolah->kecamatan ?? 'Dampal Selatan' }}</td>
            </tr>
            <tr>
                <td>Kabupaten / Kota</td>
                <td>:</td>
                <td>{{ $sekolah->kabupaten_kota ?? 'Tolitoli' }}</td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td>:</td>
                <td>{{ $sekolah->provinsi ?? 'Sulawesi Tengah' }}</td>
            </tr>
            <tr>
                <td>Anggaran Belanja</td>
                <td>:</td>
                <td><strong>Rp {{ number_format($saldoAwal ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="table-rob">
        <thead>
            <tr>
                <th width="15%">Tanggal</th>
                <th width="15%">No Bukti</th>
                <th width="30%">Uraian</th>
                <th width="15%">Realisasi</th>
                <th width="15%">Jumlah</th>
                <th width="15%">Sisa Anggaran</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalKeseluruhan = 0;
            $sisaAnggaran = $saldoAwal ?? 0;
            $totalRealisasi = $totalRealisasi ?? 0;
            $pageCount = 0;
            @endphp

            @if(!empty($robData) && count($robData) > 0)
            @foreach($robData as $rekening)
            @if($pageCount > 25)
            <tr class="page-break">
                <td colspan="6"></td>
            </tr>
            @php $pageCount = 0; @endphp
            @endif

            <tr class="rekening-header-row">
                <td colspan="6">
                    <strong>{{ $rekening['kode'] }}</strong> - {{ $rekening['nama_rekening'] }}
                </td>
            </tr>
            @php $pageCount++; @endphp

            @php
            $totalRekening = 0;
            @endphp

            @foreach($rekening['transaksi'] as $transaksi)
            @if($pageCount > 25)
            <tr class="page-break">
                <td colspan="6"></td>
            </tr>
            @php $pageCount = 0; @endphp
            @endif

            @php
            $totalRekening += $transaksi['realisasi'];
            $totalKeseluruhan += $transaksi['realisasi'];
            $sisaAnggaran = ($saldoAwal ?? 0) - $totalKeseluruhan;
            @endphp

            <tr>
                <td>{{ $transaksi['tanggal'] }}</td>
                <td>{{ $transaksi['no_bukti'] }}</td>
                <td>{{ $transaksi['uraian'] }}</td>
                <td class="text-right">Rp {{ number_format($transaksi['realisasi'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</td>
            </tr>
            @php $pageCount++; @endphp
            @endforeach
            @endforeach

            <!-- Ringkasan Keseluruhan -->
            <tr class="total-row-rob">
                <td colspan="3" class="text-center">
                    <strong>Jumlah</strong><br>
                </td>
                <td class="text-right"><strong>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</strong></td>
            </tr>
            @else
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Tidak ada data transaksi untuk bulan {{ $bulan }} {{ $tahun }}</p>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    @if(!empty($robData) && count($robData) > 0)
    <div class="ttd">
        <table class="ttd-table">
            <tr>
                <td style="width: 50%;">
                    Mengetahui<br>
                    Kepala Sekolah
                    <div class="ttd-space"></div>
                    <strong>{{ $sekolah->kepala_sekolah ?? 'Dra. MASITAH ABDULLAH' }}</strong><br>
                    NIP. {{ $sekolah->nip_kepala_sekolah ?? '19690917 200701 2 017' }}
                </td>
                <td style="width: 50%;">
                    Dibuat oleh<br>
                    Bendahara
                    <div class="ttd-space"></div>
                    <strong>{{ $sekolah->bendahara ?? 'Dra. MASITAH ABDULLAH' }}</strong><br>
                    NIP. {{ $sekolah->nip_bendahara ?? '19690917 200701 2 017' }}
                </td>
            </tr>
        </table>
    </div>
    @endif

    <div style="position: fixed; bottom: 10px; right: 10px; font-size: 8pt; color: #666;">
        Dicetak pada: {{ $tanggal_cetak }}
    </div>
</body>

</html>