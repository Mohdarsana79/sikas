document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi variabel global
    let currentRkasId = null;
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Inisialisasi komponen
    initializeSelect2();
    setupEventListeners();
    initializeAnggaranList();
    updateTahapCards();

    // ========== UTILITY FUNCTIONS ==========
    function formatNumber(num) {
        if (!num) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function formatRupiah(angka) {
        if (typeof angka === 'string') {
            angka = angka.replace(/[^\d]/g, '');
        }
        const num = parseInt(angka);
        if (isNaN(num)) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function getActiveTab() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        return activeTab ? activeTab.getAttribute('data-month').toLowerCase() : null;
    }

    // ========== SELECT2 FUNCTIONS ==========
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

    function initializeSelect2() {
        // Select2 untuk modal tambah
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

        // Select2 untuk modal edit
        $('.select2-kegiatan-edit').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: formatKegiatanOption,
            templateSelection: formatKegiatanSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening-edit').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: formatRekeningOption,
            templateSelection: formatRekeningSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }

    // ========== MODAL TAMBAH FUNCTIONS ==========
    function createAnggaranCard() {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        card.innerHTML = `
            <button type="button" class="delete-btn">
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
        
        const deleteBtn = card.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', () => {
            card.remove();
            checkTambahButtonVisibility();
            updateTotalAnggaran();
        });

        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        
        jumlahInput.addEventListener('input', updateTotalAnggaran);
        bulanSelect.addEventListener('change', updateTotalAnggaran);

        return card;
    }

    function createTambahButtonCard() {
        const btnCard = document.createElement('div');
        btnCard.className = 'btn-tambah-card';
        btnCard.innerHTML = `
            <button type="button" class="btn-tambah" id="btn-tambah-bulan">
                <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
            </button>
        `;
        
        const tambahBtn = btnCard.querySelector('#btn-tambah-bulan');
        tambahBtn.addEventListener('click', handleTambahBulanClick);
        
        return btnCard;
    }

    function checkTambahButtonVisibility() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        if (cards.length >= 12) {
            if (tambahBtnCard) tambahBtnCard.style.display = 'none';
        } else {
            if (tambahBtnCard) tambahBtnCard.style.display = 'flex';
        }
    }

    function initializeAnggaranList() {
        const container = document.getElementById('bulanContainer');
        if (!container) return;

        container.innerHTML = '';
        const initialCard = createAnggaranCard();
        const tambahBtnCard = createTambahButtonCard();
        container.appendChild(initialCard);
        container.appendChild(tambahBtnCard);
        
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
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        const newCard = createAnggaranCard();
        
        container.insertBefore(newCard, tambahBtnCard);
        checkTambahButtonVisibility();
        updateTotalAnggaran();
    }

    function updateTotalAnggaran() {
        let total = 0;
        const hargaSatuanInput = document.getElementById('harga_satuan');
        const hargaSatuan = hargaSatuanInput ? parseFloat(hargaSatuanInput.value.replace(/[^\d]/g, '')) || 0 : 0;

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

    function resetModal() {
        const form = document.getElementById('tambahRkasForm');
        if (form) form.reset();

        $('.select2-kegiatan').val(null).trigger('change');
        $('.select2-rekening').val(null).trigger('change');

        document.querySelectorAll('.form-control').forEach(function(field) {
            field.classList.remove('is-invalid');
        });

        const submitBtn = document.querySelector('#tambahRkasForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
            submitBtn.disabled = false;
        }

        initializeAnggaranList();
    }

    // ========== MODAL EDIT FUNCTIONS ==========
    function getEditHargaSatuanValue() {
        const hargaSatuanInput = document.getElementById('edit_harga_satuan');
        const hargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
        
        console.log('🔧 [HARGA DEBUG] Getting harga satuan value:', {
            display: hargaSatuanInput?.value,
            actual: hargaSatuanActual?.value,
            hasActual: !!hargaSatuanActual?.value
        });
        
        // Prioritaskan nilai dari hidden field
        if (hargaSatuanActual && hargaSatuanActual.value) {
            const numericValue = parseFloat(hargaSatuanActual.value);
            console.log('🔧 [HARGA DEBUG] Using actual value:', numericValue);
            return isNaN(numericValue) ? 0 : numericValue;
        }
        
        // Fallback ke input display
        if (!hargaSatuanInput || !hargaSatuanInput.value) {
            console.log('🔧 [HARGA DEBUG] No value found, returning 0');
            return 0;
        }
        
        const numericValue = parseFloat(hargaSatuanInput.value.replace(/[^\d]/g, ''));
        console.log('🔧 [HARGA DEBUG] Using display value:', numericValue);
        
        // PERBAIKAN: Update hidden field jika kosong
        if ((!hargaSatuanActual || !hargaSatuanActual.value) && !isNaN(numericValue)) {
            if (hargaSatuanActual) {
                hargaSatuanActual.value = numericValue.toString();
                console.log('🔧 [HARGA DEBUG] Auto-filled hidden field:', hargaSatuanActual.value);
            }
        }
        
        return isNaN(numericValue) ? 0 : numericValue;
    }

    function initializeEditAnggaranList(data = [], lockedMonths = [], allowedMonths = []) {
        const container = document.getElementById('edit_bulanContainer');
        if (!container) {
            console.error('❌ [EDIT DEBUG] Edit bulan container not found');
            return;
        }

        console.log('🔧 [EDIT DEBUG] Initializing edit bulan list with data:', data);
        console.log('🔧 [EDIT DEBUG] Locked months:', lockedMonths);
        console.log('🔧 [EDIT DEBUG] Allowed months:', allowedMonths);
        
        container.innerHTML = '';
        
        // Jika ada data bulan, buat card untuk setiap bulan
        if (data && data.length > 0) {
            console.log('🔧 [EDIT DEBUG] Creating cards for', data.length, 'months');
            data.forEach((item, index) => {
                const card = createEditAnggaranCard(item.bulan, item.jumlah, item.satuan, lockedMonths, allowedMonths);
                container.appendChild(card);
                console.log('🔧 [EDIT DEBUG] Created card for bulan:', item.bulan);
            });
        } else {
            // Jika tidak ada data, buat card default
            console.log('🔧 [EDIT DEBUG] No month data, creating default card');
            const initialCard = createEditAnggaranCard('', '', '', lockedMonths, allowedMonths);
            container.appendChild(initialCard);
        }
        
        const tambahBtnCard = createEditTambahButtonCard(lockedMonths, allowedMonths);
        container.appendChild(tambahBtnCard);
        
        console.log('🔧 [EDIT DEBUG] Total cards created:', container.querySelectorAll('.anggaran-bulan-card').length);
        
        // Update total setelah delay
        setTimeout(updateEditTotalAnggaran, 300);
    }

    function createEditAnggaranCard(bulan = '', jumlah = '', satuan = '', lockedMonths = [], allowedMonths = []) {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        
        const isLockedMonth = lockedMonths.includes(bulan);
        const isDisabled = isLockedMonth;
        
        card.innerHTML = `
            <button type="button" class="delete-btn" ${isDisabled ? 'disabled style="display:none;"' : ''} title="${isDisabled ? 'Bulan terkunci - tidak dapat dihapus' : 'Hapus bulan'}">
                <i class="bi bi-x"></i>
            </button>
            <div class="bulan-input-group">
                <select class="form-control bulan-input" name="bulan[]" ${isDisabled ? 'disabled' : 'required'} style="${isDisabled ? 'background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;' : ''}">
                    <option value="">Pilih Bulan</option>
                    ${allowedMonths.map(b => `<option value="${b}" ${b === bulan ? 'selected' : ''}>${b}</option>`).join('')}
                    ${lockedMonths.map(b => `<option value="${b}" ${b === bulan ? 'selected' : ''} disabled style="background-color: #f8f9fa; color: #6c757d;">${b} 🔒</option>`).join('')}
                </select>
                <span class="month-total">Rp 0</span>
            </div>
            <div class="jumlah-satuan-group">
                <div class="input-icon-left">
                    <i class="bi bi-hash"></i>
                    <input type="number" class="form-control jumlah-input" name="jumlah[]" 
                        placeholder="Jumlah" min="1" value="${jumlah || ''}" 
                        ${isDisabled ? 'readonly style="background-color: #f8f9fa; cursor: not-allowed;"' : 'required'}>
                </div>
                <input type="text" class="form-control satuan-input" name="satuan[]" 
                    placeholder="Satuan" value="${satuan || ''}" 
                    ${isDisabled ? 'readonly style="background-color: #f8f9fa; cursor: not-allowed;"' : 'required'}>
            </div>
            ${isLockedMonth ? '<div class="lock-indicator"><small class="text-muted"><i class="bi bi-lock"></i> Bulan terkunci</small></div>' : ''}
        `;
        
        // Hanya tambah event listener untuk bulan yang tidak terkunci
        if (!isLockedMonth) {
            const deleteBtn = card.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card:not(.locked-card)');
                    if (cards.length > 1) {
                        card.remove();
                        updateEditTotalAnggaran();
                        checkEditTambahButtonVisibility(lockedMonths, allowedMonths);
                    } else {
                        Swal.fire('Peringatan', 'Minimal harus ada satu bulan yang aktif', 'warning');
                    }
                });
            }
        }

        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        
        if (jumlahInput && !isLockedMonth) {
            jumlahInput.addEventListener('input', updateEditTotalAnggaran);
        }
        if (bulanSelect && !isLockedMonth) {
            bulanSelect.addEventListener('change', updateEditTotalAnggaran);
        }

        return card;
    }

    function createEditTambahButtonCard(lockedMonths = [], allowedMonths = []) {
        const btnCard = document.createElement('div');
        btnCard.className = 'btn-tambah-card';
        btnCard.innerHTML = `
            <button type="button" class="btn-tambah" id="edit_btn-tambah-bulan">
                <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
            </button>
        `;
        
        const tambahBtn = btnCard.querySelector('#edit_btn-tambah-bulan');
        tambahBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleEditTambahBulan(lockedMonths, allowedMonths);
        });
        
        return btnCard;
    }

    function handleEditTambahBulan(lockedMonths = [], allowedMonths = []) {
        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        const activeCards = Array.from(cards).filter(card => {
            const select = card.querySelector('.bulan-input');
            return select && !select.disabled;
        });
        
        if (activeCards.length >= allowedMonths.length) {
            Swal.fire('Peringatan', `Maksimal ${allowedMonths.length} bulan aktif (Juli-Desember) dalam setahun`, 'warning');
            return;
        }

        const container = document.getElementById('edit_bulanContainer');
        const tambahBtnCard = document.querySelector('#edit_bulanContainer .btn-tambah-card');
        const newCard = createEditAnggaranCard('', '', '', lockedMonths, allowedMonths);
        
        if (container && tambahBtnCard) {
            container.insertBefore(newCard, tambahBtnCard);
            setTimeout(updateEditTotalAnggaran, 50);
            checkEditTambahButtonVisibility(lockedMonths, allowedMonths);
        }
    }

    function checkEditTambahButtonVisibility(lockedMonths = [], allowedMonths = []) {
        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        const activeCards = Array.from(cards).filter(card => {
            const select = card.querySelector('.bulan-input');
            return select && !select.disabled;
        });
        
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        if (tambahBtnCard) {
            if (activeCards.length >= allowedMonths.length) {
                tambahBtnCard.style.display = 'none';
            } else {
                tambahBtnCard.style.display = 'flex';
            }
        }
    }

    function updateEditTotalAnggaran() {
        let total = 0;
        const hargaSatuan = getEditHargaSatuanValue();

        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        cards.forEach(function(card) {
            const jumlahInput = card.querySelector('.jumlah-input');
            const jumlah = parseInt(jumlahInput?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        const totalDisplay = document.getElementById('edit_total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + formatNumber(total);
        }
    }

    function validateEditForm() {
        let isValid = true;
        const errorMessages = [];

        document.querySelectorAll('#editRkasForm .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });

        const requiredFields = [
            { element: document.getElementById('edit_kegiatan'), name: 'Kegiatan' },
            { element: document.getElementById('edit_rekening_belanja'), name: 'Rekening Belanja' },
            { element: document.getElementById('edit_uraian'), name: 'Uraian' }
        ];

        requiredFields.forEach(field => {
            if (!field.element || !field.element.value || !field.element.value.toString().trim()) {
                isValid = false;
                if (field.element) {
                    field.element.classList.add('is-invalid');
                }
                errorMessages.push(`${field.name} harus diisi`);
            }
        });

        // PERBAIKAN: Validasi harga satuan dengan lebih detail
        const hargaSatuanValue = getEditHargaSatuanValue();
        const hargaSatuanInput = document.getElementById('edit_harga_satuan');
        const hargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
        
        console.log('🔧 [VALIDATION DEBUG] Harga satuan validation:', {
            displayValue: hargaSatuanInput?.value,
            actualValue: hargaSatuanActual?.value,
            numericValue: hargaSatuanValue
        });

        if (!hargaSatuanValue || hargaSatuanValue <= 0 || isNaN(hargaSatuanValue)) {
            isValid = false;
            if (hargaSatuanInput) {
                hargaSatuanInput.classList.add('is-invalid');
            }
            errorMessages.push('Harga satuan harus lebih dari 0');
        }

        const selectedMonths = [];
        const monthSelects = document.querySelectorAll('#edit_bulanContainer .bulan-input');

        if (monthSelects.length === 0) {
            isValid = false;
            errorMessages.push('Minimal harus ada satu bulan yang diisi');
        }

        monthSelects.forEach(function(select) {
            if (select) {
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
            }
        });

        const jumlahInputs = document.querySelectorAll('#edit_bulanContainer .jumlah-input');
        jumlahInputs.forEach(function(input) {
            if (input && !input.readOnly) { // Hanya validasi yang tidak read-only (bukan bulan terkunci)
                input.classList.remove('is-invalid');
                const value = parseInt(input.value);
                if (isNaN(value) || value <= 0) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    errorMessages.push('Jumlah harus lebih dari 0 untuk semua bulan');
                }
            }
        });

        const satuanInputs = document.querySelectorAll('#edit_bulanContainer .satuan-input');
        satuanInputs.forEach(function(input) {
            if (input && !input.readOnly) { // Hanya validasi yang tidak read-only (bukan bulan terkunci)
                input.classList.remove('is-invalid');
                if (!input.value || !input.value.toString().trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    errorMessages.push('Satuan harus diisi untuk semua bulan');
                }
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

    // ========== DELETE FUNCTIONS ==========
    function setupDeleteButton() {
        const deleteBtn = document.getElementById('btnDeleteAllData');
        console.log('🔧 [DELETE DEBUG] Setting up delete button:', deleteBtn);
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('🔧 [DELETE DEBUG] Delete button clicked');
                
                const mainDataId = document.getElementById('edit_main_data_id').value;
                console.log('🔧 [DELETE DEBUG] Main data ID:', mainDataId);
                
                if (!mainDataId) {
                    Swal.fire('Error', 'Data tidak valid untuk dihapus', 'error');
                    return;
                }
                
                showDeleteAllConfirmation(mainDataId);
            });
        } else {
            console.error('❌ [DELETE DEBUG] Delete button not found');
        }
    }

    function showDeleteAllConfirmation(mainDataId) {
        console.log('🔧 [DELETE DEBUG] Showing confirmation for ID:', mainDataId);
        
        Swal.fire({
            title: 'Hapus Semua Data?',
            html: `
                <div class="text-start">
                    <p>Anda yakin ingin menghapus <strong>SEMUA DATA</strong> untuk kegiatan ini?</p>
                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Perhatian:</strong> Tindakan ini akan menghapus semua data dengan kegiatan, rekening, dan uraian yang sama.
                            <br>Data yang terkunci (Januari-Juni) tidak akan terpengaruh.
                        </small>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('🔧 [DELETE DEBUG] User confirmed deletion');
                deleteAllData(mainDataId);
            } else {
                console.log('🔧 [DELETE DEBUG] User cancelled deletion');
            }
        });
    }

    function deleteAllData(mainDataId) {
        console.log('🔧 [DELETE DEBUG] Starting delete process for ID:', mainDataId);
        
        const deleteBtn = document.getElementById('btnDeleteAllData');
        const originalHtml = deleteBtn.innerHTML;
        
        deleteBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menghapus...';
        deleteBtn.disabled = true;

        fetch(`/rkas-perubahan/delete-all/${mainDataId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            console.log('🔧 [DELETE DEBUG] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('🔧 [DELETE DEBUG] Delete response:', data);
            if (data.success) {
                // Tutup modal edit
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editRkasModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Gagal menghapus data');
            }
        })
        .catch(error => {
            console.error('❌ [DELETE DEBUG] Error deleting all data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hapus Gagal',
                text: 'Terjadi kesalahan saat menghapus data: ' + error.message,
                confirmButtonText: 'Mengerti'
            });
        })
        .finally(() => {
            deleteBtn.innerHTML = originalHtml;
            deleteBtn.disabled = false;
            console.log('🔧 [DELETE DEBUG] Delete process completed');
        });
    }

    // ========== GLOBAL FUNCTIONS ==========
    window.showEditModal = function(id) {
        console.log('🔧 [EDIT DEBUG] Opening edit modal for ID:', id);
        
        if (!id) {
            console.error('❌ [EDIT DEBUG] Invalid ID provided');
            Swal.fire('Error', 'ID data tidak valid', 'error');
            return;
        }

        // Debug information
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('🔧 [EDIT DEBUG] CSRF Token:', csrfToken ? 'Available' : 'Missing');
        console.log('🔧 [EDIT DEBUG] Using EDIT route:', `/rkas-perubahan/${id}/edit`);

        Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // GUNAKAN ROUTE EDIT YANG BARU
        fetch(`/rkas-perubahan/${id}/edit`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        })
        .then(response => {
            console.log('🔧 [EDIT DEBUG] Response status:', response.status);
            console.log('🔧 [EDIT DEBUG] Response OK:', response.ok);
            
            if (!response.ok) {
                // Detailed error information
                if (response.status === 404) {
                    throw new Error(`Data tidak ditemukan (404). Pastikan ID ${id} valid.`);
                } else if (response.status === 500) {
                    throw new Error(`Server error (500). Periksa log server.`);
                } else {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }
            }
            return response.json();
        })
        .then(data => {
            console.log('🔧 [EDIT DEBUG] API Response:', data);
            Swal.close();
            
            if (data.success && data.data) {
                console.log('🔧 [EDIT DEBUG] Data loaded successfully:', data.data);
                populateEditForm(data.data);
            } else {
                console.error('❌ [EDIT DEBUG] API returned error:', data.message);
                Swal.fire('Error', data.message || 'Gagal memuat data untuk edit', 'error');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('❌ [EDIT DEBUG] Error fetching edit data:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Error Memuat Data',
                html: `
                    <div style="text-align: left;">
                        <p><strong>Terjadi kesalahan saat memuat data:</strong></p>
                        <p>${error.message}</p>
                        <hr>
                        <small>ID: ${id}<br>
                        Route: /rkas-perubahan/${id}/edit</small>
                    </div>
                `,
                confirmButtonText: 'Mengerti'
            });
        });
    };

    // Fungsi terpisah untuk mengisi form edit dengan MULTI-BULAN
    function populateEditForm(data) {
        console.log('🔧 [EDIT DEBUG] Populating form with MULTI-MONTH data:', data);
        console.log('🔧 [EDIT DEBUG] Month data received:', data.bulan_data);
        console.log('🔧 [EDIT DEBUG] Locked months:', data.locked_months);
        console.log('🔧 [EDIT DEBUG] Allowed months:', data.allowed_months);
        
        const editForm = document.getElementById('editRkasForm');
        if (!editForm) {
            console.error('❌ [EDIT DEBUG] Edit form not found');
            Swal.fire('Error', 'Form edit tidak ditemukan', 'error');
            return;
        }

        // Reset form
        editForm.reset();

        // Set basic form values
        const editKegiatan = document.getElementById('edit_kegiatan');
        const editRekening = document.getElementById('edit_rekening_belanja');
        const editUraian = document.getElementById('edit_uraian');
        const editHargaSatuan = document.getElementById('edit_harga_satuan');
        const editHargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
        const editMainDataId = document.getElementById('edit_main_data_id');
        
        if (editKegiatan) editKegiatan.value = data.kode_id;
        if (editRekening) editRekening.value = data.kode_rekening_id;
        if (editUraian) editUraian.value = data.uraian;
        
        // PERBAIKAN PENTING: Set main data ID untuk delete
        if (editMainDataId) {
            editMainDataId.value = data.id;
            console.log('🔧 [EDIT DEBUG] Set main data ID for delete:', data.id);
        }
        
        // Format harga satuan
        if (editHargaSatuan && data.harga_satuan_raw) {
            const hargaSatuanNumeric = parseFloat(data.harga_satuan_raw);
            if (!isNaN(hargaSatuanNumeric)) {
                const formattedValue = formatRupiah(hargaSatuanNumeric.toString());
                editHargaSatuan.value = formattedValue;
                
                // PERBAIKAN PENTING: Inisialisasi hidden field juga
                if (editHargaSatuanActual) {
                    editHargaSatuanActual.value = hargaSatuanNumeric.toString();
                    console.log('🔧 [HARGA DEBUG] Initialized hidden field with:', editHargaSatuanActual.value);
                }
                console.log('🔧 [HARGA DEBUG] Initialized display with:', formattedValue);
            } else {
                const rawValue = data.harga_satuan_raw.toString().replace(/[^\d]/g, '');
                editHargaSatuan.value = formatRupiah(rawValue);
                if (editHargaSatuanActual) {
                    editHargaSatuanActual.value = rawValue;
                    console.log('🔧 [HARGA DEBUG] Initialized hidden field with raw:', editHargaSatuanActual.value);
                }
            }
        } else {
            console.warn('🔧 [HARGA DEBUG] No harga_satuan_raw data available');
        }
        
        // Initialize Select2
        setTimeout(() => {
            if (window.jQuery && $('.select2-kegiatan-edit').length) {
                $('.select2-kegiatan-edit').val(data.kode_id).trigger('change');
                console.log('🔧 [EDIT DEBUG] Select2 kegiatan set to:', data.kode_id);
            }
            if (window.jQuery && $('.select2-rekening-edit').length) {
                $('.select2-rekening-edit').val(data.kode_rekening_id).trigger('change');
                console.log('🔧 [EDIT DEBUG] Select2 rekening set to:', data.kode_rekening_id);
            }
        }, 100);
        
        // SET DATA SEMUA BULAN - DENGAN INFORMASI BULAN TERKUNCI
        console.log('🔧 [EDIT DEBUG] Initializing MULTI-MONTH data with locked months info');
        initializeEditAnggaranList(data.bulan_data, data.locked_months, data.allowed_months);
        
        // Set form action untuk update
        editForm.action = `/rkas-perubahan/${data.id}`;
        console.log('🔧 [EDIT DEBUG] Form action set to:', editForm.action);
        
        // Show modal
        const editModalElement = document.getElementById('editRkasModal');
        if (editModalElement) {
            const editModal = new bootstrap.Modal(editModalElement);
            editModal.show();
            console.log('🔧 [EDIT DEBUG] Edit modal shown successfully');
            
            // Update total setelah modal ditampilkan
            setTimeout(updateEditTotalAnggaran, 500);
        } else {
            console.error('❌ [EDIT DEBUG] Edit modal element not found');
            Swal.fire('Error', 'Modal edit tidak ditemukan', 'error');
        }

        // Force update hidden field saat modal dibuka
        setTimeout(() => {
            const hargaSatuanInput = document.getElementById('edit_harga_satuan');
            const hargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
            
            if (hargaSatuanInput && hargaSatuanInput.value && (!hargaSatuanActual || !hargaSatuanActual.value)) {
                const numericValue = hargaSatuanInput.value.replace(/[^\d]/g, '');
                if (numericValue && hargaSatuanActual) {
                    hargaSatuanActual.value = numericValue;
                    console.log('🔧 [HARGA DEBUG] Force updated hidden field on modal open:', numericValue);
                }
            }
        }, 1000);
    }

    window.showDetailModal = function(id) {
        currentRkasId = id;

        fetch(`/rkas-perubahan/show/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rkas = data.data;

                    document.getElementById('detail-program').textContent = rkas.program_kegiatan || '-';
                    document.getElementById('detail-kegiatan').textContent = rkas.kegiatan || '-';
                    document.getElementById('detail-rekening').textContent = rkas.rekening_belanja || '-';
                    document.getElementById('detail-bulan').textContent = rkas.bulan || '-';
                    document.getElementById('detail-uraian').textContent = rkas.uraian || '-';
                    document.getElementById('detail-jumlah').textContent = rkas.dianggaran || '-';
                    document.getElementById('detail-satuan').textContent = rkas.satuan || '-';
                    document.getElementById('detail-harga-satuan').textContent = rkas.harga_satuan || '-';
                    document.getElementById('detail-total').textContent = rkas.total || '-';

                    new bootstrap.Modal(document.getElementById('detailRkasModal')).show();
                } else {
                    Swal.fire('Error', 'Gagal memuat detail data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memuat detail data', 'error');
            });
    };

    window.showSisipkanModal = function(kodeId, program, kegiatan, rekeningId, rekeningDisplay) {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const bulan = activeTab ? activeTab.getAttribute('data-month') : '';

        document.getElementById('sisipkan_kode_id').value = kodeId;
        document.getElementById('sisipkan_kode_rekening_id').value = rekeningId;
        document.getElementById('sisipkan_bulan').value = bulan;
        document.getElementById('sisipkan_program').value = program;
        document.getElementById('sisipkan_kegiatan').value = kegiatan;
        document.getElementById('sisipkan_rekening_belanja_display').value = rekeningDisplay;

        document.getElementById('sisipkan_uraian').value = '';
        document.getElementById('sisipkan_harga_satuan').value = '';
        document.getElementById('sisipkan_jumlah').value = '';
        document.getElementById('sisipkan_satuan').value = '';
        document.getElementById('sisipkan_total_display').textContent = 'Rp 0';

        new bootstrap.Modal(document.getElementById('sisipkanRkasModal')).show();
    };

    window.showTahapDetail = function(tahap) {
        const tahapName = tahap === 1 ? 'Tahap 1 (Januari - Juni)' : 'Tahap 2 (Juli - Desember)';
        const headerClass = tahap === 1 ? 'bg-primary' : 'bg-success';
        const tableHeaderClass = tahap === 1 ? 'table-primary' : 'table-success';
        const tahunAnggaran = document.querySelector('input[name="tahun_anggaran"]').value;

        fetch(`/rkas-perubahan/data-tahap/${tahap}?tahun=${tahunAnggaran}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let tableContent = `
                    <div class="modal fade" id="tahapDetailModal" tabindex="-1" aria-labelledby="tahapDetailModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" style="max-width: 95%;">
                            <div class="modal-content" style="max-height: 95vh; overflow: hidden;">
                                <div class="modal-header ${headerClass} text-white">
                                    <h5 class="modal-title" id="tahapDetailModalLabel">
                                        <i class="bi bi-calendar-event me-2"></i>Detail ${tahapName} - Tahun ${tahunAnggaran}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="padding: 15px; max-height: 70vh; overflow-y: auto;">
                                    <div class="table-responsive"
                                        style="max-height: 60vh; overflow-x: auto; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; background: white;">
                                        <table class="table table-striped table-hover"
                                            style="font-size: 8pt; min-width: 100%; margin-bottom: 0;">
                                            <thead class="${tableHeaderClass}" style="position: sticky; top: 0; z-index: 10;">
                                                <tr>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">No</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Program Kegiatan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Kegiatan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Rekening Belanja</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Uraian</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Bulan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Dianggaran</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Satuan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Harga Satuan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                    `;

                    if (data.data.length > 0) {
                        data.data.forEach((item, index) => {
                            tableContent += `
                                <tr>
                                    <td style="font-size: 8pt; padding: 6px;">${index + 1}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">
                                        ${item.program_kegiatan}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">
                                        ${item.kegiatan}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">
                                        ${item.rekening_belanja}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 200px;">
                                        ${item.uraian}</td>
                                    <td style="font-size: 8pt; padding: 6px;"><span
                                            class="badge ${tahap === 1 ? 'bg-info' : 'bg-success'}"
                                            style="font-size: 7pt;">${item.bulan}</span></td>
                                    <td style="font-size: 8pt; padding: 6px;">${item.dianggaran}</td>
                                    <td style="font-size: 8pt; padding: 6px;">${item.satuan}</td>
                                    <td style="font-size: 8pt; padding: 6px;">${item.harga_satuan}</td>
                                    <td style="font-size: 8pt; padding: 6px;"><strong>${item.total}</strong></td>
                                </tr>
                            `;
                        });
                    } else {
                        tableContent += `
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4" style="font-size: 8pt;">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2" style="font-size: 8pt;">Belum ada data untuk ${tahapName} Tahun
                                        ${tahunAnggaran}</p>
                                </td>
                            </tr>
                        `;
                    }

                    tableContent += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                        style="font-size: 8pt;">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                    const existingModal = document.getElementById('tahapDetailModal');
                    if (existingModal) existingModal.remove();

                    document.body.insertAdjacentHTML('beforeend', tableContent);

                    const modal = new bootstrap.Modal(document.getElementById('tahapDetailModal'));
                    modal.show();

                    document.getElementById('tahapDetailModal').addEventListener('hidden.bs.modal', function() {
                        this.remove();
                    });
                } else {
                    Swal.fire('Error', 'Gagal memuat data detail tahap', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memuat data detail tahap', 'error');
            });
    };

    window.editFromDetail = function() {
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailRkasModal'));
        detailModal.hide();
        setTimeout(() => showEditModal(currentRkasId), 300);
    };

    // ========== SETUP EVENT LISTENERS ==========
    function setupEventListeners() {
        console.log('🔧 [SETUP] Setting up event listeners');
        
        // Form submission
        document.getElementById('tambahRkasForm')?.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
                submitBtn.disabled = true;
            }
        });

        // Edit form submission dengan debug detail
        document.getElementById('editRkasForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('🔧 [UPDATE DEBUG] Starting form submission...');
            
            if (!validateEditForm()) {
                console.error('❌ [UPDATE DEBUG] Form validation failed');
                return false;
            }
            
            // Debug detail data form
            const bulanInputs = Array.from(document.querySelectorAll('#edit_bulanContainer select[name="bulan[]"]'));
            const jumlahInputs = Array.from(document.querySelectorAll('#edit_bulanContainer input[name="jumlah[]"]'));
            const satuanInputs = Array.from(document.querySelectorAll('#edit_bulanContainer input[name="satuan[]"]'));
            
            // PERBAIKAN: Dapatkan nilai harga satuan yang sudah diformat
            const hargaSatuanInput = document.getElementById('edit_harga_satuan');
            const hargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
            
            console.log('🔧 [UPDATE DEBUG] Harga satuan elements:', {
                display: hargaSatuanInput?.value,
                actual: hargaSatuanActual?.value
            });
            
            const formData = {
                bulan: bulanInputs.map(input => input.value),
                jumlah: jumlahInputs.map(input => input.value),
                satuan: satuanInputs.map(input => input.value),
                kode_id: document.getElementById('edit_kegiatan')?.value,
                kode_rekening_id: document.getElementById('edit_rekening_belanja')?.value,
                uraian: document.getElementById('edit_uraian')?.value,
                harga_satuan: hargaSatuanActual?.value || this.querySelector('input[name="harga_satuan"]')?.value
            };
            
            console.log('🔧 [UPDATE DEBUG] Form data to be sent:', formData);
            
            // Validasi harga satuan sebelum kirim - PERBAIKAN
            const hargaSatuanValue = getEditHargaSatuanValue();
            console.log('🔧 [UPDATE DEBUG] Final harga satuan validation:', {
                value: hargaSatuanValue,
                isValid: hargaSatuanValue > 0
            });

            if (!hargaSatuanValue || hargaSatuanValue <= 0 || isNaN(hargaSatuanValue)) {
                console.error('❌ [UPDATE DEBUG] Harga satuan invalid:', hargaSatuanValue);
                
                // Debug detail
                console.log('🔧 [UPDATE DEBUG] Debug all harga satuan elements:', {
                    display: document.getElementById('edit_harga_satuan')?.value,
                    actual: document.getElementById('edit_harga_satuan_actual')?.value,
                    formData: formData.harga_satuan
                });
                
                Swal.fire({
                    icon: 'error',
                    title: 'Harga Satuan Invalid',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Harga satuan harus lebih dari 0.</strong></p>
                            <p>Nilai saat ini: <code>${hargaSatuanValue}</code></p>
                            <p>Pastikan harga satuan sudah diisi dengan angka yang valid.</p>
                        </div>
                    `,
                    confirmButtonText: 'Mengerti'
                });
                return false;
            }
            
            const emptyMonths = formData.bulan.filter(month => !month);
            if (emptyMonths.length > 0) {
                console.error('❌ [UPDATE DEBUG] Empty months detected:', emptyMonths);
                Swal.fire('Error', 'Ada bulan yang belum dipilih. Silakan pilih bulan untuk semua card.', 'error');
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Mengupdate...';
                submitBtn.disabled = true;
            }
            
            // Buat FormData object
            const formDataObj = new FormData(this);
            
            // PERBAIKAN: Pastikan harga_satuan ada di FormData
            if (!formDataObj.has('harga_satuan')) {
                console.log('🔧 [UPDATE DEBUG] harga_satuan missing in FormData, adding manually');
                if (formData.harga_satuan) {
                    formDataObj.append('harga_satuan', formData.harga_satuan);
                    console.log('🔧 [UPDATE DEBUG] Added harga_satuan to FormData:', formData.harga_satuan);
                } else {
                    // Coba dapatkan dari hidden field
                    const actualValue = document.getElementById('edit_harga_satuan_actual')?.value;
                    if (actualValue) {
                        formDataObj.append('harga_satuan', actualValue);
                        console.log('🔧 [UPDATE DEBUG] Added harga_satuan from hidden field:', actualValue);
                    } else {
                        // Fallback ke display value
                        const displayValue = document.getElementById('edit_harga_satuan')?.value.replace(/[^\d]/g, '');
                        if (displayValue) {
                            formDataObj.append('harga_satuan', displayValue);
                            console.log('🔧 [UPDATE DEBUG] Added harga_satuan from display:', displayValue);
                        }
                    }
                }
            } else {
                console.log('🔧 [UPDATE DEBUG] harga_satuan already in FormData');
            }
            
            console.log('🔧 [UPDATE DEBUG] Actual FormData content:');
            for (let [key, value] of formDataObj.entries()) {
                console.log(`  ${key}:`, value);
            }
            
            fetch(this.action, {
                method: 'POST',
                body: formDataObj,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                console.log('🔧 [UPDATE DEBUG] Response status:', response.status);
                const responseText = await response.text();
                console.log('🔧 [UPDATE DEBUG] Response text:', responseText);
                
                try {
                    return JSON.parse(responseText);
                } catch (e) {
                    throw new Error(`Invalid JSON response: ${responseText}`);
                }
            })
            .then(data => {
                console.log('🔧 [UPDATE DEBUG] Update response:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editRkasModal'));
                        if (editModal) {
                            editModal.hide();
                        }
                        location.reload();
                    });
                } else {
                    // Tampilkan error detail dari server
                    let errorMessage = data.message || 'Gagal mengupdate data';
                    if (data.errors) {
                        errorMessage += '\n\n' + Object.values(data.errors).flat().join('\n');
                    }
                    throw new Error(errorMessage);
                }
            })
            .catch(error => {
                console.error('❌ [UPDATE DEBUG] Error updating data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Update Gagal',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Terjadi kesalahan:</strong></p>
                            <p>${error.message}</p>
                            <hr>
                            <small>Periksa data yang dimasukkan dan coba lagi.</small>
                        </div>
                    `,
                    confirmButtonText: 'Mengerti'
                });
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Data';
                    submitBtn.disabled = false;
                }
            });
        });

        // Modal reset on close
        document.getElementById('tambahRkasModal')?.addEventListener('hidden.bs.modal', function() {
            resetModal();
        });

        // Month tab click handler
        document.querySelectorAll('.nav-link[data-month]').forEach(function(tab) {
            tab.addEventListener('click', function() {
                const month = this.getAttribute('data-month');
                refreshMonthData(month);
            });
        });

        // Sisipkan form submission
        document.getElementById('sisipkanRkasForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const modal = bootstrap.Modal.getInstance(document.getElementById('sisipkanRkasModal'));
            const currentMonth = getActiveTab();

            submitButton.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
            submitButton.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        localStorage.setItem('activeRkasTab', currentMonth);
                        Swal.fire('Berhasil!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menyimpan data');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.message, 'error');
                })
                .finally(() => {
                    submitButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
                    submitButton.disabled = false;
                });
        });

        // Event listener untuk tombol tambah bulan di modal tambah
        document.getElementById('btn-tambah-bulan')?.addEventListener('click', handleTambahBulanClick);

        // Event listener untuk format rupiah pada harga satuan edit
        const editHargaSatuanInput = document.getElementById('edit_harga_satuan');
        const editHargaSatuanActual = document.getElementById('edit_harga_satuan_actual');

        if (editHargaSatuanInput) {
            console.log('🔧 [HARGA DEBUG] Initializing harga satuan event listeners');
            
            let isEditFormatting = false;

            // Fungsi untuk update hidden field
            function updateHiddenHargaSatuan(value) {
                if (editHargaSatuanActual) {
                    const numericValue = value.replace(/[^\d]/g, '');
                    editHargaSatuanActual.value = numericValue;
                    console.log('🔧 [HARGA DEBUG] Updated hidden field:', numericValue);
                }
            }

            // Event listener untuk input
            editHargaSatuanInput.addEventListener('input', function(e) {
                if (isEditFormatting) return;
                
                isEditFormatting = true;
                
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [HARGA DEBUG] Input event, raw value:', value);
                
                if (value) {
                    const formattedValue = formatRupiah(value);
                    input.value = formattedValue;
                    
                    // Update hidden field
                    updateHiddenHargaSatuan(value);
                } else {
                    input.value = '';
                    updateHiddenHargaSatuan('');
                }
                
                isEditFormatting = false;
                setTimeout(updateEditTotalAnggaran, 50);
            });

            // Event listener untuk blur
            editHargaSatuanInput.addEventListener('blur', function(e) {
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [HARGA DEBUG] Blur event, raw value:', value);
                
                if (value) {
                    const formattedValue = formatRupiah(value);
                    input.value = formattedValue;
                    
                    // Update hidden field
                    updateHiddenHargaSatuan(value);
                } else {
                    updateHiddenHargaSatuan('');
                }
                updateEditTotalAnggaran();
            });

            // Event listener untuk focus - pastikan format benar saat focus
            editHargaSatuanInput.addEventListener('focus', function(e) {
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [HARGA DEBUG] Focus event, raw value:', value);
                
                if (value) {
                    // Saat focus, tampilkan tanpa format untuk memudahkan edit
                    input.value = value;
                }
            });

            // Event listener untuk keydown - handle enter key
            editHargaSatuanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = e.target.value.replace(/[^\d]/g, '');
                    if (value) {
                        const formattedValue = formatRupiah(value);
                        e.target.value = formattedValue;
                        updateHiddenHargaSatuan(value);
                    }
                    updateEditTotalAnggaran();
                }
            });
        }

        // PERBAIKAN PENTING: Setup delete button
        setupDeleteButton();
    }

    // ========== OTHER FUNCTIONS ==========
    function refreshMonthData(month) {
        console.log('Memulai refresh data untuk bulan:', month);
        const formattedMonth = month.charAt(0).toUpperCase() + month.slice(1).toLowerCase();
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        
        if (!tableBody) {
            console.error('Table body tidak ditemukan untuk bulan:', month);
            return;
        }

        tableBody.innerHTML = `<tr>
            <td colspan="11" class="text-center">
                <div class="py-4">
                    <div class="loading-spinner me-2"></div>Memuat data...
                </div>
            </td>
        </tr>`;

        fetch(`/rkas-perubahan/bulan/${formattedMonth}?tahun=${document.querySelector('input[name="tahun_anggaran"]').value}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            console.log('Data diterima:', data);
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    populateTable(month, data.data);
                } else {
                    showNoDataMessage(month);
                }
            } else {
                throw new Error(data.message || 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error saat refresh:', error);
            tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-danger">
                    <div class="py-4">
                        <i class="bi bi-exclamation-triangle display-4"></i>
                        <p class="mt-2">Gagal memuat data: ${error.message}</p>
                    </div>
                </td>
            </tr>
            `;
        });
    }

    function populateTable(month, items) {
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        if (!tableBody) {
            console.error('Table body tidak ditemukan');
            return;
        }

        let html = '';
        let total = 0;

        items.forEach((item, index) => {
            const itemTotal = parseFloat(item.total.replace(/[^\d]/g, ''));
            total += itemTotal;

            html += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.program_kegiatan || '-'}</td>
                <td>${item.kegiatan || '-'}</td>
                <td>${item.rekening_belanja || '-'}</td>
                <td>${item.uraian || '-'}</td>
                <td>${item.dianggaran || '0'}</td>
                <td>0</td>
                <td>${item.satuan || '-'}</td>
                <td>${item.harga_satuan || 'Rp 0'}</td>
                <td><strong>${item.total || 'Rp 0'}</strong></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionDropdown${item.id}"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown${item.id}">
                            <li>
                                <a class="dropdown-item" href="#" onclick="showDetailModal(${item.id})">
                                    <i class="bi bi-eye me-2"></i>Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showSisipkanModal(
                                    ${item.kode_id},
                                    '${escapeHtml(item.program_kegiatan)}',
                                    '${escapeHtml(item.kegiatan)}',
                                    ${item.kode_rekening_id},
                                    '${escapeHtml(item.rekening_belanja)}'
                                )">
                                    <i class="bi bi-archive-fill me-2 text-warning"></i>Sisipkan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showEditModal(${item.id})">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a>
                            </li>
                            <!-- TOMBOL DELETE DIHAPUS DARI SINI -->
                        </ul>
                    </div>
                </td>
            </tr>
            `;
        });

        html += `
        <tr class="table-info">
            <td colspan="9" class="text-end"><strong>Total ${month}:</strong></td>
            <td><strong>Rp ${formatNumber(total)}</strong></td>
            <td></td>
        </tr>
        `;

        tableBody.innerHTML = html;
    }

    function showNoDataMessage(month) {
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        tableBody.innerHTML = `
            <tr class="no-data-row">
                <td colspan="11" class="text-center text-muted">
                    <div class="py-4">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Belum ada data untuk bulan ${month}</p>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#tambahRkasModal">
                            <i class="bi bi-plus me-2"></i>Tambah Data
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function updateTahapCards() {
        const tahunAnggaran = document.querySelector('input[name="tahun_anggaran"]').value;

        fetch(`/rkas-perubahan/total-tahap1?tahun=${tahunAnggaran}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tahap1Data = data.data;
                    const totalTahap1Element = document.getElementById('totalTahap1');
                    const sisaTahap1Element = document.getElementById('sisaTahap1');
                    const percentTahap1Element = document.getElementById('percentTahap1');

                    if (totalTahap1Element) {
                        totalTahap1Element.textContent = 'Rp ' + formatNumber(tahap1Data.total_anggaran);
                    }
                    if (sisaTahap1Element) {
                        sisaTahap1Element.textContent = 'Rp ' + formatNumber(tahap1Data.sisa_anggaran);
                    }
                    if (percentTahap1Element) {
                        percentTahap1Element.textContent = Math.round(tahap1Data.persentase_terpakai) + '%';
                    }

                    const progressBar1 = document.querySelector('.col-lg-4:nth-child(2) .progress-bar');
                    if (progressBar1) {
                        setTimeout(() => {
                            progressBar1.style.width = tahap1Data.persentase_terpakai + '%';
                        }, 300);
                    }
                }
            })
            .catch(error => console.error('Error updating Tahap 1:', error));

        fetch(`/rkas-perubahan/total-tahap2?tahun=${tahunAnggaran}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tahap2Data = data.data;
                    const totalTahap2Element = document.getElementById('totalTahap2');
                    const sisaTahap2Element = document.getElementById('sisaTahap2');
                    const percentTahap2Element = document.getElementById('percentTahap2');

                    if (totalTahap2Element) {
                        totalTahap2Element.textContent = 'Rp ' + formatNumber(tahap2Data.total_anggaran);
                    }
                    if (sisaTahap2Element) {
                        sisaTahap2Element.textContent = 'Rp ' + formatNumber(tahap2Data.sisa_anggaran);
                    }
                    if (percentTahap2Element) {
                        percentTahap2Element.textContent = Math.round(tahap2Data.persentase_terpakai) + '%';
                    }

                    const progressBar2 = document.querySelector('.col-lg-4:nth-child(3) .progress-bar');
                    if (progressBar2) {
                        setTimeout(() => {
                            progressBar2.style.width = tahap2Data.persentase_terpakai + '%';
                        }, 300);
                    }
                }
            })
            .catch(error => console.error('Error updating Tahap 2:', error));
    }

    // Setelah halaman dimuat, aktifkan tab yang sesuai
    document.addEventListener('DOMContentLoaded', function() {
        const savedTab = localStorage.getItem('activeRkasTab');
        if (savedTab) {
            setActiveTab(savedTab);
            localStorage.removeItem('activeRkasTab');
        }

        document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', function() {
                localStorage.setItem('activeRkasTab', this.getAttribute('data-month'));
            });
        });
    });
});

// Fungsi untuk refresh data bulan yang aktif
function refreshCurrentMonthData() {
    const activeTab = document.querySelector('#monthTabs .nav-link.active');
    if (activeTab) {
        const month = activeTab.getAttribute('data-month');
        refreshMonthData(month.toLowerCase());
    }
}