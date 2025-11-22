/**
 * Kwitansi Management System
 * Complete JavaScript for Kwitansi Page - FIXED VERSION
 * Features: Search, Filter, Generate, Delete, Pagination, Animations
 */

class KwitansiManager {
    constructor() {
        this.currentFilters = {
            search: '',
            startDate: '',
            endDate: '',
            tahun: ''
        };
        this.searchTimeout = null;
        this.init();
    }

    // Method untuk debug container
    debugContainers() {
        console.log('🔍 Debugging containers:');
        
        const containers = [
            'pagination-container',
            'kwitansi-tbody',
            'searchInput',
            'tahunFilter'
        ];
        
        containers.forEach(id => {
            const element = document.getElementById(id);
            console.log(`Element ${id}:`, element ? 'FOUND' : 'NOT FOUND', element);
        });
        
        // Cari semua pagination containers
        const allPaginationContainers = document.querySelectorAll('.pagination, [class*="pagination"]');
        console.log('All pagination-like containers:', allPaginationContainers);
    }

    // Di dalam class KwitansiManager, tambahkan method debug
    debugTahunFilter() {
        console.log('🔍 Debugging tahun filter:');
        
        const tahunFilter = document.getElementById('tahunFilter');
        console.log('Tahun Filter element:', tahunFilter);
        
        if (tahunFilter) {
            console.log('Tahun Filter options:', tahunFilter.options);
            console.log('Selected value:', tahunFilter.value);
            console.log('Current filters tahun:', this.currentFilters.tahun);
        }
    }

    init() {
        console.log('🚀 Initializing Kwitansi Manager...');
        
        // Debug containers
        this.debugContainers();
        
        // Initialize components
        this.initializeEventListeners();
        this.initializeTooltips();
        this.checkAvailableData();
        this.updateLastUpdated();
        this.initializeModalHandlers();
        this.initializeDateFilters();
        this.initializeTahunFilter();
        
        // Start periodic updates
        this.startPeriodicUpdates();
        
        console.log('✅ Kwitansi Manager initialized successfully');
    }

    initializeEventListeners() {
        // Search functionality dengan debounce
        const searchInput = document.getElementById('searchInput');
        const tahunFilter = document.getElementById('tahunFilter');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentFilters.search = e.target.value;
                
                // Clear existing timeout
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }
                
