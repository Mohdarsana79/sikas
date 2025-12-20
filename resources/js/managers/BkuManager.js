// resources/js/managers/BkuManager.js

/**
 * Class untuk mengelola logika BKU (Buku Kas Umum)
 */
export default class BkuManager {
    constructor() {
        
        // Variabel global
        this.currentStep = 1;
        this.totalSteps = 3;

        // Debug semua sumber data
        console.log('Meta tahun:', document.querySelector('meta[name="tahun"]')?.content);
        console.log('Meta bulan:', document.querySelector('meta[name="bulan"]')?.content);
        console.log('URL Path parts:', window.location.pathname.split('/'));
        this.tahun = this.extractTahunFromPage();
        this.bulan = this.extractBulanFromPage();
        
        // Data global
        this.currentTotalTransaksi = 0;
        this.kegiatanData = [];
        this.rekeningData = [];
        
        // Bind methods
        this.init = this.init.bind(this);
        this.initializeSelect2 = this.initializeSelect2.bind(this);
        this.scrollToTop = this.scrollToTop.bind(this);
        this.formatDateForInput = this.formatDateForInput.bind(this);
        this.formatRupiah = this.formatRupiah.bind(this);
        this.validateDate = this.validateDate.bind(this);
        this.disableOutOfRangeDates = this.disableOutOfRangeDates.bind(this);
        this.syncTanggalTransaksiDanNota = this.syncTanggalTransaksiDanNota.bind(this);
        this.loadKegiatanDanRekening = this.loadKegiatanDanRekening.bind(this);
        this.populateKegiatanSelect = this.populateKegiatanSelect.bind(this);
        this.reloadKegiatanDanRekening = this.reloadKegiatanDanRekening.bind(this);
        this.showError = this.showError.bind(this);
        this.setErrorState = this.setErrorState.bind(this);
        this.validateStep1 = this.validateStep1.bind(this);
        this.validateStep2 = this.validateStep2.bind(this);
        this.showValidationError = this.showValidationError.bind(this);
        this.updateSteps = this.updateSteps.bind(this);
        this.resetSteps = this.resetSteps.bind(this);
        this.calculateTotalBersih = this.calculateTotalBersih.bind(this);
        this.validateAllVolumes = this.validateAllVolumes.bind(this);
        this.renderUraianOptions = this.renderUraianOptions.bind(this);
        this.validateVolumeInput = this.validateVolumeInput.bind(this);
        this.updateUraianSubtotal = this.updateUraianSubtotal.bind(this);
        this.renderKegiatanCardsStep3 = this.renderKegiatanCardsStep3.bind(this);
        this.updateTotalTransaksiDisplay = this.updateTotalTransaksiDisplay.bind(this);
        this.collectFormData = this.collectFormData.bind(this);
        this.submitFormData = this.submitFormData.bind(this);
        this.loadLastNotaNumber = this.loadLastNotaNumber.bind(this);
        this.validateNotaFormat = this.validateNotaFormat.bind(this);
        this.toggleBkuStatus = this.toggleBkuStatus.bind(this);
        this.handleDeleteAllBulan = this.handleDeleteAllBulan.bind(this);
        this.handleDeleteIndividual = this.handleDeleteIndividual.bind(this);
        this.handleDeletePenarikan = this.handleDeletePenarikan.bind(this);
        this.handleDeletePenerimaan = this.handleDeletePenerimaan.bind(this);
        this.handleDeleteSaldoAwal = this.handleDeleteSaldoAwal.bind(this);
        this.handleDeleteSetor = this.handleDeleteSetor.bind(this);
        this.handleTutupBku = this.handleTutupBku.bind(this);
        this.handleBukaBku = this.handleBukaBku.bind(this);
        this.handleEditBunga = this.handleEditBunga.bind(this);
        this.attachBkuEventListeners = this.attachBkuEventListeners.bind(this);
        this.attachLaporPajakEvents = this.attachLaporPajakEvents.bind(this);
        this.handleSimpanLaporPajak = this.handleSimpanLaporPajak.bind(this);
        this.updateRowAfterLaporPajak = this.updateRowAfterLaporPajak.bind(this);
        this.updateTableAfterLaporPajak = this.updateTableAfterLaporPajak.bind(this);
        this.updateSaldoDisplay = this.updateSaldoDisplay.bind(this);
        this.formatRupiah = this.formatRupiah.bind(this);
        // Bind methods edit bunga modal
        this.handleEditBungaSubmit = this.handleEditBungaSubmit.bind(this);
        this.handleEditBungaModalHidden = this.handleEditBungaModalHidden.bind(this);

        this.initEditBungaModalValues = this.initEditBungaModalValues.bind(this);

        this.handleSimpanTarikTunai = this.handleSimpanTarikTunai.bind(this);

        this.handleSimpanSetorTunai = this.handleSimpanSetorTunai.bind(this);

        
        // Initialize
        this.init();
    }

    /**
     * Inisialisasi utama
     */
    init() {
        console.log('=== BKU MANAGER INITIALIZED ===');
        
        // Event handlers
        this.attachEventListeners();
        this.attachBkuEventListeners();
        this.attachLaporPajakEvents();
        this.attachModalResetEvents();
        
        // Initial setup
        this.initializeSelect2();

        // Load data tabel saat halaman pertama kali dibuka
        this.loadInitialTableData();

        
        // Inisialisasi modal edit bunga
        this.initEditBungaModalValues(); // Ganti nama method

        // Attach page unload events
        this.attachPageUnloadEvents();

        this.attachTarikTunaiEvents();
        this.attachSetorTunaiEvents();

        console.log('=== BKU MANAGER READY ===');
    }

    // Tambahkan method baru untuk extract tahun
    extractTahunFromPage() {
        // Coba dari URL terlebih dahulu
        const pathParts = window.location.pathname.split('/');
        if (pathParts.length >= 4 && pathParts[1] === 'bku') {
            return pathParts[2]; // Tahun dari URL /bku/2025/Maret
        }
        
        // Fallback ke meta tag
        return document.querySelector('meta[name="tahun"]')?.content || new Date().getFullYear().toString();
    }

    // Tambahkan method baru untuk extract bulan
    extractBulanFromPage() {
        // Coba dari container element
        const container = document.querySelector('[data-bulan]');
        if (container && container.dataset.bulan) {
            return container.dataset.bulan;
        }
        
        // Coba dari URL
        const pathParts = window.location.pathname.split('/');
        if (pathParts.length >= 4 && pathParts[1] === 'bku') {
            return decodeURIComponent(pathParts[3]);
        }
        
        // Fallback
        return 'Januari';
    }

    /**
     * Attach semua event listeners
     */
    attachEventListeners() {
        // Modal events
        const transactionModal = document.getElementById('transactionModal');
        if (transactionModal) {
            $(transactionModal).on('show.bs.modal', this.handleModalShow.bind(this));
            $(transactionModal).on('shown.bs.modal', this.handleModalShown.bind(this));
        }

        // Date events
        $('#tanggal_transaksi').on('change', this.handleTanggalTransaksiChange.bind(this));
        $('#tanggal_nota').on('change', this.handleTanggalNotaChange.bind(this));

        // Step navigation
        $('#nextBtn').on('click', this.handleNextClick.bind(this));
        $('#prevBtn').on('click', this.handlePrevClick.bind(this));

        // Form toggles
        $('#checkForm').change(this.handleCheckFormChange.bind(this));
        $('#checkNpwp').change(this.handleCheckNpwpChange.bind(this));
        $('#checkPajak').change(this.handleCheckPajakChange.bind(this));
        $('#checkPajakPb1').change(this.handleCheckPajakPb1Change.bind(this));

        // Pajak calculations
        $('#persenPajak, #persenPb1').on('input', this.calculateTotalBersih);

        // Kegiatan events
        $(document).on('change', '.kegiatan-select', this.handleKegiatanChange.bind(this));
        $(document).on('change', '.rekening-select', this.handleRekeningChange.bind(this));

        // Remove kegiatan
        $(document).on('click', '.remove-kegiatan', this.handleRemoveKegiatan.bind(this));

        // Save button
        $('#saveBtn').on('click', this.handleSaveClick.bind(this));

        // Nota validation
        $('#nomor_nota').on('blur', this.handleNotaBlur.bind(this));
        $('#nomor_nota').on('focus', this.handleNotaFocus.bind(this));

        $('#editBungaModal').on('hidden.bs.modal', this.handleEditBungaModalHidden);
        $(document).on('click', '#btnEditBunga', this.handleEditBunga.bind(this));

        // BKU operation events
        this.handleBkuOperations();
    }

    /**
     * Initialize Select2
     */
    initializeSelect2() {
        try {
            console.log('Initializing Select2...');
            
            // Destroy existing Select2 instances
            $('.kegiatan-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                    console.log('Destroyed existing Select2 on kegiatan-select');
                }
            });
            
