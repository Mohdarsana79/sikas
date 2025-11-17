class GlobalSearch {
    constructor() {
        this.currentSearchTerm = '';
        this.originalContents = new Map();
        this.currentPage = 'default';
        this.init();
    }

    init() {
        this.setupSearchForm();
        this.setupRouteBasedSearch();
        this.setupGlobalEvents();
    }

    setupSearchForm() {
        const searchForm = document.getElementById('globalSearchForm');
        const searchInput = document.getElementById('globalSearchInput');
        
        if (!searchForm || !searchInput) return;

        let searchTimeout;
        
        // Real-time search dengan debounce
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.trim();
            this.currentSearchTerm = searchTerm;
            
            if (searchTerm.length === 0) {
                this.resetSearch();
                return;
            }
            
            if (searchTerm.length < 2) return;
            
            searchTimeout = setTimeout(() => {
                this.performSearch(searchTerm);
            }, 500);
        });

        // Handle form submit
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            this.currentSearchTerm = searchTerm;
            if (searchTerm.length >= 2) {
                this.performSearch(searchTerm);
            }
        });

        // Escape key untuk reset
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.resetSearch();
                searchInput.value = '';
                this.currentSearchTerm = '';
            }
        });

        // Clear search ketika klik icon X (jika ada)
        const clearBtn = document.querySelector('.search-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                this.resetSearch();
                this.currentSearchTerm = '';
            });
        }
    }

    setupRouteBasedSearch() {
        const currentPath = window.location.pathname;
        console.log('Current path:', currentPath);
        
        if (currentPath.includes('/bku/')) {
            this.currentPage = 'bku';
        } else if (currentPath.includes('/rkas') && !currentPath.includes('/rkas-perubahan')) {
            this.currentPage = 'rkas';
            console.log('RKAS page detected');
            
            // Detect active tab bulan untuk RKAS
            this.setupRkasTabDetection();
        } else if (currentPath.includes('/rkas-perubahan')) {
            this.currentPage = 'rkas-perubahan';
            console.log('Rkas Perubahan Page Detected');

            // Detected Active Tab Bulan Untuk RKAS Perubahan
            this.setupRkasPerubahanTabDetection();
        } else if (currentPath.includes('/referensi/kode-kegiatan')) {
            this.currentPage = 'kegiatan';
        } else if (currentPath.includes('/referensi/rekening-belanja')) {
            this.currentPage = 'rekening';
        } else if (currentPath.includes('/penganggaran')) {
            this.currentPage = 'penganggaran';
        } else {
            this.currentPage = 'default';
        }

        console.log('Current page detected:', this.currentPage);
    }

    // Setup detection untuk tab aktif di RKAS
    setupRkasTabDetection() {
        // Cari tab yang aktif
        const activeTab = document.querySelector('.nav-link.active[data-bs-toggle="tab"]');
        if (activeTab) {
            this.currentRkasMonth = activeTab.getAttribute('data-bs-target').replace('#', '');
            console.log('Active RKAS tab:', this.currentRkasMonth);
        } else {
            // Fallback ke tab pertama jika tidak ada yang aktif
            const firstTab = document.querySelector('.nav-link[data-bs-toggle="tab"]');
            if (firstTab) {
                this.currentRkasMonth = firstTab.getAttribute('data-bs-target').replace('#', '');
                console.log('Using first RKAS tab:', this.currentRkasMonth);
            }
        }
        
        // Listen untuk tab changes
        document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (event) => {
                this.currentRkasMonth = event.target.getAttribute('data-bs-target').replace('#', '');
                console.log('Tab changed to:', this.currentRkasMonth);
                
                // Reset search ketika ganti tab
                this.resetSearch();
                document.getElementById('globalSearchInput').value = '';
                this.currentSearchTerm = '';
            });
        });
    }

    setupRkasPerubahanTabDetection() {
        // Cari tab yang aktif
        const activeTab = document.querySelector('.nav-link.active[data-bs-toggle="tab"]');
        if (activeTab) {
            this.currentRkasPerubahanMonth = activeTab.getAttribute('data-bs-target').replace('#', '');
            console.log('Active RKAS Perubahan tab:', this.currentRkasPerubahanMonth);
        } else {
            // Fallback ke tab pertama jika tidak ada yang aktif
            const firstTab = document.querySelector('.nav-link[data-bs-toggle="tab"]');
            if (firstTab) {
                this.currentRkasPerubahanMonth = firstTab.getAttribute('data-bs-target').replace('#', '');
                console.log('Using first RKAS Perubahan tab:', this.currentRkasPerubahanMonth);
            }
        }
        
        // Listen untuk tab changes
        document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (event) => {
                this.currentRkasPerubahanMonth = event.target.getAttribute('data-bs-target').replace('#', '');
                console.log('Tab changed to:', this.currentRkasPerubahanMonth);
                
                // Reset search ketika ganti tab
                this.resetSearch();
                document.getElementById('globalSearchInput').value = '';
                this.currentSearchTerm = '';
            });
        });
    }

    setupGlobalEvents() {
        // Global event untuk reset search
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-reset-search')) {
                e.preventDefault();
                this.resetSearch();
                document.getElementById('globalSearchInput').value = '';
                this.currentSearchTerm = '';
            }
        });
    }

    performSearch(searchTerm) {
        console.log('=== SEARCH DEBUG START ===');
        console.log('Search term:', searchTerm);
        console.log('Current page:', this.currentPage);
        console.log('Current path:', window.location.pathname);
        
        this.showLoading();

        const endpoints = {
            'bku': () => {
                const pathParts = window.location.pathname.split('/');
                const tahun = pathParts[pathParts.length - 2];
                const bulan = pathParts[pathParts.length - 1];
                return `/bku/search/${tahun}/${bulan}?search=${encodeURIComponent(searchTerm)}`;
            },
            'rkas': () => {
                // Tambahkan parameter bulan untuk RKAS
                const bulanParam = this.currentRkasMonth || 'januari'; // default ke januari
                return `/rkas/search?search=${encodeURIComponent(searchTerm)}&bulan=${bulanParam}`;
            },
            'rkas-perubahan': () => {
                // Tambahkan parameter bulan untuk RKAS Perubahan
                const bulanParam = this.currentRkasPerubahanMonth || 'januari'; // default ke januari
                return `/rkas-perubahan/search?search=${encodeURIComponent(searchTerm)}&bulan=${bulanParam}`;
            },
            'kegiatan': `/kegiatan/search?search=${encodeURIComponent(searchTerm)}`,
            'rekening': `/rekening-belanja/search?search=${encodeURIComponent(searchTerm)}`,
            'penganggaran': `/penganggaran/search?search=${encodeURIComponent(searchTerm)}`,
            'default': null
        };

        const endpoint = endpoints[this.currentPage];
        console.log('Selected endpoint:', endpoint);
        
        if (!endpoint) {
            console.log('ERROR: No endpoint found for page:', this.currentPage);
            this.hideLoading();
            this.showError('Search tidak tersedia untuk halaman ini');
            return;
        }

        const url = typeof endpoint === 'function' ? endpoint() : endpoint;
        console.log('Final URL:', url);

        // Cek CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('CSRF Token exists:', !!csrfToken);
        if (csrfToken) {
            console.log('CSRF Token content:', csrfToken.content);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            this.hideLoading();
            if (data.success) {
                this.handleSearchResponse(data, searchTerm);
            } else {
                this.showError(data.message || 'Terjadi kesalahan saat mencari data');
            }
        })
        .catch(error => {
            console.error('Search error details:', error);
            this.hideLoading();
            this.showError('Terjadi kesalahan saat menghubungi server: ' + error.message);
        })
        .finally(() => {
            console.log('=== SEARCH DEBUG END ===');
        });
    }

    handleSearchResponse(data, searchTerm) {
        const handlers = {
            'bku': () => this.updateBkuTable(data.data, searchTerm),
            'rkas': () => this.updateRkasTable(data.data, searchTerm),
            'rkas-perubahan': () => this.updateRkasPerubahanTable(data.data, searchTerm),
            'kegiatan': () => this.updateKegiatanTable(data.data, searchTerm),
            'rekening': () => this.updateRekeningTable(data.data, searchTerm),
            'penganggaran': () => this.updatePenganggaranTable(data.data, searchTerm),
            'default': () => this.updateDefaultTable(data.data, searchTerm)
        };

        const handler = handlers[this.currentPage];
        if (handler) {
            handler();
        } else {
            this.showError('Handler tidak ditemukan untuk halaman ini');
        }
    }

    // BKU Table Update
    updateBkuTable(data, searchTerm) {
        const tableBody = document.getElementById('bkuTableBody');
        if (!tableBody) {
            this.showError('Tabel BKU tidak ditemukan');
            return;
        }

        this.saveOriginalContent('bkuTableBody', tableBody.innerHTML);

        if (data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan transaksi dengan kata kunci: "<strong>${searchTerm}</strong>"
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            data.forEach(item => {
                html += this.renderBkuRow(item);
            });
            tableBody.innerHTML = html;
        }

        this.showSearchInfo(data.length, searchTerm, 'bku');
        this.reattachEventListeners();
    }

    renderBkuRow(item) {
        const pajakInfo = item.pajak_info || {};
        let pajakHtml = '';
        
        if (pajakInfo.status === 'none' || !pajakInfo.status) {
            pajakHtml = `<span class="text-muted">Rp 0</span>`;
        } else {
            pajakHtml = `
                <span class="${pajakInfo.text_class || 'text-dark'}">Rp ${pajakInfo.amount || '0'}</span>
                <small class="${pajakInfo.badge_class || 'badge-sudah-lapor'} status-pajak-badge d-block">
                    <i class="bi ${pajakInfo.icon || 'bi-info-circle'}"></i> ${pajakInfo.message || 'Tidak ada info'}
                </small>
            `;
        }
        
        return `
            <tr class="search-result-row">
                <td class="px-4 py-3">${item.id_transaksi || '-'}</td>
                <td class="px-4 py-3">${item.tanggal || '-'}</td>
                <td class="px-4 py-3">${item.kegiatan || '-'}</td>
                <td class="px-4 py-3">${item.uraian_opsional || item.rekening_belanja || '-'}</td>
                <td class="px-4 py-3">${item.jenis_transaksi || '-'}</td>
                <td class="px-4 py-3">Rp ${item.anggaran || '0'}</td>
                <td class="px-4 py-3">Rp ${item.dibelanjakan || '0'}</td>
                <td class="px-4 py-3">${pajakHtml}</td>
                <td class="px-4 py-3 text-center">${item.actions || ''}</td>
            </tr>
        `;
    }

    // Update RKAS Table Update untuk handle tab bulan
    updateRkasTable(data, searchTerm) {
        if (!this.currentRkasMonth) {
            this.setupRkasTabDetection();
        }
        
        // Cari tabel body untuk bulan yang aktif
        const tableBody = document.getElementById(`table-body-${this.currentRkasMonth}`);
        
        if (!tableBody) {
            this.showError(`Tabel RKAS untuk bulan ${this.currentRkasMonth} tidak ditemukan`);
            return;
        }

        this.saveOriginalContent(`rkas-${this.currentRkasMonth}`, tableBody.innerHTML);

        if (data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan data RKAS dengan kata kunci: "<strong>${searchTerm}</strong>" di bulan ${this.currentRkasMonth}
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            data.forEach(item => {
                html += this.renderRkasRow(item);
            });
            tableBody.innerHTML = html;
        }

        this.showSearchInfo(data.length, searchTerm, 'rkas');
        this.reattachEventListeners();
    }

    renderRkasRow(item) {
        return `
            <tr class="search-result-row">
                <td class="px-4 py-3">${item.index || '-'}</td>
                <td class="px-4 py-3">${item.program || '-'}</td>
                <td class="px-4 py-3">${item.sub_program || '-'}</td>
                <td class="px-4 py-3">${item.rincian_objek || '-'}</td>
                <td class="px-4 py-3">${item.uraian || '-'}</td>
                <td class="px-4 py-3 text-end">${item.dianggarkan || '0'}</td>
                <td class="px-4 py-3 text-end">${item.dibelanjakan || '0'}</td>
                <td class="px-4 py-3">${item.satuan || '-'}</td>
                <td class="px-4 py-3 text-end">${item.harga_satuan || '0'}</td>
                <td class="px-4 py-3 text-end">${item.total || '0'}</td>
                <td class="px-4 py-3 text-center">${item.actions || ''}</td>
            </tr>
        `;
    }

    // RKAS Perubahan Table Update
    updateRkasPerubahanTable(data, searchTerm) {
        if (!this.currentRkasPerubahanMonth) {
            this.setupRkasTabDetection();
        }
        
        // Cari tabel body untuk bulan yang aktif
        const tableBody = document.getElementById(`table-body-${this.currentRkasPerubahanMonth}`);
        
        if (!tableBody) {
            this.showError(`Tabel RKAS Perubahan untuk bulan ${this.currentRkasPerubahanMonth} tidak ditemukan`);
            return;
        }

        this.saveOriginalContent(`rkas-perubahan-${this.currentRkasPerubahanMonth}`, tableBody.innerHTML);

        if (data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan data RKAS Perubahan dengan kata kunci: "<strong>${searchTerm}</strong>" di bulan ${this.currentRkasPerubahanMonth}
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            data.forEach(item => {
                html += this.renderRkasPerubahanRow(item);
            });
            tableBody.innerHTML = html;
        }

        this.showSearchInfo(data.length, searchTerm, 'rkas-perubahan');
        this.reattachEventListeners();
    }

    renderRkasPerubahanRow(item) {
        return `
            <tr class="search-result-row">
                <td class="px-4 py-3">${item.index || '-'}</td>
                <td class="px-4 py-3">${item.program || '-'}</td>
                <td class="px-4 py-3">${item.sub_program || '-'}</td>
                <td class="px-4 py-3">${item.rincian_objek || '-'}</td>
                <td class="px-4 py-3">${item.uraian || '-'}</td>
                <td class="px-4 py-3 text-end">${item.dianggarkan || '0'}</td>
                <td class="px-4 py-3 text-end">${item.dibelanjakan || '0'}</td>
                <td class="px-4 py-3">${item.satuan || '-'}</td>
                <td class="px-4 py-3 text-end">${item.harga_satuan || '0'}</td>
                <td class="px-4 py-3 text-end">${item.total || '0'}</td>
                <td class="px-4 py-3 text-center">${item.actions || ''}</td>
            </tr>
        `;
    }

    // Kegiatan Table Update
    updateKegiatanTable(data, searchTerm) {
        const tableBody = document.getElementById('kegiatanTableBody');
        if (!tableBody) {
            this.showError('Tabel Kegiatan tidak ditemukan');
            return;
        }

        this.saveOriginalContent('kegiatanTableBody', tableBody.innerHTML);

        if (data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan kegiatan dengan kata kunci: "<strong>${searchTerm}</strong>"
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            data.forEach(item => {
                html += this.renderKegiatanRow(item);
            });
            tableBody.innerHTML = html;
        }

        this.showSearchInfo(data.length, searchTerm, 'kegiatan');
        this.reattachEventListeners();
    }

    renderKegiatanRow(item) {
        return `
            <tr class="search-result-row">
                <td class="px-4 py-3">${item.index || '-'}</td>
                <td class="px-4 py-3">${item.kode || '-'}</td>
                <td class="px-4 py-3">${item.program || '-'}</td>
                <td class="px-4 py-3">${item.sub_program || '-'}</td>
                <td class="px-4 py-3">${item.uraian || '-'}</td>
                <td class="px-4 py-3 text-center">${item.actions || ''}</td>
            </tr>
        `;
    }

    // Rekening Table Update
    updateRekeningTable(data, searchTerm) {
        const tableBody = document.getElementById('rekeningTableBody');
        if (!tableBody) {
            this.showError('Tabel Rekening Belanja tidak ditemukan');
            return;
        }

        this.saveOriginalContent('rekeningTableBody', tableBody.innerHTML);

        if (data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="100%" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan rekening belanja dengan kata kunci: "<strong>${searchTerm}</strong>"
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            data.forEach(item => {
                html += this.renderRekeningRow(item);
            });
            tableBody.innerHTML = html;
        }

        this.showSearchInfo(data.length, searchTerm, 'rekening');
        this.reattachEventListeners();
    }

    renderRekeningRow(item) {
        return `
            <tr class="search-result-row">
                <td class="px-4 py-3">${item.index || '-'}</td>
                <td class="px-4 py-3">${item.kode_rekening || '-'}</td>
                <td class="px-4 py-3">${item.rincian_objek || '-'}</td>
                <td class="px-4 py-3">${item.kategori || '-'}</td>
                <td class="px-4 py-3 text-center">${item.actions || ''}</td>
            </tr>
        `;
    }

    // Penganggaran Table Update (Template)
    updatePenganggaranTable(data, searchTerm) {
        const tableBody = document.getElementById('penganggaranTableBody');
        if (!tableBody) {
            this.showError('Tabel Penganggaran tidak ditemukan');
            return;
        }

        this.saveOriginalContent('penganggaranTableBody', tableBody.innerHTML);

        if (data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="100%" class="text-center py-5 text-muted">
                        <i class="bi bi-search me-2"></i>
                        Tidak ditemukan data penganggaran dengan kata kunci: "<strong>${searchTerm}</strong>"
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            data.forEach(item => {
                html += this.renderPenganggaranRow(item);
            });
            tableBody.innerHTML = html;
        }

        this.showSearchInfo(data.length, searchTerm, 'penganggaran');
        this.reattachEventListeners();
    }

    renderPenganggaranRow(item) {
        return `
            <tr class="search-result-row">
                <td class="px-4 py-3">${item.tahun_anggaran || '-'}</td>
                <td class="px-4 py-3">${item.nama_sekolah || '-'}</td>
                <td class="px-4 py-3">${item.status || '-'}</td>
                <td class="px-4 py-3 text-center">${item.actions || ''}</td>
            </tr>
        `;
    }

    // Default Table Update
    updateDefaultTable(data, searchTerm) {
        console.log('Default table update:', data);
        // Implementasi default handler jika diperlukan
    }

    saveOriginalContent(tableId, content) {
        if (!this.originalContents.has(tableId)) {
            this.originalContents.set(tableId, content);
        }
    }

    showSearchInfo(count, searchTerm, type) {
        this.removeSearchInfo();
        
        const typeLabel = this.getTypeLabel(type);
        
        if (count === 0) {
            // Notifikasi untuk hasil kosong
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Tidak ditemukan ${typeLabel} dengan kata kunci: "${searchTerm}"`,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#f8f9fa',
                iconColor: '#6c757d',
                customClass: {
                    popup: 'search-toast'
                }
            });
        } else {
            // Notifikasi untuk hasil ditemukan
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Ditemukan ${count} ${typeLabel}`,
                text: `Kata kunci: "${searchTerm}"`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#d1e7dd',
                iconColor: '#198754',
                customClass: {
                    popup: 'search-toast success'
                }
            });
        }
    }

    getTypeLabel(type) {
        const labels = {
            'bku': 'transaksi BKU',
            'rkas': 'data RKAS',
            'rkas-perubahan': 'data RKAS Perubahan',
            'kegiatan': 'kegiatan',
            'rekening': 'rekening belanja',
            'penganggaran': 'data penganggaran'
        };
        return labels[type] || 'data';
    }

    removeSearchInfo() {
        const existingInfo = document.querySelector('.search-result-info');
        if (existingInfo) {
            existingInfo.remove();
        }
    }

    showLoading() {
        const loadingElement = document.querySelector('.search-loading');
        if (loadingElement) {
            loadingElement.classList.remove('d-none');
        }
        
        // Tambahkan loading state ke input
        const searchInput = document.getElementById('globalSearchInput');
        if (searchInput) {
            searchInput.setAttribute('readonly', 'readonly');
        }
    }

    hideLoading() {
        const loadingElement = document.querySelector('.search-loading');
        if (loadingElement) {
            loadingElement.classList.add('d-none');
        }
        
        // Hapus loading state dari input
        const searchInput = document.getElementById('globalSearchInput');
        if (searchInput) {
            searchInput.removeAttribute('readonly');
        }
    }

    showError(message) {
        this.removeSearchInfo();
        
        Swal.fire({
            title: '❌ Error Pencarian',
            html: `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="fw-bold text-danger">${message}</p>
                    <small class="text-muted">Silakan coba lagi atau refresh halaman</small>
                </div>
            `,
            icon: 'error',
            showConfirmButton: true,
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#dc3545',
            customClass: {
                popup: 'search-result-popup error'
            }
        });
        
        console.error('Search Error:', message);
    }

    resetSearch() {
        console.log('Resetting search for RKAS...');
        
        if (this.currentPage === 'rkas' && this.currentRkasMonth) {
            // Reset hanya tab yang aktif
            const tableBody = document.getElementById(`table-body-${this.currentRkasMonth}`);
            const originalContent = this.originalContents.get(`rkas-${this.currentRkasMonth}`);
            
            if (tableBody && originalContent) {
                tableBody.innerHTML = originalContent;
            }
            
            // Clear hanya cache untuk tab ini
            this.originalContents.delete(`rkas-${this.currentRkasMonth}`);
        } else {
            // Reset untuk halaman lain
            this.originalContents.forEach((content, tableId) => {
                const tableBody = document.getElementById(tableId);
                if (tableBody) {
                    tableBody.innerHTML = content;
                }
            });
            this.originalContents.clear();
        }

        // Hapus info pencarian
        this.removeSearchInfo();
        
        // Re-attach event listeners
        this.reattachEventListeners();
        
        document.dispatchEvent(new CustomEvent('searchReset', {
            detail: { 
                page: this.currentPage,
                month: this.currentRkasMonth 
            }
        }));
    }

    reattachEventListeners() {
        // Trigger custom event untuk re-attach listeners
        document.dispatchEvent(new CustomEvent('searchResultsUpdated', {
            detail: { 
                page: this.currentPage,
                searchTerm: this.currentSearchTerm
            }
        }));
        
        console.log('Event listeners reattached for:', this.currentPage);
    }
}

