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

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any()))
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form id="sisipkanRkasForm" method="POST" action="{{ route('rkas-perubahan.sisipkan') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                    <input type="hidden" id="sisipkan_kode_id" name="kode_id">
                    <input type="hidden" id="sisipkan_kode_rekening_id" name="kode_rekening_id">
                    <input type="hidden" id="sisipkan_bulan" name="bulan">

                    <!-- Program Kegiatan yang Disisipkan -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Program Kegiatan</label>
                                    <input type="text" class="form-control" id="sisipkan_program" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kegiatan</label>
                                    <input type="text" class="form-control" id="sisipkan_kegiatan" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Rekening Belanja</label>
                                    <input type="text" class="form-control" id="sisipkan_rekening_belanja_display"
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
                            required>{{ old('uraian') }}</textarea>
                    </div>

                    <!-- Jumlah dan Satuan -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sisipkan_jumlah" class="form-label">Jumlah <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="sisipkan_jumlah" name="jumlah" placeholder="0"
                                min="1" value="{{ old('jumlah') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sisipkan_satuan" class="form-label">Satuan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sisipkan_satuan" name="satuan"
                                placeholder="pcs, unit, buah" value="{{ old('satuan') }}" required>
                        </div>
                    </div>

                    <!-- Harga Satuan -->
                    <div class="mb-3">
                        <label for="sisipkan_harga_satuan" class="form-label">Harga Satuan <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="sisipkan_harga_satuan" name="harga_satuan"
                                placeholder="0" step="0.01" min="0" value="{{ old('harga_satuan') }}" required>
                        </div>
                    </div>

                    <!-- Total Anggaran -->
                    <div class="alert alert-info">
                        <strong>Total Anggaran:</strong> <span id="sisipkan_total_display">Rp 0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>