            $('.rekening-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                    console.log('Destroyed existing Select2 on rekening-select');
                }
            });
            
            // Initialize Select2 dengan konfigurasi yang sederhana
            $('.kegiatan-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#transactionModal'),
                minimumResultsForSearch: 1
            });
            
            $('.rekening-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#transactionModal'),
                minimumResultsForSearch: 1
            });
            
            console.log('Select2 initialized successfully');
        } catch (error) {
            console.error('Error initializing Select2:', error);
        }
    }

    /**
     * Scroll to top of modal
     */
    scrollToTop() {
        $('.modal-body').animate({
            scrollTop: 0
        }, 300);
    }

    /**
     * Format date to YYYY-MM-DD for input
     */
    formatDateForInput(date) {
        const year = date.getUTCFullYear();
        const month = String(date.getUTCMonth() + 1).padStart(2, '0');
        const day = String(date.getUTCDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Format angka ke Rupiah
     */
    formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    /**
     * Validasi tanggal
     */
    validateDate(inputElement, bulan, tahun) {
        const selectedValue = inputElement.value;
        
        if (!selectedValue) {
            return true;
        }

        // Parse tanggal langsung dari string format YYYY-MM-DD
        const [year, month, day] = selectedValue.split('-').map(Number);
        
        // Dapatkan range tanggal
        const dateRange = this.getDateRangeForMonth(bulan, tahun);
        const [minYear, minMonth, minDay] = dateRange.min.split('-').map(Number);
        const [maxYear, maxMonth, maxDay] = dateRange.max.split('-').map(Number);
        
        // Buat date objects untuk perbandingan (gunakan UTC untuk menghindari timezone issues)
        const selectedDate = new Date(Date.UTC(year, month - 1, day));
        const minDate = new Date(Date.UTC(minYear, minMonth - 1, minDay));
        const maxDate = new Date(Date.UTC(maxYear, maxMonth - 1, maxDay));
        
        console.log('Validating date:', {
            selected: selectedValue,
            selectedDate: selectedDate.toISOString(),
            minDate: minDate.toISOString(),
            maxDate: maxDate.toISOString()
        });

        if (selectedDate < minDate || selectedDate > maxDate) {
            // Format tanggal untuk pesan error
            const minFormatted = `${minDay} ${this.getNamaBulan(minMonth)} ${minYear}`;
            const maxFormatted = `${maxDay} ${this.getNamaBulan(maxMonth)} ${maxYear}`;
            
            let errorMessage = `Tanggal harus antara ${minFormatted} dan ${maxFormatted}`;
            
            inputElement.value = dateRange.min;
            console.log('Date validation failed, setting to min:', dateRange.min);
            
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: errorMessage,
                confirmButtonColor: '#0d6efd',
            });
            return false;
        }
        
        console.log('Date validation passed');
        return true;
    }

    /**
     * Get date range for month
     */
    getDateRangeForMonth(bulan, tahun) {
        const bulanAngka = {
            'Januari': 1, 'Februari': 2, 'Maret': 3, 'April': 4,
            'Mei': 5, 'Juni': 6, 'Juli': 7, 'Agustus': 8,
            'September': 9, 'Oktober': 10, 'November': 11, 'Desember': 12
        }[bulan] || 1;
        
        // Gunakan UTC untuk menghindari timezone issues
        const firstDay = new Date(Date.UTC(tahun, bulanAngka - 1, 1));
        const lastDay = new Date(Date.UTC(tahun, bulanAngka, 0)); // Day 0 = last day of previous month
        
        const minDate = this.formatDateForInput(firstDay);
        const maxDate = this.formatDateForInput(lastDay);
        
        return {
            min: minDate,
            max: maxDate
        };
    }

    /**
     * Get nama bulan
     */
    getNamaBulan(angkaBulan) {
        const bulanList = {
            1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
            5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
            9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
        };
        return bulanList[angkaBulan] || 'Unknown';
    }

    /**
     * Nonaktifkan tanggal di luar range
     */
    disableOutOfRangeDates() {
        const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
        $('#tanggal_transaksi, #tanggal_nota').attr({
            'min': dateRange.min,
            'max': dateRange.max
        });
    }

    /**
     * Sinkronisasi tanggal transaksi dan nota
     */
    syncTanggalTransaksiDanNota() {
        const tanggalTransaksi = $('#tanggal_transaksi').val();
        
        if (tanggalTransaksi) {
            // Gunakan nilai langsung tanpa konversi Date object
            $('#tanggal_nota').val(tanggalTransaksi);
            console.log('Tanggal nota otomatis disinkronkan dengan tanggal transaksi:', tanggalTransaksi);
        }
    }

    /**
     * Load kegiatan dan rekening
     */
    loadKegiatanDanRekening() {
        console.log('Loading kegiatan dan rekening untuk:', this.tahun, this.bulan);
        
        // Tampilkan loading state
        $('.kegiatan-select').html('<option value="">Memuat data...</option>').prop('disabled', true);
        
        $.ajax({
            url: '/bku/kegiatan-rekening/' + this.tahun + '/' + this.bulan,
            method: 'GET',
            success: (response) => {
                console.log('API Response received:', response);
                
                if (response.success) {
                    this.kegiatanData = response.kegiatan_list || [];
                    this.rekeningData = response.rekening_list || [];
                    
                    console.log('Data loaded successfully:', {
                        kegiatan: this.kegiatanData.length,
                        rekening: this.rekeningData.length
                    });

                    // Populate select kegiatan
                    this.populateKegiatanSelect();
                    
                } else {
                    console.error('Error in API response:', response.message);
                    this.showError('Gagal memuat data: ' + (response.message || 'Unknown error'));
                    this.setErrorState();
                }
            },
            error: (xhr, status, error) => {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                this.showError('Gagal terhubung ke server. Silakan coba lagi.');
                this.setErrorState();
            }
        });
    }

    /**
     * Populate kegiatan select
     */
    populateKegiatanSelect() {
        const kegiatanSelects = $('.kegiatan-select');
        
        kegiatanSelects.each((index, element) => {
            const $select = $(element);
            $select.empty();
            
            if (this.kegiatanData && this.kegiatanData.length > 0) {
                $select.append('<option value="">Pilih Jenis Kegiatan</option>');
                
                this.kegiatanData.forEach((kegiatan) => {
                    $select.append(
                        `<option value="${kegiatan.id}">${kegiatan.kode} - ${kegiatan.uraian}</option>`
                    );
                });
                
                $select.prop('disabled', false);
                console.log('Kegiatan select populated with', this.kegiatanData.length, 'items');
                
            } else {
                $select.append('<option value="" disabled>Tidak ada kegiatan tersedia untuk bulan ini</option>');
                $select.prop('disabled', true);
                console.warn('No kegiatan data available for selection');
            }
        });

        // Reinitialize Select2 setelah data dimuat
        setTimeout(() => {
            try {
                this.initializeSelect2();
                console.log('Select2 initialized after data load');
            } catch (error) {
                console.error('Error initializing Select2 after data load:', error);
            }
        }, 200);

        // Update tombol next
        if (this.kegiatanData.length === 0) {
            $('#nextBtn').prop('disabled', true);
            console.log('Next button disabled - no kegiatan data');
        } else {
            $('#nextBtn').prop('disabled', false);
            console.log('Next button enabled - kegiatan data available');
        }
    }

    /**
     * Reload kegiatan dan rekening
     */
    reloadKegiatanDanRekening() {
        console.log('Reloading data untuk bulan:', this.bulan);
        
        // Reset data
        this.kegiatanData = [];
        this.rekeningData = [];
        
        // Tampilkan loading state
        $('.kegiatan-select').html('<option value="">Memuat data...</option>').prop('disabled', true);
        $('.rekening-select').html('<option value="">Pilih kegiatan terlebih dahulu</option>').prop('disabled', true);
        
        // Load data baru
        this.loadKegiatanDanRekening();
    }

    /**
     * Tampilkan error
     */
    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#0d6efd',
        });
    }

    /**
     * Set error state
     */
    setErrorState() {
        $('.kegiatan-select').html('<option value="" disabled>Gagal memuat data</option>').prop('disabled', true);
        $('.rekening-select').html('<option value="" disabled>Pilih kegiatan terlebih dahulu</option>').prop('disabled', true);
        
        setTimeout(() => {
            this.initializeSelect2();
        }, 100);
    }

    // ==================== EVENT HANDLERS ====================

    /**
     * Handle modal show
     */
    handleModalShow() {
        console.log('=== MODAL SHOW EVENT TRIGGERED ===');
        console.log('Current bulan:', this.bulan, 'Current tahun:', this.tahun);
        
        this.resetSteps();
        
        // Reset data
        this.kegiatanData = [];
        this.rekeningData = [];
        this.currentTotalTransaksi = 0;

        // Set default dates dengan format yang benar
        const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
        console.log('Date range for', this.bulan, this.tahun, ':', dateRange);
        
        // Set atribut min/max untuk kedua tanggal
        $('#tanggal_transaksi, #tanggal_nota').attr({
            'min': dateRange.min,
            'max': dateRange.max
        });

        // Set nilai default - gunakan format langsung
        $('#tanggal_transaksi').val(dateRange.min);
        $('#tanggal_nota').val(dateRange.min);

        // Load data lainnya
        this.loadKegiatanDanRekening();
        this.loadLastNotaNumber();
        
        console.log('Tanggal transaksi dan nota di-set ke:', dateRange.min);
    }

    /**
     * Handle modal shown
     */
    handleModalShown() {
        // Pastikan sinkronisasi setelah modal terbuka
        setTimeout(() => {
            this.syncTanggalTransaksiDanNota();
        }, 100);
    }

    /**
     * Handle tanggal transaksi change
     */
    handleTanggalTransaksiChange(event) {
        const tanggalTransaksi = $(event.target).val();
        
        console.log('Tanggal transaksi diubah:', tanggalTransaksi);
        
        if (tanggalTransaksi) {
            // Validasi tanggal transaksi
            if (this.validateDate(event.target, this.bulan, this.tahun)) {
                // Sinkronkan dengan tanggal nota - GUNAKAN FORMAT LANGSUNG
                $('#tanggal_nota').val(tanggalTransaksi);
                console.log('Tanggal nota diset ke:', tanggalTransaksi);
            }
        }
    }

    /**
     * Handle tanggal nota change
     */
    handleTanggalNotaChange(event) {
        const tanggalNota = $(event.target).val();
        const tanggalTransaksi = $('#tanggal_transaksi').val();
        
        console.log('Tanggal nota diubah:', tanggalNota, 'Tanggal transaksi:', tanggalTransaksi);
        
        if (tanggalNota) {
            this.validateDate(event.target, this.bulan, this.tahun);
        }
    }

    /**
     * Handle next button click
     */
    handleNextClick() {
        console.log('Next button clicked, current step:', this.currentStep);
        if (this.currentStep < this.totalSteps) {
            if (this.currentStep === 1) {
                if (!this.validateStep1()) {
                    console.log('Step 1 validation failed');
                    return;
                }
            } else if (this.currentStep === 2) {
                if (!this.validateStep2()) {
                    console.log('Step 2 validation failed');
                    return;
                }
                
                if (!this.validateAllVolumes()) {
                    console.log('Volume validation failed');
                    return;
                }
            }
            
            this.currentStep++;
            console.log('Moving to step:', this.currentStep);
            this.updateSteps();
            this.scrollToTop();

            if (this.currentStep === 3) {
                this.updateTotalTransaksiDisplay();
            }
        }
    }

    /**
     * Handle previous button click
     */
    handlePrevClick() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateSteps();
            this.scrollToTop();
        }
    }

    /**
     * Handle check form change
     */
    handleCheckFormChange(event) {
        if ($(event.target).is(':checked')) {
            $('#formToko').addClass('d-none');
            $('#formPenerima').removeClass('d-none');
        } else {
            $('#formToko').removeClass('d-none');
            $('#formPenerima').addClass('d-none');
        }
    }

    /**
     * Handle check NPWP change
     */
    handleCheckNpwpChange(event) {
        if ($(event.target).is(':checked')) {
            $('#npwp').prop('disabled', true).val('');
        } else {
            $('#npwp').prop('disabled', false);
        }
    }

    /**
     * Handle check pajak change
     */
    handleCheckPajakChange(event) {
        if ($(event.target).is(':checked')) {
            $('#pajakForm').removeClass('d-none');
            $('#pb1Section').removeClass('d-none');
        } else {
            $('#pajakForm').addClass('d-none');
            $('#pb1Section').addClass('d-none');
            $('#pb1Form').addClass('d-none');
            $('#selectPajak').val('');
            $('#persenPajak').val('');
            $('#totalPajak').val('');
            $('#checkPajakPb1').prop('checked', false);
            $('#selectPb1').val('pb 1');
            $('#persenPb1').val('');
            $('#totalPb1').val('');
            this.calculateTotalBersih();
        }
    }

    /**
     * Handle check pajak PB1 change
     */
    handleCheckPajakPb1Change(event) {
        if ($(event.target).is(':checked')) {
            $('#pb1Form').removeClass('d-none');
        } else {
            $('#pb1Form').addClass('d-none');
            $('#selectPb1').val('pb 1');
            $('#persenPb1').val('');
            $('#totalPb1').val('');
            this.calculateTotalBersih();
        }
    }

    /**
     * Handle kegiatan change
     */
    handleKegiatanChange(event) {
        const kegiatanId = $(event.target).val();
        const card = $(event.target).closest('.kegiatan-card');
        const rekeningSelect = card.find('.rekening-select');
        const uraianContainer = card.find('.uraian-container');

        console.log('Kegiatan changed:', kegiatanId);

        // Reset rekening dan uraian
        rekeningSelect.empty();
        uraianContainer.empty();

        if (kegiatanId) {
            // Filter rekening berdasarkan kegiatan yang dipilih
            const rekeningForKegiatan = this.rekeningData.filter((rekening) => {
                return rekening.kegiatan_id == kegiatanId;
            });

            console.log('Rekening for kegiatan', kegiatanId, ':', rekeningForKegiatan.length);

            if (rekeningForKegiatan.length === 0) {
                rekeningSelect.append('<option value="" disabled>Tidak ada rekening tersedia untuk kegiatan ini</option>');
                rekeningSelect.prop('disabled', true);
                
                uraianContainer.html('<div class="alert alert-warning mt-2">Tidak ada rekening belanja yang tersedia untuk kegiatan ini.</div>');
            } else {
                rekeningSelect.append('<option value="">Pilih Rekening Belanja</option>');
                rekeningSelect.prop('disabled', false);
                
                rekeningForKegiatan.forEach((rekening) => {
                    rekeningSelect.append(
                        `<option value="${rekening.id}">${rekening.kode_rekening} - ${rekening.rincian_objek}</option>`
                    );
                });
            }

            // Reinitialize Select2 untuk rekening
            setTimeout(() => {
                rekeningSelect.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#transactionModal'),
                    minimumResultsForSearch: 10
                });
            }, 100);

        } else {
            rekeningSelect.append('<option value="">Pilih kegiatan terlebih dahulu</option>');
            rekeningSelect.prop('disabled', true);
            
            setTimeout(() => {
                rekeningSelect.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#transactionModal'),
                    minimumResultsForSearch: 10
                });
            }, 100);
        }
    }

    /**
     * Handle rekening change
     */
    handleRekeningChange(event) {
        const rekeningId = $(event.target).val();
        const card = $(event.target).closest('.kegiatan-card');
        const kegiatanSelect = card.find('.kegiatan-select');
        const kegiatanId = kegiatanSelect.val();
        const uraianContainer = card.find('.uraian-container');
        const kegiatanIndex = card.data('kegiatan-index');

        console.log('Rekening changed:', rekeningId, 'for kegiatan:', kegiatanId);

        if (rekeningId && kegiatanId) {
            uraianContainer.html(
                '<div class="text-center py-3"> <div class="spinner-border spinner-border-sm" role="status"></div> Memuat uraian... </div>'
            );

            $.ajax({
                url: '/bku/uraian/' + this.tahun + '/' + this.bulan + '/' + rekeningId + '?kegiatan_id=' + kegiatanId,
                method: 'GET',
                success: (response) => {
                    uraianContainer.empty();

                    if (response.success) {
                        if (response.data && response.data.length > 0) {
                            this.renderUraianOptions(response.data, kegiatanIndex, uraianContainer);
                        } else {
                            uraianContainer.html(
                                '<p class="text-muted">Tidak ada uraian untuk kombinasi kegiatan dan rekening ini.</p>'
                            );
                        }
                    } else {
                        uraianContainer.html(
                            '<p class="text-danger">Error: ' + (response.message || 'Gagal memuat uraian') + '</p>'
                        );
                    }
                },
                error: (xhr) => {
                    console.error('Error loading uraian:', xhr);
                    uraianContainer.html(
                        '<p class="text-danger">Error loading uraian. Silakan coba lagi.</p>'
                    );
                }
            });
        } else {
            uraianContainer.empty();
            if (!kegiatanId) {
                uraianContainer.html('<p class="text-warning">Pilih kegiatan terlebih dahulu</p>');
            }
        }
    }

    /**
     * Handle remove kegiatan
     */
    handleRemoveKegiatan(event) {
        const card = $(event.target).closest('.kegiatan-card');
        const cardCount = $('.kegiatan-card').length;

        if (cardCount > 1) {
            card.remove();

            $('.kegiatan-card').each((index, element) => {
                $(element).find('.card-title').text(`Kegiatan ${index + 1}`);
                $(element).attr('data-kegiatan-index', index + 1);
            });

            if ($('.kegiatan-card').length === 1) {
                $('.remove-kegiatan').addClass('d-none');
            }
        }
    }

    /**
     * Handle save button click untuk BKU
     */
    handleSaveClick() {
        if ($('#checkPajak').is(':checked')) {
            const selectPajak = $('#selectPajak').val();
            const persenPajak = $('#persenPajak').val();

            if (!selectPajak) {
                this.showValidationError('Jenis pajak harus dipilih');
                return;
            }

            if (!persenPajak || parseFloat(persenPajak) <= 0) {
                this.showValidationError('Persen pajak harus diisi dengan nilai yang valid');
                return;
            }
        }

        if ($('#checkPajakPb1').is(':checked')) {
            const persenPb1 = $('#persenPb1').val();

            if (!persenPb1 || parseFloat(persenPb1) <= 0) {
                this.showValidationError('Persen pajak daerah harus diisi dengan nilai yang valid');
                return;
            }
        }

        const formData = this.collectFormData();
        
        if (!formData) return;
        
        this.submitFormData(formData);
    }

    /**
     * Handle nota blur
     */
    handleNotaBlur(event) {
        const notaValue = $(event.target).val().trim();
        
        if (notaValue && !this.validateNotaFormat(notaValue)) {
            Swal.fire({
                icon: 'warning',
                title: 'Format Nomor Nota',
                text: 'Nomor nota harus mengandung angka. Contoh: 001, BP001, NOTA-001, dll.',
                confirmButtonColor: '#0d6efd',
            });
            
            $(event.target).focus();
            return;
        }
        
        if (/^\d+$/.test(notaValue)) {
            const number = parseInt(notaValue) || 1;
            $(event.target).val(String(number).padStart(3, '0'));
        }
    }

    /**
     * Handle nota focus
     */
    handleNotaFocus(event) {
        const currentValue = $(event.target).val();
        if (!currentValue) {
            $(event.target).attr('placeholder', 'Contoh: 001, BP001, NOTA-001, INV-2024-001');
        }
    }

    // ==================== VALIDATION METHODS ====================

    /**
     * Validasi step 1
     */
    validateStep1() {
        const tanggalTransaksi = $('#tanggal_transaksi').val();
        const jenisTransaksi = $('#jenis_transaksi').val();
        const namaToko = $('#nama_toko').val();
        const namaPenerima = $('#nama_penerima').val();
        const alamat = $('#alamat_toko').val();
        const isTokoChecked = $('#checkForm').is(':checked');
        
        if (!tanggalTransaksi) {
            this.showValidationError('Tanggal transaksi harus diisi');
            $('#tanggal_transaksi').focus();
            return false;
        }

        if (!this.validateDate(document.getElementById('tanggal_transaksi'), this.bulan, this.tahun)) {
            return false;
        }
        
        if (!jenisTransaksi) {
            this.showValidationError('Jenis transaksi harus dipilih');
            $('#jenis_transaksi').focus();
            return false;
        }
        
        if (!isTokoChecked && !namaToko) {
            this.showValidationError('Nama toko/badan usaha harus diisi');
            $('#nama_toko').focus();
            return false;
        }
        
        if (isTokoChecked && !namaPenerima) {
            this.showValidationError('Nama penerima/penyedia harus diisi');
            $('#nama_penerima').focus();
            return false;
        }
        
        if (!alamat) {
            this.showValidationError('Alamat harus diisi');
            $('#alamat_toko').focus();
            return false;
        }
        
        return true;
    }

    /**
     * Validasi step 2
     */
    validateStep2() {
        const nomorNota = $('#nomor_nota').val();
        const tanggalNota = $('#tanggal_nota').val();

        console.log('Validating step 2:', { nomorNota, tanggalNota });
        
        // Validasi jika tidak ada kegiatan yang tersedia
        if (this.kegiatanData && this.kegiatanData.length === 0) {
            this.showValidationError('Tidak ada kegiatan yang tersedia untuk bulan ' + this.bulan + '. Silakan pilih bulan lain.');
            return false;
        }
        
        if (!nomorNota) {
            this.showValidationError('Nomor nota harus diisi');
            $('#nomor_nota').focus();
            return false;
        }
        
        if (!tanggalNota) {
            this.showValidationError('Tanggal belanja/nota harus diisi');
            $('#tanggal_nota').focus();
            return false;
        }

        if (!this.validateDate(document.getElementById('tanggal_nota'), this.bulan, this.tahun)) {
            return false;
        }
        
        const kegiatanDipilih = $('.kegiatan-select').filter(function() {
            return $(this).val() !== '';
        }).length > 0;

        if (!kegiatanDipilih) {
            this.showValidationError('Minimal satu kegiatan harus dipilih');
            return false;
        }

        const uraianDipilih = $('.uraian-checkbox:checked').length > 0;
        if (!uraianDipilih) {
            this.showValidationError('Minimal satu uraian harus dipilih');
            return false;
        }

        let volumeMelebihiMaksimal = false;
        let uraianMelebihi = '';
        
        $('.uraian-checkbox:checked').each((index, element) => {
            const uraianItem = $(element).closest('.uraian-item');
            const jumlahInput = uraianItem.find('.jumlah-input');
            const maxVolume = parseFloat(jumlahInput.attr('max')) || 0;
            const volume = parseFloat(jumlahInput.val()) || 0;
            const uraianText = uraianItem.find('.form-check-label').text();
            
            if (volume > maxVolume) {
                volumeMelebihiMaksimal = true;
                uraianMelebihi = uraianText;
                return false;
            }
        });
        
        if (volumeMelebihiMaksimal) {
            Swal.fire({
                icon: 'error',
                title: 'Volume Melebihi Maksimal',
                html: `Maaf, jumlah volume melebihi jumlah maksimal untuk uraian:<br><strong>${uraianMelebihi}</strong>`,
                confirmButtonColor: '#0d6efd',
            });
            return false;
        }
        
        console.log('Step 2 validation passed');
        return true;
    }

    /**
     * Tampilkan pesan error validasi
     */
    showValidationError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#0d6efd',
        });
    }

    /**
     * Update steps
     */
    updateSteps() {
        $('.step-pane').addClass('d-none');
        $(`#step${this.currentStep}`).removeClass('d-none');
        
        $('.step-item').removeClass('active completed');
        $('.step-item').each((index, element) => {
            const step = parseInt($(element).data('step'));
            if (step < this.currentStep) {
                $(element).addClass('completed');
            } else if (step === this.currentStep) {
                $(element).addClass('active');
            }
        });
        
        if (this.currentStep === 3) {
            this.renderKegiatanCardsStep3();
        }
        
        $('#prevBtn').prop('disabled', this.currentStep === 1);
        
        if (this.currentStep === this.totalSteps) {
            $('#nextBtn').addClass('d-none');
            $('#saveBtn').removeClass('d-none');
        } else {
            $('#nextBtn').removeClass('d-none');
            $('#saveBtn').addClass('d-none');
        }
    }

    /**
     * Reset steps
     */
    resetSteps() {
        this.currentStep = 1;
        $('.step-item').removeClass('active completed');
        $('.step-item:first').addClass('active');
        $('.step-pane').addClass('d-none');
        $('#step1').removeClass('d-none');
        $('#prevBtn').prop('disabled', true);
        $('#nextBtn').removeClass('d-none');
        $('#saveBtn').addClass('d-none');
    }

    // ==================== CALCULATION METHODS ====================

    /**
     * Hitung total bersih setelah pajak
     */
    calculateTotalBersih() {
        const totalTransaksi = this.currentTotalTransaksi || 0;
        let totalPajak = 0;
        let totalPb1 = 0;

        if ($('#checkPajak').is(':checked') && $('#selectPajak').val()) {
            const persenPajak = parseFloat($('#persenPajak').val()) || 0;
            totalPajak = (totalTransaksi * persenPajak) / 100;
            $('#totalPajak').val(this.formatRupiah(totalPajak));
        }

        if ($('#checkPajakPb1').is(':checked')) {
            const persenPb1 = parseFloat($('#persenPb1').val()) || 0;
            totalPb1 = (totalTransaksi * persenPb1) / 100;
            $('#totalPb1').val(this.formatRupiah(totalPb1));
        }

        const totalBersih = totalTransaksi - totalPajak - totalPb1;
        $('#totalBersih').text(`Rp ${this.formatRupiah(totalBersih)}`);
    }

    /**
     * Validasi semua volume
     */
    validateAllVolumes() {
        let volumeMelebihiMaksimal = false;
        let uraianDetails = [];
        
        $('.jumlah-input').each((index, element) => {
            const value = parseInt($(element).val()) || 0;
            const maxVolume = parseFloat($(element).attr('max')) || 0;
            const uraianText = $(element).data('uraian-text') || $(element).closest('.card-body').find('.form-check-label').text();
            const isChecked = $(element).closest('.uraian-item').find('.uraian-checkbox').is(':checked');
            
            if (isChecked && value > maxVolume) {
                volumeMelebihiMaksimal = true;
                uraianDetails.push({
                    uraian: uraianText,
                    volume: value,
                    max_volume: maxVolume
                });
            }
        });
        
        if (volumeMelebihiMaksimal) {
            let errorMessage = 'Maaf, jumlah volume melebihi jumlah maksimal untuk uraian berikut:<br><br>';
            
            uraianDetails.forEach((detail, index) => {
                errorMessage += `<strong>${index + 1}. ${detail.uraian}</strong><br>`;
                errorMessage += `Volume: ${detail.volume} (Maks: ${detail.max_volume})<br><br>`;
            });
            
            errorMessage += 'Silakan perbaiki volume sebelum melanjutkan.';
            
            Swal.fire({
                icon: 'error',
                title: 'Volume Melebihi Maksimal',
                html: errorMessage,
                confirmButtonColor: '#0d6efd',
            });
            
            return false;
        }
        
        return true;
    }

    // ==================== RENDERING METHODS ====================

    /**
     * Render opsi uraian
     */
    renderUraianOptions(data, kegiatanIndex, container) {
        container.empty();
        
        if (data.length === 0) {
            container.html('<div class="alert alert-info">Tidak ada data uraian untuk rekening ini</div>');
            return;
        }
        
        // Hitung uraian yang dapat digunakan
        const uraianDapatDigunakan = data.filter(uraian => uraian.dapat_digunakan).length;
        
        let uraianHtml = `
        <div class="col-sm-12 justify-content-between align-content-between d-flex mb-3">
            <div class="label">
                <p class="mb-0 fw-bold">Uraian</p>
                <small class="text-muted">
                    Total RKAS: ${data.reduce((sum, u) => sum + u.total_volume, 0)} | 
                    Sudah: ${data.reduce((sum, u) => sum + u.volume_sudah_dibelanjakan, 0)} | 
                    Sisa: ${data.reduce((sum, u) => sum + u.sisa_volume, 0)}
                </small>
                ${uraianDapatDigunakan === 0 ? '<div class="text-danger mt-1">Semua uraian sudah habis dibelanjakan</div>' : ''}
            </div>
            ${uraianDapatDigunakan > 0 ? `
            <div class="form-check">
                <input class="form-check-input check-all-uraian" type="checkbox" data-kegiatan-index="${kegiatanIndex}">
                <label class="form-check-label">
                    <strong>Pilih</strong> semua uraian
                </label>
            </div>
            ` : ''}
        </div>
        <hr class="mt-2 mb-3">
        `;
        
        data.forEach((uraian, index) => {
            const isDisabled = !uraian.dapat_digunakan;
            const disabledClass = isDisabled ? 'text-muted' : '';
            const disabledAttr = isDisabled ? 'disabled' : '';
            
            uraianHtml += `
            <div class="card mb-3 uraian-item ${isDisabled ? 'bg-light' : ''}" data-uraian-id="${uraian.id}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input uraian-checkbox" type="checkbox" name="uraian" value="${uraian.id}"
                                    ${isDisabled ? 'disabled' : ''}>
                                <label class="form-check-label fw-bold ${disabledClass}">
                                    ${uraian.uraian}
                                </label>
                                <input type="hidden" class="uraian-text-input" value="${uraian.uraian}">
                                <span class="satuan-text d-none">${uraian.satuan || ''}</span>
                            </div>
                        </div>
                    </div>
                    
                    ${!isDisabled ? `
                    <div class="row mt-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Jumlah yang akan dibelanjakan</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control jumlah-input" value="${uraian.sisa_volume}" min="1" 
                                    max="${uraian.sisa_volume}" aria-label="Jumlah" 
                                    oninput="window.bkuManager.validateVolumeInput(this, ${uraian.sisa_volume}, '${uraian.uraian.replace(/'/g, "\\'")}')">
                                <span class="input-group-text">Maks. ${uraian.sisa_volume}</span>
                            </div>
                            <small class="text-muted">Sisa volume: ${uraian.sisa_volume} ${uraian.satuan}</small>
                            <div class="text-danger volume-error" style="display: none;">
                                <small><i class="bi bi-exclamation-circle"></i> Jumlah melebihi sisa volume</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Harga Satuan</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control harga-input" value="${this.formatRupiah(uraian.harga_satuan)}"
                                    aria-label="Harga" readonly>
                            </div>
                            <small class="text-muted">Harga tetap sesuai RKAS</small>
                        </div>
                    </div>
                    ` : `
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Uraian tidak dapat digunakan - Volume sudah habis</strong>
                    </div>
                    `}
                </div>
            </div>
            `;
        });
        
        container.html(uraianHtml);
        
        // Hanya tambahkan event listener jika ada uraian yang dapat digunakan
        if (uraianDapatDigunakan > 0) {
            $(`.check-all-uraian[data-kegiatan-index="${kegiatanIndex}"]`).change((event) => {
                const isChecked = $(event.target).is(':checked');
                container.find('.uraian-checkbox:not(:disabled)').prop('checked', isChecked);
                container.find('.uraian-item:not(.bg-light)').each((index, element) => {
                    this.updateUraianSubtotal($(element));
                });
                this.updateTotalTransaksiDisplay();
            });
            
            container.find('.uraian-checkbox:not(:disabled)').change((event) => {
                this.updateUraianSubtotal($(event.target).closest('.uraian-item'));
                this.updateTotalTransaksiDisplay();
            });
            
            container.find('.jumlah-input').on('input', (event) => {
                const maxVolume = parseFloat($(event.target).attr('max')) || 0;
                const uraianText = $(event.target).closest('.card-body').find('.form-check-label').text();
                this.validateVolumeInput(event.target, maxVolume, uraianText);
                this.updateUraianSubtotal($(event.target).closest('.uraian-item'));
                this.updateTotalTransaksiDisplay();
            });
        }
    }

    /**
     * Validasi input volume
     */
    validateVolumeInput(inputElement, maxVolume, uraianText) {
        const value = parseInt(inputElement.value) || 0;
        const errorElement = $(inputElement).closest('.mb-2').find('.volume-error');
        
        if (value > maxVolume) {
            errorElement.show();
            inputElement.setCustomValidity('Jumlah volume melebihi maksimal');
            $(inputElement).data('exceeds-limit', true);
            $(inputElement).data('uraian-text', uraianText);
        } else {
            errorElement.hide();
            inputElement.setCustomValidity('');
            $(inputElement).data('exceeds-limit', false);
            $(inputElement).removeData('uraian-text');
        }
    }

    /**
     * Update subtotal uraian
     */
    updateUraianSubtotal(uraianItem) {
        const checkbox = uraianItem.find('.uraian-checkbox');
        const jumlahInput = uraianItem.find('.jumlah-input');
        const hargaInput = uraianItem.find('.harga-input');
        const errorElement = uraianItem.find('.volume-error');

        if (checkbox.is(':checked')) {
            const jumlah = parseFloat(jumlahInput.val()) || 0;
            const maxVolume = parseFloat(jumlahInput.attr('max')) || 0;
            const hargaText = hargaInput.val().replace(/[^\d]/g, '');
            const harga = parseFloat(hargaText) || 0;
            
            if (jumlah > maxVolume) {
                errorElement.show();
                jumlahInput[0].setCustomValidity('Jumlah volume melebihi maksimal');
            } else {
                errorElement.hide();
                jumlahInput[0].setCustomValidity('');
            }
        } else {
            errorElement.hide();
            jumlahInput[0].setCustomValidity('');
        }
    }

    /**
     * Render card kegiatan di step 3
     */
    renderKegiatanCardsStep3() {
        try {
            console.log('Rendering kegiatan cards for step 3');
            const kegiatanCardsStep3 = $('#kegiatanCardsStep3');
            kegiatanCardsStep3.empty();
            
            const kegiatanCards = $('.kegiatan-card');
            console.log('Found', kegiatanCards.length, 'kegiatan cards');

            if (kegiatanCards.length === 0) {
                kegiatanCardsStep3.html('<div class="col-12"><p class="text-muted">Tidak ada data kegiatan</p></div>');
                return;
            }

            $('.kegiatan-card').each((index, element) => {
                const kegiatanIndex = $(element).data('kegiatan-index');
                const kegiatanSelect = $(element).find('.kegiatan-select');
                const rekeningSelect = $(element).find('.rekening-select');
                const kegiatanText = kegiatanSelect.find('option:selected').text() || 'Belum dipilih';
                const rekeningText = rekeningSelect.find('option:selected').text() || 'Belum dipilih';

                let uraianList = '';
                $(element).find('.uraian-item').each((itemIndex, itemElement) => {
                    const checkbox = $(itemElement).find('.uraian-checkbox');
                    if (checkbox.is(':checked')) {
                        const uraianLabel = $(itemElement).find('.form-check-label').text();
                        const jumlah = $(itemElement).find('.jumlah-input').val() || 0;
                        const hargaText = $(itemElement).find('.harga-input').val().replace(/[^\d]/g, '');
                        const harga = parseFloat(hargaText) || 0;
                        const subtotal = jumlah * harga;

                        uraianList += `
                        <div class="mb-2">
                            <div class="fw-bold">${uraianLabel}</div>
                            <div class="small">Jumlah: ${jumlah} | Harga: Rp ${this.formatRupiah(harga)} | Subtotal: Rp ${this.formatRupiah(subtotal)}</div>
                        </div>
                    `;
                    }
                });

                if (!uraianList) {
                    uraianList = '<div class="text-muted">Tidak ada uraian yang dipilih</div>';
                }

                kegiatanCardsStep3.append(`
                <div class="col-md-12 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-info-subtle">
                            <strong>Kegiatan ${kegiatanIndex}</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Kegiatan:</strong> ${kegiatanText}
                            </div>
                            <div class="mb-2">
                                <strong>Rekening:</strong> ${rekeningText}
                            </div>
                            <div class="mt-3">
                                <strong>Uraian yang dipilih:</strong>
                                ${uraianList}
                            </div>
                        </div>
                    </div>
                </div>
            `);
            });

            console.log('Kegiatan cards rendered successfully');

        } catch (error) {
            console.error('Error rendering kegiatan cards:', error);
            $('#kegiatanCardsStep3').html('<div class="col-12"><p class="text-danger">Error rendering data</p></div>');
        }
    }

    /**
     * Hitung dan tampilkan total transaksi
     */
    updateTotalTransaksiDisplay() {
        let totalTransaksi = 0;

        $('.uraian-item').each((index, element) => {
            const checkbox = $(element).find('.uraian-checkbox');
            if (checkbox.is(':checked')) {
                const jumlah = parseFloat($(element).find('.jumlah-input').val()) || 0;
                const hargaText = $(element).find('.harga-input').val().replace(/[^\d]/g, '');
                const harga = parseFloat(hargaText) || 0;
                totalTransaksi += jumlah * harga;
            }
        });

        this.currentTotalTransaksi = totalTransaksi;
        this.calculateTotalBersih();
    }

    // ==================== FORM HANDLING METHODS ====================

    /**
     * Kumpulkan data form
     */
    collectFormData() {
        // PERBAIKAN: Ambil penganggaran_id dari berbagai sumber
        let penganggaranId = '';
        
        // 1. Coba dari meta tag
        const metaPenganggaranId = document.querySelector('meta[name="penganggaran-id"]')?.content;
        if (metaPenganggaranId) {
            penganggaranId = metaPenganggaranId;
        }
        // 2. Coba dari URL (jika ada di query string)
        else if (window.location.search) {
            const urlParams = new URLSearchParams(window.location.search);
            penganggaranId = urlParams.get('penganggaran_id');
        }
        // 3. Coba dari data attribute di body/container
        else {
            const container = document.querySelector('[data-penganggaran-id]');
            if (container) {
                penganggaranId = container.dataset.penganggaranId;
            }
        }
        
        console.log('Penganggaran ID collected:', penganggaranId);
        
        if (!penganggaranId) {
            console.error('Penganggaran ID not found!');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Data penganggaran tidak ditemukan. Silakan refresh halaman.',
            });
            return null;
        }
        const formData = {
            penganggaran_id: penganggaranId,
            tanggal_transaksi: $('#tanggal_transaksi').val(),
            jenis_transaksi: $('#jenis_transaksi').val(),
            nama_penyedia: $('#nama_toko').val(),
            nama_penerima: $('#nama_penerima').val(),
            alamat: $('#alamat_toko').val(),
            nomor_telepon: $('#nomor_telepon').val(),
            npwp: $('#checkNpwp').is(':checked') ? null : $('#npwp').val(),
            nomor_nota: $('#nomor_nota').val(),
            tanggal_nota: $('#tanggal_nota').val(),
            uraian_opsional: $('#uraian_opsional').val(),
            bulan: this.bulan,
            kode_kegiatan_id: [],
            kode_rekening_id: [],
            uraian_items: [],
            pajak_items: [],
            total_transaksi_kotor: this.currentTotalTransaksi
        };

        $('.kegiatan-card').each((index, element) => {
            const kegiatanId = $(element).find('.kegiatan-select').val();
            const rekeningId = $(element).find('.rekening-select').val();

            if (kegiatanId && rekeningId) {
                formData.kode_kegiatan_id.push(kegiatanId);
                formData.kode_rekening_id.push(rekeningId);

                $(element).find('.uraian-item').each((itemIndex, itemElement) => {
                    const checkbox = $(itemElement).find('.uraian-checkbox');
                    if (checkbox.is(':checked')) {
                        const jumlah = parseFloat($(itemElement).find('.jumlah-input').val()) || 0;
                        const hargaText = $(itemElement).find('.harga-input').val().replace(/[^\d]/g, '');
                        const harga = parseFloat(hargaText) || 0;
                        const uraianText = $(itemElement).find('.uraian-text-input').val();
                        const satuan = $(itemElement).find('.satuan-text').text().trim();

                        formData.uraian_items.push({
                            id: checkbox.val(),
                            uraian_text: uraianText,
                            satuan: satuan,
                            kegiatan_id: kegiatanId,
                            rekening_id: rekeningId,
                            volume: jumlah,
                            harga_satuan: harga,
                            jumlah_belanja: jumlah * harga
                        });
                    }
                });
            }
        });

        if ($('#checkPajak').is(':checked') && $('#selectPajak').val()) {
            formData.pajak_items.push({
                jenis_pajak: $('#selectPajak').val(),
                persen_pajak: parseFloat($('#persenPajak').val()) || 0,
                total_pajak: parseFloat($('#totalPajak').val().replace(/[^\d]/g, '')) || 0
            });
        }

        if ($('#checkPajakPb1').is(':checked')) {
            formData.pajak_items.push({
                jenis_pajak: $('#selectPb1').val(),
                persen_pajak: parseFloat($('#persenPb1').val()) || 0,
                total_pajak: parseFloat($('#totalPb1').val().replace(/[^\d]/g, '')) || 0
            });
        }

        console.log('Form data collected:', formData);
        return formData;
    }

    // mencoba ajax bku table
    attachModalResetEvents() {
        // Reset form saat modal ditutup (baik dengan tombol close, cancel, atau backdrop)
        $('#transactionModal').on('hidden.bs.modal', () => {
            console.log('Modal hidden - resetting form');
            this.resetModalForm();
        });
        
        // Reset form saat tombol batal diklik
        $('button[data-bs-dismiss="modal"]').on('click', () => {
            console.log('Cancel button clicked - resetting form');
            this.resetModalForm();
        });
        
        // Juga reset saat modal dibuka (untuk memastikan clean state)
        $('#transactionModal').on('show.bs.modal', () => {
            console.log('Modal shown - ensuring form is reset');
            this.resetModalForm();
        });
    }

    /**
     * Load data tabel saat halaman pertama kali dibuka - DIPERBAIKI
     */
    loadInitialTableData() {
        console.log('Loading initial table data...');
        
        // Tampilkan loading pada tabel
        $('#bkuTableBody').html(`
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data transaksi...</p>
                </td>
            </tr>
        `);
        
        // Panggil API untuk ambil data
        $.ajax({
            url: `/bku/table-data/${this.tahun}/${encodeURIComponent(this.bulan)}`,
            method: 'GET',
            success: (response) => {
                console.log('Initial table data response:', response);
                
                if (response.success && response.html) {
                    // Update tabel dengan data
                    $('#bkuTableBody').html(response.html);
                    
                    // Update summary data
                    if (response.data && response.data.summary) {
                        this.updateSummaryDisplay(response.data.summary);
                    }
                    
                    // Tentukan status BKU dari response atau UI
                    const isClosed = this.getBkuStatus();
                    const hasTransactions = response.data && 
                                        ((response.data.bku_data && response.data.bku_data.length > 0) ||
                                        (response.data.penarikan_tunais && response.data.penarikan_tunais.length > 0) ||
                                        (response.data.setor_tunais && response.data.setor_tunais.length > 0) ||
                                        (response.data.penerimaan_danas && response.data.penerimaan_danas.length > 0));
                    
                    console.log('Determined status:', { isClosed, hasTransactions });
                    
                    // Update tombol hapus semua berdasarkan data aktual
                    this.updateHapusSemuaButton();
                    
                    // Attach event listeners untuk row
                    this.attachBkuEventListeners();
                    
                    console.log('Initial table data loaded successfully');
                } else {
                    console.error('Failed to load initial table data:', response.message);
                    this.showTableError(response.message || 'Gagal memuat data awal');
                }
            },
            error: (xhr, status, error) => {
                console.error('Error loading initial table data:', error);
                this.showTableError('Gagal memuat data. Silakan refresh halaman.');
            }
        });
    }

    /**
     * Tampilkan error di tabel
     */
    showTableError(message) {
        $('#bkuTableBody').html(`
            <tr>
                <td colspan="9" class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${message}
                    <br>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh Halaman
                    </button>
                </td>
            </tr>
        `);
    }
    
    /**
     * Kirim data BKU ke server - VERSI SIMPLE
     */
    submitFormData(formData) {
        const button = $('#saveBtn');
        const originalText = button.html();
        
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

        $.ajax({
            url: '/bku/store',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                button.prop('disabled', false).html(originalText);

                if (response.success) {
                    console.log('Save successful, response:', response);
                    
                    // Hanya tampilkan pesan sukses sederhana
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        confirmButtonColor: '#0d6efd',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Tutup modal transaksi
                        $('#transactionModal').modal('hide');
                        
                        // Reset form modal
                        this.resetModalForm();
                        
                        // Update UI tanpa reload
                        this.handleBkuSaved(response);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message,
                        confirmButtonColor: '#0d6efd',
                    });
                }
            },
            error: (xhr) => {
                button.prop('disabled', false).html(originalText);
                
                console.error('Error submitting form:', xhr);

                let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonColor: '#0d6efd',
                });
            }
        });
    }

    /**
     * Tampilkan modal detail untuk BKU yang baru dibuat
     */
    showDetailModalForNewBku(bkuData) {
        // Jika response memiliki data BKU yang baru dibuat
        if (bkuData && bkuData.length > 0) {
            // Ambil ID BKU pertama (biasanya hanya satu kecuali multi-kegiatan)
            const firstBkuId = bkuData[0].id;
            
            // Tunggu sebentar agar tabel diperbarui dulu
            setTimeout(() => {
                // Coba tampilkan modal detail
                this.showDetailModal(firstBkuId);
            }, 500);
        }
    }

    /**
     * Tampilkan modal detail - VERSI SEDERHANA
     */
    showDetailModal(bkuId) {
        console.log('Showing detail modal for ID:', bkuId);
        
        const modal = $(`#detailModal${bkuId}`);
        
        if (modal.length > 0) {
            modal.modal('show');
            console.log('Modal found and shown');
        } else {
            console.log('Modal not found for BKU ID:', bkuId);
            
            // Tampilkan pesan error
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Detail transaksi tidak ditemukan. Silakan refresh halaman.',
                confirmButtonColor: '#0d6efd',
            });
        }
    }

    /**
     * Ambil data detail dan buat modal secara dinamis
     */
    fetchAndCreateDetailModal(bkuId) {
        $.ajax({
            url: `/bku/${bkuId}/detail`, // Anda perlu membuat route dan controller untuk ini
            method: 'GET',
            success: (response) => {
                if (response.success && response.html) {
                    // Tambahkan modal ke body
                    $('body').append(response.html);
                    
                    // Tampilkan modal
                    $(`#detailModal${bkuId}`).modal('show');
                    
                    // Re-attach event listeners
                    this.attachBkuEventListeners();
                } else {
                    console.error('Failed to fetch detail data:', response.message);
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: 'Detail transaksi berhasil disimpan. Untuk melihat detail lengkap, silakan refresh halaman.',
                        confirmButtonColor: '#0d6efd',
                    });
                }
            },
            error: (xhr) => {
                console.error('Error fetching detail:', xhr);
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Transaksi berhasil disimpan. Untuk melihat detail lengkap, silakan refresh halaman.',
                    confirmButtonColor: '#0d6efd',
                });
            }
        });
    }

    /**
     * Reset form modal
     */
    resetModalForm() {
        console.log('=== RESET MODAL FORM START ===');
        
        // Reset current step
        this.currentStep = 1;
        
        // Reset UI steps
        $('.step-item').removeClass('active completed');
        $('.step-item:first').addClass('active');
        $('.step-pane').addClass('d-none');
        $('#step1').removeClass('d-none');
        $('#prevBtn').prop('disabled', true);
        $('#nextBtn').removeClass('d-none');
        $('#saveBtn').addClass('d-none');
        
        // Reset tanggal dengan range bulan yang benar
        const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
        $('#tanggal_transaksi').val(dateRange.min).attr({
            'min': dateRange.min,
            'max': dateRange.max
        });
        $('#tanggal_nota').val(dateRange.min).attr({
            'min': dateRange.min,
            'max': dateRange.max
        });
        
        // Reset form fields
        $('#jenis_transaksi').val('');
        
        // Reset toko/penerima section
        $('#checkForm').prop('checked', false).trigger('change');
        $('#nama_toko').val('');
        $('#nama_penerima').val('');
        $('#alamat_toko').val('');
        $('#nomor_telepon').val('');
        
        // Reset NPWP
        $('#checkNpwp').prop('checked', false).trigger('change');
        $('#npwp').val('');
        
        // Reset nota
        $('#nomor_nota').val('');
        
        // Reset uraian opsional
        $('#uraian_opsional').val('');
        
        // Reset pajak section
        $('#checkPajak').prop('checked', false).trigger('change');
        $('#selectPajak').val('');
        $('#persenPajak').val('');
        $('#totalPajak').val('');
        $('#checkPajakPb1').prop('checked', false).trigger('change');
        $('#selectPb1').val('pb 1');
        $('#persenPb1').val('');
        $('#totalPb1').val('');
        $('#totalBersih').text('Rp. 0');

        // Reset checkbox handlers
        $('#checkForm').off('change').on('change', this.handleCheckFormChange.bind(this));
        $('#checkNpwp').off('change').on('change', this.handleCheckNpwpChange.bind(this));
        $('#checkPajak').off('change').on('change', this.handleCheckPajakChange.bind(this));
        $('#checkPajakPb1').off('change').on('change', this.handleCheckPajakPb1Change.bind(this));
        
        // Reset kegiatan cards (hapus semua kecuali pertama)
        const kegiatanCards = $('.kegiatan-card');
        if (kegiatanCards.length > 1) {
            kegiatanCards.not(':first').remove();
        }
        
        // Reset kegiatan card pertama
        const firstCard = $('.kegiatan-card:first');
        firstCard.find('.card-title').text('Kegiatan 1');
        firstCard.attr('data-kegiatan-index', 1);
        firstCard.find('.remove-kegiatan').addClass('d-none');
        
        // Clear Select2 values
        if (firstCard.find('.kegiatan-select').hasClass('select2-hidden-accessible')) {
            firstCard.find('.kegiatan-select').val(null).trigger('change');
        } else {
            firstCard.find('.kegiatan-select').val('');
        }
        
        if (firstCard.find('.rekening-select').hasClass('select2-hidden-accessible')) {
            firstCard.find('.rekening-select').val(null).trigger('change');
        } else {
            firstCard.find('.rekening-select').val('').prop('disabled', true);
        }
        
        // Clear uraian container
        firstCard.find('.uraian-container').empty();
        
        // Reset step 3 cards
        $('#kegiatanCardsStep3').empty();
        
        // Reset current total
        this.currentTotalTransaksi = 0;
        
        // Clear validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Reset data kegiatan dan rekening
        this.kegiatanData = [];
        this.rekeningData = [];
        
        // Load data kegiatan dan rekening baru
        setTimeout(() => {
            this.loadKegiatanDanRekening();
            this.loadLastNotaNumber();
        }, 500);
        
        console.log('=== RESET MODAL FORM COMPLETE ===');
    }

    /**
     * Update tabel dan data lainnya - VERSI DIPERBAIKI
     */
    updateTableAndData() {
        console.log('Updating table and data...');
        
        // Tampilkan loading pada tabel
        $('#bkuTableBody').html(`
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memperbarui data...</p>
                </td>
            </tr>
        `);
        
        // Ambil data terbaru
        $.ajax({
            url: `/bku/table-data/${this.tahun}/${encodeURIComponent(this.bulan)}`,
            method: 'GET',
            success: (response) => {
                console.log('Table data response:', response);
                
                if (response.success && response.html) {
                    // Update tabel
                    $('#bkuTableBody').html(response.html);
                    
                    // Tambahkan modal HTML ke body
                    if (response.modals_html) {
                        // Hapus modal detail yang lama
                        $('.modal[id^="detailModal"]').remove();
                        
                        // Tambahkan modal baru
                        $('body').append(response.modals_html);
                        
                        console.log('Detail modals added to DOM');
                    }
                    
                    // Update summary data jika ada
                    if (response.data && response.data.summary) {
                        this.updateSummaryDisplay(response.data.summary);
                    }
                    
                    // Update card anggaran
                    this.updateAnggaranCardFromSummary();
                    
                    // Re-attach event listeners untuk row baru
                    this.attachBkuEventListeners();
                    
                    console.log('Table and data updated successfully');
                    
                    // HAPUS: Jangan tampilkan modal otomatis
                    // Biarkan user yang memutuskan untuk membuka modal
                    
                } else {
                    console.error('Failed to update table:', response.message);
                    
                    // Tampilkan pesan error di tabel
                    $('#bkuTableBody').html(`
                        <tr>
                            <td colspan="9" class="text-center py-5 text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Gagal memuat data: ${response.message || 'Unknown error'}
                            </td>
                        </tr>
                    `);
                }
            },
            error: (xhr, status, error) => {
                console.error('Error updating table:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                // Tampilkan pesan error di tabel
                $('#bkuTableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Gagal memperbarui data tabel.
                            <br>
                            <small class="text-muted">${error || 'Unknown error'}</small>
                        </td>
                    </tr>
                `);
            }
        });
    }

    /**
     * Update summary data
     */
    updateSummaryData(summary) {
        console.log('Updating summary data:', summary);
        
        // Update total dibelanjakan bulan ini
        const totalDibelanjakanBulanIni = summary.total_dibelanjakan_bulan_ini || 0;
        $('input[value*="Dibelanjakan"]').closest('.fw-semibold').find('input').val(
            'Rp ' + this.formatRupiah(totalDibelanjakanBulanIni)
        );
        
        // Update saldo
        if (summary.saldo) {
            $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(summary.saldo.non_tunai || 0));
            $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(summary.saldo.tunai || 0));
            
            // Update total dana tersedia
            const totalDanaTersedia = summary.total_dana_tersedia || 0;
            $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDanaTersedia));
        }
    }
    
    /**
     * Ambil data summary dari server
     */
    fetchSummaryData() {
        return $.ajax({
            url: `/bku/summary-data/${this.tahun}/${this.bulan}`,
            method: 'GET'
        });
    }

    /**
     * Attach event untuk update otomatis
     */
    attachAutoUpdateEvents() {
        // Update saat modal ditutup (untuk penarikan/setor tunai)
        $(document).on('bkuSaved', (e, data) => {
            this.updateTableAndData();
        });
        
        // Update saat modal lapor pajak ditutup
        $('#laporPajakModal').on('hidden.bs.modal', () => {
            this.updateTableAndData();
        });
    }

    // akhir mencoba ajax bku table

    /**
     * Load nomor nota terakhir
     */
    loadLastNotaNumber() {
        $.ajax({
            url: '/bku/last-nota-number/' + this.tahun + '/' + this.bulan,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    const lastNota = response.last_nota_number;
                    const suggestedNext = response.suggested_next_number;
                    
                    let infoText = '';
                    if (lastNota) {
                        infoText = `Nomor nota terakhir: <strong>${lastNota}</strong>`;
                    } else {
                        infoText = 'Belum ada nomor nota untuk bulan ini';
                    }
                    
                    $('#lastNotaInfo').html(`<i class="bi bi-info-circle"></i> ${infoText}`);
                    
                } else {
                    $('#lastNotaInfo').html('<i class="bi bi-exclamation-triangle"></i> Gagal memuat informasi');
                }
            },
            error: (xhr) => {
                console.error('Error loading last nota number:', xhr);
                $('#lastNotaInfo').html('<i class="bi bi-exclamation-triangle"></i> Error memuat informasi');
            }
        });
    }

    /**
     * Validasi format nomor nota
     */
    validateNotaFormat(notaNumber) {
        if (!notaNumber.trim()) {
            return false;
        }
        
        const hasNumber = /\d/.test(notaNumber);
        return hasNumber;
    }

    /**
     * Handle semua operasi BKU
     */
    handleBkuOperations() {
        // Event delegation untuk semua operasi BKU
        $(document)
            .on('click', '#hapusSemuaBulan', this.handleDeleteAllBulan)
            .on('click', 'a.btn-hapus-individual', this.handleDeleteIndividual)
            .on('click', 'a.btn-hapus-penarikan', this.handleDeletePenarikan)
            .on('click', 'a.btn-hapus-penerimaan', this.handleDeletePenerimaan)
            .on('click', 'a.btn-hapus-saldo-awal', this.handleDeleteSaldoAwal)
            .on('click', 'a.btn-hapus-setor', this.handleDeleteSetor)
            .on('submit', '#formTutupBku', this.handleTutupBku)
            .on('click', '#btnBukaBku', this.handleBukaBku)
            .on('click', '#btnEditBunga', this.handleEditBunga)
            .on('submit', '#formEditBunga', this.handleEditBungaSubmit);
    }

    /**
     * Toggle status BKU (open/closed) dengan kontrol tombol hapus - DIPERBAIKI
     */
    toggleBkuStatus(isClosed, hasTransactions) {
        console.log('toggleBkuStatus called:', { isClosed, hasTransactions });
        
        // 1. Kontrol tombol utama
        $('#btnTambahTransaksi, #btnTarikTunai, #btnSetorTunai, #btnTutupBku')
            .prop('disabled', isClosed);
        
        // 2. Kontrol khusus tombol "Hapus BKU"
        const hapusSemuaButton = $('#hapusSemuaBulan');
        
        if (hapusSemuaButton.length > 0) {
            console.log('Found hapusSemuaButton, current state:', {
                isClosed: isClosed,
                hasTransactions: hasTransactions,
                buttonData: hapusSemuaButton.data(),
                isVisible: hapusSemuaButton.is(':visible')
            });
            
            if (isClosed) {
                // BKU TERTUTUP: Sembunyikan tombol hapus semua
                console.log('BKU closed - hiding hapus semua button');
                hapusSemuaButton.hide();
            } else {
                // BKU TERBUKA: Tampilkan tombol jika ada transaksi
                if (hasTransactions) {
                    console.log('BKU open with transactions - showing hapus semua button');
                    hapusSemuaButton.show();
                } else {
                    console.log('BKU open but no transactions - hiding hapus semua button');
                    hapusSemuaButton.hide();
                }
            }
        } else {
            console.log('hapusSemuaButton not found');
        }
        
        // 3. Kontrol tombol hapus individual
        if (isClosed) {
            $('.btn-hapus-individual, .btn-hapus-penarikan, .btn-hapus-penerimaan, .btn-hapus-setor')
                .addClass('disabled')
                .attr('disabled', true);
        } else {
            $('.btn-hapus-individual, .btn-hapus-penarikan, .btn-hapus-penerimaan, .btn-hapus-setor')
                .removeClass('disabled')
                .attr('disabled', false);
        }
    }

    /**
     * Update tombol hapus semua berdasarkan data terbaru - DIPERBAIKI
     */
    updateHapusSemuaButton() {
        // Dapatkan status BKU dari berbagai sumber
        const isClosed = this.getBkuStatus();
        const bkuRows = $('#bkuTableBody .bku-row');
        const hapusSemuaButton = $('#hapusSemuaBulan');
        
        console.log('updateHapusSemuaButton called:', {
            isClosed,
            bkuRowCount: bkuRows.length,
            buttonExists: hapusSemuaButton.length > 0
        });
        
        if (hapusSemuaButton.length > 0) {
            if (isClosed) {
                // BKU tertutup: sembunyikan tombol
                console.log('BKU is closed - hiding button');
                hapusSemuaButton.hide();
            } else if (bkuRows.length > 0) {
                // BKU terbuka dan ada data: tampilkan dan update jumlah
                console.log('BKU open with data - showing button, count:', bkuRows.length);
                hapusSemuaButton.show()
                    .data('jumlah-data', bkuRows.length)
                    .attr('data-jumlah-data', bkuRows.length);
            } else {
                // BKU terbuka tapi tidak ada data: sembunyikan
                console.log('BKU open but no data - hiding button');
                hapusSemuaButton.hide();
            }
        }
    }

    /**
     * Dapatkan status BKU dari berbagai sumber
     */
    getBkuStatus() {
        // 1. Coba dari meta tag
        const metaIsClosed = document.querySelector('meta[name="is-closed"]')?.content;
        if (metaIsClosed) {
            return metaIsClosed === 'true';
        }
        
        // 2. Coba dari data attribute container
        const container = document.querySelector('[data-is-closed]');
        if (container && container.dataset.isClosed) {
            return container.dataset.isClosed === 'true';
        }
        
        // 3. Coba dari badge status di UI
        const closedBadge = $('.badge.bg-danger:contains("Terkunci")');
        const openBadge = $('.badge.bg-success:contains("Terbuka")');
        
        if (closedBadge.length > 0) return true;
        if (openBadge.length > 0) return false;
        
        // 4. Default: anggap terbuka
        console.log('BKU status not found, defaulting to open');
        return false;
    }

    /**
    * Handle hapus semua data BKU (belanja saja) - DIPERBAIKI
    */
    handleDeleteAllBulan(e) {
        e.preventDefault();
        console.log('Hapus semua data BKU (belanja) button clicked');

        const button = $(e.currentTarget);
        const bulan = button.data('bulan');
        const tahun = button.data('tahun');
        const jumlahData = button.data('jumlah-data') || 0;
        const isClosed = button.data('is-closed') === 'true';

        console.log('Data:', { bulan, tahun, jumlahData, isClosed });

        if (isClosed) {
            Swal.fire({
                title: 'BKU Terkunci!',
                text: 'Tidak dapat menghapus data karena BKU sudah terkunci.',
                icon: 'warning',
                confirmButtonColor: '#0d6efd',
            });
            return;
        }

        // Hanya tampilkan data BKU (belanja) untuk dihapus
        const bkuRows = $('#bkuTableBody .bku-row');
        const bkuCount = bkuRows.length;

        if (bkuCount === 0) {
            Swal.fire({
                title: 'Tidak Ada Data!',
                text: 'Tidak ada data belanja (BKU) yang dapat dihapus.',
                icon: 'info',
                confirmButtonColor: '#0d6efd',
            });
            return;
        }

        // Hitung total belanja yang akan dihapus untuk update saldo
        let totalBelanja = 0;
        bkuRows.each((index, row) => {
            const belanjaText = $(row).find('td:nth-child(7)').text(); // Kolom "Dibelanjakan"
            const belanjaAmount = this.parseRupiah(belanjaText);
            totalBelanja += belanjaAmount;
        });

        // Tentukan jenis transaksi (kebanyakan non-tunai, tapi perlu cek per row)
        let tunaiCount = 0;
        let nonTunaiCount = 0;
        
        bkuRows.each((index, row) => {
            const jenisText = $(row).find('td:nth-child(5)').text().toLowerCase(); // Kolom "Jenis Transaksi"
            if (jenisText.includes('tunai')) {
                tunaiCount++;
            } else {
                nonTunaiCount++;
            }
        });

        Swal.fire({
            title: 'Hapus Semua Data Belanja?',
            html: `<div class="text-start">
                <p>Anda akan menghapus <strong>SEMUA</strong> data belanja (BKU) untuk:</p>
                <ul>
                    <li>Bulan: <strong>${bulan}</strong></li>
                    <li>Tahun: <strong>${tahun}</strong></li>
                    <li>Jumlah data: <strong>${bkuCount} transaksi belanja</strong></li>
                    <li>Total belanja: <strong>Rp ${this.formatRupiah(totalBelanja)}</strong></li>
                    <li>Transaksi tunai: <strong>${tunaiCount}</strong></li>
                    <li>Transaksi non-tunai: <strong>${nonTunaiCount}</strong></li>
                </ul>
                <p class="text-info mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Catatan:</strong> Hanya data belanja (BKU) yang akan dihapus.<br>
                    Data penarikan dan setor tunai tidak akan terhapus.
                </p>
                <p class="text-danger mt-3"><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!</p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua Belanja!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary me-2'
            },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve, reject) => {
                    console.log('Sending AJAX request to delete BKU data...');
                    
                    // Gunakan route yang spesifik untuk menghapus BKU saja
                    const url = `/bku/${tahun}/${bulan}/all`;
                    
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            console.log('AJAX success:', response);
                            // Tambahkan data total belanja ke response
                            response.total_belanja = totalBelanja;
                            response.tunai_count = tunaiCount;
                            response.non_tunai_count = nonTunaiCount;
                            resolve(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', xhr, status, error);
                            let errorMessage = 'Terjadi kesalahan server';
                    
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Data tidak ditemukan';
                            } else if (xhr.status === 422) {
                                errorMessage = 'Terjadi kesalahan validasi';
                            } else if (xhr.status === 500) {
                                errorMessage = 'Error server internal';
                            }

                            reject(new Error(errorMessage));
                        }
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Delete confirmed, result:', result);
                
                // Hapus baris BKU dari tabel dengan animasi
                $('#bkuTableBody .bku-row').each((index, row) => {
                    $(row).fadeOut(300, function() {
                        $(this).remove();
                    });
                });
                
                // Update UI setelah semua BKU dihapus
                this.handleAfterAllBkuDeleted(result.value);
                
                Swal.fire({
                    title: 'Berhasil!',
                    html: `<div class="text-start">
                        <p>${result.value.message}</p>
                        <div class="mt-2 p-2 bg-success bg-opacity-10 rounded">
                            <p class="text-success mb-1"><strong>Ringkasan Penghapusan:</strong></p>
                            <small class="text-muted">Jumlah data yang dihapus: <strong>${result.value.deleted_count}</strong></small><br>
                            <small class="text-muted">Total belanja yang dihapus: <strong>Rp ${this.formatRupiah(result.value.total_belanja || 0)}</strong></small><br>
                            <small class="text-muted">Transaksi tunai: <strong>${result.value.tunai_count || 0}</strong></small><br>
                            <small class="text-muted">Transaksi non-tunai: <strong>${result.value.non_tunai_count || 0}</strong></small>
                        </div>
                    </div>`,
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    },
                    buttonsStyling: false
                }).then(() => {
                    // Tidak perlu reload halaman
                    console.log('All BKU data deleted successfully');
                });
            } else {
                console.log('Delete cancelled');
            }
        }).catch((error) => {
            console.error('SweetAlert error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
        });
    }

    /**
    * Handle setelah semua data BKU dihapus
    */
    async handleAfterAllBkuDeleted(response) {
        console.log('Handling after all BKU deleted:', response);
        
        // 1. Update saldo (kembalikan saldo karena belanja dihapus)
        this.updateSaldoAfterAllBkuDeleted(response);
        
        // 2. Update card anggaran
        await this.updateAnggaranCardFromSummary();
        
        // 3. Update tombol hapus semua
        this.updateHapusSemuaButton();
        
        // 4. Update summary lainnya
        await this.updateSummaryAfterDelete();
        
        // 5. Cek apakah tabel kosong (hanya tersisa penarikan/setor tunai)
        this.checkTableEmptyAfterBkuDelete();
    }

    /**
    * Update saldo setelah semua BKU dihapus
    */
    updateSaldoAfterAllBkuDeleted(response) {
        try {
            const totalBelanja = response.total_belanja || 0;
            const tunaiCount = response.tunai_count || 0;
            const nonTunaiCount = response.non_tunai_count || 0;
            
            console.log('Updating saldo after all BKU deleted:', {
                totalBelanja,
                tunaiCount,
                nonTunaiCount
            });
            
            // Asumsi: untuk penghapusan massal, kita tidak tahu perbandingan tepatnya
            // Maka update kedua saldo secara proporsional
            
            if (totalBelanja > 0) {
                // Jika ada transaksi tunai, saldo tunai bertambah
                if (tunaiCount > 0) {
                    const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                    // Asumsi rata-rata per transaksi
                    const tunaiPerTransaction = totalBelanja / (tunaiCount + nonTunaiCount);
                    const tunaiIncrease = tunaiPerTransaction * tunaiCount;
                    const newTunai = currentTunai + tunaiIncrease;
                    $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(newTunai));
                }
                
                // Jika ada transaksi non-tunai, saldo non tunai bertambah
                if (nonTunaiCount > 0) {
                    const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                    // Asumsi rata-rata per transaksi
                    const nonTunaiPerTransaction = totalBelanja / (tunaiCount + nonTunaiCount);
                    const nonTunaiIncrease = nonTunaiPerTransaction * nonTunaiCount;
                    const newNonTunai = currentNonTunai + nonTunaiIncrease;
                    $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(newNonTunai));
                }
                
                // Update total dana tersedia
                const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newTotal = currentTunai + currentNonTunai;
                $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(newTotal));
            }
            
            console.log('Saldo updated after all BKU deleted');
            
        } catch (error) {
            console.error('Error updating saldo after all BKU deleted:', error);
        }
    }

    /**
    * Cek apakah tabel kosong setelah hapus BKU
    */
    checkTableEmptyAfterBkuDelete() {
        const remainingRows = $('#bkuTableBody tr').not('.bku-row');
        
        if (remainingRows.length === 0) {
            // Hanya tampilkan jika benar-benar tidak ada data sama sekali
            $('#bkuTableBody').html(`
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        Tidak ada data transaksi
                    </td>
                </tr>
            `);
        } else {
            // Masih ada data penarikan/setor tunai
            console.log('Remaining non-BKU rows:', remainingRows.length);
        }
    }

    /**
    * Handle hapus data generik (untuk penarikan, setor, penerimaan)
    */
    handleDeleteGeneric(e, type) {
        e.preventDefault();
        
        const button = $(e.currentTarget);
        const url = button.attr('href');
        const id = button.data('id');
        const row = button.closest('tr');
        
        const typeMap = {
            'penarikan': 'penarikan tunai',
            'penerimaan': 'penerimaan dana',
            'saldo-awal': 'saldo awal',
            'setor': 'setor tunai'
        };

        const typeText = typeMap[type] || 'data';

        Swal.fire({
            title: `Hapus ${typeText}?`,
            text: `Apakah Anda yakin ingin menghapus ${typeText} ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary me-2'
            },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Terjadi kesalahan server';
                            
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Data tidak ditemukan';
                            } else if (xhr.status === 422) {
                                errorMessage = 'Terjadi kesalahan validasi';
                            } else if (xhr.status === 500) {
                                errorMessage = 'Error server internal';
                            }
                            
                            reject(new Error(errorMessage));
                        }
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Simpan data untuk update saldo
                const rowData = this.getRowDataForUpdate(row, type);
                
                // Hapus baris dari tabel
                row.fadeOut(300, () => {
                    row.remove();
                    
                    // Update UI berdasarkan jenis data yang dihapus
                    this.handleAfterGenericDelete(type, rowData);
                    
                    // Cek apakah tabel kosong
                    if ($('#bkuTableBody tr').length === 0) {
                        $('#bkuTableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    Tidak ada data transaksi
                                </td>
                            </tr>
                        `);
                    }
                });
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.value.message || `${typeText} berhasil dihapus`,
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }).catch((error) => {
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    }

    /**
    * Helper untuk mendapatkan data row berdasarkan jenis
    */
    getRowDataForUpdate(row, type) {
        switch(type) {
            case 'penarikan':
                // Untuk penarikan: jumlah penarikan
                const penarikanText = row.find('td:nth-child(3)').text();
                const jumlahPenarikan = this.extractAmountFromUraian(penarikanText);
                return { jumlah: jumlahPenarikan, type: 'penarikan' };
                
            case 'setor':
                // Untuk setor: jumlah setor
                const setorText = row.find('td:nth-child(3)').text();
                const jumlahSetor = this.extractAmountFromUraian(setorText);
                return { jumlah: jumlahSetor, type: 'setor' };
                
            case 'penerimaan':
            case 'saldo-awal':
                // Untuk penerimaan/saldo awal
                const penerimaanText = row.find('td:nth-child(3)').text();
                const jumlahPenerimaan = this.extractAmountFromUraian(penerimaanText);
                return { jumlah: jumlahPenerimaan, type: type };
                
            default:
                return { jumlah: 0, type: type };
        }
    }

    /**
    * Extract amount dari teks uraian
    */
    extractAmountFromUraian(uraianText) {
        // Mencari pola "Rp X.XXX.XXX" atau "Rp X,XXX,XXX"
        const match = uraianText.match(/Rp\s*([\d.,]+)/);
        if (match && match[1]) {
            return this.parseRupiah(match[1]);
        }
        return 0;
    }

    /**
    * Handle setelah data generik dihapus
    */
    async handleAfterGenericDelete(type, rowData) {
        console.log('Handling after generic delete:', { type, rowData });
        
        // 1. Update saldo berdasarkan jenis data
        this.updateSaldoAfterGenericDelete(type, rowData.jumlah);
        
        // 2. Update card anggaran (jika perlu)
        if (type === 'penarikan' || type === 'setor') {
            // Untuk penarikan/setor, anggaran tidak berubah
            // Tapi kita tetap update untuk konsistensi
            await this.updateAnggaranCardFromSummary();
        }
        
        // 3. Update summary data lainnya
        await this.updateSummaryAfterDelete();
    }

    /**
    * Update saldo setelah data generik dihapus
    */
    updateSaldoAfterGenericDelete(type, jumlah) {
        try {
            console.log('Updating saldo after generic delete:', { type, jumlah });
            
            if (type === 'penarikan') {
                // Penarikan dihapus: saldo non tunai bertambah, saldo tunai berkurang
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newNonTunai = currentNonTunai + jumlah;
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(newNonTunai));
                
                const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newTunai = currentTunai - jumlah;
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newTunai)));
                
            } else if (type === 'setor') {
                // Setor dihapus: saldo tunai bertambah, saldo non tunai berkurang
                const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newTunai = currentTunai + jumlah;
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(newTunai));
                
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newNonTunai = currentNonTunai - jumlah;
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newNonTunai)));
                
            } else if (type === 'penerimaan' || type === 'saldo-awal') {
                // Penerimaan/saldo awal dihapus: total dana tersedia berkurang
                // Untuk penerimaan dana, kita perlu tahu apakah tunai atau non-tunai
                // Asumsi: penerimaan dana ke non-tunai
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newNonTunai = currentNonTunai - jumlah;
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newNonTunai)));
            }
            
            // Update total dana tersedia
            const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
            const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
            const newTotal = currentTunai + currentNonTunai;
            $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(newTotal));
            
            console.log('Saldo updated after generic delete');
            
        } catch (error) {
            console.error('Error updating saldo after generic delete:', error);
        }
    }

    /**
    * Parse rupiah ke angka
    */
    parseRupiah(rupiahString) {
        if (!rupiahString) return 0;
        return parseInt(rupiahString.toString().replace(/[^\d]/g, '')) || 0;
    }

    /**
     * Update summary setelah penghapusan
     */
    async updateSummaryAfterDelete() {
        try {
            // Ambil data summary terbaru
            const response = await $.ajax({
                url: `/bku/summary-data/${this.tahun}/${this.bulan}`,
                method: 'GET'
            });
            
            if (response.success && response.data) {
                this.updateSummaryDisplay(response.data);
            }
        } catch (error) {
            console.error('Error updating summary:', error);
        }
    }

    /**
     * Update display summary data
     */
    updateSummaryDisplay(data) {
        console.log('Updating summary display:', data);
        
        // Update total dibelanjakan bulan ini
        if (data.total_dibelanjakan_bulan_ini !== undefined) {
            const dibelanjakanInput = $('input[value*="Dibelanjakan"]').closest('.fw-semibold').find('input');
            if (dibelanjakanInput.length) {
                dibelanjakanInput.val('Rp ' + this.formatRupiah(data.total_dibelanjakan_bulan_ini));
            }
        }
        
        // Update total dibelanjakan sampai bulan ini
        if (data.total_dibelanjakan_sampai_bulan_ini !== undefined) {
            // Anda perlu menyesuaikan selector ini berdasarkan HTML Anda
            const sampaiBulanInput = $('input[placeholder*="sampai"]').closest('.fw-semibold').find('input');
            if (sampaiBulanInput.length) {
                sampaiBulanInput.val('Rp ' + this.formatRupiah(data.total_dibelanjakan_sampai_bulan_ini));
            }
        }
        
        // Update saldo non tunai dan tunai
        if (data.saldo) {
            $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(data.saldo.non_tunai || 0));
            $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(data.saldo.tunai || 0));
            
            // Update total dana tersedia
            const totalDana = (data.saldo.non_tunai || 0) + (data.saldo.tunai || 0);
            $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDana));
        }
        
        // Update anggaran yang belum dibelanjakan
        if (data.anggaran_belum_dibelanjakan !== undefined) {
            const belumDibelanjakanInput = $('input[placeholder*="dianggarkan ulang"]').closest('.fw-semibold').find('input');
            if (belumDibelanjakanInput.length) {
                belumDibelanjakanInput.val('Rp ' + this.formatRupiah(data.anggaran_belum_dibelanjakan));
            }
        }
    }

    /**
     * Execute form delete dengan AJAX (untuk fallback)
     */
    executeFormDelete(url) {
        // Method ini sekarang hanya sebagai fallback jika AJAX gagal
        console.warn('Using fallback form delete for:', url);
        
        // Buat form tersembunyi
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        // Tambahkan CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = $('meta[name="csrf-token"]').attr('content');
        form.appendChild(csrfToken);

        // Tambahkan method spoofing
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        // Submit form
        document.body.appendChild(form);
        form.submit();
    }

    /**
    * Handle hapus data BKU individual
    */
    handleDeleteIndividual(e) {
        e.preventDefault();
        
        const button = $(e.currentTarget);
        const url = button.attr('href');
        const id = button.data('id');
        const row = button.closest('tr');
        const totalBelanja = this.getRowTotalBelanja(row);

        Swal.fire({
            title: 'Hapus transaksi?',
            html: `<div class="text-start">
                <p>Apakah Anda yakin ingin menghapus transaksi ini?</p>
                <div class="mt-2 p-2 bg-light rounded">
                    <small class="text-muted"><strong>ID Transaksi:</strong> ${row.find('td:first').text()}</small><br>
                    <small class="text-muted"><strong>Tanggal:</strong> ${row.find('td:nth-child(2)').text()}</small><br>
                    <small class="text-muted"><strong>Jumlah Belanja:</strong> ${row.find('td:nth-child(7)').text()}</small>
                </div>
                <p class="text-danger mt-3"><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!</p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary me-2'
            },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Terjadi kesalahan server';
                            
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Data tidak ditemukan';
                            } else if (xhr.status === 422) {
                                errorMessage = 'Terjadi kesalahan validasi';
                            } else if (xhr.status === 500) {
                                errorMessage = 'Error server internal';
                            }
                            
                            reject(new Error(errorMessage));
                        }
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Simpan data sebelum dihapus untuk update saldo
                const belanjaAmount = this.parseRupiah(row.find('td:nth-child(7)').text());
                const jenisTransaksi = this.getJenisTransaksiFromRow(row);
                
                // Hapus baris dari tabel dengan animasi
                row.fadeOut(300, () => {
                    row.remove();
                    
                    // Update UI setelah penghapusan
                    this.handleAfterBkuDelete(belanjaAmount, jenisTransaksi);
                    
                    // Cek apakah tabel kosong
                    if ($('#bkuTableBody tr').length === 0) {
                        $('#bkuTableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    Tidak ada data transaksi
                                </td>
                            </tr>
                        `);
                    }
                });
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.value.message || 'Transaksi berhasil dihapus',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }).catch((error) => {
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    }

    /**
    * Helper untuk mendapatkan total belanja dari row
    */
    getRowTotalBelanja(row) {
        const belanjaText = row.find('td:nth-child(7)').text();
        return this.parseRupiah(belanjaText);
    }

    /**
    * Helper untuk mendapatkan jenis transaksi dari row
    */
    getJenisTransaksiFromRow(row) {
        const jenisText = row.find('td:nth-child(5)').text().toLowerCase();
        return jenisText.includes('tunai') ? 'tunai' : 'non-tunai';
    }

    /**
    * Handle setelah BKU dihapus
    */
    async handleAfterBkuDelete(belanjaAmount, jenisTransaksi) {
        console.log('Handling after BKU delete:', { belanjaAmount, jenisTransaksi });
        
        // 1. Update saldo
        this.updateSaldoAfterBkuDelete(belanjaAmount, jenisTransaksi);
        
        // 2. Update card anggaran
        await this.updateAnggaranCardFromSummary();
        
        // 3. Update summary data lainnya
        await this.updateSummaryAfterDelete();
        
        // 4. Update semua UI untuk konsistensi
        await this.updateAllUIAfterChange();
    }

    /**
    * Update saldo setelah BKU dihapus
    */
    updateSaldoAfterBkuDelete(belanjaAmount, jenisTransaksi) {
        try {
            console.log('Updating saldo after BKU delete:', { belanjaAmount, jenisTransaksi });
            
            if (jenisTransaksi === 'tunai') {
                // Untuk transaksi tunai: saldo tunai bertambah (karena belanja dihapus)
                const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newTunai = currentTunai + belanjaAmount;
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(newTunai));
            } else {
                // Untuk transaksi non-tunai: saldo non tunai bertambah
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newNonTunai = currentNonTunai + belanjaAmount;
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(newNonTunai));
            }
            
            // Update total dana tersedia (bertambah karena belanja dihapus)
            const currentTotal = parseFloat($('h4.fw-semibold.text-dark').text().replace(/[^\d]/g, '')) || 0;
            const newTotal = currentTotal + belanjaAmount;
            $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(newTotal));
            
            console.log('Saldo updated after BKU delete');
            
        } catch (error) {
            console.error('Error updating saldo after BKU delete:', error);
        }
    }

    /**
     * Handle hapus data penarikan
     */
    handleDeletePenarikan(e) {
        this.handleDeleteGeneric(e, 'penarikan');
    }

    /**
     * Handle hapus data penerimaan
     */
    handleDeletePenerimaan(e) {
        this.handleDeleteGeneric(e, 'penerimaan');
    }

    /**
     * Handle hapus data saldo awal
     */
    handleDeleteSaldoAwal(e) {
        this.handleDeleteGeneric(e, 'saldo-awal');
    }

    /**
     * Handle hapus data setor
     */
    handleDeleteSetor(e) {
        this.handleDeleteGeneric(e, 'setor');
    }

    /**
     * Handle tutup BKU dengan loading
     */
    handleTutupBku(e) {
        e.preventDefault();
        
        // Get button tutup BKU dan simpan teks asli
        const tutupButton = $('#btnTutupBku');
        const originalHtml = tutupButton.html();
        
        // Tampilkan loading di button
        tutupButton.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
        
        const formData = {
            bunga_bank: $('#bunga_bank').val().replace(/[^\d]/g, ''),
            pajak_bunga_bank: $('#pajak_bunga_bank').val().replace(/[^\d]/g, ''),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Get tahun dan bulan
        let tahun = $('meta[name="tahun"]').attr('content');
        let bulan = $('meta[name="bulan"]').attr('content');
        
        // Coba dari form jika tidak ada di meta
        if (!tahun || !bulan) {
            const form = $('#formTutupBku');
            tahun = form.data('tahun');
            bulan = form.data('bulan');
        }

        if (!tahun || !bulan) {
            Swal.fire('Error!', 'Data tahun/bulan tidak ditemukan', 'error');
            
            // Reset button
            tutupButton.prop('disabled', false).html(originalHtml);
            return;
        }

        Swal.fire({
            title: 'Tutup BKU?',
            text: `Apakah Anda yakin ingin menutup BKU untuk bulan ${bulan} ${tahun}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tutup BKU',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: `/bku/${tahun}/${bulan}/tutup`,
                        method: 'POST',
                        data: formData,
                        success: (response) => {
                            resolve(response);
                        },
                        error: (xhr) => {
                            let errorMessage = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            reject(new Error(errorMessage));
                        }
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            // Reset button state
            tutupButton.prop('disabled', false).html(originalHtml);
            
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'BKU berhasil ditutup',
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    location.reload();
                });
            }
        }).catch((error) => {
            // Reset button state jika error
            tutupButton.prop('disabled', false).html(originalHtml);
            
            Swal.fire('Error!', error.message, 'error');
        });
    }

    /**
     * Handle buka BKU dengan loading (versi menggunakan helper)
     */
    handleBukaBku(e) {
        e.preventDefault();
        
        const button = $(e.currentTarget);
        const originalHtml = button.html();
        
        // Get tahun dan bulan
        let tahun, bulan;
        
        // Coba dari button
        if (button.data('tahun') && button.data('bulan')) {
            tahun = button.data('tahun');
            bulan = button.data('bulan');
        } 
        // Fallback ke meta tags
        else {
            tahun = $('meta[name="tahun"]').attr('content');
            bulan = $('meta[name="bulan"]').attr('content');
        }
        
        if (!tahun || !bulan) {
            Swal.fire('Error!', 'Data tahun/bulan tidak ditemukan', 'error');
            return;
        }

        // Tampilkan SweetAlert konfirmasi
        Swal.fire({
            title: 'Buka BKU?',
            html: `<div class="text-start">
                <p>Anda akan membuka BKU untuk:</p>
                <ul>
                    <li><strong>Bulan:</strong> ${bulan}</li>
                    <li><strong>Tahun:</strong> ${tahun}</li>
                </ul>
                <p class="text-warning mt-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Setelah dibuka, data dapat diubah kembali.
                </p>
            </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buka BKU',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-success me-2',
                cancelButton: 'btn btn-secondary me-2'
            },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // Tampilkan loading di button
                this.setButtonLoading(button, true, 'Membuka...');
                
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: `/bku/${tahun}/${bulan}/buka`,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: (response) => {
                            resolve(response);
                        },
                        error: (xhr) => {
                            let errorMessage = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            reject(new Error(errorMessage));
                        }
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            // Reset button state
            this.setButtonLoading(button, false);
            
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Berhasil!',
                    html: `<div class="text-start">
                        <p>BKU berhasil dibuka untuk:</p>
                        <ul>
                            <li><strong>Bulan:</strong> ${bulan}</li>
                            <li><strong>Tahun:</strong> ${tahun}</li>
                        </ul>
                        <p class="text-success mt-2">
                            <i class="bi bi-check-circle me-1"></i>
                            Data sekarang dapat diubah kembali.
                        </p>
                    </div>`,
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        }).catch((error) => {
            // Reset button state jika error
            this.setButtonLoading(button, false);
            
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    }

    /**
     * Format button loading state
     */
    setButtonLoading(button, isLoading, loadingText = 'Memproses...') {
        if (isLoading) {
            const originalHtml = button.html();
            button.data('original-html', originalHtml)
                .prop('disabled', true)
                .html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`);
        } else {
            const originalHtml = button.data('original-html');
            if (originalHtml) {
                button.prop('disabled', false).html(originalHtml);
            }
        }
    }

    /**
     * Reset semua button loading state
     */
    resetAllButtonLoading() {
        $('.btn-loading').each((index, element) => {
            const button = $(element);
            const originalHtml = button.data('original-html');
            if (originalHtml) {
                button.prop('disabled', false)
                    .html(originalHtml)
                    .removeClass('btn-loading');
            }
        });
    }

    /**
     * Attach event listeners untuk page unload
     */
    attachPageUnloadEvents() {
        // Reset button state saat page akan di-unload
        $(window).on('beforeunload', () => {
            this.resetAllButtonLoading();
        });
        
        // Reset button state saat modal ditutup
        $(document).on('hidden.bs.modal', () => {
            this.resetAllButtonLoading();
        });
    }

    /**
     * Handle edit bunga bank button click
     */
    handleEditBunga(e) {
        e.preventDefault();
        
        console.log('=== EDIT BUNGA BUTTON CLICKED ===');
        
        // Ambil tahun dan bulan dari tombol
        const button = $(e.currentTarget);
        const tahun = button.data('tahun');
        const bulan = button.data('bulan');
        
        console.log('Button data:', { tahun, bulan });
        
        if (!tahun || !bulan) {
            Swal.fire({
                title: 'Error!',
                text: 'Data tahun/bulan tidak ditemukan di tombol',
                icon: 'error'
            });
            return;
        }
        
        // Tampilkan loading di button edit bunga
        const originalText = button.html();
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Membuka...');
        
        // Simpan data tahun dan bulan di modal
        $('#editBungaModal').data('tahun', tahun).data('bulan', bulan);
        
        // Juga simpan di form sebagai hidden input (optional)
        if ($('#editBungaModal input[name="tahun"]').length === 0) {
            $('#formEditBunga').append('<input type="hidden" name="tahun" value="' + tahun + '">');
            $('#formEditBunga').append('<input type="hidden" name="bulan" value="' + bulan + '">');
        }
        
        // Set nilai form dari data yang ada
        if (typeof window.bungaBankValue !== 'undefined') {
            $('#edit_bunga_bank').val(window.bungaBankValue);
        }
        
        if (typeof window.pajakBungaBankValue !== 'undefined') {
            $('#edit_pajak_bunga_bank').val(window.pajakBungaBankValue);
        }
        
        // Reset validation
        $('#edit_bunga_bank, #edit_pajak_bunga_bank')
            .removeClass('is-invalid')
            .removeClass('is-valid');
        $('#bunga_bank_error, #pajak_bunga_bank_error').text('');
        
        // Delay sedikit untuk efek loading
        setTimeout(() => {
            // Reset button
            button.prop('disabled', false).html(originalText);
            
            // Tampilkan modal
            $('#editBungaModal').modal('show');
            
            console.log('Modal opened with data:', { tahun, bulan });
        }, 300);
    }

    /**
     * Reset form dan button state ketika modal ditutup
     */
    handleEditBungaModalHidden() {
        // Reset button submit di modal
        const submitButton = $('#formEditBunga button[type="submit"]');
        submitButton.prop('disabled', false)
                    .html('Simpan');
        
        // Reset form validation errors
        $('.invalid-feedback').remove();
        $('.is-invalid').removeClass('is-invalid');
    }

    /**
     * Konversi format mata uang Indonesia ke angka
     */
    convertCurrencyToNumber(currencyString) {
        if (!currencyString) return 0;
        
        // Hapus semua karakter kecuali angka, titik, dan koma
        let cleaned = currencyString.toString()
            .replace(/[^\d,.-]/g, '')  // Hapus semua kecuali angka, koma, titik, minus
            .replace(/\./g, '')        // Hapus titik (ribuan separator)
            .replace(',', '.');        // Ganti koma dengan titik (desimal separator)
        
        // Parse ke float
        const num = parseFloat(cleaned);
        
        // Return 0 jika NaN, else return angka
        return isNaN(num) ? 0 : num;
    }

    /**
     * Handle submit edit bunga - VERSI SEDERHANA DIPERBAIKI
     */
    handleEditBungaSubmit(e) {
        e.preventDefault();
        
        console.log('=== EDIT BUNGA SUBMIT ===');
        
        // Disable button dan tampilkan loading
        const submitButton = $('#formEditBunga button[type="submit"]');
        const originalText = submitButton.html();
        submitButton.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        // Ambil nilai langsung dari input (sudah berupa angka)
        const bungaValue = parseFloat($('#edit_bunga_bank').val()) || 0;
        const pajakValue = parseFloat($('#edit_pajak_bunga_bank').val()) || 0;
        
        console.log('Input values:', { 
            bungaValue, 
            pajakValue,
            bungaInput: $('#edit_bunga_bank').val(),
            pajakInput: $('#edit_pajak_bunga_bank').val()
        });
        
        // Validasi sederhana
        let isValid = true;
        
        if (bungaValue < 0 || isNaN(bungaValue)) {
            $('#bunga_bank_error').text('Masukkan nilai bunga bank yang valid');
            $('#edit_bunga_bank').addClass('is-invalid');
            isValid = false;
        } else {
            $('#edit_bunga_bank').removeClass('is-invalid');
            $('#bunga_bank_error').text('');
        }
        
        if (pajakValue < 0 || isNaN(pajakValue)) {
            $('#pajak_bunga_bank_error').text('Masukkan nilai pajak bunga bank yang valid');
            $('#edit_pajak_bunga_bank').addClass('is-invalid');
            isValid = false;
        } else {
            $('#edit_pajak_bunga_bank').removeClass('is-invalid');
            $('#pajak_bunga_bank_error').text('');
        }
        
        if (!isValid) {
            submitButton.prop('disabled', false).html(originalText);
            return false;
        }
        
        // AMBIL TAHUN DAN BULAN DENGAN CARA YANG BENAR
        let tahun, bulan;
        
        console.log('Mencari data tahun/bulan dari berbagai sumber:');
        
        // 1. Coba dari tombol Edit Bunga (paling reliable)
        const editButton = $('#btnEditBunga');
        if (editButton.length > 0 && editButton.data('tahun') && editButton.data('bulan')) {
            tahun = editButton.data('tahun');
            bulan = editButton.data('bulan');
            console.log('Found from edit button:', { tahun, bulan });
        }
        // 2. Coba dari meta tags
        else if ($('meta[name="tahun"]').length > 0 && $('meta[name="bulan"]').length > 0) {
            tahun = $('meta[name="tahun"]').attr('content');
            bulan = $('meta[name="bulan"]').attr('content');
            console.log('Found from meta tags:', { tahun, bulan });
        }
        // 3. Coba dari URL (fallback)
        else {
            // Parse dari URL saat ini
            const currentPath = window.location.pathname;
            const pathParts = currentPath.split('/');
            console.log('Path parts:', pathParts);
            
            // URL pattern: /bku/{tahun}/{bulan}
            if (pathParts.length >= 4 && pathParts[1] === 'bku') {
                tahun = pathParts[2];
                bulan = decodeURIComponent(pathParts[3]); // Decode untuk bulan dengan spasi
                console.log('Found from URL:', { tahun, bulan });
            }
        }
        
        console.log('Final tahun/bulan:', { tahun, bulan });
        
        if (!tahun || !bulan) {
            Swal.fire({
                title: 'Error!',
                text: 'Data tahun/bulan tidak ditemukan. Tahun: ' + (tahun || 'kosong') + ', Bulan: ' + (bulan || 'kosong'),
                icon: 'error'
            });
            submitButton.prop('disabled', false).html(originalText);
            return false;
        }

        const formData = {
            bunga_bank: bungaValue,
            pajak_bunga_bank: pajakValue,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log('Sending data to server:', { tahun, bulan, formData });

        // Kirim request AJAX
        $.ajax({
            url: `/bku/${tahun}/${bulan}/update-bunga`,
            method: 'PUT',
            data: formData,
            success: (response) => {
                // Reset button
                submitButton.prop('disabled', false).html(originalText);
                
                console.log('Update successful:', response);
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Data bunga bank berhasil diperbarui',
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#editBungaModal').modal('hide');
                    location.reload();
                });
            },
            error: (xhr) => {
                // Reset button
                submitButton.prop('disabled', false).html(originalText);
                
                console.error('Error updating bunga:', xhr);
                
                let errorMessage = 'Terjadi kesalahan saat memperbarui data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            }
        });
        
        return false;
    }


    /**
    * Inisialisasi modal edit bunga - SEDERHANA
    */
    initEditBungaModalValues() {
        // Event saat modal edit bunga dibuka
        $(document).on('show.bs.modal', '#editBungaModal', () => {
            console.log('Edit bunga modal opening...');
            
            // Set nilai langsung dari variabel window
            if (typeof window.bungaBankValue !== 'undefined') {
                $('#edit_bunga_bank').val(window.bungaBankValue);
            }
            
            if (typeof window.pajakBungaBankValue !== 'undefined') {
                $('#edit_pajak_bunga_bank').val(window.pajakBungaBankValue);
            }
            
            // Reset validation
            $('#edit_bunga_bank, #edit_pajak_bunga_bank')
                .removeClass('is-invalid')
                .removeClass('is-valid');
            $('#bunga_bank_error, #pajak_bunga_bank_error').text('');
            
            // Enable input
            $('#edit_bunga_bank, #edit_pajak_bunga_bank').prop('disabled', false);
        });
        
        // Reset error saat input berubah
        $(document).on('input', '#edit_bunga_bank, #edit_pajak_bunga_bank', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        });
        
        // Reset saat modal ditutup
        $(document).on('hidden.bs.modal', '#editBungaModal', () => {
            console.log('Edit bunga modal closed');
            
            // Reset button submit di modal
            const submitButton = $('#formEditBunga button[type="submit"]');
            if (submitButton.length) {
                submitButton.prop('disabled', false)
                    .html('<i class="bi bi-check-circle me-1"></i> Simpan Perubahan');
            }
            
            // Enable semua input
            $('#edit_bunga_bank, #edit_pajak_bunga_bank').prop('disabled', false);
        });
    }

    /**
     * Attach BKU event listeners
     */
    attachBkuEventListeners() {
        console.log('Attaching BKU event listeners...');
        
        // Detail modal dengan event delegation untuk elemen yang baru dimuat
        $(document).on('click', '.btn-view-detail', (e) => {
            e.preventDefault();
            const bkuId = $(e.currentTarget).data('bku-id');
            this.showDetailModal(bkuId);
        });

        // Lapor pajak modal - gunakan event delegation untuk elemen yang baru dimuat
        $(document).on('click', '.btn-lapor-pajak', (e) => {
            e.preventDefault();
            const bkuId = $(e.currentTarget).data('id');
            const totalPajak = $(e.currentTarget).data('pajak');
            const existingNtpn = $(e.currentTarget).data('ntpn');
            
            $('#bku_id').val(bkuId);
            
            console.log('BKU ID:', bkuId, 'Pajak:', totalPajak, 'NTPN:', existingNtpn);
            
            if (totalPajak > 0) {
                const url = '/bku/' + bkuId + '/get-pajak';
                
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: (response) => {
                        if (response.success) {
                            $('#tanggal_lapor').val(response.data.tanggal_lapor);
                            $('#ntpn').val(response.data.ntpn);
                            $('#kode_masa_pajak').val(response.data.kode_masa_pajak);
                            
                            if (response.data.ntpn) {
                                $('#laporPajakModalLabel').html(`<i class="bi bi-check-circle-fill text-success me-2"></i>Edit Lapor Pajak`);
                                $('.modal-header').removeClass('bg-warning').addClass('bg-success');
                            } else {
                                $('#laporPajakModalLabel').html(`<i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Lapor Pajak`);
                                $('.modal-header').removeClass('bg-success').addClass('bg-warning');
                            }
                        }
                    },
                    error: (xhr) => {
                        console.error('Error loading pajak data:', xhr);
                        Swal.fire('Error!', 'Gagal memuat data pajak', 'error');
                    }
                });
                
                $('#laporPajakModal').modal('show');
            }
        });

        // Fix dropdown positioning
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        const dropdownList = dropdownElementList.map((dropdownToggleEl) => {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });

        document.querySelectorAll('.dropdown-menu').forEach((menu) => {
            menu.style.zIndex = '1060';
        });

        // Debug button handler
        $(document).on('click', '#btnDebug', () => {
            // Panggil fungsi debug jika ada
            if (typeof runDebugCalculation === 'function') {
                runDebugCalculation();
            }
        });

        console.log('BKU event listeners attached successfully');
    }

    /**
     * Attach lapor pajak events
     */
    attachLaporPajakEvents() {
        // Event listener untuk simpan lapor pajak
        $(document).on('click', '#btnSimpanLapor', this.handleSimpanLaporPajak);

        // Reset form ketika modal ditutup
        $('#laporPajakModal').on('hidden.bs.modal', () => {
            if ($('#formLaporPajak').length) {
                $('#formLaporPajak')[0].reset();
            }
            $('#tanggal_lapor_error, #ntpn_error, #kode_masa_pajak_error').text('');
        });

        // Event listener untuk success simpan transaksi
        $(document).on('bkuSaved', (e, data) => {
            if (data.saldo_update) {
                this.updateSaldoDisplay(data.saldo_update);
                
                $('input[data-max]').each((index, element) => {
                    const field = $(element);
                    if (field.attr('id') === 'jumlah_penarikan') {
                        field.attr('data-max', data.saldo_update.non_tunai);
                        field.next('small').text('Maksimal: Rp ' + this.formatRupiah(data.saldo_update.non_tunai));
                    } else if (field.attr('id') === 'jumlah_setor') {
                        field.attr('data-max', data.saldo_update.tunai);
                        field.next('small').text('Maksimal: Rp ' + this.formatRupiah(data.saldo_update.tunai));
                    }
                });
            }
        });
    }

    /**
     * Handle simpan lapor pajak
     */
    handleSimpanLaporPajak(e) {
        e.preventDefault();
        
        const bkuId = $('#bku_id').val();
        
        if (!bkuId) {
            Swal.fire('Error!', 'ID transaksi tidak valid', 'error');
            return;
        }
        
        const formData = {
            tanggal_lapor: $('#tanggal_lapor').val(),
            ntpn: $('#ntpn').val(),
            kode_masa_pajak: $('#kode_masa_pajak').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        let isValid = true;
        $('#tanggal_lapor_error, #ntpn_error, #kode_masa_pajak_error').text('');
        
        if (!formData.tanggal_lapor) {
            $('#tanggal_lapor_error').text('Tanggal lapor wajib diisi');
            isValid = false;
        }

        if (!formData.kode_masa_pajak) {
            $('#kode_masa_pajak_error').text('Kode masa pajak wajib diisi');
            isValid = false;
        }
        
        if (!formData.ntpn) {
            $('#ntpn_error').text('NTPN wajib diisi');
            isValid = false;
        } else if (formData.ntpn.length !== 16) {
            $('#ntpn_error').text('NTPN harus 16 digit');
            isValid = false;
        }
        
        if (!isValid) return;
        
        const url = '/bku/' + bkuId + '/lapor-pajak';
        const button = $('#btnSimpanLapor');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            beforeSend: () => {
                button.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
            },
            success: (response) => {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        html: `<div class="text-start">
                            <p>${response.message}</p>
                            <div class="mt-2 p-2 bg-light rounded">
                                <small class="text-muted">NTPN: <strong>${formData.ntpn}</strong></small><br>
                                <small class="text-muted">Tanggal Lapor: <strong>${formData.tanggal_lapor}</strong></small><br>
                                <small class="text-muted">Kode Masa Pajak: <strong>${formData.kode_masa_pajak}</strong></small>
                            </div>
                        </div>`,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        // Tutup modal tanpa reload halaman
                        $('#laporPajakModal').modal('hide');
                        
                        // Perbarui tampilan baris transaksi yang dilaporkan
                        this.updateRowAfterLaporPajak(bkuId, formData);
                        
                        // Perbarui data tabel (opsional, jika ingin memperbarui seluruh tabel)
                        // this.updateTableAfterLaporPajak();
                    });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: (xhr) => {
                let errorMessage = 'Terjadi kesalahan';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        $(`#${field}_error`).text(errors[field][0]);
                    });
                    errorMessage = 'Terjadi kesalahan validasi';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire('Error!', errorMessage, 'error');
            },
            complete: () => {
                button.prop('disabled', false).html('Simpan');
            }
        });
    }

    /**
     * Update baris BKU setelah lapor pajak
     */
    updateRowAfterLaporPajak(bkuId, pajakData) {
        // Cari baris dengan data-bku-id yang sesuai
        const row = $(`tr[data-bku-id="${bkuId}"]`);
        
        if (row.length) {
            // Ambil nilai pajak asli dari data attribute
            const pajakAmount = parseFloat(row.data('pajak-amount')) || 0;
            
            // Format nilai pajak
            const formattedPajak = 'Rp ' + this.formatRupiah(pajakAmount);
            
            // Kolom pajak (kolom ke-8)
            const pajakCell = row.find('td').eq(7);
            
            // Update tampilan pajak dengan status sudah dilaporkan
            pajakCell.html(`
                <span class="text-dark">${formattedPajak}</span>
                <small class="text-success d-block" title="NTPN: ${pajakData.ntpn}">
                    <i class="bi bi-check-circle-fill"></i> Sudah dilaporkan
                </small>
            `);
            
            // Update tombol dropdown
            const laporPajakLink = row.find('.btn-lapor-pajak');
            if (laporPajakLink.length) {
                laporPajakLink.html(`
                    <i class="bi bi-check-circle me-2"></i>Edit Lapor Pajak
                `);
                laporPajakLink.data('ntpn', pajakData.ntpn);
            }
            
            // Update icon dan warna di dropdown jika ada
            const iconElement = row.find('.btn-lapor-pajak i');
            if (iconElement.length) {
                iconElement.removeClass('bi-info-circle').addClass('bi-check-circle');
            }
            
            // Tambahkan atribut data untuk memudahkan pembaruan berikutnya
            row.data('pajak-reported', true);
            row.data('pajak-ntpn', pajakData.ntpn);
            
            console.log('Row updated for BKU ID:', bkuId, 'Pajak amount:', pajakAmount);
        } else {
            // Jika tidak ditemukan baris spesifik, perbarui seluruh tabel
            console.log('Row not found, updating entire table...');
            this.updateTableAfterLaporPajak();
        }
    }

    /**
     * Perbarui tabel setelah lapor pajak
     */
    async updateTableAfterLaporPajak() {
        try {
            console.log('Updating table after lapor pajak...');
            
            // Ambil data tabel terbaru
            const response = await $.ajax({
                url: `/bku/table-data/${this.tahun}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            if (response.success && response.html) {
                // Perbarui tabel dengan HTML baru
                $('#bkuTableBody').html(response.html);
                
                // Re-attach event listeners
                this.attachBkuEventListeners();
                
                console.log('Table updated successfully after lapor pajak');
            }
        } catch (error) {
            console.error('Error updating table after lapor pajak:', error);
        }
    }

    /**
     * Update display saldo
     */
    updateSaldoDisplay(saldoData) {
        if (saldoData) {
            const totalDana = $('#totalDanaTersediaDisplay');
            const saldoNonTunai = $('#saldoNonTunaiDisplay');
            const saldoTunai = $('#saldoTunaiDisplay');

            if (totalDana.length) {
                totalDana.text('Rp ' + this.formatRupiah(saldoData.total_dana_tersedia));
            }
            
            if (saldoNonTunai.length) {
                saldoNonTunai.val('Rp ' + this.formatRupiah(saldoData.non_tunai));
            }
            
            if (saldoTunai.length) {
                saldoTunai.val('Rp ' + this.formatRupiah(saldoData.tunai));
            }
        }
    }

    /**
     * Format angka ke Rupiah (method yang sudah ada, dipindahkan ke sini untuk konsistensi)
     */
    formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    /**
     * Attach event listeners untuk penarikan tunai
     */
    attachTarikTunaiEvents() {
        console.log('Attaching tarik tunai events...');
        
        // Event untuk simpan penarikan tunai
        $(document).on('click', '#btnSimpanTarik', this.handleSimpanTarikTunai.bind(this));
        
        // Event untuk format input rupiah
        $(document).on('blur', '#jumlah_penarikan', this.formatRupiahInput.bind(this));
        $(document).on('focus', '#jumlah_penarikan', (e) => {
            const input = $(e.target);
            input.val(input.val().replace(/[^\d]/g, ''));
        });
        
        // Event saat modal penarikan tunai dibuka
        $('#tarikTunai').on('show.bs.modal', this.handleTarikTunaiModalShow.bind(this));
        
        // Event saat modal penarikan tunai ditutup
        $('#tarikTunai').on('hidden.bs.modal', () => {
            $('#formTarikTunai')[0].reset();
            this.resetTarikTunaiErrors();
        });
        
        // Real-time validation untuk penarikan tunai
        this.attachTarikTunaiRealTimeValidation();
    }

    /**
     * Real-time validation untuk penarikan tunai
     */
    attachTarikTunaiRealTimeValidation() {
        $(document).on('input', '#jumlah_penarikan', (e) => {
            const input = $(e.target);
            const value = input.val().replace(/[^\d]/g, '');
            const maxAmount = parseFloat(input.attr('data-max')) || 0;
            const jumlahNum = parseFloat(value) || 0;
            
            // Reset error
            input.removeClass('is-invalid');
            $('#jumlah_penarikan_error').text('');
            
            // Validasi real-time
            if (value && jumlahNum > maxAmount) {
                input.addClass('is-invalid');
                $('#jumlah_penarikan_error').text(`Melebihi maksimal (Rp ${this.formatRupiah(maxAmount)})`);
                
                // Update tampilan jumlah maksimal dengan warna merah
                const maxInfo = input.next('small');
                if (maxInfo.length) {
                    maxInfo.addClass('max-limit-exceeded')
                        .html(`<i class="bi bi-exclamation-triangle-fill"></i> Maksimal: Rp ${this.formatRupiah(maxAmount)}`);
                }
            } else {
                // Reset tampilan jumlah maksimal
                const maxInfo = input.next('small');
                if (maxInfo.length) {
                    maxInfo.removeClass('max-limit-exceeded')
                        .text(`Maksimal: Rp ${this.formatRupiah(maxAmount)}`);
                }
            }
            
            // Format sebagai rupiah
            if (value) {
                setTimeout(() => {
                    input.val(this.formatRupiah(jumlahNum));
                }, 100);
            }
        });
    }

    /**
     * Handle modal penarikan tunai show
     */
    handleTarikTunaiModalShow() {
        console.log('Modal penarikan tunai dibuka');
        
        // Reset form
        this.resetTarikTunaiErrors();
        $('#formTarikTunai')[0].reset();
        
        // Set tanggal default ke hari ini (dalam range bulan)
        const today = new Date();
        const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
        
        let defaultDate = dateRange.min; // Default ke awal bulan
        const todayStr = today.toISOString().split('T')[0];
        
        // Jika hari ini dalam range bulan, gunakan hari ini
        if (todayStr >= dateRange.min && todayStr <= dateRange.max) {
            defaultDate = todayStr;
        }
        
        $('#tanggal_penarikan').val(defaultDate);
        
        // Update saldo non tunai dari display saat ini
        const saldoNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
        $('#jumlah_penarikan').attr('data-max', saldoNonTunai);
        
        // Update text maksimal
        const maxInfo = $('#jumlah_penarikan').next('small');
        if (maxInfo.length) {
            maxInfo.text(`Maksimal: Rp ${this.formatRupiah(saldoNonTunai)}`);
        }
    }

    /**
     * Reset error penarikan tunai
     */
    resetTarikTunaiErrors() {
        $('#tanggal_penarikan, #jumlah_penarikan').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    /**
     * Handle input jumlah penarikan
     */
    handleJumlahPenarikanInput(e) {
        const input = $(e.target);
        const maxAmount = parseFloat(input.attr('data-max')) || 0;
        let value = input.val().replace(/[^\d]/g, '');
        
        // Format sebagai rupiah saat user mengetik
        if (value) {
            const numValue = parseInt(value) || 0;
            if (numValue > maxAmount) {
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').remove();
                input.after(`<div class="invalid-feedback">Jumlah melebihi saldo non tunai (Rp ${this.formatRupiah(maxAmount)})</div>`);
            } else {
                input.removeClass('is-invalid');
                input.siblings('.invalid-feedback').remove();
            }
        }
    }

    /**
    * Handle simpan penarikan tunai
    */
    handleSimpanTarikTunai(e) {
        e.preventDefault();
        
        console.log('=== SIMPAN PENARIKAN TUNAI ===');
        
        // Reset error terlebih dahulu
        this.resetTarikTunaiErrors();
        
        // Validasi form
        if (!this.validateTarikTunaiForm()) {
            console.log('Form validation failed');
            return;
        }
        
        // Kumpulkan data form
        const formData = this.collectTarikTunaiFormData();
        
        // Tampilkan loading
        const button = $('#btnSimpanTarik');
        const originalText = button.html();
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        // Kirim ke server
        this.submitTarikTunaiForm(formData, button, originalText);
    }

    /**
    * Validasi form penarikan tunai
    */
    validateTarikTunaiForm() {
        console.log('=== VALIDASI PENARIKAN TUNAI ===');
        
        const tanggal = $('#tanggal_penarikan').val();
        const jumlahInput = $('#jumlah_penarikan');
        const jumlahText = jumlahInput.val();
        const jumlah = jumlahText.replace(/[^\d]/g, '');
        
        console.log('Data form:', { tanggal, jumlahText, jumlah });
        
        let isValid = true;
        let errorMessages = [];
        
        // Reset semua error
        this.resetTarikTunaiErrors();
        
        // 1. Validasi tanggal
        if (!tanggal) {
            errorMessages.push('Tanggal penarikan wajib diisi');
            $('#tanggal_penarikan').addClass('is-invalid');
            $('#tanggal_penarikan_error').text('Tanggal penarikan wajib diisi');
            isValid = false;
        } else {
            // Validasi tanggal dalam range bulan
            const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
            const selectedDate = new Date(tanggal);
            const minDate = new Date(dateRange.min);
            const maxDate = new Date(dateRange.max);
            
            if (selectedDate < minDate || selectedDate > maxDate) {
                errorMessages.push(`Tanggal harus antara ${this.formatDateToDisplay(minDate)} dan ${this.formatDateToDisplay(maxDate)}`);
                $('#tanggal_penarikan').addClass('is-invalid');
                $('#tanggal_penarikan_error').text(
                    `Tanggal harus antara ${minDate.toLocaleDateString('id-ID')} dan ${maxDate.toLocaleDateString('id-ID')}`
                );
                isValid = false;
            }
        }
        
        // 2. Validasi jumlah
        if (!jumlah) {
            errorMessages.push('Jumlah penarikan wajib diisi');
            jumlahInput.addClass('is-invalid');
            $('#jumlah_penarikan_error').text('Jumlah penarikan wajib diisi');
            isValid = false;
        } else {
            const jumlahNum = parseFloat(jumlah);
            const maxAmount = parseFloat(jumlahInput.attr('data-max')) || 0;
            
            console.log('Jumlah validasi:', { 
                jumlahNum, 
                maxAmount, 
                lebihBesar: jumlahNum > maxAmount 
            });
            
            if (isNaN(jumlahNum) || jumlahNum <= 0) {
                errorMessages.push('Jumlah penarikan harus lebih dari 0');
                jumlahInput.addClass('is-invalid');
                $('#jumlah_penarikan_error').text('Jumlah penarikan harus lebih dari 0');
                isValid = false;
            } else if (maxAmount > 0 && jumlahNum > maxAmount) {
                errorMessages.push(`Jumlah penarikan (Rp ${this.formatRupiah(jumlahNum)}) melebihi saldo non tunai (Rp ${this.formatRupiah(maxAmount)})`);
                jumlahInput.addClass('is-invalid');
                $('#jumlah_penarikan_error').text(`Melebihi saldo non tunai (Rp ${this.formatRupiah(maxAmount)})`);
                isValid = false;
            }
        }
        
        // Tampilkan semua error jika ada
        if (errorMessages.length > 0) {
            console.log('Validation errors:', errorMessages);
            
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<div class="text-start"><p>Perbaiki error berikut:</p><ul class="mb-0">' + 
                    errorMessages.map(msg => `<li>${msg}</li>`).join('') + 
                    '</ul></div>',
                confirmButtonColor: '#0d6efd',
            });
        }
        
        console.log('Validation result:', isValid);
        return isValid;
    }

    /**
    * Kumpulkan data form penarikan tunai
    */
    collectTarikTunaiFormData() {
        const penganggaranId = this.getPenganggaranId();
        
        if (!penganggaranId) {
            Swal.fire({
                title: 'Error!',
                text: 'Data penganggaran tidak ditemukan. Silakan refresh halaman.',
                icon: 'error'
            });
            return null;
        }
        
        console.log('Collecting penarikan form data, penganggaran_id:', penganggaranId);
        
        return {
            penganggaran_id: penganggaranId,
            tanggal_penarikan: $('#tanggal_penarikan').val(),
            jumlah_penarikan: $('#jumlah_penarikan').val().replace(/[^\d]/g, ''),
            _token: $('meta[name="csrf-token"]').attr('content'),
            bulan: this.bulan,
            tahun: this.tahun
        };
    }

    /**
    * Kirim form penarikan tunai ke server
    */
    submitTarikTunaiForm(formData, button, originalText) {
        console.log('Submitting penarikan tunai form:', formData);
        
        $.ajax({
            url: '/bku/penarikan-tunai',
            method: 'POST',
            data: formData,
            success: (response) => {
                console.log('Response received:', response);
                
                // Reset button
                button.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        showConfirmButton: true,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Tutup modal
                        $('#tarikTunai').modal('hide');
                        
                        // Reset form
                        $('#formTarikTunai')[0].reset();
                        this.resetTarikTunaiErrors();
                        
                        // Update tabel dan data tanpa reload
                        this.handlePenarikanTunaiSaved(response);
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Gagal menyimpan penarikan tunai',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                    });
                }
            },
            error: (xhr, status, error) => {
                console.error('Error submitting penarikan tunai:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                // Reset button
                button.prop('disabled', false).html(originalText);
                
                let errorMessage = 'Terjadi kesalahan saat menyimpan data penarikan tunai';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // Tampilkan error validasi field-by-field
                    if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach((field) => {
                            const fieldId = field === 'jumlah_penarikan' ? 'jumlah_penarikan' : field;
                            const errorElement = $(`#${fieldId}_error`);
                            
                            if (errorElement.length) {
                                errorElement.text(errors[field][0]);
                            } else {
                                $(`#${fieldId}`).addClass('is-invalid')
                                    .after(`<div class="invalid-feedback" id="${fieldId}_error">${errors[field][0]}</div>`);
                            }
                        });
                        
                        errorMessage = 'Terjadi kesalahan validasi data';
                    }
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                });
            }
        });
    }

    /**
    * Handle setelah penarikan tunai disimpan
    */
    handlePenarikanTunaiSaved(response) {
        console.log('Handling penarikan tunai saved:', response);
        
        // Update saldo display secara manual
        const jumlahPenarikan = parseFloat($('#jumlah_penarikan').val().replace(/[^\d]/g, '')) || 0;
        
        // Update saldo non tunai (berkurang)
        const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
        const newNonTunai = currentNonTunai - jumlahPenarikan;
        $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newNonTunai)));
        
        // Update saldo tunai (bertambah)
        const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
        const newTunai = currentTunai + jumlahPenarikan;
        $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(newTunai));
        
        // Update total dana tersedia
        const totalDana = newNonTunai + newTunai;
        $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDana));
        
        // Update tabel data
        this.updateTableAfterPenarikan();
        
        // Update summary data
        this.updateSummaryAfterPenarikan();
    }

    /**
    * Update saldo setelah penarikan tunai
    */
    async updateSaldoAfterPenarikan() {
        try {
            // Ambil penganggaran ID dengan benar
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) {
                console.error('Penganggaran ID not found');
                return;
            }
            
            console.log('Updating saldo after penarikan, penganggaranId:', penganggaranId);
            
            // Ambil saldo terbaru
            const response = await $.ajax({
                url: `/bku/total-dana-tersedia/${penganggaranId}`,
                method: 'GET'
            });
            
            console.log('Saldo response:', response);
            
            if (response.success) {
                // Update total dana tersedia
                $('h4.fw-semibold.text-dark').text(response.formatted_total);
                
                // Hitung saldo baru secara manual
                const jumlahPenarikan = parseFloat($('#jumlah_penarikan').val().replace(/[^\d]/g, '')) || 0;
                
                // Saldo non tunai berkurang
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newNonTunai = currentNonTunai - jumlahPenarikan;
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newNonTunai)));
                
                // Saldo tunai bertambah
                const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newTunai = currentTunai + jumlahPenarikan;
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(newTunai));
                
                console.log('Saldo updated successfully:', {
                    jumlahPenarikan,
                    oldNonTunai: currentNonTunai,
                    newNonTunai,
                    oldTunai: currentTunai,
                    newTunai
                });
            }
        } catch (error) {
            console.error('Error updating saldo after penarikan:', error);
            
            // Fallback: hitung saldo secara manual
            const jumlahPenarikan = parseFloat($('#jumlah_penarikan').val().replace(/[^\d]/g, '')) || 0;
            
            // Update saldo secara manual
            const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
            const newNonTunai = currentNonTunai - jumlahPenarikan;
            $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newNonTunai)));
            
            const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
            const newTunai = currentTunai + jumlahPenarikan;
            $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(newTunai));
            
            // Update total dana tersedia
            const totalDana = newNonTunai + newTunai;
            $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDana));
        }
    }

    /**
    * Update tabel setelah penarikan tunai
    */
    async updateTableAfterPenarikan() {
        try {
            console.log('Updating table after penarikan...');
            
            // Ambil data tabel terbaru
            const response = await $.ajax({
                url: `/bku/table-data/${this.tahun}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            console.log('Table data response:', response);
            
            if (response.success && response.data) {
                // Format data penarikan tunai dari response
                const penarikanData = response.data.penarikan_tunais || [];
                
                console.log('Penarikan data found:', penarikanData.length);
                
                if (penarikanData.length > 0) {
                    // Hapus semua baris penarikan tunai yang ada
                    $('#bkuTableBody .penarikan-row').remove();
                    
                    // Temukan posisi untuk menambahkan baris penarikan
                    const firstBkuRow = $('#bkuTableBody .bku-row:first');
                    const firstPenerimaanRow = $('#bkuTableBody .penerimaan-row:first');
                    const firstSetorRow = $('#bkuTableBody .setor-row:first');
                    
                    // Buat HTML untuk setiap penarikan baru
                    penarikanData.forEach((penarikan, index) => {
                        const newRow = `
                            <tr class="bg-light penarikan-row">
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">${penarikan.tanggal}</td>
                                <td class="px-4 py-3 fw-semibold">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-arrow-right-circle text-danger me-2"></i>
                                        <span>${penarikan.uraian}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="dropdown dropstart">
                                        <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button"
                                            id="dropdownMenuButtonPenarikan${penarikan.id}" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButtonPenarikan${penarikan.id}">
                                            <li>
                                                <a class="dropdown-item text-danger btn-hapus-penarikan" href="${penarikan.delete_url}"
                                                    data-id="${penarikan.id}">
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `;
                        
                        // Tambahkan baris baru
                        if (firstBkuRow.length) {
                            $(newRow).insertBefore(firstBkuRow);
                        } else if (firstSetorRow.length) {
                            $(newRow).insertBefore(firstSetorRow);
                        } else if (firstPenerimaanRow.length) {
                            $(newRow).insertBefore(firstPenerimaanRow);
                        } else {
                            $('#bkuTableBody').prepend(newRow);
                        }
                    });
                    
                    console.log('Penarikan rows added successfully');
                }
                
                // Update summary data
                if (response.data.summary) {
                    this.updateSummaryDisplay(response.data.summary);
                }
                
                // Re-attach event listeners untuk row baru
                this.attachBkuEventListeners();
                
            } else {
                console.error('Failed to update table:', response.message);
            }
        } catch (error) {
            console.error('Error updating table after penarikan:', error);
            
            // Fallback: reload data tabel secara lengkap
            this.loadInitialTableData();
        }
    }

    /**
    * Update summary setelah penarikan tunai
    */
    async updateSummaryAfterPenarikan() {
        await this.updateSummaryAfterDelete(); // Gunakan method yang sama
    }

    /**
     * Format input rupiah
     */
    formatRupiahInput(e) {
        const input = $(e.target);
        let value = input.val().replace(/[^\d]/g, '');
        
        if (value) {
            const numValue = parseInt(value) || 0;
            input.val(this.formatRupiah(numValue));
        }
    }

    /**
     * Parse rupiah ke angka
     */
    parseRupiah(rupiahString) {
        if (!rupiahString) return 0;
        return parseInt(rupiahString.toString().replace(/[^\d]/g, '')) || 0;
    }
    
    /**
     * Update saldo tunai dari API
     */
    async updateSaldoTunaiFromAPI() {
        try {
            const penganggaranId = document.querySelector('meta[name="penganggaran-id"]')?.content || 
                                document.querySelector('input[name="penganggaran_id"]')?.value;
            
            if (!penganggaranId) {
                console.error('Penganggaran ID not found');
                return 0;
            }
            
            const response = await $.ajax({
                url: `/bku/saldo-tunai/${penganggaranId}`,
                method: 'GET'
            });
            
            if (response.success) {
                // Update display saldo tunai
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(response.saldo_tunai));
                
                // Update max attribute pada input jumlah_setor
                $('#jumlah_setor').attr('data-max', response.saldo_tunai);
                
                // Update text maksimal
                const maxInfo = $('#jumlah_setor').next('small');
                if (maxInfo.length) {
                    maxInfo.text(`Maksimal: Rp ${this.formatRupiah(response.saldo_tunai)}`);
                }
                
                return response.saldo_tunai;
            }
            
            return 0;
        } catch (error) {
            console.error('Error updating saldo tunai from API:', error);
            return 0;
        }
    }

    /**
    * Attach event listeners untuk setor tunai
    */
    attachSetorTunaiEvents() {
        console.log('Attaching setor tunai events...');
        
        // Event untuk simpan setor tunai
        $(document).on('click', '#btnSimpanSetor', this.handleSimpanSetorTunai.bind(this));
        
        // Event untuk format input rupiah
        $(document).on('blur', '#jumlah_setor', this.formatRupiahInput.bind(this));
        $(document).on('focus', '#jumlah_setor', (e) => {
            const input = $(e.target);
            input.val(input.val().replace(/[^\d]/g, ''));
        });
        
        // Event saat modal setor tunai dibuka
        $('#setorTunai').on('show.bs.modal', this.handleSetorTunaiModalShow.bind(this));
        
        // Event saat modal setor tunai ditutup
        $('#setorTunai').on('hidden.bs.modal', () => {
            this.resetSetorTunaiErrors();
            $('#formSetorTunai')[0].reset();
        });
        
        // Tambahkan real-time validation
        this.attachSetorTunaiRealTimeValidation();
    }

    /**
    * Real-time validation untuk setor tunai
    */
    attachSetorTunaiRealTimeValidation() {
        $(document).on('input', '#jumlah_setor', (e) => {
            const input = $(e.target);
            const value = input.val().replace(/[^\d]/g, '');
            const maxAmount = parseFloat(input.attr('data-max')) || 0;
            const jumlahNum = parseFloat(value) || 0;
            
            // Reset error
            input.removeClass('is-invalid');
            $('#jumlah_setor_error').text('');
            
            // Validasi real-time
            if (value && jumlahNum > maxAmount) {
                input.addClass('is-invalid');
                $('#jumlah_setor_error').text(`Melebihi maksimal (Rp ${this.formatRupiah(maxAmount)})`);
                
                // Update tampilan jumlah maksimal dengan warna merah
                const maxInfo = input.next('small');
                if (maxInfo.length) {
                    maxInfo.addClass('max-limit-exceeded')
                        .html(`<i class="bi bi-exclamation-triangle-fill"></i> Maksimal: Rp ${this.formatRupiah(maxAmount)}`);
                }
            } else {
                // Reset tampilan jumlah maksimal
                const maxInfo = input.next('small');
                if (maxInfo.length) {
                    maxInfo.removeClass('max-limit-exceeded')
                        .text(`Maksimal: Rp ${this.formatRupiah(maxAmount)}`);
                }
            }
            
            // Format sebagai rupiah
            if (value) {
                setTimeout(() => {
                    input.val(this.formatRupiah(jumlahNum));
                }, 100);
            }
        });
        
        // Validasi tanggal real-time
        $(document).on('change', '#tanggal_setor', (e) => {
            const input = $(e.target);
            const tanggal = input.val();
            
            if (tanggal) {
                const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
                const selectedDate = new Date(tanggal);
                const minDate = new Date(dateRange.min);
                const maxDate = new Date(dateRange.max);
                
                if (selectedDate < minDate || selectedDate > maxDate) {
                    input.addClass('is-invalid');
                    $('#tanggal_setor_error').text(
                        `Tanggal harus antara ${minDate.toLocaleDateString('id-ID')} dan ${maxDate.toLocaleDateString('id-ID')}`
                    );
                } else {
                    input.removeClass('is-invalid');
                    $('#tanggal_setor_error').text('');
                }
            }
        });
    }

    /**
    * Handle modal setor tunai show
    */
    handleSetorTunaiModalShow() {
        console.log('Modal setor tunai dibuka');
        
        // Reset form dan error
        this.resetSetorTunaiErrors();
        $('#formSetorTunai')[0].reset();
        
        // Set tanggal default ke hari ini (dalam range bulan)
        const today = new Date();
        const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
        
        let defaultDate = dateRange.min; // Default ke awal bulan
        const todayStr = today.toISOString().split('T')[0];
        
        // Jika hari ini dalam range bulan, gunakan hari ini
        if (todayStr >= dateRange.min && todayStr <= dateRange.max) {
            defaultDate = todayStr;
        }
        
        $('#tanggal_setor').val(defaultDate);
        
        // Update saldo tunai dari display saat ini
        const saldoTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
        $('#jumlah_setor').attr('data-max', saldoTunai);
        
        // Update text maksimal
        const maxInfo = $('#jumlah_setor').next('small');
        if (maxInfo.length) {
            maxInfo.text(`Maksimal: Rp ${this.formatRupiah(saldoTunai)}`);
        }
    }

    // handle input jumlah setor
    handleJumlahSetorInput(e) {
        const input = $(e.target);
        const maxAmount = parseFloat(input.attr('data-max')) || 0;
        let value = input.val().replace(/[^\d]/g, '');

        // format sebagai rupiah saat user mengetik
        if (value) {
            const numValue = parseInt(value) || 0;
            if (numValue > maxAmount) {
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').remove();
                input.after(`<div class="invalid-feedback">Jumlah melebihi saldo non tunai (Rp ${this.formatRupiah(maxAmount)})</div>`);
            } else {
                input.removeClass('is-invalid');
                input.siblings('.invalid-feedback').remove();
            }
        }
    }

    // Tambahkan method formatDateToDisplay
    formatDateToDisplay(date) {
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    /**
    * Handle simpan setor tunai
    */
    handleSimpanSetorTunai(e) {
        e.preventDefault();
        
        console.log('=== SIMPAN SETOR TUNAI ===');
        
        // Reset error terlebih dahulu
        this.resetSetorTunaiErrors();
        
        // Validasi form
        if (!this.validateSetorTunaiForm()) {
            console.log('Form validation failed');
            return;
        }
        
        // Kumpulkan data form
        const formData = this.collectSetorTunaiFormData();
        
        // Validasi tambahan: cek apakah jumlah setor melebihi saldo tunai
        const saldoTunai = parseFloat($('#jumlah_setor').attr('data-max')) || 0;
        const jumlahSetor = parseFloat(formData.jumlah_setor) || 0;
        
        console.log('Validasi jumlah:', {
            saldoTunai: saldoTunai,
            jumlahSetor: jumlahSetor,
            formData: formData
        });
        
        if (jumlahSetor > saldoTunai) {
            this.showSetorTunaiError(
                'jumlah_setor', 
                `Jumlah setor (Rp ${this.formatRupiah(jumlahSetor)}) melebihi saldo tunai (Rp ${this.formatRupiah(saldoTunai)})`
            );
            return;
        }
        
        if (jumlahSetor <= 0) {
            this.showSetorTunaiError('jumlah_setor', 'Jumlah setor harus lebih dari 0');
            return;
        }
        
        // Tampilkan loading
        const button = $('#btnSimpanSetor');
        const originalText = button.html();
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        // Kirim ke server
        this.submitSetorTunaiForm(formData, button, originalText);
    }

    /**
    * Validasi form setor tunai
    */
    validateSetorTunaiForm() {
        console.log('=== VALIDASI SETOR TUNAI ===');
        
        const tanggal = $('#tanggal_setor').val();
        const jumlahInput = $('#jumlah_setor');
        const jumlahText = jumlahInput.val();
        const jumlah = jumlahText.replace(/[^\d]/g, '');
        
        console.log('Data form:', { tanggal, jumlahText, jumlah });
        
        let isValid = true;
        let errorMessages = [];
        
        // Reset semua error
        this.resetSetorTunaiErrors();
        
        // 1. Validasi tanggal
        if (!tanggal) {
            errorMessages.push('Tanggal setor wajib diisi');
            $('#tanggal_setor').addClass('is-invalid');
            $('#tanggal_setor_error').text('Tanggal setor wajib diisi');
            isValid = false;
        } else {
            // Validasi tanggal dalam range bulan
            const dateRange = this.getDateRangeForMonth(this.bulan, this.tahun);
            const selectedDate = new Date(tanggal);
            const minDate = new Date(dateRange.min);
            const maxDate = new Date(dateRange.max);
            
            if (selectedDate < minDate || selectedDate > maxDate) {
                errorMessages.push(`Tanggal harus antara ${this.formatDateToDisplay(minDate)} dan ${this.formatDateToDisplay(maxDate)}`);
                $('#tanggal_setor').addClass('is-invalid');
                $('#tanggal_setor_error').text(
                    `Tanggal harus antara ${minDate.toLocaleDateString('id-ID')} dan ${maxDate.toLocaleDateString('id-ID')}`
                );
                isValid = false;
            }
        }
        
        // 2. Validasi jumlah
        if (!jumlah) {
            errorMessages.push('Jumlah setor wajib diisi');
            jumlahInput.addClass('is-invalid');
            $('#jumlah_setor_error').text('Jumlah setor wajib diisi');
            isValid = false;
        } else {
            const jumlahNum = parseFloat(jumlah);
            const maxAmount = parseFloat(jumlahInput.attr('data-max')) || 0;
            
            console.log('Jumlah validasi:', { 
                jumlahNum, 
                maxAmount, 
                lebihBesar: jumlahNum > maxAmount 
            });
            
            if (isNaN(jumlahNum) || jumlahNum <= 0) {
                errorMessages.push('Jumlah setor harus lebih dari 0');
                jumlahInput.addClass('is-invalid');
                $('#jumlah_setor_error').text('Jumlah setor harus lebih dari 0');
                isValid = false;
            } else if (maxAmount > 0 && jumlahNum > maxAmount) {
                errorMessages.push(`Jumlah setor (Rp ${this.formatRupiah(jumlahNum)}) melebihi saldo tunai (Rp ${this.formatRupiah(maxAmount)})`);
                jumlahInput.addClass('is-invalid');
                $('#jumlah_setor_error').text(`Melebihi saldo tunai (Rp ${this.formatRupiah(maxAmount)})`);
                isValid = false;
            }
        }
        
        // Tampilkan semua error jika ada
        if (errorMessages.length > 0) {
            console.log('Validation errors:', errorMessages);
            
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<div class="text-start"><p>Perbaiki error berikut:</p><ul class="mb-0">' + 
                    errorMessages.map(msg => `<li>${msg}</li>`).join('') + 
                    '</ul></div>',
                confirmButtonColor: '#0d6efd',
            });
        }
        
        console.log('Validation result:', isValid);
        return isValid;
    }

    /**
    * Kumpulkan data form setor tunai
    */
    collectSetorTunaiFormData() {
        const penganggaranId = this.getPenganggaranId();
        
        if (!penganggaranId) {
            Swal.fire({
                title: 'Error!',
                text: 'Data penganggaran tidak ditemukan. Silakan refresh halaman.',
                icon: 'error'
            });
            return null;
        }
        
        console.log('Collecting setor form data, penganggaran_id:', penganggaranId);
        
        return {
            penganggaran_id: penganggaranId,
            tanggal_setor: $('#tanggal_setor').val(),
            jumlah_setor: $('#jumlah_setor').val().replace(/[^\d]/g, ''),
            _token: $('meta[name="csrf-token"]').attr('content'),
            bulan: this.bulan,
            tahun: this.tahun
        };
    }

    /**
    * Kirim form setor tunai ke server
    */
    submitSetorTunaiForm(formData, button, originalText) {
        console.log('Submitting setor tunai form:', formData);
        
        $.ajax({
            url: '/bku/setor-tunai',
            method: 'POST',
            data: formData,
            success: (response) => {
                console.log('Response received:', response);
                
                // Reset button
                button.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        showConfirmButton: true,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Tutup modal
                        $('#setorTunai').modal('hide');
                        
                        // Reset form
                        $('#formSetorTunai')[0].reset();
                        this.resetSetorTunaiErrors();
                        
                        // Update tabel dan data tanpa reload
                        this.handleSetorTunaiSaved(response);
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Gagal menyimpan data setor tunai',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                    });
                }
            },
            error: (xhr, status, error) => {
                console.error('Error submitting setor tunai:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                // Reset button
                button.prop('disabled', false).html(originalText);
                
                let errorMessage = 'Terjadi kesalahan saat menyimpan data setor tunai';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // Tampilkan error validasi field-by-field
                    if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach((field) => {
                            const fieldId = field === 'jumlah_setor' ? 'jumlah_setor' : field;
                            const errorElement = $(`#${fieldId}_error`);
                            
                            if (errorElement.length) {
                                errorElement.text(errors[field][0]);
                            } else {
                                $(`#${fieldId}`).addClass('is-invalid')
                                    .after(`<div class="invalid-feedback" id="${fieldId}_error">${errors[field][0]}</div>`);
                            }
                        });
                        
                        errorMessage = 'Terjadi kesalahan validasi data';
                    }
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                });
            }
        });
    }

    /**
    * Handle setelah setor tunai disimpan
    */
    handleSetorTunaiSaved(response) {
        console.log('Handling setor tunai saved:', response);
        
        // Update saldo display secara manual
        const jumlahSetor = parseFloat($('#jumlah_setor').val().replace(/[^\d]/g, '')) || 0;
        
        // Update saldo tunai (berkurang)
        const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
        const newTunai = currentTunai - jumlahSetor;
        $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newTunai)));
        
        // Update saldo non tunai (bertambah)
        const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
        const newNonTunai = currentNonTunai + jumlahSetor;
        $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(newNonTunai));
        
        // Update total dana tersedia
        const totalDana = newNonTunai + newTunai;
        $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDana));
        
        // Update tabel data
        this.updateTableAfterSetor();
        
        // Update summary data
        this.updateSummaryAfterSetor();
    }

    /**
    * Update saldo setelah setor tunai
    */
    async updateSaldoAfterSetor() {
        try {
            // Ambil penganggaran ID dengan benar
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) {
                console.error('Penganggaran ID not found');
                return;
            }
            
            console.log('Updating saldo after setor, penganggaranId:', penganggaranId);
            
            // Ambil saldo terbaru
            const response = await $.ajax({
                url: `/bku/total-dana-tersedia/${penganggaranId}`,
                method: 'GET'
            });
            
            console.log('Saldo response:', response);
            
            if (response.success) {
                // Update total dana tersedia
                $('h4.fw-semibold.text-dark').text(response.formatted_total);
                
                // Hitung saldo baru secara manual
                const jumlahSetor = parseFloat($('#jumlah_setor').val().replace(/[^\d]/g, '')) || 0;
                
                // Saldo tunai berkurang
                const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newTunai = currentTunai - jumlahSetor;
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newTunai)));
                
                // Saldo non tunai bertambah
                const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
                const newNonTunai = currentNonTunai + jumlahSetor;
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(newNonTunai));
                
                console.log('Saldo updated successfully:', {
                    jumlahSetor,
                    oldTunai: currentTunai,
                    newTunai,
                    oldNonTunai: currentNonTunai,
                    newNonTunai
                });
            }
        } catch (error) {
            console.error('Error updating saldo after setor:', error);
            
            // Fallback: hitung saldo secara manual
            const jumlahSetor = parseFloat($('#jumlah_setor').val().replace(/[^\d]/g, '')) || 0;
            
            // Update saldo secara manual
            const currentTunai = parseFloat($('#saldoTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
            const newTunai = currentTunai - jumlahSetor;
            $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(Math.max(0, newTunai)));
            
            const currentNonTunai = parseFloat($('#saldoNonTunaiDisplay').val().replace(/[^\d]/g, '')) || 0;
            const newNonTunai = currentNonTunai + jumlahSetor;
            $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(newNonTunai));
            
            // Update total dana tersedia
            const totalDana = newNonTunai + newTunai;
            $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDana));
        }
    }

    /**
    * Update tabel setelah setor tunai
    */
    async updateTableAfterSetor() {
        try {
            console.log('Updating table after setor...');
            
            // Ambil data tabel terbaru
            const response = await $.ajax({
                url: `/bku/table-data/${this.tahun}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            console.log('Table data response:', response);
            
            if (response.success && response.data) {
                // Format data setor tunai dari response
                const setorData = response.data.setor_tunais || [];
                
                console.log('Setor data found:', setorData.length);
                
                if (setorData.length > 0) {
                    // Hapus semua baris setor tunai yang ada
                    $('#bkuTableBody .setor-row').remove();
                    
                    // Temukan posisi untuk menambahkan baris setor
                    const firstBkuRow = $('#bkuTableBody .bku-row:first');
                    const firstPenarikanRow = $('#bkuTableBody .penarikan-row:first');
                    const firstPenerimaanRow = $('#bkuTableBody .penerimaan-row:first');
                    
                    // Buat HTML untuk setiap setor baru
                    setorData.forEach((setor, index) => {
                        const newRow = `
                            <tr class="bg-light setor-row">
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">${setor.tanggal}</td>
                                <td class="px-4 py-3 fw-semibold">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-arrow-left-circle text-success me-2"></i>
                                        <span>${setor.uraian}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="dropdown dropstart">
                                        <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button"
                                            id="dropdownMenuButtonSetor${setor.id}" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButtonSetor${setor.id}">
                                            <li>
                                                <a class="dropdown-item text-danger btn-hapus-setor" href="${setor.delete_url}"
                                                    data-id="${setor.id}">
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `;
                        
                        // Tambahkan baris baru
                        if (firstBkuRow.length) {
                            $(newRow).insertBefore(firstBkuRow);
                        } else if (firstPenarikanRow.length) {
                            $(newRow).insertBefore(firstPenarikanRow);
                        } else if (firstPenerimaanRow.length) {
                            $(newRow).insertBefore(firstPenerimaanRow);
                        } else {
                            $('#bkuTableBody').prepend(newRow);
                        }
                    });
                    
                    console.log('Setor rows added successfully');
                }
                
                // Update summary data
                if (response.data.summary) {
                    this.updateSummaryDisplay(response.data.summary);
                }
                
                // Re-attach event listeners untuk row baru
                this.attachBkuEventListeners();
                
            } else {
                console.error('Failed to update table:', response.message);
            }
        } catch (error) {
            console.error('Error updating table after setor:', error);
            
            // Fallback: reload data tabel secara lengkap
            this.loadInitialTableData();
        }
    }

    /**
    * Update summary setelah setor tunai
    */
    async updateSummaryAfterSetor() {
        await this.updateSummaryAfterDelete(); // Gunakan method yang sama
    }

    /**
    * Method untuk menampilkan error setor tunai
    */
    showSetorTunaiError(fieldId, message) {
        const field = $(`#${fieldId}`);
        const errorElement = $(`#${fieldId}_error`);
        
        field.addClass('is-invalid');
        
        if (errorElement.length) {
            errorElement.text(message);
        } else {
            field.after(`<div class="invalid-feedback" id="${fieldId}_error">${message}</div>`);
        }
        
        // Scroll ke field yang error
        field.focus();
        
        // Tampilkan alert juga untuk user feedback yang lebih jelas
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: message,
            confirmButtonColor: '#0d6efd',
        });
    }

    /**
    * Reset error setor tunai
    */
    resetSetorTunaiErrors() {
        $('#tanggal_setor, #jumlah_setor').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    /**
    * Get penganggaran ID dari berbagai sumber
    */
    getPenganggaranId() {
        // Coba dari meta tag
        const metaPenganggaranId = document.querySelector('meta[name="penganggaran-id"]')?.content;
        if (metaPenganggaranId) {
            return metaPenganggaranId;
        }
        
        // Coba dari container data attribute
        const container = document.querySelector('[data-penganggaran-id]');
        if (container && container.dataset.penganggaranId) {
            return container.dataset.penganggaranId;
        }
        
        // Coba dari URL query string
        const urlParams = new URLSearchParams(window.location.search);
        const urlPenganggaranId = urlParams.get('penganggaran_id');
        if (urlPenganggaranId) {
            return urlPenganggaranId;
        }
        
        console.error('Penganggaran ID not found from any source');
        return null;
    }

    /**
     * Update card anggaran setelah perubahan data
     */
    async updateAnggaranCard() {
        try {
            console.log('Updating anggaran card...');
            
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) {
                console.error('Penganggaran ID not found');
                return;
            }
            
            // Hitung data terbaru
            const anggaranBulanIni = await this.hitungAnggaranBulanIni();
            const totalDibelanjakanBulanIni = await this.hitungTotalDibelanjakanBulanIni();
            const anggaranBelumDibelanjakan = await this.hitungAnggaranBelumDibelanjakan();
            
            // Update display
            $('#anggaranBisaDibelanjakan').val('Rp ' + this.formatRupiah(anggaranBulanIni));
            $('#sudahDibelanjakan').val('Rp ' + this.formatRupiah(totalDibelanjakanBulanIni));
            $('#bisaDianggarkanUlang').val('Rp ' + this.formatRupiah(anggaranBelumDibelanjakan));
            
            console.log('Anggaran card updated:', {
                anggaranBulanIni,
                totalDibelanjakanBulanIni,
                anggaranBelumDibelanjakan
            });
            
        } catch (error) {
            console.error('Error updating anggaran card:', error);
        }
    }

    /**
     * Hitung anggaran bulan ini
     */
    async hitungAnggaranBulanIni() {
        try {
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) return 0;
            
            const response = await $.ajax({
                url: `/bku/anggaran-bulan-ini/${penganggaranId}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            return response.success ? response.total_anggaran : 0;
        } catch (error) {
            console.error('Error hitung anggaran bulan ini:', error);
            return 0;
        }
    }

    /**
     * Hitung total dibelanjakan bulan ini
     */
    async hitungTotalDibelanjakanBulanIni() {
        try {
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) return 0;
            
            const response = await $.ajax({
                url: `/bku/total-dibelanjakan/${penganggaranId}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            return response.success ? response.total_dibelanjakan : 0;
        } catch (error) {
            console.error('Error hitung total dibelanjakan:', error);
            return 0;
        }
    }

    /**
     * Hitung anggaran belum dibelanjakan
     */
    async hitungAnggaranBelumDibelanjakan() {
        try {
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) return 0;
            
            const response = await $.ajax({
                url: `/bku/anggaran-belum-dibelanjakan/${penganggaranId}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            return response.success ? response.anggaran_belum_dibelanjakan : 0;
        } catch (error) {
            console.error('Error hitung anggaran belum dibelanjakan:', error);
            return 0;
        }
    }

    /**
     * Method helper untuk mengambil semua data anggaran sekaligus
     */
    async getAnggaranData() {
        try {
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) {
                return {
                    anggaranBulanIni: 0,
                    totalDibelanjakanBulanIni: 0,
                    anggaranBelumDibelanjakan: 0
                };
            }
            
            // Menggunakan summary data yang sudah ada
            const response = await $.ajax({
                url: `/bku/summary-data/${this.tahun}/${encodeURIComponent(this.bulan)}`,
                method: 'GET'
            });
            
            if (response.success && response.data) {
                return {
                    anggaranBulanIni: response.data.anggaran_bulan_ini || 0,
                    totalDibelanjakanBulanIni: response.data.total_dibelanjakan_bulan_ini || 0,
                    anggaranBelumDibelanjakan: response.data.anggaran_belum_dibelanjakan || 0
                };
            }
            
            return {
                anggaranBulanIni: 0,
                totalDibelanjakanBulanIni: 0,
                anggaranBelumDibelanjakan: 0
            };
        } catch (error) {
            console.error('Error get anggaran data:', error);
            return {
                anggaranBulanIni: 0,
                totalDibelanjakanBulanIni: 0,
                anggaranBelumDibelanjakan: 0
            };
        }
    }

    /**
     * Update card anggaran dari data summary
     */
    async updateAnggaranCardFromSummary() {
        try {
            const data = await this.getAnggaranData();
            
            // Update display
            $('#anggaranBisaDibelanjakan').val('Rp ' + this.formatRupiah(data.anggaranBulanIni));
            $('#sudahDibelanjakan').val('Rp ' + this.formatRupiah(data.totalDibelanjakanBulanIni));
            $('#bisaDianggarkanUlang').val('Rp ' + this.formatRupiah(data.anggaranBelumDibelanjakan));
            
            console.log('Anggaran card updated from summary:', data);
        } catch (error) {
            console.error('Error updating anggaran card from summary:', error);
        }
    }

    /**
     * Handle setelah BKU disimpan
     */
    handleBkuSaved(response) {
        console.log('Handling BKU saved:', response);
        
        // Update tabel
        this.updateTableAndData();
        
        // Update card anggaran
        this.updateAnggaranCardFromSummary();
        
        // Update saldo
        this.updateSaldoAfterBku(response);
    }

    /**
     * Tampilkan modal detail - VERSI SEDERHANA
     */
    showDetailModal(bkuId) {
        console.log('Showing detail modal for ID:', bkuId);
        
        const modal = $(`#detailModal${bkuId}`);
        
        if (modal.length > 0) {
            // Modal sudah ada di DOM
            modal.modal('show');
            console.log('Modal found and shown');
        } else {
            // Modal belum ada, coba lagi setelah beberapa saat
            console.log('Modal not found, waiting for DOM update...');
            
            setTimeout(() => {
                const retryModal = $(`#detailModal${bkuId}`);
                if (retryModal.length > 0) {
                    retryModal.modal('show');
                    console.log('Modal found on retry');
                } else {
                    console.log('Modal still not found after retry');
                    
                    // Tampilkan pesan alternatif
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `<div class="text-start">
                            <p>Transaksi berhasil disimpan.</p>
                            <p class="text-muted">Untuk melihat detail lengkap, silakan klik tombol "Lihat Detail" pada transaksi yang baru ditambahkan.</p>
                        </div>`,
                        confirmButtonColor: '#0d6efd',
                    });
                }
            }, 500);
        }
    }

    /**
     * Update setelah BKU dihapus
     */
    async updateAfterBkuDelete() {
        // Update tabel
        await this.updateTableAndData();
        
        // Update card anggaran
        await this.updateAnggaranCardFromSummary();
        
        // Update summary
        await this.updateSummaryAfterDelete();
    }

    /**
     * Update saldo setelah BKU disimpan
     */
    updateSaldoAfterBku(response) {
        try {
            if (response.saldo_update) {
                // Update saldo dari response
                $('#saldoNonTunaiDisplay').val('Rp ' + this.formatRupiah(response.saldo_update.non_tunai || 0));
                $('#saldoTunaiDisplay').val('Rp ' + this.formatRupiah(response.saldo_update.tunai || 0));
                
                // Update total dana tersedia
                const totalDana = (response.saldo_update.non_tunai || 0) + (response.saldo_update.tunai || 0);
                $('h4.fw-semibold.text-dark').text('Rp ' + this.formatRupiah(totalDana));
            }
        } catch (error) {
            console.error('Error updating saldo after BKU:', error);
        }
    }

    /**
    * Update semua UI setelah perubahan data
    */
    async updateAllUIAfterChange() {
        console.log('Updating all UI after change...');
        
        // 1. Update card anggaran
        await this.updateAnggaranCardFromSummary();
        
        // 2. Update saldo display
        await this.updateSaldoDisplayFromAPI();
        
        // 3. Update summary lainnya jika perlu
        await this.updateSummaryAfterDelete();
        
        console.log('All UI updated successfully');
    }

    /**
    * Update saldo display dari API
    */
    async updateSaldoDisplayFromAPI() {
        try {
            const penganggaranId = this.getPenganggaranId();
            
            if (!penganggaranId) return;
            
            const response = await $.ajax({
                url: `/bku/total-dana-tersedia/${penganggaranId}`,
                method: 'GET'
            });
            
            if (response.success) {
                // Update total dana tersedia
                $('h4.fw-semibold.text-dark').text(response.formatted_total);
                
                // Untuk saldo non tunai dan tunai, kita perlu hitung ulang
                // Atau gunakan method lain jika ada
            }
        } catch (error) {
            console.error('Error updating saldo display from API:', error);
        }
    }
}

// Export class untuk digunakan di file lain
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BkuManager;
} else {
    window.BkuManager = BkuManager;
}