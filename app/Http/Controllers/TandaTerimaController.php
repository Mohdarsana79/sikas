<?php

namespace App\Http\Controllers;

use App\Models\BukuKasUmum;
use App\Models\PenerimaanDana;
use App\Models\Penganggaran;
use App\Models\Sekolah;
use App\Models\TandaTerima;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TandaTerimaController extends Controller
{
    // Method untuk mendapatkan tahun anggaran (internal, tidak return JSON)
    private function getTahunAnggaranData()
    {
        try {
            $tahunAnggaran = Penganggaran::select('id', 'tahun_anggaran')
                ->orderBy('tahun_anggaran', 'desc')
                ->get()
                ->map(function ($penganggaran) {
                    return [
                        'id' => $penganggaran->id,
                        'tahun' => $penganggaran->tahun_anggaran,
                    ];
                });

            return $tahunAnggaran;
        } catch (\Exception $e) {
            Log::error('Error fetching tahun anggaran: '.$e->getMessage());

            return collect(); // Return empty collection jika error
        }
    }

    // Method untuk API (jika diperlukan)
    public function getTahunAnggaran()
    {
        try {
            $tahunAnggaran = $this->getTahunAnggaranData();

            return response()->json([
                'status' => 'success',
                'data' => $tahunAnggaran,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tahun anggaran: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil tahun anggaran.',
            ], 500);
        }
    }

    // return ke index - DIPERBAIKI
    public function index(Request $request)
    {
        try {
            // Ambil Tahun Anggaran Untuk Select Dropdown langsung dari database
            $tahunAnggarans = $this->getTahunAnggaranData();

            // Ambil Tahun yang dipilih dari request, jika tidak ada gunakan tahun terbaru
            $selectedTahun = $request->input('tahun', $tahunAnggarans->first()['id'] ?? null);

            // query ambil data dengan filter tahun jika dipilih
            $query = TandaTerima::with([
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'bukuKasUmum',
            ]);

            if ($selectedTahun) {
                $query->where('penganggaran_id', $selectedTahun);
            }

            $tandaTerimas = $query->latest()->paginate(10);

            return view('tanda-terima.index', compact('tandaTerimas', 'tahunAnggarans', 'selectedTahun'));
        } catch (\Exception $e) {
            Log::error('Error Tanda Terima Index: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data tanda terima.');
        }
    }

    public function search(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $tahun = $request->input('tahun', '');

            // Query dengan filter tahun
            $query = TandaTerima::with([
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'bukuKasUmum',
            ]);

            // Filter berdasarkan tahun jika dipilih
            if ($tahun) {
                $query->where('penganggaran_id', $tahun);
            }

            // Filter pencarian
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('bukuKasUmum', function ($q) use ($search) {
                        $q->where('uraian', 'ILIKE', "%{$search}%")
                            ->orWhere('uraian_opsional', 'ILIKE', "%{$search}%");
                    })
                        ->orWhereHas('rekeningBelanja', function ($q) use ($search) {
                            $q->where('kode_rekening', 'ILIKE', "%{$search}%");
                        });
                });
            }

            $tandaTerimas = $query->latest()->paginate(10);

            // Format data untuk response JSON
            $formattedTandaTerimas = $tandaTerimas->map(function ($tandaTerima, $index) use ($tandaTerimas) {
                $number = ($tandaTerimas->currentPage() - 1) * $tandaTerimas->perPage() + $index + 1;

                return [
                    'id' => $tandaTerima->id,
                    'number' => $number,
                    'kode_rekening' => $tandaTerima->rekeningBelanja->kode_rekening ?? '-',
                    'uraian' => $tandaTerima->bukuKasUmum->uraian_opsional ?? $tandaTerima->bukuKasUmum->uraian,
                    'tanggal' => \Carbon\Carbon::parse($tandaTerima->bukuKasUmum->tanggal_transaksi)->format('d/m/Y'),
                    'jumlah' => 'Rp '.number_format($tandaTerima->bukuKasUmum->total_transaksi_kotor, 0, ',', '.'),
                    'preview_url' => route('tanda-terima.preview', $tandaTerima->id),
                    'pdf_url' => route('tanda-terima.pdf', $tandaTerima->id),
                    'delete_url' => route('tanda-terima.destroy', $tandaTerima->id),
                    'delete_data' => [
                        'id' => $tandaTerima->id,
                        'uraian' => $tandaTerima->bukuKasUmum->uraian_opsional ?? $tandaTerima->bukuKasUmum->uraian,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedTandaTerimas,
                'total' => $tandaTerimas->total(),
                'search_term' => $search,
                'selected_tahun' => $tahun,
                'pagination' => [
                    'current_page' => $tandaTerimas->currentPage(),
                    'last_page' => $tandaTerimas->lastPage(),
                    'per_page' => $tandaTerimas->perPage(),
                    'total' => $tandaTerimas->total(),
                    'has_more' => $tandaTerimas->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching tanda terima: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(TandaTerima $tandaTerima)
    {
        $tandaTerima->load([
            'sekolah',
            'penganggaran',
            'kodeKegiatan',
            'rekeningBelanja',
            'penerimaanDana',
            'bukuKasUmum',
        ]);

        return view('tanda-terima.show', compact('tandaTerima'));
    }

    // AJAX method untuk mendapatkan detail Transaksi
    public function getTransaksiDetail($bukuKasUmumId)
    {
        try {
            $bukuKasUmum = BukuKasUmum::with(['kodeKegiatan', 'rekeningBelanja', 'penganggaran', 'sekolah'])
                ->findOrFail($bukuKasUmumId);

            return response()->json([
                'success' => true,
                'data' => $bukuKasUmum,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaksi detail: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }

    public function checkAvailableData()
    {
        try {
            // PERBAIKAN: hitung hanya dari bukukasumum yang belum ada di tanda terima DAN memiliki nama_penerima_pembayaran
            $availableCount = BukuKasUmum::whereDoesntHave('tandaTerima')
                ->where('is_bunga_record', false)
                ->whereNotNull('nama_penerima_pembayaran') // Hanya yang ada nama penerima
                ->where('nama_penerima_pembayaran', '!=', '') // Hanya yang tidak kosong
                ->count();

            Log::info('Available data count for Tanda Terima (with nama_penerima_pembayaran): '.$availableCount);

            return response()->json([
                'success' => true,
                'data' => [
                    'availableCount' => $availableCount,
                    'pending_count' => 0,
                    'failed_count' => 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking available data for Tanda Terima: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa data yang tersedia.',
            ], 500);
        }
    }

    public function generateBatch(Request $request)
    {
        try {
            $offset = $request->input('offset', 0);

            // PERBAIKAN: Hitung total dari BukuKasUmum yang belum memiliki tanda terima DAN memiliki nama_penerima_pembayaran
            $totalWithoutTandaTerima = BukuKasUmum::whereDoesntHave('tandaTerima')
                ->where('is_bunga_record', false)
                ->whereNotNull('nama_penerima_pembayaran') // Hanya yang ada nama penerima
                ->where('nama_penerima_pembayaran', '!=', '') // Hanya yang tidak kosong
                ->count();

            Log::info("Generate Batch - Total BukuKasUmum without tanda terima and with nama_penerima_pembayaran: {$totalWithoutTandaTerima}, Offset: {$offset}");

            // Jika tidak ada data, return completion
            if ($totalWithoutTandaTerima === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'processed' => 0,
                        'success' => 0,
                        'failed' => 0,
                        'remaining' => 0,
                        'progress' => 100,
                        'has_more' => false,
                        'total' => 0,
                        'total_processed' => 0,
                        'message' => 'Tidak ada data yang perlu diproses (tidak ada data dengan nama penerima pembayaran)',
                    ],
                ]);
            }

            // PERBAIKAN: Query hanya BukuKasUmum yang memiliki nama_penerima_pembayaran
            $bukuKasUmums = BukuKasUmum::with([
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
            ])
                ->whereDoesntHave('tandaTerima')
                ->where('is_bunga_record', false)
                ->whereNotNull('nama_penerima_pembayaran') // Hanya yang ada nama penerima
                ->where('nama_penerima_pembayaran', '!=', '') // Hanya yang tidak kosong
                ->orderBy('id')
                ->skip($offset)
                ->get();

            Log::info("Found {$bukuKasUmums->count()} BukuKasUmum with nama_penerima_pembayaran to process");

            $processed = 0;
            $success = 0;
            $failed = 0;

            // Jika tidak ada data yang ditemukan, artinya sudah selesai
            if ($bukuKasUmums->isEmpty()) {
                Log::info("No BukuKasUmum with nama_penerima_pembayaran found at offset {$offset}, marking as complete");

                return response()->json([
                    'success' => true,
                    'data' => [
                        'processed' => 0,
                        'success' => 0,
                        'failed' => 0,
                        'remaining' => 0,
                        'progress' => 100,
                        'has_more' => false,
                        'total' => $totalWithoutTandaTerima,
                        'total_processed' => $offset,
                        'message' => 'Tidak ada data dengan nama penerima pembayaran yang perlu diproses',
                    ],
                ]);
            }

            // Gunakan transaction database untuk konsistensi
            DB::beginTransaction();

            try {
                foreach ($bukuKasUmums as $bukuKasUmum) {
                    try {
                        // Double check - pastikan tanda terima belum ada untuk BukuKasUmum ini
                        $existingTandaTerima = TandaTerima::where('buku_kas_umum_id', $bukuKasUmum->id)->first();

                        if (! $existingTandaTerima) {
                            // PERBAIKAN: Validasi nama_penerima_pembayaran lagi (double check)
                            if (empty($bukuKasUmum->nama_penerima_pembayaran)) {
                                Log::warning('Skipping BukuKasUmum without nama_penerima_pembayaran: '.$bukuKasUmum->id);
                                $failed++;
                                $processed++;

                                continue;
                            }

                            // Ambil sekolah_id langsung dari penganggaran
                            $sekolahId = null;

                            // Cek apakah penganggaran ada dan memiliki sekolah_id
                            if ($bukuKasUmum->penganggaran && $bukuKasUmum->penganggaran->sekolah_id) {
                                $sekolahId = $bukuKasUmum->penganggaran->sekolah_id;
                                Log::info("Found sekolah_id from penganggaran: {$sekolahId} for BukuKasUmum: {$bukuKasUmum->id}");
                            } else {
                                // Jika penganggaran tidak memiliki sekolah_id, cari sekolah default
                                $sekolahDefault = Sekolah::first();
                                if ($sekolahDefault) {
                                    $sekolahId = $sekolahDefault->id;
                                    Log::info("Using default sekolah_id: {$sekolahId} for BukuKasUmum: {$bukuKasUmum->id}");
                                } else {
                                    Log::warning('No sekolah found for BukuKasUmum: '.$bukuKasUmum->id);
                                    $failed++;
                                    $processed++;

                                    continue;
                                }
                            }

                            // Cari penerimaan dana berdasarkan penganggaran_id
                            $penerimaanDana = PenerimaanDana::where('penganggaran_id', $bukuKasUmum->penganggaran_id)->first();

                            if (! $penerimaanDana) {
                                // Buat penerimaan dana default jika tidak ada
                                $penerimaanDana = PenerimaanDana::create([
                                    'penganggaran_id' => $bukuKasUmum->penganggaran_id,
                                    'sumber_dana' => 'BOSP Reguler',
                                    'jumlah_dana' => 0,
                                    'tanggal_terima' => now(),
                                ]);
                                Log::info("Created default penerimaan dana for penganggaran: {$bukuKasUmum->penganggaran_id}");
                            }

                            // Validasi data yang diperlukan
                            if (! $bukuKasUmum->kode_kegiatan_id) {
                                Log::warning('Kode kegiatan ID not found for BukuKasUmum: '.$bukuKasUmum->id);
                                $failed++;
                                $processed++;

                                continue;
                            }

                            if (! $bukuKasUmum->kode_rekening_id) {
                                Log::warning('Kode rekening ID not found for BukuKasUmum: '.$bukuKasUmum->id);
                                $failed++;
                                $processed++;

                                continue;
                            }

                            // Buat tanda terima
                            TandaTerima::create([
                                'sekolah_id' => $sekolahId,
                                'penganggaran_id' => $bukuKasUmum->penganggaran_id,
                                'kode_kegiatan_id' => $bukuKasUmum->kode_kegiatan_id,
                                'kode_rekening_id' => $bukuKasUmum->kode_rekening_id,
                                'penerimaan_dana_id' => $penerimaanDana->id,
                                'buku_kas_umum_id' => $bukuKasUmum->id,
                            ]);
                            $success++;
                            Log::info("Successfully created tanda terima for BukuKasUmum: {$bukuKasUmum->id} with penerima: {$bukuKasUmum->nama_penerima_pembayaran}");
                        } else {
                            Log::info("Tanda terima already exists for BukuKasUmum: {$bukuKasUmum->id}, skipping...");
                            // Tetap hitung sebagai processed meskipun skip
                        }
                        $processed++;
                    } catch (\Exception $e) {
                        Log::error('Error generating tanda terima for BukuKasUmum '.$bukuKasUmum->id.': '.$e->getMessage());
                        $failed++;
                        $processed++;
                    }
                }

                DB::commit();

                Log::info("Generate Batch Result - SUCCESS: Created {$success} tanda terima for data with nama_penerima_pembayaran");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction failed in generateBatch: '.$e->getMessage());
                throw $e;
            }

            // Hitung progress berdasarkan total yang sudah diproses vs total awal
            $totalProcessedSoFar = $offset + $processed;

            // Progress calculation
            $progress = 0;
            if ($totalWithoutTandaTerima > 0) {
                $progress = min(100, (int) round(($totalProcessedSoFar / $totalWithoutTandaTerima) * 100));
            }

            // Hitung ulang remaining (hanya yang punya nama_penerima_pembayaran)
            $remainingAfterProcess = BukuKasUmum::whereDoesntHave('tandaTerima')
                ->where('is_bunga_record', false)
                ->whereNotNull('nama_penerima_pembayaran')
                ->where('nama_penerima_pembayaran', '!=', '')
                ->count();

            $hasMore = $remainingAfterProcess > 0 && $progress < 100;

            if ($progress >= 100 || $remainingAfterProcess <= 0) {
                $hasMore = false;
                $progress = 100;
                $remainingAfterProcess = 0;
            }

            Log::info("Generate Batch Result - Offset: {$offset}, Processed: {$processed}, Success: {$success}, Failed: {$failed}, Progress: {$progress}%, Remaining: {$remainingAfterProcess}");

            return response()->json([
                'success' => true,
                'data' => [
                    'processed' => $processed,
                    'success' => $success,
                    'failed' => $failed,
                    'remaining' => $remainingAfterProcess,
                    'progress' => $progress,
                    'has_more' => $hasMore,
                    'total' => $totalWithoutTandaTerima,
                    'total_processed' => $totalProcessedSoFar,
                    'offset' => $offset + $processed,
                    'message' => "Diproses: {$processed} transaksi dengan nama penerima, Berhasil: {$success}, Gagal: {$failed}",
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in generateBatch: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Cari tanda terima by ID
            $tandaTerima = TandaTerima::with(['bukuKasUmum'])->find($id);

            if (! $tandaTerima) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tanda terima tidak ditemukan',
                ], 404);
            }

            $uraian = $tandaTerima->bukuKasUmum->uraian_opsional ?? $tandaTerima->bukuKasUmum->uraian;

            // Hapus tanda Terima
            $tandaTerima->delete();

            return response()->json([
                'success' => true,
                'message' => "Tanda terima untuk '{$uraian}' berhasil dihapus!",
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting tanda terima: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tanda terima: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteAll(Request $request)
    {
        try {
            // Hitung total tanda terima sebelum dihapus
            $totalTandaTerima = TandaTerima::count();

            if ($totalTandaTerima === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data tanda terima yang dapat dihapus',
                ], 404);
            }

            // Hapus semua data tanda terima
            $deletedCount = TandaTerima::query()->delete();

            // Log activity
            Log::info("Deleted all tanda terima: {$deletedCount} records deleted by user");

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} data tanda terima",
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting all tanda terima: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus semua data tanda terima: '.$e->getMessage(),
            ], 500);
        }
    }

    public function generatePdf($id)
    {
        try {
            $tandaTerima = TandaTerima::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum',
            ])->findOrFail($id);

            $kodeKegiatan = $tandaTerima->kodeKegiatan;
            $rekeningBelanja = $tandaTerima->rekeningBelanja;
            $bukuKasUmum = $tandaTerima->bukuKasUmum;

            // Hitung total amount dari BukuKasUmum
            $totalAmount = $tandaTerima->bukuKasUmum->total_transaksi_kotor ?? 0;

            // Ambil pajak pusat dari BukuKasUmum
            $pajakPusat = $tandaTerima->bukuKasUmum->total_pajak ?? 0;

            // Hitung jumlah terima = total amount - pajak pusat
            $jumlahTerima = $totalAmount - $pajakPusat;

            // Format jumlah uang dalam text (gunakan jumlah terima)
            $jumlahUang = $this->formatJumlahUang($jumlahTerima);

            $data = [
                'tandaTerima' => $tandaTerima,
                'kodeKegiatan' => $kodeKegiatan,
                'rekeningBelanja' => $rekeningBelanja,
                'bukuKasUmum' => $bukuKasUmum,
                'jumlahUangText' => $jumlahUang,
                'totalAmount' => $totalAmount,
                'pajakPusat' => $pajakPusat,
                'jumlahTerima' => $jumlahTerima,
                'tanggalLunas' => $this->formatTanggalLunas($tandaTerima->bukuKasUmum->tanggal_transaksi ?? now()),
                'sekolah' => Sekolah::first(),
            ];

            $pdf = PDF::loadView('tanda-terima.pdf', $data);
            $pdf->setPaper('folio', 'landscape'); // Ubah ke landscape folio

            $filename = 'Tanda_Terima_Honor_'.$tandaTerima->id.'_'.now()->format('Y-m-d').'.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating PDF tanda terima: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return redirect()->route('tanda-terima.index')
                ->with('error', 'Gagal generate PDF: '.$e->getMessage());
        }
    }

    public function previewPdf($id)
    {
        try {
            $tandaTerima = TandaTerima::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum',
            ])->findOrFail($id);

            $kodeKegiatan = $tandaTerima->kodeKegiatan;
            $rekeningBelanja = $tandaTerima->rekeningBelanja;
            $bukuKasUmum = $tandaTerima->bukuKasUmum;

            // Hitung total amount dari BukuKasUmum
            $totalAmount = $tandaTerima->bukuKasUmum->total_transaksi_kotor ?? 0;

            // Ambil pajak pusat dari BukuKasUmum
            $pajakPusat = $tandaTerima->bukuKasUmum->total_pajak ?? 0;

            // Hitung jumlah terima = total amount - pajak pusat
            $jumlahTerima = $totalAmount - $pajakPusat;

            // Format jumlah uang dalam text (gunakan jumlah terima)
            $jumlahUang = $this->formatJumlahUang($jumlahTerima);

            $data = [
                'tandaTerima' => $tandaTerima,
                'kodeKegiatan' => $kodeKegiatan,
                'rekeningBelanja' => $rekeningBelanja,
                'bukuKasUmum' => $bukuKasUmum,
                'jumlahUangText' => $jumlahUang,
                'totalAmount' => $totalAmount,
                'pajakPusat' => $pajakPusat,
                'jumlahTerima' => $jumlahTerima,
                'tanggalLunas' => $this->formatTanggalLunas($tandaTerima->bukuKasUmum->tanggal_transaksi ?? now()),
                'sekolah' => Sekolah::first(),
            ];

            // Generate PDF untuk preview dengan landscape folio
            $pdf = PDF::loadView('tanda-terima.pdf', $data);
            $pdf->setPaper('folio', 'landscape'); // Ubah ke landscape folio

            // Return PDF sebagai response dengan header yang tepat untuk preview
            return $pdf->stream('Tanda_Terima_Honor_'.$tandaTerima->id.'.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating preview PDF: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate preview PDF: '.$e->getMessage(),
            ], 500);
        }
    }

    // Method untuk preview PDF dalam modal
    public function previewModal($id)
    {
        try {
            $tandaTerima = TandaTerima::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum',
            ])->findOrFail($id);

            $kodeKegiatan = $tandaTerima->kodeKegiatan;
            $rekeningBelanja = $tandaTerima->rekeningBelanja;
            $bukuKasUmum = $tandaTerima->bukuKasUmum;

            // Hitung total amount dari BukuKasUmum
            $totalAmount = $tandaTerima->bukuKasUmum->total_transaksi_kotor ?? 0;

            // Ambil pajak pusat dari BukuKasUmum
            $pajakPusat = $tandaTerima->bukuKasUmum->total_pajak ?? 0;

            // Hitung jumlah terima = total amount - pajak pusat
            $jumlahTerima = $totalAmount - $pajakPusat;

            // Format jumlah uang dalam text (gunakan jumlah terima)
            $jumlahUang = $this->formatJumlahUang($jumlahTerima);

            $data = [
                'tandaTerima' => $tandaTerima,
                'kodeKegiatan' => $kodeKegiatan,
                'rekeningBelanja' => $rekeningBelanja,
                'bukuKasUmum' => $bukuKasUmum,
                'jumlahUangText' => $jumlahUang,
                'totalAmount' => $totalAmount,
                'pajakPusat' => $pajakPusat,
                'jumlahTerima' => $jumlahTerima,
                'tanggalLunas' => $this->formatTanggalLunas($tandaTerima->bukuKasUmum->tanggal_transaksi ?? now()),
                'sekolah' => Sekolah::first(),
            ];

            return view('tanda-terima.pdf', $data);
        } catch (\Exception $e) {
            Log::error('Error preview modal tanda terima: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat preview: '.$e->getMessage(),
            ], 500);
        }
    }

    // Method downloadAll - untuk download semua tanda terima dalam satu PDF
    public function downloadAll()
    {
        try {
            // Ambil semua tanda terima dengan relasi yang diperlukan
            $tandaTerimas = TandaTerima::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum',
            ])->latest()->get();

            // Jika tidak ada data
            if ($tandaTerimas->isEmpty()) {
                return redirect()->route('tanda-terima.index')
                    ->with('error', 'Tidak ada data tanda terima untuk diunduh');
            }

            // Siapkan data untuk PDF
            $tandaTerimaData = [];
            foreach ($tandaTerimas as $tandaTerima) {
                $penganggaran = $tandaTerima->penganggaran;
                $kodeKegiatan = $tandaTerima->kodeKegiatan;
                $rekeningBelanja = $tandaTerima->rekeningBelanja;
                $bukuKasUmum = $tandaTerima->bukuKasUmum;

                // Hitung total amount dari BukuKasUmum
                $totalAmount = $tandaTerima->bukuKasUmum->total_transaksi_kotor ?? 0;

                // Ambil pajak pusat dari BukuKasUmum
                $pajakPusat = $tandaTerima->bukuKasUmum->total_pajak ?? 0;

                // Hitung jumlah terima = total amount - pajak pusat
                $jumlahTerima = $totalAmount - $pajakPusat;

                // Format jumlah uang dalam text (gunakan jumlah terima)
                $jumlahUang = $this->formatJumlahUang($jumlahTerima);

                $tandaTerimaData[] = [
                    'tandaTerima' => $tandaTerima,
                    'penganggaran' => $penganggaran,
                    'kodeKegiatan' => $kodeKegiatan,
                    'rekeningBelanja' => $rekeningBelanja,
                    'bukuKasUmum' => $bukuKasUmum,
                    'jumlahUangText' => $jumlahUang,
                    'totalAmount' => $totalAmount,
                    'pajakPusat' => $pajakPusat,
                    'jumlahTerima' => $jumlahTerima,
                    'tanggalLunas' => $this->formatTanggalLunas($tandaTerima->bukuKasUmum->tanggal_transaksi ?? now()),
                ];
            }

            $data = [
                'tandaTerimas' => $tandaTerimaData,
                'totalTandaTerima' => $tandaTerimas->count(),
                'tanggalDownload' => now()->format('d/m/Y H:i'),
                'sekolah' => Sekolah::first(),
            ];

            // Generate PDF
            $pdf = PDF::loadView('tanda-terima.download-all', $data);
            $pdf->setPaper('folio', 'landscape');

            $filename = 'Tanda_Terima_All_'.now()->format('Y-m-d_H-i-s').'.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error downloading all tanda terima: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return redirect()->route('tanda-terima.index')
                ->with('error', 'Gagal mengunduh semua tanda terima: '.$e->getMessage());
        }
    }

    // Helper method untuk format jumlah uang
    private function formatJumlahUang($amount)
    {
        $formatter = new \NumberFormatter('id_ID', \NumberFormatter::SPELLOUT);
        $words = $formatter->format($amount);

        // Tambahkan "Rupiah" di akhir
        return ucfirst($words).' Rupiah';
    }

    private function formatTanggalLunas($tanggal)
    {
        $bulanIndonesia = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        $tanggalObj = \Carbon\Carbon::parse($tanggal);
        $bulan = $bulanIndonesia[$tanggalObj->format('F')];

        return "Lunas Bayar, {$tanggalObj->format('d')} {$bulan} {$tanggalObj->format('Y')}";
    }
}
