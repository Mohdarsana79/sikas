/**
 * Kwitansi Management System
 * Complete JavaScript for Kwitansi Page
 * Features: Search, Filter, Generate, Delete, Pagination, Animations
 */

class KwitansiManager {
    constructor() {
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

    init() {
        console.log('🚀 Initializing Kwitansi Manager...');
        
        // Debug containers
        this.debugContainers();
        
        // Initialize components
        this.initializeEventListeners();
        this.initializeTooltips();
        this.checkAvailableData();
        this.updateLastUpdated();
        
        // Start periodic updates
        this.startPeriodicUpdates();
        
        console.log('✅ Kwitansi Manager initialized successfully');
    }

    initializeEventListeners() {
        console.log('🚀 Initializing Kwitansi Manager Event Listeners...');
        
        // Search and Filter
        const searchInput = document.getElementById('searchInput');
        const tahunFilter = document.getElementById('tahunFilter');
        const clearSearchBtn = document.getElementById('clearSearch');

        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value, tahunFilter?.value || '');
                }, 500);
            });
        }

        if (tahunFilter) {
            tahunFilter.addEventListener('change', () => {
                this.performSearch(searchInput?.value || '', tahunFilter.value);
            });
        }

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (tahunFilter) tahunFilter.value = '';
                this.performSearch('', '');
            });
        }

        // Action Buttons
        this.initializeActionButtons();
        
        // Generate from empty state
        const generateEmptyBtn = document.getElementById('generate-empty-btn');
        if (generateEmptyBtn) {
            generateEmptyBtn.addEventListener('click', () => {
                this.generateAllKwitansi();
            });
        }

        // PERBAIKAN: Inisialisasi pagination untuk halaman pertama
        this.attachPaginationEventListeners();
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

        // Delete All
        const deleteAllBtn = document.getElementById('delete-all-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.deleteAllKwitansi();
            });
        }

        // Download All
        const downloadAllBtn = document.getElementById('download-all-btn');
        if (downloadAllBtn) {
            downloadAllBtn.addEventListener('click', (e) => {
                this.handleDownloadAll(e);
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

    async performSearch(searchTerm = '', tahun = '') {
        try {
            console.log('🔍 Performing search:', { searchTerm, tahun });
            this.showLoadingState('search');
            
            let url = `${this.getBaseUrl()}/kwitansi/search?search=${encodeURIComponent(searchTerm)}`;
            if (tahun) {
                url += `&tahun=${encodeURIComponent(tahun)}`;
            }

            console.log('📡 Fetch URL:', url);

            const response = await fetch(url);
            const data = await response.json();

            console.log('📦 Response data:', data);

            if (data.success) {
                console.log('✅ Search successful, updating results...');
                this.updateSearchResults(data.data, data.pagination);
                this.updateCounters(data.total);
                this.updateTableCount(data.total);
                
                // Update tahun filter if needed
                const tahunFilter = document.getElementById('tahunFilter');
                if (tahunFilter && data.selected_tahun) {
                    tahunFilter.value = data.selected_tahun;
                }
            } else {
                console.error('❌ Search failed:', data.message);
                this.showError('Search failed', data.message);
            }
        } catch (error) {
            console.error('💥 Search error:', error);
            this.showError('Search Error', 'Terjadi kesalahan saat mencari data');
        } finally {
            this.hideLoadingState('search');
        }
    }

    // ==================== SEARCH & FILTER ====================

    updateSearchResults(data, pagination) {
        const tbody = document.getElementById('kwitansi-tbody');
        if (!tbody) return;

        if (data.length === 0) {
            tbody.innerHTML = this.getEmptySearchHTML();
            // Sembunyikan pagination jika tidak ada data
            this.hidePagination();
            return;
        }

        let html = '';
        data.forEach((item, index) => {
            const number = (pagination.current_page - 1) * pagination.per_page + index + 1;
            html += this.getTableRowHTML(item, number, pagination);
        });

        tbody.innerHTML = html;
        
        // PERBAIKAN: Update pagination dengan fallback
        this.updatePagination(pagination);
        this.reinitializeTooltips();
        this.reattachDeleteListeners();
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

    getTableRowHTML(item, number, pagination) {
        // Pastikan URL preview dan PDF menggunakan base URL yang benar
        const previewUrl = `${this.getBaseUrl()}/kwitansi/${item.id}/preview`;
        const pdfUrl = `${this.getBaseUrl()}/kwitansi/${item.id}/pdf`;
        
        return `
            <tr id="kwitansi-row-${item.id}" class="animate__animated animate__fadeIn">
                <td class="fw-medium text-dark">${number}</td>
                <td>
                    <span class="badge badge-code">${item.kode_rekening}</span>
                </td>
                <td class="text-dark">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text me-2 text-muted"></i>
                        <span class="text-truncate" style="max-width: 300px;">${item.uraian}</span>
                    </div>
                </td>
                <td class="text-muted">
                    <i class="bi bi-calendar me-2"></i>${item.tanggal}
                </td>
                <td class="fw-bold text-success">
                    <i class="bi bi-currency-dollar me-2"></i>${item.jumlah}
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <a href="${previewUrl}" 
                            class="btn btn-action btn-outline-primary" 
                            title="Lihat Preview" data-bs-toggle="tooltip" target="_blank">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="${pdfUrl}" 
                            class="btn btn-action btn-outline-success" 
                            title="Download PDF" data-bs-toggle="tooltip" target="_blank">
                            <i class="bi bi-printer"></i>
                        </a>
                        <button class="btn btn-action btn-outline-danger delete-kwitansi" 
                            data-id="${item.id}"
                            data-uraian="${item.uraian}"
                            title="Hapus Kwitansi" data-bs-toggle="tooltip">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    getEmptySearchHTML() {
        return `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="empty-state" style="padding: 2rem; background: transparent; border: none;">
                        <i class="bi bi-search empty-state-icon" style="font-size: 3rem;"></i>
                        <h5 class="text-dark mb-2">Tidak ada data yang ditemukan</h5>
                        <p class="text-muted">Coba ubah kata kunci pencarian atau filter tahun</p>
                    </div>
                </td>
            </tr>
        `;
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
                    const searchTerm = document.getElementById('searchInput')?.value || '';
                    const selectedTahun = document.getElementById('tahunFilter')?.value || '';
                    
                    this.loadPage(page, searchTerm, selectedTahun);
                });
            }
        });
    }

    // PERBAIKAN: Method loadPage yang lebih baik
    async loadPage(page, searchTerm = '', tahun = '') {
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

    // ==================== GENERATE FUNCTIONALITY ====================

    async generateAllKwitansi() {
        const result = await Swal.fire({
            title: 'Generate Kwitansi Otomatis?',
            html: `
                <div class="text-center">
                    <p>Proses ini akan membuat kwitansi untuk semua transaksi yang belum memiliki kwitansi.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Pastikan tidak menutup halaman selama proses berlangsung.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-play mr-1"></i> Mulai Generate',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            this.startGenerationWithProgress();
        }
    }

    async startGenerationWithProgress() {
        let totalSuccess = 0;
        let totalFailed = 0;
        let totalRecords = 0;
        let timeoutId = null;

        // Show initial loading
        Swal.fire({
            title: 'Memulai Generate...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                    <p>Sedang mempersiapkan data...</p>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false
        });

        try {
            // Check available data first
            const availableData = await this.checkAvailableData(true);
            totalRecords = availableData.available_count || 0;

            if (totalRecords === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak ada data',
                    text: 'Tidak ada data yang perlu digenerate.'
                });
                return;
            }

            // Set timeout for safety
            timeoutId = setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Proses Dihentikan',
                    text: 'Proses generate dihentikan karena terlalu lama.',
                    confirmButtonText: 'Mengerti'
                });
            }, 5 * 60 * 1000);

            // Start batch processing
            await this.processBatch(0, totalSuccess, totalFailed, totalRecords, timeoutId);

        } catch (error) {
            console.error('Generation error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal memulai proses generate'
            });
        }
    }

    async processBatch(offset, totalSuccess, totalFailed, totalRecords, timeoutId, attempt = 1) {
        try {
            const formData = new FormData();
            formData.append('_token', this.getCsrfToken());
            formData.append('offset', offset);

            const response = await fetch(`${this.getBaseUrl()}/kwitansi/generate-batch`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                const batchData = data.data;
                const newSuccess = totalSuccess + batchData.success;
                const newFailed = totalFailed + batchData.failed;
                const newOffset = batchData.offset;

                // Update progress
                this.updateSimpleProgress(batchData.progress, newSuccess, newFailed, batchData.remaining, batchData.total);
                this.updateElementText('pending-count', batchData.remaining);
                this.updateElementText('failed-count', newFailed);

                // Continue or complete
                if (batchData.has_more && batchData.progress < 100) {
                    setTimeout(() => {
                        this.processBatch(newOffset, newSuccess, newFailed, batchData.total, timeoutId, attempt + 1);
                    }, 500);
                } else {
                    clearTimeout(timeoutId);
                    setTimeout(() => {
                        this.showSimpleCompletionMessage(newSuccess, newFailed, batchData.total);
                    }, 1000);
                }
            } else {
                clearTimeout(timeoutId);
                this.showError('Generate Error', data.message || 'Terjadi kesalahan saat proses generate');
            }
        } catch (error) {
            clearTimeout(timeoutId);
            console.error('Batch process error:', error);
            this.showError('Network Error', 'Terjadi kesalahan koneksi saat proses generate');
        }
    }

    updateSimpleProgress(percent, success, failed, remaining, total) {
        const safePercent = Math.min(100, Math.max(0, percent));

        Swal.update({
            title: `Generate Kwitansi ${safePercent}%`,
            html: `
                <div class="text-center">
                    <div class="mb-3">
                        <div class="progress" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                style="width: ${safePercent}%; font-size: 12px; font-weight: bold;">
                                ${safePercent}%
                            </div>
                        </div>
                    </div>

                    <div class="row text-center small">
                        <div class="col-4">
                            <div class="text-success font-weight-bold">${success}</div>
                            <small>Berhasil</small>
                        </div>
                        <div class="col-4">
                            <div class="text-danger font-weight-bold">${failed}</div>
                            <small>Gagal</small>
                        </div>
                        <div class="col-4">
                            <div class="text-info font-weight-bold">${remaining}</div>
                            <small>Sisa</small>
                        </div>
                    </div>

                    ${safePercent < 100 ? `
                    <div class="mt-3">
                        <div class="spinner-border text-primary" style="width: 1.5rem; height: 1.5rem;"></div>
                        <small class="text-muted ml-2">Memproses data...</small>
                    </div>
                    ` : ''}
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: false
        });
    }

    showSimpleCompletionMessage(successCount, failedCount, totalCount) {
        const resultHtml = `
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                    <h5 class="text-success">Proses Generate Selesai!</h5>
                </div>
                
                <div class="alert alert-success">
                    <p><strong>Proses generate telah selesai. Silahkan cek data Anda.</strong></p>
                    <p class="mb-0">
                        Berhasil: <strong>${successCount}</strong> | 
                        Gagal: <strong>${failedCount}</strong> | 
                        Total: <strong>${totalCount}</strong>
                    </p>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Proses Selesai',
            html: resultHtml,
            icon: 'success',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke',
            allowOutsideClick: false
        }).then((result) => {
            location.reload();
        });
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
        const downloadAllBtn = e.currentTarget;
        
        if (downloadAllBtn.hasAttribute('disabled')) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Tidak ada data',
                text: 'Tidak ada data kwitansi untuk diunduh'
            });
            return;
        }

        e.preventDefault();
        
        Swal.fire({
            title: 'Download Semua Kwitansi?',
            html: `
                <div class="alert alert-info text-left">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Informasi Download</strong>
                    <hr>
                    <p>Anda akan mengunduh <strong>${document.getElementById('total-kwitansi').textContent} kwitansi</strong> dalam satu file PDF.</p>
                    <p class="mb-0">Proses ini mungkin membutuhkan waktu beberapa saat tergantung jumlah data.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-download mr-1"></i> Download',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Mempersiapkan Download...',
                    html: `
                        <div class="text-center">
                            <div class="spinner-border text-info mb-3" style="width: 2rem; height: 2rem;"></div>
                            <p>Sedang menyiapkan file PDF...</p>
                        </div>
                    `,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                window.location.href = downloadAllBtn.href;
                
                setTimeout(() => {
                    Swal.close();
                }, 3000);
            }
        });
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