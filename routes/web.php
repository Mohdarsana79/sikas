<?php

use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\BeritaAcaraPenutupanController;
use App\Http\Controllers\BukuBankController;
use App\Http\Controllers\BukuKasPembantuTunaiController;
use App\Http\Controllers\BukuKasUmumController;
use App\Http\Controllers\BukuPajakController;
use App\Http\Controllers\BukuRobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\KodeKegiatanController;
use App\Http\Controllers\KopSekolahController;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\LaporanRealisasiController;
use App\Http\Controllers\PenarikanTunaiController;
use App\Http\Controllers\PenatausahaanController;
use App\Http\Controllers\PenerimaanDanaController;
use App\Http\Controllers\PenganggaranController;
use App\Http\Controllers\RegistrasiPenutupanKasController;
use App\Http\Controllers\RekamanPerubahanController;
use App\Http\Controllers\RekapitulasiRealisasiController;
use App\Http\Controllers\RekeningBelanjaController;
use App\Http\Controllers\RkasController;
use App\Http\Controllers\RkasPerubahanController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SetorTunaiController;
use App\Http\Controllers\SpmthController;
use App\Http\Controllers\SptjController;
use App\Http\Controllers\TandaTerimaController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Login Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Register Routes - Biarkan seperti biasa, protection sudah di Controller
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected Routes - Only for authenticated users
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    // routes/web.php
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.dashboard');
    Route::get('/api/dashboard-new-data', [DashboardController::class, 'getDashboardNewData'])->name('dashboard.new.data');
});

// Routes untuk search semua modul
Route::middleware(['auth'])->group(function () {
    // BKU Search
    Route::get('/bku/search/{tahun}/{bulan}', [BukuKasUmumController::class, 'search'])->name('bku.search');

    // Kode Kegiatan Search
    Route::get('/kegiatan/search', [KodeKegiatanController::class, 'search'])->name('kegiatan.search');

    // Rekening Belanja Search
    Route::get('/rekening-belanja/search', [RekeningBelanjaController::class, 'search'])->name('rekening-belanja.search');

    // RKAS Perubahan Search
    Route::get('/rkas-perubahan/search', [RkasPerubahanController::class, 'search'])->name('rkas-perubahan.search');
});

Route::middleware(['auth'])->prefix('sekolah')->group(function () {
    Route::get('/', [SekolahController::class, 'index'])->name('sekolah.index');
    Route::post('/', [SekolahController::class, 'store'])->name('sekolah.store');
    Route::put('/{sekolah}', [SekolahController::class, 'update'])->name('sekolah.update');
    Route::delete('/{sekolah}', [SekolahController::class, 'destroy'])->name('sekolah.destroy');
});

Route::middleware(['auth'])->prefix('kop-sekolah')->group(function () {
    Route::get('/', [KopSekolahController::class, 'index'])->name('kop-sekolah.index');
    Route::post('/', [KopSekolahController::class, 'store'])->name('kop-sekolah.store');
    Route::delete('/{id}', [KopSekolahController::class, 'destroy'])->name('kop-sekolah.destroy');
});

Route::middleware(['auth'])->prefix('backup')->group(function () {
    Route::get('/', [DatabaseController::class, 'index'])->name('backup.index');
    Route::post('/create', [DatabaseController::class, 'backup'])->name('backup.create');
    Route::post('/restore', [DatabaseController::class, 'restore'])->name('backup.restore');
    Route::get('/restore/progress', [DatabaseController::class, 'getRestoreProgress'])->name('backup.restore.progress');
    Route::get('/download', [DatabaseController::class, 'download'])->name('backup.download');
    Route::delete('/delete', [DatabaseController::class, 'delete'])->name('backup.delete');
    Route::post('/validate-password', [DatabaseController::class, 'validatePassword'])->name('backup.validate-password');
    Route::post('/reset', [DatabaseController::class, 'reset'])->name('database.reset');
});

