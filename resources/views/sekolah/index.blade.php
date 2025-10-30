@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
    <div class="content-area" id="contentArea">
        <div class="fade-in">
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gradient">Data Sekolah</h1>
                    <p class="text-muted">Kelola data sekolah Anda.</p>
                </div>
                @if (!$exists)
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Data Sekolah
                        </button>
                    </div>
                @else
                    <div>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal">
                            <i class="bi bi-pencil-square me-2"></i>Edit Data Sekolah
                        </button>
                    </div>
                @endif
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Data Sekolah -->
            @if ($exists)
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="bg-light p-4 rounded">
                                    <i class="bi bi-building fs-1 text-primary"></i>
                                    <h4 class="mt-3">{{ $sekolah->nama_sekolah }}</h4>
                                    <p class="text-muted mb-0">NPSN: {{ $sekolah->npsn }}</p>
                                    <p class="text-muted mb-0">Status: {{ $sekolah->status_sekolah }}</p>
                                    <p class="text-muted mb-0">Jenjang: {{ $sekolah->jenjang_sekolah }}</p>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th width="30%">Alamat</th>
                                                <td>{{ $sekolah->alamat }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kelurahan/Desa</th>
                                                <td>{{ $sekolah->kelurahan_desa }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kecamatan</th>
                                                <td>{{ $sekolah->kecamatan }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kabupaten/Kota</th>
                                                <td>{{ $sekolah->kabupaten_kota }}</td>
                                            </tr>
                                            <tr>
                                                <th>Provinsi</th>
                                                <td>{{ $sekolah->provinsi }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Data sekolah belum tersedia</h5>
                        <p class="text-muted">Silahkan tambahkan data sekolah terlebih dahulu</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Data Sekolah
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Tambah Modal -->
    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Data Sekolah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('sekolah.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="npsn" class="form-label">NPSN</label>
                                    <input type="text" class="form-control" id="npsn" name="npsn" required>
                                </div>
                                <div class="mb-3">
                                    <label for="status_sekolah" class="form-label">Status</label>
                                    <select class="form-select" id="status_sekolah" name="status_sekolah" required>
                                        <option value="" selected disabled>Pilih Status Sekolah</option>
                                        <option value="Negeri">Negeri</option>
                                        <option value="Swasta">Swasta</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="jenjang_sekolah" class="form-label">Jenjang</label>
                                    <select class="form-select" id="jenjang_sekolah" name="jenjang_sekolah" required>
                                        <option value="" selected disabled>Pilih Jenjang</option>
                                        <option value="SD">SD</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA">SMA</option>
                                        <option value="SMK">SMK</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="kelurahan_desa" class="form-label">Kelurahan/Desa</label>
                                    <input type="text" class="form-control" id="kelurahan_desa" name="kelurahan_desa"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kecamatan" class="form-label">Kecamatan</label>
                                    <input type="text" class="form-control" id="kecamatan" name="kecamatan" required>
                                </div>
                                <div class="mb-3">
                                    <label for="kabupaten_kota" class="form-label">Kabupaten/Kota</label>
                                    <input type="text" class="form-control" id="kabupaten_kota" name="kabupaten_kota"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="provinsi" class="form-label">Provinsi</label>
                                    <input type="text" class="form-control" id="provinsi" name="provinsi" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
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

    <!-- Edit Modal -->
    @if ($exists)
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Data Sekolah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('sekolah.update', $sekolah->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_nama_sekolah" class="form-label">Nama Sekolah</label>
                                        <input type="text" class="form-control" id="edit_nama_sekolah"
                                            name="nama_sekolah" value="{{ $sekolah->nama_sekolah }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_npsn" class="form-label">NPSN</label>
                                        <input type="text" class="form-control" id="edit_npsn" name="npsn"
                                            value="{{ $sekolah->npsn }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_status_sekolah" class="form-label">Status</label>
                                        <select class="form-select" id="edit_status_sekolah" name="status_sekolah"
                                            required>
                                            <option value="Negeri"
                                                {{ $sekolah->status_sekolah == 'Negeri' ? 'selected' : '' }}>Negeri
                                            </option>
                                            <option value="Swasta"
                                                {{ $sekolah->status_sekolah == 'Swasta' ? 'selected' : '' }}>Swasta
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_jenjang_sekolah" class="form-label">Jenjang</label>
                                        <select class="form-select" id="edit_jenjang_sekolah" name="jenjang_sekolah"
                                            required>
                                            <option value="SD"
                                                {{ $sekolah->jenjang_sekolah == 'SD' ? 'selected' : '' }}>SD
                                            </option>
                                            <option value="SMP"
                                                {{ $sekolah->jenjang_sekolah == 'SMP' ? 'selected' : '' }}>SMP
                                            </option>
                                            <option value="SMA"
                                                {{ $sekolah->jenjang_sekolah == 'SMA' ? 'selected' : '' }}>SMA
                                            </option>
                                            <option value="SMK"
                                                {{ $sekolah->jenjang_sekolah == 'SMK' ? 'selected' : '' }}>SMK
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_kelurahan_desa" class="form-label">Kelurahan/Desa</label>
                                        <input type="text" class="form-control" id="edit_kelurahan_desa"
                                            name="kelurahan_desa" value="{{ $sekolah->kelurahan_desa }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_kecamatan" class="form-label">Kecamatan</label>
                                        <input type="text" class="form-control" id="edit_kecamatan" name="kecamatan"
                                            value="{{ $sekolah->kecamatan }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_kabupaten_kota" class="form-label">Kabupaten/Kota</label>
                                        <input type="text" class="form-control" id="edit_kabupaten_kota"
                                            name="kabupaten_kota" value="{{ $sekolah->kabupaten_kota }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_provinsi" class="form-label">Provinsi</label>
                                        <input type="text" class="form-control" id="edit_provinsi" name="provinsi"
                                            value="{{ $sekolah->provinsi }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required>{{ $sekolah->alamat }}</textarea>
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
    @endif
@endsection
