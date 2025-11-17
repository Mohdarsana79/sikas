<?php

namespace App\Services;

use App\Models\RkasPerubahan;
use Illuminate\Support\Facades\Auth;
use App\Models\RekamanPerubahan;

class RekamanPerubahanService
{
    public static function catatPerubahan($rkasPerubahanId, $action, $changes = null)
    {
        // Jika action adalah delete, kita perlu menyimpan data sebelum dihapus
        if ($action === 'delete') {
            $rkasPerubahan = RkasPerubahan::with(['kodeKegiatan', 'rekeningBelanja'])->find($rkasPerubahanId);

            if ($rkasPerubahan) {
                $changes = [
                    'kode_kegiatan' => $rkasPerubahan->kodeKegiatan->kode ?? '-',
                    'program' => $rkasPerubahan->kodeKegiatan->program ?? '-',
                    'sub_program' => $rkasPerubahan->kodeKegiatan->sub_program ?? '-',
                    'kode_rekening' => $rkasPerubahan->rekeningBelanja->kode_rekening ?? '-',
                    'rincian_objek' => $rkasPerubahan->rekeningBelanja->rincian_objek ?? '-',
                    'uraian' => $rkasPerubahan->uraian,
                    'jumlah' => $rkasPerubahan->jumlah,
                    'satuan' => $rkasPerubahan->satuan,
                    'harga_satuan' => $rkasPerubahan->harga_satuan,
                    'bulan' => $rkasPerubahan->bulan,
                    'total' => $rkasPerubahan->jumlah * $rkasPerubahan->harga_satuan
                ];
            }
        }

        RekamanPerubahan::create([
            'rkas_perubahan_id' => $rkasPerubahanId,
            'action' => $action,
            'changes' => $changes,
            'user_id' => Auth::id()
        ]);
    }

    public static function catatUpdate($rkasPerubahanId, $dataSebelum, $dataSesudah)
    {
        $changes = [
            'from' => $dataSebelum,
            'to' => $dataSesudah
        ];

        self::catatPerubahan($rkasPerubahanId, 'update', $changes);
    }
}