Route::middleware(['auth'])->prefix('penganggaran')->group(function () {
    Route::get('/', [PenganggaranController::class, 'index'])->name('penganggaran.index');
    Route::post('/', [PenganggaranController::class, 'store'])->name('penganggaran.store');
    Route::put('/{id}', [PenganggaranController::class, 'update'])->name('penganggaran.update');
    Route::delete('/{id}', [PenganggaranController::class, 'destroy'])->name('penganggaran.destroy');

    // untuk hapus RKAS Perubahan
    Route::delete('/rkas-perubahan/{id}', [PenganggaranController::class, 'destroyRkasPerubahan'])
        ->name('penganggaran.destroy-rkas-perubahan');
});

// Rkas Perubahan
Route::middleware(['auth'])->prefix('rkas-perubahan')->group(function () {
    // RKAS Perubahan
    Route::get('/', [RkasPerubahanController::class, 'index'])->name('rkas-perubahan.index');
    Route::get('/check-status', [RkasPerubahanController::class, 'checkStatusPerubahan'])
        ->name('rkas-perubahan.check-status');

    // ROUTE BARU UNTUK PROSES PENYALINAN DATA
    Route::post('/salin', [RkasPerubahanController::class, 'salinDariRkas'])
        ->name('rkas-perubahan.salin');

    Route::get('/search', [RkasPerubahanController::class, 'search'])->name('rkas-perubahan.search');

    Route::get('/show/{id}', [RkasPerubahanController::class, 'show'])->name('rkas-perubahan.show');
    // Route untuk delete semua data dengan kriteria yang sama
    Route::delete('/delete-all/{id}', [RkasPerubahanController::class, 'deleteAll'])->name('rkas-perubahan.delete-all');

    // Route khusus untuk edit - TAMBAHKAN INI
    Route::get('/{id}/edit', [RkasPerubahanController::class, 'edit'])->name('rkas-perubahan.edit');

    Route::post('/', [RkasPerubahanController::class, 'store'])->name('rkas-perubahan.store');
    Route::get('/bulan/{bulan}', [RkasPerubahanController::class, 'getByMonth'])->name('rkas-perubahan.getByMonth');
    // Route untuk mendapatkan semua data RKAS Perubahan
    Route::get('/all-data', [RkasPerubahanController::class, 'getAllData'])->name('rkas-perubahan.getAllData');

    // route untuk update RKAS Perubahan
    Route::put('/{id}', [RkasPerubahanController::class, 'update'])->name('rkas-perubahan.update');

    Route::put('/{id}/update-tanggal-perubahan', [PenganggaranController::class, 'updateTanggalPerubahan'])
        ->name('penganggaran.update-tanggal-perubahan');
    Route::post('/sisipkan', [RkasPerubahanController::class, 'sisipkan'])
        ->name('rkas-perubahan.sisipkan');

    Route::delete('/{id}', [RkasPerubahanController::class, 'destroy'])->name('rkas-perubahan.destroy');
    Route::get('/total/per-bulan', [RkasPerubahanController::class, 'getTotalPerBulan'])->name('rkas-perubahan.getTotalPerBulan');
    // Routes for Tahap 1 and Tahap 2 functionality
    Route::get('/total-tahap1', [RkasPerubahanController::class, 'getTotalTahap1'])->name('rkas-perubahan.total-tahap1');
    Route::get('/total-tahap2', [RkasPerubahanController::class, 'getTotalTahap2'])->name('rkas-perubahan.total-tahap2');
    Route::get('/data-tahap/{tahap}', [RkasPerubahanController::class, 'getDataByTahap'])->name('rkas-perubahan.data-tahap');
    Route::get('/rkas-perubahan/rekapan-perubahan', [RkasPerubahanController::class, 'showRekapanPerubahan'])
        ->name('rkas-perubahan.rekapan-perubahan');

    // Route Pengaturan Cetak

    // Routes untuk PDF dan Preview
    Route::get('/rkas-perubahan/generate-tahapan-pdf', [RkasPerubahanController::class, 'generateTahapanPdf'])
        ->name('rkas-perubahan.generate-tahapan-pdf');

    // Menjadi:
    Route::get('/rkas-perubahan/generate-pdf-rekap/{tahun}', [RkasPerubahanController::class, 'generatePdfRkaRekap'])
        ->name('rkas-perubahan.generate-pdf-rekap');

    Route::get('/rkas-perubahan/generate-rka-221-pdf/{tahun}', [RkasPerubahanController::class, 'generateRkaDuaSatuPdf'])
        ->name('rkas-perubahan.generate-rka-221-pdf');

    Route::get('/rkas-perubahan/get-monthly-data', [RkasPerubahanController::class, 'getMonthlyData'])
        ->name('rkas-perubahan.get-monthly-data');

    Route::get('/rkas-perubahan/bulan/{month}', [RkasPerubahanController::class, 'getDataByMonth'])->name('rkas-perubahan.get-data-by-month');

    Route::get('/rkas-perubahan/rekap-bulanan/{bulan}', [RkasPerubahanController::class, 'getRekapBulanan'])
        ->name('rkas-perubahan.rekap-bulanan');

    Route::get('/rkas-perubahan/rka-bulanan-pdf/{tahun}/{bulan}', [RkasPerubahanController::class, 'generatePdfBulanan'])
        ->name('rkas-perubahan.rka-bulanan-pdf')->middleware('noCache');
});

