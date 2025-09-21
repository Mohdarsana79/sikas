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
        'is_bunga_record'
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
        'is_bunga_record' => 'boolean'
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
}
