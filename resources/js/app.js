import './bootstrap';
import BkuManager from './managers/BkuManager';
import StsManager from './managers/StsManager';

// Inisialisasi ketika document ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Document ready - Application loaded');
    
    // Inisialisasi BkuManager jika modal tersedia
    if (document.getElementById('transactionModal')) {
        window.bkuManager = new BkuManager();
        console.log('✅ BkuManager initialized');
    }
    
    // Inisialisasi StsManager jika ada tabel STS
    if (document.getElementById('stsTable')) {
        window.stsManager = new StsManager();
        console.log('✅ StsManager initialized');
    }
    
    // Attach event untuk search results
    document.addEventListener('searchResultsUpdated', function(e) {
        if (e.detail.page === 'bku' && window.bkuManager) {
            console.log('Re-attaching BKU event listeners after search...');
            window.bkuManager.attachBkuEventListeners();
        }
    });

    // Event untuk update saldo setelah setor tunai berhasil
    $(document).on('setorTunaiSaved', (e, response) => {
        console.log('Setor tunai saved event triggered', response);
        
        // Update saldo display jika ada data baru
        if (window.bkuManager && response.saldo_update) {
            window.bkuManager.updateSaldoDisplay(response.saldo_update);
        }
    });
    
    // Tambahkan event untuk tombol back STS
    document.querySelector('.sts-btn-back')?.addEventListener('click', function() {
        window.history.back();
    });
});