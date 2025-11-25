// Inisialisasi RkasManager
document.addEventListener('DOMContentLoaded', function() {
    // Buat instance RkasManager
    window.rkasManager = new RkasManager();
    
    // Inisialisasi semua komponen
    window.rkasManager.initialize();
    
    // Handle saved tab - PERBAIKAN: handle dengan benar setelah initialize
    const savedTab = localStorage.getItem('activeRkasTab');
    if (savedTab && savedTab !== 'Januari') {
        console.log('🔧 [TAB DEBUG] Restoring saved tab:', savedTab);
        // Tunggu sebentar untuk memastikan Bootstrap sudah initialize
        setTimeout(() => {
            window.rkasManager.setActiveTab(savedTab);
            localStorage.removeItem('activeRkasTab');
        }, 100);
    } else {
        // Pastikan Januari aktif sebagai default
        console.log('🔧 [TAB DEBUG] Setting default tab to Januari');
        setTimeout(() => {
            window.rkasManager.setActiveTab('Januari');
            localStorage.removeItem('activeRkasTab');
        }, 100);
    }

    // Handle tab change event - PERBAIKAN: simplifikasi
    document.querySelectorAll('#monthTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function() {
            const month = this.getAttribute('data-month');
            localStorage.setItem('activeRkasTab', month);
        });
    });

    console.log('=== RKAS JS LOADED SUCCESSFULLY ===');
});

// Fungsi global untuk kompatibilitas dengan kode existing
function refreshCurrentMonthData() {
    if (window.rkasManager) {
        const activeTab = document.querySelector('#monthTabs .nav-link.active');
        if (activeTab) {
            const month = activeTab.getAttribute('data-month');
            console.log('🔧 [TAB DEBUG] Refreshing data for:', month);
            window.rkasManager.refreshMonthData(month.toLowerCase());
        }
    }
}

// Export fungsi global untuk kompatibilitas
window.showEditModal = function(id) {
    if (window.rkasManager) {
        window.rkasManager.showEditModal(id);
    }
};

window.showDetailModal = function(id) {
    if (window.rkasManager) {
        window.rkasManager.showDetailModal(id);
    }
};

window.showDeleteModal = function(id) {
    if (window.rkasManager) {
        window.rkasManager.currentRkasId = id;

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
    }
};

window.editFromDetail = function() {
    if (window.rkasManager && window.rkasManager.currentRkasId) {
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailRkasModal'));
        detailModal.hide();
        setTimeout(() => window.rkasManager.showEditModal(window.rkasManager.currentRkasId), 300);
    }
};

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

// Kompatibilitas dengan ButtonRkasPerubahan - tambahkan di akhir file rkas.js
document.addEventListener('DOMContentLoaded', function() {
    // Tunggu sebentar untuk memastikan ButtonRkasPerubahan sudah initialize
    setTimeout(() => {
        // Cek status dari data attribute
        const bodyElement = document.body;
        const hasPerubahan = bodyElement.getAttribute('data-has-perubahan');
        
        if (hasPerubahan === 'true' && window.rkasManager) {
            console.log('✅ [RKAS] Disabling RKAS based on PHP variable');
            
            const tableContainers = document.querySelectorAll('.rkas-table-container');
            tableContainers.forEach(container => {
                container.classList.add('table-disabled');
            });
            
            const btnTambah = document.getElementById('btnTambah');
            if (btnTambah) {
                btnTambah.style.display = 'none';
            }
        }
    }, 1000);
});