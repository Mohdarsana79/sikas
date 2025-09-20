<?php

namespace App\Http\Controllers;

use App\Models\BukuKasUmum;
use App\Models\Penganggaran;
use App\Models\Rkas;
use App\Models\RkasPerubahan;
use App\Models\PenarikanTunai;
use App\Models\SetorTunai;
use App\Models\PenerimaanDana;
use App\Models\RekeningBelanja;
use App\Models\BukuKasUmumUraianDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class BukuKasUmumController extends Controller
{
    // Di file BukuKasUmumController.php
    private function hitungSaldoTunaiNonTunai($penganggaran_id)
    {
        $totalDanaTersedia = $this->hitungTotalDanaTersedia($penganggaran_id);

        $totalPenarikan = PenarikanTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_penarikan');
        $totalSetor = SetorTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_setor');

        // Hitung total yang sudah dibelanjakan (baik tunai maupun non-tunai)
        $totalDibelanjakan = BukuKasUmum::where('penganggaran_id', $penganggaran_id)->sum('dibelanjakan');

        // Hitung saldo tunai: penarikan - setoran - belanja tunai
        $totalBelanjaTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
            ->where('jenis_transaksi', 'tunai')
            ->sum('dibelanjakan');

        $saldoTunai = $totalPenarikan - $totalSetor - $totalBelanjaTunai;

        // Hitung saldo non-tunai: total dana - saldo tunai - total dibelanjakan
        $saldoNonTunai = $totalDanaTersedia - $saldoTunai - $totalDibelanjakan;

        return [
            'tunai' => max(0, $saldoTunai),
            'non_tunai' => max(0, $saldoNonTunai),
            'total_dana_tersedia' => max(0, $totalDanaTersedia - $totalDibelanjakan)
        ];
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

        if (!$penganggaran) {
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

        // Ambil data BKU untuk bulan tersebut
        $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
            ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
            ->with(['kodeKegiatan', 'rekeningBelanja', 'uraianDetails'])
            ->get();

        $data = [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'penganggaran' => $penganggaran,
            'totalDanaTersedia' => $totalDanaTersedia,
            'saldoTunai' => $saldo['tunai'],
            'saldoNonTunai' => $saldo['non_tunai'],
            'anggaranBulanIni' => $anggaranBulanIni,
            'totalDibelanjakanBulanIni' => $totalDibelanjakanBulanIni,
            'totalDibelanjakanSampaiBulanIni' => $totalDibelanjakanSampaiBulanIni,
            'anggaranBelumDibelanjakan' => $anggaranBelumDibelanjakan,
            'bkuData' => $bkuData
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
            Log::error('Error menghitung total dana tersedia: ' . $e->getMessage());
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
                'formatted_total' => 'Rp ' . number_format($totalDana, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dana tersedia: ' . $e->getMessage()
            ], 500);
        }
    }

    // PERBAIKAN V2: ambil kegiatan dan rekening - dengan debug dan struktur yang diperbaiki
    public function getKegiatanDanRekening($tahun, $bulan)
    {
        try {
            Log::info('=== DEBUG getKegiatanDanRekening ===', [
                'tahun' => $tahun,
                'bulan' => $bulan
            ]);

            // Cari data penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::warning('Penganggaran tidak ditemukan', ['tahun' => $tahun]);
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            Log::info('Penganggaran ditemukan', ['id' => $penganggaran->id]);

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            Log::info('Model yang digunakan', ['model' => $model, 'isTahap1' => $isTahap1]);

            // Dapatkan semua bulan sampai bulan target
            $bulanList = [
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

            $bulanIndex = array_search($bulan, $bulanList);
            $bulanSampaiTarget = array_slice($bulanList, 0, $bulanIndex + 1);

            if ($bulanIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulan tidak valid'
                ], 400);
            }

            Log::info('Bulan sampai target', ['bulan_sampai_target' => $bulanSampaiTarget]);

            // Ambil data RKAS untuk semua bulan sampai target
            $rkasData = $model::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', $bulanSampaiTarget)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get();

            Log::info('Data RKAS ditemukan', ['count' => $rkasData->count()]);

            if ($rkasData->isEmpty()) {
                Log::warning('Tidak ada data RKAS ditemukan');
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'kegiatan_list' => [],
                    'rekening_list' => [],
                    'message' => 'Tidak ada data RKAS untuk periode ini'
                ]);
            }

            // Ambil data BKU yang sudah dibelanjakan untuk bulan-bulan sebelumnya
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
                'Desember' => 12
            ];

            $bulanTargetNumber = $bulanAngkaList[$bulan];

            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTargetNumber])
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get();

            Log::info('Data BKU ditemukan', ['count' => $bkuData->count()]);

            // PERBAIKAN V2: Struktur data yang lebih sederhana dan debug-friendly
            $kegiatanList = [];
            $rekeningList = [];

            // Kelompokkan data berdasarkan kode kegiatan
            $groupedData = $rkasData->groupBy('kode_id')->map(function ($items) use ($bkuData, &$kegiatanList, &$rekeningList) {
                $kegiatan = $items->first()->kodeKegiatan;

                Log::info('Processing kegiatan', [
                    'kegiatan_id' => $kegiatan->id,
                    'kegiatan_kode' => $kegiatan->kode,
                    'kegiatan_uraian' => $kegiatan->uraian
                ]);

                // Tambahkan ke daftar kegiatan jika belum ada
                if (!isset($kegiatanList[$kegiatan->id])) {
                    $kegiatanList[$kegiatan->id] = [
                        'id' => $kegiatan->id,
                        'kode' => $kegiatan->kode,
                        'program' => $kegiatan->program,
                        'sub_program' => $kegiatan->sub_program,
                        'uraian' => $kegiatan->uraian,
                        'rekening_belanja' => []
                    ];
                }

                // Kelompokkan rekening belanja by kode_rekening_id untuk menghindari duplikasi
                $rekeningGrouped = $items->groupBy('kode_rekening_id')->map(function ($rekeningItems) use ($bkuData, $kegiatan, &$rekeningList) {
                    $firstItem = $rekeningItems->first();

                    Log::info('Processing rekening', [
                        'rekening_id' => $firstItem->kode_rekening_id,
                        'rekening_kode' => $firstItem->rekeningBelanja->kode_rekening ?? 'N/A',
                        'rekening_rincian' => $firstItem->rekeningBelanja->rincian_objek ?? 'N/A'
                    ]);

                    // Hitung total yang sudah dibelanjakan untuk rekening ini di bulan sebelumnya
                    $sudahDibelanjakan = $bkuData->where('kode_rekening_id', $firstItem->kode_rekening_id)
                        ->where('kode_kegiatan_id', $kegiatan->id) // Filter juga berdasarkan kegiatan
                        ->sum('dibelanjakan');

                    // Hitung total anggaran untuk rekening ini (sum dari semua bulan)
                    $totalAnggaran = $rekeningItems->sum(function ($item) {
                        return $item->harga_satuan * $item->jumlah;
                    });

                    $sisaAnggaran = $totalAnggaran - $sudahDibelanjakan;

                    Log::info('Rekening calculation', [
                        'total_anggaran' => $totalAnggaran,
                        'sudah_dibelanjakan' => $sudahDibelanjakan,
                        'sisa_anggaran' => $sisaAnggaran
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
                            'sisa_anggaran' => $sisaAnggaran
                        ];

                        // Tambahkan ke daftar rekening global
                        $rekeningList[] = $rekeningData;

                        return $rekeningData;
                    }

                    return null;
                })->filter()->values();

                // Update daftar rekening untuk kegiatan ini
                $kegiatanList[$kegiatan->id]['rekening_belanja'] = $rekeningGrouped->toArray();

                return [
                    'kegiatan' => $kegiatan,
                    'rekening_belanja' => $rekeningGrouped
                ];
            })->filter(function ($kegiatan) {
                // Hanya tampilkan kegiatan yang masih memiliki rekening dengan sisa anggaran
                return count($kegiatan['rekening_belanja']) > 0;
            })->values();

            Log::info('Final result', [
                'kegiatan_count' => count($kegiatanList),
                'rekening_count' => count($rekeningList),
                'grouped_data_count' => $groupedData->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $groupedData,
                'kegiatan_list' => array_values($kegiatanList), // Daftar kegiatan untuk select
                'rekening_list' => $rekeningList, // Daftar rekening untuk select
                'bulan' => $bulan,
                'tahap' => $isTahap1 ? 'RKAS Asli' : 'RKAS Perubahan',
                'debug' => [
                    'penganggaran_id' => $penganggaran->id,
                    'model_used' => $model,
                    'bulan_sampai_target' => $bulanSampaiTarget,
                    'rkas_count' => $rkasData->count(),
                    'bku_count' => $bkuData->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting kegiatan dan rekening: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    // PERBAIKAN V2: getUraianByRekening dengan debug dan logika yang lebih fleksibel
    public function getUraianByRekening($tahun, $bulan, $rekeningId, Request $request)
    {
        try {
            // Ambil kegiatan_id dari query parameter
            $kegiatanId = $request->query('kegiatan_id');

            Log::info('=== DEBUG getUraianByRekening ===', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'rekeningId' => $rekeningId,
                'kegiatanId' => $kegiatanId
            ]);

            if (!$kegiatanId) {
                Log::warning('Parameter kegiatan_id tidak ditemukan');
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter kegiatan_id diperlukan'
                ], 400);
            }

            // Cari data penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                Log::warning('Penganggaran tidak ditemukan untuk tahun: ' . $tahun);
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            Log::info('Penganggaran ditemukan', ['penganggaran_id' => $penganggaran->id]);

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            Log::info('Model yang digunakan', ['model' => $model, 'isTahap1' => $isTahap1]);

            // Dapatkan semua bulan sampai bulan target
            $bulanList = [
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

            $bulanIndex = array_search($bulan, $bulanList);
            if ($bulanIndex === false) {
                Log::error('Bulan tidak valid: ' . $bulan);
                return response()->json([
                    'success' => false,
                    'message' => 'Bulan tidak valid'
                ], 400);
            }

            $bulanSampaiTarget = array_slice($bulanList, 0, $bulanIndex + 1);
            Log::info('Bulan sampai target', ['bulan_sampai_target' => $bulanSampaiTarget]);

            // PERBAIKAN V2: Coba query dengan berbagai tingkat filter untuk debugging

            // 1. Query tanpa filter kegiatan dulu untuk melihat apakah ada data rekening
            $uraianTanpaFilterKegiatan = $model::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', $bulanSampaiTarget)
                ->where('kode_rekening_id', $rekeningId)
                ->with('rekeningBelanja')
                ->get();

            Log::info('Uraian tanpa filter kegiatan', ['count' => $uraianTanpaFilterKegiatan->count()]);

            // 2. Query dengan filter kegiatan dan rekening (query asli)
            $uraian = $model::where('penganggaran_id', $penganggaran->id)
                ->whereIn('bulan', $bulanSampaiTarget)
                ->where('kode_rekening_id', $rekeningId)
                ->where('kode_id', $kegiatanId)
                ->with('rekeningBelanja')
                ->get();

            Log::info('Uraian dengan filter kegiatan', ['count' => $uraian->count()]);

            // 3. Jika tidak ada data dengan filter kegiatan, coba tanpa filter kegiatan
            if ($uraian->isEmpty() && !$uraianTanpaFilterKegiatan->isEmpty()) {
                Log::warning('Tidak ada data dengan filter kegiatan, menggunakan data tanpa filter kegiatan');
                $uraian = $uraianTanpaFilterKegiatan;
            }

            // 4. Jika masih kosong, coba query untuk semua bulan (tidak hanya sampai target)
            if ($uraian->isEmpty()) {
                Log::warning('Mencoba query untuk semua bulan');
                $uraianSemuaBulan = $model::where('penganggaran_id', $penganggaran->id)
                    ->where('kode_rekening_id', $rekeningId)
                    ->where('kode_id', $kegiatanId)
                    ->with('rekeningBelanja')
                    ->get();

                Log::info('Uraian semua bulan', ['count' => $uraianSemuaBulan->count()]);

                if (!$uraianSemuaBulan->isEmpty()) {
                    $uraian = $uraianSemuaBulan;
                }
            }

            // Jika tidak ada uraian, kembalikan response kosong dengan debug info
            if ($uraian->isEmpty()) {
                Log::warning('Tidak ada uraian ditemukan setelah semua percobaan');

                // Debug: Cek apakah ada data RKAS sama sekali untuk penganggaran ini
                $totalRkas = $model::where('penganggaran_id', $penganggaran->id)->count();
                $totalRkasKegiatan = $model::where('penganggaran_id', $penganggaran->id)
                    ->where('kode_id', $kegiatanId)->count();
                $totalRkasRekening = $model::where('penganggaran_id', $penganggaran->id)
                    ->where('kode_rekening_id', $rekeningId)->count();

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada uraian untuk kombinasi kegiatan dan rekening ini',
                    'debug' => [
                        'penganggaran_id' => $penganggaran->id,
                        'kegiatan_id' => $kegiatanId,
                        'rekening_id' => $rekeningId,
                        'bulan_target' => $bulan,
                        'bulan_sampai_target' => $bulanSampaiTarget,
                        'model_used' => $model,
                        'total_rkas' => $totalRkas,
                        'total_rkas_kegiatan' => $totalRkasKegiatan,
                        'total_rkas_rekening' => $totalRkasRekening,
                        'uraian_tanpa_filter_kegiatan' => $uraianTanpaFilterKegiatan->count()
                    ]
                ]);
            }

            Log::info('Jumlah uraian ditemukan: ' . $uraian->count());

            // Kelompokkan uraian by nama uraian untuk menggabungkan yang sama
            $uraianGrouped = $uraian->groupBy('uraian')->map(function ($uraianItems) use ($penganggaran, $bulan, $kegiatanId, $rekeningId) {
                $firstItem = $uraianItems->first();

                // Hitung total volume (jumlah) dari semua bulan sampai target
                $totalVolume = $uraianItems->sum('jumlah');

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
                    'Desember' => 12
                ];

                $bulanTargetNumber = $bulanAngkaList[$bulan] ?? 1;

                try {
                    // PERBAIKAN: Hitung total yang sudah dibelanjakan (dalam rupiah) untuk uraian ini
                    $sudahDibelanjakanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                        ->where('kode_rekening_id', $rekeningId)
                        ->where('kode_kegiatan_id', $kegiatanId)
                        ->where('uraian', 'LIKE', '%' . $firstItem->uraian . '%')
                        ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTargetNumber])
                        ->sum('dibelanjakan');

                    $sudahDibelanjakanBulanIni = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                        ->where('kode_rekening_id', $rekeningId)
                        ->where('kode_kegiatan_id', $kegiatanId)
                        ->where('uraian', 'LIKE', '%' . $firstItem->uraian . '%')
                        ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) = ?', [$bulanTargetNumber])
                        ->sum('dibelanjakan');

                    $totalAnggaran = $firstItem->harga_satuan * $totalVolume;
                    $sudahDibelanjakan = $sudahDibelanjakanSebelumnya + $sudahDibelanjakanBulanIni;
                    $sisaAnggaran = $totalAnggaran - $sudahDibelanjakan;

                    // PERBAIKAN: Hitung volume yang sudah digunakan berdasarkan harga satuan RKAS
                    $volumeSudahDibelanjakanTotal = 0;
                    if ($firstItem->harga_satuan > 0) {
                        $volumeSudahDibelanjakanTotal = $sudahDibelanjakan / $firstItem->harga_satuan;
                    }

                    // Hitung volume yang tersedia untuk bulan INI SAJA
                    $volumeBulanIni = $uraianItems->where('bulan', $bulan)->sum('jumlah');

                    // Jika tidak ada volume untuk bulan ini, gunakan sisa volume total
                    if ($volumeBulanIni == 0) {
                        $volumeBulanIni = max(0, $totalVolume - $volumeSudahDibelanjakanTotal);
                    }

                    // Hitung sisa volume yang bisa digunakan di bulan ini
                    $sisaVolumeTotal = max(0, $totalVolume - $volumeSudahDibelanjakanTotal);
                    $sisaVolumeBulanIni = min($volumeBulanIni, $sisaVolumeTotal);

                    // Cek apakah sudah mencapai maksimal di SEMUA bulan
                    $sudahMaksimal = $volumeSudahDibelanjakanTotal >= $totalVolume;

                    Log::info('Uraian calculation', [
                        'uraian' => $firstItem->uraian,
                        'total_volume' => $totalVolume,
                        'volume_bulan_ini' => $volumeBulanIni,
                        'sisa_volume_bulan_ini' => $sisaVolumeBulanIni,
                        'sudah_maksimal' => $sudahMaksimal
                    ]);

                    return [
                        'id' => $firstItem->id,
                        'uraian' => $firstItem->uraian,
                        'total_volume' => $totalVolume,
                        'volume_bulan_ini' => $volumeBulanIni,
                        'harga_satuan' => $firstItem->harga_satuan,
                        'satuan' => $firstItem->satuan,
                        'total_anggaran' => $totalAnggaran,
                        'sudah_dibelanjakan' => $sudahDibelanjakan,
                        'sisa_anggaran' => max(0, $sisaAnggaran),
                        'volume_sudah_dibelanjakan' => $volumeSudahDibelanjakanTotal,
                        'sisa_volume' => $sisaVolumeBulanIni,
                        'sudah_maksimal' => $sudahMaksimal,
                        'dapat_digunakan' => !$sudahMaksimal && $sisaVolumeBulanIni > 0,
                        'kode_id' => $firstItem->kode_id
                    ];
                } catch (\Exception $e) {
                    Log::error('Error processing uraian: ' . $e->getMessage());
                    return null;
                }
            })->filter()->values();

            Log::info('Jumlah uraian setelah grouping: ' . count($uraianGrouped));

            return response()->json([
                'success' => true,
                'data' => $uraianGrouped,
                'debug' => [
                    'total_uraian_raw' => $uraian->count(),
                    'total_uraian_grouped' => count($uraianGrouped),
                    'bulan_target' => $bulan,
                    'bulan_sampai_target' => $bulanSampaiTarget,
                    'penganggaran_id' => $penganggaran->id,
                    'rekening_id' => $rekeningId,
                    'kegiatan_id' => $kegiatanId,
                    'model_used' => $model
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting uraian: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data uraian: ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
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
            'Desember' => 12
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
                'nama_penyedia' => 'required|string|max:255',
                'nama_penerima' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'nomor_telepon' => 'nullable|string|max:20',
                'npwp' => 'nullable|string|max:25',
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
                'total_transaksi_kotor' => 'nullable|numeric'
            ]);

            // Validasi tambahan: pastikan tanggal nota sesuai dengan bulan yang dipilih
            $bulanTarget = $validated['bulan'];
            $bulanAngka = $this->convertBulanToNumber($bulanTarget);
            $tahunAnggaran = Penganggaran::find($validated['penganggaran_id'])->tahun_anggaran;

            $tanggalNota = \Carbon\Carbon::parse($validated['tanggal_nota']);
            if ($tanggalNota->month != $bulanAngka || $tanggalNota->year != $tahunAnggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal nota harus dalam bulan ' . $bulanTarget . ' tahun ' . $tahunAnggaran
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

                // Kurangi pajak jika ada (proporsional)
                $totalKegiatanSetelahPajak = $totalKegiatan;
                if (!empty($validated['pajak_items'])) {
                    $rasioKegiatan = $totalKegiatan / $validated['total_transaksi_kotor'];
                    foreach ($validated['pajak_items'] as $pajak) {
                        $totalKegiatanSetelahPajak -= ($pajak['total_pajak'] ?? 0) * $rasioKegiatan;
                    }
                }

                // Dapatkan data rekening belanja untuk uraian
                $rekeningBelanja = RekeningBelanja::find($rekeningId);
                $uraianText = "Lunas Bayar Belanja " . $rekeningBelanja->rincian_objek;

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
                    'nama_penerima_pembayaran' => $validated['nama_penerima'] ?? $validated['nama_penyedia'],
                    'alamat' => $validated['alamat'],
                    'nomor_telepon' => $validated['nomor_telepon'],
                    'npwp' => $validated['npwp'],
                    'uraian' => $uraianText,
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
                        'rkas_perubahan_id' => !$isTahap1 ? $item['id'] : null,
                        'uraian' => $item['uraian_text'],
                        'satuan' => $item['satuan'] ?? null,
                        'harga_satuan' => $item['harga_satuan'],
                        'volume' => $item['volume'],
                        'subtotal' => $item['jumlah_belanja']
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
                'saldo_update' => $this->hitungSaldoTunaiNonTunai($validated['penganggaran_id'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan BKU: ' . $e->getMessage());
            Log::error('Request data: ', $request->all());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'debug' => $request->all()
            ], 500);
        }
    }

    private function hitungTotalDibelanjakan($penganggaran_id, $bulan)
    {
        try {
            $totalDibelanjakan = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
                ->sum('dibelanjakan');

            return $totalDibelanjakan;
        } catch (\Exception $e) {
            Log::error('Error menghitung total dibelanjakan: ' . $e->getMessage());
            return 0;
        }
    }

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
                'Desember' => 12
            ];

            $bulanTargetNumber = $bulanList[$bulanTarget];

            $totalDibelanjakan = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) <= ?', [$bulanTargetNumber])
                ->sum('dibelanjakan');

            return $totalDibelanjakan;
        } catch (\Exception $e) {
            Log::error('Error menghitung total dibelanjakan sampai bulan ini: ' . $e->getMessage());
            return 0;
        }
    }

    // Tambahkan method untuk menghitung anggaran yang belum dibelanjakan
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
                'Desember' => 12
            ];

            $bulanTargetNumber = $bulanList[$bulanTarget];

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulanTarget, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            // Hitung total anggaran sampai bulan target
            $totalAnggaranSampaiBulanIni = $model::where('penganggaran_id', $penganggaran_id)
                ->whereIn('bulan', array_slice(array_keys($bulanList), 0, $bulanTargetNumber))
                ->sum(DB::raw('harga_satuan * jumlah'));

            // Hitung total yang sudah dibelanjakan sampai bulan target
            $totalDibelanjakanSampaiBulanIni = $this->hitungTotalDibelanjakanSampaiBulanIni($penganggaran_id, $bulanTarget);

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
                'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dibelanjakan: ' . $e->getMessage()
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
                'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil total dibelanjakan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, BukuKasUmum $bukuKasUmum)
    {
        //
    }

    public function destroyAllByBulan($tahun, $bulan)
    {
        try {
            DB::beginTransaction();

            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
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
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menghapus semua data BKU: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
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
                    'message' => 'Transaksi berhasil dihapus'
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
                    'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
                ], 500);
            }

            // Redirect untuk request biasa
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
};
