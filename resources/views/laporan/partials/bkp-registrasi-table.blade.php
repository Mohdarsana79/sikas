<div class="table-responsive">
    <table class="table table-bordered table-sm" style="font-size: 9pt;">
        <thead>
            <tr>
                <th colspan="4" class="text-center bg-light">Formulir BOS-K7B</th>
            </tr>
            <tr>
                <th colspan="4" class="text-center bg-light">REGISTER PENUTUPAN KAS</th>
            </tr>
        </thead>
        <tbody>
            <!-- Informasi Header -->
            <tr>
                <td colspan="2">Tanggal Penutupan Kas :</td>
                <td colspan="2"><strong>{{ $tanggalPenutupan }}</strong></td>
            </tr>
            <tr>
                <td colspan="2">Nama Penutup Kas (Pemegang Kas) :</td>
                <td colspan="2"><strong>{{ $namaBendahara }}</strong></td>
            </tr>
            <tr>
                <td colspan="2">Tanggal Penutupan Kas Yang Lalu :</td>
                <td colspan="2"><strong>{{ $tanggalPenutupanLalu }}</strong></td>
            </tr>
            <tr>
                <td colspan="2">Jumlah Total Penerimaan (D) :</td>
                <td colspan="2"><strong>Rp. {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="2">Jumlah Total Pengeluaran (K) :</td>
                <td colspan="2"><strong>Rp. {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="2">Saldo Buku (A = D - K) :</td>
                <td colspan="2"><strong>Rp. {{ number_format($saldoBuku, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="2">Saldo Kas (B) :</td>
                <td colspan="2"><strong>Rp. {{ number_format($saldoKas, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Spacer -->
            <tr>
                <td colspan="4" class="bg-light"><strong>Saldo Kas B terdiri dari :</strong></td>
            </tr>

            <!-- Uang Kertas -->
            <tr>
                <td colspan="4"><strong>1. Lembaran uang kertas</strong></td>
            </tr>
            @foreach($uangKertas as $uang)
            <tr>
                <td width="5%"></td>
                <td>Lembaran uang kertas Rp. {{ number_format($uang['denominasi'], 0, ',', '.') }}</td>
                <td>{{ $uang['lembar'] }} Lembar</td>
                <td class="text-end">Rp. {{ number_format($uang['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-end"><strong>Sub Jumlah (1)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($totalUangKertas, 0, ',', '.') }}</strong></td>
            </tr>
            
            <!-- Uang Logam -->
            <tr>
                <td colspan="4"><strong>2. Keping uang logam</strong></td>
            </tr>
            @foreach($uangLogam as $logam)
            <tr>
                <td width="5%"></td>
                <td>Keping uang logam Rp. {{ number_format($logam['denominasi'], 0, ',', '.') }}</td>
                <td>{{ $logam['keping'] }} Keping</td>
                <td class="text-end">Rp. {{ number_format($logam['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-end"><strong>Sub Jumlah (2)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($totalUangLogam, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Saldo Bank -->
            <tr>
                <td colspan="4"><strong>3. Saldo Bank, Surat Berharga, dll</strong></td>
            </tr>
            <tr>
                <td colspan="3" class="text-end"><strong>Sub Jumlah (3)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($saldoBank, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Total -->
            <tr>
                <td colspan="3" class="text-end"><strong>Jumlah (1 + 2 + 3)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($saldoAkhirBuku, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Perbedaan -->
            <tr>
                <td colspan="3" class="text-end"><strong>Perbedaan (A-B)</strong></td>
                <td class="text-end"><strong>Rp. {{ number_format($perbedaan, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Penjelasan Perbedaan -->
            <tr>
                <td colspan="4">
                    <strong>Penjelasan Perbedaan :</strong><br>
                    <em>{{ $penjelasanPerbedaan }}</em>
                </td>
            </tr>

            <!-- Tanda Tangan -->
            <tr>
                <td colspan="2" class="text-center">
                    <br><br>
                    <strong>Tanggal, {{ $tanggalPenutupan }}</strong><br>
                    <strong>Yang diperiksa,</strong><br>
                    <br><br><br>
                    <strong>{{ $namaBendahara }}</strong><br>
                    NIP. {{ $nipBendahara }}
                </td>
                <td colspan="2" class="text-center">
                    <br><br>
                    <strong>&nbsp;</strong><br>
                    <strong>Yang Memeriksa,</strong><br>
                    <br><br><br>
                    <strong>{{ $namaKepalaSekolah }}</strong><br>
                    NIP. {{ $nipKepalaSekolah }}
                </td>
            </tr>
        </tbody>
    </table>
</div>