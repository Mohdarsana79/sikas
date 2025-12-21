<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sts;
use App\Models\Penganggaran;
use Illuminate\Support\Facades\DB;

class StsController extends Controller
{
    // Index - Menampilkan semua data STS
    public function index()
    {
        $sts = Sts::with('penganggaran')
            ->orderBy('created_at', 'desc')
            ->get();

        // Format data untuk view
        $formattedSts = $sts->map(function ($item) {
            $sisa = $item->jumlah_sts - $item->jumlah_bayar;

            // Tentukan status
            if ($item->jumlah_bayar == 0) {
                $status = 'Menunggu';
                $badge = 'badge-pending';
            } elseif ($sisa > 0) {
                $status = 'Parsial';
                $badge = 'badge-partial';
            } else {
                $status = 'Lunas';
                $badge = 'badge-paid';
            }

            return [
                'id' => $item->id,
                'nomor_sts' => $item->nomor_sts,
                'tahun' => $item->penganggaran ? $item->penganggaran->tahun_anggaran : '-',
                'jumlah_sts' => number_format($item->jumlah_sts, 0, ',', '.'),
                'jumlah_bayar' => number_format($item->jumlah_bayar, 0, ',', '.'),
                'tanggal_bayar' => $item->tanggal_bayar ? $item->tanggal_bayar->format('d/m/Y') : '-',
                'status' => $status,
                'badge' => $badge,
                'sisa' => $sisa,
            ];
        });

        return view('sts.index', compact('formattedSts'));
    }

    // Store - Menyimpan data STS baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penganggaran_id' => 'required|exists:penganggarans,id',
            'nomor_sts' => 'required|string|max:100|unique:status_sts_giros,nomor_sts',
            'jumlah_sts' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sts = Sts::create([
                'penganggaran_id' => $validated['penganggaran_id'],
                'nomor_sts' => $validated['nomor_sts'],
                'jumlah_sts' => $validated['jumlah_sts'],
                'jumlah_bayar' => 0,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'STS berhasil ditambahkan',
                'data' => $sts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan STS: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update - Mengupdate data STS
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nomor_sts' => 'required|string|max:100|unique:status_sts_giros,nomor_sts,' . $id,
            'jumlah_sts' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sts = Sts::findOrFail($id);

            // Jika mengupdate jumlah STS, validasi bahwa jumlah tidak kurang dari yang sudah dibayar
            if ($request->has('jumlah_sts') && $request->jumlah_sts < $sts->jumlah_bayar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah STS tidak boleh kurang dari jumlah yang sudah dibayar'
                ], 400);
            }

            $sts->update([
                'nomor_sts' => $validated['nomor_sts'],
                'jumlah_sts' => $validated['jumlah_sts'],
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'STS berhasil diupdate',
                'data' => $sts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate STS: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bayar - Proses pembayaran STS
    public function bayar(Request $request, $id)
    {
        $validated = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sts = Sts::findOrFail($id);

            $jumlahBisaDibayar = $sts->jumlah_sts - $sts->jumlah_bayar;

            if ($validated['jumlah_bayar'] > $jumlahBisaDibayar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran melebihi sisa tagihan. Sisa yang bisa dibayar: ' . number_format($jumlahBisaDibayar, 0, ',', '.')
                ], 400);
            }

            $sts->update([
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $sts->jumlah_bayar + $validated['jumlah_bayar'],
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => $sts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update Bayar - Edit pembayaran
    public function updateBayar(Request $request, $id)
    {
        $validated = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sts = Sts::findOrFail($id);

            if ($validated['jumlah_bayar'] > $sts->jumlah_sts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran tidak boleh melebihi total STS'
                ], 400);
            }

            $sts->update([
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $validated['jumlah_bayar'],
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diupdate',
                'data' => $sts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    // Destroy - Hapus STS
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $sts = Sts::findOrFail($id);
            $sts->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'STS berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus STS: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get STS by ID untuk modal edit
    public function show($id)
    {
        $sts = Sts::with('penganggaran')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sts->id,
                'penganggaran_id' => $sts->penganggaran_id,
                'nomor_sts' => $sts->nomor_sts,
                'jumlah_sts' => $sts->jumlah_sts,
                'jumlah_bayar' => $sts->jumlah_bayar,
                'tahun_anggaran' => $sts->penganggaran ? $sts->penganggaran->tahun_anggaran : null,
            ]
        ]);
    }

    // Get data penganggaran untuk dropdown
    public function getPenganggaran()
    {
        $penganggaran = Penganggaran::select('id', 'tahun_anggaran')
            ->orderBy('tahun_anggaran', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $penganggaran
        ]);
    }

    // API untuk mendapatkan data STS dalam format JSON
    public function apiData()
    {
        $sts = Sts::with('penganggaran')
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedData = $sts->map(function ($item) {
            $sisa = $item->jumlah_sts - $item->jumlah_bayar;

            // Tentukan status
            if ($item->jumlah_bayar == 0) {
                $status = 'Menunggu';
                $badge = 'badge-pending';
            } elseif ($sisa > 0) {
                $status = 'Parsial';
                $badge = 'badge-partial';
            } else {
                $status = 'Lunas';
                $badge = 'badge-paid';
            }

            return [
                'id' => $item->id,
                'nomor_sts' => $item->nomor_sts,
                'tahun' => $item->penganggaran ? $item->penganggaran->tahun_anggaran : '-',
                'jumlah_sts' => $item->jumlah_sts,
                'jumlah_bayar' => $item->jumlah_bayar,
                'sisa' => $sisa,
                'tanggal_bayar' => $item->tanggal_bayar ? $item->tanggal_bayar->format('d/m/Y') : null,
                'status' => $status,
                'badge' => $badge,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData
        ]);
    }
}