// Rekaman Perubahan routes (TERPISAH)
Route::middleware(['auth'])->prefix('rekaman-perubahan')->group(function () {
    Route::get('/', [RekamanPerubahanController::class, 'index'])->name('rekaman-perubahan.index');
});

// Di web.php - dalam group prefix rkas
Route::middleware(['auth'])->prefix('rkas')->group(function () {
    Route::get('/', [RkasController::class, 'index'])->name('rkas.index');
    Route::post('/', [RkasController::class, 'store'])->name('rkas.store');

    // Routes untuk salin data - HARUS SEBELUM {id}
    Route::get('/check-rkas-data', [RkasController::class, 'checkRkasData'])
        ->name('rkas.check-rkas-data')->middleware('noCache');
    Route::get('/check-previous-perubahan', [RkasController::class, 'checkPreviousYearPerubahan'])
        ->name('rkas.check-previous-perubahan')->middleware('noCache');
    Route::post('/copy-previous-perubahan', [RkasController::class, 'copyPreviousYearPerubahan'])
        ->name('rkas.copy-previous-perubahan')->middleware('noCache');

    Route::get('/search', [RkasController::class, 'search'])->name('rkas.search');
    // Routes untuk total tahap - HARUS SEBELUM {id}
    Route::get('/total-tahap1', [RkasController::class, 'getTotalTahap1'])->name('rkas.total-tahap1');
    Route::get('/total-tahap2', [RkasController::class, 'getTotalTahap2'])->name('rkas.total-tahap2');

    // Routes untuk bulan - HARUS SEBELUM {id}
    Route::get('/bulan/{bulan}', [RkasController::class, 'getByMonth'])->name('rkas.getByMonth')->middleware('noCache');
    Route::get('/all-data', [RkasController::class, 'getAllData'])->name('rkas.getAllData')->middleware('noCache');
    Route::get('/total/per-bulan', [RkasController::class, 'getTotalPerBulan'])->name('rkas.getTotalPerBulan');
    Route::get('/data-tahap/{tahap}', [RkasController::class, 'getDataByTahap'])->name('rkas.data-tahap');

    // Routes dengan parameter ID - HARUS DI AKHIR
    Route::get('/{id}/edit', [RkasController::class, 'edit'])->name('rkas.edit');
    Route::get('/{id}', [RkasController::class, 'show'])->name('rkas.show');
    Route::put('/{id}', [RkasController::class, 'update'])->name('rkas.update');
    Route::delete('/{id}', [RkasController::class, 'destroy'])->name('rkas.destroy');
    Route::delete('/delete-all/{id}', [RkasController::class, 'deleteAll'])->name('rkas.delete-all');

    Route::put('/{id}/update-tanggal-cetak', [PenganggaranController::class, 'updateTanggalCetak'])
        ->name('penganggaran.update-tanggal-cetak');
    // Sisipkan dan lainnya
    Route::post('/sisipkan', [RkasController::class, 'sisipkan'])->name('rkas.sisipkan');

    // Routes untuk PDF dan lainnya
    Route::get('/rkas/rekapan', [RkasController::class, 'showRekapan'])->name('rkas.rekapan');
    Route::get('/rkas/generate-pdf', [RkasController::class, 'generatePdf'])->name('rkas.generate-pdf');
    Route::get('/rkas/generate-pdf-rekap/{tahun}', [RkasController::class, 'generatePdfRkaRekap'])->name('rkas.generate-pdf-rekap');
    Route::get('/rkas/generate-rka-221-pdf/{tahun}', [RkasController::class, 'generateRkaDuaSatuPdf'])->name('rkas.generate-rka-221-pdf');
    Route::get('/rkas/get-monthly-data', [RkasController::class, 'getMonthlyData'])->name('rkas.get-monthly-data')->middleware('noCache');
    Route::get('/rkas/bulan/{month}', [RkasController::class, 'getDataByMonth'])->name('rkas.get-data-by-month')->middleware('noCache');
    Route::get('/rkas/rekap-bulanan/{bulan}', [RkasController::class, 'getRekapBulanan'])->name('rkas.rekap-bulanan');
    Route::get('/rkas/rka-bulanan-pdf/{tahun}/{bulan}', [RkasController::class, 'generatePdfBulanan'])->name('rkas.rka-bulanan-pdf')->middleware('noCache');
});

