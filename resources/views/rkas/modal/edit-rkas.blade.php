<!-- Modal Edit RKAS -->
<div class="modal fade" id="editRkasModal" tabindex="-1" aria-labelledby="editRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editRkasModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Data RKAS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRkasForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-kegiatan" class="form-label">Kegiatan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select select2-kegiatan-edit" id="edit-kegiatan" name="kode_id"
                                    required>
                                    <option value="">-- Pilih Kegiatan --</option>
                                    @foreach ($kodeKegiatans as $kode)
                                    <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                        data-program="{{ $kode->program }}" data-sub-program="{{ $kode->sub_program }}"
                                        data-uraian="{{ $kode->uraian }}">
                                        {{ $kode->kode }} - {{ $kode->uraian }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-rekening-belanja" class="form-label">Rekening Belanja <span
                                        class="text-danger">*</span></label>
                                <select class="form-select select2-rekening-edit" id="edit-rekening-belanja"
                                    name="kode_rekening_id" required>
                                    <option value="">-- Pilih Rekening Belanja --</option>
                                    @foreach ($rekeningBelanjas as $rekening)
                                    <option value="{{ $rekening->id }}"
                                        data-kode-rekening="{{ $rekening->kode_rekening }}"
                                        data-rincian-objek="{{ $rekening->rincian_objek }}">
                                        {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-uraian" class="form-label">Uraian <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit-uraian" name="uraian" rows="4"
                                    required></textarea>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-bulan" class="form-label">Bulan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit-bulan" name="bulan" required>
                                    <option value="">-- Pilih Bulan --</option>
                                    @foreach ($months as $month)
                                    <option value="{{ $month }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit-harga-satuan" class="form-label">Harga Satuan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="edit-harga-satuan" name="harga_satuan"
                                        placeholder="0" step="0.01" min="0" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-jumlah" class="form-label">Jumlah <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit-jumlah" name="jumlah" placeholder="0"
                                    min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-satuan" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-satuan" name="satuan"
                                    placeholder="pcs, unit, buah" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>Total Anggaran:</strong> <span id="edit-total-display">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>