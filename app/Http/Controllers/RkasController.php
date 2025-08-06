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
                    return !is_null($key); // Filter out null keys
                });

            // Ambil data penganggaran terbaru
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();

            // Ambil data sekolah (harusnya hanya ada 1 data)
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

            // Data penerimaan dari penganggaran
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

            // Hitung total
            $totalTahap1 = Rkas::getTotalTahap1();
            $totalTahap2 = Rkas::getTotalTahap2();

            // Organisasikan data RKAS ke dalam struktur hierarkis
            $dataTerkelola = $this->kelolaDataRkas($rkasData);

            $pdf = PDF::loadView('penganggaran.rkas.rkas-tahap-pdf', [
                'dataSekolah' => $dataSekolah,
                'penerimaan' => $penerimaan,
                'belanja' => $dataTerkelola,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2,
                'totalBelanja' => $totalTahap1 + $totalTahap2
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
            // Ambil data penganggaran terbaru
            $penganggaran = Penganggaran::orderBy('tahun_anggaran', 'desc')->first();

            // Ambil data RKAS dengan relasi lengkap
            $rkasData = Rkas::with([
                'kodeKegiatan' => function ($query) {
                    $query->select('id', 'kode', 'program', 'sub_program', 'uraian');
                },
                'rekeningBelanja' => function ($query) {
                    $query->select('id', 'kode_rekening', 'rincian_objek');
                }
            ])
                ->orderBy('kode_id')
                ->get();

            // Organisasikan data RKAS ke dalam struktur hierarkis
            $dataTerkelola = $this->kelolaDataRkas($rkasData->groupBy(function ($item) {
                return optional($item->kodeKegiatan)->kode;
            }));

            // Hitung total tahap
            $totalTahap1 = Rkas::getTotalTahap1();
            $totalTahap2 = Rkas::getTotalTahap2();

            // Data untuk view
            $data = [
                'penerimaan' => [
                    'total' => $penganggaran ? $penganggaran->pagu_anggaran : 0,
                    'items' => [
                        [
                            'kode' => '4.3.1.01.',
                            'uraian' => 'BOS Reguler',
                            'jumlah' => $penganggaran ? $penganggaran->pagu_anggaran : 0
                        ]
                    ]
                ],
                'belanja' => $dataTerkelola,
                'totalTahap1' => $totalTahap1,
                'totalTahap2' => $totalTahap2
            ];

            return view('penganggaran.rkas.rekapan', $data);
        } catch (\Exception $e) {
            Log::error('Error showing rekapan: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menampilkan rekapan RKAS: ' . $e->getMessage());
        }
    }
}
