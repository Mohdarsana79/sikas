// sekolah.js - Dynamic School Management
class SekolahManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeAnimations();
        this.setupCSRF();
    }

    setupCSRF() {
        // Setup AJAX headers for CSRF protection
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    bindEvents() {
        // Form submission handlers
        $('#tambahForm').on('submit', (e) => this.handleTambahForm(e));
        $('#editForm').on('submit', (e) => this.handleEditForm(e));
        
        // Button click handlers
        $(document).on('click', '.btn-refresh', (e) => this.refreshData(e));
        $(document).on('click', '.btn-delete-school', (e) => this.handleDeleteSchool(e));
        
        // Modal events
        $('#tambahModal').on('show.bs.modal', () => this.resetTambahForm());
        $('#editModal').on('show.bs.modal', () => this.prepareEditForm());
        $('#tambahModal, #editModal').on('hidden.bs.modal', () => this.resetForms());
        
        // Real-time validation - hanya untuk form yang sedang aktif
        this.bindRealTimeValidation();
    }

    initializeAnimations() {
        // Add animation to cards on load
        $('.school-card, .empty-state-card').addClass('animate__animated animate__fadeIn');
        
        // Add hover effects to stat cards
        $('.stat-card').hover(
            function() {
                $(this).addClass('animate__animated animate__pulse');
            },
            function() {
                $(this).removeClass('animate__animated animate__pulse');
            }
        );
    }

    bindRealTimeValidation() {
        // Real-time validation untuk form tambah
        $('#tambahForm input, #tambahForm select, #tambahForm textarea').on('blur', (e) => {
            this.validateField($(e.target));
        });
        
        // Real-time validation untuk form edit
        $('#editForm input, #editForm select, #editForm textarea').on('blur', (e) => {
            this.validateField($(e.target));
        });
    }

    validateField(field) {
        // Pastikan field ada dan memiliki metode val()
        if (!field || !field.length) {
            console.warn('Field tidak ditemukan:', field);
            return false;
        }

        const value = field.val();
        // Handle undefined, null, atau empty string
        const stringValue = value ? value.toString().trim() : '';
        const isRequired = field.prop('required');
        
        if (isRequired && !stringValue) {
            field.addClass('is-invalid');
            field.removeClass('is-valid');
            return false;
        }
        
        // Specific validations
        const fieldName = field.attr('name');
        if (fieldName === 'npsn' && stringValue.length > 20) {
            field.addClass('is-invalid');
            field.removeClass('is-valid');
            return false;
        }
        
        field.removeClass('is-invalid');
        field.addClass('is-valid');
        return true;
    }

    async handleTambahForm(e) {
        e.preventDefault();
        
        const form = $(e.target);
        
        if (!this.validateForm(form)) {
            this.showSweetAlert('Tolong periksa kembali data yang diinput.', 'error');
            return;
        }

        try {
            this.setLoadingState(form.find('.btn-save'), true, 'Menyimpan...');
            
            const formData = new FormData(form[0]);
            const response = await $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            // Tutup modal
            $('#tambahModal').modal('hide');
            
            // Tampilkan SweetAlert success
            await this.showSweetAlert(
                response.message || 'Data sekolah berhasil ditambahkan!', 
                'success',
                'Berhasil!'
            );
            
            // Reload page after success
            window.location.reload();

        } catch (error) {
            this.handleAjaxError(error);
        } finally {
            this.setLoadingState(form.find('.btn-save'), false, 'Simpan');
        }
    }

    async handleEditForm(e) {
        e.preventDefault();
        console.log('Edit form submitted');
        
        const form = $(e.target);
        
        // Validasi form
        if (!this.validateForm(form)) {
            this.showSweetAlert('Tolong periksa kembali data yang diinput.', 'error');
            return;
        }

        try {
            this.setLoadingState(form.find('.btn-update'), true, 'Memperbarui...');
            
            const formData = new FormData(form[0]);
            
            // Debug: lihat data yang akan dikirim
            const formDataObj = {};
            for (let [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            console.log('Form Data to be sent:', formDataObj);

            const response = await $.ajax({
                url: form.attr('action'),
                method: 'POST', // Use POST with _method field
                data: formData,
                processData: false,
                contentType: false
            });

            console.log('Update response:', response);
            
            // Tutup modal
            $('#editModal').modal('hide');
            
            // Tampilkan SweetAlert success
            await this.showSweetAlert(
                response.message || 'Data sekolah berhasil diperbarui!', 
                'success',
                'Berhasil!'
            );
            
            // Update UI tanpa reload
            if (response.data) {
                this.updateUI(response.data);
            }
            
            // Reload untuk memastikan konsistensi
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } catch (error) {
            console.error('Update error:', error);
            this.handleAjaxError(error);
        } finally {
            this.setLoadingState(form.find('.btn-update'), false, 'Update');
        }
    }

    // Method untuk menampilkan SweetAlert notifikasi
    showSweetAlert(message, type = 'info', title = null) {
        const config = {
            title: title || this.getAlertTitle(type),
            text: message,
            icon: type,
            confirmButtonColor: this.getButtonColor(type),
            confirmButtonText: 'OK',
            timer: type === 'success' ? 3000 : undefined,
            timerProgressBar: type === 'success',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        };

        return Swal.fire(config);
    }

    // Method untuk mendapatkan judul alert berdasarkan type
    getAlertTitle(type) {
        const titles = {
            'success': 'Berhasil!',
            'error': 'Error!',
            'warning': 'Peringatan!',
            'info': 'Informasi',
            'question': 'Konfirmasi'
        };
        return titles[type] || 'Informasi';
    }

    // Method untuk mendapatkan warna button berdasarkan type
    getButtonColor(type) {
        const colors = {
            'success': '#28a745',
            'error': '#dc3545',
            'warning': '#ffc107',
            'info': '#17a2b8',
            'question': '#3085d6'
        };
        return colors[type] || '#3085d6';
    }

    validateForm(form) {
        let isValid = true;
        
        form.find('input[required], select[required], textarea[required]').each((index, element) => {
            const field = $(element);
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    updateUI(schoolData) {
        console.log('Updating UI with:', schoolData);
        
        // Update school information in real-time
        if (schoolData.nama_sekolah) $('.school-name').text(schoolData.nama_sekolah);
        if (schoolData.npsn) $('.school-npsn').text(`NPSN: ${schoolData.npsn}`);
        if (schoolData.status_sekolah) $('.school-status').text(`Status: ${schoolData.status_sekolah}`);
        if (schoolData.jenjang_sekolah) $('.school-level').text(`Jenjang: ${schoolData.jenjang_sekolah}`);
        if (schoolData.alamat) $('.school-address').text(schoolData.alamat);
        if (schoolData.kelurahan_desa) $('.school-village').text(schoolData.kelurahan_desa);
        if (schoolData.kecamatan) $('.school-district').text(schoolData.kecamatan);
        if (schoolData.kabupaten_kota) $('.school-city').text(schoolData.kabupaten_kota);
        if (schoolData.provinsi) $('.school-province').text(schoolData.provinsi);

        // Update stat cards
        if (schoolData.jenjang_sekolah) $('.stat-card:eq(0) h4').text(schoolData.jenjang_sekolah);
        if (schoolData.status_sekolah) $('.stat-card:eq(1) h4').text(schoolData.status_sekolah);
        if (schoolData.npsn) $('.stat-card:eq(2) h4').text(schoolData.npsn);
        if (schoolData.kabupaten_kota) $('.stat-card:eq(3) h4').text(schoolData.kabupaten_kota);

        // Add update animation
        $('.school-card').addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $('.school-card').removeClass('animate__animated animate__pulse');
        }, 1000);
    }

    handleDeleteSchool(e) {
        e.preventDefault();
        
        const schoolId = $(e.currentTarget).data('id');
        
        Swal.fire({
            title: 'Hapus Data Sekolah?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await $.ajax({
                        url: `/sekolah/${schoolId}`,
                        method: 'DELETE'
                    });

                    // Tampilkan SweetAlert success
                    await this.showSweetAlert(
                        response.message || 'Data sekolah berhasil dihapus!', 
                        'success',
                        'Berhasil!'
                    );
                    
                    // Reload page after deletion
                    window.location.reload();

                } catch (error) {
                    this.handleAjaxError(error);
                }
            }
        });
    }

    refreshData(e) {
        if (e) e.preventDefault();
        
        // Add refresh animation
        $('.school-card').addClass('animate__animated animate__rotateIn');
        
        // Tampilkan SweetAlert info
        this.showSweetAlert('Memperbarui data...', 'info', 'Loading');
        
        // Reload page
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    resetTambahForm() {
        $('#tambahForm')[0].reset();
        $('#tambahForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
    }

    prepareEditForm() {
        // Remove any existing validation errors
        $('#editForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
    }

    resetForms() {
        this.resetTambahForm();
        this.prepareEditForm();
    }

    setLoadingState(button, isLoading, text = null) {
        if (!button || !button.length) {
            console.warn('Button tidak ditemukan');
            return;
        }

        if (isLoading) {
            button.prop('disabled', true);
            const loadingText = text || 'Loading...';
            button.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`);
        } else {
            button.prop('disabled', false);
            const originalText = text || 
                (button.hasClass('btn-save') ? 
                    '<i class="bi bi-check-circle me-2"></i>Simpan' : 
                    '<i class="bi bi-check-circle me-2"></i>Update');
            button.html(originalText);
        }
    }

    // Method lama untuk alert bootstrap (sebagai fallback)
    showAlert(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type];

        const icon = {
            'success': 'bi-check-circle',
            'error': 'bi-exclamation-triangle',
            'warning': 'bi-exclamation-circle',
            'info': 'bi-info-circle'
        }[type];

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i class="bi ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        $('#alertContainer').html(alertHtml);
        
        // Auto remove alert after 5 seconds
        setTimeout(() => {
            const alert = $('#alertContainer .alert');
            if (alert.length) {
                alert.addClass('animate__animated animate__fadeOutUp');
                setTimeout(() => {
                    alert.alert('close');
                }, 500);
            }
        }, 5000);
    }

    handleAjaxError(error) {
        console.error('AJAX Error:', error);
        
        let message = 'Terjadi kesalahan saat memproses data.';
        
        if (error.responseJSON) {
            if (error.responseJSON.message) {
                message = error.responseJSON.message;
            } else if (error.responseJSON.errors) {
                // Handle validation errors
                const errors = error.responseJSON.errors;
                message = 'Data yang diinput tidak valid:<br>' + 
                         Object.values(errors).map(err => `- ${err}`).join('<br>');
            }
        } else if (error.status === 422) {
            message = 'Data yang diinput tidak valid. Silakan periksa kembali.';
        } else if (error.status === 500) {
            message = 'Terjadi kesalahan server. Silakan coba lagi.';
        } else if (error.status === 404) {
            message = 'Data tidak ditemukan.';
        }

        // Gunakan SweetAlert untuk error juga
        this.showSweetAlert(message, 'error');
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Tunggu sebentar untuk memastikan semua element sudah terload
    setTimeout(() => {
        try {
            new SekolahManager();
            console.log('✅ SekolahManager initialized successfully');
        } catch (error) {
            console.error('❌ Error initializing SekolahManager:', error);
        }
    }, 100);
});

// Fallback untuk handle error global
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
});

// Custom styles untuk SweetAlert
const customStyles = `
    <style>
        .school-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        
        .school-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .empty-state-card {
            border: 2px dashed #dee2e6;
            background: #f8f9fa;
        }
        
        .empty-icon {
            opacity: 0.5;
        }
        
        .btn-add-school, .btn-edit-school {
            transition: all 0.3s ease;
        }
        
        .btn-add-school:hover, .btn-edit-school:hover {
            transform: translateY(-1px);
        }
        
        .spinner-border {
            width: 1rem;
            height: 1rem;
        }
        
        .is-valid {
            border-color: #198754 !important;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        /* SweetAlert custom styles */
        .swal2-popup {
            border-radius: 15px;
            font-family: inherit;
            padding: 2rem;
        }
        
        .swal2-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .swal2-html-container {
            font-size: 1.1rem;
            color: #666;
        }
        
        .swal2-success {
            border-color: #28a745;
        }
        
        .swal2-error {
            border-color: #dc3545;
        }
        
        .swal2-warning {
            border-color: #ffc107;
        }
        
        .swal2-info {
            border-color: #17a2b8;
        }
        
        .swal2-confirm {
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
    </style>
`;

// Inject styles
$(document).ready(function() {
    if (!$('#sekolah-custom-styles').length) {
        $('head').append(customStyles);
    }
});