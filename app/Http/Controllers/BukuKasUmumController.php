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

        // Ambil data BKU untuk bulan tersebut
        $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
            ->whereMonth('tanggal_transaksi', $this->convertBulanToNumber($bulan))
            ->with(['kodeKegiatan', 'rekeningBelanja'])
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

    public function getKegiatanDanRekening($tahun, $bulan)
    {
        try {
            // Cari data penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            // Ambil data kegiatan dan rekening belanja
            $data = $model::where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->get()
                ->groupBy('kode_id')
                ->map(function ($items) {
                    return [
                        'kegiatan' => $items->first()->kodeKegiatan,
                        'rekening_belanja' => $items->map(function ($item) {
                            return [
                                'id' => $item->kode_rekening_id,
                                'rekening' => $item->rekeningBelanja,
                                'uraian' => $item->uraian,
                                'anggaran' => $item->harga_satuan * $item->jumlah
                            ];
                        })->unique('id')->values()
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data' => $data,
                'bulan' => $bulan,
                'tahap' => $isTahap1 ? 'RKAS Asli' : 'RKAS Perubahan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting kegiatan dan rekening: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUraianByRekening($tahun, $bulan, $rekeningId)
    {
        try {
            // Cari data penganggaran berdasarkan tahun
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan'
                ], 404);
            }

            // Tentukan model yang akan digunakan berdasarkan bulan
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            // Ambil uraian berdasarkan rekening belanja
            $uraian = $model::where('penganggaran_id', $penganggaran->id)
                ->where('bulan', $bulan)
                ->where('kode_rekening_id', $rekeningId)
                ->with('rekeningBelanja')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'uraian' => $item->uraian,
                        'anggaran' => $item->harga_satuan * $item->jumlah,
                        'harga_satuan' => $item->harga_satuan,
                        'jumlah' => $item->jumlah,
                        'satuan' => $item->satuan
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $uraian
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting uraian: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data uraian: ' . $e->getMessage()
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

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validasi data
            $validated = $request->validate([
                'penganggaran_id' => 'required|exists:penganggarans,id',
                'kode_kegiatan_id' => 'required|exists:kode_kegiatans,id',
                'kode_rekening_id' => 'required|exists:rekening_belanjas,id',
                'tanggal_nota' => 'required|date',
                'jenis_transaksi' => 'required|in:tunai,non-tunai',
                'nomor_nota' => 'required|string|max:100',
                'nama_penyedia' => 'required|string|max:255',
                'nama_penerima' => 'nullable|string|max:255', // Ubah menjadi nullable
                'alamat' => 'nullable|string',
                'nomor_telepon' => 'nullable|string|max:20',
                'npwp' => 'nullable|string|max:25',
                'uraian_items' => 'required|array',
                'uraian_items.*.id' => 'required|numeric',
                'uraian_items.*.jumlah_belanja' => 'required|numeric|min:0',
                'pajak_items' => 'nullable|array',
                'bulan' => 'required|string',
                'total_transaksi_kotor' => 'nullable|numeric' // Tambahkan validasi untuk total kotor
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
            foreach ($validated['uraian_items'] as $item) {
                $totalDibelanjakan += $item['jumlah_belanja'];
            }

            // Kurangi pajak jika ada
            if (!empty($validated['pajak_items'])) {
                foreach ($validated['pajak_items'] as $pajak) {
                    $totalDibelanjakan -= $pajak['total_pajak'] ?? 0;
                }
            }

            // Dapatkan data rekening belanja untuk uraian
            $rekeningBelanja = RekeningBelanja::find($validated['kode_rekening_id']);
            $uraianText = "Lunas Bayar Belanja " . $rekeningBelanja->rincian_objek;

            // Dapatkan total anggaran untuk rekening belanja di bulan tersebut
            $bulan = $validated['bulan'];
            $isTahap1 = in_array($bulan, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
            $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

            $totalAnggaran = $model::where('penganggaran_id', $validated['penganggaran_id'])
                ->where('kode_rekening_id', $validated['kode_rekening_id'])
                ->where('bulan', $bulan)
                ->sum(DB::raw('harga_satuan * jumlah'));

            // Simpan data Buku Kas Umum
            $bku = BukuKasUmum::create([
                'penganggaran_id' => $validated['penganggaran_id'],
                'kode_kegiatan_id' => $validated['kode_kegiatan_id'],
                'kode_rekening_id' => $validated['kode_rekening_id'],
                'tanggal_transaksi' => $validated['tanggal_nota'],
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'id_transaksi' => $validated['nomor_nota'],
                'nama_penyedia_barang_jasa' => $validated['nama_penyedia'],
                'nama_penerima_pembayaran' => $validated['nama_penerima'] ?? $validated['nama_penyedia'], // Default ke nama penyedia jika kosong
                'alamat' => $validated['alamat'],
                'nomor_telepon' => $validated['nomor_telepon'],
                'npwp' => $validated['npwp'],
                'uraian' => $uraianText,
                'anggaran' => $totalAnggaran,
                'dibelanjakan' => $totalDibelanjakan,
                'total_transaksi_kotor' => $request->total_transaksi_kotor ?? $totalDibelanjakan, // Simpan total kotor
                'pajak' => $validated['pajak_items'][0]['jenis_pajak'] ?? null,
                'persen_pajak' => $validated['pajak_items'][0]['persen_pajak'] ?? null,
                'total_pajak' => $validated['pajak_items'][0]['total_pajak'] ?? null,
                'pajak_daerah' => $validated['pajak_items'][1]['jenis_pajak'] ?? null,
                'persen_pajak_daerah' => $validated['pajak_items'][1]['persen_pajak'] ?? null,
                'total_pajak_daerah' => $validated['pajak_items'][1]['total_pajak'] ?? null,
            ]);

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
                'data' => $bku,
                'saldo_update' => $this->hitungSaldoTunaiNonTunai($validated['penganggaran_id'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan BKU: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
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
}
