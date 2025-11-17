<?php

namespace App\Http\Controllers;

use App\Models\BukuKasUmum;
use App\Models\Penganggaran;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BukuPajakController extends Controller
{
    /**
     * Get data pajak untuk transaksi tertentu
     */
    public function getDataPajak($id)
    {
        try {
            $bku = BukuKasUmum::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'tanggal_lapor' => $bku->tanggal_lapor ? $bku->tanggal_lapor->format('Y-m-d') : null,
                    'ntpn' => $bku->ntpn,
                    'kode_masa_pajak' => $bku->kode_masa_pajak,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pajak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan laporan pajak
     */
    public function laporPajak(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'tanggal_lapor' => 'required|date',
                'kode_masa_pajak' => 'required|string',
                'ntpn' => 'required|string|max:16|min:16',
            ], [
                'ntpn.required' => 'NTPN wajib diisi',
                'ntpn.max' => 'NTPN harus 16 digit',
                'ntpn.min' => 'NTPN harus 16 digit',
                'kode_masa_pajak.required' => 'Kode masa pajak wajib diisi',
                'tanggal_lapor.required' => 'Tanggal lapor wajib diisi',
                'tanggal_lapor.date' => 'Format tanggal tidak valid',
            ]);

            $bku = BukuKasUmum::findOrFail($id);

            // Update data pajak
            $bku->update([
                'tanggal_lapor' => $validated['tanggal_lapor'],
                'kode_masa_pajak' => $validated['kode_masa_pajak'],
                'ntpn' => $validated['ntpn'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pajak berhasil disimpan',
                'data' => $bku,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan lapor pajak: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data pajak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data BKP Pajak
     */
    public function getBkpPajakData($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penganggaran tidak ditemukan',
                ], 404);
            }

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil data transaksi BKU yang memiliki pajak
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where(function ($query) {
                    $query->where('total_pajak', '>', 0)
                        ->orWhere('total_pajak_daerah', '>', 0);
                })
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Siapkan data untuk tampilan (bisa multiple rows per transaksi)
            $pajakRows = [];
            $runningPenerimaan = 0;
            $runningPengeluaran = 0;
            $currentSaldo = 0;

            // Variabel untuk total
            $totalPpn = 0;
            $totalPph21 = 0;
            $totalPph22 = 0;
            $totalPph23 = 0;
            $totalPb1 = 0;
            $totalPenerimaan = 0;
            $totalPengeluaran = 0;

            foreach ($bkuData as $transaksi) {
                $pajakName = strtolower($transaksi->pajak ?? '');
                $pajakDaerahName = strtolower($transaksi->pajak_daerah ?? '');

                $baseUraian = $transaksi->uraian_opsional ?? $transaksi->uraian ?? '';
                $hasPajakPusat = $transaksi->total_pajak > 0;
                $hasPajakDaerah = $transaksi->total_pajak_daerah > 0;

                // Jika ada pajak pusat, buat baris terpisah
                if ($hasPajakPusat) {
                    $ppn = 0;
                    $pph21 = 0;
                    $pph22 = 0;
                    $pph23 = 0;

                    // Klasifikasi Pajak Pusat
                    if (strpos($pajakName, 'pph21') !== false || strpos($pajakName, 'pph 21') !== false) {
                        $pph21 = $transaksi->total_pajak;
                        $totalPph21 += $pph21;
                    } elseif (strpos($pajakName, 'pph22') !== false || strpos($pajakName, 'pph 22') !== false) {
                        $pph22 = $transaksi->total_pajak;
                        $totalPph22 += $pph22;
                    } elseif (strpos($pajakName, 'pph23') !== false || strpos($pajakName, 'pph 23') !== false) {
                        $pph23 = $transaksi->total_pajak;
                        $totalPph23 += $pph23;
                    } else {
                        $ppn = $transaksi->total_pajak;
                        $totalPpn += $ppn;
                    }

                    $jumlah = $ppn + $pph21 + $pph22 + $pph23;
                    $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                    $totalPengeluaran += $pengeluaran;

                    // Tentukan uraian untuk pajak pusat
                    $uraianPusat = '';
                    if (!empty($transaksi->ntpn)) {
                        if ($pph21 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph22 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph23 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianPusat = 'Setor Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        }
                    } else {
                        if ($pph21 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph22 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph23 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianPusat = 'Terima Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        }
                    }

                    // Update running total
                    if ($jumlah != 0 || $pengeluaran != 0) {
                        $runningPenerimaan += $jumlah;
                        $runningPengeluaran += $pengeluaran;
                        $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }

                    $pajakRows[] = [
                        'transaksi' => $transaksi,
                        'uraian' => $uraianPusat,
                        'ppn' => $ppn,
                        'pph21' => $pph21,
                        'pph22' => $pph22,
                        'pph23' => $pph23,
                        'pb1' => 0,
                        'jumlah' => $jumlah,
                        'pengeluaran' => $pengeluaran,
                        'saldo' => $currentSaldo
                    ];
                }

                // Jika ada pajak daerah (PB 1), buat baris terpisah
                if ($hasPajakDaerah) {
                    $pb1 = $transaksi->total_pajak_daerah;
                    $totalPb1 += $pb1;

                    $jumlah = $pb1;
                    $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                    $totalPengeluaran += $pengeluaran;

                    // Tentukan uraian untuk pajak daerah
                    $uraianDaerah = '';
                    if (!empty($transaksi->ntpn)) {
                        $uraianDaerah = 'Setor Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                    } else {
                        $uraianDaerah = 'Terima Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                    }

                    // Update running total
                    if ($jumlah != 0 || $pengeluaran != 0) {
                        $runningPenerimaan += $jumlah;
                        $runningPengeluaran += $pengeluaran;
                        $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }

                    $pajakRows[] = [
                        'transaksi' => $transaksi,
                        'uraian' => $uraianDaerah,
                        'ppn' => 0,
                        'pph21' => 0,
                        'pph22' => 0,
                        'pph23' => 0,
                        'pb1' => $pb1,
                        'jumlah' => $jumlah,
                        'pengeluaran' => $pengeluaran,
                        'saldo' => $currentSaldo
                    ];
                }
            }

            $totalPenerimaan = $totalPpn + $totalPph21 + $totalPph22 + $totalPph23 + $totalPb1;

            $html = view('laporan.partials.bkp-pajak-table', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulanAngka' => $bulanAngka,
                'pajakRows' => $pajakRows,
                'totalPph21' => $totalPph21,
                'totalPph22' => $totalPph22,
                'totalPph23' => $totalPph23,
                'totalPb1' => $totalPb1,
                'totalPpn' => $totalPpn,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => [
                    'total_penerimaan' => $totalPenerimaan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_akhir' => $currentSaldo,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP Pajak data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Pajak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF BKP Pajak - VERSI DIPERBAIKI DENGAN MULTI ROWS
     */
    public function generateBkpPajakPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil data transaksi BKU yang memiliki pajak
            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where(function ($query) {
                    $query->where('total_pajak', '>', 0)
                        ->orWhere('total_pajak_daerah', '>', 0);
                })
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Siapkan data untuk tampilan (bisa multiple rows per transaksi)
            $pajakRows = [];
            $runningPenerimaan = 0;
            $runningPengeluaran = 0;
            $currentSaldo = 0;

            // Variabel untuk total
            $totalPpn = 0;
            $totalPph21 = 0;
            $totalPph22 = 0;
            $totalPph23 = 0;
            $totalPb1 = 0;
            $totalPenerimaan = 0;
            $totalPengeluaran = 0;

            foreach ($bkuData as $transaksi) {
                $pajakName = strtolower($transaksi->pajak ?? '');
                $pajakDaerahName = strtolower($transaksi->pajak_daerah ?? '');

                $baseUraian = $transaksi->uraian_opsional ?? $transaksi->uraian ?? '';
                $hasPajakPusat = $transaksi->total_pajak > 0;
                $hasPajakDaerah = $transaksi->total_pajak_daerah > 0;

                // Jika ada pajak pusat, buat baris terpisah
                if ($hasPajakPusat) {
                    $ppn = 0;
                    $pph21 = 0;
                    $pph22 = 0;
                    $pph23 = 0;

                    // Klasifikasi Pajak Pusat
                    if (strpos($pajakName, 'pph21') !== false || strpos($pajakName, 'pph 21') !== false) {
                        $pph21 = $transaksi->total_pajak;
                        $totalPph21 += $pph21;
                    } elseif (strpos($pajakName, 'pph22') !== false || strpos($pajakName, 'pph 22') !== false) {
                        $pph22 = $transaksi->total_pajak;
                        $totalPph22 += $pph22;
                    } elseif (strpos($pajakName, 'pph23') !== false || strpos($pajakName, 'pph 23') !== false) {
                        $pph23 = $transaksi->total_pajak;
                        $totalPph23 += $pph23;
                    } else {
                        $ppn = $transaksi->total_pajak;
                        $totalPpn += $ppn;
                    }

                    $jumlah = $ppn + $pph21 + $pph22 + $pph23;
                    $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                    $totalPengeluaran += $pengeluaran;

                    // Tentukan uraian untuk pajak pusat
                    $uraianPusat = '';
                    if (!empty($transaksi->ntpn)) {
                        if ($pph21 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph22 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph23 > 0) {
                            $uraianPusat = 'Setor Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianPusat = 'Setor Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        }
                    } else {
                        if ($pph21 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 21 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph22 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 22 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } elseif ($pph23 > 0) {
                            $uraianPusat = 'Terima Pajak PPh 23 ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        } else {
                            $uraianPusat = 'Terima Pajak ' . ($transaksi->pajak ?? '') . ' ' . ($transaksi->persen_pajak ?? '') . '% ' . $baseUraian;
                        }
                    }

                    // Update running total
                    if ($jumlah != 0 || $pengeluaran != 0) {
                        $runningPenerimaan += $jumlah;
                        $runningPengeluaran += $pengeluaran;
                        $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }

                    $pajakRows[] = [
                        'transaksi' => $transaksi,
                        'uraian' => $uraianPusat,
                        'ppn' => $ppn,
                        'pph21' => $pph21,
                        'pph22' => $pph22,
                        'pph23' => $pph23,
                        'pb1' => 0,
                        'jumlah' => $jumlah,
                        'pengeluaran' => $pengeluaran,
                        'saldo' => $currentSaldo
                    ];
                }

                // Jika ada pajak daerah (PB 1), buat baris terpisah
                if ($hasPajakDaerah) {
                    $pb1 = $transaksi->total_pajak_daerah;
                    $totalPb1 += $pb1;

                    $jumlah = $pb1;
                    $pengeluaran = (!empty($transaksi->ntpn)) ? $jumlah : 0;
                    $totalPengeluaran += $pengeluaran;

                    // Tentukan uraian untuk pajak daerah
                    $uraianDaerah = '';
                    if (!empty($transaksi->ntpn)) {
                        $uraianDaerah = 'Setor Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                    } else {
                        $uraianDaerah = 'Terima Pajak PB 1 ' . ($transaksi->persen_pajak_daerah ?? '') . '% ' . $baseUraian;
                    }

                    // Update running total
                    if ($jumlah != 0 || $pengeluaran != 0) {
                        $runningPenerimaan += $jumlah;
                        $runningPengeluaran += $pengeluaran;
                        $currentSaldo = $runningPenerimaan - $runningPengeluaran;
                    }

                    $pajakRows[] = [
                        'transaksi' => $transaksi,
                        'uraian' => $uraianDaerah,
                        'ppn' => 0,
                        'pph21' => 0,
                        'pph22' => 0,
                        'pph23' => 0,
                        'pb1' => $pb1,
                        'jumlah' => $jumlah,
                        'pengeluaran' => $pengeluaran,
                        'saldo' => $currentSaldo
                    ];
                }
            }

            $totalPenerimaan = $totalPpn + $totalPph21 + $totalPph22 + $totalPph23 + $totalPb1;

            $printSettings = [
                'ukuran_kertas' => request()->input('ukuran_kertas', 'A4'),
                'orientasi' => request()->input('orientasi', 'landscape'),
                'font_size' => request()->input('font_size', '10pt')
            ];

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'pajakRows' => $pajakRows,
                'totalPph21' => $totalPph21,
                'totalPph22' => $totalPph22,
                'totalPph23' => $totalPph23,
                'totalPb1' => $totalPb1,
                'totalPpn' => $totalPpn,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'currentSaldo' => $currentSaldo,
                'tanggalAkhirBulan' => $this->getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => $this->getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => $this->formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => $this->formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => $this->formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
                'printSettings' => $printSettings
            ];

            $pdf = PDF::loadView('laporan.bkp-pajak-pdf', $data);

            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            $filename = "BKP_Pajak_{$bulan}_{$tahun}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Pajak PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods
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
            'Desember' => 12,
        ];

        return $bulanList[$bulan] ?? 1;
    }

    private function convertNumberToBulan($angka)
    {
        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $bulanList[$angka] ?? 'Januari';
    }

    private function getTanggalAkhirBulan($tahun, $bulan)
    {
        $bulanAngka = $this->convertBulanToNumber($bulan);
        return Carbon::create($tahun, $bulanAngka, 1)->endOfMonth();
    }

    private function getHariAkhirBulan($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->locale('id')->dayName;
    }

    private function formatAkhirBulanLengkapHari($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->locale('id')->translatedFormat('l, j F Y');
    }

    private function formatAkhirBulanSingkat($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->format('d/m/Y');
    }

    private function formatTanggalAkhirBulanLengkap($tahun, $bulan)
    {
        $tanggalAkhirBulan = $this->getTanggalAkhirBulan($tahun, $bulan);
        return $tanggalAkhirBulan->locale('id')->translatedFormat('j F Y');
    }
}
