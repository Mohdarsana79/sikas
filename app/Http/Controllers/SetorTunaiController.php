<?php

namespace App\Http\Controllers;

use App\Models\SetorTunai;
use App\Models\Penganggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SetorTunaiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'penganggaran_id' => 'required|exists:penganggarans,id',
            'tanggal_setor' => 'required|date',
            'jumlah_setor' => 'required|numeric|min:1'
        ]);

        try {
            // Konversi format angka
            $jumlah_setor = (float) str_replace(['Rp', '.', ',', ' '], '', $request->jumlah_setor);

            SetorTunai::create([
                'penganggaran_id' => $request->penganggaran_id,
                'tanggal_setor' => $request->tanggal_setor,
                'jumlah_setor' => $jumlah_setor,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setor tunai berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing setor tunai: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan setor tunai: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $setorTunai = SetorTunai::findOrFail($id);
            $setorTunai->delete();

            // Cek jika request AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Setor tunai berhasil dihapus',
                ]);
            }
            // Redirect untuk request biasa
            return redirect()->back()->with('success', 'Setor tunai berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting setor tunai: ' . $e->getMessage());
            // Cek jika request AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus setor tunai: ' . $e->getMessage(),
                ], 500);
            }

            // Redirect untuk request biasa
            return redirect()->back()->with('error', 'Gagal menghapus setor tunai: ' . $e->getMessage());
        }
    }
}
