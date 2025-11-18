class TandaTerimaManager {
    constructor() {
        this.init();
    }

    init() {
        console.log('🚀 Initializing Tanda Terima Manager...');
        this.initializeEventListeners();
        this.checkAvailableData();
        this.updateLastUpdated();
        this.startPeriodicUpdates();
        this.initializeModalHandlers();
    }

    initializeEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const tahunFilter = document.getElementById('tahunFilter');

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
                const searchInput = document.getElementById('searchInput');
                this.performSearch(searchInput?.value || '', tahunFilter.value);
            });
        }

        // Action buttons
        this.initializeActionButtons();
    }

    initializeModalHandlers() {
        // Preview Tanda Terima Modal Handler
        const previewModal = document.getElementById('previewModal');
        const pdfPreview = document.getElementById('pdfPreview');
        const pdfLoading = document.getElementById('pdfLoading');
        const fallbackDownload = document.getElementById('fallbackDownload');
        const fullscreenToggle = document.getElementById('fullscreenToggle');
        
        if (previewModal) {
            // When modal is about to be shown
            previewModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const tandaTerimaId = button.getAttribute('data-id');
                const previewUrl = `${this.getBaseUrl()}/tanda-terima/${tandaTerimaId}/preview-pdf`;
                const downloadUrl = `${this.getBaseUrl()}/tanda-terima/${tandaTerimaId}/pdf`;
                
                console.log('Opening preview modal for ID:', tandaTerimaId);
                console.log('Preview URL:', previewUrl);
                
                // Show loading
                if (pdfLoading) {
                    pdfLoading.style.display = 'flex';
                    pdfLoading.style.height = '100%';
                }
                if (pdfPreview) {
                    pdfPreview.style.display = 'none';
                }
                
                // Set PDF source dengan parameter untuk menghilangkan toolbar
                if (pdfPreview) {
                    pdfPreview.setAttribute('data', `${previewUrl}#toolbar=0&navpanes=0&scrollbar=0`);
                }
                if (fallbackDownload) {
                    fallbackDownload.setAttribute('href', downloadUrl);
                }
                
                // Update modal title
                document.getElementById('previewModalLabel').textContent = `Preview Tanda Terima #${tandaTerimaId}`;
                
                // Reset modal size
                const modalDialog = previewModal.querySelector('.modal-dialog');
                if (modalDialog) {
                    modalDialog.classList.remove('modal-fullscreen');
                    modalDialog.style.height = '95vh';
                }
                if (fullscreenToggle) {
                    fullscreenToggle.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                    fullscreenToggle.setAttribute('title', 'Fullscreen');
                }
            });

            // When modal is fully shown
            previewModal.addEventListener('shown.bs.modal', () => {
                // Force resize untuk memastikan PDF mengambil seluruh space
                setTimeout(() => {
                    if (pdfPreview) {
                        const container = pdfPreview.parentElement;
                        if (container) {
                            container.style.height = '100%';
                            container.style.width = '100%';
                        }
                        pdfPreview.style.height = '100%';
                        pdfPreview.style.width = '100%';
                    }
                }, 100);

                // Hide loading and show PDF
                setTimeout(() => {
                    if (pdfLoading) pdfLoading.style.display = 'none';
                    if (pdfPreview) {
                        pdfPreview.style.display = 'block';
                    }
                }, 500);
            });

            // Reset modal when closed
            previewModal.addEventListener('hidden.bs.modal', () => {
                if (pdfPreview) {
                    pdfPreview.setAttribute('data', '');
                    pdfPreview.style.display = 'none';
                }
                if (pdfLoading) {
                    pdfLoading.style.display = 'none';
                }
                
                // Reset fullscreen
                const modalDialog = previewModal.querySelector('.modal-dialog');
                if (modalDialog) {
                    modalDialog.classList.remove('modal-fullscreen');
                    modalDialog.style.height = '95vh';
                }
                if (fullscreenToggle) {
                    fullscreenToggle.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                    fullscreenToggle.setAttribute('title', 'Fullscreen');
                }
            });
        }

        // Fullscreen toggle dengan perbaikan height
        if (fullscreenToggle) {
            fullscreenToggle.addEventListener('click', () => {
                const modalDialog = previewModal.querySelector('.modal-dialog');
                if (!modalDialog) return;
                
                const isFullscreen = modalDialog.classList.contains('modal-fullscreen');
                
                if (isFullscreen) {
                    // Exit fullscreen
                    modalDialog.classList.remove('modal-fullscreen');
                    modalDialog.style.height = '95vh';
                    fullscreenToggle.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                    fullscreenToggle.setAttribute('title', 'Fullscreen');
                    
                    // Reset body overflow
                    document.body.style.overflow = '';
                } else {
                    // Enter fullscreen
                    modalDialog.classList.add('modal-fullscreen');
                    modalDialog.style.height = '100vh';
                    fullscreenToggle.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
                    fullscreenToggle.setAttribute('title', 'Exit Fullscreen');
                    
                    // Prevent body scroll
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        // Handle PDF loading events
        if (pdfPreview) {
            pdfPreview.addEventListener('load', () => {
                console.log('PDF loaded successfully in modal');
                if (pdfLoading) {
                    pdfLoading.style.display = 'none';
                }
                pdfPreview.style.display = 'block';
            });

            pdfPreview.addEventListener('error', (e) => {
                console.error('Error loading PDF in modal:', e);
                if (pdfLoading) {
                    pdfLoading.style.display = 'none';
                }
                
                // Show error message dengan layout yang proper
                const container = pdfPreview.parentElement;
                const downloadUrl = fallbackDownload ? fallbackDownload.getAttribute('href') : '#';
                
                if (container) {
                    container.innerHTML = `
                        <div class="d-flex justify-content-center align-items-center h-100 w-100">
                            <div class="text-center p-4">
                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-dark">Gagal Memuat PDF</h5>
                                <p class="text-muted mb-3">Terjadi kesalahan saat memuat preview PDF.</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-outline-secondary" onclick="location.reload()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                                    </button>
                                    <a href="${downloadUrl}" class="btn btn-primary" target="_blank">
                                        <i class="bi bi-download me-2"></i>Download PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
        }
    }

    initializeActionButtons() {
        // Generate All
        const generateBtn = document.getElementById('generate-all-btn');
        if (generateBtn) {
            generateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.generateAllTandaTerima();
            });
        }

        // Generate from empty state
        const generateEmptyBtn = document.getElementById('generate-empty-btn');
        if (generateEmptyBtn) {
            generateEmptyBtn.addEventListener('click', () => {
                this.generateAllTandaTerima();
            });
        }

        // Delete All
        const deleteAllBtn = document.getElementById('delete-all-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.deleteAllTandaTerima();
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
            if (e.target.closest('.delete-tanda-terima')) {
                e.preventDefault();
                const button = e.target.closest('.delete-tanda-terima');
                const tandaTerimaId = button.getAttribute('data-id');
                const uraian = button.getAttribute('data-uraian');
                this.deleteSingleTandaTerima(tandaTerimaId, uraian);
            }
        });
    }

    async performSearch(searchTerm = '', tahun = '') {
        try {
            this.showLoadingState('search');
            
            let url = `${this.getBaseUrl()}/tanda-terima/search?search=${encodeURIComponent(searchTerm)}`;
            if (tahun) {
                url += `&tahun=${encodeURIComponent(tahun)}`;
            }

            console.log('Search URL:', url);

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.updateSearchResults(data.data, data.pagination);
                this.updateCounters(data.total);
                this.updateTableCount(data.total);
            } else {
                this.showError('Search failed', data.message);
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Search Error', 'Terjadi kesalahan saat mencari data');
        } finally {
            this.hideLoadingState('search');
        }
    }

    updateSearchResults(data, pagination) {
        const tbody = document.getElementById('tanda-terima-tbody');
        if (!tbody) return;

        if (data.length === 0) {
            tbody.innerHTML = this.getEmptySearchHTML();
            return;
        }

        let html = '';
        data.forEach((item, index) => {
            const number = (pagination.current_page - 1) * pagination.per_page + index + 1;
            html += this.getTableRowHTML(item, number);
        });

        tbody.innerHTML = html;
        
        console.log('Search results updated, rows:', data.length);
    }

    getTableRowHTML(item, number) {
        return `
            <tr id="tanda-terima-row-${item.id}">
                <td class="py-3 ps-4">
                    <div class="fw-bold">${number}</div>
                </td>
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
                <td class="fw-bold text-success text-end">
                    <i class="bi bi-currency-dollar me-2"></i>${item.jumlah}
                </td>
                <td class="text-center pe-4">
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn table-action-btn preview-tanda-terima" 
                                title="Lihat Preview" 
                                data-id="${item.id}"
                                data-bs-toggle="modal" 
                                data-bs-target="#previewModal">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="${this.getBaseUrl()}/tanda-terima/${item.id}/pdf" 
                           class="btn table-action-btn" 
                           title="Download PDF" 
                           target="_blank">
                            <i class="bi bi-download"></i>
                        </a>
                        <button class="btn table-action-btn delete-tanda-terima" 
                                title="Hapus Tanda Terima"
                                data-id="${item.id}"
                                data-uraian="${item.uraian}">
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
                        <i class="bi bi-search empty-state-icon" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h5 class="text-dark mb-2">Tidak ada data yang ditemukan</h5>
                        <p class="text-muted">Coba ubah kata kunci pencarian atau filter tahun</p>
                    </div>
                </td>
            </tr>
        `;
    }

    async generateAllTandaTerima() {
        const result = await Swal.fire({
            title: 'Generate Tanda Terima Otomatis?',
            html: `
                <div class="text-center">
                    <p>Proses ini akan membuat tanda terima untuk semua transaksi Buku Kas Umum yang belum memiliki tanda terima.</p>
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

        // Show initial progress modal
        Swal.fire({
            title: 'Memulai Generate...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                    <p>Sedang mempersiapkan data...</p>
                    <div class="progress mt-3" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        try {
            // Check available data first
            const availableData = await this.checkAvailableData(true);
            totalRecords = availableData.availableCount || 0;

            if (totalRecords === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak ada data',
                    text: 'Tidak ada data yang perlu digenerate.'
                });
                return;
            }

            // Set timeout for safety (10 minutes)
            timeoutId = setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Proses Dihentikan',
                    text: 'Proses generate dihentikan karena terlalu lama.',
                    confirmButtonText: 'Mengerti'
                });
            }, 10 * 60 * 1000);

            // Start batch processing
            await this.processBatch(0, totalSuccess, totalFailed, totalRecords, timeoutId);

        } catch (error) {
            console.error('Generation error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal memulai proses generate: ' + error.message
            });
        }
    }

    async processBatch(offset, totalSuccess, totalFailed, totalRecords, timeoutId, attempt = 1) {
        try {
            const formData = new FormData();
            formData.append('_token', this.getCsrfToken());
            formData.append('offset', offset);

            const response = await fetch(`${this.getBaseUrl()}/tanda-terima/generate-batch`, {
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
                this.updateProgress(batchData.progress, newSuccess, newFailed, batchData.remaining, batchData.total);

                // Continue or complete
                if (batchData.has_more && batchData.progress < 100) {
                    // Continue with next batch after short delay
                    setTimeout(() => {
                        this.processBatch(newOffset, newSuccess, newFailed, batchData.total, timeoutId, attempt + 1);
                    }, 1000);
                } else {
                    // Complete the process
                    clearTimeout(timeoutId);
                    setTimeout(() => {
                        this.showCompletionMessage(newSuccess, newFailed, batchData.total);
                    }, 1000);
                }
            } else {
                clearTimeout(timeoutId);
                this.showError('Generate Error', data.message || 'Terjadi kesalahan saat proses generate');
            }
        } catch (error) {
            clearTimeout(timeoutId);
            console.error('Batch process error:', error);
            
            // Retry logic
            if (attempt < 3) {
                Swal.showLoading();
                Swal.update({
                    html: `
                        <div class="text-center">
                            <div class="spinner-border text-warning mb-3" style="width: 2rem; height: 2rem;"></div>
                            <p>Gagal memproses batch, mencoba ulang (${attempt}/3)...</p>
                            <div class="progress mt-3" style="height: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                     role="progressbar" style="width: ${Math.min(attempt * 30, 90)}%">Retry ${attempt}/3</div>
                            </div>
                        </div>
                    `
                });
                
                setTimeout(() => {
                    this.processBatch(offset, totalSuccess, totalFailed, totalRecords, timeoutId, attempt + 1);
                }, 2000);
            } else {
                this.showError('Network Error', 'Terjadi kesalahan koneksi saat proses generate setelah 3x percobaan');
            }
        }
    }

    updateProgress(percent, success, failed, remaining, total) {
        const safePercent = Math.min(100, Math.max(0, percent));

        Swal.update({
            title: `Generate Tanda Terima ${safePercent}%`,
            html: `
                <div class="text-center">
                    <div class="mb-3">
                        <div class="progress" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                role="progressbar" style="width: ${safePercent}%; font-size: 12px; font-weight: bold;">
                                ${safePercent}%
                            </div>
                        </div>
                    </div>

                    <div class="row text-center small">
                        <div class="col-3">
                            <div class="text-success font-weight-bold">${success}</div>
                            <small>Berhasil</small>
                        </div>
                        <div class="col-3">
                            <div class="text-danger font-weight-bold">${failed}</div>
                            <small>Gagal</small>
                        </div>
                        <div class="col-3">
                            <div class="text-info font-weight-bold">${remaining}</div>
                            <small>Sisa</small>
                        </div>
                        <div class="col-3">
                            <div class="text-primary font-weight-bold">${total}</div>
                            <small>Total</small>
                        </div>
                    </div>

                    ${safePercent < 100 ? `
                    <div class="mt-3">
                        <div class="spinner-border text-primary" style="width: 1.5rem; height: 1.5rem;"></div>
                        <small class="text-muted ml-2">Memproses data...</small>
                    </div>
                    ` : ''}
                </div>
            `
        });
    }

    showCompletionMessage(successCount, failedCount, totalCount) {
        const resultHtml = `
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                    <h5 class="text-success">Proses Generate Selesai!</h5>
                </div>
                
                <div class="alert alert-success">
                    <p><strong>Proses generate telah selesai.</strong></p>
                    <p class="mb-0">
                        Berhasil: <strong>${successCount}</strong> | 
                        Gagal: <strong>${failedCount}</strong> | 
                        Total: <strong>${totalCount}</strong>
                    </p>
                </div>
                
                ${failedCount > 0 ? `
                <div class="alert alert-warning mt-3">
                    <small>Beberapa data gagal digenerate. Periksa log untuk detail lebih lanjut.</small>
                </div>
                ` : ''}
            </div>
        `;

        Swal.fire({
            title: 'Proses Selesai',
            html: resultHtml,
            icon: 'success',
            confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Refresh Halaman',
            allowOutsideClick: false
        }).then((result) => {
            location.reload();
        });
    }

    async checkAvailableData(silent = false) {
        try {
            if (!silent) this.showLoadingState('available');
            
            const response = await fetch(`${this.getBaseUrl()}/tanda-terima/check-available`);
            const data = await response.json();

            if (data.success) {
                this.updateElementText('ready-generate', data.data.availableCount || 0);
                this.updateElementText('ready-generate-badge', data.data.availableCount || 0);
                return data.data;
            } else {
                console.error('Error checking available data:', data.message);
                this.updateElementText('ready-generate', '0');
                this.updateElementText('ready-generate-badge', '0');
                return { availableCount: 0 };
            }
        } catch (error) {
            console.error('AJAX error checking available data:', error);
            this.updateElementText('ready-generate', '0');
            this.updateElementText('ready-generate-badge', '0');
            return { availableCount: 0 };
        } finally {
            if (!silent) this.hideLoadingState('available');
        }
    }

    async deleteAllTandaTerima() {
        const result = await Swal.fire({
            title: 'Hapus Semua Tanda Terima?',
            html: `
                <div class="alert alert-warning text-left">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Konfirmasi Penghapusan</strong>
                    <hr>
                    <p>Apakah Anda yakin ingin menghapus <strong>SEMUA DATA TANDA TERIMA</strong>?</p>
                    <p class="mb-0">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data tanda terima secara permanen.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Semua!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            this.processDeleteAll();
        }
    }

    async processDeleteAll() {
        this.showGlobalLoading();

        try {
            const response = await fetch(`${this.getBaseUrl()}/tanda-terima/delete/all`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Berhasil!', data.message);
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                this.showError('Delete Failed', data.message);
            }
        } catch (error) {
            console.error('Delete all error:', error);
            this.showError('Delete Error', 'Terjadi kesalahan saat menghapus semua data tanda terima');
        } finally {
            this.hideGlobalLoading();
        }
    }

    deleteSingleTandaTerima(tandaTerimaId, uraian) {
        Swal.fire({
            title: 'Hapus Tanda Terima?',
            html: `
                <div class="alert alert-warning text-left">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Konfirmasi Penghapusan</strong>
                    <hr>
                    <p>Apakah Anda yakin ingin menghapus tanda terima untuk:</p>
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
                this.processDeleteSingle(tandaTerimaId, uraian);
            }
        });
    }

    async processDeleteSingle(tandaTerimaId, uraian) {
        this.showGlobalLoading();

        try {
            const response = await fetch(`${this.getBaseUrl()}/tanda-terima/${tandaTerimaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Berhasil!', data.message);
                this.removeRowFromTable(tandaTerimaId);
                this.updateCountersAfterDelete();
            } else {
                this.showError('Delete Failed', data.message);
            }
        } catch (error) {
            console.error('Delete single error:', error);
            this.showError('Delete Error', 'Terjadi kesalahan saat menghapus tanda terima');
        } finally {
            this.hideGlobalLoading();
        }
    }

    handleDownloadAll(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Download Semua Tanda Terima?',
            html: `
                <div class="alert alert-info text-left">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Informasi Download</strong>
                    <hr>
                    <p>Anda akan mengunduh semua tanda terima dalam satu file PDF.</p>
                    <p class="mb-0">File akan langsung didownload tanpa loading yang lama.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-download mr-1"></i> Download Sekarang',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // PILIH SALAH SATU SOLUSI:
                this.processDownloadAllSimple();      // Solusi 1
                // this.processDownloadAllHybrid();       // Solusi 2 (Rekomendasi)
                // this.processDownloadAllInstant();    // Solusi 3  
                // this.processDownloadAllOptimized();  // Solusi 4
            }
        });
    }

    async processDownloadAllSimple() {
        try {
            // Show loading sangat singkat
            Swal.fire({
                title: 'Memulai Download...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                        <p>Membuka file download...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 10000, // Hanya 10 detik
                timerProgressBar: true,
            });

            // Tunggu sebentar lalu redirect ke download URL
            setTimeout(() => {
                window.location.href = `${this.getBaseUrl()}/tanda-terima/download/all`;
            }, 1000);

            // Auto close loading setelah 10 detik (file sudah mulai download)
            setTimeout(() => {
                Swal.close();
            }, 10000);

        } catch (error) {
            console.error('Download all error:', error);
            Swal.close();
            this.showError('Download Error', 'Terjadi kesalahan saat mengunduh semua tanda terima');
        }
    }

    // async processDownloadAllHybrid() {
    //     try {
    //         // Show quick loading toast
    //         const downloadToast = Swal.fire({
    //             title: 'Memulai Download...',
    //             html: `
    //                 <div class="text-center">
    //                     <div class="spinner-border text-primary mb-2" style="width: 1.5rem; height: 1.5rem;"></div>
    //                     <p class="small mb-0">Mempersiapkan file PDF...</p>
    //                 </div>
    //             `,
    //             showConfirmButton: false,
    //             allowOutsideClick: false,
    //             timer: 2000,
    //             timerProgressBar: true,
    //             didOpen: () => {
    //                 // Immediately start download in new tab
    //                 setTimeout(() => {
    //                     window.open(`${this.getBaseUrl()}/tanda-terima/download/all`, '_blank');
    //                 }, 300);
    //             }
    //         });

    //         // After toast completes, show brief success message
    //         downloadToast.then(() => {
    //             Swal.fire({
    //                 icon: 'success',
    //                 title: 'Download Dimulai!',
    //                 html: `
    //                     <div class="text-center">
    //                         <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
    //                         <p class="small">File sedang didownload.<br>Periksa folder download atau tab baru.</p>
    //                     </div>
    //                 `,
    //                 confirmButtonText: '<i class="fas fa-check mr-1"></i> Mengerti',
    //                 timer: 4000,
    //                 showConfirmButton: true
    //             });
    //         });

    //     } catch (error) {
    //         console.error('Download all error:', error);
    //         Swal.close();
    //         this.showError('Download Error', 'Terjadi kesalahan: ' + error.message);
    //     }
    // }

    updateCounters(total) {
        this.updateElementText('total-tanda-terima', total);
        this.updateElementText('table-count', total);
        this.updateButtonStates(total);
    }

    updateTableCount(count) {
        const tableCount = document.getElementById('table-count');
        if (tableCount) {
            tableCount.textContent = count;
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

    removeRowFromTable(tandaTerimaId) {
        const row = document.getElementById(`tanda-terima-row-${tandaTerimaId}`);
        if (row) {
            row.remove();
            // Jika tidak ada data lagi, reload halaman
            if (document.querySelectorAll('#tanda-terima-tbody tr').length === 0) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        }
    }

    updateCountersAfterDelete() {
        const currentTotal = parseInt(this.getElementText('total-tanda-terima')) || 0;
        const newTotal = Math.max(0, currentTotal - 1);
        
        this.updateElementText('total-tanda-terima', newTotal);
        this.updateElementText('table-count', newTotal);
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

    showGlobalLoading() {
        const loading = document.getElementById('global-loading');
        if (loading) loading.style.display = 'block';
    }

    hideGlobalLoading() {
        const loading = document.getElementById('global-loading');
        if (loading) loading.style.display = 'none';
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

    showSuccess(title, message) {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke'
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

    getBaseUrl() {
        return window.location.origin;
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    window.tandaTerimaManager = new TandaTerimaManager();
});