
/**
 * RKAS Manager Class
 * Mengelola semua fungsi dan logika untuk modul RKAS
 */
class RkasManager {
    constructor() {
        this.currentRkasId = null;
        this.monthIndex = 1;
        this.months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        this.firstSatuanValue = '';
        this.isSearching = false;
        
        console.log('=== RKAS MANAGER INITIALIZED ===');
    }

    // ========== UTILITY FUNCTIONS ==========

    /**
     * Format number dengan separator ribuan
     */
    formatNumber(num) {
        if (!num && num !== 0) return '0';
        
        // Pastikan num adalah number
        const numberValue = typeof num === 'string' ? 
            parseFloat(num.replace(/[^\d]/g, '')) : 
            parseFloat(num);
        
        if (isNaN(numberValue)) return '0';
        
        // Format tanpa desimal
        return numberValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    /**
     * Format angka ke format Rupiah
     */
    formatRupiah(angka) {
        if (typeof angka === 'string') {
            angka = angka.replace(/[^\d]/g, '');
        }
        const num = parseInt(angka);
        if (isNaN(num)) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    /**
     * Escape HTML untuk mencegah XSS
     */
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Get active tab
     */
    getActiveTab() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        return activeTab ? activeTab.getAttribute('data-month').toLowerCase() : 'januari';
    }

    /**
     * Set active tab
     */
    setActiveTab(month) {
        if (!month) {
            month = 'januari';
        }
        
        const tabButton = document.querySelector(`#monthTabs .nav-link[data-month="${month}"]`);
        if (tabButton && !tabButton.classList.contains('active')) {
            // Remove active class from all tabs
            document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab panes
            document.querySelectorAll('#monthTabsContent .tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Add active class to target tab
            tabButton.classList.add('active');
            
            // Add active class to target tab pane
            const targetPane = document.getElementById(month.toLowerCase());
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
            
            console.log('🔧 [TAB DEBUG] Set active tab to:', month);
        }
    }

    // ========== SELECT2 FUNCTIONS ==========

    /**
     * Initialize Select2 components
     */
    initializeSelect2() {
        if (typeof $ === 'undefined') {
            console.warn('jQuery not loaded, skipping Select2 initialization');
            return;
        }

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

    /**
     * Format option untuk kegiatan
     */
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

    /**
     * Format selection untuk kegiatan
     */
    formatKegiatanSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kode = $(element).data('kode') || '';
        const uraian = $(element).data('uraian') || '';
        return kode + ' - ' + uraian;
    }

    /**
     * Format option untuk rekening
     */
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

    /**
     * Format selection untuk rekening
     */
    formatRekeningSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kodeRekening = $(element).data('kode-rekening') || '';
        const rincianObjek = $(element).data('rincian-objek') || '';
        return kodeRekening + ' - ' + rincianObjek;
    }

    // ========== TAB MANAGEMENT FUNCTIONS ==========

    /**
     * Initialize tabs dengan handling reload
     */
    initializeTabs() {
        console.log('🔧 [TAB DEBUG] Initializing tabs...');
        
        // Hapus semua active class terlebih dahulu
        document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        
        document.querySelectorAll('#monthTabsContent .tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Cek localStorage untuk tab yang disimpan
        const savedTab = localStorage.getItem('activeRkasTab');
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        
        if (savedTab && savedTab !== 'Januari') {
            console.log('🔧 [TAB DEBUG] Found saved tab in localStorage:', savedTab);
            // Set tab dari localStorage
            this.setActiveTab(savedTab);
        } else if (activeTab) {
            console.log('🔧 [TAB DEBUG] Active tab found in DOM:', activeTab.getAttribute('data-month'));
            // Biarkan Bootstrap handle active tab
            this.syncTabContent();
        } else {
            console.log('🔧 [TAB DEBUG] No active tab found, setting januari as default');
            // Force set Januari sebagai default
            this.setActiveTab('Januari');
        }
        
        // Reset localStorage setelah digunakan
        localStorage.removeItem('activeRkasTab');
    }

    /**
     * Sync tab content dengan active tab - PERBAIKAN: handle duplikasi
     */
    syncTabContent() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const activeMonth = activeTab ? activeTab.getAttribute('data-month') : 'Januari';
        
        console.log('🔧 [TAB DEBUG] Syncing tab content for:', activeMonth);
        
        // Hapus semua active class terlebih dahulu
        document.querySelectorAll('#monthTabsContent .tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Set hanya tab pane yang sesuai dengan active tab
        const targetPane = document.getElementById(activeMonth.toLowerCase());
        if (targetPane) {
            targetPane.classList.add('show', 'active');
            console.log('🔧 [TAB DEBUG] Successfully synced tab pane for:', activeMonth);
        } else {
            console.error('🔧 [TAB DEBUG] Target pane not found for:', activeMonth);
        }
    }

    // ========== SATUAN OTOMATIS FUNCTIONS ==========

    /**
     * Update satuan otomatis untuk form tambah
     */
    updateSatuanOtomatis(satuanValue) {
        console.log('🔧 [SATUAN DEBUG] Updating satuan otomatis:', satuanValue);
        
        this.firstSatuanValue = satuanValue;
        
        const allSatuanInputs = document.querySelectorAll('#bulanContainer .satuan-input');
        let firstCardProcessed = false;
        
        allSatuanInputs.forEach((input, index) => {
            if (!firstCardProcessed) {
                // Card pertama tetap editable
                input.readOnly = false;
                firstCardProcessed = true;
            } else {
                // Card selanjutnya di-set otomatis dan readonly
                input.value = satuanValue;
                input.readOnly = true;
            }
        });
    }

    /**
     * Reset satuan otomatis ketika card dihapus
     */
    resetSatuanOtomatis() {
        const allSatuanInputs = document.querySelectorAll('#bulanContainer .satuan-input');
        if (allSatuanInputs.length > 0) {
            const firstSatuanValue = allSatuanInputs[0].value;
            if (firstSatuanValue) {
                this.updateSatuanOtomatis(firstSatuanValue);
            }
        }
    }

    // ========== MODAL TAMBAH FUNCTIONS ==========

    /**
     * Create anggaran card
     */
    createAnggaranCard() {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';

        const isFirstCard = document.querySelectorAll('#bulanContainer .anggaran-bulan-card').length === 0;
        const shouldBeReadonly = !isFirstCard && this.firstSatuanValue;

        card.innerHTML = `
            <button type="button" class="delete-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash"><path d="M3 6h18"/><path d="M19 6v14c0 1.1-0.9 2-2 2H7c-1.1 0-2-0.9-2-2V6"/><path d="M8 6V4c0-1.1 0.9-2 2-2h4c1.1 0 2 0.9 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            </button>
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                <select class="form-control bulan-input" name="bulan[]" required>
                    <option value="">Pilih Bulan</option>
                    ${this.months.map(b => `<option value="${b}">${b}</option>`).join('')}
                </select>
                <span class="uang-display month-total" style="flex: 1; text-align: right; font-weight: 500;">Rp 0</span>
            </div>
            <div class="jumlah-satuan-group form-row">
                <div class="form-col">
                    <div class="input-icon-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <input type="number" class="form-control jumlah-input" name="jumlah[]" placeholder="Jumlah" min="1" required>
                    </div>
                </div>
                <div class="form-col">
                    <input type="text" class="form-control satuan-input" name="satuan[]" placeholder="Satuan"  value="${shouldBeReadonly ? this.firstSatuanValue : ''}" 
                ${shouldBeReadonly ? 'readonly' : ''} required>
                </div>
            </div>
        `;
        
        const deleteBtn = card.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', () => {
            card.remove();
            this.checkTambahButtonVisibility();
            this.updateTotalAnggaran();
            this.resetSatuanOtomatis(); // Reset satuan setelah hapus card
        });

        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        const satuanInput = card.querySelector('.satuan-input');
        
        jumlahInput.addEventListener('input', this.updateTotalAnggaran.bind(this));
        bulanSelect.addEventListener('change', this.updateTotalAnggaran.bind(this));

        // Event listener untuk sync satuan (hanya untuk card pertama)
        if (isFirstCard) {
            satuanInput.addEventListener('input', (e) => {
                this.updateSatuanOtomatis(e.target.value);
            });
            
            satuanInput.addEventListener('change', (e) => {
                this.updateSatuanOtomatis(e.target.value);
            });
        }

        return card;
    }

    /**
     * Create tambah button card
     */
    createTambahButtonCard() {
        const btnCard = document.createElement('div');
        btnCard.className = 'btn-tambah-card';
        btnCard.innerHTML = `
            <button type="button" class="btn-tambah" id="btn-tambah-bulan">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Tambah Bulan
            </button>
        `;
        
        const tambahBtn = btnCard.querySelector('#btn-tambah-bulan');
        tambahBtn.addEventListener('click', this.handleTambahBulanClick.bind(this));
        
        return btnCard;
    }

    /**
     * Check tambah button visibility
     */
    checkTambahButtonVisibility() {
        const cards = document.querySelectorAll('#bulanContainer .anggaran-bulan-card');
        const tambahBtnCard = document.querySelector('#bulanContainer .btn-tambah-card');
        if (tambahBtnCard) {
            if (cards.length >= 12) {
                tambahBtnCard.style.display = 'none';
            } else {
                tambahBtnCard.style.display = 'flex';
            }
        }
    }

