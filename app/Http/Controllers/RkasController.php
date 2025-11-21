<?php

namespace App\Http\Controllers;

use App\Models\KodeKegiatan;
use App\Models\Penganggaran;
use App\Models\RekeningBelanja;
use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RkasController extends Controller
{
    // Di method index()
    public function index(Request $request)
    {
        try {
            $kodeKegiatans = KodeKegiatan::all();
            $rekeningBelanjas = RekeningBelanja::all();

            // Get selected year from request
            $tahun = $request->input('tahun');

            // Validate year selection
            if (! $tahun) {
                $tahun = Penganggaran::max('tahun_anggaran');
            }

            // Get penganggaran for selected year
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            $sekolah = Sekolah::first();

            // Ambil data untuk grafik
            $grafikData = $this->getGrafikData($penganggaran->id);

            // Get all RKAS data grouped by month for selected year
            $rkasData = $this->getAllRkasDataGroupedByMonth($penganggaran->id);

            // Calculate totals for selected year only
            $totalBudget = Rkas::where('penganggaran_id', $penganggaran->id)
                ->sum(DB::raw('jumlah * harga_satuan'));

            $totalTahap1 = Rkas::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            $totalTahap2 = Rkas::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            // Calculate 50% of pagu anggaran
            $paguAnggaranTahap1 = $penganggaran->pagu_anggaran * 0.5;
            $paguAnggaranTahap2 = $penganggaran->pagu_anggaran * 0.5;

            // Cek status perubahan
            $hasPerubahan = RkasPerubahan::where('penganggaran_id', $penganggaran->id)->exists();

            return view('rkas.rkas', compact(
                'kodeKegiatans',
                'rekeningBelanjas',
                'rkasData',
                'totalBudget',
                'penganggaran',
                'totalTahap1',
                'totalTahap2',
                'paguAnggaranTahap1',
                'paguAnggaranTahap2',
                'tahun', // Pass the selected year to view
                'hasPerubahan',
                'grafikData',
                'sekolah'
            ));
        } catch (\Exception $e) {
            Log::error('Error in RKAS index: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memuat data RKAS.');
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->get('search', '');
            $bulan = $request->get('bulan', '');

            Log::info('RKAS Search called', [
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
            $query = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
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
        try {
            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string',
                'harga_satuan' => 'required|numeric|min:0',
                'bulan' => 'required|array',
                'bulan.*' => 'required|string',
                'jumlah' => 'required|array',
                'jumlah.*' => 'required|integer|min:1',
                'satuan' => 'required|array',
                'satuan.*' => 'required|string',
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

            // Create RKAS entries for each month
            for ($i = 0; $i < count($bulanArray); $i++) {
                // Check if combination already exists
                $exists = Rkas::where('penganggaran_id', $penganggaran->id)
                    ->where('kode_id', $request->kode_id)
                    ->where('kode_rekening_id', $request->kode_rekening_id)
                    ->where('bulan', $bulanArray[$i])
                    ->where('uraian', $request->uraian)
                    ->exists();

                if ($exists) {
                    DB::rollBack();

                    return back()->withErrors(['bulan' => "Data untuk bulan {$bulanArray[$i]} sudah ada dengan kegiatan dan rekening yang sama."]);
                }

                Rkas::create([
                    'penganggaran_id' => $penganggaran->id,
                    'kode_id' => $request->kode_id,
                    'kode_rekening_id' => $request->kode_rekening_id,
                    'uraian' => $request->uraian,
                    'harga_satuan' => $request->harga_satuan,
                    'bulan' => $bulanArray[$i],
                    'jumlah' => $jumlahArray[$i],
                    'satuan' => $satuanArray[$i],
                ]);
            }

            DB::commit();

            return redirect()->route('rkas.index')->with('success', 'Data RKAS berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing RKAS: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    public function show($id)
    {
        try {
            Log::info('🔧 [DETAIL DEBUG] Fetching ALL monthly data for detail, ID: ' . $id);

            // Dapatkan data utama berdasarkan ID
            $mainRkas = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

            if (! $mainRkas) {
                Log::warning('❌ [DETAIL DEBUG] Main data not found for ID: ' . $id);

                return response()->json([
                    'success' => false,
                    'message' => 'Data Rkas tidak ditemukan.',
                ], 400);
            }

            // Dapatkan Semua Data Dengan Kode_id, Kode_rekening_id, dan uraian yang sama
            $allRkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->where('harga_satuan', $mainRkas->harga_satuan)
                ->get();

            Log::info('🔧 [DETAIL DEBUG] Found ' . $allRkasData->count() . ' monthly records for:', [
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'uraian' => $mainRkas->uraian,
            ]);

            // format data untuk semua bulan
            $bulanData = [];
            $totalAnggaran = 0;

            foreach ($allRkasData as $rkas) {
                $bulanData[] = [
                    'bulan' => $rkas->bulan,
                    'jumlah' => $rkas->jumlah,
                    'satuan' => $rkas->satuan,
                    'total' => $rkas->jumlah * $rkas->harga_satuan,
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

    public function sisipkan(Request $request)
    {
        Log::info('Sisipkan RKAS Request:', $request->all());

        try {
            // Validasi manual untuk memastikan ID adalah angka
            if (! is_numeric($request->kode_id) || ! is_numeric($request->kode_rekening_id)) {
                Log::error('Invalid IDs received:', [
                    'kode_id' => $request->kode_id,
                    'kode_rekening_id' => $request->kode_rekening_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'ID kegiatan atau rekening tidak valid.',
                ], 422);
            }

            // Convert to integers
            $kodeId = (int) $request->kode_id;
            $rekeningId = (int) $request->kode_rekening_id;

            $request->merge([
                'kode_id' => $kodeId,
                'kode_rekening_id' => $rekeningId,
            ]);

            Log::info('After conversion:', [
                'kode_id' => $kodeId,
                'kode_rekening_id' => $rekeningId,
            ]);

            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string|max:500',
                'harga_satuan' => 'required|numeric|min:0',
                'jumlah' => 'required|integer|min:1',
                'satuan' => 'required|string|max:50',
                'tahun_anggaran' => 'required',
                'bulan' => 'required|string|in:Januari,Februari,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember',
            ]);

            Log::info('Validation passed');

            $penganggaran = Penganggaran::where('tahun_anggaran', $request->tahun_anggaran)->first();

            if (! $penganggaran) {
                Log::error('Penganggaran not found for year: ' . $request->tahun_anggaran);

                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran untuk tahun ' . $request->tahun_anggaran . ' tidak ditemukan.',
                ], 404);
            }

            Log::info('Penganggaran found: ' . $penganggaran->id);

            // Check for duplicate
            $exists = Rkas::where('penganggaran_id', $penganggaran->id)
                ->where('kode_id', $kodeId)
                ->where('kode_rekening_id', $rekeningId)
                ->where('bulan', $request->bulan)
                ->where('uraian', $request->uraian)
                ->exists();

            if ($exists) {
                Log::warning('Duplicate data found');

                return response()->json([
                    'success' => false,
                    'message' => "Data untuk bulan {$request->bulan} sudah ada dengan kegiatan dan rekening yang sama.",
                ], 422);
            }

            Log::info('Creating new RKAS record...');

            $rkas = Rkas::create([
                'penganggaran_id' => $penganggaran->id,
                'kode_id' => $kodeId,
                'kode_rekening_id' => $rekeningId,
                'uraian' => $request->uraian,
                'harga_satuan' => $request->harga_satuan,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'bulan' => $request->bulan,
            ]);

            Log::info('RKAS created successfully: ' . $rkas->id);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disisipkan ke bulan ' . $request->bulan,
                'bulan' => $request->bulan,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));

            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = implode(', ', $messages);
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errorMessages),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in sisipkan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            Log::info('🔧 [EDIT DEBUG] Fetching ALL monthly data for edit, ID: ' . $id);

            // Dapatkan data utama berdasarkan ID
            $mainRkas = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

            if (! $mainRkas) {
                Log::warning('❌ [EDIT DEBUG] Main data not found for ID: ' . $id);

                return response()->json([
                    'success' => false,
                    'message' => 'Data Rkas tidak ditemukan.',
                ], 400);
            }

            // Dapatkan Semua Data Dengan Kode_id, Kode_rekening_id, dan uraian yang sama
            $allRkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->where('harga_satuan', $mainRkas->harga_satuan)
                ->get();

            Log::info('🔧 [EDIT DEBUG] Found ' . $allRkasData->count() . ' monthly records for:', [
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'uraian' => $mainRkas->uraian,
            ]);

            // format data untuk semua bulan
            $bulanData = [];
            $totalAnggaran = 0;

            foreach ($allRkasData as $rkas) {
                $bulanData[] = [
                    'bulan' => $rkas->bulan,
                    'jumlah' => $rkas->jumlah,
                    'satuan' => $rkas->satuan,
                    'total' => $rkas->jumlah * $rkas->harga_satuan,
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
        try {
            Log::info('🔧 [UPDATE DEBUG] Starting update process for ID: ' . $id);
            Log::info('🔧 [UPDATE DEBUG] Request data:', [
                'bulan' => $request->bulan,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'harga_satuan' => $request->harga_satuan,
            ]);

            // Validasi data
            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string',
                'harga_satuan' => 'required|numeric|min:0',
                'bulan' => 'required|array',
                'bulan.*' => 'required|string|in:Januari,Februari,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember',
                'jumlah' => 'required|array',
                'jumlah.*' => 'required|integer|min:1',
                'satuan' => 'required|array',
                'satuan.*' => 'required|string',
            ]);

            // Dapatkan data utama
            $mainRkas = Rkas::find($id);
            if (! $mainRkas) {
                Log::error('❌ [UPDATE DEBUG] Data not found for ID: ' . $id);

                return response()->json([
                    'success' => false,
                    'message' => 'Data RKAS tidak ditemukan.',
                ], 404);
            }

            // Validasi duplikasi bulan
            if (count($request->bulan) !== count(array_unique($request->bulan))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak boleh ada bulan yang sama dalam satu kegiatan.',
                ], 422);
            }

            DB::beginTransaction();

            try {
                // HAPUS SEMUA DATA LAMA dengan kriteria yang sama
                $deletedCount = Rkas::where('penganggaran_id', $mainRkas->penganggaran_id)
                    ->where('kode_id', $request->kode_id)
                    ->where('kode_rekening_id', $request->kode_rekening_id)
                    ->where('uraian', $request->uraian)
                    ->where('harga_satuan', $mainRkas->harga_satuan) // Harga satuan lama
                    ->delete();

                Log::info('🔧 [UPDATE DEBUG] Deleted ' . $deletedCount . ' old records');

                // BUAT DATA BARU untuk semua bulan yang dipilih
                $createdRecords = [];
                $bulanArray = $request->bulan;
                $jumlahArray = $request->jumlah;
                $satuanArray = $request->satuan;

                Log::info('🔧 [UPDATE DEBUG] Creating new records for months:', $bulanArray);

                for ($i = 0; $i < count($bulanArray); $i++) {
                    $newRkas = Rkas::create([
                        'penganggaran_id' => $mainRkas->penganggaran_id,
                        'kode_id' => $request->kode_id,
                        'kode_rekening_id' => $request->kode_rekening_id,
                        'uraian' => $request->uraian,
                        'harga_satuan' => $request->harga_satuan,
                        'bulan' => $bulanArray[$i],
                        'jumlah' => $jumlahArray[$i],
                        'satuan' => $satuanArray[$i],
                    ]);

                    $createdRecords[] = $newRkas;
                    Log::info('🔧 [UPDATE DEBUG] Created record for bulan: ' . $bulanArray[$i]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data RKAS berhasil diupdate untuk ' . count($createdRecords) . ' bulan.',
                    'redirect' => route('rkas.index', ['tahun' => $request->tahun_anggaran]),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ [UPDATE DEBUG] Database error: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('❌ [UPDATE DEBUG] Error updating RKAS: ' . $e->getMessage());
            Log::error('❌ [UPDATE DEBUG] Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rkas = Rkas::findOrFail($id);
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

    public function deleteAll($id)
    {
        try {
            Log::info('🔧 [DELETE ALL] Starting delete all process for ID: ' . $id);

            // Dapat data utama
            $mainRkas = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

            if (! $mainRkas) {
                Log::error('❌ [DELETE ALL] Main data not found for ID: ' . $id);

                return response()->json([
                    'success' => false,
                    'message' => 'Data RKAS tidak ditemukan.',
                ], 404);
            }

            Log::info('🔧 [DELETE ALL] Found main data:', [
                'kode_id' => $mainRkas->kode_id,
                'kode_rekening_id' => $mainRkas->kode_rekening_id,
                'uraian' => $mainRkas->uraian,
            ]);

            DB::beginTransaction();

            // Hapus Data - GUNAKAN MODEL RKAS, BUKAN RKAS PERUBAHAN
            $deletedCount = Rkas::where('penganggaran_id', $mainRkas->penganggaran_id)
                ->where('kode_id', $mainRkas->kode_id)
                ->where('kode_rekening_id', $mainRkas->kode_rekening_id)
                ->where('uraian', $mainRkas->uraian)
                ->delete();

            DB::commit();

            Log::info('🔧 [DELETE ALL] Successfully deleted ' . $deletedCount . ' records');

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus ' . $deletedCount . ' data untuk kegiatan ini.',
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

            $rkasData = Rkas::with([
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
            $rkasData = $this->getAllRkasDataGroupedByMonth($penganggaran->id);

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
                $total = Rkas::where('bulan', $month)
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

            $totalTahap1 = Rkas::where('penganggaran_id', $penganggaran->id)
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

            $totalTahap2 = Rkas::where('penganggaran_id', $penganggaran->id)
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

            $rkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
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

    private function getAllRkasDataGroupedByMonth($penganggaranId)
    {
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

        $rkasData = [];

        foreach ($months as $month) {
            $rkasData[$month] = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaranId)
                ->where('bulan', $month)
                ->get();
        }

        return collect($rkasData);
    }

    // Method untuk generate PDF
    public function generatePdf(Request $request)
    {
        try {
            Log::info('Generate PDF RKA Tahapan dipanggil', [
                'request_data' => $request->all(),
                'tahun' => $request->input('tahun'),
                'ukuran_kertas' => $request->input('ukuran_kertas'),
                'orientasi' => $request->input('orientasi'),
                'font_size' => $request->input('font_size'),
            ]);
            // Ambil tahun dari parameter
            $tahun = $request->input('tahun');

            if (! $tahun) {
                Log::error('Parameter tahun tidak ditemukan dalam request');
                throw new \Exception('Parameter tahun diperlukan');
            }

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            if (! $penganggaran) {
                Log::error('Data penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                throw new \Exception('Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan');
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (! $sekolah) {
                throw new \Exception('Data sekolah belum tersedia');
            }

            // Ambil semua data RKAS dikelompokkan berdasarkan kode kegiatan - filter by penganggaran_id
            $rkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode;
                })
                ->filter(function ($group, $key) {
                    return ! is_null($key);
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

            // Data penerimaan - ambil dari penganggaran yang dipilih
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

            // Hitung total berdasarkan penganggaran_id
            $totalTahap1 = Rkas::getTotalTahap1($penganggaran->id);
            $totalTahap2 = Rkas::getTotalTahap2($penganggaran->id);

            // Organisasikan data RKAS
            $dataTerkelola = $this->kelolaDataRkas($rkasData);

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => $request->input('ukuran_kertas', 'A4'),
                'orientasi' => $request->input('orientasi', 'landscape'),
                'font_size' => $request->input('font_size', '8pt'),
            ];

            Log::info('Data untuk PDF RKA Tahapan siap', [
                'tahun' => $tahun,
                'total_tahap1' => $totalTahap1,
                'total_tahap2' => $totalTahap2,
                'print_settings' => $printSettings,
            ]);

            $pdf = PDF::loadView('rkas.rkas-tahap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $dataTerkelola,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'totalBelanja' => $totalTahap1 + $totalTahap2,
                'printSettings' => $printSettings,
                'penganggaran' => $penganggaran,
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

            return $pdf->stream('RKAS-Tahap-' . $tahun . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKAS: ' . $e->getMessage());

            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response('
            <html>
                <body>
                    <h1>Error Generating PDF RKA Tahapan</h1>
                    <p>Terjadi kesalahan saat membuat PDF: ' . $e->getMessage() . "</p>
                    <p>Silakan coba lagi atau hubungi administrator.</p>
                    <button onclick='window.history.back()'>Kembali</button>
                </body>
            </html>
            ", 500);
        }
    }

    private function kelolaDataRkas($rkasData)
    {
        $terorganisir = [];

        foreach ($rkasData as $kode => $items) {
            if (empty($items) || $items->isEmpty()) {
                continue;
            }

            $bagian = explode('.', $kode);

            // Level program (contoh: "03")
            $kodeProgram = $bagian[0];
            if (! isset($terorganisir[$kodeProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->program ?? '-',
                    'sub_programs' => [],
                    'total' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                    'jumlah' => 0, // Jumlah = tahap1 + tahap2
                ];
            }

            // Level sub-program (contoh: "03.03")
            $kodeSubProgram = count($bagian) > 1 ? $bagian[0] . '.' . $bagian[1] : null;
            if ($kodeSubProgram && ! isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->sub_program ?? '-',
                    'uraian_programs' => [],
                    'items' => [],
                    'total' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                    'jumlah' => 0, // Jumlah = tahap1 + tahap2
                ];
            }

            // Level uraian (contoh: "03.03.06")
            $kodeUraian = $kode;
            if ($kodeSubProgram && ! isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->uraian ?? '-',
                    'items' => [],
                    'total' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                    'jumlah' => 0, // Jumlah = tahap1 + tahap2
                ];
            }

            // Kelompokkan item berdasarkan kode_rekening dan uraian
            $groupedItems = [];
            foreach ($items as $item) {
                if (! $item->rekeningBelanja) {
                    continue;
                }

                $key = $item->rekeningBelanja->kode_rekening . '-' . $item->uraian;
                if (! isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                        'uraian' => $item->uraian,
                        'volume' => 0,
                        'satuan' => $item->satuan,
                        'harga_satuan' => $item->harga_satuan,
                        'jumlah' => 0, // Jumlah = tahap1 + tahap2
                        'tahap1' => 0,
                        'tahap2' => 0,
                    ];
                }

                $jumlah = $item->jumlah * $item->harga_satuan;
                $isTahap1 = in_array($item->bulan, Rkas::getBulanTahap1());

                $groupedItems[$key]['volume'] += $item->jumlah;
                $groupedItems[$key]['jumlah'] += $jumlah;

                if ($isTahap1) {
                    $groupedItems[$key]['tahap1'] += $jumlah;
                } else {
                    $groupedItems[$key]['tahap2'] += $jumlah;
                }
            }

            // Tambahkan item ke struktur data
            if ($kodeSubProgram) {
                foreach ($groupedItems as $item) {
                    // Update program
                    $terorganisir[$kodeProgram]['tahap1'] += $item['tahap1'];
                    $terorganisir[$kodeProgram]['tahap2'] += $item['tahap2'];
                    $terorganisir[$kodeProgram]['jumlah'] = $terorganisir[$kodeProgram]['tahap1'] + $terorganisir[$kodeProgram]['tahap2'];

                    // Update sub program
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['tahap1'] += $item['tahap1'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['tahap2'] += $item['tahap2'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['jumlah'] =
                        $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['tahap1'] +
                        $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['tahap2'];

                    // Update uraian program
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['tahap1'] += $item['tahap1'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['tahap2'] += $item['tahap2'];
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['jumlah'] =
                        $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['tahap1'] +
                        $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['tahap2'];

                    // Tambahkan item detail
                    $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['items'][] = $item;
                }
            }
        }

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

    public function showRekapan(Request $request)
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

            // Data untuk RKAS Tahapan - filter by penganggaran_id
            $belanja = $this->kelolaDataRkas(
                Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                    ->where('penganggaran_id', $penganggaran->id)
                    ->orderBy('kode_id')
                    ->get()
                    ->groupBy(function ($item) {
                        return optional($item->kodeKegiatan)->kode;
                    })
            );

            // Data indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => number_format($penganggaran->pagu_anggaran, 0, ',', '.')],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => ''],
            ];

            // Data untuk RKAS Tahapan - ambil berdasarkan penganggaran_id
            $dataTerkelola = $this->kelolaDataRkas(
                Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                    ->where('penganggaran_id', $penganggaran->id)
                    ->orderBy('kode_id')
                    ->get()
                    ->groupBy(function ($item) {
                        return optional($item->kodeKegiatan)->kode;
                    })
            );

            // Data untuk RKA Rekap - ambil berdasarkan penganggaran_id
            $rekapData = $this->getRekapRkas($penganggaran->id);

            // Data untuk Lembar Kerja 221 - ambil berdasarkan penganggaran_id
            [$groupedItemsFor221, $totalsFor221] = $this->prepare221Data($penganggaran->id);

            // Get selected month or default to January
            $bulan = request('bulan') ?? 'Januari';

            // Query data untuk bulan yang dipilih - berdasarkan penganggaran_id
            $rkasBulanan = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode ?? 'unknown';
                });

            $totalBulanan = Rkas::where('penganggaran_id', $penganggaran->id)
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
            $totalTahap1 = Rkas::getTotalTahap1($penganggaran->id);
            $totalTahap2 = Rkas::getTotalTahap2($penganggaran->id);

            // Debug: Log hasil perhitungan
            Log::info("Total Tahap 1: {$totalTahap1}, Total Tahap 2: {$totalTahap2}");

            return view('rkas.rekapan', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'rkasBulanan' => $rkasBulanan,
                'totalBulanan' => $totalBulanan,
                'months' => Rkas::getBulanList(),
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

        $rkasDetail = Rkas::with(['rekeningBelanja'])
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

    private function getRekapRkas($penganggaranId)
    {
        try {
            $penganggaran = Penganggaran::findOrFail($penganggaranId);
            $rkasData = Rkas::with(['rekeningBelanja'])
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

    public function generatePdfRkaRekap(Request $request, $tahun)
    {
        try {
            Log::info('Generate PDF RKA Rekap dipanggil', [
                'tahun' => $tahun,
                'ukuran_kertas' => $request->input('ukuran_kertas'),
                'orientasi' => $request->input('orientasi'),
                'font_size' => $request->input('font_size'),
            ]);

            // Validasi tahun
            if (! is_numeric($tahun)) {
                throw new \Exception('Tahun harus berupa angka');
            }
            // Ambil tahun dari parameter
            $tahun = (int) $tahun;

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            if (! $penganggaran) {
                Log::error('Data penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                throw new \Exception("Data penganggaran untuk tahun {$tahun} tidak ditemukan");
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (! $sekolah) {
                Log::error('Data sekolah tidak ditemukan');
                throw new \Exception('Data sekolah belum tersedia');
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
                'komite' => $penganggaran->komite,
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

            // Data belanja (rekap) - ambil berdasarkan penganggaran_id
            $rekapData = $this->getRekapRkas($penganggaran->id);

            // Data tahapan - ambil berdasarkan penganggaran_id
            $totalTahap1 = Rkas::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            $totalTahap2 = Rkas::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            // Format tanggal cetak
            $tanggalCetak = [
                'tanggal_cetak' => $penganggaran->format_tanggal_cetak ?? 'Belum diisi',
            ];

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => $request->input('ukuran_kertas', 'A4'),
                'orientasi' => $request->input('orientasi', 'portrait'),
                'font_size' => $request->input('font_size', '10pt'),
            ];

            Log::info('Data untuk PDF siap', [
                'total_tahap1' => $totalTahap1,
                'total_tahap2' => $totalTahap2,
                'rekap_data_count' => count($rekapData),
            ]);

            $pdf = PDF::loadView('rkas.rka-rekap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'rekapData' => $rekapData,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'tanggalCetak' => $tanggalCetak,
                'penganggaran' => $penganggaran,
                'printSettings' => $printSettings,
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

            return response('
            <html>
                <body>
                    <h1>Error Generating PDF</h1>
                    <p>Terjadi kesalahan saat membuat PDF: ' . $e->getMessage() . "</p>
                    <p>Silakan coba lagi atau hubungi administrator.</p>
                    <button onclick='window.history.back()'>Kembali</button>
                </body>
            </html>
        ", 500);
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
            $rkasData = Rkas::with(['rekeningBelanja'])
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

            return view('rkas.rekapan', [
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
                'font_size' => $request->input('font_size'),
            ]);

            // Ambil tahun dari parameter request
            $tahun = (int) $tahun;

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            if (! $penganggaran) {
                Log::error('Data penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                throw new \Exception("Data penganggaran untuk tahun {$tahun} tidak ditemukan");
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (! $sekolah) {
                Log::error('Data sekolah tidak ditemukan');
                throw new \Exception('Data sekolah belum tersedia');
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
            $rkasData = Rkas::with(['rekeningBelanja'])
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

                if (! isset($groupedItems[$mainCode])) {
                    $groupedItems[$mainCode] = [];
                }

                if (! isset($groupedItems[$mainCode][$itemKey])) {
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

            // Get print settings from request
            $printSettings = [
                'ukuran_kertas' => $request->input('ukuran_kertas', 'A4'),
                'orientasi' => $request->input('orientasi', 'portrait'),
                'font_size' => $request->input('font_size', '10pt'),
            ];

            Log::info('Data untuk PDF RKA 221 siap', [
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'grouped_items_count' => count($groupedItems),
                'print_settings' => $printSettings,
            ]);

            $pdf = PDF::loadView('rkas.rka-dua-satu-pdf', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'indikatorKinerja' => $indikatorKinerja,
                'mainStructure' => $mainStructure,
                'groupedItems' => $groupedItems,
                'totals' => $totals,
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'tahun_anggaran' => $penganggaran->tahun_anggaran,
                'printSettings' => $printSettings,
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

            return response('
            <html>
                <body>
                    <h1>Error Generating PDF RKA 221</h1>
                    <p>Terjadi kesalahan saat membuat PDF: ' . $e->getMessage() . "</p>
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

            $rkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
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

            $totalBulanan = Rkas::where('bulan', $bulan)
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

            if (! $sekolah || ! $penganggaran) {
                throw new \Exception('Data sekolah atau penganggaran tidak ditemukan');
            }

            // Ambil data RKAS hanya untuk tahun dan bulan yang diminta
            $rkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
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
                'font_size' => request()->input('font_size', '10pt'),
            ];

            Log::info('Generate PDF RKA Bulanan', [
                'tahun' => $tahun,
                'bulan' => $validBulan,
                'print_settings' => $printSettings,
                'total_belanja' => $totalBelanja,
            ]);

            $pdf = PDF::loadView('rkas.rka-bulanan-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $dataTerkelola,
                'totalBelanja' => $totalBelanja,
                'bulan' => $validBulan,
                'penganggaran' => $penganggaran,
                'printSettings' => $printSettings,
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
            $bulan = $request->input('bulan', 'Januari');
            $tahun = $request->input('tahun');

            // Ambil data penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            // Query data untuk bulan yang dipilih berdasarkan penganggaran_id
            $rkasBulanan = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode ?? 'unknown';
                });

            $totalBulanan = Rkas::where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->sum(DB::raw('jumlah * harga_satuan'));

            return response()->json([
                'success' => true,
                'html' => view('rkas.partials.monthly-data', [
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
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            // Fix: Gunakan whereBulan dengan nilai yang konsisten (huruf pertama kapital)
            $monthFormatted = ucfirst(strtolower($month));

            $data = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
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
            $bukuAnggaran = Rkas::where('penganggaran_id', $penganggaranId)
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
            $honorAnggaran = Rkas::where('penganggaran_id', $penganggaranId)
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
            $sarprasAnggaran = Rkas::where('penganggaran_id', $penganggaranId)
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
            $jenisBelanjaData = Rkas::where('penganggaran_id', $penganggaranId)
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

    // Di RkasController.php - tambahkan method baru

    /**
     * Check if previous year RKAS Perubahan exists
     */
    public function checkPreviousYearPerubahan(Request $request)
    {
        try {
            Log::info('🔍 [CHECK PREVIOUS PERUBAHAN] Checking for year: ' . $request->input('tahun'));

            $currentYear = $request->input('tahun');

            if (!$currentYear) {
                Log::warning('❌ [CHECK PREVIOUS PERUBAHAN] Tahun parameter missing');
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
                ], 400);
            }

            $previousYear = $currentYear - 1;

            Log::info('🔍 [CHECK PREVIOUS PERUBAHAN] Previous year: ' . $previousYear);

            // Cek apakah ada data penganggaran tahun sebelumnya
            $previousPenganggaran = Penganggaran::where('tahun_anggaran', $previousYear)->first();

            if (!$previousPenganggaran) {
                Log::info('🔍 [CHECK PREVIOUS PERUBAHAN] No penganggaran found for year: ' . $previousYear);
                return response()->json([
                    'success' => true, // Tetap success karena ini kondisi normal
                    'has_previous_perubahan' => false,
                    'message' => 'Data penganggaran tahun ' . $previousYear . ' tidak ditemukan'
                ]);
            }

            Log::info('🔍 [CHECK PREVIOUS PERUBAHAN] Penganggaran found, ID: ' . $previousPenganggaran->id);

            // Cek apakah ada RKAS Perubahan tahun sebelumnya
            $hasPreviousPerubahan = RkasPerubahan::where('penganggaran_id', $previousPenganggaran->id)->exists();

            Log::info('🔍 [CHECK PREVIOUS PERUBAHAN] Has previous perubahan: ' . ($hasPreviousPerubahan ? 'YES' : 'NO'));

            return response()->json([
                'success' => true,
                'has_previous_perubahan' => $hasPreviousPerubahan,
                'previous_year' => $previousYear,
                'current_year' => $currentYear,
                'message' => $hasPreviousPerubahan ?
                    'Data RKAS Perubahan tahun ' . $previousYear . ' tersedia' :
                    'Tidak ada data RKAS Perubahan tahun ' . $previousYear
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CHECK PREVIOUS PERUBAHAN] Error: ' . $e->getMessage());
            Log::error('❌ [CHECK PREVIOUS PERUBAHAN] Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy previous year RKAS Perubahan to current year
     */
    public function copyPreviousYearPerubahan(Request $request)
    {
        try {
            DB::beginTransaction();

            $currentYear = $request->input('tahun_anggaran');

            if (!$currentYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun anggaran diperlukan'
                ], 400);
            }

            $previousYear = $currentYear - 1;

            // Dapatkan data penganggaran
            $previousPenganggaran = Penganggaran::where('tahun_anggaran', $previousYear)->first();
            $currentPenganggaran = Penganggaran::where('tahun_anggaran', $currentYear)->first();

            if (!$previousPenganggaran || !$currentPenganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak lengkap'
                ], 404);
            }

            // Ambil semua data RKAS Perubahan tahun sebelumnya
            $previousPerubahanData = RkasPerubahan::where('penganggaran_id', $previousPenganggaran->id)->get();

            if ($previousPerubahanData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data RKAS Perubahan tahun ' . $previousYear . ' untuk disalin'
                ], 404);
            }

            $copiedCount = 0;

            foreach ($previousPerubahanData as $previousData) {
                // Cek apakah data sudah ada di tahun ini (berdasarkan kriteria unik)
                $exists = Rkas::where('penganggaran_id', $currentPenganggaran->id)
                    ->where('kode_id', $previousData->kode_id)
                    ->where('kode_rekening_id', $previousData->kode_rekening_id)
                    ->where('uraian', $previousData->uraian)
                    ->where('bulan', $previousData->bulan)
                    ->exists();

                if (!$exists) {
                    // Salin data ke RKAS tahun berjalan
                    Rkas::create([
                        'penganggaran_id' => $currentPenganggaran->id,
                        'kode_id' => $previousData->kode_id,
                        'kode_rekening_id' => $previousData->kode_rekening_id,
                        'uraian' => $previousData->uraian,
                        'harga_satuan' => $previousData->harga_satuan,
                        'jumlah' => $previousData->jumlah,
                        'satuan' => $previousData->satuan,
                        'bulan' => $previousData->bulan,
                    ]);

                    $copiedCount++;
                }
            }

            DB::commit();

            // Simpan status di session
            session()->put('salin_data_done_' . $currentYear, true);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menyalin ' . $copiedCount . ' data dari RKAS Perubahan tahun ' . $previousYear,
                'copied_count' => $copiedCount,
                'previous_year' => $previousYear,
                'current_year' => $currentYear
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error copying previous year perubahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyalin data: ' . $e->getMessage()
            ], 500);
        }
    }
}
