@extends('layouts.app')
@include('layouts.navbar')
@include('layouts.sidebar')
@section('content')
<div class="container-fluid p-4">
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- Top Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-arrow-left text-blue me-2"></i>
                    <a href="{{ route('penatausahaan.penatausahaan') }}" class="text-decoration-none"><span
                            class="text-blue small">Kembali ke Pengelolaan</span></a>
                </div>
                <div class="d-flex align-items-center small text-muted">
                    <i class="bi bi-question-circle me-2"></i>
                    <span>Butuh panduan mengisi?</span>
                    <a href="#" class="text-blue text-decoration-underline ms-2">Baca Panduan Selengkapnya</a>
                </div>
            </div>

            <!-- Main Header -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-semibold text-dark mb-1">BKU {{ $bulan }} {{$tahun}}</h1>
                    <div class="d-flex align-items-center small text-muted">
                        <span>BKSP REGULER {{$tahun}}</span>
                        <span class="mx-2">|</span>
                        @if($is_closed)
                        <span class="badge bg-danger me-2">Terkunci</span>
                        @if(!$has_transactions)
                        <span class="badge bg-info">BKU Kosong - Terkunci</span>
                        @endif
                        @else
                        <span class="badge bg-success me-2">Terbuka</span>
                        @if(count($bkuData) > 0)
                        <button href="#" class="btn btn-danger btn-sm ms-2" id="hapusSemuaBulan"
                            data-bulan="{{ $bulan }}" data-tahun="{{ $tahun }}"
                            style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .55rem;">
                            Hapus BKU
                        </button>
                        @endif
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    @if(!$is_closed)
                    <button type="button" class="btn btn-outline-secondary btn-sm-custom" data-bs-toggle="modal"
                        data-bs-target="#transactionModal" id="btnTambahTransaksi">
                        <i class="bi bi-plus me-1"></i>
                        Tambah Pembelanjaan
                    </button>
                    {{-- TOMBOL AUDIT --}}
                    <button type="button" class="btn btn-outline-info btn-sm-custom" id="btnAudit">
                        <i class="bi bi-search me-1"></i>
                        Audit Data
                    </button>
                    <a href="{{ route('laporan.rekapan-bku') }}?tahun={{ $tahun }}"
                        class="btn btn-outline-secondary btn-sm-custom" id="btnCetak">
                        <i class="bi bi-printer me-1"></i>
                        Cetak
                    </a>
                    <button type="button" class="btn btn-dark btn-sm-custom" data-bs-toggle="modal"
                        data-bs-target="#tutupBku" id="btnTutupBku">
                        Tutup BKU
                    </button>
                    @else
                    <button type="button" class="btn btn-success btn-sm-custom" id="btnBukaBku">
                        <i class="bi bi-unlock me-1"></i>
                        Buka BKU
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm-custom" id="btnEditBunga">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Bunga Bank
                    </button>
                    {{-- TOMBOL AUDIT UNTUK BKU TERTUTUP --}}
                    <button type="button" class="btn btn-outline-info btn-sm-custom" id="btnAudit">
                        <i class="bi bi-search me-1"></i>
                        Audit Data
                    </button>
                    <a href="{{ route('laporan.rekapan-bku') }}?tahun={{ $tahun }}"
                        class="btn btn-outline-secondary btn-sm-custom" id="btnCetak">
                        <i class="bi bi-printer me-1"></i>
                        Cetak
                    </a>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column - Dana Tersedia -->
        <div class="col-lg-6">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-inline-flex mb-1 justify-content-between w-100 align-items-center">
                        <h5 class="small fw-bold text-muted me-3">TOTAL DANA TERSEDIA</h5>
                        <div class="btn small-group" role="group" aria-label="Basic example">
                            <button class="btn btn-sm btn-dark me-2" id="btnTarikTunai"
                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                data-bs-toggle="modal" data-bs-target="#tarikTunai">Tarik Tunai</button>
                            <button class="btn btn-sm btn-light" id="btnSetorTunai"
                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                data-bs-toggle="modal" data-bs-target="#setorTunai">Setor Tunai</button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h4 class="fw-semibold text-dark">Rp {{ number_format($totalDanaTersedia -
                            $totalDibelanjakanSampaiBulanIni, 0, ',', '.') }}</h4>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <span class="form-label">Non Tunai</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                <input type="text" class="form-control" id="saldoNonTunaiDisplay"
                                    value="{{ number_format($saldoNonTunai, 0, ',', '.') }}"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                            </div>
                        </div>
                        <div class="col-6">
                            <span class="form-label">Tunai</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                <input type="text" class="form-control" id="saldoTunaiDisplay"
                                    value="{{ number_format($saldoTunai, 0, ',', '.') }}"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Anggaran -->
        <div class="col-lg-6">
            <!-- Anggaran Card -->
            <div class="card anggaran-card">
                <div class="card-body">
                    <h6 class="small fw-medium text-blue mt-3 mb-4">ANGGARAN DIBELANJAKAN SAMPAI BULAN INI</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Bisa dibelanjakan</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($anggaranBulanIni, 0, ',', '.') }}"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Sudah Dibelanjakan</span>
                                    <i class="bi bi-question-circle help-icon ms-1"
                                        title="Total belanja yang sudah dilakukan pada bulan {{ $bulan }}"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($totalDibelanjakanBulanIni, 0, ',', '.') }}"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                <div class="mb-3">
                                    <span class="text-muted-custom">Bisa dianggarkan ulang</span>
                                    <i class="bi bi-question-circle help-icon ms-1"></i>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Rp</span>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($anggaranBelumDibelanjakan, 0, ',', '.') }}"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"
                                        disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row col">
            <!-- Table Card -->
            <div class="card mt-4">
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-3">ID</th>
                                    <th scope="col" class="px-4 py-3">Tanggal</th>
                                    <th scope="col" class="px-4 py-3">Kegiatan</th>
                                    <th scope="col" class="px-4 py-3">Rekening Belanja</th>
                                    <th scope="col" class="px-4 py-3">Jenis Transaksi</th>
                                    <th scope="col" class="px-4 py-3">Anggaran</th>
                                    <th scope="col" class="px-4 py-3">Dibelanjakan</th>
                                    <th scope="col" class="px-4 py-3">Pajak Wajib Lapor</th>
                                    <th scope="col" class="px-4 py-3 text-center" style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="bkuTableBody">
                                <!-- Baris Penerimaan Dana (Hanya tampil di bulan yang sesuai) -->
                                @if($penerimaanDanas && $penerimaanDanas->count() > 0)
                                @foreach($penerimaanDanas as $penerimaan)
                                @php
                                $bulanPenerimaan = \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('F');
                                $bulanPenerimaanIndo = [
                                'January' => 'Januari',
                                'February' => 'Februari',
                                'March' => 'Maret',
                                'April' => 'April',
                                'May' => 'Mei',
                                'June' => 'Juni',
                                'July' => 'Juli',
                                'August' => 'Agustus',
                                'September' => 'September',
                                'October' => 'Oktober',
                                'November' => 'November',
                                'December' => 'Desember'
                                ][$bulanPenerimaan] ?? $bulanPenerimaan;
                                @endphp

                                @if($bulanPenerimaanIndo === $bulan)
                                <tr class="bg-light penerimaan-row">
                                    <td class="px-4 py-3">-</td>
                                    <td class="px-4 py-3">{{
                                        \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 fw-semibold">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-arrow-left-circle text-success me-2"></i>
                                            <span>Penerimaan dana {{ $penerimaan->sumber_dana }} Rp {{
                                                number_format($penerimaan->jumlah_dana, 0, ',', '.') }}</span>
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
                                                id="dropdownMenuButton{{ $penerimaan->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="border: none; background: none;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $penerimaan->id }}">
                                                <li>
                                                    <a class="dropdown-item text-danger btn-hapus-penerimaan"
                                                        href="{{ route('penerimaan-dana.destroy', $penerimaan->id ) }}"
                                                        data-id="{{ $penerimaan->id }}">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @if($penerimaan->sumber_dana === 'Bosp Reguler Tahap 1' && $penerimaan->saldo_awal)
                                <tr class="bg-light penerimaan-row">
                                    <td class="px-4 py-3">-</td>
                                    <td class="px-4 py-3">{{
                                        \Carbon\Carbon::parse($penerimaan->tanggal_saldo_awal)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 fw-semibold">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-arrow-left-circle text-success me-2"></i>
                                            <span>Saldo Awal {{ $penerimaan->sumber_dana }} Rp {{
                                                number_format($penerimaan->saldo_awal, 0, ',', '.') }}</span>
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
                                                id="dropdownMenuButton{{ $penerimaan->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="border: none; background: none;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $penerimaan->id }}">
                                                <li>
                                                    <a class="dropdown-item text-danger btn-hapus-saldo-awal"
                                                        href="{{ route('penerimaan-dana.destroy-saldo-awal', $penerimaan->id ) }}"
                                                        data-id="{{ $penerimaan->id }}">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endif
                                @endforeach
                                @endif

                                <!-- Baris Penarikan Tunai (Hanya tampil di bulan yang sesuai) -->
                                @if($penarikanTunais && $penarikanTunais->count() > 0)
                                @foreach($penarikanTunais as $penarikan)
                                @php
                                $bulanPenarikan = \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('F');
                                $bulanPenarikanIndo = [
                                'January' => 'Januari',
                                'February' => 'Februari',
                                'March' => 'Maret',
                                'April' => 'April',
                                'May' => 'Mei',
                                'June' => 'Juni',
                                'July' => 'Juli',
                                'August' => 'Agustus',
                                'September' => 'September',
                                'October' => 'Oktober',
                                'November' => 'November',
                                'December' => 'Desember'
                                ][$bulanPenarikan] ?? $bulanPenarikan;
                                @endphp

                                @if($bulanPenarikanIndo === $bulan)
                                <tr class="bg-light penarikan-row">
                                    <td class="px-4 py-3">-</td>
                                    <td class="px-4 py-3">{{
                                        \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 fw-semibold">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-arrow-right-circle text-danger me-2"></i>
                                            <span>Penarikan tunai Rp {{ number_format($penarikan->jumlah_penarikan, 0,
                                                ',', '.') }}</span>
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
                                                id="dropdownMenuButton{{ $penarikan->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="border: none; background: none;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $penarikan->id }}">
                                                <li>
                                                    <a class="dropdown-item text-danger btn-hapus-penarikan"
                                                        href="{{ route('penarikan.destroy', $penarikan->id ) }}"
                                                        data-id="{{ $penarikan->id }}">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                @endif

                                <!-- Baris Setor Tunai (Hanya tampil di bulan yang sesuai) -->
                                @if($setorTunais && $setorTunais->count() > 0)
                                @foreach($setorTunais as $setor)
                                @php
                                $bulanSetor = \Carbon\Carbon::parse($setor->tanggal_setor)->format('F');
                                $bulanSetorIndo = [
                                'January' => 'Januari',
                                'February' => 'Februari',
                                'March' => 'Maret',
                                'April' => 'April',
                                'May' => 'Mei',
                                'June' => 'Juni',
                                'July' => 'Juli',
                                'August' => 'Agustus',
                                'September' => 'September',
                                'October' => 'Oktober',
                                'November' => 'November',
                                'December' => 'Desember'
                                ][$bulanSetor] ?? $bulanSetor;
                                @endphp

                                @if($bulanSetorIndo === $bulan)
                                <tr class="bg-light setor-row">
                                    <td class="px-4 py-3">-</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($setor->tanggal_setor)->format('d M
                                        Y') }}</td>
                                    <td class="px-4 py-3 fw-semibold">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-arrow-left-circle text-success me-2"></i>
                                            <span>Setor tunai Rp {{ number_format($setor->jumlah_setor, 0, ',', '.')
                                                }}</span>
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
                                                id="dropdownMenuButton{{ $penerimaan->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="border: none; background: none;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $setor->id }}">
                                                <li>
                                                    <a class="dropdown-item text-danger btn-hapus-setor"
                                                        href="{{ route('setor-tunai.destroy', $setor->id ) }}"
                                                        data-id="{{ $setor->id }}">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                @endif

                                <!-- Data BKU yang sudah ada -->
                                @forelse($bkuData as $bku)
                                <tr class="bku-row" data-bku-id="{{ $bku->id }}">
                                    <td class="px-4 py-3">{{ $bku->id_transaksi }}</td>
                                    <td class="px-4 py-3">{{ $bku->tanggal_transaksi->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $bku->kodeKegiatan->sub_program }}</td>
                                    @if ($bku->uraian_opsional)
                                    <td class="px-4 py-3">
                                        {{ $bku->uraian_opsional }}
                                    </td>
                                    @else
                                    <td class="px-4 py-3">
                                        {{ $bku->rekeningBelanja->rincian_objek }}
                                    </td>
                                    @endif
                                    <td class="px-4 py-3">{{ ucfirst($bku->jenis_transaksi) }}</td>
                                    <td class="px-4 py-3">Rp {{ number_format($bku->anggaran, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">Rp {{ number_format($bku->total_transaksi_kotor, 0, ',', '.')
                                        }}</td>
                                    <td class="px-4 py-3">
                                        @if($bku->total_pajak > 0)
                                        @if($bku->ntpn)
                                        <span class="text-dark">Rp {{ number_format($bku->total_pajak, 0, ',', '.')
                                            }}</span>
                                        <small class="text-success d-block" title="NTPN: {{ $bku->ntpn }}">
                                            <i class="bi bi-check-circle-fill"></i> Sudah dilaporkan
                                        </small>
                                        @else
                                        <span class="text-danger fw-bold">Rp {{ number_format($bku->total_pajak, 0, ',',
                                            '.') }}</span>
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
                                            <button class="btn btn-sm p-0 dropdown-toggle-simple" type="button"
                                                id="dropdownMenuButton{{ $bku->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="border: none; background: none;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $bku->id }}">
                                                <li>
                                                    <a class="dropdown-item btn-view-detail" href="#"
                                                        data-bku-id="{{ $bku->id }}">
                                                        <i class="bi bi-eye me-2"></i>Lihat Detail
                                                    </a>
                                                </li>
                                                {{-- Hanya tampilkan Lapor Pajak jika ada pajak yang harus dilaporkan
                                                --}}
                                                @if($bku->total_pajak > 0)
                                                <li>
                                                    <a href="#" class="dropdown-item btn-lapor-pajak"
                                                        data-bs-toggle="modal" data-bs-target="#laporPajakModal"
                                                        data-id="{{ $bku->id }}" data-pajak="{{ $bku->total_pajak }}"
                                                        data-ntpn="{{ $bku->ntpn }}">
                                                        <i
                                                            class="bi bi-{{ $bku->ntpn ? 'check-circle' : 'info-circle' }} me-2"></i>
                                                        {{ $bku->ntpn ? 'Edit Lapor Pajak' : 'Lapor Pajak' }}
                                                    </a>
                                                </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item text-danger btn-hapus-individual"
                                                        href="{{ route('bku.destroy', $bku->id ) }}"
                                                        data-id="{{ $bku->id }}">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        Tidak ada data transaksi
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('bku.modal.tambah-modal')
@include('bku.modal.tarik-tunai-modal')
@include('bku.modal.setor-tunai-modal')
@include('bku.modal.tutup-bku-modal')
@include('bku.modal.lapor-pajak-modal')

