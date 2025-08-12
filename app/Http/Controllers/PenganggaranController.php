<?php

namespace App\Http\Controllers;

use App\Models\Penganggaran;
use Illuminate\Http\Request;

class PenganggaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anggarans = Penganggaran::orderBy('tahun_anggaran', 'desc')->get();
        $availableYears = Penganggaran::select('tahun_anggaran')->distinct()->orderBy('tahun_anggaran', 'desc')->pluck('tahun_anggaran');

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
            'pagu_anggaran' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4|integer|min:2000|max:' . (date('Y') + 5),
            'kepala_sekolah' => 'required|string|max:255',
            'bendahara' => 'required|string|max:255',
            'komite' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:255',
            'tanggal_cetak' => 'nullable|date',
        ]);

        // Format angka sebelum disimpan
        $pagu = str_replace(['.', ','], '', $request->pagu_anggaran);

        Penganggaran::create([
            'pagu_anggaran' => $pagu,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kepala_sekolah' => $request->kepala_sekolah,
            'nip_kepala_sekolah' => $request->nip_kepala_sekolah,
            'bendahara' => $request->bendahara,
            'nip_bendahara' => $request->nip_bendahara,
            'komite' => $request->komite,
            'tanggal_cetak' => $request->tanggal_cetak,
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
    public function update(Request $request, $id)
    {
        //
        $anggaran = Penganggaran::findOrFail($id);

        $request->validate([
            'pagu_anggaran' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4|integer|min:2000|max:' . (date('Y') + 5),
            'kepala_sekolah' => 'required|string|max:255',
            'bendahara' => 'required|string|max:255',
            'komite' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:255',
        ]);

        // Format angka sebelum disimpan
        $pagu = str_replace(['.', ','], '', $request->pagu_anggaran);

        $anggaran->update([
            'pagu_anggaran' => $pagu,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kepala_sekolah' => $request->kepala_sekolah,
            'nip_kepala_sekolah' => $request->nip_kepala_sekolah,
            'bendahara' => $request->bendahara,
            'nip_bendahara' => $request->nip_bendahara,
            'komite' => $request->komite,
            'tanggal_cetak' => $request->tanggal_cetak,
        ]);

        return redirect()->route('penganggaran.index')
            ->with('success', 'Data anggaran berhasil diperbaharui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $anggaran = Penganggaran::findOrFail($id);

        $anggaran->delete();
        return redirect()->route('penganggaran.index')
            ->with('success', 'Data anggaran berhasil dihapus');
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
            ->with('success', 'Tanggal cetak berhasil diperbarui');
    }
}