                // Set new timeout untuk debounce
                this.searchTimeout = setTimeout(() => {
                    this.performSearchWithFilters();
                }, 500);
            });
        }

        // Filter tanggal sudah di handle di initializeDateFilters()
        // Filter tahun sudah di handle di initializeTahunFilter()

        // Action buttons
        this.initializeActionButtons();
        
        // Event listener untuk clear search filters dari empty state
        document.addEventListener('click', (e) => {
            if (e.target.id === 'clear-search-filters') {
                e.preventDefault();
                this.clearAllFilters();
            }
        });
    }

    initializeTahunFilter() {
        const tahunFilter = document.getElementById('tahunFilter');
        if (tahunFilter) {
            console.log('🔄 Initializing tahun filter...');
            
            // Debug initial state
            this.debugTahunFilter();
            
            // Set nilai default dari selected attribute atau dari current filters
            const selectedOption = tahunFilter.querySelector('option[selected]');
            if (selectedOption) {
                tahunFilter.value = selectedOption.value;
                this.currentFilters.tahun = selectedOption.value;
                console.log('✅ Set tahun from selected option:', selectedOption.value);
            } else if (this.currentFilters.tahun) {
                tahunFilter.value = this.currentFilters.tahun;
                console.log('✅ Set tahun from current filters:', this.currentFilters.tahun);
            }
            
            tahunFilter.addEventListener('change', (e) => {
                this.currentFilters.tahun = e.target.value;
                console.log('🎯 Tahun filter changed to:', e.target.value);
                this.performSearchWithFilters();
            });
            
            // Trigger initial search jika ada tahun yang dipilih
            if (this.currentFilters.tahun) {
                setTimeout(() => {
                    this.performSearchWithFilters();
                }, 500);
            }
        } else {
            console.error('❌ Tahun filter element not found!');
        }
    }

    performSearchWithFilters() {
        this.performSearch(
            this.currentFilters.search,
            this.currentFilters.tahun,
            this.currentFilters.startDate,
            this.currentFilters.endDate
        );
    }

    // ==================== FILTER TANGGAL ====================

    initializeDateFilters() {
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const resetFilterBtn = document.getElementById('resetFilter');
        const clearFilterBtn = document.getElementById('clearFilter');
        
        // Event listeners for date inputs - AUTO SEARCH
        if (startDateInput) {
            startDateInput.addEventListener('change', (e) => {
                this.currentFilters.startDate = e.target.value;
                this.validateDateRange();
                // Auto search setelah perubahan tanggal
                this.debouncedSearch();
            });
        }
        
        if (endDateInput) {
            endDateInput.addEventListener('change', (e) => {
                this.currentFilters.endDate = e.target.value;
                this.validateDateRange();
                // Auto search setelah perubahan tanggal
                this.debouncedSearch();
            });
        }
        
        // Reset filter button (hanya tanggal)
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', () => {
                this.resetDateFilters();
            });
        }
        
        // Clear all filters button
        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', () => {
                this.clearAllFilters();
            });
        }
        
        // Initial filter update
        this.updateFilterInfo();
    }

    // Tambahkan debounce function untuk search
    debouncedSearch() {
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = setTimeout(() => {
            this.performSearchWithFilters();
        }, 300);
    }

    validateDateRange() {
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            
            if (start > end) {
                this.showError('Tanggal Tidak Valid', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                // Auto correct dan search
                endDate.value = startDate.value;
                this.currentFilters.endDate = endDate.value;
                this.performSearchWithFilters();
            }
        }
    }

    resetDateFilters() {
        // Reset dates ke KOSONG
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        
        if (startDateInput) {
            startDateInput.value = '';
            this.currentFilters.startDate = '';
        }
        
        if (endDateInput) {
            endDateInput.value = '';
            this.currentFilters.endDate = '';
        }
        
        // Auto search setelah reset
        this.performSearchWithFilters();
        this.showToast('Filter tanggal direset', 'info');
    }

    clearAllFilters() {
        // Reset search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            this.currentFilters.search = '';
        }
        
        // Reset tahun
        const tahunFilter = document.getElementById('tahunFilter');
        if (tahunFilter) {
            tahunFilter.value = '';
            this.currentFilters.tahun = '';
        }
        
        // Reset dates ke KOSONG (bukan default 30 hari)
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        
        if (startDateInput) {
            startDateInput.value = '';
            this.currentFilters.startDate = '';
        }
        
        if (endDateInput) {
            endDateInput.value = '';
            this.currentFilters.endDate = '';
        }
        
        // Auto search dengan filter kosong (tampilkan semua data)
        this.performSearchWithFilters();
        this.showToast('Semua filter telah dihapus, menampilkan semua data', 'success');
    }

    initializeModalHandlers() {
        const previewModal = document.getElementById('previewModal');
        const fullscreenToggle = document.getElementById('fullscreenToggle');
        const refreshPdf = document.getElementById('refreshPdf');
        const downloadPdf = document.getElementById('downloadPdf');
        const fallbackDownload = document.getElementById('fallbackDownload');
        
        let currentKwitansiId = null;
        let isFullscreen = false;
        
        if (previewModal) {
            // When modal is about to be shown
            previewModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                currentKwitansiId = button.getAttribute('data-id');
                const previewUrl = `${this.getBaseUrl()}/kwitansi/${currentKwitansiId}/preview-pdf`;
                const downloadUrl = `${this.getBaseUrl()}/kwitansi/${currentKwitansiId}/pdf`;
                
                console.log('Opening preview modal for ID:', currentKwitansiId);
                console.log('Preview URL:', previewUrl);
                console.log('Download URL:', downloadUrl);
                
                // Reset fullscreen state
                this.exitFullscreen();
                isFullscreen = false;
                
                // Update modal title
                document.getElementById('previewModalLabel').innerHTML = 
                    `<i class="bi bi-file-earmark-pdf me-2"></i>Preview Kwitansi #${currentKwitansiId}`;
                
                // Set download URLs
                if (downloadPdf) downloadPdf.href = downloadUrl;
                if (fallbackDownload) fallbackDownload.href = downloadUrl;
                
                // Update info text
                document.getElementById('pdfInfo').textContent = 'Memuat dokumen PDF...';
                
                // Reset dan set iframe source
                this.setIframeSource(previewUrl, currentKwitansiId);

                // Force resize setelah modal terbuka
                setTimeout(() => {
                    this.adjustIframeSize();
                }, 500);
            });

             // Handle modal shown event untuk adjust size
            previewModal.addEventListener('shown.bs.modal', () => {
                this.adjustIframeSize();
                
                // Tambahkan event listener untuk window resize
                window.addEventListener('resize', this.adjustIframeSize.bind(this));
            });

            // Reset modal when closed
            previewModal.addEventListener('hidden.bs.modal', () => {
                console.log('Modal closed, clearing iframe...');
                
                // Exit fullscreen jika aktif
                this.exitFullscreen();
                isFullscreen = false;
                
                // Clear iframe source saat modal ditutup
                const pdfIframe = document.getElementById('pdfIframe');
                if (pdfIframe) {
                    pdfIframe.src = 'about:blank';
                }
                
                // Reset UI states
                this.resetModalStates();

                // Remove resize event listener
                window.removeEventListener('resize', this.adjustIframeSize.bind(this));
                
                // Reset current ID
                currentKwitansiId = null;
            });
        }

        // Fullscreen toggle
        if (fullscreenToggle) {
            fullscreenToggle.addEventListener('click', () => {
                if (!isFullscreen) {
                    this.enterFullscreen();
                    isFullscreen = true;
                } else {
                    this.exitFullscreen();
                    isFullscreen = false;
                }
            });
        }
        
        // Refresh PDF
        if (refreshPdf) {
            refreshPdf.addEventListener('click', () => {
                if (currentKwitansiId) {
                    const previewUrl = `${this.getBaseUrl()}/kwitansi/${currentKwitansiId}/preview-pdf`;
                    this.setIframeSource(previewUrl, currentKwitansiId);
                    this.showToast('Memuat ulang PDF...', 'info');
                }
            });
        }
        
        // Handle escape key untuk keluar fullscreen
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isFullscreen) {
                this.exitFullscreen();
                isFullscreen = false;
            }
        });
    }

    enterFullscreen() {
        const modal = document.getElementById('previewModal');
        const modalDialog = modal.querySelector('.modal-dialog');
        const modalContent = modal.querySelector('.modal-content');
        const fullscreenToggle = document.getElementById('fullscreenToggle');
        const modalHeader = modal.querySelector('.modal-header');
        
        if (!modalDialog) return;
        
        // Add fullscreen classes
        modalDialog.classList.add('modal-fullscreen');
        modalContent.classList.add('modal-fullscreen');
        document.body.classList.add('modal-fullscreen-active');
        
        // Update toggle button
        fullscreenToggle.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
        fullscreenToggle.setAttribute('title', 'Keluar Fullscreen');
        fullscreenToggle.classList.remove('btn-light');
        fullscreenToggle.classList.add('btn-warning');
        
        // Darker header in fullscreen for better contrast
        modalHeader.classList.remove('bg-primary');
        modalHeader.classList.add('bg-dark');
        
        // Hide other page elements
        this.hidePageElements();
        
        this.showToast('Masuk ke mode fullscreen - tekan ESC untuk keluar', 'success');
        
        // Trigger resize untuk iframe
        this.triggerIframeResize();
    }

    exitFullscreen() {
        const modal = document.getElementById('previewModal');
        const modalDialog = modal.querySelector('.modal-dialog');
        const modalContent = modal.querySelector('.modal-content');
        const fullscreenToggle = document.getElementById('fullscreenToggle');
        const modalHeader = modal.querySelector('.modal-header');
        
        if (!modalDialog) return;
        
        // Remove fullscreen classes
        modalDialog.classList.remove('modal-fullscreen');
        modalContent.classList.remove('modal-fullscreen');
        document.body.classList.remove('modal-fullscreen-active');
        
        // Update toggle button
        fullscreenToggle.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
        fullscreenToggle.setAttribute('title', 'Fullscreen');
        fullscreenToggle.classList.remove('btn-warning');
        fullscreenToggle.classList.add('btn-light');
        
        // Restore header color
        modalHeader.classList.remove('bg-dark');
        modalHeader.classList.add('bg-primary');
        
        // Show other page elements
        this.showPageElements();
        
        this.showToast('Keluar dari mode fullscreen', 'info');
    }

    hidePageElements() {
        // Sembunyikan elemen halaman lainnya
        const elementsToHide = [
            '.navbar',
            '.sidebar',
            '.container-main',
            '.card-shadow'
        ];
        
        elementsToHide.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.display = 'none';
            });
        });
    }

    showPageElements() {
        // Tampilkan kembali elemen halaman
        const elementsToShow = [
            '.navbar',
            '.sidebar',
            '.container-main',
            '.card-shadow'
        ];
        
        elementsToShow.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.display = '';
            });
        });
    }

    triggerIframeResize() {
        const pdfIframe = document.getElementById('pdfIframe');
        if (pdfIframe && pdfIframe.style.display !== 'none') {
            // Force iframe resize
            setTimeout(() => {
                const iframeDoc = pdfIframe.contentDocument || pdfIframe.contentWindow.document;
                if (iframeDoc) {
                    pdfIframe.style.width = '100%';
                    pdfIframe.style.height = '100%';
                }
            }, 100);
        }
    }

    // Tambahkan method untuk adjust iframe size
    adjustIframeSize() {
        const pdfIframe = document.getElementById('pdfIframe');
        const modalBody = document.querySelector('.modal-body');
        const modalPdfContainer = document.querySelector('.modal-pdf-container');
        
        if (pdfIframe && modalBody && modalPdfContainer) {
            // Hitung height yang optimal
            const headerHeight = document.querySelector('.modal-header').offsetHeight || 60;
            const footerHeight = document.querySelector('.modal-footer').offsetHeight || 60;
            const bodyPadding = 16; // 1rem padding
            const availableHeight = window.innerHeight - headerHeight - footerHeight - bodyPadding - 20;
            
            // Set height container dan iframe
            const optimalHeight = Math.max(500, availableHeight);
            modalPdfContainer.style.height = optimalHeight + 'px';
            pdfIframe.style.height = '100%';
            
            console.log('Adjusted iframe height to:', optimalHeight);
        }
    }

    setIframeSource(previewUrl, kwitansiId) {
        const pdfIframe = document.getElementById('pdfIframe');
        const pdfLoading = document.getElementById('pdfLoading');
        const pdfFallback = document.getElementById('pdfFallback');
        
        // Reset states
        this.resetModalStates();
        
        if (pdfLoading) {
            pdfLoading.style.display = 'flex';
            pdfLoading.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-3 text-muted">Memuat dokumen PDF...</p>
                    <small class="text-muted">ID: ${kwitansiId}</small>
                </div>
            `;
        }
        
        if (pdfIframe) {
            pdfIframe.style.display = 'none';
            pdfIframe.src = 'about:blank'; // Clear previous content

            // Set optimal size sebelum load
            this.adjustIframeSize();
            
            console.log('Setting iframe source to:', previewUrl);
            
            // Set new source after a brief delay
            setTimeout(() => {
                pdfIframe.src = previewUrl;
                document.getElementById('pdfInfo').textContent = 'PDF sedang dimuat...';
            }, 300);
        }
        
        // Fallback timeout
        setTimeout(() => {
            const pdfIframe = document.getElementById('pdfIframe');
            if (pdfIframe && pdfIframe.style.display === 'none') {
                if (pdfLoading) pdfLoading.style.display = 'none';
                if (pdfFallback) {
                    pdfFallback.style.display = 'flex';
                }
                document.getElementById('pdfInfo').textContent = 'Gagal memuat PDF';
                console.error('PDF loading timeout for URL:', previewUrl);
            }
        }, 10000);
    }

    resetModalStates() {
        const pdfLoading = document.getElementById('pdfLoading');
        const pdfFallback = document.getElementById('pdfFallback');
        const pdfIframe = document.getElementById('pdfIframe');
        
        if (pdfLoading) {
            pdfLoading.style.display = 'none';
        }
        if (pdfFallback) {
            pdfFallback.style.display = 'none';
        }
        if (pdfIframe) {
            pdfIframe.style.display = 'none';
        }
    }

    showToast(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.custom-toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `custom-toast position-fixed top-0 end-0 p-3`;
        toast.style.zIndex = '99999';
        toast.innerHTML = `
            <div class="toast align-items-center text-white bg-${type} border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }

    initializePaginationListeners() {
        // Event delegation untuk pagination
        document.addEventListener('click', (e) => {
            if (e.target.closest('.pagination .page-link') && !e.target.closest('.disabled')) {
                const link = e.target.closest('.page-link');
                if (link.getAttribute('href')) {
                    e.preventDefault();
                    this.handlePaginationClick(e);
                }
            }
        });
    }

    initializeActionButtons() {
        // Generate All
        const generateBtn = document.getElementById('generate-all-btn');
        if (generateBtn) {
            generateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.generateAllKwitansi();
            });
        }

        // Download All
        const downloadAllBtn = document.getElementById('download-all-btn');
        if (downloadAllBtn) {
            downloadAllBtn.addEventListener('click', (e) => {
                this.handleDownloadAll(e);
            });
        }

        // Delete All
        const deleteAllBtn = document.getElementById('delete-all-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.deleteAllKwitansi();
            });
        }

        // Generate from empty state
        const generateEmptyBtn = document.getElementById('generate-empty-btn');
        if (generateEmptyBtn) {
            generateEmptyBtn.addEventListener('click', () => {
                this.generateAllKwitansi();
            });
        }

        // Single Delete (delegated)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-kwitansi')) {
                e.preventDefault();
                const button = e.target.closest('.delete-kwitansi');
                const kwitansiId = button.getAttribute('data-id');
                const uraian = button.getAttribute('data-uraian');
                this.deleteSingleKwitansi(kwitansiId, uraian);
            }
            
            // Clear search filters dari empty state
            if (e.target.id === 'clear-search-filters') {
                e.preventDefault();
                this.clearAllFilters();
            }
        });
    }

    initializeTooltips() {
        // Initialize Bootstrap tooltips
        if (typeof $ !== 'undefined') {
            $('[data-bs-toggle="tooltip"]').tooltip();
        } else {
            // Fallback for tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    // ==================== SEARCH & FILTER ====================

    // Perbaiki method performSearch untuk debug response
    async performSearch(searchTerm = '', tahun = '', startDate = '', endDate = '') {
        try {
            console.log('🔍 Performing search with filters:', {
                search: searchTerm,
                tahun: tahun,
                startDate: startDate,
                endDate: endDate
            });
            
            this.showLoadingState('search');
            this.showFilterLoading();
            
            let url = `${this.getBaseUrl()}/kwitansi/search?search=${encodeURIComponent(searchTerm)}`;
            
            // Add tahun filter
            if (tahun) {
                url += `&tahun=${encodeURIComponent(tahun)}`;
            }
            
            // Add date filters
            if (startDate) {
                url += `&start_date=${encodeURIComponent(startDate)}`;
            }
            
            if (endDate) {
                url += `&end_date=${encodeURIComponent(endDate)}`;
            }

            // Tambahkan cache busting
            url += `&_=${Date.now()}`;

            console.log('📡 Search URL:', url);

            const response = await fetch(url);
            console.log('📦 Response status:', response.status);
            
            const responseText = await response.text();
            console.log('📄 Raw response:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('✅ JSON parsed successfully:', data);
            } catch (parseError) {
                console.error('❌ JSON parse error:', parseError);
                throw new Error('Invalid JSON response');
            }

            if (data.success) {
                console.log('✅ Search successful, updating results...');
                this.updateSearchResults(data.data, data.pagination);
                this.updateCounters(data.total);
                this.updateTableCount(data.total);
                this.updateFilterInfo(data.filter_info);
                
                // Show result count toast untuk filter non-search
                if (!searchTerm && (startDate || endDate || tahun)) {
                    this.showResultCount(data.total);
                }
            } else {
                console.error('❌ Search failed:', data.message);
                this.showError('Search failed', data.message);
            }
        } catch (error) {
            console.error('💥 Search error:', error);
            this.showError('Search Error', 'Terjadi kesalahan saat mencari data: ' + error.message);
        } finally {
            this.hideLoadingState('search');
            this.hideFilterLoading();
        }
    }

    showResultCount(count) {
        let message = `Ditemukan ${count} data`;
        
        // Tambahkan info filter spesifik
        const filters = [];
        
        if (this.currentFilters.startDate && this.currentFilters.endDate) {
            const start = this.formatDateDisplay(this.currentFilters.startDate);
            const end = this.formatDateDisplay(this.currentFilters.endDate);
            filters.push(`periode ${start} - ${end}`);
        }
        
        if (this.currentFilters.tahun) {
            const tahunSelect = document.getElementById('tahunFilter');
            const selectedOption = tahunSelect ? tahunSelect.options[tahunSelect.selectedIndex] : null;
            if (selectedOption) {
                filters.push(`tahun ${selectedOption.text}`);
            }
        }
        
        if (filters.length > 0) {
            message += ` untuk ${filters.join(' dan ')}`;
        }
        
        this.showToast(message, 'info');
    }

    updateFilterInfo(filterInfo = null) {
        const filterInfoElement = document.getElementById('filterInfo');
        const filterTextElement = document.getElementById('filterText');
        
        if (!filterInfoElement || !filterTextElement) return;
        
        const hasActiveFilters = 
            this.currentFilters.search || 
            this.currentFilters.tahun || 
            this.currentFilters.startDate || 
            this.currentFilters.endDate;
        
        if (hasActiveFilters) {
            let filterText = '';
            const filters = [];
            
            // Search filter
            if (this.currentFilters.search) {
                filters.push(`Pencarian: "${this.currentFilters.search}"`);
            }
            
            // Tahun filter
            if (this.currentFilters.tahun) {
                const tahunSelect = document.getElementById('tahunFilter');
                const selectedOption = tahunSelect ? tahunSelect.options[tahunSelect.selectedIndex] : null;
                const tahunText = selectedOption ? selectedOption.text : this.currentFilters.tahun;
                filters.push(`Tahun: ${tahunText}`);
            }
            
            // Date filters
            if (this.currentFilters.startDate && this.currentFilters.endDate) {
                const start = this.formatDateDisplay(this.currentFilters.startDate);
                const end = this.formatDateDisplay(this.currentFilters.endDate);
                filters.push(`Tanggal: ${start} - ${end}`);
            } else if (this.currentFilters.startDate) {
                filters.push(`Dari: ${this.formatDateDisplay(this.currentFilters.startDate)}`);
            } else if (this.currentFilters.endDate) {
                filters.push(`Sampai: ${this.formatDateDisplay(this.currentFilters.endDate)}`);
            }
            
            filterText = filters.join(' | ');
            filterTextElement.textContent = filterText;
            filterInfoElement.style.display = 'block';
        } else {
            filterInfoElement.style.display = 'none';
        }
    }

    formatDateDisplay(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    // ==================== SEARCH & FILTER ====================

    // Method untuk update search results - DIPERBAIKI DENGAN DEBUG
    updateSearchResults(data, pagination) {
        console.log('🔄 Updating search results...');
        console.log('📊 Data received:', data);
        console.log('📄 Pagination:', pagination);
        
        const tbody = document.getElementById('kwitansi-tbody');
        if (!tbody) {
            console.error('❌ tbody element not found!');
            return;
        }

        console.log('✅ tbody element found');

        if (data.length === 0) {
            console.log('📭 No data found, showing empty state');
            tbody.innerHTML = this.getEmptySearchHTML();
            this.hidePagination();
            return;
        }

        console.log(`🎯 Rendering ${data.length} items`);

        let html = '';
        data.forEach((item, index) => {
            const number = (pagination.current_page - 1) * pagination.per_page + index + 1;
            const rowHtml = this.getTableRowHTML(item, number, pagination);
            console.log(`📝 Row ${index}:`, rowHtml);
            html += rowHtml;
        });

        console.log('📋 Final HTML:', html);
        
        tbody.innerHTML = html;
        
        // PERBAIKAN: Update pagination dengan fallback
        this.updatePagination(pagination);
        this.reinitializeTooltips();
        this.reattachDeleteListeners();
        
        // Update filter info setelah hasil search
        this.updateFilterInfo();
        
        console.log('✅ Search results updated successfully');
    }

    hidePagination() {
        const containers = [
            document.getElementById('pagination-container'),
            document.querySelector('.pagination'),
            document.querySelector('.pagination-simple')
        ];
        
        containers.forEach(container => {
            if (container) {
                container.innerHTML = '';
                container.style.display = 'none';
            }
        });
    }

    // Method untuk get table row HTML - DIPERBAIKI
    getTableRowHTML(item, number, pagination) {
        console.log('📝 Creating row HTML for item:', item);
        
        // PERBAIKAN: Pastikan semua property ada
        const kodeRekening = item.kode_rekening || '-';
        const uraian = item.uraian || 'Tidak ada uraian';
        const tanggal = item.tanggal || '-';
        const jumlah = item.jumlah || 'Rp 0';
        const itemId = item.id || 'unknown';
        
        const rowHtml = `
            <tr id="kwitansi-row-${itemId}" class="animate__animated animate__fadeIn">
                <td class="fw-medium text-dark">${number}</td>
                <td>
                    <span class="badge badge-code">${kodeRekening}</span>
                </td>
                <td class="text-dark">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text me-2 text-muted"></i>
                        <span class="text-truncate" style="max-width: 300px;">${uraian}</span>
                    </div>
                </td>
                <td class="text-muted">
                    <i class="bi bi-calendar me-2"></i>${tanggal}
                </td>
                <td class="fw-bold text-success">
                    <i class="bi bi-currency-dollar me-2"></i>${jumlah}
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <!-- Preview Button - Modal Trigger -->
                        <button class="btn btn-action btn-outline-primary preview-kwitansi" 
                                title="Lihat Preview" 
                                data-id="${itemId}"
                                data-bs-toggle="modal" 
                                data-bs-target="#previewModal">
                            <i class="bi bi-eye"></i>
                        </button>
                        <!-- Download PDF -->
                        <a href="${this.getBaseUrl()}/kwitansi/${itemId}/pdf" 
                            class="btn btn-action btn-outline-success" 
                            title="Download PDF" data-bs-toggle="tooltip" target="_blank">
                            <i class="bi bi-download"></i>
                        </a>
                        <!-- Hapus -->
                        <button class="btn btn-action btn-outline-danger delete-kwitansi" 
                            data-id="${itemId}"
                            data-uraian="${uraian}"
                            title="Hapus Kwitansi" data-bs-toggle="tooltip">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        console.log('✅ Row HTML created for item ID:', itemId);
        return rowHtml;
    }

    getEmptySearchHTML() {
        const hasActiveFilters = 
            this.currentFilters.search || 
            this.currentFilters.tahun || 
            this.currentFilters.startDate || 
            this.currentFilters.endDate;
        
        if (hasActiveFilters) {
            return `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state" style="padding: 2rem; background: transparent; border: none;">
                            <i class="bi bi-search empty-state-icon" style="font-size: 3rem;"></i>
                            <h5 class="text-dark mb-2">Tidak ada data yang ditemukan</h5>
                            <p class="text-muted">Coba ubah kata kunci pencarian atau filter yang digunakan</p>
                            <button class="btn btn-primary mt-3" id="clear-search-filters">
                                <i class="bi bi-x-circle me-2"></i>Hapus Semua Filter
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            return `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state" style="padding: 2rem; background: transparent; border: none;">
                            <i class="bi bi-file-earmark-x empty-state-icon" style="font-size: 3rem;"></i>
                            <h4 class="text-dark mb-3">Belum ada data kwitansi</h4>
                            <p class="text-muted mb-4">Mulai dengan membuat kwitansi baru atau generate otomatis dari data yang tersedia.</p>
                            <button class="btn btn-primary btn-modern" id="generate-empty-btn">
                                <i class="bi bi-magic me-2"></i>Generate Kwitansi Otomatis
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    // ==================== PAGINATION HANDLING ====================

    async handlePaginationClick(e) {
        console.log('🎯 Handling pagination click');
        e.preventDefault();
        
        const link = e.target.closest('.page-link');
        if (!link) {
            console.error('❌ No page-link found');
            return;
        }
        
        const pageUrl = link.getAttribute('href');
        console.log('📄 Page URL:', pageUrl);
        
        if (pageUrl) {
            try {
                // Extract page number from URL
                const url = new URL(pageUrl, window.location.origin);
                const page = url.searchParams.get('page') || 1;
                const searchTerm = document.getElementById('searchInput')?.value || '';
                const selectedTahun = document.getElementById('tahunFilter')?.value || '';
                
                console.log('🔍 Loading page:', { page, searchTerm, selectedTahun });
                
                await this.loadPage(page, searchTerm, selectedTahun);
            } catch (error) {
                console.error('💥 Error in handlePaginationClick:', error);
            }
        }
    }

    // ==================== PAGINATION HANDLING ====================

    updatePagination(pagination) {
        console.log('🔄 Updating pagination with data:', pagination);
        
        // PERBAIKAN: Cari container dengan berbagai selector
        let paginationContainer = document.getElementById('pagination-container');
        
        if (!paginationContainer) {
            console.log('🔍 Pagination container not found by ID, searching by class...');
            
            // Coba berbagai selector
            const selectors = [
                '.pagination',
                '.pagination-simple',
                '[aria-label="Page navigation"] ul',
                'nav ul.pagination'
            ];
            
            for (const selector of selectors) {
                paginationContainer = document.querySelector(selector);
                if (paginationContainer) {
                    console.log(`✅ Found pagination container with selector: ${selector}`);
                    break;
                }
            }
        }
        
        if (!paginationContainer) {
            console.error('❌ No pagination container found anywhere! Creating one...');
            this.createPaginationContainer();
            paginationContainer = document.getElementById('pagination-container');
        }
        
        if (paginationContainer) {
            this.renderPagination(paginationContainer, pagination);
        }
    }

    createPaginationContainer() {
        console.log('🏗️ Creating pagination container...');
        
        // Cari tempat yang tepat untuk menambahkan pagination
        const table = document.querySelector('.table-responsive');
        const cardBody = document.querySelector('.card-body');
        
        let insertionPoint = cardBody || table || document.body;
        
        // Buat container pagination
        const paginationHtml = `
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top border-light">
                <div class="text-muted small mb-3 mb-md-0" id="pagination-info">
                    Menampilkan data
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-simple mb-0" id="pagination-container"></ul>
                </nav>
            </div>
        `;
        
        if (insertionPoint === cardBody) {
            insertionPoint.insertAdjacentHTML('beforeend', paginationHtml);
        } else if (table) {
            table.parentNode.insertAdjacentHTML('afterend', paginationHtml);
        } else {
            document.body.insertAdjacentHTML('beforeend', paginationHtml);
        }
        
        console.log('✅ Pagination container created');
    }

    renderPagination(container, pagination) {
        console.log('🎨 Rendering pagination in container:', container);
        
        // PERBAIKAN: Jika hanya ada 1 halaman, sembunyikan pagination
        if (pagination.last_page <= 1) {
            console.log('ℹ️ Only one page, hiding pagination');
            container.innerHTML = '';
            container.style.display = 'none';
            
            // Update info
            const infoElement = document.getElementById('pagination-info');
            if (infoElement) {
                infoElement.textContent = `Menampilkan semua ${pagination.total} data`;
            }
            return;
        }
        
        container.style.display = 'flex';

        let paginationHtml = '';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page="${pagination.current_page - 1}" rel="prev">&laquo;</a>
                </li>
            `;
        } else {
            paginationHtml += `
                <li class="page-item disabled">
                    <span class="page-link">&laquo;</span>
                </li>
            `;
        }

        // Page numbers - Tampilkan maksimal 5 halaman
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        // Tampilkan ellipsis di awal jika perlu
        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page="1">1</a>
                </li>
            `;
            if (startPage > 2) {
                paginationHtml += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `
                    <li class="page-item active">
                        <span class="page-link">${i}</span>
                    </li>
                `;
            } else {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a>
                    </li>
                `;
            }
        }
        
        // Tampilkan ellipsis di akhir jika perlu
        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                paginationHtml += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page="${pagination.last_page}">${pagination.last_page}</a>
                </li>
            `;
        }

        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page="${pagination.current_page + 1}" rel="next">&raquo;</a>
                </li>
            `;
        } else {
            paginationHtml += `
                <li class="page-item disabled">
                    <span class="page-link">&raquo;</span>
                </li>
            `;
        }

        container.innerHTML = paginationHtml;
        console.log('✅ Pagination HTML updated successfully');
        
        // Update pagination info
        this.updatePaginationInfo(pagination);
        
        // Re-attach event listeners
        this.attachPaginationEventListeners();
    }

    updatePaginationInfo(pagination) {
        const infoElement = document.getElementById('pagination-info');
        if (infoElement) {
            const startItem = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const endItem = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            
            infoElement.textContent = `Menampilkan ${startItem} sampai ${endItem} dari ${pagination.total} data`;
        }
    }

    attachPaginationEventListeners() {
        console.log('🔗 Attaching pagination event listeners');
        const paginationLinks = document.querySelectorAll('.pagination .page-link[data-page]');
        
        console.log(`Found ${paginationLinks.length} pagination links`);
        
        paginationLinks.forEach((link) => {
            if (!link.parentElement.classList.contains('disabled') && 
                !link.parentElement.classList.contains('active')) {
                
                const page = link.getAttribute('data-page');
                console.log(`Attaching listener to page: ${page}`);
                
                // Hapus event listener lama dan tambahkan yang baru
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('🖱️ Pagination link clicked, loading page:', page);
                    const searchTerm = this.currentFilters.search;
                    const selectedTahun = this.currentFilters.tahun;
                    const startDate = this.currentFilters.startDate;
                    const endDate = this.currentFilters.endDate;
                    
                    this.loadPage(page, searchTerm, selectedTahun, startDate, endDate);
                });
            }
        });
    }

    // ==================== PAGINATION HANDLING ====================
    async loadPage(page, searchTerm = '', tahun = '', startDate = '', endDate = '') {
        try {
            console.log('📄 Loading page:', page);
            this.showLoadingState('search');
            
            let url = `${this.getBaseUrl()}/kwitansi/search?page=${page}`;
            if (searchTerm) {
                url += `&search=${encodeURIComponent(searchTerm)}`;
            }
            if (tahun) {
                url += `&tahun=${encodeURIComponent(tahun)}`;
            }
            if (startDate) {
                url += `&start_date=${encodeURIComponent(startDate)}`;
            }
            if (endDate) {
                url += `&end_date=${encodeURIComponent(endDate)}`;
            }

            console.log('📡 Loading URL:', url);

            const response = await fetch(url);
            const data = await response.json();

            console.log('📦 Page response:', data);

            if (data.success) {
                this.updateSearchResults(data.data, data.pagination);
                this.updateCounters(data.total);
                this.updateTableCount(data.total);
                
                // Scroll ke atas tabel
                const table = document.querySelector('.table-responsive');
                if (table) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                
                console.log('✅ Page loaded successfully');
            } else {
                console.error('❌ Page load failed:', data.message);
                this.showError('Load Failed', data.message);
            }
        } catch (error) {
            console.error('💥 Page load error:', error);
            this.showError('Pagination Error', 'Terjadi kesalahan saat memuat halaman');
        } finally {
            this.hideLoadingState('search');
        }
    }

    // Method untuk mendapatkan options tahun anggaran - DIPERBAIKI
    async getTahunAnggaranOptions() {
        try {
            console.log('📡 Fetching tahun anggaran options...');
            
            const response = await fetch(`${this.getBaseUrl()}/kwitansi/tahun-anggaran`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📦 Tahun anggaran response:', data);
            
            if (data.success) {
                console.log('✅ Tahun options fetched:', data.data);
                
                // PERBAIKAN: Gunakan property yang benar dari response
                const options = data.data.map(item => ({
                    id: item.id,
                    tahun_anggaran: item.tahun || item.tahun_anggaran // Handle kedua kemungkinan
                }));
                
                console.log('🔄 Formatted options:', options);
                return options;
            } else {
                console.error('❌ Error in tahun anggaran response:', data.message);
                this.showError('Error', 'Gagal mengambil data tahun anggaran: ' + data.message);
                return [];
            }
        } catch (error) {
            console.error('💥 Error fetching tahun options:', error);
            this.showError('Error', 'Gagal mengambil data tahun anggaran: ' + error.message);
            return [];
        }
    }

    // ==================== GENERATE FUNCTIONALITY DENGAN PILIHAN TAHUN ====================

    async generateAllKwitansi() {
        console.log('🎯 Generate All Kwitansi clicked');
        
        try {
            // Ambil data tahun anggaran tersedia
            const tahunOptions = await this.getTahunAnggaranOptions();
            console.log('📋 Tahun options:', tahunOptions);
            
            if (!tahunOptions || tahunOptions.length === 0) {
                this.showError('Tidak Ada Data', 'Tidak ada tahun anggaran yang tersedia');
                return;
            }

            // Buat HTML options untuk pilihan tahun - DIPERBAIKI
            const tahunOptionsHtml = tahunOptions.map(tahun => {
                const tahunText = tahun.tahun_anggaran || tahun.tahun || 'Tidak diketahui';
                return `<option value="${tahun.id}">${tahunText}</option>`;
            }).join('');

            console.log('📝 Tahun options HTML created');

            const result = await Swal.fire({
                title: 'Generate Kwitansi Otomatis',
                html: `
                    <div class="text-start">
                        <p class="mb-3">Pilih tahun anggaran untuk generate kwitansi:</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tahun Anggaran</label>
                            <select class="form-select form-select-sm" id="generateTahunSelect">
                                <option value="">-- Pilih Tahun --</option>
                                ${tahunOptionsHtml}
                            </select>
                        </div>
                        <div class="alert alert-info mt-3 small">
                            <i class="fas fa-info-circle me-2"></i>
                            Proses akan membuat kwitansi untuk semua transaksi pada tahun yang dipilih yang belum memiliki kwitansi.
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-play me-1"></i> Mulai Generate',
                cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
                reverseButtons: true,
                allowOutsideClick: false,
                didOpen: () => {
                    console.log('✅ Generate modal opened');
                    // Focus ke select box
                    const select = document.getElementById('generateTahunSelect');
                    if (select) {
                        select.focus();
                        console.log('🎯 Select element focused');
                        
                        // Debug: log options yang tersedia
                        console.log('🔍 Available options in select:');
                        Array.from(select.options).forEach((option, index) => {
                            console.log(`  ${index}: ${option.value} - ${option.text}`);
                        });
                    }
                },
                preConfirm: () => {
                    const selectedTahun = document.getElementById('generateTahunSelect').value;
                    const selectedText = document.getElementById('generateTahunSelect').options[document.getElementById('generateTahunSelect').selectedIndex].text;
                    
                    console.log('🔍 Pre-confirm tahun:', {
                        value: selectedTahun,
                        text: selectedText
                    });
                    
                    if (!selectedTahun) {
                        Swal.showValidationMessage('Harap pilih tahun anggaran');
                        return false;
                    }
                    return selectedTahun;
                }
            });

            console.log('🔮 SweetAlert result:', result);

            if (result.isConfirmed && result.value) {
                console.log('🚀 Starting generation for tahun:', result.value);
                this.startSimpleGeneration(result.value);
            } else {
                console.log('❌ Generation cancelled');
            }

        } catch (error) {
            console.error('💥 Error in generateAllKwitansi:', error);
            this.showError('Error', 'Terjadi kesalahan saat mempersiapkan generate');
        }
    }

    // Modifikasi startSimpleGeneration untuk menerima parameter tahun - DIPERBAIKI
    async startSimpleGeneration(selectedTahun = '') {
        console.log('🚀 Starting simple generation for tahun:', selectedTahun);
        
        if (!selectedTahun) {
            console.error('❌ No tahun selected for generation');
            this.showError('Error', 'Tahun tidak dipilih');
            return;
        }

        // Show simple loading
        Swal.fire({
            title: 'Sedang Memproses...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <p class="mb-2"><strong>Generate Kwitansi Sedang Berjalan</strong></p>
                    <p class="small text-muted mb-0">
                        Sistem sedang membuat kwitansi untuk data tahun ${selectedTahun}.<br>
                        Proses ini mungkin membutuhkan waktu beberapa menit.
                    </p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        });

        try {
            const formData = new FormData();
            formData.append('_token', this.getCsrfToken());
            formData.append('tahun', selectedTahun); // Tambahkan parameter tahun

            console.log('📤 Sending generate request with tahun:', selectedTahun);

            const response = await fetch(`${this.getBaseUrl()}/kwitansi/generate-batch`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 Generate response:', data);

            Swal.close();

            if (data.success) {
                console.log('✅ Generation successful');
                this.showSimpleCompletionMessage(data.data);
            } else {
                console.error('❌ Generation failed:', data.message);
                this.showError('Generate Gagal', data.message || 'Terjadi kesalahan saat proses generate');
            }
        } catch (error) {
            Swal.close();
            console.error('💥 Generation error:', error);
            this.showError('Koneksi Error', 'Terjadi kesalahan koneksi saat proses generate: ' + error.message);
        }
    }

    // Method untuk refresh data setelah generate - DIPERBAIKI
    async refreshDataAfterGenerate() {
        try {
            console.log('🔄 Refreshing data after generate...');
            console.log('🔍 Current filters before refresh:', this.currentFilters);
            
            this.showLoadingState('search');
            
            // GUNAKAN ROUTE SEARCH DENGAN PARAMETER YANG SAMA
            let url = `${this.getBaseUrl()}/kwitansi/search?`;
            
            const params = new URLSearchParams();
            
            // Gunakan current filters yang aktif
            if (this.currentFilters.search) {
                params.append('search', this.currentFilters.search);
            }
            if (this.currentFilters.tahun) {
                params.append('tahun', this.currentFilters.tahun);
            }
            if (this.currentFilters.startDate) {
                params.append('start_date', this.currentFilters.startDate);
            }
            if (this.currentFilters.endDate) {
                params.append('end_date', this.currentFilters.endDate);
            }
            
            // Tambahkan cache busting parameter
            params.append('_', Date.now());
            
            url += params.toString();
            
            console.log('📡 Refresh URL with filters:', url);

            const response = await fetch(url, {
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📦 Refresh response data:', data);

            if (data.success) {
                console.log('✅ Data updated successfully, total:', data.total);
                this.updateSearchResults(data.data, data.pagination);
                this.updateCounters(data.total);
                this.updateTableCount(data.total);
                this.updateFilterInfo();
                
                this.showToast(`Data berhasil diperbarui. Ditemukan ${data.total} data`, 'success');
                
                // Debug tambahan
                this.debugRefreshResults(data);
            } else {
                this.showError('Refresh Failed', data.message);
            }
        } catch (error) {
            console.error('💥 Refresh error:', error);
            this.showError('Refresh Error', 'Terjadi kesalahan saat memperbarui data: ' + error.message);
            
            // Fallback: reload halaman
            setTimeout(() => {
                this.showToast('Memuat ulang halaman...', 'info');
                location.reload();
            }, 2000);
        } finally {
            this.hideLoadingState('search');
        }
    }

    // Method untuk debug hasil refresh
    debugRefreshResults(data) {
        console.log('🔍 Debug refresh results:');
        console.log('📊 Total data:', data.total);
        console.log('📋 Data items:', data.data);
        console.log('🎯 Current tahun filter:', this.currentFilters.tahun);
        
        // Cek apakah data sesuai dengan filter tahun
        if (this.currentFilters.tahun && data.data.length > 0) {
            const hasMatchingTahun = data.data.some(item => {
                // Asumsikan item memiliki informasi tahun
                return true; // Kita akan cek di controller
            });
            console.log('✅ Data contains items for current tahun filter:', hasMatchingTahun);
        }
    }

    // Method untuk debug - DIPERBAIKI
    async debugGeneratedData() {
        try {
            console.log('🔍 Debugging generated data...');
            
            // Gunakan route search dengan parameter kosong untuk mendapatkan semua data
            const response = await fetch(`${this.getBaseUrl()}/kwitansi/search`);
            const data = await response.json();
            
            console.log('📊 Latest data count:', data.total);
            console.log('📋 Latest data:', data.data);
            
        } catch (error) {
            console.error('💥 Debug error:', error);
        }
    }

    // Perbaiki method showSimpleCompletionMessage
    showSimpleCompletionMessage(resultData) {
        const successCount = resultData.success || 0;
        const failedCount = resultData.failed || 0;
        const totalCount = resultData.total || 0;
        const processedCount = resultData.processed || 0;
        const tahunText = resultData.tahun_text || resultData.tahun || 'yang dipilih';
        
        // DAPATKAN TAHUN YANG DIGENERATE
        const generatedTahun = resultData.tahun;
        
        const resultHtml = `
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                    <h5 class="text-success">Proses Generate Selesai!</h5>
                </div>
                
                <div class="alert alert-success">
                    <p><strong>${resultData.message || 'Proses generate selesai'}</strong></p>
                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <div class="text-success fw-bold fs-4">${successCount}</div>
                            <small>Berhasil</small>
                        </div>
                        <div class="col-4">
                            <div class="text-danger fw-bold fs-4">${failedCount}</div>
                            <small>Gagal</small>
                        </div>
                        <div class="col-4">
                            <div class="text-info fw-bold fs-4">${processedCount}</div>
                            <small>Diproses</small>
                        </div>
                    </div>
                    ${totalCount ? `<div class="row text-center mt-2">
                        <div class="col-12">
                            <div class="text-dark fw-bold fs-5">${totalCount}</div>
                            <small>Total Data Tersedia</small>
                        </div>
                    </div>` : ''}
                </div>
                
                <p class="text-muted small mt-3">
                    Data untuk tahun <strong>${tahunText}</strong> telah digenerate.
                </p>
            </div>
        `;

        Swal.fire({
            title: 'Proses Selesai',
            html: resultHtml,
            icon: 'success',
            confirmButtonText: '<i class="fas fa-check me-1"></i> Tampilkan Data',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Tutup',
            showCancelButton: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('🎯 User wants to show generated data for tahun:', generatedTahun);
                
                // OTOMATIS SET FILTER KE TAHUN YANG BARU DIGENERATE
                if (generatedTahun) {
                    this.autoSetTahunFilter(generatedTahun);
                } else {
                    // Refresh dengan filter yang ada
                    this.refreshDataAfterGenerate();
                }
            }
        });
    }

    // Method untuk otomatis set filter tahun setelah generate
    autoSetTahunFilter(tahunId) {
        console.log('🎯 Auto-setting tahun filter to:', tahunId);
        
        const tahunFilter = document.getElementById('tahunFilter');
        if (tahunFilter) {
            // Set nilai filter
            tahunFilter.value = tahunId;
            this.currentFilters.tahun = tahunId;
            
            console.log('✅ Tahun filter set to:', tahunId);
            
            // Refresh data dengan filter baru
            setTimeout(() => {
                this.refreshDataAfterGenerate();
            }, 500);
        } else {
            // Fallback ke refresh biasa
            this.refreshDataAfterGenerate();
        }
    }

    // ==================== DELETE FUNCTIONALITY ====================

    deleteAllKwitansi() {
        Swal.fire({
            title: 'Hapus Semua Kwitansi?',
            html: `
                <div class="alert alert-warning text-left">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Konfirmasi Penghapusan</strong>
                    <hr>
                    <p>Apakah Anda yakin ingin menghapus <strong>SEMUA DATA KWITANSI</strong>?</p>
                    <p class="mb-0">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data kwitansi secara permanen.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Semua!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.processDeleteAll();
            }
        });
    }

    async processDeleteAll() {
        Swal.fire({
            title: 'Menghapus...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-danger mb-3" style="width: 3rem; height: 3rem;"></div>
                    <p>Sedang menghapus semua data kwitansi...</p>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false
        });

        try {
            const response = await fetch(`${this.getBaseUrl()}/kwitansi/delete/all`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    confirm_text: 'HAPUS-SEMUA-KWITANSI'
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success">Data Berhasil Dihapus</h5>
                            <p>${data.message}</p>
                            <div class="alert alert-success mt-3">
                                <i class="fas fa-check mr-2"></i>
                                Semua data kwitansi telah berhasil dihapus dari sistem.
                            </div>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Refresh Halaman',
                    allowOutsideClick: false
                }).then((result) => {
                    location.reload();
                });
            } else {
                this.showError('Delete Failed', data.message);
            }
        } catch (error) {
            console.error('Delete all error:', error);
            this.showError('Delete Error', 'Terjadi kesalahan saat menghapus semua data kwitansi');
        }
    }

    deleteSingleKwitansi(kwitansiId, uraian) {
        Swal.fire({
            title: 'Hapus Kwitansi?',
            html: `
                <div class="alert alert-warning text-left">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Konfirmasi Penghapusan</strong>
                    <hr>
                    <p>Apakah Anda yakin ingin menghapus kwitansi untuk:</p>
                    <p class="mb-0"><strong>"${uraian}"</strong>?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.processDeleteSingle(kwitansiId, uraian);
            }
        });
    }

    async processDeleteSingle(kwitansiId, uraian) {
        Swal.fire({
            title: 'Menghapus...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-danger mb-3" style="width: 2rem; height: 2rem;"></div>
                    <p>Sedang menghapus kwitansi...</p>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false
        });

        try {
            const response = await fetch(`${this.getBaseUrl()}/kwitansi/${kwitansiId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success">Kwitansi Berhasil Dihapus</h5>
                            <p>${data.message}</p>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke'
                }).then((result) => {
                    this.removeRowFromTable(kwitansiId);
                    this.updateCountersAfterDelete();
                });
            } else {
                this.showError('Delete Failed', data.message);
            }
        } catch (error) {
            console.error('Delete single error:', error);
            this.showError('Delete Error', 'Terjadi kesalahan saat menghapus kwitansi');
        }
    }

    // ==================== DOWNLOAD FUNCTIONALITY ====================

    handleDownloadAll(e) {
        e.preventDefault();
        
        // Tampilkan modal konfirmasi dengan filter
        this.showDownloadConfirmation();
    }

    showDownloadConfirmation() {
        const hasActiveFilters = 
            this.currentFilters.search || 
            this.currentFilters.tahun || 
            this.currentFilters.startDate || 
            this.currentFilters.endDate;
        
        let downloadMessage = '';
        let downloadUrl = `${this.getBaseUrl()}/kwitansi/download-all`;
        
        // Build message berdasarkan filter aktif
        if (hasActiveFilters) {
            downloadMessage = `<div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Download dengan Filter Aktif</strong>
                <hr class="my-2">
                <div class="small">`;
            
            const filters = [];
            
            if (this.currentFilters.search) {
                filters.push(`Pencarian: <strong>"${this.currentFilters.search}"</strong>`);
            }
            
            if (this.currentFilters.tahun) {
                const tahunSelect = document.getElementById('tahunFilter');
                const selectedOption = tahunSelect ? tahunSelect.options[tahunSelect.selectedIndex] : null;
                const tahunText = selectedOption ? selectedOption.text : this.currentFilters.tahun;
                filters.push(`Tahun: <strong>${tahunText}</strong>`);
            }
            
            if (this.currentFilters.startDate && this.currentFilters.endDate) {
                const start = this.formatDateDisplay(this.currentFilters.startDate);
                const end = this.formatDateDisplay(this.currentFilters.endDate);
                filters.push(`Tanggal: <strong>${start} - ${end}</strong>`);
            } else if (this.currentFilters.startDate) {
                filters.push(`Dari: <strong>${this.formatDateDisplay(this.currentFilters.startDate)}</strong>`);
            } else if (this.currentFilters.endDate) {
                filters.push(`Sampai: <strong>${this.formatDateDisplay(this.currentFilters.endDate)}</strong>`);
            }
            
            downloadMessage += filters.join('<br>');
            downloadMessage += `</div></div>`;
            
            // Tambahkan parameter filter ke URL download
            downloadUrl += '?' + new URLSearchParams({
                search: this.currentFilters.search,
                tahun: this.currentFilters.tahun,
                start_date: this.currentFilters.startDate,
                end_date: this.currentFilters.endDate
            }).toString();
            
        } else {
            downloadMessage = `<div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Download Semua Data</strong>
                <hr class="my-2">
                <div class="small">
                    Anda akan mendownload <strong>semua kwitansi</strong> tanpa filter.
                </div>
            </div>`;
        }
        
        // Tampilkan modal konfirmasi
        Swal.fire({
            title: 'Konfirmasi Download',
            html: `
                <div class="text-left">
                    ${downloadMessage}
                    <div class="mt-3 p-3 border rounded bg-light">
                        <h6 class="mb-2"><i class="bi bi-gear me-2"></i>Opsi Download:</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="downloadOption" id="downloadWithFilter" value="with_filter" ${hasActiveFilters ? 'checked' : ''}>
                            <label class="form-check-label small" for="downloadWithFilter">
                                Download dengan filter saat ini
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="downloadOption" id="downloadAll" value="all" ${!hasActiveFilters ? 'checked' : ''}>
                            <label class="form-check-label small" for="downloadAll">
                                Download semua data (abaikan filter)
                            </label>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="changeFilterBtn">
                            <i class="bi bi-funnel me-1"></i>Ubah Filter
                        </button>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-download me-1"></i> Download Sekarang',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const selectedOption = document.querySelector('input[name="downloadOption"]:checked').value;
                
                if (selectedOption === 'all') {
                    // Download semua data tanpa filter
                    return this.processDownloadAll(`${this.getBaseUrl()}/kwitansi/download-all`);
                } else {
                    // Download dengan filter
                    return this.processDownloadAll(downloadUrl);
                }
            },
            didOpen: () => {
                // Handle tombol ubah filter
                const changeFilterBtn = document.getElementById('changeFilterBtn');
                if (changeFilterBtn) {
                    changeFilterBtn.addEventListener('click', () => {
                        Swal.close();
                        // Focus ke search input untuk memudahkan ubah filter
                        const searchInput = document.getElementById('searchInput');
                        if (searchInput) {
                            searchInput.focus();
                        }
                    });
                }
                
                // Handle perubahan opsi download
                const downloadOptions = document.querySelectorAll('input[name="downloadOption"]');
                downloadOptions.forEach(option => {
                    option.addEventListener('change', (e) => {
                        const selectedValue = e.target.value;
                        const confirmButton = Swal.getConfirmButton();
                        
                        if (selectedValue === 'all') {
                            confirmButton.innerHTML = '<i class="bi bi-download me-1"></i> Download Semua Data';
                        } else {
                            confirmButton.innerHTML = '<i class="bi bi-download me-1"></i> Download dengan Filter';
                        }
                    });
                });
            }
        });
    }

    async processDownloadAll(downloadUrl) {
        try {
            // Show loading
            Swal.fire({
                title: 'Mempersiapkan Download...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                        <p class="small mb-0">Menyiapkan file PDF untuk download</p>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    // Start download setelah modal terbuka
                    setTimeout(() => {
                        window.open(downloadUrl, '_blank');
                        
                        // Tutup loading setelah 2 detik
                        setTimeout(() => {
                            Swal.close();
                            
                            // Tampilkan success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Download Dimulai!',
                                html: `
                                    <div class="text-center">
                                        <i class="bi bi-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                        <p class="small">File sedang didownload.<br>Periksa folder download atau tab baru browser Anda.</p>
                                    </div>
                                `,
                                confirmButtonText: '<i class="bi bi-check me-1"></i> Mengerti',
                                timer: 5000,
                                showConfirmButton: true
                            });
                        }, 2000);
                    }, 500);
                }
            });

        } catch (error) {
            console.error('Download all error:', error);
            Swal.close();
            this.showError('Download Error', 'Terjadi kesalahan saat mengunduh: ' + error.message);
        }
    }

    // ==================== UTILITY FUNCTIONS ====================

    showFilterLoading() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.parentElement.classList.add('filter-loading');
        }
    }

    hideFilterLoading() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.parentElement.classList.remove('filter-loading');
        }
    }

    // ==================== UTILITY FUNCTIONS ====================

    async checkAvailableData(silent = false) {
        try {
            if (!silent) this.showLoadingState('available');
            
            const response = await fetch(`${this.getBaseUrl()}/kwitansi/check-available`);
            const data = await response.json();

            if (data.success) {
                this.updateElementText('ready-generate', data.data.available_count || 0);
                return data.data;
            } else {
                console.error('Error checking available data:', data.message);
                this.updateElementText('ready-generate', '0');
                return { available_count: 0 };
            }
        } catch (error) {
            console.error('AJAX error checking available data:', error);
            this.updateElementText('ready-generate', '0');
            return { available_count: 0 };
        } finally {
            if (!silent) this.hideLoadingState('available');
        }
    }

    updateCounters(total) {
        this.updateElementText('total-kwitansi', total);
        this.updateElementText('footer-total', total);
        this.updateButtonStates(total);
    }

    updateTableCount(count) {
        const tableCount = document.getElementById('table-count');
        if (tableCount) {
            tableCount.textContent = count;
            tableCount.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                tableCount.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        }
    }

    updateButtonStates(total) {
        const downloadAllBtn = document.getElementById('download-all-btn');
        const deleteAllBtn = document.getElementById('delete-all-btn');
        
        if (total === 0) {
            if (downloadAllBtn) downloadAllBtn.setAttribute('disabled', 'disabled');
            if (deleteAllBtn) deleteAllBtn.setAttribute('disabled', 'disabled');
        } else {
            if (downloadAllBtn) downloadAllBtn.removeAttribute('disabled');
            if (deleteAllBtn) deleteAllBtn.removeAttribute('disabled');
        }
    }

    removeRowFromTable(kwitansiId) {
        const row = document.getElementById(`kwitansi-row-${kwitansiId}`);
        if (row) {
            row.classList.add('animate__animated', 'animate__fadeOut');
            setTimeout(() => {
                row.remove();
                if (document.querySelectorAll('#kwitansi-tbody tr').length === 0) {
                    location.reload();
                }
            }, 500);
        }
    }

    updateCountersAfterDelete() {
        const currentTotal = parseInt(this.getElementText('total-kwitansi')) || 0;
        const newTotal = Math.max(0, currentTotal - 1);
        
        this.updateElementText('total-kwitansi', newTotal);
        this.updateElementText('footer-total', newTotal);
        this.updateTableCount(newTotal);
        this.updateButtonStates(newTotal);
    }

    updateLastUpdated() {
        const now = new Date();
        const formattedTime = now.toLocaleString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        this.updateElementText('last-updated', formattedTime);
    }

    startPeriodicUpdates() {
        // Update time every minute
        setInterval(() => {
            this.updateLastUpdated();
        }, 60000);

        // Check available data every 30 seconds
        setInterval(() => {
            this.checkAvailableData(true);
        }, 30000);
    }

    showLoadingState(type) {
        const element = document.getElementById(`${type}-loading`);
        if (element) {
            element.classList.add('loading-pulse');
        }
    }

    hideLoadingState(type) {
        const element = document.getElementById(`${type}-loading`);
        if (element) {
            element.classList.remove('loading-pulse');
        }
    }

    showError(title, message) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Tutup'
        });
    }

    updateElementText(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) element.textContent = text;
    }

    getElementText(elementId) {
        const element = document.getElementById(elementId);
        return element ? element.textContent : '';
    }

    reinitializeTooltips() {
        if (typeof $ !== 'undefined') {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    }

    reattachDeleteListeners() {
        document.querySelectorAll('.delete-kwitansi').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const kwitansiId = button.getAttribute('data-id');
                const uraian = button.getAttribute('data-uraian');
                this.deleteSingleKwitansi(kwitansiId, uraian);
            });
        });
    }

    getBaseUrl() {
        return window.location.origin;
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
}

// ==================== INITIALIZATION ====================

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Kwitansi Manager
    window.kwitansiManager = new KwitansiManager();
    
    // Add some global error handling
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
    });
    
    // Handle page unload
    window.addEventListener('beforeunload', function(e) {
        // Clean up if needed
    });
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = KwitansiManager;
}