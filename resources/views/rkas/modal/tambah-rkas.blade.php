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
                <input type="hidden" name="tahun_anggaran" value="{{ $penganggaran->tahun_anggaran }}">
                <!-- Hidden field untuk harga satuan numeric -->
                <input type="hidden" name="harga_satuan" id="harga_satuan_actual">

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="kegiatan" class="form-label">Kegiatan <span class="text-danger">*</span></label>
                        <select class="form-select select2-kegiatan" id="kegiatan" name="kode_id" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach ($kodeKegiatans as $kode)
                            <option value="{{ $kode->id }}" data-kode="{{ $kode->kode }}"
                                data-program="{{ $kode->program }}" data-sub-program="{{ $kode->sub_program }}"
                                data-uraian="{{ $kode->uraian }}" {{ old('kode_id')==$kode->id ? 'selected' : '' }}>
                                {{ $kode->kode }} - {{ $kode->uraian }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="rekening_belanja" class="form-label">Rekening Belanja <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2-rekening" id="rekening_belanja" name="kode_rekening_id"
                            required>
                            <option value="">-- Pilih Rekening Belanja --</option>
                            @foreach ($rekeningBelanjas as $rekening)
                            <option value="{{ $rekening->id }}" data-kode-rekening="{{ $rekening->kode_rekening }}"
                                data-rincian-objek="{{ $rekening->rincian_objek }}" {{
                                old('kode_rekening_id')==$rekening->id ? 'selected' : '' }}>
                                {{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="uraian" class="form-label">Uraian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="uraian" name="uraian" rows="1"
                                    placeholder="Jelaskan detail barang atau jasa yang akan diadakan..."
                                    required>{{ old('uraian') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="harga_satuan" class="form-label">Harga Satuan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <!-- Input untuk tampilan format rupiah -->
                                    <input type="text" class="form-control" id="harga_satuan" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="anggaran-box">
                        <div class="anggaran-info">
                            <span class="text-white">Di Anggarkan untuk bulan</span>
                            <span class="text-white">Total Anggaran : <span class="text-white" id="total-anggaran">Rp. 0</span></span>
                        </div>

                        <!-- Container untuk card bulan -->
                        <div class="anggaran-bulan-list-container" id="bulanContainer">
                            <!-- Card bulan akan ditambahkan secara dinamis oleh JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-circle me-2"></i>Simpan Data
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
        background: linear-gradient(90deg, #5e72e4, #8b5cf6, #d946ef);
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
        border-color: #5e72e4;
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.25);
    }

    .anggaran-box {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
        background-color: #f9fafb;
    }

    .anggaran-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .anggaran-info span {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .anggaran-bulan-list-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .anggaran-bulan-card {
        position: relative;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        background-color: #fff;
        flex: 1 1 calc(50% - 0.5rem);
        min-width: 300px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
        margin-bottom: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .anggaran-bulan-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-color: #5e72e4;
    }

    .anggaran-bulan-card.selected {
        border-color: #5e72e4;
        box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.1);
    }

    .delete-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #ef4444;
        color: #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.2s ease;
        border: none;
        font-size: 12px;
        z-index: 10;
    }

    .delete-btn:hover {
        background-color: #dc2626;
    }

    .bulan-input-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .bulan-input-group select {
        flex: 2;
    }

    .month-total {
        flex: 1;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 0.5rem;
        text-align: center;
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        min-width: 100px;
    }

    .jumlah-satuan-group {
        display: flex;
        gap: 0.5rem;
    }

    .jumlah-satuan-group .form-control {
        flex: 1;
    }

    .input-icon-left {
        position: relative;
        flex: 1;
    }

    .input-icon-left svg {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6B7280;
        z-index: 1;
    }

    .input-icon-left .form-control {
        padding-left: 35px;
    }

    .btn-tambah-card {
        flex: 1 1 calc(50% - 0.5rem);
        min-width: 300px;
        background-color: transparent;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        color: #6b7280;
        font-size: 0.875rem;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        text-align: center;
        padding: 2rem 1rem;
        margin-bottom: 1rem;
    }

    .btn-tambah-card:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
        color: #4b5563;
    }

    .btn-tambah {
        background: none;
        border: none;
        color: inherit;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 0.875rem;
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
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
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
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .satuan-input:read-only {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    /* Select2 Custom Table Styles */
    .select2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .select2-table th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 2px solid #dee2e6;
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .select2-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
        word-wrap: break-word;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .select2-table tr:hover {
        background-color: #f8f9fa;
    }

    .select2-results__option--highlighted .select2-table tr {
        background-color: #007bff !important;
        color: white;
    }

    .select2-results__option--highlighted .select2-table th {
        background-color: #0056b3 !important;
        color: white;
    }

    .select2-table-kode {
        width: 80px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #000000;
    }

    .select2-table-program {
        width: 150px;
        font-weight: 500;
    }

    .select2-table-sub-program {
        width: 150px;
    }

    .select2-table-uraian {
        min-width: 200px;
        white-space: normal;
        line-height: 1.4;
    }

    .select2-table-rekening {
        width: 100px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #000000;
    }

    .select2-table-rincian {
        min-width: 250px;
        white-space: normal;
        line-height: 1.4;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .anggaran-bulan-card {
            flex: 1 1 100%;
            min-width: auto;
        }

        .btn-tambah-card {
            flex: 1 1 100%;
            min-width: auto;
        }

        .bulan-input-group {
            flex-direction: column;
        }

        .month-total {
            width: 100%;
            margin-top: 0.5rem;
        }

        .select2-table th,
        .select2-table td {
            padding: 6px 8px;
            font-size: 0.8rem;
        }

        .select2-table-kode {
            width: 60px;
        }

        .select2-table-program,
        .select2-table-sub-program {
            width: 120px;
        }

        .select2-table-uraian,
        .select2-table-rincian {
            min-width: 150px;
        }
    }

    /* Custom select styling */
    select.form-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi variabel
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    let firstSatuanValue = '';
    let submitButtonOriginalText ='';

    // Inisialisasi saat modal dibuka
    const tambahModal = document.getElementById('tambahRkasModal');
    const submitBtn = document.getElementById('submitBtn');

    if (submitBtn) {
        submitButtonOriginalText = submitBtn.innerHTML;
    }

    if (tambahModal) {
        tambahModal.addEventListener('show.bs.modal', function() {
            initializeAnggaranList();
            initializeSelect2();
        });

        // Reset saat modal ditutup
        tambahModal.addEventListener('hidden.bs.modal', function() {
            firstSatuanValue = '';
            resetForm();
            resetSubmitButton();
        });
    }

    // Reset Form
    function resetForm() {
        const form = document.getElementById('tambahRkasForm');
        if (form) {
            form.reset();
        }

        const container = document.getElementById('bulanContainer');
        if (container) {
            container.innerHTML = '';
        }

        const totalAnggaran = document.getElementById('total-anggaran');
        if (totalAnggaran) {
            totalAnggaran.textContent = 'Rp. 0';
        }
        firstSatuanValue = '';

        // Reset Select2
        $('.select2-kegiatan').val(null).trigger('change');
        $('.select2-rekening').val(null).trigger('change');
    }

    // Reset Submit Button
    function resetSubmitButton() {
        if (submitBtn) {
            submitBtn.innerHTML = submitButtonOriginalText;
            submitBtn.disabled = false;
        }
    }

    // Inisialisasi Select2
    function initializeSelect2() {
        $('.select2-kegiatan').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#tambahRkasModal'),
            templateResult: formatKegiatanOption,
            templateSelection: formatKegiatanSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#tambahRkasModal'),
            templateResult: formatRekeningOption,
            templateSelection: formatRekeningSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }

    // fungsi format Rupiah Tanpa Desimal
    function formatRupiah(angka) {
        // Jika sudah beruba string dengan format, konversi dulu ke number
        if (typeof angka === 'string') {
            angka = parseFloat(angka.replace(/[^\d]/g, ''));
        }

        const num = parseInt(angka);
        if (isNaN(num)) return '0';

        // format tanpa desimal
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // fungsi untuk mendapatkan nilai numerik dari format rupiah
    function getNumericValue(rupiah) {
        if (!rupiah) return 0;

        // hapus semua karakter non-digit
        const numericString = rupiah.toString().replace(/[^\d]/g, '');

        // parse ke integer
        return parseInt(numericString) || 0;
    }

    // function get harga satuan
    function getHargaSatuanValue() {
        const hargaSatuanInput = document.getElementById('harga_satuan');

        if (!hargaSatuanInput || !hargaSatuanInput.value) return 0;

        return getNumericValue(hargaSatuanInput.value);
    }

    // fungsi update Total Anggaran
    function updateTotalAnggaran() {
        let total = 0;
        const hargaSatuan = getHargaSatuanValue();

        // reset total dulu
        total = 0;

        document.querySelectorAll('.anggaran-bulan-card').forEach(function(card) {
            const jumlahInput = card.querySelector('.jumlah-input');
            const jumlah = parseInt(jumlahInput?.value) || 0;

            // hitung total perbulan
            const monthTotal = hargaSatuan * jumlah;

            // update display perbulan
            const montTotalEl= card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + formatRupiah(monthTotal);
            }

            // tambahkan ke total keseluruhan
            total += monthTotal;
        });

        // update total display
        const totalDisplay = document.getElementById('total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + formatRupiah(total);
        }
    }

    function createAnggaranCard() {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        card.innerHTML = `
            <button type="button" class="delete-btn" title="Hapus bulan">
                <i class="bi bi-x"></i>
            </button>
            <div class="bulan-input-group">
                <select class="form-control bulan-input" name="bulan[]" required>
                    <option value="">Pilih Bulan</option>
                    ${months.map(b => `<option value="${b}">${b}</option>`).join('')}
                </select>
                <span class="month-total">Rp 0</span>
            </div>
            <div class="jumlah-satuan-group">
                <div class="input-icon-left">
                    <i class="bi bi-hash"></i>
                    <input type="number" class="form-control jumlah-input" name="jumlah[]" placeholder="Jumlah" min="1" required>
                </div>
                <input type="text" class="form-control satuan-input" name="satuan[]" placeholder="Satuan" required>
            </div>
        `;
        
        // Event listener untuk tombol hapus
        const deleteBtn = card.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.anggaran-bulan-card').length > 1) {
                card.remove();
                checkTambahButtonVisibility();
                updateTotalAnggaran();
                
                // Jika card pertama dihapus, reset sync satuan
                const firstCard = document.querySelector('.anggaran-bulan-card');
                if (firstCard) {
                    const firstSatuanInput = firstCard.querySelector('.satuan-input');
                    firstSatuanValue = firstSatuanInput.value;
                    updateSatuanOtomatis(firstSatuanValue);
                }
            } else {
                Swal.fire('Peringatan', 'Minimal harus ada satu bulan', 'warning');
            }
        });

        // Event listener untuk input perubahan
        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        const satuanInput = card.querySelector('.satuan-input');
        
        jumlahInput.addEventListener('input', function() {
            // validasi jumlah
            validateJumlahInput(this);
            // update total setelah valida
            setTimeout(updateTotalAnggaran, 10);
        });
        bulanSelect.addEventListener('change', function() {
            setTimeOut(updateTotalAnggaran, 10);
        });
        
        // Event listener untuk sync satuan
        satuanInput.addEventListener('input', function(e) {
            // Jika ini adalah card pertama
            const isFirstCard = card === document.querySelector('.anggaran-bulan-card');
            if (isFirstCard) {
                firstSatuanValue = e.target.value;
                updateSatuanOtomatis(firstSatuanValue);
            }
        });

        satuanInput.addEventListener('change', function(e) {
            // Jika ini adalah card pertama
            const isFirstCard = card === document.querySelector('.anggaran-bulan-card');
            if (isFirstCard) {
                firstSatuanValue = e.target.value;
                updateSatuanOtomatis(firstSatuanValue);
            }
        });

        // Event listiner untuk klik card (selected state)
        card.addEventListener('click', function() {
            if (!e.target.closest('.delete-btn')) {
                const allCards = document.querySelectorAll('.anggaran-bulan-card');
                allCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            }
        });

        // Jika sudah ada nilai satuan pertama, set ke card baru
        if (firstSatuanValue) {
            satuanInput.value = firstSatuanValue;
            satuanInput.readOnly = true;
        }

        return card;
    }

    function createTambahButtonCard() {
        const btnCard = document.createElement('div');
        btnCard.className = 'btn-tambah-card';
        btnCard.innerHTML = `
            <button class="btn-tambah" id="btn-tambah-bulan">
                <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
            </button>
            `;
            return btnCard;
    }

    // validasi input jumlah
    function validateJumlahInput(input) {
        const value = parseInt(input.value);
        if (isNaN(value) || value < 1) {
            input.classList.add('is-invalid');
            input.value = ''; //reset jika invalid
        } else {
            input.classList.remove('is-invalid');
            input.value = value; //pastikan nilai integer
        }
    }

    function initializeAnggaranList() {
        const container = document.getElementById('bulanContainer');
        if (!container) return;

        // Reset container
        container.innerHTML = '';
        
        // Reset nilai satuan pertama
        firstSatuanValue = '';
        
        // Tambah card pertama
        const initialCard = createAnggaranCard();
        const tambahBtnCard = createTambahButtonCard();

        container.appendChild(initialCard);
        container.appendChild(tambahBtnCard);

        // Event listiner untuk tombol tambah bulan
        document.getElementById('btn-tambah-bulan').addEventListener('click', handleTambahBulanClick);
        
        checkTambahButtonVisibility();
        updateTotalAnggaran();
    }

    function handleTambahBulanClick() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        if (cards.length >= 12) {
            Swal.fire('Peringatan', 'Maksimal 12 bulan dalam setahun', 'warning');
            return;
        }

        const container = document.getElementById('bulanContainer');
        const newCard = createAnggaranCard();
        const tambahBtnCard = document.querySelector('.btn-tambah-card');

        container.insertBefore(newCard, tambahBtnCard);
        
        checkTambahButtonVisibility();

        // beri sedikit delay untuk update
        setTimeOut(updateTotalAnggaran, 50);
    }

    function updateSatuanOtomatis(satuanValue) {
        if (!satuanValue) return;
        
        // Update semua input satuan kecuali yang pertama
        const allCards = document.querySelectorAll('.anggaran-bulan-card');
        allCards.forEach((card, index) => {
            if (index > 0) { // Skip card pertama
                const satuanInput = card.querySelector('.satuan-input');
                if (satuanInput) {
                    satuanInput.value = satuanValue;
                    satuanInput.readOnly = true;
                }
            } else {
                // Card pertama tetap editable
                const satuanInput = card.querySelector('.satuan-input');
                if (satuanInput) {
                    satuanInput.readOnly = false;
                }
            }
        });
    }

    function checkTambahButtonVisibility() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        
        if (tambahBtnCard) {
            if (cards.length >= 12) {
                tambahBtnCard.style.display = 'none';
            } else {
                tambahBtnCard.style.display = 'flex';
            }
        }
    }

    // format rupiah otomatis untuk harga satuan
    const hargaSatuanInput = document.getElementById('harga_satuan');
    if (hargaSatuanInput) {
        let isFormatting = false;

        // format saat input berubah
        hargaSatuanInput.addEventListener('input', function(e) {
            if (isFormatting) return;

            isFormatting = true;

            const input = e.target;
            let value = input.value.replace(/[^\d]/g, '');

            if (value) {
                // format sebagai rupiah
                const formattedValue = formatRupiah(value);
                input.value = formattedValue;
            } else {
                input.value = '';
            }

            isFormatting = false;

            // update total anggaran
            setTimeOut(updateTotalAnggaran, 50);
        });
        
        // format saat kehilangan fokus (blur)
        hargaSatuanInput.addEventListener('blur', function(e) {
            const input = e.target;
            let value = input.value.replace(/[^\d]/g, '');

            if (value) {
                const formattedValue = formatRupiah(value);
                input.value = formattedValue;
            }

            updateTotalAnggaran();
        });

        // validasi input - hanya angka yang di perbolehkan
        hargaSatuanInput.addEventListener('keydown', function(e) {
            // izinkan: backspace, delete, tab, escape, enter, arrow keys
            if ([46, 8, 9, 27, 13, 110, 190].include(e.keyCode) || 
                // izinkan: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) || 
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // izinkan: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }

            // pastikan hanya angka yang dimasukkan
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

    // event listener untuk form submission
    document.getElementById('tambahRkasForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateForm()) {
            return false;
        }

        // set nilai hidden field dengan harga satuan yang sudah di unformat
        const unformattedHargaSatuan = getHargaSatuanValue().toString();
        document.getElementById('harga_satuan_actual').value = unformattedHargaSatuan;

        // tampilkan loading
        if (submitBtn) {
            submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
            submitBtn.disabled = true;
        }

        // submit form secara manual
        this.submit();
    });

    function validateForm() {
        let isValid = true;
        const errorMessages = [];

        // Reset semua error terlebih dahulu
        document.querySelectorAll('is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });

        // validasi field required dasar
        const requiredFields = [
            { element: document.getElementById('kegiatan'), name: 'Kegiatan'},
            { element: document.getElementById('rekening_belanja'), name: 'Rekening Belanja'},
            { element: document.getElementById('uraian'), name: 'Uraian'},
            { element: document.getElementById('harga_satuan'), name: 'Harga Satuan'}
        ];

        requiredFields.forEach(field => {
            if (!field.element || !field.element.value.trim()) {
                isValid = false;
                field.element?.classList.add('is-invalid');
                errorMessages.push(`${field.name} wajib diisi.`);
            }
        });

        // Validasi harga satuan harus lebih dari 0
        const hargaSatuanValue = getHargaSatuanValue();
        if (hargaSatuanValue <= 0) {
            isValid = false;
            document.getElementById('harga_satuan').classList.add('is-invalid');
            errorMessages.push('Harga satuan harus lebih dari 0');
        }

        // Validasi minimal satu bulan terisi
            const bulanCards = document.querySelectorAll('.anggaran-bulan-card');
            if (bulanCards.length === 0) {
                isValid = false;
                errorMessages.push('Minimal harus ada satu bulan yang diisi');
            }

        // Validate month uniqueness dan validasi bulan
            const selectedMonths = [];
            const monthSelects = document.querySelectorAll('.bulan-input');

            monthSelects.forEach(function(select) {
                select.classList.remove('is-invalid');
                const month = select.value;
                
                if (!month) {
                    isValid = false;
                    select.classList.add('is-invalid');
                    errorMessages.push('Semua bulan harus dipilih');
                } else {
                    if (selectedMonths.includes(month)) {
                        isValid = false;
                        select.classList.add('is-invalid');
                        errorMessages.push('Tidak boleh ada bulan yang sama dalam satu kegiatan');
                    }
                    selectedMonths.push(month);
                }
            });

            // Validasi jumlah harus lebih dari 0 untuk setiap bulan
            const jumlahInputs = document.querySelectorAll('.jumlah-input');
            jumlahInputs.forEach(function(input) {
                input.classList.remove('is-invalid');
                const value = parseInt(input.value);
                if (isNaN(value) || value <= 0) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    errorMessages.push('Jumlah harus lebih dari 0 untuk semua bulan');
                }
            });

            // Validasi satuan harus diisi
            const satuanInputs = document.querySelectorAll('.satuan-input');
            satuanInputs.forEach(function(input) {
                input.classList.remove('is-invalid');
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    errorMessages.push('Satuan harus diisi untuk semua bulan');
                }
            });

            if (!isValid) {
                const uniqueMessages = [...new Set(errorMessages)];
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: uniqueMessages.join('<br>'),
                    confirmButtonText: 'Mengerti'
                });
            }

        return isValid;
    }

    // Fungsi untuk Select2 templates
    function formatKegiatanOption(option) {
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
    }

    function formatKegiatanSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kode = $(element).data('kode') || '';
        const uraian = $(element).data('uraian') || '';
        return kode + ' - ' + uraian;
    }

    function formatRekeningOption(option) {
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
    }

    function formatRekeningSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kodeRekening = $(element).data('kode-rekening') || '';
        const rincianObjek = $(element).data('rincian-objek') || '';
        return kodeRekening + ' - ' + rincianObjek;
    }
});
</script>
@endpush