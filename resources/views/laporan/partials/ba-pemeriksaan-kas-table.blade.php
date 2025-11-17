<div class="card mt-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">BERITA ACARA PEMERIKSAAN KAS - {{ strtoupper($bulan) }} {{ $tahun }}</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm" style="font-size: 9pt;">
                <tbody>
                    <tr>
                        <td colspan="4" class="text-center fw-bold">BERITA ACARA PEMERIKSAAN KAS</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            Pada hari ini, <span class="fw-bold">{{ $namaHariAkhirBulan }}</span> tanggal
                            <span class="fw-bold">{{ $formatTanggalAkhirBulan }}</span>
                            yang bertanda tangan di bawah ini, kami Kepala Sekolah yang ditunjuk berdasarkan<br>
                            Surat Keputusan No. <span class="fw-bold">{{ $sekolah->sk_kepala_sekolah ??
                                '821.24/2063.03/BKD Tanggal 03 September 2013' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="25%">Nama</td>
                        <td width="25%">: {{ $namaKepalaSekolah }}</td>
                        <td width="25%">Jabatan</td>
                        <td width="25%">: Kepala Sekolah {{ $sekolah->nama_sekolah ?? 'SMP MUHAMMADIYAH SONI' }}</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            melakukan pemeriksaan kas kepada :
                        </td>
                    </tr>
                    <tr>
                        <td>Nama</td>
                        <td>: {{ $namaBendahara }}</td>
                        <td>Jabatan</td>
                        <td>: Bendahara BOS</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            Yang berdasarkan Surat Keputusan Nomor :<br>
                            <span class="fw-bold">{{ $penganggaran->sk_bendahara ?? '045.2/001/SMP.MUH/DISDIKBUD/2025
                                Tanggal 04 Januari 2023' }}</span> ditugaskan
                            dengan pengurusan uang Bantuan Operasional Sekolah (BOS).
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            Berdasarkan pemeriksaan kas serta bukti-bukti dalam pengurusan itu, kami menemui
                            kenyataan sebagai berikut :
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            Jumlah uang yang dihitung di hadapan Bendahara / Pemegang Kas adalah :
                        </td>
                    </tr>
                    <tr>
                        <td>a. Uang kertas bank, uang logam</td>
                        <td>: Rp. {{ number_format($totalUangKertasLogam, 0, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>b. Saldo Bank</td>
                        <td>: Rp. {{ number_format($saldoBank, 0, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>c. Surat Berharga dil</td>
                        <td>: Rp. -</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Jumlah</td>
                        <td>: Rp. {{ number_format($totalKas, 0, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Saldo uang menurut Buku Kas Umum</td>
                        <td>: Rp. {{ number_format($saldoBuku, 0, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Perbedaan antara Saldo Kas dan Saldo buku</td>
                        <td>: Rp. {{ number_format($perbedaan, 0, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-center">
                            {{ $sekolah->kecamatan }}, {{ $formatTanggalAkhirBulan }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">
                            <div class="fw-bold">Bendahara / Pemegang Kas</div>
                            <br><br><br>
                            <div class="fw-bold">{{ $namaBendahara }}</div>
                            <div>NIP. {{ $nipBendahara }}</div>
                        </td>
                        <td colspan="2" class="text-center">
                            <div class="fw-bold">Kepala Sekolah</div>
                            <br><br><br>
                            <div class="fw-bold">{{ $namaKepalaSekolah }}</div>
                            <div>NIP. {{ $nipKepalaSekolah }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- DEBUG: Tambahkan section debug untuk melihat data aktual -->
        <div class="mt-3 p-3 bg-light rounded" style="font-size: 8pt;">
            <strong>DEBUG INFO:</strong><br>
            totalUangKertasLogam: {{ number_format($totalUangKertasLogam, 0, ',', '.') }}<br>
            saldoBank: {{ number_format($saldoBank, 0, ',', '.') }}<br>
            totalKas: {{ number_format($totalKas, 0, ',', '.') }}<br>
            saldoBuku: {{ number_format($saldoBuku, 0, ',', '.') }}<br>
            perbedaan: {{ number_format($perbedaan, 0, ',', '.') }}
        </div>
    </div>
</div>