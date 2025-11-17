<!-- Modal Sisipkan RKAS -->
<div class="modal fade" id="sisipkanRkasModal" tabindex="-1" aria-labelledby="sisipkanRkasModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="sisipkanRkasModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Sisipkan Data RKAS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="sisipkanRkasForm" method="POST" action="{{ route('rkas.sisipkan') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                    <input type="hidden" id="sisipkan_kode_id" name="kode_id">
                    <input type="hidden" id="sisipkan_kode_rekening_id" name="kode_rekening_id">
                    <input type="hidden" id="sisipkan_bulan" name="bulan">

                    <!-- Program Kegiatan yang Disisipkan -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="mb-3">Data Kegiatan yang Disisipkan</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Program Kegiatan</strong></label>
                                    <input type="text" class="form-control bg-white" id="sisipkan_program" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Kegiatan</strong></label>
                                    <input type="text" class="form-control bg-white" id="sisipkan_kegiatan" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Rekening Belanja</strong></label>
                                    <input type="text" class="form-control bg-white"
                                        id="sisipkan_rekening_belanja_display" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Bulan Tujuan</strong></label>
                                    <input type="text" class="form-control bg-white" id="sisipkan_bulan_display"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Uraian -->
                    <div class="mb-3">
                        <label for="sisipkan_uraian" class="form-label">Uraian <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="sisipkan_uraian" name="uraian" rows="3"
                            placeholder="Masukkan uraian kegiatan" required></textarea>
                    </div>

                    <!-- Jumlah dan Satuan -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sisipkan_jumlah" class="form-label">Jumlah <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="sisipkan_jumlah" name="jumlah" placeholder="0"
                                min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sisipkan_satuan" class="form-label">Satuan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sisipkan_satuan" name="satuan"
                                placeholder="pcs, unit, buah" required>
                        </div>
                    </div>

                    <!-- Harga Satuan -->
                    <div class="mb-3">
                        <label for="sisipkan_harga_satuan" class="form-label">Harga Satuan <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="sisipkan_harga_satuan" name="harga_satuan"
                                placeholder="0" step="100" min="0" required>
                        </div>
                    </div>

                    <!-- Total Anggaran -->
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total Anggaran:</strong>
                            <span id="sisipkan_total_display" class="fw-bold">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="sisipkanSubmitBtn">
                        <i class="bi bi-check-circle me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>