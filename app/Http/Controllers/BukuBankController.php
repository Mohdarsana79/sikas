<?php

namespace App\Http\Controllers;

use App\Models\Penganggaran;
use App\Models\PenerimaanDana;
use App\Models\PenarikanTunai;
use App\Models\BukuKasUmum;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BukuBankController extends Controller
{
    /**
     * Get data BKP Bank (digunakan oleh AJAX) - VERSI DIPERBAIKI DENGAN PENERIMAAN DANA
     */
    public function getBkpBankData($tahun, $bulan)
    {
        try {
            Log::info('=== GET BKP BANK DATA DIPANGGIL ===', ['tahun' => $tahun, 'bulan' => $bulan]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil data penerimaan dana untuk bulan tersebut
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_terima', $bulanAngka)
                ->whereYear('tanggal_terima', $tahun)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

            // Ambil data penarikan tunai untuk bulan tersebut
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            // Ambil data bunga bank
            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', true)
                ->first();

            // Pastikan tanggal bunga adalah akhir bulan
            if ($bungaRecord) {
                $tanggalAkhirBulan = Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();
                if ($bungaRecord->tanggal_transaksi->format('Y-m-d') !== $tanggalAkhirBulan->format('Y-m-d')) {
                    $bungaRecord->update([
                        'tanggal_transaksi' => $tanggalAkhirBulan,
                    ]);
                    $bungaRecord->refresh();
                }
            }

            // Hitung saldo awal dengan method yang sudah diperbaiki
            $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka);

            Log::info('=== DATA BKP BANK - DENGAN PENERIMAAN DANA ===', [
                'penganggaran_id' => $penganggaran->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulan_angka' => $bulanAngka,
                'saldo_awal' => $saldoAwal,
                'jumlah_penerimaan_dana' => $penerimaanDanas->count(),
                'jumlah_penarikan' => $penarikanTunais->count(),
                'bunga_record_exists' => !is_null($bungaRecord)
            ]);

            // Hitung total untuk summary
            $totalPenerimaanDana = $penerimaanDanas->sum('jumlah_dana');
            $totalPenarikan = $penarikanTunais->sum('jumlah_penarikan');
            $totalBunga = $bungaRecord ? $bungaRecord->bunga_bank : 0;
            $totalPajak = $bungaRecord ? $bungaRecord->pajak_bunga_bank : 0;

            // Sesuai dengan rumus Excel =IF(OR(F11<>0,G11<>0),SUM(F$11:F11)-SUM(G$11:G11),0)
            $totalPenerimaan = $saldoAwal + $totalPenerimaanDana + $totalBunga;
            $totalPengeluaran = $totalPenarikan + $totalPajak;
            $saldoAkhir = $totalPenerimaan - $totalPengeluaran;

            $html = view('laporan.partials.bkp-bank-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulanAngka' => $bulanAngka,
                'penerimaanDanas' => $penerimaanDanas,
                'penarikanTunais' => $penarikanTunais,
                'bungaRecord' => $bungaRecord,
                'saldoAwal' => $saldoAwal,
                'totalPenerimaanDana' => $totalPenerimaanDana,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoAkhir' => $saldoAkhir,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => [
                    'saldo_awal' => $saldoAwal,
                    'total_penerimaan_dana' => $totalPenerimaanDana,
                    'total_penarikan' => $totalPenarikan,
                    'total_bunga' => $totalBunga,
                    'total_pajak' => $totalPajak,
                    'saldo_akhir' => $saldoAkhir,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP Bank data: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Bank: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF BKP Bank - VERSI DIPERBAIKI DENGAN PENERIMAAN DANA
     */
    public function generateBkpBankPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil data penerimaan dana untuk bulan tersebut
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_terima', $bulanAngka)
                ->whereYear('tanggal_terima', $tahun)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

            // Ambil data penarikan tunai untuk bulan tersebut
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            // Ambil data bunga bank
            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', true)
                ->first();

            // Pastikan tanggal bunga adalah akhir bulan
            if ($bungaRecord) {
                $tanggalAkhirBulan = Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();
                if ($bungaRecord->tanggal_transaksi->format('Y-m-d') !== $tanggalAkhirBulan->format('Y-m-d')) {
                    $bungaRecord->update([
                        'tanggal_transaksi' => $tanggalAkhirBulan,
                    ]);
                    $bungaRecord->refresh();
                }
            }

            // Hitung saldo awal
            $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka);

            // Hitung total untuk footer
            $totalPenerimaanDana = $penerimaanDanas->sum('jumlah_dana');
            $totalPenarikan = $penarikanTunais->sum('jumlah_penarikan');
            $totalBunga = $bungaRecord ? $bungaRecord->bunga_bank : 0;
            $totalPajak = $bungaRecord ? $bungaRecord->pajak_bunga_bank : 0;

            $totalPenerimaan = $saldoAwal + $totalPenerimaanDana + $totalBunga;
            $totalPengeluaran = $totalPenarikan + $totalPajak;
            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            $printSettings = [
                'ukuran_kertas' => request()->input('ukuran_kertas', 'A4'),
                'orientasi' => request()->input('orientasi', 'landscape'),
                'font_size' => request()->input('font_size', '10pt')
            ];

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'penerimaanDanas' => $penerimaanDanas,
                'penarikanTunais' => $penarikanTunais,
                'bungaRecord' => $bungaRecord,
                'saldoAwal' => $saldoAwal,
                'totalPenerimaanDana' => $totalPenerimaanDana,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'totalPenarikan' => $totalPenarikan,
                'tanggalAkhirBulan' => BukuKasUmum::getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => BukuKasUmum::getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => BukuKasUmum::formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => BukuKasUmum::formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => BukuKasUmum::formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
                'printSettings' => $printSettings
            ];

            $pdf = PDF::loadView('laporan.bkp-bank-pdf', $data);

            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            $filename = "BKP_Bank_{$bulan}_{$tahun}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Bank PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hitung saldo bank sebelum bulan tertentu - VERSI DIPERBAIKI
     */
    private function hitungSaldoBankSebelumBulan($penganggaran_id, $bulanTarget)
    {
        try {
            Log::info('=== HITUNG SALDO BANK SEBELUM BULAN - VERSI DIPERBAIKI ===', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget
            ]);

            // Jika bulan target adalah Januari (1), maka saldo awal adalah 0
            if ($bulanTarget == 1) {
                Log::info('Saldo awal Januari = 0');
                return 0;
            }

            // Hitung total penerimaan dana sampai bulan sebelumnya
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_terima) < ?', [$bulanTarget])
                ->get();

            $totalPenerimaan = $penerimaanDanas->sum(function ($penerimaan) {
                $total = $penerimaan->jumlah_dana;
                if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                    $total += $penerimaan->saldo_awal;
                }
                return $total;
            });

            Log::info('Total Penerimaan Dana sampai bulan sebelumnya:', ['total' => $totalPenerimaan]);

            // Hitung total penarikan tunai sampai bulan sebelumnya
            $totalPenarikanSampaiBulanSebelumnya = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_penarikan) < ?', [$bulanTarget])
                ->sum('jumlah_penarikan');

            Log::info('Total Penarikan sampai bulan sebelumnya:', ['total' => $totalPenarikanSampaiBulanSebelumnya]);

            // Hitung bunga bank sampai bulan sebelumnya
            $totalBungaSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', true)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTarget])
                ->sum('bunga_bank');

            Log::info('Total Bunga sampai bulan sebelumnya:', ['total' => $totalBungaSampaiBulanSebelumnya]);

            // Hitung pajak bunga sampai bulan sebelumnya
            $totalPajakBungaSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', true)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTarget])
                ->sum('pajak_bunga_bank');

            Log::info('Total Pajak Bunga sampai bulan sebelumnya:', ['total' => $totalPajakBungaSampaiBulanSebelumnya]);

            // PERBAIKAN: Hitung total belanja NON-TUNAI sampai bulan sebelumnya
            $totalBelanjaNonTunaiSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'non-tunai') // HANYA belanja non-tunai yang mempengaruhi saldo bank
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTarget])
                ->sum('total_transaksi_kotor');

            Log::info('Total Belanja Non-Tunai sampai bulan sebelumnya:', ['total' => $totalBelanjaNonTunaiSampaiBulanSebelumnya]);

            // PERBAIKAN: Rumus saldo bank yang benar
            // Saldo Bank = Total Penerimaan - Total Penarikan + Total Bunga - Total Pajak Bunga - Total Belanja Non-Tunai
            $saldoBank = $totalPenerimaan
                - $totalPenarikanSampaiBulanSebelumnya
                + $totalBungaSampaiBulanSebelumnya
                - $totalPajakBungaSampaiBulanSebelumnya
                - $totalBelanjaNonTunaiSampaiBulanSebelumnya;

            Log::info('Perhitungan Saldo Bank Sebelum Bulan - VERSI DIPERBAIKI', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget,
                'total_penerimaan' => $totalPenerimaan,
                'total_penarikan_sampai_bulan_sebelumnya' => $totalPenarikanSampaiBulanSebelumnya,
                'total_bunga_sampai_bulan_sebelumnya' => $totalBungaSampaiBulanSebelumnya,
                'total_pajak_bunga_sampai_bulan_sebelumnya' => $totalPajakBungaSampaiBulanSebelumnya,
                'total_belanja_non_tunai_sampai_bulan_sebelumnya' => $totalBelanjaNonTunaiSampaiBulanSebelumnya,
                'saldo_bank' => $saldoBank
            ]);

            return max(0, $saldoBank);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoBankSebelumBulan: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Hitung saldo akhir BKP Bank untuk bulan tertentu - DIPERBAIKI LAGI
     */
    public function hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulan)
    {
        try {
            $bulanAngka = $this->convertBulanToNumber($bulan);

            Log::info('=== HITUNG SALDO AKHIR BKP BANK - DIPERBAIKI ===', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulan_angka' => $bulanAngka
            ]);

            // Hitung saldo awal BKP Bank untuk bulan ini
            $saldoAwalBank = 0;
            if ($bulanAngka > 1) {
                // Ambil saldo akhir dari bulan sebelumnya
                $bulanSebelumnyaAngka = $bulanAngka - 1;
                $bulanSebelumnyaNama = array_search($bulanSebelumnyaAngka, [
                    'Januari' => 1,
                    'Februari' => 2,
                    'Maret' => 3,
                    'April' => 4,
                    'Mei' => 5,
                    'Juni' => 6,
                    'Juli' => 7,
                    'Agustus' => 8,
                    'September' => 9,
                    'Oktober' => 10,
                    'November' => 11,
                    'Desember' => 12
                ]);

                if ($bulanSebelumnyaNama) {
                    $saldoAwalBank = $this->hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulanSebelumnyaNama);
                }
            } else {
                // Untuk bulan Januari, hitung penerimaan dana di bulan Januari
                $penerimaanJanuari = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                    ->whereYear('tanggal_terima', $tahun)
                    ->whereMonth('tanggal_terima', 1)
                    ->sum('jumlah_dana');

                $saldoAwalBank = $penerimaanJanuari;
                Log::info('Penerimaan Dana Januari:', ['total' => $penerimaanJanuari]);
            }

            // Data untuk bulan ini dari BKP Bank
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereYear('tanggal_penarikan', $tahun)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', true)
                ->whereYear('tanggal_transaksi', $tahun)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->first();

            $totalPenarikan = $penarikanTunais->sum('jumlah_penarikan');
            $totalBunga = $bungaRecord ? $bungaRecord->bunga_bank : 0;
            $totalPajakBunga = $bungaRecord ? $bungaRecord->pajak_bunga_bank : 0;

            // PERBAIKAN: Untuk bulan selain Januari, tambahkan penerimaan dana di bulan tersebut
            if ($bulanAngka > 1) {
                $penerimaanBulanIni = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                    ->whereYear('tanggal_terima', $tahun)
                    ->whereMonth('tanggal_terima', $bulanAngka)
                    ->sum('jumlah_dana');

                $saldoAwalBank += $penerimaanBulanIni;
                Log::info('Penerimaan Dana Bulan Ini:', [
                    'bulan' => $bulan,
                    'penerimaan' => $penerimaanBulanIni
                ]);
            }

            // Rumus: Saldo Awal + Bunga - Penarikan - Pajak Bunga
            $saldoAkhir = $saldoAwalBank + $totalBunga - $totalPenarikan - $totalPajakBunga;

            Log::info('Perhitungan Saldo Akhir BKP Bank - DIPERBAIKI:', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'saldo_awal_bank' => $saldoAwalBank,
                'total_penarikan' => $totalPenarikan,
                'total_bunga' => $totalBunga,
                'total_pajak_bunga' => $totalPajakBunga,
                'saldo_akhir_bank' => $saldoAkhir
            ]);

            return max(0, $saldoAkhir);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoAkhirBkpBank: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Debug method untuk melihat data BKP Bank
     */
    public function debugBkpBank($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Penganggaran tidak ditemukan'], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Data penarikan tunai
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                ->whereYear('tanggal_penarikan', $tahun)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->get();

            // Data bunga bank
            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->where('is_bunga_record', true)
                ->whereYear('tanggal_transaksi', $tahun)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'penganggaran_id' => $penganggaran->id,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'bulan_angka' => $bulanAngka,
                    'penarikan_tunai' => [
                        'count' => $penarikanTunais->count(),
                        'total' => $penarikanTunais->sum('jumlah_penarikan'),
                        'data' => $penarikanTunais->map(function ($item) {
                            return [
                                'tanggal' => $item->tanggal_penarikan->format('Y-m-d'),
                                'jumlah' => $item->jumlah_penarikan
                            ];
                        })
                    ],
                    'bunga_bank' => $bungaRecord ? [
                        'tanggal' => $bungaRecord->tanggal_transaksi->format('Y-m-d'),
                        'bunga' => $bungaRecord->bunga_bank,
                        'pajak' => $bungaRecord->pajak_bunga_bank
                    ] : null,
                    'saldo_akhir_bkp_bank' => $this->hitungSaldoAkhirBkpBank($penganggaran->id, $tahun, $bulan)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error debug BKP Bank: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function convertBulanToNumber($bulan)
    {
        $bulanList = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12,
        ];

        return $bulanList[$bulan] ?? 1;
    }

    private function convertNumberToBulan($angka)
    {
        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $bulanList[$angka] ?? 'Januari';
    }
}
