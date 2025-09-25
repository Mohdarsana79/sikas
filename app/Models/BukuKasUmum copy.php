<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class BukuKasUmum extends Model
{
    use HasFactory;

    protected $table = 'buku_kas_umums';

    protected $fillable = [
        'penganggaran_id',
        'kode_kegiatan_id',
        'kode_rekening_id',
        'tanggal_transaksi',
        'jenis_transaksi',
        'id_transaksi',
        'nama_penyedia_barang_jasa',
        'nama_penerima_pembayaran',
        'alamat',
        'nomor_telepon',
        'npwp',
        'uraian',
        'anggaran',
        'dibelanjakan',
        'total_transaksi_kotor',
        'pajak',
        'persen_pajak',
        'total_pajak',
        'pajak_daerah',
        'persen_pajak_daerah',
        'total_pajak_daerah',
        'tanggal_lapor',
        'ntpn',
        'bunga_bank',
        'pajak_bunga_bank',
        'status',
        'status_bulan',
        'is_bunga_record',
        'closed_without_spending'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'anggaran' => 'decimal:2',
        'dibelanjakan' => 'decimal:2',
        'total_transaksi_kotor' => 'decimal:2',
        'total_pajak' => 'decimal:2',
        'total_pajak_daerah' => 'decimal:2',
        'tanggal_lapor' => 'date',
        'bunga_bank' => 'decimal:2',
        'pajak_bunga_bank' => 'decimal:2',
        'status' => 'string',
        'is_bunga_record' => 'boolean',
        'closed_without_spending' => 'boolean'
    ];

    // Accessor untuk bulan
    public function getBulanAttribute()
    {
        return $this->tanggal_transaksi->format('F');
    }

    // Accessor untuk tahun
    public function getTahunAttribute()
    {
        return $this->tanggal_transaksi->format('Y');
    }

    public function penganggaran()
    {
        return $this->belongsTo(Penganggaran::class);
    }

    public function kodeKegiatan()
    {
        return $this->belongsTo(KodeKegiatan::class, 'kode_kegiatan_id');
    }

    public function rekeningBelanja()
    {
        return $this->belongsTo(RekeningBelanja::class, 'kode_rekening_id');
    }

    // Scope untuk mencari transaksi berdasarkan uraian
    public function scopeWhereUraianLike($query, $uraian)
    {
        return $query->where('uraian', 'LIKE', '%' . $uraian . '%');
    }

    // Scope untuk BKU yang terbuka
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    // Scope untuk BKU yang tertutup
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Scope untuk record bunga bank
    public function scopeBungaRecords($query)
    {
        return $query->where('is_bunga_record', true);
    }

    // Scope untuk transaksi reguler (bukan record bunga)
    public function scopeRegularTransactions($query)
    {
        return $query->where('is_bunga_record', false);
    }

    // Method untuk mendapatkan data BKU by bulan dan tahun
    public static function getByBulanTahun($penganggaran_id, $bulan, $tahun)
    {
        $bulanAngka = self::convertBulanToNumber($bulan);

        return self::where('penganggaran_id', $penganggaran_id)
            ->whereYear('tanggal_transaksi', $tahun)
            ->whereMonth('tanggal_transaksi', $bulanAngka)
            ->get();
    }

    // Helper untuk konversi bulan
    private static function convertBulanToNumber($bulan)
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

    public function uraianDetails()
    {
        return $this->hasMany(BukuKasUmumUraianDetail::class, 'buku_kas_umum_id');
    }

    public static function getBulanTertutupTanpaBelanja($penganggaran_id, $bulanTarget)
    {
        $bulanList = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
            'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
            'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
        ];
        
        $bulanTargetNumber = $bulanList[$bulanTarget];
        
        // Cari bulan-bulan sebelumnya yang ditutup tanpa belanja
        $bulanTertutup = [];
        
        for ($i = 1; $i < $bulanTargetNumber; $i++) {
            $bulanNama = array_search($i, $bulanList);
            
            // Cek apakah bulan ini ditutup
            $isClosed = self::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $i)
                ->where('status', 'closed')
                ->exists();
                
            // Cek apakah ada transaksi reguler
            $hasTransactions = self::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $i)
                ->where('is_bunga_record', false)
                ->exists();
                
            if ($isClosed && !$hasTransactions) {
                $bulanTertutup[] = $bulanNama;
            }
        }
        
        return $bulanTertutup;
    }

    public static function getVolumeSudahDibelanjakan($penganggaran_id, $kegiatan_id, $rekening_id, $uraian, $sampaiBulan = null)
    {
        $query = BukuKasUmumUraianDetail::where('penganggaran_id', $penganggaran_id)
            ->where('kode_kegiatan_id', $kegiatan_id)
            ->where('kode_rekening_id', $rekening_id)
            ->where('uraian', 'LIKE', '%' . $uraian . '%');

        if ($sampaiBulan) {
            $bulanAngka = self::convertBulanToNumber($sampaiBulan);
            $query->whereHas('bukuKasUmum', function ($q) use ($bulanAngka) {
                $q->whereRaw('EXTRACT(MONTH FROM tanggal_transaksi) <= ?', [$bulanAngka]);
            });
        }

        return $query->sum('volume');
    }

    // Method untuk mendapatkan volume dari bulan tertutup tanpa belanja
    public static function getVolumeFromClosedMonths($penganggaran_id, $kegiatan_id, $rekening_id, $uraian, $sampaiBulan)
    {
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

        $sampaiBulanNumber = $bulanAngkaList[$sampaiBulan] ?? 1;
        $totalVolume = 0;
        $bulanList = [];

        for ($i = 1; $i < $sampaiBulanNumber; $i++) {
            $bulanNama = array_search($i, $bulanAngkaList);

            // Cek apakah bulan ini ditutup tanpa belanja
            $isClosedWithoutSpending = self::where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $i)
                ->where('status', 'closed')
                ->where('closed_without_spending', true)
                ->exists();

            if ($isClosedWithoutSpending) {
                // Tentukan model yang akan digunakan berdasarkan bulan
                $isTahap1 = in_array($bulanNama, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']);
                $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

                // Hitung volume untuk bulan ini dari RKAS
                $volumeBulan = $model::where('penganggaran_id', $penganggaran_id)
                    ->where('bulan', $bulanNama)
                    ->where('kode_rekening_id', $rekening_id)
                    ->where('kode_id', $kegiatan_id)
                    ->where('uraian', 'LIKE', '%' . $uraian . '%')
                    ->sum('jumlah');

                $totalVolume += $volumeBulan;
                $bulanList[] = $bulanNama;
            }
        }

        return [
            'total_volume' => $totalVolume,
            'bulan_list' => $bulanList
        ];
    }

    // Method untuk mendapatkan volume yang sudah dibelanjakan per bulan
    public static function getVolumeSudahDibelanjakanPerBulan($penganggaran_id, $kegiatan_id, $rekening_id, $uraian, $bulanAngka)
    {
        return BukuKasUmumUraianDetail::whereHas('bukuKasUmum', function ($query) use ($penganggaran_id, $bulanAngka) {
            $query->where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', $bulanAngka);
        })
            ->where('kode_rekening_id', $rekening_id)
            ->where('kode_kegiatan_id', $kegiatan_id)
            ->where('uraian', 'LIKE', '%' . $uraian . '%')
            ->sum('volume');
    }

    // Method untuk mendapatkan volume RKAS per bulan
    public static function getVolumeRkasPerBulan($penganggaran_id, $kegiatan_id, $rekening_id, $uraian, $bulanNama, $isTahap1)
    {
        $model = $isTahap1 ? Rkas::class : RkasPerubahan::class;

        return $model::where('penganggaran_id', $penganggaran_id)
            ->where('bulan', $bulanNama)
            ->where('kode_rekening_id', $rekening_id)
            ->where('kode_id', $kegiatan_id)
            ->where('uraian', $uraian)
            ->sum('jumlah');
    }

    // Method untuk mendapatkan volume yang sudah dibelanjakan untuk bulan tertentu di SEMUA bulan berikutnya
    public static function getVolumeSudahDibelanjakanSetelahBulan($penganggaran_id, $kegiatan_id, $rekening_id, $uraian, $bulanAngka)
    {
        return BukuKasUmumUraianDetail::whereHas('bukuKasUmum', function ($query) use ($penganggaran_id, $bulanAngka) {
            $query->where('penganggaran_id', $penganggaran_id)
                ->whereMonth('tanggal_transaksi', '>', $bulanAngka);
        })
            ->where('kode_rekening_id', $rekening_id)
            ->where('kode_kegiatan_id', $kegiatan_id)
            ->where('uraian', 'LIKE', '%' . $uraian . '%')
            ->sum('volume');
    }

    // Method untuk update status bulan agar card bulan status draft
    public static function updateStatusBulan($penganggaran_id, $bulan, $status)
    {
        // Update semua record BKU di bulan tersebut
        $bulanAngka = self::convertBulanToNumber($bulan);

        return self::where('penganggaran_id', $penganggaran_id)
            ->whereMonth('tanggal_transaksi', $bulanAngka)
            ->update(['status_bulan' => $status]);
    }

    // Method untuk mendapatkan status bulan draft
    public static function getStatusBulan($penganggaran_id, $bulan)
    {
        $bulanAngka = self::convertBulanToNumber($bulan);

        $bku = self::where('penganggaran_id', $penganggaran_id)
            ->whereMonth('tanggal_transaksi', $bulanAngka)
            ->first();

        if ($bku) {
            return $bku->status_bulan;
        }

        return null;
    }

    /**
     * The "booted" method of the model default ordering.
     */
    protected static function booted()
    {
        static::addGlobalScope('orderByIdTransaksi', function ($builder) {
            $builder->orderBy('id_transaksi', 'asc');
        });
    }
}
