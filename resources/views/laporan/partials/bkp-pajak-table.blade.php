<div class="table-responsive">
    <table class="table table-bordered table-sm" style="font-size: 9pt;">
        <thead class="table-light">
            <tr>
                <th width="">Tanggal</th>
                <th width="">No. Kode</th>
                <th width="15%">No. Bukti</th>
                <th width="30%">Uraian</th>
                <th width="8%">PPN</th>
                <th width="8%">PPh 21</th>
                <th width="8%">PPh 22</th>
                <th width="8%">PPh 23</th>
                <th width="8%">PB 1</th>
                <th width="">JML</th>
                <th width="">Pengeluaran (Kredit)</th>
                <th width="">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
            $currentSaldo = 0;
            // Safety check - jika $pajakRows tidak ada, buat array kosong
            $pajakRows = $pajakRows ?? [];
            @endphp

            <!-- Baris Saldo Awal -->
            <tr>
                <td>1/{{ $bulanAngka }}/{{ $tahun }}</td>
                <td>-</td>
                <td>-</td>
                <td>Saldo awal bulan</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>

            <!-- Data Transaksi Pajak -->
            @if(count($pajakRows) > 0)
                @foreach($pajakRows as $row)
                @php
                $transaksi = $row['transaksi'];
                @endphp

                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</td>
                    <td>{{ $transaksi->rekeningBelanja->kode_rekening ?? '-' }}</td>
                    <td>{{ $transaksi->kode_masa_pajak ?? '-' }}</td>
                    <td>{{ $row['uraian'] }}</td>
                    <td class="text-end">{{ $row['ppn'] > 0 ? number_format($row['ppn'], 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $row['pph21'] > 0 ? number_format($row['pph21'], 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $row['pph22'] > 0 ? number_format($row['pph22'], 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $row['pph23'] > 0 ? number_format($row['pph23'], 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $row['pb1'] > 0 ? number_format($row['pb1'], 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ $row['pengeluaran'] > 0 ? number_format($row['pengeluaran'], 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @else
                <!-- Jika tidak ada data pajak -->
                <tr>
                    <td colspan="12" class="text-center text-muted py-3">
                        <i class="bi bi-receipt me-2"></i>
                        Tidak ada data pajak untuk bulan {{ $bulan }} {{ $tahun }}
                    </td>
                </tr>
            @endif

            <!-- Baris Jumlah Penutupan -->
            @if(count($pajakRows) > 0)
            <tr class="table-secondary fw-bold">
                <td colspan="4" class="text-center">Jumlah Penutupan</td>
                <td class="text-end">{{ number_format($totalPpn ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPph21 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPph22 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPph23 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPb1 ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPenerimaan ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPengeluaran ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>