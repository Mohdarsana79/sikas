<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\KopSekolah;

class KopSekolahController extends Controller
{
    public function index()
    {
        $kopSekolah = KopSekolah::first();
        return view('kop-sekolah.index', compact('kopSekolah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kop_sekolah' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Hapus file lama jika ada
            $existing = KopSekolah::first();
            if ($existing && $existing->file_path) {
                $oldFilePath = public_path('storage/kop_sekolah/' . $existing->file_path);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Upload file baru
            $file = $request->file('kop_sekolah');
            $filename = 'kop-sekolah-' . time() . '.' . $file->getClientOriginalExtension();

            // Pastikan folder ada
            if (!file_exists(public_path('storage/kop_sekolah'))) {
                mkdir(public_path('storage/kop_sekolah'), 0777, true);
            }

            // Pindahkan file
            $file->move(public_path('storage/kop_sekolah'), $filename);

            // Simpan atau update data
            KopSekolah::updateOrCreate(
                ['id' => $existing ? $existing->id : null],
                ['file_path' => $filename]
            );

            return redirect()->route('kop-sekolah.index')
                ->with('success', 'Kop sekolah berhasil diupload');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupload kop sekolah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $kopSekolah = KopSekolah::findOrFail($id);

        try {
            // Hapus file
            if ($kopSekolah->file_path && file_exists(public_path('storage/kop_sekolah/' . $kopSekolah->file_path))) {
                unlink(public_path('storage/kop_sekolah/' . $kopSekolah->file_path));
            }

            // Hapus record
            $kopSekolah->delete();

            return redirect()->route('kop-sekolah.index')
                ->with('success', 'Kop sekolah berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus kop sekolah: ' . $e->getMessage());
        }
    }
}
