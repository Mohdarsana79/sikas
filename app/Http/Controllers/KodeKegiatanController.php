<?php

namespace App\Http\Controllers;

use App\Models\KodeKegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Imports\KodeKegiatanImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class KodeKegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = request()->input('search');

        $kodeKegiatans = KodeKegiatan::when($search, function ($query) use ($search) {
            return $query->where('kode', 'like', '%' . $search . '%')
                ->orWhere('program', 'like', '%' . $search . '%')
                ->orWhere('sub_program', 'like', '%' . $search . '%')
                ->orWhere('uraian', 'like', '%' . $search . '%');
        })
            ->orderBy('kode')
            ->paginate(10)
            ->onEachSide(1);

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
                    'index' => $index + 1,
                    'kode' => $kegiatan->kode,
                    'program' => $kegiatan->program,
                    'sub_program' => $kegiatan->sub_program,
                    'uraian' => $kegiatan->uraian,
                    'actions' => $this->getKegiatanActionButtons($kegiatan)
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
        <button class="btn btn-sm btn-danger btn-delete" data-id="' . $kegiatan->id . '" 
                data-kode="' . $kegiatan->kode . '" data-program="' . $kegiatan->program . '"
                data-sub-program="' . $kegiatan->sub_program . '" data-uraian="' . $kegiatan->uraian . '">
            <i class="bi bi-trash"></i>
        </button>
    ';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:kode_kegiatans,kode',
            'program' => 'required|string|max:255',
            'sub_program' => 'required|string',
            'uraian' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            KodeKegiatan::create($request->all());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil ditambahkan'
                ]);
            }

            return redirect()->route('referensi.kode-kegiatan.index')
                ->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating kode kegiatan: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menambahkan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
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
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $kodeKegiatan->update($request->all());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diperbarui'
                ]);
            }

            return redirect()->route('referensi.kode-kegiatan.index')
                ->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating kode kegiatan: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $kodeKegiatan = KodeKegiatan::findOrFail($id);
            $kodeKegiatan->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            }

            return redirect()->route('referensi.kode-kegiatan.index')->with('success', 'Data Berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting kode kegiatan: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('referensi.kode-kegiatan.index')->with('error', 'Gagal menghapus data');
        }
    }

    /**
     * Import data from Excel file with AJAX support
     */
    public function import(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi file gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'File tidak valid: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $import = new KodeKegiatanImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getRowCount();
            $duplicateCount = count($import->getDuplicates());
            $errorCount = count($import->failures());

            // Jika request AJAX, return JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'imported_count' => $successCount,
                    'duplicate_count' => $duplicateCount,
                    'error_count' => $errorCount,
                    'message' => "Berhasil mengimport {$successCount} data"
                ]);
            }

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
                /** @var \Maatwebsite\Excel\Validators\Failure $failure */
                foreach ($import->failures() as $failure) {
                    $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                }
                $messages['import_errors'] = $errorMessages;
                $messages['error'] = "{$errorCount} data tidak valid";
            }

            return redirect()->route('referensi.kode-kegiatan.index')
                ->with($messages);
        } catch (\Exception $e) {
            Log::error('Error importing kode kegiatan: ' . $e->getMessage());

            // Jika request AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download template for import
     */
    public function downloadTemplate(): BinaryFileResponse|RedirectResponse
    {
        $path = storage_path('app/public/templates/template_kode_kegiatan.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return redirect()->back()
            ->with('error', 'Template tidak ditemukan');
    }
}
