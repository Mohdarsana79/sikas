<?php

namespace App\Http\Controllers;

use App\Models\Penganggaran;
use Illuminate\Http\Request;
use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\RekamanPerubahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenganggaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anggarans = Penganggaran::orderBy('tahun_anggaran', 'desc')->get();
        $availableYears = Penganggaran::select('tahun_anggaran')->distinct()->orderBy('tahun_anggaran', 'desc')->pluck('tahun_anggaran');

        // Pengecekan data RKAS dan RKAS Perubahan untuk setiap anggaran
        $anggarans->each(function ($anggaran) {
            // Pengecekan untuk RKAS awal
            $anggaran->has_rkas = Rkas::where('penganggaran_id', $anggaran->id)->exists();

            // Pengecekan untuk RKAS Perubahan
            $anggaran->has_perubahan = RkasPerubahan::where('penganggaran_id', $anggaran->id)->exists();
        });

        return view('penganggaran.index', compact('anggarans', 'availableYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'pagu_anggaran' => 'required', // Validasi numerik akan dilakukan setelah sanitasi
            'tahun_anggaran' => 'required|digits:4|integer|min:2000|max:' . (date('Y') + 5),
            'kepala_sekolah' => 'required|string|max:255',
            'sk_kepala_sekolah' => 'required|string|max:255',
            'bendahara' => 'required|string|max:255',
            'sk_bendahara' => 'required|string|max:255',
            'komite' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:255',
            'tanggal_sk_kepala_sekolah' => 'required|date',
            'tanggal_sk_bendahara' => 'required|date',
            'tanggal_cetak' => 'nullable|date',
            'tanggal_perubahan' => 'nullable|date',
        ]);

        // Format angka sebelum disimpan
        $pagu = preg_replace('/[^\d]/', '', $request->pagu_anggaran);

        Penganggaran::create([
            'pagu_anggaran' => $pagu,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kepala_sekolah' => $request->kepala_sekolah,
            'sk_kepala_sekolah' => $request->sk_kepala_sekolah,
            'nip_kepala_sekolah' => $request->nip_kepala_sekolah,
            'bendahara' => $request->bendahara,
            'sk_bendahara' => $request->sk_bendahara,
            'nip_bendahara' => $request->nip_bendahara,
            'komite' => $request->komite,
            'tanggal_sk_kepala_sekolah' => $request->tanggal_sk_kepala_sekolah,
            'tanggal_sk_bendahara' => $request->tanggal_sk_bendahara,
            'tanggal_cetak' => $request->tanggal_cetak,
            'tanggal_perubahan' => $request->tanggal_perubahan,
        ]);

        return redirect()->route('penganggaran.index')
            ->with('success', 'Data anggaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Penganggaran $penganggaran)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penganggaran $penganggaran)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     //
    //     $anggaran = Penganggaran::findOrFail($id);

    //     $request->validate([
    //         'pagu_anggaran' => 'required', // Validasi numerik akan dilakukan setelah sanitasi
    //         'tahun_anggaran' => 'required|digits:4|integer|min:2000|max:' . (date('Y') + 5),
    //         'kepala_sekolah' => 'required|string|max:255',
    //         'bendahara' => 'required|string|max:255',
    //         'komite' => 'required|string|max:255',
    //         'nip_kepala_sekolah' => 'required|string|max:255',
    //         'nip_bendahara' => 'required|string|max:255',
    //     ]);

    //     // Format angka sebelum disimpan
    //     $pagu = preg_replace('/[^\d]/', '', $request->pagu_anggaran);


    //     $anggaran->update([
    //         'pagu_anggaran' => $pagu,
    //         'tahun_anggaran' => $request->tahun_anggaran,
    //         'kepala_sekolah' => $request->kepala_sekolah,
    //         'nip_kepala_sekolah' => $request->nip_kepala_sekolah,
    //         'bendahara' => $request->bendahara,
    //         'nip_bendahara' => $request->nip_bendahara,
    //         'komite' => $request->komite,
    //         'tanggal_cetak' => $request->tanggal_cetak,
    //         'tanggal_perubahan' => $request->tanggal_perubahan,
    //     ]);

    //     return redirect()->route('penganggaran.index')
    //         ->with('success', 'Data anggaran berhasil diperbaharui');
    // }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $anggaran = Penganggaran::findOrFail($id);
            $tahunAnggaran = $anggaran->tahun_anggaran;

            // Hapus data RKAS Perubahan terkait
            RkasPerubahan::where('penganggaran_id', $anggaran->id)->forceDelete();

            // Delete all RKAS data related to this penganggaran
            Rkas::where('penganggaran_id', $anggaran->id)->forceDelete();

            // Delete the penganggaran
            $anggaran->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data anggaran tahun ' . $tahunAnggaran . ' beserta semua data RKAS terkait berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Penganggaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data anggaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove RKAS Perubahan for specified resource.
     */
    public function destroyRkasPerubahan($id)
    {
        try {
            DB::beginTransaction();

            $anggaran = Penganggaran::findOrFail($id);
            $tahunAnggaran = $anggaran->tahun_anggaran;

            // FORCE DELETE - Hapus permanen data RKAS Perubahan
            RkasPerubahan::where('penganggaran_id', $anggaran->id)->forceDelete();

            // Reset tanggal_perubahan menjadi null
            $anggaran->update([
                'tanggal_perubahan' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data RKAS Perubahan tahun ' . $tahunAnggaran . ' berhasil dihapus permanen'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting RKAS Perubahan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data RKAS Perubahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTanggalCetak(Request $request, $id)
    {
        $request->validate([
            'tanggal_cetak' => 'nullable|date',
        ]);

        $anggaran = Penganggaran::findOrFail($id);
        $anggaran->update([
            'tanggal_cetak' => $request->tanggal_cetak,
        ]);

        return redirect()->back()
            ->with('success', 'Tanggal cetak berhasil disimpan');
    }

    public function updateTanggalPerubahan(Request $request, $id)
    {
        $request->validate([
            'tanggal_perubahan' => 'nullable|date',
        ]);

        $anggaran = Penganggaran::findOrFail($id);
        $anggaran->update([
            'tanggal_perubahan' => $request->tanggal_perubahan,
        ]);

        return redirect()->back()
            ->with('success', 'Tanggal cetak berhasil disimpan');
    }
}
