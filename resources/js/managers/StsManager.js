export default class StsManager {
    constructor() {
        console.log('🔄 StsManager constructor called');
        
        this.selectedStsId = null;
        this.penganggaranData = [];

        // Cek jika ini pertama kali user mengakses halaman STS
        this.isFirstTime = !localStorage.getItem('sts_info_seen');
        
        // Dapatkan CSRF token
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        this.csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        
        console.log('CSRF Token available:', !!this.csrfToken);
        
        this.init();
    }

    async init() {
        console.log('🔧 StsManager init started');
        
        try {
            await this.loadPenganggaran();
            this.initEventListeners();
            this.setDefaultDates();
            this.setupAutoNomorSTS();
            this.setupRupiahFormatting();

            // Inisialisasi data tabel
            await this.reloadTableDataViaAPI();

            // Auto-show info modal untuk first-time users
            if (this.isFirstTime) {
                setTimeout(() => {
                    this.showInfoModal();
                }, 1000);
            }
            
            console.log('✅ StsManager initialized successfully');
        } catch (error) {
            console.error('❌ StsManager initialization failed:', error);
        }
    }

    // Method untuk menampilkan modal info
    showInfoModal() {
        const infoModal = new bootstrap.Modal(document.getElementById('modalInfoSTS'));
        infoModal.show();
        console.log('ℹ️ Info modal shown for first-time user');
        
        // Set flag bahwa user sudah melihat info
        localStorage.setItem('sts_info_seen', 'true');
    }

    // Method untuk reset first-time flag (untuk testing)
    resetFirstTimeFlag() {
        localStorage.removeItem('sts_info_seen');
        this.isFirstTime = true;
        console.log('🔁 First-time flag reset');
    }

    setDefaultDates() {
        try {
            const today = new Date().toISOString().split('T')[0];
            const payTanggal = document.getElementById('payTanggal');
            const editPayTanggal = document.getElementById('editPayTanggal');
            
            if (payTanggal) {
                payTanggal.value = today;
                console.log('📅 Set default payment date:', today);
            }
            
            if (editPayTanggal) {
                editPayTanggal.value = today;
                console.log('📅 Set default edit payment date:', today);
            }
        } catch (error) {
            console.error('Error setting default dates:', error);
        }
    }

    async loadPenganggaran() {
        console.log('🔄 Loading penganggaran data...');
        
        try {
            const response = await fetch('/sts/api/penganggaran');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📊 Penganggaran API response:', data);
            
            if (data.success) {
                this.penganggaranData = data.data;
                console.log(`✅ Loaded ${this.penganggaranData.length} penganggaran items`);
                this.populatePenganggaranDropdowns();
            } else {
                console.error('❌ Failed to load penganggaran data:', data.message);
                this.showAlert('error', 'Error', 'Gagal memuat data penganggaran');
            }
        } catch (error) {
            console.error('❌ Error loading penganggaran:', error);
            this.showAlert('error', 'Error', 'Gagal memuat data penganggaran: ' + error.message);
        }
    }

    populatePenganggaranDropdowns() {
        console.log('🔄 Populating penganggaran dropdowns...');
        
        if (!this.penganggaranData || this.penganggaranData.length === 0) {
            console.warn('⚠️ Penganggaran data is empty');
            return;
        }

        // Buat options untuk dropdown
        let options = '<option value="" selected disabled>Pilih Tahun Anggaran</option>';
        this.penganggaranData.forEach(item => {
            options += `<option value="${item.id}">${item.tahun_anggaran}</option>`;
        });

        // Update dropdown tambah STS
        const selectTambah = document.querySelector('#formTambahSTS select[name="penganggaran_id"]');
        if (selectTambah) {
            selectTambah.innerHTML = options;
            console.log('✅ Updated tambah STS dropdown');
        }

        // Update dropdown edit STS
        const selectEdit = document.querySelector('#formEditSTS select[name="penganggaran_id"]');
        if (selectEdit) {
            selectEdit.innerHTML = options;
            console.log('✅ Updated edit STS dropdown');
        }
    }

    setupAutoNomorSTS() {
        const tahunSelect = document.querySelector('#formTambahSTS select[name="penganggaran_id"]');
        const nomorInput = document.querySelector('#formTambahSTS input[name="nomor_sts"]');
        
        if (tahunSelect && nomorInput) {
            tahunSelect.addEventListener('change', (e) => {
                const selectedOption = tahunSelect.options[tahunSelect.selectedIndex];
                const tahun = selectedOption ? selectedOption.textContent : '';
                
                if (tahun && !nomorInput.value) {
                    nomorInput.placeholder = `STS-${tahun}-001`;
                }
            });
        }
    }

    initEventListeners() {
        console.log('🔄 Initializing event listeners...');
        
        // Form handlers
        const formTambah = document.getElementById('formTambahSTS');
        const formEdit = document.getElementById('formEditSTS');
        const formBayar = document.getElementById('formBayarSTS');
        const formEditBayar = document.getElementById('formEditBayarSTS');
        
        if (formTambah) {
            formTambah.addEventListener('submit', (e) => this.handleTambahSTS(e));
            console.log('✅ Added submit listener to tambah form');
        }
        
        if (formEdit) {
            formEdit.addEventListener('submit', (e) => this.handleEditSTS(e));
            console.log('✅ Added submit listener to edit form');
        }
        
        if (formBayar) {
            formBayar.addEventListener('submit', (e) => this.handleBayarSTS(e));
            console.log('✅ Added submit listener to bayar form');
        }
        
        if (formEditBayar) {
            formEditBayar.addEventListener('submit', (e) => this.handleEditBayarSTS(e));
            console.log('✅ Added submit listener to edit bayar form');
        }

        // Table row click - menggunakan event delegation
        const tableBody = document.querySelector('#stsTable tbody');
        if (tableBody) {
            tableBody.addEventListener('click', (e) => {
                const row = e.target.closest('tr.sts-row');
                if (row) {
                    this.toggleSingleSelection(row);
                }
            });
            console.log('✅ Added table row click listener');
        }

        // Action buttons dengan event listeners langsung
        document.getElementById('btnBayar')?.addEventListener('click', () => this.handleAction('bayar'));
        document.getElementById('btnEditBayar')?.addEventListener('click', () => this.handleAction('edit_bayar'));
        document.getElementById('btnEdit')?.addEventListener('click', () => this.handleAction('edit'));
        document.getElementById('btnHapus')?.addEventListener('click', () => this.handleAction('hapus'));
        // Tambahkan event listener untuk tombol info
        document.getElementById('btnInfo')?.addEventListener('click', () => {
            console.log('ℹ️ Info button clicked');
        });
        
        console.log('✅ All event listeners initialized');
    }

    toggleSingleSelection(row) {
        // Cegah event jika klik langsung di checkbox
        if (event.target.type === 'checkbox') {
            event.stopPropagation();
            return;
        }
        
        const checkbox = row.querySelector('.row-checkbox');
        const isAlreadySelected = row.classList.contains('selected-row');

        // Clear all selections
        document.querySelectorAll('#stsTable tbody tr').forEach(r => {
            r.classList.remove('selected-row');
            r.querySelector('.row-checkbox').checked = false;
        });

        if (!isAlreadySelected) {
            row.classList.add('selected-row');
            checkbox.checked = true;
            this.selectedStsId = row.dataset.id;
            console.log('✅ Selected STS ID:', this.selectedStsId);
        } else {
            this.selectedStsId = null;
        }
    }

    handleAction(type) {
        console.log(`🔄 Handling action: ${type}`);
        
        if (!this.selectedStsId) {
            this.showAlert('warning', 'Peringatan', 'Silakan pilih salah satu data STS terlebih dahulu.');
            return;
        }

        const row = document.querySelector(`tr[data-id="${this.selectedStsId}"]`);
        if (!row) {
            this.showAlert('error', 'Error', 'Data STS tidak ditemukan');
            return;
        }
        
        switch(type) {
            case 'edit':
                this.loadStsForEdit();
                break;
            case 'bayar':
                this.openBayarModal(row);
                break;
            case 'edit_bayar':
                this.openEditBayarModal(row);
                break;
            case 'hapus':
                this.confirmDelete(row);
                break;
        }
    }

    async loadStsForEdit() {
        console.log('🔄 Loading STS data for edit...');
        
        const editButton = document.getElementById('btnEdit');
        // this.showButtonLoading(editButton, 'Memuat...');
        
        try {
            const response = await fetch(`/sts/${this.selectedStsId}`);
            const data = await response.json();
            console.log('📊 STS edit response:', data);
            
            this.hideButtonLoading(editButton);
            
            if (data.success) {
                // Set nilai form
                document.getElementById('editNoSts').value = data.data.nomor_sts;
                
                // Format jumlah STS dengan format Indonesia
                const jumlahStsFormatted = this.formatRupiahDisplay(data.data.jumlah_sts);
                document.getElementById('editJmlSts').value = jumlahStsFormatted;
                
                // Set penganggaran_id di dropdown
                const selectEdit = document.querySelector('#formEditSTS select[name="penganggaran_id"]');
                if (selectEdit) {
                    setTimeout(() => {
                        selectEdit.value = data.data.penganggaran_id;
                        console.log('✅ Set select value to:', data.data.penganggaran_id);
                        
                        if (data.data.jumlah_bayar > 0) {
                            selectEdit.disabled = true;
                            selectEdit.title = "Tahun tidak dapat diubah karena sudah ada pembayaran";
                        }
                    }, 100);
                }
                
                new bootstrap.Modal(document.getElementById('modalEditSTS')).show();
                console.log('✅ Edit modal shown with formatted amount:', jumlahStsFormatted);
            }
        } catch (error) {
            console.error('❌ Error:', error);
            this.hideButtonLoading(editButton);
            this.showAlert('error', 'Error', 'Gagal memuat data STS');
        }
    }

    // Method untuk memuat ulang data STS
    async reloadTableData() {
        console.log('🔄 Reloading STS table data...');
        
        try {
            const response = await fetch('/sts');
            const text = await response.text();
            
            // Parse HTML untuk mendapatkan data tabel
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            
            // Ekstrak data dari tabel
            const newTableBody = doc.querySelector('#stsTable tbody');
            const newRows = newTableBody ? newTableBody.innerHTML : '';
            
            // Update tabel jika ada data baru
            if (newRows) {
                const currentTableBody = document.querySelector('#stsTable tbody');
                if (currentTableBody) {
                    currentTableBody.innerHTML = newRows;
                    console.log('✅ Table body updated');
                    
                    // Re-attach event listeners untuk row yang baru
                    this.attachRowEventListeners();
                    
                    // Clear selection
                    this.selectedStsId = null;
                    document.querySelectorAll('#stsTable tbody tr').forEach(r => {
                        r.classList.remove('selected-row');
                        r.querySelector('.row-checkbox').checked = false;
                    });
                }
            }
            
            return true;
        } catch (error) {
            console.error('❌ Error reloading table data:', error);
            return false;
        }
    }

    // Method untuk attach event listeners ke row
    attachRowEventListeners() {
        const tableBody = document.querySelector('#stsTable tbody');
        if (tableBody) {
            // Event delegation untuk row click
            tableBody.addEventListener('click', (e) => {
                const row = e.target.closest('tr.sts-row');
                const checkbox = e.target.closest('.row-checkbox');
                
                // Jika klik pada checkbox, toggle selection
                if (checkbox) {
                    e.stopPropagation();
                    this.toggleSelectionByCheckbox(row, checkbox);
                    return;
                }
                
                // Jika klik pada row (bukan checkbox)
                if (row && !checkbox) {
                    this.toggleSingleSelection(row);
                }
            });
            
            console.log('✅ Re-attached row event listeners');
        }
    }

    // Method untuk toggle selection via checkbox
    toggleSelectionByCheckbox(row, checkbox) {
        const isChecked = checkbox.checked;
        
        // Clear semua selection lainnya
        document.querySelectorAll('#stsTable tbody tr').forEach(r => {
            if (r !== row) {
                r.classList.remove('selected-row');
                r.querySelector('.row-checkbox').checked = false;
            }
        });
        
        // Set state untuk row ini
        if (isChecked) {
            row.classList.add('selected-row');
            this.selectedStsId = row.dataset.id;
            console.log('✅ Selected STS ID via checkbox:', this.selectedStsId);
        } else {
            row.classList.remove('selected-row');
            this.selectedStsId = null;
            console.log('❌ Selection cleared via checkbox');
        }
    }

    // Method untuk clear semua selection
    clearSelection() {
        document.querySelectorAll('#stsTable tbody tr').forEach(r => {
            r.classList.remove('selected-row');
            r.querySelector('.row-checkbox').checked = false;
        });
        this.selectedStsId = null;
        console.log('✅ All selections cleared');
    }

    // Method untuk fetch data STS via API (lebih efisien)
    async fetchStsData() {
        try {
            const response = await fetch('/sts/api/data'); // Kita perlu buat route ini
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching STS data:', error);
            return [];
        }
    }

    // Method untuk render tabel dari data JSON
    renderTableData(stsData) {
        const tableBody = document.querySelector('#stsTable tbody');
        if (!tableBody) return;
        
        // Tambah class loading sementara
        tableBody.classList.add('table-loading');
        
        let html = '';
        
        stsData.forEach((item, index) => {
            // Tentukan badge berdasarkan status
            let badge = 'badge-pending';
            let status = 'Menunggu';
            
            if (item.jumlah_bayar > 0) {
                if (item.sisa > 0) {
                    badge = 'badge-partial';
                    status = 'Parsial';
                } else {
                    badge = 'badge-paid';
                    status = 'Lunas';
                }
            }
            
            // Cek jika ini adalah item yang sedang dipilih
            const isSelected = this.selectedStsId == item.id;
            const selectedClass = isSelected ? 'selected-row' : '';
            const checkedAttr = isSelected ? 'checked' : '';
            
            html += `
                <tr class="sts-row sts-row-updated ${selectedClass}" data-id="${item.id}">
                    <td class="col-selection">
                        <input class="form-check-input row-checkbox" type="checkbox" 
                            value="${item.id}" ${checkedAttr}>
                    </td>
                    <td class="text-center text-muted">${index + 1}</td>
                    <td>${item.nomor_sts}</td>
                    <td>${item.tahun}</td>
                    <td>Rp ${this.formatNumber(item.jumlah_sts)}</td>
                    <td>Rp ${this.formatNumber(item.jumlah_bayar)}</td>
                    <td class="text-muted">
                        ${item.tanggal_bayar ? `<i class="bi bi-calendar-check me-1"></i> ${item.tanggal_bayar}` : '-'}
                    </td>
                    <td>
                        <span class="badge badge-status ${badge}">${status}</span>
                    </td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
        tableBody.classList.remove('table-loading');
        
        console.log(`✅ Rendered ${stsData.length} STS items with visible checkboxes`);
        
        // Hapus class animasi setelah selesai
        setTimeout(() => {
            document.querySelectorAll('.sts-row-updated').forEach(row => {
                row.classList.remove('sts-row-updated');
            });
        }, 2000);
    }

    // Helper untuk format angka
    formatNumber(number) {
        // Format untuk display di tabel (2 desimal)
        return this.formatRupiahDisplay(number);
    }

    openBayarModal(row) {
        const total = this.extractNumber(row.cells[4].innerText);
        const terbayar = this.extractNumber(row.cells[5].innerText);
        const sisa = total - terbayar;
        
        document.getElementById('payInfoNoSts').innerText = row.cells[2].innerText;
        document.getElementById('payInfoSisa').innerText = this.formatCurrency(sisa);
        
        // Format sisa dengan format Indonesia
        const sisaFormatted = this.formatRupiahDisplay(sisa);
        document.getElementById('payNominal').value = sisaFormatted;
        document.getElementById('payNominal').max = sisa;
        
        new bootstrap.Modal(document.getElementById('modalBayarSTS')).show();
        console.log('✅ Bayar modal shown with amount:', sisaFormatted);
    }

    openEditBayarModal(row) {
        const noSts = row.cells[2].innerText;
        const terbayar = this.extractNumber(row.cells[5].innerText);
        const tglBayar = row.cells[6].innerText;

        document.getElementById('editPayInfoNoSts').innerText = noSts;
        
        // Format terbayar dengan format Indonesia
        const terbayarFormatted = this.formatRupiahDisplay(terbayar);
        document.getElementById('editPayNominal').value = terbayarFormatted;
        
        const formattedDate = this.parseDate(tglBayar);
        if (formattedDate) {
            document.getElementById('editPayTanggal').value = formattedDate;
        }

        new bootstrap.Modal(document.getElementById('modalEditBayarSTS')).show();
        console.log('✅ Edit bayar modal shown with amount:', terbayarFormatted);
    }

    confirmDelete(row) {
        const stsNumber = row.cells[2].innerText;
        
        Swal.fire({
            title: 'Hapus STS?',
            text: `Apakah Anda yakin ingin menghapus ${stsNumber}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef476f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.deleteSts(row);
            }
        });
    }

    async deleteSts(row) {
        const deleteButton = document.getElementById('btnHapus');
        this.showButtonLoading(deleteButton, 'Menghapus...');
        
        try {
            const response = await fetch(`/sts/${this.selectedStsId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            this.hideButtonLoading(deleteButton);
            
            if (data.success) {
                // Tampilkan alert sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Dihapus!',
                    text: 'Data STS telah dihapus.',
                    confirmButtonColor: '#4361ee',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Hapus row langsung dari DOM
                row.remove();
                this.updateRowNumbers();
                this.selectedStsId = null;
                
            } else {
                this.showAlert('error', 'Error!', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.hideButtonLoading(deleteButton);
            this.showAlert('error', 'Error!', 'Terjadi kesalahan saat menghapus data');
        }
    }

    async handleTambahSTS(e) {
        e.preventDefault();
        console.log('🔄 Handling tambah STS...');
        
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Tampilkan loading hanya di button
        this.showButtonLoading(submitButton, 'Menyimpan...');
        
        const penganggaranSelect = form.querySelector('select[name="penganggaran_id"]');
        const nomorStsInput = form.querySelector('input[name="nomor_sts"]');
        const jumlahStsInput = form.querySelector('input[name="jumlah_sts"]');
        
        // Validasi input
        if (!penganggaranSelect?.value) {
            this.hideButtonLoading(submitButton);
            this.showAlert('warning', 'Peringatan', 'Silakan pilih tahun anggaran');
            return;
        }
        
        if (!nomorStsInput?.value?.trim()) {
            this.hideButtonLoading(submitButton);
            this.showAlert('warning', 'Peringatan', 'Silakan masukkan nomor STS');
            nomorStsInput?.focus();
            return;
        }
        
        // Parse nilai rupiah ke angka
        const jumlahStsNumber = this.parseRupiahToNumber(jumlahStsInput?.value || '0');
        console.log('Parsed jumlah STS:', jumlahStsNumber, 'from:', jumlahStsInput?.value);

        if (jumlahStsNumber <= 0) {
            this.hideButtonLoading(submitButton);
            this.showAlert('warning', 'Peringatan', 'Silakan masukkan jumlah yang valid');
            jumlahStsInput?.focus();
            return;
        }

        const formData = {
            penganggaran_id: penganggaranSelect.value,
            nomor_sts: nomorStsInput.value.trim(),
            jumlah_sts: jumlahStsNumber,
        };

        console.log('Sending form data:', formData);

        try {
            const response = await fetch('/sts', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            console.log('Response:', data);
            
            this.hideButtonLoading(submitButton);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahSTS'));
            modal.hide();
            
            if (data.success) {
                // Reset form
                form.reset();
                this.populatePenganggaranDropdowns();
                
                // Tampilkan alert sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'STS baru telah ditambahkan.',
                    confirmButtonColor: '#4361ee',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Reload tabel data tanpa refresh halaman
                await this.reloadTableDataViaAPI();
                this.clearSelection();
                
            } else {
                this.showAlert('error', 'Error!', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.hideButtonLoading(submitButton);
            this.showAlert('error', 'Error!', 'Terjadi kesalahan saat menambahkan data');
        }
    }

    async handleEditSTS(e) {
        e.preventDefault();
        console.log('🔄 Handling edit STS...');
        
        const submitButton = e.target.querySelector('button[type="submit"]');
        this.showButtonLoading(submitButton, 'Mengupdate...');
        
        // Parse nilai rupiah ke angka
        const jumlahStsNumber = this.parseRupiahToNumber(document.getElementById('editJmlSts')?.value || '0');

        if (jumlahStsNumber <= 0) {
            this.hideButtonLoading(submitButton);
            this.showAlert('warning', 'Peringatan', 'Silakan masukkan jumlah yang valid');
            jumlahStsInput?.focus();
            return;
        }
        
        const formData = {
            nomor_sts: document.getElementById('editNoSts').value,
            jumlah_sts: jumlahStsNumber,
            penganggaran_id: document.getElementById('editPenganggaranId').value,
        };

        console.log('Edit form data:', formData);

        // Validasi: tahun tidak boleh diubah jika sudah ada pembayaran
        try {
            const stsResponse = await fetch(`/sts/${this.selectedStsId}`);
            const stsData = await stsResponse.json();
            
            if (stsData.success && stsData.data.jumlah_bayar > 0) {
                // Pastikan penganggaran_id tidak berubah
                if (parseInt(formData.penganggaran_id) !== stsData.data.penganggaran_id) {
                    this.hideButtonLoading(submitButton);
                    this.showAlert('error', 'Error!', 'Tahun anggaran tidak dapat diubah karena sudah ada pembayaran.');
                    return;
                }
            }
        } catch (error) {
            console.error('Error validating STS data:', error);
        }

        try {
            const response = await fetch(`/sts/${this.selectedStsId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            console.log('Edit response:', data);
            
            this.hideButtonLoading(submitButton);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditSTS'));
            modal.hide();
            
            if (data.success) {
                // Tampilkan alert sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data STS telah diperbarui.',
                    confirmButtonColor: '#4361ee',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Reload tabel data tanpa refresh halaman
                await this.reloadTableDataViaAPI();
                this.clearSelection();
                
            } else {
                this.showAlert('error', 'Error!', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.hideButtonLoading(submitButton);
            this.showAlert('error', 'Error!', 'Terjadi kesalahan saat mengupdate data');
        }
    }

    async handleBayarSTS(e) {
        e.preventDefault();
        console.log('🔄 Handling bayar STS...');
        
        const submitButton = e.target.querySelector('button[type="submit"]');
        this.showButtonLoading(submitButton, 'Memproses...');
        
        // Parse nilai rupiah ke angka
        const jumlahBayarNumber = this.parseRupiahToNumber(document.getElementById('payNominal')?.value || '0');

        if (jumlahBayarNumber <= 0) {
            this.hideButtonLoading(submitButton);
            this.showAlert('warning', 'Peringatan', 'Silakan masukkan jumlah yang valid');
            jumlahBayarInput?.focus();
            return;
        }
        
        const formData = {
            tanggal_bayar: document.getElementById('payTanggal').value,
            jumlah_bayar: jumlahBayarNumber,
        };

        console.log('Bayar form data:', formData);

        try {
            const response = await fetch(`/sts/${this.selectedStsId}/bayar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            console.log('Bayar response:', data);
            
            this.hideButtonLoading(submitButton);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalBayarSTS'));
            modal.hide();
            
            if (data.success) {
                // Tampilkan alert sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    text: 'Pembayaran STS telah diproses.',
                    confirmButtonColor: '#4361ee',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Reload tabel data tanpa refresh halaman
                await this.reloadTableDataViaAPI();
                this.clearSelection();
                
            } else {
                this.showAlert('error', 'Error!', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.hideButtonLoading(submitButton);
            this.showAlert('error', 'Error!', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    async handleEditBayarSTS(e) {
        e.preventDefault();
        console.log('🔄 Handling edit bayar STS...');
        
        const submitButton = e.target.querySelector('button[type="submit"]');
        this.showButtonLoading(submitButton, 'Mengupdate...');
        
        // Parse nilai rupiah ke angka
        const jumlahBayarNumber = this.parseRupiahToNumber(document.getElementById('editPayNominal')?.value || '0');

        if (jumlahBayarNumber <= 0) {
            this.hideButtonLoading(submitButton);
            this.showAlert('warning', 'Peringatan', 'Silakan masukkan jumlah yang valid');
            jumlahBayarInput?.focus();
            return;
        }
        
        const formData = {
            tanggal_bayar: document.getElementById('editPayTanggal').value,
            jumlah_bayar: jumlahBayarNumber,
        };

        console.log('Edit bayar form data:', formData);

        try {
            const response = await fetch(`/sts/${this.selectedStsId}/bayar`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            console.log('Edit bayar response:', data);
            
            this.hideButtonLoading(submitButton);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditBayarSTS'));
            modal.hide();
            
            if (data.success) {
                // Tampilkan alert sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data pembayaran telah diperbarui.',
                    confirmButtonColor: '#4361ee',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Reload tabel data tanpa refresh halaman
                await this.reloadTableDataViaAPI();
                this.clearSelection();
                
            } else {
                this.showAlert('error', 'Error!', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.hideButtonLoading(submitButton);
            this.showAlert('error', 'Error!', 'Terjadi kesalahan saat mengupdate pembayaran');
        }
    }

    // Method untuk reload data via API (lebih efisien)
    async reloadTableDataViaAPI() {
        console.log('🔄 Reloading table data via API...');
        
        try {
            const stsData = await this.fetchStsData();
            if (stsData.length > 0) {
                this.renderTableData(stsData);
                this.attachRowEventListeners();
                console.log('✅ Table reloaded via API');
            } else {
                // Fallback ke metode lama jika API belum tersedia
                await this.reloadTableData();
            }
        } catch (error) {
            console.error('Error reloading via API:', error);
            // Fallback ke metode lama
            await this.reloadTableData();
        }
    }

    // Helper methods untuk button loading saja (tidak global)
    showButtonLoading(button, text = 'Memproses...') {
        if (!button) return;
        
        // Simpan HTML asli dan state
        const originalHtml = button.innerHTML;
        const originalDisabled = button.disabled;
        
        button.dataset.originalHtml = originalHtml;
        button.dataset.originalDisabled = originalDisabled;
        
        // Set state loading
        button.disabled = true;
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            ${text}
        `;
        
        // Tambah class loading
        button.classList.add('btn-loading');
        
        console.log(`✅ Show loading on button: ${button.id || button.className}`);
    }

    hideButtonLoading(button) {
        if (!button) return;
        
        // Kembalikan ke state semula
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
            button.disabled = button.dataset.originalDisabled === 'true';
            
            // Hapus data atribut
            delete button.dataset.originalHtml;
            delete button.dataset.originalDisabled;
        }
        
        // Hapus class loading
        button.classList.remove('btn-loading');
        
        console.log(`✅ Hide loading on button: ${button.id || button.className}`);
    }

    // Helper methods lainnya
    updateRowNumbers() {
        const rows = document.querySelectorAll('#stsTable tbody tr');
        rows.forEach((row, index) => {
            row.cells[1].innerText = index + 1;
        });
    }

    parseDate(dateStr) {
        if (!dateStr || dateStr.includes('-')) {
            return dateStr;
        }
        
        const cleanStr = dateStr.replace(/[^\d/]/g, '').trim();
        const parts = cleanStr.split('/');
        
        if (parts.length === 3) {
            const day = parts[0].padStart(2, '0');
            const month = parts[1].padStart(2, '0');
            const year = parts[2];
            return `${year}-${month}-${day}`;
        }
        return null;
    }

    formatCurrency(number) {
        // Format dengan pemisah ribuan dan 2 angka desimal
        return 'Rp ' + this.formatRupiahDisplay(number);
    }

    extractNumber(currencyString) {
        if (!currencyString) return 0;
        
        // Contoh string: "Rp 24.000,00"
        // Hapus "Rp" dan spasi
        let cleanString = currencyString.replace('Rp', '').trim();
        
        // Hapus semua titik (pemisah ribuan)
        cleanString = cleanString.replace(/\./g, '');
        
        // Ubah koma menjadi titik untuk parse float
        cleanString = cleanString.replace(',', '.');
        
        // Parse ke float
        const number = parseFloat(cleanString);
        
        return isNaN(number) ? 0 : number;
    }

    showAlert(icon, title, text, reload = false) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonColor: '#4361ee'
        }).then(() => {
            if (reload) {
                location.reload();
            }
        });
    }

    // Tambahkan fungsi untuk format display (dengan 2 desimal)
    formatRupiahDisplay(number) {
        if (!number && number !== 0) return '0';
        
        const num = parseFloat(number);
        if (isNaN(num)) return '0';
        
        // Format dengan pemisah ribuan dan 2 angka desimal
        return num.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Tambahkan methods untuk format rupiah
    formatRupiahInput(value) {
        if (!value) return '';
        
        let number = value.toString();
        
        // Hapus semua karakter non-digit dan non-koma
        number = number.replace(/[^\d,]/g, '');
        
        // Jika ada lebih dari satu koma, hanya ambil yang pertama
        const commaCount = (number.match(/,/g) || []).length;
        if (commaCount > 1) {
            const parts = number.split(',');
            number = parts[0] + ',' + parts.slice(1).join('');
        }
        
        // Pisahkan bagian sebelum dan sesudah koma
        let beforeComma = number;
        let afterComma = '';
        
        if (number.includes(',')) {
            const parts = number.split(',');
            beforeComma = parts[0];
            afterComma = parts[1] ? parts[1].substring(0, 2) : ''; // Maksimal 2 digit desimal
        }
        
        // Format bagian sebelum koma dengan pemisah ribuan
        if (beforeComma) {
            beforeComma = beforeComma.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Gabungkan kembali
        if (afterComma) {
            return beforeComma + ',' + afterComma;
        }
        
        return beforeComma;
    }

    parseRupiahToNumber(rupiahString) {
        if (!rupiahString) return 0;
        
        // Hapus semua karakter non-digit dan non-koma
        // Biarkan koma untuk handle desimal
        let cleanString = rupiahString.toString().trim();
        
        // Jika ada format dengan titik sebagai pemisah ribuan dan koma sebagai desimal
        // Contoh: 24.000,50 → 24000.50
        cleanString = cleanString.replace(/\./g, ''); // Hapus titik (pemisah ribuan)
        cleanString = cleanString.replace(/,/g, '.'); // Ubah koma menjadi titik (desimal)
        
        // Parse ke float
        const number = parseFloat(cleanString);
        
        // Jika hasil NaN, return 0
        return isNaN(number) ? 0 : number;
    }

    setupRupiahFormatting() {
        console.log('🔧 Setting up rupiah formatting...');
        
        // Setup untuk input jumlah STS di form tambah
        const jumlahStsInputTambah = document.querySelector('#formTambahSTS input[name="jumlah_sts"]');
        if (jumlahStsInputTambah) {
            // Ubah type menjadi text
            jumlahStsInputTambah.type = 'text';
            jumlahStsInputTambah.pattern = '[0-9.,]*';
            jumlahStsInputTambah.placeholder = 'Contoh: 24.000,00';
            
            jumlahStsInputTambah.addEventListener('input', (e) => {
                const cursorPosition = e.target.selectionStart;
                const originalValue = e.target.value;
                
                // Format nilai
                const formatted = this.formatRupiahInput(originalValue);
                e.target.value = formatted;
                
                // Kembalikan cursor position
                const diff = formatted.length - originalValue.length;
                e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
            });
            
            jumlahStsInputTambah.addEventListener('blur', (e) => {
                if (e.target.value) {
                    // Parse untuk validasi
                    const number = this.parseRupiahToNumber(e.target.value);
                    if (number > 0) {
                        // Format dengan 2 desimal
                        e.target.value = this.formatRupiahDisplay(number);
                    }
                }
            });
            
            jumlahStsInputTambah.addEventListener('focus', (e) => {
                if (e.target.value) {
                    // Saat focus, hapus titik (pemisah ribuan) untuk memudahkan editing
                    const number = this.parseRupiahToNumber(e.target.value);
                    e.target.value = number.toString().replace('.', ',');
                }
            });
            
            console.log('✅ Setup rupiah formatting for tambah STS');
        }
        
        // Setup untuk input jumlah STS di form edit
        const jumlahStsInputEdit = document.getElementById('editJmlSts');
        if (jumlahStsInputEdit) {
            jumlahStsInputEdit.type = 'text';
            jumlahStsInputEdit.pattern = '[0-9.,]*';
            jumlahStsInputEdit.placeholder = 'Contoh: 24.000,00';
            
            jumlahStsInputEdit.addEventListener('input', (e) => {
                const cursorPosition = e.target.selectionStart;
                const originalValue = e.target.value;
                
                const formatted = this.formatRupiahInput(originalValue);
                e.target.value = formatted;
                
                const diff = formatted.length - originalValue.length;
                e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
            });
            
            jumlahStsInputEdit.addEventListener('blur', (e) => {
                if (e.target.value) {
                    const number = this.parseRupiahToNumber(e.target.value);
                    if (number > 0) {
                        e.target.value = this.formatRupiahDisplay(number);
                    }
                }
            });
            
            jumlahStsInputEdit.addEventListener('focus', (e) => {
                if (e.target.value) {
                    const number = this.parseRupiahToNumber(e.target.value);
                    e.target.value = number.toString().replace('.', ',');
                }
            });
            
            console.log('✅ Setup rupiah formatting for edit STS');
        }
        
        // Setup untuk input nominal bayar
        const payNominalInput = document.getElementById('payNominal');
        if (payNominalInput) {
            payNominalInput.type = 'text';
            payNominalInput.pattern = '[0-9.,]*';
            payNominalInput.placeholder = 'Contoh: 24.000,00';
            
            payNominalInput.addEventListener('input', (e) => {
                const cursorPosition = e.target.selectionStart;
                const originalValue = e.target.value;
                
                const formatted = this.formatRupiahInput(originalValue);
                e.target.value = formatted;
                
                const diff = formatted.length - originalValue.length;
                e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
            });
            
            payNominalInput.addEventListener('blur', (e) => {
                if (e.target.value) {
                    const number = this.parseRupiahToNumber(e.target.value);
                    if (number > 0) {
                        e.target.value = this.formatRupiahDisplay(number);
                    }
                }
            });
            
            console.log('✅ Setup rupiah formatting for bayar nominal');
        }
        
        // Setup untuk input edit nominal bayar
        const editPayNominalInput = document.getElementById('editPayNominal');
        if (editPayNominalInput) {
            editPayNominalInput.type = 'text';
            editPayNominalInput.pattern = '[0-9.,]*';
            editPayNominalInput.placeholder = 'Contoh: 24.000,00';
            
            editPayNominalInput.addEventListener('input', (e) => {
                const cursorPosition = e.target.selectionStart;
                const originalValue = e.target.value;
                
                const formatted = this.formatRupiahInput(originalValue);
                e.target.value = formatted;
                
                const diff = formatted.length - originalValue.length;
                e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
            });
            
            editPayNominalInput.addEventListener('blur', (e) => {
                if (e.target.value) {
                    const number = this.parseRupiahToNumber(e.target.value);
                    if (number > 0) {
                        e.target.value = this.formatRupiahDisplay(number);
                    }
                }
            });
            
            console.log('✅ Setup rupiah formatting for edit bayar nominal');
        }
    }
}

// Export class untuk digunakan di file lain
export { StsManager };