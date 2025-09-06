<!-- Modal Tarik Tunai-->
<div class="modal fade" id="setorTunai" tabindex="-1" aria-labelledby="setorTunaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header flex-column align-items-start bg-primary text-white rounded-top-4 p-4">
                <h5 class="modal-title fw-bold" id="setorTunaiModalLabel">Penarikan Tunai</h5>
                <p class="small mb-0 opacity-75">Isi sesuai dengan detail yang tertera di slip dari bank</p>
            </div>
            <div class="modal-body p-4">
                <form>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="tanggalTarik1" class="form-label fw-semibold">Tanggal Setor Tunai</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </span>
                                <input type="date" class="form-control" id="tanggalTarik1" placeholder="Pilih tanggal"
                                    aria-describedby="tanggalTarik1Help" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="tanggalTarik2" class="form-label fw-semibold mb-0">Jumlah Setor</label>
                                <small class="text-primary fw-bold">Saldo: Rp. 500.000</small>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" class="form-control" id="tanggalTarik2" placeholder="0" min="0"
                                    max="500000" aria-describedby="tanggalTarik2Help" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
            </div>
        </div>
    </div>
</div>