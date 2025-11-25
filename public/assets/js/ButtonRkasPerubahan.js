/**
 * ButtonRkasPerubahan Manager Class
 * Mengelola semua fungsi dan logika untuk tombol RKAS Perubahan
 */
class ButtonRkasPerubahan {
    constructor() {
        this.isProcessing = false;
        this.hasPerubahanFromServer = false;
        console.log('=== BUTTON RKAS PERUBAHAN MANAGER INITIALIZED ===');
    }

    // ========== MAIN FUNCTIONS ==========

    /**
     * Menonaktifkan tabel dan tombol setelah perubahan
     */
    disableRkasAfterPerubahan() {
        // Nonaktifkan semua tabel
        const tableContainers = document.querySelectorAll('.rkas-table-container');
        tableContainers.forEach(container => {
            container.classList.add('table-disabled');
        });
        
        // Sembunyikan tombol tambah
        const btnTambah = document.getElementById('btnTambah');
        if (btnTambah) {
            btnTambah.style.display = 'none';
        }
        
        // Nonaktifkan semua dropdown aksi
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.disabled = true;
            toggle.style.pointerEvents = 'none';
            toggle.style.opacity = '0.6';
        });

        console.log('✅ [PERUBAHAN] RKAS dinonaktifkan setelah perubahan');
    }

    /**
     * Menonaktifkan tombol perubahan
     */
    disablePerubahanButton() {
        const btnPerubahan = document.getElementById('btnPerubahan');
        if (btnPerubahan && !btnPerubahan.disabled) {
            btnPerubahan.disabled = true;
            btnPerubahan.classList.add('disabled');
            btnPerubahan.title = 'RKAS Perubahan sudah dilakukan';
            
            // Tambahkan tooltip
            btnPerubahan.setAttribute('data-bs-toggle', 'tooltip');
            btnPerubahan.setAttribute('data-bs-placement', 'top');
            btnPerubahan.setAttribute('data-bs-title', 'Mohon Maaf Anda Telah Melakukan Perubahan, RKAS Perubahan hanya dapat dilakukan sekali, Terima Kasih Atas Antusias Anda');
            
            // Inisialisasi tooltip Bootstrap
            const existingTooltip = bootstrap.Tooltip.getInstance(btnPerubahan);
            if (existingTooltip) {
                existingTooltip.dispose();
            }
            new bootstrap.Tooltip(btnPerubahan);
            
            // Simpan status di localStorage
            const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
            if (tahun) {
                localStorage.setItem('rkas_perubahan_done_' + tahun, 'true');
            }
            
            console.log('✅ [PERUBAHAN] Tombol perubahan dinonaktifkan');
        }
    }

    /**
     * Mengaktifkan tombol perubahan
     */
    enablePerubahanButton() {
        const btnPerubahan = document.getElementById('btnPerubahan');
        if (btnPerubahan && btnPerubahan.disabled) {
            btnPerubahan.disabled = false;
            btnPerubahan.classList.remove('disabled');
            btnPerubahan.removeAttribute('title');
            btnPerubahan.removeAttribute('data-bs-toggle');
            btnPerubahan.removeAttribute('data-bs-placement');
            btnPerubahan.removeAttribute('data-bs-title');
            
            // Hapus tooltip jika ada
            const tooltip = bootstrap.Tooltip.getInstance(btnPerubahan);
            if (tooltip) {
                tooltip.dispose();
            }
            
            // Hapus status dari localStorage
            const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
            if (tahun) {
                localStorage.removeItem('rkas_perubahan_done_' + tahun);
            }

            console.log('✅ [PERUBAHAN] Tombol perubahan diaktifkan');
        }
    }

    /**
     * Sync status dengan server
     */
    syncPerubahanStatus() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        
        if (!tahun) {
            console.warn('⚠️ [PERUBAHAN] Tahun anggaran tidak ditemukan');
            return;
        }

        console.log('🔍 [PERUBAHAN] Syncing status for year:', tahun);

        // Cek status dari server untuk memastikan sync
        fetch(`/rkas-perubahan/check-status?tahun=${tahun}`, {
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
                if (data.has_perubahan) {
                    // Jika server mengatakan sudah ada perubahan, disable tombol dan tabel
                    this.disablePerubahanButton();
                    this.disableRkasAfterPerubahan();
                    this.hasPerubahanFromServer = true;
                    console.log('✅ [PERUBAHAN] Status sync: PERUBAHAN SUDAH DILAKUKAN');
                } else {
                    // Jika server mengatakan belum ada perubahan, enable tombol
                    this.enablePerubahanButton();
                    this.hasPerubahanFromServer = false;
                    console.log('✅ [PERUBAHAN] Status sync: BELUM ADA PERUBAHAN');
                }
            } else {
                throw new Error(data.message || 'Gagal memeriksa status perubahan');
            }
        })
        .catch(error => {
            console.error('❌ [PERUBAHAN] Error syncing perubahan status:', error);
            // Fallback: gunakan localStorage status
            const isDoneInLocalStorage = localStorage.getItem('rkas_perubahan_done_' + tahun) === 'true';
            if (isDoneInLocalStorage) {
                this.disablePerubahanButton();
                this.disableRkasAfterPerubahan();
                this.hasPerubahanFromServer = true;
            } else {
                this.enablePerubahanButton();
                this.hasPerubahanFromServer = false;
            }
        });
    }

    /**
     * Handle form submission dengan AJAX
     */
    handlePerubahanSubmission(event) {
        event.preventDefault();
        
        if (this.isProcessing) {
            console.log('⚠️ [PERUBAHAN] Request already in progress');
            return;
        }

        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        
        if (!tahun) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Tahun anggaran tidak ditemukan',
                confirmButtonText: 'OK'
            });
            return;
        }

        console.log('🔍 [PERUBAHAN] Checking status for year:', tahun);
        
        // Cek status perubahan terlebih dahulu sebelum menampilkan konfirmasi
        this.isProcessing = true;
        
        fetch(`/rkas-perubahan/check-status?tahun=${tahun}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('🔍 [PERUBAHAN] Response status:', response.status);
            
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('🔍 [PERUBAHAN] Response data:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Gagal memeriksa status perubahan');
            }
            
            if (data.has_perubahan) {
                // Jika sudah ada perubahan, tampilkan warning langsung
                Swal.fire({
                    icon: 'warning',
                    title: 'Perubahan Sudah Dilakukan',
                    text: 'Mohon Maaf Anda Telah Melakukan Perubahan, RKAS Perubahan hanya dapat dilakukan sekali, Terima Kasih Atas Antusias Anda',
                    confirmButtonText: 'Mengerti'
                });
                
                // Nonaktifkan tombol dan tabel
                this.disablePerubahanButton();
                this.disableRkasAfterPerubahan();
                this.hasPerubahanFromServer = true;
                
            } else {
                // Jika belum ada perubahan, tampilkan konfirmasi
                this.showPerubahanConfirmation();
            }
        })
        .catch(error => {
            console.error('❌ [PERUBAHAN] Error checking perubahan status:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memeriksa status perubahan: ' + error.message,
                confirmButtonText: 'OK'
            });
        })
        .finally(() => {
            this.isProcessing = false;
        });
    }

    /**
     * Menampilkan konfirmasi perubahan
     */
    showPerubahanConfirmation() {
        const tahun = document.querySelector('input[name="tahun_anggaran"]')?.value;
        
        Swal.fire({
            title: 'Konfirmasi Perubahan',
            html: `
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <p class="mt-3"><strong>Apakah Anda yakin ingin membuat RKAS Perubahan?</strong></p>
                    <p>Data RKAS saat ini akan disalin ke RKAS Perubahan.</p>
                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan. RKAS Perubahan hanya dapat dilakukan sekali.
                        </small>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check me-2"></i>Ya, Buat Perubahan',
            cancelButtonText: '<i class="bi bi-x me-2"></i>Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true,
            backdrop: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    // Submit form menggunakan AJAX
                    const formData = new FormData(document.getElementById('perubahanForm'));
                    
                    const response = await fetch('/rkas-perubahan/salin', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    // Handle response properly
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const data = await response.json();
                        
                        if (data.success) {
                            return data;
                        } else {
                            // Tampilkan error message dari server
                            throw new Error(data.message || 'Gagal membuat perubahan');
                        }
                    } else {
                        // Handle non-JSON response
                        const text = await response.text();
                        throw new Error(`Unexpected response: ${text}`);
                    }
                } catch (error) {
                    Swal.showValidationMessage(
                        `Error: ${error.message}`
                    );
                    return false;
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value && result.value.success) {
                    // Nonaktifkan tombol dan tabel setelah berhasil
                    this.disableRkasAfterPerubahan();
                    this.disablePerubahanButton();
                    this.hasPerubahanFromServer = true;
                
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `
                            <div class="text-center">
                                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                                <p class="mt-3">${result.value.message || 'Data RKAS berhasil disalin ke RKAS Perubahan.'}</p>
                                <div class="alert alert-success mt-3">
                                    <small>
                                        <i class="bi bi-check2-circle me-1"></i>
                                        Anda akan diarahkan ke halaman RKAS Perubahan
                                    </small>
                                </div>
                            </div>
                        `,
                        confirmButtonText: '<i class="bi bi-arrow-right me-2"></i>Lanjutkan',
                        confirmButtonColor: '#198754',
                        allowOutsideClick: false
                    }).then(() => {
                        // Redirect ke halaman RKAS Perubahan
                        if (result.value.redirect) {
                            window.location.href = result.value.redirect;
                        } else {
                            // Fallback redirect
                            window.location.href = '/rkas-perubahan';
                        }
                    });
                }
            } else {
                console.log('🔧 [PERUBAHAN] User cancelled perubahan');
            }
        });
    }

    /**
     * Handle pesan flash dari server
     */
    handleFlashMessages() {
        // Cek elemen hidden yang mungkin berisi data dari server
        const perubahanStatusElement = document.getElementById('perubahan-status');
        if (perubahanStatusElement && perubahanStatusElement.value === 'done') {
            console.log('🔍 [PERUBAHAN] Found perubahan status from hidden field');
            this.disablePerubahanButton();
            this.disableRkasAfterPerubahan();
            this.hasPerubahanFromServer = true;
        }

        // Cek data attribute dari body atau container
        const bodyElement = document.body;
        const hasPerubahanAttr = bodyElement.getAttribute('data-has-perubahan');
        if (hasPerubahanAttr === 'true') {
            console.log('🔍 [PERUBAHAN] Found perubahan status from data attribute');
            this.disablePerubahanButton();
            this.disableRkasAfterPerubahan();
            this.hasPerubahanFromServer = true;
        }
    }

    /**
     * Cek apakah berada di halaman RKAS
     */
    isRkasPage() {
        const path = window.location.pathname;
        return path.includes('/rkas') && !path.includes('/rkas-perubahan');
    }

    /**
     * Cek status perubahan dari PHP variable (via data attribute)
     */
    checkPhpVariableStatus() {
        // Cek dari data attribute di body
        const bodyElement = document.body;
        const hasPerubahan = bodyElement.getAttribute('data-has-perubahan');
        
        if (hasPerubahan === 'true') {
            console.log('🔍 [PERUBAHAN] PHP variable indicates perubahan has been done');
            this.disableRkasAfterPerubahan();
            this.disablePerubahanButton();
            this.hasPerubahanFromServer = true;
            return true;
        }
        
        return false;
    }

    /**
     * Initialize semua komponen
     */
    initialize() {
        console.log('=== BUTTON RKAS PERUBAHAN MANAGER INITIALIZING ===');
        
        // Cek apakah kita berada di halaman RKAS
        if (!this.isRkasPage()) {
            console.log('⚠️ [PERUBAHAN] Not on RKAS page, skipping initialization');
            return;
        }

        // Setup event listener untuk form perubahan
        const perubahanForm = document.getElementById('perubahanForm');
        if (perubahanForm) {
            perubahanForm.addEventListener('submit', this.handlePerubahanSubmission.bind(this));
            console.log('✅ [PERUBAHAN] Form event listener attached');
        } else {
            console.error('❌ [PERUBAHAN] Perubahan form not found');
        }
        
        // Cek status dari PHP variable terlebih dahulu
        const hasPhpStatus = this.checkPhpVariableStatus();
        
        // Jika tidak ada status dari PHP, sync dengan server
        if (!hasPhpStatus) {
            this.syncPerubahanStatus();
        }
        
        // Handle flash messages
        this.handleFlashMessages();

        console.log('=== BUTTON RKAS PERUBAHAN MANAGER INITIALIZATION COMPLETE ===');
    }

    /**
     * Public method untuk sync status (bisa dipanggil dari luar)
     */
    syncStatus() {
        this.syncPerubahanStatus();
    }

    /**
     * Public method untuk mendapatkan status perubahan
     */
    getPerubahanStatus() {
        return this.hasPerubahanFromServer;
    }
}

// Export class untuk penggunaan global
window.ButtonRkasPerubahan = ButtonRkasPerubahan;