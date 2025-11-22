class PenganggaranManager {
    constructor() {
        this.init();
    }

    init() {
        this.initEventListeners();
        this.initSweetAlertMessages();
        this.checkAndDisableDeleteButtons();
    }

    initEventListeners() {
        // Format input angka tambah anggaran
        $('#pagu_anggaran').on('keyup', function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            if (value.length > 0) {
                value = parseInt(value).toLocaleString('id-ID');
                $(this).val(value);
            }
        });

        // Reset form ketika modal ditutup
        $('#tambahAnggaranModal').on('hidden.bs.modal', function() {
            $('#formTambahAnggaran')[0].reset();
            $('#formTambahAnggaran').find('.is-invalid').removeClass('is-invalid');
        });

        // Validasi form sebelum submit
        $('#formTambahAnggaran').on('submit', (e) => {
            this.validateForm(e);
        });

        // Hapus validasi ketika user mulai mengetik
        $('input[required]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $(this).removeClass('is-invalid');
            }
        });

        // Event delegation untuk tombol hapus
        $(document).on('click', '.btn-hapus-anggaran', (e) => {
            this.handleDeleteAnggaran(e);
        });

        $(document).on('click', '.btn-hapus-rkas-perubahan', (e) => {
            this.handleDeleteRkasPerubahan(e);
        });
    }

    // Cek dan nonaktifkan tombol hapus jika RKAS Perubahan sudah ada
    checkAndDisableDeleteButtons() {
        $('.rkas-item').each(function() {
            const $card = $(this);
            const tahun = $card.find('.btn-hapus-anggaran').data('tahun');
            const hasPerubahan = $card.next('.rkas-item').hasClass('border-warning') && 
                               !$card.next('.rkas-item').hasClass('d-none');
            
            if (hasPerubahan) {
                $card.find('.btn-hapus-anggaran')
                    .addClass('disabled')
                    .prop('disabled', true)
                    .attr('title', 'Tidak dapat dihapus karena sudah ada RKAS Perubahan')
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-secondary');
            }
        });
    }

    validateForm(e) {
        let isValid = true;
        $('#formTambahAnggaran').find('input[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            this.showAlert('warning', 'Perhatian', 'Harap lengkapi semua field yang wajib diisi!');
        }
    }

    handleDeleteAnggaran(e) {
        const $button = $(e.currentTarget);
        
        // Cek jika tombol disabled
        if ($button.hasClass('disabled')) {
            this.showAlert('warning', 'Tidak Dapat Dihapus', 'RKAS tidak dapat dihapus karena sudah ada RKAS Perubahan untuk tahun anggaran ini.');
            return;
        }

        const id = $button.data('id');
        const tahun = $button.data('tahun');
        const pagu = $button.data('pagu');

        this.showConfirmDialog(
            'Hapus Anggaran?',
            `Apakah Anda yakin ingin menghapus anggaran tahun <strong>${tahun}</strong> dengan pagu <strong>Rp ${pagu}</strong>?<br><br><span class="text-danger">Data yang dihapus tidak dapat dikembalikan!</span>`,
            'warning',
            'Ya, Hapus!'
        ).then((result) => {
            if (result.isConfirmed) {
                this.showLoading('Menghapus...', 'Sedang menghapus data anggaran');
                this.deleteData(`/penganggaran/penganggaran/${id}`, tahun, 'anggaran');
            }
        });
    }

    handleDeleteRkasPerubahan(e) {
        const $button = $(e.currentTarget);
        const id = $button.data('id');
        const tahun = $button.data('tahun');

        this.showConfirmDialog(
            'Hapus RKAS Perubahan?',
            `Apakah Anda yakin ingin menghapus <strong>RKAS Perubahan</strong> tahun <strong>${tahun}</strong>?<br><br><span class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Hanya data RKAS Perubahan yang akan dihapus. Data RKAS awal tetap tersimpan.</span>`,
            'warning',
            'Ya, Hapus!',
            '#f59e0b'
        ).then((result) => {
            if (result.isConfirmed) {
                this.showLoading('Menghapus...', 'Sedang menghapus data RKAS Perubahan');
                this.deleteData(`/penganggaran/rkas-perubahan/${id}`, tahun, 'RKAS Perubahan');
            }
        });
    }

    deleteData(url, tahun, type) {
        // Ambil CSRF token dari meta tag
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: csrfToken,
                _method: 'DELETE'
            },
            success: (response) => {
                Swal.close();
                this.showAlert('success', 'Berhasil!', `Data ${type} tahun ${tahun} berhasil dihapus`, true);
            },
            error: (xhr) => {
                Swal.close();
                let errorMessage = `Gagal menghapus data ${type}`;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                this.showAlert('error', 'Gagal!', errorMessage);
            }
        });
    }

    showConfirmDialog(title, html, icon, confirmText, confirmColor = '#d33') {
        return Swal.fire({
            title: title,
            html: html,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal',
            reverseButtons: true
        });
    }

    showLoading(title, text) {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    showAlert(icon, title, text, reload = false) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonColor: icon === 'success' ? '#10b981' : '#ef4444',
            timer: reload ? 3000 : undefined,
            showConfirmButton: true
        }).then(() => {
            if (reload) {
                location.reload();
            }
        });
    }

    initSweetAlertMessages() {
        // Handle SweetAlert untuk pesan sukses dari session
        if (typeof successMessage !== 'undefined' && successMessage) {
            this.showAlert('success', 'Berhasil!', successMessage);
        }

        if (typeof errorMessage !== 'undefined' && errorMessage) {
            this.showAlert('error', 'Gagal!', errorMessage);
        }
    }
}

// Inisialisasi class ketika document ready
$(document).ready(function() {
    new PenganggaranManager();
});