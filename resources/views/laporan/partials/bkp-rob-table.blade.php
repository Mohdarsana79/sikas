<div class="table-responsive">
    <table class="table table-bordered table-sm" style="font-size: 9pt;">
        <thead>
            <tr>
                <th colspan="6" class="text-center bg-light">
                    <strong>BUKU PEMBANTU RINCIAN OBJEK BELANJA</strong><br>
                    Bulan {{ $bulan }} Tahun {{ $tahun }}
                </th>
            </tr>
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
            $sisaAnggaran = $saldoAwal ?? 0; // GUNAKAN saldoAwal
            $totalRealisasi = $totalRealisasi ?? 0;
            @endphp

            @if(!empty($robData) && count($robData) > 0)
            @foreach($robData as $rekening)
            <tr class="rekening-header-row text-start">
                <td colspan="6">
                    <strong>{{ $rekening['kode'] }}</strong> - {{ $rekening['nama_rekening'] }}
                </td>
            </tr>

            @php
            $totalRekening = 0;
            @endphp

            @foreach($rekening['transaksi'] as $index => $transaksi)
            @php
            $totalRekening += $transaksi['realisasi'];
            $totalKeseluruhan += $transaksi['realisasi'];
            $sisaAnggaran = ($saldoAwal ?? 0) - $totalKeseluruhan;
            @endphp

            <tr>
                <td>{{ $transaksi['tanggal'] }}</td>
                <td>{{ $transaksi['no_bukti'] }}</td>
                <td class="text-start">{{ $transaksi['uraian'] }}</td>
                <td class="text-right">Rp {{ number_format($transaksi['realisasi'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endforeach

            <!-- Ringkasan Keseluruhan -->
            <tr class="total-row-rob">
                <td colspan="3" class="text-center">
                    <strong>Jumlah</strong><br>
                </td>
                <td class="text-right"><strong>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right bg-body-secondary"><strong>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right bg-body-secondary"><strong>Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</strong></td>
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
</div>