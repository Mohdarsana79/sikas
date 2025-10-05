<?php

use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\LogoutController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\BukuKasUmumController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\KodeKegiatanController;
use App\Http\Controllers\PenarikanTunaiController;
use App\Http\Controllers\PenatausahaanController;
use App\Http\Controllers\PenerimaanDanaController;
use App\Http\Controllers\PenganggaranController;
use App\Http\Controllers\RekamanPerubahanController;
use App\Http\Controllers\RekeningBelanjaController;
use App\Http\Controllers\RkasController;
use App\Http\Controllers\RkasPerubahanController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SetorTunaiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.post');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.post');

Route::post('/logout', [LogoutController::class, 'store'])->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth'])->prefix('sekolah')->group(function () {
    Route::get('/', [SekolahController::class, 'index'])->name('sekolah.index');
    Route::post('/', [SekolahController::class, 'store'])->name('sekolah.store');
    Route::put('/{sekolah}', [SekolahController::class, 'update'])->name('sekolah.update');
});

Route::middleware(['auth'])->prefix('backup')->group(function () {
    // Halaman utama backup & restore
    Route::get('/', [DatabaseController::class, 'index'])->name('backup.index');

    // Backup database
    Route::post('/create', [DatabaseController::class, 'backup'])->name('backup.create');

    // Restore database
    Route::post('/restore', [DatabaseController::class, 'restore'])->name('backup.restore');

    // Get restore progress (AJAX endpoint)
    Route::get('/restore/progress', [DatabaseController::class, 'getRestoreProgress'])->name('backup.restore.progress');

    // Download file backup
    Route::get('/download', [DatabaseController::class, 'download'])->name('backup.download');

    // Hapus file backup
    Route::delete('/delete', [DatabaseController::class, 'delete'])->name('backup.delete');

    // Reset database - perbaikan route
    Route::post('/reset', [DatabaseController::class, 'reset'])->name('database.reset');
});

Route::middleware(['auth'])->prefix('penganggaran')->group(function () {
    Route::get('/', [PenganggaranController::class, 'index'])->name('penganggaran.index');
    Route::post('/', [PenganggaranController::class, 'store'])->name('penganggaran.store');
    Route::put('/penganggaran/{id}', [PenganggaranController::class, 'update'])->name('penganggaran.update');
    Route::delete('/penganggaran/{id}', [PenganggaranController::class, 'destroy'])->name('penganggaran.destroy');
});