@if(isset($penganggaran) && $penganggaran->id)
@include('bku-audit.audit-modal', ['penganggaran' => $penganggaran])
@endif

@foreach ($bkuData as $bku)
@include('bku.modal.detail-modal')
@endforeach

<!-- Modal untuk Edit Bunga Bank -->
<div class="modal fade" id="editBungaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bunga Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditBunga">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_bunga_bank" class="form-label">Bunga Bank</label>
                        <input type="number" class="form-control" id="edit_bunga_bank" name="bunga_bank"
                            value="{{ $bunga_bank }}" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_pajak_bunga_bank" class="form-label">Pajak Bunga Bank</label>
                        <input type="number" class="form-control" id="edit_pajak_bunga_bank" name="pajak_bunga_bank"
                            value="{{ $pajak_bunga_bank }}" step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
        font-size: 14px;
    }

    .card {
        border: 1px solid #e9ecef;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .text-muted-custom {
        color: #6c757d !important;
        font-size: 12px;
    }

    .text-blue {
        color: #0d6efd !important;
    }

    .btn-sm-custom {
        padding: 0.375rem 0.75rem;
        font-size: 13px;
    }

    .input-group-text-rp {
        background-color: transparent;
        border-right: none;
        color: #6c757d;
    }

    .form-control-rp {
        border-left: none;
        color: #0d6efd;
        font-weight: 500;
    }

    .table th {
        background-color: #f8f9fa;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 500;
        color: #6c757d;
    }

    .table td {
        font-size: 12px;
        font-weight: 400;
        color: #212529;
        vertical-align: middle;
    }

    .nav-tabs .nav-link {
        border: 1px solid #dee2e6;
        border-bottom: none;
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .anggaran-card {
        border-left: 4px solid #0d6efd;
    }

    .help-icon {
        width: 12px;
        height: 12px;
        color: #0d6efd;
    }

    /* Dropdown styles - FIXED */
    .dropdown-toggle-simple {
        border: none;
        background: transparent;
        padding: 0.25rem;
        display: inline-block;
    }

    .dropdown-toggle-simple:focus {
        box-shadow: none;
    }

    .dropdown-menu {
        font-size: 12px;
        min-width: 120px;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid #dee2e6;
        z-index: 1060;
    }

    .dropdown-item {
        padding: 0.375rem 0.75rem;
        display: flex;
        align-items: center;
    }

    .dropdown-item i {
        font-size: 14px;
        width: 16px;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    /* Fix for dropdown positioning */
    .table-responsive {
        position: relative;
        overflow: visible !important;
    }

    /* Ensure dropdown is not clipped by table */
    .table {
        position: relative;
        z-index: 1;
    }

    /* Action column styles */
    td:last-child {
        position: relative;
        z-index: 2;
    }

    /* Specific fix for dropdown in table */
    .dropdown {
        position: static;
    }

    .dropdown-menu {
        position: absolute;
        inset: 0px auto auto 0px;
        margin: 0px;
        transform: translate(-120px, 30px);
    }

    /* Ensure dropdown appears above table */
    .table-responsive>.table>tbody>tr>td:last-child {
        overflow: visible;
    }

    /* Style for disabled state */
    .disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    /* Style khusus untuk baris penerimaan dana */
    .table-hover .bg-light:hover {
        background-color: #e9ecef !important;
    }

    /* Style untuk ikon panah */
    .bi-arrow-left-circle {
        color: #198754;
        /* Hijau untuk penerimaan/setor */
    }

    .bi-arrow-right-circle {
        color: #dc3545;
        /* Merah untuk penarikan */
    }

    /* Style untuk status pajak */
    .text-pajak-belum-lapor {
        color: #dc3545 !important;
        font-weight: 600;
    }

    .text-pajak-sudah-lapor {
        color: #212529 !important;
        font-weight: 500;
    }

    .status-pajak-badge {
        font-size: 0.7rem;
        padding: 0.1rem 0.3rem;
        border-radius: 0.25rem;
    }

    .badge-belum-lapor {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .badge-sudah-lapor {
        background-color: #d1edff;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Debug section styles */
    .debug-table {
        font-size: 12px;
    }

    .debug-table th {
        background-color: #fff3cd;
    }

    .debug-positive {
        color: #198754;
        font-weight: bold;
    }

    .debug-negative {
        color: #dc3545;
        font-weight: bold;
    }

    /* Search result styling */
    .search-result-info {
        font-size: 0.875rem;
        border-left: 4px solid #0dcaf0;
    }

    .search-result-row {
        background-color: #f8f9fa !important;
        border-left: 3px solid #0d6efd;
    }

    .search-result-row:hover {
        background-color: #e9ecef !important;
    }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Document ready - BKU script loaded');

        // Function untuk disable/enable elements
        function toggleBkuStatus(isClosed, hasTransactions) {
            $('#btnTambahTransaksi, #btnCari, #btnCetak, #btnTutupBku, #btnTarikTunai, #btnSetorTunai').prop('disabled', isClosed);
            
            // Hanya non-aktifkan hapus jika ada data dan terkunci
            if (isClosed && hasTransactions) {
                $('.btn-hapus-individual').addClass('disabled');
                $('#hapusSemuaBulan').hide();
            } else {
                $('.btn-hapus-individual').removeClass('disabled');
                if (hasTransactions) {
                    $('#hapusSemuaBulan').show();
                } else {
                    $('#hapusSemuaBulan').hide();
                }
            }
        }

        // Initialize status
        toggleBkuStatus({{ $is_closed ? 'true' : 'false' }}, {{ $has_transactions ? 'true' : 'false' }});

        // Event listener untuk tombol hapus semua
        $(document).on('click', '#hapusSemuaBulan', function(e) {
            e.preventDefault();
            console.log('Hapus semua button clicked');

            const bulan = $(this).data('bulan');
            const tahun = $(this).data('tahun');
            const jumlahData = {{ count($bkuData) }};

            console.log('Bulan:', bulan, 'Tahun:', tahun, 'Jumlah Data:', jumlahData);

            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 not loaded!');
                alert('Error: SweetAlert2 tidak terload. Silakan refresh halaman.');
                return;
            }

            Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `<div class="text-start">
                    <p>Anda akan menghapus <strong>SEMUA</strong> data BKU untuk:</p>
                    <ul>
                        <li>Bulan: <strong>${bulan}</strong></li>
                        <li>Tahun: <strong>${tahun}</strong></li>
                        <li>Jumlah data: <strong>${jumlahData} transaksi</strong></li>
                    </ul>
                    <p class="text-danger mt-3"><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!</p>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        console.log('Sending AJAX request...');
                    
                        $.ajax({
                            url: '{{ route("bku.destroy-all-bulan", ["tahun" => ":tahun", "bulan" => ":bulan"]) }}'
                                .replace(':tahun', tahun)
                                .replace(':bulan', bulan),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            success: function(response) {
                                console.log('AJAX success:', response);
                                resolve(response);
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX error:', xhr, status, error);
                                let errorMessage = 'Terjadi kesalahan server';
                        
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.status === 404) {
                                    errorMessage = 'Data tidak ditemukan';
                                } else if (xhr.status === 422) {
                                    errorMessage = 'Terjadi kesalahan validasi';
                                } else if (xhr.status === 500) {
                                    errorMessage = 'Error server internal';
                                }

                                reject(new Error(errorMessage));
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Delete confirmed, result:', result);
                    Swal.fire({
                        title: 'Berhasil!',
                        html: `<div class="text-start">
                            <p>${result.value.message}</p>
                            <p class="text-success">Jumlah data yang dihapus: <strong>${result.value.deleted_count}</strong></p>
                        </div>`,
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        console.log('Reloading page...');
                        location.reload();
                    });
                } else {
                    console.log('Delete cancelled');
                }
            }).catch((error) => {
                console.error('SweetAlert error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });
            });
        });

        // SweetAlert untuk hapus data individual
        $(document).on('click', 'a.btn-hapus-individual', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Apakah Anda yakin ingin menghapus transaksi ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat form untuk submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Tambahkan CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
                    form.appendChild(csrfToken);

                    // Tambahkan method spoofing
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);

                    // Submit form
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // SweetAlert untuk hapus data penarikan
        $(document).on('click', 'a.btn-hapus-penarikan', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Apakah Anda yakin ingin menghapus penarikan tunai ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat form untuk submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Tambahkan CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
                    form.appendChild(csrfToken);

                    // Tambahkan method spoofing
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);

                    // Submit form
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // SweetAlert untuk hapus data penerimaan
        $(document).on('click', 'a.btn-hapus-penerimaan', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Apakah Anda yakin ingin menghapus perimaan dana ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat form untuk submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Tambahkan CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
                    form.appendChild(csrfToken);

                    // Tambahkan method spoofing
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);

                    // Submit form
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // SweetAlert untuk hapus data saldo awal
        $(document).on('click', 'a.btn-hapus-saldo-awal', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Apakah Anda yakin ingin menghapus Saldo Awal dana ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat form untuk submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Tambahkan CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
                    form.appendChild(csrfToken);

                    // Tambahkan method spoofing
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);

                    // Submit form
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // SweetAlert untuk hapus data setor
        $(document).on('click', 'a.btn-hapus-setor', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Apakah Anda yakin ingin menghapus setor tunai ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat form untuk submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Tambahkan CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
                    form.appendChild(csrfToken);

                    // Tambahkan method spoofing
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);

                    // Submit form
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Form tutup BKU
        $('#formTutupBku').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                bunga_bank: $('#bunga_bank').val().replace(/[^\d]/g, ''),
                pajak_bunga_bank: $('#pajak_bunga_bank').val().replace(/[^\d]/g, ''),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            Swal.fire({
                title: 'Tutup BKU?',
                text: 'Apakah Anda yakin ingin menutup BKU untuk bulan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tutup BKU',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("bku.tutup", ["tahun" => $tahun, "bulan" => $bulan]) }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            Swal.fire('Berhasil!', 'BKU berhasil ditutup', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        });

        // Buka BKU
        $('#btnBukaBku').on('click', function() {
            Swal.fire({
                title: 'Buka BKU?',
                text: 'Apakah Anda yakin ingin membuka BKU untuk bulan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buka BKU',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("bku.buka", ["tahun" => $tahun, "bulan" => $bulan]) }}',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire('Berhasil!', 'BKU berhasil dibuka', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        });

        // Edit bunga bank
        $('#btnEditBunga').on('click', function() {
            $('#editBungaModal').modal('show');
        });

        $('#formEditBunga').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                bunga_bank: $('#edit_bunga_bank').val(),
                pajak_bunga_bank: $('#edit_pajak_bunga_bank').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: '{{ route("bku.update-bunga", ["tahun" => $tahun, "bulan" => $bulan]) }}',
                method: 'PUT',
                data: formData,
                success: function(response) {
                    Swal.fire('Berhasil!', 'Data bunga bank berhasil diperbarui', 'success').then(() => {
                        $('#editBungaModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        });

        // Fix dropdown positioning
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });

        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.style.zIndex = '1060';
        });

        // Debug button handler
        $('#btnDebug').on('click', function() {
            runDebugCalculation();
        });

        // Initial attach event listeners
        attachBkuEventListeners();
    });

    // Function untuk attach BKU specific event listeners
    function attachBkuEventListeners() {
        console.log('Attaching BKU event listeners...');
        
        // Detail modal
        $('.btn-view-detail').off('click').on('click', function(e) {
            e.preventDefault();
            const bkuId = $(this).data('bku-id');
            $(`#detailModal${bkuId}`).modal('show');
        });

        // Lapor pajak modal
        $('.btn-lapor-pajak').off('click').on('click', function(e) {
            e.preventDefault();
            const bkuId = $(this).data('id');
            const totalPajak = $(this).data('pajak');
            const existingNtpn = $(this).data('ntpn');
            
            $('#bku_id').val(bkuId);
            
            console.log('BKU ID:', bkuId, 'Pajak:', totalPajak, 'NTPN:', existingNtpn);
            
            if (totalPajak > 0) {
                const url = '/bku/' + bkuId + '/get-pajak';
                
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#tanggal_lapor').val(response.data.tanggal_lapor);
                            $('#ntpn').val(response.data.ntpn);
                            $('#kode_masa_pajak').val(response.data.kode_masa_pajak);
                            
                            if (response.data.ntpn) {
                                $('#laporPajakModalLabel').html(`<i class="bi bi-check-circle-fill text-success me-2"></i>Edit Lapor Pajak`);
                                $('.modal-header').removeClass('bg-warning').addClass('bg-success');
                            } else {
                                $('#laporPajakModalLabel').html(`<i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Lapor Pajak`);
                                $('.modal-header').removeClass('bg-success').addClass('bg-warning');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading pajak data:', xhr);
                        Swal.fire('Error!', 'Gagal memuat data pajak', 'error');
                    }
                });
                
                $('#laporPajakModal').modal('show');
            }
        });

        console.log('BKU event listeners attached successfully');
    }

    // Event listener untuk search results updated dari search.js
    document.addEventListener('searchResultsUpdated', function(e) {
        if (e.detail.page === 'bku') {
            console.log('Re-attaching BKU event listeners after search...');
            attachBkuEventListeners();
        }
    });

    // Function untuk update display saldo
    function updateSaldoDisplay(saldoData) {
        if (saldoData) {
            $('#totalDanaTersediaDisplay').text('Rp ' + formatRupiah(saldoData.total_dana_tersedia));
            $('#saldoNonTunaiDisplay').val('Rp ' + formatRupiah(saldoData.non_tunai));
            $('#saldoTunaiDisplay').val('Rp ' + formatRupiah(saldoData.tunai));
        }
    }

    // Function format rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    // Event listener untuk success simpan transaksi
    document.addEventListener('DOMContentLoaded', function() {
        $(document).on('bkuSaved', function(e, data) {
            if (data.saldo_update) {
                updateSaldoDisplay(data.saldo_update);
                
                $('input[data-max]').each(function() {
                    const field = $(this);
                    if (field.attr('id') === 'jumlah_penarikan') {
                        field.attr('data-max', data.saldo_update.non_tunai);
                        field.next('small').text('Maksimal: Rp ' + formatRupiah(data.saldo_update.non_tunai));
                    } else if (field.attr('id') === 'jumlah_setor') {
                        field.attr('data-max', data.saldo_update.tunai);
                        field.next('small').text('Maksimal: Rp ' + formatRupiah(data.saldo_update.tunai));
                    }
                });
            }
        });

        // Event listener untuk simpan lapor pajak
        $('#btnSimpanLapor').on('click', function() {
            const bkuId = $('#bku_id').val();
            
            if (!bkuId) {
                Swal.fire('Error!', 'ID transaksi tidak valid', 'error');
                return;
            }
            
            const formData = {
                tanggal_lapor: $('#tanggal_lapor').val(),
                ntpn: $('#ntpn').val(),
                kode_masa_pajak: $('#kode_masa_pajak').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            let isValid = true;
            $('#tanggal_lapor_error, #ntpn_error', '#kode_masa_pajak_error').text('');
            
            if (!formData.tanggal_lapor) {
                $('#tanggal_lapor_error').text('Tanggal lapor wajib diisi');
                isValid = false;
            }

            if (!formData.kode_masa_pajak) {
                $('#kode_masa_pajak_error').text('Kode masa pajak wajib diisi');
                isValid = false;
            }
            
            if (!formData.ntpn) {
                $('#ntpn_error').text('NTPN wajib diisi');
                isValid = false;
            } else if (formData.ntpn.length !== 16) {
                $('#ntpn_error').text('NTPN harus 16 digit');
                isValid = false;
            }
            
            if (!isValid) return;
            
            const url = '/bku/' + bkuId + '/lapor-pajak';
            
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#btnSimpanLapor').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            html: `<div class="text-start">
                                <p>${response.message}</p>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-muted">NTPN: <strong>${formData.ntpn}</strong></small><br>
                                    <small class="text-muted">Tanggal Lapor: <strong>${formData.tanggal_lapor}</strong></small><br>
                                    <small class="text-muted">Kode Masa Pajak: <strong>${formData.kode_masa_pajak}</strong></small>
                                </div>
                            </div>`,
                            icon: 'success',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            $('#laporPajakModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan';
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(field => {
                            $(`#${field}_error`).text(errors[field][0]);
                        });
                        errorMessage = 'Terjadi kesalahan validasi';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire('Error!', errorMessage, 'error');
                },
                complete: function() {
                    $('#btnSimpanLapor').prop('disabled', false).html('Simpan');
                }
            });
        });

        // Reset form ketika modal ditutup
        $('#laporPajakModal').on('hidden.bs.modal', function() {
            $('#formLaporPajak')[0].reset();
            $('#tanggal_lapor_error, #ntpn_error', '#kode_masa_pajak_error').text('');
        });
    });
</script>
@endpush
@endsection
@include('layouts.footer')