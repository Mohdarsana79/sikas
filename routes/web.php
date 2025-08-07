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

Route::middleware(['auth'])->prefix('penganggaran/rkas')->group(function () {
    Route::get('/', [RkasController::class, 'index'])->name('penganggaran.rkas.index');
    Route::post('/', [RkasController::class, 'store'])->name('penganggaran.rkas.store');
    Route::get('/bulan/{bulan}', [RkasController::class, 'getByMonth'])->name('penganggaran.rkas.getByMonth');
    Route::get('/all-data', [RkasController::class, 'getAllData'])->name('penganggaran.rkas.getAllData');
    Route::put('/{id}', [RkasController::class, 'update'])->name('penganggaran.rkas.update');
    Route::put('/{id}/update-tanggal-cetak', [PenganggaranController::class, 'updateTanggalCetak'])
        ->name('penganggaran.update-tanggal-cetak');
    Route::get('/{id}', [RkasController::class, 'show'])->name('penganggaran.rkas.show');
    Route::delete('/{id}', [RkasController::class, 'destroy'])->name('penganggaran.rkas.destroy');
    Route::get('/total/per-bulan', [RkasController::class, 'getTotalPerBulan'])->name('penganggaran.rkas.getTotalPerBulan');
    // Routes for Tahap 1 and Tahap 2 functionality
    Route::get('/total-tahap1', [RkasController::class, 'getTotalTahap1'])->name('penganggaran.rkas.total-tahap1');
    Route::get('/total-tahap2', [RkasController::class, 'getTotalTahap2'])->name('penganggaran.rkas.total-tahap2');
    Route::get('/data-tahap/{tahap}', [RkasController::class, 'getDataByTahap'])->name('penganggaran.rkas.data-tahap');

    // Routes untuk PDF dan Preview
    Route::get('/generate-pdf', [RkasController::class, 'generatePdf'])->name('penganggaran.rkas.generate-pdf');
    Route::get('/penganggaran/rkas/rekapan', [RkasController::class, 'showRekapan'])->name('penganggaran.rkas.rekapan');
    Route::get('/penganggaran/rkas/generate-pdf-rekap', [RkasController::class, 'generatePdfRkaRekap'])->name('penganggaran.rkas.generate-pdf-rekap');
    Route::get('/penganggaran/rkas/generate-rka-221-pdf', [RkasController::class, 'generateRkaDuaSatuPdf'])
        ->name('penganggaran.rkas.generate-rka-221-pdf');
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
