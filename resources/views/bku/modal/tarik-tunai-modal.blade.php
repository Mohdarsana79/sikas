<!-- Modal Tarik Tunai-->
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
{{-- Format tanggal di batasi selesai --}}
<div class="modal fade" id="tarikTunai" tabindex="-1" aria-labelledby="tarikTunaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header flex-column align-items-start bg-primary text-white rounded-top-4 p-4">
                <h5 class="modal-title fw-bold" id="tarikTunaiModalLabel">Penarikan Tunai</h5>
                <p class="small mb-0 opacity-75">Isi sesuai dengan detail yang tertera di slip dari bank</p>
            </div>
            <div class="modal-body p-4">
                <form id="formTarikTunai">
                    @csrf
                    <input type="hidden" name="penganggaran_id" value="{{ $penganggaran->id }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="tanggal_penarikan" class="form-label fw-semibold">Tanggal Tarik Tunai</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </span>
                                <input type="date" class="form-control" name="tanggal_penarikan" id="tanggal_penarikan"
                                    aria-label="Sizing example input" aria-describedby="dateHelp1"
                                    min="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-01"
                                    max="{{ $tahun }}-{{ str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($lastDay, 2, '0', STR_PAD_LEFT) }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="jumlah_penarikan" class="form-label fw-semibold mb-0">Jumlah
                                    Penarikan</label>
                                <small class="text-primary fw-bold">Saldo: Rp {{ number_format($saldoNonTunai, 0, ',',
                                    '.') }}</small>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control" name="jumlah_penarikan" id="jumlah_penarikan"
                                    placeholder="0" required data-max="{{ $saldoNonTunai }}">
                            </div>
                            <small class="text-muted">Maksimal: Rp {{ number_format($saldoNonTunai, 0, ',', '.')
                                }}</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSimpanTarik">Simpan</button>
            </div>
        </div>
    </div>
</div>