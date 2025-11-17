<?php

namespace App\Http\Controllers;

use App\Models\PenarikanTunai;
use App\Models\SetorTunai;
use App\Models\Penganggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PenarikanTunaiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'penganggaran_id' => 'required|exists:penganggarans,id',
            'tanggal_penarikan' => 'required|date',
            'jumlah_penarikan' => 'required|numeric|min:1'
        ]);

        try {
            // Konversi format angka
            $jumlah_penarikan = (float) str_replace(['Rp', '.', ',', ' '], '', $request->jumlah_penarikan);

            // Cek apakah dana tersedia cukup
            $totalDanaTersedia = $this->hitungTotalDanaTersedia($request->penganggaran_id);

            if ($jumlah_penarikan > $totalDanaTersedia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk penarikan ini'
                ], 422);
            }

            PenarikanTunai::create([
                'penganggaran_id' => $request->penganggaran_id,
                'tanggal_penarikan' => $request->tanggal_penarikan,
                'jumlah_penarikan' => $jumlah_penarikan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing penarikan tunai: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan penarikan tunai: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $penarikan = PenarikanTunai::findOrFail($id);
            $penarikan->delete();

            // Cek jika request AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan berhasil dihapus',
                ]);
            }

            // Redirect untuk request biasa
            return redirect()->back()->with('success', 'Penarikan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting penarikan tunai: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus penarikan: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    private function hitungTotalDanaTersedia($penganggaran_id)
    {
        $penerimaanDanas = \App\Models\PenerimaanDana::where('penganggaran_id', $penganggaran_id)->get();

        $totalDana = 0;
        foreach ($penerimaanDanas as $penerimaan) {
            if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                $totalDana += $penerimaan->saldo_awal;
            }
            $totalDana += $penerimaan->jumlah_dana;
        }

        // Kurangi dengan total penarikan yang sudah dilakukan
        $totalPenarikan = PenarikanTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_penarikan');
        $totalSetor = SetorTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_setor');

        return $totalDana - $totalPenarikan + $totalSetor;
    }
}
