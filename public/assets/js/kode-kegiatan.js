// kode-kegiatan.js
class KodeKegiatanManager {
    constructor() {
        this.init();
    }

    init() {
        console.log('🔄 Initializing KodeKegiatanManager...');
        this.initializeForms();
        this.initializeDeleteButtons();
        this.initializeSearch();
        this.initializeFileUpload();
        console.log('✅ KodeKegiatanManager initialized');
    }

    initializeForms() {
        // Handle form tambah
        const tambahForm = document.querySelector('#tambahModal form');
        if (tambahForm) {
            console.log('✅ Found create form');
            this.setupForm(tambahForm, 'create');
        }

        // Handle form edit - perbaiki selector untuk form edit
        const editForms = document.querySelectorAll('form[action*="kode-kegiatan"]');
        editForms.forEach(form => {
            if (form.method === 'POST' && form.querySelector('input[name="_method"][value="PUT"]')) {
                console.log('✅ Found update form');
                this.setupForm(form, 'update');
            }
        });

        // Handle form import
        const importForm = document.querySelector('#importModal form');
        if (importForm) {
            console.log('✅ Found import form');
            this.setupForm(importForm, 'import');
        }
    }

    setupForm(form, type) {
        // Hapus event listener lama
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        // Tambah event listener baru
        newForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleFormSubmit(newForm, type);
        });

        console.log(`✅ Form ${type} setup completed`);
    }

    async handleFormSubmit(form, type) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton ? submitButton.innerHTML : '';

        try {
            console.log(`🚀 ${type.toUpperCase()} form submission started`);

            // Show loading state
            if (submitButton) {
                this.showLoadingState(submitButton, type);
            }

            // Validasi untuk form create dan update
            if ((type === 'create' || type === 'update') && !this.validateForm(form)) {
                if (submitButton) {
                    this.resetButtonState(submitButton, originalText);
                }
                return;
            }

            let response;
            
            if (type === 'import') {
                // Handle import dengan FormData
                const formData = new FormData(form);
                response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } else {
                // Handle create dan update
                const formData = new FormData(form);
                
                // Untuk update, tambahkan method spoofing
                if (type === 'update') {
                    formData.append('_method', 'PUT');
                }

                response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
            }

            console.log(`📥 ${type.toUpperCase()} response status:`, response.status);

            const contentType = response.headers.get('content-type');
            let result;

            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                // Jika response bukan JSON, anggap success dan reload
                if (response.ok) {
                    this.handleFormSuccess(type, { success: true, message: 'Operation completed successfully' }, form);
                    return;
                } else {
                    throw new Error(`Server error: ${response.status}`);
                }
            }

            if (response.ok && result.success) {
                console.log(`✅ ${type.toUpperCase()} success:`, result);
                this.handleFormSuccess(type, result, form);
            } else {
                let errorMessage = result.message || `Server error: ${response.status}`;
                
                // Handle validation errors
                if (result.errors) {
                    errorMessage = this.formatValidationErrors(result.errors);
                }
                
                throw new Error(errorMessage);
            }

        } catch (error) {
            console.error(`❌ ${type.toUpperCase()} failed:`, error);
            this.handleFormError(type, error, submitButton, originalText);
        }
    }

    formatValidationErrors(errors) {
        let message = 'Validasi gagal:\n';
        for (const field in errors) {
            message += `- ${errors[field].join(', ')}\n`;
        }
        return message;
    }

    handleFormSuccess(type, data, form) {
        // Tutup modal berdasarkan type
        const modalId = type === 'create' ? 'tambahModal' : 
                       type === 'import' ? 'importModal' : null;
        
        if (modalId) {
            this.closeModalById(modalId);
        } else {
            // Untuk update, tutup modal yang aktif
            this.closeActiveModal();
        }

        // Reset form
        if (form && type !== 'update') {
            form.reset();
        }

        // Tampilkan success message
        const successTitle = type === 'create' ? 'Data Berhasil Disimpan' :
                           type === 'update' ? 'Data Berhasil Diperbarui' :
                           'Import Berhasil';

        Swal.fire({
            icon: 'success',
            title: successTitle,
            text: data.message || 'Operasi berhasil dilakukan',
            confirmButtonColor: '#198754',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.reload();
        });
    }

    handleFormError(type, error, button, originalText) {
        if (button) {
            this.resetButtonState(button, originalText);
        }
        
        const errorTitle = type === 'create' ? 'Gagal Menyimpan' :
                          type === 'update' ? 'Gagal Memperbarui' :
                          'Import Gagal';

        Swal.fire({
            icon: 'error',
            title: errorTitle,
            html: error.message.replace(/\n/g, '<br>') || 'Terjadi kesalahan. Silakan coba lagi.',
            confirmButtonColor: '#dc3545'
        });
    }

    initializeDeleteButtons() {
        console.log('🔄 Initializing delete buttons...');
        
        // Event delegation untuk tombol hapus
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-delete')) {
                e.preventDefault();
                this.handleDeleteClick(e.target.closest('.btn-delete'));
            }
        });
    }

    handleDeleteClick(button) {
        const kegiatanId = button.getAttribute('data-id');
        const kode = button.getAttribute('data-kode');
        const program = button.getAttribute('data-program');
        const subProgram = button.getAttribute('data-sub-program');
        const uraian = button.getAttribute('data-uraian');

        if (!kegiatanId) {
            console.error('❌ No kegiatan ID found');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'ID kegiatan tidak ditemukan'
            });
            return;
        }

        this.showDeleteConfirmation(kegiatanId, kode, program, subProgram, uraian);
    }

    showDeleteConfirmation(kegiatanId, kode, program, subProgram, uraian) {
        const itemDescription = `${kode} - ${program}`;

        Swal.fire({
            title: 'Apakah Anda yakin?',
            html: `
                <div class="text-start">
                    <p>Anda akan menghapus kegiatan berikut:</p>
                    <div class="bg-light p-3 rounded mt-2 mb-3">
                        <strong>${itemDescription}</strong>
                        ${subProgram ? `<div class="mt-1"><small>Sub Program: ${subProgram}</small></div>` : ''}
                        ${uraian ? `<div class="mt-1"><small>Uraian: ${uraian}</small></div>` : ''}
                    </div>
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Data yang dihapus tidak dapat dikembalikan!
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const response = await fetch(`/referensi/kode-kegiatan/${kegiatanId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            return { success: true };
                        } else {
                            throw new Error(result.message || 'Delete failed');
                        }
                    } else {
                        throw new Error(`HTTP ${response.status}`);
                    }
                } catch (error) {
                    Swal.showValidationMessage(`Hapus gagal: ${error.message}`);
                    return false;
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus!',
                    text: 'Data berhasil dihapus.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else if (result.isDismissed) {
                // User cancelled, do nothing
                console.log('Delete cancelled by user');
            }
        });
    }

    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        // Reset validasi sebelumnya
        requiredFields.forEach(field => {
            field.classList.remove('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        });

        // Validasi field required
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Tambah pesan error jika belum ada
                if (!field.parentNode.querySelector('.invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Field ini wajib diisi';
                    field.parentNode.appendChild(errorDiv);
                }
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Harap lengkapi semua field yang wajib diisi',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }

        return true;
    }

    initializeSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let timeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const searchTerm = e.target.value.trim();
                    if (searchTerm.length >= 2 || searchTerm.length === 0) {
                        this.performSearch(searchTerm);
                    }
                }, 500);
            });
        }
    }

    async performSearch(searchTerm) {
        try {
            const response = await fetch(`/kegiatan/search?search=${encodeURIComponent(searchTerm)}`);
            const data = await response.json();
            
            if (data.success) {
                this.updateTable(data.data);
            } else {
                console.error('Search failed:', data.message);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    updateTable(data) {
        const tbody = document.getElementById('kegiatanTableBody');
        if (!tbody) return;

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-search me-2"></i>Tidak ada data ditemukan
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map(item => `
            <tr>
                <td>${item.index}</td>
                <td>${item.kode}</td>
                <td>${item.program}</td>
                <td>${item.sub_program}</td>
                <td>${item.uraian}</td>
                <td>${item.actions}</td>
            </tr>
        `).join('');
    }

    // FILE UPLOAD FUNCTIONALITY
    initializeFileUpload() {
        console.log('🔄 Initializing file upload...');
        
        const fileInput = document.getElementById('file');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const uploadPreview = document.getElementById('uploadPreview');
        const importForm = document.getElementById('importForm');

        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.handleFileSelect(e);
            });

            // Drag and drop functionality
            const uploadArea = document.querySelector('.file-upload-area .border-dashed');
            if (uploadArea) {
                this.setupDragAndDrop(uploadArea, fileInput);
            }
        }

        if (importForm) {
            importForm.addEventListener('submit', (e) => {
                this.handleImportSubmit(e);
            });
        }

        // Reset file input ketika modal import ditutup
        const importModal = document.getElementById('importModal');
        if (importModal) {
            importModal.addEventListener('hidden.bs.modal', () => {
                this.clearFile();
            });
        }
    }

    handleFileSelect(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validasi file type
        const validTypes = ['application/vnd.ms-excel', 
                          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                          'text/csv'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const isValidType = validTypes.includes(file.type) || 
                          ['xlsx', 'xls', 'csv'].includes(fileExtension);

        if (!isValidType) {
            Swal.fire({
                icon: 'error',
                title: 'Format File Tidak Valid',
                html: `
                    <div class="text-start">
                        <p>File <strong>${file.name}</strong> tidak didukung.</p>
                        <p class="small text-muted">Format yang didukung: .xlsx, .xls, .csv</p>
                    </div>
                `,
                confirmButtonColor: '#dc3545'
            });
            this.clearFile();
            return;
        }

        // Validasi file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                html: `
                    <div class="text-start">
                        <p>File <strong>${file.name}</strong> melebihi batas ukuran.</p>
                        <p class="small text-muted">Ukuran file: ${this.formatFileSize(file.size)}<br>Maksimal: 2MB</p>
                    </div>
                `,
                confirmButtonColor: '#dc3545'
            });
            this.clearFile();
            return;
        }

        // Tampilkan preview dengan info lengkap
        this.showFilePreview(file);
    }

    setupDragAndDrop(uploadArea, fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => this.highlight(uploadArea), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => this.unhighlight(uploadArea), false);
        });

        uploadArea.addEventListener('drop', (e) => {
            this.handleDrop(e, fileInput);
        }, false);
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    highlight(element) {
        element.classList.add('bg-primary', 'bg-opacity-10', 'border-primary');
    }

    unhighlight(element) {
        element.classList.remove('bg-primary', 'bg-opacity-10', 'border-primary');
    }

    handleDrop(e, fileInput) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        const event = new Event('change');
        fileInput.dispatchEvent(event);
    }

    showFilePreview(file) {
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const uploadPreview = document.getElementById('uploadPreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileType = document.getElementById('fileType');
        const fileInfoSummary = document.getElementById('fileInfoSummary');
        const infoFileName = document.getElementById('infoFileName');
        const infoFileSize = document.getElementById('infoFileSize');
        const infoFileType = document.getElementById('infoFileType');
        const fileReadyText = document.getElementById('fileReadyText');

        // Update main preview
        fileName.textContent = file.name;
        fileSize.textContent = this.formatFileSize(file.size);
        fileType.textContent = this.getFileTypeDescription(file);
        
        // Update info summary
        infoFileName.textContent = this.truncateFileName(file.name, 20);
        infoFileSize.textContent = this.formatFileSize(file.size);
        infoFileType.textContent = this.getFileExtension(file.name).toUpperCase();
        
        // Update ready text
        fileReadyText.textContent = `File "${this.truncateFileName(file.name, 30)}" siap diimport.`;
        
        // Show elements
        uploadPlaceholder.classList.add('d-none');
        uploadPreview.classList.remove('d-none');
        fileInfoSummary.classList.remove('d-none');
        
        // Add success animation
        this.addAnimation(uploadPreview, 'animate__fadeIn');
    }

    handleImportSubmit(e) {
        const fileInput = document.getElementById('file');
        const file = fileInput.files[0];
        
        if (!file) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Harap pilih file Excel terlebih dahulu',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        const btnImport = document.getElementById('btnImport');
        if (btnImport) {
            btnImport.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Mengimport ${this.truncateFileName(file.name, 15)}...
            `;
            btnImport.disabled = true;
        }
    }

    clearFile() {
        const fileInput = document.getElementById('file');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const uploadPreview = document.getElementById('uploadPreview');
        const fileInfoSummary = document.getElementById('fileInfoSummary');
        
        if (fileInput) fileInput.value = '';
        if (uploadPlaceholder) uploadPlaceholder.classList.remove('d-none');
        if (uploadPreview) uploadPreview.classList.add('d-none');
        if (fileInfoSummary) fileInfoSummary.classList.add('d-none');
        
        // Add reset animation
        if (uploadPlaceholder) {
            this.addAnimation(uploadPlaceholder, 'animate__fadeIn');
        }
    }

    // Utility methods
    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    showLoadingState(button, type) {
        const loadingText = type === 'create' ? 'Menyimpan data...' : 
                          type === 'update' ? 'Memperbarui data...' : 
                          'Mengimport data...';
        
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            ${loadingText}
        `;
        button.disabled = true;
    }

    resetButtonState(button, originalText) {
        button.innerHTML = originalText;
        button.disabled = false;
    }

    closeModalById(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
            bsModal.hide();
        }
    }

    closeActiveModal() {
        const activeModal = document.querySelector('.modal.show');
        if (activeModal) {
            const bsModal = bootstrap.Modal.getInstance(activeModal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    // File utility methods
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    getFileTypeDescription(file) {
        const extension = this.getFileExtension(file.name);
        const typeMap = {
            'xlsx': 'Microsoft Excel (OpenXML)',
            'xls': 'Microsoft Excel',
            'csv': 'Comma Separated Values'
        };
        return typeMap[extension] || 'Unknown File Type';
    }

    getFileExtension(filename) {
        return filename.split('.').pop().toLowerCase();
    }

    truncateFileName(filename, maxLength) {
        if (filename.length <= maxLength) return filename;
        const extension = this.getFileExtension(filename);
        const nameWithoutExt = filename.slice(0, -(extension.length + 1));
        const truncateLength = maxLength - extension.length - 4; // -4 for "..." and "."
        return nameWithoutExt.slice(0, truncateLength) + '...' + extension;
    }

    addAnimation(element, animationClass) {
        element.classList.add('animate__animated', animationClass);
        setTimeout(() => {
            element.classList.remove('animate__animated', animationClass);
        }, 1000);
    }
}

// Initialize ketika DOM siap
document.addEventListener('DOMContentLoaded', function() {
    new KodeKegiatanManager();
});

// Juga initialize dengan timeout untuk backup
setTimeout(() => {
    if (typeof KodeKegiatanManager !== 'undefined') {
        new KodeKegiatanManager();
    }
}, 100);