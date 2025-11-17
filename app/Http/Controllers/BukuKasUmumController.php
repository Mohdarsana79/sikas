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
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\BukuKasService;


class BukuKasUmumController extends Controller
{
    protected $bukuKasService;
    protected $beritaAcaraController;
    protected $registrasiPenutupanKasController;
    protected $realisasiController;

    public function __construct(BukuKasService $bukuKasService, BeritaAcaraPenutupanController $beritaAcaraController, RegistrasiPenutupanKasController $registrasiPenutupanKasController, RekapitulasiRealisasiController $realisasiController)
    {
        $this->bukuKasService = $bukuKasService;
        $this->beritaAcaraController = $beritaAcaraController;
        $this->registrasiPenutupanKasController = $registrasiPenutupanKasController;
        $this->realisasiController = $realisasiController;
    }

    // Ganti semua pemanggilan method yang dipindah ke service
    private function hitungSaldoTunaiNonTunai($penganggaran_id)
    {
        return $this->bukuKasService->hitungSaldoTunaiNonTunai($penganggaran_id);
    }

    private function hitungTotalDanaTersedia($penganggaran_id)
    {
        return $this->bukuKasService->hitungTotalDanaTersedia($penganggaran_id);
    }

    private function hitungSaldoBankSebelumBulan($penganggaran_id, $bulanTarget)
    {
        return $this->bukuKasService->hitungSaldoBankSebelumBulan($penganggaran_id, $bulanTarget);
    }

