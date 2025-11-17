<?php

namespace App\Imports;

use App\Models\KodeKegiatan;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class KodeKegiatanImport implements
    ToModel,
    WithStartRow,
    WithValidation,
    SkipsOnFailure,
    SkipsEmptyRows
{
    use \Maatwebsite\Excel\Concerns\SkipsFailures;

    private $rowCount = 0;
    private $duplicates = [];

    public function startRow(): int
    {
        return 3; // Mulai dari baris ke-2 (abaikan judul dan header)
    }

    public function model(array $row)
    {
        // Skip jika baris kosong
        if (empty($row[0]) && empty($row[1])) {
            return null;
        }

        $kode = Str::trim($row[0]);
        $program = Str::trim($row[1]);
        $subProgram = Str::trim($row[2]);
        $uraian = Str::trim($row[3]);

        // Cek duplikasi kode
        if (KodeKegiatan::where('kode', $kode)->exists()) {
            $this->duplicates[] = [
                'row' => $this->rowCount + $this->startRow(),
                'kode' => $kode,
                'program' => $program,
                'sub_program' => $subProgram,
                'uraian' => $uraian,
            ];
            return null;
        }

        $this->rowCount++;

        return new KodeKegiatan([
            'kode' => $kode,
            'program' => $program,
            'sub_program' => $subProgram,
            'uraian' => $uraian,
        ]);
    }

    public function rules(): array
    {
        return [
            '0.unique' => 'Kode :input sudah ada di database',
            '0.required' => 'Kolom Kode harus diisi',
            '1.required' => 'Kolom Program harus diisi',
            '2.required' => 'Kolom Sub Program harus diisi',
            '3.required' => 'Kolom Uraian harus diisi',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.unique' => 'Kode :input sudah ada pada baris :attribute',
            '0.required' => 'Kolom Kode harus diisi pada baris :attribute',
            '1.required' => 'Kolom program harus diisi pada baris :attribute',
            '2.required' => 'Kolom sub program harus diisi pada baris :attribute',
            '3.required' => 'Kolom uraian harus diisi pada baris :attribute',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getDuplicates(): array
    {
        return $this->duplicates;
    }
}
