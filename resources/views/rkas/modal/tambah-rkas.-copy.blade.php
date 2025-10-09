<!-- Modal Tambah RKAS -->
<div class="modal fade" id="tambahRkasModal" tabindex="-1" aria-labelledby="tambahRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tambahRkasModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Data RKAS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="tambahRkasForm" method="POST" action="{{ route('rkas.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                    <!-- Progress Steps -->
                    <div class="progress-steps mb-4">
                        <div class="step-indicator">
                            <div class="step active" id="step-1">
                                <span class="step-number">1</span>
                                <span class="step-title">Kegiatan</span>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-2">
                                <span class="step-number">2</span>
                                <span class="step-title">Detail</span>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-3">
                                <span class="step-number">3</span>
                                <span class="step-title">Anggaran</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Kegiatan Selection -->
                    <div class="form-step" id="form-step-1">
                        <h6 class="mb-3"><i class="bi bi-bookmark me-2"></i>Pilih Kegiatan dan Rekening</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kegiatan" class="form-label">Kegiatan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2-kegiatan" id="kegiatan" name="kode_id" required>
                                        <option value="">-- Pilih Kegiatan --</option>
                                        @foreach ($kodeKegiatans as $kode)
                                        <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                            data-program="{{ $kode->program }}"
                                            data-sub-program="{{ $kode->sub_program }}"
                                            data-uraian="{{ $kode->uraian }}" {{ old('kode_id')==$kode->id ? 'selected'
                                            : '' }}>
                                            {{ $kode->kode }} - {{ $kode->uraian }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rekening_belanja" class="form-label">Rekening Belanja <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2-rekening" id="rekening_belanja"
                                        name="kode_rekening_id" required>
                                        <option value="">-- Pilih Rekening Belanja --</option>
                                        @foreach ($rekeningBelanjas as $rekening)
                                        <option value="{{ $rekening->id }}"
                                            data-kode-rekening="{{ $rekening->kode_rekening }}"
                                            data-rincian-objek="{{ $rekening->rincian_objek }}" {{
                                            old('kode_rekening_id')==$rekening->id ? 'selected' : '' }}>
                                            {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Detail Information -->
                    <div class="form-step" id="form-step-2" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>Detail Uraian dan Harga</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="uraian" class="form-label">Uraian <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="uraian" name="uraian" rows="4"
                                        placeholder="Jelaskan detail barang atau jasa yang akan diadakan..."
                                        required>{{ old('uraian') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga_satuan" class="form-label">Harga Satuan <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="harga_satuan" name="harga_satuan"
                                            placeholder="0" step="0.01" min="0" value="{{ old('harga_satuan') }}"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Budget Planning -->
                    <div class="form-step" id="form-step-3" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Perencanaan Anggaran Bulanan
                            </h6>
                            <span class="badge bg-success fs-6" id="totalAnggaranDisplay">Total: Rp 0</span>
                        </div>

                        <!-- Dynamic Month Entries -->
                        <div id="bulanContainer">
                            <div class="month-entry border rounded p-3 mb-3" data-index="0">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Bulan <span class="text-danger">*</span></label>
                                        <select class="form-select month-select" name="bulan[]" required>
                                            <option value="">Pilih Bulan</option>
                                            @foreach ($months as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control jumlah-input" name="jumlah[]"
                                            placeholder="0" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control satuan-input" name="satuan[]"
                                            placeholder="pcs, unit, buah" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Total</label>
                                        <div class="month-total badge bg-info w-100">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Month Button -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="addMonthBtn">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="prevStepBtn" style="display: none;">
                        <i class="bi bi-arrow-left me-2"></i>Sebelumnya
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="nextStepBtn">
                        Selanjutnya<i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="bi bi-check-circle me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>