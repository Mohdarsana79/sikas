<?php

namespace App\Http\Controllers;

use App\Services\BukuKasService;
use App\Models\Penganggaran;
use App\Models\BukuKasUmum;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BukuRobController extends Controller
{
    protected $bukuKasService;

    public function __construct(BukuKasService $bukuKasService)
    {
        $this->bukuKasService = $bukuKasService;
    }

    /**
     * Get data ROB (Rincian Objek Belanja) - DIPERBAIKI
     */
    public function getBkpRobData($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // GUNAKAN SALDO AWAL DARI BULAN SEBELUMNYA
            $saldoAwal = $this->hitungSaldoAwalRob($penganggaran->id, $tahun, $bulan);

            // Ambil data transaksi BKU untuk bulan tersebut
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['rekeningBelanja', 'kodeKegiatan'])
                ->orderBy('kode_rekening_id')
                ->orderBy('tanggal_transaksi')
                ->get();

            // Kelompokkan data berdasarkan rekening
            $robData = [];
            $totalRealisasi = 0;
            $runningTotal = 0;

            foreach ($bkuData as $transaksi) {
                $kodeRekening = $transaksi->rekeningBelanja->kode_rekening ?? 'N/A';
                $namaRekening = $transaksi->rekeningBelanja->rincian_objek ?? 'N/A';

                if (!isset($robData[$kodeRekening])) {
                    $robData[$kodeRekening] = [
                        'kode' => $kodeRekening,
                        'nama_rekening' => $namaRekening,
                        'transaksi' => [],
                        'total_realisasi' => 0
                    ];
                }

                $realisasi = $transaksi->total_transaksi_kotor;
                $runningTotal += $realisasi;
                $totalRealisasi += $realisasi;
                $robData[$kodeRekening]['total_realisasi'] += $realisasi;

                $robData[$kodeRekening]['transaksi'][] = [
                    'tanggal' => $transaksi->tanggal_transaksi->format('d-m-Y'),
                    'no_bukti' => $transaksi->id_transaksi,
                    'uraian' => $transaksi->uraian_opsional ?? $transaksi->uraian,
                    'realisasi' => $realisasi,
                    'sisa_anggaran' => $saldoAwal - $runningTotal
                ];
            }

            $html = view('laporan.partials.bkp-rob-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'saldoAwal' => $saldoAwal,
                'robData' => $robData,
                'totalRealisasi' => $totalRealisasi,
                'sisaAnggaran' => $saldoAwal - $totalRealisasi
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => [
                    'saldo_awal' => $saldoAwal,
                    'total_realisasi' => $totalRealisasi,
                    'sisa_anggaran' => $saldoAwal - $totalRealisasi,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP ROB data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP ROB: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF BKP ROB - DIPERBAIKI
     */
    public function generateBkpRobPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // GUNAKAN SALDO AWAL DARI BULAN SEBELUMNYA
            $saldoAwal = $this->hitungSaldoAwalRob($penganggaran->id, $tahun, $bulan);

            // Ambil data transaksi BKU untuk bulan tersebut
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['rekeningBelanja', 'kodeKegiatan'])
                ->orderBy('kode_rekening_id')
                ->orderBy('tanggal_transaksi')
                ->get();

            // Kelompokkan data berdasarkan rekening
            $robData = [];
            $totalRealisasi = 0;
            $runningTotal = 0;

            foreach ($bkuData as $transaksi) {
                $kodeRekening = $transaksi->rekeningBelanja->kode_rekening ?? 'N/A';
                $namaRekening = $transaksi->rekeningBelanja->rincian_objek ?? 'N/A';

                if (!isset($robData[$kodeRekening])) {
                    $robData[$kodeRekening] = [
                        'kode' => $kodeRekening,
                        'nama_rekening' => $namaRekening,
                        'transaksi' => [],
                        'total_realisasi' => 0
                    ];
                }

                $realisasi = $transaksi->total_transaksi_kotor;
                $runningTotal += $realisasi;
                $totalRealisasi += $realisasi;
                $robData[$kodeRekening]['total_realisasi'] += $realisasi;

                $robData[$kodeRekening]['transaksi'][] = [
                    'tanggal' => $transaksi->tanggal_transaksi->format('d-m-Y'),
                    'no_bukti' => $transaksi->id_transaksi,
                    'uraian' => $transaksi->uraian_opsional ?? $transaksi->uraian,
                    'realisasi' => $realisasi,
                    'sisa_anggaran' => $saldoAwal - $runningTotal
                ];
            }

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
                'saldoAwal' => $saldoAwal,
                'robData' => $robData,
                'totalRealisasi' => $totalRealisasi,
                'sisaAnggaran' => $saldoAwal - $totalRealisasi,
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

            $pdf = PDF::loadView('laporan.bkp-rob-pdf', $data);

            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            $filename = "BKP_ROB_{$bulan}_{$tahun}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP ROB PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate HTML untuk ROB - DIPERBAIKI DENGAN SISA BULAN SEBELUMNYA
     */
    public function generateRobHtml($penganggaran, $tahun, $bulan, $bulanAngka)
    {
        try {
            // HITUNG SISA ANGGARAN DARI BULAN SEBELUMNYA
            $saldoAwalBulanIni = $this->hitungSaldoAwalRob($penganggaran->id, $tahun, $bulan);

            // Ambil data transaksi BKU untuk bulan tersebut
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['rekeningBelanja', 'kodeKegiatan'])
                ->orderBy('kode_rekening_id')
                ->orderBy('tanggal_transaksi')
                ->get();

            // Kelompokkan data berdasarkan rekening
            $robData = [];
            $totalRealisasi = 0;
            $runningTotal = 0;

            foreach ($bkuData as $transaksi) {
                $kodeRekening = $transaksi->rekeningBelanja->kode_rekening ?? 'N/A';
                $namaRekening = $transaksi->rekeningBelanja->rincian_objek ?? 'N/A';

                if (!isset($robData[$kodeRekening])) {
                    $robData[$kodeRekening] = [
                        'kode' => $kodeRekening,
                        'nama_rekening' => $namaRekening,
                        'transaksi' => [],
                        'total_realisasi' => 0
                    ];
                }

                $realisasi = $transaksi->total_transaksi_kotor;
                $runningTotal += $realisasi;
                $totalRealisasi += $realisasi;
                $robData[$kodeRekening]['total_realisasi'] += $realisasi;

                $robData[$kodeRekening]['transaksi'][] = [
                    'tanggal' => $transaksi->tanggal_transaksi->format('d-m-Y'),
                    'no_bukti' => $transaksi->id_transaksi,
                    'uraian' => $transaksi->uraian_opsional ?? $transaksi->uraian,
                    'realisasi' => $realisasi,
                    'sisa_anggaran' => $saldoAwalBulanIni - $runningTotal
                ];
            }

            return view('laporan.partials.bkp-rob-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'saldoAwal' => $saldoAwalBulanIni,
                'robData' => $robData,
                'totalRealisasi' => $totalRealisasi,
                'sisaAnggaran' => $saldoAwalBulanIni - $totalRealisasi
            ])->render();
        } catch (\Exception $e) {
            Log::error('Error generate ROB HTML: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Hitung saldo awal ROB untuk bulan tertentu (sisa dari bulan sebelumnya)
     */
    private function hitungSaldoAwalRob($penganggaran_id, $tahun, $bulan)
    {
        try {
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
                'Desember' => 12
            ];

            $bulanAngka = $bulanList[$bulan] ?? 1;

            // Jika bulan Januari, saldo awal adalah total penerimaan dana
            if ($bulanAngka == 1) {
                return $this->bukuKasService->hitungTotalDanaTersedia($penganggaran_id);
            }

            // Hitung total penerimaan dana sampai saat ini
            $totalPenerimaan = $this->bukuKasService->hitungTotalDanaTersedia($penganggaran_id);

            // Hitung total realisasi sampai bulan sebelumnya
            $totalRealisasiSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', false)
                ->where(function ($query) use ($tahun, $bulanAngka) {
                    // Transaksi dari Januari sampai bulan sebelumnya
                    $query->whereYear('tanggal_transaksi', $tahun)
                        ->whereMonth('tanggal_transaksi', '<', $bulanAngka);
                })
                ->sum('total_transaksi_kotor');

            // Saldo awal = Total Penerimaan - Total Realisasi sampai bulan sebelumnya
            $saldoAwal = $totalPenerimaan - $totalRealisasiSampaiBulanSebelumnya;

            Log::info('Perhitungan Saldo Awal ROB', [
                'penganggaran_id' => $penganggaran_id,
                'bulan' => $bulan,
                'total_penerimaan' => $totalPenerimaan,
                'realisasi_sampai_bulan_sebelumnya' => $totalRealisasiSampaiBulanSebelumnya,
                'saldo_awal' => $saldoAwal
            ]);

            return max(0, $saldoAwal);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoAwalRob: ' . $e->getMessage());
            return 0;
        }
    }

    // Helper methods
    private function convertBulanToNumber($bulan)
    {
        return $this->bukuKasService->convertBulanToNumber($bulan);
    }

    private function convertNumberToBulan($angka)
    {
        return $this->bukuKasService->convertNumberToBulan($angka);
    }
}
