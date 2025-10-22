<!-- Modal Pengaturan Cetak Realisasi -->
<div class="modal fade" id="pengaturanCetakModalRealisasi" tabindex="-1"
    aria-labelledby="pengaturanCetakModalRealisasiLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pengaturanCetakModalRealisasiLabel">Pengaturan Cetak Realisasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ukuran_kertas_realisasi" class="form-label">Ukuran Kertas</label>
                        <select class="form-select" id="ukuran_kertas_realisasi" name="ukuran_kertas" required>
                            <option value="A4">A4</option>
                            <option value="Folio">Folio</option>
                            <option value="Legal">Legal</option>
                            <option value="Letter">Letter</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="orientasi_realisasi" class="form-label">Orientasi</label>
                        <select class="form-select" id="orientasi_realisasi" name="orientasi" required>
                            <option value="portrait">Portrait</option>
                            <option value="landscape" selected>Landscape</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="font_size_realisasi" class="form-label">Ukuran Font</label>
                        <select class="form-select" id="font_size_realisasi" name="font_size" required>
                            <option value="8pt">8pt</option>
                            <option value="9pt" selected>9pt</option>
                            <option value="10pt">10pt</option>
                            <option value="11pt">11pt</option>
                            <option value="12pt">12pt</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnCetakRealisasiPDF">Cetak PDF</button>
            </div>
        </div>
    </div>
</div>