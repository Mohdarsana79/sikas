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
            // VARIABEL UNTUK IMPLEMENTASI RUMUS EXCEL
            $runningPenerimaan = 0;
            $runningPengeluaran = 0;
            $currentSaldo = 0;
            $rowIndex = 0;

            // DATA UNTUK PERHITUNGAN
            $rowsData = [];

            // BARIS 1: Saldo Awal
            $rowsData[] = [
            'tanggal' => '1/'.$bulanAngka.'/'.$tahun,
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Saldo Awal bulan ' . $bulan,
            'penerimaan' => $saldoAwal,
            'pengeluaran' => 0,
            'is_saldo_awal' => true
            ];

            // BARIS 2: Saldo Kas Tunai (jika ada)
            if($saldoAwalTunai > 0) {
            $rowsData[] = [
            'tanggal' => '1/'.$bulanAngka.'/'.$tahun,
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Saldo Kas Tunai',
            'penerimaan' => $saldoAwalTunai,
            'pengeluaran' => 0
            ];
            }

            // DATA PENERIMAAN DANA
            foreach($penerimaanDanas as $penerimaan) {
            if(\Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka) {
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d/m/Y'),
            'kode_rekening' => '299',
            'no_bukti' => '-',
            'uraian' => 'Terima Dana '.$penerimaan->sumber_dana.' T.A '.$tahun,
            'penerimaan' => $penerimaan->jumlah_dana,
            'pengeluaran' => 0
            ];
            }
            }

            // DATA PENARIKAN TUNAI
            foreach($penarikanTunais as $penarikan) {
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d/m/Y'),
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Penarikan Tunai ' .$penerimaan->sumber_dana.' T.A '.$tahun,
            'penerimaan' => 0,
            'pengeluaran' => $penarikan->jumlah_penarikan
            ];
            }

            // DATA TERIMA TUNAI
            foreach($terimaTunais as $terima) {
                $rowsData[] = [
                    'tanggal' => \Carbon\Carbon::parse($terima->tanggal_penarikan)->format('d/m/Y'),
                    'kode_rekening' => '-',
                    'no_bukti' => '-',
                    'uraian' => 'Terima Tunai ' .$penerimaan->sumber_dana.' T.A '.$tahun,
                    'penerimaan' => $terima->jumlah_penarikan,
                    'pengeluaran' => 0
            ];
            }

            // DATA SETOR TUNAI
            foreach($setorTunais as $setor) {
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($setor->tanggal_setor)->format('d/m/Y'),
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Setor Tunai',
            'penerimaan' => 0,
            'pengeluaran' => $setor->jumlah_setor
            ];
            }

            // DATA TRANSAKSI BKU
            foreach($bkuData as $transaksi) {
            // Baris transaksi utama
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => $transaksi->rekeningBelanja->kode_rekening ?? '-',
            'no_bukti' => $transaksi->id_transaksi,
            'uraian' => $transaksi->uraian_opsional ?? $transaksi->uraian,
            'penerimaan' => 0,
            'pengeluaran' => $transaksi->total_transaksi_kotor
            ];

            // Baris Pajak Pusat jika ada
            if($transaksi->total_pajak > 0) {
            if(empty($transaksi->ntpn)) {
            // Jika NTPN belum ada (Terima Pajak) - di kolom PENERIMAAN
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => '-',
            'no_bukti' => $transaksi->kode_masa_pajak ?? '-',
            'uraian' => 'Terima Pajak '.($transaksi->pajak ?? '').' '.($transaksi->persen_pajak ?? '').'%
            '.($transaksi->uraian_opsional ?? $transaksi->uraian),
            'penerimaan' => $transaksi->total_pajak,
            'pengeluaran' => 0
            ];
            } else {
            // Jika NTPN sudah ada (Setor Pajak) - tampil di KEDUA KOLOM
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => '-',
            'no_bukti' => $transaksi->kode_masa_pajak ?? '-',
            'uraian' => 'Setor Pajak '.($transaksi->pajak ?? '').' '.($transaksi->persen_pajak ?? '').'%
            '.($transaksi->uraian_opsional ?? $transaksi->uraian),
            'penerimaan' => $transaksi->total_pajak,
            'pengeluaran' => $transaksi->total_pajak
            ];
            }
            }

            // Baris Pajak Daerah jika ada
            if($transaksi->total_pajak_daerah > 0) {
            if(empty($transaksi->ntpn)) {
            // Jika NTPN belum ada (Terima Pajak Daerah) - di kolom PENERIMAAN
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Terima Pajak Daerah '.($transaksi->pajak_daerah ?? '').' '.($transaksi->persen_pajak_daerah ??
            '').'% '.($transaksi->uraian_opsional ?? $transaksi->uraian),
            'penerimaan' => $transaksi->total_pajak_daerah,
            'pengeluaran' => 0
            ];
            } else {
            // Jika NTPN sudah ada (Setor Pajak Daerah) - tampil di KEDUA KOLOM
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Setor Pajak Daerah '.($transaksi->pajak_daerah ?? '').' '.($transaksi->persen_pajak_daerah ??
            '').'% '.($transaksi->uraian_opsional ?? $transaksi->uraian),
            'penerimaan' => $transaksi->total_pajak_daerah,
            'pengeluaran' => $transaksi->total_pajak_daerah
            ];
            }
            }
            }

            // BUNGA BANK
            if($bungaRecord && $bungaRecord->bunga_bank > 0) {
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => '299',
            'no_bukti' => '-',
            'uraian' => 'Bunga Bank',
            'penerimaan' => $bungaRecord->bunga_bank,
            'pengeluaran' => 0
            ];
            }

            // PAJAK BUNGA BANK
            if($bungaRecord && $bungaRecord->pajak_bunga_bank > 0) {
            $rowsData[] = [
            'tanggal' => \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d/m/Y'),
            'kode_rekening' => '199',
            'no_bukti' => '-',
            'uraian' => 'Pajak Bunga Bank',
            'penerimaan' => 0,
            'pengeluaran' => $bungaRecord->pajak_bunga_bank
            ];
            }
            @endphp

            <!-- Debug Info -->
            {{-- @if(env('APP_DEBUG'))
            <tr class="table-warning">
                <td colspan="7" style="font-size: 8pt;">
                    <strong>DEBUG INFO:</strong>
                    Saldo Awal dari BKP Bank: {{ number_format($saldoAwal, 0, ',', '.') }} |
                    Saldo Awal Tunai: {{ number_format($saldoAwalTunai, 0, ',', '.') }} |
                    Total Rows: {{ count($rowsData) }}
                </td>
            </tr>
            @endif --}}

            <!-- RENDER SEMUA BARIS DENGAN PERHITUNGAN SALDO YANG BENAR -->
            @foreach($rowsData as $index => $row)
            @php
            // IMPLEMENTASI RUMUS EXCEL: =IF(OR(G11<>0,H11<>0),SUM(G$11:G11)-SUM(H$11:H11),0)
                    $runningPenerimaan += $row['penerimaan'];
                    $runningPengeluaran += $row['pengeluaran'];

                    // Hanya hitung saldo jika ada penerimaan atau pengeluaran di baris ini
                    if($row['penerimaan'] != 0 || $row['pengeluaran'] != 0) {
                    $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }
                    // Untuk baris saldo awal, tetap gunakan nilai saldo awal
                    elseif(isset($row['is_saldo_awal']) && $row['is_saldo_awal']) {
                    $currentSaldo = $row['penerimaan'];
                    }
                    @endphp

                    <tr>
                        <td>{{ $row['tanggal'] }}</td>
                        <td>{{ $row['kode_rekening'] }}</td>
                        <td>{{ $row['no_bukti'] }}</td>
                        <td>{{ $row['uraian'] }}</td>
                        <td class="text-end">
                            @if($row['penerimaan'] > 0)
                            {{ number_format($row['penerimaan'], 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-end">
                            @if($row['pengeluaran'] > 0)
                            {{ number_format($row['pengeluaran'], 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($currentSaldo, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    <!-- Baris Jumlah Penutupan -->
                    @php
                    $finalPenerimaan = $runningPenerimaan;
                    $finalPengeluaran = $runningPengeluaran;
                    $finalSaldo = $currentSaldo;
                    @endphp

                    <tr class="table-secondary fw-bold">
                        <td colspan="4" class="text-center">Jumlah Penutupan</td>
                        <td class="text-end">{{ number_format($finalPenerimaan, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($finalPengeluaran, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($finalSaldo, 0, ',', '.') }}</td>
                    </tr>
        </tbody>
    </table>
</div>