document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi variabel global
    let currentStep = 1;
    let monthIndex = 1;
    let currentRkasId = null;
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    let firstSatuanValue = ''; // Variabel untuk menyimpan satuan 
    

    // Inisialisasi komponen
    initializeSelect2();
    setupEventListeners();
    showStep(1);
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
        // Next button
        document.getElementById('nextStepBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateCurrentStep()) {
                if (currentStep < 3) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });

        // Previous button
        document.getElementById('prevStepBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Add month button
        document.getElementById('addMonthBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            addMonthEntry();
        });

        // Remove month functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-month') || e.target.closest('.btn-remove-month')) {
                e.preventDefault();
                const monthEntry = e.target.closest('.month-entry');
                if (monthEntry) {
                    monthEntry.remove();
                    updateTotalAnggaran();
                }
            }
        });

        // Input changes
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('satuan-input')) {updateSatuanOtomatis(e.target.value);
            }
            if (e.target.classList.contains('satuan-input') || e.target.id === 'satuan') {
                updateTotalAnggaran();
            }
            if (e.target.classList.contains('jumlah-input') || e.target.id === 'harga_satuan') {
                updateTotalAnggaran();
            }
            if (e.target.id === 'edit-jumlah' || e.target.id === 'edit-harga-satuan') {
                updateEditTotal();
            }
            if (e.target.id === 'sisipkan_harga_satuan' || e.target.id === 'sisipkan_jumlah') {
                updateSisipkanTotal();
            }

        });

        // Month tab click handler
        document.querySelectorAll('.nav-link[data-month]').forEach(function(tab) {
            tab.addEventListener('click', function() {
                const month = this.getAttribute('data-month');
                refreshMonthData(month);
            });
        });

        // Form submission
        document.getElementById('tambahRkasForm')?.addEventListener('submit', function(e) {
            if (!validateAllSteps()) {
                e.preventDefault();
                return false;
            }
            const submitBtn = document.getElementById('submitBtn');
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

        // Sisipkan form submission
        document.getElementById('sisipkanRkasForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const modal = bootstrap.Modal.getInstance(document.getElementById('sisipkanRkasModal'));
            const currentMonth = getActiveTab();

            submitButton.innerHTML = '<span class="loading-spinner me-2"></span>Menyimpan...';
            submitButton.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        localStorage.setItem('activeRkasTab', currentMonth);
                        Swal.fire('Berhasil!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menyimpan data');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.message, 'error');
                })
                .finally(() => {
                    submitButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
                    submitButton.disabled = false;
                });
        });

    }

    function updateSatuanOtomatis(satuanValue) {
        // Simpan nilai satuan pertama
        firstSatuanValue = satuanValue;
        
        // Update semua input satuan kecuali yang pertama
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
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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

    // Fungsi-fungsi utama
    function showStep(step) {
        // Update progress indicator
        document.querySelectorAll('.step').forEach(function(stepEl) {
            stepEl.classList.remove('active', 'completed');
        });

        for (let i = 1; i < step; i++) {
            const stepEl = document.getElementById(`step-${i}`);
            if (stepEl) stepEl.classList.add('completed');
        }

        const activeStepEl = document.getElementById(`step-${step}`);
        if (activeStepEl) activeStepEl.classList.add('active');

        // Show/hide form steps
        document.querySelectorAll('.form-step').forEach(function(formStep) {
            formStep.style.display = 'none';
        });

        const currentFormStep = document.getElementById(`form-step-${step}`);
        if (currentFormStep) currentFormStep.style.display = 'block';

        // Update buttons
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        if (prevBtn) prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
        if (nextBtn) nextBtn.style.display = step < 3 ? 'inline-block' : 'none';
        if (submitBtn) submitBtn.style.display = step === 3 ? 'inline-block' : 'none';
    }

    function validateCurrentStep() {
        let isValid = true;
        const currentStepElement = document.getElementById(`form-step-${currentStep}`);

        if (currentStepElement) {
            const requiredFields = currentStepElement.querySelectorAll(
                'input[required], select[required], textarea[required]');

            requiredFields.forEach(function(field) {
                field.classList.remove('is-invalid');
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
        }

        if (!isValid) {
            Swal.fire('Error', 'Mohon lengkapi semua field yang diperlukan.', 'error');
        }

        return isValid;
    }

    function validateAllSteps() {
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
        const monthSelects = document.querySelectorAll('.month-select');

        monthSelects.forEach(function(select) {
            const month = select.value;
            if (month) {
                if (selectedMonths.includes(month)) {
                    isValid = false;
                    Swal.fire('Error', 'Tidak boleh ada bulan yang sama dalam satu kegiatan.', 'error');
                    return false;
                }
                selectedMonths.push(month);
            }
        });

        return isValid;
    }

    function addMonthEntry() {
        // Ambil nilai satuan pertama jika ada
        const firstSatuan = document.querySelector('.satuan-input')?.value || '';

        const monthTemplate = `
            <div class="month-entry border rounded p-3 mb-3" data-index="${monthIndex}">
                <div class="row align-items-center mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Bulan <span class="text-danger">*</span></label>
                        <select class="form-select month-select" name="bulan[]" required>
                            <option value="">Pilih Bulan</option>
                            ${months.map(month => `<option value="${month}">${month}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control jumlah-input"
                            name="jumlah[]" placeholder="0" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control satuan-input"
                            name="satuan[]" placeholder="pcs, unit, buah" value="${firstSatuan}" ${firstSatuan ? 'readonly' : ''} required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total</label>
                        <div class="month-total badge bg-info w-100 mb-2">Rp 0</div>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-month">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        const container = document.getElementById('bulanContainer');
        if (container) {
            container.insertAdjacentHTML('beforeend', monthTemplate);
            monthIndex++;

            updateTotalAnggaran();
        }
    }

    function updateTotalAnggaran() {
        let total = 0;
        const hargaSatuan = parseFloat(document.getElementById('harga_satuan')?.value) || 0;

        document.querySelectorAll('.month-entry').forEach(function(entry) {
            const jumlah = parseFloat(entry.querySelector('.jumlah-input')?.value) || 0;
            const monthTotal = hargaSatuan * jumlah;

            const monthTotalEl = entry.querySelector('.month-total');
            if (monthTotalEl) {
                monthTotalEl.textContent = 'Rp ' + formatNumber(monthTotal);
            }
            total += monthTotal;
        });

        const totalDisplay = document.getElementById('totalAnggaranDisplay');
        if (totalDisplay) {
            totalDisplay.textContent = 'Total: Rp ' + formatNumber(total);
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

    function updateSisipkanTotal() {
        const hargaSatuan = parseFloat(document.getElementById('sisipkan_harga_satuan')?.value) || 0;
        const jumlah = parseFloat(document.getElementById('sisipkan_jumlah')?.value) || 0;
        const total = hargaSatuan * jumlah;

        document.getElementById('sisipkan_total_display').textContent = 'Rp ' + formatNumber(total);
    }

    function resetModal() {
        currentStep = 1;
        showStep(1);
        firstSatuanValue = ''; // Reset nilai satuan pertama

        const form = document.getElementById('tambahRkasForm');
        if (form) form.reset();

        $('.select2-kegiatan').val(null).trigger('change');
        $('.select2-rekening').val(null).trigger('change');

        document.querySelectorAll('.form-control').forEach(function(field) {
            field.classList.remove('is-invalid');
        });

        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Simpan Data';
            submitBtn.disabled = false;
        }

        // Reset to single month entry
        const container = document.getElementById('bulanContainer');
        if (container) {
            container.innerHTML = `
                <div class="month-entry border rounded p-3 mb-3" data-index="0">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Bulan <span class="text-danger">*</span></label>
                            <select class="form-select month-select" name="bulan[]" required>
                                <option value="">Pilih Bulan</option>
                                ${months.map(month => `<option value="${month}">${month}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" class="form-control jumlah-input"
                                name="jumlah[]" placeholder="0" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control satuan-input"
                                name="satuan[]" placeholder="pcs, unit, buah" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total</label>
                            <div class="month-total badge bg-info w-100">Rp 0</div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Reset semua input satuan menjadi editable
        document.querySelectorAll('.satuan-input').forEach(input => {
            input.readOnly = false;
        });

        monthIndex = 1;
        updateTotalAnggaran();
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
                                <a class="dropdown-item" href="#" onclick="showSisipkanModal(
                                    ${item.kode_id},
                                    '${escapeHtml(item.program_kegiatan)}',
                                    '${escapeHtml(item.kegiatan)}',
                                    ${item.kode_rekening_id},
                                    '${escapeHtml(item.rekening_belanja)}'
                                )">
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

    window.showSisipkanModal = function(kodeId, program, kegiatan, rekeningId, rekeningDisplay) {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        const bulan = activeTab ? activeTab.getAttribute('data-month') : '';

        // Set nilai form
        document.getElementById('sisipkan_kode_id').value = kodeId;
        document.getElementById('sisipkan_kode_rekening_id').value = rekeningId;
        document.getElementById('sisipkan_bulan').value = bulan;
        document.getElementById('sisipkan_program').value = program;
        document.getElementById('sisipkan_kegiatan').value = kegiatan;
        document.getElementById('sisipkan_rekening_belanja_display').value = rekeningDisplay;

        // Reset form lainnya
        document.getElementById('sisipkan_uraian').value = '';
        document.getElementById('sisipkan_harga_satuan').value = '';
        document.getElementById('sisipkan_jumlah').value = '';
        document.getElementById('sisipkan_satuan').value = '';
        document.getElementById('sisipkan_total_display').textContent = 'Rp 0';

        // Tampilkan modal
        new bootstrap.Modal(document.getElementById('sisipkanRkasModal')).show();
    };

    // Setelah halaman dimuat, aktifkan tab yang sesuai
    document.addEventListener('DOMContentLoaded', function() {
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
    });
});

// Fungsi untuk refresh data bulan yang aktif
function refreshCurrentMonthData() {
    const activeTab = document.querySelector('#monthTabs .nav-link.active');
    if (activeTab) {
        const month = activeTab.getAttribute('data-month');
        refreshMonthData(month.toLowerCase());
    }

    
}

