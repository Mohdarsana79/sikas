<?php

namespace App\Services;

use App\Models\PenerimaanDana;
use App\Models\PenarikanTunai;
use App\Models\SetorTunai;
use App\Models\BukuKasUmum;
use Illuminate\Support\Facades\Log;

class SaldoCalculator
{
    /**
     * Hitung saldo dengan logika yang sederhana dan jelas
     */
    public function calculateSimpleSaldo($penganggaran_id)
    {
        try {
            Log::info('=== SIMPLE SALDO CALCULATION ===', ['penganggaran_id' => $penganggaran_id]);

            // 1. Total penerimaan dana
            $totalPenerimaan = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                ->sum('jumlah_dana');

            // 2. Total penarikan tunai
            $totalPenarikan = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->sum('jumlah_penarikan');

            // 3. Total setor tunai
            $totalSetor = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->sum('jumlah_setor');

            // 4. Total belanja
            $totalBelanja = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', false)
                ->sum('total_transaksi_kotor');

            // 5. Hitung belanja berdasarkan jenis
            $belanjaTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'tunai')
                ->sum('total_transaksi_kotor');

            $belanjaNonTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'non-tunai')
                ->sum('total_transaksi_kotor');

            // 6. LOGIKA INTI YANG BENAR:
            // Saldo Non-Tunai = Dana masuk ke bank - Dana keluar dari bank - Belanja non-tunai
            // Dana masuk ke bank = Total Penerimaan
            // Dana keluar dari bank = Total Penarikan
            $saldoNonTunai = $totalPenerimaan - $totalPenarikan - $belanjaNonTunai;

            // Saldo Tunai = Dana masuk ke kas - Dana keluar dari kas - Belanja tunai
            // Dana masuk ke kas = Total Penarikan
            // Dana keluar dari kas = Total Setor
            $saldoTunai = $totalPenarikan - $totalSetor - $belanjaTunai;

            // Pastikan tidak negatif
            $saldoNonTunai = max(0, $saldoNonTunai);
            $saldoTunai = max(0, $saldoTunai);

            $totalDanaTersedia = $saldoNonTunai + $saldoTunai;

            Log::info('SIMPLE SALDO RESULT:', [
                'total_penerimaan' => $totalPenerimaan,
                'total_penarikan' => $totalPenarikan,
                'total_setor' => $totalSetor,
                'belanja_tunai' => $belanjaTunai,
                'belanja_non_tunai' => $belanjaNonTunai,
                'saldo_non_tunai' => $saldoNonTunai,
                'saldo_tunai' => $saldoTunai,
                'formula_check' => [
                    'non_tunai' => "{$totalPenerimaan} - {$totalPenarikan} - {$belanjaNonTunai} = {$saldoNonTunai}",
                    'tunai' => "{$totalPenarikan} - {$totalSetor} - {$belanjaTunai} = {$saldoTunai}"
                ]
            ]);

            return [
                'tunai' => $saldoTunai,
                'non_tunai' => $saldoNonTunai,
                'total_dana_tersedia' => $totalDanaTersedia,
                'total_penerimaan' => $totalPenerimaan,
                'total_penarikan' => $totalPenarikan,
                'total_setor' => $totalSetor,
                'belanja_tunai' => $belanjaTunai,
                'belanja_non_tunai' => $belanjaNonTunai
            ];
        } catch (\Exception $e) {
            Log::error('Simple saldo calculation error: ' . $e->getMessage());

            return [
                'tunai' => 0,
                'non_tunai' => 0,
                'total_dana_tersedia' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