// Referensi
Route::middleware(['auth'])->prefix('referensi')->group(function () {

    // Rekening Belanja
    Route::get('/rekening-belanja', [RekeningBelanjaController::class, 'index'])->name('referensi.rekening-belanja.index');
    Route::get('/rekening-belanja/paginate', [RekeningBelanjaController::class, 'paginate'])->name('referensi.rekening-belanja.paginate');
    Route::post('/rekening-belanja', [RekeningBelanjaController::class, 'store'])->name('referensi.rekening-belanja.store');
    Route::put('/rekening-belanja/{rekeningBelanja}', [RekeningBelanjaController::class, 'update'])->name('referensi.rekening-belanja.update');

    Route::delete('/rekening-belanja/{id}', [RekeningBelanjaController::class, 'destroy'])->name('referensi.rekening-belanja.destroy');

    // import uraian program
    Route::post('/referensi/rekening-belanja/import', [RekeningBelanjaController::class, 'import'])
        ->name('referensi.rekening-belanja.import');

    Route::get('/referensi/rekening-belanja/download-template', [RekeningBelanjaController::class, 'downloadTemplate'])
        ->name('referensi.rekening-belanja.download-template');

    // Kode Kegiatan
    Route::get('/referensi/kode-kegiatan', [KodeKegiatanController::class, 'index'])->name('referensi.kode-kegiatan.index');
    Route::get('/kode-kegiatan/paginate', [KodeKegiatanController::class, 'paginate'])->name('referensi.kode-kegiatan.paginate');
    Route::post('/kode-kegiatan', [KodeKegiatanController::class, 'store'])->name('referensi.kode-kegiatan.store');
    Route::put('/kode-kegiatan/{id}', [KodeKegiatanController::class, 'update'])->name('referensi.kode-kegiatan.update');
    Route::delete('/kode-kegiatan/{id}', [KodeKegiatanController::class, 'destroy'])->name('referensi.kode-kegiatan.destroy');
    // Kode Kegiatan Import
    Route::post('/kode-kegiatan/import', [KodeKegiatanController::class, 'import'])->name('referensi.kode-kegiatan.import');
    Route::get('/kode-kegiatan/download-template', [KodeKegiatanController::class, 'downloadTemplate'])->name('referensi.kode-kegiatan.download-template');
    Route::get('/generate-pdf', [RkasController::class, 'generatePdf'])
        ->name('penganggaran.rkas.generate-pdf');
});

