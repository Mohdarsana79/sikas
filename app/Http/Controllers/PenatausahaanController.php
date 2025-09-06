<?php

namespace App\Http\Controllers;

use App\Models\Penganggaran;
use App\Models\Rkas;
use App\Models\PenerimaanDana;
use Illuminate\Http\Request;

class PenatausahaanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $tahunList = range(date('Y') - 5, date('Y') + 1);

        $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

        // Ambil bulan yang sudah memiliki RKAS
        $bulanWithRkas = [];
        if ($penganggaran) {
            $bulanWithRkas = Rkas::where('penganggaran_id', $penganggaran->id)
                ->distinct()
                ->pluck('bulan')
                ->toArray();
        }

        return view('penatausahaan.penatausahaan', [
            'tahun' => $tahun,
            'tahunList' => $tahunList,
            'penganggaran' => $penganggaran,
            'bulanWithRkas' => $bulanWithRkas
        ]);
    }
}
