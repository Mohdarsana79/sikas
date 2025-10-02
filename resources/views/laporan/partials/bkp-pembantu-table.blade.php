<div class="card mt-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">BUKU KAS PEMBANTU TUNAI - {{ strtoupper($bulan) }} {{ $tahun }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-primary table-bordered table-sm mb-0"
                style="font-size: 10pt; border-collapse: collapse;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%">TANGGAL</th>
                        <th style="width: 12%">KODE KEGIATAN</th>
                        <th style="width: 12%">KODE REKENING</th>
                        <th style="width: 12%">NO. BUKTI</th>
                        <th style="width: 24%">URAIAN</th>
                        <th style="width: 10%">PENERIMAAN</th>
                        <th style="width: 10%">PENGELUARAN</th>
                        <th style="width: 10%">SALDO</th>
                    </tr>
                    <tr>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $saldo = $saldoAwalTunai ?? 0;
                    $totalPenerimaan = $saldoAwalTunai ?? 0;
                    $totalPengeluaran = 0;
                    $hasData = false; // Flag untuk mengecek apakah ada data
                    @endphp

                    <!-- Baris Saldo Awal -->
                    <tr>
                        <td>
                            @php
                            $bulanAngkaFormatted = str_pad($bulanAngka, 2, '0', STR_PAD_LEFT);
                            @endphp
                            01-{{ $bulanAngkaFormatted }}-{{ $tahun }}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            @if($bulanAngka == 1)
                            Saldo Awal Tunai {{ $tahun }}
                            @else
                            Saldo Tunai Bulan {{ \App\Models\BukuKasUmum::convertNumberToBulan($bulanAngka-1) }} {{
                            $tahun }}
                            @endif
                        </td>
                        <td class="text-end">Rp {{ number_format($saldoAwalTunai ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end">0</td>
                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Baris Penarikan Tunai -->
                    @forelse($penarikanTunais as $penarikan)
                    @php
                    $hasData = true;
                    $totalPenerimaan += $penarikan->jumlah_penarikan;
                    $saldo += $penarikan->jumlah_penarikan;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Tarik Tunai</td>
                        <td class="text-end">Rp {{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                        <td class="text-end">0</td>
                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <!-- Tidak ada penarikan tunai -->
                    @endforelse

                    <!-- Baris Transaksi BKU Tunai -->
                    @forelse($bkuDataTunai as $transaksi)
                    @php
                    $hasData = true;
                    $totalPengeluaran += $transaksi->total_transaksi_kotor;
                    $saldo -= $transaksi->total_transaksi_kotor;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</td>
                        <td>{{ $transaksi->kodeKegiatan->kode ?? '' }}</td>
                        <td>{{ $transaksi->rekeningBelanja->kode_rekening ?? '' }}</td>
                        <td>{{ $transaksi->id_transaksi }}</td>
                        @if ($transaksi->uraian_opsional)
                        <td>{{ $transaksi->uraian_opsional }}</td>
                        @else
                        <td>{{ $transaksi->uraian }}</td>
                        @endif
                        <td class="text-end">0</td>
                        <td class="text-end">Rp {{ number_format($transaksi->total_transaksi_kotor, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <!-- Tidak ada transaksi BKU tunai -->
                    @endforelse

                    <!-- Baris Setor Tunai -->
                    @forelse($setorTunais as $setor)
                    @php
                    $hasData = true;
                    $totalPengeluaran += $setor->jumlah_setor;
                    $saldo -= $setor->jumlah_setor;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($setor->tanggal_setor)->format('d-m-Y') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Setor Tunai</td>
                        <td class="text-end">0</td>
                        <td class="text-end">Rp {{ number_format($setor->jumlah_setor, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <!-- Tidak ada setor tunai -->
                    @endforelse

                    <!-- Baris jika tidak ada data transaksi -->
                    @if(!$hasData && $penarikanTunais->isEmpty() && $bkuDataTunai->isEmpty() && $setorTunais->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            <i class="bi bi-inbox me-2"></i>Tidak ada transaksi tunai pada bulan {{ $bulan }} {{ $tahun
                            }}
                        </td>
                    </tr>
                    @endif

                    <!-- Baris Jumlah -->
                    @if($hasData || $saldoAwalTunai > 0)
                    <tr class="table-active fw-bold">
                        <td colspan="5" class="text-center">Jumlah</td>
                        <td class="text-end">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>