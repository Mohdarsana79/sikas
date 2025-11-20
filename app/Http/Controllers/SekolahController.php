<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index()
    {
        $sekolah = Sekolah::first();
        $exists = $sekolah ? true : false;

        return view('sekolah.index', compact('sekolah', 'exists'));
    }

    public function store(Request $request)
    {
        // Cek apakah sudah ada data sekolah
        if (Sekolah::count() > 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya boleh ada satu data sekolah'
                ], 422);
            }
            return redirect()->route('sekolah.index')
                ->with('error', 'Hanya boleh ada satu data sekolah');
        }

        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|max:20',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'jenjang_sekolah' => 'required|in:SD,SMP,SMA,SMK',
            'kelurahan_desa' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten_kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'alamat' => 'required|string',
        ]);

        Sekolah::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data sekolah berhasil disimpan'
            ], 201);
        }

        return redirect()->route('sekolah.index')
            ->with('success', 'Data sekolah berhasil disimpan');
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|max:20',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'jenjang_sekolah' => 'required|in:SD,SMP,SMA,SMK',
            'kelurahan_desa' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten_kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'alamat' => 'required|string',
        ]);

        $sekolah->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data sekolah berhasil diperbarui',
                'data' => $sekolah
            ], 200);
        }

        return redirect()->route('sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data sekolah berhasil dihapus'
            ], 200);
        }

        return redirect()->route('sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus');
    }
}
