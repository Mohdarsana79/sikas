<!-- Modal Edit RKAS Perubahan -->
<div class="modal fade" id="editRkasModal" tabindex="-1" aria-labelledby="editRkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editRkasModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data RKAS Perubahan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="editRkasForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit-kegiatan" class="form-label">Kegiatan <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2-kegiatan-edit" id="edit-kegiatan" name="kode_id" required>
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

                    <div class="form-group mb-3">
                        <label for="edit-rekening-belanja" class="form-label">Rekening Belanja <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2-rekening-edit" id="edit-rekening-belanja"
                            name="kode_rekening_id" required>
                            <option value="">-- Pilih Rekening Belanja --</option>
                            @foreach ($rekeningBelanjas as $rekening)
                            <option value="{{ $rekening->id }}" data-kode-rekening="{{ $rekening->kode_rekening }}"
                                data-rincian-objek="{{ $rekening->rincian_objek }}">
                                {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit-uraian" class="form-label">Uraian <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit-uraian" name="uraian" rows="2"
                                    placeholder="Jelaskan detail barang atau jasa yang akan diadakan..."
                                    required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-bulan" class="form-label">Bulan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit-bulan" name="bulan" required>
                                    <option value="">Pilih Bulan</option>
                                    @php
                                    $allowedMonths = ['Juli', 'Agustus', 'September', 'Oktober', 'November',
                                    'Desember'];
                                    @endphp
                                    @foreach($allowedMonths as $month)
                                    <option value="{{ $month }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="edit-jumlah" class="form-label">Jumlah <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit-jumlah" name="jumlah" placeholder="0"
                                    min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit-satuan" class="form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-satuan" name="satuan"
                                    placeholder="pcs, unit, buah" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit-harga-satuan" class="form-label">Harga Satuan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="edit-harga-satuan" name="harga_satuan"
                                        placeholder="0" min="0" step="100" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Total:</strong></span>
                                <span id="edit-total-display" class="fw-bold fs-5">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="bi bi-check-circle me-2"></i>Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(90deg, #ffc107, #ffb300, #ff8f00);
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
        font-size: 10pt;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #4b5563;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease-in-out;
    }

    .form-control:focus {
        outline: none;
        border-color: #ffc107;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.25);
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    /* Select2 Custom Styles */
    .select2-container {
        width: 100% !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-selection__rendered {
        line-height: calc(2.25rem) !important;
        padding-left: 12px !important;
        color: #495057 !important;
    }

    .select2-selection__arrow {
        height: calc(2.25rem) !important;
        right: 10px !important;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #ffc107;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Select2 untuk modal edit
        $('.select2-kegiatan-edit').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: function(option) {
                if (!option.id) return option.text;

                const element = option.element;
                const kode = $(element).data('kode') || '';
                const program = $(element).data('program') || '';
                const subProgram = $(element).data('sub-program') || '';
                const uraian = $(element).data('uraian') || '';

                if (!kode && !program && !subProgram && !uraian) return option.text;

                return $(`
                    <table class="select2-table">
                        <thead>
                            <tr>
                                <th class="select2-table-kode">Kode</th>
                                <th class="select2-table-program">Program</th>
                                <th class="select2-table-sub-program">Sub Program</th>
                                <th class="select2-table-uraian">Uraian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="select2-table-kode">${kode}</td>
                                <td class="select2-table-program">${program}</td>
                                <td class="select2-table-sub-program">${subProgram}</td>
                                <td class="select2-table-uraian">${uraian}</td>
                            </tr>
                        </tbody>
                    </table>
                `);
            },
            templateSelection: function(option) {
                if (!option.id) return option.text;
                const element = option.element;
                const kode = $(element).data('kode') || '';
                const uraian = $(element).data('uraian') || '';
                return kode + ' - ' + uraian;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening-edit').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: function(option) {
                if (!option.id) return option.text;

                const element = option.element;
                const kodeRekening = $(element).data('kode-rekening') || '';
                const rincianObjek = $(element).data('rincian-objek') || '';

                if (!kodeRekening && !rincianObjek) return option.text;

                return $(`
                    <table class="select2-table">
                        <thead>
                            <tr>
                                <th class="select2-table-rekening">Kode Rekening</th>
                                <th class="select2-table-rincian">Rekening Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="select2-table-rekening">${kodeRekening}</td>
                                <td class="select2-table-rincian">${rincianObjek}</td>
                            </tr>
                        </tbody>
                    </table>
                `);
            },
            templateSelection: function(option) {
                if (!option.id) return option.text;
                const element = option.element;
                const kodeRekening = $(element).data('kode-rekening') || '';
                const rincianObjek = $(element).data('rincian-objek') || '';
                return kodeRekening + ' - ' + rincianObjek;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        // Event listener untuk menghitung total
        document.getElementById('edit-jumlah')?.addEventListener('input', updateEditTotal);
        document.getElementById('edit-harga-satuan')?.addEventListener('input', updateEditTotal);

        function updateEditTotal() {
            const jumlah = parseFloat(document.getElementById('edit-jumlah')?.value) || 0;
            const hargaSatuan = parseFloat(document.getElementById('edit-harga-satuan')?.value) || 0;
            const total = jumlah * hargaSatuan;

            const totalDisplay = document.getElementById('edit-total-display');
            if (totalDisplay) {
                totalDisplay.textContent = 'Rp ' + formatNumber(total);
            }
        }

        function formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Reset modal saat ditutup
        document.getElementById('editRkasModal')?.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('editRkasForm');
            if (form) form.reset();
            
            $('.select2-kegiatan-edit').val(null).trigger('change');
            $('.select2-rekening-edit').val(null).trigger('change');
            
            document.getElementById('edit-total-display').textContent = 'Rp 0';
            
            document.querySelectorAll('.form-control').forEach(function(field) {
                field.classList.remove('is-invalid');
            });
        });
    });
</script>