<?php

namespace App\Http\Controllers;

use App\Models\Penganggaran;
use App\Models\BukuKasUmum;
use App\Models\PenerimaanDana;
use App\Models\RekeningBelanja;
use App\Models\KodeKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan template baru
     */
    public function index()
    {
        try {
            // Ambil tahun aktif (bisa dari session atau default tahun terbaru)
            $tahunAktif = request()->get('tahun') ?? date('Y');

            // Ambil data penganggaran untuk tahun aktif
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahunAktif)->first();

            if (!$penganggaran) {
                // Jika tidak ada data penganggaran, redirect ke penganggaran index
                return redirect()->route('penganggaran.index')
                    ->with('warning', 'Silakan buat data penganggaran terlebih dahulu untuk tahun ' . $tahunAktif);
            }

            // Hitung statistik utama
            $statistik = $this->hitungStatistikDashboard($penganggaran->id, $tahunAktif);

            // Data untuk grafik realisasi per tahun
            $grafikRealisasiTahunan = $this->getGrafikRealisasiTahunan($tahunAktif);

            // Data untuk chart realisasi program
            $chartRealisasiProgram = $this->getChartRealisasiProgram($penganggaran->id, $tahunAktif);

            // Data untuk pemanfaatan anggaran 8 SNP
            $pemanfaatanAnggaran = $this->getPemanfaatanAnggaran($penganggaran->id, $tahunAktif);

            // Data perbandingan 5 tahun
            $perbandinganLimaTahun = $this->getPerbandinganLimaTahun($tahunAktif);

            // List tahun yang tersedia untuk dropdown
            $availableYears = Penganggaran::select('tahun_anggaran')
                ->distinct()
                ->orderBy('tahun_anggaran', 'desc')
                ->pluck('tahun_anggaran');

            return view('dashboard.dashboard', compact(
                'statistik',
                'grafikRealisasiTahunan',
                'chartRealisasiProgram',
                'pemanfaatanAnggaran',
                'perbandinganLimaTahun',
                'penganggaran',
                'tahunAktif',
                'availableYears'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading dashboard: ' . $e->getMessage());
            return redirect()->route('penganggaran.index')
                ->with('error', 'Gagal memuat dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Format Rupiah untuk display (31320000 -> 31,320)
     */
    private function formatRupiahDisplay($amount)
    {
        return number_format($amount / 1000, 0, ',', '.');
    }

    /**
     * Hitung statistik utama untuk dashboard
     */
    private function hitungStatistikDashboard($penganggaranId, $tahun)
    {
        try {
            // 1. Pagu Anggaran - dari PenganggaranController
            $penganggaran = Penganggaran::find($penganggaranId);
            $paguAnggaran = $penganggaran ? $penganggaran->pagu_anggaran : 0;

            // 2. Total Realisasi - dari BukuKasUmum
            $totalRealisasi = BukuKasUmum::where('penganggaran_id', $penganggaranId)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->sum('total_transaksi_kotor');

            // 3. Total Belanja (sama dengan realisasi untuk sekarang)
            $totalBelanja = $totalRealisasi;

            // 4. Defisit/Surplus
            $defisit = $paguAnggaran - $totalRealisasi;

            // 5. Persentase Realisasi - Hindari division by zero
            $persentaseRealisasi = ($paguAnggaran > 0) ? ($totalRealisasi / $paguAnggaran) * 100 : 0;

            // 6. Total Penerimaan Dana
            $totalPenerimaan = PenerimaanDana::where('penganggaran_id', $penganggaranId)
                ->whereYear('tanggal_terima', $tahun)
                ->get()
                ->sum(function ($penerimaan) {
                    $total = $penerimaan->jumlah_dana;
                    if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                        $total += $penerimaan->saldo_awal;
                    }
                    return $total;
                });

            // 7. Sisa Anggaran
            $sisaAnggaran = $paguAnggaran - $totalRealisasi;

            // 8. Surplus Penerimaan
            $surplusPenerimaan = $totalPenerimaan - $totalBelanja;

            // 9. Efisiensi Anggaran (dalam persentase)
            $efisiensiAnggaran = ($paguAnggaran > 0) ? (($totalRealisasi / $paguAnggaran) * 100) : 0;

            return [
                'pagu_anggaran' => $paguAnggaran,
                'pagu_anggaran_display' => $this->formatRupiahDisplay($paguAnggaran),
                'total_realisasi' => $totalRealisasi,
                'total_realisasi_display' => $this->formatRupiahDisplay($totalRealisasi),
                'total_belanja' => $totalBelanja,
                'total_belanja_display' => $this->formatRupiahDisplay($totalBelanja),
                'defisit' => $defisit,
                'defisit_display' => $this->formatRupiahDisplay(abs($defisit)),
                'persentase_realisasi' => $persentaseRealisasi,
                'total_penerimaan' => $totalPenerimaan,
                'total_penerimaan_display' => $this->formatRupiahDisplay($totalPenerimaan),
                'sisa_anggaran' => $sisaAnggaran,
                'sisa_anggaran_display' => $this->formatRupiahDisplay($sisaAnggaran),
                'surplus_penerimaan' => $surplusPenerimaan,
                'surplus_penerimaan_display' => $this->formatRupiahDisplay($surplusPenerimaan),
                'efisiensi_anggaran' => $efisiensiAnggaran,
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating dashboard statistics: ' . $e->getMessage());
            return [
                'pagu_anggaran' => 0,
                'pagu_anggaran_display' => '0',
                'total_realisasi' => 0,
                'total_realisasi_display' => '0',
                'total_belanja' => 0,
                'total_belanja_display' => '0',
                'defisit' => 0,
                'defisit_display' => '0',
                'persentase_realisasi' => 0,
                'total_penerimaan' => 0,
                'total_penerimaan_display' => '0',
                'sisa_anggaran' => 0,
                'sisa_anggaran_display' => '0',
                'surplus_penerimaan' => 0,
                'surplus_penerimaan_display' => '0',
                'efisiensi_anggaran' => 0,
            ];
        }
    }

    /**
     * Data untuk grafik realisasi per bulan dalam tahun anggaran
     */
    private function getGrafikRealisasiTahunan($tahunAktif)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahunAktif)->first();

            if (!$penganggaran) {
                return [
                    'categories' => [],
                    'realisasi_data' => [],
                    'pagu_total' => 0
                ];
            }

            // Label bulan singkat
            $categories = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            $realisasiData = [];

            // Hitung realisasi per bulan
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $realisasiBulan = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                    ->whereYear('tanggal_transaksi', $tahunAktif)
                    ->whereMonth('tanggal_transaksi', $bulan)
                    ->where('is_bunga_record', false)
                    ->sum('total_transaksi_kotor');

                // Konversi ke format ribuan untuk chart: 31320000 -> 31320
                $realisasiData[] = (float) ($realisasiBulan / 1000);
            }

            return [
                'categories' => $categories,
                'realisasi_data' => $realisasiData,
                'pagu_total' => $penganggaran->pagu_anggaran,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting monthly realization chart data: ' . $e->getMessage());
            return [
                'categories' => [],
                'realisasi_data' => [],
                'pagu_total' => 0,
            ];
        }
    }

    /**
     * Data untuk pemanfaatan anggaran 8 SNP - UNTUK PIE CHART
     */
    private function getPemanfaatanAnggaran($penganggaranId, $tahun)
    {
        try {
            // Ambil data langsung dari BukuKasUmum dan kelompokkan berdasarkan program
            $realisasiPerProgram = BukuKasUmum::where('penganggaran_id', $penganggaranId)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with('kodeKegiatan')
                ->get()
                ->groupBy(function ($transaksi) {
                    $kode = $transaksi->kodeKegiatan->kode ?? '';
                    return substr($kode, 0, 2);
                })
                ->map(function ($transactions) {
                    return $transactions->sum('total_transaksi_kotor');
                });

            // Struktur 8 Standar Nasional Pendidikan
            $snps = [
                '01' => 'Pengembangan Kompetensi Lulusan',
                '02' => 'Pengembangan Standar Isi',
                '03' => 'Pengembangan Standar Proses',
                '04' => 'Pengembangan Pendidik dan Tenaga Kependidikan',
                '05' => 'Pengembangan Sarana dan Prasarana Sekolah',
                '06' => 'Pengembangan Standar Pengelolaan',
                '07' => 'Pengembangan Standar Pembiayaan',
                '08' => 'Pengembangan dan Implementasi Sistem Penilaian'
            ];

            $pemanfaatanData = [];

            // Hitung TOTAL realisasi semua program
            $totalRealisasiSemuaProgram = array_sum($realisasiPerProgram->toArray());

            foreach ($snps as $kode => $snp) {
                $realisasi = $realisasiPerProgram[$kode] ?? 0;

                // Hitung persentase terhadap TOTAL REALISASI
                $persentase = ($totalRealisasiSemuaProgram > 0) ? ($realisasi / $totalRealisasiSemuaProgram) * 100 : 0;

                $realisasiDisplay = $this->formatRupiahDisplay($realisasi);

                $pemanfaatanData[] = [
                    'program' => $snp,
                    'persentase' => round($persentase, 1),
                    'realisasi' => $realisasi,
                    'realisasi_display' => $realisasiDisplay,
                    'total_realisasi' => $totalRealisasiSemuaProgram,
                    'program_asli' => $snp
                ];
            }

            return $pemanfaatanData;
        } catch (\Exception $e) {
            Log::error('Error getting SNP utilization data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper untuk mapping keyword SNP
     */
    private function getKeywordBySNP($index)
    {
        $keywords = [
            'lulusan',
            'kompetensi',
            'isi',
            'kurikulum',
            'proses',
            'pembelajaran',
            'pendidik',
            'guru',
            'tenaga',
            'sarana',
            'prasarana',
            'pengelolaan',
            'manajemen',
            'pembiayaan',
            'anggaran',
            'penilaian',
            'asesmen'
        ];
        return $keywords[$index] ?? '';
    }

    /**
     * Helper untuk mendapatkan pagu SNP
     */
    private function getPaguSNP($penganggaranId, $index)
    {
        $penganggaran = Penganggaran::find($penganggaranId);
        return $penganggaran ? ($penganggaran->pagu_anggaran / 8) : 0;
    }

    /**
     * Data perbandingan 5 tahun terakhir - DIPERBAIKI UNTUK TOOLTIP
     */
    private function getPerbandinganLimaTahun($tahunDipilih)
    {
        try {
            $tahunMulai = $tahunDipilih - 4;
            $tahunAkhir = $tahunDipilih;

            $dataPerbandingan = [];

            Log::info('Mencari data perbandingan 5 tahun:', [
                'tahun_dipilih' => $tahunDipilih,
                'range' => $tahunMulai . ' - ' . $tahunAkhir
            ]);

            // Ambil semua data penganggaran untuk 5 tahun terakhir
            $penganggaranLimaTahun = Penganggaran::whereBetween('tahun_anggaran', [$tahunMulai, $tahunAkhir])
                ->orderBy('tahun_anggaran', 'asc')
                ->get()
                ->keyBy('tahun_anggaran');

            // Untuk setiap tahun dalam range 5 tahun
            for ($tahun = $tahunMulai; $tahun <= $tahunAkhir; $tahun++) {
                $penganggaran = $penganggaranLimaTahun->get($tahun);

                if ($penganggaran) {
                    // Hitung realisasi dari BukuKasUmum untuk tahun tersebut
                    $realisasi = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                        ->whereYear('tanggal_transaksi', $tahun) // PASTIKAN tahun transaksi sesuai
                        ->where('is_bunga_record', false)
                        ->sum('total_transaksi_kotor');

                    $pagu = $penganggaran->pagu_anggaran;

                    // Hindari division by zero
                    $persentase = ($pagu > 0) ? ($realisasi / $pagu) * 100 : 0;

                    $dataPerbandingan[] = [
                        'index' => count($dataPerbandingan), // Tambahkan index untuk referensi
                        'tahun' => $tahun,
                        'realisasi' => round($persentase, 1),
                        'realisasi_rupiah' => $realisasi,
                        'realisasi_display' => $this->formatRupiahDisplay($realisasi),
                        'pagu_anggaran' => $pagu,
                        'pagu_display' => $this->formatRupiahDisplay($pagu),
                        'penganggaran_id' => $penganggaran->id,
                        'data_tersedia' => true
                    ];
                } else {
                    // Jika tidak ada data penganggaran untuk tahun tersebut
                    $dataPerbandingan[] = [
                        'index' => count($dataPerbandingan),
                        'tahun' => $tahun,
                        'realisasi' => 0,
                        'realisasi_rupiah' => 0,
                        'realisasi_display' => '0',
                        'pagu_anggaran' => 0,
                        'pagu_display' => '0',
                        'penganggaran_id' => null,
                        'data_tersedia' => false
                    ];
                }
            }

            // Log data untuk debugging
            Log::info('Data perbandingan final:', array_map(function ($item) {
                return [
                    'index' => $item['index'],
                    'tahun' => $item['tahun'],
                    'realisasi' => $item['realisasi'],
                    'data_tersedia' => $item['data_tersedia']
                ];
            }, $dataPerbandingan));

            return $dataPerbandingan;
        } catch (\Exception $e) {
            Log::error('Error getting 5-year comparison data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Data untuk chart realisasi program (pie chart)
     */
    private function getChartRealisasiProgram($penganggaranId, $tahun)
    {
        try {
            // Ambil data realisasi per program/kegiatan
            $realisasiPerProgram = BukuKasUmum::where('penganggaran_id', $penganggaranId)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with('kodeKegiatan')
                ->get()
                ->groupBy('kode_kegiatan_id')
                ->map(function ($transactions, $kegiatanId) {
                    $kegiatan = $transactions->first()->kodeKegiatan;
                    $total = $transactions->sum('total_transaksi_kotor');

                    $namaProgram = 'Tidak Terkategori';
                    if ($kegiatan) {
                        $namaProgram = $kegiatan->sub_program ?: $kegiatan->program;
                        // Potong teks jika terlalu panjang
                        if (strlen($namaProgram) > 30) {
                            $namaProgram = substr($namaProgram, 0, 30) . '...';
                        }
                    }

                    return [
                        'nama_program' => $namaProgram,
                        'total' => $total,
                        'warna' => $this->generateColor($kegiatanId)
                    ];
                })
                ->sortByDesc('total')
                ->values();

            // Jika tidak ada data, return empty collection
            if ($realisasiPerProgram->isEmpty()) {
                return collect();
            }

            // Pisahkan data menjadi 8 terbesar dan lainnya
            $topPrograms = $realisasiPerProgram->take(8);
            $lainnya = $realisasiPerProgram->slice(8);

            // Hitung total lainnya
            $totalLainnya = $lainnya->sum('total');

            $result = $topPrograms;

            // Tambahkan kategori "Lainnya" jika ada
            if ($totalLainnya > 0) {
                $result->push([
                    'nama_program' => 'Lainnya',
                    'total' => $totalLainnya,
                    'warna' => '#6c757d'
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error getting program realization chart data: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Generate warna acak untuk chart
     */
    private function generateColor($seed)
    {
        $hash = md5($seed);
        $colors = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#858796',
            '#6f42c1',
            '#e83e8c',
            '#fd7e14',
            '#20c997',
            '#6610f2',
            '#6f42c1',
            '#d63384',
            '#dc3545',
            '#fd7e14'
        ];

        $index = hexdec(substr($hash, 0, 8)) % count($colors);
        return $colors[$index];
    }

    /**
     * API untuk mendapatkan data dashboard baru (AJAX) - DIPERBAIKI
     */
    public function getDashboardNewData(Request $request)
    {
        try {
            $tahun = $request->get('tahun', date('Y'));

            Log::info('API Dashboard Called', ['tahun' => $tahun, 'ip' => $request->ip()]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::warning('Data penganggaran tidak ditemukan', ['tahun' => $tahun]);
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan untuk tahun ' . $tahun
                ], 404);
            }

            // Hitung semua data dengan error handling
            try {
                $statistik = $this->hitungStatistikDashboard($penganggaran->id, $tahun);
            } catch (\Exception $e) {
                Log::error('Error calculating statistics', ['tahun' => $tahun, 'error' => $e->getMessage()]);
                $statistik = $this->getDefaultStatistics();
            }

            try {
                $grafikRealisasiTahunan = $this->getGrafikRealisasiTahunan($tahun);
            } catch (\Exception $e) {
                Log::error('Error getting monthly chart data', ['tahun' => $tahun, 'error' => $e->getMessage()]);
                $grafikRealisasiTahunan = ['categories' => [], 'realisasi_data' => []];
            }

            try {
                $chartRealisasiProgram = $this->getChartRealisasiProgram($penganggaran->id, $tahun);
            } catch (\Exception $e) {
                Log::error('Error getting program chart data', ['tahun' => $tahun, 'error' => $e->getMessage()]);
                $chartRealisasiProgram = collect();
            }

            try {
                $pemanfaatanAnggaran = $this->getPemanfaatanAnggaran($penganggaran->id, $tahun);
            } catch (\Exception $e) {
                Log::error('Error getting utilization data', ['tahun' => $tahun, 'error' => $e->getMessage()]);
                $pemanfaatanAnggaran = [];
            }

            try {
                $perbandinganLimaTahun = $this->getPerbandinganLimaTahun($tahun);
            } catch (\Exception $e) {
                Log::error('Error getting 5-year comparison data', ['tahun' => $tahun, 'error' => $e->getMessage()]);
                $perbandinganLimaTahun = [];
            }

            // Format data untuk dashboard baru
            $formattedData = [
                'success' => true,
                'statistik' => $statistik,
                'grafik_realisasi_tahunan' => $grafikRealisasiTahunan,
                'chart_realisasi_program' => $chartRealisasiProgram,
                'pemanfaatan_anggaran' => $pemanfaatanAnggaran,
                'perbandingan_lima_tahun' => $perbandinganLimaTahun,
                'tahun' => $tahun
            ];

            Log::info('API Dashboard Success', [
                'tahun' => $tahun,
                'statistics_calculated' => true,
                'monthly_data_count' => count($grafikRealisasiTahunan['realisasi_data'] ?? []),
                'program_data_count' => count($chartRealisasiProgram ?? []),
                'utilization_data_count' => count($pemanfaatanAnggaran ?? []),
                'comparison_data_count' => count($perbandinganLimaTahun ?? [])
            ]);

            return response()->json($formattedData);
        } catch (\Exception $e) {
            Log::error('Error getting new dashboard data: ' . $e->getMessage(), [
                'tahun' => $request->get('tahun'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Default statistics jika terjadi error
     */
    private function getDefaultStatistics()
    {
        return [
            'pagu_anggaran' => 0,
            'pagu_anggaran_display' => '0',
            'total_realisasi' => 0,
            'total_realisasi_display' => '0',
            'total_belanja' => 0,
            'total_belanja_display' => '0',
            'defisit' => 0,
            'defisit_display' => '0',
            'persentase_realisasi' => 0,
            'total_penerimaan' => 0,
            'total_penerimaan_display' => '0',
            'sisa_anggaran' => 0,
            'sisa_anggaran_display' => '0',
            'surplus_penerimaan' => 0,
            'surplus_penerimaan_display' => '0',
            'efisiensi_anggaran' => 0,
        ];
    }
}
