<?php

namespace App\Http\Controllers;

use App\Models\BukuKasUmum;
use App\Models\BukuKasUmumUraianDetail;
use App\Models\Kwitansi;
use App\Models\PenerimaanDana;
use App\Models\Penganggaran;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KwitansiController extends Controller
{
    public function getTahunAnggaran()
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

            return response()->json([
                'success' => true,
                'data' => $tahunAnggaran,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting tahun anggaran: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tahun anggaran',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            // Ambil tahun anggaran untuk dropdown
            $tahunAnggarans = Penganggaran::select('id', 'tahun_anggaran')
                ->orderBy('tahun_anggaran', 'desc')
                ->get();

            // Ambil tahun yang dipilih dari request atau default ke tahun terbaru
            $selectedTahun = $request->input('tahun', $tahunAnggarans->first()->id ?? null);

            // Query dengan filter tahun jika dipilih
            $query = Kwitansi::with([
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'bukuKasUmum',
            ]);

            if ($selectedTahun) {
                $query->where('penganggaran_id', $selectedTahun);
            }

            $kwitansis = $query->latest()->paginate(10);

            return view('kwitansi.index', compact('kwitansis', 'tahunAnggarans', 'selectedTahun'));
        } catch (\Exception $e) {
            Log::error('Error in kwitansi index: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data kwitansi');
        }
    }

    public function search(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $tahun = $request->input('tahun', '');
            $startDate = $request->input('start_date', '');
            $endDate = $request->input('end_date', '');

            // Query dengan filter tahun
            $query = Kwitansi::with([
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'bukuKasUmum',
            ]);

            // Filter berdasarkan tahun jika dipilih
            if ($tahun) {
                $query->where('penganggaran_id', $tahun);
            }

            // Filter berdasarkan tanggal
            if ($startDate) {
                $query->whereHas('bukuKasUmum', function ($q) use ($startDate) {
                    $q->whereDate('tanggal_transaksi', '>=', $startDate);
                });
            }

            if ($endDate) {
                $query->whereHas('bukuKasUmum', function ($q) use ($endDate) {
                    $q->whereDate('tanggal_transaksi', '<=', $endDate);
                });
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

            $kwitansis = $query->latest()->paginate(10);

            // Format data untuk response JSON
            $formattedKwitansis = $kwitansis->map(function ($kwitansi, $index) use ($kwitansis) {
                $number = ($kwitansis->currentPage() - 1) * $kwitansis->perPage() + $index + 1;

                return [
                    'id' => $kwitansi->id,
                    'number' => $number,
                    'kode_rekening' => $kwitansi->rekeningBelanja->kode_rekening ?? '-',
                    'uraian' => $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian,
                    'tanggal' => \Carbon\Carbon::parse($kwitansi->bukuKasUmum->tanggal_transaksi)->format('d/m/Y'),
                    'jumlah' => 'Rp ' . number_format($kwitansi->bukuKasUmum->total_transaksi_kotor, 0, ',', '.'),
                    'preview_url' => route('kwitansi.preview', $kwitansi->id),
                    'pdf_url' => route('kwitansi.pdf', $kwitansi->id),
                    'delete_url' => route('kwitansi.destroy', $kwitansi->id),
                    'delete_data' => [
                        'id' => $kwitansi->id,
                        'uraian' => $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian
                    ]
                ];
            });

            // Prepare filter info for display
            $filterInfo = [
                'search' => $search,
                'tahun' => $tahun,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'has_filters' => $search || $tahun || $startDate || $endDate,
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedKwitansis,
                'total' => $kwitansis->total(),
                'search_term' => $search,
                'selected_tahun' => $tahun,
                'filter_info' => $filterInfo,
                'pagination' => [
                    'current_page' => $kwitansis->currentPage(),
                    'last_page' => $kwitansis->lastPage(),
                    'per_page' => $kwitansis->perPage(),
                    'total' => $kwitansis->total(),
                    'has_more' => $kwitansis->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching kwitansi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $penganggarans = Penganggaran::with(['sekolah'])->get();
        $bukuKasUmums = BukuKasUmum::with(['kodeKegiatan', 'rekeningBelanja', 'uraianDetails', 'penganggaran.sekolah'])
            ->where('is_bunga_record', false)
            ->get();

        return view('kwitansi.create', compact('penganggarans', 'bukuKasUmums'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'buku_kas_umum_id' => 'required|exists:buku_kas_umums,id',
                'bku_uraian_detail_id' => 'required|exists:bku_uraian_details,id',
            ]);

            $bukuKasUmum = BukuKasUmum::with(['penganggaran.sekolah', 'kodeKegiatan', 'rekeningBelanja'])->find($validated['buku_kas_umum_id']);
            $bkuUraianDetail = BukuKasUmumUraianDetail::find($validated['bku_uraian_detail_id']);

            // Cek apakah kwitansi sudah ada untuk detail ini
            $existingKwitansi = Kwitansi::where('bku_uraian_detail_id', $validated['bku_uraian_detail_id'])->first();

            if ($existingKwitansi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kwitansi untuk detail uraian ini sudah ada',
                ], 422);
            }

            // Cari penerimaan dana berdasarkan penganggaran
            $penerimaanDana = PenerimaanDana::where('penganggaran_id', $bukuKasUmum->penganggaran_id)->first();

            if (! $penerimaanDana) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penerimaan dana tidak ditemukan untuk penganggaran ini',
                ], 404);
            }

            // Dapatkan sekolah_id dari penganggaran
            $sekolahId = $bukuKasUmum->penganggaran->sekolah_id;

            // Jika masih null, cari sekolah default
            if (! $sekolahId) {
                $sekolah = Sekolah::first();
                if ($sekolah) {
                    $sekolahId = $sekolah->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data sekolah tidak ditemukan',
                    ], 404);
                }
            }

            $kwitansi = Kwitansi::create([
                'sekolah_id' => $sekolahId,
                'penganggaran_id' => $bukuKasUmum->penganggaran_id,
                'kode_kegiatan_id' => $bukuKasUmum->kode_kegiatan_id,
                'kode_rekening_id' => $bukuKasUmum->kode_rekening_id,
                'penerimaan_dana_id' => $penerimaanDana->id,
                'buku_kas_umum_id' => $bukuKasUmum->id,
                'bku_uraian_detail_id' => $bkuUraianDetail->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kwitansi berhasil dibuat!',
                'data' => $kwitansi,
                'redirect_url' => route('kwitansi.index'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating kwitansi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kwitansi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Kwitansi $kwitansi)
    {
        $kwitansi->load([
            'sekolah',
            'penganggaran',
            'kodeKegiatan',
            'rekeningBelanja',
            'penerimaanDana',
            'bukuKasUmum',
            'bkuUraianDetail',
        ]);

        return view('kwitansi.show', compact('kwitansi'));
    }

    /**
     * Hitung total amount dari uraian details
     */
    private function calculateTotalFromUraianDetails($bukuKasUmum)
    {
        if ($bukuKasUmum->uraianDetails && $bukuKasUmum->uraianDetails->count() > 0) {
            return $bukuKasUmum->uraianDetails->sum('subtotal');
        }

        // Fallback ke total_transaksi_kotor jika tidak ada uraian details
        return $bukuKasUmum->total_transaksi_kotor ?? 0;
    }

    /**
     * Memecah kode kegiatan menjadi bagian-bagian
     */
    private function parseKodeKegiatan($kodeKegiatan)
    {
        $defaultProgram = '-';
        $defaultSubProgram = '-';
        $defaultUraian = '-';

        if (! $kodeKegiatan) {
            return [
                'kode_full' => '06.05.01',
                'kode_program' => '06',
                'kode_sub_program' => '06.05',
                'kode_uraian' => '06.05.01',
                'program' => $defaultProgram,
                'sub_program' => $defaultSubProgram,
                'uraian' => $defaultUraian,
            ];
        }

        // Ambil kode dari model
        $kode = $kodeKegiatan->kode ?? '-';

        // Split kode berdasarkan titik
        $kodeParts = explode('.', $kode);

        // Bangun bagian-bagian kode
        $kodeProgram = $kodeParts[0] ?? '-';
        $kodeSubProgram = ($kodeParts[0] ?? '-') . '.' . ($kodeParts[1] ?? '-');
        $kodeUraian = $kode;

        return [
            'kode_full' => $kode,
            'kode_program' => $kodeProgram,
            'kode_sub_program' => $kodeSubProgram,
            'kode_uraian' => $kodeUraian,
            'program' => $kodeKegiatan->program ?? $defaultProgram,
            'sub_program' => $kodeKegiatan->sub_program ?? $defaultSubProgram,
            'uraian' => $kodeKegiatan->uraian ?? $defaultUraian,
        ];
    }

    // Di KwitansiController.php - PERBAIKI method generatePdf
    public function generatePdf($id)
    {
        try {
            $kwitansi = Kwitansi::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum' => function ($query) {
                    $query->with(['uraianDetails']); // Pastikan memuat uraianDetails
                },
                'bkuUraianDetail',
            ])->findOrFail($id);

            // Parse kode kegiatan
            $parsedKode = $this->parseKodeKegiatan($kwitansi->kodeKegiatan);

            // Konversi angka ke teks - gunakan total dari uraian details jika ada
            $totalAmount = $this->calculateTotalFromUraianDetails($kwitansi->bukuKasUmum);
            $jumlahUang = $this->convertToText($totalAmount);

            // Klasifikasi pajak
            $pajakData = $this->klasifikasiPajak($kwitansi->bukuKasUmum);

            $data = [
                'kwitansi' => $kwitansi,
                'parsedKode' => $parsedKode,
                'jumlahUangText' => $jumlahUang,
                'totalAmount' => $totalAmount, // Kirim totalAmount ke view
                'tanggalLunas' => $this->formatTanggalLunas($kwitansi->bukuKasUmum->tanggal_transaksi),
                'pajakData' => $pajakData,
            ];

            $pdf = PDF::loadView('kwitansi.pdf', $data);
            $pdf->setPaper('Folio', 'portrait');

            $filename = "Kwitansi_{$kwitansi->bukuKasUmum->uraian_opsional}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating kwitansi PDF: ' . $e->getMessage());

            return redirect()->route('kwitansi.index')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    // Tambahkan method downloadAll di KwitansiController
    public function downloadAll(Request $request)
    {
        try {
            // Ambil parameter filter dari request
            $search = $request->input('search', '');
            $tahun = $request->input('tahun', '');
            $startDate = $request->input('start_date', '');
            $endDate = $request->input('end_date', '');

            // Query dengan filter yang sama seperti search
            $query = Kwitansi::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum' => function ($query) {
                    $query->with(['uraianDetails']);
                },
                'bkuUraianDetail',
            ]);

            // Filter berdasarkan tahun jika dipilih
            if ($tahun) {
                $query->where('penganggaran_id', $tahun);
            }

            // Filter berdasarkan tanggal
            if ($startDate) {
                $query->whereHas('bukuKasUmum', function ($q) use ($startDate) {
                    $q->whereDate('tanggal_transaksi', '>=', $startDate);
                });
            }

            if ($endDate) {
                $query->whereHas('bukuKasUmum', function ($q) use ($endDate) {
                    $q->whereDate('tanggal_transaksi', '<=', $endDate);
                });
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

            $kwitansis = $query->latest()->get();

            // Jika tidak ada data
            if ($kwitansis->isEmpty()) {
                return redirect()->route('kwitansi.index')
                    ->with('error', 'Tidak ada data kwitansi untuk diunduh dengan filter yang dipilih');
            }

            // Siapkan data untuk PDF
            $kwitansiData = [];
            foreach ($kwitansis as $kwitansi) {
                // Parse kode kegiatan
                $parsedKode = $this->parseKodeKegiatan($kwitansi->kodeKegiatan);

                // Hitung total amount
                $totalAmount = $this->calculateTotalFromUraianDetails($kwitansi->bukuKasUmum);
                $jumlahUang = $this->convertToText($totalAmount);

                // Klasifikasi pajak
                $pajakData = $this->klasifikasiPajak($kwitansi->bukuKasUmum);

                $kwitansiData[] = [
                    'kwitansi' => $kwitansi,
                    'parsedKode' => $parsedKode,
                    'jumlahUangText' => $jumlahUang,
                    'totalAmount' => $totalAmount,
                    'tanggalLunas' => $this->formatTanggalLunas($kwitansi->bukuKasUmum->tanggal_transaksi),
                    'pajakData' => $pajakData,
                ];
            }

            $data = [
                'kwitansis' => $kwitansiData,
                'totalKwitansi' => $kwitansis->count(),
                'tanggalDownload' => now()->format('d/m/Y H:i'),
                'filterInfo' => [
                    'search' => $search,
                    'tahun' => $tahun,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'has_filter' => $search || $tahun || $startDate || $endDate
                ]
            ];

            // Generate PDF
            $pdf = PDF::loadView('kwitansi.download-all', $data);
            $pdf->setPaper('Folio', 'portrait');

            // Generate filename berdasarkan filter
            $filename = $this->generateDownloadFilename($search, $tahun, $startDate, $endDate, $kwitansis->count());

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error downloading all kwitansi: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->route('kwitansi.index')
                ->with('error', 'Gagal mengunduh semua kwitansi: ' . $e->getMessage());
        }
    }

    // Helper method untuk generate filename berdasarkan filter
    private function generateDownloadFilename($search, $tahun, $startDate, $endDate, $count)
    {
        $baseName = 'Kwitansi';
        $parts = [];

        // Tambahkan info count
        $parts[] = $count . 'data';

        // Tambahkan info tahun
        if ($tahun) {
            $tahunSelect = \App\Models\Penganggaran::find($tahun);
            if ($tahunSelect) {
                $parts[] = 'Tahun_' . $tahunSelect->tahun_anggaran;
            }
        }

        // Tambahkan info tanggal
        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->format('d-m-Y');
            $end = \Carbon\Carbon::parse($endDate)->format('d-m-Y');
            $parts[] = $start . '_sd_' . $end;
        } elseif ($startDate) {
            $parts[] = 'dari_' . \Carbon\Carbon::parse($startDate)->format('d-m-Y');
        } elseif ($endDate) {
            $parts[] = 'sampai_' . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        }

        // Tambahkan timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        $filename = $baseName . '_' . implode('_', $parts) . '.pdf';

        // Hapus karakter yang tidak valid untuk filename
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    // PERBAIKAN: Method klasifikasiPajak yang lebih robust
    private function klasifikasiPajak($bukuKasUmum)
    {
        $pajakName = strtolower($bukuKasUmum->pajak ?? '');
        $pajakDaerahName = strtolower($bukuKasUmum->pajak_daerah ?? '');

        $pajakData = [
            'ppn' => 0,
            'pph' => 0,
            'pb1' => 0,
        ];

        // Debug logging
        Log::info("Klasifikasi Pajak - Pajak: {$bukuKasUmum->pajak}, Total Pajak: {$bukuKasUmum->total_pajak}, Pajak Daerah: {$bukuKasUmum->pajak_daerah}, Total Pajak Daerah: {$bukuKasUmum->total_pajak_daerah}");

        // Klasifikasi Pajak Pusat
        if ($bukuKasUmum->total_pajak > 0) {
            // Jika ada nama pajak spesifik, klasifikasikan berdasarkan nama
            if (strpos($pajakName, 'pph') !== false) {
                $pajakData['pph'] = $bukuKasUmum->total_pajak;
            } elseif (strpos($pajakName, 'ppn') !== false) {
                $pajakData['ppn'] = $bukuKasUmum->total_pajak;
            } else {
                // Default: jika tidak ada indikasi PPh, anggap sebagai PPn
                $pajakData['ppn'] = $bukuKasUmum->total_pajak;
            }
        }

        // Pajak Daerah (PB1)
        if ($bukuKasUmum->total_pajak_daerah > 0) {
            $pajakData['pb1'] = $bukuKasUmum->total_pajak_daerah;
        }

        // PERBAIKAN: Jika ada data pajak tapi total_pajak = 0, cek field lain
        if ($bukuKasUmum->total_pajak == 0 && $bukuKasUmum->pajak) {
            // Coba ekstrak nilai dari nama pajak
            $pajakValue = $this->extractPajakValueFromName($bukuKasUmum->pajak);
            if ($pajakValue > 0) {
                if (strpos($pajakName, 'pph') !== false) {
                    $pajakData['pph'] = $pajakValue;
                } else {
                    $pajakData['ppn'] = $pajakValue;
                }
            }
        }

        Log::info("Hasil Klasifikasi Pajak - PPn: {$pajakData['ppn']}, PPh: {$pajakData['pph']}, PB1: {$pajakData['pb1']}");

        return $pajakData;
    }

    /**
     * Helper untuk mengekstrak nilai pajak dari nama pajak
     */
    private function extractPajakValueFromName($pajakName)
    {
        // Cari angka dalam string
        preg_match('/\d+/', $pajakName, $matches);
        if (! empty($matches)) {
            return (float) $matches[0];
        }

        return 0;
    }

    // Di KwitansiController - PERBAIKI method previewPdf
    public function previewPdf($id)
    {
        try {
            Log::info('Starting PDF preview for kwitansi ID: ' . $id);

            $kwitansi = Kwitansi::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum' => function ($query) {
                    $query->with(['uraianDetails']);
                },
                'bkuUraianDetail',
            ])->findOrFail($id);

            Log::info('Kwitansi found: ' . $kwitansi->id);

            // Parse kode kegiatan
            $parsedKode = $this->parseKodeKegiatan($kwitansi->kodeKegiatan);

            // Hitung total dari uraian details
            $totalAmount = $this->calculateTotalFromUraianDetails($kwitansi->bukuKasUmum);
            $jumlahUang = $this->convertToText($totalAmount);

            // Klasifikasi pajak
            $pajakData = $this->klasifikasiPajak($kwitansi->bukuKasUmum);

            $data = [
                'kwitansi' => $kwitansi,
                'parsedKode' => $parsedKode,
                'jumlahUangText' => $jumlahUang,
                'totalAmount' => $totalAmount,
                'tanggalLunas' => $this->formatTanggalLunas($kwitansi->bukuKasUmum->tanggal_transaksi),
                'pajakData' => $pajakData,
            ];

            Log::info('Data prepared for PDF, generating...');

            // Generate PDF untuk preview (STREAM, bukan download)
            $pdf = PDF::loadView('kwitansi.pdf', $data);
            $pdf->setPaper('Folio', 'portrait');

            Log::info('PDF generated successfully for ID: ' . $id);

            // Return PDF sebagai response dengan header yang tepat untuk preview
            return $pdf->stream('Kwitansi_Preview_' . $kwitansi->id . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating preview PDF for ID ' . $id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate preview PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Tambahkan method previewModal di KwitansiController
    public function previewModal($id)
    {
        try {
            $kwitansi = Kwitansi::with([
                'sekolah',
                'penganggaran',
                'kodeKegiatan',
                'rekeningBelanja',
                'penerimaanDana',
                'bukuKasUmum' => function ($query) {
                    $query->with(['uraianDetails']);
                },
                'bkuUraianDetail',
            ])->findOrFail($id);

            // Parse kode kegiatan
            $parsedKode = $this->parseKodeKegiatan($kwitansi->kodeKegiatan);

            // Hitung total dari uraian details
            $totalAmount = $this->calculateTotalFromUraianDetails($kwitansi->bukuKasUmum);
            $jumlahUang = $this->convertToText($totalAmount);

            // Klasifikasi pajak
            $pajakData = $this->klasifikasiPajak($kwitansi->bukuKasUmum);

            $data = [
                'kwitansi' => $kwitansi,
                'parsedKode' => $parsedKode,
                'jumlahUangText' => $jumlahUang,
                'totalAmount' => $totalAmount,
                'tanggalLunas' => $this->formatTanggalLunas($kwitansi->bukuKasUmum->tanggal_transaksi),
                'pajakData' => $pajakData,
            ];

            return view('kwitansi.pdf', $data);
        } catch (\Exception $e) {
            Log::error('Error preview modal kwitansi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Cari kwitansi by ID
            $kwitansi = Kwitansi::with(['bukuKasUmum'])->find($id);

            if (! $kwitansi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kwitansi tidak ditemukan',
                ], 404);
            }

            $uraian = $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian;

            // Hapus kwitansi
            $kwitansi->delete();

            return response()->json([
                'success' => true,
                'message' => "Kwitansi untuk '{$uraian}' berhasil dihapus!",
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting kwitansi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kwitansi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // AJAX method untuk mendapatkan detail transaksi
    public function getDetailTransaksi($bukuKasUmumId)
    {
        try {
            $bukuKasUmum = BukuKasUmum::with(['uraianDetails', 'kodeKegiatan', 'rekeningBelanja', 'penganggaran.sekolah'])
                ->findOrFail($bukuKasUmumId);

            return response()->json([
                'success' => true,
                'data' => $bukuKasUmum,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }

    public function checkAvailableData()
    {
        try {
            // PERBAIKAN: Hitung dari BukuKasUmum yang belum memiliki kwitansi
            $availableCount = BukuKasUmum::whereDoesntHave('kwitansi')
                ->where('is_bunga_record', false)
                ->count();

            Log::info("Available BukuKasUmum count: {$availableCount}");

            return response()->json([
                'success' => true,
                'data' => [
                    'available_count' => $availableCount,
                    'pending_count' => 0,
                    'failed_count' => 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking available data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate Batch tanpa progress tracking - MODIFIKASI BARU
     */
    public function generateBatch(Request $request)
    {
        try {
            // Tampilkan loading sederhana
            Log::info("Starting batch generation without progress tracking");

            // Hitung total dari BukuKasUmum yang belum memiliki kwitansi
            $totalWithoutKwitansi = BukuKasUmum::whereDoesntHave('kwitansi')
                ->where('is_bunga_record', false)
                ->count();

            Log::info("Total BukuKasUmum without kwitansi: {$totalWithoutKwitansi}");

            // Jika tidak ada data, return completion
            if ($totalWithoutKwitansi === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada data yang perlu digenerate.',
                    'data' => [
                        'processed' => 0,
                        'success' => 0,
                        'failed' => 0,
                        'total' => 0
                    ]
                ]);
            }

            // HAPUS LIMIT DAN OFFSET - ambil semua data sekaligus
            $bukuKasUmums = BukuKasUmum::with([
                'penganggaran.sekolah',
                'kodeKegiatan',
                'rekeningBelanja',
                'uraianDetails',
            ])
                ->whereDoesntHave('kwitansi')
                ->where('is_bunga_record', false)
                ->orderBy('id')
                ->get(); // HAPUS ->take() dan ->skip()

            Log::info("Found {$bukuKasUmums->count()} BukuKasUmum to process");

            $success = 0;
            $failed = 0;

            DB::beginTransaction();

            try {
                foreach ($bukuKasUmums as $bukuKasUmum) {
                    $result = $this->processSingleItem($bukuKasUmum);

                    if ($result['status'] === 'success') {
                        $success++;
                    } else {
                        $failed++;
                    }
                }

                DB::commit();

                Log::info("Batch generation completed - Success: {$success}, Failed: {$failed}");

                return response()->json([
                    'success' => true,
                    'message' => "Proses generate selesai. Berhasil: {$success}, Gagal: {$failed}",
                    'data' => [
                        'processed' => $bukuKasUmums->count(),
                        'success' => $success,
                        'failed' => $failed,
                        'total' => $totalWithoutKwitansi,
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction failed in generateBatch: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in generateBatch: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat proses generate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method untuk process single item
     */
    private function processSingleItem($bukuKasUmum)
    {
        $itemResult = [
            'buku_kas_umum_id' => $bukuKasUmum->id,
            'kode_rekening' => $bukuKasUmum->rekeningBelanja->kode_rekening ?? '-',
            'uraian' => $bukuKasUmum->uraian_opsional ?? $bukuKasUmum->uraian,
            'status' => 'pending',
            'message' => ''
        ];

        try {
            $existingKwitansi = Kwitansi::where('buku_kas_umum_id', $bukuKasUmum->id)->first();

            if (!$existingKwitansi) {
                $penerimaanDana = PenerimaanDana::where('penganggaran_id', $bukuKasUmum->penganggaran_id)->first();

                if ($penerimaanDana) {
                    $sekolahId = $bukuKasUmum->penganggaran->sekolah_id;
                    if (!$sekolahId) {
                        $sekolah = Sekolah::first();
                        $sekolahId = $sekolah->id ?? null;
                    }

                    if ($sekolahId) {
                        $bkuUraianDetail = $bukuKasUmum->uraianDetails->first();

                        if ($bkuUraianDetail) {
                            Kwitansi::create([
                                'sekolah_id' => $sekolahId,
                                'penganggaran_id' => $bukuKasUmum->penganggaran_id,
                                'kode_kegiatan_id' => $bukuKasUmum->kode_kegiatan_id,
                                'kode_rekening_id' => $bukuKasUmum->kode_rekening_id,
                                'penerimaan_dana_id' => $penerimaanDana->id,
                                'buku_kas_umum_id' => $bukuKasUmum->id,
                                'bku_uraian_detail_id' => $bkuUraianDetail->id,
                            ]);
                            $itemResult['status'] = 'success';
                            $itemResult['message'] = 'Kwitansi berhasil dibuat';
                        } else {
                            $itemResult['status'] = 'failed';
                            $itemResult['message'] = 'Tidak ada detail uraian';
                        }
                    } else {
                        $itemResult['status'] = 'failed';
                        $itemResult['message'] = 'ID Sekolah tidak ditemukan';
                    }
                } else {
                    $itemResult['status'] = 'failed';
                    $itemResult['message'] = 'Data penerimaan dana tidak ditemukan';
                }
            } else {
                $itemResult['status'] = 'skipped';
                $itemResult['message'] = 'Kwitansi sudah ada';
            }
        } catch (\Exception $e) {
            $itemResult['status'] = 'error';
            $itemResult['message'] = 'Error: ' . $e->getMessage();
        }

        return $itemResult;
    }

    public function deleteAll(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'confirm_text' => 'required|in:HAPUS-SEMUA-KWITANSI',
            ]);

            // Hitung total kwitansi sebelum dihapus
            $totalKwitansi = Kwitansi::count();

            if ($totalKwitansi === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data kwitansi yang dapat dihapus',
                ], 404);
            }

            // Hapus semua data kwitansi
            $deletedCount = Kwitansi::query()->delete();

            // Log activity
            Log::info("Deleted all kwitansi: {$deletedCount} records deleted by user");

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} data kwitansi",
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting all kwitansi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus semua data kwitansi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // PERBAIKAN: Method debug untuk melihat data sebenarnya
    public function debugDataCount()
    {
        try {
            $totalBukuKasUmum = BukuKasUmum::where('is_bunga_record', false)->count();
            $totalWithoutKwitansi = BukuKasUmum::whereDoesntHave('kwitansi')
                ->where('is_bunga_record', false)
                ->count();
            $totalWithKwitansi = BukuKasUmum::whereHas('kwitansi')
                ->where('is_bunga_record', false)
                ->count();

            Log::info("Debug Data Count - Total BKU: {$totalBukuKasUmum}, Without Kwitansi: {$totalWithoutKwitansi}, With Kwitansi: {$totalWithKwitansi}");

            return response()->json([
                'success' => true,
                'data' => [
                    'total_buku_kas_umum' => $totalBukuKasUmum,
                    'without_kwitansi' => $totalWithoutKwitansi,
                    'with_kwitansi' => $totalWithKwitansi,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in debugDataCount: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Helper methods
    private function convertToText($number)
    {
        $number = (int) $number;

        $units = ['', 'ribu', 'juta', 'miliar', 'triliun'];
        $words = [];

        if ($number == 0) {
            return 'nol Rupiah';
        }

        // Handle ribuan, jutaan, etc.
        $unitIndex = 0;
        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk != 0) {
                $chunkWords = $this->convertChunk($chunk);
                if ($unitIndex > 0) {
                    $chunkWords .= ' ' . $units[$unitIndex];
                }
                array_unshift($words, $chunkWords);
            }
            $number = floor($number / 1000);
            $unitIndex++;
        }

        $result = implode(' ', $words) . ' Rupiah';

        return ucfirst(strtolower($result));
    }

    private function convertChunk($number)
    {
        $ones = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
        $tens = ['', '', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh', 'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh'];
        $teens = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];

        $words = [];

        // Ratusan
        $hundreds = floor($number / 100);
        if ($hundreds > 0) {
            if ($hundreds == 1) {
                $words[] = 'seratus';
            } else {
                $words[] = $ones[$hundreds] . ' ratus';
            }
            $number %= 100;
        }

        // Puluhan dan satuan
        if ($number >= 10 && $number <= 19) {
            $words[] = $teens[$number - 10];
        } else {
            $tensDigit = floor($number / 10);
            $onesDigit = $number % 10;

            if ($tensDigit > 0) {
                $words[] = $tens[$tensDigit];
            }

            if ($onesDigit > 0) {
                $words[] = $ones[$onesDigit];
            }
        }

        return implode(' ', $words);
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
