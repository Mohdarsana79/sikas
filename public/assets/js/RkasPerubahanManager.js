// RkasPerubahanManager.js
class RkasPerubahanManager {
    constructor() {
        this.currentRkasId = null;
        this.months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        this.isSearching = false;
        
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeComponents();
            this.setupEventListeners();
            this.forceJanuariTabOnLoad();
        });
    }

    initializeComponents() {
        this.initializeSelect2();
        this.initializeAnggaranList();
        this.updateTahapCards();
        this.initializeTabs();
        this.initializeRkasPerubahanSearch();
    }

    setupEventListeners() {
        this.setupFormSubmissions();
        this.setupModalEvents();
        this.setupTabEvents();
        this.setupDeleteButton();
        this.setupHargaSatuanEvents();
    }

    // ========== UTILITY FUNCTIONS ==========
    formatNumber(num) {
        if (!num) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    formatRupiah(angka) {
        if (typeof angka === 'string') {
            angka = angka.replace(/[^\d]/g, '');
        }
        const num = parseInt(angka);
        if (isNaN(num)) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    getActiveTab() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        return activeTab ? activeTab.getAttribute('data-month').toLowerCase() : 'januari';
    }

    setActiveTab(month) {
        if (!month) {
            month = 'Januari';
        }
        
        const tabButton = document.querySelector(`#monthTabs .nav-link[data-month="${month}"]`);
        if (tabButton && !tabButton.classList.contains('active')) {
            document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('#monthTabsContent .tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            tabButton.classList.add('active');
            
            const targetPane = document.getElementById(month.toLowerCase());
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
            
            console.log('🔧 [TAB DEBUG] Set active tab to:', month);
        }
    }

    // ========== TAB MANAGEMENT FUNCTIONS ==========
    initializeTabs() {
        console.log('🔧 [TAB DEBUG] Initializing tabs...');
        
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        
        if (!activeTab) {
            console.log('🔧 [TAB DEBUG] No active tab found, setting januari as default');
            this.setActiveTab('Januari');
        } else {
            console.log('🔧 [TAB DEBUG] Active tab found:', activeTab.getAttribute('data-month'));
        }
        
        this.syncTabContent();
    }

    syncTabContent() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const activeMonth = activeTab ? activeTab.getAttribute('data-month') : 'Januari';
        
        document.querySelectorAll('#monthTabsContent .tab-pane').forEach(pane => {
            if (pane.id === activeMonth.toLowerCase()) {
                pane.classList.add('show', 'active');
            } else {
                pane.classList.remove('show', 'active');
            }
        });
        
        console.log('🔧 [TAB DEBUG] Synced tab content for:', activeMonth);
    }

    // ========== SELECT2 FUNCTIONS ==========
    formatKegiatanOption(option) {
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

    formatKegiatanSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kode = $(element).data('kode') || '';
        const uraian = $(element).data('uraian') || '';
        return kode + ' - ' + uraian;
    }

    formatRekeningOption(option) {
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

    formatRekeningSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kodeRekening = $(element).data('kode-rekening') || '';
        const rincianObjek = $(element).data('rincian-objek') || '';
        return kodeRekening + ' - ' + rincianObjek;
    }

    initializeSelect2() {
        $('.select2-kegiatan').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#tambahRkasModal'),
            templateResult: this.formatKegiatanOption.bind(this),
            templateSelection: this.formatKegiatanSelection.bind(this),
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#tambahRkasModal'),
            templateResult: this.formatRekeningOption.bind(this),
            templateSelection: this.formatRekeningSelection.bind(this),
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-kegiatan-edit').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: this.formatKegiatanOption.bind(this),
            templateSelection: this.formatKegiatanSelection.bind(this),
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening-edit').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: this.formatRekeningOption.bind(this),
            templateSelection: this.formatRekeningSelection.bind(this),
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }

    // ========== MODAL TAMBAH FUNCTIONS ==========
    createAnggaranCard() {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        card.innerHTML = `
            <button type="button" class="delete-btn">
                <i class="bi bi-x"></i>
            </button>
            <div class="bulan-input-group">
                <select class="form-control bulan-input" name="bulan[]" required>
                    <option value="">Pilih Bulan</option>
                    ${this.months.map(b => `<option value="${b}">${b}</option>`).join('')}
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
            this.checkTambahButtonVisibility();
            this.updateTotalAnggaran();
        });

        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        
        jumlahInput.addEventListener('input', this.updateTotalAnggaran.bind(this));
        bulanSelect.addEventListener('change', this.updateTotalAnggaran.bind(this));

        return card;
    }

    createTambahButtonCard() {
        const btnCard = document.createElement('div');
        btnCard.className = 'btn-tambah-card';
        btnCard.innerHTML = `
            <button type="button" class="btn-tambah" id="btn-tambah-bulan">
                <i class="bi bi-plus-circle me-2"></i>Tambah Bulan
            </button>
        `;
        
        const tambahBtn = btnCard.querySelector('#btn-tambah-bulan');
        tambahBtn.addEventListener('click', this.handleTambahBulanClick.bind(this));
        
        return btnCard;
    }

    checkTambahButtonVisibility() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        if (cards.length >= 12) {
            if (tambahBtnCard) tambahBtnCard.style.display = 'none';
        } else {
            if (tambahBtnCard) tambahBtnCard.style.display = 'flex';
        }
    }

    initializeAnggaranList() {
        const container = document.getElementById('bulanContainer');
        if (!container) return;

        container.innerHTML = '';
        const initialCard = this.createAnggaranCard();
        const tambahBtnCard = this.createTambahButtonCard();
        container.appendChild(initialCard);
        container.appendChild(tambahBtnCard);
        
        this.checkTambahButtonVisibility();
        this.updateTotalAnggaran();
    }

    handleTambahBulanClick() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        if (cards.length >= 12) {
            Swal.fire('Peringatan', 'Maksimal 12 bulan dalam setahun', 'warning');
            return;
        }

        const container = document.getElementById('bulanContainer');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        const newCard = this.createAnggaranCard();
        
        container.insertBefore(newCard, tambahBtnCard);
        this.checkTambahButtonVisibility();
        this.updateTotalAnggaran();
    }

    updateTotalAnggaran() {
        let total = 0;
        const hargaSatuanInput = document.getElementById('harga_satuan');
        const hargaSatuan = hargaSatuanInput ? parseFloat(hargaSatuanInput.value.replace(/[^\d]/g, '')) || 0 : 0;

        document.querySelectorAll('.anggaran-bulan-card').forEach(function(card) {
            const jumlah = parseFloat(card.querySelector('.jumlah-input')?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + this.formatNumber(monthTotal);
            }
            total += monthTotal;
        }.bind(this));

        const totalDisplay = document.getElementById('total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(total);
        }
    }

    validateForm() {
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

    resetModal() {
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

        this.initializeAnggaranList();
    }

    // ========== MODAL EDIT FUNCTIONS ==========
    getEditHargaSatuanValue() {
        const hargaSatuanInput = document.getElementById('edit_harga_satuan');
        const hargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
        
        console.log('🔧 [HARGA DEBUG] Getting harga satuan value:', {
            display: hargaSatuanInput?.value,
            actual: hargaSatuanActual?.value,
            hasActual: !!hargaSatuanActual?.value
        });
        
        if (hargaSatuanActual && hargaSatuanActual.value) {
            const numericValue = parseFloat(hargaSatuanActual.value);
            console.log('🔧 [HARGA DEBUG] Using actual value:', numericValue);
            return isNaN(numericValue) ? 0 : numericValue;
        }
        
        if (!hargaSatuanInput || !hargaSatuanInput.value) {
            console.log('🔧 [HARGA DEBUG] No value found, returning 0');
            return 0;
        }
        
        const numericValue = parseFloat(hargaSatuanInput.value.replace(/[^\d]/g, ''));
        console.log('🔧 [HARGA DEBUG] Using display value:', numericValue);
        
        if ((!hargaSatuanActual || !hargaSatuanActual.value) && !isNaN(numericValue)) {
            if (hargaSatuanActual) {
                hargaSatuanActual.value = numericValue.toString();
                console.log('🔧 [HARGA DEBUG] Auto-filled hidden field:', hargaSatuanActual.value);
            }
        }
        
        return isNaN(numericValue) ? 0 : numericValue;
    }

    initializeEditAnggaranList(data = [], lockedMonths = [], allowedMonths = []) {
        const container = document.getElementById('edit_bulanContainer');
        if (!container) {
            console.error('❌ [EDIT DEBUG] Edit bulan container not found');
            return;
        }

        console.log('🔧 [EDIT DEBUG] Initializing edit bulan list with data:', data);
        
        const frontendLockedMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        const frontendAllowedMonths = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        console.log('🔧 [EDIT DEBUG] Frontend locked months:', frontendLockedMonths);
        console.log('🔧 [EDIT DEBUG] Frontend allowed months:', frontendAllowedMonths);
        
        container.innerHTML = '';
        
        if (data && data.length > 0) {
            console.log('🔧 [EDIT DEBUG] Creating cards for', data.length, 'months');
            data.forEach((item, index) => {
                const card = this.createEditAnggaranCard(item.bulan, item.jumlah, item.satuan, frontendLockedMonths, frontendAllowedMonths);
                container.appendChild(card);
                console.log('🔧 [EDIT DEBUG] Created card for bulan:', item.bulan);
            });
        } else {
            console.log('🔧 [EDIT DEBUG] No month data, creating default card');
            const initialCard = this.createEditAnggaranCard('', '', '', frontendLockedMonths, frontendAllowedMonths);
            container.appendChild(initialCard);
        }
        
        const tambahBtnCard = this.createEditTambahButtonCard(frontendLockedMonths, frontendAllowedMonths);
        container.appendChild(tambahBtnCard);
        
        console.log('🔧 [EDIT DEBUG] Total cards created:', container.querySelectorAll('.anggaran-bulan-card').length);
        
        setTimeout(this.updateEditTotalAnggaran.bind(this), 300);
    }

    createEditAnggaranCard(bulan = '', jumlah = '', satuan = '', lockedMonths = [], allowedMonths = []) {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        
        const isLockedMonth = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'].includes(bulan);
        const isDisabled = isLockedMonth;
        
        card.innerHTML = `
            <button type="button" class="delete-btn" ${isDisabled ? 'disabled style="display:none;"' : ''} title="${isDisabled ? 'Bulan terkunci - tidak dapat dihapus' : 'Hapus bulan'}">
                <i class="bi bi-x"></i>
            </button>
            <div class="bulan-input-group">
                <select class="form-control bulan-input" name="bulan[]" ${isDisabled ? 'disabled' : 'required'} style="${isDisabled ? 'background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;' : ''}">
                    <option value="">Pilih Bulan</option>
                    ${['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'].map(b => 
                        `<option value="${b}" ${b === bulan ? 'selected' : ''} ${['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'].includes(b) ? 'disabled style="background-color: #f8f9fa; color: #6c757d;"' : ''}>${b} ${['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'].includes(b) ? '🔒' : ''}</option>`
                    ).join('')}
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
            ${isLockedMonth ? '<div class="lock-indicator"><small class="text-muted"><i class="bi bi-lock"></i> Bulan terkunci (Januari-Juni) - tidak dapat dihapus</small></div>' : ''}
        `;
        
        if (!isLockedMonth) {
            const deleteBtn = card.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card:not(.locked-card)');
                    if (cards.length > 1) {
                        card.remove();
                        this.updateEditTotalAnggaran();
                        this.checkEditTambahButtonVisibility(lockedMonths, allowedMonths);
                    } else {
                        Swal.fire('Peringatan', 'Minimal harus ada satu bulan yang aktif', 'warning');
                    }
                }.bind(this));
            }
        }

        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        
        if (jumlahInput && !isLockedMonth) {
            jumlahInput.addEventListener('input', this.updateEditTotalAnggaran.bind(this));
        }
        if (bulanSelect && !isLockedMonth) {
            bulanSelect.addEventListener('change', this.updateEditTotalAnggaran.bind(this));
        }

        return card;
    }

    createEditTambahButtonCard(lockedMonths = [], allowedMonths = []) {
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
            this.handleEditTambahBulan(lockedMonths, allowedMonths);
        }.bind(this));
        
        return btnCard;
    }

    handleEditTambahBulan(lockedMonths = [], allowedMonths = []) {
        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        const activeCards = Array.from(cards).filter(card => {
            const select = card.querySelector('.bulan-input');
            return select && !select.disabled;
        });
        
        if (activeCards.length >= 12) {
            Swal.fire('Peringatan', 'Maksimal 12 bulan dalam setahun', 'warning');
            return;
        }

        const container = document.getElementById('edit_bulanContainer');
        const tambahBtnCard = document.querySelector('#edit_bulanContainer .btn-tambah-card');
        const newCard = this.createEditAnggaranCard('', '', '', lockedMonths, allowedMonths);
        
        if (container && tambahBtnCard) {
            container.insertBefore(newCard, tambahBtnCard);
            setTimeout(this.updateEditTotalAnggaran.bind(this), 50);
            this.checkEditTambahButtonVisibility(lockedMonths, allowedMonths);
        }
    }

    checkEditTambahButtonVisibility(lockedMonths = [], allowedMonths = []) {
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

    updateEditTotalAnggaran() {
        let total = 0;
        const hargaSatuan = this.getEditHargaSatuanValue();

        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        cards.forEach(function(card) {
            const jumlahInput = card.querySelector('.jumlah-input');
            const jumlah = parseInt(jumlahInput?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + this.formatNumber(monthTotal);
            }
            total += monthTotal;
        }.bind(this));

        const totalDisplay = document.getElementById('edit_total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(total);
        }
    }

    validateEditForm() {
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

        const hargaSatuanValue = this.getEditHargaSatuanValue();
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

        const bulanInputs = document.querySelectorAll('#edit_bulanContainer select[name="bulan[]"]');
        const jumlahInputs = document.querySelectorAll('#edit_bulanContainer input[name="jumlah[]"]');
        const satuanInputs = document.querySelectorAll('#edit_bulanContainer input[name="satuan[]"]');

        console.log('🔧 [VALIDATION DEBUG] All inputs before filtering:', {
            bulan: Array.from(bulanInputs).map(input => input.value),
            jumlah: Array.from(jumlahInputs).map(input => input.value),
            satuan: Array.from(satuanInputs).map(input => input.value)
        });

        const filledBulanInputs = Array.from(bulanInputs).filter(input => input.value !== '');
        const filledJumlahInputs = Array.from(jumlahInputs).filter((input, index) => {
            const correspondingBulan = bulanInputs[index];
            return correspondingBulan && correspondingBulan.value !== '';
        });
        const filledSatuanInputs = Array.from(satuanInputs).filter((input, index) => {
            const correspondingBulan = bulanInputs[index];
            return correspondingBulan && correspondingBulan.value !== '';
        });

        console.log('🔧 [VALIDATION DEBUG] After filtering empty months:', {
            filledBulan: filledBulanInputs.map(input => input.value),
            filledJumlah: filledJumlahInputs.map(input => input.value),
            filledSatuan: filledSatuanInputs.map(input => input.value),
            filledBulanCount: filledBulanInputs.length,
            filledJumlahCount: filledJumlahInputs.length,
            filledSatuanCount: filledSatuanInputs.length
        });

        if (filledBulanInputs.length !== filledJumlahInputs.length || filledBulanInputs.length !== filledSatuanInputs.length) {
            isValid = false;
            errorMessages.push('Data tidak konsisten: untuk setiap bulan yang dipilih, jumlah dan satuan harus diisi');
            console.error('❌ [VALIDATION DEBUG] Inconsistent filled data detected');
            
            filledBulanInputs.forEach(input => input.classList.add('is-invalid'));
            filledJumlahInputs.forEach(input => input.classList.add('is-invalid'));
            filledSatuanInputs.forEach(input => input.classList.add('is-invalid'));
        }

        const selectedMonths = [];
        
        filledBulanInputs.forEach(function(select) {
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

        filledJumlahInputs.forEach(function(input, index) {
            if (input && !input.readOnly) {
                input.classList.remove('is-invalid');
                const value = parseInt(input.value);
                if (isNaN(value) || value <= 0) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    const bulanValue = filledBulanInputs[index] ? filledBulanInputs[index].value : 'unknown';
                    errorMessages.push(`Jumlah harus lebih dari 0 untuk bulan ${bulanValue}`);
                }
            }
        });

        filledSatuanInputs.forEach(function(input, index) {
            if (input && !input.readOnly) {
                input.classList.remove('is-invalid');
                if (!input.value || !input.value.toString().trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    const bulanValue = filledBulanInputs[index] ? filledBulanInputs[index].value : 'unknown';
                    errorMessages.push(`Satuan harus diisi untuk bulan ${bulanValue}`);
                }
            }
        });

        if (filledBulanInputs.length === 0) {
            isValid = false;
            errorMessages.push('Minimal harus ada satu bulan aktif (Juli-Desember) yang diisi');
        }

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
    setupDeleteButton() {
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
                
                const activeCards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card:not([style*="display: none"])');
                const hasActiveMonths = Array.from(activeCards).some(card => {
                    const bulanSelect = card.querySelector('.bulan-input');
                    return bulanSelect && !bulanSelect.disabled;
                });
                
                if (!hasActiveMonths) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ada Data yang Dapat Dihapus',
                        html: `
                            <div class="text-start">
                                <p>Data hanya tersedia untuk bulan <strong>Januari-Juni</strong> yang tidak dapat dihapus.</p>
                                <div class="alert alert-info mt-2">
                                    <small>
                                        <i class="bi bi-info-circle me-1"></i>
                                        Hanya data bulan <strong>Juli-Desember</strong> yang dapat dihapus.
                                    </small>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Mengerti'
                    });
                    return;
                }
                
                this.showDeleteAllConfirmation(mainDataId);
            }.bind(this));
        } else {
            console.error('❌ [DELETE DEBUG] Delete button not found');
        }
    }

    showDeleteAllConfirmation(mainDataId) {
        console.log('🔧 [DELETE DEBUG] Showing confirmation for ID:', mainDataId);
        
        Swal.fire({
            title: 'Hapus Semua Data?',
            html: `
                <div class="text-start">
                    <p>Anda yakin ingin menghapus <strong>SEMUA DATA</strong> untuk kegiatan ini?</p>
                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Perhatian:</strong> Tindakan ini hanya akan menghapus data untuk bulan <strong>Juli-Desember</strong>.
                            <br>Data untuk bulan <strong>Januari-Juni</strong> akan tetap tersimpan dan tidak dapat dihapus.
                        </small>
                    </div>
                    <div class="alert alert-info mt-2">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Info:</strong> Hanya data perubahan (Juli-Desember) yang dapat dihapus.
                        </small>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Data Juli-Desember!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('🔧 [DELETE DEBUG] User confirmed deletion');
                this.deleteAllData(mainDataId);
            } else {
                console.log('🔧 [DELETE DEBUG] User cancelled deletion');
            }
        });
    }

    deleteAllData(mainDataId) {
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
                return response.json().then(errorData => {
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('🔧 [DELETE DEBUG] Delete response:', data);
            if (data.success) {
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editRkasModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `
                        <div class="text-start">
                            <p>${data.message}</p>
                            <div class="alert alert-success mt-2">
                                <small>
                                    <i class="bi bi-check-circle me-1"></i>
                                    <strong>Berhasil:</strong> ${data.deleted_count} data bulan Juli-Desember telah dihapus.
                                    <br>Data bulan Januari-Juni tetap tersimpan.
                                </small>
                            </div>
                        </div>
                    `,
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
            
            let errorMessage = 'Terjadi kesalahan saat menghapus data: ' + error.message;
            let errorIcon = 'error';
            
            if (error.message.includes('tidak ada data yang dapat dihapus') || 
                error.message.includes('Januari-Juni')) {
                errorMessage = `
                    <div class="text-start">
                        <p><strong>Tidak ada data yang dapat dihapus</strong></p>
                        <p>${error.message}</p>
                        <div class="alert alert-info mt-2">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Hanya data bulan <strong>Juli-Desember</strong> yang dapat dihapus.
                                Data bulan <strong>Januari-Juni</strong> terkunci dan tidak dapat dihapus.
                            </small>
                        </div>
                    </div>
                `;
                errorIcon = 'info';
            }
            
            Swal.fire({
                icon: errorIcon,
                title: 'Hapus Gagal',
                html: errorMessage,
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
    showEditModal(id) {
        console.log('🔧 [EDIT DEBUG] Opening edit modal for ID:', id);
        
        if (!id) {
            console.error('❌ [EDIT DEBUG] Invalid ID provided');
            Swal.fire('Error', 'ID data tidak valid', 'error');
            return;
        }

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
                this.populateEditForm(data.data);
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
    }

    populateEditForm(data) {
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

        editForm.reset();

        const editKegiatan = document.getElementById('edit_kegiatan');
        const editRekening = document.getElementById('edit_rekening_belanja');
        const editUraian = document.getElementById('edit_uraian');
        const editHargaSatuan = document.getElementById('edit_harga_satuan');
        const editHargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
        const editMainDataId = document.getElementById('edit_main_data_id');
        
        if (editKegiatan) editKegiatan.value = data.kode_id;
        if (editRekening) editRekening.value = data.kode_rekening_id;
        if (editUraian) editUraian.value = data.uraian;
        
        if (editMainDataId) {
            editMainDataId.value = data.id;
            console.log('🔧 [EDIT DEBUG] Set main data ID for delete:', data.id);
        }
        
        if (editHargaSatuan && data.harga_satuan_raw) {
            const hargaSatuanNumeric = parseFloat(data.harga_satuan_raw);
            if (!isNaN(hargaSatuanNumeric)) {
                const formattedValue = this.formatRupiah(hargaSatuanNumeric.toString());
                editHargaSatuan.value = formattedValue;
                
                if (editHargaSatuanActual) {
                    editHargaSatuanActual.value = hargaSatuanNumeric.toString();
                    console.log('🔧 [HARGA DEBUG] Initialized hidden field with:', editHargaSatuanActual.value);
                }
                console.log('🔧 [HARGA DEBUG] Initialized display with:', formattedValue);
            } else {
                const rawValue = data.harga_satuan_raw.toString().replace(/[^\d]/g, '');
                editHargaSatuan.value = this.formatRupiah(rawValue);
                if (editHargaSatuanActual) {
                    editHargaSatuanActual.value = rawValue;
                    console.log('🔧 [HARGA DEBUG] Initialized hidden field with raw:', editHargaSatuanActual.value);
                }
            }
        } else {
            console.warn('🔧 [HARGA DEBUG] No harga_satuan_raw data available');
        }
        
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
        
        console.log('🔧 [EDIT DEBUG] Initializing MULTI-MONTH data with locked months info');
        this.initializeEditAnggaranList(data.bulan_data, data.locked_months, data.allowed_months);
        
        editForm.action = `/rkas-perubahan/${data.id}`;
        console.log('🔧 [EDIT DEBUG] Form action set to:', editForm.action);
        
        const editModalElement = document.getElementById('editRkasModal');
        if (editModalElement) {
            const editModal = new bootstrap.Modal(editModalElement);
            editModal.show();
            console.log('🔧 [EDIT DEBUG] Edit modal shown successfully');
            
            setTimeout(this.updateEditTotalAnggaran.bind(this), 500);
        } else {
            console.error('❌ [EDIT DEBUG] Edit modal element not found');
            Swal.fire('Error', 'Modal edit tidak ditemukan', 'error');
        }

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

    showDetailModal(id) {
        console.log('🔧 [DETAIL DEBUG] Opening detail modal for ID:', id);
        
        this.currentRkasId = id;

        Swal.fire({
            title: 'Memuat detail...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/rkas-perubahan/show/${id}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            console.log('🔧 [DETAIL DEBUG] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('🔧 [DETAIL DEBUG] API Response:', data);
            Swal.close();
            
            if (data.success && data.data) {
                console.log('🔧 [DETAIL DEBUG] Data loaded successfully:', data.data);
                this.populateDetailForm(data.data);
            } else {
                throw new Error(data.message || 'Gagal memuat detail data');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('❌ [DETAIL DEBUG] Error fetching detail data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error Memuat Detail',
                text: 'Terjadi kesalahan saat memuat data detail: ' + error.message,
                confirmButtonText: 'Mengerti'
            });
        });
    }

    populateDetailForm(data) {
        console.log('🔧 [DETAIL DEBUG] Populating detail form with data:', data);
        console.log('🔧 [DETAIL DEBUG] Month data received:', data.bulan_data);
        
        try {
            this.setElementText('detail_program_kegiatan', data.program_kegiatan || '-');
            this.setElementText('detail_kegiatan', data.kegiatan || '-');
            this.setElementText('detail_rekening_belanja', data.rekening_belanja || '-');
            this.setElementText('detail_uraian', data.uraian || '-');
            this.setElementText('detail_harga_satuan', data.harga_satuan || 'Rp 0');
            this.setElementText('detail_total_anggaran', data.total_anggaran || 'Rp 0');
            this.setElementText('detail_total_anggaran_display', data.total_anggaran || 'Rp 0');

            this.initializeDetailAnggaranList(data.bulan_data, data.harga_satuan_raw || 0);
            
            const detailModalElement = document.getElementById('detailRkasModal');
            if (detailModalElement) {
                const detailModal = new bootstrap.Modal(detailModalElement);
                detailModal.show();
                console.log('🔧 [DETAIL DEBUG] Detail modal shown successfully');
            } else {
                console.error('❌ [DETAIL DEBUG] Detail modal element not found');
                Swal.fire('Error', 'Modal detail tidak ditemukan', 'error');
                return;
            }
        } catch (error) {
            console.error('❌ [DETAIL DEBUG] Error populating detail form:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error Memuat Detail',
                text: 'Terjadi kesalahan saat mengisi data detail: ' + error.message,
                confirmButtonText: 'Mengerti'
            });
        }
    }

    setElementText(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = text;
        } else {
            console.warn(`❌ [DETAIL DEBUG] Element with id '${elementId}' not found`);
        }
    }

    initializeDetailAnggaranList(data = [], hargaSatuan = 0) {
        const container = document.getElementById('detail_bulanContainer');
        if (!container) {
            console.error('❌ [DETAIL DEBUG] Detail bulan container not found');
            return;
        }

        console.log('🔧 [DETAIL DEBUG] Initializing detail bulan list with data:', data);
        console.log('🔧 [DETAIL DEBUG] Harga satuan:', hargaSatuan);
        
        container.innerHTML = '';
        
        let totalAnggaran = 0;

        if (data && Array.isArray(data) && data.length > 0) {
            console.log('🔧 [DETAIL DEBUG] Creating detail cards for', data.length, 'months');
            data.forEach((item, index) => {
                try {
                    const card = this.createDetailAnggaranCard(item.bulan, item.jumlah, item.satuan, hargaSatuan);
                    if (card) {
                        container.appendChild(card);
                        console.log('🔧 [DETAIL DEBUG] Created detail card for bulan:', item.bulan);
                        
                        const itemTotal = item.jumlah * hargaSatuan;
                        totalAnggaran += itemTotal;
                    }
                } catch (error) {
                    console.error('❌ [DETAIL DEBUG] Error creating detail card for item:', item, error);
                }
            });
        } else {
            console.log('🔧 [DETAIL DEBUG] No month data for detail');
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-2">Tidak ada data bulan</p>
                </div>
            `;
        }

        const totalDisplay = document.getElementById('detail_total_anggaran_display');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(totalAnggaran);
        }

        console.log('🔧 [DETAIL DEBUG] Total cards created:', container.querySelectorAll('.detail-bulan-card').length);
        console.log('🔧 [DETAIL DEBUG] Total anggaran:', totalAnggaran);
    }

    createDetailAnggaranCard(bulan = '', jumlah = '', satuan = '', hargaSatuan = 0) {
        const card = document.createElement('div');
        card.className = 'detail-bulan-card';
        
        const total = jumlah * hargaSatuan;
        
        card.innerHTML = `
            <div class="bulan-input-group">
                <div class="bulan-display">${bulan || '-'}</div>
                <span class="month-total">Rp ${this.formatNumber(total)}</span>
            </div>
            <div class="jumlah-satuan-group">
                <div>
                    <small class="text-muted">Jumlah</small>
                    <div class="input-display">${jumlah || '0'}</div>
                </div>
                <div>
                    <small class="text-muted">Satuan</small>
                    <div class="input-display">${satuan || '-'}</div>
                </div>
            </div>
        `;

        return card;
    }

    showSisipkanModal(kodeId, program, kegiatan, rekeningId, rekeningDisplay) {
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
    }

    showTahapDetail(tahap) {
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
    }

    editFromDetail() {
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailRkasModal'));
        detailModal.hide();
        setTimeout(() => this.showEditModal(this.currentRkasId), 300);
    }

    // ========== EVENT LISTENER SETUP ==========
    setupFormSubmissions() {
        const tambahForm = document.getElementById('tambahRkasForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', (e) => {
                if (!this.validateForm()) {
                    e.preventDefault();
                    return false;
                }
                const submitBtn = tambahForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
                    submitBtn.disabled = true;
                }
            });
        }

        const editForm = document.getElementById('editRkasForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                console.log('🔧 [UPDATE DEBUG] Starting form submission...');
                
                if (!this.validateEditForm()) {
                    console.error('❌ [UPDATE DEBUG] Form validation failed');
                    return false;
                }
                
                const allBulanInputs = Array.from(document.querySelectorAll('#edit_bulanContainer select[name="bulan[]"]'));
                const allJumlahInputs = Array.from(document.querySelectorAll('#edit_bulanContainer input[name="jumlah[]"]'));
                const allSatuanInputs = Array.from(document.querySelectorAll('#edit_bulanContainer input[name="satuan[]"]'));
                
                const filledData = allBulanInputs
                    .map((bulanInput, index) => {
                        if (bulanInput.value !== '') {
                            return {
                                bulan: bulanInput.value,
                                jumlah: allJumlahInputs[index] ? allJumlahInputs[index].value : '',
                                satuan: allSatuanInputs[index] ? allSatuanInputs[index].value : ''
                            };
                        }
                        return null;
                    })
                    .filter(item => item !== null);
                
                console.log('🔧 [SUBMIT DEBUG] Filtered form data (only filled months):', {
                    filledData: filledData,
                    filledCount: filledData.length
                });
                
                const hargaSatuanInput = document.getElementById('edit_harga_satuan');
                const hargaSatuanActual = document.getElementById('edit_harga_satuan_actual');
                
                console.log('🔧 [UPDATE DEBUG] Harga satuan elements:', {
                    display: hargaSatuanInput?.value,
                    actual: hargaSatuanActual?.value
                });
                
                const formData = {
                    bulan: filledData.map(item => item.bulan),
                    jumlah: filledData.map(item => item.jumlah),
                    satuan: filledData.map(item => item.satuan),
                    kode_id: document.getElementById('edit_kegiatan')?.value,
                    kode_rekening_id: document.getElementById('edit_rekening_belanja')?.value,
                    uraian: document.getElementById('edit_uraian')?.value,
                    harga_satuan: hargaSatuanActual?.value || document.querySelector('#editRkasForm input[name="harga_satuan"]')?.value
                };
                
                console.log('🔧 [UPDATE DEBUG] Final form data to be sent:', formData);
                
                const hargaSatuanValue = this.getEditHargaSatuanValue();
                console.log('🔧 [UPDATE DEBUG] Final harga satuan validation:', {
                    value: hargaSatuanValue,
                    isValid: hargaSatuanValue > 0
                });

                if (!hargaSatuanValue || hargaSatuanValue <= 0 || isNaN(hargaSatuanValue)) {
                    console.error('❌ [UPDATE DEBUG] Harga satuan invalid:', hargaSatuanValue);
                    
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
                
                if (formData.bulan.length === 0) {
                    console.error('❌ [UPDATE DEBUG] No filled months detected');
                    Swal.fire('Error', 'Minimal harus ada satu bulan yang diisi.', 'error');
                    return false;
                }
                
                const emptyMonths = formData.bulan.filter(month => !month);
                if (emptyMonths.length > 0) {
                    console.error('❌ [UPDATE DEBUG] Empty months detected in filtered data:', emptyMonths);
                    Swal.fire('Error', 'Ada bulan yang belum dipilih. Silakan pilih bulan untuk semua card.', 'error');
                    return false;
                }
                
                const submitBtn = editForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Mengupdate...';
                    submitBtn.disabled = true;
                }
                
                const formDataObj = new FormData(editForm);
                
                formDataObj.delete('bulan[]');
                formDataObj.delete('jumlah[]');
                formDataObj.delete('satuan[]');
                
                formData.bulan.forEach(bulan => {
                    formDataObj.append('bulan[]', bulan);
                });
                formData.jumlah.forEach(jumlah => {
                    formDataObj.append('jumlah[]', jumlah);
                });
                formData.satuan.forEach(satuan => {
                    formDataObj.append('satuan[]', satuan);
                });
                
                if (!formDataObj.has('harga_satuan')) {
                    console.log('🔧 [UPDATE DEBUG] harga_satuan missing in FormData, adding manually');
                    if (formData.harga_satuan) {
                        formDataObj.append('harga_satuan', formData.harga_satuan);
                        console.log('🔧 [UPDATE DEBUG] Added harga_satuan to FormData:', formData.harga_satuan);
                    } else {
                        const actualValue = document.getElementById('edit_harga_satuan_actual')?.value;
                        if (actualValue) {
                            formDataObj.append('harga_satuan', actualValue);
                            console.log('🔧 [UPDATE DEBUG] Added harga_satuan from hidden field:', actualValue);
                        } else {
                            const displayValue = document.getElementById('edit_harga_satuan')?.value.replace(/[^\d]/g, '');
                            if (displayValue) {
                                formDataObj.append('harga_satuan', displayValue);
                                console.log('🔧 [UPDATE DEBUG] Added harga_satuan from display:', displayValue);
                            }
                        }
                    }
                }
                
                console.log('🔧 [UPDATE DEBUG] Final FormData content:');
                for (let [key, value] of formDataObj.entries()) {
                    console.log(`  ${key}:`, value);
                }
                
                fetch(editForm.action, {
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
        }

        const sisipkanForm = document.getElementById('sisipkanRkasForm');
        if (sisipkanForm) {
            sisipkanForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const form = sisipkanForm;
                const submitButton = form.querySelector('button[type="submit"]');
                const modal = bootstrap.Modal.getInstance(document.getElementById('sisipkanRkasModal'));
                const currentMonth = this.getActiveTab();

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
        }
    }

    setupModalEvents() {
        document.getElementById('tambahRkasModal')?.addEventListener('hidden.bs.modal', function() {
            this.resetModal();
        }.bind(this));

        document.getElementById('btn-tambah-bulan')?.addEventListener('click', this.handleTambahBulanClick.bind(this));
    }

    setupTabEvents() {
        // Handle tab click events
        document.querySelectorAll('.nav-link[data-month]').forEach((tab) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const month = tab.getAttribute('data-month');
                console.log('🔧 [TAB DEBUG] Tab clicked:', month);
                
                this.setActiveTab(month);
                this.refreshMonthData(month.toLowerCase());
            });
        });

        // Handle Bootstrap tab shown events - simpler approach
        const monthTabs = document.getElementById('monthTabs');
        if (monthTabs) {
            monthTabs.addEventListener('shown.bs.tab', (e) => {
                const activeTab = e.target;
                const month = activeTab.getAttribute('data-month');
                
                if (month) {
                    console.log('🔧 [TAB DEBUG] Tab shown via Bootstrap event:', month);
                    this.resetRkasPerubahanSearch();
                    
                    setTimeout(() => {
                        this.refreshMonthData(month.toLowerCase());
                    }, 100);
                }
            });
        }
    }

    setupHargaSatuanEvents() {
        const editHargaSatuanInput = document.getElementById('edit_harga_satuan');
        const editHargaSatuanActual = document.getElementById('edit_harga_satuan_actual');

        if (editHargaSatuanInput) {
            console.log('🔧 [HARGA DEBUG] Initializing harga satuan event listeners');
            
            let isEditFormatting = false;

            function updateHiddenHargaSatuan(value) {
                if (editHargaSatuanActual) {
                    const numericValue = value.replace(/[^\d]/g, '');
                    editHargaSatuanActual.value = numericValue;
                    console.log('🔧 [HARGA DEBUG] Updated hidden field:', numericValue);
                }
            }

            editHargaSatuanInput.addEventListener('input', function(e) {
                if (isEditFormatting) return;
                
                isEditFormatting = true;
                
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [HARGA DEBUG] Input event, raw value:', value);
                
                if (value) {
                    const formattedValue = this.formatRupiah(value);
                    input.value = formattedValue;
                    
                    updateHiddenHargaSatuan(value);
                } else {
                    input.value = '';
                    updateHiddenHargaSatuan('');
                }
                
                isEditFormatting = false;
                setTimeout(this.updateEditTotalAnggaran.bind(this), 50);
            }.bind(this));

            editHargaSatuanInput.addEventListener('blur', function(e) {
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [HARGA DEBUG] Blur event, raw value:', value);
                
                if (value) {
                    const formattedValue = this.formatRupiah(value);
                    input.value = formattedValue;
                    
                    updateHiddenHargaSatuan(value);
                } else {
                    updateHiddenHargaSatuan('');
                }
                this.updateEditTotalAnggaran();
            }.bind(this));

            editHargaSatuanInput.addEventListener('focus', function(e) {
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [HARGA DEBUG] Focus event, raw value:', value);
                
                if (value) {
                    input.value = value;
                }
            });

            editHargaSatuanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = e.target.value.replace(/[^\d]/g, '');
                    if (value) {
                        const formattedValue = this.formatRupiah(value);
                        e.target.value = formattedValue;
                        updateHiddenHargaSatuan(value);
                    }
                    this.updateEditTotalAnggaran();
                }
            }.bind(this));
        }
    }

    // ========== RKAS SEARCH FUNCTIONS ==========
    initializeRkasPerubahanSearch() {
        const searchForm = document.getElementById('SearchForm');
        const searchInput = document.getElementById('SearchInput');
        
        if (!searchForm || !searchInput) {
            console.warn('⚠️ [RKAS Perubahan SEARCH] Search elements not found');
            return;
        }

        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.trim();
            
            if (searchTerm.length === 0) {
                if (this.isSearching) {
                    this.showRkasPerubahanSearchLoading(false);
                    this.resetRkasPerubahanSearch();
                    this.isSearching = false;
                }
                return;
            }
            
            if (searchTerm.length < 2) {
                if (this.isSearching) {
                    this.showRkasPerubahanSearchLoading(false);
                    this.isSearching = false;
                }
                return;
            }
            
            searchTimeout = setTimeout(() => {
                this.isSearching = true;
                this.showRkasPerubahanSearchLoading(true);
                this.performRkasPerubahanSearch(searchTerm);
            }, 500);
        });

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            if (searchTerm.length >= 2) {
                this.isSearching = true;
                this.showRkasPerubahanSearchLoading(true);
                this.performRkasPerubahanSearch(searchTerm);
            }
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.resetRkasPerubahanSearch();
                searchInput.value = '';
                searchInput.blur();
                this.isSearching = false;
            }
        });

        const clearButton = document.querySelector('.search-clear');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                this.resetRkasPerubahanSearch();
                this.isSearching = false;
            });
        }
        
        console.log('✅ [RKAS Perubahan SEARCH] Initialized successfully');
    }

    showRkasPerubahanSearchLoading(show) {
        const searchInput = document.getElementById('SearchInput');
        const loadingElement = document.querySelector('.search-loading');
        const searchIcon = document.querySelector('.search-icon');
        
        if (loadingElement) {
            loadingElement.classList.toggle('d-none', !show);
        }
        
        if (searchInput) {
            if (show) {
                searchInput.setAttribute('readonly', 'readonly');
                searchInput.style.backgroundColor = '#f8f9fa';
                searchInput.style.opacity = '0.7';
                searchInput.style.cursor = 'not-allowed';
            } else {
                searchInput.removeAttribute('readonly');
                searchInput.style.backgroundColor = '';
                searchInput.style.opacity = '';
                searchInput.style.cursor = '';
            }
        }
        
        if (searchIcon) {
            if (show) {
                searchIcon.style.opacity = '0.5';
            } else {
                searchIcon.style.opacity = '';
            }
        }
        
        const searchForm = document.getElementById('SearchForm');
        if (searchForm) {
            searchForm.classList.toggle('search-loading', show);
        }
    }

    showTableLoading(month, show) {
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        
        if (!tableBody) return;
        
        if (show) {
            if (!tableBody.hasAttribute('data-original-content')) {
                tableBody.setAttribute('data-original-content', tableBody.innerHTML);
            }
            
            tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-4">
                        <div class="search-table-loading">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="text-muted">Mencari data...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    performRkasPerubahanSearch(searchTerm) {
        console.log('🔍 [RKAS Perubahan SEARCH] Searching for:', searchTerm);
        
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const currentMonth = activeTab ? activeTab.getAttribute('data-month') : 'januari';
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        
        if (!tahun) {
            console.error('❌ [RKAS SEARCH] Tahun tidak ditemukan');
            this.showRkasPerubahanSearchLoading(false);
            this.showRkasPerubahanSearchError('Tahun anggaran tidak ditemukan');
            return;
        }

        this.showTableLoading(currentMonth, true);

        const params = new URLSearchParams({
            search: searchTerm,
            bulan: currentMonth,
            tahun: tahun
        });

        fetch(`/rkas-perubahan/search?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        })
        .then(data => {
            this.showRkasPerubahanSearchLoading(false);
            if (data.success) {
                this.handleRkasPerubahanSearchResponse(data, searchTerm, currentMonth);
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat mencari data');
            }
        })
        .catch(error => {
            console.error('❌ [RKAS Perubahan SEARCH] Error:', error);
            this.showRkasPerubahanSearchLoading(false);
            this.showTableLoading(currentMonth, false);
            this.resetRkasPerubahanSearch();
            this.showRkasPerubahanSearchError('Terjadi kesalahan saat mencari data: ' + error.message);
        });
    }

    handleRkasPerubahanSearchResponse(data, searchTerm, currentMonth) {
        const tableBody = document.getElementById(`table-body-${currentMonth.toLowerCase()}`);
        
        if (!tableBody) {
            this.showRkasPerubahanSearchError(`Tabel untuk bulan ${currentMonth} tidak ditemukan`);
            return;
        }

        if (!tableBody.hasAttribute('data-original-content')) {
            tableBody.setAttribute('data-original-content', tableBody.innerHTML);
        }

        if (data.data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan data RKAS Perubahan dengan kata kunci: "<strong>${this.escapeHtml(searchTerm)}</strong>" di bulan ${currentMonth}
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            let total = 0;

            data.data.forEach((item, index) => {
                const itemTotal = parseFloat(item.total.replace(/[^\d]/g, ''));
                total += itemTotal;

                html += `
                <tr class="search-result-row">
                    <td>${index + 1}</td>
                    <td>${this.escapeHtml(item.program || '-')}</td>
                    <td>${this.escapeHtml(item.sub_program || '-')}</td>
                    <td>${this.escapeHtml(item.rincian_objek || '-')}</td>
                    <td>${this.escapeHtml(item.uraian || '-')}</td>
                    <td class="text-end">${item.dianggarkan || '0'}</td>
                    <td class="text-end">${item.dibelanjakan || '0'}</td>
                    <td>${this.escapeHtml(item.satuan || '-')}</td>
                    <td class="text-end">${item.harga_satuan || '0'}</td>
                    <td class="text-end"><strong>${item.total || '0'}</strong></td>
                    <td class="text-center">${item.actions || ''}</td>
                </tr>
                `;
            });

            html += `
            <tr class="table-info">
                <td colspan="9" class="text-end"><strong>Total ${currentMonth}:</strong></td>
                <td class="text-end"><strong>Rp ${this.formatNumber(total)}</strong></td>
                <td></td>
            </tr>
            `;

            tableBody.innerHTML = html;
        }

        this.showRkasPerubahanSearchInfo(data.data.length, searchTerm, currentMonth);
        this.reattachRkasPerubahanEventListeners();
    }

    showRkasPerubahanSearchInfo(count, searchTerm, month) {
        this.removeRkasPerubahanSearchInfo();

        if (count === 0) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Tidak ditemukan data dengan kata kunci: "${searchTerm}"`,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#f8f9fa',
                iconColor: '#6c757d'
            });
        } else {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Ditemukan ${count} data di bulan ${month}`,
                text: `Kata kunci: "${searchTerm}"`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#d1e7dd',
                iconColor: '#198754'
            });
        }
    }

    showRkasPerubahanSearchError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error Pencarian',
            text: message,
            confirmButtonText: 'Mengerti'
        });
    }

    removeRkasPerubahanSearchInfo() {
        // Tidak perlu implementasi khusus untuk sekarang
    }

    resetRkasPerubahanSearch() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const currentMonth = activeTab ? activeTab.getAttribute('data-month').toLowerCase() : 'januari';
        const tableBody = document.getElementById(`table-body-${currentMonth}`);
        
        this.showRkasPerubahanSearchLoading(false);
        
        if (tableBody && tableBody.hasAttribute('data-original-content')) {
            tableBody.innerHTML = tableBody.getAttribute('data-original-content');
            tableBody.removeAttribute('data-original-content');
        }
        
        this.removeRkasPerubahanSearchInfo();
        this.reattachRkasPerubahanEventListeners();
        
        console.log('🔍 [RKAS SEARCH] Search reset');
    }

    reattachRkasPerubahanEventListeners() {
        document.dispatchEvent(new CustomEvent('rkasSearchReset'));
    }

    // ========== OTHER FUNCTIONS ==========
    refreshMonthData(month) {
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
                    this.populateTable(month, data.data);
                } else {
                    this.showNoDataMessage(month);
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

    populateTable(month, items) {
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
                                <a class="dropdown-item" href="#" onclick="rkasManager.showDetailModal(${item.id})">
                                    <i class="bi bi-eye me-2"></i>Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="rkasManager.showSisipkanModal(
                                    ${item.kode_id},
                                    '${this.escapeHtml(item.program_kegiatan)}',
                                    '${this.escapeHtml(item.kegiatan)}',
                                    ${item.kode_rekening_id},
                                    '${this.escapeHtml(item.rekening_belanja)}'
                                )">
                                    <i class="bi bi-archive-fill me-2 text-warning"></i>Sisipkan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="rkasManager.showEditModal(${item.id})">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            `;
        });

        html += `
        <tr class="table-info">
            <td colspan="9" class="text-end"><strong>Total ${month}:</strong></td>
            <td><strong>Rp ${this.formatNumber(total)}</strong></td>
            <td></td>
        </tr>
        `;

        tableBody.innerHTML = html;
    }

    showNoDataMessage(month) {
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

    updateTahapCards() {
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
                        totalTahap1Element.textContent = 'Rp ' + this.formatNumber(tahap1Data.total_anggaran);
                    }
                    if (sisaTahap1Element) {
                        sisaTahap1Element.textContent = 'Rp ' + this.formatNumber(tahap1Data.sisa_anggaran);
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
                        totalTahap2Element.textContent = 'Rp ' + this.formatNumber(tahap2Data.total_anggaran);
                    }
                    if (sisaTahap2Element) {
                        sisaTahap2Element.textContent = 'Rp ' + this.formatNumber(tahap2Data.sisa_anggaran);
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

    refreshCurrentMonthData() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        if (activeTab) {
            const month = activeTab.getAttribute('data-month');
            console.log('🔧 [TAB DEBUG] Refreshing data for:', month);
            this.refreshMonthData(month.toLowerCase());
        }
    }

    forceJanuariTabOnLoad() {
        console.log('🔧 [TAB DEBUG] Forcing januari tab on load...');
        
        setTimeout(() => {
            document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('#monthTabsContent .tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            const januariTab = document.querySelector('#monthTabs .nav-link[data-month="Januari"]');
            const januariPane = document.getElementById('januari');
            
            if (januariTab && januariPane) {
                januariTab.classList.add('active');
                januariPane.classList.add('show', 'active');
                
                console.log('🔧 [TAB DEBUG] Successfully forced januari tab');
                
                this.refreshMonthData('januari');
            } else {
                console.error('❌ [TAB DEBUG] Januari tab or pane not found');
            }
        }, 500);
    }
}

// Initialize the manager
const rkasManager = new RkasPerubahanManager();

// Expose methods to global scope for onclick attributes
window.showEditModal = (id) => rkasManager.showEditModal(id);
window.showDetailModal = (id) => rkasManager.showDetailModal(id);
window.showSisipkanModal = (kodeId, program, kegiatan, rekeningId, rekeningDisplay) => 
    rkasManager.showSisipkanModal(kodeId, program, kegiatan, rekeningId, rekeningDisplay);
window.showTahapDetail = (tahap) => rkasManager.showTahapDetail(tahap);
window.editFromDetail = () => rkasManager.editFromDetail();