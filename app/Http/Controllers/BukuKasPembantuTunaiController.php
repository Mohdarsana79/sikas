<?php

namespace App\Http\Controllers;

use App\Models\PenarikanTunai;
use App\Models\SetorTunai;
use App\Models\BukuKasUmum;
use App\Models\Penganggaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BukuKasPembantuTunaiController extends Controller
{
    /**
     * Get tanggal penarikan tunai terakhir untuk penganggaran tertentu
     */
    public function getTanggalPenarikanTunai($penganggaran_id)
    {
        try {
            Log::info('Getting tanggal penarikan for penganggaran:', ['penganggaran_id' => $penganggaran_id]);

            // Validasi penganggaran_id
            if (!is_numeric($penganggaran_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID penganggaran tidak valid',
                ], 400);
            }

            $penarikanTerakhir = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->orderBy('tanggal_penarikan', 'desc')
                ->first();

            Log::info('Penarikan terakhir found:', [
                'exists' => !is_null($penarikanTerakhir),
                'tanggal' => $penarikanTerakhir ? $penarikanTerakhir->tanggal_penarikan : null,
            ]);

            if ($penarikanTerakhir && $penarikanTerakhir->tanggal_penarikan) {
                return response()->json([
                    'success' => true,
                    'tanggal_penarikan' => $penarikanTerakhir->tanggal_penarikan->format('Y-m-d'),
                    'formatted_date' => $penarikanTerakhir->tanggal_penarikan->format('d F Y'),
                    'debug' => [
                        'penganggaran_id' => $penganggaran_id,
                        'found' => true,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'tanggal_penarikan' => null,
                'formatted_date' => null,
                'debug' => [
                    'penganggaran_id' => $penganggaran_id,
                    'found' => false,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting tanggal penarikan: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tanggal penarikan: ' . $e->getMessage(),
                'debug' => [
                    'penganggaran_id' => $penganggaran_id,
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Get data BKP Pembantu untuk tampilan web - METHOD BARU
     */
    public function getBkpPembantuData($tahun, $bulan)
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

            // Data untuk BKP Pembantu Tunai
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuDataTunai = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'tunai')
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Hitung saldo awal tunai
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

            // Hitung total untuk BKP Pembantu
            $totalPenerimaan = $saldoAwalTunai + $penarikanTunais->sum('jumlah_penarikan');

            // Hitung pajak untuk pembantu
            $pajakPenerimaan = 0;
            $pajakPengeluaran = 0;
            $pajakDaerahPenerimaan = 0;
            $pajakDaerahPengeluaran = 0;

            foreach ($bkuDataTunai as $transaksi) {
                // Pajak Pusat
                if ($transaksi->total_pajak > 0) {
                    $pajakPenerimaan += $transaksi->total_pajak;
                    if (!empty($transaksi->ntpn)) {
                        $pajakPengeluaran += $transaksi->total_pajak;
                    }
                }

                // Pajak Daerah
                if ($transaksi->total_pajak_daerah > 0) {
                    $pajakDaerahPenerimaan += $transaksi->total_pajak_daerah;
                    if (!empty($transaksi->ntpn)) {
                        $pajakDaerahPengeluaran += $transaksi->total_pajak_daerah;
                    }
                }
            }

            // Tambahkan SEMUA pajak ke total
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuDataTunai->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran;

            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            // Log untuk debugging
            Log::info('Data BKP Pembantu untuk Web', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'penganggaran_id' => $penganggaran->id,
                'penarikan_count' => $penarikanTunais->count(),
                'setor_count' => $setorTunais->count(),
                'bku_count' => $bkuDataTunai->count(),
                'saldoAwalTunai' => $saldoAwalTunai,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo
            ]);

            $html = view('laporan.partials.bkp-pembantu-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulanAngka' => $bulanAngka,
                'penarikanTunais' => $penarikanTunais,
                'setorTunais' => $setorTunais,
                'bkuDataTunai' => $bkuDataTunai,
                'saldoAwalTunai' => $saldoAwalTunai,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => [
                    'penarikan_count' => $penarikanTunais->count(),
                    'setor_count' => $setorTunais->count(),
                    'bku_count' => $bkuDataTunai->count(),
                    'saldoAwalTunai' => $saldoAwalTunai,
                    'totalPenerimaan' => $totalPenerimaan,
                    'totalPengeluaran' => $totalPengeluaran,
                    'currentSaldo' => $currentSaldo
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getBkpPembantuData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Pembantu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF BKP Pembantu Tunai - VERSI DIPERBAIKI
     */
    public function generateBkuPembantuTunaiPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Data untuk BKP Pembantu Tunai
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuDataTunai = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'tunai')
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Hitung saldo awal tunai
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

            // Hitung total untuk BKP Pembantu
            $totalPenerimaan = $saldoAwalTunai + $penarikanTunais->sum('jumlah_penarikan');

            // Hitung pajak untuk pembantu (SEMUA dihitung di kedua kolom)
            $pajakPenerimaan = 0;
            $pajakPengeluaran = 0;
            $pajakDaerahPenerimaan = 0;
            $pajakDaerahPengeluaran = 0;

            foreach ($bkuDataTunai as $transaksi) {
                // Pajak Pusat - SEMUA dihitung di kedua kolom
                if ($transaksi->total_pajak > 0) {
                    $pajakPenerimaan += $transaksi->total_pajak; // Selalu tambah ke penerimaan
                    if (!empty($transaksi->ntpn)) {
                        $pajakPengeluaran += $transaksi->total_pajak; // Hanya tambah ke pengeluaran jika NTPN ada
                    }
                }

                // Pajak Daerah - SEMUA dihitung di kedua kolom
                if ($transaksi->total_pajak_daerah > 0) {
                    $pajakDaerahPenerimaan += $transaksi->total_pajak_daerah; // Selalu tambah ke penerimaan
                    if (!empty($transaksi->ntpn)) {
                        $pajakDaerahPengeluaran += $transaksi->total_pajak_daerah; // Hanya tambah ke pengeluaran jika NTPN ada
                    }
                }
            }

            // Tambahkan SEMUA pajak ke total
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuDataTunai->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran;

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
                'penarikanTunais' => $penarikanTunais,
                'setorTunais' => $setorTunais,
                'bkuDataTunai' => $bkuDataTunai,
                'saldoAwalTunai' => $saldoAwalTunai,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'tanggalAkhirBulan' => $this->getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => $this->getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => $this->formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => $this->formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => $this->formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
                'printSettings' => $printSettings
            ];

            $pdf = PDF::loadView('laporan.bku-pembantu-tunai-pdf', $data);

            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            $filename = "BKP_Pembantu_Tunai_{$bulan}_{$tahun}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Pembantu Tunai PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hitung saldo tunai sebelum bulan tertentu - DIPERBAIKI
     */
    private function hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanTarget)
    {
        try {
            // Jika bulan target adalah Januari (1), maka saldo awal adalah 0
            if ($bulanTarget == 1) {
                return 0;
            }

            // Hitung total penarikan tunai sampai bulan sebelumnya
            $totalPenarikanSampaiBulanSebelumnya = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_penarikan) <= ?', [$bulanTarget - 1])
                ->sum('jumlah_penarikan');

            // Hitung total setor tunai sampai bulan sebelumnya
            $totalSetorSampaiBulanSebelumnya = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_setor) <= ?', [$bulanTarget - 1])
                ->sum('jumlah_setor');

            // Hitung total belanja tunai sampai bulan sebelumnya
            $belanjaTunaiSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('jenis_transaksi', 'tunai')
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) <= ?', [$bulanTarget - 1])
                ->sum('total_transaksi_kotor');

            $saldoTunai = ($totalPenarikanSampaiBulanSebelumnya - $totalSetorSampaiBulanSebelumnya) - $belanjaTunaiSampaiBulanSebelumnya;

            Log::info('Perhitungan Saldo Tunai Sebelum Bulan - DIPERBAIKI', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget,
                'total_penarikan' => $totalPenarikanSampaiBulanSebelumnya,
                'total_setor' => $totalSetorSampaiBulanSebelumnya,
                'belanja_tunai' => $belanjaTunaiSampaiBulanSebelumnya,
                'saldo_tunai' => $saldoTunai
            ]);

            return max(0, $saldoTunai);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoTunaiSebelumBulan: ' . $e->getMessage());
            return 0;
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

    private function getTanggalAkhirBulan($tahun, $bulan)
    {
        $bulanAngka = $this->convertBulanToNumber($bulan);
        return Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();
    }

    private function getHariAkhirBulan($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->locale('id')->dayName;
    }

    private function formatAkhirBulanLengkapHari($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->locale('id')->translatedFormat('l, j F Y');
    }

    private function formatAkhirBulanSingkat($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->format('d/m/Y');
    }

    private function formatTanggalAkhirBulanLengkap($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->locale('id')->translatedFormat('j F Y');
    }
}
