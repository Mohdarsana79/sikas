<?php

namespace App\Http\Controllers;

use App\Models\KodeKegiatan;
use App\Models\Penganggaran;
use App\Models\RekeningBelanja;
use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\Sekolah;
use App\Services\RekamanPerubahanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RkasPerubahanController extends Controller
{
    /**
     * Memeriksa apakah sudah dilakukan perubahan untuk tahun tertentu
     */
    public function checkStatusPerubahan(Request $request)
    {
        try {
            $tahun = $request->input('tahun');

            Log::info('Check status perubahan untuk tahun: ' . $tahun);

            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                Log::warning('Penganggaran tidak ditemukan untuk tahun: ' . $tahun);

                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan untuk tahun ' . $tahun,
                ], 404);
            }

            $perubahanExists = RkasPerubahan::where('penganggaran_id', $penganggaran->id)->exists();

            Log::info('Status perubahan untuk tahun ' . $tahun . ': ' . ($perubahanExists ? 'ADA' : 'TIDAK ADA'));

            return response()->json([
                'success' => true,
                'has_perubahan' => $perubahanExists,
                'tahun' => $tahun,
                'penganggaran_id' => $penganggaran->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checkStatusPerubahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memeriksa status perubahan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menyalin data dari RKAS asli ke RKAS Perubahan.
     */
    public function salinDariRkas(Request $request)
    {
        // Set header untuk response JSON
        header('Content-Type: application/json');

        $request->validate([
            'tahun_anggaran' => 'required|integer',
        ]);

        try {
            $tahun = $request->tahun_anggaran;
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan.',
                ], 404);
            }

            // Periksa apakah data perubahan untuk tahun ini sudah ada
            $perubahanExists = RkasPerubahan::where('penganggaran_id', $penganggaran->id)->exists();

            if ($perubahanExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon Maaf Anda Telah Melakukan Perubahan, RKAS Perubahan hanya dapat dilakukan sekali, Terima Kasih Atas Antusias Anda',
                ]);
            }

            // Ambil semua data RKAS asli
            $rkasAsli = Rkas::where('penganggaran_id', $penganggaran->id)->get();

            if ($rkasAsli->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data RKAS yang dapat disalin untuk tahun ' . $tahun,
                ]);
            }

            // Mulai transaksi database
            DB::beginTransaction();

            $dataToInsert = [];
            foreach ($rkasAsli as $item) {
                $dataToInsert[] = [
                    'penganggaran_id' => $item->penganggaran_id,
                    'kode_id' => $item->kode_id,
                    'kode_rekening_id' => $item->kode_rekening_id,
                    'uraian' => $item->uraian,
                    'harga_satuan' => $item->harga_satuan,
                    'bulan' => $item->bulan,
                    'jumlah' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'rkas_id' => $item->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Salin data ke tabel rkas_perubahans
            RkasPerubahan::insert($dataToInsert);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data RKAS berhasil disalin ke RKAS Perubahan.',
                'redirect' => route('rkas-perubahan.index', ['tahun' => $tahun]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menyalin data RKAS ke Perubahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data perubahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan halaman utama RKAS Perubahan.
     */
    public function index(Request $request)
    {
        try {
            $kodeKegiatans = KodeKegiatan::all();
            $rekeningBelanjas = RekeningBelanja::all();

            $tahun = $request->input('tahun');
            if (! $tahun) {
                // Coba ambil tahun terakhir dari penganggaran jika tidak ada di request
                $latestPenganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
                if ($latestPenganggaran) {
                    $tahun = $latestPenganggaran->tahun_anggaran;
                } else {
                    // Jika tidak ada data penganggaran sama sekali
                    return view('rkas-perubahan.rkas-perubahan-kosong')->with('message', 'Belum ada data penganggaran yang tersedia.');
                }
            }

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            $sekolah = Sekolah::first();

            // Ambil data untuk grafik
            $grafikData = $this->getGrafikData($penganggaran->id);

            // Ambil data yang sudah disalin ke tabel RkasPerubahan
            $rkasData = collect($this->getAllRkasPerubahanDataGroupedByMonth($penganggaran->id));

            $totalBudget = RkasPerubahan::where('penganggaran_id', $penganggaran->id)->sum(DB::raw('jumlah * harga_satuan'));
            $totalTahap1 = RkasPerubahan::getTotalTahap1($penganggaran->id);
            $totalTahap2 = RkasPerubahan::getTotalTahap2($penganggaran->id);

            $paguAnggaranTahap1 = $penganggaran->pagu_anggaran * 0.5;
            $paguAnggaranTahap2 = $penganggaran->pagu_anggaran * 0.5;

            return view('rkas-perubahan.rkas-perubahan', compact(
                'kodeKegiatans',
                'rekeningBelanjas',
                'rkasData',
                'totalBudget',
                'penganggaran',
                'totalTahap1',
                'totalTahap2',
                'paguAnggaranTahap1',
                'paguAnggaranTahap2',
                'tahun',
                'grafikData',
                'sekolah'
            ));
        } catch (\Exception $e) {
            Log::error('Error di RkasPerubahanController@index: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memuat data RKAS Perubahan.');
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->get('search', '');
            $bulan = $request->get('bulan', '');

            Log::info('RKAS Perubahan Search called', [
                'search_term' => $searchTerm,
                'bulan' => $bulan
            ]);

            if (empty($searchTerm)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata pencarian tidak boleh kosong'
                ], 400);
            }

            // Query dasar
            $query = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where(function ($q) use ($searchTerm) {
                    $q->where('uraian', 'ILIKE', "%{$searchTerm}%")
                        ->orWhere('satuan', 'ILIKE', "%{$searchTerm}%")
                        ->orWhereHas('kodeKegiatan', function ($q2) use ($searchTerm) {
                            $q2->where('kode', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('program', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('sub_program', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('uraian', 'ILIKE', "%{$searchTerm}%");
                        })
                        ->orWhereHas('rekeningBelanja', function ($q2) use ($searchTerm) {
                            $q2->where('kode_rekening', 'ILIKE', "%{$searchTerm}%")
                                ->orWhere('rincian_objek', 'ILIKE', "%{$searchTerm}%");
                        });
                });

            // Filter berdasarkan bulan jika ada parameter bulan
            if (!empty($bulan) && $bulan !== 'all') {
                $bulanFormatted = ucfirst($bulan);
                $query->where('bulan', $bulanFormatted);
            }

            $rkasData = $query->orderBy('bulan')
                ->orderBy('kode_id')
                ->get();

            $formattedData = $rkasData->map(function ($item, $index) {
                // Hitung dianggarkan dan dibelanjakan (sesuaikan dengan logika bisnis Anda)
                $dianggarkan = $item->jumlah; // atau field lain yang sesuai
                $dibelanjakan = 0; // Sesuaikan dengan logika realisasi belanja

                return [
                    'id' => $item->id,
                    'index' => $index + 1,
                    'program' => $item->kodeKegiatan->program ?? '-',
                    'sub_program' => $item->kodeKegiatan->sub_program ?? '-',
                    'rincian_objek' => $item->rekeningBelanja->rincian_objek ?? '-',
                    'uraian' => $item->uraian,
                    'dianggarkan' => $dianggarkan,
                    'dibelanjakan' => 'Rp ' . number_format($dibelanjakan, 0, ',', '.'),
                    'satuan' => $item->satuan,
                    'harga_satuan' => 'Rp ' . number_format($item->harga_satuan, 0, ',', '.'),
                    'total' => 'Rp ' . number_format($item->jumlah * $item->harga_satuan, 0, ',', '.'),
                    'actions' => $this->getRkasActionButtons($item)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'total' => $rkasData->count(),
                'search_term' => $searchTerm,
                'month_filter' => $bulan
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching RKAS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data RKAS'
            ], 500);
        }
    }

    /**
     * Generate action buttons for RKAS - Sesuai dengan blade template
     */
    private function getRkasActionButtons($item): string
    {
        $program = $item->kodeKegiatan ? $item->kodeKegiatan->program : '-';
        $subProgram = $item->kodeKegiatan ? $item->kodeKegiatan->sub_program : '-';
        $rekeningDisplay = $item->rekeningBelanja ? $item->rekeningBelanja->kode_rekening . ' - ' . $item->rekeningBelanja->rincian_objek : '-';

        return '
        <div class="dropdown" style="position: relative; z-index: 1050;">
            <button
                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                type="button" id="actionDropdown' . $item->id . '"
                data-bs-toggle="dropdown" aria-expanded="false"
                style="border: 1px solid #dee2e6; background: white; color: #6c757d; font-size: 8pt; padding: 4px 8px; min-width: 40px;">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0"
                aria-labelledby="actionDropdown' . $item->id . '"
                style="z-index: 1060; min-width: 120px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;">
                <li>
                    <a class="dropdown-item d-flex align-items-center"
                        href="#"
                        onclick="showDetailModal(' . $item->id . ')"
                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                        <i class="bi bi-eye me-2 text-primary"></i>Detail
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center sisipkan-btn" href="#" 
                        data-kode-id="' . $item->kode_id . '"
                        data-program="' . htmlspecialchars($program) . '"
                        data-kegiatan="' . htmlspecialchars($subProgram) . '"
                        data-rekening-id="' . $item->kode_rekening_id . '"
                        data-rekening-display="' . htmlspecialchars($rekeningDisplay) . '"
                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                        <i class="bi bi-archive-fill me-2 text-warning"></i>Sisipkan
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center"
                        href="#"
                        onclick="showEditModal(' . $item->id . ')"
                        style="font-size: 8pt; padding: 8px 12px; transition: all 0.2s ease;">
                        <i class="bi bi-pencil me-2 text-warning"></i>Edit
                    </a>
                </li>
            </ul>
        </div>
    ';
    }

    public function store(Request $request)
    {
        $lockedMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        // Validasi bulan yang dikunci
        $bulanArray = $request->bulan;
        foreach ($bulanArray as $bulan) {
            if (in_array($bulan, $lockedMonths)) {
                return back()->withErrors(['bulan' => "Tidak dapat menambah data untuk bulan {$bulan} karena bulan tersebut terkunci."]);
            }
        }

        try {
            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string',
                'harga_satuan' => 'nullable|numeric|min:0',
                'bulan' => 'required|array',
                'bulan.*' => 'required|string|in:Juli,Agustus,September,Oktober,November,Desember',
                'jumlah' => 'nullable|array',
                'jumlah.*' => 'nullable|integer|min:1',
                'satuan' => 'nullable|array',
                'satuan.*' => 'nullable|string',
            ]);

            // Get penganggaran based on selected year
            $tahun = $request->input('tahun_anggaran', date('Y'));
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return back()->with('error', 'Data penganggaran untuk tahun ' . $tahun . ' belum tersedia.');
            }

            DB::beginTransaction();

            $bulanArray = $request->bulan;
            $jumlahArray = $request->jumlah;
            $satuanArray = $request->satuan;

            // Check for duplicate months
            if (count($bulanArray) !== count(array_unique($bulanArray))) {
                return back()->withErrors(['bulan' => 'Tidak boleh ada bulan yang sama dalam satu kegiatan.']);
            }

            $createdRecords = []; // Simpan semua record yang dibuat

            // Create RKAS entries for each month
            for ($i = 0; $i < count($bulanArray); $i++) {
                // Check if combination already exists
                $exists = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                    ->where('kode_id', $request->kode_id)
                    ->where('kode_rekening_id', $request->kode_rekening_id)
                    ->where('bulan', $bulanArray[$i])
                    ->where('uraian', $request->uraian)
                    ->exists();

                if ($exists) {
                    DB::rollBack();

                    return back()->withErrors(['bulan' => "Data untuk bulan {$bulanArray[$i]} sudah ada dengan kegiatan dan rekening yang sama."]);
                }

                $rkasPerubahan = RkasPerubahan::create([
                    'penganggaran_id' => $penganggaran->id,
                    'kode_id' => $request->kode_id,
                    'kode_rekening_id' => $request->kode_rekening_id,
                    'uraian' => $request->uraian,
                    'harga_satuan' => $request->harga_satuan,
                    'bulan' => $bulanArray[$i],
                    'jumlah' => $jumlahArray[$i],
                    'satuan' => $satuanArray[$i],
                ]);

                $createdRecords[] = $rkasPerubahan; // Simpan record yang dibuat
            }

            // Catat penambahan data untuk SEMUA record yang dibuat
            foreach ($createdRecords as $record) {
                RekamanPerubahanService::catatPerubahan(
                    $record->id,
                    'create',
                    [
                        'kode_kegiatan' => $record->kodeKegiatan->kode ?? '-',
                        'program' => $record->kodeKegiatan->program ?? '-',
                        'sub_program' => $record->kodeKegiatan->sub_program ?? '-',
                        'kode_rekening' => $record->rekeningBelanja->kode_rekening ?? '-',
                        'rincian_objek' => $record->rekeningBelanja->rincian_objek ?? '-',
                        'uraian' => $record->uraian,
                        'jumlah' => $record->jumlah,
                        'satuan' => $record->satuan,
                        'harga_satuan' => $record->harga_satuan,
                        'bulan' => $record->bulan,
                        'total' => $record->jumlah * $record->harga_satuan,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('rkas-perubahan.index', ['tahun' => $tahun])->with('success', 'Data RKAS Perubahan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing RKAS Perubahan: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data:' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            Log::info('🔧 [DETAIL DEBUG] Fetching ALL monthly data for detail, ID: ' . $id);

            // Dapatkan data utama berdasarkan ID dengan select spesifik
            $mainRkas = RkasPerubahan::select(['id', 'penganggaran_id', 'kode_id', 'kode_rekening_id', 'uraian', 'harga_satuan'])
                ->with([
                    'kodeKegiatan' => function ($query) {
                        $query->select(['id', 'kode', 'program', 'sub_program', 'uraian']);
                    },
                    'rekeningBelanja' => function ($query) {
                        $query->select(['id', 'kode_rekening', 'rincian_objek']);
                    }
                ])
                ->find($id);

            if (! $mainRkas) {
                Log::warning('❌ [DETAIL DEBUG] Main data not found for ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Data Rkas Perubahan tidak ditemukan.',
                ], 400);
            }

            // Dapatkan Semua Data Dengan Kode_id, Kode_rekening_id, dan uraian yang sama
            // Hanya ambil kolom yang diperlukan
            $allRkasData = RkasPerubahan::select(['id', 'bulan', 'jumlah', 'satuan', 'harga_satuan'])
                ->where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->where('harga_satuan', $mainRkas->harga_satuan)
                ->get();

            Log::info('🔧 [DETAIL DEBUG] Found ' . $allRkasData->count() . ' monthly records');

            // format data untuk semua bulan
            $bulanData = [];
            $totalAnggaran = 0;

            foreach ($allRkasData as $rkas) {
                $totalPerBulan = $rkas->jumlah * $rkas->harga_satuan;
                $bulanData[] = [
                    'bulan' => $rkas->bulan,
                    'jumlah' => $rkas->jumlah,
                    'satuan' => $rkas->satuan,
                    'total' => $totalPerBulan,
                ];
                $totalAnggaran += $totalPerBulan;
            }

            // Data utama untuk form
            $data = [
                'id' => $mainRkas->id,
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'program_kegiatan' => $mainRkas->kodeKegiatan->program ?? '-',
                'kegiatan' => $mainRkas->kodeKegiatan->sub_program ?? '-',
                'rekening_belanja' => $mainRkas->rekeningBelanja->rincian_objek ?? '-',
                'uraian' => $mainRkas->uraian,
                'harga_satuan' => 'Rp ' . number_format($mainRkas->harga_satuan, 0, ',', '.'),
                'harga_satuan_raw' => $mainRkas->harga_satuan,
                'total_anggaran' => 'Rp ' . number_format($totalAnggaran, 0, ',', '.'),

                // DATA SEMUA BULAN
                'bulan_data' => $bulanData,

                // Data tambahan untuk select2
                'kode_kegiatan_data' => [
                    'kode' => $mainRkas->kodeKegiatan->kode ?? '',
                    'program' => $mainRkas->kodeKegiatan->program ?? '',
                    'sub_program' => $mainRkas->kodeKegiatan->sub_program ?? '',
                    'uraian' => $mainRkas->kodeKegiatan->uraian ?? '',
                ],
                'rekening_belanja_data' => [
                    'kode_rekening' => $mainRkas->rekeningBelanja->kode_rekening ?? '',
                    'rincian_objek' => $mainRkas->rekeningBelanja->rincian_objek ?? '',
                ],
            ];

            Log::info('🔧 [DETAIL DEBUG] Sending data with ' . count($bulanData) . ' months');

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [DETAIL DEBUG] Error in show method: ' . $e->getMessage());
            Log::error('❌ [DETAIL DEBUG] Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan form untuk mengedit data RKAS Perubahan (SEMUA BULAN).
     */
    public function edit($id)
    {
        try {
            Log::info('🔧 [EDIT DEBUG] Fetching ALL monthly data for edit, ID: ' . $id);

            // Dapatkan data utama berdasarkan ID
            $mainRkas = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

            if (!$mainRkas) {
                Log::warning('❌ [EDIT DEBUG] Main data not found for ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Data RKAS Perubahan tidak ditemukan.',
                ], 404);
            }

            // Dapatkan SEMUA data dengan kode_id, kode_rekening_id, dan uraian yang sama
            $allRkasData = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->where('harga_satuan', $mainRkas->harga_satuan)
                ->get();

            Log::info('🔧 [EDIT DEBUG] Found ' . $allRkasData->count() . ' monthly records for:', [
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'uraian' => $mainRkas->uraian
            ]);

            // Format data untuk semua bulan
            $bulanData = [];
            $totalAnggaran = 0;

            foreach ($allRkasData as $rkas) {
                $bulanData[] = [
                    'bulan' => $rkas->bulan,
                    'jumlah' => $rkas->jumlah,
                    'satuan' => $rkas->satuan,
                    'total' => $rkas->jumlah * $rkas->harga_satuan
                ];
                $totalAnggaran += $rkas->jumlah * $rkas->harga_satuan;
            }

            // Data utama untuk form
            $data = [
                'id' => $mainRkas->id,
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'program_kegiatan' => $mainRkas->kodeKegiatan->program ?? '-',
                'kegiatan' => $mainRkas->kodeKegiatan->sub_program ?? '-',
                'rekening_belanja' => $mainRkas->rekeningBelanja->rincian_objek ?? '-',
                'uraian' => $mainRkas->uraian,
                'harga_satuan' => 'Rp ' . number_format($mainRkas->harga_satuan, 0, ',', '.'),
                'harga_satuan_raw' => $mainRkas->harga_satuan,
                'total_anggaran' => 'Rp ' . number_format($totalAnggaran, 0, ',', '.'),

                // DATA SEMUA BULAN
                'bulan_data' => $bulanData,

                // INFORMASI BULAN TERKUNCI UNTUK FRONTEND SAJA
                'locked_months' => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'], // Hanya untuk UI
                'allowed_months' => ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'], // Hanya untuk UI

                // Data tambahan untuk select2
                'kode_kegiatan_data' => [
                    'kode' => $mainRkas->kodeKegiatan->kode ?? '',
                    'program' => $mainRkas->kodeKegiatan->program ?? '',
                    'sub_program' => $mainRkas->kodeKegiatan->sub_program ?? '',
                    'uraian' => $mainRkas->kodeKegiatan->uraian ?? '',
                ],
                'rekening_belanja_data' => [
                    'kode_rekening' => $mainRkas->rekeningBelanja->kode_rekening ?? '',
                    'rincian_objek' => $mainRkas->rekeningBelanja->rincian_objek ?? '',
                ]
            ];

            Log::info('🔧 [EDIT DEBUG] Sending data with ' . count($bulanData) . ' months');

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [EDIT DEBUG] Error in edit method: ' . $e->getMessage());
            Log::error('❌ [EDIT DEBUG] Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk edit: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $allowedMonths = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        try {
            Log::info('🔧 [UPDATE DEBUG] Starting update process for ID: ' . $id);
            Log::info('🔧 [UPDATE DEBUG] Full request data:', $request->all());

            // DEBUG: Log detailed array information
            Log::info('🔧 [UPDATE DEBUG] Array details:', [
                'bulan_count' => count($request->bulan ?? []),
                'jumlah_count' => count($request->jumlah ?? []),
                'satuan_count' => count($request->satuan ?? []),
                'bulan_values' => $request->bulan,
                'jumlah_values' => $request->jumlah,
                'satuan_values' => $request->satuan,
                'kode_id' => $request->kode_id,
                'kode_rekening_id' => $request->kode_rekening_id,
                'uraian' => $request->uraian,
                'harga_satuan' => $request->harga_satuan
            ]);

            // PERBAIKAN: Validasi konsistensi array - hanya untuk data yang ada
            if (
                count($request->bulan ?? []) !== count($request->jumlah ?? []) ||
                count($request->bulan ?? []) !== count($request->satuan ?? [])
            ) {

                Log::error('❌ [UPDATE DEBUG] Array inconsistency detected', [
                    'bulan_count' => count($request->bulan ?? []),
                    'jumlah_count' => count($request->jumlah ?? []),
                    'satuan_count' => count($request->satuan ?? [])
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak konsisten: jumlah bulan, jumlah, dan satuan harus sama',
                    'errors' => [
                        'bulan' => ['Data tidak konsisten: jumlah bulan (' . count($request->bulan ?? []) . '), jumlah (' . count($request->jumlah ?? []) . '), dan satuan (' . count($request->satuan ?? []) . ') tidak sama']
                    ]
                ], 422);
            }

            // VALIDASI MANUAL
            $errors = [];

            // Validasi required fields
            if (!$request->kode_id) $errors['kode_id'] = ['Kegiatan harus dipilih'];
            if (!$request->kode_rekening_id) $errors['kode_rekening_id'] = ['Rekening Belanja harus dipilih'];
            if (!$request->uraian) $errors['uraian'] = ['Uraian harus diisi'];
            if (!$request->harga_satuan || $request->harga_satuan <= 0) $errors['harga_satuan'] = ['Harga satuan harus lebih dari 0'];

            // Validasi array bulan - untuk semua bulan
            $validBulan = [];
            $validJumlah = [];
            $validSatuan = [];

            if ($request->bulan && is_array($request->bulan) && count($request->bulan) > 0) {
                foreach ($request->bulan as $index => $bulan) {
                    // PERBAIKAN: Skip jika bulan kosong (tidak dipilih)
                    if (empty($bulan)) {
                        Log::info('🔧 [UPDATE DEBUG] Skipping empty bulan at index: ' . $index);
                        continue;
                    }

                    // PERBAIKAN: Pastikan index untuk jumlah dan satuan ada
                    if (!isset($request->jumlah[$index])) {
                        Log::error('❌ [UPDATE DEBUG] Missing jumlah for index: ' . $index);
                        $errors["jumlah.$index"] = ["Jumlah untuk bulan $bulan harus diisi"];
                        continue;
                    }

                    if ($request->jumlah[$index] <= 0) {
                        Log::error('❌ [UPDATE DEBUG] Invalid jumlah for index: ' . $index . ': ' . $request->jumlah[$index]);
                        $errors["jumlah.$index"] = ["Jumlah untuk bulan $bulan harus lebih dari 0"];
                        continue;
                    }

                    if (!isset($request->satuan[$index])) {
                        Log::error('❌ [UPDATE DEBUG] Missing satuan for index: ' . $index);
                        $errors["satuan.$index"] = ["Satuan untuk bulan $bulan harus diisi"];
                        continue;
                    }

                    if (empty(trim($request->satuan[$index]))) {
                        Log::error('❌ [UPDATE DEBUG] Empty satuan for index: ' . $index);
                        $errors["satuan.$index"] = ["Satuan untuk bulan $bulan harus diisi"];
                        continue;
                    }

                    // Tambahkan ke array valid
                    $validBulan[] = $bulan;
                    $validJumlah[] = $request->jumlah[$index];
                    $validSatuan[] = $request->satuan[$index];

                    Log::info('🔧 [UPDATE DEBUG] Added valid data for bulan: ' . $bulan, [
                        'index' => $index,
                        'jumlah' => $request->jumlah[$index],
                        'satuan' => $request->satuan[$index]
                    ]);
                }
            }

            // Validasi duplikasi bulan (untuk semua bulan)
            if (count($validBulan) !== count(array_unique($validBulan))) {
                $errors['bulan'] = ['Tidak boleh ada bulan yang sama dalam satu kegiatan.'];
                Log::error('❌ [UPDATE DEBUG] Duplicate months detected:', $validBulan);
            }

            // Jika ada error validasi
            if (!empty($errors)) {
                Log::error('❌ [UPDATE DEBUG] Validation errors:', $errors);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $errors,
                ], 422);
            }

            // Dapatkan data utama
            $mainRkas = RkasPerubahan::find($id);
            if (!$mainRkas) {
                Log::error('❌ [UPDATE DEBUG] Data not found for ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Data RKAS Perubahan tidak ditemukan.',
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Simpan data sebelum diubah untuk rekaman perubahan
                $oldData = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                    ->where('penganggaran_id', $mainRkas->penganggaran_id)
                    ->where('kode_id', $mainRkas->kode_id)
                    ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                    ->where('uraian', $mainRkas->uraian)
                    ->get();

                $dataSebelum = [];
                foreach ($oldData as $item) {
                    $dataSebelum[] = [
                        'kode_kegiatan' => $item->kodeKegiatan->kode ?? '-',
                        'program' => $item->kodeKegiatan->program ?? '-',
                        'sub_program' => $item->kodeKegiatan->sub_program ?? '-',
                        'kode_rekening' => $item->rekeningBelanja->kode_rekening ?? '-',
                        'rincian_objek' => $item->rekeningBelanja->rincian_objek ?? '-',
                        'uraian' => $item->uraian,
                        'jumlah' => $item->jumlah,
                        'satuan' => $item->satuan,
                        'harga_satuan' => $item->harga_satuan,
                        'bulan' => $item->bulan,
                        'total' => $item->jumlah * $item->harga_satuan,
                    ];
                }

                Log::info('🔧 [UPDATE DEBUG] Processing update for months:', $validBulan);
                Log::info('🔧 [UPDATE DEBUG] Valid data arrays:', [
                    'validBulan' => $validBulan,
                    'validJumlah' => $validJumlah,
                    'validSatuan' => $validSatuan,
                    'validCount' => count($validBulan)
                ]);

                // HAPUS SEMUA DATA dengan kriteria yang sama
                $deletedCount = RkasPerubahan::where('penganggaran_id', $mainRkas->penganggaran_id)
                    ->where('kode_id', $mainRkas->kode_id)
                    ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                    ->where('uraian', $mainRkas->uraian)
                    ->delete();

                Log::info('🔧 [UPDATE DEBUG] Deleted ' . $deletedCount . ' records');

                // BUAT DATA BARU UNTUK SEMUA BULAN YANG MASIH ADA
                $createdRecords = [];

                Log::info('🔧 [UPDATE DEBUG] Creating new records for months:', $validBulan);

                for ($i = 0; $i < count($validBulan); $i++) {
                    $newRkas = RkasPerubahan::create([
                        'penganggaran_id' => $mainRkas->penganggaran_id,
                        'kode_id' => $request->kode_id,
                        'kode_rekening_id' => $request->kode_rekening_id,
                        'uraian' => $request->uraian,
                        'harga_satuan' => $request->harga_satuan,
                        'bulan' => $validBulan[$i],
                        'jumlah' => $validJumlah[$i],
                        'satuan' => $validSatuan[$i],
                        'rkas_id' => $mainRkas->rkas_id,
                    ]);

                    $createdRecords[] = $newRkas;
                    Log::info('🔧 [UPDATE DEBUG] Created record for bulan: ' . $validBulan[$i], [
                        'jumlah' => $validJumlah[$i],
                        'satuan' => $validSatuan[$i],
                        'harga_satuan' => $request->harga_satuan
                    ]);
                }

                // Catat perubahan untuk rekaman
                foreach ($createdRecords as $record) {
                    RekamanPerubahanService::catatPerubahan(
                        $record->id,
                        'update',
                        [
                            'kode_kegiatan' => $record->kodeKegiatan->kode ?? '-',
                            'program' => $record->kodeKegiatan->program ?? '-',
                            'sub_program' => $record->kodeKegiatan->sub_program ?? '-',
                            'kode_rekening' => $record->rekeningBelanja->kode_rekening ?? '-',
                            'rincian_objek' => $record->rekeningBelanja->rincian_objek ?? '-',
                            'uraian' => $record->uraian,
                            'jumlah' => $record->jumlah,
                            'satuan' => $record->satuan,
                            'harga_satuan' => $record->harga_satuan,
                            'bulan' => $record->bulan,
                            'total' => $record->jumlah * $record->harga_satuan,
                        ],
                        $dataSebelum
                    );
                }

                DB::commit();

                Log::info('🔧 [UPDATE DEBUG] Update completed successfully. Created ' . count($createdRecords) . ' records');

                return response()->json([
                    'success' => true,
                    'message' => 'Data RKAS Perubahan berhasil diupdate untuk ' . count($createdRecords) . ' bulan.',
                    'data_created' => count($createdRecords),
                    'redirect' => route('rkas-perubahan.index', ['tahun' => $request->tahun_anggaran]),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ [UPDATE DEBUG] Database error: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('❌ [UPDATE DEBUG] Error updating RKAS Perubahan: ' . $e->getMessage());
            Log::error('❌ [UPDATE DEBUG] Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function sisipkan(Request $request)
    {
        try {
            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string',
                'harga_satuan' => 'required|numeric|min:0',
                'jumlah' => 'required|integer|min:1',
                'satuan' => 'required|string',
                'tahun_anggaran' => 'required',
                'bulan' => 'required|string',
            ]);

            $penganggaran = Penganggaran::where('tahun_anggaran', $request->tahun_anggaran)->firstOrFail();

            RkasPerubahan::create([
                'penganggaran_id' => $penganggaran->id,
                'kode_id' => $request->kode_id,
                'kode_rekening_id' => $request->kode_rekening_id,
                'uraian' => $request->uraian,
                'harga_satuan' => $request->harga_satuan,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'bulan' => $request->bulan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disisipkan ke bulan ' . $request->bulan,
                'bulan' => $request->bulan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rkas = RkasPerubahan::findOrFail($id);

            // Catat penghapusan data sebelum dihapus
            RekamanPerubahanService::catatPerubahan($id, 'delete');

            $rkas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data RKAS berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting RKAS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
            ], 500);
        }
    }

    /**
     * Menghapus semua data RKAS Perubahan dengan kriteria yang sama (hanya bulan Juli-Desember)
     */
    public function deleteAll($id)
    {
        $lockedMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        $allowedMonths = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        try {
            Log::info('🔧 [DELETE ALL] Starting delete all process for ID: ' . $id);

            // Dapatkan data utama
            $mainRkas = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

            if (!$mainRkas) {
                Log::error('❌ [DELETE ALL] Main data not found for ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Data RKAS Perubahan tidak ditemukan.',
                ], 404);
            }

            Log::info('🔧 [DELETE ALL] Found main data:', [
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'uraian' => $mainRkas->uraian
            ]);

            // PERIKSA APAKAH ADA DATA BULAN AKTIF (Juli-Desember) YANG BISA DIHAPUS
            $activeDataCount = RkasPerubahan::where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->whereIn('bulan', $allowedMonths)
                ->count();

            Log::info('🔧 [DELETE ALL] Active data count (Juli-Desember): ' . $activeDataCount);

            // JIKA TIDAK ADA DATA BULAN AKTIF, BERI PESAN ERROR
            if ($activeDataCount === 0) {
                Log::warning('❌ [DELETE ALL] No active data found to delete for ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang dapat dihapus. Data hanya tersedia untuk bulan terkunci (Januari-Juni) yang tidak dapat dihapus.',
                ], 400);
            }

            DB::beginTransaction();

            // Simpan data sebelum dihapus untuk rekaman perubahan (hanya bulan aktif)
            $dataToDelete = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->whereIn('bulan', $allowedMonths) // Hanya data bulan aktif
                ->get();

            Log::info('🔧 [DELETE ALL] Found ' . $dataToDelete->count() . ' active records to delete');

            // Catat data yang akan dihapus untuk rekaman perubahan
            foreach ($dataToDelete as $item) {
                RekamanPerubahanService::catatPerubahan(
                    $item->id,
                    'delete_all',
                    [
                        'kode_kegiatan' => $item->kodeKegiatan->kode ?? '-',
                        'program' => $item->kodeKegiatan->program ?? '-',
                        'sub_program' => $item->kodeKegiatan->sub_program ?? '-',
                        'kode_rekening' => $item->rekeningBelanja->kode_rekening ?? '-',
                        'rincian_objek' => $item->rekeningBelanja->rincian_objek ?? '-',
                        'uraian' => $item->uraian,
                        'jumlah' => $item->jumlah,
                        'satuan' => $item->satuan,
                        'harga_satuan' => $item->harga_satuan,
                        'bulan' => $item->bulan,
                        'total' => $item->jumlah * $item->harga_satuan,
                        'action' => 'Delete semua data melalui modal edit'
                    ]
                );
            }

            // Hapus data (hanya bulan aktif - Juli-Desember)
            $deletedCount = RkasPerubahan::where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->whereIn('bulan', $allowedMonths) // Hanya hapus bulan aktif
                ->delete();

            DB::commit();

            Log::info('🔧 [DELETE ALL] Successfully deleted ' . $deletedCount . ' active records');

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus ' . $deletedCount . ' data untuk kegiatan ini (hanya bulan Juli-Desember). Data bulan Januari-Juni tetap tersimpan.',
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ [DELETE ALL] Error deleting all data: ' . $e->getMessage());
            Log::error('❌ [DELETE ALL] Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getByMonth($bulan)
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $rkasData = RkasPerubahan::with([
                'kodeKegiatan' => function ($query) {
                    $query->select('id', 'kode', 'program', 'sub_program', 'uraian');
                },
                'rekeningBelanja' => function ($query) {
                    $query->select('id', 'kode_rekening', 'rincian_objek');
                },
            ])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->get();

            if ($rkasData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada data untuk bulan ' . $bulan . ' tahun ' . $tahun,
                ]);
            }

            $formattedData = $rkasData->map(function ($rkas) {
                return [
                    'id' => $rkas->id,
                    'program_kegiatan' => $rkas->kodeKegiatan ? $rkas->kodeKegiatan->program : '-',
                    'kegiatan' => $rkas->kodeKegiatan ? $rkas->kodeKegiatan->sub_program : '-',
                    'rekening_belanja' => $rkas->rekeningBelanja ? $rkas->rekeningBelanja->rincian_objek : '-',
                    'uraian' => $rkas->uraian,
                    'dianggaran' => $rkas->jumlah,
                    'dibelanjakan' => 0,
                    'satuan' => $rkas->satuan,
                    'harga_satuan' => 'Rp ' . number_format($rkas->harga_satuan, 0, ',', '.'),
                    'total' => 'Rp ' . number_format($rkas->jumlah * $rkas->harga_satuan, 0, ',', '.'),
                    'bulan' => $rkas->bulan,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting RKAS by month: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getAllData()
    {
        try {
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $rkasData = $this->getAllRkasPerubahanDataGroupedByMonth($penganggaran->id);

            return response()->json([
                'success' => true,
                'data' => $rkasData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all RKAS data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function getTotalPerBulan()
    {
        try {
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

            $totals = [];
            foreach ($months as $month) {
                $total = RkasPerubahan::where('bulan', $month)
                    ->sum(DB::raw('jumlah * harga_satuan'));
                $totals[$month] = $total;
            }

            return response()->json([
                'success' => true,
                'data' => $totals,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting total per month: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total.',
            ], 500);
        }
    }

    public function getTotalTahap1()
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $totalTahap1 = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            $paguAnggaranTahap1 = $penganggaran->pagu_anggaran * 0.5;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_anggaran' => $totalTahap1,
                    'pagu_anggaran' => $paguAnggaranTahap1,
                    'sisa_anggaran' => $paguAnggaranTahap1 - $totalTahap1,
                    'persentase_terpakai' => $paguAnggaranTahap1 > 0 ? ($totalTahap1 / $paguAnggaranTahap1) * 100 : 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting total Tahap 1: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total Tahap 1.',
            ], 500);
        }
    }

    public function getTotalTahap2()
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $totalTahap2 = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            $paguAnggaranTahap2 = $penganggaran->pagu_anggaran * 0.5;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_anggaran' => $totalTahap2,
                    'pagu_anggaran' => $paguAnggaranTahap2,
                    'sisa_anggaran' => $paguAnggaranTahap2 - $totalTahap2,
                    'persentase_terpakai' => $paguAnggaranTahap2 > 0 ? ($totalTahap2 / $paguAnggaranTahap2) * 100 : 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting total Tahap 2: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total Tahap 2.',
            ], 500);
        }
    }

    public function getDataByTahap($tahap)
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (! $penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $rkasData = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->byTahap($tahap)
                ->get();

            $formattedData = $rkasData->map(function ($rkas) {
                return [
                    'id' => $rkas->id,
                    'program_kegiatan' => $rkas->kodeKegiatan ? $rkas->kodeKegiatan->program : '-',
                    'kegiatan' => $rkas->kodeKegiatan ? $rkas->kodeKegiatan->sub_program : '-',
                    'rekening_belanja' => $rkas->rekeningBelanja ? $rkas->rekeningBelanja->rincian_objek : '-',
                    'uraian' => $rkas->uraian,
                    'dianggaran' => $rkas->jumlah,
                    'dibelanjakan' => 0,
                    'satuan' => $rkas->satuan,
                    'harga_satuan' => 'Rp ' . number_format($rkas->harga_satuan, 0, ',', '.'),
                    'total' => 'Rp ' . number_format($rkas->jumlah * $rkas->harga_satuan, 0, ',', '.'),
                    'bulan' => $rkas->bulan,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting RKAS by tahap: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    /**
     * Mengambil semua data RKAS Perubahan dan mengelompokkannya per bulan.
     */
    private function getAllRkasPerubahanDataGroupedByMonth($penganggaranId)
    {
        $months = RkasPerubahan::getBulanList();
        $rkasData = [];

        foreach ($months as $month) {
            $rkasData[$month] = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaranId)
                ->where('bulan', $month)
                ->get();
        }

        return $rkasData;
    }

    // Method untuk generate PDF
    public function generateTahapanPdf(Request $request)
    {
        try {
            Log::info('Generate PDF RKA Tahapan dipanggil', [
                'request_data' => $request->all(),
                'tahun' => $request->input('tahun'),
                'ukuran_kertas' => $request->input('ukuran_kertas'),
                'orientasi' => $request->input('orientasi'),
                'font_size' => $request->input('font_size')
            ]);

            // Ambil tahun dari parameter
            $tahun = $request->input('tahun');

            if (!$tahun) {
                Log::error('Parameter tahun tidak ditemukan dalam request');
                throw new \Exception('Parameter tahun diperlukan');
            }

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::error('Data penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                throw new \Exception('Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan');
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
                throw new \Exception('Data sekolah belum tersedia');
            }

            // Data untuk RKAS Tahapan - dengan relasi rkasAsli
            $rkasData = RkasPerubahan::withTrashedData()->with(['rkasAsli', 'kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode;
                })
                ->filter(function ($group, $key) {
                    return !is_null($key);
                });

            // Data sekolah untuk PDF
            $dataSekolah = [
                'npsn' => $sekolah->npsn,
                'nama' => $sekolah->nama_sekolah,
                'alamat' => $sekolah->alamat,
                'kabupaten' => $sekolah->kabupaten_kota,
                'provinsi' => $sekolah->provinsi,
                'tahun_anggaran' => $penganggaran->tahun_anggaran,
                'tahap' => 'I dan II',
                'kepala_sekolah' => $penganggaran->kepala_sekolah,
                'nip_kepala_sekolah' => $penganggaran->nip_kepala_sekolah,
                'bendahara' => $penganggaran->bendahara,
                'nip_bendahara' => $penganggaran->nip_bendahara,
                'komite' => $penganggaran->komite,
            ];

            // Data penerimaan
            $penerimaan = [
                'penganggaran' => $penganggaran,
                'total' => $penganggaran->pagu_anggaran,
                'items' => [
                    [
                        'kode' => '4.3.1.01.',
                        'uraian' => 'BOS Reguler',
                        'jumlah' => $penganggaran->pagu_anggaran,
                    ],
                ],
            ];

            // Organisasikan data RKAS
            $belanja = $this->kelolaDataRkasPerubahan($rkasData);

            // Hitung total bertambah dan berkurang
            $totalBertambah = 0;
            $totalBerkurang = 0;
            $totalTahap1 = 0;
            $totalTahap2 = 0;

            foreach ($belanja as $program) {
                $totalBertambah += $program['bertambah'] ?? 0;
                $totalBerkurang += $program['berkurang'] ?? 0;
                $totalTahap1 += $program['tahap1'] ?? 0;
                $totalTahap2 += $program['tahap2'] ?? 0;
            }

            // Format tanggal perubahan
            $formatTanggalPerubahan = $penganggaran->format_tanggal_perubahan ?
                \Carbon\Carbon::parse($penganggaran->tanggal_perubahan)->format('d/m/Y') :
                \Carbon\Carbon::now()->format('d/m/Y');

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => $request->input('ukuran_kertas', 'A4'),
                'orientasi' => $request->input('orientasi', 'landscape'),
                'font_size' => $request->input('font_size', '8pt')
            ];

            Log::info('Data untuk PDF RKA Tahapan siap', [
                'tahun' => $tahun,
                'total_tahap1' => $totalTahap1,
                'total_tahap2' => $totalTahap2,
                'print_settings' => $printSettings
            ]);

            $pdf = PDF::loadView('rkas-perubahan.rkas-perubahan-tahap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $belanja,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'totalBertambah' => $totalBertambah,
                'totalBerkurang' => $totalBerkurang,
                'totalBelanja' => $totalTahap1 + $totalTahap2,
                'penganggaran' => $penganggaran,
                'printSettings' => $printSettings,
                'format_tanggal_perubahan' => $formatTanggalPerubahan
            ]);

            // Set paper options
            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            // Set options for better font handling
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            Log::info('PDF RKA Tahapan berhasil di-generate');

            return $pdf->stream('RKAS-Perubahan-Tahap-' . $tahun . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKAS Perubahan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response("
        <html>
            <body>
                <h1>Error Generating PDF RKA Tahapan</h1>
                <p>Terjadi kesalahan saat membuat PDF: " . $e->getMessage() . "</p>
                <p>Silakan coba lagi atau hubungi administrator.</p>
                <button onclick='window.history.back()'>Kembali</button>
            </body>
        </html>
        ", 500);
        }
    }

    private function kelolaDataRkasPerubahan($rkasData)
    {
        $terorganisir = [];

        foreach ($rkasData as $kode => $items) {
            if (empty($items) || $items->isEmpty()) {
                continue;
            }

            $bagian = explode('.', $kode);

            // Level program
            $kodeProgram = $bagian[0];
            if (! isset($terorganisir[$kodeProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->program ?? '-',
                    'sub_programs' => [],
                    'total_asli' => 0,
                    'total_perubahan' => 0,
                    'bertambah' => 0,
                    'berkurang' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                ];
            }

            // Level sub-program
            $kodeSubProgram = count($bagian) > 1 ? $bagian[0] . '.' . $bagian[1] : null;
            if ($kodeSubProgram && ! isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->sub_program ?? '-',
                    'uraian_programs' => [],
                    'items' => [],
                    'total_asli' => 0,
                    'total_perubahan' => 0,
                    'bertambah' => 0,
                    'berkurang' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                ];
            }

            // Level uraian
            $kodeUraian = $kode;
            if ($kodeSubProgram && ! isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->uraian ?? '-',
                    'items' => [],
                    'total_asli' => 0,
                    'total_perubahan' => 0,
                    'bertambah' => 0,
                    'berkurang' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                ];
            }

            // Kelompokkan item berdasarkan kode_rekening dan uraian
            $groupedItems = [];
            foreach ($items as $item) {
                if (! $item->rekeningBelanja) {
                    continue;
                }

                $key = $item->rekeningBelanja->kode_rekening . '-' . $item->uraian;

                // LOG: Debug informasi item
                Log::info("🔧 [RKA_TAHAPAN_DEBUG] Processing item:", [
                    'item_id' => $item->id,
                    'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                    'uraian' => $item->uraian,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah,
                    'group_key' => $key
                ]);

                // Data sebelum perubahan (dari RKAS asli) - AGREGAT SEMUA BULAN
                $jumlahAsli = 0;
                $volumeAsli = 0;
                $hargaSatuanAsli = 0;

                if ($item->rkasAsli) {
                    // Ambil semua data RKAS asli dengan kode dan uraian yang sama
                    $rkasAsliItems = Rkas::where('penganggaran_id', $item->penganggaran_id)
                        ->where('kode_id', $item->kode_id)
                        ->where('kode_rekening_id', $item->kode_rekening_id)
                        ->where('uraian', $item->uraian)
                        ->get();

                    foreach ($rkasAsliItems as $asliItem) {
                        $volumeAsli += $asliItem->jumlah;
                        $hargaSatuanAsli = $asliItem->harga_satuan; // Harga satuan dianggap sama
                        $jumlahAsli += $asliItem->jumlah * $asliItem->harga_satuan;
                    }
                }

                // Data setelah perubahan - AGREGAT SEMUA BULAN (termasuk yang dihapus)
                $rkasPerubahanItems = RkasPerubahan::withTrashedData()
                    ->where('penganggaran_id', $item->penganggaran_id)
                    ->where('kode_id', $item->kode_id)
                    ->where('kode_rekening_id', $item->kode_rekening_id)
                    ->where('uraian', $item->uraian)
                    ->get();

                $volumePerubahan = 0;
                $hargaSatuanPerubahan = 0;
                $jumlahPerubahan = 0;

                foreach ($rkasPerubahanItems as $perubahanItem) {
                    // Jika data dihapus, set volume dan jumlah ke 0
                    if ($perubahanItem->trashed()) {
                        $volumePerubahan += 0;
                        $hargaSatuanPerubahan = 0;
                        $jumlahPerubahan += 0;
                    } else {
                        $volumePerubahan += $perubahanItem->jumlah;
                        $hargaSatuanPerubahan = $perubahanItem->harga_satuan;
                        $jumlahPerubahan += $perubahanItem->jumlah * $perubahanItem->harga_satuan;
                    }
                }

                // LOG: Debug perhitungan perubahan
                Log::info("🔧 [RKA_TAHAPAN_DEBUG] Perubahan calculation:", [
                    'item_id' => $item->id,
                    'volume_perubahan' => $volumePerubahan,
                    'harga_satuan_perubahan' => $hargaSatuanPerubahan,
                    'jumlah_perubahan' => $jumlahPerubahan,
                    'rkas_perubahan_items_count' => $rkasPerubahanItems->count()
                ]);

                // Hitung selisih
                $selisih = $jumlahPerubahan - $jumlahAsli;
                $bertambah = $selisih > 0 ? $selisih : 0;
                $berkurang = $selisih < 0 ? abs($selisih) : 0;

                // Tentukan tahap
                $tahap1 = 0;
                $tahap2 = 0;

                foreach ($rkasPerubahanItems as $perubahanItem) {
                    $isTahap1 = in_array($perubahanItem->bulan, RkasPerubahan::getBulanTahap1());

                    // Jika data dihapus, jumlah item = 0
                    $jumlahItem = $perubahanItem->trashed() ? 0 : $perubahanItem->jumlah * $perubahanItem->harga_satuan;

                    if ($isTahap1) {
                        $tahap1 += $jumlahItem;
                    } else {
                        $tahap2 += $jumlahItem;
                    }
                }

                // PERBAIKAN: Gunakan harga satuan dari item saat ini, bukan dari groupedItems
                if (! isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                        'uraian' => $item->uraian,
                        'volume_asli' => $volumeAsli,
                        'volume_perubahan' => $volumePerubahan,
                        'satuan' => $item->satuan,
                        'harga_satuan_asli' => $hargaSatuanAsli,
                        'harga_satuan_perubahan' => $hargaSatuanPerubahan, // Gunakan harga satuan perubahan
                        'jumlah_asli' => $jumlahAsli,
                        'jumlah_perubahan' => $jumlahPerubahan,
                        'bertambah' => $bertambah,
                        'berkurang' => $berkurang,
                        'tahap1' => $tahap1,
                        'tahap2' => $tahap2,
                    ];

                    // LOG: Debug pembuatan item baru
                    Log::info("🔧 [RKA_TAHAPAN_DEBUG] Created new grouped item:", [
                        'key' => $key,
                        'harga_satuan_perubahan' => $hargaSatuanPerubahan
                    ]);
                } else {
                    // LOG: Debug item yang sudah ada
                    Log::warning("⚠️ [RKA_TAHAPAN_DEBUG] Item already exists in grouped items:", [
                        'key' => $key,
                        'existing_harga_satuan_perubahan' => $groupedItems[$key]['harga_satuan_perubahan'],
                        'new_harga_satuan_perubahan' => $hargaSatuanPerubahan
                    ]);
                }
            }

            // LOG: Debug grouped items sebelum ditambahkan
            Log::info("🔧 [RKA_TAHAPAN_DEBUG] Grouped items before adding to structure:", [
                'kode_program' => $kodeProgram,
                'grouped_items_count' => count($groupedItems),
                'grouped_items' => array_keys($groupedItems)
            ]);

            // Tambahkan item ke struktur data
            if ($kodeSubProgram) {
                foreach ($groupedItems as $itemKey => $item) {
                    // LOG: Debug setiap item sebelum ditambahkan
                    Log::info("🔧 [RKA_TAHAPAN_DEBUG] Adding item to structure:", [
                        'item_key' => $itemKey,
                        'harga_satuan_perubahan' => $item['harga_satuan_perubahan'],
                        'jumlah_perubahan' => $item['jumlah_perubahan']
                    ]);

                    // Update program
                    $terorganisir[$kodeProgram]['total_asli'] += $item['jumlah_asli'];
                    $terorganisir[$kodeProgram]['total_perubahan'] += $item['jumlah_perubahan'];
                    $terorganisir[$kodeProgram]['bertambah'] += $item['bertambah'];
                    $terorganisir[$kodeProgram]['berkurang'] += $item['berkurang'];
                    $terorganisir[$kodeProgram]['tahap1'] += $item['tahap1'];
                    $terorganisir[$kodeProgram]['tahap2'] += $item['tahap2'];

                    // Update sub program
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['total_asli'] += $item['jumlah_asli'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['total_perubahan'] += $item['jumlah_perubahan'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['bertambah'] += $item['bertambah'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['berkurang'] += $item['berkurang'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['tahap1'] += $item['tahap1'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['tahap2'] += $item['tahap2'];

                    // Update uraian program
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['total_asli'] += $item['jumlah_asli'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['total_perubahan'] += $item['jumlah_perubahan'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['bertambah'] += $item['bertambah'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['berkurang'] += $item['berkurang'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['tahap1'] += $item['tahap1'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['tahap2'] += $item['tahap2'];

                    // Tambahkan item detail
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['items'][] = $item;
                }
            }
        }

        // LOG: Debug struktur akhir
        Log::info("🔧 [RKA_TAHAPAN_DEBUG] Final organized structure:", [
            'programs_count' => count($terorganisir),
            'programs' => array_keys($terorganisir)
        ]);

        // Urutkan data
        ksort($terorganisir);
        foreach ($terorganisir as &$program) {
            if (! empty($program['sub_programs'])) {
                ksort($program['sub_programs']);
                foreach ($program['sub_programs'] as &$subProgram) {
                    if (! empty($subProgram['uraian_programs'])) {
                        ksort($subProgram['uraian_programs']);
                    }
                }
            }
        }

        return $terorganisir;
    }

    public function showRekapanPerubahan(Request $request)
    {
        try {
            $tahun = $request->input('tahun');
            Log::info('Parameter tahun dari URL: ' . $tahun);

            // Ambil data penganggaran berdasarkan tahun yang diminta, bukan yang terbaru
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();
            Log::info('ID Penganggaran ditemukan: ' . $penganggaran->id);

            $sekolah = Sekolah::first();

            if (! $sekolah) {
                throw new \Exception('Data sekolah tidak ditemukan');
            }

            // Ambil data untuk grafik
            $grafikData = $this->getGrafikData($penganggaran->id);
            Log::info('Data grafik berhasil diambil:', $grafikData);

            // Data untuk RKAS Tahapan - dengan relasi rkasAsli
            $belanja = $this->kelolaDataRkasPerubahan(
                RkasPerubahan::withTrashedData()->with(['rkasAsli', 'kodeKegiatan', 'rekeningBelanja'])
                    ->where('penganggaran_id', $penganggaran->id)
                    ->orderBy('kode_id')
                    ->get()
                    ->groupBy(function ($item) {
                        return optional($item->kodeKegiatan)->kode;
                    })
            );

            // Hitung total bertambah dan berkurang
            $totalBertambah = 0;
            $totalBerkurang = 0;
            $totalAsli = 0;
            $totalPerubahan = 0;

            foreach ($belanja as $program) {
                $totalAsli += $program['total_asli'] ?? 0;
                $totalPerubahan += $program['total_perubahan'] ?? 0;
                // $totalBertambah += $program['bertambah'] ?? 0;
                // $totalBerkurang += $program['berkurang'] ?? 0;

                foreach ($program['sub_programs'] as $subProgram) {
                    $totalAsli += $subProgram['total_asli'] ?? 0;
                    $totalPerubahan += $subProgram['total_perubahan'] ?? 0;
                    // $totalBertambah += $subProgram['bertambah'] ?? 0;
                    // $totalBerkurang += $subProgram['berkurang'] ?? 0;

                    foreach ($subProgram['uraian_programs'] as $uraian) {
                        $totalAsli += $uraian['total_asli'] ?? 0;
                        $totalPerubahan += $uraian['total_perubahan'] ?? 0;
                        // $totalBertambah += $uraian['bertambah'] ?? 0;
                        // $totalBerkurang += $uraian['berkurang'] ?? 0;

                        foreach ($uraian['items'] as $item) {
                            $totalAsli += $item['jumlah_asli'] ?? 0;
                            $totalPerubahan += $item['jumlah_perubahan'] ?? 0;
                            $totalBertambah += $item['bertambah'] ?? 0;
                            $totalBerkurang += $item['berkurang'] ?? 0;
                        }
                    }
                }
            }

            // Data indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => number_format($penganggaran->pagu_anggaran, 0, ',', '.')],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => ''],
            ];

            // Data untuk RKAS Tahapan - ambil berdasarkan penganggaran_id
            $dataTerkelola = $this->kelolaDataRkasPerubahan(
                RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                    ->where('penganggaran_id', $penganggaran->id)
                    ->orderBy('kode_id')
                    ->get()
                    ->groupBy(function ($item) {
                        return optional($item->kodeKegiatan)->kode;
                    })
            );

            // Data untuk RKA Rekap - ambil berdasarkan penganggaran_id
            $rekapData = $this->getRekapRkasPerubahan($penganggaran->id);

            // Data untuk Lembar Kerja 221 - ambil berdasarkan penganggaran_id
            [$groupedItemsFor221, $totalsFor221] = $this->prepare221Data($penganggaran->id);

            // Get selected month or default to January
            $bulan = request('bulan') ?? 'Januari';

            // Query data untuk bulan yang dipilih - berdasarkan penganggaran_id
            $rkasBulanan = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode ?? 'unknown';
                });

            $totalBulanan = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->sum(DB::raw('jumlah * harga_satuan'));

            // Debug: Log ID penganggaran yang ditemukan
            Log::info('ID Penganggaran ditemukan: ' . $penganggaran->id);
            // Data penerimaan - ambil dari penganggaran yang dipilih
            $penerimaan = [
                'total' => $penganggaran->pagu_anggaran,
                'items' => [
                    [
                        'kode' => '4.3.1.01.',
                        'uraian' => 'BOS Reguler',
                        'jumlah' => $penganggaran->pagu_anggaran,
                    ],
                ],
            ];

            // Hitung total tahap 1 dan 2 berdasarkan penganggaran_id
            $totalTahap1 = RkasPerubahan::getTotalTahap1($penganggaran->id);
            $totalTahap2 = RkasPerubahan::getTotalTahap2($penganggaran->id);

            Log::info('RKAS Perubahan Data for bulan ' . $bulan . ': ' . $rkasBulanan->count() . ' items');
            Log::info('RKAS Perubahan Total: ' . $totalBulanan);

            // Debug: Log hasil perhitungan
            Log::info("Total Tahap 1: {$totalTahap1}, Total Tahap 2: {$totalTahap2}");

            return view('rkas-perubahan.rekapan-perubahan', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'totalBertambah' => $totalBertambah,
                'totalBerkurang' => $totalBerkurang,
                'rkasBulanan' => $rkasBulanan,
                'totalBulanan' => $totalBulanan,
                'months' => RkasPerubahan::getBulanList(),
                'indikatorKinerja' => $indikatorKinerja,
                'penerimaan' => $penerimaan,
                'belanja' => $belanja,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'rekapData' => $rekapData,
                'mainStructure' => [
                    '5' => 'BELANJA',
                    '5.1' => 'BELANJA OPERASI',
                    '5.1.02' => 'BELANJA BARANG DAN JASA',
                    '5.2' => 'BELANJA MODAL',
                    '5.2.02' => 'BELANJA MODAL PERALATAN DAN MESIN',
                    '5.2.04' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
                    '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA',
                ],
                'groupedItems' => $groupedItemsFor221,
                'totals' => $totalsFor221,
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'grafikData' => $grafikData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing rekapan: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menampilkan rekapan RKAS: ' . $e->getMessage());
        }
    }

    private function prepare221Data($penganggaranId)
    {
        $mainStructure = [
            '5' => 'BELANJA',
            '5.1' => 'BELANJA OPERASI',
            '5.1.02' => 'BELANJA BARANG DAN JASA',
            '5.2' => 'BELANJA MODAL',
            '5.2.02' => 'BELANJA MODAL PERALATAN DAN MESIN',
            '5.2.04' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
            '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA',
        ];

        $rkasDetail = RkasPerubahan::with(['rekeningBelanja'])
            ->where('penganggaran_id', $penganggaranId)
            ->orderBy('kode_rekening_id')
            ->get();

        $groupedItems = [];
        $totals = [];

        foreach ($mainStructure as $kode => $uraian) {
            $totals[$kode] = 0;
        }

        foreach ($rkasDetail as $item) {
            $kode = $item->rekeningBelanja->kode_rekening;
            $mainCode = $this->findClosestMainCode($kode, array_keys($mainStructure));

            $key = $kode . '-' . $item->uraian . '-' . $item->harga_satuan;

            if (! isset($groupedItems[$mainCode][$key])) {
                $groupedItems[$mainCode][$key] = [
                    'kode_rekening' => $kode,
                    'uraian' => $item->uraian,
                    'volume' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah * $item->harga_satuan,
                ];
            } else {
                $groupedItems[$mainCode][$key]['volume'] += $item->jumlah;
                $groupedItems[$mainCode][$key]['jumlah'] = $groupedItems[$mainCode][$key]['volume'] * $item->harga_satuan;
            }

            $totals[$mainCode] += $item->jumlah * $item->harga_satuan;
        }

        return [$groupedItems, $totals];
    }

    public function generatePdfRkaRekap(Request $request, $tahun)
    {
        try {
            Log::info('Generate PDF RKA Rekap dipanggil', [
                'tahun' => $tahun,
                'ukuran_kertas' => $request->input('ukuran_kertas'),
                'orientasi' => $request->input('orientasi'),
                'font_size' => $request->input('font_size')
            ]);

            // Validasi tahun
            if (!is_numeric($tahun)) {
                throw new \Exception("Tahun harus berupa angka");
            }

            $tahun = (int) $tahun;

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::error('Data penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                throw new \Exception("Data penganggaran untuk tahun {$tahun} tidak ditemukan");
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
                Log::error('Data sekolah tidak ditemukan');
                throw new \Exception("Data sekolah belum tersedia");
            }

            // Data sekolah untuk PDF
            $dataSekolah = [
                'npsn' => $sekolah->npsn,
                'nama' => $sekolah->nama_sekolah,
                'alamat' => $sekolah->alamat,
                'kabupaten' => $sekolah->kabupaten_kota,
                'provinsi' => $sekolah->provinsi,
                'tahun_anggaran' => $penganggaran->tahun_anggaran,
                'kepala_sekolah' => $penganggaran->kepala_sekolah,
                'nip_kepala_sekolah' => $penganggaran->nip_kepala_sekolah,
                'bendahara' => $penganggaran->bendahara,
                'nip_bendahara' => $penganggaran->nip_bendahara,
                'komite' => $penganggaran->komite
            ];

            // Data penerimaan
            $penerimaan = [
                'total' => $penganggaran->pagu_anggaran,
                'items' => [
                    [
                        'kode' => '4.3.1.01.',
                        'uraian' => 'BOS Reguler',
                        'jumlah' => $penganggaran->pagu_anggaran
                    ]
                ]
            ];

            // Data belanja (rekap) - ambil berdasarkan penganggaran_id
            $rekapData = $this->getRekapRkasPerubahan($penganggaran->id);

            // Data tahapan - ambil berdasarkan penganggaran_id
            $totalTahap1 = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'])
                ->sum(DB::raw('COALESCE(jumlah, 0) * COALESCE(harga_satuan, 0)'));

            $totalTahap2 = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                ->sum(DB::raw('COALESCE(jumlah, 0) * COALESCE(harga_satuan, 0)'));

            // Format tanggal cetak
            $tanggalPerubahan = [
                'tanggal_perubahan' => $penganggaran->format_tanggal_cetak ?? 'Belum diisi'
            ];

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => $request->input('ukuran_kertas', 'A4'),
                'orientasi' => $request->input('orientasi', 'portrait'),
                'font_size' => $request->input('font_size', '10pt')
            ];

            Log::info('Data untuk PDF siap', [
                'total_tahap1' => $totalTahap1,
                'total_tahap2' => $totalTahap2,
                'rekap_data_count' => count($rekapData)
            ]);

            $pdf = PDF::loadView('rkas-perubahan.rka-rekap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'rekapData' => $rekapData,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'tanggalPerubahan' => $tanggalPerubahan,
                'penganggaran' => $penganggaran,
                'printSettings' => $printSettings
            ]);

            // Set paper options
            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            // Set options for better font handling
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            Log::info('PDF berhasil di-generate');

            return $pdf->stream('RKA-Rekap-' . $tahun . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKA Rekap: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return error response yang lebih user-friendly
            return response("
            <html>
                <body>
                    <h1>Error Generating PDF</h1>
                    <p>Terjadi kesalahan saat membuat PDF: " . $e->getMessage() . "</p>
                    <p>Silakan coba lagi atau hubungi administrator.</p>
                    <button onclick='window.history.back()'>Kembali</button>
                </body>
            </html>
        ", 500);
        }
    }


    /**
     * Get rekap data untuk RKAS Perubahan
     */
    private function getRekapRkasPerubahan($penganggaranId)
    {
        try {
            $penganggaran = Penganggaran::findOrFail($penganggaranId);
            $rkasData = RkasPerubahan::with(['rekeningBelanja'])
                ->where('penganggaran_id', $penganggaranId)
                ->get();

            // 1. Hitung semua jumlah berdasarkan kode rekening
            $belanjaTotal = 0;
            $belanjaOperasi = 0;
            $belanjaBarangJasa = 0;
            $belanjaBarang = 0;
            $belanjaJasa = 0;
            $belanjaPemeliharaan = 0;
            $belanjaPerjalanan = 0;
            $belanjaModal = 0;
            $belanjaModalPeralatan = 0;
            $belanjaModalJalan = 0;
            $belanjaModalAset = 0;

            foreach ($rkasData as $item) {
                $kode = $item->rekeningBelanja->kode_rekening;
                $jumlah = $item->jumlah * $item->harga_satuan;

                // 5 - BELANJA
                if (strpos($kode, '5') === 0) {
                    $belanjaTotal += $jumlah;
                }

                // 5.1 - BELANJA OPERASI
                if (strpos($kode, '5.1') === 0) {
                    $belanjaOperasi += $jumlah;
                }

                // 5.1.02 - BELANJA BARANG DAN JASA
                if (strpos($kode, '5.1.02') === 0) {
                    $belanjaBarangJasa += $jumlah;
                }

                // 5.1.02.01 - BELANJA BARANG
                if (strpos($kode, '5.1.02.01') === 0) {
                    $belanjaBarang += $jumlah;
                }

                // 5.1.02.02 - BELANJA JASA
                if (strpos($kode, '5.1.02.02') === 0) {
                    $belanjaJasa += $jumlah;
                }

                // 5.1.02.03 - BELANJA PEMELIHARAAN
                if (strpos($kode, '5.1.02.03') === 0) {
                    $belanjaPemeliharaan += $jumlah;
                }

                // 5.1.02.04 - BELANJA PERJALANAN DINAS
                if (strpos($kode, '5.1.02.04') === 0) {
                    $belanjaPerjalanan += $jumlah;
                }

                // 5.2 - BELANJA MODAL
                if (strpos($kode, '5.2') === 0) {
                    $belanjaModal += $jumlah;
                }

                // 5.2.02 - BELANJA MODAL PERALATAN DAN MESIN
                if (strpos($kode, '5.2.02') === 0) {
                    $belanjaModalPeralatan += $jumlah;
                }

                // 5.2.04 - BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI
                if (strpos($kode, '5.2.04') === 0) {
                    $belanjaModalJalan += $jumlah;
                }

                // 5.2.05 - BELANJA MODAL ASET TETAP LAINNYA
                if (strpos($kode, '5.2.05') === 0) {
                    $belanjaModalAset += $jumlah;
                }
            }

            $totalPendapatan = $penganggaran ? $penganggaran->pagu_anggaran : 0;
            $defisit = $totalPendapatan - $belanjaTotal;

            // Format data untuk ditampilkan
            $rekapData = [];

            // 1. PENDAPATAN
            $rekapData[] = [
                'kode' => '',
                'uraian' => 'JUMLAH PENDAPATAN',
                'jumlah' => $totalPendapatan,
            ];

            // 2. BELANJA
            $rekapData[] = [
                'kode' => '5',
                'uraian' => 'BELANJA',
                'jumlah' => $belanjaTotal > 0 ? $belanjaTotal : '-',
            ];

            // 3. BELANJA OPERASI
            $rekapData[] = [
                'kode' => '5.1',
                'uraian' => 'BELANJA OPERASI',
                'jumlah' => $belanjaOperasi > 0 ? $belanjaOperasi : '-',
            ];

            // 4. BELANJA BARANG DAN JASA
            $rekapData[] = [
                'kode' => '5.1.02',
                'uraian' => 'BELANJA BARANG DAN JASA',
                'jumlah' => $belanjaBarangJasa > 0 ? $belanjaBarangJasa : '-',
            ];

            // 5. BELANJA BARANG
            $rekapData[] = [
                'kode' => '5.1.02.01',
                'uraian' => 'BELANJA BARANG',
                'jumlah' => $belanjaBarang > 0 ? $belanjaBarang : '-',
            ];

            // 6. BELANJA JASA
            $rekapData[] = [
                'kode' => '5.1.02.02',
                'uraian' => 'BELANJA JASA',
                'jumlah' => $belanjaJasa > 0 ? $belanjaJasa : '-',
            ];

            // 7. BELANJA PEMELIHARAAN
            $rekapData[] = [
                'kode' => '5.1.02.03',
                'uraian' => 'BELANJA PEMELIHARAAN',
                'jumlah' => $belanjaPemeliharaan > 0 ? $belanjaPemeliharaan : '-',
            ];

            // 8. BELANJA PERJALANAN DINAS
            $rekapData[] = [
                'kode' => '5.1.02.04',
                'uraian' => 'BELANJA PERJALANAN DINAS',
                'jumlah' => $belanjaPerjalanan > 0 ? $belanjaPerjalanan : '-',
            ];

            // 9. BELANJA MODAL
            $rekapData[] = [
                'kode' => '5.2',
                'uraian' => 'BELANJA MODAL',
                'jumlah' => $belanjaModal > 0 ? $belanjaModal : '-',
            ];

            // 10. BELANJA MODAL PERALATAN DAN MESIN
            $rekapData[] = [
                'kode' => '5.2.02',
                'uraian' => 'BELANJA MODAL PERALATAN DAN MESIN',
                'jumlah' => $belanjaModalPeralatan > 0 ? $belanjaModalPeralatan : '-',
            ];

            // 11. BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI
            $rekapData[] = [
                'kode' => '5.2.04',
                'uraian' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
                'jumlah' => $belanjaModalJalan > 0 ? $belanjaModalJalan : '-',
            ];

            // 12. BELANJA MODAL ASET TETAP LAINNYA
            $rekapData[] = [
                'kode' => '5.2.05',
                'uraian' => 'BELANJA MODAL ASET TETAP LAINNYA',
                'jumlah' => $belanjaModalAset > 0 ? $belanjaModalAset : '-',
            ];

            // 13. TOTAL BELANJA
            $rekapData[] = [
                'kode' => '',
                'uraian' => 'JUMLAH BELANJA',
                'jumlah' => $belanjaTotal,
            ];

            // 14. DEFISIT
            $rekapData[] = [
                'kode' => '',
                'uraian' => 'DEFISIT',
                'jumlah' => $defisit,
            ];

            return $rekapData;
        } catch (\Exception $e) {
            Log::error('Error getting rekap RKAS: ' . $e->getMessage());

            return [];
        }
    }

    public function rekapRkaDuaSatu()
    {
        try {
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $sekolah = Sekolah::first();

            if (! $penganggaran || ! $sekolah) {
                throw new \Exception('Data penganggaran atau sekolah tidak ditemukan');
            }

            // Data indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => number_format($penganggaran->pagu_anggaran, 0, ',', '.')],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => ''],
            ];

            // Data untuk tabel rincian anggaran
            $rkasData = RkasPerubahan::with(['rekeningBelanja'])
                ->orderBy('kode_rekening_id')
                ->get();

            // Struktur hierarkis kode rekening utama
            $mainStructure = [
                '5' => 'BELANJA',
                '5.1' => 'BELANJA OPERASI',
                '5.1.02' => 'BELANJA BARANG DAN JASA',
                '5.2' => 'BELANJA MODAL',
                '5.2.02' => 'BELANJA MODAL PERALATAN DAN MESIN',
                '5.2.04' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
                '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA',
            ];

            // Kelompokkan item berdasarkan kode utama
            $groupedItems = [];
            foreach ($rkasData as $item) {
                $kode = $item->rekeningBelanja->kode_rekening;

                // Temukan kode utama terdekat
                $mainCode = $this->findClosestMainCode($kode, array_keys($mainStructure));

                if (! isset($groupedItems[$mainCode])) {
                    $groupedItems[$mainCode] = [];
                }

                $groupedItems[$mainCode][] = [
                    'kode_rekening' => $kode,
                    'uraian' => $item->uraian,
                    'volume' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah * $item->harga_satuan,
                ];
            }

            // Hitung total per kode utama
            $totals = [];
            foreach ($mainStructure as $kode => $uraian) {
                $totals[$kode] = 0;
                foreach ($rkasData as $item) {
                    if (strpos($item->rekeningBelanja->kode_rekening, $kode) === 0) {
                        $totals[$kode] += $item->jumlah * $item->harga_satuan;
                    }
                }
            }

            return view('rkas-perubahan.rekapan-perubahan', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'indikatorKinerja' => $indikatorKinerja,
                'mainStructure' => $mainStructure,
                'groupedItems' => $groupedItems,
                'totals' => $totals,
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'tahun_anggaran' => $penganggaran->tahun_anggaran,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in rekapRkaDuaSatu: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memuat data Lembar Kerja 221.');
        }
    }

    public function generateRkaDuaSatuPdf(Request $request, $tahun)
    {
        try {
            Log::info('Generate PDF RKA Dua Satu dipanggil', [
                'tahun' => $tahun,
                'ukuran_kertas' => $request->input('ukuran_kertas'),
                'orientasi' => $request->input('orientasi'),
                'font_size' => $request->input('font_size')
            ]);

            // Validasi tahun
            if (!is_numeric($tahun)) {
                throw new \Exception("Tahun harus berupa angka");
            }

            $tahun = (int) $tahun;

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::error('Data penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                throw new \Exception("Data penganggaran untuk tahun {$tahun} tidak ditemukan");
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
                Log::error('Data sekolah tidak ditemukan');
                throw new \Exception("Data sekolah belum tersedia");
            }

            // Data untuk tabel indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => $penganggaran->pagu_anggaran],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => 0],
            ];

            // Data untuk tabel rincian anggaran - filter berdasarkan penganggaran_id
            $rkasData = RkasPerubahan::with(['rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->orderBy('kode_rekening_id')
                ->get();

            // Struktur hierarkis kode rekening utama
            $mainStructure = [
                '5' => 'BELANJA',
                '5.1' => 'BELANJA OPERASI',
                '5.1.02' => 'BELANJA BARANG DAN JASA',
                '5.2' => 'BELANJA MODAL',
                '5.2.02' => 'BELANJA MODAL PERALATAN DAN MESIN',
                '5.2.04' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
                '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA',
            ];

            // Kelompokkan item dengan menggabungkan yang sama (kode rekening, uraian, dan harga satuan)
            $groupedItems = [];
            foreach ($rkasData as $item) {
                $kode = $item->rekeningBelanja->kode_rekening;
                $uraian = $item->uraian;
                $hargaSatuan = $item->harga_satuan;

                // Temukan kode utama terdekat
                $mainCode = $this->findClosestMainCode($kode, array_keys($mainStructure));

                // Buat key unik berdasarkan kode rekening, uraian, dan harga satuan
                $itemKey = $kode . '-' . $uraian . '-' . $hargaSatuan;

                if (!isset($groupedItems[$mainCode])) {
                    $groupedItems[$mainCode] = [];
                }

                if (!isset($groupedItems[$mainCode][$itemKey])) {
                    $groupedItems[$mainCode][$itemKey] = [
                        'kode_rekening' => $kode,
                        'uraian' => $uraian,
                        'volume' => $item->jumlah,
                        'satuan' => $item->satuan,
                        'harga_satuan' => $hargaSatuan,
                        'jumlah' => $item->jumlah * $hargaSatuan,
                    ];
                } else {
                    // Jika sudah ada, tambahkan volumenya dan hitung ulang jumlah
                    $groupedItems[$mainCode][$itemKey]['volume'] += $item->jumlah;
                    $groupedItems[$mainCode][$itemKey]['jumlah'] = $groupedItems[$mainCode][$itemKey]['volume'] * $hargaSatuan;
                }
            }

            // Hitung total per kode utama
            $totals = [];
            foreach ($mainStructure as $kode => $uraian) {
                $totals[$kode] = 0;
                if (isset($groupedItems[$kode])) {
                    foreach ($groupedItems[$kode] as $item) {
                        $totals[$kode] += $item['jumlah'];
                    }
                }
            }

            // Format tanggal perubahan
            $formatTanggalPerubahan = $penganggaran->tanggal_perubahan ?
                \Carbon\Carbon::parse($penganggaran->tanggal_perubahan)->format('d/m/Y') :
                \Carbon\Carbon::now()->format('d/m/Y');

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => $request->input('ukuran_kertas', 'A4'),
                'orientasi' => $request->input('orientasi', 'portrait'),
                'font_size' => $request->input('font_size', '10pt')
            ];

            Log::info('Data untuk PDF RKA 221 siap', [
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'grouped_items_count' => count($groupedItems),
                'print_settings' => $printSettings
            ]);

            $pdf = PDF::loadView('rkas-perubahan.rka-dua-satu-pdf', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'indikatorKinerja' => $indikatorKinerja,
                'mainStructure' => $mainStructure,
                'groupedItems' => $groupedItems,
                'totals' => $totals,
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'tahun_anggaran' => $penganggaran->tahun_anggaran,
                'format_tanggal_perubahan' => $formatTanggalPerubahan,
                'printSettings' => $printSettings
            ]);

            // Set paper options
            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            // Set options for better font handling
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            Log::info('PDF RKA 221 berhasil di-generate');

            return $pdf->stream('RKA-221-' . $tahun . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating RKA 221 PDF: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return error response yang lebih user-friendly
            return response("
        <html>
            <body>
                <h1>Error Generating PDF RKA 221</h1>
                <p>Terjadi kesalahan saat membuat PDF: " . $e->getMessage() . "</p>
                <p>Silakan coba lagi atau hubungi administrator.</p>
                <button onclick='window.history.back()'>Kembali</button>
            </body>
        </html>
        ", 500);
        }
    }

    private function findClosestMainCode($kode, $mainCodes)
    {
        $closestCode = '';
        $maxLength = 0;

        foreach ($mainCodes as $mainCode) {
            if (strpos($kode, $mainCode) === 0 && strlen($mainCode) > $maxLength) {
                $maxLength = strlen($mainCode);
                $closestCode = $mainCode;
            }
        }

        return $closestCode;
    }

    // Add to RkasController class

    public function getRekapBulanan($bulan)
    {
        try {
            Log::info('Mengambil data rekap untuk bulan: ' . $bulan);

            $rkasData = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('bulan', $bulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode;
                })
                ->filter(function ($group, $key) {
                    return ! is_null($key);
                });

            $dataTerkelola = $this->kelolaDataRkasBulanan($rkasData, $bulan);

            $totalBulanan = RkasPerubahan::where('bulan', $bulan)
                ->sum(DB::raw('jumlah * harga_satuan'));

            return response()->json([
                'success' => true,
                'data' => $dataTerkelola,
                'total' => $totalBulanan,
                'bulan' => $bulan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting rekap bulanan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data bulanan.',
            ], 500);
        }
    }

    private function kelolaDataRkasBulanan($rkasData, $bulan)
    {
        $terorganisir = [];

        // Jika tidak ada data untuk bulan tersebut
        if ($rkasData->isEmpty()) {
            return $terorganisir;
        }

        foreach ($rkasData as $kode => $items) {
            if (empty($items) || $items->isEmpty()) {
                continue;
            }

            $bagian = explode('.', $kode);
            $kodeProgram = $bagian[0] ?? '';

            if (! isset($terorganisir[$kodeProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->program ?? '-',
                    'sub_programs' => [],
                    'total' => 0,
                ];
            }

            $kodeSubProgram = count($bagian) > 1 ? $bagian[0] . '.' . $bagian[1] : null;
            if ($kodeSubProgram && ! isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->sub_program ?? '-',
                    'uraian_programs' => [],
                    'items' => [],
                    'total' => 0,
                ];
            }

            $kodeUraian = $kode;
            if ($kodeSubProgram && ! isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->uraian ?? '-',
                    'items' => [],
                    'total' => 0,
                ];
            }

            foreach ($items as $item) {
                if (! $item->rekeningBelanja) {
                    continue;
                }

                $jumlah = $item->jumlah * $item->harga_satuan;

                $itemData = [
                    'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                    'uraian' => $item->uraian,
                    'volume' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $jumlah,
                    'bulan' => $item->bulan,
                ];

                $terorganisir[$kodeProgram]['total'] += $jumlah;
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['total'] += $jumlah;
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['total'] += $jumlah;

                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['items'][] = $itemData;
            }
        }

        ksort($terorganisir);
        foreach ($terorganisir as &$program) {
            if (! empty($program['sub_programs'])) {
                ksort($program['sub_programs']);
                foreach ($program['sub_programs'] as &$subProgram) {
                    if (! empty($subProgram['uraian_programs'])) {
                        ksort($subProgram['uraian_programs']);
                    }
                }
            }
        }

        return $terorganisir;
    }

    public function generatePdfBulanan($tahun, $bulan)
    {
        try {
            // Validasi bulan
            $validBulan = in_array($bulan, [
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
            ]) ? $bulan : 'Januari';

            // Ambil data penganggaran berdasarkan tahun yang diminta
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();
            $sekolah = Sekolah::first();

            if (!$sekolah || !$penganggaran) {
                throw new \Exception('Data sekolah atau penganggaran tidak ditemukan');
            }

            // Ambil data RKAS hanya untuk tahun dan bulan yang diminta
            $rkasData = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $validBulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode ?? 'unknown';
                });

            // Data sekolah untuk PDF
            $dataSekolah = [
                'npsn' => $sekolah->npsn,
                'nama' => $sekolah->nama_sekolah,
                'alamat' => $sekolah->alamat,
                'kabupaten' => $sekolah->kabupaten_kota,
                'provinsi' => $sekolah->provinsi,
                'tahun_anggaran' => $penganggaran->tahun_anggaran,
                'kepala_sekolah' => $penganggaran->kepala_sekolah,
                'nip_kepala_sekolah' => $penganggaran->nip_kepala_sekolah,
                'bendahara' => $penganggaran->bendahara,
                'nip_bendahara' => $penganggaran->nip_bendahara,
                'komite' => $penganggaran->komite,
                'bulan' => $validBulan,
            ];

            // Data penerimaan
            $penerimaan = [
                'total' => $penganggaran->pagu_anggaran,
                'items' => [
                    [
                        'kode' => '4.3.1.01.',
                        'uraian' => 'BOS Reguler',
                        'jumlah' => $penganggaran->pagu_anggaran,
                    ],
                ],
            ];

            // Organisasikan data RKAS
            $dataTerkelola = $this->kelolaDataRkasBulanan($rkasData, $validBulan);

            $totalBelanja = $rkasData->sum(function ($group) {
                return $group->sum(function ($item) {
                    return $item->jumlah * $item->harga_satuan;
                });
            });

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => request()->input('ukuran_kertas', 'A4'),
                'orientasi' => request()->input('orientasi', 'landscape'),
                'font_size' => request()->input('font_size', '10pt')
            ];

            // Format tanggal perubahan
            $formatTanggalPerubahan = $penganggaran->format_tanggal_perubahan;
            Log::info('Generate PDF RKA Bulanan', [
                'tahun' => $tahun,
                'bulan' => $validBulan,
                'print_settings' => $printSettings,
                'total_belanja' => $totalBelanja
            ]);

            $pdf = PDF::loadView('rkas-perubahan.rka-bulanan-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $dataTerkelola,
                'totalBelanja' => $totalBelanja,
                'bulan' => $validBulan,
                'penganggaran' => $penganggaran,
                'printSettings' => $printSettings,
                'format_tanggal_perubahan' => $formatTanggalPerubahan
            ]);

            // Set paper options
            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            // Set options for better font handling
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            return $pdf->stream("RKAS-Bulanan-{$validBulan}-{$tahun}.pdf");
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKAS Bulanan: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghasilkan PDF: ' . $e->getMessage());
        }
    }

    public function getMonthlyData(Request $request)
    {
        try {
            Log::info('getMonthlyData called for RKAS Perubahan');
            Log::info('Bulan: ' . $request->input('bulan'));
            Log::info('Tahun: ' . $request->input('tahun'));

            $bulan = $request->input('bulan', 'Januari');
            $tahun = $request->input('tahun');

            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }

            // Ambil data penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            // Query data untuk bulan yang dipilih berdasarkan penganggaran_id - PASTIKAN menggunakan RkasPerubahan
            $rkasBulanan = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode ?? 'unknown';
                });

            $totalBulanan = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->sum(DB::raw('jumlah * harga_satuan'));

            Log::info('RKAS Perubahan Data for bulan ' . $bulan . ': ' . $rkasBulanan->count() . ' items');

            return response()->json([
                'success' => true,
                'html' => view('rkas-perubahan.partials.monthly-data-perubahan', [
                    'rkasBulanan' => $rkasBulanan,
                    'bulan' => $bulan,
                    'totalBulanan' => $totalBulanan,
                ])->render(),
                'totalBulanan' => $totalBulanan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting monthly data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data bulanan.',
            ], 500);
        }
    }

    public function getDataByMonth($month)
    {
        try {
            $tahun = request()->query('tahun');
            if (! $tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan',
                ], 400);
            }
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            // Fix: Gunakan whereBulan dengan nilai yang konsisten (huruf pertama kapital)
            $monthFormatted = ucfirst(strtolower($month));

            $data = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $monthFormatted)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'program_kegiatan' => $item->kodeKegiatan->program ?? '-',
                        'kegiatan' => $item->kodeKegiatan->sub_program ?? '-',
                        'rekening_belanja' => $item->rekeningBelanja ?
                            $item->rekeningBelanja->kode_rekening . ' - ' . $item->rekeningBelanja->rincian_objek : '-',
                        'uraian' => $item->uraian,
                        'dianggaran' => $item->jumlah,
                        'dibelanjakan' => 0, // Sesuaikan dengan logika Anda
                        'satuan' => $item->satuan,
                        'harga_satuan' => 'Rp ' . number_format($item->harga_satuan, 0, ',', '.'),
                        'total' => 'Rp ' . number_format($item->jumlah * $item->harga_satuan, 0, ',', '.'),
                        'kode_id' => $item->kode_id,
                        'kode_rekening_id' => $item->kode_rekening_id,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $data,
                'month' => $monthFormatted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Mendapatkan data untuk grafik proporsi anggaran - BERDASARKAN KODE KEGIATAN
     */
    private function getGrafikData($penganggaranId)
    {
        Log::info('🔍 [GRAFIK_DEBUG] Starting getGrafikData for penganggaran_id: ' . $penganggaranId);

        try {
            // Total pagu anggaran
            $penganggaran = Penganggaran::find($penganggaranId);
            $totalPagu = $penganggaran->pagu_anggaran ?? 0;

            Log::info('🔍 [GRAFIK_DEBUG] Total pagu anggaran: ' . number_format($totalPagu, 2));

            // 1. Hitung anggaran BUKU - BERDASARKAN KODE KEGIATAN
            $bukuAnggaran = RkasPerubahan::where('penganggaran_id', $penganggaranId)
                ->whereHas('kodeKegiatan', function ($query) {
                    // Kode kegiatan yang terkait dengan buku
                    $query->where('kode', 'like', '05.02.%') // Pengembangan Perpustakaan
                        ->orWhere('kode', 'like', '02.02.%') // Kegiatan pemberdayaan perpustakaan
                        ->orWhere('kode', 'like', '03.02.%') // Pengembangan Perpustakaan
                        ->orWhere('sub_program', 'ilike', '%perpustakaan%')
                        ->orWhere('uraian', 'ilike', '%buku%')
                        ->orWhere('uraian', 'ilike', '%perpustakaan%');
                })
                ->get()
                ->sum(function ($item) {
                    return $item->jumlah * $item->harga_satuan;
                });

            Log::info('📚 [GRAFIK_DEBUG] Buku anggaran calculated: ' . number_format($bukuAnggaran, 2));

            // 2. Hitung anggaran HONOR - BERDASARKAN KODE KEGIATAN
            $honorAnggaran = RkasPerubahan::where('penganggaran_id', $penganggaranId)
                ->whereHas('kodeKegiatan', function ($query) {
                    // Kode kegiatan yang terkait dengan honor/gaji
                    $query->where('kode', 'like', '07.12.%') // Pembayaran Honor
                        ->orWhere('sub_program', 'ilike', '%honor%')
                        ->orWhere('sub_program', 'ilike', '%gaji%')
                        ->orWhere('uraian', 'ilike', '%honor%')
                        ->orWhere('uraian', 'ilike', '%gaji%')
                        ->orWhere('uraian', 'ilike', '%pembayaran%guru%')
                        ->orWhere('uraian', 'ilike', '%pembayaran%tenaga%');
                })
                ->get()
                ->sum(function ($item) {
                    return $item->jumlah * $item->harga_satuan;
                });

            Log::info('💰 [GRAFIK_DEBUG] Honor anggaran calculated: ' . number_format($honorAnggaran, 2));

            // PERBAIKAN: Hitung persentase honor dari 100% total pagu
            $honorPercentage = $totalPagu > 0 ? ($honorAnggaran / $totalPagu) * 100 : 0;
            Log::info('💰 [GRAFIK_DEBUG] Honor percentage dari 100% pagu: ' . number_format($honorPercentage, 2) . '%');

            // 3. Hitung anggaran SARPRAS - HANYA DARI KODE KEGIATAN 05.08
            $sarprasAnggaran = RkasPerubahan::where('penganggaran_id', $penganggaranId)
                ->whereHas('kodeKegiatan', function ($query) {
                    // HANYA kode kegiatan 05.08 - Pemeliharaan Sarana dan Prasarana Sekolah
                    $query->where('kode', 'like', '05.08.%');
                })
                ->get()
                ->sum(function ($item) {
                    return $item->jumlah * $item->harga_satuan;
                });

            Log::info('🏫 [GRAFIK_DEBUG] Sarpras anggaran calculated: ' . number_format($sarprasAnggaran, 2));
            Log::info('🏫 [GRAFIK_DEBUG] Sarpras percentage: ' . ($totalPagu > 0 ? number_format(($sarprasAnggaran / $totalPagu) * 100, 2) : 0) . '%');

            // 4. Data untuk grafik jenis belanja lainnya - BERDASARKAN REKENING BELANJA SAJA
            $jenisBelanjaData = RkasPerubahan::where('penganggaran_id', $penganggaranId)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get()
                ->groupBy(function ($item) {
                    // Group by kombinasi kode kegiatan dan rekening belanja
                    $kodeKegiatan = $item->kodeKegiatan->kode ?? '';
                    $kodeRekening = $item->rekeningBelanja->kode_rekening ?? '';

                // HONORARIUM - Ambil dari kode kegiatan spesifik
                $honorariumKegiatanCodes = [
                    '07.12.01.',
                    '07.12.02.',
                    '07.12.03.',
                    '07.12.04.'
                ];

                // Cek apakah kode kegiatan termasuk honorarium
                foreach ($honorariumKegiatanCodes as $honorCode) {
                    if (strpos($kodeKegiatan, $honorCode) === 0) {
                        return 'Honorarium';
                    }
                }

                    // Kategorikan berdasarkan kode rekening
                    if (strpos($kodeRekening, '5.1.02.01') === 0) {
                        return 'Barang';
                    } elseif (strpos($kodeRekening, '5.1.02.02') === 0) {
                        return 'Jasa';
                    } elseif (strpos($kodeRekening, '5.1.02.03') === 0) {
                        return 'Pemeliharaan';
                    } elseif (strpos($kodeRekening, '5.1.02.04') === 0) {
                        return 'Perjalanan Dinas';
                    } elseif (strpos($kodeRekening, '5.2.02') === 0) {
                        return 'Modal Peralatan Mesin';
                    } elseif (strpos($kodeRekening, '5.2.05') === 0) {
                        return 'Modal Aset Tetap Lainnya';
                    } else {
                        return 'Belum di Anggarkan';
                    }
                })
                ->map(function ($group, $category) use ($totalPagu) {
                    $total = $group->sum(function ($item) {
                        return $item->jumlah * $item->harga_satuan;
                    });

                    $percentage = $totalPagu > 0 ? ($total / $totalPagu) * 100 : 0;

                    return [
                        'label' => $category,
                        'value' => $percentage,
                        'percentage' => number_format($percentage, 2) . '%',
                        'color' => $this->getRandomColor(),
                        'total' => number_format($total, 2)
                    ];
                })
                ->sortByDesc('value')
                ->values();

            Log::info('📊 [GRAFIK_DEBUG] Jenis belanja data count: ' . $jenisBelanjaData->count());

            $grafikData = [
                'buku_anggaran' => $bukuAnggaran,
                'honor_anggaran' => $honorAnggaran,
                'sarpras_anggaran' => $sarprasAnggaran,
                'jenis_belanja' => $jenisBelanjaData,
                'total_pagu' => $totalPagu,
                'honor_percentage' => $honorPercentage,
            ];

            Log::info('✅ [GRAFIK_DEBUG] Final grafik data result: ', [
                'total_pagu' => $totalPagu,
                'buku_anggaran' => $bukuAnggaran,
                'buku_persentase' => $totalPagu > 0 ? ($bukuAnggaran / $totalPagu) * 100 : 0,
                'honor_anggaran' => $honorAnggaran,
                'honor_persentase_dari_100pagu' => $honorPercentage,
                'sarpras_anggaran' => $sarprasAnggaran,
                'sarpras_persentase' => $totalPagu > 0 ? ($sarprasAnggaran / $totalPagu) * 100 : 0,
            ]);

            return $grafikData;
        } catch (\Exception $e) {
            Log::error('❌ [GRAFIK_DEBUG] Error in getGrafikData: ' . $e->getMessage());
            Log::error('❌ [GRAFIK_DEBUG] Stack trace: ' . $e->getTraceAsString());

            return [
                'buku_anggaran' => 0,
                'honor_anggaran' => 0,
                'sarpras_anggaran' => 0,
                'jenis_belanja' => collect(),
                'total_pagu' => 0,
                'honor_percentage' => 0,
            ];
        }
    }

    /**
     * Generate random color for charts
     */
    private function getRandomColor()
    {
        $colors = [
            '#4DB6AC',
            '#F48FB1',
            '#EE82EE',
            '#9FA8DA',
            '#4FC3F7',
            '#BA68C8',
            '#4DD0E1',
            '#7986CB',
            '#81D4FA',
            '#FFB74D',
            '#9575CD',
            '#F48FB1',
            '#7986CB'
        ];

        return $colors[array_rand($colors)];
    }
}
