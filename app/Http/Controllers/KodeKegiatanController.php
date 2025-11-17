<?php

namespace App\Http\Controllers;

use App\Models\KodeKegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Imports\KodeKegiatanImport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class KodeKegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $search = request()->input('search');

        $kodeKegiatans = KodeKegiatan::when($search, function ($query) use ($search) {
            return $query->where('kode', 'like', '%' . $search . '%')
                ->orWhere('program', 'like', '%' . $search . '%')
                ->orWhere('sub_program', 'like', '%' . $search . '%')
                ->orWhere('uraian', 'like', '%' . $search . '%');
        })
            ->orderBy('kode')
            ->paginate(20);
        return view('referensi.kode-kegiatan', compact('kodeKegiatans', 'search'));
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->get('search', '');

            if (empty($searchTerm)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata pencarian tidak boleh kosong'
                ], 400);
            }

            $kegiatans = KodeKegiatan::where(function ($query) use ($searchTerm) {
                $query->where('kode', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('program', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('sub_program', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('uraian', 'ILIKE', "%{$searchTerm}%");
            })
                ->orderBy('kode', 'asc')
                ->get();

            $formattedData = $kegiatans->map(function ($kegiatan, $index) {
                return [
                    'id' => $kegiatan->id,
                    'index' => $index + 1, // Nomor urut
                    'kode' => $kegiatan->kode,
                    'program' => $kegiatan->program,
                    'sub_program' => $kegiatan->sub_program,
                    'uraian' => $kegiatan->uraian,
                    'actions' => $this->getKegiatanActionButtons($kegiatan) // Tombol aksi
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'total' => $kegiatans->count(),
                'search_term' => $searchTerm
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching kode kegiatan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate action buttons for kode kegiatan
     */
    private function getKegiatanActionButtons($kegiatan): string
    {
        return '
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                data-bs-target="#editModal' . $kegiatan->id . '">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal' . $kegiatan->id . '">
            <i class="bi bi-trash"></i>
        </button>
    ';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'kode' => 'required|string|max:10',
            'program' => 'required|string|max:255',
            'sub_program' => 'required|string',
            'uraian' => 'required|string',
        ], [
            'kode.required' => 'Kode Wajib Di Isi',
            'kode.max' => 'Kode Maksimal 10 Karakter',
            'program.required' => 'Program wajib di isi',
            'sub_program.required' => 'Sub program wajib di isi',
            'uraian.required' => 'Uraian kegiatan wajib di isi'
        ]);

        KodeKegiatan::create($request->all());

        return redirect()->route('referensi.kode-kegiatan.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(KodeKegiatan $kodeKegiatan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KodeKegiatan $kodeKegiatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $kodeKegiatan = KodeKegiatan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kode_kegiatans')->ignore($kodeKegiatan->id),
            ],
            'program' => 'required|string|max:255',
            'sub_program' => 'required|string',
            'uraian' => 'required|string'
        ], [
            'kode.required' => 'Kode Wajib Di Isi',
            'kode.max' => 'Kode Maksimal 10 Karakter',
            'kode.unique' => 'Kode sudah ada',
            'program.required' => 'Program wajib di isi',
            'sub_program.required' => 'Sub program wajib di isi',
            'uraian.required' => 'Uraian kegiatan wajib di isi'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal memperbaharui kode kegiatan. silahkan periksa kembali inputan anda');
        }

        $kodeKegiatan->update($request->all());
        return redirect()->route('referensi.kode-kegiatan.index')->with('success', 'Data berhasil diperbaharui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $kodeKegiatan = KodeKegiatan::findOrFail($id);

        $kodeKegiatan->delete();

        return redirect()->route('referensi.kode-kegiatan.index')->with('Data Berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new KodeKegiatanImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getRowCount();
            $duplicateCount = count($import->getDuplicates());
            $errorCount = count($import->failures());

            $messages = [];

            if ($successCount > 0) {
                $messages['success'] = "Berhasil mengimport {$successCount} data";
            }

            if ($duplicateCount > 0) {
                $duplicateDetails = array_map(function ($item) {
                    return "Baris {$item['row']}: Kode {$item['kode']} ({$item['program']}) ({$item['sub_program']}) ({$item['uraian']})";
                }, $import->getDuplicates());

                $messages['warning'] = "{$duplicateCount} data tidak diimport karena sudah ada di database";
                $messages['duplicate_details'] = $duplicateDetails;
            }

            if ($errorCount > 0) {
                $errorMessages = [];
                foreach ($import->failures() as $failure) {
                    $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                }
                $messages['import_errors'] = $errorMessages;
                $messages['error'] = "{$errorCount} data tidak valid";
            }

            return redirect()->route('referensi.kode-kegiatan.index')
                ->with($messages);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/public/templates/template_kode_kegiatan.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return redirect()->back()
            ->with('error', 'Template tidak ditemukan');
    }
}
