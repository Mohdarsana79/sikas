<?php

namespace App\Http\Controllers;

use App\Models\BukuKasUmum;
use App\Models\Penganggaran;
use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\PenerimaanDana;
use Illuminate\Http\Request;

class BukuKasUmumController extends Controller
{
    /**
     * Display BKU berdasarkan bulan dan tahun
     */
    public function showByBulan($tahun, $bulan)
    {
        // Cari data penganggaran berdasarkan tahun
        $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

        if (!$penganggaran) {
            return redirect()->route('penatausahaan.penatausahaan')
                ->with('error', 'Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan');
        }

        // Ambil data BKU berdasarkan bulan (akan dikembangkan lebih lanjut)
        // Untuk sekarang kita hanya akan menampilkan halaman dengan data dasar
        $data = [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'penganggaran' => $penganggaran,
        ];

        return view('bku.bku', $data);
    }

    // Method yang sudah ada tetap dipertahankan...
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(BukuKasUmum $bukuKasUmum)
    {
        //
    }

    public function edit(BukuKasUmum $bukuKasUmum)
    {
        //
    }

    public function update(Request $request, BukuKasUmum $bukuKasUmum)
    {
        //
    }

    public function destroy(BukuKasUmum $bukuKasUmum)
    {
        //
    }
}
