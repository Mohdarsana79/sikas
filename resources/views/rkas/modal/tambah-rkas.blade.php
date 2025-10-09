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
                                <textarea class="form-control" id="uraian" name="uraian" rows="3"
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
                            <span>Di Anggarkan untuk bulan</span>
                            <span>Total Anggaran : <span id="total-anggaran">Rp. 0</span></span>
                        </div>

                        <!-- Container untuk card bulan -->
                        <div class="anggaran-bulan-list-container" id="bulanContainer">
                            <!-- Card bulan akan ditambahkan secara dinamis oleh JavaScript -->
                        </div>

                        <!-- Tombol Tambah Bulan -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="btn-tambah-bulan">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
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
    }

    .anggaran-bulan-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        border-top: 3px solid #007bff;
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

    .btn-tambah-bulan-container {
        text-align: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #d1d5db;
    }

    .satuan-input:read-only {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi variabel
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    let firstSatuanValue = '';

    // Inisialisasi saat modal dibuka
    const tambahModal = document.getElementById('tambahRkasModal');
    if (tambahModal) {
        tambahModal.addEventListener('show.bs.modal', function() {
            initializeAnggaranList();
        });

        // Reset saat modal ditutup
        tambahModal.addEventListener('hidden.bs.modal', function() {
            firstSatuanValue = '';
        });
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

    // Panggil inisialisasi Select2
    initializeSelect2();

    // Event listener untuk tombol tambah bulan
    document.getElementById('btn-tambah-bulan')?.addEventListener('click', handleTambahBulanClick);

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
        
        jumlahInput.addEventListener('input', updateTotalAnggaran);
        bulanSelect.addEventListener('change', updateTotalAnggaran);
        
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

        // Jika sudah ada nilai satuan pertama, set ke card baru
        if (firstSatuanValue) {
            satuanInput.value = firstSatuanValue;
            satuanInput.readOnly = true;
        }

        return card;
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
        container.appendChild(initialCard);
        
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
        container.appendChild(newCard);
        
        checkTambahButtonVisibility();
        updateTotalAnggaran();
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
        const tambahBtn = document.getElementById('btn-tambah-bulan');
        
        if (tambahBtn) {
            if (cards.length >= 12) {
                tambahBtn.style.display = 'none';
            } else {
                tambahBtn.style.display = 'inline-block';
            }
        }
    }

    function updateTotalAnggaran() {
        let total = 0;
        const hargaSatuan = getHargaSatuanValue();

        document.querySelectorAll('.anggaran-bulan-card').forEach(function(card) {
            const jumlah = parseFloat(card.querySelector('.jumlah-input')?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        const totalDisplay = document.getElementById('total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + formatNumber(total);
        }
    }

    function formatNumber(num) {
        if (!num) return '0';
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Fungsi untuk mendapatkan nilai harga satuan sebagai number
    function getHargaSatuanValue() {
        const hargaSatuanInput = document.getElementById('harga_satuan');
        if (!hargaSatuanInput || !hargaSatuanInput.value) return 0;
        
        // Hapus format titik untuk perhitungan
        const numericValue = hargaSatuanInput.value.replace(/\./g, '');
        return parseFloat(numericValue) || 0;
    }

    // FORMAT RUPIAH OTOMATIS
    const hargaSatuanInput = document.getElementById('harga_satuan');
    if (hargaSatuanInput) {
        // Format saat input berubah
        hargaSatuanInput.addEventListener('input', function(e) {
            const input = e.target;
            const cursorPosition = input.selectionStart;
            
            // Simpan nilai sebelum format
            const originalValue = input.value;
            
            // Hapus semua karakter non-digit
            let numericValue = originalValue.replace(/\D/g, '');
            
            // Format dengan titik
            let formattedValue = formatNumber(numericValue);
            
            // Update nilai
            input.value = formattedValue;
            
            // Sesuaikan cursor position
            const dotsAdded = countDotsAfterPosition(originalValue, cursorPosition, formattedValue);
            const newCursorPosition = cursorPosition + dotsAdded;
            
            input.setSelectionRange(newCursorPosition, newCursorPosition);
            
            // Update total anggaran
            updateTotalAnggaran();
        });

        // Validasi input - hanya angka yang diperbolehkan
        hargaSatuanInput.addEventListener('keydown', function(e) {
            // Izinkan: backspace, delete, tab, escape, enter, arrow keys
            if ([46, 8, 9, 27, 13, 110, 190].includes(e.keyCode) ||
                // Izinkan: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) || 
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Izinkan: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            
            // Pastikan hanya angka yang dimasukkan
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

    // Fungsi untuk menghitung berapa banyak titik yang ditambahkan setelah cursor position
    function countDotsAfterPosition(originalValue, cursorPosition, formattedValue) {
        const beforeCursorOriginal = originalValue.substring(0, cursorPosition);
        const numericBeforeCursor = beforeCursorOriginal.replace(/\D/g, '');
        const formattedBeforeCursor = formatNumber(numericBeforeCursor);
        
        return formattedBeforeCursor.length - beforeCursorOriginal.length;
    }

    // Reset saat modal dibuka
    if (tambahModal) {
        tambahModal.addEventListener('show.bs.modal', function() {
            // Reset semua input satuan menjadi editable
            document.querySelectorAll('.satuan-input').forEach(input => {
                input.readOnly = false;
            });
            firstSatuanValue = '';
            
            // Reset harga satuan
            if (hargaSatuanInput) {
                hargaSatuanInput.value = '';
            }
        });
    }

    // Event listener untuk form submission - SOLUSI SIMPLE
    document.getElementById('tambahRkasForm')?.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Set nilai hidden field dengan harga satuan yang sudah di-unformat
        const unformattedHargaSatuan = getHargaSatuanValue().toString();
        document.getElementById('harga_satuan_actual').value = unformattedHargaSatuan;
        
        // Tampilkan loading
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
            submitBtn.disabled = true;
        }
        
        // Biarkan form submit secara normal
        // Tidak perlu fetch() lagi
    });

    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll(
            '#tambahRkasForm input[required], #tambahRkasForm select[required], #tambahRkasForm textarea[required]'
        );

        requiredFields.forEach(function(field) {
            field.classList.remove('is-invalid');
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        // Validasi harga satuan harus lebih dari 0
        const hargaSatuanValue = getHargaSatuanValue();
        if (hargaSatuanValue <= 0) {
            isValid = false;
            document.getElementById('harga_satuan').classList.add('is-invalid');
            Swal.fire('Error', 'Harga satuan harus lebih dari 0', 'error');
        }

        // Validate month uniqueness
        const selectedMonths = [];
        const monthSelects = document.querySelectorAll('.bulan-input');

        monthSelects.forEach(function(select) {
            const month = select.value;
            if (month) {
                if (selectedMonths.includes(month)) {
                    isValid = false;
                    select.classList.add('is-invalid');
                    Swal.fire('Error', 'Tidak boleh ada bulan yang sama dalam satu kegiatan.', 'error');
                    return false;
                }
                selectedMonths.push(month);
            }
        });

        if (!isValid) {
            Swal.fire('Error', 'Mohon lengkapi semua field yang diperlukan.', 'error');
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