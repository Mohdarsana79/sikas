<!-- Modal tutup BKU-->
<div class="modal fade" id="tutupBku" tabindex="-1" aria-labelledby="tutupBkuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header flex-column align-items-start bg-primary text-white rounded-top-4 p-4">
                <h5 class="modal-title fw-bold" id="tutupBkuModalLabel">Isi Bunga dan Pajak Bunga Bank</h5>
                <p class="small mb-0 opacity-75">Isi nominal bunga dan pajak bunga bank yang anda terima bulan ini jika ada.</p>
            </div>
            <div class="modal-body p-4">
                <form id="formTutupBku">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label for="bunga_bank" class="form-label fw-semibold">Bunga Bank / Jasa Giro</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-currency-dollar"></i>
                                </span>
                                <input type="text" class="form-control" id="bunga_bank" name="bunga_bank" placeholder="Rp. 0"
                                    aria-describedby="jasaGiroHelp" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="pajak_bunga_bank" class="form-label fw-semibold">Pajak Bunga Bank / Administrasi</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-currency-dollar"></i>
                                </span>
                                <input type="text" class="form-control" id="pajak_bunga_bank" name="pajak_bunga_bank" placeholder="Rp. 0"
                                    aria-describedby="pajakBungaHelp">
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
            </div>
            </form>
        </div>
    </div>
</div>