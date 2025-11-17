<?php

namespace App\Http\Controllers;

use App\Models\RekamanPerubahan;
use App\Models\Penganggaran;
use Illuminate\Http\Request;

class RekamanPerubahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tahun = $request->input('tahun');

        if (!$tahun) {
            $latestPenganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            if ($latestPenganggaran) {
                $tahun = $latestPenganggaran->tahun_anggaran;
            } else {
                return view('rekaman-perubahan.index')->with('message', 'Belum ada data penganggaran yang tersedia.');
            }
        }

        $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

        $rekaman = RekamanPerubahan::with([
            'rkasPerubahan.rkasAsli.kodeKegiatan',
            'rkasPerubahan.rkasAsli.rekeningBelanja',
            'user'
        ])
            ->whereHas('rkasPerubahan', function ($query) use ($penganggaran) {
                $query->where('penganggaran_id', $penganggaran->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rekaman-perubahan.rekaman-perubahan', compact('rekaman', 'penganggaran', 'tahun'));
    }
}
