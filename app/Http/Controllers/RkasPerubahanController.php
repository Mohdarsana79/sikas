<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\RekamanPerubahan;
use App\Models\Penganggaran;
use App\Models\KodeKegiatan;
use App\Models\RekeningBelanja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;

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

            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
                ], 400);
            }

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::warning('Penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan untuk tahun ' . $tahun
                ], 404);
            }

            $perubahanExists = RkasPerubahan::where('penganggaran_id', $penganggaran->id)->exists();

            Log::info('Status perubahan untuk tahun ' . $tahun . ': ' . ($perubahanExists ? 'ADA' : 'TIDAK ADA'));

            return response()->json([
                'success' => true,
                'has_perubahan' => $perubahanExists,
                'tahun' => $tahun,
                'penganggaran_id' => $penganggaran->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checkStatusPerubahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memeriksa status perubahan.',
                'error' => $e->getMessage()
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

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran untuk tahun ' . $tahun . ' tidak ditemukan.'
                ], 404);
            }

            // Periksa apakah data perubahan untuk tahun ini sudah ada
            $perubahanExists = RkasPerubahan::where('penganggaran_id', $penganggaran->id)->exists();

            if ($perubahanExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon Maaf Anda Telah Melakukan Perubahan, RKAS Perubahan hanya dapat dilakukan sekali, Terima Kasih Atas Antusias Anda'
                ]);
            }

            // Ambil semua data RKAS asli
            $rkasAsli = Rkas::where('penganggaran_id', $penganggaran->id)->get();

            if ($rkasAsli->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data RKAS yang dapat disalin untuk tahun ' . $tahun
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
                'redirect' => route('rkas-perubahan.index', ['tahun' => $tahun])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menyalin data RKAS ke Perubahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data perubahan: ' . $e->getMessage()
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
            if (!$tahun) {
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
                'tahun'
            ));
        } catch (\Exception $e) {
            Log::error('Error di RkasPerubahanController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data RKAS Perubahan.');
        }
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

            if (!$penganggaran) {
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

                RkasPerubahan::create([
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
            $rkas = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

            if (!$rkas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data RKAS tidak ditemukan.'
                ], 404);
            }

            $data = [
                'id' => $rkas->id,
                'kode_id' => $rkas->kode_id,
                'kode_rekening_id' => $rkas->kode_rekening_id,
                'program_kegiatan' => $rkas->kodeKegiatan->program,
                'kegiatan' => $rkas->kodeKegiatan->sub_program,
                'rekening_belanja' => $rkas->rekeningBelanja->rincian_objek,
                'uraian' => $rkas->uraian,
                'bulan' => $rkas->bulan,
                'dianggaran' => $rkas->jumlah,
                'dibelanjakan' => 0,
                'satuan' => $rkas->satuan,
                'harga_satuan' => 'Rp ' . number_format($rkas->harga_satuan, 0, ',', '.'),
                'harga_satuan_raw' => $rkas->harga_satuan,
                'total' => 'Rp ' . number_format($rkas->jumlah * $rkas->harga_satuan, 0, ',', '.'),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing RKAS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $lockedMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        // Validasi bulan yang dikunci
        if (in_array($request->bulan, $lockedMonths)) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat mengupdate data untuk bulan {$request->bulan} karena bulan tersebut terkunci."
            ], 422);
        }
        try {
            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string',
                'harga_satuan' => 'nullable|numeric|min:0',
                'bulan' => 'required|in:Juli,Agustus,September,Oktober,November,Desember',
                'jumlah' => 'nullable|integer|min:1',
                'satuan' => 'nullable|string',
            ]);

            $rkasPerubahan = RkasPerubahan::findOrFail($id);

            // Check if combination already exists (excluding current record)
            $exists = RkasPerubahan::where('kode_id', $request->kode_id)
                ->where('kode_rekening_id', $request->kode_rekening_id)
                ->where('bulan', $request->bulan)
                ->where('uraian', $request->uraian)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => "Data untuk bulan {$request->bulan} sudah ada dengan kegiatan dan rekening yang sama."
                ], 422);
            }

            $rkasPerubahan->update([
                'kode_id' => $request->kode_id,
                'kode_rekening_id' => $request->kode_rekening_id,
                'uraian' => $request->uraian,
                'harga_satuan' => $request->harga_satuan,
                'bulan' => $request->bulan,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data RKAS Perubahan berhasil diupdate.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating RKAS Perubahan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rkas = RkasPerubahan::findOrFail($id);

            $rkas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data RKAS berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting RKAS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    public function getByMonth($bulan)
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            $rkasData = RkasPerubahan::with([
                'kodeKegiatan' => function ($query) {
                    $query->select('id', 'kode', 'program', 'sub_program', 'uraian');
                },
                'rekeningBelanja' => function ($query) {
                    $query->select('id', 'kode_rekening', 'rincian_objek');
                }
            ])
                ->where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->get();

            if ($rkasData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada data untuk bulan ' . $bulan . ' tahun ' . $tahun
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
                    'bulan' => $rkas->bulan
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting RKAS by month: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                'data' => $rkasData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all RKAS data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.'
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
                'Desember'
            ];

            $totals = [];
            foreach ($months as $month) {
                $total = RkasPerubahan::where('bulan', $month)
                    ->sum(DB::raw('jumlah * harga_satuan'));
                $totals[$month] = $total;
            }

            return response()->json([
                'success' => true,
                'data' => $totals
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting total per month: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total.'
            ], 500);
        }
    }

    public function getTotalTahap1()
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
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
                    'persentase_terpakai' => $paguAnggaranTahap1 > 0 ? ($totalTahap1 / $paguAnggaranTahap1) * 100 : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting total Tahap 1: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total Tahap 1.'
            ], 500);
        }
    }

    public function getTotalTahap2()
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
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
                    'persentase_terpakai' => $paguAnggaranTahap2 > 0 ? ($totalTahap2 / $paguAnggaranTahap2) * 100 : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting total Tahap 2: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung total Tahap 2.'
            ], 500);
        }
    }

    public function getDataByTahap($tahap)
    {
        try {
            // Dapatkan tahun dari request
            $tahun = request('tahun');

            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
                ], 400);
            }

            // Dapatkan penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
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
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting RKAS by tahap: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.'
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
            // Ambil tahun dari parameter
            $tahun = $request->input('tahun');

            if (!$tahun) {
                throw new \Exception("Parameter tahun diperlukan");
            }

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            if (!$penganggaran) {
                throw new \Exception("Data penganggaran belum tersedia");
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
                throw new \Exception("Data sekolah belum tersedia");
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
                'komite' => $penganggaran->komite
            ];

            // Data penerimaan
            $penerimaan = [
                'penganggaran' => $penganggaran,
                'total' => $penganggaran->pagu_anggaran,
                'items' => [
                    [
                        'kode' => '4.3.1.01.',
                        'uraian' => 'BOS Reguler',
                        'jumlah' => $penganggaran->pagu_anggaran
                    ]
                ]
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

            $pdf = PDF::loadView('rkas-perubahan.rkas-perubahan-tahap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $belanja,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'totalBertambah' => $totalBertambah,
                'totalBerkurang' => $totalBerkurang,
                'totalBelanja' => $totalTahap1 + $totalTahap2,
                'penganggaran' => $penganggaran
            ]);

            return $pdf->stream('RKAS-Perubahan-Tahap.pdf');
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKAS Perubahan: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghasilkan PDF RKAS Perubahan: ' . $e->getMessage());
        }
    }

    private function kelolaDataRkasPerubahan($rkasData)
    {
        $terorganisir = [];

        foreach ($rkasData as $kode => $items) {
            if (empty($items) || $items->isEmpty()) continue;

            $bagian = explode('.', $kode);

            // Level program
            $kodeProgram = $bagian[0];
            if (!isset($terorganisir[$kodeProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->program ?? '-',
                    'sub_programs' => [],
                    'total_asli' => 0,
                    'total_perubahan' => 0,
                    'bertambah' => 0,
                    'berkurang' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0
                ];
            }

            // Level sub-program
            $kodeSubProgram = count($bagian) > 1 ? $bagian[0] . '.' . $bagian[1] : null;
            if ($kodeSubProgram && !isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram])) {
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
                    'tahap2' => 0
                ];
            }

            // Level uraian
            $kodeUraian = $kode;
            if ($kodeSubProgram && !isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->uraian ?? '-',
                    'items' => [],
                    'total_asli' => 0,
                    'total_perubahan' => 0,
                    'bertambah' => 0,
                    'berkurang' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0
                ];
            }

            // Kelompokkan item berdasarkan kode_rekening dan uraian
            $groupedItems = [];
            foreach ($items as $item) {
                if (!$item->rekeningBelanja) continue;

                $key = $item->rekeningBelanja->kode_rekening . '-' . $item->uraian;

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

                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                        'uraian' => $item->uraian,
                        'volume_asli' => $volumeAsli,
                        'volume_perubahan' => $volumePerubahan,
                        'satuan' => $item->satuan,
                        'harga_satuan_asli' => $hargaSatuanAsli,
                        'harga_satuan_perubahan' => $hargaSatuanPerubahan,
                        'jumlah_asli' => $jumlahAsli,
                        'jumlah_perubahan' => $jumlahPerubahan,
                        'bertambah' => $bertambah,
                        'berkurang' => $berkurang,
                        'tahap1' => $tahap1,
                        'tahap2' => $tahap2
                    ];
                }
            }

            // Tambahkan item ke struktur data
            if ($kodeSubProgram) {
                foreach ($groupedItems as $item) {
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

        // Urutkan data
        ksort($terorganisir);
        foreach ($terorganisir as &$program) {
            if (!empty($program['sub_programs'])) {
                ksort($program['sub_programs']);
                foreach ($program['sub_programs'] as &$subProgram) {
                    if (!empty($subProgram['uraian_programs'])) {
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

            if (!$sekolah) {
                throw new \Exception("Data sekolah tidak ditemukan");
            }

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
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => '']
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
                        'jumlah' => $penganggaran->pagu_anggaran
                    ]
                ]
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
                    '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA'
                ],
                'groupedItems' => $groupedItemsFor221,
                'totals' => $totalsFor221,
                'total_anggaran' => $penganggaran->pagu_anggaran
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
            '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA'
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

            if (!isset($groupedItems[$mainCode][$key])) {
                $groupedItems[$mainCode][$key] = [
                    'kode_rekening' => $kode,
                    'uraian' => $item->uraian,
                    'volume' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah * $item->harga_satuan
                ];
            } else {
                $groupedItems[$mainCode][$key]['volume'] += $item->jumlah;
                $groupedItems[$mainCode][$key]['jumlah'] = $groupedItems[$mainCode][$key]['volume'] * $item->harga_satuan;
            }

            $totals[$mainCode] += $item->jumlah * $item->harga_satuan;
        }

        return [$groupedItems, $totals];
    }

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
                'jumlah' => $totalPendapatan
            ];

            // 2. BELANJA
            $rekapData[] = [
                'kode' => '5',
                'uraian' => 'BELANJA',
                'jumlah' => $belanjaTotal > 0 ? $belanjaTotal : '-'
            ];

            // 3. BELANJA OPERASI
            $rekapData[] = [
                'kode' => '5.1',
                'uraian' => 'BELANJA OPERASI',
                'jumlah' => $belanjaOperasi > 0 ? $belanjaOperasi : '-'
            ];

            // 4. BELANJA BARANG DAN JASA
            $rekapData[] = [
                'kode' => '5.1.02',
                'uraian' => 'BELANJA BARANG DAN JASA',
                'jumlah' => $belanjaBarangJasa > 0 ? $belanjaBarangJasa : '-'
            ];

            // 5. BELANJA BARANG
            $rekapData[] = [
                'kode' => '5.1.02.01',
                'uraian' => 'BELANJA BARANG',
                'jumlah' => $belanjaBarang > 0 ? $belanjaBarang : '-'
            ];

            // 6. BELANJA JASA
            $rekapData[] = [
                'kode' => '5.1.02.02',
                'uraian' => 'BELANJA JASA',
                'jumlah' => $belanjaJasa > 0 ? $belanjaJasa : '-'
            ];

            // 7. BELANJA PEMELIHARAAN
            $rekapData[] = [
                'kode' => '5.1.02.03',
                'uraian' => 'BELANJA PEMELIHARAAN',
                'jumlah' => $belanjaPemeliharaan > 0 ? $belanjaPemeliharaan : '-'
            ];

            // 8. BELANJA PERJALANAN DINAS
            $rekapData[] = [
                'kode' => '5.1.02.04',
                'uraian' => 'BELANJA PERJALANAN DINAS',
                'jumlah' => $belanjaPerjalanan > 0 ? $belanjaPerjalanan : '-'
            ];

            // 9. BELANJA MODAL
            $rekapData[] = [
                'kode' => '5.2',
                'uraian' => 'BELANJA MODAL',
                'jumlah' => $belanjaModal > 0 ? $belanjaModal : '-'
            ];

            // 10. BELANJA MODAL PERALATAN DAN MESIN
            $rekapData[] = [
                'kode' => '5.2.02',
                'uraian' => 'BELANJA MODAL PERALATAN DAN MESIN',
                'jumlah' => $belanjaModalPeralatan > 0 ? $belanjaModalPeralatan : '-'
            ];

            // 11. BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI
            $rekapData[] = [
                'kode' => '5.2.04',
                'uraian' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
                'jumlah' => $belanjaModalJalan > 0 ? $belanjaModalJalan : '-'
            ];

            // 12. BELANJA MODAL ASET TETAP LAINNYA
            $rekapData[] = [
                'kode' => '5.2.05',
                'uraian' => 'BELANJA MODAL ASET TETAP LAINNYA',
                'jumlah' => $belanjaModalAset > 0 ? $belanjaModalAset : '-'
            ];

            // 13. TOTAL BELANJA
            $rekapData[] = [
                'kode' => '',
                'uraian' => 'JUMLAH BELANJA',
                'jumlah' => $belanjaTotal
            ];

            // 14. DEFISIT
            $rekapData[] = [
                'kode' => '',
                'uraian' => 'DEFISIT',
                'jumlah' => $defisit
            ];

            return $rekapData;
        } catch (\Exception $e) {
            Log::error('Error getting rekap RKAS: ' . $e->getMessage());
            return [];
        }
    }

    public function generatePdfRkaRekap(Request $request)
    {
        try {
            // Ambil tahun dari parameter
            $tahun = $request->input('tahun');

            if (!$tahun) {
                throw new \Exception("Parameter tahun diperlukan");
            }

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            if (!$penganggaran) {
                throw new \Exception("Data penganggaran belum tersedia");
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
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
                ->sum(DB::raw('jumlah * harga_satuan'));

            $totalTahap2 = RkasPerubahan::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                ->sum(DB::raw('jumlah * harga_satuan'));

            // Format tanggal cetak
            $tanggalPerubahan = [
                'tanggal_perubahan' => $penganggaran->format_tanggal_perubahan ?? 'Belum diisi'
            ];

            $pdf = PDF::loadView('rkas-perubahan.rka-rekap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'rekapData' => $rekapData,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'tanggalPerubahan' => $tanggalPerubahan,
                'penganggaran' => $penganggaran
            ]);

            return $pdf->stream('RKA-Rekap.pdf');
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKA Rekap: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghasilkan PDF RKA Rekap: ' . $e->getMessage());
        }
    }

    public function rekapRkaDuaSatu()
    {
        try {
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $sekolah = Sekolah::first();

            if (!$penganggaran || !$sekolah) {
                throw new \Exception("Data penganggaran atau sekolah tidak ditemukan");
            }

            // Data indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => number_format($penganggaran->pagu_anggaran, 0, ',', '.')],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => '']
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
                '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA'
            ];

            // Kelompokkan item berdasarkan kode utama
            $groupedItems = [];
            foreach ($rkasData as $item) {
                $kode = $item->rekeningBelanja->kode_rekening;

                // Temukan kode utama terdekat
                $mainCode = $this->findClosestMainCode($kode, array_keys($mainStructure));

                if (!isset($groupedItems[$mainCode])) {
                    $groupedItems[$mainCode] = [];
                }

                $groupedItems[$mainCode][] = [
                    'kode_rekening' => $kode,
                    'uraian' => $item->uraian,
                    'volume' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah * $item->harga_satuan
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
                'tahun_anggaran' => $penganggaran->tahun_anggaran
            ]);
        } catch (\Exception $e) {
            Log::error('Error in rekapRkaDuaSatu: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data Lembar Kerja 221.');
        }
    }

    public function generateRkaDuaSatuPdf(Request $request)
    {
        try {
            // Ambil tahun dari parameter request
            $tahun = $request->input('tahun');

            if (!$tahun) {
                throw new \Exception("Parameter tahun diperlukan");
            }

            // Ambil data penganggaran berdasarkan tahun yang dipilih
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah || !$penganggaran) {
                throw new \Exception("Data sekolah atau penganggaran tidak ditemukan");
            }

            // Data untuk tabel indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => $penganggaran->pagu_anggaran],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => 0]
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
                '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA'
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
                        'jumlah' => $item->jumlah * $hargaSatuan
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

            $pdf = PDF::loadView('rkas-perubahan.rka-dua-satu-pdf', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'indikatorKinerja' => $indikatorKinerja,
                'mainStructure' => $mainStructure,
                'groupedItems' => $groupedItems,
                'totals' => $totals,
                'total_anggaran' => $penganggaran->pagu_anggaran,
                'tahun_anggaran' => $penganggaran->tahun_anggaran
            ]);

            return $pdf->stream('RKA-221.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating RKA 221 PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghasilkan PDF RKA 221: ' . $e->getMessage());
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
            Log::info("Mengambil data rekap untuk bulan: " . $bulan);

            $rkasData = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('bulan', $bulan)
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode;
                })
                ->filter(function ($group, $key) {
                    return !is_null($key);
                });

            $dataTerkelola = $this->kelolaDataRkasBulanan($rkasData, $bulan);

            $totalBulanan = RkasPerubahan::where('bulan', $bulan)
                ->sum(DB::raw('jumlah * harga_satuan'));

            return response()->json([
                'success' => true,
                'data' => $dataTerkelola,
                'total' => $totalBulanan,
                'bulan' => $bulan
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting rekap bulanan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data bulanan.'
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
            if (empty($items) || $items->isEmpty()) continue;

            $bagian = explode('.', $kode);
            $kodeProgram = $bagian[0] ?? '';

            if (!isset($terorganisir[$kodeProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->program ?? '-',
                    'sub_programs' => [],
                    'total' => 0
                ];
            }

            $kodeSubProgram = count($bagian) > 1 ? $bagian[0] . '.' . $bagian[1] : null;
            if ($kodeSubProgram && !isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->sub_program ?? '-',
                    'uraian_programs' => [],
                    'items' => [],
                    'total' => 0
                ];
            }

            $kodeUraian = $kode;
            if ($kodeSubProgram && !isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->uraian ?? '-',
                    'items' => [],
                    'total' => 0
                ];
            }

            foreach ($items as $item) {
                if (!$item->rekeningBelanja) continue;

                $jumlah = $item->jumlah * $item->harga_satuan;

                $itemData = [
                    'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                    'uraian' => $item->uraian,
                    'volume' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $jumlah,
                    'bulan' => $item->bulan
                ];

                $terorganisir[$kodeProgram]['total'] += $jumlah;
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['total'] += $jumlah;
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['total'] += $jumlah;

                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian]['items'][] = $itemData;
            }
        }

        ksort($terorganisir);
        foreach ($terorganisir as &$program) {
            if (!empty($program['sub_programs'])) {
                ksort($program['sub_programs']);
                foreach ($program['sub_programs'] as &$subProgram) {
                    if (!empty($subProgram['uraian_programs'])) {
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
                'Desember'
            ]) ? $bulan : 'Januari';

            // Ambil data penganggaran berdasarkan tahun yang diminta
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->firstOrFail();
            $sekolah = Sekolah::first();

            if (!$sekolah || !$penganggaran) {
                throw new \Exception("Data sekolah atau penganggaran tidak ditemukan");
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
                'bulan' => $validBulan
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

            // Organisasikan data RKAS
            $dataTerkelola = $this->kelolaDataRkasBulanan($rkasData, $validBulan);

            $totalBelanja = $rkasData->sum(function ($group) {
                return $group->sum(function ($item) {
                    return $item->jumlah * $item->harga_satuan;
                });
            });

            $pdf = PDF::loadView('rkas-perubahan.rka-bulanan-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $dataTerkelola,
                'totalBelanja' => $totalBelanja,
                'bulan' => $validBulan,
                'penganggaran' => $penganggaran
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

            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
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
                    'totalBulanan' => $totalBulanan
                ])->render(),
                'totalBulanan' => $totalBulanan
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting monthly data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data bulanan.'
            ], 500);
        }
    }

    public function getDataByMonth($month)
    {
        try {
            $tahun = request()->query('tahun');
            if (!$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tahun diperlukan'
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
                        'kode_rekening_id' => $item->kode_rekening_id
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $data,
                'month' => $monthFormatted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}
