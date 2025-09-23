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
                        <div class="col-md-6">
                            <label for="tanggal_lapor" class="form-label fw-semibold">Tanggal Lapor Pajak *</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </span>
                                <input type="date" class="form-control" name="tanggal_lapor" id="tanggal_lapor"
                                    placeholder="Pilih tanggal" required>
                            </div>
                            <div class="form-text text-danger" id="tanggal_lapor_error"></div>
                        </div>

                        <div class="col-md-6">
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