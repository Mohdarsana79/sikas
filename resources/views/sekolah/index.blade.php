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
                <button class="btn btn-primary btn-add-school" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Data Sekolah
                </button>
            </div>
            @else
            <div class="btn-group">
                <button class="btn btn-warning btn-edit-school" data-bs-toggle="modal" data-bs-target="#editModal">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Sekolah
                </button>
                <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item btn-refresh" href="#"><i class="bi bi-arrow-clockwise me-2"></i>Refresh
                            Data</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger btn-delete-school" href="#" data-id="{{ $sekolah->id }}"><i
                                class="bi bi-trash me-2"></i>Hapus Data</a></li>
                </ul>
            </div>
            @endif
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Data Sekolah -->
        @if ($exists)
        <div class="card school-card animate__animated animate__fadeIn">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4 mb-md-0">
                        <div class="school-avatar bg-light p-4 rounded">
                            <i class="bi bi-building fs-1 text-primary school-icon"></i>
                            <h4 class="mt-3 school-name">{{ $sekolah->nama_sekolah }}</h4>
                            <p class="text-muted mb-0 school-npsn">NPSN: {{ $sekolah->npsn }}</p>
                            <p class="text-muted mb-0 school-status">Status: {{ $sekolah->status_sekolah }}</p>
                            <p class="text-muted mb-0 school-level">Jenjang: {{ $sekolah->jenjang_sekolah }}</p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-borderless school-info-table">
                                <tbody>
                                    <tr>
                                        <th width="30%">Alamat</th>
                                        <td class="school-address">{{ $sekolah->alamat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kelurahan/Desa</th>
                                        <td class="school-village">{{ $sekolah->kelurahan_desa }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kecamatan</th>
                                        <td class="school-district">{{ $sekolah->kecamatan }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kabupaten/Kota</th>
                                        <td class="school-city">{{ $sekolah->kabupaten_kota }}</td>
                                    </tr>
                                    <tr>
                                        <th>Provinsi</th>
                                        <td class="school-province">{{ $sekolah->provinsi }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $sekolah->jenjang_sekolah }}</h4>
                                <small>Jenjang</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-mortarboard fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $sekolah->status_sekolah }}</h4>
                                <small>Status</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-building fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $sekolah->npsn }}</h4>
                                <small>NPSN</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-hash fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $sekolah->kabupaten_kota }}</h4>
                                <small>Wilayah</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-geo-alt fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card empty-state-card animate__animated animate__fadeIn">
            <div class="card-body text-center py-5">
                <i class="bi bi-building text-muted empty-icon" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">Data sekolah belum tersedia</h5>
                <p class="text-muted">Silahkan tambahkan data sekolah terlebih dahulu</p>
                <button class="btn btn-primary btn-add-school" data-bs-toggle="modal" data-bs-target="#tambahModal">
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tambahModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Data Sekolah
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="tambahForm" action="{{ route('sekolah.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_sekolah" class="form-label">Nama Sekolah <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah" required>
                                <div class="invalid-feedback">Nama sekolah harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="npsn" class="form-label">NPSN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="npsn" name="npsn" required maxlength="20">
                                <div class="invalid-feedback">NPSN harus diisi (maksimal 20 karakter)</div>
                            </div>
                            <div class="mb-3">
                                <label for="status_sekolah" class="form-label">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="status_sekolah" name="status_sekolah" required>
                                    <option value="" selected disabled>Pilih Status Sekolah</option>
                                    <option value="Negeri">Negeri</option>
                                    <option value="Swasta">Swasta</option>
                                </select>
                                <div class="invalid-feedback">Status sekolah harus dipilih</div>
                            </div>
                            <div class="mb-3">
                                <label for="jenjang_sekolah" class="form-label">Jenjang <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="jenjang_sekolah" name="jenjang_sekolah" required>
                                    <option value="" selected disabled>Pilih Jenjang</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA</option>
                                    <option value="SMK">SMK</option>
                                </select>
                                <div class="invalid-feedback">Jenjang sekolah harus dipilih</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kelurahan_desa" class="form-label">Kelurahan/Desa <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kelurahan_desa" name="kelurahan_desa"
                                    required>
                                <div class="invalid-feedback">Kelurahan/Desa harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="kecamatan" class="form-label">Kecamatan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kecamatan" name="kecamatan" required>
                                <div class="invalid-feedback">Kecamatan harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="kabupaten_kota" class="form-label">Kabupaten/Kota <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kabupaten_kota" name="kabupaten_kota"
                                    required>
                                <div class="invalid-feedback">Kabupaten/Kota harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="provinsi" class="form-label">Provinsi <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="provinsi" name="provinsi" required>
                                <div class="invalid-feedback">Provinsi harus diisi</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                        <div class="invalid-feedback">Alamat lengkap harus diisi</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-save">
                        <i class="bi bi-check-circle me-2"></i>Simpan
                    </button>
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
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Sekolah
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="{{ route('sekolah.update', $sekolah->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nama_sekolah" class="form-label">Nama Sekolah <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_sekolah" name="nama_sekolah"
                                    value="{{ $sekolah->nama_sekolah }}" required>
                                <div class="invalid-feedback">Nama sekolah harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_npsn" class="form-label">NPSN <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_npsn" name="npsn"
                                    value="{{ $sekolah->npsn }}" required maxlength="20">
                                <div class="invalid-feedback">NPSN harus diisi (maksimal 20 karakter)</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_status_sekolah" class="form-label">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status_sekolah" name="status_sekolah" required>
                                    <option value="Negeri" {{ $sekolah->status_sekolah == 'Negeri' ? 'selected' : ''
                                        }}>Negeri</option>
                                    <option value="Swasta" {{ $sekolah->status_sekolah == 'Swasta' ? 'selected' : ''
                                        }}>Swasta</option>
                                </select>
                                <div class="invalid-feedback">Status sekolah harus dipilih</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_jenjang_sekolah" class="form-label">Jenjang <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_jenjang_sekolah" name="jenjang_sekolah" required>
                                    <option value="SD" {{ $sekolah->jenjang_sekolah == 'SD' ? 'selected' : '' }}>SD
                                    </option>
                                    <option value="SMP" {{ $sekolah->jenjang_sekolah == 'SMP' ? 'selected' : '' }}>SMP
                                    </option>
                                    <option value="SMA" {{ $sekolah->jenjang_sekolah == 'SMA' ? 'selected' : '' }}>SMA
                                    </option>
                                    <option value="SMK" {{ $sekolah->jenjang_sekolah == 'SMK' ? 'selected' : '' }}>SMK
                                    </option>
                                </select>
                                <div class="invalid-feedback">Jenjang sekolah harus dipilih</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_kelurahan_desa" class="form-label">Kelurahan/Desa <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kelurahan_desa" name="kelurahan_desa"
                                    value="{{ $sekolah->kelurahan_desa }}" required>
                                <div class="invalid-feedback">Kelurahan/Desa harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_kecamatan" class="form-label">Kecamatan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kecamatan" name="kecamatan"
                                    value="{{ $sekolah->kecamatan }}" required>
                                <div class="invalid-feedback">Kecamatan harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_kabupaten_kota" class="form-label">Kabupaten/Kota <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kabupaten_kota" name="kabupaten_kota"
                                    value="{{ $sekolah->kabupaten_kota }}" required>
                                <div class="invalid-feedback">Kabupaten/Kota harus diisi</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_provinsi" class="form-label">Provinsi <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_provinsi" name="provinsi"
                                    value="{{ $sekolah->provinsi }}" required>
                                <div class="invalid-feedback">Provinsi harus diisi</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat Lengkap <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"
                            required>{{ $sekolah->alamat }}</textarea>
                        <div class="invalid-feedback">Alamat lengkap harus diisi</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-update">
                        <i class="bi bi-check-circle me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sekolah.js') }}"></script>
@endpush