<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use App\Models\KodeKegiatan;
use App\Models\RekeningBelanja;
use App\Models\Penganggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sekolah;

class RkasController extends Controller
{
    public function index()
    {
        try {
            $kodeKegiatans = KodeKegiatan::all();
            $rekeningBelanjas = RekeningBelanja::all();

            // Get all RKAS data grouped by month
            $rkasData = $this->getAllRkasDataGroupedByMonth();

            // Calculate total budget
            $totalBudget = Rkas::sum(DB::raw('jumlah * harga_satuan'));

            // Calculate total budget for Tahap 1 (Jan-Jun)
            $totalTahap1 = Rkas::getTotalTahap1();

            // Calculate total budget for Tahap 2 (Jul-Dec)
            $totalTahap2 = Rkas::getTotalTahap2();

            // Get latest penganggaran data
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();

            // Calculate 50% of pagu anggaran for Tahap 1 and Tahap 2
            $paguAnggaranTahap1 = $penganggaran ? ($penganggaran->pagu_anggaran * 0.5) : 0;
            $paguAnggaranTahap2 = $penganggaran ? ($penganggaran->pagu_anggaran * 0.5) : 0;

            return view('penganggaran.rkas.rkas', compact(
                'kodeKegiatans',
                'rekeningBelanjas',
                'rkasData',
                'totalBudget',
                'penganggaran',
                'totalTahap1',
                'totalTahap2',
                'paguAnggaranTahap1',
                'paguAnggaranTahap2',
            ));
        } catch (\Exception $e) {
            Log::error('Error in RKAS index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data RKAS.');
        }
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
                $exists = Rkas::where('kode_id', $request->kode_id)
                    ->where('kode_rekening_id', $request->kode_rekening_id)
                    ->where('bulan', $bulanArray[$i])
                    ->where('uraian', $request->uraian)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return back()->withErrors(['bulan' => "Data untuk bulan {$bulanArray[$i]} sudah ada dengan kegiatan dan rekening yang sama."]);
                }

