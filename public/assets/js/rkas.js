document.addEventListener('DOMContentLoaded', function() {
    console.log('=== RKAS JS LOADED SUCCESSFULLY ===');
    
    // Inisialisasi variabel global
    let currentRkasId = null;
    let monthIndex = 1;
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    let firstSatuanValue = '';

    // Inisialisasi komponen
    initializeSelect2();
    setupEventListeners();
    initializeAnggaranList();
    updateTahapCards();

    // Fungsi inisialisasi Select2
    function initializeSelect2() {
        $('.select2-kegiatan').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#tambahRkasModal'),
            templateResult: formatKegiatanOption,
            templateSelection: formatKegiatanSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#tambahRkasModal'),
            templateResult: formatRekeningOption,
            templateSelection: formatRekeningSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-kegiatan-edit').select2({
            placeholder: '-- Pilih Kegiatan --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: formatKegiatanOption,
            templateSelection: formatKegiatanSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.select2-rekening-edit').select2({
            placeholder: '-- Pilih Rekening Belanja --',
            allowClear: true,
            dropdownParent: $('#editRkasModal'),
            templateResult: formatRekeningOption,
            templateSelection: formatRekeningSelection,
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }

    // Fungsi setup event listeners
    function setupEventListeners() {
        // Input changes
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('satuan-input')) {
                updateSatuanOtomatis(e.target.value);
            }
            if (e.target.classList.contains('jumlah-input') || e.target.id === 'harga_satuan') {
                updateTotalAnggaran();
            }
            if (e.target.id === 'edit-jumlah' || e.target.id === 'edit-harga-satuan') {
                updateEditTotal();
            }
            if (e.target.id === 'sisipkan_harga_satuan' || e.target.id === 'sisipkan_jumlah') {
                calculateSisipkanTotal();
            }
        });

        // Form submission
        document.getElementById('tambahRkasForm')?.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
                submitBtn.disabled = true;
            }
        });

        // Edit form submission
        document.getElementById('editRkasForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<span class="loading-spinner me-2"></span>Updating...';
            submitButton.disabled = true;

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', 'Data berhasil diupdate', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal mengupdate data');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.message, 'error');
                })
                .finally(() => {
                    submitButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Data';
                    submitButton.disabled = false;
                });
        });

        // Delete confirmation
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
            if (currentRkasId) {
                deleteRkas(currentRkasId);
            }
        });

        // Modal reset on close
        document.getElementById('tambahRkasModal')?.addEventListener('hidden.bs.modal', function() {
            resetModal();
        });

        // Month tab click handler
        document.querySelectorAll('.nav-link[data-month]').forEach(function(tab) {
            tab.addEventListener('click', function() {
                const month = this.getAttribute('data-month');
                refreshMonthData(month);
            });
        });

        // Sisipkan form submission
        document.getElementById('sisipkanRkasForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            submitSisipkanForm(this);
        });

        // Event listener untuk tombol sisipkan
        document.addEventListener('click', function(e) {
            if (e.target.closest('.sisipkan-btn')) {
                e.preventDefault();
                handleSisipkanButtonClick(e.target.closest('.sisipkan-btn'));
            }
        });
    }

    // ==============================================
    // SISIPKAN RKAS FUNCTIONALITY
    // ==============================================

    function handleSisipkanButtonClick(button) {
        console.log('=== SISIPKAN BUTTON CLICKED ===');
        
        // Ambil data dari attributes
        const kodeId = button.getAttribute('data-kode-id');
        const program = button.getAttribute('data-program');
        const kegiatan = button.getAttribute('data-kegiatan');
        const rekeningId = button.getAttribute('data-rekening-id');
        const rekeningDisplay = button.getAttribute('data-rekening-display');
        
        console.log('Data from attributes:', {
            kodeId: kodeId,
            program: program,
            kegiatan: kegiatan,
            rekeningId: rekeningId,
            rekeningDisplay: rekeningDisplay
        });
        
        // Validasi data
        if (!kodeId || !rekeningId || kodeId === 'undefined' || rekeningId === 'undefined') {
            console.error('Invalid data received:', { kodeId, rekeningId });
            Swal.fire('Error', 'Data tidak valid. Silakan refresh halaman dan coba lagi.', 'error');
            return;
        }
        
        // Convert to numbers
        const validKodeId = parseInt(kodeId);
        const validRekeningId = parseInt(rekeningId);
        
        console.log('After conversion:', {
            validKodeId: validKodeId,
            validRekeningId: validRekeningId
        });
        
        if (isNaN(validKodeId) || isNaN(validRekeningId)) {
            console.error('Invalid IDs after conversion:', { validKodeId, validRekeningId });
            Swal.fire('Error', 'ID kegiatan atau rekening tidak valid.', 'error');
            return;
        }
        
        // Dapatkan bulan aktif
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const bulan = activeTab ? activeTab.getAttribute('data-month') : '';
        console.log('Active bulan:', bulan);
        
        if (!bulan) {
            Swal.fire('Error', 'Tidak dapat menentukan bulan tujuan.', 'error');
            return;
        }
        
        // Set nilai form
        setSisipkanFormValues(validKodeId, program, kegiatan, validRekeningId, rekeningDisplay, bulan);
        
        // Tampilkan modal
        const modal = new bootstrap.Modal(document.getElementById('sisipkanRkasModal'));
        modal.show();
        
        console.log('=== SISIPKAN MODAL SHOWN ===');
    }

    function setSisipkanFormValues(kodeId, program, kegiatan, rekeningId, rekeningDisplay, bulan) {
        // Set hidden values
        document.getElementById('sisipkan_kode_id').value = kodeId;
        document.getElementById('sisipkan_kode_rekening_id').value = rekeningId;
        document.getElementById('sisipkan_bulan').value = bulan;
        
        // Set display values
        document.getElementById('sisipkan_program').value = program || '-';
        document.getElementById('sisipkan_kegiatan').value = kegiatan || '-';
        document.getElementById('sisipkan_rekening_belanja_display').value = rekeningDisplay || '-';
        document.getElementById('sisipkan_bulan_display').value = bulan;
        
        // Reset input values
        document.getElementById('sisipkan_uraian').value = '';
        document.getElementById('sisipkan_harga_satuan').value = '';
        document.getElementById('sisipkan_jumlah').value = '';
        document.getElementById('sisipkan_satuan').value = '';
        document.getElementById('sisipkan_total_display').textContent = 'Rp 0';
        
        console.log('Form values set:', {
            kode_id: kodeId,
            kode_rekening_id: rekeningId,
            bulan: bulan
        });
    }

    function calculateSisipkanTotal() {
        const hargaSatuan = parseFloat(document.getElementById('sisipkan_harga_satuan').value) || 0;
        const jumlah = parseFloat(document.getElementById('sisipkan_jumlah').value) || 0;
        const total = hargaSatuan * jumlah;
        
        document.getElementById('sisipkan_total_display').textContent = 'Rp ' + formatNumber(total);
    }

    function submitSisipkanForm(form) {
        const submitButton = form.querySelector('#sisipkanSubmitBtn');
        const modal = bootstrap.Modal.getInstance(document.getElementById('sisipkanRkasModal'));
        
        // Validasi form
        if (!validateSisipkanForm()) {
            return;
        }
        
        // Show loading
        submitButton.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
        submitButton.disabled = true;
        
        // Prepare form data
        const formData = new FormData(form);
        
        console.log('Submitting form data:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        // Send request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            const text = await response.text();
            let data;
            
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid response from server');
            }
            
            if (!response.ok) {
                throw new Error(data.message || `Server error: ${response.status}`);
            }
            
            return data;
        })
        .then(data => {
            if (data.success) {
                modal.hide();
                // Simpan tab aktif untuk refresh
                const activeTab = document.querySelector('#monthTabs .nav-link.active');
                if (activeTab) {
                    localStorage.setItem('activeRkasTab', activeTab.getAttribute('data-month'));
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Gagal menyimpan data');
            }
        })
        .catch(error => {
            console.error('Sisipkan error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message,
                confirmButtonText: 'OK'
            });
        })
        .finally(() => {
            submitButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
            submitButton.disabled = false;
        });
    }

    function validateSisipkanForm() {
        const requiredFields = [
            'sisipkan_uraian',
            'sisipkan_jumlah', 
            'sisipkan_satuan',
            'sisipkan_harga_satuan'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validasi numeric fields
        const hargaSatuan = parseFloat(document.getElementById('sisipkan_harga_satuan').value);
        const jumlah = parseFloat(document.getElementById('sisipkan_jumlah').value);
        
        if (hargaSatuan <= 0) {
            document.getElementById('sisipkan_harga_satuan').classList.add('is-invalid');
            isValid = false;
        }
        
        if (jumlah <= 0) {
            document.getElementById('sisipkan_jumlah').classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            Swal.fire('Error', 'Mohon lengkapi semua field yang diperlukan dengan nilai yang valid.', 'error');
        }
        
        return isValid;
    }

    // ==============================================
    // EXISTING RKAS FUNCTIONALITY
    // ==============================================

    function updateSatuanOtomatis(satuanValue) {
        firstSatuanValue = satuanValue;
        
        const allSatuanInputs = document.querySelectorAll('.satuan-input');
        allSatuanInputs.forEach((input, index) => {
            if (index > 0 && satuanValue) {
                input.value = satuanValue;
                input.readOnly = true;
            }
        });
    }

    // Fungsi-fungsi utilitas
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function getActiveTab() {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        return activeTab ? activeTab.getAttribute('data-month').toLowerCase() : null;
    }

    function setActiveTab(month) {
        if (!month) return;
        const tabButton = document.querySelector(`#monthTabs .nav-link[data-month="${month}"]`);
        if (tabButton) {
            new bootstrap.Tab(tabButton).show();
        }
    }

    function createAnggaranCard() {
        const card = document.createElement('div');
        card.className = 'anggaran-bulan-card';
        card.innerHTML = `
            <button type="button" class="delete-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash"><path d="M3 6h18"/><path d="M19 6v14c0 1.1-0.9 2-2 2H7c-1.1 0-2-0.9-2-2V6"/><path d="M8 6V4c0-1.1 0.9-2 2-2h4c1.1 0 2 0.9 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            </button>
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                <select class="form-control bulan-input" name="bulan[]" required>
                    <option value="">Pilih Bulan</option>
                    ${months.map(b => `<option value="${b}">${b}</option>`).join('')}
                </select>
                <span class="uang-display month-total" style="flex: 1; text-align: right; font-weight: 500;">Rp 0</span>
            </div>
            <div class="jumlah-satuan-group form-row">
                <div class="form-col">
                    <div class="input-icon-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <input type="number" class="form-control jumlah-input" name="jumlah[]" placeholder="Jumlah" min="1" required>
                    </div>
                </div>
                <div class="form-col">
                    <input type="text" class="form-control satuan-input" name="satuan[]" placeholder="Satuan" required>
                </div>
            </div>
        `;
        
        const deleteBtn = card.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', () => {
            card.remove();
            checkTambahButtonVisibility();
            updateTotalAnggaran();
        });

        // Add event listeners for inputs
        const jumlahInput = card.querySelector('.jumlah-input');
        const bulanSelect = card.querySelector('.bulan-input');
        
        jumlahInput.addEventListener('input', updateTotalAnggaran);
        bulanSelect.addEventListener('change', updateTotalAnggaran);

        return card;
    }

    function createTambahButtonCard() {
        const btnCard = document.createElement('div');
        btnCard.className = 'btn-tambah-card';
        btnCard.innerHTML = `
            <button type="button" class="btn-tambah" id="btn-tambah-bulan">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Tambah Bulan
            </button>
        `;
        
        const tambahBtn = btnCard.querySelector('#btn-tambah-bulan');
        tambahBtn.addEventListener('click', handleTambahBulanClick);
        
        return btnCard;
    }

    function checkTambahButtonVisibility() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        if (cards.length >= 12) {
            if (tambahBtnCard) tambahBtnCard.style.display = 'none';
        } else {
            if (tambahBtnCard) tambahBtnCard.style.display = 'flex';
        }
    }

    function initializeAnggaranList() {
        const container = document.getElementById('bulanContainer');
        if (!container) return;

        container.innerHTML = '';
        const initialCard = createAnggaranCard();
        const tambahBtnCard = createTambahButtonCard();
        container.appendChild(initialCard);
        container.appendChild(tambahBtnCard);
        
        checkTambahButtonVisibility();
        updateTotalAnggaran();
    }

    function handleTambahBulanClick() {
        const cards = document.querySelectorAll('.anggaran-bulan-card');
        if (cards.length >= 12) {
            return;
        }

        const container = document.getElementById('bulanContainer');
        const tambahBtnCard = document.querySelector('.btn-tambah-card');
        const newCard = createAnggaranCard();
        
        container.insertBefore(newCard, tambahBtnCard);
        checkTambahButtonVisibility();
        updateTotalAnggaran();
    }

    function updateTotalAnggaran() {
        let total = 0;
        const hargaSatuan = parseFloat(document.getElementById('harga_satuan')?.value) || 0;

        document.querySelectorAll('.anggaran-bulan-card').forEach(function(card) {
            const jumlah = parseFloat(card.querySelector('.jumlah-input')?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = card.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        const totalDisplay = document.getElementById('total-anggaran');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + formatNumber(total);
        }
    }

    function updateEditTotal() {
        const jumlah = parseFloat(document.getElementById('edit-jumlah')?.value) || 0;
        const hargaSatuan = parseFloat(document.getElementById('edit-harga-satuan')?.value) || 0;
        const total = jumlah * hargaSatuan;

        const totalDisplay = document.getElementById('edit-total-display');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + formatNumber(total);
        }
    }

    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll(
            '#tambahRkasForm input[required], #tambahRkasForm select[required], #tambahRkasForm textarea[required]'
        );

        requiredFields.forEach(function(field) {
            field.classList.remove('is-invalid');
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        // Validate month uniqueness
        const selectedMonths = [];
        const monthSelects = document.querySelectorAll('.bulan-input');

        monthSelects.forEach(function(select) {
            const month = select.value;
            if (month) {
                if (selectedMonths.includes(month)) {
                    isValid = false;
                    select.classList.add('is-invalid');
                    Swal.fire('Error', 'Tidak boleh ada bulan yang sama dalam satu kegiatan.', 'error');
                    return false;
                }
                selectedMonths.push(month);
            }
        });

        if (!isValid) {
            Swal.fire('Error', 'Mohon lengkapi semua field yang diperlukan.', 'error');
        }

        return isValid;
    }

    function resetModal() {
        firstSatuanValue = '';

        const form = document.getElementById('tambahRkasForm');
        if (form) form.reset();

        $('.select2-kegiatan').val(null).trigger('change');
        $('.select2-rekening').val(null).trigger('change');

        document.querySelectorAll('.form-control').forEach(function(field) {
            field.classList.remove('is-invalid');
        });

        const submitBtn = document.querySelector('#tambahRkasForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
            submitBtn.disabled = false;
        }

        // Reset semua input satuan menjadi editable
        document.querySelectorAll('.satuan-input').forEach(input => {
            input.readOnly = false;
        });

        initializeAnggaranList();
    }

    function refreshMonthData(month) {
        console.log('Memulai refresh data untuk bulan:', month);
        const formattedMonth = month.charAt(0).toUpperCase() + month.slice(1).toLowerCase();
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        
        if (!tableBody) {
            console.error('Table body tidak ditemukan untuk bulan:', month);
            return;
        }

        // Tampilkan loading
        tableBody.innerHTML = `<tr>
            <td colspan="11" class="text-center">
                <div class="py-4">
                    <div class="loading-spinner me-2"></div>Memuat data...
                </div>
            </td>
        </tr>`;

        fetch(`/rkas/bulan/${formattedMonth}?tahun=${document.querySelector('input[name="tahun_anggaran"]').value}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            console.log('Data diterima:', data);
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    populateTable(month, data.data);
                } else {
                    showNoDataMessage(month);
                }
            } else {
                throw new Error(data.message || 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error saat refresh:', error);
            tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-danger">
                    <div class="py-4">
                        <i class="bi bi-exclamation-triangle display-4"></i>
                        <p class="mt-2">Gagal memuat data: ${error.message}</p>
                    </div>
                </td>
            </tr>
            `;
        });
    }

    function populateTable(month, items) {
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        if (!tableBody) {
            console.error('Table body tidak ditemukan');
            return;
        }

        let html = '';
        let total = 0;

        items.forEach((item, index) => {
            const itemTotal = parseFloat(item.total.replace(/[^\d]/g, ''));
            total += itemTotal;

            html += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.program_kegiatan || '-'}</td>
                <td>${item.kegiatan || '-'}</td>
                <td>${item.rekening_belanja || '-'}</td>
                <td>${item.uraian || '-'}</td>
                <td>${item.dianggaran || '0'}</td>
                <td>0</td>
                <td>${item.satuan || '-'}</td>
                <td>${item.harga_satuan || 'Rp 0'}</td>
                <td><strong>${item.total || 'Rp 0'}</strong></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionDropdown${item.id}"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown${item.id}">
                            <li>
                                <a class="dropdown-item" href="#" onclick="showDetailModal(${item.id})">
                                    <i class="bi bi-eye me-2"></i>Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sisipkan-btn" href="#" 
                                    data-kode-id="${item.kode_id}"
                                    data-program="${escapeHtml(item.program_kegiatan)}"
                                    data-kegiatan="${escapeHtml(item.kegiatan)}"
                                    data-rekening-id="${item.kode_rekening_id}"
                                    data-rekening-display="${escapeHtml(item.rekening_belanja)}">
                                    <i class="bi bi-archive-fill me-2 text-warning"></i>Sisipkan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showEditModal(${item.id})">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="showDeleteModal(${item.id})">
                                    <i class="bi bi-trash me-2"></i>Hapus
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            `;
        });

        // Add total row
        html += `
        <tr class="table-info">
            <td colspan="9" class="text-end"><strong>Total ${month}:</strong></td>
            <td><strong>Rp ${formatNumber(total)}</strong></td>
            <td></td>
        </tr>
        `;

        tableBody.innerHTML = html;
    }

    function showNoDataMessage(month) {
        const tableBody = document.getElementById(`table-body-${month.toLowerCase()}`);
        tableBody.innerHTML = `
            <tr class="no-data-row">
                <td colspan="11" class="text-center text-muted">
                    <div class="py-4">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Belum ada data untuk bulan ${month}</p>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#tambahRkasModal">
                            <i class="bi bi-plus me-2"></i>Tambah Data
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function updateTahapCards() {
        const tahunAnggaran = document.querySelector('input[name="tahun_anggaran"]').value;

        // Update Tahap 1
        fetch(`/rkas/total-tahap1?tahun=${tahunAnggaran}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tahap1Data = data.data;
                    const totalTahap1Element = document.getElementById('totalTahap1');
                    const sisaTahap1Element = document.getElementById('sisaTahap1');
                    const percentTahap1Element = document.getElementById('percentTahap1');

                    if (totalTahap1Element) {
                        totalTahap1Element.textContent = 'Rp ' + formatNumber(tahap1Data.total_anggaran);
                    }
                    if (sisaTahap1Element) {
                        sisaTahap1Element.textContent = 'Rp ' + formatNumber(tahap1Data.sisa_anggaran);
                    }
                    if (percentTahap1Element) {
                        percentTahap1Element.textContent = Math.round(tahap1Data.persentase_terpakai) + '%';
                    }

                    // Update progress bar
                    const progressBar1 = document.querySelector('.col-lg-4:nth-child(2) .progress-bar');
                    if (progressBar1) {
                        setTimeout(() => {
                            progressBar1.style.width = tahap1Data.persentase_terpakai + '%';
                        }, 300);
                    }
                }
            })
            .catch(error => console.error('Error updating Tahap 1:', error));

        // Update Tahap 2
        fetch(`/rkas/total-tahap2?tahun=${tahunAnggaran}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tahap2Data = data.data;
                    const totalTahap2Element = document.getElementById('totalTahap2');
                    const sisaTahap2Element = document.getElementById('sisaTahap2');
                    const percentTahap2Element = document.getElementById('percentTahap2');

                    if (totalTahap2Element) {
                        totalTahap2Element.textContent = 'Rp ' + formatNumber(tahap2Data.total_anggaran);
                    }
                    if (sisaTahap2Element) {
                        sisaTahap2Element.textContent = 'Rp ' + formatNumber(tahap2Data.sisa_anggaran);
                    }
                    if (percentTahap2Element) {
                        percentTahap2Element.textContent = Math.round(tahap2Data.persentase_terpakai) + '%';
                    }

                    // Update progress bar
                    const progressBar2 = document.querySelector('.col-lg-4:nth-child(3) .progress-bar');
                    if (progressBar2) {
                        setTimeout(() => {
                            progressBar2.style.width = tahap2Data.persentase_terpakai + '%';
                        }, 300);
                    }
                }
            })
            .catch(error => console.error('Error updating Tahap 2:', error));
    }

    // Fungsi-fungsi untuk Select2 templates
    function formatKegiatanOption(option) {
        if (!option.id) return option.text;

        const element = option.element;
        const kode = $(element).data('kode') || '';
        const program = $(element).data('program') || '';
        const subProgram = $(element).data('sub-program') || '';
        const uraian = $(element).data('uraian') || '';

        if (!kode && !program && !subProgram && !uraian) return option.text;

        return $(`
            <table class="select2-table">
                <thead>
                    <tr>
                        <th class="select2-table-kode">Kode</th>
                        <th class="select2-table-program">Program</th>
                        <th class="select2-table-sub-program">Sub Program</th>
                        <th class="select2-table-uraian">Uraian</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="select2-table-kode">${kode}</td>
                        <td class="select2-table-program">${program}</td>
                        <td class="select2-table-sub-program">${subProgram}</td>
                        <td class="select2-table-uraian">${uraian}</td>
                    </tr>
                </tbody>
            </table>
        `);
    }

    function formatKegiatanSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kode = $(element).data('kode') || '';
        const uraian = $(element).data('uraian') || '';
        return kode + ' - ' + uraian;
    }

    function formatRekeningOption(option) {
        if (!option.id) return option.text;

        const element = option.element;
        const kodeRekening = $(element).data('kode-rekening') || '';
        const rincianObjek = $(element).data('rincian-objek') || '';

        if (!kodeRekening && !rincianObjek) return option.text;

        return $(`
            <table class="select2-table">
                <thead>
                    <tr>
                        <th class="select2-table-rekening">Kode Rekening</th>
                        <th class="select2-table-rincian">Rekening Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="select2-table-rekening">${kodeRekening}</td>
                        <td class="select2-table-rincian">${rincianObjek}</td>
                    </tr>
                </tbody>
            </table>
        `);
    }

    function formatRekeningSelection(option) {
        if (!option.id) return option.text;
        const element = option.element;
        const kodeRekening = $(element).data('kode-rekening') || '';
        const rincianObjek = $(element).data('rincian-objek') || '';
        return kodeRekening + ' - ' + rincianObjek;
    }

    // Fungsi-fungsi global yang dipanggil dari HTML
    window.showDetailModal = function(id) {
        currentRkasId = id;

        fetch(`/rkas/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rkas = data.data;

                    // Populate detail modal
                    document.getElementById('detail-program').textContent = rkas.program_kegiatan || '-';
                    document.getElementById('detail-kegiatan').textContent = rkas.kegiatan || '-';
                    document.getElementById('detail-rekening').textContent = rkas.rekening_belanja || '-';
                    document.getElementById('detail-bulan').textContent = rkas.bulan || '-';
                    document.getElementById('detail-uraian').textContent = rkas.uraian || '-';
                    document.getElementById('detail-jumlah').textContent = rkas.dianggaran || '-';
                    document.getElementById('detail-satuan').textContent = rkas.satuan || '-';
                    document.getElementById('detail-harga-satuan').textContent = rkas.harga_satuan || '-';
                    document.getElementById('detail-total').textContent = rkas.total || '-';

                    // Show modal
                    new bootstrap.Modal(document.getElementById('detailRkasModal')).show();
                } else {
                    Swal.fire('Error', 'Gagal memuat detail data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memuat detail data', 'error');
            });
    };

    window.showEditModal = function(id) {
        currentRkasId = id;

        fetch(`/rkas/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rkas = data.data;

                    // Populate edit form
                    $('.select2-kegiatan-edit').val(rkas.kode_id).trigger('change');
                    $('.select2-rekening-edit').val(rkas.kode_rekening_id).trigger('change');
                    document.getElementById('edit-uraian').value = rkas.uraian || '';
                    document.getElementById('edit-bulan').value = rkas.bulan || '';
                    document.getElementById('edit-harga-satuan').value = rkas.harga_satuan_raw || '';
                    document.getElementById('edit-jumlah').value = rkas.dianggaran || '';
                    document.getElementById('edit-satuan').value = rkas.satuan || '';

                    // Set form action
                    document.getElementById('editRkasForm').action = `/rkas/${id}`;

                    // Update total
                    updateEditTotal();

                    // Show modal
                    new bootstrap.Modal(document.getElementById('editRkasModal')).show();
                } else {
                    Swal.fire('Error', 'Gagal memuat data untuk edit', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memuat data untuk edit', 'error');
            });
    };

    window.showDeleteModal = function(id) {
        currentRkasId = id;

        fetch(`/rkas/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rkas = data.data;

                    // Populate delete confirmation
                    document.getElementById('delete-kegiatan-info').textContent = rkas.kegiatan || '-';
                    document.getElementById('delete-bulan-info').textContent = rkas.bulan || '-';
                    document.getElementById('delete-total-info').textContent = rkas.total || '-';

                    // Show modal
                    new bootstrap.Modal(document.getElementById('deleteRkasModal')).show();
                } else {
                    Swal.fire('Error', 'Gagal memuat data untuk hapus', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memuat data untuk hapus', 'error');
            });
    };

    window.editFromDetail = function() {
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailRkasModal'));
        detailModal.hide();
        setTimeout(() => showEditModal(currentRkasId), 300);
    };

    function deleteRkas(id) {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.innerHTML = '<span class="loading-spinner me-2"></span>Menghapus...';
        confirmBtn.disabled = true;
        const currentMonth = getActiveTab();

        fetch(`/rkas/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    localStorage.setItem('activeRkasTab', currentMonth);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRkasModal'));
                    modal.hide();
                    Swal.fire('Berhasil!', 'Data berhasil dihapus', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Gagal menghapus data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat menghapus data: ' + error.message, 'error');
            })
            .finally(() => {
                confirmBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Ya, Hapus Data';
                confirmBtn.disabled = false;
            });
    }

    window.showTahapDetail = function(tahap) {
        const tahapName = tahap === 1 ? 'Tahap 1 (Januari - Juni)' : 'Tahap 2 (Juli - Desember)';
        const headerClass = tahap === 1 ? 'bg-primary' : 'bg-success';
        const tableHeaderClass = tahap === 1 ? 'table-primary' : 'table-success';
        const tahunAnggaran = document.querySelector('input[name="tahun_anggaran"]').value;

        fetch(`/rkas/data-tahap/${tahap}?tahun=${tahunAnggaran}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let tableContent = `
                    <div class="modal fade" id="tahapDetailModal" tabindex="-1" aria-labelledby="tahapDetailModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" style="max-width: 95%;">
                            <div class="modal-content" style="max-height: 95vh; overflow: hidden;">
                                <div class="modal-header ${headerClass} text-white">
                                    <h5 class="modal-title" id="tahapDetailModalLabel">
                                        <i class="bi bi-calendar-event me-2"></i>Detail ${tahapName} - Tahun ${tahunAnggaran}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="padding: 15px; max-height: 70vh; overflow-y: auto;">
                                    <div class="table-responsive"
                                        style="max-height: 60vh; overflow-x: auto; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; background: white;">
                                        <table class="table table-striped table-hover"
                                            style="font-size: 8pt; min-width: 100%; margin-bottom: 0;">
                                            <thead class="${tableHeaderClass}" style="position: sticky; top: 0; z-index: 10;">
                                                <tr>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">No</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Program Kegiatan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Kegiatan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Rekening Belanja</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Uraian</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Bulan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Dianggaran</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Satuan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Harga Satuan</th>
                                                    <th style="font-size: 8pt; padding: 6px; white-space: nowrap;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                    `;

                    if (data.data.length > 0) {
                        data.data.forEach((item, index) => {
                            tableContent += `
                                <tr>
                                    <td style="font-size: 8pt; padding: 6px;">${index + 1}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">
                                        ${item.program_kegiatan}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">
                                        ${item.kegiatan}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 150px;">
                                        ${item.rekening_belanja}</td>
                                    <td style="font-size: 8pt; padding: 6px; word-break: break-word; max-width: 200px;">
                                        ${item.uraian}</td>
                                    <td style="font-size: 8pt; padding: 6px;"><span
                                            class="badge ${tahap === 1 ? 'bg-info' : 'bg-success'}"
                                            style="font-size: 7pt;">${item.bulan}</span></td>
                                    <td style="font-size: 8pt; padding: 6px;">${item.dianggaran}</td>
                                    <td style="font-size: 8pt; padding: 6px;">${item.satuan}</td>
                                    <td style="font-size: 8pt; padding: 6px;">${item.harga_satuan}</td>
                                    <td style="font-size: 8pt; padding: 6px;"><strong>${item.total}</strong></td>
                                </tr>
                            `;
                        });
                    } else {
                        tableContent += `
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4" style="font-size: 8pt;">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2" style="font-size: 8pt;">Belum ada data untuk ${tahapName} Tahun
                                        ${tahunAnggaran}</p>
                                </td>
                            </tr>
                        `;
                    }

                    tableContent += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                        style="font-size: 8pt;">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                    // Remove existing modal if any
                    const existingModal = document.getElementById('tahapDetailModal');
                    if (existingModal) existingModal.remove();

                    // Add new modal to body
                    document.body.insertAdjacentHTML('beforeend', tableContent);

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('tahapDetailModal'));
                    modal.show();

                    // Remove modal from DOM when hidden
                    document.getElementById('tahapDetailModal').addEventListener('hidden.bs.modal', function() {
                        this.remove();
                    });
                } else {
                    Swal.fire('Error', 'Gagal memuat data detail tahap', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memuat data detail tahap', 'error');
            });
    };

    // Setelah halaman dimuat, aktifkan tab yang sesuai
    const savedTab = localStorage.getItem('activeRkasTab');
    if (savedTab) {
        setActiveTab(savedTab);
        localStorage.removeItem('activeRkasTab');
    }

    // Simpan tab aktif saat berpindah tab
    document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function() {
            localStorage.setItem('activeRkasTab', this.getAttribute('data-month'));
        });
    });

    console.log('=== RKAS JS INITIALIZATION COMPLETE ===');
});

// Fungsi untuk refresh data bulan yang aktif
function refreshCurrentMonthData() {
    const activeTab = document.querySelector('#monthTabs .nav-link.active');
    if (activeTab) {
        const month = activeTab.getAttribute('data-month');
        refreshMonthData(month.toLowerCase());
    }
}