Route::middleware(['auth'])->prefix('penatausahaan')->group(function () {
    Route::get('/penatausahaan', [PenatausahaanController::class, 'index'])->name('penatausahaan.penatausahaan');
    Route::get('/get-penganggaran-id', [PenatausahaanController::class, 'getPenganggaranId']);

    // Route untuk penerimaan dana - lebih spesifik
    Route::post('/penerimaan-dana/store', [PenerimaanDanaController::class, 'store'])->name('penerimaan-dana.store');
    Route::get('/penerimaan-dana/{penganggaran_id}', [PenerimaanDanaController::class, 'getByPenganggaran'])->name('penerimaan-dana.getByPenganggaran');

    Route::delete('/penerimaan-dana/{id}', [PenerimaanDanaController::class, 'destroy'])->name('penerimaan-dana.destroy');

    Route::delete('/penerimaan-dana/saldo-awal/{id}', [PenerimaanDanaController::class, 'destroySaldoAwal'])
        ->name('penerimaan-dana.destroy-saldo-awal');
});

// ROUTE BKU AUDIT (BARU - DIPISAHKAN)
Route::middleware(['auth'])->prefix('bku-audit')->group(function () {
    // Halaman audit detail
    Route::get('/{penganggaran_id}', [BukuKasUmumController::class, 'auditPage'])
        ->name('bku.audit.page');

    // API untuk get data audit
    Route::get('/data/{penganggaran_id}', [BukuKasUmumController::class, 'getAuditData'])
        ->name('bku.audit.data');

    // API untuk fix data
    Route::post('/fix/{penganggaran_id}', [BukuKasUmumController::class, 'fixData'])
        ->name('bku.fix.data');
});

