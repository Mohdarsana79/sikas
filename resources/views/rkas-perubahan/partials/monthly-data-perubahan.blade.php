@if(empty($rkasBulanan))
<tr>
    <td colspan="8" class="text-center text-muted">Tidak ada data untuk bulan {{ $bulan }}</td>
</tr>
@else
@php $counter = 1; @endphp
@foreach($rkasBulanan as $kodeProgram => $program)
@php
// Data Program
$programUraian = $program->first()->kodeKegiatan->program ?? '-';
$programTotal = $program->sum(fn($item) => $item->jumlah * $item->harga_satuan);

// Group by sub program (kode 2 level)
$subPrograms = $program->groupBy(function($item) {
return explode('.', optional($item->kodeKegiatan)->kode ?? '')[0] . '.' .
(explode('.', optional($item->kodeKegiatan)->kode ?? '')[1] ?? '0');
});
@endphp

<!-- Baris Program -->
<tr class="table-primary">
    <td class="text-center">{{ $counter++ }}</td>
    <td></td>
    <td>{{ $kodeProgram }}</td>
    <td><strong>{{ $programUraian }}</strong></td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td class="text-right"><strong>{{ number_format($programTotal, 0, ',', '.') }}</strong></td>
</tr>

@foreach($subPrograms as $kodeSubProgram => $subProgram)
@php
// Data Sub Program
$subProgramUraian = $subProgram->first()->kodeKegiatan->sub_program ?? '-';
$subProgramTotal = $subProgram->sum(fn($item) => $item->jumlah * $item->harga_satuan);

// Group by uraian program (kode 3 level)
$uraianPrograms = $subProgram->groupBy(function($item) {
return optional($item->kodeKegiatan)->kode ?? 'unknown';
});
@endphp

<!-- Baris Sub Program -->
<tr class="table-secondary">
    <td class="text-center">{{ $counter++ }}</td>
    <td></td>
    <td>{{ $kodeSubProgram }}</td>
    <td style="padding-left: 20px;">{{ $subProgramUraian }}</td>
    <td></td>
    <td></td>
    <td></td>
    <td class="text-right">{{ number_format($subProgramTotal, 0, ',', '.') }}</td>
</tr>

@foreach($uraianPrograms as $kodeUraian => $uraian)
@php
// Data Uraian Program
$uraianUraian = $uraian->first()->kodeKegiatan->uraian ?? '-';
$uraianTotal = $uraian->sum(fn($item) => $item->jumlah * $item->harga_satuan);
@endphp

<!-- Baris Uraian Program -->
<tr class="table-light">
    <td class="text-center">{{ $counter++ }}</td>
    <td></td>
    <td>{{ $kodeUraian }}</td>
    <td style="padding-left: 40px;">{{ $uraianUraian }}</td>
    <td></td>
    <td></td>
    <td></td>
    <td class="text-right">{{ number_format($uraianTotal, 0, ',', '.') }}</td>
</tr>

@foreach($uraian as $item)
@php
// Data Item Detail
$kodeRekening = $item->rekeningBelanja->kode_rekening ?? '-';
$uraianItem = $item->uraian ?? '-';
$volume = $item->jumlah ?? 0;
$satuan = $item->satuan ?? '-';
$hargaSatuan = $item->harga_satuan ?? 0;
$jumlah = $volume * $hargaSatuan;
@endphp

<!-- Baris Item Detail -->
<tr>
    <td class="text-center">{{ $counter++ }}</td>
    <td>{{ $kodeRekening }}</td>
    <td>{{ $kodeUraian }}</td>
    <td style="padding-left: 60px;" class="detail-item">{{ $uraianItem }}</td>
    <td class="text-center">{{ $volume }}</td>
    <td class="text-center">{{ $satuan }}</td>
    <td class="text-right">{{ number_format($hargaSatuan, 0, ',', '.') }}</td>
    <td class="text-right">{{ number_format($jumlah, 0, ',', '.') }}</td>
</tr>
@endforeach
@endforeach
@endforeach
@endforeach
@endif