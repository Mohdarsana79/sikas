<div class="card mt-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">BUKU KAS PEMBANTU BANK - {{ strtoupper($bulan) }} {{ $tahun }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0"
                style="font-size: 9pt; border-collapse: collapse;">
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
                    $saldo = $saldoAwal;
                    $totalPenerimaan = $saldoAwal; // Saldo awal termasuk dalam penerimaan
                    $totalPengeluaran = 0;
                    $totalPenarikan = 0;
                    $totalPenerimaanDana = 0;
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
                            Saldo Awal Bank {{ $tahun }}
                            @else
                            Saldo Bank Bulan {{ \App\Models\BukuKasUmum::convertNumberToBulan($bulanAngka-1) }} {{
                            $tahun }}
                            @endif
                        </td>
                        <td class="text-end">@if($saldoAwal > 0) {{ number_format($saldoAwal, 0, ',', '.') }} @else 0 @endif
                            
                        </td>
                        <td class="text-end">0</td>
                        <td class="text-end">@if($saldo > 0) {{ number_format($saldo, 0, ',', '.') }} @else 0 @endif
                        </td>
                    </tr>

                    <!-- Baris Penerimaan Dana -->
                    @foreach($penerimaanDanas as $penerimaan)
                    @php
                    $totalPenerimaanDana += $penerimaan->jumlah_dana;
                    $totalPenerimaan += $penerimaan->jumlah_dana;
                    $saldo += $penerimaan->jumlah_dana;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d-m-Y') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Terima Dana {{ $penerimaan->sumber_dana }}</td>
                        <td class="text-end">{{ number_format($penerimaan->jumlah_dana, 0, ',', '.') }}</td>
                        <td class="text-end">0</td>
                        <td class="text-end">{{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    <!-- Baris Penarikan Tunai -->
                    @foreach($penarikanTunais as $penarikan)
                    @php
                    $totalPenarikan += $penarikan->jumlah_penarikan;
                    $totalPengeluaran += $penarikan->jumlah_penarikan;
                    $saldo -= $penarikan->jumlah_penarikan;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Tarik Tunai</td>
                        <td class="text-end">0</td>
                        <td class="text-end">{{ number_format($penarikan->jumlah_penarikan, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    <!-- Baris Bunga Bank -->
                    @if($bungaRecord && $bungaRecord->bunga_bank > 0)
                    @php
                    $totalPenerimaan += $bungaRecord->bunga_bank;
                    $saldo += $bungaRecord->bunga_bank;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Bunga Bank</td>
                        <td class="text-end">{{ number_format($bungaRecord->bunga_bank, 0, ',', '.') }}</td>
                        <td class="text-end">0</td>
                        <td class="text-end">{{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    <!-- Baris Pajak Bunga -->
                    @if($bungaRecord && $bungaRecord->pajak_bunga_bank > 0)
                    @php
                    $totalPengeluaran += $bungaRecord->pajak_bunga_bank;
                    $saldo -= $bungaRecord->pajak_bunga_bank;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Pajak Bunga</td>
                        <td class="text-end">0</td>
                        <td class="text-end">{{ number_format($bungaRecord->pajak_bunga_bank, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    <!-- Baris Jumlah -->
                    <tr class="table-active fw-bold">
                        <td colspan="5" class="text-center">Jumlah</td>
                        <td class="text-end">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>