<?php

namespace App\Services;

use App\Models\PenerimaanDana;
use App\Models\PenarikanTunai;
use App\Models\SetorTunai;
use App\Models\BukuKasUmum;
use App\Models\Penganggaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BukuKasService
{
    /**
     * Hitung saldo bank sebelum bulan tertentu - VERSI DIPERBAIKI
     */
    public function hitungSaldoBankSebelumBulan($penganggaran_id, $bulanTarget)
    {
        try {
            Log::info('=== HITUNG SALDO BANK SEBELUM BULAN - VERSI DIPERBAIKI ===', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget
            ]);

            // Jika bulan target adalah Januari (1), maka saldo awal adalah 0
            if ($bulanTarget == 1) {
                Log::info('Saldo awal Januari = 0');
                return 0;
            }

            // Hitung total penerimaan dana sampai bulan sebelumnya
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_terima) < ?', [$bulanTarget])
                ->get();

            $totalPenerimaan = $penerimaanDanas->sum(function ($penerimaan) {
                $total = $penerimaan->jumlah_dana;
                if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                    $total += $penerimaan->saldo_awal;
                }
                return $total;
            });

            Log::info('Total Penerimaan Dana sampai bulan sebelumnya:', ['total' => $totalPenerimaan]);

            // Hitung total penarikan tunai sampai bulan sebelumnya
            $totalPenarikanSampaiBulanSebelumnya = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_penarikan) < ?', [$bulanTarget])
                ->sum('jumlah_penarikan');

            Log::info('Total Penarikan sampai bulan sebelumnya:', ['total' => $totalPenarikanSampaiBulanSebelumnya]);

            // Hitung bunga bank sampai bulan sebelumnya
            $totalBungaSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', true)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTarget])
                ->sum('bunga_bank');

            Log::info('Total Bunga sampai bulan sebelumnya:', ['total' => $totalBungaSampaiBulanSebelumnya]);

            // Hitung pajak bunga sampai bulan sebelumnya
            $totalPajakBungaSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', true)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTarget])
                ->sum('pajak_bunga_bank');

            Log::info('Total Pajak Bunga sampai bulan sebelumnya:', ['total' => $totalPajakBungaSampaiBulanSebelumnya]);

            // PERBAIKAN: Hitung total belanja NON-TUNAI sampai bulan sebelumnya
            $totalBelanjaNonTunaiSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'non-tunai')
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) < ?', [$bulanTarget])
                ->sum('total_transaksi_kotor');

            Log::info('Total Belanja Non-Tunai sampai bulan sebelumnya:', ['total' => $totalBelanjaNonTunaiSampaiBulanSebelumnya]);

            // PERBAIKAN: Rumus saldo bank yang benar
            $saldoBank = $totalPenerimaan
                - $totalPenarikanSampaiBulanSebelumnya
                + $totalBungaSampaiBulanSebelumnya
                - $totalPajakBungaSampaiBulanSebelumnya
                - $totalBelanjaNonTunaiSampaiBulanSebelumnya;

            Log::info('Perhitungan Saldo Bank Sebelum Bulan - VERSI DIPERBAIKI', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget,
                'total_penerimaan' => $totalPenerimaan,
                'total_penarikan_sampai_bulan_sebelumnya' => $totalPenarikanSampaiBulanSebelumnya,
                'total_bunga_sampai_bulan_sebelumnya' => $totalBungaSampaiBulanSebelumnya,
                'total_pajak_bunga_sampai_bulan_sebelumnya' => $totalPajakBungaSampaiBulanSebelumnya,
                'total_belanja_non_tunai_sampai_bulan_sebelumnya' => $totalBelanjaNonTunaiSampaiBulanSebelumnya,
                'saldo_bank' => $saldoBank
            ]);

            return max(0, $saldoBank);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoBankSebelumBulan: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Hitung saldo tunai sebelum bulan tertentu - DIPERBAIKI
     */
    public function hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanTarget)
    {
        try {
            // Jika bulan target adalah Januari (1), maka saldo awal adalah 0
            if ($bulanTarget == 1) {
                return 0;
            }

            // Hitung total penarikan tunai sampai bulan sebelumnya
            $totalPenarikanSampaiBulanSebelumnya = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_penarikan) <= ?', [$bulanTarget - 1])
                ->sum('jumlah_penarikan');

            // Hitung total setor tunai sampai bulan sebelumnya
            $totalSetorSampaiBulanSebelumnya = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereRaw('EXTRACT(MONTH FROM tanggal_setor) <= ?', [$bulanTarget - 1])
                ->sum('jumlah_setor');

            // Hitung total belanja tunai sampai bulan sebelumnya
            $belanjaTunaiSampaiBulanSebelumnya = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('jenis_transaksi', 'tunai')
                ->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) <= ?', [$bulanTarget - 1])
                ->sum('total_transaksi_kotor');

            $saldoTunai = ($totalPenarikanSampaiBulanSebelumnya - $totalSetorSampaiBulanSebelumnya) - $belanjaTunaiSampaiBulanSebelumnya;

            Log::info('Perhitungan Saldo Tunai Sebelum Bulan - DIPERBAIKI', [
                'penganggaran_id' => $penganggaran_id,
                'bulan_target' => $bulanTarget,
                'total_penarikan' => $totalPenarikanSampaiBulanSebelumnya,
                'total_setor' => $totalSetorSampaiBulanSebelumnya,
                'belanja_tunai' => $belanjaTunaiSampaiBulanSebelumnya,
                'saldo_tunai' => $saldoTunai
            ]);

            return max(0, $saldoTunai);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoTunaiSebelumBulan: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Hitung total dana tersedia berdasarkan penerimaan dana
     */
    public function hitungTotalDanaTersedia($penganggaran_id)
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
     * Helper function untuk konversi bulan
     */
    public function convertBulanToNumber($bulan)
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

    /**
     * Get nama bulan dari angka (Helper function)
     */
    public function convertNumberToBulan($angka)
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

    /**
     * Hitung saldo tunai dan non-tunai
     */
    public function hitungSaldoTunaiNonTunai($penganggaran_id)
    {
        try {
            Log::info('=== PERHITUNGAN SALDO DIMULAI ===', ['penganggaran_id' => $penganggaran_id]);

            // 1. Hitung total penerimaan dana (termasuk saldo awal)
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)->get();
            $totalPenerimaan = $penerimaanDanas->sum(function ($penerimaan) {
                $total = $penerimaan->jumlah_dana;
                if ($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal) {
                    $total += $penerimaan->saldo_awal;
                }
                return $total;
            });

            Log::info('Total Penerimaan', ['total' => $totalPenerimaan]);

            // 2. Hitung total penarikan dan setor tunai
            $totalPenarikan = PenarikanTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_penarikan');
            $totalSetor = SetorTunai::where('penganggaran_id', $penganggaran_id)->sum('jumlah_setor');
            $netTunai = $totalPenarikan - $totalSetor;

            Log::info('Transaksi Tunai', [
                'penarikan' => $totalPenarikan,
                'setor' => $totalSetor,
                'net' => $netTunai,
            ]);

            // 3. Hitung total belanja (gunakan total_transaksi_kotor)
            $totalBelanja = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->sum('total_transaksi_kotor');

            Log::info('Total Belanja', ['total' => $totalBelanja]);

            // 4. Hitung belanja tunai dan non-tunai
            $belanjaTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('jenis_transaksi', 'tunai')
                ->sum('total_transaksi_kotor');

            $belanjaNonTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('jenis_transaksi', 'non-tunai')
                ->sum('total_transaksi_kotor');

            Log::info('Detail Belanja', [
                'tunai' => $belanjaTunai,
                'non_tunai' => $belanjaNonTunai,
            ]);

            // 5. Hitung saldo tunai
            $saldoTunai = $netTunai - $belanjaTunai;

            // 6. Hitung saldo non-tunai
            $saldoNonTunai = $totalPenerimaan - $belanjaNonTunai - $saldoTunai;

            // 7. Validasi konsistensi data
            $totalSaldo = $saldoTunai + $saldoNonTunai;
            $totalSeharusnya = $totalPenerimaan - $totalBelanja;
            $selisih = $totalSeharusnya - $totalSaldo;

            Log::info('Validasi Saldo', [
                'saldo_tunai' => $saldoTunai,
                'saldo_non_tunai' => $saldoNonTunai,
                'total_saldo' => $totalSaldo,
                'total_seharusnya' => $totalSeharusnya,
                'selisih' => $selisih,
            ]);

            // 8. Jika ada selisih signifikan, koreksi saldo non-tunai
            if (abs($selisih) > 1000) {
                Log::warning('Koreksi saldo diperlukan', ['selisih' => $selisih]);
                $saldoNonTunai = $totalSeharusnya - $saldoTunai;
            }

            $result = [
                'tunai' => max(0, $saldoTunai),
                'non_tunai' => max(0, $saldoNonTunai),
                'total_dana_tersedia' => max(0, $totalPenerimaan - $totalBelanja),
                'total_penerimaan' => $totalPenerimaan,
                'total_belanja' => $totalBelanja,
                'belanja_tunai' => $belanjaTunai,
                'belanja_non_tunai' => $belanjaNonTunai,
                'net_tunai' => $netTunai,
                'selisih' => $selisih,
            ];

            Log::info('=== HASIL PERHITUNGAN SALDO ===', $result);

            return $result;
        } catch (\Exception $e) {
            Log::error('Error dalam hitungSaldoTunaiNonTunai: ' . $e->getMessage());

            return [
                'tunai' => 0,
                'non_tunai' => 0,
                'total_dana_tersedia' => 0,
                'total_penerimaan' => 0,
                'total_belanja' => 0,
                'belanja_tunai' => 0,
                'belanja_non_tunai' => 0,
                'net_tunai' => 0,
                'selisih' => 0,
            ];
        }
    }

    /**
     * Hitung saldo akhir BKP Bank untuk bulan tertentu - DIPERBAIKI LAGI
     */
    public function hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulan)
    {
        try {
            $bulanAngka = $this->convertBulanToNumber($bulan);

            Log::info('=== HITUNG SALDO AKHIR BKP BANK - DIPERBAIKI ===', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulan_angka' => $bulanAngka
            ]);

            // Hitung saldo awal BKP Bank untuk bulan ini
            $saldoAwalBank = 0;
            if ($bulanAngka > 1) {
                // Ambil saldo akhir dari bulan sebelumnya
                $bulanSebelumnyaAngka = $bulanAngka - 1;
                $bulanSebelumnyaNama = array_search($bulanSebelumnyaAngka, [
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
                ]);

                if ($bulanSebelumnyaNama) {
                    $saldoAwalBank = $this->hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulanSebelumnyaNama);
                }
            } else {
                // Untuk bulan Januari, hitung penerimaan dana di bulan Januari
                $penerimaanJanuari = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                    ->whereYear('tanggal_terima', $tahun)
                    ->whereMonth('tanggal_terima', 1)
                    ->sum('jumlah_dana');

                $saldoAwalBank = $penerimaanJanuari;
                Log::info('Penerimaan Dana Januari:', ['total' => $penerimaanJanuari]);
            }

            // Data untuk bulan ini dari BKP Bank
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereYear('tanggal_penarikan', $tahun)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->where('is_bunga_record', true)
                ->whereYear('tanggal_transaksi', $tahun)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->first();

            $totalPenarikan = $penarikanTunais->sum('jumlah_penarikan');
            $totalBunga = $bungaRecord ? $bungaRecord->bunga_bank : 0;
            $totalPajakBunga = $bungaRecord ? $bungaRecord->pajak_bunga_bank : 0;

            // PERBAIKAN: Untuk bulan selain Januari, tambahkan penerimaan dana di bulan tersebut
            if ($bulanAngka > 1) {
                $penerimaanBulanIni = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                    ->whereYear('tanggal_terima', $tahun)
                    ->whereMonth('tanggal_terima', $bulanAngka)
                    ->sum('jumlah_dana');

                $saldoAwalBank += $penerimaanBulanIni;
                Log::info('Penerimaan Dana Bulan Ini:', [
                    'bulan' => $bulan,
                    'penerimaan' => $penerimaanBulanIni
                ]);
            }

            // Rumus: Saldo Awal + Bunga - Penarikan - Pajak Bunga
            $saldoAkhir = $saldoAwalBank + $totalBunga - $totalPenarikan - $totalPajakBunga;

            Log::info('Perhitungan Saldo Akhir BKP Bank - DIPERBAIKI:', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'saldo_awal_bank' => $saldoAwalBank,
                'total_penarikan' => $totalPenarikan,
                'total_bunga' => $totalBunga,
                'total_pajak_bunga' => $totalPajakBunga,
                'saldo_akhir_bank' => $saldoAkhir
            ]);

            return max(0, $saldoAkhir);
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoAkhirBkpBank: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Hitung saldo awal BKP Umum berdasarkan saldo akhir BKP Bank bulan sebelumnya - DIPERBAIKI
     */
    public function hitungSaldoAwalBkpUmum($penganggaran_id, $tahun, $bulan)
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

            $bulanAngka = $bulanList[$bulan] ?? 1;

            Log::info('=== HITUNG SALDO AWAL BKP UMUM ===', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'bulan_angka' => $bulanAngka
            ]);

            // Jika bulan Januari, saldo awal adalah 0
            if ($bulanAngka == 1) {
                Log::info('Saldo Awal BKP Umum - Januari = 0');
                return 0;
            }

            // Ambil saldo akhir dari BKP Bank bulan sebelumnya
            $bulanSebelumnyaAngka = $bulanAngka - 1;
            $bulanSebelumnyaNama = array_search($bulanSebelumnyaAngka, $bulanList);

            if (!$bulanSebelumnyaNama) {
                Log::warning('Bulan sebelumnya tidak ditemukan', ['bulan_sebelumnya_angka' => $bulanSebelumnyaAngka]);
                return 0;
            }

            // Hitung saldo akhir BKP Bank bulan sebelumnya
            $saldoAkhirBank = $this->hitungSaldoAkhirBkpBank($penganggaran_id, $tahun, $bulanSebelumnyaNama);

            Log::info('Saldo Awal BKP Umum - Ambil dari BKP Bank bulan sebelumnya:', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan_sekarang' => $bulan,
                'bulan_sebelumnya' => $bulanSebelumnyaNama,
                'saldo_akhir_bank_bulan_sebelumnya' => $saldoAkhirBank,
                'saldo_awal_bkp_umum' => $saldoAkhirBank
            ]);

            return $saldoAkhirBank;
        } catch (\Exception $e) {
            Log::error('Error hitungSaldoAwalBkpUmum: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil saldo kas dari currentSaldo di tab Pembantu
     */
    public function getSaldoKasFromPembantu($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Ambil data penarikan tunai untuk bulan tersebut
            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuDataTunai = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->where('jenis_transaksi', 'tunai')
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            // Hitung saldo awal tunai (sama seperti di tab Pembantu)
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanAngka);

            // Hitung total untuk BKP Pembantu (sama seperti di tab Pembantu)
            $totalPenerimaan = $saldoAwalTunai + $penarikanTunais->sum('jumlah_penarikan');

            // Hitung pajak untuk pembantu (sama seperti di tab Pembantu)
            $pajakPenerimaan = 0;
            $pajakPengeluaran = 0;
            $pajakDaerahPenerimaan = 0;
            $pajakDaerahPengeluaran = 0;

            foreach ($bkuDataTunai as $transaksi) {
                // Pajak Pusat
                if ($transaksi->total_pajak > 0) {
                    $pajakPenerimaan += $transaksi->total_pajak;
                    if (!empty($transaksi->ntpn)) {
                        $pajakPengeluaran += $transaksi->total_pajak;
                    }
                }

                // Pajak Daerah
                if ($transaksi->total_pajak_daerah > 0) {
                    $pajakDaerahPenerimaan += $transaksi->total_pajak_daerah;
                    if (!empty($transaksi->ntpn)) {
                        $pajakDaerahPengeluaran += $transaksi->total_pajak_daerah;
                    }
                }
            }

            // Tambahkan SEMUA pajak ke total (sama seperti di tab Pembantu)
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuDataTunai->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran;

            // CurrentSaldo dari tab Pembantu
            $currentSaldo = $totalPenerimaan - $totalPengeluaran;

            Log::info('Saldo Kas dari Pembantu', [
                'penganggaran_id' => $penganggaran_id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'saldo_awal_tunai' => $saldoAwalTunai,
                'total_penarikan' => $penarikanTunais->sum('jumlah_penarikan'),
                'total_setor' => $setorTunais->sum('jumlah_setor'),
                'total_belanja_tunai' => $bkuDataTunai->sum('total_transaksi_kotor'),
                'current_saldo' => $currentSaldo
            ]);

            return max(0, $currentSaldo);
        } catch (\Exception $e) {
            Log::error('Error getSaldoKasFromPembantu: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil total penerimaan dan pengeluaran dari tab Umum - VERSI DIPERBAIKI
     */
    public function getDataFromBkpUmumCalculation($penganggaran_id, $tahun, $bulan, $bulanAngka)
    {
        try {
            // Ambil semua data yang diperlukan (sama seperti di tab Umum)
            $penerimaanDanas = PenerimaanDana::where('penganggaran_id', $penganggaran_id)
                ->orderBy('tanggal_terima', 'asc')
                ->get();

            $penarikanTunais = PenarikanTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_penarikan', $bulanAngka)
                ->whereYear('tanggal_penarikan', $tahun)
                ->orderBy('tanggal_penarikan', 'asc')
                ->get();

            $setorTunais = SetorTunai::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_setor', $bulanAngka)
                ->whereYear('tanggal_setor', $tahun)
                ->orderBy('tanggal_setor', 'asc')
                ->get();

            $bkuData = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', false)
                ->with(['kodeKegiatan', 'rekeningBelanja'])
                ->orderBy('tanggal_transaksi', 'asc')
                ->get();

            $bungaRecord = BukuKasUmum::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->whereYear('tanggal_transaksi', $tahun)
                ->where('is_bunga_record', true)
                ->first();

            // Hitung saldo untuk BKP Umum (sama seperti di tab Umum)
            $saldoAwal = $this->hitungSaldoAwalBkpUmum($penganggaran_id, $tahun, $bulan);
            $saldoAwalTunai = $this->hitungSaldoTunaiSebelumBulan($penganggaran_id, $bulanAngka);

            // Hitung total penerimaan dasar (sama seperti di tab Umum)
            $totalPenerimaan = $saldoAwal + $saldoAwalTunai;

            // Tambahkan penerimaan dana di bulan ini
            $penerimaanBulanIni = $penerimaanDanas->filter(function ($penerimaan) use ($bulanAngka) {
                return \Carbon\Carbon::parse($penerimaan->tanggal_terima)->month == $bulanAngka;
            });
            $totalPenerimaan += $penerimaanBulanIni->sum('jumlah_dana');

            // Tambahkan penarikan tunai
            $totalPenerimaan += $penarikanTunais->sum('jumlah_penarikan');

            // Tambahkan bunga bank
            $totalPenerimaan += ($bungaRecord ? $bungaRecord->bunga_bank : 0);

            // Hitung pajak untuk BKP Umum (sama seperti di tab Umum)
            $pajakPenerimaan = 0;
            $pajakPengeluaran = 0;
            $pajakDaerahPenerimaan = 0;
            $pajakDaerahPengeluaran = 0;

            foreach ($bkuData as $transaksi) {
                // Pajak Pusat - SEMUA dihitung di penerimaan
                if ($transaksi->total_pajak > 0) {
                    $pajakPenerimaan += $transaksi->total_pajak;
                    if (!empty($transaksi->ntpn)) {
                        $pajakPengeluaran += $transaksi->total_pajak;
                    }
                }

                // Pajak Daerah - SEMUA dihitung di penerimaan
                if ($transaksi->total_pajak_daerah > 0) {
                    $pajakDaerahPenerimaan += $transaksi->total_pajak_daerah;
                    if (!empty($transaksi->ntpn)) {
                        $pajakDaerahPengeluaran += $transaksi->total_pajak_daerah;
                    }
                }
            }

            // Tambahkan SEMUA pajak ke total (sama seperti di tab Umum)
            $totalPenerimaan += $pajakPenerimaan + $pajakDaerahPenerimaan;
            $totalPengeluaran = $setorTunais->sum('jumlah_setor')
                + $bkuData->sum('total_transaksi_kotor')
                + $pajakPengeluaran + $pajakDaerahPengeluaran
                + ($bungaRecord ? $bungaRecord->pajak_bunga_bank : 0);

            // TAMBAHKAN LOGGING DETAIL
            Log::info('=== DETAIL PERHITUNGAN BKP UMUM ===', [
                'penganggaran_id' => $penganggaran_id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'saldoAwal' => $saldoAwal,
                'saldoAwalTunai' => $saldoAwalTunai,
                'penerimaanBulanIni' => $penerimaanBulanIni->sum('jumlah_dana'),
                'penarikanTunai' => $penarikanTunais->sum('jumlah_penarikan'),
                'bungaBank' => ($bungaRecord ? $bungaRecord->bunga_bank : 0),
                'pajakPenerimaan' => $pajakPenerimaan,
                'pajakDaerahPenerimaan' => $pajakDaerahPenerimaan,
                'totalPenerimaan' => $totalPenerimaan,
                'setorTunai' => $setorTunais->sum('jumlah_setor'),
                'belanjaBKU' => $bkuData->sum('total_transaksi_kotor'),
                'pajakPengeluaran' => $pajakPengeluaran,
                'pajakDaerahPengeluaran' => $pajakDaerahPengeluaran,
                'totalPengeluaran' => $totalPengeluaran
            ]);

            return [
                'totalPenerimaan' => $totalPenerimaan,
                'totalPengeluaran' => $totalPengeluaran
            ];
        } catch (\Exception $e) {
            Log::error('Error getDataFromBkpUmumCalculation: ' . $e->getMessage());
            return [
                'totalPenerimaan' => 0,
                'totalPengeluaran' => 0
            ];
        }
    }

    /**
     * Hitung denominasi uang kertas (selalu tampilkan semua denominasi meskipun saldo 0)
     */
    public function getDenominasiUangKertas($saldoKas)
    {
        $denominasi = [
            100000,
            50000,
            20000,
            10000,
            5000,
            2000,
            1000
        ];

        $result = [];
        $sisaSaldo = $saldoKas;

        foreach ($denominasi as $nominal) {
            if ($sisaSaldo >= $nominal) {
                $jumlahLembar = floor($sisaSaldo / $nominal);
                $total = $jumlahLembar * $nominal;
                $result[] = [
                    'denominasi' => $nominal,
                    'lembar' => $jumlahLembar,
                    'jumlah' => $total
                ];
                $sisaSaldo -= $total;
            } else {
                // Tetap tampilkan meskipun 0
                $result[] = [
                    'denominasi' => $nominal,
                    'lembar' => 0,
                    'jumlah' => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Hitung denominasi uang logam (selalu tampilkan semua denominasi meskipun saldo 0)
     */
    public function getDenominasiUangLogam($sisaUntukLogam = 0)
    {
        $denominasi = [
            1000,
            500,
            200,
            100
        ];

        $result = [];
        $sisaSaldo = $sisaUntukLogam;

        foreach ($denominasi as $nominal) {
            if ($sisaSaldo >= $nominal) {
                $jumlahKeping = floor($sisaSaldo / $nominal);
                $total = $jumlahKeping * $nominal;
                $result[] = [
                    'denominasi' => $nominal,
                    'keping' => $jumlahKeping,
                    'jumlah' => $total
                ];
                $sisaSaldo -= $total;
            } else {
                // Selalu tampilkan semua denominasi dengan nilai 0
                $result[] = [
                    'denominasi' => $nominal,
                    'keping' => 0,
                    'jumlah' => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Hitung total dari array uang kertas
     */
    public function hitungTotalUangKertas($uangKertas)
    {
        return array_sum(array_column($uangKertas, 'jumlah'));
    }

    /**
     * Hitung total dari array uang logam
     */
    public function hitungTotalUangLogam($uangLogam)
    {
        return array_sum(array_column($uangLogam, 'jumlah'));
    }
}
