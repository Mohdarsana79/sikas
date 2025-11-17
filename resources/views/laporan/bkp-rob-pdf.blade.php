<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKP ROB {{ $bulan }} {{ $tahun }}</title>
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
            margin-top: 5px;
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

<body style="font-size: {{ $printSettings['font_size'] }};">
    <div class="header" style="font-size: {{ $printSettings['font_size'] }};">
        <h2 style="font-size: {{ $printSettings['font_size'] }};">BUKU PEMBANTU RINCIAN OBJEK BELANJA</h2>
        <h3 style="font-size: {{ $printSettings['font_size'] }};">Bulan {{ $bulan }} Tahun {{ $tahun }}</h3>
    </div>

    <div class="info-sekolah" style="font-size: {{ $printSettings['font_size'] }};">
        <table style="width: 100%; border: none; font-size: {{$printSettings['font_size']}};">
            <tr>
                <td style="width: 30%; font-size: {{$printSettings['font_size']}};">NPSN</td>
                <td style="width: 2%; font-size: {{$printSettings['font_size']}};">:</td>
                <td style="width: 68%; font-size:{{$printSettings['font_size']}};">{{ $sekolah->npsn ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">Nama Sekolah</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->nama_sekolah ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">Desa / Kelurahan</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->kelurahan_desa ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $printSettings['font_size'] }};">Kecamatan</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $sekolah->kecamatan ?? '-' }}</td>
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
                <td style="font-size: {{ $printSettings['font_size'] }};">Anggaran Belanja</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">:</td>
                <td style="font-size: {{ $printSettings['font_size'] }};"><strong>Rp {{ number_format($saldoAwal ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="table-rob" style="font-size: {{ $printSettings['font_size'] }};">
        <thead>
            <tr>
                <th width="15%" style="font-size: {{ $printSettings['font_size'] }};">Tanggal</th>
                <th width="15%" style="font-size: {{ $printSettings['font_size'] }};">No Bukti</th>
                <th width="30%" style="font-size: {{ $printSettings['font_size'] }};">Uraian</th>
                <th width="15%" style="font-size: {{ $printSettings['font_size'] }};">Realisasi</th>
                <th width="15%" style="font-size: {{ $printSettings['font_size'] }};">Jumlah</th>
                <th width="15%" style="font-size: {{ $printSettings['font_size'] }};">Sisa Anggaran</th>
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
                <td colspan="6" style="font-size: {{ $printSettings['font_size'] }};">
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
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $transaksi['tanggal'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $transaksi['no_bukti'] }}</td>
                <td style="font-size: {{ $printSettings['font_size'] }};">{{ $transaksi['uraian'] }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">Rp {{ number_format($transaksi['realisasi'], 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};">Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</td>
            </tr>
            @php $pageCount++; @endphp
            @endforeach
            @endforeach

            <!-- Ringkasan Keseluruhan -->
            <tr class="total-row-rob">
                <td colspan="3" class="text-center" style="font-size: {{ $printSettings['font_size'] }};">
                    <strong>Jumlah</strong><br>
                </td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right" style="font-size: {{ $printSettings['font_size'] }};"><strong>Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</strong></td>
            </tr>
            @else
            <tr>
                <td colspan="6" class="text-center py-4" style="font-size: {{ $printSettings['font_size'] }};">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Tidak ada data transaksi untuk bulan {{ $bulan }} {{ $tahun }}</p>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    @if(!empty($robData) && count($robData) > 0)
    <div class="ttd">
        <table class="ttd-table" style="font-size: {{ $printSettings['font_size'] }};">
            <tr>
                <td style="width: 50%; font-size: {{$printSettings['font_size']}};">
                    Mengetahui<br>
                    Kepala Sekolah
                    <div class="ttd-space"></div>
                    <strong>{{ $penganggaran->kepala_sekolah ?? '-' }}</strong><br>
                    NIP. {{ $penganggaran->nip_kepala_sekolah ?? '-' }}
                </td>
                <td style="width: 50%; font-size: {{$printSettings['font_size']}};">
                    {{ $sekolah->kabupaten_kota }}, {{$formatAkhirBulanSingkat}}<br>
                    Bendahara
                    <div class="ttd-space"></div>
                    <strong>{{ $penganggaran->bendahara ?? '-' }}</strong><br>
                    NIP. {{ $penganggaran->nip_bendahara ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
    @endif
</body>

</html>