Route::middleware(['auth'])->prefix('bku')->group(function () {
    // Route untuk debug volume

    Route::get('/debug-volume/{tahun}/{bulan}/{rekeningId}', [BukuKasUmumController::class, 'debugVolumePerhitungan'])
        ->name('bku.debug-volume');

    Route::get('/{tahun}/{bulan}', [BukuKasUmumController::class, 'showByBulan'])->name('bku.showByBulan');

    // route status bulan: belum diisi, draft, selesai
    Route::get('/status/{tahun}/{bulan}', [BukuKasUmumController::class, 'getStatusBulan'])
        ->name('bku.status');
    // route untuk API total dana tersedia
    Route::get('/total-dana-tersedia/{penganggaran_id}', [BukuKasUmumController::class, 'getTotalDanaTersedia'])
        ->name('bku.total-dana-tersedia');

    // penarikan tunai
    Route::post('/penarikan-tunai', [PenarikanTunaiController::class, 'store'])->name('bku.penarikan-tunai.store');
    Route::delete('/penarikan-tunai/{id}', [PenarikanTunaiController::class, 'destroy'])->name('penarikan.destroy');

    // set tanggal sebelum tanggal penarikan tunai
    Route::get('/tanggal-penarikan/{penganggaran_id}', [BukuKasPembantuTunaiController::class, 'getTanggalPenarikanTunai'])
        ->name('bku.tanggal-penarikan');

    // setor tunai
    Route::post('/setor-tunai', [SetorTunaiController::class, 'store'])->name('bku.setor-tunai.store');
    Route::delete('/setor-tunai/{id}', [SetorTunaiController::class, 'destroy'])->name('setor-tunai.destroy');
    // API untuk mendapatkan saldo tunai
    Route::get('/saldo-tunai/{penganggaran_id}', [SetorTunaiController::class, 'getSaldoTunai'])
        ->name('bku.saldo-tunai');

    // API untuk riwayat saldo tunai
    Route::get('/riwayat-saldo-tunai/{penganggaran_id}', [SetorTunaiController::class, 'getRiwayatSaldoTunai'])
        ->name('bku.riwayat-saldo-tunai');

    Route::get('/riwayat-saldo-tunai/{penganggaran_id}/{tahun}', [SetorTunaiController::class, 'getRiwayatSaldoTunai']);

    // Validasi sebelum hapus
    Route::get('/validate-delete-setor/{id}', [SetorTunaiController::class, 'validateDelete'])
        ->name('bku.validate-delete-setor');

    Route::get('/kegiatan-rekening/{tahun}/{bulan}', [BukuKasUmumController::class, 'getKegiatanDanRekening'])
        ->name('bku.kegiatan-rekening');

    Route::get('/uraian/{tahun}/{bulan}/{rekeningId}', [BukuKasUmumController::class, 'getUraianByRekening'])
        ->name('bku.uraian');
    
    // Api partials table ajax sementara
    Route::get('/table-data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getTableData'])
        ->name('bku.table-data');

    // Route untuk summary data
    Route::get('/summary-data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getSummaryData'])
        ->name('bku.summary-data');

    Route::post('/store', [BukuKasUmumController::class, 'store'])->name('bku.store');

    // Route untuk lapor pajak
    Route::post('/{id}/lapor-pajak', [BukuPajakController::class, 'laporPajak'])->name('bku.lapor-pajak');
    Route::get('/{id}/get-pajak', [BukuPajakController::class, 'getDataPajak'])->name('bku.get-pajak');

    // Route untuk mendapatkan nomor nota terakhir
    Route::get('/last-nota-number/{tahun}/{bulan}', [BukuKasUmumController::class, 'getLastNotaNumber'])
        ->name('bku.last-nota-number');

    // API untuk mendapatkan total belanja
    Route::get('/total-dibelanjakan/{penganggaran_id}/{bulan}', [BukuKasUmumController::class, 'getTotalDibelanjakan'])
        ->name('bku.total-dibelanjakan');

    Route::get('/total-dibelanjakan-sampai-bulan/{penganggaran_id}/{bulan}', [BukuKasUmumController::class, 'getTotalDibelanjakanSampaiBulan'])
        ->name('bku.total-dibelanjakan-sampai-bulan');

    Route::get('/bku/saldo/{penganggaran_id}', [BukuKasUmumController::class, 'getSaldoRealTime'])
        ->name('bku.saldo-realtime');

    Route::delete('/{id}', [BukuKasUmumController::class, 'destroy'])->name('bku.destroy');
    // Route baru untuk menghapus semua data dalam bulan
    Route::delete('/{tahun}/{bulan}/all', [BukuKasUmumController::class, 'destroyAllByBulan'])
        ->name('bku.destroy-all-bulan');

    Route::get('/bku/kegiatan-rekening/{tahun}/{bulan}', [BukuKasUmumController::class, 'getKegiatanDanRekening'])
        ->name('bku.kegiatan-rekening');

    // Route untuk tutup/buka BKU
    Route::post('/{tahun}/{bulan}/tutup', [BukuKasUmumController::class, 'tutupBku'])->name('bku.tutup');
    Route::post('/{tahun}/{bulan}/buka', [BukuKasUmumController::class, 'bukaBku'])->name('bku.buka');

    // Route untuk update bunga bank (tanpa modal)
    Route::put('/{tahun}/{bulan}/update-bunga', [BukuKasUmumController::class, 'updateBungaBank'])->name('bku.update-bunga');
});

