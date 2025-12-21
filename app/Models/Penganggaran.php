<?php

namespace App\Models;

use App\Models\Sts;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penganggaran extends Model
{
    use HasFactory;

    protected $table = 'penganggarans';

    protected $fillable = [
        'sekolah_id', // Tambahkan foreign key jika belum ada
        'pagu_anggaran',
        'tahun_anggaran',
        'kepala_sekolah',
        'sk_kepala_sekolah',
        'bendahara',
        'sk_bendahara',
        'komite',
        'nip_kepala_sekolah',
        'nip_bendahara',
        'tanggal_cetak',
        'tanggal_perubahan',
        'tanggal_sk_kepala_sekolah',
        'tanggal_sk_bendahara'
    ];

    protected $casts = [
        'pagu_anggaran' => 'decimal:2',
        'tahun_anggaran' => 'integer',
        'tanggal_sk_kepala_sekolah' => 'date',
        'tanggal_sk_bendahara' => 'date',
    ];

    // Relasi dengan model Sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    // Relasi dengan model Rkas
    public function rkas()
    {
        return $this->hasMany(Rkas::class);
    }

    public function getFormatTanggalCetakAttribute()
    {
        if ($this->tanggal_cetak) {
            return $this->formatTanggalIndonesia($this->tanggal_cetak);
        }
        return 'Belum diisi';
    }

    public function getFormatTanggalPerubahanAttribute()
    {
        if ($this->tanggal_perubahan) {
            return $this->formatTanggalIndonesia($this->tanggal_perubahan);
        }
        return 'Belum diisi';
    }

    // Relasi Model Rkas Perubahan
    public function rkasPerubahan()
    {
        return $this->hasMany(RkasPerubahan::class);
    }

    private function formatTanggalIndonesia($date)
    {
        // Daftar nama bulan dalam bahasa Indonesia
        $bulan = [
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
            12 => 'Desember'
        ];

        $date = Carbon::parse($date);
        return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
    }

    /**
     * Accessor untuk format tanggal SK Kepala Sekolah
     */
    public function getFormatTanggalSkKepsekAttribute()
    {
        return $this->tanggal_sk_kepala_sekolah
            ? $this->formatTanggalIndonesia($this->tanggal_sk_kepala_sekolah)
            : null;
    }

    /**
     * Accessor untuk format tanggal SK Bendahara
     */
    public function getFormatTanggalSkBendaharaAttribute()
    {
        return $this->tanggal_sk_bendahara
            ? $this->formatTanggalIndonesia($this->tanggal_sk_bendahara)
            : null;
    }

    public function penerimaanDanas()
    {
        return $this->hasMany(PenerimaanDana::class, 'penganggaran_id');
    }

    public function penarikanTunai()
    {
        return $this->hasMany(PenarikanTunai::class, 'penganggaran_id');
    }

    public function setorTunai()
    {
        return $this->hasMany(SetorTunai::class, 'penganggaran_id');
    }

    // fungsi untuk menandai status card bulan jadi belum diisi, draft, dan selesai
    public function getBulanStatus()
    {
        $statusBulan = [];
        $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // Cek apakah penerimaan dana tahap 1 sudah ada
        $penerimaanTahap1 = PenerimaanDana::where('penganggaran_id', $this->id)
            ->where('sumber_dana', 'Bosp Reguler Tahap 1')
            ->first();

        if (!$penerimaanTahap1) {
            foreach ($bulanList as $bulan) {
                $statusBulan[$bulan] = 'disabled';
            }
            return $statusBulan;
        }

        $bulanTertutupSebelumnya = true;

        foreach ($bulanList as $index => $bulan) {
            $bulanAngka = $index + 1;

            // Dapatkan status dari database
            $status = BukuKasUmum::getStatusBulan($this->id, $bulan);

            // Cek apakah BKU closed
            $isClosed = BukuKasUmum::where('penganggaran_id', $this->id)
                ->whereMonth('tanggal_transaksi', $bulanAngka)
                ->where('status', 'closed')
                ->exists();

            if ($isClosed) {
                $statusBulan[$bulan] = 'selesai';
                $bulanTertutupSebelumnya = true;
                continue;
            }

            if (!$bulanTertutupSebelumnya && $index > 0) {
                $statusBulan[$bulan] = 'disabled';
                continue;
            }

            if ($status === 'draft') {
                $statusBulan[$bulan] = 'draft';
                $bulanTertutupSebelumnya = false;
            } else {
                if ($index === 0 || $statusBulan[$bulanList[$index - 1]] === 'selesai') {
                    $statusBulan[$bulan] = 'belum_diisi';
                    $bulanTertutupSebelumnya = false;
                } else {
                    $statusBulan[$bulan] = 'disabled';
                }
            }
        }

        return $statusBulan;
    }

    public function tandaTerima()
    {
        return $this->hasMany(TandaTerima::class);
    }

    public function sts()
    {
        return $this->hasMany(Sts::class);
    }
}