// Initialize global search ketika DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.globalSearch = new GlobalSearch();
    
    // Global event untuk handle dynamic content
    document.addEventListener('searchResultsUpdated', function(e) {
        console.log('Search results updated for:', e.detail.page);
        
        // Event ini bisa digunakan oleh masing-masing halaman untuk re-attach listeners spesifik
        if (typeof window.attachBkuEventListeners === 'function' && e.detail.page === 'bku') {
            window.attachBkuEventListeners();
        }
        if (typeof window.attachRkasEventListeners === 'function' && e.detail.page === 'rkas') {
            window.attachRkasEventListeners();
        }
        if (typeof window.attachKegiatanEventListeners === 'function' && e.detail.page === 'kegiatan') {
            window.attachKegiatanEventListeners();
        }
        if (typeof window.attachRekeningEventListeners === 'function' && e.detail.page === 'rekening') {
            window.attachRekeningEventListeners();
        }
    });
    
    document.addEventListener('searchReset', function(e) {
        console.log('Search reset for:', e.detail.page);
        
        // Event ini bisa digunakan oleh masing-masing halaman untuk cleanup
        if (typeof window.attachBkuEventListeners === 'function' && e.detail.page === 'bku') {
            window.attachBkuEventListeners();
        }
        if (typeof window.attachRkasEventListeners === 'function' && e.detail.page === 'rkas') {
            window.attachRkasEventListeners();
        }
        if (typeof window.attachKegiatanEventListeners === 'function' && e.detail.page === 'kegiatan') {
            window.attachKegiatanEventListeners();
        }
        if (typeof window.attachRekeningEventListeners === 'function' && e.detail.page === 'rekening') {
            window.attachRekeningEventListeners();
        }
    });
});

// Export untuk module system (jika menggunakan Vite)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GlobalSearch;
}