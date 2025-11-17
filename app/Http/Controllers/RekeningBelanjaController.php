<?php

namespace App\Http\Controllers;

use App\Imports\RekeningBelanjaImport;
use App\Models\RekeningBelanja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class RekeningBelanjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $search = $request->input('search');

        $rekenings = RekeningBelanja::when($search, function ($query) use ($search) {
            return $query->where('kode_rekening', 'like', '%' . $search . '%')
                ->orWhere('rincian_objek', 'like', '%' . $search . '%');
        })
            ->orderBy('kode_rekening')
            ->paginate(20); // Sesuaikan jumlah item per halaman

        return view('referensi.rekening-belanja', compact('rekenings', 'search'));
    }

    // search ajax
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

            $rekenings = RekeningBelanja::where(function ($query) use ($searchTerm) {
                $query->where('kode_rekening', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('rincian_objek', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('kategori', 'ILIKE', "%{$searchTerm}%");
            })
                ->orderBy('kode_rekening', 'asc')
                ->get();

            $formattedData = $rekenings->map(function ($rekening, $index) {
                return [
                    'id' => $rekening->id,
                    'index' => $index + 1, // Tambahkan nomor urut
                    'kode_rekening' => $rekening->kode_rekening,
                    'rincian_objek' => $rekening->rincian_objek,
                    'kategori' => $rekening->kategori, // Tambahkan kategori
                    'actions' => $this->getRekeningActionButtons($rekening)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'total' => $rekenings->count(),
                'search_term' => $searchTerm
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching rekening belanja: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getRekeningActionButtons($rekening): string
    {
        return '
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                data-bs-target="#editModal' . $rekening->id . '">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal' . $rekening->id . '">
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
            'kode_rekening' => 'required|string|max:20|unique:rekening_belanjas,kode_rekening',
            'rincian_objek' => 'required|string',
            'kategori' => 'required|string|in:Modal,Operasi',
        ], [
            'kode_rekening.required' => 'Kode rekening wajib diisi',
            'kode_rekening.unique' => 'Kode rekening sudah ada dalam database',
            'kode_rekening.max' => 'Kode rekening maksimal 20 karakter',
            'rincian_objek.required' => 'Rincian objek belanja wajib diisi',
            'kategori.required' => 'Kategori belanja modal atau operasi wajib di isi'
        ]);

        RekeningBelanja::create($request->all());
        return redirect()->route('referensi.rekening-belanja.index')->with('success', 'Rekening Belanja berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(RekeningBelanja $rekeningBelanja)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RekeningBelanja $rekeningBelanja)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rekeningBelanja = RekeningBelanja::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'kode_rekening' => [
                'required',
                'string',
                'max:20',
                Rule::unique('rekening_belanjas')->ignore($rekeningBelanja->id)
            ],
            'rincian_objek' => 'required|string',
            'kategori' => 'required|string|in:Modal,Operasi'
        ], [
            'kode_rekening.required' => 'Kode rekening wajib diisi',
            'kode_rekening.unique' => 'Kode rekening sudah ada dalam database',
            'kode_rekening.max' => 'Kode rekening maksimal 20 karakter',
            'rincian_objek.required' => 'Rincian objek belanja wajib diisi',
            'kategori.required' => 'Kategori belanja modal atau operasi wajib di isi'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan!');
        }

        $rekeningBelanja->update($request->all());
        return redirect()->route('referensi.rekening-belanja.index')->with('success', 'Rekening Belanja berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $rekeningBelanja = RekeningBelanja::findOrFail($id);
        $rekeningBelanja->delete();
        return redirect()->route('referensi.rekening-belanja.index')->with('success', 'Data berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new RekeningBelanjaImport();
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
                    return "Baris {$item['row']}: Kode {$item['kode_rekening']} ({$item['rincian_objek']} {$item['kategori']})";
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

            return redirect()->route('referensi.rekening-belanja.index')
                ->with($messages);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/public/templates/template_rekening_belanja.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return redirect()->back()
            ->with('error', 'Template tidak ditemukan');
    }
}
