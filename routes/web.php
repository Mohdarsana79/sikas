<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\PenganggaranController;
use App\Http\Controllers\RkasController;
use App\Http\Controllers\RekeningBelanjaController;
use App\Http\Controllers\auth\LogoutController;
use App\Http\Controllers\KodeKegiatanController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\RkasPerubahanController;
use App\Http\Controllers\RekamanPerubahanController;
use App\Http\Controllers\BkuController;

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

    // ROUTE BARU UNTUK PROSES PENYALINAN DATA
    Route::post('/salin-rkas', [RkasPerubahanController::class, 'salinDariRkas'])
        ->name('rkas-perubahan.salin');

    Route::post('/', [RkasPerubahanController::class, 'store'])->name('rkas-perubahan.store');
    Route::get('/bulan/{bulan}', [RkasPerubahanController::class, 'getByMonth'])->name('rkas-perubahan.getByMonth');
    Route::get('/all-data', [RkasPerubahanController::class, 'getAllData'])->name('rkas-perubahan.getAllData');
    Route::put('/{id}', [RkasPerubahanController::class, 'update'])->name('rkas-perubahan.update');
    Route::put('/{id}/update-tanggal-cetak', [PenganggaranController::class, 'updateTanggalCetak'])
        ->name('penganggaran.update-tanggal-cetak');
    Route::get('/{id}', [RkasPerubahanController::class, 'show'])->name('rkas-perubahan.show');
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
    Route::get('/bku', [BkuController::class, 'index'])->name('penatausahaan.bku');
});
