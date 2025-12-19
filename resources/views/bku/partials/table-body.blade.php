@php
use Carbon\Carbon;

$hasData = !empty($tableData['penerimaan_danas']) || !empty($tableData['penarikan_tunais']) ||
!empty($tableData['setor_tunais']) || !empty($tableData['bku_data']);
@endphp

@if(!$hasData)
<tr>
    <td colspan="9" class="text-center py-5 text-muted">
        Tidak ada data transaksi
    </td>
</tr>
@else
<!-- Baris Penerimaan Dana -->
@foreach($tableData['penerimaan_danas'] as $penerimaan)
<tr class="bg-light penerimaan-row">
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">{{ $penerimaan['tanggal'] }}</td>
    <td class="px-4 py-3 fw-semibold">
        <div class="d-flex align-items-center">
            <i class="bi bi-arrow-left-circle text-success me-2"></i>
            <span>{{ $penerimaan['uraian'] }}</span>
        </div>
    </td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3 text-center">
        <div class="dropdown dropstart">
            <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button"
                id="dropdownMenuButton{{ $penerimaan['id'] }}" data-bs-toggle="dropdown" aria-expanded="false"
                style="border: none; background: none;">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $penerimaan['id'] }}">
                <li>
                    <a class="dropdown-item text-danger btn-hapus-penerimaan" href="{{ $penerimaan['delete_url'] }}"
                        data-id="{{ $penerimaan['id'] }}">
                        <i class="bi bi-trash me-2"></i>Hapus
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>
@endforeach

<!-- Baris Penarikan Tunai -->
@foreach($tableData['penarikan_tunais'] as $penarikan)
<tr class="bg-light penarikan-row">
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">{{ $penarikan['tanggal'] }}</td>
    <td class="px-4 py-3 fw-semibold">
        <div class="d-flex align-items-center">
            <i class="bi bi-arrow-right-circle text-danger me-2"></i>
            <span>{{ $penarikan['uraian'] }}</span>
        </div>
    </td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3 text-center">
        <div class="dropdown dropstart">
            <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button"
                id="dropdownMenuButton{{ $penarikan['id'] }}" data-bs-toggle="dropdown" aria-expanded="false"
                style="border: none; background: none;">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $penarikan['id'] }}">
                <li>
                    <a class="dropdown-item text-danger btn-hapus-penarikan" href="{{ $penarikan['delete_url'] }}"
                        data-id="{{ $penarikan['id'] }}">
                        <i class="bi bi-trash me-2"></i>Hapus
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>
@endforeach

<!-- Baris Setor Tunai -->
@foreach($tableData['setor_tunais'] as $setor)
<tr class="bg-light setor-row">
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">{{ $setor['tanggal'] }}</td>
    <td class="px-4 py-3 fw-semibold">
        <div class="d-flex align-items-center">
            <i class="bi bi-arrow-left-circle text-success me-2"></i>
            <span>{{ $setor['uraian'] }}</span>
        </div>
    </td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3">-</td>
    <td class="px-4 py-3 text-center">
        <div class="dropdown dropstart">
            <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button"
                id="dropdownMenuButton{{ $setor['id'] }}" data-bs-toggle="dropdown" aria-expanded="false"
                style="border: none; background: none;">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $setor['id'] }}">
                <li>
                    <a class="dropdown-item text-danger btn-hapus-setor" href="{{ $setor['delete_url'] }}"
                        data-id="{{ $setor['id'] }}">
                        <i class="bi bi-trash me-2"></i>Hapus
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>
@endforeach

<!-- Data BKU -->
@foreach($tableData['bku_data'] as $bku)
<tr class="bku-row" data-bku-id="{{ $bku['id'] }}">
    <td class="px-4 py-3">{{ $bku['id_transaksi'] }}</td>
    <td class="px-4 py-3">{{ $bku['tanggal'] }}</td>
    <td class="px-4 py-3">{{ $bku['kegiatan'] }}</td>
    <td class="px-4 py-3">{{ $bku['rekening_belanja'] }}</td>
    <td class="px-4 py-3">{{ $bku['jenis_transaksi'] }}</td>
    <td class="px-4 py-3">Rp {{ $bku['anggaran'] }}</td>
    <td class="px-4 py-3">Rp {{ $bku['dibelanjakan'] }}</td>
    <td class="px-4 py-3">
        @if($bku['total_pajak'] > 0)
        @if($bku['ntpn'])
        <span class="text-dark">Rp {{ number_format($bku['total_pajak'], 0, ',', '.') }}</span>
        <small class="text-success d-block" title="NTPN: {{ $bku['ntpn'] }}">
            <i class="bi bi-check-circle-fill"></i> Sudah dilaporkan
        </small>
        @else
        <span class="text-danger fw-bold">Rp {{ number_format($bku['total_pajak'], 0, ',', '.') }}</span>
        <small class="text-warning d-block">
            <i class="bi bi-exclamation-triangle-fill"></i> Belum dilaporkan
        </small>
        @endif
        @else
        <span class="text-muted">Rp 0</span>
        @endif
    </td>
    <td class="px-4 py-3 text-center">
        <div class="dropdown dropstart">
            <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button" id="dropdownMenuButton{{ $bku['id'] }}"
                data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: none;">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $bku['id'] }}">
                <li>
                    <a class="dropdown-item btn-view-detail" href="#" data-bku-id="{{ $bku['id'] }}">
                        <i class="bi bi-eye me-2"></i>Lihat Detail
                    </a>
                </li>
                @if($bku['total_pajak'] > 0)
                <li>
                    <a href="#" class="dropdown-item btn-lapor-pajak" data-bs-toggle="modal"
                        data-bs-target="#laporPajakModal" data-id="{{ $bku['id'] }}"
                        data-pajak="{{ $bku['total_pajak'] }}" data-ntpn="{{ $bku['ntpn'] }}">
                        <i class="bi bi-{{ $bku['ntpn'] ? 'check-circle' : 'info-circle' }} me-2"></i>
                        {{ $bku['ntpn'] ? 'Edit Lapor Pajak' : 'Lapor Pajak' }}
                    </a>
                </li>
                @endif
                <li>
                    <a class="dropdown-item text-danger btn-hapus-individual" href="{{ $bku['delete_url'] }}"
                        data-id="{{ $bku['id'] }}">
                        <i class="bi bi-trash me-2"></i>Hapus
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>
@endforeach
@endif