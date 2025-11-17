{{-- Batasi Format Tanggal --}}
@php
$tahun = $tahun ?? date('Y');
$bulan = $bulan ?? 'Januari';
$bulanList = [
'Januari' => 1,
'Februari' => 2,
'Maret' => 3,
'April' => 4,
'Mei' => 5,
'Juni' => 6,
'Juli' => 7,
'Agustus' => 8,
'September' => 9,
'Oktober' => 10,
'November' => 11,
'Desember' => 12,
];
$bulanAngka = $bulanList[$bulan] ?? 1;
$lastDay = cal_days_in_month(CAL_GREGORIAN, $bulanAngka, $tahun);
@endphp
<!-- Modal Lapor Pajak -->
<div class="modal fade" id="laporPajakModal" tabindex="-1" aria-labelledby="laporPajakModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header flex-column align-items-start bg-warning text-dark rounded-top-4 p-4">
                <h5 class="modal-title fw-bold text-white" id="laporPajakModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Lapor Pajak
                </h5>
                <p class="small mb-0 opacity-75 text-white">Isi sesuai dengan detail yang tertera di slip pajak</p>
            </div>
            <div class="modal-body p-4">
                <form id="formLaporPajak">
                    @csrf
                    <input type="hidden" id="bku_id" name="bku_id">

                    <div class="card bg-info bg-opacity-10 mb-4 p-3">
                        <div class="d-inline">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Transaksi ini memiliki kewajiban pelaporan pajak. Silakan isi NTPN dan
                                tanggal lapor
                                sesuai dengan bukti pembayaran pajak.</small>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="tanggal_lapor" class="form-label fw-semibold">Tanggal Lapor Pajak *</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </span>
                                <input type="date" class="form-control" name="tanggal_lapor" id="tanggal_lapor"
                                   aria-label="Sizing example input"
                                        aria-describedby="dateHelp1"
                                        min="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-01"
                                        max="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($lastDay, 2, '0', STR_PAD_LEFT) }}"
                                        required>
                            </div>
                            <div class="form-text text-danger" id="tanggal_lapor_error"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="kode_masa_pajak" class="form-label fw-semibold">Kode Masa Pajak *</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-code"></i>
                                </span>
                                <input type="text" class="form-control" name="kode_masa_pajak" id="kode_masa_pajak" placeholder="isi kode masa pajak">
                            </div>
                            <div class="form-text text-danger" id="kode_masa_pajak_error"></div>
                            <div class="form-text">contoh : 411121-100</div>
                        </div>

                        <div class="col-md-4">
                            <label for="ntpn" class="form-label fw-semibold">NTPN (16 Digit) *</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-receipt"></i>
                                </span>
                                <input type="text" class="form-control" name="ntpn" id="ntpn"
                                    placeholder="Masukan NTPN 16 Digit" maxlength="16" minlength="16" required>
                            </div>
                            <div class="form-text text-danger" id="ntpn_error"></div>
                            <div class="form-text">NTPN harus terdiri dari 16 digit angka</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSimpanLapor">Simpan</button>
            </div>
        </div>
    </div>
</div>