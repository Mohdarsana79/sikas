@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
    <div class="content-area" id="contentArea">
        <div class="fade-in">
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gradient">Rekening Belanja Operasional</h1>
                    <p class="text-muted">Kelola data rekening belanja anda.</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Baru
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload me-2"></i>Import Excel
                    </button>
                    <a href="{{ route('referensi.rekening-belanja.download-template') }}"
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
                                    <th width="15%">Kode Rekening</th>
                                    <th>Rincian Objek</th>
                                    <th>Kategori Belanja</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rekeningTableBody">
                                @foreach ($rekenings as $key => $rekening)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $rekening->kode_rekening }}</td>
                                        <td>{{ $rekening->rincian_objek }}</td>
                                        <td>{{ $rekening->kategori }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $rekening->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ $rekening->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Menampilkan <span class="fw-semibold">{{ $rekenings->firstItem() }}</span> sampai
                                <span class="fw-semibold">{{ $rekenings->lastItem() }}</span> dari
                                <span class="fw-semibold">{{ $rekenings->total() }}</span> data
                            </div>
                            <div class="pagination-container">
                                {{ $rekenings->links('vendor.pagination.bootstrap-5') }}
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
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Rekeing Belanja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('referensi.rekening-belanja.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kode_rekening" class="form-label">Kode Rekening</label>
                            <input type="text" class="form-control @error('kode_rekening') is-invalid @enderror"
                                id="kode_rekening" name="kode_rekening" value="{{ old('kode_rekening') }}" required>
                            @error('kode_rekening')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="rincian_objek" class="form-label">Rincian Objek Belanja</label>
                            <textarea class="form-control @error('rincian_objek') is-invalid @enderror" id="rincian_objek" name="rincian_objek"
                                rows="3" required>{{ old('rincian_objek') }}</textarea>
                            @error('rincian_objek')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori Belanja</label>
                            <select name="kategori" id="kategori" class="form-control">
                                <option value="">Pilih Kategori</option>
                                <option value="Modal">Modal</option>
                                <option value="Operasi">Operasi</option>
                            </select>
                            @error('kategori')
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
    @foreach ($rekenings as $rekening)
        <div class="modal fade" id="editModal{{ $rekening->id }}" tabindex="-1"
            aria-labelledby="editModalLabel{{ $rekening->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $rekening->id }}">Edit Rekening Belanja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('referensi.rekening-belanja.update', $rekening->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="kode_rekening" class="form-label">Kode Rekening</label>
                                <input type="text" class="form-control @error('kode_rekening') is-invalid @enderror"
                                    id="kode_rekening" name="kode_rekening"
                                    value="{{ old('kode_rekening', $rekening->kode_rekening) }}" required>
                                @error('kode_rekening')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="rincian_objek" class="form-label">Rincian Objek Belanja</label>
                                <textarea class="form-control @error('rincian_objek') is-invalid @enderror" id="rincian_objek" name="rincian_objek"
                                    rows="3" required>{{ old('rincian_objek', $rekening->rincian_objek) }}</textarea>
                                @error('rincian_objek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori Belanja</label>
                                <select name="kategori" id="kategori"
                                    class="form-control @error('kategori') is-invalid @enderror" required>
                                    <option value="Modal"
                                        {{ old('kategori', $rekening->kategori) == 'Modal' ? 'selected' : '' }}>Modal
                                    </option>
                                    <option value="Operasi"
                                        {{ old('kategori', $rekening->kategori) == 'Operasi' ? 'selected' : '' }}>Operasi
                                    </option>
                                </select>
                                @error('kategori')
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
    @foreach ($rekenings as $rekening)
        <div class="modal fade" id="deleteModal{{ $rekening->id }}" tabindex="-1"
            aria-labelledby="deleteModalLabel{{ $rekening->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel{{ $rekening->id }}">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('referensi.rekening-belanja.destroy', $rekening->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menghapus Rekening Belanja ini?</p>
                            <p><strong>{{ $rekening->kode_rekening }} - {{ $rekening->rincian_objek }}</strong></p>
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
                <form action="{{ route('referensi.rekening-belanja.import') }}" method="POST"
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