    private function hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanTarget)
    {
        return $this->bukuKasService->hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanTarget);
    }

    // Helper function untuk konversi bulan
    private function convertBulanToNumber($bulan)
    {
        return $this->bukuKasService->convertBulanToNumber($bulan);
    }

    private function convertNumberToBulan($angka)
    {
        return $this->bukuKasService->convertNumberToBulan($angka);
    }

    // Tambahkan method-method ini ke BukuKasUmumController
    private function hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulan)
    {
        return $this->bukuKasService->hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulan);
    }

    private function hitungSaldoAwalBkpUmum($penganggaran_id, $tahun, $bulan)
    {
        return $this->bukuKasService->hitungSaldoAwalBkpUmum($penganggaran_id, $tahun, $bulan);
    }

    private function getSaldoKasFromPembantu($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        return $this->bukuKasService->getSaldoKasFromPembantu($penganggaran_id, $tahun, $bulan, $bulanAngka);
    }

    private function getDataFromBkpUmumCalculation($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        return $this->bukuKasService->getDataFromBkpUmumCalculation($penganggaran_id, $tahun, $bulan, $bulanAngka);
    }

    // Tambahkan method-method ini ke BukuKasUmumController
    private function getDenominasiUangKertas($saldoKas)
    {
        return $this->bukuKasService->getDenominasiUangKertas($saldoKas);
    }

    private function getDenominasiUangLogam($sisaUntukLogam = 0)
    {
        return $this->bukuKasService->getDenominasiUangLogam($sisaUntukLogam);
    }

    private function hitungTotalUangKertas($uangKertas)
    {
        return $this->bukuKasService->hitungTotalUangKertas($uangKertas);
    }

    private function hitungTotalUangLogam($uangLogam)
    {
        return $this->bukuKasService->hitungTotalUangLogam($uangLogam);
    }



    private function getBeritaAcaraData($penganggaran, $tahun, $bulan, $bulanAngka)
    {
        try {
            Log::info('=== MENGAMBIL DATA BERITA ACARA DARI CONTROLLER ===', [
                'penganggaran_id' => $penganggaran->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulan_angka' => $bulanAngka
            ]);

            // Gunakan method yang sudah ada di BeritaAcaraController
            return $this->beritaAcaraController->getDataForBeritaAcara(
                $penganggaran->id,
                $tahun,
                $bulan,
                $bulanAngka
            );
        } catch (\Exception $e) {
            Log::error('Error getBeritaAcaraData from controller: ' . $e->getMessage());

            // Return data default jika error
            return [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'penganggaran' => $penganggaran,
                'sekolah' => \App\Models\Sekolah::first(),
                'uangKertas' => [],
                'uangLogam' => [],
                'totalUangKertas' => 0,
                'totalUangLogam' => 0,
                'totalUangKertasLogam' => 0,
                'saldoBank' => 0,
                'totalKas' => 0,
                'saldoBuku' => 0,
                'perbedaan' => 0,
                'penjelasanPerbedaan' => 'Data tidak tersedia',
                'formatTanggalAkhirBulan' => '',
                'namaHariAkhirBulan' => '',
                'tanggalPemeriksaan' => '',
                'namaBendahara' => '-',
                'namaKepalaSekolah' => '-',
                'nipBendahara' => '-',
                'nipKepalaSekolah' => '-',
            ];
        }
    }

    private function getRegistrasiPenutupanData($tahun, $bulan)
    {
        try {
            Log::info('===MENGAMBIL DATA REGISTRASI PENUTUPAN KAS DARI CONTROLLER===', [
                'bulan' => $bulan,
                'tahun' => $tahun
            ]);

            return $this->registrasiPenutupanKasController->getBkpRegData($tahun, $bulan);
        } catch (\Exception $e) {
            Log::error('Error getBkpRegData from controller: ' . $e->getMessage());
            // kembalikan default jika error
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dari controller getBkpRegData: ' . $e->getMessage(),
            ], 500);
        }
    }

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
            Log::error('Error loading audit page: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memuat halaman audit: ' . $e->getMessage());
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
            Log::error('Error dalam getAuditData: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan audit: ' . $e->getMessage(),
                'audit_data' => null,
                'debug' => [
                    'penganggaran_id' => $penganggaran_id,
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    // Tambahkan method search di BukuKasUmumController
    public function search(Request $request, $tahun, $bulan)
    {
        try {
            $searchTerm = $request->get('search');

            if (empty($searchTerm)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata pencarian tidak boleh kosong'
                ], 400);
            }

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Query pencarian dengan multiple criteria
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('id_transaksi', 'ILIKE', "%{$searchTerm}%")
                        ->orWhereHas('kodeKegiatan', function ($q) use ($searchTerm) {
                            $q->where('program', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('sub_program', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('uraian', 'ILIKE', "%{$searchTerm}%");
                        })
                        ->orWhereHas('rekeningBelanja', function ($q) use ($searchTerm) {
                            $q->where('rincian_objek', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('kode_rekening', 'ILIKE', "%{$searchTerm}%");
                        })
                        ->orWhere('uraian', 'ILIKE', "%{$searchTerm}%")
                        ->orWhere('uraian_opsional', 'ILIKE', "%{$searchTerm}%");
                })
                ->with(['kodeKegiatan', 'rekeningBelanja', 'uraianDetails'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->orderBy('id_transaksi', 'asc')
                ->get();

            // Format data untuk response
            $formattedData = $bkuData->map(function ($bku) {
                return [
                    'id' => $bku->id,
                    'id_transaksi' => $bku->id_transaksi,
                    'tanggal' => $bku->tanggal_transaksi->format('d/m/Y'),
                    'kegiatan' => $bku->kodeKegiatan->sub_program ?? '-',
                    'rekening_belanja' => $bku->rekeningBelanja->rincian_objek ?? '-',
                    'uraian_opsional' => $bku->uraian_opsional,
                    'uraian' => $bku->uraian,
                    'jenis_transaksi' => ucfirst($bku->jenis_transaksi),
                    'anggaran' => number_format($bku->anggaran, 0, ',', '.'),
                    'dibelanjakan' => number_format($bku->total_transaksi_kotor, 0, ',', '.'),
                    'pajak_info' => $this->getPajakInfo($bku),
                    'actions' => $this->getActionButtons($bku)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'total' => $bkuData->count(),
                'search_term' => $searchTerm
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching BKU data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper method untuk info pajak
    private function getPajakInfo($bku)
    {
        if ($bku->total_pajak > 0) {
            if ($bku->ntpn) {
                return [
                    'amount' => number_format($bku->total_pajak, 0, ',', '.'),
                    'status' => 'reported',
                    'badge_class' => 'badge-sudah-lapor',
                    'text_class' => 'text-dark',
                    'icon' => 'bi-check-circle-fill',
                    'message' => 'Sudah dilaporkan'
                ];
            } else {
                return [
                    'amount' => number_format($bku->total_pajak, 0, ',', '.'),
                    'status' => 'unreported',
                    'badge_class' => 'badge-belum-lapor',
                    'text_class' => 'text-danger fw-bold',
                    'icon' => 'bi-exclamation-triangle-fill',
                    'message' => 'Belum dilaporkan'
                ];
            }
        }

        return [
            'amount' => '0',
            'status' => 'none',
            'text_class' => 'text-muted',
            'message' => 'Rp 0'
        ];
    }

    // Helper method untuk action buttons
    private function getActionButtons($bku)
    {
        $buttons = '
    <div class="dropdown dropstart">
        <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button" 
                data-bs-toggle="dropdown" aria-expanded="false" 
                style="border: none; background: none;">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item btn-view-detail" href="#" 
                   data-bku-id="' . $bku->id . '">
                    <i class="bi bi-eye me-2"></i>Lihat Detail
                </a>
            </li>';

        if ($bku->total_pajak > 0) {
            $buttons .= '
            <li>
                <a href="#" class="dropdown-item btn-lapor-pajak" 
                   data-bs-toggle="modal" data-bs-target="#laporPajakModal"
                   data-id="' . $bku->id . '" 
                   data-pajak="' . $bku->total_pajak . '" 
                   data-ntpn="' . ($bku->ntpn ?? '') . '">
                    <i class="bi bi-' . ($bku->ntpn ? 'check-circle' : 'info-circle') . ' me-2"></i>
                    ' . ($bku->ntpn ? 'Edit Lapor Pajak' : 'Lapor Pajak') . '
                </a>
            </li>';
        }

        $buttons .= '
            <li>
                <a class="dropdown-item text-danger btn-hapus-individual" 
                   href="' . route('bku.destroy', $bku->id) . '" 
                   data-id="' . $bku->id . '">
                    <i class="bi bi-trash me-2"></i>Hapus
                </a>
            </li>
        </ul>
    </div>';

        return $buttons;
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
            Log::error('Error dalam fixData: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbaiki data: ' . $e->getMessage(),
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
                $recommendations[] = 'Ditemukan selisih signifikan: Rp ' . number_format($auditResult['saldo']['selisih'], 0, ',', '.') . '. Periksa data BKU dan penerimaan dana.';
            }

            if ($bkuInternalProblem->count() > 0) {
                $recommendations[] = 'Ditemukan ' . $bkuInternalProblem->count() . ' transaksi BKU dengan ketidaksesuaian internal.';
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
            Log::error('Error dalam auditData: ' . $e->getMessage());

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
            Log::error('Error debug perhitungan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error debug: ' . $e->getMessage(),
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
            Log::error('Error debug kegiatan rekening: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error debug: ' . $e->getMessage(),
            ], 500);
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
            session(['saldo_tunai_' . $penganggaran_id => max(0, $saldoTunaiBaru)]);
        }

        $totalDanaTersediaBaru = $saldo['total_dana_tersedia'] - $jumlah_belanja;
        session(['total_dana_tersedia_' . $penganggaran_id => max(0, $totalDanaTersediaBaru)]);

        return $saldo;
    }

    // Update method showByBulan
    public function showByBulan($tahun, $bulan)
    {
        // Cari data penganggaran berdasarkan tahun
        $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

        if (! $penganggaran) {
            return redirect()->route('penatausahaan.penatausahaan')
                ->with('error', 'Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan');
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
            Log::error('Error menghitung anggaran bulan ini: ' . $e->getMessage());

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
                'formatted_total' => 'Rp ' . number_format($totalDana, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dana tersedia: ' . $e->getMessage(),
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

                Log::info('Data RKAS untuk bulan ' . $bulanItem . ':', ['count' => $rkasData->count()]);

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
                    'message' => 'Tidak ada data RKAS untuk bulan ' . implode(', ', $bulanUntukDiambil),
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
            Log::error('Error getting kegiatan dan rekening: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
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

                Log::info('Perhitungan uraian: ' . $uraianName, [
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
                    'uraian_dapat_digunakan' => collect($uraianGrouped)->filter(fn($u) => $u['dapat_digunakan'])->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting uraian: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data uraian: ' . $e->getMessage(),
            ], 500);
        }
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
                'uraian_items.*.satuan' => 'required|string',
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
                    'message' => 'Tanggal nota harus dalam bulan ' . $bulanTarget . ' tahun ' . $tahunAnggaran,
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
                $uraianText = 'Lunas Bayar ' . $rekeningBelanja->rincian_objek;

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
                        'satuan' => $item['satuan'],
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
            Log::error('Error menyimpan BKU: ' . $e->getMessage());
            Log::error('Request data: ', $request->all());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
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
            Log::error('Error menghitung total dibelanjakan: ' . $e->getMessage());

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
            Log::error('Error menghitung total dibelanjakan sampai bulan ini: ' . $e->getMessage());

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
            Log::error('Error menghitung anggaran belum dibelanjakan: ' . $e->getMessage());

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
                'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dibelanjakan: ' . $e->getMessage(),
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
                'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dibelanjakan: ' . $e->getMessage(),
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
                'message' => 'Berhasil menghapus ' . $deletedCount . ' data BKU untuk bulan ' . $bulan . ' ' . $tahun,
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menghapus semua data BKU: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
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
            Log::error('Error deleting BKU: ' . $e->getMessage());

            // Cek jika request AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus transaksi: ' . $e->getMessage(),
                ], 500);
            }

            // Redirect untuk request biasa
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
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
                    'id_transaksi' => 'BUNGA-BANK-' . $bulan . '-' . $tahun,
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
                        'id_transaksi' => 'BUNGA-BANK-' . $bulan . '-' . $tahun,
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
            Log::error('Error menutup BKU: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup BKU: ' . $e->getMessage(),
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
            Log::error('Error membuka BKU: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka BKU: ' . $e->getMessage(),
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
            Log::error('Error update bunga bank: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data bunga bank: ' . $e->getMessage(),
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
            Log::error('Error getting last nota number: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil nomor nota terakhir: ' . $e->getMessage(),
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

            return $prefix . $formattedNumber;
        }

        // Jika tidak ada angka, tambahkan -001
        return $lastNotaNumber . '-001';
    }

    // Method untuk debug perhitungan volume - VERSI DIPERBAIKI
    // public function debugVolumePerhitungan($tahun, $bulan, $rekeningId, Request $request)
    // {
    //     try {
    //         $kegiatanId = $request->query('kegiatan_id');

    //         Log::info('=== DEBUG VOLUME PERHITUNGAN ===', [
    //             'tahun' => $tahun,
    //             'bulan' => $bulan,
    //             'rekeningId' => $rekeningId,
    //             'kegiatanId' => $kegiatanId,
    //         ]);

    //         $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

    //         if (! $penganggaran) {
    //             return response()->json(['success' => false, 'message' => 'Penganggaran tidak ditemukan']);
    //         }

    //         // Tentukan model
    //         $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
    //         $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

    //         $bulanAngkaList = [
    //             'Januari' => 1,
    //             'Februari' => 2,
    //             'Maret' => 3,
    //             'April' => 4,
    //             'Mei' => 5,
    //             'Juni' => 6,
    //             'Juli' => 7,
    //             'Agustus' => 8,
    //             'September' => 9,
    //             'Oktober' => 10,
    //             'November' => 11,
    //             'Desember' => 12,
    //         ];

    //         $bulanTargetNumber = $bulanAngkaList[$bulan] ?? 1;

    //         // Ambil data RKAS
    //         $startBulan = $isTahap1 ? 1 : 7;
    //         $allRkasData = collect();

    //         for ($i = $startBulan; $i <= $bulanTargetNumber; $i++) {
    //             $bulanNama = array_search($i, $bulanAngkaList);
    //             if (! $bulanNama) {
    //                 continue;
    //             }

    //             $rkasData = $model::where('penganggaran_id', $penganggaran->id)
    //                 ->where('bulan', $bulanNama)
    //                 ->where('kode_rekening_id', $rekeningId)
    //                 ->where('kode_id', $kegiatanId)
    //                 ->get();

    //             $rkasData->each(function ($item) use ($bulanNama) {
    //                 $item->bulan_asal = $bulanNama;
    //             });

    //             $allRkasData = $allRkasData->merge($rkasData);
    //         }

    //         // Hitung volume dari RKAS per uraian
    //         $volumeRkasPerUraian = [];
    //         foreach ($allRkasData as $item) {
    //             $uraian = $item->uraian; // PERBAIKAN: Definisikan variabel
    //             if (! isset($volumeRkasPerUraian[$uraian])) {
    //                 $volumeRkasPerUraian[$uraian] = 0;
    //             }
    //             $volumeRkasPerUraian[$uraian] += $item->jumlah;
    //         }

    //         // Hitung volume yang sudah dibelanjakan per uraian
    //         $volumeSudahDibelanjakanPerUraian = [];
    //         $uraianNames = $allRkasData->pluck('uraian')->unique();

    //         foreach ($uraianNames as $uraianName) {
    //             $volume = BukuKasUmumUraianDetail::where('penganggaran_id', $penganggaran->id)
    //                 ->where('kode_rekening_id', $rekeningId)
    //                 ->where('kode_kegiatan_id', $kegiatanId)
    //                 ->where('uraian', 'LIKE', '%' . $uraianName . '%')
    //                 ->whereHas('bukuKasUmum', function ($query) use ($isTahap1, $bulanTargetNumber) {
    //                     if ($isTahap1) {
    //                         $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 1 AND ?', [$bulanTargetNumber]);
    //                     } else {
    //                         $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 7 AND ?', [$bulanTargetNumber]);
    //                     }
    //                 })
    //                 ->sum('volume');

    //             $volumeSudahDibelanjakanPerUraian[$uraianName] = $volume;
    //         }

    //         // Hitung sisa volume per uraian - PERBAIKAN: Gunakan cara yang benar
    //         $sisaVolumePerUraian = [];
    //         foreach ($volumeRkasPerUraian as $uraian => $volumeRkas) {
    //             $volumeSudah = $volumeSudahDibelanjakanPerUraian[$uraian] ?? 0;
    //             $sisaVolumePerUraian[$uraian] = max(0, $volumeRkas - $volumeSudah);
    //         }

    //         // Detail data BKU yang sudah dibelanjakan
    //         $detailBku = BukuKasUmumUraianDetail::where('penganggaran_id', $penganggaran->id)
    //             ->where('kode_rekening_id', $rekeningId)
    //             ->where('kode_kegiatan_id', $kegiatanId)
    //             ->whereHas('bukuKasUmum', function ($query) use ($isTahap1, $bulanTargetNumber) {
    //                 if ($isTahap1) {
    //                     $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 1 AND ?', [$bulanTargetNumber]);
    //                 } else {
    //                     $query->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) BETWEEN 7 AND ?', [$bulanTargetNumber]);
    //                 }
    //             })
    //             ->with('bukuKasUmum')
    //             ->get()
    //             ->map(function ($item) {
    //                 return [
    //                     'uraian' => $item->uraian,
    //                     'volume' => $item->volume,
    //                     'bulan' => $item->bukuKasUmum->tanggal_transaksi->format('F'),
    //                     'tanggal' => $item->bukuKasUmum->tanggal_transaksi->format('Y-m-d'),
    //                 ];
    //             });

    //         return response()->json([
    //             'success' => true,
    //             'debug_info' => [
    //                 'penganggaran_id' => $penganggaran->id,
    //                 'periode' => $isTahap1 ? 'Tahap 1' : 'Tahap 2',
    //                 'bulan_target' => $bulan,
    //                 'bulan_diambil' => array_slice($bulanAngkaList, $startBulan - 1, $bulanTargetNumber - $startBulan + 1, true),
    //             ],
    //             'volume_rkas' => $volumeRkasPerUraian,
    //             'volume_sudah_dibelanjakan' => $volumeSudahDibelanjakanPerUraian,
    //             'sisa_volume' => $sisaVolumePerUraian, // PERBAIKAN: Gunakan array yang sudah dihitung
    //             'detail_bku' => $detailBku,
    //             'total_rkas' => array_sum($volumeRkasPerUraian),
    //             'total_sudah_dibelanjakan' => array_sum($volumeSudahDibelanjakanPerUraian),
    //             'total_sisa' => array_sum($sisaVolumePerUraian),
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error debug volume: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // Tambahkan method public ini di BukuKasUmumController
    public function getSaldoBank($penganggaran_id, $bulan = null)
    {
        try {
            $bulanAngka = $bulan ? $this->convertBulanToNumber($bulan) : 1;

            return $this->hitungSaldoBankSebelumBulan($penganggaran_id, $bulanAngka);
        } catch (\Exception $e) {
            Log::error('Error getSaldoBank: ' . $e->getMessage());

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

            // Data untuk BKP Umum Terima Tunai
            $terimaTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
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

            // AMBIL DATA BERITA ACARA LANGSUNG DARI CONTROLLER
            $beritaAcaraData = $this->getBeritaAcaraData($penganggaran, $tahun, $bulan, $bulanAngka);
            $registrasiPenutupanKasData = $this->getRegistrasiPenutupanData($tahun, $bulan);

            $danaSekolah = 0;
            $danaBosp = $saldoBank;

            return view('laporan.rekapan-bku', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'months' => $months,
                'penganggaran' => $penganggaran,
                'bulanAngka' => $bulanAngka,
                'penarikanTunais' => $penarikanTunais,
                'terimaTunais' => $terimaTunais,
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
                'beritaAcaraData' => $beritaAcaraData,
                'registrasiPenutupanKasData' => $registrasiPenutupanKasData,
                // Data untuk realisasi tab
                'jenisLaporan' => 'bulanan', // default
                'tahap' => 'tahap1' // default
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading rekapan BKU: ' . $e->getMessage());

            return redirect()->route('penatausahaan.penatausahaan')
                ->with('error', 'Gagal memuat halaman rekapan: ' . $e->getMessage());
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
                $pembantuController = new BukuKasPembantuTunaiController();
                $html = $pembantuController->getBkpPembantuData($tahun, $bulan);

                return $html;
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

                    $terimaTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
                        ->whereMonth('tanggal_penarikan', $bulanAngka)
                        ->whereYear('tanggal_penarikan', $tahun)
                        ->orderBy('tanggal_penarikan', 'asc')
                        ->get();

                    Log::info('Data terima tunai ditemukan:', ['count' => $terimaTunais->count()]);

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
                        'terimaTunais' => $terimaTunais,
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
            } else if ($tabType === 'Pajak') {
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
            } elseif ($tabType === 'Rob') {
                // LOGIKA BARU UNTUK ROB
                $bukuRobController = new BukuRobController($this->bukuKasService);

                $html = $bukuRobController->generateRobHtml($penganggaran, $tahun, $bulan, $bulanAngka);
            } elseif ($tabType === 'Reg') {
                // DATA UNTUK BKP REGISTRASI
                $html = $this->generateRegHtml($penganggaran, $tahun, $bulan, $bulanAngka);
            } elseif ($tabType === 'Ba') {
                // GUNAKAN DATA LANGSUNG DARI BERITA ACARA CONTROLLER
                $beritaAcaraData = $this->getBeritaAcaraData($penganggaran, $tahun, $bulan, $bulanAngka);

                $html = view('laporan.partials.ba-pemeriksaan-kas-table', $beritaAcaraData)->render();

                return response()->json([
                    'success' => true,
                    'html' => $html
                ]);
            } else if ($tabType === 'Realisasi') {
                try {
                    $tahun = $request->get('tahun');
                    $bulan = $request->get('bulan');

                    $realisasiController = new RekapitulasiRealisasiController();
                    $response = $realisasiController->getRealisasiForRekapanBku($tahun, $bulan);

                    if ($response->getData()->success) {
                        $html = $response->getData()->html;
                    } else {
                        $html = '<div class="alert alert-danger">Gagal memuat data realisasi: ' . $response->getData()->message . '</div>';
                    }
                } catch (\Exception $e) {
                    Log::error('Error loading realisasi data: ' . $e->getMessage());
                    $html = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            } else {
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

            // PERBAIKAN: Gunakan data langsung dari perhitungan BKP Umum
            $dataUmum = $this->getDataFromBkpUmumCalculation($penganggaran->id, $tahun, $bulan, $bulanAngka);
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

            $saldoAkhirBuku = $totalUangKertas + $totalUangLogam + $saldoBank;

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
                'saldoAkhirBuku' => $saldoAkhirBuku,
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

            $terimaTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
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
                'terimaTunais' => $terimaTunais,
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
     * Generate PDF BKP Umum - VERSI DIPERBAIKI DENGAN PERHITUNGAN SALDO YANG BENAR
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

            $terimaTunais = PenarikanTunai::where('penganggaran_id', $penganggaran->id)
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
            $saldoAwal = $this->hitungSaldoAwalBkpUmum($penganggaran->id, $tahun, $bulan);
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka);

            Log::info('=== DATA UNTUK PDF BKP UMUM ===', [
                'penganggaran_id' => $penganggaran->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'saldo_awal' => $saldoAwal,
                'saldo_awal_tunai' => $saldoAwalTunai,
                'jumlah_penerimaan_dana' => $penerimaanDanas->count(),
                'jumlah_penarikan' => $penarikanTunais->count(),
                'jumlah_setor' => $setorTunais->count(),
                'jumlah_bku' => $bkuData->count(),
                'bunga_record_exists' => !is_null($bungaRecord)
            ]);

            // Siapkan data rows untuk perhitungan saldo yang benar
            $rowsData = $this->siapkanDataRowsBkpUmum(
                $penganggaran->id,
                $tahun,
                $bulan,
                $bulanAngka,
                $saldoAwal,
                $saldoAwalTunai,
                $penerimaanDanas,
                $penarikanTunais,
                $terimaTunais,
                $setorTunais,
                $bkuData,
                $bungaRecord
            );

            // Hitung total akhir dari rows data
            $totalPenerimaan = 0;
            $totalPengeluaran = 0;
            $currentSaldo = 0;

            foreach ($rowsData as $row) {
                $totalPenerimaan += $row['penerimaan'];
                $totalPengeluaran += $row['pengeluaran'];
            }
            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo bank dan tunai akhir bulan
            $saldoBank = $this->hitungSaldoAkhirBkpBank($penganggaran->id, $tahun, $bulan);
            $saldoTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Asumsi untuk dana sekolah dan BOSP
            $danaSekolah = 0;
            $danaBosp = $saldoBank;

            $printSettings = [
                'ukuran_kertas' => request()
                    ->input('ukuran_kertas', 'A4'),
                'orientasi' => request()->input('orientasi', 'landscape'),
                'font_size' => request()->input('font_size', '10pt')
            ];

            Log::info('Generate ROB', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'print_settings' => $printSettings,
            ]);

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'rowsData' => $rowsData, // Data rows yang sudah disiapkan
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
                'printSettings' => $printSettings
            ];

            $pdf = PDF::loadView('laporan.bkp-umum-pdf', $data);

            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            $filename = "BKP_Umum_{$bulan}_{$tahun}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Umum PDF: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Siapkan data rows untuk BKP Umum dengan struktur yang konsisten
     */
    private function siapkanDataRowsBkpUmum($penganggaran_id, $tahun, $bulan, $bulanAngka, $saldoAwal, $saldoAwalTunai, $penerimaanDanas, $penarikanTunais, $terimaTunais, $setorTunais, $bkuData, $bungaRecord)
    {
        $rowsData = [];

        // BARIS 1: Saldo Awal
        $rowsData[] = [
            'tanggal' => '1/' . $bulanAngka . '/' . $tahun,
            'kode_rekening' => '-',
            'no_bukti' => '-',
            'uraian' => 'Saldo Awal bulan ' . $bulan,
            'penerimaan' => $saldoAwal,
            'pengeluaran' => 0,
            'is_saldo_awal' => true
        ];

        // BARIS 2: Saldo Kas Tunai (jika ada)
        if ($saldoAwalTunai > 0) {
            $rowsData[] = [
                'tanggal' => '01-' . $bulanAngka . '-' . $tahun,
                'kode_rekening' => '-',
                'no_bukti' => '-',
                'uraian' => 'Saldo Kas Tunai',
                'penerimaan' => $saldoAwalTunai,
                'pengeluaran' => 0
            ];
        }

        // DATA PENERIMAAN DANA
        foreach ($penerimaanDanas as $penerimaan) {
            if (\Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka) {
                $rowsData[] = [
                    'tanggal' => \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d-m-Y'),
                    'kode_rekening' => '299',
                    'no_bukti' => '-',
                    'uraian' => 'Terima Dana ' . $penerimaan->sumber_dana . ' T.A ' . $tahun,
                    'penerimaan' => $penerimaan->jumlah_dana,
                    'pengeluaran' => 0
                ];
            }
        }

        // DATA PENARIKAN TUNAI
        foreach ($penarikanTunais as $penarikan) {
            $rowsData[] = [
                'tanggal' => \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y'),
                'kode_rekening' => '-',
                'no_bukti' => '-',
                'uraian' => 'Penarikan Tunai ' . $penerimaan->sumber_dana . ' T.A ' . $tahun,
                'penerimaan' => 0,
                'pengeluaran' => $penarikan->jumlah_penarikan
            ];
        }

        foreach ($terimaTunais as $terima) {
            $rowsData[] = [
                'tanggal' => \Carbon\Carbon::parse($terima->tanggal_penarikan)->format('d/m/Y'),
                'kode_rekening' => '-',
                'no_bukti' => '-',
                'uraian' => 'Terima Tunai ' . $penerimaan->sumber_dana . ' T.A ' . $tahun,
                'penerimaan' => $terima->jumlah_penarikan,
                'pengeluaran' => 0
            ];
        }

        // DATA SETOR TUNAI
        foreach ($setorTunais as $setor) {
            $rowsData[] = [
                'tanggal' => \Carbon\Carbon::parse($setor->tanggal_setor)->format('d-m-Y'),
                'kode_rekening' => '-',
                'no_bukti' => '-',
                'uraian' => 'Setor Tunai',
                'penerimaan' => 0,
                'pengeluaran' => $setor->jumlah_setor
            ];
        }

        // DATA TRANSAKSI BKU
        foreach ($bkuData as $transaksi) {
            // Baris transaksi utama
            $rowsData[] = [
                'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y'),
                'kode_rekening' => $transaksi->rekeningBelanja->kode_rekening ?? '-',
                'no_bukti' => $transaksi->id_transaksi,
                'uraian' => $transaksi->uraian_opsional ?? $transaksi->uraian,
                'penerimaan' => 0,
                'pengeluaran' => $transaksi->total_transaksi_kotor
            ];

            // Baris Pajak Pusat jika ada
            if ($transaksi->total_pajak > 0) {
                if (empty($transaksi->ntpn)) {
                    // Jika NTPN belum ada (Terima Pajak) - di kolom PENERIMAAN
                    $rowsData[] = [
                        'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y'),
                        'kode_rekening' => '-',
                        'no_bukti' => $transaksi->kode_masa_pajak ?? '-',
                        'uraian' => 'Terima Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . ($transaksi->uraian_opsional ?? $transaksi->uraian),
                        'penerimaan' => $transaksi->total_pajak,
                        'pengeluaran' => 0
                    ];
                } else {
                    // Jika NTPN sudah ada (Setor Pajak) - tampil di KEDUA KOLOM
                    $rowsData[] = [
                        'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y'),
                        'kode_rekening' => '-',
                        'no_bukti' => $transaksi->kode_masa_pajak,
                        'uraian' => 'Setor Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . ($transaksi->uraian_opsional ?? $transaksi->uraian),
                        'penerimaan' => $transaksi->total_pajak,
                        'pengeluaran' => $transaksi->total_pajak
                    ];
                }
            }

            // Baris Pajak Daerah jika ada
            if ($transaksi->total_pajak_daerah > 0) {
                if (empty($transaksi->ntpn)) {
                    // Jika NTPN belum ada (Terima Pajak Daerah) - di kolom PENERIMAAN
                    $rowsData[] = [
                        'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y'),
                        'kode_rekening' => '-',
                        'no_bukti' => '-',
                        'uraian' => 'Terima Pajak Daerah ' . ($transaksi->pajak_daerah ?? '') . ' ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . ($transaksi->uraian_opsional ?? $transaksi->uraian),
                        'penerimaan' => $transaksi->total_pajak_daerah,
                        'pengeluaran' => 0
                    ];
                } else {
                    // Jika NTPN sudah ada (Setor Pajak Daerah) - tampil di KEDUA KOLOM
                    $rowsData[] = [
                        'tanggal' => \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y'),
                        'kode_rekening' => '-',
                        'no_bukti' => '-',
                        'uraian' => 'Setor Pajak Daerah ' . ($transaksi->pajak_daerah ?? '') . ' ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . ($transaksi->uraian_opsional ?? $transaksi->uraian),
                        'penerimaan' => $transaksi->total_pajak_daerah,
                        'pengeluaran' => $transaksi->total_pajak_daerah
                    ];
                }
            }
        }

        // BUNGA BANK
        if ($bungaRecord && $bungaRecord->bunga_bank > 0) {
            $rowsData[] = [
                'tanggal' => \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y'),
                'kode_rekening' => '299',
                'no_bukti' => '-',
                'uraian' => 'Bunga Bank',
                'penerimaan' => $bungaRecord->bunga_bank,
                'pengeluaran' => 0
            ];
        }

        // PAJAK BUNGA BANK
        if ($bungaRecord && $bungaRecord->pajak_bunga_bank > 0) {
            $rowsData[] = [
                'tanggal' => \Carbon\Carbon::parse($bungaRecord->tanggal_transaksi)->format('d-m-Y'),
                'kode_rekening' => '199',
                'no_bukti' => '-',
                'uraian' => 'Pajak Bunga Bank',
                'penerimaan' => 0,
                'pengeluaran' => $bungaRecord->pajak_bunga_bank
            ];
        }

        return $rowsData;
    }
}