                Rkas::create([
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
            return redirect()->route('penganggaran.rkas.index')->with('success', 'Data RKAS berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing RKAS: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    public function show($id)
    {
        try {
            $rkas = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])->find($id);

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
        try {
            $request->validate([
                'kode_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'uraian' => 'required|string',
                'harga_satuan' => 'required|numeric|min:0',
                'bulan' => 'required|string',
                'jumlah' => 'required|integer|min:1',
                'satuan' => 'required|string',
            ]);

            $rkas = Rkas::findOrFail($id);

            // Check if combination already exists (excluding current record)
            $exists = Rkas::where('kode_id', $request->kode_id)
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

            $rkas->update([
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
                'message' => 'Data RKAS berhasil diupdate.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating RKAS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data.'
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
            $rkasData = Rkas::with([
                'kodeKegiatan' => function ($query) {
                    $query->select('id', 'kode', 'program', 'sub_program', 'uraian');
                },
                'rekeningBelanja' => function ($query) {
                    $query->select('id', 'kode_rekening', 'rincian_objek');
                }
            ])
                ->where('bulan', $bulan)
                ->get();

            if ($rkasData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada data untuk bulan ' . $bulan
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
            $rkasData = $this->getAllRkasDataGroupedByMonth();

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
                $total = Rkas::where('bulan', $month)
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
            $totalTahap1 = Rkas::getTotalTahap1();
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $paguAnggaranTahap1 = $penganggaran ? ($penganggaran->pagu_anggaran * 0.5) : 0;

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
            $totalTahap2 = Rkas::getTotalTahap2();
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $paguAnggaranTahap2 = $penganggaran ? ($penganggaran->pagu_anggaran * 0.5) : 0;

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
            $rkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
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

    private function getAllRkasDataGroupedByMonth()
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
            'Desember'
        ];

        $rkasData = [];

        foreach ($months as $month) {
            $rkasData[$month] = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->where('bulan', $month)
                ->get();
        }

        return collect($rkasData);
    }

    // Method untuk generate PDF
    public function generatePdf()
    {
        try {
            // Ambil semua data RKAS dikelompokkan berdasarkan kode kegiatan
            $rkasData = Rkas::with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('kode_id')
                ->get()
                ->groupBy(function ($item) {
                    return optional($item->kodeKegiatan)->kode;
                })
                ->filter(function ($group, $key) {
                    return !is_null($key);
                });

            // Ambil data penganggaran terbaru
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
                throw new \Exception("Data sekolah belum tersedia");
            }

            if (!$penganggaran) {
                throw new \Exception("Data penganggaran belum tersedia");
            }

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

            // Hitung total
            $totalTahap1 = Rkas::getTotalTahap1();
            $totalTahap2 = Rkas::getTotalTahap2();

            // Organisasikan data RKAS
            $dataTerkelola = $this->kelolaDataRkas($rkasData);

            // Format tanggal cetak menggunakan accessor dari model
            $tanggalCetak = [
                'tanggal_cetak' => $penganggaran->format_tanggal_cetak // Menggunakan accessor
            ];

            $pdf = PDF::loadView('penganggaran.rkas.rkas-tahap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $dataTerkelola,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'totalBelanja' => $totalTahap1 + $totalTahap2,
                'tanggalCetak' => $tanggalCetak,
                'penganggaran' => $penganggaran
            ]);

            return $pdf->stream('RKAS-Tahap.pdf');
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF RKAS: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghasilkan PDF RKAS: ' . $e->getMessage());
        }
    }

    private function kelolaDataRkas($rkasData)
    {
        $terorganisir = [];

        foreach ($rkasData as $kode => $items) {
            if (empty($items) || $items->isEmpty()) continue;

            $bagian = explode('.', $kode);

            // Level program (contoh: "03")
            $kodeProgram = $bagian[0];
            if (!isset($terorganisir[$kodeProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->program ?? '-',
                    'sub_programs' => [],
                    'total' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                    'jumlah' => 0 // Jumlah = tahap1 + tahap2
                ];
            }

            // Level sub-program (contoh: "03.03")
            $kodeSubProgram = count($bagian) > 1 ? $bagian[0] . '.' . $bagian[1] : null;
            if ($kodeSubProgram && !isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->sub_program ?? '-',
                    'uraian_programs' => [],
                    'items' => [],
                    'total' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                    'jumlah' => 0 // Jumlah = tahap1 + tahap2
                ];
            }

            // Level uraian (contoh: "03.03.06")
            $kodeUraian = $kode;
            if ($kodeSubProgram && !isset($terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian])) {
                $firstItem = $items->first();
                $terorganisir[$kodeProgram]['sub_programs'][$kodeSubProgram]['uraian_programs'][$kodeUraian] = [
                    'uraian' => optional($firstItem->kodeKegiatan)->uraian ?? '-',
                    'items' => [],
                    'total' => 0,
                    'tahap1' => 0,
                    'tahap2' => 0,
                    'jumlah' => 0 // Jumlah = tahap1 + tahap2
                ];
            }

            // Kelompokkan item berdasarkan kode_rekening dan uraian
            $groupedItems = [];
            foreach ($items as $item) {
                if (!$item->rekeningBelanja) continue;

                $key = $item->rekeningBelanja->kode_rekening . '-' . $item->uraian;
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'kode_rekening' => $item->rekeningBelanja->kode_rekening,
                        'uraian' => $item->uraian,
                        'volume' => 0,
                        'satuan' => $item->satuan,
                        'harga_satuan' => $item->harga_satuan,
                        'jumlah' => 0, // Jumlah = tahap1 + tahap2
                        'tahap1' => 0,
                        'tahap2' => 0
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

    public function showRekapan()
    {
        try {
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $sekolah = Sekolah::first();

            if (!$penganggaran || !$sekolah) {
                throw new \Exception("Data penganggaran atau sekolah tidak ditemukan");
            }

            // Data indikator kinerja untuk semua tab
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => number_format($penganggaran->pagu_anggaran, 0, ',', '.')],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => ''],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => '']
            ];

            // Data untuk RKAS Tahapan (tanpa perubahan)
            $rkasData = Rkas::with([
                'kodeKegiatan' => function ($query) {
                    $query->select('id', 'kode', 'program', 'sub_program', 'uraian');
                },
                'rekeningBelanja' => function ($query) {
                    $query->select('id', 'kode_rekening', 'rincian_objek');
                }
            ])->orderBy('kode_id')->get();

            $dataTerkelola = $this->kelolaDataRkas($rkasData->groupBy(function ($item) {
                return optional($item->kodeKegiatan)->kode;
            }));

            // Data untuk RKA Rekap (tanpa perubahan)
            $rekapData = $this->getRekapRkas();

            // Data untuk Lembar Kerja 221 (dengan penggabungan)
            $rkasDetail = Rkas::with(['rekeningBelanja'])
                ->orderBy('kode_rekening_id')
                ->get();

            // Struktur untuk Lembar Kerja 221
            $mainStructure = [
                '5' => 'BELANJA',
                '5.1' => 'BELANJA OPERASI',
                '5.1.02' => 'BELANJA BARANG DAN JASA',
                '5.2' => 'BELANJA MODAL',
                '5.2.02' => 'BELANJA MODAL PERALATAN DAN MESIN',
                '5.2.04' => 'BELANJA MODAL JALAN, JARINGAN, DAN IRIGASI',
                '5.2.05' => 'BELANJA MODAL ASET TETAP LAINNYA'
            ];

            // Kelompokkan item untuk RKA 221 dengan menggabungkan yang sama
            $groupedItemsFor221 = [];
            foreach ($rkasDetail as $item) {
                $kode = $item->rekeningBelanja->kode_rekening;
                $uraian = $item->uraian;
                $hargaSatuan = $item->harga_satuan;
                $mainCode = $this->findClosestMainCode($kode, array_keys($mainStructure));

                $key = $kode . '-' . $uraian . '-' . $hargaSatuan;

                if (!isset($groupedItemsFor221[$mainCode])) {
                    $groupedItemsFor221[$mainCode] = [];
                }

                if (!isset($groupedItemsFor221[$mainCode][$key])) {
                    $groupedItemsFor221[$mainCode][$key] = [
                        'kode_rekening' => $kode,
                        'uraian' => $uraian,
                        'volume' => $item->jumlah,
                        'satuan' => $item->satuan,
                        'harga_satuan' => $hargaSatuan,
                        'jumlah' => $item->jumlah * $hargaSatuan
                    ];
                } else {
                    // Gabungkan volume dan hitung ulang jumlah
                    $groupedItemsFor221[$mainCode][$key]['volume'] += $item->jumlah;
                    $groupedItemsFor221[$mainCode][$key]['jumlah'] = $groupedItemsFor221[$mainCode][$key]['volume'] * $hargaSatuan;
                }
            }

            // Hitung total per kode utama untuk RKA 221
            $totalsFor221 = [];
            foreach ($mainStructure as $kode => $uraian) {
                $totalsFor221[$kode] = 0;
                if (isset($groupedItemsFor221[$kode])) {
                    foreach ($groupedItemsFor221[$kode] as $item) {
                        $totalsFor221[$kode] += $item['jumlah'];
                    }
                }
            }

            // Hitung total tahap
            $totalTahap1 = Rkas::getTotalTahap1();
            $totalTahap2 = Rkas::getTotalTahap2();

            return view('penganggaran.rkas.rekapan', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'indikatorKinerja' => $indikatorKinerja,
                'penerimaan' => [
                    'total' => $penganggaran->pagu_anggaran,
                    'items' => [
                        [
                            'kode' => '4.3.1.01.',
                            'uraian' => 'BOS Reguler',
                            'jumlah' => $penganggaran->pagu_anggaran
                        ]
                    ]
                ],
                'belanja' => $dataTerkelola,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'rekapData' => $rekapData,
                // Data khusus untuk Lembar Kerja 221
                'mainStructure' => $mainStructure,
                'groupedItems' => $groupedItemsFor221,
                'totals' => $totalsFor221,
                'total_anggaran' => $penganggaran->pagu_anggaran
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing rekapan: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menampilkan rekapan RKAS: ' . $e->getMessage());
        }
    }

    private function getRekapRkas()
    {
        try {
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $rkasData = Rkas::with(['rekeningBelanja'])->get();

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

    public function generatePdfRkaRekap()
    {
        try {
            // Ambil data penganggaran terbaru
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            if (!$sekolah) {
                throw new \Exception("Data sekolah belum tersedia");
            }

            if (!$penganggaran) {
                throw new \Exception("Data penganggaran belum tersedia");
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

            // Data belanja (rekap)
            $rekapData = $this->getRekapRkas();

            // Data tahapan
            $totalTahap1 = Rkas::getTotalTahap1();
            $totalTahap2 = Rkas::getTotalTahap2();

            // Format tanggal cetak
            $tanggalCetak = [
                'tanggal_cetak' => $penganggaran->format_tanggal_cetak
            ];

            $pdf = PDF::loadView('penganggaran.rkas.rka-rekap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'rekapData' => $rekapData,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'tanggalCetak' => $tanggalCetak,
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

            return view('penganggaran.rkas.rekapan', [
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

    public function generateRkaDuaSatuPdf()
    {
        try {
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();
            $sekolah = Sekolah::first();

            if (!$penganggaran || !$sekolah) {
                throw new \Exception("Data penganggaran atau sekolah tidak ditemukan");
            }

            // Data untuk tabel indikator kinerja
            $indikatorKinerja = [
                ['indikator' => 'Capaian Program', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Masukan', 'tolok_ukur' => 'Dana', 'target' => $penganggaran->pagu_anggaran],
                ['indikator' => 'Keluaran', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Hasil', 'tolok_ukur' => '', 'target' => 0],
                ['indikator' => 'Sasaran Keg', 'tolok_ukur' => '', 'target' => 0]
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

            $pdf = PDF::loadView('penganggaran.rkas.rka-dua-satu-pdf', [
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'indikatorKinerja' => $indikatorKinerja,
                'mainStructure' => $mainStructure,
                'groupedItems' => $groupedItems,
                'totals' => $totals,
                'total_anggaran' => $penganggaran->pagu_anggaran
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
}