// Rkas Perubahan
Route::middleware(['auth'])->prefix('rkas-perubahan')->group(function () {
    // RKAS Perubahan
    Route::get('/', [RkasPerubahanController::class, 'index'])->name('rkas-perubahan.index');
    Route::get('/rkas-perubahan/check-status', [RkasPerubahanController::class, 'checkStatusPerubahan'])
        ->name('rkas-perubahan.check-status');

    // ROUTE BARU UNTUK PROSES PENYALINAN DATA
    Route::post('/rkas-perubahan/salin', [RkasPerubahanController::class, 'salinDariRkas'])
        ->name('rkas-perubahan.salin');

    Route::post('/', [RkasPerubahanController::class, 'store'])->name('rkas-perubahan.store');
    Route::get('/bulan/{bulan}', [RkasPerubahanController::class, 'getByMonth'])->name('rkas-perubahan.getByMonth');
    Route::get('/all-data', [RkasPerubahanController::class, 'getAllData'])->name('rkas-perubahan.getAllData');
    Route::put('/{id}', [RkasPerubahanController::class, 'update'])->name('rkas-perubahan.update');
    Route::put('/{id}/update-tanggal-perubahan', [PenganggaranController::class, 'updateTanggalPerubahan'])
        ->name('penganggaran.update-tanggal-perubahan');
    Route::get('/{id}', [RkasPerubahanController::class, 'show'])->name('rkas-perubahan.show');
    Route::delete('/{id}', [RkasPerubahanController::class, 'destroy'])->name('rkas-perubahan.destroy');
    Route::get('/total/per-bulan', [RkasPerubahanController::class, 'getTotalPerBulan'])->name('rkas-perubahan.getTotalPerBulan');
    // Routes for Tahap 1 and Tahap 2 functionality
    Route::get('/total-tahap1', [RkasPerubahanController::class, 'getTotalTahap1'])->name('rkas-perubahan.total-tahap1');
    Route::get('/total-tahap2', [RkasPerubahanController::class, 'getTotalTahap2'])->name('rkas-perubahan.total-tahap2');
    Route::get('/data-tahap/{tahap}', [RkasPerubahanController::class, 'getDataByTahap'])->name('rkas-perubahan.data-tahap');
    Route::get('/rkas-perubahan/rekapan-perubahan', [RkasPerubahanController::class, 'showRekapanPerubahan'])
        ->name('rkas-perubahan.rekapan-perubahan');

    // Routes untuk PDF dan Preview
    Route::get('/rkas-perubahan/generate-tahapan-pdf', [RkasPerubahanController::class, 'generateTahapanPdf'])
        ->name('rkas-perubahan.generate-tahapan-pdf');
    Route::get('/rkas-perubahan/generate-pdf-rekap', [RkasPerubahanController::class, 'generatePdfRkaRekap'])->name('rkas-perubahan.generate-pdf-rekap');
    Route::get('/rkas-perubahan/generate-rka-221-pdf', [RkasPerubahanController::class, 'generateRkaDuaSatuPdf'])
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

Route::middleware(['auth'])->prefix('rkas')->group(function () {
    Route::get('/', [RkasController::class, 'index'])->name('rkas.index');
    Route::post('/', [RkasController::class, 'store'])->name('rkas.store');
    Route::get('/bulan/{bulan}', [RkasController::class, 'getByMonth'])->name('rkas.getByMonth');
    Route::get('/all-data', [RkasController::class, 'getAllData'])->name('rkas.getAllData');
    Route::put('/{id}', [RkasController::class, 'update'])->name('rkas.update');
    Route::put('/{id}/update-tanggal-cetak', [PenganggaranController::class, 'updateTanggalCetak'])
        ->name('penganggaran.update-tanggal-cetak');
    Route::get('/{id}', [RkasController::class, 'show'])->name('rkas.show');
    Route::post('/sisipkan', [RkasController::class, 'sisipkan'])
        ->name('rkas.sisipkan');
    Route::delete('/{id}', [RkasController::class, 'destroy'])->name('rkas.destroy');
    Route::get('/total/per-bulan', [RkasController::class, 'getTotalPerBulan'])->name('rkas.getTotalPerBulan');
    // Routes for Tahap 1 and Tahap 2 functionality
    Route::get('/total-tahap1', [RkasController::class, 'getTotalTahap1'])->name('rkas.total-tahap1');
    Route::get('/total-tahap2', [RkasController::class, 'getTotalTahap2'])->name('rkas.total-tahap2');
    Route::get('/data-tahap/{tahap}', [RkasController::class, 'getDataByTahap'])->name('rkas.data-tahap');
    Route::get('/rkas/rekapan', [RkasController::class, 'showRekapan'])
        ->name('rkas.rekapan');

    // Routes untuk PDF dan Preview
    Route::get('/rkas/generate-pdf', [RkasController::class, 'generatePdf'])->name('rkas.generate-pdf');
    Route::get('/rkas/generate-pdf-rekap', [RkasController::class, 'generatePdfRkaRekap'])->name('rkas.generate-pdf-rekap');
    Route::get('/rkas/generate-rka-221-pdf', [RkasController::class, 'generateRkaDuaSatuPdf'])
        ->name('rkas.generate-rka-221-pdf');

    Route::get('/rkas/get-monthly-data', [RkasController::class, 'getMonthlyData'])
        ->name('rkas.get-monthly-data');

    Route::get('/rkas/bulan/{month}', [RkasController::class, 'getDataByMonth'])->name('rkas.get-data-by-month');

    Route::get('/rkas/rekap-bulanan/{bulan}', [RkasController::class, 'getRekapBulanan'])
        ->name('rkas.rekap-bulanan');

    Route::get('/rkas/rka-bulanan-pdf/{tahun}/{bulan}', [RkasController::class, 'generatePdfBulanan'])
        ->name('rkas.rka-bulanan-pdf')->middleware('noCache');
});

// Referensi
Route::middleware(['auth'])->prefix('referensi')->group(function () {

    // Rekening Belanja
    Route::get('/rekening-belanja', [RekeningBelanjaController::class, 'index'])->name('referensi.rekening-belanja.index');
    Route::post('/rekening-belanja', [RekeningBelanjaController::class, 'store'])->name('referensi.rekening-belanja.store');
    Route::put('/rekening-belanja/{rekeningBelanja}', [RekeningBelanjaController::class, 'update'])->name('referensi.rekening-belanja.update');
    Route::delete('/rekening-belanja/{rekeningBelanja}', [RekeningBelanjaController::class, 'destroy'])->name('referensi.rekening-belanja.destroy');

    // import uraian program
    Route::post('/referensi/rekening-belanja/import', [RekeningBelanjaController::class, 'import'])
        ->name('referensi.rekening-belanja.import');

    Route::get('/referensi/rekening-belanja/download-template', [RekeningBelanjaController::class, 'downloadTemplate'])
        ->name('referensi.rekening-belanja.download-template');

    // Kode Kegiatan
    Route::get('/referensi/kode-kegiatan', [KodeKegiatanController::class, 'index'])->name('referensi.kode-kegiatan.index');
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
    Route::get('/tanggal-penarikan/{penganggaran_id}', [BukuKasUmumController::class, 'getTanggalPenarikanTunai'])
        ->name('bku.tanggal-penarikan');

    // setor tunai
    Route::post('/setor-tunai', [SetorTunaiController::class, 'store'])->name('bku.setor-tunai.store');
    Route::delete('/setor-tunai/{id}', [SetorTunaiController::class, 'destroy'])->name('setor-tunai.destroy');

    Route::get('/kegiatan-rekening/{tahun}/{bulan}', [BukuKasUmumController::class, 'getKegiatanDanRekening'])
        ->name('bku.kegiatan-rekening');

    Route::get('/uraian/{tahun}/{bulan}/{rekeningId}', [BukuKasUmumController::class, 'getUraianByRekening'])
        ->name('bku.uraian');

    Route::post('/store', [BukuKasUmumController::class, 'store'])->name('bku.store');

    // Route untuk lapor pajak
    Route::post('/{id}/lapor-pajak', [BukuKasUmumController::class, 'laporPajak'])->name('bku.lapor-pajak');
    Route::get('/{id}/get-pajak', [BukuKasUmumController::class, 'getDataPajak'])->name('bku.get-pajak');

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

    Route::get('/bkp-bank/data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getBkpBankData'])
        ->name('bkp-bank.data');

    Route::get('/rekapan-bku', [BukuKasUmumController::class, 'rekapanBku'])
        ->name('laporan.rekapan-bku');

    // Route untuk generate PDF BKP Bank dengan prefix yang konsisten
    Route::get('/laporan/bkp-bank-pdf/{tahun}/{bulan}', [BukuKasUmumController::class, 'generateBkpBankPdf'])
        ->name('laporan.bkp-bank-pdf');

    Route::get('/laporan/bku-pembantu-tunai-pdf/{tahun}/{bulan}', [BukuKasUmumController::class, 'generateBkuPembantuTunaiPdf'])
    ->name('laporan.bku-pembantu-tunai-pdf');

    // Route untuk BKP Umum
    Route::get('/bkp-umum/data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getBkpUmumData'])
        ->name('bkp-umum.data');
    Route::get('/laporan/bkp-umum-pdf/{tahun}/{bulan}', [BukuKasUmumController::class, 'generateBkpUmumPdf'])
        ->name('laporan.bkp-umum-pdf');

    // Route untuk Pajak
    Route::get('/bkp-pajak/data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getBkpPajakData'])
        ->name('bkp-pajak.data');
    Route::get('/bkp-pajak-pdf/{tahun}/{bulan}', [BukuKasUmumController::class, 'generateBkpPajakPdf'])
        ->name('laporan.bkp-pajak-pdf');

    // Route untuk ROB
    Route::get('/bkp-rob/data/{tahun}/{bulan}', [BukuKasUmumController::class, 'getBkpRobData'])
        ->name('bkp-rob.data');
    Route::get('/bkp-rob-pdf/{tahun}/{bulan}', [BukuKasUmumController::class, 'generateBkpRobPdf'])
        ->name('laporan.bkp-rob-pdf');
});
