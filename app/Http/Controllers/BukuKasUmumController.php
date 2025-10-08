<?php

namespace App\Http\Controllers;

use App\Models\BukuKasUmum;
use App\Models\BukuKasUmumUraianDetail;
use App\Models\PenarikanTunai;
use App\Models\PenerimaanDana;
use App\Models\Penganggaran;
use App\Models\RekeningBelanja;
use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\SetorTunai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BukuKasUmumController extends Controller
{
    // Tambahkan method untuk mendapatkan status bulan
    public function getStatusBulan($tahun, $bulan)
    {
        $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

        if (! $penganggaran) {
            return response()->json(['status' => 'disabled']);
        }

        $status = BukuKasUmum::getStatusBulan($penganggaran->id, $bulan);

        return response()->json(['status' => $status ?? 'belum_diisi']);
    }

    // METHOD UNTUK HALAMAN AUDIT - DIPERBAIKI
    public function auditPage($penganggaran_id)
    {
        try {
            Log::info('Loading audit page', ['penganggaran_id' => $penganggaran_id]);

            $penganggaran = Penganggaran::findOrFail($penganggaran_id);

            // Ambil tahun dari penganggaran
            $tahun = $penganggaran->tahun_anggaran;
            $bulan = 'Januari'; // Default value

            return view('bku-audit.audit', [
                'penganggaran' => $penganggaran,
                'tahun' => $tahun,
                'bulan' => $bulan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading audit page: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal memuat halaman audit: '.$e->getMessage());
        }
    }

    // METHOD UNTUK GET DATA AUDIT (API)
    public function getAuditData($penganggaran_id)
    {
        try {
            Log::info('Get Audit Data API Called', ['penganggaran_id' => $penganggaran_id]);

            $auditData = $this->auditData($penganggaran_id);

            return response()->json([
                'success' => true,
                'audit_data' => $auditData,
                'message' => 'Audit data berhasil dilakukan',
                'debug' => [
                    'penganggaran_id' => $penganggaran_id,
                    'timestamp' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error dalam getAuditData: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan audit: '.$e->getMessage(),
                'audit_data' => null,
                'debug' => [
                    'penganggaran_id' => $penganggaran_id,
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    // METHOD UNTUK FIX DATA (API)
    public function fixData($penganggaran_id)
    {
        try {
            DB::beginTransaction();

            Log::info('=== PERBAIKAN DATA DIMULAI ===', ['penganggaran_id' => $penganggaran_id]);

            $fixResults = [];

            // 1. Perbaiki BKU dengan total_transaksi_kotor yang tidak sesuai
            $bkuToFix = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where(function ($query) {
                    $query->whereNull('total_transaksi_kotor')
                        ->orWhere('total_transaksi_kotor', '<=', 0)
                        ->orWhereRaw('ABS(total_transaksi_kotor - dibelanjakan) > 1000');
                })
                ->get();

            $fixedCount = 0;
            foreach ($bkuToFix as $bku) {
                $oldTotal = $bku->total_transaksi_kotor;
                $oldDibelanjakan = $bku->dibelanjakan;

                if ($bku->total_transaksi_kotor === null || $bku->total_transaksi_kotor <= 0) {
                    $bku->total_transaksi_kotor = $bku->dibelanjakan;
                } elseif (abs($bku->total_transaksi_kotor - $bku->dibelanjakan) > 1000) {
                    $bku->dibelanjakan = $bku->total_transaksi_kotor;
                }

                $bku->save();
                $fixedCount++;

                $fixResults['bku_fixed'][] = [
                    'id' => $bku->id,
                    'id_transaksi' => $bku->id_transaksi,
                    'old_total' => $oldTotal,
                    'old_dibelanjakan' => $oldDibelanjakan,
                    'new_total' => $bku->total_transaksi_kotor,
                    'new_dibelanjakan' => $bku->dibelanjakan,
                ];
            }

            $fixResults['bku_fixed_count'] = $fixedCount;

            // 2. Perbaiki closed_without_spending flag
            $closedMonths = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('status', 'closed')
                ->selectRaw('EXTRACT(MONTH FROM tanggal_transaksi) as bulan, EXTRACT(YEAR FROM tanggal_transaksi) as tahun')
                ->groupBy(DB::raw('EXTRACT(MONTH FROM tanggal_transaksi), EXTRACT(YEAR FROM tanggal_transaksi)'))
                ->get();

            $fixedClosedFlags = 0;
            foreach ($closedMonths as $month) {
                // PERBAIKAN: Gunakan logika yang sama seperti di tutupBku
                $hasRegularTransactions = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                    ->whereMonth('tanggal_transaksi', $month->bulan)
                    ->whereYear('tanggal_transaksi', $month->tahun)
                    ->where('is_bunga_record', false)
                    ->exists();

                $updateCount = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                    ->whereMonth('tanggal_transaksi', $month->bulan)
                    ->whereYear('tanggal_transaksi', $month->tahun)
                    ->update(['closed_without_spending' => ! $hasRegularTransactions]);

                $fixedClosedFlags += $updateCount;
            }

            $fixResults['closed_flags_fixed'] = $fixedClosedFlags;

            DB::commit();

            Log::info('=== PERBAIKAN DATA SELESAI ===', $fixResults);

            return response()->json([
                'success' => true,
                'fix_results' => $fixResults,
                'message' => 'Perbaikan data berhasil dilakukan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error dalam fixData: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbaiki data: '.$e->getMessage(),
            ], 500);
        }
    }

    // METHOD AUDIT DATA (INTERNAL)
    private function auditData($penganggaran_id)
    {
        try {
            Log::info('=== AUDIT DATA DIMULAI ===', ['penganggaran_id' => $penganggaran_id]);

            $auditResult = [];

            // 1. Data Penerimaan Dana
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)->get();
            $totalPenerimaan = $penerimaanDanas->sum(function ($p) {
                return $p->jumlah_dana + ($p->sumber_dana === 'Bosp Reguler Tahap 1' ? ($p->saldo_awal ?? 0) : 0);
            });

            $auditResult['penerimaan'] = [
                'total' => $totalPenerimaan,
                'detail' => $penerimaanDanas->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'sumber' => $p->sumber_dana,
                        'jumlah_dana' => $p->jumlah_dana,
                        'saldo_awal' => $p->saldo_awal,
                        'tanggal_terima' => $p->tanggal_terima,
                        'total' => $p->jumlah_dana + ($p->saldo_awal ?? 0),
                    ];
                }),
                'count' => $penerimaanDanas->count(),
            ];

            // 2. Data Penarikan dan Setor Tunai
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)->get();
            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran_id)->get();

            $totalPenarikan = $penarikanTunais->sum('jumlah_penarikan');
            $totalSetor = $setorTunais->sum('jumlah_setor');

            $auditResult['tunai_operations'] = [
                'penarikan' => [
                    'total' => $totalPenarikan,
                    'detail' => $penarikanTunais,
                    'count' => $penarikanTunais->count(),
                ],
                'setor' => [
                    'total' => $totalSetor,
                    'detail' => $setorTunais,
                    'count' => $setorTunais->count(),
                ],
                'net' => $totalPenarikan - $totalSetor,
            ];

            // 3. Data BKU
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->orderBy('tanggal_transaksi')
                ->get();

            $belanjaPerBulan = [];
            $totalBelanja = 0;

            foreach ($bkuData as $bku) {
                $bulan = $bku->tanggal_transaksi->format('F Y');
                if (! isset($belanjaPerBulan[$bulan])) {
                    $belanjaPerBulan[$bulan] = 0;
                }
                $belanjaPerBulan[$bulan] += $bku->total_transaksi_kotor;
                $totalBelanja += $bku->total_transaksi_kotor;
            }

            $auditResult['belanja'] = [
                'total' => $totalBelanja,
                'per_bulan' => $belanjaPerBulan,
                'detail' => $bkuData->map(function ($bku) {
                    return [
                        'id' => $bku->id,
                        'id_transaksi' => $bku->id_transaksi,
                        'tanggal' => $bku->tanggal_transaksi->format('d/m/Y'),
                        'jenis_transaksi' => $bku->jenis_transaksi,
                        'total_transaksi_kotor' => $bku->total_transaksi_kotor,
                        'dibelanjakan' => $bku->dibelanjakan,
                        'is_bunga_record' => $bku->is_bunga_record,
                        'status' => $bku->status,
                        'selisih_internal' => $bku->total_transaksi_kotor - $bku->dibelanjakan,
                    ];
                }),
                'count' => $bkuData->count(),
            ];

            // 4. Hitung saldo seharusnya
            $saldoSeharusnya = $totalPenerimaan - $totalBelanja;

            // 5. Saldo menurut sistem
            $saldoSistem = $this->hitungSaldoTunaiNonTunai($penganggaran_id);

            $auditResult['saldo'] = [
                'seharusnya' => $saldoSeharusnya,
                'sistem' => $saldoSistem['total_dana_tersedia'],
                'selisih' => $saldoSeharusnya - $saldoSistem['total_dana_tersedia'],
                'detail_sistem' => $saldoSistem,
            ];

            // 6. Identifikasi data bermasalah
            $problematicData = [];

            // BKU dengan selisih internal
            $bkuInternalProblem = $bkuData->filter(function ($bku) {
                return abs($bku->total_transaksi_kotor - $bku->dibelanjakan) > 1000;
            });

            if ($bkuInternalProblem->count() > 0) {
                $problematicData['bku_internal_selisih'] = $bkuInternalProblem->values();
            }

            // BKU dengan nilai negatif
            $bkuNegative = $bkuData->filter(function ($bku) {
                return $bku->total_transaksi_kotor < 0 || $bku->dibelanjakan < 0;
            });

            if ($bkuNegative->count() > 0) {
                $problematicData['bku_negative'] = $bkuNegative->values();
            }

            $auditResult['problematic_data'] = $problematicData;

            // 7. Rekomendasi perbaikan
            $recommendations = [];

            if (abs($auditResult['saldo']['selisih']) > 1000) {
                $recommendations[] = 'Ditemukan selisih signifikan: Rp '.number_format($auditResult['saldo']['selisih'], 0, ',', '.').'. Periksa data BKU dan penerimaan dana.';
            }

            if ($bkuInternalProblem->count() > 0) {
                $recommendations[] = 'Ditemukan '.$bkuInternalProblem->count().' transaksi BKU dengan ketidaksesuaian internal.';
            }

            if (count($problematicData) > 0) {
                $recommendations[] = 'Ditemukan data bermasalah yang perlu diperbaiki.';
            }

            $auditResult['recommendations'] = $recommendations;

            Log::info('=== AUDIT DATA SELESAI ===', [
                'penganggaran_id' => $penganggaran_id,
                'selisih' => $auditResult['saldo']['selisih'],
                'rekomendasi' => count($recommendations),
            ]);

            return $auditResult;
        } catch (\Exception $e) {
            Log::error('Error dalam auditData: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Method untuk debug perhitungan selisih RKAS vs BKU
     */
    public function debugPerhitungan($penganggaran_id)
    {
        try {
            Log::info('=== DEBUG PERHITUNGAN DIMULAI ===', ['penganggaran_id' => $penganggaran_id]);

            $penganggaran = Penganggaran::find($penganggaran_id);
            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanList = ['Januari', 'Februari', 'Maret'];
            $results = [];
            $totalRkas = 0;
            $totalBku = 0;

            foreach ($bulanList as $bulan) {
                // Hitung dari RKAS
                $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
                $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

                $rkasTotal = $model::where('penganggaran_id', $penganggaran_id)
                    ->where('bulan', $bulan)
                    ->sum(DB::raw('harga_satuan * jumlah'));

                // Hitung dari BKU
                $bkuTotal = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                    ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
                    ->sum('total_transaksi_kotor');

                $results[$bulan] = [
                    'rkas' => (float) $rkasTotal,
                    'bku' => (float) $bkuTotal,
                    'selisih' => (float) $rkasTotal - (float) $bkuTotal,
                ];

                $totalRkas += (float) $rkasTotal;
                $totalBku += (float) $bkuTotal;
            }

            $results['total'] = [
                'rkas' => (float) $totalRkas,
                'bku' => (float) $totalBku,
                'selisih' => (float) $totalRkas - (float) $totalBku,
            ];

            Log::info('=== DEBUG PERHITUNGAN SELESAI ===', $results);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Debug perhitungan berhasil',
                'penganggaran' => [
                    'id' => $penganggaran->id,
                    'tahun' => $penganggaran->tahun_anggaran,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error debug perhitungan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error debug: '.$e->getMessage(),
                'trace' => env('APP_DEBUG') ? $e->getTrace() : null,
            ], 500);
        }
    }

    /**
     * Method untuk debug data kegiatan dan rekening
     */
    public function debugKegiatanRekening($tahun, $bulan)
    {
        try {
            Log::info('=== DEBUG KEGIATAN REKENING ===', ['tahun' => $tahun, 'bulan' => $bulan]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();
            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            // Data RKAS
            $rkasData = $model::where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get();

            // Data BKU
            $bulanAngka = $this->convertBulanToNumber($bulan);
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'rkas_count' => $rkasData->count(),
                    'bku_count' => $bkuData->count(),
                    'rkas_data' => $rkasData->take(3), // Ambil 3 data pertama
                    'bku_data' => $bkuData->take(3),   // Ambil 3 data pertama
                    'summary' => [
                        'total_rkas' => $rkasData->sum(function ($item) {
                            return $item->harga_satuan * $item->jumlah;
                        }),
                        'total_bku' => $bkuData->sum('total_transaksi_kotor'),
                    ],
                ],
                'message' => 'Debug kegiatan rekening berhasil',
            ]);
        } catch (\Exception $e) {
            Log::error('Error debug kegiatan rekening: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error debug: '.$e->getMessage(),
            ], 500);
        }
    }

    // PERBAIKAN UTAMA: Method hitungSaldoTunaiNonTunai yang lebih akurat
    private function hitungSaldoTunaiNonTunai($penganggaran_id)
    {
        try {
            Log::info('=== PERHITUNGAN SALDO DIMULAI ===', ['penganggaran_id' => $penganggaran_id]);

            // 1. Hitung total penerimaan dana (termasuk saldo awal)
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)->get();
            $totalPenerimaan = $penerimaanDanas->sum(function ($penerimaan) {
                $total = $penerimaan->jumlah_dana;
                if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                    $total += $penerimaan->saldo_awal;
                }

                return $total;
            });

            Log::info('Total Penerimaan', ['total' => $totalPenerimaan]);

            // 2. Hitung total penarikan dan setor tunai
            $totalPenarikan = PenarikanTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_penarikan');
            $totalSetor = SetorTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_setor');
            $netTunai = $totalPenarikan - $totalSetor;

            Log::info('Transaksi Tunai', [
                'penarikan' => $totalPenarikan,
                'setor' => $totalSetor,
                'net' => $netTunai,
            ]);

            // 3. Hitung total belanja (gunakan total_transaksi_kotor)
            $totalBelanja = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->sum('total_transaksi_kotor');

            Log::info('Total Belanja', ['total' => $totalBelanja]);

            // 4. Hitung belanja tunai dan non-tunai
            $belanjaTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('jenis_transaksi', 'tunai')
                ->sum('total_transaksi_kotor');

            $belanjaNonTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('jenis_transaksi', 'non-tunai')
                ->sum('total_transaksi_kotor');

            Log::info('Detail Belanja', [
                'tunai' => $belanjaTunai,
                'non_tunai' => $belanjaNonTunai,
            ]);

            // 5. Hitung saldo tunai
            $saldoTunai = $netTunai - $belanjaTunai;

            // 6. Hitung saldo non-tunai
            $saldoNonTunai = $totalPenerimaan - $belanjaNonTunai - $saldoTunai;

            // 7. Validasi konsistensi data
            $totalSaldo = $saldoTunai + $saldoNonTunai;
            $totalSeharusnya = $totalPenerimaan - $totalBelanja;
            $selisih = $totalSeharusnya - $totalSaldo;

            Log::info('Validasi Saldo', [
                'saldo_tunai' => $saldoTunai,
                'saldo_non_tunai' => $saldoNonTunai,
                'total_saldo' => $totalSaldo,
                'total_seharusnya' => $totalSeharusnya,
                'selisih' => $selisih,
            ]);

            // 8. Jika ada selisih signifikan, koreksi saldo non-tunai
            if (abs($selisih) > 1000) {
                Log::warning('Koreksi saldo diperlukan', ['selisih' => $selisih]);
                $saldoNonTunai = $totalSeharusnya - $saldoTunai;
            }

            $result = [
                'tunai' => max(0, $saldoTunai),
                'non_tunai' => max(0, $saldoNonTunai),
                'total_dana_tersedia' => max(0, $totalPenerimaan - $totalBelanja),
                'total_penerimaan' => $totalPenerimaan,
                'total_belanja' => $totalBelanja,
                'belanja_tunai' => $belanjaTunai,
                'belanja_non_tunai' => $belanjaNonTunai,
                'net_tunai' => $netTunai,
                'selisih' => $selisih,
            ];

            Log::info('=== HASIL PERHITUNGAN SALDO ===', $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error dalam hitungSaldoTunaiNonTunai: '.$e->getMessage());

            return [
                'tunai' => 0,
                'non_tunai' => 0,
                'total_dana_tersedia' => 0,
                'total_penerimaan' => 0,
                'total_belanja' => 0,
                'belanja_tunai' => 0,
                'belanja_non_tunai' => 0,
                'net_tunai' => 0,
                'selisih' => 0,
            ];
        }
    }

    // Method untuk update saldo setelah simpan transaksi
    private function updateSaldoSetelahTransaksi($penganggaran_id, $jumlah_belanja, $jenis_transaksi)
    {
        // Dapatkan saldo saat ini
        $saldo = $this->hitungSaldoTunaiNonTunai($penganggaran_id);

        // Update saldo berdasarkan jenis transaksi
        if ($jenis_transaksi === 'tunai') {
            $saldoTunaiBaru = $saldo['tunai'] - $jumlah_belanja;
            // Simpan ke session atau cache untuk penggunaan real-time
            session(['saldo_tunai_'.$penganggaran_id => max(0, $saldoTunaiBaru)]);
        }

        $totalDanaTersediaBaru = $saldo['total_dana_tersedia'] - $jumlah_belanja;
        session(['total_dana_tersedia_'.$penganggaran_id => max(0, $totalDanaTersediaBaru)]);

        return $saldo;
    }

    // Update method showByBulan
    public function showByBulan($tahun, $bulan)
    {
        // Cari data penganggaran berdasarkan tahun
        $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

        if (! $penganggaran) {
            return redirect()->route('penatausahaan.penatausahaan')
                ->with('error', 'Data penganggaran untuk tahun '.$tahun.' tidak ditemukan');
        }

        // Hitung anggaran bulan ini
        $anggaranBulanIni = $this->hitungAnggaranBulanIni($penganggaran->id, $bulan);

        // Hitung total dana tersedia dan saldo tunai/non-tunai
        $totalDanaTersedia = $this->hitungTotalDanaTersedia($penganggaran->id);
        $saldo = $this->hitungSaldoTunaiNonTunai($penganggaran->id);

        // Hitung total yang sudah dibelanjakan
        $totalDibelanjakanBulanIni = $this->hitungTotalDibelanjakan($penganggaran->id, $bulan);
        $totalDibelanjakanSampaiBulanIni = $this->hitungTotalDibelanjakanSampaiBulanIni($penganggaran->id, $bulan);

        // Hitung anggaran yang belum dibelanjakan
        $anggaranBelumDibelanjakan = $this->hitungAnggaranBelumDibelanjakan($penganggaran->id, $bulan);

        // Ambil data BKU untuk bulan tersebut (hanya transaksi reguler, bukan record bunga)
        $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
            ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
            ->where('is_bunga_record', false) // Hanya transaksi reguler
            ->with(['kodeKegiatan', 'rekeningBelanja', 'uraianDetails'])
            ->orderBy('id_transaksi', 'asc') // TAMBAHKAN INI
            ->get();

        // Cek status BKU - apakah ada record yang status closed
        $isClosed = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
            ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
            ->where('status', 'closed')
            ->exists();

        // Update status bulan menjadi 'draft' ketika diakses
        if (! $isClosed) {
            BukuKasUmum::updateStatusBulan($penganggaran->id, $bulan, 'draft');
        }

        $bulanAngka = $this->convertBulanToNumber($bulan);

        // Ambil data bunga bank dari record manapun yang closed di bulan tersebut
        $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
            ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
            ->where('status', 'closed')
            ->first();

        if ($bungaRecord) {
            // Pastikan tanggal bunga adalah akhir bulan
            $tanggalAkhirBulan = Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();
            if ($bungaRecord->tanggal_transaksi->format('Y-m-d') !== $tanggalAkhirBulan->format('Y-m-d')) {
                // Update tanggal jika tidak sesuai akhir bulan
                $bungaRecord->update([
                    'tanggal_transaksi' => $tanggalAkhirBulan,
                ]);
                $bungaRecord->refresh(); // Refresh data
            }
        }

        // Ambil data penerimaan dana
        $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
            ->orderBy('tanggal_terima', 'asc')
            ->get();

        // Ambil data penarikan tunai
        $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
            ->orderBy('tanggal_penarikan', 'asc')
            ->get();

        // Ambil data setor tunai
        $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran->id)
            ->orderBy('tanggal_setor', 'asc')
            ->get();

        $data = [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'penganggaran' => $penganggaran,
            'penerimaanDanas' => $penerimaanDanas,
            'penarikanTunais' => $penarikanTunais,
            'setorTunais' => $setorTunais,
            'totalDanaTersedia' => $totalDanaTersedia,
            'saldoTunai' => $saldo['tunai'],
            'saldoNonTunai' => $saldo['non_tunai'],
            'anggaranBulanIni' => $anggaranBulanIni,
            'totalDibelanjakanBulanIni' => $totalDibelanjakanBulanIni,
            'totalDibelanjakanSampaiBulanIni' => $totalDibelanjakanSampaiBulanIni,
            'anggaranBelumDibelanjakan' => $anggaranBelumDibelanjakan,
            'bkuData' => $bkuData,
            'is_closed' => $isClosed,
            'bunga_bank' => $bungaRecord ? $bungaRecord->bunga_bank : 0,
            'pajak_bunga_bank' => $bungaRecord ? $bungaRecord->pajak_bunga_bank : 0,
            'has_transactions' => $bkuData->count() > 0,
        ];

        return view('bku.bku', $data);
    }

    private function hitungAnggaranBulanIni($penganggaran_id, $bulan)
    {
        try {
            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            // Hitung total anggaran untuk bulan tertentu
            $totalAnggaran = $model::where('penganggaran_id', $penganggaran_id)
                ->where('bulan', $bulan)
                ->sum(DB::raw('harga_satuan * jumlah'));

            return $totalAnggaran;
        } catch (\Exception $e) {
            Log::error('Error menghitung anggaran bulan ini: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Hitung total dana tersedia berdasarkan penerimaan dana
     */
    private function hitungTotalDanaTersedia($penganggaran_id)
    {
        try {
            // Ambil semua penerimaan dana untuk penganggaran tertentu
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)->get();

            $totalDana = 0;

            foreach ($penerimaanDanas as $penerimaan) {
                // Untuk BOSP Reguler Tahap 1, tambahkan saldo_awal jika ada
                if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                    $totalDana += $penerimaan->saldo_awal;
                }

                // Tambahkan jumlah dana untuk semua sumber
                $totalDana += $penerimaan->jumlah_dana;
            }

            return $totalDana;
        } catch (\Exception $e) {
            Log::error('Error menghitung total dana tersedia: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * API untuk mendapatkan total dana tersedia (jika diperlukan untuk AJAX)
     */
    public function getTotalDanaTersedia($penganggaran_id)
    {
        try {
            $totalDana = $this->hitungTotalDanaTersedia($penganggaran_id);

            return response()->json([
                'success' => true,
                'total_dana_tersedia' => $totalDana,
                'formatted_total' => 'Rp '.number_format($totalDana, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dana tersedia: '.$e->getMessage(),
            ], 500);
        }
    }

    // PERBAIKAN: ambil kegiatan dan rekening dengan memperhitungkan pergeseran bulan dalam periode yang sama
    public function getKegiatanDanRekening($tahun, $bulan)
    {
        try {
            Log::info('=== DEBUG getKegiatanDanRekening - PERGESERAN DALAM PERIODE ===', [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            Log::info('Model yang digunakan:', [
                'bulan' => $bulan,
                'isTahap1' => $isTahap1,
                'model' => $model,
            ]);

            // Konversi bulan ke angka
            $bulanAngkaList = [
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

            $bulanTargetNumber = $bulanAngkaList[$bulan] ?? 1;

            // PERBAIKAN PENTING: Tentukan range bulan yang akan diambil
            // Untuk periode yang sama, ambil SEMUA BULAN dari awal periode sampai bulan target
            if ($isTahap1) {
                // Tahap 1: Januari sampai bulan target (Januari-Juni)
                $startBulan = 1;
                $endBulan = $bulanTargetNumber;
            } else {
                // Tahap 2: Juli sampai bulan target (Juli-Desember)
                $startBulan = 7; // Mulai dari Juli
                $endBulan = $bulanTargetNumber;
            }

            $bulanUntukDiambil = [];
            for ($i = $startBulan; $i <= $endBulan; $i++) {
                $bulanNama = array_search($i, $bulanAngkaList);
                if ($bulanNama) {
                    $bulanUntukDiambil[] = $bulanNama;
                }
            }

            Log::info('Bulan yang akan diambil:', [
                'start_bulan' => $startBulan,
                'end_bulan' => $endBulan,
                'bulan_diambil' => $bulanUntukDiambil,
            ]);

            // Ambil data RKAS dari bulan-bulan yang ditentukan
            $allRkasData = collect();
            foreach ($bulanUntukDiambil as $bulanItem) {
                $rkasData = $model::where('penganggaran_id', $penganggaran->id)
                    ->where('bulan', $bulanItem)
                    ->with(['kodeKegiatan', 'rekeningBelanja'])
                    ->get();

                Log::info('Data RKAS untuk bulan '.$bulanItem.':', ['count' => $rkasData->count()]);

                $allRkasData = $allRkasData->merge($rkasData);
            }

            Log::info('Total data RKAS ditemukan:', ['count' => $allRkasData->count()]);

            if ($allRkasData->isEmpty()) {
                Log::warning('Tidak ada data RKAS ditemukan');

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'kegiatan_list' => [],
                    'rekening_list' => [],
                    'message' => 'Tidak ada data RKAS untuk bulan '.implode(', ', $bulanUntukDiambil),
                ]);
            }

            // Ambil data BKU yang sudah dibelanjakan untuk periode yang sesuai
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->where(function ($query) use ($isTahap1, $bulanTargetNumber) {
                    if ($isTahap1) {
                        // Untuk Tahap 1: data dari Januari sampai bulan target
                        $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 1 AND ?', [$bulanTargetNumber]);
                    } else {
                        // Untuk Tahap 2: data dari Juli sampai bulan target
                        $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 7 AND ?', [$bulanTargetNumber]);
                    }
                })
                ->whereYear('tanggal_transaksi', $tahun)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get();

            Log::info('Data BKU ditemukan:', ['count' => $bkuData->count()]);

            // Kelompokkan data berdasarkan kode kegiatan
            $kegiatanList = [];
            $rekeningList = [];

            $groupedData = $allRkasData->groupBy('kode_id')->map(function ($items) use ($bkuData, &$kegiatanList, &$rekeningList) {
                $kegiatan = $items->first()->kodeKegiatan;

                // Kelompokkan rekening belanja by kode_rekening_id
                $rekeningGrouped = $items->groupBy('kode_rekening_id')->map(function ($rekeningItems) use ($bkuData, $kegiatan) {
                    $firstItem = $rekeningItems->first();

                    // Hitung total yang sudah dibelanjakan untuk rekening ini dalam periode yang sesuai
                    $sudahDibelanjakan = $bkuData->where('kode_rekening_id', $firstItem->kode_rekening_id)
                        ->where('kode_kegiatan_id', $kegiatan->id)
                        ->sum('dibelanjakan');

                    // Hitung total anggaran untuk rekening ini
                    $totalAnggaran = $rekeningItems->sum(function ($item) {
                        return $item->harga_satuan * $item->jumlah;
                    });

                    $sisaAnggaran = $totalAnggaran - $sudahDibelanjakan;

                    Log::info('Perhitungan rekening:', [
                        'rekening' => $firstItem->rekeningBelanja->rincian_objek ?? 'N/A',
                        'total_anggaran' => $totalAnggaran,
                        'sudah_dibelanjakan' => $sudahDibelanjakan,
                        'sisa_anggaran' => $sisaAnggaran,
                    ]);

                    // Hanya tampilkan rekening yang masih memiliki sisa anggaran
                    if ($sisaAnggaran > 0) {
                        $rekeningData = [
                            'id' => $firstItem->kode_rekening_id,
                            'kegiatan_id' => $kegiatan->id,
                            'kode_rekening' => $firstItem->rekeningBelanja->kode_rekening ?? 'N/A',
                            'rincian_objek' => $firstItem->rekeningBelanja->rincian_objek ?? 'N/A',
                            'total_anggaran' => $totalAnggaran,
                            'sudah_dibelanjakan' => $sudahDibelanjakan,
                            'sisa_anggaran' => $sisaAnggaran,
                            'uraian_tersedia' => true,
                        ];

                        return $rekeningData;
                    }

                    return null;
                })->filter()->values();

                // Hanya tambahkan kegiatan jika memiliki minimal satu rekening yang valid
                if ($rekeningGrouped->count() > 0) {
                    $kegiatanList[] = [
                        'id' => $kegiatan->id,
                        'kode' => $kegiatan->kode,
                        'program' => $kegiatan->program,
                        'sub_program' => $kegiatan->sub_program,
                        'uraian' => $kegiatan->uraian,
                        'rekening_count' => $rekeningGrouped->count(),
                    ];

                    foreach ($rekeningGrouped as $rekening) {
                        $rekeningList[] = $rekening;
                    }

                    return [
                        'kegiatan' => $kegiatan,
                        'rekening_belanja' => $rekeningGrouped,
                    ];
                }

                return null;
            })->filter()->values();

            Log::info('Final result:', [
                'kegiatan_count' => count($kegiatanList),
                'rekening_count' => count($rekeningList),
                'periode' => $isTahap1 ? 'Tahap 1' : 'Tahap 2',
            ]);

            return response()->json([
                'success' => true,
                'data' => $groupedData,
                'kegiatan_list' => $kegiatanList,
                'rekening_list' => $rekeningList,
                'periode' => $isTahap1 ? 'Tahap 1 (Januari-Juni)' : 'Perubahan (Juli-Desember)',
                'debug' => [
                    'model_digunakan' => $model,
                    'bulan_diambil' => $bulanUntukDiambil,
                    'start_bulan' => $isTahap1 ? 1 : 7,
                    'end_bulan' => $bulanTargetNumber,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting kegiatan dan rekening: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: '.$e->getMessage(),
            ], 500);
        }
    }

    // PERBAIKAN: ambil uraian dengan perhitungan yang lebih akurat - VERSI DIPERBAIKI
    public function getUraianByRekening($tahun, $bulan, $rekeningId, Request $request)
    {
        try {
            $kegiatanId = $request->query('kegiatan_id');

            Log::info('=== DEBUG getUraianByRekening - PERHITUNGAN AKURAT ===', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'rekeningId' => $rekeningId,
                'kegiatanId' => $kegiatanId,
            ]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            // Tentukan model
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            $bulanAngkaList = [
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

            $bulanTargetNumber = $bulanAngkaList[$bulan] ?? 1;
            $startBulan = $isTahap1 ? 1 : 7;

            // Ambil data RKAS
            $allRkasData = collect();
            for ($i = $startBulan; $i <= $bulanTargetNumber; $i++) {
                $bulanNama = array_search($i, $bulanAngkaList);
                if (! $bulanNama) {
                    continue;
                }

                $rkasData = $model::where('penganggaran_id', $penganggaran->id)
                    ->where('bulan', $bulanNama)
                    ->where('kode_rekening_id', $rekeningId)
                    ->where('kode_id', $kegiatanId)
                    ->get();

                $rkasData->each(function ($item) use ($bulanNama) {
                    $item->bulan_asal = $bulanNama;
                });

                $allRkasData = $allRkasData->merge($rkasData);
            }

            if ($allRkasData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada uraian untuk kombinasi kegiatan dan rekening ini',
                ]);
            }

            // PERBAIKAN PENTING: Hitung volume yang sudah dibelanjakan dengan cara yang lebih akurat
            // Ambil semua data BKU detail untuk kombinasi ini
            $bkuDetails = BukuKasUmumUraianDetail::where('penganggaran_id', $penganggaran->id)
                ->where('kode_rekening_id', $rekeningId)
                ->where('kode_kegiatan_id', $kegiatanId)
                ->whereHas('bukuKasUmum', function ($query) use ($isTahap1, $bulanTargetNumber) {
                    if ($isTahap1) {
                        $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 1 AND ?', [$bulanTargetNumber]);
                    } else {
                        $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 7 AND ?', [$bulanTargetNumber]);
                    }
                })
                ->get();

            Log::info('Data BKU Details ditemukan:', ['count' => $bkuDetails->count()]);

            // Kelompokkan uraian dengan matching yang lebih akurat
            $uraianGrouped = $allRkasData->groupBy('uraian')->map(function ($uraianItems) use ($bkuDetails, $isTahap1) {
                $firstItem = $uraianItems->first();
                $uraianName = $firstItem->uraian; // PERBAIKAN: Definisikan variabel

                // Hitung total volume dari RKAS
                $totalVolumeRkas = $uraianItems->sum('jumlah');
                $bulanAsal = $uraianItems->pluck('bulan_asal')->unique()->sort()->values();

                // PERBAIKAN: Hitung volume yang sudah dibelanjakan dengan matching yang lebih akurat
                $sudahDibelanjakanVolume = 0;

                // Cari data BKU yang memiliki uraian yang sama persis atau mengandung uraian ini
                $matchingBkuDetails = $bkuDetails->filter(function ($bkuDetail) use ($uraianName) {
                    // Matching exact atau partial yang lebih longgar
                    return strpos($bkuDetail->uraian, $uraianName) !== false ||
                        strpos($uraianName, $bkuDetail->uraian) !== false;
                });

                $sudahDibelanjakanVolume = $matchingBkuDetails->sum('volume');

                Log::info('Perhitungan uraian: '.$uraianName, [
                    'total_volume_rkas' => $totalVolumeRkas,
                    'volume_sudah_dibelanjakan' => $sudahDibelanjakanVolume,
                    'matching_bku_count' => $matchingBkuDetails->count(),
                    'sisa_volume' => $totalVolumeRkas - $sudahDibelanjakanVolume,
                    'bulan_asal' => $bulanAsal->toArray(),
                ]);

                $sisaVolume = max(0, $totalVolumeRkas - $sudahDibelanjakanVolume);

                return [
                    'id' => $firstItem->id,
                    'uraian' => $uraianName,
                    'total_volume' => $totalVolumeRkas,
                    'volume_maksimal' => $sisaVolume,
                    'harga_satuan' => $firstItem->harga_satuan,
                    'satuan' => $firstItem->satuan,
                    'volume_sudah_dibelanjakan' => $sudahDibelanjakanVolume,
                    'sisa_volume' => $sisaVolume,
                    'dapat_digunakan' => $sisaVolume > 0,
                    'bulan_asal' => $bulanAsal->toArray(),
                    'is_tahap1' => $isTahap1,
                    'debug_info' => "RKAS: {$totalVolumeRkas}, Sudah: {$sudahDibelanjakanVolume}, Sisa: {$sisaVolume}",
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $uraianGrouped,
                'debug' => [
                    'periode' => $isTahap1 ? 'Tahap 1' : 'Tahap 2',
                    'total_uraian' => count($uraianGrouped),
                    'uraian_dapat_digunakan' => collect($uraianGrouped)->filter(fn ($u) => $u['dapat_digunakan'])->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting uraian: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data uraian: '.$e->getMessage(),
            ], 500);
        }
    }

    // Helper function untuk konversi bulan
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

    // Method yang sudah ada tetap dipertahankan...
    public function index()
    {
        //
    }

    // Di dalam method store
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validasi data
            $validated = $request->validate([
                'penganggaran_id' => 'required|exists:penganggarans,id',
                'kode_kegiatan_id' => 'required|array',
                'kode_kegiatan_id.*' => 'exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|array',
                'kode_rekening_id.*' => 'exists:rekening_belanjas,id',
                'tanggal_nota' => 'required|date',
                'jenis_transaksi' => 'required|in:tunai,non-tunai',
                'nomor_nota' => 'required|string|max:100',
                'nama_penyedia' => 'nullable|string|max:255',
                'nama_penerima' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'nomor_telepon' => 'nullable|string|max:20',
                'npwp' => 'nullable|string|max:25',
                'uraian_opsional' => 'nullable|string',
                'uraian_items' => 'required|array',
                'uraian_items.*.id' => 'required|numeric',
                'uraian_items.*.uraian_text' => 'required|string',
                'uraian_items.*.jumlah_belanja' => 'required|numeric|min:0',
                'uraian_items.*.volume' => 'required|numeric|min:0',
                'uraian_items.*.harga_satuan' => 'required|numeric|min:0',
                'uraian_items.*.kegiatan_id' => 'required|exists:kode_kegiatans,id',
                'uraian_items.*.rekening_id' => 'required|exists:rekening_belanjas,id',
                'pajak_items' => 'nullable|array',
                'bulan' => 'required|string',
                'total_transaksi_kotor' => 'nullable|numeric',
            ]);

            // Validasi tambahan: pastikan tanggal nota sesuai dengan bulan yang dipilih
            $bulanTarget = $validated['bulan'];
            $bulanAngka = $this->convertBulanToNumber($bulanTarget);
            $tahunAnggaran = Penganggaran::find($validated['penganggaran_id'])->tahun_anggaran;

            $tanggalNota = \Carbon\Carbon::parse($validated['tanggal_nota']);
            if ($tanggalNota->month != $bulanAngka || $tanggalNota->year != $tahunAnggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal nota harus dalam bulan '.$bulanTarget.' tahun '.$tahunAnggaran,
                ], 422);
            }

            // Hitung total yang dibelanjakan
            $totalDibelanjakan = 0;
            $savedBkuIds = [];

            // Simpan data untuk setiap kegiatan dan rekening
            foreach ($validated['kode_kegiatan_id'] as $index => $kegiatanId) {
                $rekeningId = $validated['kode_rekening_id'][$index];

                // Filter uraian items untuk kegiatan dan rekening ini
                $uraianItemsForThisKegiatan = array_filter($validated['uraian_items'], function ($item) use ($kegiatanId, $rekeningId) {
                    return $item['kegiatan_id'] == $kegiatanId && $item['rekening_id'] == $rekeningId;
                });

                if (empty($uraianItemsForThisKegiatan)) {
                    continue;
                }

                // Hitung total untuk kegiatan ini
                $totalKegiatan = 0;
                foreach ($uraianItemsForThisKegiatan as $item) {
                    $totalKegiatan += $item['jumlah_belanja'];
                }

                // PERBAIKAN: Jangan kurangi pajak dari total belanja
                $totalKegiatanSetelahPajak = $totalKegiatan; // Tetap sama, tidak dikurangi pajak

                // Dapatkan data rekening belanja untuk uraian
                $rekeningBelanja = RekeningBelanja::find($rekeningId);
                $uraianText = 'Lunas Bayar '.$rekeningBelanja->rincian_objek;

                // Dapatkan total anggaran untuk rekening belanja di bulan tersebut
                $isTahap1 = in_array($bulanTarget, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
                $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

                $totalAnggaran = $model::where('penganggaran_id', $validated['penganggaran_id'])
                    ->where('kode_rekening_id', $rekeningId)
                    ->where('bulan', $bulanTarget)
                    ->sum(DB::raw('harga_satuan * jumlah'));

                // Simpan data Buku Kas Umum
                $bku = BukuKasUmum::create([
                    'penganggaran_id' => $validated['penganggaran_id'],
                    'kode_kegiatan_id' => $kegiatanId,
                    'kode_rekening_id' => $rekeningId,
                    'tanggal_transaksi' => $validated['tanggal_nota'],
                    'jenis_transaksi' => $validated['jenis_transaksi'],
                    'id_transaksi' => $validated['nomor_nota'],
                    'nama_penyedia_barang_jasa' => $validated['nama_penyedia'],
                    'nama_penerima_pembayaran' => $validated['nama_penerima'],
                    'alamat' => $validated['alamat'],
                    'nomor_telepon' => $validated['nomor_telepon'],
                    'npwp' => $validated['npwp'],
                    'uraian' => $uraianText,
                    'uraian_opsional' => $validated['uraian_opsional'],
                    'anggaran' => $totalAnggaran,
                    'dibelanjakan' => $totalKegiatanSetelahPajak,
                    'total_transaksi_kotor' => $totalKegiatan,
                    'pajak' => $validated['pajak_items'][0]['jenis_pajak'] ?? null,
                    'persen_pajak' => $validated['pajak_items'][0]['persen_pajak'] ?? null,
                    'total_pajak' => $validated['pajak_items'][0]['total_pajak'] ?? null,
                    'pajak_daerah' => $validated['pajak_items'][1]['jenis_pajak'] ?? null,
                    'persen_pajak_daerah' => $validated['pajak_items'][1]['persen_pajak'] ?? null,
                    'total_pajak_daerah' => $validated['pajak_items'][1]['total_pajak'] ?? null,
                ]);

                // SIMPAN DETAIL URAIAN
                foreach ($uraianItemsForThisKegiatan as $item) {
                    BukuKasUmumUraianDetail::create([
                        'buku_kas_umum_id' => $bku->id,
                        'penganggaran_id' => $validated['penganggaran_id'],
                        'kode_kegiatan_id' => $kegiatanId,
                        'kode_rekening_id' => $rekeningId,
                        'rkas_id' => $isTahap1 ? $item['id'] : null,
                        'rkas_perubahan_id' => ! $isTahap1 ? $item['id'] : null,
                        'uraian' => $item['uraian_text'],
                        'satuan' => $item['satuan'] ?? null,
                        'harga_satuan' => $item['harga_satuan'],
                        'volume' => $item['volume'],
                        'subtotal' => $item['jumlah_belanja'],
                    ]);
                }

                $savedBkuIds[] = $bku->id;
                $totalDibelanjakan += $totalKegiatanSetelahPajak;
            }

            // UPDATE SALDO SETELAH TRANSAKSI
            $this->updateSaldoSetelahTransaksi(
                $validated['penganggaran_id'],
                $totalDibelanjakan,
                $validated['jenis_transaksi']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data BKU berhasil disimpan',
                'data' => BukuKasUmum::whereIn('id', $savedBkuIds)->with('uraianDetails')->get(),
                'saldo_update' => $this->hitungSaldoTunaiNonTunai($validated['penganggaran_id']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan BKU: '.$e->getMessage());
            Log::error('Request data: ', $request->all());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
                'debug' => $request->all(),
            ], 500);
        }
    }

    // PERBAIKAN: Method hitungTotalDibelanjakan (gunakan total_transaksi_kotor)
    private function hitungTotalDibelanjakan($penganggaran_id, $bulan)
    {
        try {
            $totalDibelanjakan = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
                ->sum('total_transaksi_kotor'); // PERBAIKAN: Gunakan total_transaksi_kotor

            Log::info('Perhitungan Total Dibelanjakan - PERBAIKAN', [
                'penganggaran_id' => $penganggaran_id,
                'bulan' => $bulan,
                'total_dibelanjakan' => $totalDibelanjakan,
            ]);

            return $totalDibelanjakan;
        } catch (\Exception $e) {
            Log::error('Error menghitung total dibelanjakan: '.$e->getMessage());

            return 0;
        }
    }

    // PERBAIKAN: Method hitungTotalDibelanjakanSampaiBulanIni
    private function hitungTotalDibelanjakanSampaiBulanIni($penganggaran_id, $bulanTarget)
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
                'Desember' => 12,
            ];

            $bulanTargetNumber = $bulanList[$bulanTarget];

            $totalDibelanjakan = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) <= ?', [$bulanTargetNumber])
                ->sum('total_transaksi_kotor'); // PERBAIKAN: Gunakan total_transaksi_kotor

            Log::info('Perhitungan Total Dibelanjakan Sampai Bulan - PERBAIKAN', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget,
                'total_dibelanjakan' => $totalDibelanjakan,
            ]);

            return $totalDibelanjakan;
        } catch (\Exception $e) {
            Log::error('Error menghitung total dibelanjakan sampai bulan ini: '.$e->getMessage());

            return 0;
        }
    }

    // PERBAIKAN: Method untuk memastikan anggaran dan belanja sama
    private function hitungAnggaranBelumDibelanjakan($penganggaran_id, $bulanTarget)
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
                'Desember' => 12,
            ];

            $bulanTargetNumber = $bulanList[$bulanTarget];

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulanTarget, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            // Hitung total anggaran sampai bulan target
            $totalAnggaranSampaiBulanIni = $model::where('penganggaran_id', $penganggaran_id)
                ->whereIn('bulan', array_slice(array_keys($bulanList), 0, $bulanTargetNumber))
                ->sum(DB::raw('harga_satuan * jumlah'));

            // Hitung total yang sudah dibelanjakan sampai bulan target (TANPA PAJAK)
            $totalDibelanjakanSampaiBulanIni = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) <= ?', [$bulanTargetNumber])
                ->sum('total_transaksi_kotor'); // Gunakan total_transaksi_kotor

            // Hitung anggaran yang belum dibelanjakan
            $anggaranBelumDibelanjakan = $totalAnggaranSampaiBulanIni - $totalDibelanjakanSampaiBulanIni;

            return max(0, $anggaranBelumDibelanjakan);
        } catch (\Exception $e) {
            Log::error('Error menghitung anggaran belum dibelanjakan: '.$e->getMessage());

            return 0;
        }
    }

    public function getTotalDibelanjakan($penganggaran_id, $bulan)
    {
        try {
            $total = $this->hitungTotalDibelanjakan($penganggaran_id, $bulan);

            return response()->json([
                'success' => true,
                'total_dibelanjakan' => $total,
                'formatted_total' => 'Rp '.number_format($total, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dibelanjakan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getTotalDibelanjakanSampaiBulan($penganggaran_id, $bulan)
    {
        try {
            $total = $this->hitungTotalDibelanjakanSampaiBulanIni($penganggaran_id, $bulan);

            return response()->json([
                'success' => true,
                'total_dibelanjakan' => $total,
                'formatted_total' => 'Rp '.number_format($total, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dibelanjakan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroyAllByBulan($tahun, $bulan)
    {
        try {
            DB::beginTransaction();

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            $deletedCount = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus '.$deletedCount.' data BKU untuk bulan '.$bulan.' '.$tahun,
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menghapus semua data BKU: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus data BKU individual
     */
    public function destroy($id)
    {
        try {
            $bku = BukuKasUmum::findOrFail($id);
            $bku->delete();

            // Cek jika request AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil dihapus',
                ]);
            }

            // Redirect untuk request biasa
            return redirect()->back()->with('success', 'Transaksi berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting BKU: '.$e->getMessage());

            // Cek jika request AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus transaksi: '.$e->getMessage(),
                ], 500);
            }

            // Redirect untuk request biasa
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: '.$e->getMessage());
        }
    }

    public function tutupBku(Request $request, $tahun, $bulan)
    {
        try {
            DB::beginTransaction();

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $validated = $request->validate([
                'bunga_bank' => 'required|numeric|min:0',
                'pajak_bunga_bank' => 'required|numeric|min:0',
            ]);

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Tentukan tanggal akhir bulan
            $tanggalAkhirBulan = Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();

            // PERBAIKAN: Cek apakah ada transaksi reguler (bukan record bunga)
            $hasRegularTransactions = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->exists();

            // PERBAIKAN: Set flag closed_without_spending dengan benar
            $closedWithoutSpending = ! $hasRegularTransactions;

            // Update semua data BKU untuk bulan tersebut menjadi status closed
            $updated = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->update([
                    'status' => 'closed',
                    'bunga_bank' => $validated['bunga_bank'],
                    'pajak_bunga_bank' => $validated['pajak_bunga_bank'],
                    'closed_without_spending' => $closedWithoutSpending,
                    'updated_at' => now(),
                ]);

            // Jika tidak ada transaksi reguler, buat record bunga bank dengan tanggal akhir bulan
            if (! $hasRegularTransactions && $updated === 0) {
                $bku = BukuKasUmum::create([
                    'penganggaran_id' => $penganggaran->id,
                    'kode_kegiatan_id' => null,
                    'kode_rekening_id' => null,
                    'tanggal_transaksi' => $tanggalAkhirBulan, // TANGGAL AKHIR BULAN
                    'jenis_transaksi' => 'non-tunai',
                    'id_transaksi' => 'BUNGA-BANK-'.$bulan.'-'.$tahun,
                    'nama_penyedia_barang_jasa' => 'Bank',
                    'uraian' => 'Pencatatan bunga bank dan pajak bunga bank',
                    'anggaran' => 0,
                    'dibelanjakan' => 0,
                    'total_transaksi_kotor' => 0,
                    'bunga_bank' => $validated['bunga_bank'],
                    'pajak_bunga_bank' => $validated['pajak_bunga_bank'],
                    'status' => 'closed',
                    'is_bunga_record' => true,
                    'closed_without_spending' => true,
                ]);

                $updated = 1;
            } else {
                // Jika ada transaksi reguler, update record bunga yang sudah ada dengan tanggal akhir bulan
                $existingBungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                    ->whereMonth('tanggal_transaksi', $bulanAngka)
                    ->whereYear('tanggal_transaksi', $tahun)
                    ->where('is_bunga_record', true)
                    ->first();

                if ($existingBungaRecord) {
                    $existingBungaRecord->update([
                        'tanggal_transaksi' => $tanggalAkhirBulan, // TANGGAL AKHIR BULAN
                        'bunga_bank' => $validated['bunga_bank'],
                        'pajak_bunga_bank' => $validated['pajak_bunga_bank'],
                    ]);
                } else {
                    // Buat record bunga baru dengan tanggal akhir bulan
                    $bku = BukuKasUmum::create([
                        'penganggaran_id' => $penganggaran->id,
                        'kode_kegiatan_id' => null,
                        'kode_rekening_id' => null,
                        'tanggal_transaksi' => $tanggalAkhirBulan, // TANGGAL AKHIR BULAN
                        'jenis_transaksi' => 'non-tunai',
                        'id_transaksi' => 'BUNGA-BANK-'.$bulan.'-'.$tahun,
                        'nama_penyedia_barang_jasa' => 'Bank',
                        'uraian' => 'Pencatatan bunga bank dan pajak bunga bank',
                        'anggaran' => 0,
                        'dibelanjakan' => 0,
                        'total_transaksi_kotor' => 0,
                        'bunga_bank' => $validated['bunga_bank'],
                        'pajak_bunga_bank' => $validated['pajak_bunga_bank'],
                        'status' => 'closed',
                        'is_bunga_record' => true,
                        'closed_without_spending' => false,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'BKU berhasil ditutup',
                'updated_count' => $updated,
                'closed_without_spending' => $closedWithoutSpending,
                'tanggal_bunga' => $tanggalAkhirBulan->format('d/m/Y'), // Info tanggal bunga
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menutup BKU: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup BKU: '.$e->getMessage(),
            ], 500);
        }
    }

    // Method bukaBku yang diperbarui
    public function bukaBku($tahun, $bulan)
    {
        try {
            DB::beginTransaction();

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Update status menjadi open untuk semua record di bulan tersebut
            $updated = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->update([
                    'status' => 'open',
                    'updated_at' => now(),
                ]);

            // Hapus record bunga bank jika tidak ada transaksi lain
            $regularTransactions = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->count();

            if ($regularTransactions === 0) {
                BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                    ->whereMonth('tanggal_transaksi', $bulanAngka)
                    ->whereYear('tanggal_transaksi', $tahun)
                    ->where('is_bunga_record', true)
                    ->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'BKU berhasil dibuka',
                'updated_count' => $updated,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuka BKU: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka BKU: '.$e->getMessage(),
            ], 500);
        }
    }

    public function updateBungaBank(Request $request, $tahun, $bulan)
    {
        try {
            DB::beginTransaction();

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $validated = $request->validate([
                'bunga_bank' => 'required|numeric|min:0',
                'pajak_bunga_bank' => 'required|numeric|min:0',
            ]);

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Tentukan tanggal akhir bulan
            $tanggalAkhirBulan = Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();

            // Update bunga bank untuk semua record closed di bulan tersebut dengan tanggal akhir bulan
            $updated = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('status', 'closed')
                ->update([
                    'tanggal_transaksi' => $tanggalAkhirBulan, // TANGGAL AKHIR BULAN
                    'bunga_bank' => $validated['bunga_bank'],
                    'pajak_bunga_bank' => $validated['pajak_bunga_bank'],
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data bunga bank berhasil diperbarui',
                'updated_count' => $updated,
                'tanggal_bunga' => $tanggalAkhirBulan->format('d/m/Y'), // Info tanggal bunga
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update bunga bank: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data bunga bank: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data pajak untuk transaksi tertentu
     */
    public function getDataPajak($id)
    {
        try {
            $bku = BukuKasUmum::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'tanggal_lapor' => $bku->tanggal_lapor ? $bku->tanggal_lapor->format('Y-m-d') : null,
                    'ntpn' => $bku->ntpn,
                    'kode_masa_pajak' => $bku->kode_masa_pajak,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pajak: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan laporan pajak
     */
    public function laporPajak(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'tanggal_lapor' => 'required|date',
                'kode_masa_pajak' => 'required|string',
                'ntpn' => 'required|string|max:16|min:16',
            ], [
                'ntpn.required' => 'NTPN wajib diisi',
                'ntpn.max' => 'NTPN harus 16 digit',
                'ntpn.min' => 'NTPN harus 16 digit',
                'kode_masa_pajak.required' => 'Kode masa pajak wajib diisi',
                'tanggal_lapor.required' => 'Tanggal lapor wajib diisi',
                'tanggal_lapor.date' => 'Format tanggal tidak valid',
            ]);

            $bku = BukuKasUmum::findOrFail($id);

            // Update data pajak
            $bku->update([
                'tanggal_lapor' => $validated['tanggal_lapor'],
                'kode_masa_pajak' => $validated['kode_masa_pajak'],
                'ntpn' => $validated['ntpn'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pajak berhasil disimpan',
                'data' => $bku,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan lapor pajak: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data pajak: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get last nota number for the month
     */
    public function getLastNotaNumber($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil semua nomor nota untuk bulan tersebut
            $bkuRecords = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false) // Hanya transaksi reguler
                ->orderBy('created_at', 'desc')
                ->get(['id_transaksi']);

            $lastNotaNumber = null;
            $lastNumericValue = 0;

            if ($bkuRecords->isNotEmpty()) {
                // Cari nomor nota dengan nilai numerik tertinggi
                foreach ($bkuRecords as $bku) {
                    $currentNota = $bku->id_transaksi;

                    // Coba ekstrak bagian numerik dari nomor nota
                    preg_match('/(\d+)/', $currentNota, $matches);

                    if (! empty($matches)) {
                        $currentNumeric = (int) $matches[1];

                        if ($currentNumeric > $lastNumericValue) {
                            $lastNumericValue = $currentNumeric;
                            $lastNotaNumber = $currentNota;
                        }
                    } else {
                        // Jika tidak ada angka, gunakan sebagai fallback
                        if (! $lastNotaNumber) {
                            $lastNotaNumber = $currentNota;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'last_nota_number' => $lastNotaNumber,
                'suggested_next_number' => $this->generateNextNotaNumber($lastNotaNumber),
                'debug' => [
                    'total_records' => $bkuRecords->count(),
                    'found_nota' => $lastNotaNumber,
                    'highest_numeric' => $lastNumericValue,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting last nota number: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil nomor nota terakhir: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate suggested next nota number
     */
    private function generateNextNotaNumber($lastNotaNumber)
    {
        if (! $lastNotaNumber) {
            return '001'; // Default untuk pertama kali
        }

        // Coba ekstrak bagian numerik
        preg_match('/(\d+)/', $lastNotaNumber, $matches);

        if (! empty($matches)) {
            $numericPart = (int) $matches[1];
            $nextNumeric = $numericPart + 1;

            // Pertahankan format prefix jika ada
            $prefix = preg_replace('/\d+/', '', $lastNotaNumber);

            // Format angka menjadi 3 digit
            $formattedNumber = str_pad($nextNumeric, 3, '0', STR_PAD_LEFT);

            return $prefix.$formattedNumber;
        }

        // Jika tidak ada angka, tambahkan -001
        return $lastNotaNumber.'-001';
    }

    // Method untuk debug perhitungan volume - VERSI DIPERBAIKI
    public function debugVolumePerhitungan($tahun, $bulan, $rekeningId, Request $request)
    {
        try {
            $kegiatanId = $request->query('kegiatan_id');

            Log::info('=== DEBUG VOLUME PERHITUNGAN ===', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'rekeningId' => $rekeningId,
                'kegiatanId' => $kegiatanId,
            ]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json(['success' => false, 'message' => 'Penganggaran tidak ditemukan']);
            }

            // Tentukan model
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            $bulanAngkaList = [
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

            $bulanTargetNumber = $bulanAngkaList[$bulan] ?? 1;

            // Ambil data RKAS
            $startBulan = $isTahap1 ? 1 : 7;
            $allRkasData = collect();

            for ($i = $startBulan; $i <= $bulanTargetNumber; $i++) {
                $bulanNama = array_search($i, $bulanAngkaList);
                if (! $bulanNama) {
                    continue;
                }

                $rkasData = $model::where('penganggaran_id', $penganggaran->id)
                    ->where('bulan', $bulanNama)
                    ->where('kode_rekening_id', $rekeningId)
                    ->where('kode_id', $kegiatanId)
                    ->get();

                $rkasData->each(function ($item) use ($bulanNama) {
                    $item->bulan_asal = $bulanNama;
                });

                $allRkasData = $allRkasData->merge($rkasData);
            }

            // Hitung volume dari RKAS per uraian
            $volumeRkasPerUraian = [];
            foreach ($allRkasData as $item) {
                $uraian = $item->uraian; // PERBAIKAN: Definisikan variabel
                if (! isset($volumeRkasPerUraian[$uraian])) {
                    $volumeRkasPerUraian[$uraian] = 0;
                }
                $volumeRkasPerUraian[$uraian] += $item->jumlah;
            }

            // Hitung volume yang sudah dibelanjakan per uraian
            $volumeSudahDibelanjakanPerUraian = [];
            $uraianNames = $allRkasData->pluck('uraian')->unique();

            foreach ($uraianNames as $uraianName) {
                $volume = BukuKasUmumUraianDetail::where('penganggaran_id', $penganggaran->id)
                    ->where('kode_rekening_id', $rekeningId)
                    ->where('kode_kegiatan_id', $kegiatanId)
                    ->where('uraian', 'LIKE', '%'.$uraianName.'%')
                    ->whereHas('bukuKasUmum', function ($query) use ($isTahap1, $bulanTargetNumber) {
                        if ($isTahap1) {
                            $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 1 AND ?', [$bulanTargetNumber]);
                        } else {
                            $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 7 AND ?', [$bulanTargetNumber]);
                        }
                    })
                    ->sum('volume');

                $volumeSudahDibelanjakanPerUraian[$uraianName] = $volume;
            }

            // Hitung sisa volume per uraian - PERBAIKAN: Gunakan cara yang benar
            $sisaVolumePerUraian = [];
            foreach ($volumeRkasPerUraian as $uraian => $volumeRkas) {
                $volumeSudah = $volumeSudahDibelanjakanPerUraian[$uraian] ?? 0;
                $sisaVolumePerUraian[$uraian] = max(0, $volumeRkas - $volumeSudah);
            }

            // Detail data BKU yang sudah dibelanjakan
            $detailBku = BukuKasUmumUraianDetail::where('penganggaran_id', $penganggaran->id)
                ->where('kode_rekening_id', $rekeningId)
                ->where('kode_kegiatan_id', $kegiatanId)
                ->whereHas('bukuKasUmum', function ($query) use ($isTahap1, $bulanTargetNumber) {
                    if ($isTahap1) {
                        $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 1 AND ?', [$bulanTargetNumber]);
                    } else {
                        $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 7 AND ?', [$bulanTargetNumber]);
                    }
                })
                ->with('bukuKasUmum')
                ->get()
                ->map(function ($item) {
                    return [
                        'uraian' => $item->uraian,
                        'volume' => $item->volume,
                        'bulan' => $item->bukuKasUmum->tanggal_transaksi->format('F'),
                        'tanggal' => $item->bukuKasUmum->tanggal_transaksi->format('Y-m-d'),
                    ];
                });

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'penganggaran_id' => $penganggaran->id,
                    'periode' => $isTahap1 ? 'Tahap 1' : 'Tahap 2',
                    'bulan_target' => $bulan,
                    'bulan_diambil' => array_slice($bulanAngkaList, $startBulan - 1, $bulanTargetNumber - $startBulan + 1, true),
                ],
                'volume_rkas' => $volumeRkasPerUraian,
                'volume_sudah_dibelanjakan' => $volumeSudahDibelanjakanPerUraian,
                'sisa_volume' => $sisaVolumePerUraian, // PERBAIKAN: Gunakan array yang sudah dihitung
                'detail_bku' => $detailBku,
                'total_rkas' => array_sum($volumeRkasPerUraian),
                'total_sudah_dibelanjakan' => array_sum($volumeSudahDibelanjakanPerUraian),
                'total_sisa' => array_sum($sisaVolumePerUraian),
            ]);
        } catch (\Exception $e) {
            Log::error('Error debug volume: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tanggal penarikan tunai terakhir untuk penganggaran tertentu
     */
    /**
     * Get tanggal penarikan tunai terakhir untuk penganggaran tertentu
     */
    public function getTanggalPenarikanTunai($penganggaran_id)
    {
        try {
            Log::info('Getting tanggal penarikan for penganggaran:', ['penganggaran_id' => $penganggaran_id]);

            // Validasi penganggaran_id
            if (! is_numeric($penganggaran_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID penganggaran tidak valid',
                ], 400);
            }

            $penarikanTerakhir = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->orderBy('tanggal_penarikan', 'desc')
                ->first();

            Log::info('Penarikan terakhir found:', [
                'exists' => ! is_null($penarikanTerakhir),
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
            Log::error('Error getting tanggal penarikan: '.$e->getMessage());
            Log::error('Error trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tanggal penarikan: '.$e->getMessage(),
                'debug' => [
                    'penganggaran_id' => $penganggaran_id,
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    // Tambahkan method public ini di BukuKasUmumController
    public function getSaldoBank($penganggaran_id, $bulan = null)
    {
        try {
            $bulanAngka = $bulan ? $this->convertBulanToNumber($bulan) : 1;

            return $this->hitungSaldoBankSebelumBulan($penganggaran_id, $bulanAngka);
        } catch (\Exception $e) {
            Log::error('Error getSaldoBank: '.$e->getMessage());

            return 0;
        }
    }

    public function rekapanBku(Request $request)
    {
        try {
            $tahun = $request->get('tahun');

            Log::info('Rekapan BKU accessed', [
                'tahun_parameter' => $tahun,
                'type' => gettype($tahun),
                'full_url' => request()->fullUrl(),
            ]);

            // Jika tahun tidak ada di query parameter, coba dapatkan dari session atau default
            if (!$tahun) {
                // Coba dapatkan tahun dari penganggaran aktif
                $penganggaranAktif = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
                $tahun = $penganggaranAktif ? $penganggaranAktif->tahun_anggaran : date('Y');
            }

            // Validasi tahun
            if (!is_numeric($tahun) || strlen($tahun) !== 4) {
                $tahun = date('Y');
            }

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return redirect()->route('penatausahaan.penatausahaan')
                    ->with('error', 'Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan');
            }

            $months = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];

            $bulan = $request->get('bulan', 'Januari');

            if (!in_array($bulan, $months)) {
                $bulan = 'Januari';
            }

            // PERBAIKAN: Ambil data yang diperlukan untuk rekapan
            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Data untuk BKP Bank
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

            // Data untuk BKP Umum (semua transaksi)
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('status', 'closed')
                ->first();

            if ($bungaRecord) {
                $tanggalAkhirBulan = Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();
                if ($bungaRecord->tanggal_transaksi->format('Y-m-d') !== $tanggalAkhirBulan->format('Y-m-d')) {
                    $bungaRecord->update([
                        'tanggal_transaksi' => $tanggalAkhirBulan,
                    ]);
                    $bungaRecord->refresh();
                }
            }

            // Hitung saldo untuk BKP Umum
            $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka);
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

            // Hitung total untuk BKP Umum
            $totalPenerimaan = $saldoAwal + $saldoAwalTunai
                + $penerimaanDanas->where(function ($p) use ($bulanAngka) {
                    return \Carbon\Carbon::parse($p->tanggal_terima)->month == $bulanAngka;
                })->sum('jumlah_dana')
                + $penarikanTunais->sum('jumlah_penarikan')
                + ($bungaRecord ? $bungaRecord->bunga_bank : 0);

            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuData->sum('total_transaksi_kotor')
                + $bkuData->sum('total_pajak')
                + ($bungaRecord ? $bungaRecord->pajak_bunga_bank : 0);

            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);
            $saldoTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka + 1);

            $danaSekolah = 0;
            $danaBosp = $saldoBank;


            return view('laporan.rekapan-bku', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'months' => $months,
                'penganggaran' => $penganggaran,
                'bulanAngka' => $bulanAngka,
                'penarikanTunais' => $penarikanTunais,
                'setorTunais' => $setorTunais,
                'bkuDataTunai' => $bkuDataTunai,
                'bungaRecord' => $bungaRecord,
                'saldoAwal' => $saldoAwal,
                'saldoAwalTunai' => $saldoAwalTunai,
                // Data untuk BKP Umum
                'penerimaanDanas' => $penerimaanDanas,
                'bkuData' => $bkuData,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'saldoBank' => $saldoBank,
                'saldoTunai' => $saldoTunai,
                'danaSekolah' => $danaSekolah,
                'danaBosp' => $danaBosp,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading rekapan BKU: ' . $e->getMessage());

            return redirect()->route('penatausahaan.penatausahaan')
                ->with('error', 'Gagal memuat halaman rekapan: ' . $e->getMessage());
        }
    }

    /**
     * Get data BKP Bank (digunakan oleh AJAX) - VERSI DIPERBAIKI DENGAN PENERIMAAN DANA
     */
    public function getBkpBankData($tahun, $bulan)
    {
        try {
            Log::info('=== GET BKP BANK DATA DIPANGGIL ===', ['tahun' => $tahun, 'bulan' => $bulan]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
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
     * Get rekapan BKU data via AJAX
     */
    /**
     * Get rekapan BKU data via AJAX - DIPERBAIKI UNTUK BKP UMUM
     */
    public function getRekapanBkuAjax(Request $request)
    {
        try {
            $tahun = $request->get('tahun');
            $bulan = $request->get('bulan');
            $tabType = $request->get('tab_type', 'Bank');

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Di dalam method getRekapanBkuAjax, bagian untuk tab Bank
            if ($tabType === 'Bank') {
                // Data untuk BKP Bank
                $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                    ->whereMonth('tanggal_penarikan', $bulanAngka)
                    ->whereYear('tanggal_penarikan', $tahun)
                    ->orderBy('tanggal_penarikan', 'asc')
                    ->get();

                // TAMBAHKAN: Ambil data penerimaan dana untuk bulan tersebut
                $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                    ->whereMonth('tanggal_terima', $bulanAngka)
                    ->whereYear('tanggal_terima', $tahun)
                    ->orderBy('tanggal_terima', 'asc')
                    ->get();

                $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                    ->whereMonth('tanggal_transaksi', $bulanAngka)
                    ->whereYear('tanggal_transaksi', $tahun)
                    ->where('is_bunga_record', true)
                    ->first();

                // Hitung saldo awal dengan method yang diperbaiki
                $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka);

                // Hitung total untuk summary
                $totalPenerimaanDana = $penerimaanDanas->sum('jumlah_dana');
                $totalPenarikan = $penarikanTunais->sum('jumlah_penarikan');
                $totalBunga = $bungaRecord ? $bungaRecord->bunga_bank : 0;
                $totalPajak = $bungaRecord ? $bungaRecord->pajak_bunga_bank : 0;

                $totalPenerimaan = $saldoAwal + $totalPenerimaanDana + $totalBunga;
                $totalPengeluaran = $totalPenarikan + $totalPajak;
                $currentSaldo = $totalPenerimaan - $totalPengeluaran;

                $html = view('laporan.partials.bkp-bank-table', [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'bulanAngka' => $bulanAngka,
                    'penarikanTunais' => $penarikanTunais,
                    'penerimaanDanas' => $penerimaanDanas, // TAMBAHKAN INI
                    'bungaRecord' => $bungaRecord,
                    'saldoAwal' => $saldoAwal,
                    'totalPenerimaanDana' => $totalPenerimaanDana, // TAMBAHKAN INI
                    'totalPenerimaan' => $totalPenerimaan,
                    'totalPengeluaran' => $totalPengeluaran,
                    'currentSaldo' => $currentSaldo
                ])->render();
            } else if ($tabType === 'Pembantu') {
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
                    'currentSaldo' => $currentSaldo,
                ])->render();
            } else if ($tabType === 'Umum') {
                try {
                    Log::info('=== MEMUAT DATA BKP UMUM ===', ['bulan' => $bulan, 'tahun' => $tahun]);

                    // Data untuk BKP Umum
                    $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                        ->whereMonth('tanggal_penarikan', $bulanAngka)
                        ->whereYear('tanggal_penarikan', $tahun)
                        ->orderBy('tanggal_penarikan', 'asc')
                        ->get();

                    Log::info('Data penarikan tunai ditemukan:', ['count' => $penarikanTunais->count()]);

                    $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran->id)
                        ->whereMonth('tanggal_setor', $bulanAngka)
                        ->whereYear('tanggal_setor', $tahun)
                        ->orderBy('tanggal_setor', 'asc')
                        ->get();

                    Log::info('Data setor tunai ditemukan:', ['count' => $setorTunais->count()]);

                    // Ambil semua data BKU (tunai dan non-tunai) untuk BKP Umum
                    $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                        ->whereMonth('tanggal_transaksi', $bulanAngka)
                        ->whereYear('tanggal_transaksi', $tahun)
                        ->where('is_bunga_record', false)
                        ->with(['kodeKegiatan', 'rekeningBelanja'])
                        ->orderBy('tanggal_transaksi', 'asc')
                        ->get();

                    Log::info('Data BKU ditemukan:', ['count' => $bkuData->count()]);

                    $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                        ->orderBy('tanggal_terima', 'asc')
                        ->get();

                    Log::info('Data penerimaan dana ditemukan:', ['count' => $penerimaanDanas->count()]);

                    $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                        ->whereMonth('tanggal_transaksi', $bulanAngka)
                        ->whereYear('tanggal_transaksi', $tahun)
                        ->where('is_bunga_record', true)
                        ->first();

                    Log::info('Data bunga bank:', ['exists' => !is_null($bungaRecord)]);

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

                    // PERBAIKAN PENTING: Ambil saldo awal dari SALDO AKHIR BKP BANK BULAN SEBELUMNYA
                    $saldoAwal = $this->hitungSaldoAwalBkpUmum($penganggaran->id, $tahun, $bulan);
                    $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

                    Log::info('=== SALDO AWAL BKP UMUM DARI BKP BANK ===', [
                        'bulan' => $bulan,
                        'bulan_angka' => $bulanAngka,
                        'saldo_awal_dari_bkp_bank' => $saldoAwal,
                        'saldo_awal_tunai' => $saldoAwalTunai
                    ]);

                    // Hitung total penerimaan - DIMULAI DARI SALDO AWAL BKP BANK
                    $totalPenerimaan = $saldoAwal + $saldoAwalTunai;

                    // Tambahkan penerimaan dana di bulan ini
                    $penerimaanBulanIni = $penerimaanDanas->filter(function ($penerimaan) use ($bulanAngka) {
                        return \Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka;
                    });
                    $totalPenerimaan += $penerimaanBulanIni->sum('jumlah_dana');

                    // Tambahkan penarikan tunai di bulan ini
                    $totalPenerimaan += $penarikanTunais->sum('jumlah_penarikan');

                    // Tambahkan bunga bank di bulan ini
                    $totalPenerimaan += ($bungaRecord ? $bungaRecord->bunga_bank : 0);

                    // Hitung pajak untuk BKP Umum
                    $pajakPenerimaan = 0;
                    $pajakPengeluaran = 0;
                    $pajakDaerahPenerimaan = 0;
                    $pajakDaerahPengeluaran = 0;

                    foreach ($bkuData as $transaksi) {
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
                        + $bkuData->sum('total_transaksi_kotor')
                        + $pajakPengeluaran + $pajakDaerahPengeluaran;

                    $currentSaldo = $totalPenerimaan - $totalPengeluaran;

                    // Hitung saldo bank dan tunai akhir bulan
                    $saldoBank = $this->hitungSaldoAkhirBkpBank($penganggaran->id, $tahun, $bulan);
                    $saldoTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka + 1);

                    // Asumsi untuk dana sekolah dan BOSP
                    $danaSekolah = 0;
                    $danaBosp = $saldoBank;

                    Log::info('=== DATA READY FOR VIEW ===', [
                        'total_penerimaan' => $totalPenerimaan,
                        'total_pengeluaran' => $totalPengeluaran,
                        'current_saldo' => $currentSaldo
                    ]);

                    $html = view('laporan.partials.bkp-umum-table', [
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'bulanAngka' => $bulanAngka,
                        'penerimaanDanas' => $penerimaanDanas,
                        'penarikanTunais' => $penarikanTunais,
                        'setorTunais' => $setorTunais,
                        'bkuData' => $bkuData,
                        'bungaRecord' => $bungaRecord,
                        'saldoAwal' => $saldoAwal,
                        'saldoAwalTunai' => $saldoAwalTunai,
                        'totalPenerimaan' => $totalPenerimaan,
                        'totalPengeluaran' => $totalPengeluaran,
                        'currentSaldo' => $currentSaldo,
                        'saldoBank' => $saldoBank,
                        'saldoTunai' => $saldoTunai,
                        'danaSekolah' => $danaSekolah,
                        'danaBosp' => $danaBosp,
                    ])->render();

                    Log::info('=== VIEW RENDERED SUCCESSFULLY ===');

                    return response()->json([
                        'success' => true,
                        'html' => $html,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'tab_type' => $tabType
                    ]);
                } catch (\Exception $e) {
                    Log::error('ERROR IN BKP UMUM TAB: ' . $e->getMessage());
                    Log::error('Stack trace: ' . $e->getTraceAsString());

                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                        'debug' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]
                    ], 500);
                }
            }else if ($tabType === 'Pajak') {
                // DATA UNTUK BKP PAJAK - PERBAIKAN UNTUK MULTI PAJAK
                $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                    ->whereMonth('tanggal_transaksi', $bulanAngka)
                    ->whereYear('tanggal_transaksi', $tahun)
                    ->where(function ($query) {
                        $query->where('total_pajak', '>', 0)
                            ->orWhere('total_pajak_daerah', '>', 0);
                    })
                    ->with(['kodeKegiatan', 'rekeningBelanja'])
                    ->orderBy('tanggal_transaksi', 'asc')
                    ->get();

                // Siapkan data untuk tampilan (bisa multiple rows per transaksi)
                $pajakRows = [];
                $runningPenerimaan = 0;
                $runningPengeluaran = 0;
                $currentSaldo = 0;

                // Variabel untuk total
                $totalPpn = 0;
                $totalPph21 = 0;
                $totalPph22 = 0;
                $totalPph23 = 0;
                $totalPb1 = 0;
                $totalPenerimaan = 0;
                $totalPengeluaran = 0;

                foreach ($bkuData as $transaksi) {
                    $pajakName = strtolower($transaksi->pajak ?? '');
                    $pajakDaerahName = strtolower($transaksi->pajak_daerah ?? '');

                    $baseUraian = $transaksi->uraian_opsional ?? $transaksi->uraian ?? '';
                    $hasPajakPusat = $transaksi->total_pajak > 0;
                    $hasPajakDaerah = $transaksi->total_pajak_daerah > 0;

                    // Jika ada pajak pusat, buat baris terpisah
                    if ($hasPajakPusat) {
                        $ppn = 0;
                        $pph21 = 0;
                        $pph22 = 0;
                        $pph23 = 0;

                        // Klasifikasi Pajak Pusat
                        if (strpos($pajakName, 'pph21') !== false || strpos($pajakName, 'pph 21') !== false) {
                            $pph21 = $transaksi->total_pajak;
                            $totalPph21 += $pph21;
                        } elseif (strpos($pajakName, 'pph22') !== false || strpos($pajakName, 'pph 22') !== false) {
                            $pph22 = $transaksi->total_pajak;
                            $totalPph22 += $pph22;
                        } elseif (strpos($pajakName, 'pph23') !== false || strpos($pajakName, 'pph 23') !== false) {
                            $pph23 = $transaksi->total_pajak;
                            $totalPph23 += $pph23;
                        } else {
                            $ppn = $transaksi->total_pajak;
                            $totalPpn += $ppn;
                        }

                        $jumlah = $ppn + $pph21 + $pph22 + $pph23;
                        $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                        $totalPengeluaran += $pengeluaran;

                        // Tentukan uraian untuk pajak pusat
                        $uraianPusat = '';
                        if (!empty($transaksi->ntpn)) {
                            if ($pph21 > 0) {
                                $uraianPusat = 'Setor Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            } elseif ($pph22 > 0) {
                                $uraianPusat = 'Setor Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            } elseif ($pph23 > 0) {
                                $uraianPusat = 'Setor Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            } else {
                                $uraianPusat = 'Setor Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            }
                        } else {
                            if ($pph21 > 0) {
                                $uraianPusat = 'Terima Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            } elseif ($pph22 > 0) {
                                $uraianPusat = 'Terima Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            } elseif ($pph23 > 0) {
                                $uraianPusat = 'Terima Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            } else {
                                $uraianPusat = 'Terima Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                            }
                        }

                        // Update running total
                        if ($jumlah != 0 || $pengeluaran != 0) {
                            $runningPenerimaan += $jumlah;
                            $runningPengeluaran += $pengeluaran;
                            $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                        }

                        $pajakRows[] = [
                            'transaksi' => $transaksi,
                            'uraian' => $uraianPusat,
                            'ppn' => $ppn,
                            'pph21' => $pph21,
                            'pph22' => $pph22,
                            'pph23' => $pph23,
                            'pb1' => 0,
                            'jumlah' => $jumlah,
                            'pengeluaran' => $pengeluaran,
                            'saldo' => $currentSaldo
                        ];
                    }

                    // Jika ada pajak daerah (PB 1), buat baris terpisah
                    if ($hasPajakDaerah) {
                        $pb1 = $transaksi->total_pajak_daerah;
                        $totalPb1 += $pb1;

                        $jumlah = $pb1;
                        $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                        $totalPengeluaran += $pengeluaran;

                        // Tentukan uraian untuk pajak daerah
                        $uraianDaerah = '';
                        if (!empty($transaksi->ntpn)) {
                            $uraianDaerah = 'Setor Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianDaerah = 'Terima Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                        }

                        // Update running total
                        if ($jumlah != 0 || $pengeluaran != 0) {
                            $runningPenerimaan += $jumlah;
                            $runningPengeluaran += $pengeluaran;
                            $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                        }

                        $pajakRows[] = [
                            'transaksi' => $transaksi,
                            'uraian' => $uraianDaerah,
                            'ppn' => 0,
                            'pph21' => 0,
                            'pph22' => 0,
                            'pph23' => 0,
                            'pb1' => $pb1,
                            'jumlah' => $jumlah,
                            'pengeluaran' => $pengeluaran,
                            'saldo' => $currentSaldo
                        ];
                    }
                }

                $totalPenerimaan = $totalPpn + $totalPph21 + $totalPph22 + $totalPph23 + $totalPb1;

                $html = view('laporan.partials.bkp-pajak-table', [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'bulanAngka' => $bulanAngka,
                    'pajakRows' => $pajakRows, // PASTIKAN INI DIKIRIM
                    'totalPph21' => $totalPph21,
                    'totalPph22' => $totalPph22,
                    'totalPph23' => $totalPph23,
                    'totalPb1' => $totalPb1,
                    'totalPpn' => $totalPpn,
                    'totalPenerimaan' => $totalPenerimaan,
                    'totalPengeluaran' => $totalPengeluaran,
                    'currentSaldo' => $currentSaldo,
                ])->render();
            }elseif ($tabType === 'Rob'){
                // LOGIKA BARU UNTUK ROB
                $html = $this->generateRobHtml($penganggaran, $tahun, $bulan, $bulanAngka);
            }elseif ($tabType === 'Reg'){
                // DATA UNTUK BKP REGISTRASI
                $html = $this->generateRegHtml($penganggaran, $tahun, $bulan, $bulanAngka);                
            }else {
                // Data untuk tab lainnya...
                $html = '<div class="text-center py-5"><i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i><p class="text-muted mt-3">Belum ada data lainnya</p></div>';
            }   
                return response()->json([
                'success' => true,
                'html' => $html,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tab_type' => $tabType
            ]);
        } catch (\Exception $e) {
            Log::error('Error get rekapan BKU AJAX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateRegHtml($penganggaran, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            // Ambil total penerimaan dan pengeluaran dari tab Umum
            $dataUmum = $this->getDataFromUmum($penganggaran->id, $tahun, $bulan, $bulanAngka);
            $totalPenerimaan = $dataUmum['totalPenerimaan'];
            $totalPengeluaran = $dataUmum['totalPengeluaran'];
            $saldoBuku = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo kas B dari currentSaldo di tab Pembantu
            $saldoKas = $this->getSaldoKasFromPembantu($penganggaran->id, $tahun, $bulan, $bulanAngka);

            // Hitung saldo bank
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Data uang kertas berdasarkan saldo kas dari Pembantu
            $uangKertas = $this->getDenominasiUangKertas($saldoKas);
            $totalUangKertas = $this->hitungTotalUangKertas($uangKertas);

            // Hitung sisa untuk uang logam
            $sisaUntukLogam = $saldoKas - $totalUangKertas;
            $uangLogam = $this->getDenominasiUangLogam($sisaUntukLogam);
            $totalUangLogam = $this->hitungTotalUangLogam($uangLogam);

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank,
                'uangKertas' => $uangKertas,
                'uangLogam' => $uangLogam,
                'totalUangKertas' => $totalUangKertas,
                'totalUangLogam' => $totalUangLogam,
                'perbedaan' => $saldoBuku - $saldoKas,
                'penjelasanPerbedaan' => 'Masih ada sebagian dana BOS yang belum diambil di rekening bank. Masih ada sisa tunai yang disimpan bendahara.',
                'tanggalPenutupan' => Carbon::create($tahun, $bulanAngka, 1)->endOfMonth()->format('d F Y'),
                'tanggalPenutupanLalu' => '-',
                'namaBendahara' => $sekolah->bendahara ?? 'Dra. MASITAH ABDULLAH',
                'namaKepalaSekolah' => $sekolah->kepala_sekolah ?? 'Dra. MASITAH ABDULLAH',
                'nipBendahara' => $sekolah->nip_bendahara ?? '19690917 200701 2 017',
                'nipKepalaSekolah' => $sekolah->nip_kepala_sekolah ?? '19690917 200701 2 017',
            ];

            return view('laporan.partials.bkp-registrasi-table', $data)->render();
        } catch (\Exception $e) {
            Log::error('Error generate REG HTML: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Helper method untuk generate HTML ROB - DIPERBAIKI DENGAN SISA BULAN SEBELUMNYA
     */
    private function generateRobHtml($penganggaran, $tahun, $bulan, $bulanAngka)
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
                'saldoAwal' => $saldoAwalBulanIni, // GANTI DARI totalPenerimaan MENJADI saldoAwal
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
                return $this->hitungTotalDanaTersedia($penganggaran_id);
            }

            // Hitung total penerimaan dana sampai saat ini
            $totalPenerimaan = $this->hitungTotalDanaTersedia($penganggaran_id);

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

    /**
     * Get nama bulan dari angka (Helper function)
     */
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

    /**
     * Generate PDF BKP Bank - VERSI DIPERBAIKI DENGAN PENERIMAAN DANA
     */
    public function generateBkpBankPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

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
            ];

            $pdf = PDF::loadView('laporan.bkp-bank-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = "BKP_Bank_{$bulan}_{$tahun}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Bank PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF BKP Pembantu Tunai
     */
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
                'tanggalAkhirBulan' => BukuKasUmum::getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => BukuKasUmum::getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => BukuKasUmum::formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => BukuKasUmum::formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => BukuKasUmum::formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y')
            ];

            $pdf = PDF::loadView('laporan.bku-pembantu-tunai-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = "BKP_Pembantu_Tunai_{$bulan}_{$tahun}.pdf";
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Pembantu Tunai PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    // Method untuk mendapatkan data BKP Umum
    public function getBkpUmumData($tahun, $bulan)
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

            // Ambil semua data yang diperlukan
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

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

            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', true)
                ->first();

            // Hitung saldo awal
            $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka);
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

            // Hitung total
            $totalPenerimaan = $saldoAwal + $saldoAwalTunai
                + $penerimaanDanas->where(function ($p) use ($bulanAngka) {
                    return \Carbon\Carbon::parse($p->tanggal_terima)->month == $bulanAngka;
                })->sum('jumlah_dana')
                + $penarikanTunais->sum('jumlah_penarikan')
                + ($bungaRecord ? $bungaRecord->bunga_bank : 0);

            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuData->sum('total_transaksi_kotor')
                + $bkuData->sum('total_pajak')
                + ($bungaRecord ? $bungaRecord->pajak_bunga_bank : 0);

            // Hitung saldo akhir
            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            // Hitung komponen saldo
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);
            $saldoTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Asumsi untuk dana sekolah dan BOSP (bisa disesuaikan)
            $danaSekolah = 0; // Sesuaikan dengan logika bisnis
            $danaBosp = $saldoBank; // Asumsi semua saldo bank adalah dana BOSP

            $html = view('laporan.partials.bkp-umum-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulanAngka' => $bulanAngka,
                'penerimaanDanas' => $penerimaanDanas,
                'penarikanTunais' => $penarikanTunais,
                'setorTunais' => $setorTunais,
                'bkuData' => $bkuData,
                'bungaRecord' => $bungaRecord,
                'saldoAwal' => $saldoAwal,
                'saldoAwalTunai' => $saldoAwalTunai,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'saldoBank' => $saldoBank,
                'saldoTunai' => $saldoTunai,
                'danaSekolah' => $danaSekolah,
                'danaBosp' => $danaBosp,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => [
                    'saldo_awal' => $saldoAwal,
                    'total_penerimaan' => $totalPenerimaan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_akhir' => $currentSaldo,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP Umum data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Umum: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hitung total penerimaan dan pengeluaran untuk BKP Umum dengan cara yang konsisten
     */
    private function hitungTotalBkpUmum($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Data yang sama seperti di getRekapanBkuAjax
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', true)
                ->first();

            // Hitung dengan cara yang sama seperti di blade template
            $totalPenerimaan = 0;
            $totalPengeluaran = 0;

            // Saldo Awal
            $totalPenerimaan += $this->hitungSaldoAwalBkpUmum($penganggaran_id, $tahun, $bulan);

            // Saldo Tunai Awal
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanAngka);
            $totalPenerimaan += $saldoAwalTunai;

            // Penerimaan Dana bulan ini
            $penerimaanBulanIni = $penerimaanDanas->filter(function ($penerimaan) use ($bulanAngka) {
                return \Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka;
            });
            $totalPenerimaan += $penerimaanBulanIni->sum('jumlah_dana');

            // Penarikan Tunai
            $totalPenerimaan += $penarikanTunais->sum('jumlah_penarikan');

            // Setor Tunai
            $totalPengeluaran += $setorTunais->sum('jumlah_setor');

            // Transaksi BKU
            $totalPengeluaran += $bkuData->sum('total_transaksi_kotor');

            // Pajak (dihitung sesuai logika blade)
            foreach ($bkuData as $transaksi) {
                // Pajak Pusat - untuk penerimaan
                if ($transaksi->total_pajak > 0) {
                    $totalPenerimaan += $transaksi->total_pajak;
                    if (!empty($transaksi->ntpn)) {
                        $totalPengeluaran += $transaksi->total_pajak;
                    }
                }

                // Pajak Daerah - untuk penerimaan
                if ($transaksi->total_pajak_daerah > 0) {
                    $totalPenerimaan += $transaksi->total_pajak_daerah;
                    if (!empty($transaksi->ntpn)) {
                        $totalPengeluaran += $transaksi->total_pajak_daerah;
                    }
                }
            }

            // Bunga Bank
            if ($bungaRecord && $bungaRecord->bunga_bank > 0) {
                $totalPenerimaan += $bungaRecord->bunga_bank;
            }

            // Pajak Bunga Bank
            if ($bungaRecord && $bungaRecord->pajak_bunga_bank > 0) {
                $totalPengeluaran += $bungaRecord->pajak_bunga_bank;
            }

            return [
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $totalPenerimaan - $totalPengeluaran
            ];
        } catch (\Exception $e) {
            Log::error('Error hitungTotalBkpUmum: ' . $e->getMessage());
            return [
                'totalPenerimaan' => 0,
                'totalPengeluaran' => 0,
                'currentSaldo' => 0
            ];
        }
    }

    // Method untuk generate PDF BKP Umum
    /**
     * Generate PDF BKP Umum - VERSI DIPERBAIKI
     */
    public function generateBkpUmumPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil semua data yang diperlukan
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran->id)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

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

            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

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

            // Hitung saldo untuk BKP Umum
            $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka);
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

            // Hitung total penerimaan dasar
            $totalPenerimaan = $saldoAwal + $saldoAwalTunai;

            // Tambahkan penerimaan dana di bulan ini
            $penerimaanBulanIni = $penerimaanDanas->filter(function ($penerimaan) use ($bulanAngka) {
                return \Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka;
            });
            $totalPenerimaan += $penerimaanBulanIni->sum('jumlah_dana');

            // Tambahkan penarikan tunai
            $totalPenerimaan += $penarikanTunais->sum('jumlah_penarikan');

            // Tambahkan bunga bank
            $totalPenerimaan += ($bungaRecord ? $bungaRecord->bunga_bank : 0);

            // Hitung pajak untuk BKP Umum (SEMUA dihitung di kedua kolom)
            $pajakPenerimaan = 0;
            $pajakPengeluaran = 0;
            $pajakDaerahPenerimaan = 0;
            $pajakDaerahPengeluaran = 0;

            foreach ($bkuData as $transaksi) {
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

            // Hitung pajak bunga bank - SEMUA dihitung di kedua kolom
            if ($bungaRecord && $bungaRecord->pajak_bunga_bank > 0) {
                $pajakPenerimaan += $bungaRecord->pajak_bunga_bank; // Selalu tambah ke penerimaan
                if (!empty($bungaRecord->ntpn)) {
                    $pajakPengeluaran += $bungaRecord->pajak_bunga_bank; // Hanya tambah ke pengeluaran jika NTPN ada
                }
            }

            // Tambahkan SEMUA pajak ke total
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuData->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran;

            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo bank dan tunai akhir bulan
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);
            $saldoTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Asumsi untuk dana sekolah dan BOSP
            $danaSekolah = 0;
            $danaBosp = $saldoBank;

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'penerimaanDanas' => $penerimaanDanas,
                'penarikanTunais' => $penarikanTunais,
                'setorTunais' => $setorTunais,
                'bkuData' => $bkuData,
                'bungaRecord' => $bungaRecord,
                'saldoAwal' => $saldoAwal,
                'saldoAwalTunai' => $saldoAwalTunai,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'saldoBank' => $saldoBank,
                'saldoTunai' => $saldoTunai,
                'danaSekolah' => $danaSekolah,
                'danaBosp' => $danaBosp,
                'tanggalAkhirBulan' => BukuKasUmum::getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => BukuKasUmum::getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => BukuKasUmum::formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => BukuKasUmum::formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => BukuKasUmum::formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
            ];

            $pdf = PDF::loadView('laporan.bkp-umum-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = "BKP_Umum_{$bulan}_{$tahun}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Umum PDF: ' . $e->getMessage());

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

    /**
     * Hitung saldo akhir BKP Bank untuk bulan tertentu - DIPERBAIKI LAGI
     */
    private function hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulan)
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
     * Hitung saldo awal BKP Umum berdasarkan saldo akhir BKP Bank bulan sebelumnya - DIPERBAIKI
     */
    private function hitungSaldoAwalBkpUmum($penganggaran_id, $tahun, $bulan)
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

            Log::info('=== HITUNG SALDO AWAL BKP UMUM ===', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulan_angka' => $bulanAngka
            ]);

            // Jika bulan Januari, saldo awal adalah 0
            if ($bulanAngka == 1) {
                Log::info('Saldo Awal BKP Umum - Januari = 0');
                return 0;
            }

            // Ambil saldo akhir dari BKP Bank bulan sebelumnya
            $bulanSebelumnyaAngka = $bulanAngka - 1;
            $bulanSebelumnyaNama = array_search($bulanSebelumnyaAngka, $bulanList);

            if (!$bulanSebelumnyaNama) {
                Log::warning('Bulan sebelumnya tidak ditemukan', ['bulan_sebelumnya_angka' => $bulanSebelumnyaAngka]);
                return 0;
            }

            // Hitung saldo akhir BKP Bank bulan sebelumnya
            $saldoAkhirBank = $this->hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulanSebelumnyaNama);

            Log::info('Saldo Awal BKP Umum - Ambil dari BKP Bank bulan sebelumnya:', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan_sekarang' => $bulan,
                'bulan_sebelumnya' => $bulanSebelumnyaNama,
                'saldo_akhir_bank_bulan_sebelumnya' => $saldoAkhirBank,
                'saldo_awal_bkp_umum' => $saldoAkhirBank
            ]);

            return $saldoAkhirBank;
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoAwalBkpUmum: ' . $e->getMessage());
            return 0;
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

    /**
     * Get data BKP Pajak
     */
    public function getBkpPajakData($tahun, $bulan)
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

            // Ambil data transaksi BKU yang memiliki pajak
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where(function ($query) {
                    $query->where('total_pajak', '>', 0)
                        ->orWhere('total_pajak_daerah', '>', 0);
                })
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Hitung total pajak
            $totalPph21 = $bkuData->sum(function ($item) {
                return strpos($item->pajak ?? '', 'PPh21') !== false ? $item->total_pajak : 0;
            });

            $totalPph22 = $bkuData->sum(function ($item) {
                return strpos($item->pajak ?? '', 'PPh22') !== false ? $item->total_pajak : 0;
            });

            $totalPph23 = $bkuData->sum(function ($item) {
                return strpos($item->pajak ?? '', 'PPh23') !== false ? $item->total_pajak : 0;
            });

            $totalPphFinal = $bkuData->sum(function ($item) {
                return strpos($item->pajak ?? '', 'PPh Final') !== false ? $item->total_pajak : 0;
            });

            $totalPpn = $bkuData->sum('total_pajak_daerah');

            $totalPenerimaan = $totalPph21 + $totalPph22 + $totalPph23 + $totalPphFinal + $totalPpn;
            $totalPengeluaran = $bkuData->sum(function ($item) {
                return (!empty($item->ntpn)) ? ($item->total_pajak + $item->total_pajak_daerah) : 0;
            });

            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            $html = view('laporan.partials.bkp-pajak-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulanAngka' => $bulanAngka,
                'bkuData' => $bkuData,
                'totalPph21' => $totalPph21,
                'totalPph22' => $totalPph22,
                'totalPph23' => $totalPph23,
                'totalPphFinal' => $totalPphFinal,
                'totalPpn' => $totalPpn,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => [
                    'total_penerimaan' => $totalPenerimaan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_akhir' => $currentSaldo,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP Pajak data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Pajak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF BKP Pajak - VERSI DIPERBAIKI DENGAN MULTI ROWS
     */
    public function generateBkpPajakPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil data transaksi BKU yang memiliki pajak
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where(function ($query) {
                    $query->where('total_pajak', '>', 0)
                        ->orWhere('total_pajak_daerah', '>', 0);
                })
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Siapkan data untuk tampilan (bisa multiple rows per transaksi)
            $pajakRows = [];
            $runningPenerimaan = 0;
            $runningPengeluaran = 0;
            $currentSaldo = 0;

            // Variabel untuk total
            $totalPpn = 0;
            $totalPph21 = 0;
            $totalPph22 = 0;
            $totalPph23 = 0;
            $totalPb1 = 0;
            $totalPenerimaan = 0;
            $totalPengeluaran = 0;

            foreach ($bkuData as $transaksi) {
                $pajakName = strtolower($transaksi->pajak ?? '');
                $pajakDaerahName = strtolower($transaksi->pajak_daerah ?? '');

                $baseUraian = $transaksi->uraian_opsional ?? $transaksi->uraian ?? '';
                $hasPajakPusat = $transaksi->total_pajak > 0;
                $hasPajakDaerah = $transaksi->total_pajak_daerah > 0;

                // Jika ada pajak pusat, buat baris terpisah
                if ($hasPajakPusat) {
                    $ppn = 0;
                    $pph21 = 0;
                    $pph22 = 0;
                    $pph23 = 0;

                    // Klasifikasi Pajak Pusat
                    if (strpos($pajakName, 'pph21') !== false || strpos($pajakName, 'pph 21') !== false) {
                        $pph21 = $transaksi->total_pajak;
                        $totalPph21 += $pph21;
                    } elseif (strpos($pajakName, 'pph22') !== false || strpos($pajakName, 'pph 22') !== false) {
                        $pph22 = $transaksi->total_pajak;
                        $totalPph22 += $pph22;
                    } elseif (strpos($pajakName, 'pph23') !== false || strpos($pajakName, 'pph 23') !== false) {
                        $pph23 = $transaksi->total_pajak;
                        $totalPph23 += $pph23;
                    } else {
                        $ppn = $transaksi->total_pajak;
                        $totalPpn += $ppn;
                    }

                    $jumlah = $ppn + $pph21 + $pph22 + $pph23;
                    $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                    $totalPengeluaran += $pengeluaran;

                    // Tentukan uraian untuk pajak pusat
                    $uraianPusat = '';
                    if (!empty($transaksi->ntpn)) {
                        if ($pph21 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph22 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph23 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianPusat = 'Setor Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        }
                    } else {
                        if ($pph21 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph22 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph23 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianPusat = 'Terima Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        }
                    }

                    // Update running total
                    if ($jumlah != 0 || $pengeluaran != 0) {
                        $runningPenerimaan += $jumlah;
                        $runningPengeluaran += $pengeluaran;
                        $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }

                    $pajakRows[] = [
                        'transaksi' => $transaksi,
                        'uraian' => $uraianPusat,
                        'ppn' => $ppn,
                        'pph21' => $pph21,
                        'pph22' => $pph22,
                        'pph23' => $pph23,
                        'pb1' => 0,
                        'jumlah' => $jumlah,
                        'pengeluaran' => $pengeluaran,
                        'saldo' => $currentSaldo
                    ];
                }

                // Jika ada pajak daerah (PB 1), buat baris terpisah
                if ($hasPajakDaerah) {
                    $pb1 = $transaksi->total_pajak_daerah;
                    $totalPb1 += $pb1;

                    $jumlah = $pb1;
                    $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                    $totalPengeluaran += $pengeluaran;

                    // Tentukan uraian untuk pajak daerah
                    $uraianDaerah = '';
                    if (!empty($transaksi->ntpn)) {
                        $uraianDaerah = 'Setor Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                    } else {
                        $uraianDaerah = 'Terima Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                    }

                    // Update running total
                    if ($jumlah != 0 || $pengeluaran != 0) {
                        $runningPenerimaan += $jumlah;
                        $runningPengeluaran += $pengeluaran;
                        $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }

                    $pajakRows[] = [
                        'transaksi' => $transaksi,
                        'uraian' => $uraianDaerah,
                        'ppn' => 0,
                        'pph21' => 0,
                        'pph22' => 0,
                        'pph23' => 0,
                        'pb1' => $pb1,
                        'jumlah' => $jumlah,
                        'pengeluaran' => $pengeluaran,
                        'saldo' => $currentSaldo
                    ];
                }
            }

            $totalPenerimaan = $totalPpn + $totalPph21 + $totalPph22 + $totalPph23 + $totalPb1;

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'pajakRows' => $pajakRows, // PASTIKAN INI DIKIRIM
                'totalPph21' => $totalPph21,
                'totalPph22' => $totalPph22,
                'totalPph23' => $totalPph23,
                'totalPb1' => $totalPb1,
                'totalPpn' => $totalPpn,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'tanggalAkhirBulan' => BukuKasUmum::getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => BukuKasUmum::getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => BukuKasUmum::formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => BukuKasUmum::formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => BukuKasUmum::formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
            ];

            $pdf = PDF::loadView('laporan.bkp-pajak-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = "BKP_Pajak_{$bulan}_{$tahun}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Pajak PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
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
                'saldoAwal' => $saldoAwal, // GANTI MENJADI saldoAwal
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
            $sekolah = \App\Models\Sekolah::first();

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

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'saldoAwal' => $saldoAwal, // GANTI MENJADI saldoAwal
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
            ];

            $pdf = PDF::loadView('laporan.bkp-rob-pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = "BKP_ROB_{$bulan}_{$tahun}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP ROB PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get data BKP Registrasi
     */
    public function getBkpRegData($tahun, $bulan)
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

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            // Ambil total penerimaan dan pengeluaran dari tab Umum
            $dataUmum = $this->getDataFromUmum($penganggaran->id, $tahun, $bulan, $bulanAngka);
            $totalPenerimaan = $dataUmum['totalPenerimaan'];
            $totalPengeluaran = $dataUmum['totalPengeluaran'];
            $saldoBuku = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo kas B dari currentSaldo di tab Pembantu
            $saldoKas = $this->getSaldoKasFromPembantu($penganggaran->id, $tahun, $bulan, $bulanAngka);

            // Hitung saldo bank
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Data uang kertas berdasarkan saldo kas dari Pembantu
            $uangKertas = $this->getDenominasiUangKertas($saldoKas);
            $totalUangKertas = $this->hitungTotalUangKertas($uangKertas);

            // Hitung sisa untuk uang logam
            $sisaUntukLogam = $saldoKas - $totalUangKertas;
            $uangLogam = $this->getDenominasiUangLogam($sisaUntukLogam);
            $totalUangLogam = $this->hitungTotalUangLogam($uangLogam);

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank,
                'uangKertas' => $uangKertas,
                'uangLogam' => $uangLogam,
                'totalUangKertas' => $totalUangKertas,
                'totalUangLogam' => $totalUangLogam,
                'perbedaan' => $saldoBuku - $saldoKas,
                'penjelasanPerbedaan' => 'Masih ada sebagian dana BOS yang belum diambil di rekening bank. Masih ada sisa tunai yang disimpan bendahara.',
                'tanggalPenutupan' => Carbon::create($tahun, $bulanAngka, 1)->endOfMonth()->format('d F Y'),
                'tanggalPenutupanLalu' => '-',
                'namaBendahara' => $sekolah->bendahara ?? 'Dra. MASITAH ABDULLAH',
                'namaKepalaSekolah' => $sekolah->kepala_sekolah ?? 'Dra. MASITAH ABDULLAH',
                'nipBendahara' => $sekolah->nip_bendahara ?? '19690917 200701 2 017',
                'nipKepalaSekolah' => $sekolah->nip_kepala_sekolah ?? '19690917 200701 2 017',
            ];

            $html = view('laporan.partials.bkp-registrasi-table', $data)->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP Registrasi data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Registrasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF BKP Registrasi
     */
    public function generateBkpRegPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil total penerimaan dan pengeluaran dari tab Umum
            $dataUmum = $this->getDataFromUmum($penganggaran->id, $tahun, $bulan, $bulanAngka);
            $totalPenerimaan = $dataUmum['totalPenerimaan'];
            $totalPengeluaran = $dataUmum['totalPengeluaran'];
            $saldoBuku = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo kas B dari currentSaldo di tab Pembantu
            $saldoKas = $this->getSaldoKasFromPembantu($penganggaran->id, $tahun, $bulan, $bulanAngka);

            // Hitung saldo bank
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Data uang kertas berdasarkan saldo kas dari Pembantu
            $uangKertas = $this->getDenominasiUangKertas($saldoKas);
            $totalUangKertas = $this->hitungTotalUangKertas($uangKertas);

            // Hitung sisa untuk uang logam
            $sisaUntukLogam = $saldoKas - $totalUangKertas;
            $uangLogam = $this->getDenominasiUangLogam($sisaUntukLogam);
            $totalUangLogam = $this->hitungTotalUangLogam($uangLogam);

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank,
                'uangKertas' => $uangKertas,
                'uangLogam' => $uangLogam,
                'totalUangKertas' => $totalUangKertas,
                'totalUangLogam' => $totalUangLogam,
                'perbedaan' => $saldoBuku - $saldoKas,
                'penjelasanPerbedaan' => 'Masih ada sebagian dana BOS yang belum diambil di rekening bank. Masih ada sisa tunai yang disimpan bendahara.',
                'tanggalPenutupan' => Carbon::create($tahun, $bulanAngka, 1)->endOfMonth()->format('d F Y'),
                'tanggalPenutupanLalu' => '-',
                'namaBendahara' => $sekolah->bendahara ?? 'Dra. MASITAH ABDULLAH',
                'namaKepalaSekolah' => $sekolah->kepala_sekolah ?? 'Dra. MASITAH ABDULLAH',
                'nipBendahara' => $sekolah->nip_bendahara ?? '19690917 200701 2 017',
                'nipKepalaSekolah' => $sekolah->nip_kepala_sekolah ?? '19690917 200701 2 017',
                'tanggalAkhirBulan' => BukuKasUmum::getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => BukuKasUmum::getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => BukuKasUmum::formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => BukuKasUmum::formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => BukuKasUmum::formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
            ];

            $pdf = PDF::loadView('laporan.bkp-registrasi-pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = "BKP_Registrasi_{$bulan}_{$tahun}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Registrasi PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hitung denominasi uang kertas (selalu tampilkan semua denominasi meskipun saldo 0)
     */
    private function getDenominasiUangKertas($saldoKas)
    {
        $denominasi = [
            100000,
            50000,
            20000,
            10000,
            5000,
            2000,
            1000
        ];

        $result = [];
        $sisaSaldo = $saldoKas;

        foreach ($denominasi as $nominal) {
            if ($sisaSaldo >= $nominal) {
                $jumlahLembar = floor($sisaSaldo / $nominal);
                $total = $jumlahLembar * $nominal;
                $result[] = [
                    'denominasi' => $nominal,
                    'lembar' => $jumlahLembar,
                    'jumlah' => $total
                ];
                $sisaSaldo -= $total;
            } else {
                // Tetap tampilkan meskipun 0
                $result[] = [
                    'denominasi' => $nominal,
                    'lembar' => 0,
                    'jumlah' => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Hitung denominasi uang logam (selalu tampilkan semua denominasi meskipun saldo 0)
     */
    private function getDenominasiUangLogam()
    {
        $denominasi = [
            1000,
            500,
            200,
            100
        ];

        $result = [];

        foreach ($denominasi as $nominal) {
            // Selalu tampilkan semua denominasi dengan nilai 0
            $result[] = [
                'denominasi' => $nominal,
                'keping' => 0,
                'jumlah' => 0
            ];
        }

        return $result;
    }

    /**
     * Hitung total dari array uang kertas
     */
    private function hitungTotalUangKertas($uangKertas)
    {
        return array_sum(array_column($uangKertas, 'jumlah'));
    }

    /**
     * Hitung total dari array uang logam
     */
    private function hitungTotalUangLogam($uangLogam)
    {
        return array_sum(array_column($uangLogam, 'jumlah'));
    }

    /**
     * Ambil saldo kas dari currentSaldo di tab Pembantu
     */
    private function getSaldoKasFromPembantu($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Ambil data penarikan tunai untuk bulan tersebut
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuDataTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'tunai')
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Hitung saldo awal tunai (sama seperti di tab Pembantu)
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanAngka);

            // Hitung total untuk BKP Pembantu (sama seperti di tab Pembantu)
            $totalPenerimaan = $saldoAwalTunai + $penarikanTunais->sum('jumlah_penarikan');

            // Hitung pajak untuk pembantu (sama seperti di tab Pembantu)
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

            // Tambahkan SEMUA pajak ke total (sama seperti di tab Pembantu)
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuDataTunai->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran;

            // CurrentSaldo dari tab Pembantu
            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            Log::info('Saldo Kas dari Pembantu', [
                'penganggaran_id' => $penganggaran_id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'saldo_awal_tunai' => $saldoAwalTunai,
                'total_penarikan' => $penarikanTunais->sum('jumlah_penarikan'),
                'total_setor' => $setorTunais->sum('jumlah_setor'),
                'total_belanja_tunai' => $bkuDataTunai->sum('total_transaksi_kotor'),
                'current_saldo' => $currentSaldo
            ]);

            return max(0, $currentSaldo);
        } catch (\Exception $e) {
            Log::error('Error getSaldoKasFromPembantu: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil total penerimaan dan pengeluaran dari tab Umum
     */
    private function getDataFromUmum($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Ambil semua data yang diperlukan (sama seperti di tab Umum)
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
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

            // Hitung saldo untuk BKP Umum (sama seperti di tab Umum)
            $saldoAwal = $this->hitungSaldoBankSebelumBulan($penganggaran_id, $bulanAngka);
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanAngka);

            // Hitung total penerimaan dasar (sama seperti di tab Umum)
            $totalPenerimaan = $saldoAwal + $saldoAwalTunai;

            // Tambahkan penerimaan dana di bulan ini
            $penerimaanBulanIni = $penerimaanDanas->filter(function ($penerimaan) use ($bulanAngka) {
                return \Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka;
            });
            $totalPenerimaan += $penerimaanBulanIni->sum('jumlah_dana');

            // Tambahkan penarikan tunai
            $totalPenerimaan += $penarikanTunais->sum('jumlah_penarikan');

            // Tambahkan bunga bank
            $totalPenerimaan += ($bungaRecord ? $bungaRecord->bunga_bank : 0);

            // Hitung pajak untuk BKP Umum (sama seperti di tab Umum)
            $pajakPenerimaan = 0;
            $pajakPengeluaran = 0;
            $pajakDaerahPenerimaan = 0;
            $pajakDaerahPengeluaran = 0;

            foreach ($bkuData as $transaksi) {
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

            // Hitung pajak bunga bank
            if ($bungaRecord && $bungaRecord->pajak_bunga_bank > 0) {
                $pajakPenerimaan += $bungaRecord->pajak_bunga_bank;
                if (!empty($bungaRecord->ntpn)) {
                    $pajakPengeluaran += $bungaRecord->pajak_bunga_bank;
                }
            }

            // Tambahkan SEMUA pajak ke total (sama seperti di tab Umum)
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuData->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran;

            Log::info('Data dari Tab Umum', [
                'penganggaran_id' => $penganggaran_id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_penerimaan' => $totalPenerimaan,
                'total_pengeluaran' => $totalPengeluaran,
                'saldo_awal' => $saldoAwal,
                'saldo_awal_tunai' => $saldoAwalTunai,
                'penerimaan_dana_bulan_ini' => $penerimaanBulanIni->sum('jumlah_dana'),
                'penarikan_tunai' => $penarikanTunais->sum('jumlah_penarikan'),
                'bunga_bank' => $bungaRecord ? $bungaRecord->bunga_bank : 0
            ]);

            return [
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran
            ];
        } catch (\Exception $e) {
            Log::error('Error getDataFromUmum: ' . $e->getMessage());
            return [
                'totalPenerimaan' => 0,
                'totalPengeluaran' => 0
            ];
        }
    }

}
