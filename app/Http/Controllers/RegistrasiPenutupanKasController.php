<?php

namespace App\Http\Controllers;

use App\Models\Penganggaran;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\BukuKasService;
use Illuminate\Support\Facades\Log;
use App\Models\BukuKasUmum;

class RegistrasiPenutupanKasController extends Controller
{
    protected $bukuKasService;

    public function __construct(BukuKasService $bukuKasService)
    {
        $this->bukuKasService = $bukuKasService;
    }

    private function getDataFromBkpUmumCalculation($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        return $this->bukuKasService->getDataFromBkpUmumCalculation($penganggaran_id, $tahun, $bulan, $bulanAngka);
    }

    private function getSaldoKasFromPembantu($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        return $this->bukuKasService->getSaldoKasFromPembantu($penganggaran_id, $tahun, $bulan, $bulanAngka);
    }

    private function hitungSaldoBankSebelumBulan($penganggaran_id, $bulanAngka)
    {
        return $this->bukuKasService->hitungSaldoBankSebelumBulan($penganggaran_id, $bulanAngka);
    }

    private function getDenominasiUangKertas($saldoKas)
    {
        return $this->bukuKasService->getDenominasiUangKertas($saldoKas);
    }

    private function hitungTotalUangKertas($uangKertas)
    {
        return $this->bukuKasService->hitungTotalUangKertas($uangKertas);
    }

    private function getDenominasiUangLogam($sisaUntukLogam)
    {
        return $this->bukuKasService->getDenominasiUangLogam($sisaUntukLogam);
    }

    private function hitungTotalUangLogam($uangLogam)
    {
        return $this->bukuKasService->hitungTotalUangLogam($uangLogam);
    }



    /**
     * Get data BKP Registrasi
     */
    public function getBkpRegData($tahun, $bulan)
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

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            // PERBAIKAN: Gunakan data langsung dari perhitungan BKP Umum
            $dataUmum = $this->getDataFromBkpUmumCalculation($penganggaran->id, $tahun, $bulan, $bulanAngka);
            $totalPenerimaan = $dataUmum['totalPenerimaan'];
            $totalPengeluaran = $dataUmum['totalPengeluaran'];
            $saldoBuku = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo kas B dari currentSaldo di tab Pembantu
            $saldoKas = $this->getSaldoKasFromPembantu($penganggaran->id, $tahun, $bulan, $bulanAngka);

            // Hitung saldo bank
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Data uang kertas berdasarkan saldo kas dari Pembantu
            $uangKertas = $this->getDenominasiUangKertas($saldoKas);
            $totalUangKertas = $this->hitungTotalUangKertas($uangKertas);

            // Hitung sisa untuk uang logam
            $sisaUntukLogam = $saldoKas - $totalUangKertas;
            $uangLogam = $this->getDenominasiUangLogam($sisaUntukLogam);
            $totalUangLogam = $this->hitungTotalUangLogam($uangLogam);

            $saldoAkhirBuku = $totalUangKertas + $totalUangLogam + $saldoBank;

