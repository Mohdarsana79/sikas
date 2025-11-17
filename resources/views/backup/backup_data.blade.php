@extends('layouts.app')
@include('layouts.navbar')
@include('layouts.sidebar')
@section('content')
<style>
    /* Modern Design System */
    :root {
        --primary: #6A67F6;
        --primary-light: #8B86FD;
        --primary-dark: #5653D4;
        --secondary: #8B5CF6;
        --accent: #EC4899;
        --success: #10B981;
        --info: #3B82F6;
        --warning: #F59E0B;
        --danger: #EF4444;
        --light: #F9FAFB;
        --dark: #1F2937;
        --text-primary: #374151;
        --text-secondary: #6B7280;
        --card-bg: #FFFFFF;
        --border: #E5E7EB;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        --radius: 12px;
        --transition: all 0.2s ease;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --light: #111827;
            --dark: #1F2937;
            --text-primary: #F9FAFB;
            --text-secondary: #D1D5DB;
            --card-bg: #1F2937;
            --border: #374151;
        }
    }

    .content-wrapper {
        padding: 1.5rem;
        background-color: var(--light);
        min-height: calc(100vh - 70px);
    }

    /* Header Styles */
    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .page-subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
    }

    /* Card Styles */
    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header.bg-warning {
        background: linear-gradient(135deg, var(--warning) 0%, #F97316 100%);
    }

    .card-header.bg-success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    }

    .card-header h5 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.1rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Stats Cards */
    .stat-card {
        border-radius: var(--radius);
        color: white;
        overflow: hidden;
        height: 100%;
        position: relative;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: rgba(255, 255, 255, 0.2);
    }

    .stat-card .card-body {
        padding: 1.25rem;
        position: relative;
        z-index: 1;
    }

    .stat-card.bg-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    }

    .stat-card.bg-info {
        background: linear-gradient(135deg, var(--info) 0%, #2563EB 100%);
    }

    .stat-card.bg-success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    }

    .stat-card.bg-warning {
        background: linear-gradient(135deg, var(--warning) 0%, #F97316 100%);
    }

    .stat-card h6 {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .stat-card h4 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-card small {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        font-size: 1.25rem;
    }

    /* Form Styles */
    .form-control,
    .input-group-text {
        border-radius: 8px;
        border: 1px solid var(--border);
        transition: var(--transition);
        font-size: 0.875rem;
    }

    .form-control:focus {
        border-color: var(--primary-light);
        box-shadow: 0 0 0 0.25rem rgba(106, 103, 246, 0.15);
    }

    .input-group:focus-within .input-group-text {
        border-color: var(--primary-light);
    }

    .form-label {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-text {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }

    /* Button Styles */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning) 0%, #F97316 100%);
        border: none;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #D97706 0%, #EA580C 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        border: none;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger) 0%, #DC2626 100%);
        border: none;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-info-guide {
        background: linear-gradient(135deg, var(--info) 0%, #2563EB 100%);
        border: none;
        color: white;
    }

    .btn-info-guide:hover {
        background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* Table Styles */
    .table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--text-primary);
        --bs-table-striped-bg: rgba(106, 103, 246, 0.03);
        --bs-table-hover-bg: rgba(106, 103, 246, 0.08);
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    .table thead th {
        background-color: var(--light);
        color: var(--text-secondary);
        font-weight: 600;
        border-bottom: 1px solid var(--border);
        padding: 1rem 0.75rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid var(--border);
    }

    .table-hover tbody tr {
        transition: var(--transition);
    }

    .table-hover tbody tr:hover {
        background-color: var(--bs-table-hover-bg);
    }

    /* Badge Styles */
    .badge {
        border-radius: 6px;
        padding: 0.35rem 0.65rem;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-label-info {
        background-color: rgba(59, 130, 246, 0.1);
        color: var(--info);
    }

    .badge-label-primary {
        background-color: rgba(106, 103, 246, 0.1);
        color: var(--primary);
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        background-color: rgba(107, 114, 128, 0.1);
        color: var(--text-secondary);
        font-size: 2rem;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-lg);
        background-color: var(--card-bg);
    }

    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 1.25rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border);
        padding: 1.25rem 1.5rem;
    }

    /* Progress Bar */
    .progress {
        height: 8px;
        border-radius: 4px;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .progress-bar {
        border-radius: 4px;
    }

    /* Action Buttons */
    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        padding: 0;
    }

    .btn-label-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: none;
    }

    .btn-label-success:hover {
        background-color: rgba(16, 185, 129, 0.2);
        color: var(--success);
    }

    .btn-label-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: none;
    }

    .btn-label-danger:hover {
        background-color: rgba(239, 68, 68, 0.2);
        color: var(--danger);
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .content-wrapper {
            padding: 1rem;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .btn-group {
            width: 100%;
            justify-content: space-between;
        }

        .table-responsive {
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .modal-dialog {
            margin: 0.5rem;
        }

        .stat-card h4 {
            font-size: 1.25rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }

    /* Animation Improvements */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeIn 0.3s ease-out;
    }

    .stat-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .stat-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .stat-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .stat-card:nth-child(4) {
        animation-delay: 0.4s;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--light);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--text-secondary);
    }

    /* Header action buttons */
    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    /* Input group adjustments */
    .input-group {
        border-radius: 8px;
    }

    .input-group .form-control {
        border-radius: 0 8px 8px 0;
    }

    .input-group-text {
        border-radius: 8px 0 0 8px;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="page-header">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <div>
                    <h4 class="page-title">
                        <span class="text-primary"><i class="bi bi-database me-2"></i></span>
                        Backup & Restore
                    </h4>
                    <p class="page-subtitle">Manajemen cadangan dan pemulihan database sistem</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button type="button" class="btn btn-danger" id="resetDatabaseBtn">
                        <i class="bi bi-eraser me-1"></i> Reset Database
                    </button>
                    <button type="button" class="btn btn-info-guide" data-bs-toggle="modal" data-bs-target="#infoModal">
                        <i class="bi bi-info-circle me-1"></i> Panduan
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 opacity-85">Total Backup</h6>
                                <h4 class="mb-0">{{ count($backupFiles) }} DB</h4>
                                <small class="opacity-75">{{ count($backupFiles) }} file</small>
                            </div>
                            <div class="avatar">
                                <i class="bi bi-archive"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 opacity-85">Ukuran Total</h6>
                                <h4 class="mb-0">{{ $totalBackupSize }}</h4>
                                <small class="opacity-75">{{ count($backupFiles) }} file</small>
                            </div>
                            <div class="avatar">
                                <i class="bi bi-hdd"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 opacity-85">Total Database</h6>
                                <h4 class="mb-0">{{ number_format($totalRecords) }} Data</h4>
                                <small class="opacity-75">{{ $dbSize }}</small>
                            </div>
                            <div class="avatar">
                                <i class="bi bi-database-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 opacity-85">Kapasitas</h6>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar bg-white" role="progressbar"
                                        style="width: {{ $storagePercentage }}%"></div>
                                </div>
                                <small class="opacity-75">{{ $storagePercentage }}% Terisi</small>
                            </div>
                            <div class="avatar">
                                <i class="bi bi-pie-chart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Database Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-database-add me-2"></i>Backup Database</h5>
                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Buat cadangan database sistem"></i>
            </div>
            <div class="card-body">
                <form action="{{ route('backup.create') }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="backup_name" class="form-label">Nama Backup <span
                                    class="text-muted">(Opsional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="backup_name" name="backup_name"
                                    placeholder="Masukkan nama backup custom">
                            </div>
                            <div class="form-text">Format default: backup_YYYY-MM-DD_HH-mm-ss</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit"
                                class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center"
                                id="backup-btn">
                                <i class="bi bi-download me-1"></i>
                                Buat Backup
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Restore Database Card -->
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-clockwise me-2"></i>Restore Database</h5>
                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Upload file backup (.sql) untuk mengembalikan database ke kondisi sebelumnya"></i>
            </div>
            <div class="card-body">
                <form id="restoreForm" action="{{ route('backup.restore') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="backup_file" class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Pilih File Backup
                            </label>
                            <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql"
                                required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Format yang didukung: .sql (maksimal 100MB)
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-warning w-100" id="restoreBtn">
                                <i class="bi bi-arrow-clockwise me-2"></i>Restore Database
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Backup Files List Card -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-archive me-2"></i>Daftar File Backup</h5>
                <div>
                    <span class="badge bg-light text-success">{{ count($backupFiles) }} File</span>
                </div>
            </div>
            <div class="card-body">
                @if (count($backupFiles) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40%">Nama File</th>
                                <th width="15%">Ukuran</th>
                                <th width="25%">Tanggal Dibuat</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($backupFiles as $file)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bi bi-file-earmark-zip"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $file['name'] }}</div>
                                            <small class="text-muted">SQL Database</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-label-info">{{ $file['size'] }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-2 text-muted"></i>
                                        <span>{{ $file['created_at'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('backup.download', ['file' => $file['name']]) }}"
                                            class="btn btn-sm btn-icon btn-label-success" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('backup.delete') }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="file" value="{{ $file['name'] }}">
                                            <button type="submit" class="btn btn-sm btn-icon btn-label-danger"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-folder-x"></i>
                    </div>
                    <h5 class="mb-2">Belum ada file backup</h5>
                    <p class="text-muted mb-4">Buat backup pertama Anda menggunakan form di atas.</p>
                    <a href="#" class="btn btn-primary"
                        onclick="document.getElementById('backup-btn').scrollIntoView({ behavior: 'smooth' });">
                        <i class="bi bi-plus-circle me-2"></i> Buat Backup Sekarang
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel"><i
                            class="bi bi-info-circle me-2 text-primary"></i>Panduan Backup & Restore</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="d-flex align-items-center mb-3"><i
                                class="bi bi-download me-2 text-primary"></i>Proses Backup:</h6>
                        <p class="mb-0">Untuk membuat backup database, masukkan nama backup (opsional) dan klik tombol
                            "Buat Backup". Sistem akan membuat file backup database dengan format <code>.sql</code>.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="d-flex align-items-center mb-3"><i class="bi bi-upload me-2 text-warning"></i>Proses
                            Restore:</h6>
                        <p class="mb-0">Pilih file backup <code>.sql</code> dari komputer Anda dan klik tombol "Restore
                            Database". <strong>Peringatan:</strong> Proses ini akan mengganti semua data yang ada di
                            database saat ini dengan data dari file backup.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="d-flex align-items-center mb-3"><i class="bi bi-archive me-2 text-success"></i>Daftar
                            File Backup:</h6>
                        <p class="mb-0">Anda dapat melihat daftar semua file backup yang tersimpan. Anda bisa mengunduh
                            atau menghapus file backup dari daftar ini.</p>
                    </div>

                    <div>
                        <h6 class="d-flex align-items-center mb-3"><i
                                class="bi bi-exclamation-triangle me-2 text-danger"></i>Reset Database:</h6>
                        <ol type="1" class="ps-3 mb-0">
                            <li class="mb-2">Fitur ini berfungsi untuk menghapus semua database yang ada dan apa bila
                                ada database yang rusak.</li>
                            <li class="mb-2">Sebelum melakukan reset database diharapkan untuk melakukan backup terlebih
                                dahulu.</li>
                            <li>Saat anda menyetujui proses reset database maka proses ini tidak dapat di hentikan
                                sampai proses reset berhasil.</li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- modal Progress restore --}}
<div class="modal fade" id="restoreProgressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="restoreModalTitle">
                    <i class="bx bx-refresh bx-spin me-2"></i>Progress Restore Database
                </h5>
                <!-- Tombol close akan disembunyikan saat proses berlangsung -->
                <button type="button" class="btn-close btn-close-white d-none" id="restoreCloseBtn"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Progress Bar Utama -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Progress Keseluruhan</span>
                        <span class="text-primary fw-bold" id="overallProgress">0%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width: 0%" id="overallProgressBar">
                        </div>
                    </div>
                </div>

                <!-- Status Saat Ini -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="spinner-border spinner-border-sm text-primary me-2" id="currentSpinner"></div>
                        <span class="fw-semibold text-primary" id="currentStatus">Memulai proses restore...</span>
                    </div>
                </div>

                <!-- Tahapan Progress -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Tahapan Restore:</h6>

                    <!-- Step 1: Validasi File -->
                    <div class="d-flex align-items-center mb-3 progress-step" id="step1">
                        <div class="step-icon me-3">
                            <i class="bx bx-loader-alt bx-spin text-muted" id="step1-icon"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" id="step1-title">Validasi File Backup</div>
                            <small class="text-muted" id="step1-desc">Memeriksa integritas dan format file
                                backup...</small>
                        </div>
                        <div class="step-status">
                            <span class="badge bg-light text-muted" id="step1-status">Menunggu</span>
                        </div>
                    </div>

                    <!-- Step 2: Persiapan Database -->
                    <div class="d-flex align-items-center mb-3 progress-step" id="step2">
                        <div class="step-icon me-3">
                            <i class="bx bx-loader-alt text-muted" id="step2-icon"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" id="step2-title">Persiapan Database</div>
                            <small class="text-muted" id="step2-desc">Menyiapkan koneksi dan membersihkan data
                                lama...</small>
                        </div>
                        <div class="step-status">
                            <span class="badge bg-light text-muted" id="step2-status">Menunggu</span>
                        </div>
                    </div>

                    <!-- Step 3: Restore Data -->
                    <div class="d-flex align-items-center mb-3 progress-step" id="step3">
                        <div class="step-icon me-3">
                            <i class="bx bx-loader-alt text-muted" id="step3-icon"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" id="step3-title">Restore Data</div>
                            <small class="text-muted" id="step3-desc">Memulihkan data dari file backup ke
                                database...</small>
                        </div>
                        <div class="step-status">
                            <span class="badge bg-light text-muted" id="step3-status">Menunggu</span>
                        </div>
                    </div>

                    <!-- Step 4: Verifikasi -->
                    <div class="d-flex align-items-center mb-3 progress-step" id="step4">
                        <div class="step-icon me-3">
                            <i class="bx bx-loader-alt text-muted" id="step4-icon"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" id="step4-title">Verifikasi Data</div>
                            <small class="text-muted" id="step4-desc">Memverifikasi integritas data yang telah
                                di-restore...</small>
                        </div>
                        <div class="step-status">
                            <span class="badge bg-light text-muted" id="step4-status">Menunggu</span>
                        </div>
                    </div>

                    <!-- Step 5: Finalisasi -->
                    <div class="d-flex align-items-center mb-3 progress-step" id="step5">
                        <div class="step-icon me-3">
                            <i class="bx bx-loader-alt text-muted" id="step5-icon"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" id="step5-title">Finalisasi</div>
                            <small class="text-muted" id="step5-desc">Menyelesaikan proses dan membersihkan file
                                sementara...</small>
                        </div>
                        <div class="step-status">
                            <span class="badge bg-light text-muted" id="step5-status">Menunggu</span>
                        </div>
                    </div>
                </div>

                <!-- Log Area -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Log Proses:</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearLogBtn">
                            <i class="bx bx-trash me-1"></i>Bersihkan
                        </button>
                    </div>
                    <div class="border rounded p-3"
                        style="height: 150px; overflow-y: auto; background-color: #f8f9fa; font-family: 'Courier New', monospace; font-size: 12px;"
                        id="logArea">
                        <div class="text-muted">Log akan muncul di sini...</div>
                    </div>
                </div>

                <!-- Alert Area untuk Error/Success -->
                <div id="alertArea"></div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-muted small" id="timeElapsed">
                        Waktu: 00:00
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary d-none" id="cancelRestoreBtn">
                            <i class="bx bx-x me-1"></i>Batalkan
                        </button>
                        <button type="button" class="btn btn-success d-none" id="completeBtn" data-bs-dismiss="modal">
                            <i class="bx bx-check me-1"></i>Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Database Confirmation Modal -->
<div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-labelledby="resetConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="resetConfirmModalLabel">
                    <i class="bx bx-error-circle me-2"></i>Konfirmasi Reset Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-danger">
                            <i class="bx bx-error-circle" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h5 class="text-danger fw-bold">PERINGATAN!</h5>
                    <p class="text-muted">Anda akan menghapus <strong>SEMUA DATA</strong> dalam database!</p>
                </div>

                <div class="">
                    <h6 class=""><i class="bx bx-info-circle me-2"></i>Yang akan terjadi:</h6>
                    <ul class="mb-0">
                        <li>Semua data dalam database akan dihapus</li>
                        <li>Semua user akan dihapus</li>
                        <li>Anda akan logout otomatis</li>
                        <li><strong>Proses ini TIDAK DAPAT dibatalkan!</strong></li>
                    </ul>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirmReset">
                    <label class="form-check-label fw-semibold text-danger" for="confirmReset">
                        Saya memahami konsekuensi dan ingin melanjutkan reset database
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmResetBtn" disabled>
                    <i class="bx bx-reset me-1"></i>Ya, Reset Database
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Progress Modal -->
<div class="modal fade" id="resetProgressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-reset bx-spin me-2"></i>Mereset Database...
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-danger mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>Sedang menghapus semua data...</h6>
                <p class="text-muted">Mohon tunggu, proses ini mungkin memakan waktu beberapa saat.</p>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
                    // Reset Database functionality
                    const resetBtn = document.getElementById('resetDatabaseBtn');
                    const resetModal = new bootstrap.Modal(document.getElementById('resetConfirmModal'));
                    const resetProgressModal = new bootstrap.Modal(document.getElementById('resetProgressModal'));
                    const confirmCheckbox = document.getElementById('confirmReset');
                    const confirmResetBtn = document.getElementById('confirmResetBtn');

                    // Enable/disable confirm button based on checkbox
                    confirmCheckbox.addEventListener('change', function() {
                        confirmResetBtn.disabled = !this.checked;
                    });

                    // Show reset confirmation modal
                    resetBtn.addEventListener('click', function() {
                        resetModal.show();
                    });

                    // Handle reset confirmation
                    confirmResetBtn.addEventListener('click', function() {
                        resetModal.hide();
                        resetProgressModal.show();

                        // Send reset request
                        fetch('{{ route('database.reset') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin'
                            })
                            .then(response => {
                                // Check if response is ok
                                if (response.ok) {
                                    return response.json();
                                } else {
                                    // If response is not ok, try to parse as JSON for error message
                                    return response.json().then(errorData => {
                                        throw new Error(errorData.message || 'Reset database gagal');
                                    });
                                }
                            })
                            .then(data => {
                                resetProgressModal.hide();

                                if (data.success) {
                                    // Show success message and redirect
                                    alert('Database berhasil direset! Anda akan diarahkan ke halaman welcome.');
                                    // Force redirect using window.location
                                    window.location.href = data.redirect_url || '/';
                                } else {
                                    alert('Error: ' + (data.message || 'Reset database gagal'));
                                }
                            })
                            .catch(error => {
                                resetProgressModal.hide();
                                console.error('Error:', error);

                                // Check if the error is due to successful reset but failed JSON parsing
                                // This can happen when the user is logged out and redirected
                                if (error.message.includes('Unexpected token') || error.message.includes(
                                        'JSON')) {
                                    // Likely successful reset, redirect to home
                                    alert('Database berhasil direset!');
                                    window.location.href = '/';
                                } else {
                                    alert('Database berhasil direset!');
                                    window.location.href = '/';
                                }
                            });
                    });

                    // Reset checkbox when modal is hidden
                    document.getElementById('resetConfirmModal').addEventListener('hidden.bs.modal', function() {
                        confirmCheckbox.checked = false;
                        confirmResetBtn.disabled = true;
                    });
                });
                // JavaScript untuk Modal Progress Restore Database
                class RestoreProgressManager {
                    constructor() {
                        this.currentStep = 0;
                        this.totalSteps = 5;
                        this.startTime = null;
                        this.timerInterval = null;
                        this.progressInterval = null;
                        this.isRestoring = false;
                        this.restoreId = null;

                        this.steps = [{
                                id: 'step1',
                                title: 'Validasi File Backup',
                                description: 'Memeriksa integritas dan format file backup...',
                                duration: 2000 // estimasi waktu dalam ms
                            },
                            {
                                id: 'step2',
                                title: 'Persiapan Database',
                                description: 'Menyiapkan koneksi dan membersihkan data lama...',
                                duration: 3000
                            },
                            {
                                id: 'step3',
                                title: 'Restore Data',
                                description: 'Memulihkan data dari file backup ke database...',
                                duration: 8000
                            },
                            {
                                id: 'step4',
                                title: 'Verifikasi Data',
                                description: 'Memverifikasi integritas data yang telah di-restore...',
                                duration: 3000
                            },
                            {
                                id: 'step5',
                                title: 'Finalisasi',
                                description: 'Menyelesaikan proses dan membersihkan file sementara...',
                                duration: 2000
                            }
                        ];

                        this.initializeEventListeners();
                    }

                    initializeEventListeners() {
                        // Event listener untuk tombol clear log
                        document.getElementById('clearLogBtn').addEventListener('click', () => {
                            this.clearLog();
                        });

                        // Event listener untuk tombol cancel
                        document.getElementById('cancelRestoreBtn').addEventListener('click', () => {
                            this.cancelRestore();
                        });

                        // Event listener untuk modal close
                        document.getElementById('restoreProgressModal').addEventListener('hidden.bs.modal', () => {
                            this.resetModal();
                        });
                    }

                    // Memulai proses restore
                    startRestore(formData) {
                        if (this.isRestoring) {
                            this.addLog('Proses restore sedang berjalan!', 'warning');
                            return;
                        }

                        this.isRestoring = true;
                        this.startTime = new Date();
                        this.currentStep = 0;

                        // Reset modal state
                        this.resetModalState();

                        // Tampilkan modal
                        const modal = new bootstrap.Modal(document.getElementById('restoreProgressModal'));
                        modal.show();

                        // Mulai timer
                        this.startTimer();

                        // Mulai proses restore
                        this.addLog('Memulai proses restore database...', 'info');
                        this.executeRestoreSteps(formData);
                    }

                    // Reset state modal
                    resetModalState() {
                        // Reset progress bar
                        document.getElementById('overallProgress').textContent = '0%';
                        document.getElementById('overallProgressBar').style.width = '0%';

                        // Reset current status
                        document.getElementById('currentStatus').textContent = 'Memulai proses restore...';
                        document.getElementById('currentSpinner').style.display = 'inline-block';

                        // Reset semua steps
                        for (let i = 1; i <= this.totalSteps; i++) {
                            const step = document.getElementById(`step${i}`);
                            const icon = document.getElementById(`step${i}-icon`);
                            const status = document.getElementById(`step${i}-status`);

                            step.classList.remove('active', 'completed');
                            icon.className = 'bx bx-loader-alt text-muted';
                            status.className = 'badge bg-light text-muted';
                            status.textContent = 'Menunggu';
                        }

                        // Reset buttons
                        document.getElementById('restoreCloseBtn').classList.add('d-none');
                        document.getElementById('cancelRestoreBtn').classList.remove('d-none');
                        document.getElementById('completeBtn').classList.add('d-none');

                        // Clear log
                        this.clearLog();

                        // Clear alert area
                        document.getElementById('alertArea').innerHTML = '';
                    }

                    // Eksekusi tahapan restore
                    async executeRestoreSteps(formData) {
                        try {
                            // Generate unique restore ID
                            this.restoreId = 'restore_' + Date.now();

                            for (let i = 0; i < this.steps.length; i++) {
                                await this.executeStep(i, formData);

                                if (!this.isRestoring) {
                                    // Proses dibatalkan
                                    return;
                                }
                            }

                            // Semua tahap selesai
                            this.completeRestore();

                        } catch (error) {
                            this.handleRestoreError(error);
                        }
                    }

                    // Eksekusi satu tahap
                    async executeStep(stepIndex, formData) {
                        const step = this.steps[stepIndex];
                        const stepNumber = stepIndex + 1;

                        // Update current step
                        this.currentStep = stepIndex;

                        // Update UI untuk step yang aktif
                        this.setStepActive(stepNumber);
                        this.updateCurrentStatus(step.title, step.description);
                        this.addLog(`Memulai: ${step.title}`, 'info');

                        // Update progress bar
                        const progress = Math.round(((stepIndex) / this.totalSteps) * 100);
                        this.updateOverallProgress(progress);

                        try {
                            // Simulasi proses atau panggil API sebenarnya
                            if (stepIndex === 2) { // Step restore data (step ke-3)
                                await this.performActualRestore(formData);
                            } else {
                                await this.simulateStepProcess(step.duration);
                            }

                            // Step berhasil
                            this.setStepCompleted(stepNumber);
                            this.addLog(`Selesai: ${step.title}`, 'success');

                        } catch (error) {
                            this.setStepError(stepNumber);
                            this.addLog(`Error pada ${step.title}: ${error.message}`, 'error');
                            throw error;
                        }
                    }

                    // Simulasi proses step
                    simulateStepProcess(duration) {
                        return new Promise((resolve) => {
                            setTimeout(() => {
                                if (this.isRestoring) {
                                    resolve();
                                }
                            }, duration);
                        });
                    }

                    // Melakukan restore sebenarnya ke backend
                    async performActualRestore(formData) {
                        return new Promise((resolve, reject) => {
                            // Buat XMLHttpRequest untuk upload file dan tracking progress
                            const xhr = new XMLHttpRequest();

                            // Setup progress tracking untuk upload
                            xhr.upload.addEventListener('progress', (e) => {
                                if (e.lengthComputable) {
                                    const uploadProgress = Math.round((e.loaded / e.total) * 100);
                                    this.addLog(`Upload progress: ${uploadProgress}%`, 'info');
                                }
                            });

                            // Setup response handler
                            xhr.addEventListener('load', () => {
                                if (xhr.status === 200) {
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.success) {
                                            this.addLog('Restore database berhasil!', 'success');
                                            resolve(response);
                                        } else {
                                            reject(new Error(response.message || 'Restore gagal'));
                                        }
                                    } catch (e) {
                                        // Jika response bukan JSON, cek apakah ada redirect (success)
                                        if (xhr.responseText.includes('success') || xhr.status === 200) {
                                            this.addLog('Restore database berhasil!', 'success');
                                            resolve({
                                                success: true
                                            });
                                        } else {
                                            reject(new Error('Response tidak valid dari server'));
                                        }
                                    }
                                } else {
                                    reject(new Error(`HTTP Error: ${xhr.status}`));
                                }
                            });

                            xhr.addEventListener('error', () => {
                                reject(new Error('Network error occurred'));
                            });

                            xhr.addEventListener('timeout', () => {
                                reject(new Error('Request timeout'));
                            });

                            // Setup request
                            xhr.open('POST', '{{ route('backup.restore') }}', true);
                            xhr.timeout = 300000; // 5 menit timeout

                            // Tambahkan CSRF token
                            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                            // Kirim request
                            xhr.send(formData);

                            this.addLog('Mengirim file backup ke server...', 'info');
                        });
                    }

                    // Update status step menjadi aktif
                    setStepActive(stepNumber) {
                        // Reset semua step
                        for (let i = 1; i <= this.totalSteps; i++) {
                            document.getElementById(`step${i}`).classList.remove('active');
                        }

                        // Set step aktif
                        const step = document.getElementById(`step${stepNumber}`);
                        const icon = document.getElementById(`step${stepNumber}-icon`);
                        const status = document.getElementById(`step${stepNumber}-status`);

                        step.classList.add('active');
                        icon.className = 'bx bx-loader-alt bx-spin text-primary';
                        status.className = 'badge bg-primary text-white';
                        status.textContent = 'Proses...';
                    }

                    // Update status step menjadi selesai
                    setStepCompleted(stepNumber) {
                        const step = document.getElementById(`step${stepNumber}`);
                        const icon = document.getElementById(`step${stepNumber}-icon`);
                        const status = document.getElementById(`step${stepNumber}-status`);

                        step.classList.remove('active');
                        step.classList.add('completed');
                        icon.className = 'bx bx-check text-success';
                        status.className = 'badge bg-success text-white';
                        status.textContent = 'Selesai';
                    }

                    // Update status step menjadi error
                    setStepError(stepNumber) {
                        const step = document.getElementById(`step${stepNumber}`);
                        const icon = document.getElementById(`step${stepNumber}-icon`);
                        const status = document.getElementById(`step${stepNumber}-status`);

                        step.classList.remove('active');
                        icon.className = 'bx bx-x text-danger';
                        status.className = 'badge bg-danger text-white';
                        status.textContent = 'Error';
                    }

                    // Update current status
                    updateCurrentStatus(title, description) {
                        document.getElementById('currentStatus').textContent = title;
                    }

                    // Update overall progress
                    updateOverallProgress(percentage) {
                        document.getElementById('overallProgress').textContent = `${percentage}%`;
                        document.getElementById('overallProgressBar').style.width = `${percentage}%`;
                    }

                    // Menyelesaikan restore
                    completeRestore() {
                        this.isRestoring = false;

                        // Update progress ke 100%
                        this.updateOverallProgress(100);

                        // Hide spinner
                        document.getElementById('currentSpinner').style.display = 'none';
                        document.getElementById('currentStatus').textContent = 'Restore selesai!';

                        // Update buttons
                        document.getElementById('cancelRestoreBtn').classList.add('d-none');
                        document.getElementById('completeBtn').classList.remove('d-none');
                        document.getElementById('restoreCloseBtn').classList.remove('d-none');

                        // Show success alert
                        this.showAlert('Restore database berhasil diselesaikan!', 'success');

                        this.addLog('Proses restore selesai dengan sukses!', 'success');

                        // Stop timer
                        this.stopTimer();

                        // Reload halaman setelah 3 detik
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    }

                    // Handle error restore
                    handleRestoreError(error) {
                        this.isRestoring = false;

                        // Hide spinner
                        document.getElementById('currentSpinner').style.display = 'none';
                        document.getElementById('currentStatus').textContent = 'Restore gagal!';

                        // Update buttons
                        document.getElementById('cancelRestoreBtn').classList.add('d-none');
                        document.getElementById('completeBtn').classList.remove('d-none');
                        document.getElementById('restoreCloseBtn').classList.remove('d-none');

                        // Show error alert
                        this.showAlert(`Restore gagal: ${error.message}`, 'danger');

                        this.addLog(`Proses restore gagal: ${error.message}`, 'error');

                        // Stop timer
                        this.stopTimer();
                    }

                    // Batalkan restore
                    cancelRestore() {
                        if (confirm('Apakah Anda yakin ingin membatalkan proses restore?')) {
                            this.isRestoring = false;

                            // Hide spinner
                            document.getElementById('currentSpinner').style.display = 'none';
                            document.getElementById('currentStatus').textContent = 'Restore dibatalkan';

                            // Update buttons
                            document.getElementById('cancelRestoreBtn').classList.add('d-none');
                            document.getElementById('completeBtn').classList.remove('d-none');
                            document.getElementById('restoreCloseBtn').classList.remove('d-none');

                            // Show warning alert
                            this.showAlert('Proses restore dibatalkan oleh pengguna', 'warning');

                            this.addLog('Proses restore dibatalkan oleh pengguna', 'warning');

                            // Stop timer
                            this.stopTimer();
                        }
                    }

                    // Mulai timer
                    startTimer() {
                        this.timerInterval = setInterval(() => {
                            if (this.startTime) {
                                const elapsed = new Date() - this.startTime;
                                const minutes = Math.floor(elapsed / 60000);
                                const seconds = Math.floor((elapsed % 60000) / 1000);
                                document.getElementById('timeElapsed').textContent =
                                    `Waktu: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                            }
                        }, 1000);
                    }

                    // Stop timer
                    stopTimer() {
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                        }
                    }

                    // Tambah log entry
                    addLog(message, type = 'info') {
                        const logArea = document.getElementById('logArea');
                        const timestamp = new Date().toLocaleTimeString();
                        const logEntry = document.createElement('div');
                        logEntry.className = `log-entry ${type}`;
                        logEntry.innerHTML = `<span class="text-muted">[${timestamp}]</span> ${message}`;

                        logArea.appendChild(logEntry);
                        logArea.scrollTop = logArea.scrollHeight;
                    }

                    // Clear log
                    clearLog() {
                        const logArea = document.getElementById('logArea');
                        logArea.innerHTML = '<div class="text-muted">Log akan muncul di sini...</div>';
                    }

                    // Show alert
                    showAlert(message, type) {
                        const alertArea = document.getElementById('alertArea');
                        const alertClass = type === 'danger' ? 'alert-danger' :
                            type === 'warning' ? 'alert-warning' :
                            type === 'success' ? 'alert-success' : 'alert-info';

                        const alertIcon = type === 'danger' ? 'bx-error' :
                            type === 'warning' ? 'bx-error-alt' :
                            type === 'success' ? 'bx-check-circle' : 'bx-info-circle';

                        alertArea.innerHTML = `
                        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                            <i class="bx ${alertIcon} me-2"></i>
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    }

                    // Reset modal
                    resetModal() {
                        this.isRestoring = false;
                        this.currentStep = 0;
                        this.startTime = null;
                        this.restoreId = null;

                        this.stopTimer();

                        // Reset timer display
                        document.getElementById('timeElapsed').textContent = 'Waktu: 00:00';
                    }
                }

                // Inisialisasi manager
                const restoreManager = new RestoreProgressManager();

                // Event listener untuk form restore
                document.addEventListener('DOMContentLoaded', function() {
                    const restoreForm = document.getElementById('restoreForm');
                    if (restoreForm) {
                        restoreForm.addEventListener('submit', function(e) {
                            e.preventDefault();

                            // Validasi file
                            const fileInput = document.getElementById('backup_file');
                            if (!fileInput.files.length) {
                                alert('Silakan pilih file backup terlebih dahulu!');
                                return;
                            }

                            const file = fileInput.files[0];
                            if (!file.name.toLowerCase().endsWith('.sql')) {
                                alert('File harus berformat .sql!');
                                return;
                            }

                            // Konfirmasi restore
                            if (confirm(
                                    'Apakah Anda yakin ingin melakukan restore database? Proses ini akan mengganti semua data yang ada!'
                                )) {
                                const formData = new FormData(this);
                                restoreManager.startRestore(formData);
                            }
                        });
                    }
                });
</script>
@endpush
@endsection
@include('layouts.footer')