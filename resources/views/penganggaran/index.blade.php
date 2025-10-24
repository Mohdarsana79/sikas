@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<div class="content-area" id="contentArea">
    <!-- Dashboard Content -->
    <div class="fade-in">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gradient">Penganggaran</h1>
                <p class="mb-0 text-muted">Kelola anggaran dan laporan keuangan Anda dengan mudah.</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahAnggaranModal">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Baru
                </button>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- Modal Tambah --}}
        <div class="modal fade" id="tambahAnggaranModal" tabindex="-1" aria-labelledby="tambahAnggaranModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tambahAnggaranModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Anggaran Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('penganggaran.store') }}" method="POST" id="formTambahAnggaran">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <!-- Informasi Anggaran -->
                                <div class="col-md-6">
                                    <div class="border-bottom pb-2 mb-3">
                                        <h6 class="text-primary"><i class="bi bi-cash-coin me-2"></i>Informasi Anggaran
                                        </h6>
                                    </div>

                                    <div class="mb-3">
                                        <label for="pagu_anggaran" class="form-label">Pagu Anggaran <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            <input type="text" class="form-control" id="pagu_anggaran"
                                                name="pagu_anggaran" required placeholder="Masukkan pagu anggaran">
                                        </div>
                                        <div class="form-text">Contoh: 1.000.000</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tahun_anggaran" class="form-label">Tahun Anggaran <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="tahun_anggaran"
                                            name="tahun_anggaran" min="2000" max="{{ date('Y') + 5 }}" required
                                            placeholder="{{ date('Y') }}">
                                    </div>
                                </div>

                                <!-- Informasi Komite -->
                                <div class="col-md-6">
                                    <div class="border-bottom pb-2 mb-3">
                                        <h6 class="text-primary"><i class="bi bi-people me-2"></i>Informasi Komite</h6>
                                    </div>

                                    <div class="mb-3">
                                        <label for="komite" class="form-label">Nama Komite <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="komite" name="komite" required
                                            placeholder="Nama Komite Sekolah">
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Kepala Sekolah -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="border-bottom pb-2 mb-3">
                                        <h6 class="text-primary"><i class="bi bi-person-badge me-2"></i>Informasi Kepala
                                            Sekolah
                                        </h6>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="kepala_sekolah" class="form-label">Nama Kepala Sekolah <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="kepala_sekolah"
                                            name="kepala_sekolah" required placeholder="Nama Lengkap Kepala Sekolah">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nip_kepala_sekolah" class="form-label">NIP <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nip_kepala_sekolah"
                                            name="nip_kepala_sekolah" required placeholder="Nomor Induk Pegawai">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sk_kepala_sekolah" class="form-label">SK Pelantikan <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="sk_kepala_sekolah"
                                            name="sk_kepala_sekolah" required placeholder="Nomor SK Pelantikan">
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Bendahara -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="border-bottom pb-2 mb-3">
                                        <h6 class="text-primary"><i class="bi bi-person-check me-2"></i>Informasi
                                            Bendahara</h6>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="bendahara" class="form-label">Nama Bendahara <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="bendahara" name="bendahara" required
                                            placeholder="Nama Lengkap Bendahara">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nip_bendahara" class="form-label">NIP <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nip_bendahara" name="nip_bendahara"
                                            required placeholder="Nomor Induk Pegawai">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sk_bendahara" class="form-label">SK Bendahara <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="sk_bendahara" name="sk_bendahara"
                                            required placeholder="Nomor SK Bendahara">
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Tambahan -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_sk_bendahara" class="form-label">Tanggal SK
                                            Bendahara</label>
                                        <input type="date" class="form-control" id="tanggal_sk_bendahara"
                                            name="tanggal_sk_bendahara">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_sk_kepala_sekolah" class="form-label">Tanggal SK Pelantikan
                                            Kepsek</label>
                                        <input type="date" class="form-control" id="tanggal_sk_kepala_sekolah"
                                            name="tanggal_sk_kepala_sekolah">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="bi bi-info-circle me-2"></i>
                                    Field dengan tanda <span class="text-danger">*</span> wajib diisi.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSimpan">
                                <i class="bi bi-check-circle me-2"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card Penganggaran -->
        <div class="card">
            <div class="card-body p-0">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs border-bottom-0" id="rkasTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bosp-reguler-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-reguler" type="button" role="tab">
                            BOSP Reguler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-daerah-tab" data-bs-toggle="tab" data-bs-target="#bosp-daerah"
                            type="button" role="tab">
                            BOSP Daerah
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-kinerja-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-kinerja" type="button" role="tab">
                            BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="silpa-bosp-tab" data-bs-toggle="tab" data-bs-target="#silpa-bosp"
                            type="button" role="tab">
                            SiLPA BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lainnya-tab" data-bs-toggle="tab" data-bs-target="#lainnya"
                            type="button" role="tab">
                            Lainnya
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="rkasTabsContent">
                    <!-- BOSP Reguler Tab -->
                    <div class="tab-pane fade show active" id="bosp-reguler" role="tabpanel">
                        <div class="p-4">
                            @forelse ($anggarans as $anggaran)
                            <!-- CARD RKAS ASLI -->
                            <div
                                class="rkas-item d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text me-3 text-primary fs-4"></i>
                                    <div>
                                        <h6 class="mb-1 fw-semibold">RKAS BOSP Reguler {{ $anggaran->tahun_anggaran }}
                                        </h6>
                                        <p class="mb-1">Pagu: Rp {{ number_format($anggaran->pagu_anggaran, 0, ',', '.')
                                            }}</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('rkas.index', ['tahun' => $anggaran->tahun_anggaran]) }}"
                                        class="btn btn-sm btn-outline-primary" title="Lihat RKAS Awal">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($anggaran->has_rkas)
                                    <a class="btn btn-sm btn-outline-dark"
                                        href="{{ route('rkas.rekapan', ['tahun' => $anggaran->tahun_anggaran]) }}"
                                        title="Cetak RKAS Awal">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @else
                                    <button class="btn btn-sm btn-outline-dark" disabled
                                        title="Tidak ada data untuk dicetak">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-sm btn-outline-danger btn-hapus-anggaran" title="Hapus"
                                        data-id="{{ $anggaran->id }}" data-tahun="{{ $anggaran->tahun_anggaran }}"
                                        data-pagu="{{ number_format($anggaran->pagu_anggaran, 0, ',', '.') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- CARD RKAS PERUBAHAN (Tersembunyi secara default) -->
                            <div class="rkas-item d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded border border-warning {{ $anggaran->has_perubahan ? '' : 'd-none' }}"
                                id="rkas-perubahan-card-{{ $anggaran->tahun_anggaran }}">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-diff me-3 text-warning fs-4"></i>
                                    <div>
                                        <h6 class="mb-1 fw-semibold">RKAS BOSP Reguler Perubahan {{
                                            $anggaran->tahun_anggaran }}</h6>
                                        <p class="mb-1">Pagu: Rp {{ number_format($anggaran->pagu_anggaran, 0, ',', '.')
                                            }}</p>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-info-circle-fill me-1"></i>Status: RKAS Perubahan
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <!-- Tombol mata mengarah ke route rkas-perubahan -->
                                    <a href="{{ route('rkas-perubahan.index', ['tahun' => $anggaran->tahun_anggaran]) }}"
                                        class="btn btn-sm btn-outline-warning" title="Lihat RKAS Perubahan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-dark"
                                        href="{{ route('rkas-perubahan.rekapan-perubahan', ['tahun' => $anggaran->tahun_anggaran]) }}"
                                        title="Cetak RKAS Perubahan">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <!-- Tombol Hapus RKAS Perubahan -->
                                    @if($anggaran->has_perubahan)
                                    <button class="btn btn-sm btn-outline-warning btn-hapus-rkas-perubahan"
                                        title="Hapus RKAS Perubahan" data-id="{{ $anggaran->id }}"
                                        data-tahun="{{ $anggaran->tahun_anggaran }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data penganggaran.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- BOSP Daerah Tab -->
                    <div class="tab-pane fade" id="bosp-daerah" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data BOSP Daerah</p>
                            </div>
                        </div>
                    </div>

                    <!-- BOSP Kinerja Tab -->
                    <div class="tab-pane fade" id="bosp-kinerja" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data BOSP Kinerja</p>
                            </div>
                        </div>
                    </div>

                    <!-- SiLPA BOSP Kinerja Tab -->
                    <div class="tab-pane fade" id="silpa-bosp" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data SiLPA BOSP Kinerja</p>
                            </div>
                        </div>
                    </div>

                    <!-- Lainnya Tab -->
                    <div class="tab-pane fade" id="lainnya" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .modal-header {
        border-bottom: 2px solid #dee2e6;
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }

    .form-control:focus+.input-group-text {
        border-color: #86b7fe;
        background-color: #f8f9fa;
    }

    .border-bottom h6 {
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
    // Format input angka tambah anggaran
    $('#pagu_anggaran').on('keyup', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value.length > 0) {
            value = parseInt(value).toLocaleString('id-ID');
            $(this).val(value);
        }
    });

    // Reset form ketika modal ditutup
    $('#tambahAnggaranModal').on('hidden.bs.modal', function () {
        $('#formTambahAnggaran')[0].reset();
    });

    // Validasi form sebelum submit
    $('#formTambahAnggaran').on('submit', function(e) {
        let isValid = true;
        $(this).find('input[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Harap lengkapi semua field yang wajib diisi!',
                confirmButtonColor: '#3085d6',
            });
        }
    });

    // Hapus validasi ketika user mulai mengetik
    $('input[required]').on('input', function() {
        if ($(this).val().trim() !== '') {
            $(this).removeClass('is-invalid');
        }
    });

    // SweetAlert untuk Hapus Anggaran
    $(document).on('click', '.btn-hapus-anggaran', function() {
        const id = $(this).data('id');
        const tahun = $(this).data('tahun');
        const pagu = $(this).data('pagu');

        Swal.fire({
            title: 'Hapus Anggaran?',
            html: `Apakah Anda yakin ingin menghapus anggaran tahun <strong>${tahun}</strong> dengan pagu <strong>Rp ${pagu}</strong>?<br><br><span class="text-danger">Data yang dihapus tidak dapat dikembalikan!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Sedang menghapus data anggaran',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form via AJAX
                $.ajax({
                    url: `{{ url('penganggaran/penganggaran') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: `Data anggaran tahun ${tahun} beserta semua data RKAS terkait berhasil dihapus`,
                            confirmButtonColor: '#10b981',
                            timer: 3000,
                            showConfirmButton: true
                        }).then(() => {
                            // Refresh halaman untuk update data
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Gagal menghapus data anggaran';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage,
                            confirmButtonColor: '#ef4444',
                        });
                    }
                });
            }
        });
    });

    // SweetAlert untuk Hapus RKAS Perubahan
    $(document).on('click', '.btn-hapus-rkas-perubahan', function() {
        const id = $(this).data('id');
        const tahun = $(this).data('tahun');

        Swal.fire({
            title: 'Hapus RKAS Perubahan?',
            html: `Apakah Anda yakin ingin menghapus <strong>RKAS Perubahan</strong> tahun <strong>${tahun}</strong>?<br><br><span class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Hanya data RKAS Perubahan yang akan dihapus. Data RKAS awal tetap tersimpan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Sedang menghapus data RKAS Perubahan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form via AJAX
                $.ajax({
                    url: `{{ url('penganggaran/rkas-perubahan') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: `Data RKAS Perubahan tahun ${tahun} berhasil dihapus`,
                            confirmButtonColor: '#10b981',
                            timer: 3000,
                            showConfirmButton: true
                        }).then(() => {
                            // Refresh halaman untuk update data
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Gagal menghapus data RKAS Perubahan';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage,
                            confirmButtonColor: '#ef4444',
                        });
                    }
                });
            }
        });
    });
});

// Handle SweetAlert untuk pesan sukses dari session
@if (session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#10b981',
        timer: 3000
    });
@endif

@if (session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#ef4444',
    });
@endif
</script>
@endpush