            // LOG UNTUK DEBUG
            Log::info('=== DATA REGISTRASI PENUTUPAN ===', [
                'penganggaran_id' => $penganggaran->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank
            ]);

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank,
                'uangKertas' => $uangKertas,
                'uangLogam' => $uangLogam,
                'totalUangKertas' => $totalUangKertas,
                'totalUangLogam' => $totalUangLogam,
                'saldoAkhirBuku' => $saldoAkhirBuku,
                'perbedaan' => $saldoBuku - $saldoKas,
                'penjelasanPerbedaan' => 'Masih ada sebagian dana BOS yang belum diambil di rekening bank. Masih ada sisa tunai yang disimpan bendahara.',
                'tanggalPenutupan' => Carbon::create($tahun, $bulanAngka, 1)->endOfMonth()->format('d F Y'),
                'tanggalPenutupanLalu' => '-',
                'namaBendahara' => $sekolah->bendahara ?? '-',
                'namaKepalaSekolah' => $sekolah->kepala_sekolah ?? '-',
                'nipBendahara' => $sekolah->nip_bendahara ?? '-',
                'nipKepalaSekolah' => $sekolah->nip_kepala_sekolah ?? '-',
            ];

            $html = view('laporan.partials.bkp-registrasi-table', $data)->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error get BKP Registrasi data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BKP Registrasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateRegHtml($penganggaran, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Ambil data sekolah
            $sekolah = \App\Models\Sekolah::first();

            // PERBAIKAN: Gunakan data langsung dari perhitungan BKP Umum
            $dataUmum = $this->getDataFromBkpUmumCalculation($penganggaran->id, $tahun, $bulan, $bulanAngka);
            $totalPenerimaan = $dataUmum['totalPenerimaan'];
            $totalPengeluaran = $dataUmum['totalPengeluaran'];
            $saldoBuku = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo kas B dari currentSaldo di tab Pembantu
            $saldoKas = $this->getSaldoKasFromPembantu($penganggaran->id, $tahun, $bulan, $bulanAngka);

            // Hitung saldo bank
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Data uang kertas berdasarkan saldo kas dari Pembantu
            $uangKertas = $this->getDenominasiUangKertas($saldoKas);
            $totalUangKertas = $this->hitungTotalUangKertas($uangKertas);

            // Hitung sisa untuk uang logam
            $sisaUntukLogam = $saldoKas - $totalUangKertas;
            $uangLogam = $this->getDenominasiUangLogam($sisaUntukLogam);
            $totalUangLogam = $this->hitungTotalUangLogam($uangLogam);

            $saldoAkhirBuku = $totalUangKertas + $totalUangLogam + $saldoBank;

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank,
                'uangKertas' => $uangKertas,
                'uangLogam' => $uangLogam,
                'totalUangKertas' => $totalUangKertas,
                'totalUangLogam' => $totalUangLogam,
                'saldoAkhirBuku' => $saldoAkhirBuku,
                'perbedaan' => $saldoBuku - $saldoKas,
                'penjelasanPerbedaan' => 'Masih ada sebagian dana BOS yang belum diambil di rekening bank. Masih ada sisa tunai yang disimpan bendahara.',
                'tanggalPenutupan' => Carbon::create($tahun, $bulanAngka, 1)->endOfMonth()->format('d F Y'),
                'tanggalPenutupanLalu' => '-',
                'namaBendahara' => $sekolah->bendahara ?? 'Dra. MASITAH ABDULLAH',
                'namaKepalaSekolah' => $sekolah->kepala_sekolah ?? 'Dra. MASITAH ABDULLAH',
                'nipBendahara' => $sekolah->nip_bendahara ?? '19690917 200701 2 017',
                'nipKepalaSekolah' => $sekolah->nip_kepala_sekolah ?? '19690917 200701 2 017',
            ];

            return view('laporan.partials.bkp-registrasi-table', $data)->render();
        } catch (\Exception $e) {
            Log::error('Error generate REG HTML: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Generate PDF BKP Registrasi
     */
    public function generateBkpRegPdf($tahun, $bulan)
    {
        try {
            $penganggaran = Penganggaran::where('tahun_anggaran', $tahun)->first();

            if (!$penganggaran) {
                return response()->json(['error' => 'Data penganggaran tidak ditemukan'], 404);
            }

            // Ambil data sekolah
            $sekolah = Sekolah::first();

            $bulanAngka = $this->convertBulanToNumber($bulan);

            // Ambil total penerimaan dan pengeluaran dari tab Umum
            $dataUmum = $this->getDataFromBkpUmumCalculation($penganggaran->id, $tahun, $bulan, $bulanAngka);
            $totalPenerimaan = $dataUmum['totalPenerimaan'];
            $totalPengeluaran = $dataUmum['totalPengeluaran'];
            $saldoBuku = $totalPenerimaan - $totalPengeluaran;

            // Hitung saldo kas B dari currentSaldo di tab Pembantu
            $saldoKas = $this->getSaldoKasFromPembantu($penganggaran->id, $tahun, $bulan, $bulanAngka);

            // Hitung saldo bank
            $saldoBank = $this->hitungSaldoBankSebelumBulan($penganggaran->id, $bulanAngka + 1);

            // Data uang kertas berdasarkan saldo kas dari Pembantu
            $uangKertas = $this->getDenominasiUangKertas($saldoKas);
            $totalUangKertas = $this->hitungTotalUangKertas($uangKertas);

            // Hitung sisa untuk uang logam
            $sisaUntukLogam = $saldoKas - $totalUangKertas;
            $uangLogam = $this->getDenominasiUangLogam($sisaUntukLogam);
            $totalUangLogam = $this->hitungTotalUangLogam($uangLogam);
            $saldoAkhirBuku = $totalUangKertas + $totalUangLogam + $saldoBank;

            $printSettings = [
                'ukuran_kertas' => request()->input('ukuran_kertas', 'A4'),
                'orientasi' => request()->input('orientasi', 'potrait'),
                'font_size' => request()->input('font_size', '10pt')
            ];

            $data = [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulanAngka' => $bulanAngka,
                'penganggaran' => $penganggaran,
                'sekolah' => $sekolah,
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoBuku' => $saldoBuku,
                'saldoKas' => $saldoKas,
                'saldoBank' => $saldoBank,
                'uangKertas' => $uangKertas,
                'uangLogam' => $uangLogam,
                'totalUangKertas' => $totalUangKertas,
                'totalUangLogam' => $totalUangLogam,
                'saldoAkhirBuku' => $saldoAkhirBuku,
                'perbedaan' => $saldoBuku - $saldoKas,
                'penjelasanPerbedaan' => 'Masih ada sebagian dana BOS yang belum diambil di rekening bank. Masih ada sisa tunai yang disimpan bendahara.',
                'tanggalPenutupan' => Carbon::create($tahun, $bulanAngka, 1)->endOfMonth()->format('d F Y'),
                'tanggalPenutupanLalu' => '-',
                'namaBendahara' => $sekolah->bendahara ?? 'Dra. MASITAH ABDULLAH',
                'namaKepalaSekolah' => $sekolah->kepala_sekolah ?? 'Dra. MASITAH ABDULLAH',
                'nipBendahara' => $sekolah->nip_bendahara ?? '19690917 200701 2 017',
                'nipKepalaSekolah' => $sekolah->nip_kepala_sekolah ?? '19690917 200701 2 017',
                'tanggalAkhirBulan' => BukuKasUmum::getTanggalAkhirBulan($tahun, $bulan),
                'namaHariAkhirBulan' => BukuKasUmum::getHariAkhirBulan($tahun, $bulan),
                'formatAkhirBulanLengkapHari' => BukuKasUmum::formatAkhirBulanLengkapHari($tahun, $bulan),
                'formatAkhirBulanSingkat' => BukuKasUmum::formatAkhirBulanSingkat($tahun, $bulan),
                'formatTanggalAkhirBulanLengkap' => BukuKasUmum::formatTanggalAkhirBulanLengkap($tahun, $bulan),
                'convertNumberToBulan' => function ($angka) {
                    return $this->convertNumberToBulan($angka);
                },
                'tanggal_cetak' => now()->format('d/m/Y'),
                'printSettings' => $printSettings
            ];

            $pdf = PDF::loadView('laporan.bkp-registrasi-pdf', $data);

            $pdf->setPaper($printSettings['ukuran_kertas'], $printSettings['orientasi']);

            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => realpath(base_path()),
            ]);

            $filename = "BKP_Registrasi_{$bulan}_{$tahun}.pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating BKP Registrasi PDF: ' . $e->getMessage());

            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods (sama seperti di controller lain)
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
}
