<!-- Modal Setor Tunai-->
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

<div class="modal fade" id="setorTunai" tabindex="-1" aria-labelledby="setorTunaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header flex-column align-items-start bg-primary text-white rounded-top-4 p-4">
                <h5 class="modal-title fw-bold" id="setorTunaiModalLabel">Setor Tunai</h5>
                <p class="small mb-0 opacity-75">Isi sesuai dengan detail setor tunai ke bank</p>
            </div>
            <div class="modal-body p-4">
                <form id="formSetorTunai" novalidate>
                    @csrf
                    <input type="hidden" name="penganggaran_id" value="{{ $penganggaran->id }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="tanggal_setor" class="form-label fw-semibold">Tanggal Setor Tunai</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </span>
                                <input type="date" class="form-control" name="tanggal_setor" id="tanggal_setor"
                                    aria-label="Sizing example input" aria-describedby="dateHelp1"
                                    min="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-01"
                                    max="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($lastDay, 2, '0', STR_PAD_LEFT) }}"
                                    required>
                            </div>
                            <div class="invalid-feedback" id="tanggal_setor_error"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="jumlah_setor" class="form-label fw-semibold mb-0">Jumlah Setor</label>
                                <small class="text-primary fw-bold">Saldo Tunai: Rp {{ number_format($saldoTunai, 0,
                                    ',', '.') }}</small>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control" name="jumlah_setor" id="jumlah_setor"
                                    placeholder="0" required data-max="{{ $saldoTunai }}"
                                    data-saldotunai="{{ $saldoTunai }}">
                            </div>
                            <small class="text-muted">Maksimal: Rp {{ number_format($saldoTunai, 0, ',', '.') }}</small>
                            <div class="invalid-feedback" id="jumlah_setor_error"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSimpanSetor">Simpan</button>
            </div>
        </div>
    </div>
</div>