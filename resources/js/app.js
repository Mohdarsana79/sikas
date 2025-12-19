import './bootstrap';
import BkuManager from './managers/BkuManager';

// Inisialisasi ketika document ready
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi BkuManager jika modal tersedia
    if (document.getElementById('transactionModal')) {
        window.bkuManager = new BkuManager();
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
    // Logika lain yang sudah ada...
    console.log('Document ready - BKU application loaded');
});