    /**
     * Update total anggaran untuk form tambah
     */
    updateTotalAnggaran() {
        let total = 0;
        const hargaSatuan = this.getTambahHargaSatuanValue();

        document.querySelectorAll('#bulanContainer .anggaran-bulan-card').forEach((card) => {
            const jumlah = parseFloat(card.querySelector('.jumlah-input')?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + this.formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        const totalDisplay = document.getElementById('total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(total);
        }
    }

    /**
     * Initialize anggaran list untuk form tambah
     */
    initializeAnggaranList() {
        const container = document.getElementById('bulanContainer');
        if (!container) return;

        // Reset container dan nilai satuan
        container.innerHTML = '';
        this.firstSatuanValue = '';
        
        // Tambah card pertama
        const initialCard = this.createAnggaranCard();
        const tambahBtnCard = this.createTambahButtonCard();
        
        container.appendChild(initialCard);
        container.appendChild(tambahBtnCard);
        
        this.checkTambahButtonVisibility();
        this.updateTotalAnggaran();
    }

    /**
     * Handle tambah bulan click
     */
    handleTambahBulanClick() {
        const cards = document.querySelectorAll('#bulanContainer .anggaran-bulan-card');
        if (cards.length >= 12) {
            Swal.fire('Peringatan', 'Maksimal 12 bulan dalam setahun', 'warning');
            return;
        }

        const container = document.getElementById('bulanContainer');
        const tambahBtnCard = document.querySelector('#bulanContainer .btn-tambah-card');
        const newCard = this.createAnggaranCard();
        
        container.insertBefore(newCard, tambahBtnCard);
        this.checkTambahButtonVisibility();
        this.updateTotalAnggaran();
        
        // Update satuan otomatis untuk card baru
        if (this.firstSatuanValue) {
            this.updateSatuanOtomatis(this.firstSatuanValue);
        }
    }

    /**
     * Update total anggaran untuk form tambah
     */
    updateTotalAnggaran() {
        let total = 0;
        const hargaSatuan = this.getTambahHargaSatuanValue();

        console.log('🔧 [TOTAL DEBUG] Harga satuan numeric:', hargaSatuan);

        document.querySelectorAll('#bulanContainer .anggaran-bulan-card').forEach((card, index) => {
            const jumlahInput = card.querySelector('.jumlah-input');
            const jumlah = parseFloat(jumlahInput?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            console.log(`🔧 [TOTAL DEBUG] Card ${index + 1}:`, {
                jumlah: jumlah,
                monthTotal: monthTotal
            });

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + this.formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        console.log('🔧 [TOTAL DEBUG] Total keseluruhan:', total);

        const totalDisplay = document.getElementById('total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(total);
            console.log('🔧 [TOTAL DEBUG] Display text:', totalDisplay.textContent);
        }
    }

    /**
     * Update satuan otomatis
     */
    updateSatuanOtomatis(satuanValue) {
        this.firstSatuanValue = satuanValue;
        
        const allSatuanInputs = document.querySelectorAll('.satuan-input');
        allSatuanInputs.forEach((input, index) => {
            if (index > 0 && satuanValue) {
                input.value = satuanValue;
                input.readOnly = true;
            }
        });
    }

    /**
     * Validate form
     */
    validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll(
            '#tambahRkasForm input[required], #tambahRkasForm select[required], #tambahRkasForm textarea[required]'
        );

        requiredFields.forEach((field) => {
            field.classList.remove('is-invalid');
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        // Validate month uniqueness
        const selectedMonths = [];
        const monthSelects = document.querySelectorAll('.bulan-input');

        monthSelects.forEach((select) => {
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

    /**
     * Reset modal
     */
    resetModal() {
        this.firstSatuanValue = ''; // Reset nilai satuan

        const form = document.getElementById('tambahRkasForm');
        if (form) form.reset();

        if (typeof $ !== 'undefined') {
            $('.select2-kegiatan').val(null).trigger('change');
            $('.select2-rekening').val(null).trigger('change');
        }

        document.querySelectorAll('.form-control').forEach((field) => {
            field.classList.remove('is-invalid');
        });

        const submitBtn = document.querySelector('#tambahRkasForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
            submitBtn.disabled = false;
        }

        // Reset semua satuan input ke editable
        document.querySelectorAll('#bulanContainer .satuan-input').forEach(input => {
            input.readOnly = false;
        });

        this.initializeAnggaranList(); // Re-initialize list
    }

    // ========== EDIT MODAL FUNCTIONS ==========

    /**
     * Get edit harga satuan value
     */
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

    /**
     * Initialize edit anggaran list
     */
    initializeEditAnggaranList(data = []) {
        const container = document.getElementById('edit_bulanContainer');
        if (!container) {
            console.error('❌ [EDIT DEBUG] Edit bulan container not found');
            return;
        }

        console.log('🔧 [EDIT DEBUG] Initializing edit bulan list with data:', data);
        
        try {
            container.innerHTML = '';
        } catch (error) {
            console.error('❌ [EDIT DEBUG] Error clearing container:', error);
            return;
        }
        
        let isFirstCard = true;
        
        if (data && Array.isArray(data) && data.length > 0) {
            console.log('🔧 [EDIT DEBUG] Creating cards for', data.length, 'months');
            data.forEach((item, index) => {
                try {
                    const card = this.createEditAnggaranCard(item.bulan, item.jumlah, item.satuan);
                    if (card) {
                        container.appendChild(card);
                        console.log('🔧 [EDIT DEBUG] Created card for bulan:', item.bulan);
                        
                        if (isFirstCard && item.satuan) {
                            setTimeout(() => {
                                this.updateEditSatuanOtomatis(item.satuan);
                            }, 100);
                            isFirstCard = false;
                        }
                    }
                } catch (error) {
                    console.error('❌ [EDIT DEBUG] Error creating card for item:', item, error);
                }
            });
        } else {
            console.log('🔧 [EDIT DEBUG] No month data, creating default card');
            try {
                const initialCard = this.createEditAnggaranCard('', '', '');
                if (initialCard) {
                    container.appendChild(initialCard);
                }
            } catch (error) {
                console.error('❌ [EDIT DEBUG] Error creating initial card:', error);
            }
        }
        
        try {
            const tambahBtnCard = this.createEditTambahButtonCard();
            if (tambahBtnCard) {
                container.appendChild(tambahBtnCard);
            }
        } catch (error) {
            console.error('❌ [EDIT DEBUG] Error creating tambah button card:', error);
        }
        
        console.log('🔧 [EDIT DEBUG] Total cards created:', container.querySelectorAll('.anggaran-bulan-card').length);
        
        setTimeout(this.updateEditTotalAnggaran.bind(this), 300);
    }

    /**
     * Create edit anggaran card
     */
    createEditAnggaranCard(bulan = '', jumlah = '', satuan = '') {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        
        const isFirstCard = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card').length === 0;
        const shouldBeReadonly = !isFirstCard && document.querySelector('#edit_bulanContainer .satuan-input')?.value;
        
        card.innerHTML = `
            <button type="button" class="delete-btn" title="Hapus bulan">
                <i class="bi bi-x"></i>
            </button>
            <div class="bulan-input-group">
                <select class="form-control bulan-input" name="bulan[]" required>
                    <option value="">Pilih Bulan</option>
                    ${this.months.map(b => `<option value="${b}" ${b === bulan ? 'selected' : ''}>${b}</option>`).join('')}
                </select>
                <span class="month-total">Rp 0</span>
            </div>
            <div class="jumlah-satuan-group">
                <div class="input-icon-left">
                    <i class="bi bi-hash"></i>
                    <input type="number" class="form-control jumlah-input" name="jumlah[]" 
                        placeholder="Jumlah" min="1" value="${jumlah || ''}" required>
                </div>
                <input type="text" class="form-control satuan-input" name="satuan[]" 
                    placeholder="Satuan" value="${satuan || ''}" ${shouldBeReadonly ? 'readonly' : ''}>
            </div>
        `;
        
        const deleteBtn = card.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
                if (cards.length > 1) {
                    card.remove();
                    this.updateEditTotalAnggaran();
                    this.checkEditTambahButtonVisibility();
                    this.resetEditSatuanOtomatis();
                } else {
                    Swal.fire('Peringatan', 'Minimal harus ada satu bulan yang aktif', 'warning');
                }
            }.bind(this));
        }

        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        const satuanInput = card.querySelector('.satuan-input');
        
        if (jumlahInput) {
            jumlahInput.addEventListener('input', this.updateEditTotalAnggaran.bind(this));
        }
        if (bulanSelect) {
            bulanSelect.addEventListener('change', this.updateEditTotalAnggaran.bind(this));
        }
        
        if (satuanInput && isFirstCard) {
            satuanInput.addEventListener('input', function(e) {
                this.updateEditSatuanOtomatis(e.target.value);
            }.bind(this));
        }

        return card;
    }

    /**
     * Update edit satuan otomatis
     */
    updateEditSatuanOtomatis(satuanValue) {
        console.log('🔧 [SATUAN DEBUG] Updating satuan otomatis:', satuanValue);
        
        if (!satuanValue) return;
        
        const allSatuanInputs = document.querySelectorAll('#edit_bulanContainer .satuan-input');
        let firstCardProcessed = false;
        
        allSatuanInputs.forEach((input, index) => {
            if (!firstCardProcessed) {
                input.readOnly = false;
                firstCardProcessed = true;
            } else {
                input.value = satuanValue;
                input.readOnly = true;
            }
        });
    }

    /**
     * Reset edit satuan otomatis
     */
    resetEditSatuanOtomatis() {
        const allSatuanInputs = document.querySelectorAll('#edit_bulanContainer .satuan-input');
        if (allSatuanInputs.length > 0) {
            const firstSatuanValue = allSatuanInputs[0].value;
            if (firstSatuanValue) {
                this.updateEditSatuanOtomatis(firstSatuanValue);
            }
        }
    }

    /**
     * Create edit tambah button card
     */
    createEditTambahButtonCard() {
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
            this.handleEditTambahBulan();
        }.bind(this));
        
        return btnCard;
    }

    /**
     * Handle edit tambah bulan
     */
    handleEditTambahBulan() {
        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        
        if (cards.length >= 12) {
            Swal.fire('Peringatan', 'Maksimal 12 bulan dalam setahun', 'warning');
            return;
        }

        const container = document.getElementById('edit_bulanContainer');
        const tambahBtnCard = document.querySelector('#edit_bulanContainer .btn-tambah-card');
        const newCard = this.createEditAnggaranCard('', '', '');
        
        if (container && tambahBtnCard) {
            container.insertBefore(newCard, tambahBtnCard);
            setTimeout(this.updateEditTotalAnggaran.bind(this), 50);
            this.checkEditTambahButtonVisibility();
            
            const firstSatuanInput = document.querySelector('#edit_bulanContainer .satuan-input');
            if (firstSatuanInput && firstSatuanInput.value) {
                this.updateEditSatuanOtomatis(firstSatuanInput.value);
            }
        }
    }

    /**
     * Check edit tambah button visibility
     */
    checkEditTambahButtonVisibility() {
        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        if (tambahBtnCard) {
            if (cards.length >= 12) {
                tambahBtnCard.style.display = 'none';
            } else {
                tambahBtnCard.style.display = 'flex';
            }
        }
    }

    /**
     * Update edit total anggaran
     */
    updateEditTotalAnggaran() {
        let total = 0;
        const hargaSatuan = this.getEditHargaSatuanValue();

        const cards = document.querySelectorAll('#edit_bulanContainer .anggaran-bulan-card');
        cards.forEach((card) => {
            const jumlahInput = card.querySelector('.jumlah-input');
            const jumlah = parseInt(jumlahInput?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + this.formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        const totalDisplay = document.getElementById('edit_total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(total);
        }
    }

    /**
     * Validate edit form
     */
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

        // Validasi harga satuan
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

        const selectedMonths = [];
        const monthSelects = document.querySelectorAll('#edit_bulanContainer .bulan-input');

        if (monthSelects.length === 0) {
            isValid = false;
            errorMessages.push('Minimal harus ada satu bulan yang diisi');
        }

        // PERBAIKAN: gunakan arrow function
        monthSelects.forEach((select) => {
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
        // PERBAIKAN: gunakan arrow function
        jumlahInputs.forEach((input) => {
            if (input) {
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
        // PERBAIKAN: gunakan arrow function
        satuanInputs.forEach((input) => {
            if (input) {
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

    // ========== TAMBAH DATA FUNCTIONS ==========

    /**
     * Setup tambah form submission dengan AJAX
     */
    setupTambahFormSubmission() {
        const tambahForm = document.getElementById('tambahRkasForm');
        if (tambahForm) {
            tambahForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                console.log('🔧 [TAMBAH DEBUG] Starting form submission...');
                
                if (!this.validateTambahForm()) {
                    console.error('❌ [TAMBAH DEBUG] Form validation failed');
                    return false;
                }
                
                const bulanInputs = Array.from(document.querySelectorAll('#bulanContainer select[name="bulan[]"]'));
                const jumlahInputs = Array.from(document.querySelectorAll('#bulanContainer input[name="jumlah[]"]'));
                const satuanInputs = Array.from(document.querySelectorAll('#bulanContainer input[name="satuan[]"]'));
                
                const hargaSatuanInput = document.getElementById('harga_satuan');
                const hargaSatuanActual = document.getElementById('harga_satuan_actual');
                
                console.log('🔧 [TAMBAH DEBUG] Harga satuan elements:', {
                    display: hargaSatuanInput?.value,
                    actual: hargaSatuanActual?.value
                });
                
                const formData = {
                    bulan: bulanInputs.map(input => input.value),
                    jumlah: jumlahInputs.map(input => input.value),
                    satuan: satuanInputs.map(input => input.value),
                    kode_id: document.getElementById('kegiatan')?.value,
                    kode_rekening_id: document.getElementById('rekening_belanja')?.value,
                    uraian: document.getElementById('uraian')?.value,
                    harga_satuan: hargaSatuanActual?.value || document.querySelector('#tambahRkasForm input[name="harga_satuan"]')?.value,
                    tahun_anggaran: document.querySelector('#tambahRkasForm input[name="tahun_anggaran"]')?.value
                };
                
                console.log('🔧 [TAMBAH DEBUG] Form data to be sent:', formData);
                
                const hargaSatuanValue = this.getTambahHargaSatuanValue();
                console.log('🔧 [TAMBAH DEBUG] Final harga satuan validation:', {
                    value: hargaSatuanValue,
                    isValid: hargaSatuanValue > 0
                });

                if (!hargaSatuanValue || hargaSatuanValue <= 0 || isNaN(hargaSatuanValue)) {
                    console.error('❌ [TAMBAH DEBUG] Harga satuan invalid:', hargaSatuanValue);
                    
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
                    console.error('❌ [TAMBAH DEBUG] Empty months detected:', emptyMonths);
                    Swal.fire('Error', 'Ada bulan yang belum dipilih. Silakan pilih bulan untuk semua card.', 'error');
                    return false;
                }
                
                const submitBtn = tambahForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
                    submitBtn.disabled = true;
                }
                
                const formDataObj = new FormData(tambahForm);
                
                if (!formDataObj.has('harga_satuan')) {
                    console.log('🔧 [TAMBAH DEBUG] harga_satuan missing in FormData, adding manually');
                    if (formData.harga_satuan) {
                        formDataObj.append('harga_satuan', formData.harga_satuan);
                        console.log('🔧 [TAMBAH DEBUG] Added harga_satuan to FormData:', formData.harga_satuan);
                    } else {
                        const actualValue = document.getElementById('harga_satuan_actual')?.value;
                        if (actualValue) {
                            formDataObj.append('harga_satuan', actualValue);
                            console.log('🔧 [TAMBAH DEBUG] Added harga_satuan from hidden field:', actualValue);
                        } else {
                            const displayValue = document.getElementById('harga_satuan')?.value.replace(/[^\d]/g, '');
                            if (displayValue) {
                                formDataObj.append('harga_satuan', displayValue);
                                console.log('🔧 [TAMBAH DEBUG] Added harga_satuan from display:', displayValue);
                            }
                        }
                    }
                } else {
                    console.log('🔧 [TAMBAH DEBUG] harga_satuan already in FormData');
                }
                
                console.log('🔧 [TAMBAH DEBUG] Actual FormData content:');
                for (let [key, value] of formDataObj.entries()) {
                    console.log(`  ${key}:`, value);
                }
                
                fetch(tambahForm.action, {
                    method: 'POST',
                    body: formDataObj,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    console.log('🔧 [TAMBAH DEBUG] Response status:', response.status);
                    const responseText = await response.text();
                    console.log('🔧 [TAMBAH DEBUG] Response text:', responseText);
                    
                    try {
                        return JSON.parse(responseText);
                    } catch (e) {
                        throw new Error(`Invalid JSON response: ${responseText}`);
                    }
                })
                .then(data => {
                    console.log('🔧 [TAMBAH DEBUG] Tambah response:', data);
                    if (data.success) {
                        // Tutup modal tambah
                        const tambahModal = bootstrap.Modal.getInstance(document.getElementById('tambahRkasModal'));
                        if (tambahModal) {
                            tambahModal.hide();
                        }
                        
                        // Update UI tanpa reload
                        this.handleTambahSuccess(data, formData);
                        
                    } else {
                        let errorMessage = data.message || 'Gagal menambah data';
                        if (data.errors) {
                            errorMessage += '\n\n' + Object.values(data.errors).flat().join('\n');
                        }
                        throw new Error(errorMessage);
                    }
                })
                .catch(error => {
                    console.error('❌ [TAMBAH DEBUG] Error adding data:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Tambah Gagal',
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
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
                        submitBtn.disabled = false;
                    }
                });
            });
        }
    }

    /**
     * Get tambah harga satuan value
     */
    getTambahHargaSatuanValue() {
        const hargaSatuanInput = document.getElementById('harga_satuan');
        const hargaSatuanActual = document.getElementById('harga_satuan_actual');
        
        console.log('🔧 [TAMBAH HARGA DEBUG] Getting harga satuan value:', {
            display: hargaSatuanInput?.value,
            actual: hargaSatuanActual?.value,
            hasActual: !!hargaSatuanActual?.value
        });
        
        // Prioritaskan nilai dari hidden field
        if (hargaSatuanActual && hargaSatuanActual.value) {
            const numericValue = parseFloat(hargaSatuanActual.value);
            console.log('🔧 [TAMBAH HARGA DEBUG] Using actual value:', numericValue);
            return isNaN(numericValue) ? 0 : numericValue;
        }
        
        // Jika hidden field kosong, ambil dari input display
        if (!hargaSatuanInput || !hargaSatuanInput.value) {
            console.log('🔧 [TAMBAH HARGA DEBUG] No value found, returning 0');
            return 0;
        }
        
        // Konversi dari format rupiah ke numeric
        const numericValue = parseFloat(hargaSatuanInput.value.replace(/[^\d]/g, ''));
        console.log('🔧 [TAMBAH HARGA DEBUG] Using display value:', numericValue);
        
        // Update hidden field jika perlu
        if ((!hargaSatuanActual || !hargaSatuanActual.value) && !isNaN(numericValue)) {
            if (hargaSatuanActual) {
                hargaSatuanActual.value = numericValue.toString();
                console.log('🔧 [TAMBAH HARGA DEBUG] Auto-filled hidden field:', hargaSatuanActual.value);
            }
        }
        
        return isNaN(numericValue) ? 0 : numericValue;
    }

    /**
     * Handle successful tambah tanpa reload
     */
    handleTambahSuccess(data, formData) {
        console.log('🔧 [TAMBAH DEBUG] Handling tambah success:', data);
        
        // Tambah data baru ke tab yang sesuai
        this.addNewTambahData(formData);
        
        // Refresh data semua tab
        this.refreshAllTabsData();
        
        // Update tahap cards
        this.updateTahapCards();
        
        // Reset form
        this.resetModal();
        
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: data.message,
            confirmButtonText: 'OK'
        });
    }

    /**
     * Add new tambah data to appropriate tabs
     */
    addNewTambahData(formData) {
        console.log('🔧 [TAMBAH DEBUG] Adding new data:', formData);
        
        // Dapatkan informasi kegiatan dan rekening dari form
        const kegiatanSelect = document.getElementById('kegiatan');
        const rekeningSelect = document.getElementById('rekening_belanja');
        
        if (!kegiatanSelect || !rekeningSelect) {
            console.error('❌ [TAMBAH DEBUG] Select elements not found');
            return;
        }
        
        const selectedKegiatan = kegiatanSelect.options[kegiatanSelect.selectedIndex];
        const selectedRekening = rekeningSelect.options[rekeningSelect.selectedIndex];
        
        const programKegiatan = selectedKegiatan.getAttribute('data-program') || '-';
        const kegiatan = selectedKegiatan.getAttribute('data-sub-program') || '-';
        const rekeningBelanja = selectedRekening.getAttribute('data-rincian-objek') || '-';
        const hargaSatuan = this.formatRupiah(formData.harga_satuan);
        
        // Untuk setiap bulan yang ditambah, tambahkan ke tab yang sesuai
        formData.bulan.forEach((bulan, index) => {
            const jumlah = formData.jumlah[index];
            const satuan = formData.satuan[index];
            const total = jumlah * formData.harga_satuan;
            
            this.addDataToMonthTab(bulan, {
                programKegiatan,
                kegiatan,
                rekeningBelanja,
                uraian: formData.uraian,
                jumlah,
                satuan,
                hargaSatuan,
                total,
                id: this.generateTempId() // ID sementara untuk operasi UI
            });
        });
    }

    /**
     * Validate tambah form
     */
    validateTambahForm() {
        let isValid = true;
        const errorMessages = [];

        document.querySelectorAll('#tambahRkasForm .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });

        const requiredFields = [
            { element: document.getElementById('kegiatan'), name: 'Kegiatan' },
            { element: document.getElementById('rekening_belanja'), name: 'Rekening Belanja' },
            { element: document.getElementById('uraian'), name: 'Uraian' }
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

        // Validasi harga satuan
        const hargaSatuanValue = this.getTambahHargaSatuanValue();
        const hargaSatuanInput = document.getElementById('harga_satuan');
        const hargaSatuanActual = document.getElementById('harga_satuan_actual');
        
        console.log('🔧 [TAMBAH VALIDATION DEBUG] Harga satuan validation:', {
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
        const monthSelects = document.querySelectorAll('#bulanContainer .bulan-input');

        if (monthSelects.length === 0) {
            isValid = false;
            errorMessages.push('Minimal harus ada satu bulan yang diisi');
        }

        monthSelects.forEach((select) => {
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

        const jumlahInputs = document.querySelectorAll('#bulanContainer .jumlah-input');
        jumlahInputs.forEach((input) => {
            if (input) {
                input.classList.remove('is-invalid');
                const value = parseInt(input.value);
                if (isNaN(value) || value <= 0) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    errorMessages.push('Jumlah harus lebih dari 0 untuk semua bulan');
                }
            }
        });

        const satuanInputs = document.querySelectorAll('#bulanContainer .satuan-input');
        satuanInputs.forEach((input) => {
            if (input) {
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

    /**
     * Setup tambah harga satuan formatting
     */
    setupTambahHargaSatuanFormatting() {
        const tambahHargaSatuanInput = document.getElementById('harga_satuan');
        const tambahHargaSatuanActual = document.getElementById('harga_satuan_actual');

        if (tambahHargaSatuanInput && tambahHargaSatuanActual) {
            console.log('🔧 [TAMBAH HARGA DEBUG] Initializing tambah harga satuan event listeners');
            
            let isTambahFormatting = false;

            const updateHiddenHargaSatuan = (value) => {
                if (tambahHargaSatuanActual) {
                    const numericValue = value.replace(/[^\d]/g, '');
                    tambahHargaSatuanActual.value = numericValue;
                    console.log('🔧 [TAMBAH HARGA DEBUG] Updated hidden field:', numericValue);
                }
            };

            tambahHargaSatuanInput.addEventListener('input', (e) => {
                if (isTambahFormatting) return;
                
                isTambahFormatting = true;
                
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [TAMBAH HARGA DEBUG] Input event, raw value:', value);
                
                if (value) {
                    const formattedValue = this.formatRupiah(value);
                    input.value = formattedValue;
                    
                    updateHiddenHargaSatuan(value);
                } else {
                    input.value = '';
                    updateHiddenHargaSatuan('');
                }
                
                isTambahFormatting = false;
                setTimeout(() => this.updateTotalAnggaran(), 50);
            });

            tambahHargaSatuanInput.addEventListener('blur', (e) => {
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [TAMBAH HARGA DEBUG] Blur event, raw value:', value);
                
                if (value) {
                    const formattedValue = this.formatRupiah(value);
                    input.value = formattedValue;
                    
                    updateHiddenHargaSatuan(value);
                } else {
                    updateHiddenHargaSatuan('');
                }
                this.updateTotalAnggaran();
            });

            tambahHargaSatuanInput.addEventListener('focus', (e) => {
                const input = e.target;
                let value = input.value.replace(/[^\d]/g, '');
                
                console.log('🔧 [TAMBAH HARGA DEBUG] Focus event, raw value:', value);
                
                if (value) {
                    input.value = value; // Tampilkan tanpa format saat focus
                }
            });

            tambahHargaSatuanInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = e.target.value.replace(/[^\d]/g, '');
                    if (value) {
                        const formattedValue = this.formatRupiah(value);
                        e.target.value = formattedValue;
                        updateHiddenHargaSatuan(value);
                    }
                    this.updateTotalAnggaran();
                }
            });
        } else {
            console.warn('🔧 [TAMBAH HARGA DEBUG] Harga satuan elements not found');
        }
    }

    /**
     * Setup edit harga satuan formatting
     */
    setupEditHargaSatuanFormatting() {
        const editHargaSatuanInput = document.getElementById('edit_harga_satuan');
        const editHargaSatuanActual = document.getElementById('edit_harga_satuan_actual');

        if (editHargaSatuanInput && editHargaSatuanActual) {
            console.log('🔧 [HARGA DEBUG] Initializing edit harga satuan event listeners');
            
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
        } else {
            console.warn('🔧 [HARGA DEBUG] Harga satuan elements not found');
        }
    }

    // ========== UPDATE FUNCTIONS ==========

    /**
     * Setup edit form submission dengan AJAX
     */
    setupEditFormSubmission() {
        const editForm = document.getElementById('editRkasForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                console.log('🔧 [UPDATE DEBUG] Starting form submission...');
                
                if (!this.validateEditForm()) {
                    console.error('❌ [UPDATE DEBUG] Form validation failed');
                    return false;
                }
                
                const bulanInputs = Array.from(document.querySelectorAll('#edit_bulanContainer select[name="bulan[]"]'));
                const jumlahInputs = Array.from(document.querySelectorAll('#edit_bulanContainer input[name="jumlah[]"]'));
                const satuanInputs = Array.from(document.querySelectorAll('#edit_bulanContainer input[name="satuan[]"]'));
                
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
                    harga_satuan: hargaSatuanActual?.value || document.querySelector('#editRkasForm input[name="harga_satuan"]')?.value,
                    tahun_anggaran: document.querySelector('#editRkasForm input[name="tahun_anggaran"]')?.value
                };
                
                console.log('🔧 [UPDATE DEBUG] Form data to be sent:', formData);
                
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
                
                const emptyMonths = formData.bulan.filter(month => !month);
                if (emptyMonths.length > 0) {
                    console.error('❌ [UPDATE DEBUG] Empty months detected:', emptyMonths);
                    Swal.fire('Error', 'Ada bulan yang belum dipilih. Silakan pilih bulan untuk semua card.', 'error');
                    return false;
                }
                
                const submitBtn = editForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Mengupdate...';
                    submitBtn.disabled = true;
                }
                
                const formDataObj = new FormData(editForm);
                
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
                } else {
                    console.log('🔧 [UPDATE DEBUG] harga_satuan already in FormData');
                }
                
                console.log('🔧 [UPDATE DEBUG] Actual FormData content:');
                for (let [key, value] of formDataObj.entries()) {
                    console.log(`  ${key}:`, value);
                }
                
                // Simpan ID data yang sedang di-edit untuk referensi
                const mainDataId = document.getElementById('edit_main_data_id')?.value;
                
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
                        // Tutup modal edit
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editRkasModal'));
                        if (editModal) {
                            editModal.hide();
                        }
                        
                        // Update UI tanpa reload
                        this.handleUpdateSuccess(data, mainDataId, formData);
                        
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
    }

    /**
     * Handle successful update tanpa reload
     */
    handleUpdateSuccess(data, oldMainDataId, formData) {
        console.log('🔧 [UPDATE DEBUG] Handling update success:', data);
        
        // Hapus data lama dari semua tab
        this.removeOldData(oldMainDataId, formData)
            .then(() => {
                // Tambah data baru ke tab yang sesuai
                this.addNewData(formData);
                
                // Refresh data semua tab
                this.refreshAllTabsData();
                
                // Update tahap cards
                this.updateTahapCards();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonText: 'OK'
                });
            })
            .catch(error => {
                console.error('❌ [UPDATE DEBUG] Error removing old data:', error);
                
                // Fallback: Refresh halaman jika ada error
                Swal.fire({
                    icon: 'warning',
                    title: 'Memuat ulang halaman...',
                    text: 'Sedang memuat ulang data terbaru.',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 1500
                });
            });
    }

    /**
     * Remove old data from all tabs - PERBAIKAN: Gunakan metode alternatif
     */
    removeOldData(oldMainDataId, formData) {
        console.log('🔧 [UPDATE DEBUG] Removing old data for ID:', oldMainDataId);
        
        // PERBAIKAN: Gunakan data dari formData langsung atau cari di UI
        const oldUraian = formData.old_uraian || this.getOldDataFromUI(oldMainDataId);
        
        console.log('🔧 [UPDATE DEBUG] Removing data with criteria:', {
            uraian: oldUraian,
            formData: formData
        });
        
        // Hapus berdasarkan kriteria data lama dari UI tanpa fetch API
        const tables = document.querySelectorAll('.rkas-table');
        let removedCount = 0;
        
        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr:not(.table-info)');
            rows.forEach(row => {
                // Cari row yang sesuai dengan kriteria data lama
                const uraianCell = row.querySelector('td:nth-child(5)');
                const jumlahCell = row.querySelector('td:nth-child(6)');
                
                // Log untuk debugging
                console.log('🔧 [UPDATE DEBUG] Checking row:', {
                    uraian: uraianCell?.textContent.trim(),
                    jumlah: jumlahCell?.textContent.trim()
                });
                
                if (uraianCell && uraianCell.textContent.trim() === oldUraian) {
                    row.remove();
                    removedCount++;
                    console.log('🔧 [UPDATE DEBUG] Removed old row');
                }
            });
            
            // Update nomor urut dan total
            if (removedCount > 0) {
                this.updateRowNumbers(table);
                this.updateMonthTotal(table);
            }
        });
        
        console.log('🔧 [UPDATE DEBUG] Total removed rows:', removedCount);
        
        // Return promise untuk chain
        return Promise.resolve(removedCount);
    }

    /**
     * Get old data from UI (alternative jika tidak bisa fetch)
     */
    getOldDataFromUI(oldMainDataId) {
        // Cari data di UI berdasarkan ID atau simpan data lama saat modal dibuka
        const oldUraian = sessionStorage.getItem(`old_uraian_${oldMainDataId}`);
        console.log('🔧 [UPDATE DEBUG] Old uraian from sessionStorage:', oldUraian);
        return oldUraian || '';
    }

    /**
     * Save old data when opening edit modal
     */
    saveOldDataWhenOpeningModal(data) {
        if (data.id && data.uraian) {
            sessionStorage.setItem(`old_uraian_${data.id}`, data.uraian);
            console.log('🔧 [UPDATE DEBUG] Saved old uraian for ID:', data.id, data.uraian);
        }
    }

    /**
     * Add new data to appropriate tabs
     */
    addNewData(formData) {
        console.log('🔧 [UPDATE DEBUG] Adding new data:', formData);
        
        // Dapatkan informasi kegiatan dan rekening dari form
        const kegiatanSelect = document.getElementById('edit_kegiatan');
        const rekeningSelect = document.getElementById('edit_rekening_belanja');
        
        if (!kegiatanSelect || !rekeningSelect) {
            console.error('❌ [UPDATE DEBUG] Select elements not found');
            return;
        }
        
        const selectedKegiatan = kegiatanSelect.options[kegiatanSelect.selectedIndex];
        const selectedRekening = rekeningSelect.options[rekeningSelect.selectedIndex];
        
        const programKegiatan = selectedKegiatan.getAttribute('data-program') || '-';
        const kegiatan = selectedKegiatan.getAttribute('data-sub-program') || '-';
        const rekeningBelanja = selectedRekening.getAttribute('data-rincian-objek') || '-';
        const hargaSatuan = this.formatRupiah(formData.harga_satuan);
        
        // Untuk setiap bulan yang di-update, tambahkan ke tab yang sesuai
        formData.bulan.forEach((bulan, index) => {
            const jumlah = formData.jumlah[index];
            const satuan = formData.satuan[index];
            const total = jumlah * formData.harga_satuan;
            
            this.addDataToMonthTab(bulan, {
                programKegiatan,
                kegiatan,
                rekeningBelanja,
                uraian: formData.uraian,
                jumlah,
                satuan,
                hargaSatuan,
                total,
                id: this.generateTempId() // ID sementara untuk operasi UI
            });
        });
    }

    /**
     * Add data to specific month tab
     */
    addDataToMonthTab(month, data) {
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        if (!tableBody) {
            console.error('❌ [UPDATE DEBUG] Table body not found for month:', month);
            return;
        }
        
        // Cek jika ada row "no data"
        const noDataRow = tableBody.querySelector('.no-data-row');
        if (noDataRow) {
            noDataRow.remove();
        }
        
        // Hitung jumlah row yang ada (exclude total row)
        const existingRows = tableBody.querySelectorAll('tr:not(.table-info)');
        const rowNumber = existingRows.length + 1;
        
        // Buat row baru
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${rowNumber}</td>
            <td>${this.escapeHtml(data.programKegiatan)}</td>
            <td>${this.escapeHtml(data.kegiatan)}</td>
            <td>${this.escapeHtml(data.rekeningBelanja)}</td>
            <td>${this.escapeHtml(data.uraian)}</td>
            <td class="text-end">${data.jumlah}</td>
            <td class="text-end">0</td>
            <td>${this.escapeHtml(data.satuan)}</td>
            <td class="text-end">Rp ${this.formatNumber(data.hargaSatuan)}</td>
            <td class="text-end"><strong>Rp ${this.formatNumber(data.total)}</strong></td>
            <td class="text-center">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                        id="actionDropdown${data.id}" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown${data.id}">
                        <li>
                            <a class="dropdown-item" href="#" onclick="window.rkasManager.showDetailModal(${data.id})">
                                <i class="bi bi-eye me-2"></i>Detail
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="window.rkasManager.showEditModal(${data.id})">
                                <i class="bi bi-pencil me-2"></i>Edit
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        `;
        
        // Tambahkan row sebelum total row
        const totalRow = tableBody.querySelector('tr.table-info');
        if (totalRow) {
            tableBody.insertBefore(newRow, totalRow);
        } else {
            // Jika tidak ada total row, buat baru
            tableBody.appendChild(newRow);
            this.addTotalRow(tableBody, month);
        }
        
        // Update total bulan
        this.updateMonthTotal(tableBody);
        
        console.log('🔧 [UPDATE DEBUG] Added data to month:', month);
    }

    /**
     * Add total row to table
     */
    addTotalRow(tableBody, month) {
        const totalRow = document.createElement('tr');
        totalRow.className = 'table-info';
        totalRow.innerHTML = `
            <td colspan="9" class="text-end"><strong>Total ${month}:</strong></td>
            <td class="text-end"><strong>Rp 0</strong></td>
            <td></td>
        `;
        tableBody.appendChild(totalRow);
    }

    /**
     * Generate temporary ID for UI operations
     */
    generateTempId() {
        return 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Refresh all tabs data from server
     */
    refreshAllTabsData() {
        const months = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 
                    'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
        
        months.forEach(month => {
            this.refreshMonthData(month);
        });
    }

    // ========== DELETE FUNCTIONS ==========

    /**
     * Setup delete button
     */
    setupDeleteButton() {
        const deleteBtn = document.getElementById('btnDeleteAllData');
        console.log('🔧 [DELETE DEBUG] Setting up delete button:', deleteBtn);
        
        if (deleteBtn) {
            // Hapus event listener lama jika ada
            deleteBtn.replaceWith(deleteBtn.cloneNode(true));
            const newDeleteBtn = document.getElementById('btnDeleteAllData');
            
            newDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('🔧 [DELETE DEBUG] Delete button clicked');
                
                const mainDataId = document.getElementById('edit_main_data_id').value;
                console.log('🔧 [DELETE DEBUG] Main data ID:', mainDataId);
                
                if (!mainDataId) {
                    Swal.fire('Error', 'Data tidak valid untuk dihapus', 'error');
                    return;
                }
                
                this.showDeleteAllConfirmation(mainDataId);
            }.bind(this));
        } else {
            console.error('❌ [DELETE DEBUG] Delete button not found');
        }
    }

    /**
     * Show delete all confirmation
     */
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
                            <strong>Perhatian:</strong> Tindakan ini akan menghapus semua data dengan kegiatan, rekening, dan uraian yang sama.
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
                this.deleteAllData(mainDataId);
            } else {
                console.log('🔧 [DELETE DEBUG] User cancelled deletion');
            }
        });
    }

    /**
     * Delete all data dengan AJAX
     */
    deleteAllData(mainDataId) {
        console.log('🔧 [DELETE DEBUG] Starting delete all process for ID:', mainDataId);
        
        const deleteBtn = document.getElementById('btnDeleteAllData');
        const originalHtml = deleteBtn.innerHTML;
        
        deleteBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menghapus...';
        deleteBtn.disabled = true;

        fetch(`/rkas/delete-all/${mainDataId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
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
            console.log('🔧 [DELETE DEBUG] Delete all response:', data);
            if (data.success) {
                // Tutup modal edit
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editRkasModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                // Refresh semua data tanpa reload
                this.refreshAllTabsData();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonText: 'OK'
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

    /**
     * Refresh all tabs data
     */
    refreshAllTabsData() {
        const months = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 
                    'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
        
        months.forEach(month => {
            this.refreshMonthData(month);
        });
        
        // Update tahap cards
        this.updateTahapCards();
    }

    // ========== GLOBAL FUNCTIONS ==========

    /**
     * Show edit modal
     */
    showEditModal(id) {
        console.log('🔧 [EDIT DEBUG] Opening edit modal for ID:', id);
        
        if (!id) {
            console.error('❌ [EDIT DEBUG] Invalid ID provided');
            Swal.fire('Error', 'ID data tidak valid', 'error');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('🔧 [EDIT DEBUG] CSRF Token:', csrfToken ? 'Available' : 'Missing');
        console.log('🔧 [EDIT DEBUG] Using EDIT route:', `/rkas/${id}/edit`);

        Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/rkas/${id}/edit`, {
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
                        Route: /rkas/${id}/edit</small>
                    </div>
                `,
                confirmButtonText: 'Mengerti'
            });
        });
    }

    /**
     * Populate edit form
     */
    populateEditForm(data) {
        console.log('🔧 [EDIT DEBUG] Populating form with MULTI-MONTH data:', data);
        console.log('🔧 [EDIT DEBUG] Month data received:', data.bulan_data);
        
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
        
        if (editKegiatan && data.kode_id) editKegiatan.value = data.kode_id;
        if (editRekening && data.kode_rekening_id) editRekening.value = data.kode_rekening_id;
        if (editUraian && data.uraian) editUraian.value = data.uraian;
        
        if (editMainDataId && data.id) {
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
                if (editHargaSatuan) editHargaSatuan.value = this.formatRupiah(rawValue);
                if (editHargaSatuanActual) {
                    editHargaSatuanActual.value = rawValue;
                    console.log('🔧 [HARGA DEBUG] Initialized hidden field with raw:', editHargaSatuanActual.value);
                }
            }
        } else {
            console.warn('🔧 [HARGA DEBUG] No harga_satuan_raw data available');
        }
        
        setTimeout(() => {
            if (window.jQuery && $('.select2-kegiatan-edit').length && data.kode_id) {
                try {
                    $('.select2-kegiatan-edit').val(data.kode_id).trigger('change');
                    console.log('🔧 [EDIT DEBUG] Select2 kegiatan set to:', data.kode_id);
                } catch (error) {
                    console.error('❌ [EDIT DEBUG] Error setting select2 kegiatan:', error);
                }
            }
            if (window.jQuery && $('.select2-rekening-edit').length && data.kode_rekening_id) {
                try {
                    $('.select2-rekening-edit').val(data.kode_rekening_id).trigger('change');
                    console.log('🔧 [EDIT DEBUG] Select2 rekening set to:', data.kode_rekening_id);
                } catch (error) {
                    console.error('❌ [EDIT DEBUG] Error setting select2 rekening:', error);
                }
            }
        }, 100);
        
        console.log('🔧 [EDIT DEBUG] Initializing MULTI-MONTH data');
        if (data.bulan_data) {
            this.initializeEditAnggaranList(data.bulan_data);
        } else {
            console.warn('🔧 [EDIT DEBUG] No bulan_data provided, initializing empty');
            this.initializeEditAnggaranList([]);
        }
        
        if (data.id) {
            editForm.action = `/rkas/${data.id}`;
            console.log('🔧 [EDIT DEBUG] Form action set to:', editForm.action);
        } else {
            console.error('❌ [EDIT DEBUG] No data ID provided');
            Swal.fire('Error', 'Data ID tidak valid', 'error');
            return;
        }
        
        const editModalElement = document.getElementById('editRkasModal');
        if (editModalElement) {
            try {
                const editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
                console.log('🔧 [EDIT DEBUG] Edit modal shown successfully');
                
                setTimeout(this.updateEditTotalAnggaran.bind(this), 500);
            } catch (error) {
                console.error('❌ [EDIT DEBUG] Error showing modal:', error);
                Swal.fire('Error', 'Gagal menampilkan modal edit', 'error');
            }
        } else {
            console.error('❌ [EDIT DEBUG] Edit modal element not found');
            Swal.fire('Error', 'Modal edit tidak ditemukan', 'error');
            return;
        }

        this.setupEditHargaSatuanFormatting();

        setTimeout(() => {
            this.setupDeleteButton();
        }, 500);
    }

    // ========== RKAS SEARCH FUNCTIONS ==========

    /**
     * Initialize RKAS search
     */
    initializeRkasSearch() {
        const searchForm = document.getElementById('SearchForm');
        const searchInput = document.getElementById('SearchInput');
        
        if (!searchForm || !searchInput) {
            console.warn('⚠️ [RKAS SEARCH] Search elements not found');
            return;
        }

        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.trim();
            
            if (searchTerm.length === 0) {
                if (this.isSearching) {
                    this.showRkasSearchLoading(false);
                    this.resetRkasSearch();
                    this.isSearching = false;
                }
                return;
            }
            
            if (searchTerm.length < 2) {
                if (this.isSearching) {
                    this.showRkasSearchLoading(false);
                    this.isSearching = false;
                }
                return;
            }
            
            searchTimeout = setTimeout(() => {
                this.isSearching = true;
                this.showRkasSearchLoading(true);
                this.performRkasSearch(searchTerm);
            }, 500);
        });

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            if (searchTerm.length >= 2) {
                this.isSearching = true;
                this.showRkasSearchLoading(true);
                this.performRkasSearch(searchTerm);
            }
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.resetRkasSearch();
                searchInput.value = '';
                searchInput.blur();
                this.isSearching = false;
            }
        });

        const clearButton = document.querySelector('.search-clear');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                this.resetRkasSearch();
                this.isSearching = false;
            });
        }
        
        console.log('✅ [RKAS SEARCH] Initialized successfully');
    }

    /**
     * Perform RKAS search
     */
    performRkasSearch(searchTerm) {
        console.log('🔍 [RKAS SEARCH] Searching for:', searchTerm);
        
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const currentMonth = activeTab ? activeTab.getAttribute('data-month') : 'januari';
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        
        if (!tahun) {
            console.error('❌ [RKAS SEARCH] Tahun tidak ditemukan');
            this.showRkasSearchLoading(false);
            this.showRkasSearchError('Tahun anggaran tidak ditemukan');
            return;
        }

        this.showTableLoading(currentMonth, true);

        const params = new URLSearchParams({
            search: searchTerm,
            bulan: currentMonth,
            tahun: tahun
        });

        fetch(`/rkas/search?${params.toString()}`, {
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
            this.showRkasSearchLoading(false);
            if (data.success) {
                this.handleRkasSearchResponse(data, searchTerm, currentMonth);
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat mencari data');
            }
        })
        .catch(error => {
            console.error('❌ [RKAS SEARCH] Error:', error);
            this.showRkasSearchLoading(false);
            this.showTableLoading(currentMonth, false);
            this.resetRkasSearch();
            this.showRkasSearchError('Terjadi kesalahan saat mencari data: ' + error.message);
        });
    }

    /**
     * Handle RKAS search response
     */
    handleRkasSearchResponse(data, searchTerm, currentMonth) {
        const tableBody = document.getElementById(`table-body-${currentMonth.toLowerCase()}`);
        
        if (!tableBody) {
            this.showRkasSearchError(`Tabel untuk bulan ${currentMonth} tidak ditemukan`);
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
                        Tidak ditemukan data RKAS dengan kata kunci: "<strong>${this.escapeHtml(searchTerm)}</strong>" di bulan ${currentMonth}
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

        this.showRkasSearchInfo(data.data.length, searchTerm, currentMonth);
        this.reattachRkasEventListeners();
    }

    /**
     * Show RKAS search loading
     */
    showRkasSearchLoading(show) {
        const searchInput = document.getElementById('SearchInput');
        const loadingElement = document.querySelector('.search-loading');
        
        if (loadingElement) {
            loadingElement.classList.toggle('d-none', !show);
        }
        
        if (searchInput) {
            searchInput.readOnly = show;
        }
    }

    /**
     * Show table loading
     */
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

    /**
     * Show RKAS search info
     */
    showRkasSearchInfo(count, searchTerm, month) {
        this.removeRkasSearchInfo();

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

    /**
     * Show RKAS search error
     */
    showRkasSearchError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error Pencarian',
            text: message,
            confirmButtonText: 'Mengerti'
        });
    }

    /**
     * Remove RKAS search info
     */
    removeRkasSearchInfo() {
        // Tidak perlu implementasi khusus untuk sekarang
    }

    /**
     * Reset RKAS search
     */
    resetRkasSearch() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const currentMonth = activeTab ? activeTab.getAttribute('data-month').toLowerCase() : 'januari';
        const tableBody = document.getElementById(`table-body-${currentMonth}`);
        
        this.showRkasSearchLoading(false);
        
        if (tableBody && tableBody.hasAttribute('data-original-content')) {
            tableBody.innerHTML = tableBody.getAttribute('data-original-content');
            tableBody.removeAttribute('data-original-content');
        }
        
        this.removeRkasSearchInfo();
        this.reattachRkasEventListeners();
        
        console.log('🔍 [RKAS SEARCH] Search reset');
    }

