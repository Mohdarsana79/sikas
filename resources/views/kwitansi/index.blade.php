@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<style>
    /* Menggunakan font Inter untuk tampilan yang bersih dan modern */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f7f9fc;
        /* Warna latar belakang yang lembut */
    }

    /* Kelas untuk Kartu yang lebih modern */
    .modern-card {
        border: none;
        border-radius: 1.5rem;
        /* Sudut lebih membulat (rounded-2xl) */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        position: relative;
        padding-top: 1.5rem;
        /* Tambahkan padding agar border-top tidak menutupi isi */
    }

    /* Garis Aksen Warna di Atas Kartu */
    .modern-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        /* Ketebalan garis */
        border-top-left-radius: 1.5rem;
        border-top-right-radius: 1.5rem;
    }

    /* Efek Hover */
    .modern-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 1rem 1.5rem rgba(0, 0, 0, 0.1), 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }

    /* Warna Border Aksen */
    .card-blue::before {
        background-color: #3b82f6;
        /* blue-500 */
    }

    .card-green::before {
        background-color: #10b981;
        /* green-500 */
    }

    .card-yellow::before {
        background-color: #f59e0b;
        /* yellow-500 */
    }

    .card-red::before {
        background-color: #ef4444;
        /* red-500 */
    }

    /* Badge untuk Kode Rekening */
    .badge-code {
        background-color: #dbeafe;
        /* blue-100 */
        color: #1e40af;
        /* blue-800 */
        font-weight: 600;
        padding: 0.5em 0.75em;
        border-radius: 50rem;
    }

    /* Style untuk pagination */
    .pagination-custom .page-link {
        border-radius: 0.5rem;
        margin: 0 2px;
        border: 1px solid #dee2e6;
    }

    .pagination-custom .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
