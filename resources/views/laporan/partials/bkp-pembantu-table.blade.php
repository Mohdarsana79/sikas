<div class="table-responsive">
    <table class="table table-bordered table-sm" style="font-size: 9pt;">
        <thead class="table-light">
            <tr>
                <th width="8%">Tanggal</th>
                <th width="12%">Kode Rekening</th>
                <th width="8%">No. Bukti</th>
                <th width="30%">Uraian</th>
                <th width="12%">Penerimaan (Kredit)</th>
                <th width="12%">Pengeluaran (Debet)</th>
                <th width="12%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
            $currentSaldo = $saldoAwalTunai;
            @endphp

            <!-- Baris Saldo Kas Tunai -->
            <tr>
                <td>1/{{ $bulanAngka }}/{{ $tahun }}</td>
                <td>-</td>
                <td>-</td>
                <td>Saldo Kas Tunai</td>
                <td class="text-end">{{ number_format($saldoAwalTunai, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>

            <!-- Data Penarikan Tunai -->
            @foreach($penarikanTunais as $penarikan)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Penarikan Tunai</td>
                <td class="text-end">{{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $penarikan->jumlah_penarikan;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endforeach

            <!-- Data Setor Tunai -->
            @foreach($setorTunais as $setor)
            <tr>
                <td>{{ \Carbon\Carbon::parse($setor->tanggal_setor)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Setor Tunai</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($setor->jumlah_setor, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    $currentSaldo -= $setor->jumlah_setor;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endforeach

            <!-- Data Transaksi BKU Tunai -->
            @foreach($bkuDataTunai as $transaksi)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>{{ $transaksi->rekeningBelanja->kode_rekening ?? '-' }}</td>
                <td>{{ $transaksi->id_transaksi }}</td>
                <td>{{ $transaksi->uraian_opsional }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($transaksi->total_transaksi_kotor, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    $currentSaldo -= $transaksi->total_transaksi_kotor;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>

            <!-- Baris Pajak Pusat jika ada -->
            @if($transaksi->total_pajak > 0)
            @if(empty($transaksi->ntpn))
            <!-- Jika NTPN belum ada (Terima Pajak) - di kolom PENERIMAAN -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</td>
                <td>-</td>
                <td>{{ $transaksi->kode_masa_pajak }}</td>
                <td>Terima Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional }}
                </td>
                <td class="text-end">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $transaksi->total_pajak;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @else
            <!-- Jika NTPN sudah ada (Setor Pajak) - tampil di KEDUA KOLOM -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>{{ $transaksi->kode_masa_pajak }}</td>
                <td>Setor Pajak {{ $transaksi->pajak }} {{ $transaksi->persen_pajak }}% {{ $transaksi->uraian_opsional }}
                </td>
                <td class="text-end">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    // Saldo tetap karena penerimaan dan pengeluaran sama
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif
            @endif

            <!-- Baris Pajak Daerah jika ada -->
            @if($transaksi->total_pajak_daerah > 0)
            @if(empty($transaksi->ntpn))
            <!-- Jika NTPN belum ada (Terima Pajak Daerah) - di kolom PENERIMAAN -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Terima Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">
                    @php
                    $currentSaldo += $transaksi->total_pajak_daerah;
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @else
            <!-- Jika NTPN sudah ada (Setor Pajak Daerah) - tampil di KEDUA KOLOM -->
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                <td>-</td>
                <td>-</td>
                <td>Setor Pajak Daerah {{ $transaksi->pajak_daerah }} {{ $transaksi->persen_pajak_daerah }}% {{
                    $transaksi->uraian_opsional }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($transaksi->total_pajak_daerah, 0, ',', '.') }}</td>
                <td class="text-end">
                    @php
                    // Saldo tetap karena penerimaan dan pengeluaran sama
                    echo number_format($currentSaldo, 0, ',', '.');
                    @endphp
                </td>
            </tr>
            @endif
            @endif
            @endforeach

            <!-- Baris Jumlah Penutupan -->
            <tr class="table-secondary fw-bold">
                <td colspan="4" class="text-center">Jumlah Penutupan</td>
                <td class="text-end">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>