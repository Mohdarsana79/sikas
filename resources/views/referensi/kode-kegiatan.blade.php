@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
    <div class="content-area" id="contentArea">
        <div class="fade-in">
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gradient">Kode Kegiatan</h1>
                    <p class="text-muted">Kelola data Program Kegiatan Anda.</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Baru
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload me-2"></i>Import Excel
                    </button>
                    <a href="{{ route('referensi.kode-kegiatan.download-template') }}"
                        class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-download me-2"></i>Download Template
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode</th>
                                    <th>Program</th>
                                    <th>Sub Program</th>
                                    <th>Uraian</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kegiatanTableBody">
                                @foreach ($kodeKegiatans as $key => $kegiatan)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $kegiatan->kode }}</td>
                                        <td>{{ $kegiatan->program }}</td>
                                        <td>{{ $kegiatan->sub_program }}</td>
                                        <td>{{ $kegiatan->uraian }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $kegiatan->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ $kegiatan->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Menampilkan <span class="fw-semibold">{{ $kodeKegiatans->firstItem() }}</span> sampai
                                <span class="fw-semibold">{{ $kodeKegiatans->lastItem() }}</span> dari
                                <span class="fw-semibold">{{ $kodeKegiatans->total() }}</span> data
                            </div>
                            <div class="pagination-container">
                                {{ $kodeKegiatans->links('vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambah Modal -->
    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Program Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('referensi.kode-kegiatan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode</label>
                            <input type="text" class="form-control @error('kode') is_invalid @enderror" id="kode"
                                name="kode" value="{{ old('kode') }}" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="program" class="form-label">program</label>
                            <input type="text" class="form-control @error('program') is_invalid @enderror" id="program"
                                name="program" value="{{ old('program') }}" required>
                            @error('program')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="sub_program" class="form-label">Sub Program</label>
                            <textarea class="form-control @error('sub_program') is-invalid @enderror" id="sub_program" name="sub_program"
                                rows="3" required>{{ old('sub_program') }}</textarea>
                            @error('sub_program')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="uraian" class="form-label">Uraian</label>
                            <textarea class="form-control @error('uraian') is-invalid @enderror" id="uraian" name="uraian" rows="3"
                                required>{{ old('uraian') }}</textarea>
                            @error('uraian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modals (One for each item) -->
    @foreach ($kodeKegiatans as $kegiatan)
        <div class="modal fade" id="editModal{{ $kegiatan->id }}" tabindex="-1"
            aria-labelledby="editModalLabel{{ $kegiatan->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $kegiatan->id }}">Edit Program Kegiatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('referensi.kode-kegiatan.update', $kegiatan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="kode" class="form-label">Kode</label>
                                <input type="text" class="form-control @error('kode') is_invalid @enderror"
                                    id="kode" name="kode" value="{{ old('kode', $kegiatan->kode) }}" required>
                                @error('kode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="program" class="form-label">program</label>
                                <input type="text" class="form-control @error('program') is_invalid @enderror"
                                    id="program" name="program" value="{{ old('program', $kegiatan->program) }}"
                                    required>
                                @error('program')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="sub_program" class="form-label">Sub Program</label>
                                <textarea class="form-control @error('sub_program') is-invalid @enderror" id="sub_program" name="sub_program"
                                    rows="3" required>{{ old('sub_program', $kegiatan->sub_program) }}</textarea>
                                @error('sub_program')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="uraian" class="form-label">Uraian</label>
                                <textarea class="form-control @error('uraian') is-invalid @enderror" id="uraian" name="uraian" rows="3"
                                    required>{{ old('uraian', $kegiatan->uraian) }}</textarea>
                                @error('uraian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Delete Modals (One for each item) -->
    @foreach ($kodeKegiatans as $kegiatan)
        <div class="modal fade" id="deleteModal{{ $kegiatan->id }}" tabindex="-1"
            aria-labelledby="deleteModalLabel{{ $kegiatan->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel{{ $kegiatan->id }}">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('referensi.kode-kegiatan.destroy', $kegiatan->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menghapus kegiatan ini?</p>
                            <p><strong>{{ $kegiatan->kode }} - {{ $kegiatan->program }} - {{ $kegiatan->sub_program }} -
                                    {{ $kegiatan->uraian }}</strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('referensi.kode-kegiatan.import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">File Excel</label>
                            <input type="file" class="form-control" id="file" name="file"
                                accept=".xlsx,.xls" required>
                            <div class="form-text">Format file harus .xlsx atau .xls</div>
                        </div>
                        <div class="card bg-info">
                            <strong>Petunjuk:</strong>
                            <ul class="mb-0">
                                <li>Download template terlebih dahulu</li>
                                <li>Pastikan format kolom sesuai template</li>
                                <li>Data duplikat akan diabaikan</li>
                                <li>Kegiatan Wajib di Awali dengan Huruf Kapital</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