</style>
<div class="container-xxl">

    <!-- HEADER DAN Aksi BUTTONS -->
    <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div class="mb-4 mb-md-0">
            <h1 class="display-5 fw-bold text-dark mb-1">Manajemen Kwitansi</h1>
            <p class="text-secondary lead fs-6">Kelola dan pantau semua dokumen kwitansi dalam satu tempat</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Generate Otomatis -->
            <button
                class="btn btn-success btn-sm fw-semibold rounded-pill shadow-sm py-2 px-4 d-flex align-items-center"
                id="generate-all-btn">
                <i class="bi bi-plus-circle me-2"></i>
                Generate
            </button>
            <!-- Hapus Semua -->
            <button class="btn btn-danger btn-sm fw-semibold rounded-pill shadow-sm py-2 px-4 d-flex align-items-center"
                id="delete-all-btn" {{ $kwitansis->count() === 0 ? 'disabled' : '' }}>
                <i class="bi bi-trash me-2"></i>
                Hapus Semua
            </button>
            <!-- Buat Manual -->
            {{-- <a href="{{ route('kwitansi.create') }}"
                class="btn btn-primary btn-sm fw-semibold rounded-pill shadow-sm py-2 px-4 d-flex align-items-center">
                <i class="bi bi-pencil-square me-2"></i>
                Buat Manual
            </a> --}}
        </div>
    </header>

    <!-- KARTU RINGKASAN/WIDGETS -->
    <div class="row g-4 mb-5">

        <!-- Kartu 1: Total Kwitansi (Biru) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-blue p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="fs-6 text-secondary fw-semibold">Total Kwitansi</h3>
                    <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                </div>
                <p class="h1 fw-bolder text-dark mt-2" id="total-kwitansi">{{ $kwitansis->total() }}</p>
                <p class="small text-muted mt-1">Update real-time</p>
            </div>
        </div>

        <!-- Kartu 2: Siap Generate (Hijau) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-green p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="fs-6 text-secondary fw-semibold">Siap Generate</h3>
                    <i class="bi bi-check-circle text-success fs-4"></i>
                </div>
                <p class="h1 fw-bolder text-dark mt-2" id="ready-generate">0</p>
                <p class="small text-muted mt-1">Menunggu proses</p>
            </div>
        </div>

        <!-- Kartu 3: Dalam Antrian (Kuning) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-yellow p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="fs-6 text-secondary fw-semibold">Dalam Antrian</h3>
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <p class="h1 fw-bolder text-dark mt-2" id="pending-count">0</p>
                <p class="small text-muted mt-1">Sedang diproses</p>
            </div>
        </div>

        <!-- Kartu 4: Gagal Generate (Merah) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-red p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="fs-6 text-secondary fw-semibold">Gagal Generate</h3>
                    <i class="bi bi-x-octagon text-danger fs-4"></i>
                </div>
                <p class="h1 fw-bolder text-dark mt-2" id="failed-count">0</p>
                <p class="small text-muted mt-1">Perlu perbaikan</p>
            </div>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card modern-card p-4 p-md-5">
        <!-- HEADER TABEL BARU: JUDUL DAN SEARCH BERDAMPINGAN -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

            <!-- Judul -->
            <h2 class="fs-4 fw-bold text-dark mb-0">Daftar Kwitansi</h2>

            <!-- Search Input yang lebih pendek (Lebar 100% di HP, 25% di MD ke atas) -->
            <div class="input-group w-50 w-md-25">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i
                        class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0 rounded-end-pill"
                    placeholder="Cari berdasarkan Uraian atau Kode Rekening..." id="searchInput">
                <!-- Tombol "Cari" dihilangkan untuk tampilan yang lebih minimalis dan modern -->
            </div>
        </div>
        <!-- AKHIR HEADER TABEL BARU -->

        @if($kwitansis->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-secondary fw-medium">No</th>
                        <th scope="col" class="text-secondary fw-medium">Kode Rekening</th>
                        <th scope="col" class="text-secondary fw-medium">Uraian</th>
                        <th scope="col" class="text-secondary fw-medium">Tanggal</th>
                        <th scope="col" class="text-secondary fw-medium">Jumlah</th>
                        <th scope="col" class="text-center text-secondary fw-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kwitansis as $index => $kwitansi)
                    <tr id="kwitansi-row-{{ $kwitansi->id }}">
                        <!-- PERUBAHAN: Hitung nomor urut berdasarkan pagination -->
                        <td class="fw-medium">{{ ($kwitansis->currentPage() - 1) * $kwitansis->perPage() + $index + 1 }}
                        </td>
                        <td>
                            <span class="badge badge-code">{{ $kwitansi->rekeningBelanja->kode_rekening ?? '-' }}</span>
                        </td>
                        <td title="{{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}">
                            {{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}
                        </td>
                        <td class="text-muted">{{
                            \Carbon\Carbon::parse($kwitansi->bukuKasUmum->tanggal_transaksi)->format('d/m/Y') }}</td>
                        <td class="fw-semibold text-success">Rp {{
                            number_format($kwitansi->bukuKasUmum->total_transaksi_kotor, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <!-- Lihat -->
                                <a href="{{ route('kwitansi.preview', $kwitansi->id) }}" title="Lihat Preview"
                                    class="btn btn-sm btn-outline-primary border-0 rounded-circle p-1"
                                    data-bs-toggle="tooltip" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!-- Cetak -->
                                <a href="{{ route('kwitansi.pdf', $kwitansi->id) }}" title="Download PDF"
                                    target="_blank" data-bs-toggle="tooltip"
                                    class="btn btn-sm btn-outline-success border-0 rounded-circle p-1">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <!-- Hapus -->
                                <button title="Hapus Data"
                                    class="btn btn-sm btn-outline-danger delete-kwitansi border-0 rounded-circle p-1"
                                    data-id="{{ $kwitansi->id }}"
                                    data-uraian="{{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}"
                                    title="Hapus Kwitansi" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- PERUBAHAN: Tambahkan pagination links -->
        @if($kwitansis->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Menampilkan {{ ($kwitansis->currentPage() - 1) * $kwitansis->perPage() + 1 }}
                sampai {{ min($kwitansis->currentPage() * $kwitansis->perPage(), $kwitansis->total()) }}
                dari {{ $kwitansis->total() }} data
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-custom mb-0">
                    {{-- Previous Page Link --}}
                    @if ($kwitansis->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">&laquo;</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $kwitansis->previousPageUrl() }}" rel="prev">&laquo;</a>
                    </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($kwitansis->getUrlRange(1, $kwitansis->lastPage()) as $page => $url)
                    @if ($page == $kwitansis->currentPage())
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($kwitansis->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $kwitansis->nextPageUrl() }}" rel="next">&raquo;</a>
                    </li>
                    @else
                    <li class="page-item disabled">
                        <span class="page-link">&raquo;</span>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif

        @else
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-x fs-1 text-muted"></i>
            <h5 class="text-muted mt-3">Belum ada data kwitansi</h5>
            <p class="text-muted">Mulai dengan membuat kwitansi baru atau generate otomatis.</p>
        </div>
        @endif

        @if($kwitansis->count() > 0)
        <div class="card-footer bg-transparent border-top-0">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <small class="text-muted">
                            Total: <strong id="footer-total" class="text-primary">{{ $kwitansis->total() }}</strong>
                            kwitansi
                        </small>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <i class="fas fa-clock text-muted me-2"></i>
                        <small class="text-muted">
                            Terakhir diperbarui: <span id="last-updated" class="fw-semibold">{{ now()->format('d/m/Y
                                H:i') }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded - initializing kwitansi page');
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Check available data on page load
        checkAvailableData();

        // Generate All Button
        const generateBtn = document.getElementById('generate-all-btn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Generate button clicked');
                generateAllKwitansi();
            });
        }

        // Delete All Button
        const deleteAllBtn = document.getElementById('delete-all-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                deleteAllKwitansi();
            });
        }

        // Function untuk check data yang tersedia untuk generate
        function checkAvailableData() {
            console.log('Checking available data...');
            
            fetch('{{ route("kwitansi.check-available") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Available data response:', data);
                if (data.success) {
                    updateElementText('ready-generate', data.data.available_count || 0);
                } else {
                    console.error('Error checking available data:', data.message);
                    updateElementText('ready-generate', '0');
                }
            })
            .catch(error => {
                console.error('AJAX error checking available data:', error);
                updateElementText('ready-generate', '0');
            });
        }

        // Function untuk generate semua kwitansi dengan SweetAlert Progress yang sederhana
        function generateAllKwitansi() {
            console.log('Starting generate process...');
            
            // Tampilkan konfirmasi sederhana
            Swal.fire({
                title: 'Generate Kwitansi Otomatis?',
                html: `
                    <div class="text-center">
                        <p>Proses ini akan membuat kwitansi untuk semua transaksi yang belum memiliki kwitansi.</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Pastikan tidak menutup halaman selama proses berlangsung.
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-play mr-1"></i> Mulai Generate',
                cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    startGenerationWithProgress();
                }
            });
        }

        // Function untuk memulai proses generate
        function startGenerationWithProgress() {
            let totalSuccess = 0;
            let totalFailed = 0;
            let totalRecords = 0;
            let timeoutId = null;

            // Tampilkan loading awal yang sederhana
            Swal.fire({
                title: 'Memulai Generate...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                        <p>Sedang mempersiapkan data...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            // Ambil total records yang akan diproses
            fetch('{{ route("kwitansi.check-available") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    totalRecords = data.data.available_count || 0;
                    
                    if (totalRecords === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak ada data',
                            text: 'Tidak ada data yang perlu digenerate.'
                        });
                        return;
                    }

                    // Set timeout untuk mencegah stuck (5 menit)
                    timeoutId = setTimeout(() => {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Proses Dihentikan',
                            text: 'Proses generate dihentikan karena terlalu lama.',
                            confirmButtonText: 'Mengerti'
                        });
                    }, 5 * 60 * 1000);

                    // Mulai proses batch
                    processBatch(0, totalSuccess, totalFailed, totalRecords, timeoutId);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal mengambil data'
                    });
                }
            })
            .catch(error => {
                console.error('Error checking available data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Koneksi!',
                    text: 'Terjadi kesalahan saat memeriksa data.'
                });
            });
        }

        // Function untuk proses batch yang diperbaiki
        function processBatch(offset, totalSuccess, totalFailed, totalRecords, timeoutId, attempt = 1) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('offset', offset);

            console.log(`🔄 Batch Attempt ${attempt}: offset=${offset}, totalSuccess=${totalSuccess}, totalFailed=${totalFailed}, totalRecords=${totalRecords}`);

            fetch('{{ route("kwitansi.generate-batch") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(`📊 Server response:`, data.data);
                
                if (data.success) {
                    const batchData = data.data;
                    
                    // Akumulasi yang benar
                    const newSuccess = totalSuccess + batchData.success;
                    const newFailed = totalFailed + batchData.failed;
                    const newOffset = batchData.offset;

                    console.log(`✅ Batch ${attempt}: Processed ${batchData.processed}, Success: ${batchData.success}, Failed: ${batchData.failed}, Progress: ${batchData.progress}%, HasMore: ${batchData.has_more}`);

                    // Update UI dengan progress yang sederhana
                    updateSimpleProgress(batchData.progress, newSuccess, newFailed, batchData.remaining, batchData.total);
                    updateElementText('pending-count', batchData.remaining);
                    updateElementText('failed-count', newFailed);

                    // Logic continue
                    if (batchData.has_more && batchData.progress < 100) {
                        console.log(`🔄 Continuing to next batch from offset ${newOffset}...`);
                        setTimeout(() => {
                            processBatch(newOffset, newSuccess, newFailed, batchData.total, timeoutId, attempt + 1);
                        }, 500);
                    } else {
                        // Final completion
                        clearTimeout(timeoutId);
                        console.log('🎉 All batches completed!', { 
                            totalSuccess: newSuccess, 
                            totalFailed: newFailed, 
                            totalRecords: batchData.total
                        });
                        
                        // Tampilkan completion message yang sederhana
                        setTimeout(() => {
                            showSimpleCompletionMessage(newSuccess, newFailed, batchData.total);
                        }, 1000);
                    }
                } else {
                    clearTimeout(timeoutId);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Terjadi kesalahan saat proses generate'
                    });
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Network error in processBatch:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Koneksi!',
                    text: 'Terjadi kesalahan koneksi saat proses generate'
                });
            });
        }

        // Function untuk update progress yang sederhana
        function updateSimpleProgress(percent, success, failed, remaining, total) {
            const safePercent = Math.min(100, Math.max(0, percent));

            Swal.update({
                title: `Generate Kwitansi - ${safePercent}%`,
                html: `
                    <div class="text-center">
                        <!-- Progress Bar Sederhana -->
                        <div class="mb-3">
                            <div class="progress" style="height: 20px; border-radius: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                    style="width: ${safePercent}%; font-size: 12px; font-weight: bold;">
                                    ${safePercent}%
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Singkat -->
                        <div class="row text-center small">
                            <div class="col-4">
                                <div class="text-success font-weight-bold">${success}</div>
                                <small>Berhasil</small>
                            </div>
                            <div class="col-4">
                                <div class="text-danger font-weight-bold">${failed}</div>
                                <small>Gagal</small>
                            </div>
                            <div class="col-4">
                                <div class="text-info font-weight-bold">${remaining}</div>
                                <small>Sisa</small>
                            </div>
                        </div>

                        <!-- Loading Animation -->
                        ${safePercent < 100 ? `
                        <div class="mt-3">
                            <div class="spinner-border text-primary" style="width: 1.5rem; height: 1.5rem;"></div>
                            <small class="text-muted ml-2">Memproses data...</small>
                        </div>
                        ` : ''}
                    </div>
                `,
                showConfirmButton: false,
                showCancelButton: false
            });
        }

        // Function untuk menampilkan pesan penyelesaian yang sederhana
        function showSimpleCompletionMessage(successCount, failedCount, totalCount) {
            let resultHtml = `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <h5 class="text-success">Proses Generate Selesai!</h5>
                    </div>
                    
                    <div class="alert alert-success">
                        <p><strong>Proses generate telah selesai. Silahkan cek data Anda.</strong></p>
                        <p class="mb-0">
                            Berhasil: <strong>${successCount}</strong> | 
                            Gagal: <strong>${failedCount}</strong> | 
                            Total: <strong>${totalCount}</strong>
                        </p>
                    </div>
                </div>
            `;

            Swal.fire({
                title: 'Proses Selesai',
                html: resultHtml,
                icon: 'success',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke',
                allowOutsideClick: false
            }).then((result) => {
                // Refresh halaman untuk update data
                location.reload();
            });
        }

        // Function untuk hapus semua kwitansi dengan loading sederhana
        function deleteAllKwitansi() {
            console.log('Delete all button clicked');
            
            Swal.fire({
                title: 'Hapus Semua Kwitansi?',
                html: `
                    <div class="alert alert-warning text-left">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Konfirmasi Penghapusan</strong>
                        <hr>
                        <p>Apakah Anda yakin ingin menghapus <strong>SEMUA DATA KWITANSI</strong>?</p>
                        <p class="mb-0">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data kwitansi secara permanen.</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Semua!',
                cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processDeleteAll();
                }
            });
        }

        // Function proses hapus semua dengan loading sederhana (seperti deleteSingle)
        function processDeleteAll() {
            // Show loading state sederhana
            Swal.fire({
                title: 'Menghapus...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-danger mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p>Sedang menghapus semua data kwitansi...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch('{{ route("kwitansi.delete-all") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    confirm_text: 'HAPUS-SEMUA-KWITANSI'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="text-success">Data Berhasil Dihapus</h5>
                                <p>${data.message}</p>
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check mr-2"></i>
                                    Semua data kwitansi telah berhasil dihapus dari sistem.
                                </div>
                            </div>
                        `,
                        confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Refresh Halaman',
                        allowOutsideClick: false
                    }).then((result) => {
                        // Refresh halaman setelah konfirmasi
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message,
                        confirmButtonText: '<i class="fas fa-times mr-1"></i> Tutup'
                    });
                }
            })
            .catch(error => {
                console.error('Delete all error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus semua data kwitansi',
                    confirmButtonText: '<i class="fas fa-times mr-1"></i> Tutup'
                });
            });
        }

        // Function untuk update element text
        function updateElementText(elementId, text) {
            const element = document.getElementById(elementId);
            if (element) element.textContent = text;
        }

        // Function untuk update waktu terakhir update
        function updateLastUpdated() {
            const now = new Date();
            const formattedTime = now.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            updateElementText('last-updated', formattedTime);
        }

        // Update waktu setiap menit
        setInterval(updateLastUpdated, 60000);

        // Initialize last updated time
        updateLastUpdated();

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch(this.value);
                }, 500);
            });
        }

        function performSearch(searchTerm) {
            fetch('{{ route("kwitansi.search") }}?search=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSearchResults(data.data, data.pagination);
                        updateElementText('total-kwitansi', data.total);
                        updateElementText('footer-total', data.total);
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }

        function updateSearchResults(data, pagination) {
            const tbody = document.querySelector('tbody');
            const paginationContainer = document.querySelector('.pagination');
            const paginationInfo = document.querySelector('.pagination-info');
            
            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-search fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Tidak ada data yang ditemukan</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            data.forEach((item, index) => {
                // Hitung nomor urut berdasarkan pagination
                const number = (pagination.current_page - 1) * pagination.per_page + index + 1;
                
                html += `
                    <tr id="kwitansi-row-${item.id}">
                        <td class="fw-medium">${number}</td>
                        <td>
                            <span class="badge badge-code">${item.kode_rekening}</span>
                        </td>
                        <td title="${item.uraian}">${item.uraian}</td>
                        <td class="text-muted">${item.tanggal}</td>
                        <td class="fw-semibold text-success">${item.jumlah}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="${item.preview_url}" title="Lihat Preview"
                                    class="btn btn-sm btn-outline-primary border-0 rounded-circle p-1"
                                    data-bs-toggle="tooltip" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="${item.pdf_url}" title="Download PDF"
                                    target="_blank" data-bs-toggle="tooltip"
                                    class="btn btn-sm btn-outline-success border-0 rounded-circle p-1">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <button title="Hapus Data"
                                    class="btn btn-sm btn-outline-danger delete-kwitansi border-0 rounded-circle p-1"
                                    data-id="${item.id}"
                                    data-uraian="${item.uraian}"
                                    title="Hapus Kwitansi" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;

            // Update pagination
            if (paginationContainer && pagination.last_page > 1) {
                let paginationHtml = '';
                
                // Previous button
                if (pagination.current_page > 1) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">&laquo;</a></li>`;
                } else {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                }

                // Page numbers
                for (let i = 1; i <= pagination.last_page; i++) {
                    if (i === pagination.current_page) {
                        paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }
                }

                // Next button
                if (pagination.current_page < pagination.last_page) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">&raquo;</a></li>`;
                } else {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                }

                paginationContainer.innerHTML = paginationHtml;

                // Add event listeners to pagination links
                paginationContainer.querySelectorAll('.page-link[data-page]').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = this.getAttribute('data-page');
                        const searchTerm = document.getElementById('searchInput').value;
                        loadPage(page, searchTerm);
                    });
                });
            }

            // Update pagination info
            if (paginationInfo) {
                const start = (pagination.current_page - 1) * pagination.per_page + 1;
                const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
                paginationInfo.textContent = `Menampilkan ${start} sampai ${end} dari ${pagination.total} data`;
            }

            // Re-initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        function loadPage(page, searchTerm = '') {
            const url = new URL('{{ route("kwitansi.search") }}');
            url.searchParams.append('page', page);
            if (searchTerm) {
                url.searchParams.append('search', searchTerm);
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSearchResults(data.data, data.pagination);
                        updateElementText('total-kwitansi', data.total);
                        updateElementText('footer-total', data.total);
                    }
                })
                .catch(error => {
                    console.error('Pagination error:', error);
                });
        }
    });
</script>
@endpush
@endsection