Route::middleware(['auth'])->prefix('laporan')->group(function () {
    // Route untuk AJAX rekapan BKU
    Route::get('/laporan/rekapan-bku/ajax', [BukuKasUmumController::class, 'getRekapanBkuAjax'])
        ->name('laporan.rekapan-bku.ajax');

    Route::get('/bkp-bank/data/{tahun}/{bulan}', [BukuBankController::class, 'getBkpBankData'])
        ->name('bkp-bank.data');

    Route::get('/rekapan-bku', [BukuKasUmumController::class, 'rekapanBku'])
        ->name('laporan.rekapan-bku');

    // Route untuk generate PDF BKP Bank dengan prefix yang konsisten
    Route::get('/laporan/bkp-bank-pdf/{tahun}/{bulan}', [BukuBankController::class, 'generateBkpBankPdf'])
        ->name('laporan.bkp-bank-pdf');

    Route::get('/data/{tahun}/{bulan}', [BukuKasPembantuTunaiController::class, 'getBkpPembantuData'])
        ->name('bkp-pembantu.data');

    Route::get('/laporan/bku-pembantu-tunai-pdf/{tahun}/{bulan}', [BukuKasPembantuTunaiController::class, 'generateBkuPembantuTunaiPdf'])
        ->name('laporan.bku-pembantu-tunai-pdf');

    // Route debug
    Route::get('/debug/bkp-bank/{tahun}/{bulan}', [BukuBankController::class, 'debugBkpBank'])
        ->name('debug.bkp-bank');

    // Route untuk BKP Umum
    Route::get('/bkp-umum/data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getBkpUmumData'])
        ->name('bkp-umum.data');
    Route::get('/laporan/bkp-umum-pdf/{tahun}/{bulan}', [BukuKasUmumController::class, 'generateBkpUmumPdf'])
        ->name('laporan.bkp-umum-pdf');

    // Route untuk Pajak
    Route::get('/bkp-pajak/data/{tahun}/{bulan}', [BukuPajakController::class, 'getBkpPajakData'])
        ->name('bkp-pajak.data');
    Route::get('/bkp-pajak-pdf/{tahun}/{bulan}', [BukuPajakController::class, 'generateBkpPajakPdf'])
        ->name('laporan.bkp-pajak-pdf');

    // Route untuk ROB
    Route::get('/bkp-rob/data/{tahun}/{bulan}', [BukuRobController::class, 'getBkpRobData'])
        ->name('bkp-rob.data');
    Route::get('/bkp-rob-pdf/{tahun}/{bulan}', [BukuRobController::class, 'generateBkpRobPdf'])
        ->name('laporan.bkp-rob-pdf');

    // Route untuk BKP Registrasi
    Route::get('/laporan/bkp-reg-pdf/{tahun}/{bulan}', [RegistrasiPenutupanKasController::class, 'generateBkpRegPdf'])
        ->name('laporan.bkp-reg-pdf');

    // Route untuk get data BKP Registrasi (AJAX)
    Route::get('/laporan/bkp-reg-data/{tahun}/{bulan}', [RegistrasiPenutupanKasController::class, 'getBkpRegData'])
        ->name('laporan.bkp-reg-data');

    // Route untuk Berita Acara
    Route::get('/laporan/berita-acara/data/{tahun}/{bulan}', [BeritaAcaraPenutupanController::class, 'getBeritaAcaraData'])
        ->name('laporan.berita-acara.data');
    Route::get('/laporan/berita-acara-pdf/{tahun}/{bulan}', [BeritaAcaraPenutupanController::class, 'generateBeritaAcaraPdf'])
        ->name('laporan.berita-acara-pdf');

    // Route untuk rekapitulasi realisasi
    Route::get('/realisasi/data', [RekapitulasiRealisasiController::class, 'getRealisasiData'])
        ->name('laporan.realisasi.data');
    Route::get('/realisasi-pdf/{tahun}/{periode?}', [RekapitulasiRealisasiController::class, 'generateRealisasiPdf'])
        ->name('laporan.realisasi-pdf');
    Route::get('/realisasi-rekapan/{tahun}/{bulan}', [RekapitulasiRealisasiController::class, 'getRealisasiForRekapanBku'])
        ->name('laporan.realisasi-rekapan');
});

