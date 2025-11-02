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
                id="generate-all-btn" {{ $kwitansis->count() === 0 ? 'disabled' : '' }}>
                <i class="bi bi-plus-circle me-2"></i>
                Generate Otomatis
            </button>
            <!-- Hapus Semua -->
            <button class="btn btn-danger btn-sm fw-semibold rounded-pill shadow-sm py-2 px-4 d-flex align-items-center"
                id="delete-all-btn">
                <i class="bi bi-trash me-2"></i>
                Hapus Semua
            </button>
            <!-- Buat Manual -->
            <a href="{{ route('kwitansi.create') }}" class="btn btn-primary btn-sm fw-semibold rounded-pill shadow-sm py-2 px-4 d-flex align-items-center">
                <i class="bi bi-pencil-square me-2"></i>
                Buat Manual
            </a>
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
                <p class="h1 fw-bolder text-dark mt-2" id="total-kwitansi">{{ $kwitansis->count() }}</p>
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
        <h2 class="fs-4 fw-bold text-dark mb-4">Daftar Kwitansi</h2>
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
                        <th scope="col" class="text-secondary fw-medium">Status</th>
                        <th scope="col" class="text-center text-secondary fw-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kwitansis as $index => $kwitansi)
                    <tr id="kwitansi-row-{{ $kwitansi->id }}">
                        <td class="fw-medium">{{ $index + 1 }}</td>
                        <td>
                            <span class="badge badge-code">{{ $kwitansi->rekeningBelanja->kode_rekening ?? '-' }}</span>
                        </td>
                        <td title="{{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}">
                            {{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}
                        </td>
                        <td class="text-muted">{{
                        \Carbon\Carbon::parse($kwitansi->bukuKasUmum->tanggal_transaksi)->format('d/m/Y')
                        }}</td>
                        <td class="fw-semibold text-success">Rp {{ number_format($kwitansi->bukuKasUmum->total_transaksi_kotor, 0, ',',
                        '.') }}</td>
                        <td>
                            <span class="badge text-bg-success-subtle text-success fw-semibold rounded-pill">Siap</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <!-- Lihat -->
                                <a href="{{ route('kwitansi.preview', $kwitansi->id) }}" title="Lihat Preview"
                                    class="btn btn-sm btn-outline-primary border-0 rounded-circle p-1" data-bs-toggle="tooltip">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!-- Cetak -->
                                <a href="{{ route('kwitansi.pdf', $kwitansi->id) }}" title="Download PDF"   target ="_blank" data-bs-toggle="tooltip"
                                    class="btn btn-sm btn-outline-success border-0 rounded-circle p-1">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <!-- Hapus -->
                                <button title="Hapus Data"
                                    class="btn btn-sm btn-outline-danger delete-kwitansi border-0 rounded-circle p-1" data-id="{{ $kwitansi->id }}" data-uraian="{{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}" title="Hapus Kwitansi" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($kwitansis->count() > 0)
        <div class="card-footer bg-transparent border-top-0">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <small class="text-muted">
                            Total: <strong id="footer-total" class="text-primary">{{ $kwitansis->count()
                                }}</strong> kwitansi
                        </small>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <i class="fas fa-clock text-muted me-2"></i>
                        <small class="text-muted">
                            Terakhir diperbarui: <span id="last-updated" class="fw-semibold">{{
                                now()->format('d/m/Y H:i') }}</span>
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

        // Function untuk generate semua kwitansi dengan SweetAlert Progress
        function generateAllKwitansi() {
            console.log('Starting generate process...');
            
            // Tampilkan konfirmasi sederhana
            Swal.fire({
                title: 'Generate Kwitansi Otomatis?',
                html: `
                    <div class="text-center">
                        <i class="fas fa-bolt fa-3x text-warning mb-3 pulse"></i>
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

            // Tampilkan loading awal
            Swal.fire({
                title: 'Memulai Generate...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
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

                    // Set timeout untuk mencegah stuck (5 menit saja)
                    timeoutId = setTimeout(() => {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Proses Dihentikan',
                            html: `
                                <div class="text-left">
                                    <p>Proses generate dihentikan karena terlalu lama.</p>
                                    <div class="alert alert-warning">
                                        <strong>Status:</strong><br>
                                        Berhasil: ${totalSuccess} kwitansi<br>
                                        Gagal: ${totalFailed}<br>
                                        Total: ${totalRecords} data
                                    </div>
                                </div>
                            `,
                            confirmButtonText: '<i class="fas fa-check mr-1"></i> Mengerti'
                        });
                    }, 5 * 60 * 1000); // 5 menit

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
                    
                    // PERBAIKAN: Akumulasi yang benar
                    const newSuccess = totalSuccess + batchData.success;
                    const newFailed = totalFailed + batchData.failed;
                    const newOffset = batchData.offset; // Gunakan offset dari server

                    console.log(`✅ Batch ${attempt}: Processed ${batchData.processed}, Success: ${batchData.success}, Failed: ${batchData.failed}, Progress: ${batchData.progress}%, HasMore: ${batchData.has_more}`);

                    // PERBAIKAN: Update UI dengan progress dari server
                    updateSweetAlertProgress(batchData.progress, newSuccess, newFailed, batchData.remaining, batchData.total);
                    updateElementText('pending-count', batchData.remaining);
                    updateElementText('failed-count', newFailed);

                    // PERBAIKAN: Logic continue yang lebih ketat
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
                            totalRecords: batchData.total,
                            finalOffset: newOffset,
                            finalProgress: batchData.progress,
                            finalRemaining: batchData.remaining
                        });
                        
                        // Tampilkan completion message
                        setTimeout(() => {
                            showCompletionMessage(newSuccess, newFailed, batchData.total);
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

        // Function untuk update progress di SweetAlert - DIPERBAIKI
        function updateSweetAlertProgress(percent, success, failed, remaining, total) {
            const displayTotal = total || '?';
            const displaySuccess = success || 0;
            const displayFailed = failed || 0;
            const displayRemaining = remaining || 0;

            // Determine progress color based on percentage
            let progressColor = 'bg-success';
            if (percent < 30) progressColor = 'bg-danger';
            else if (percent < 70) progressColor = 'bg-warning';

            // PERBAIKAN: Pastikan progress tidak melebihi 100% dan tidak negatif
            const safePercent = Math.min(100, Math.max(0, percent));

            // PERBAIKAN: Tampilkan progress yang lebih detail
            let progressText = '';
            if (safePercent === 0) {
                progressText = 'Memulai proses...';
            } else if (safePercent < 100) {
                progressText = `Sedang memproses... ${safePercent}%`;
            } else {
                progressText = 'Proses selesai! 100%';
            }

            Swal.update({
                title: `Generate Kwitansi - ${safePercent}%`,
                html: `
                    <div class="text-center">
                        <!-- Progress Text -->
                        <div class="mb-2">
                            <h6 class="text-primary">${progressText}</h6>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="progress" style="height: 25px; border-radius: 12px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated ${progressColor}" 
                                    style="width: ${safePercent}%; font-size: 14px; font-weight: bold;">
                                    ${safePercent}%
                                </div>
                            </div>
                        </div>

                        <!-- Statistics in Cards -->
                        <div class="row mb-3">
                            <div class="col-6 mb-2">
                                <div class="card border-success">
                                    <div class="card-body text-center py-2">
                                        <div class="h5 mb-1 text-success font-weight-bold">${displaySuccess}</div>
                                        <small class="text-muted">Berhasil</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="card border-danger">
                                    <div class="card-body text-center py-2">
                                        <div class="h5 mb-1 text-danger font-weight-bold">${displayFailed}</div>
                                        <small class="text-muted">Gagal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-info">
                                    <div class="card-body text-center py-2">
                                        <div class="h5 mb-1 text-info font-weight-bold">${displayRemaining}</div>
                                        <small class="text-muted">Sisa</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-primary">
                                    <div class="card-body text-center py-2">
                                        <div class="h5 mb-1 text-primary font-weight-bold">${displayTotal}</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Animation -->
                        ${safePercent < 100 ? `
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="spinner-border text-primary mr-2" style="width: 1.5rem; height: 1.5rem;"></div>
                            <span class="text-muted">Memproses data...</span>
                        </div>
                        ` : ''}

                        <div class="alert alert-light border">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Progress: ${safePercent}% • Berhasil: ${displaySuccess} • Gagal: ${displayFailed} • Sisa: ${displayRemaining}
                            </small>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCancelButton: false
            });
        }

        // Function untuk menampilkan pesan penyelesaian
        function showCompletionMessage(successCount, failedCount, totalCount) {
            const successRate = totalCount > 0 ? Math.round((successCount / totalCount) * 100) : 0;
            
            let resultHtml = `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 class="text-success">Generate Selesai!</h4>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-4">
                            <div class="card bg-success text-white">
                                <div class="card-body py-3">
                                    <div class="h3 mb-1 font-weight-bold">${successCount}</div>
                                    <small>Berhasil</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body py-3">
                                    <div class="h3 mb-1 font-weight-bold">${failedCount}</div>
                                    <small>Gagal</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-3">
                                    <div class="h3 mb-1 font-weight-bold">${successRate}%</div>
                                    <small>Sukses</small>
                                </div>
                            </div>
                        </div>
                    </div>
            `;

            if (failedCount > 0) {
                resultHtml += `
                    <div class="alert alert-warning text-left">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Ada ${failedCount} data yang gagal digenerate.</strong><br>
                        Periksa log sistem untuk detail lebih lanjut.
                    </div>
                `;
            } else {
                resultHtml += `
                    <div class="alert alert-success">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        <strong>Semua data berhasil digenerate!</strong>
                    </div>
                `;
            }

            resultHtml += `
                <div class="mt-4">
                    <button onclick="location.reload()" class="btn btn-success btn-lg">
                        <i class="fas fa-sync mr-2"></i> Refresh Halaman
                    </button>
                </div>
            </div>`;

            Swal.fire({
                title: 'Proses Selesai',
                html: resultHtml,
                icon: failedCount > 0 ? 'warning' : 'success',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke',
                allowOutsideClick: false
            }).then((result) => {
                // Refresh halaman untuk update data
                location.reload();
            });
        }

        // Function untuk hapus semua kwitansi
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
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        processDeleteAll(resolve);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }

        // Function proses hapus semua dengan loading
        function processDeleteAll(resolve) {
            // Show loading state
            Swal.update({
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <h5>Menghapus Semua Data Kwitansi...</h5>
                        <p class="text-muted">Sedang memproses penghapusan semua data, harap tunggu.</p>
                    </div>
                `,
                showCancelButton: false,
                showConfirmButton: false
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
                    // Update UI
                    updateElementText('total-kwitansi', '0');
                    updateElementText('footer-total', '0');
                    
                    // Hapus semua baris dari tabel
                    const tableBody = document.querySelector('#simple-kwitansi-table tbody');
                    if (tableBody) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                                    <h5 class="text-muted">Tidak ada data kwitansi</h5>
                                    <p class="text-info">
                                        <i class="fas fa-lightbulb"></i>
                                        Klik tombol "Generate Otomatis" untuk membuat kwitansi dari data transaksi yang tersedia
                                    </p>
                                </td>
                            </tr>
                        `;
                    }
                    
                    // Nonaktifkan tombol hapus semua
                    const deleteAllBtn = document.getElementById('delete-all-btn');
                    if (deleteAllBtn) deleteAllBtn.disabled = true;
                    
                    // Update statistics
                    checkAvailableData();
                    updateLastUpdated();
                    
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
                        confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke',
                        allowOutsideClick: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message,
                        confirmButtonText: '<i class="fas fa-times mr-1"></i> Tutup'
                    });
                }
                if (resolve) resolve();
            })
            .catch(error => {
                console.error('Delete all error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus semua data kwitansi',
                    confirmButtonText: '<i class="fas fa-times mr-1"></i> Tutup'
                });
                if (resolve) resolve();
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
            const formattedDate = now.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            updateElementText('last-updated', formattedDate);
        }

        // Event listener untuk delete individual kwitansi
        document.querySelectorAll('.delete-kwitansi').forEach(button => {
            button.addEventListener('click', function() {
                const kwitansiId = this.getAttribute('data-id');
                const uraian = this.getAttribute('data-uraian');
                
                Swal.fire({
                    title: 'Hapus Kwitansi?',
                    html: `
                        <div class="alert alert-warning text-left">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Konfirmasi Penghapusan</strong>
                            <hr>
                            <p>Apakah Anda yakin ingin menghapus kwitansi ini?</p>
                            <p class="mb-0"><strong>Uraian:</strong> ${uraian}</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteSingleKwitansi(kwitansiId, uraian);
                    }
                });
            });
        });

        // Function untuk hapus single kwitansi
        function deleteSingleKwitansi(id, uraian) {
            fetch(`/kwitansi/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from table
                    const row = document.getElementById(`kwitansi-row-${id}`);
                    if (row) row.remove();
                    
                    // Update statistics
                    updateElementText('total-kwitansi', parseInt(document.getElementById('total-kwitansi').textContent) - 1);
                    updateElementText('footer-total', parseInt(document.getElementById('footer-total').textContent) - 1);
                    checkAvailableData();
                    updateLastUpdated();
                    
                    // Check if table is empty
                    const tableBody = document.querySelector('#simple-kwitansi-table tbody');
                    if (tableBody && tableBody.children.length === 0) {
                        location.reload();
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Kwitansi berhasil dihapus',
                        confirmButtonText: '<i class="fas fa-check mr-1"></i> Oke'
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
                console.error('Delete error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus kwitansi',
                    confirmButtonText: '<i class="fas fa-times mr-1"></i> Tutup'
                });
            });
        }
    });
</script>
@endpush

@endsection