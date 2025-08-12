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

            <!-- Modal Tambah Anggaran -->
            <div class="modal fade" id="tambahAnggaranModal" tabindex="-1" aria-labelledby="tambahAnggaranModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tambahAnggaranModalLabel">Tambah Anggaran Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('penganggaran.store') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="pagu_anggaran" class="form-label">Pagu Anggaran</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="pagu_anggaran" name="pagu_anggaran"
                                            required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tahun_anggaran" class="form-label">Tahun Anggaran</label>
                                    <input type="number" class="form-control" id="tahun_anggaran" name="tahun_anggaran"
                                        min="2000" max="{{ date('Y') + 5 }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="kepala_sekolah" class="form-label">Kepala Sekolah</label>
                                    <input type="text" class="form-control" id="kepala_sekolah" name="kepala_sekolah"
                                        pleaceholder="Nama Kepala Sekolah" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nip_kepala_sekolah" class="form-label">NIP Kepala Sekolah</label>
                                    <input type="text" class="form-control" id="nip_kepala_sekolah"
                                        name="nip_kepala_sekolah" pleaceholder="NIP Kepala Sekolah" required>
                                </div>
                                <div class="mb-3">
                                    <label for="bendahara" class="form-label">Bendahara</label>
                                    <input type="text" class="form-control" id="bendahara" name="bendahara"
                                        pleaceholder="Nama Bendahara" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nip_bendahara" class="form-label">NIP Bendahara</label>
                                    <input type="text" class="form-control" id="nip_bendahara" name="nip_bendahara"
                                        pleaceholder="NIP Bendahara" required>
                                </div>
                                <div class="mb-3">
                                    <label for="komite" class="form-label">Komite</label>
                                    <input type="text" class="form-control" id="komite" name="komite"
                                        pleaceholder="Nama Komite" required>
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

            <!-- Modal Edit Anggaran -->
            @foreach ($anggarans as $anggaran)
                <!-- Modal Edit untuk setiap anggaran -->
                <div class="modal fade" id="editAnggaranModal{{ $anggaran->id }}" tabindex="-1"
                    aria-labelledby="editAnggaranModalLabel{{ $anggaran->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editAnggaranModalLabel{{ $anggaran->id }}">Edit Anggaran
                                    {{ $anggaran->tahun_anggaran }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('penganggaran.update', $anggaran->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="edit_pagu_anggaran_{{ $anggaran->id }}" class="form-label">Pagu
                                            Anggaran</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control"
                                                id="edit_pagu_anggaran_{{ $anggaran->id }}" name="pagu_anggaran"
                                                value="{{ number_format($anggaran->pagu_anggaran, 0, ',', '.') }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_tahun_anggaran_{{ $anggaran->id }}" class="form-label">Tahun
                                            Anggaran</label>
                                        <input type="number" class="form-control"
                                            id="edit_tahun_anggaran_{{ $anggaran->id }}" name="tahun_anggaran"
                                            value="{{ $anggaran->tahun_anggaran }}" min="2000"
                                            max="{{ date('Y') + 5 }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_kepala_sekolah_{{ $anggaran->id }}" class="form-label">Kepala
                                            Sekolah</label>
                                        <input type="text" class="form-control"
                                            id="edit_kepala_sekolah_{{ $anggaran->id }}" name="kepala_sekolah"
                                            value="{{ $anggaran->kepala_sekolah }}" pleaceholder="Nama Kepala Sekolah"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_nip_kepala_sekolah_{{ $anggaran->id }}" class="form-label">NIP
                                            Kepala Sekolah</label>
                                        <input type="text" class="form-control"
                                            id="edit_nip_kepala_sekolah_{{ $anggaran->id }}" name="nip_kepala_sekolah"
                                            value="{{ $anggaran->nip_kepala_sekolah }}" pleaceholder="NIP Kepala Sekolah"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_bendahara_{{ $anggaran->id }}"
                                            class="form-label">Bendahara</label>
                                        <input type="text" class="form-control"
                                            id="edit_bendahara_{{ $anggaran->id }}" name="bendahara"
                                            value="{{ $anggaran->bendahara }}" pleaceholder="Nama Bendahara" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_nip_bendahara_{{ $anggaran->id }}" class="form-label">NIP
                                            Bendahara</label>
                                        <input type="text" class="form-control"
                                            id="edit_nip_bendahara_{{ $anggaran->id }}" name="nip_bendahara"
                                            value="{{ $anggaran->nip_bendahara }}" pleaceholder="NIP Bendahara" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_komite_{{ $anggaran->id }}" class="form-label">Komite</label>
                                        <input type="text" class="form-control" id="edit_komite_{{ $anggaran->id }}"
                                            name="komite" value="{{ $anggaran->komite }}" pleaceholder="Nama Komite"
                                            required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach ($anggarans as $anggaran)
                <!-- Modal Delete untuk setiap anggaran -->
                <div class="modal fade" id="deleteAnggaranModal{{ $anggaran->id }}" tabindex="-1"
                    aria-labelledby="deleteAnggaranModalLabel{{ $anggaran->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="deleteAnggaranModalLabel{{ $anggaran->id }}">Hapus Anggaran
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('penganggaran.destroy', $anggaran->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus anggaran tahun
                                        <strong>{{ $anggaran->tahun_anggaran }}</strong> dengan pagu <strong>Rp
                                            {{ number_format($anggaran->pagu_anggaran, 0, ',', '.') }}</strong>?
                                    </p>
                                    <p class="text-danger">Data yang dihapus tidak dapat dikembalikan!</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

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
                            <button class="nav-link" id="bosp-daerah-tab" data-bs-toggle="tab"
                                data-bs-target="#bosp-daerah" type="button" role="tab">
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
                            <button class="nav-link" id="silpa-bosp-tab" data-bs-toggle="tab"
                                data-bs-target="#silpa-bosp" type="button" role="tab">
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
                                    <div
                                        class="rkas-item d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-text me-3 text-primary fs-4"></i>
                                            <div>
                                                <h6 class="mb-1 fw-semibold">RKAS BOSP Reguler
                                                    {{ $anggaran->tahun_anggaran }}</h6>
                                                <p class="mb-1">Pagu: Rp
                                                    {{ number_format($anggaran->pagu_anggaran, 0, ',', '.') }}</p>
                                                <button class="btn btn-success"
                                                    style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editAnggaranModal{{ $anggaran->id }}">
                                                    <i class="bi bi-pencil me-2"></i>Edit Anggaran
                                                </button>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('penganggaran.rkas.index', ['tahun' => $anggaran->tahun_anggaran]) }}"
                                                class="btn btn-sm btn-outline-primary" title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-secondary" title="Download">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-dark" title="Print">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteAnggaranModal{{ $anggaran->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5">
                                        <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">Belum ada data BOSP Daerah</p>
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

            // Format edit input angka
            // Format input angka untuk modal edit (semua modal edit)
            $('[id^="edit_pagu_anggaran_"]').on('keyup', function() {
                let value = $(this).val().replace(/[^\d]/g, '');
                if (value.length > 0) {
                    value = parseInt(value).toLocaleString('id-ID');
                    $(this).val(value);
                }
            });
        });
    </script>
@endpush