Route::middleware(['auth'])->prefix('kwitansi')->group(function () {
    Route::get('/', [KwitansiController::class, 'index'])->name('kwitansi.index');
    Route::get('/search', [KwitansiController::class, 'search'])->name('kwitansi.search');
    Route::get('/tahun-anggaran', [KwitansiController::class, 'getTahunAnggaran'])->name('kwitansi.tahun-anggaran');

    Route::get('/create', [KwitansiController::class, 'create'])->name('kwitansi.create');
    Route::post('/', [KwitansiController::class, 'store'])->name('kwitansi.store');

    // Routes untuk generate otomatis
    Route::get('/check-available', [KwitansiController::class, 'checkAvailableData'])->name('kwitansi.check-available');
    Route::post('/generate-batch', [KwitansiController::class, 'generateBatch'])->name('kwitansi.generate-batch');

    // Routes dengan parameter ID
    Route::get('/{id}/pdf', [KwitansiController::class, 'generatePdf'])->name('kwitansi.pdf');
    Route::get('/download-all', [KwitansiController::class, 'downloadAll'])->name('kwitansi.download-all');

    // ROUTE BARU UNTUK PREVIEW PDF (STREAM)
    Route::get('/{id}/preview-pdf', [KwitansiController::class, 'previewPdf'])->name('kwitansi.preview-pdf');

    Route::get('/{id}/preview', [KwitansiController::class, 'previewPdf'])->name('kwitansi.preview');
    Route::get('/{id}/preview-modal', [KwitansiController::class, 'previewModal'])->name('kwitansi.preview-modal')->middleware('noCache');

    Route::delete('/{id}', [KwitansiController::class, 'destroy'])->name('kwitansi.destroy');
    Route::delete('/delete/all', [KwitansiController::class, 'deleteAll'])->name('kwitansi.delete-all');
    Route::get('/debug-count', [KwitansiController::class, 'debugDataCount'])->name('kwitansi.debug-count');
});

// Tambahkan di dalam group middleware auth
Route::middleware(['auth'])->prefix('tanda-terima')->group(function () {
    Route::get('/', [TandaTerimaController::class, 'index'])->name('tanda-terima.index');
    Route::get('/search', [TandaTerimaController::class, 'search'])->name('tanda-terima.search');
    Route::get('/tahun-anggaran', [TandaTerimaController::class, 'getTahunAnggaran'])->name('tanda-terima.tahun-anggaran');

    // Routes untuk generate otomatis
    Route::get('/check-available', [TandaTerimaController::class, 'checkAvailableData'])->name('tanda-terima.check-available');
    Route::post('/generate-batch', [TandaTerimaController::class, 'generateBatch'])->name('tanda-terima.generate-batch');

    // Routes dengan parameter ID
    Route::get('/{id}', [TandaTerimaController::class, 'show'])->name('tanda-terima.show');
    Route::get('/{id}/preview-pdf', [TandaTerimaController::class, 'previewPdf'])->name('tanda-terima.preview-pdf');
    Route::get('/{id}/pdf', [TandaTerimaController::class, 'generatePdf'])->name('tanda-terima.pdf');
    Route::get('/{id}/preview', [TandaTerimaController::class, 'previewPdf'])->name('tanda-terima.preview')->middleware('noCache');
    Route::get('/{id}/preview-modal', [TandaTerimaController::class, 'previewModal'])->name('tanda-terima.preview-modal')->middleware('noCache');
    Route::delete('/{id}', [TandaTerimaController::class, 'destroy'])->name('tanda-terima.destroy');

    // Route untuk hapus semua tanda terima
    Route::delete('/delete/all', [TandaTerimaController::class, 'deleteAll'])->name('tanda-terima.delete-all');
    Route::get('/download/all', [TandaTerimaController::class, 'downloadAll'])->name('tanda-terima.download-all');
});

Route::middleware(['auth'])->prefix('spmth')->group(function () {
    Route::get('/', [SpmthController::class, 'index'])->name('spmth.index');
});

Route::middleware(['auth'])->prefix('sptj')->group(function () {
    Route::get('/', [SptjController::class, 'index'])->name('sptj.index');
});

Route::middleware(['auth'])->prefix('laporan-realisasi')->group(function () {
    Route::get('/', [LaporanRealisasiController::class, 'index'])->name('laporan-realisasi.index');
});