    /**
     * Reattach RKAS event listeners
     */
    reattachRkasEventListeners() {
        document.dispatchEvent(new CustomEvent('rkasSearchReset'));
    }

    // ========== SALIN DATA FUNCTIONS ==========

    /**
     * Hide salin data button
     */
    hideSalinDataButton() {
        const btnSalinData = document.getElementById('btnSalinData');
        if (btnSalinData) {
            btnSalinData.style.display = 'none';
            console.log('✅ [SALIN DATA] Tombol salin data disembunyikan');
        }
    }

    /**
     * Disable salin data button
     */
    disableSalinDataButton() {
        const btnSalinData = document.getElementById('btnSalinData');
        if (btnSalinData) {
            btnSalinData.disabled = true;
            btnSalinData.innerHTML = '<i class="bi bi-copy me-2"></i>Salin Data';
            btnSalinData.title = 'Data sudah disalin';
            btnSalinData.setAttribute('data-bs-toggle', 'tooltip');
            new bootstrap.Tooltip(btnSalinData);
            console.log('✅ [SALIN DATA] Tombol salin data dinonaktifkan');
        }
    }

    /**
     * Set salin data done
     */
    setSalinDataDone() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        if (tahun) {
            localStorage.setItem(`salin_data_done_${tahun}`, 'true');
            console.log('✅ [SALIN DATA] Status disimpan ke localStorage untuk tahun:', tahun);
        }
    }

    /**
     * Check salin data status
     */
    checkSalinDataStatus() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        if (tahun) {
            const isDone = localStorage.getItem(`salin_data_done_${tahun}`) === 'true';
            console.log('🔍 [SALIN DATA] Status dari localStorage:', isDone ? 'SUDAH DISALIN' : 'BELUM DISALIN');
            return isDone;
        }
        return false;
    }

    /**
     * Initialize salin data button
     */
    initializeSalinDataButton() {
        const btnSalinData = document.getElementById('btnSalinData');
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;

        console.log('🔍 [SALIN DATA] Initializing button for year:', tahun);
        
        if (!btnSalinData) {
            console.error('❌ [SALIN DATA] Button element not found');
            return;
        }

        // Nonaktifkan tombol sementara
        btnSalinData.disabled = true;
        btnSalinData.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memeriksa...';

        // CEK STATUS DATA RKAS SEKARANG TERLEBIH DAHULU
        this.checkCurrentRkasData(tahun)
            .then(hasCurrentData => {
                if (hasCurrentData) {
                    // Jika sudah ada data RKAS, sembunyikan tombol salin data
                    this.hideSalinDataButton();
                    console.log('✅ [SALIN DATA] Tombol disembunyikan - sudah ada data RKAS');
                    return;
                }

                // Jika tidak ada data RKAS, lanjutkan pengecekan data perubahan tahun sebelumnya
                return this.checkPreviousYearPerubahan(tahun);
            })
            .then(hasPreviousPerubahan => {
                if (hasPreviousPerubahan === undefined) return; // Sudah dihandle di step sebelumnya

                if (hasPreviousPerubahan) {
                    // Aktifkan tombol jika ada data perubahan tahun sebelumnya
                    this.enableSalinDataButton();
                    console.log('✅ [SALIN DATA] Button enabled - data available from previous year');
                } else {
                    // Nonaktifkan tombol jika tidak ada data
                    this.disableSalinDataButton();
                    console.log('ℹ️ [SALIN DATA] Button disabled - no data from previous year');
                }
            })
            .catch(error => {
                console.error('❌ [SALIN DATA] Error during initialization:', error);
                this.disableSalinDataButton();
            });
    }

    /**
     * Check current RKAS data
     */
    checkCurrentRkasData(tahun) {
        return fetch(`/rkas/check-rkas-data?tahun=${tahun}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('🔍 [SALIN DATA] Current RKAS data status:', data.has_rkas_data ? 'ADA' : 'KOSONG');
                return data.has_rkas_data;
            } else {
                throw new Error(data.message || 'Server returned error');
            }
        })
        .catch(error => {
            console.error('❌ [SALIN DATA] Error checking current RKAS data:', error);
            return false; // Default to false jika error
        });
    }

    /**
     * Check previous year perubahan
     */
    checkPreviousYearPerubahan(tahun) {
        return fetch(`/rkas/check-previous-perubahan?tahun=${tahun}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('🔍 [SALIN DATA] Previous year perubahan status:', data.has_previous_perubahan ? 'ADA' : 'KOSONG');
                return data.has_previous_perubahan;
            } else {
                throw new Error(data.message || 'Server returned error');
            }
        });
    }

    /**
     * Enable salin data button
     */
    enableSalinDataButton() {
        const btnSalinData = document.getElementById('btnSalinData');
        if (btnSalinData) {
            btnSalinData.disabled = false;
            btnSalinData.innerHTML = '<i class="bi bi-copy me-2"></i>Salin Data';
            btnSalinData.removeAttribute('title');
            btnSalinData.removeAttribute('data-bs-toggle');
            
            // Hapus tooltip jika ada
            const tooltip = bootstrap.Tooltip.getInstance(btnSalinData);
            if (tooltip) {
                tooltip.dispose();
            }
            
            // Setup event listener
            btnSalinData.addEventListener('click', this.showSalinDataConfirmation.bind(this));
            
            console.log('✅ [SALIN DATA] Tombol salin data diaktifkan');
        }
    }

    /**
     * Show salin data confirmation
     */
    showSalinDataConfirmation() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        const previousYear = tahun - 1;

        Swal.fire({
            title: 'Salin Data RKAS Perubahan?',
            html: `
                <div class="text-center">
                    <i class="bi bi-question-circle text-info" style="font-size: 4rem;"></i>
                    <p class="mt-3">Apakah Anda ingin menyalin data RKAS tahun sebelumnya?</p>
                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Data dari RKAS tahun <strong>${previousYear}</strong> akan disalin ke tahun <strong>${tahun}</strong>
                        </small>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check me-2"></i>Ya, Salin Data',
            cancelButtonText: '<i class="bi bi-x me-2"></i>Batal',
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            backdrop: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return this.processSalinData();
            }
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                // Sembunyikan tombol salin data
                this.hideSalinDataButton();
                
                // Simpan status ke localStorage
                this.setSalinDataDone();
                
                // Tampilkan success message
                Swal.fire({
                    title: 'Berhasil!',
                    html: `
                        <div class="text-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                            <p class="mt-3">${result.value.message}</p>
                            <div class="alert alert-success mt-3">
                                <small>
                                    <i class="bi bi-check2-circle me-1"></i>
                                    Berhasil menyalin <strong>${result.value.copied_count}</strong> data dari tahun ${result.value.previous_year}
                                </small>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: '<i class="bi bi-arrow-clockwise me-2"></i>OK',
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then(() => {
                    // Refresh halaman
                    location.reload();
                });
            }
        });
    }

    /**
     * Process salin data
     */
    processSalinData() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;

        if (!tahun) {
            return Promise.reject(new Error('Tahun anggaran tidak ditemukan'));
        }

        return fetch('/rkas/copy-previous-perubahan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                tahun_anggaran: tahun
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                return data;
            } else {
                throw new Error(data.message || 'Gagal menyalin data');
            }
        })
        .catch(error => {
            let errorMessage = 'Terjadi kesalahan saat menyalin data';
            
            if (error.message.includes('404')) {
                errorMessage = 'Data RKAS Perubahan tahun sebelumnya tidak ditemukan';
            } else if (error.message.includes('500')) {
                errorMessage = 'Terjadi kesalahan server. Silakan coba lagi.';
            } else {
                errorMessage = error.message;
            }
            
            Swal.showValidationMessage(errorMessage);
            return Promise.reject(error);
        });
    }

    // ========== TAHAP CARDS FUNCTIONS ==========

    /**
     * Update tahap cards
     */
    updateTahapCards() {
        const tahunAnggaran = document.querySelector('input[name="tahun_anggaran"]').value;

        // Update Tahap 1
        fetch(`/rkas/total-tahap1?tahun=${tahunAnggaran}`)
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

                    // Update progress bar
                    const progressBar1 = document.querySelector('.col-lg-4:nth-child(2) .progress-bar');
                    if (progressBar1) {
                        setTimeout(() => {
                            progressBar1.style.width = tahap1Data.persentase_terpakai + '%';
                        }, 300);
                    }
                }
            })
            .catch(error => console.error('Error updating Tahap 1:', error));

        // Update Tahap 2
        fetch(`/rkas/total-tahap2?tahun=${tahunAnggaran}`)
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

                    // Update progress bar
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

    // ========== DETAIL FUNCTIONS ==========

    /**
     * Show detail modal
     */
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

        fetch(`/rkas/${id}`, {
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

    /**
     * Populate detail form
     */
    populateDetailForm(data) {
        console.log('🔧 [DETAIL DEBUG] Populating detail form with data:', data);
        console.log('🔧 [DETAIL DEBUG] Month data received:', data.bulan_data);
        
        // Set informasi dasar
        this.setElementText('detail_program_kegiatan', data.program_kegiatan || '-');
        this.setElementText('detail_kegiatan', data.kegiatan || '-');
        this.setElementText('detail_rekening_belanja', data.rekening_belanja || '-');
        this.setElementText('detail_uraian', data.uraian || '-');
        this.setElementText('detail_harga_satuan', data.harga_satuan || 'Rp 0');
        this.setElementText('detail_total_anggaran', data.total_anggaran || 'Rp 0');
        this.setElementText('detail_total_anggaran_display', data.total_anggaran || 'Rp 0');

        // Set data bulan
        this.initializeDetailAnggaranList(data.bulan_data, data.harga_satuan_raw || 0);
        
        // Show modal
        const detailModalElement = document.getElementById('detailRkasModal');
        if (detailModalElement) {
            const detailModal = new bootstrap.Modal(detailModalElement);
            detailModal.show();
            console.log('🔧 [DETAIL DEBUG] Detail modal shown successfully');
        } else {
            console.error('❌ [DETAIL DEBUG] Detail modal element not found');
            Swal.fire('Error', 'Modal detail tidak ditemukan', 'error');
        }
    }

    /**
     * Set element text
     */
    setElementText(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = text;
        }
    }

    /**
     * Initialize detail anggaran list
     */
    initializeDetailAnggaranList(data = [], hargaSatuan = 0) {
        const container = document.getElementById('detail_bulanContainer');
        if (!container) {
            console.error('❌ [DETAIL DEBUG] Detail bulan container not found');
            return;
        }

        console.log('🔧 [DETAIL DEBUG] Initializing detail bulan list with data:', data);
        console.log('🔧 [DETAIL DEBUG] Harga satuan:', hargaSatuan);
        
        // Clear container
        container.innerHTML = '';
        
        let totalAnggaran = 0;

        // Jika ada data bulan, buat card untuk setiap bulan
        if (data && Array.isArray(data) && data.length > 0) {
            console.log('🔧 [DETAIL DEBUG] Creating detail cards for', data.length, 'months');
            data.forEach((item, index) => {
                try {
                    const card = this.createDetailAnggaranCard(item.bulan, item.jumlah, item.satuan, hargaSatuan);
                    if (card) {
                        container.appendChild(card);
                        console.log('🔧 [DETAIL DEBUG] Created detail card for bulan:', item.bulan);
                        
                        // Hitung total
                        const itemTotal = item.jumlah * hargaSatuan;
                        totalAnggaran += itemTotal;
                    }
                } catch (error) {
                    console.error('❌ [DETAIL DEBUG] Error creating detail card for item:', item, error);
                }
            });
        } else {
            // Jika tidak ada data, tampilkan pesan
            console.log('🔧 [DETAIL DEBUG] No month data for detail');
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-2">Tidak ada data bulan</p>
                </div>
            `;
        }

        // Update total display
        const totalDisplay = document.getElementById('detail_total_anggaran_display');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + this.formatNumber(totalAnggaran);
        }

        console.log('🔧 [DETAIL DEBUG] Total cards created:', container.querySelectorAll('.detail-bulan-card').length);
        console.log('🔧 [DETAIL DEBUG] Total anggaran:', totalAnggaran);
    }

    /**
     * Create detail anggaran card
     */
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

    // ========== OTHER FUNCTIONS ==========

    /**
     * Refresh month data dengan handling duplikasi
     */
    refreshMonthData(month) {
        console.log('🔧 [TAB DEBUG] Starting refresh for month:', month);
        
        const formattedMonth = month.charAt(0).toUpperCase() + month.slice(1).toLowerCase();
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        
        if (!tableBody) {
            console.error('🔧 [TAB DEBUG] Table body not found for month:', month);
            return;
        }

        // Hanya refresh jika tab ini aktif
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const activeMonth = activeTab ? activeTab.getAttribute('data-month').toLowerCase() : 'januari';
        
        if (activeMonth !== month.toLowerCase()) {
            console.log('🔧 [TAB DEBUG] Skipping refresh for inactive tab:', month);
            return;
        }

        // Tampilkan loading
        tableBody.innerHTML = `<tr>
            <td colspan="11" class="text-center">
                <div class="py-4">
                    <div class="loading-spinner me-2"></div>Memuat data...
                </div>
            </td>
        </tr>`;

        fetch(`/rkas/bulan/${formattedMonth}?tahun=${document.querySelector('input[name="tahun_anggaran"]').value}`, {
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
            console.log('🔧 [TAB DEBUG] Data received for month:', month, data);
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
            console.error('🔧 [TAB DEBUG] Error during refresh for month:', month, error);
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

    /**
     * Populate table
     */
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
                                <a class="dropdown-item" href="#" onclick="window.rkasManager.showDetailModal(${item.id})">
                                    <i class="bi bi-eye me-2"></i>Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="window.rkasManager.showEditModal(${item.id})">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            `;
        });

        // Add total row
        html += `
        <tr class="table-info">
            <td colspan="9" class="text-end"><strong>Total ${month}:</strong></td>
            <td><strong>Rp ${this.formatNumber(total)}</strong></td>
            <td></td>
        </tr>
        `;

        tableBody.innerHTML = html;
    }

    /**
     * Show no data message
     */
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

    // ========== INITIALIZATION ==========

    /**
     * Initialize semua komponen
     */
    initialize() {
        console.log('=== RKAS MANAGER INITIALIZING ===');
        
        this.initializeSelect2();
        this.setupEventListeners();
        this.initializeAnggaranList();
        this.updateTahapCards();
        this.initializeSalinDataButton();
        this.initializeTabs();
        this.initializeRkasSearch();

        // Setup tambah form
        this.setupTambahFormSubmission();
        this.setupTambahHargaSatuanFormatting();

        console.log('=== RKAS MANAGER INITIALIZATION COMPLETE ===');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Input changes
        document.addEventListener('input', (e) => {
            // Untuk form tambah
            if (e.target.classList.contains('satuan-input') && 
                e.target.closest('#tambahRkasModal')) {
                // Handle oleh method terpisah
            }
            if ((e.target.classList.contains('jumlah-input') || e.target.id === 'harga_satuan') &&
                e.target.closest('#tambahRkasModal')) {
                this.updateTotalAnggaran();
            }
            
            // Untuk form edit (jika ada)
            if (e.target.classList.contains('satuan-input') && 
                e.target.closest('#editRkasModal')) {
                // Handle oleh method edit
            }
        });

        // Delete confirmation - PERBAIKAN: gunakan arrow function
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
            if (this.currentRkasId) {
                this.deleteRkas(this.currentRkasId);
            }
        });

        // Modal reset on close - PERBAIKAN: gunakan arrow function
        document.getElementById('tambahRkasModal')?.addEventListener('hidden.bs.modal', () => {
            this.resetModal();
        });

        // Month tab click handler - PERBAIKAN: handle tab switching dengan benar
        document.querySelectorAll('.nav-link[data-month]').forEach((tab) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const month = tab.getAttribute('data-month');
                console.log('🔧 [TAB DEBUG] Tab clicked:', month);
                
                // Set active tab menggunakan method kita
                this.setActiveTab(month);
                
                // Simpan ke localStorage untuk consistency
                localStorage.setItem('activeRkasTab', month);
                
                // Refresh data untuk bulan yang dipilih
                this.refreshMonthData(month.toLowerCase());
                
                // Reset search ketika pindah tab
                this.resetRkasSearch();
            });
        });

        // Handle Bootstrap tab events untuk consistency
        document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                const month = e.target.getAttribute('data-month');
                console.log('🔧 [TAB DEBUG] Bootstrap tab shown:', month);
                
                // Sync dengan method kita
                this.setActiveTab(month);
                
                // Simpan ke localStorage
                localStorage.setItem('activeRkasTab', month);
                
                // Refresh data
                setTimeout(() => {
                    this.refreshMonthData(month.toLowerCase());
                }, 100);
            });
        });

        // Setup edit form submission
        try {
            this.setupEditFormSubmission();
        } catch (error) {
            console.error('Error setting up edit form submission:', error);
        }
    }

    // ========== DELETE FUNCTIONS ==========

    /**
     * Delete single RKAS data dengan AJAX
     */
    deleteRkas(id) {
        console.log('🔧 [DELETE DEBUG] Starting single delete for ID:', id);
        
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menghapus...';
            confirmBtn.disabled = true;
        }

        fetch(`/rkas/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
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
                // Tutup modal delete
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteRkasModal'));
                if (deleteModal) {
                    deleteModal.hide();
                }
                
                // Hapus row dari tabel tanpa reload
                this.removeRkasRow(id);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonText: 'OK'
                });
                
                // Refresh data tab aktif
                this.refreshCurrentTabData();
                
            } else {
                throw new Error(data.message || 'Gagal menghapus data');
            }
        })
        .catch(error => {
            console.error('❌ [DELETE DEBUG] Error deleting data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hapus Gagal',
                text: 'Terjadi kesalahan saat menghapus data: ' + error.message,
                confirmButtonText: 'Mengerti'
            });
        })
        .finally(() => {
            if (confirmBtn) {
                confirmBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Ya, Hapus Data';
                confirmBtn.disabled = false;
            }
        });
    }

    /**
     * Remove RKAS row from table
     */
    removeRkasRow(id) {
        // Cari semua tabel di semua tab
        const tables = document.querySelectorAll('.rkas-table');
        
        tables.forEach(table => {
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                // Cari tombol delete di row yang sesuai dengan ID
                const deleteBtn = row.querySelector(`[onclick*="showDeleteModal(${id})"]`);
                if (deleteBtn) {
                    row.remove();
                    console.log('🔧 [DELETE DEBUG] Removed row for ID:', id);
                    
                    // Update nomor urut
                    this.updateRowNumbers(table);
                    
                    // Update total bulan
                    this.updateMonthTotal(table);
                }
            });
        });
    }

    /**
     * Update row numbers after deletion
     */
    updateRowNumbers(table) {
        const rows = table.querySelectorAll('tbody tr:not(.table-info)');
        rows.forEach((row, index) => {
            const firstCell = row.querySelector('td:first-child');
            if (firstCell) {
                firstCell.textContent = index + 1;
            }
        });
    }

    /**
     * Update month total after deletion
     */
    updateMonthTotal(table) {
        let total = 0;
        const rows = table.querySelectorAll('tbody tr:not(.table-info)');
        
        rows.forEach(row => {
            const totalCell = row.querySelector('td:nth-child(10)');
            if (totalCell) {
                const totalText = totalCell.textContent.replace(/[^\d]/g, '');
                total += parseInt(totalText) || 0;
            }
        });
        
        // Update total row
        const totalRow = table.querySelector('tr.table-info');
        if (totalRow) {
            const totalCell = totalRow.querySelector('td:nth-child(2)');
            if (totalCell) {
                totalCell.innerHTML = `<strong>Total ${this.getCurrentMonth()}:</strong>`;
            }
            
            const amountCell = totalRow.querySelector('td:nth-child(1)');
            if (amountCell) {
                amountCell.innerHTML = `<strong>Rp ${this.formatNumber(total)}</strong>`;
            }
        }
    }

    /**
     * Get current active month
     */
    getCurrentMonth() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        return activeTab ? activeTab.getAttribute('data-month') : 'Januari';
    }

    /**
     * Refresh current tab data
     */
    refreshCurrentTabData() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        if (activeTab) {
            const month = activeTab.getAttribute('data-month').toLowerCase();
            this.refreshMonthData(month);
        }
    }
}

// Export class untuk penggunaan global
window.RkasManager = RkasManager;
