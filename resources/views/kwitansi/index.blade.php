@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<style>
    /* Menggunakan font Inter untuk tampilan yang bersih dan modern */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-size: 10pt;
        min-height: 100vh;
    }

    .main-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        margin: 20px;
        padding: 30px;
        position: relative;
        overflow: hidden;
    }

    .main-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
        background-size: 200% 100%;
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    /* Kelas untuk Kartu yang lebih modern */
    .modern-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        padding-top: 2rem;
        overflow: hidden;
        background: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modern-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        background: linear-gradient(90deg, currentColor 0%, transparent 100%);
        opacity: 0.8;
    }

    /* Efek Hover yang lebih dramatis */
    .modern-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Warna Border Aksen dengan gradient */
    .card-blue {
        color: #3b82f6;
    }

    .card-green {
        color: #10b981;
    }

    .card-yellow {
        color: #f59e0b;
    }

    .card-red {
        color: #ef4444;
    }

    /* Icon styling */
    .card-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .modern-card:hover .card-icon {
        transform: scale(1.1);
        opacity: 1;
    }

    /* Badge untuk Kode Rekening */
    .badge-code {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        padding: 0.5em 1em;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }

    .badge-code:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    /* Button styling */
    .btn-modern {
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .btn-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .btn-primary {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    /* Table styling */
    .table-modern {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .table-modern thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table-modern thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    .table-modern tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .table-modern tbody tr:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
        transform: translateX(4px);
    }

    .table-modern tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
    }

    /* Action buttons */
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-action:hover {
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Search and filter styling - UPDATED FOR SMALLER SIZE */
    .search-container {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Smaller form controls */
    .form-control-modern {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .form-control-modern.form-control-sm {
        padding: 0.375rem 0.75rem;
        height: calc(1.5em + 0.75rem + 3px);
    }

    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }

    .form-select-modern {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .form-select-modern.form-select-sm {
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        height: calc(1.5em + 0.75rem + 3px);
    }

    .form-select-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }

    .input-group-sm>.form-control,
    .input-group-sm>.form-select,
    .input-group-sm>.input-group-text {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 1.5px solid #e2e8f0;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }

    /* Smaller labels */
    .form-label.small {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.375rem;
        color: #374151;
    }

    /* Filter Date Styles */
    .input-group-sm .input-group-text {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .input-group-sm .form-control {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Filter Info Styles */
    #filterInfo {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.75rem;
        border-left: 4px solid #17a2b8;
    }

    #filterInfo .badge {
        font-size: 0.7rem;
    }

    /* Date Input Styling */
    input[type="date"] {
        position: relative;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    /* Filter Date Styles dengan efek interaktif */
    .input-group-sm .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .input-group-sm .input-group-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: 1px solid #667eea;
    }

    /* Loading state untuk filter */
    .filter-loading {
        position: relative;
    }

    .filter-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 8px;
        width: 12px;
        height: 12px;
        border: 2px solid transparent;
        border-top: 2px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translateY(-50%);
    }

    @keyframes spin {
        0% {
            transform: translateY(-50%) rotate(0deg);
        }

        100% {
            transform: translateY(-50%) rotate(360deg);
        }
    }

    /* Hover effects untuk filter */
    .input-group:hover .input-group-text {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    /* Active filter state */
    .form-control:not(:placeholder-shown) {
        border-color: #28a745;
        background-color: #f8fff9;
    }

    /* Date input khusus */
    input[type="date"]:focus {
        background-color: #f8f9ff;
    }

    /* Responsive Filter */
    @media (max-width: 768px) {
        .search-container .row.g-2 {
            flex-wrap: wrap;
        }

        .search-container .col-12 {
            margin-bottom: 0.5rem;
        }

        .d-flex.gap-2 {
            flex-direction: column;
        }

        .d-flex.gap-2 .input-group {
            width: 100% !important;
        }
    }

    @media (max-width: 576px) {
        .search-container {
            padding: 0.75rem;
        }

        .d-flex.gap-2 {
            flex-direction: column;
        }

        .d-flex.gap-2 .input-group {
            width: 100% !important;
        }
    }

    /* Label Styles untuk Filter */
    .form-label.small {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6c757d !important;
    }

    /* Responsive untuk label */
    @media (max-width: 768px) {
        .d-flex.align-items-center.gap-1 {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .d-flex.align-items-center.gap-1 .form-label {
            min-width: auto !important;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
        }

        .d-flex.align-items-center.gap-1 .input-group {
            width: 100% !important;
        }

        /* Tampilkan label di mobile juga */
        .d-flex.align-items-center.gap-1 .form-label.d-none.d-md-block {
            display: block !important;
        }
    }

    /* Pagination Sederhana */
    .pagination-simple {
        margin: 0;
        padding: 0;
    }

    .pagination-simple .page-link {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        margin: 0 3px;
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
        background-color: white;
        text-decoration: none;
        display: inline-block;
    }

    .pagination-simple .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }

    .pagination-simple .page-link:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
        text-decoration: none;
    }

    .pagination-simple .page-item.disabled .page-link {
        background-color: #f8f9fa;
        border-color: #e2e8f0;
        color: #9ca3af;
    }

    /* Loading animation */
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .loading-pulse {
        animation: pulse 2s infinite;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-container {
            margin: 10px;
            padding: 20px;
            border-radius: 16px;
        }

        .btn-modern {
            padding: 10px 20px;
            font-size: 0.9rem;
        }

        .table-modern {
            font-size: 0.9rem;
        }

        .search-container {
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        .form-control-modern.form-control-sm,
        .form-select-modern.form-select-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        /* Responsive pagination */
        .pagination-simple .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.8rem;
            margin: 0 2px;
        }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }

    /* Empty state styling */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        border: 2px dashed #cbd5e1;
    }

    .empty-state-icon {
        font-size: 4rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1rem;
    }

    /* PERBAIKAN: Pagination Styles */
    #pagination-container {
        margin: 0;
        padding: 0;
        display: flex;
        list-style: none;
        gap: 4px;
        flex-wrap: wrap;
    }

    #pagination-container .page-item .page-link {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
        background-color: white;
        text-decoration: none;
        display: block;
        min-width: 44px;
        text-align: center;
    }

    #pagination-container .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }

    #pagination-container .page-item:not(.disabled):not(.active) .page-link:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }

    #pagination-container .page-item.disabled .page-link {
        background-color: #f8f9fa;
        border-color: #e2e8f0;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Responsive pagination */
    @media (max-width: 768px) {
        #pagination-container {
            justify-content: center;
        }

        #pagination-container .page-item .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.8rem;
            min-width: 38px;
        }
    }

    /* Modal Styles untuk Landscape PDF - FIXED FULLSCREEN */
    .modal-pdf-container {
        height: 85vh;
        /* DIPERBESAR dari 70vh */
        width: 100%;
        background: #f8f9fa;
        border-radius: 0.5rem;
        overflow: hidden;
        position: relative;
        border: 1px solid #dee2e6;
    }

    .modal-pdf-container iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }

    .modal-pdf-container object {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Modal XL untuk PDF - DIPERBESAR */
    .modal-xl {
        max-width: 98%;
        /* DIPERBESAR dari 95% */
        height: 95vh;
        /* DIPERBESAR dari 80vh */
        margin: 2.5vh auto;
    }

    /* FIXED FULLSCREEN - BENAR-BENAR MEMENUHI LAYAR */
    .modal-fullscreen {
        width: 100vw !important;
        height: 100vh !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        position: fixed !important;
    }

    .modal-fullscreen .modal-content {
        height: 100vh;
        width: 100vw;
        border-radius: 0;
        border: none;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .modal-fullscreen .modal-header {
        flex-shrink: 0;
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .modal-fullscreen .modal-body {
        padding: 0;
        flex: 1;
        overflow: hidden;
        background: #f8f9fa;
        height: calc(100vh - 120px);
        /* Header + Footer height */
    }

    .modal-fullscreen .modal-pdf-container {
        height: 100%;
        border-radius: 0;
        border: none;
    }

    .modal-fullscreen .modal-footer {
        flex-shrink: 0;
    }

    /* Pastikan modal backdrop juga fullscreen */
    .modal-fullscreen~.modal-backdrop {
        background-color: rgba(0, 0, 0, 0.8) !important;
    }

    /* Untuk landscape PDF */
    @media (min-width: 1200px) {
        .modal-xl {
            max-width: 1200px;
        }
    }

    /* Responsive modal */
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 95%;
            height: 70vh;
            margin: 2.5vh auto;
        }

        .modal-pdf-container {
            height: 60vh;
        }
    }

    @media (max-width: 576px) {
        .modal-xl {
            max-width: 98%;
            height: 65vh;
            margin: 2.5vh auto;
        }

        .modal-pdf-container {
            height: 55vh;
        }

        .modal-fullscreen .modal-body {
            height: calc(100vh - 140px);
            /* Adjust for mobile */
        }
    }

    /* PDF Loading Styles */
    .pdf-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        flex-direction: column;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }

    .pdf-loading .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.3em;
    }

    .pdf-loading p {
        margin-top: 1rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Fallback styles */
    #pdfFallback {
        display: none;
        justify-content: center;
        align-items: center;
        height: 100%;
        background: #f8f9fa;
        border-radius: 0.5rem;
        flex-direction: column;
        text-align: center;
    }

    #pdfFallback .bi-exclamation-triangle {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    #pdfFallback p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    /* Modal header improvements */
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        padding: 0.75rem 1.5rem;
        flex-shrink: 0;
    }

    .modal-header .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    .modal-header .btn-close {
        filter: invert(1);
        opacity: 0.8;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-footer {
        padding: 0.75rem 1.5rem;
        flex-shrink: 0;
    }

    .modal-body {
        padding: 1rem;
        flex: 1;
        overflow: hidden;
        background: #f8f9fa;
    }

    /* Fullscreen toggle button */
    #fullscreenToggle {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        transition: all 0.2s ease;
    }

    #fullscreenToggle:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-1px);
    }

    /* Smooth transitions */
    .modal-content,
    .modal-dialog {
        transition: all 0.3s ease;
    }

    /* Custom scrollbar for modal */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Fullscreen specific styles */
    body.modal-fullscreen-active {
        overflow: hidden !important;
        padding-right: 0 !important;
    }

    .modal-fullscreen-active .navbar,
    .modal-fullscreen-active .sidebar {
        display: none !important;
    }

    /* Custom scrollbar untuk progress details */
    .progress-details::-webkit-scrollbar {
        width: 6px;
    }

    .progress-details::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .progress-details::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .progress-details::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<div class="main-container">
    <!-- HEADER DENGAN GRADIENT -->
    <header
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 pb-4 border-bottom border-light">
        <div class="mb-4 mb-md-0">
            <h1 class="display-5 fw-bold text-dark mb-2">
                <i class="bi bi-receipt me-3"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                Manajemen Kwitansi
            </h1>
            <p class="text-muted lead fs-6 mb-0">
                Kelola dan pantau semua dokumen kwitansi dalam satu tempat yang terintegrasi
            </p>
        </div>

        <div class="d-flex flex-wrap gap-3">
            <!-- Generate Otomatis -->
            <button class="btn btn-success btn-modern btn-sm fw-semibold d-flex align-items-center"
                id="generate-all-btn">
                <i class="bi bi-magic me-2"></i>
                Generate Otomatis
            </button>
            <!-- Download All -->
            <a href="{{ route('kwitansi.download-all') }}"
                class="btn btn-info btn-modern btn-sm fw-semibold d-flex align-items-center" id="download-all-btn">
                <i class="bi bi-download me-2"></i>
                Download All
            </a>
            <!-- Hapus Semua -->
            <button class="btn btn-danger btn-modern btn-sm fw-semibold d-flex align-items-center" id="delete-all-btn">
                <i class="bi bi-trash me-2"></i>
                Hapus Semua
            </button>
        </div>
    </header>

    <!-- KARTU RINGKASAN/WIDGETS DENGAN ANIMASI -->
    <div class="row g-4 mb-5">
        <!-- Kartu 1: Total Kwitansi (Biru) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-blue p-4 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fs-6 text-secondary fw-semibold mb-2">Total Kwitansi</h3>
                        <p class="h2 fw-bolder text-dark mb-0" id="total-kwitansi">{{ $kwitansis->total() }}</p>
                        <p class="small text-muted mt-1">Update real-time</p>
                    </div>
                    <i class="bi bi-file-earmark-text card-icon"></i>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-blue" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Siap Generate (Hijau) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-green p-4 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fs-6 text-secondary fw-semibold mb-2">Siap Generate</h3>
                        <p class="h2 fw-bolder text-dark mb-0" id="ready-generate">0</p>
                        <p class="small text-muted mt-1">Menunggu proses</p>
                    </div>
                    <i class="bi bi-check-circle card-icon"></i>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 75%"></div>
                </div>
            </div>
        </div>

        <!-- Kartu 3: Dalam Antrian (Kuning) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-yellow p-4 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fs-6 text-secondary fw-semibold mb-2">Dalam Antrian</h3>
                        <p class="h2 fw-bolder text-dark mb-0" id="pending-count">0</p>
                        <p class="small text-muted mt-1">Sedang diproses</p>
                    </div>
                    <i class="bi bi-hourglass-split card-icon"></i>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: 50%"></div>
                </div>
            </div>
        </div>

        <!-- Kartu 4: Gagal Generate (Merah) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card card-red p-4 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fs-6 text-secondary fw-semibold mb-2">Gagal Generate</h3>
                        <p class="h2 fw-bolder text-dark mb-0" id="failed-count">0</p>
                        <p class="small text-muted mt-1">Perlu perbaikan</p>
                    </div>
                    <i class="bi bi-x-octagon card-icon"></i>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: 25%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH DAN FILTER SECTION - DITAMBAH FILTER TANGGAL -->
    <div class="search-container">
        <div class="row g-2 align-items-end">
            <!-- Filter Tanggal Mulai -->
            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label fw-semibold text-dark mb-1 small">
                    <i class="bi bi-calendar-range me-1"></i>Mulai
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-calendar-range text-muted"></i>
                    </span>
                    <input type="date" class="form-control form-control-modern form-control-sm border-start-0"
                        id="startDate" placeholder="Mulai" aria-label="Tanggal mulai">
                </div>
            </div>

            <!-- Filter Tanggal Akhir -->
            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label fw-semibold text-dark mb-1 small">
                    <i class="bi bi-calendar-range me-1"></i>Akhir
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-calendar-range text-muted"></i>
                    </span>
                    <input type="date" class="form-control form-control-modern form-control-sm border-start-0"
                        id="endDate" placeholder="Akhir" aria-label="Tanggal akhir">
                </div>
            </div>

            <!-- Filter Tahun -->
            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label fw-semibold text-dark mb-1 small">
                    <i class="bi bi-funnel me-1"></i>Tahun
                </label>
                @php
                $selectedTahun = request()->input('tahun', $tahunAnggarans->first()->id ?? '');
                @endphp

                <select class="form-select form-select-modern form-select-sm" id="tahunFilter">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunAnggarans as $tahun)
                    <option value="{{ $tahun->id }}" {{ $selectedTahun==$tahun->id ? 'selected' : '' }}>
                        {{ $tahun->tahun_anggaran }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Input -->
            <div class="col-12 col-md-6 col-lg-6">
                <label class="form-label fw-semibold text-dark mb-1 small">
                    <i class="bi bi-search me-1"></i>Pencarian
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                        class="form-control form-control-modern form-control-sm border-start-0 rounded-end-pill"
                        placeholder="Cari uraian, kode rekening, tanggal..." id="searchInput">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill ms-2 px-3" type="button"
                        id="resetFilter">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </button>
                    <button class="btn btn-primary btn-sm rounded-pill ms-2 px-3" type="button" id="clearFilter">
                        <i class="bi bi-x-circle me-1"></i>Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Filter Aktif -->
        <div id="filterInfo" class="mt-3 small" style="display: none;">
            <span class="badge bg-info text-dark me-2">
                <i class="bi bi-funnel me-1"></i> Filter Aktif
            </span>
            <span id="filterText" class="text-muted"></span>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card modern-card p-0 border-0">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <h2 class="fs-4 fw-bold text-dark mb-0 d-flex align-items-center">
                <i class="bi bi-list-ul me-3 text-primary"></i>
                Daftar Kwitansi
                <span class="badge bg-primary ms-3 fs-6" id="table-count">{{ $kwitansis->total() }}</span>
            </h2>
        </div>

        <div class="card-body p-4">
            <!-- TABEL UTAMA DENGAN ID YANG SESUAI DENGAN JAVASCRIPT -->
            <div class="table-responsive">
                <table class="table table-modern table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-dark fw-medium">No</th>
                            <th scope="col" class="text-dark fw-medium">Kode Rekening</th>
                            <th scope="col" class="text-dark fw-medium">Uraian</th>
                            <th scope="col" class="text-dark fw-medium">Tanggal</th>
                            <th scope="col" class="text-dark fw-medium">Jumlah</th>
                            <th scope="col" class="text-center text-dark fw-medium">Aksi</th>
                        </tr>
                    </thead>
                    <!-- TBODY DENGAN ID YANG SESUAI DENGAN JAVASCRIPT -->
                    <tbody id="kwitansi-tbody">
                        @if($kwitansis->count() > 0)
                        @foreach($kwitansis as $index => $kwitansi)
                        <tr id="kwitansi-row-{{ $kwitansi->id }}" class="animate__animated animate__fadeIn">
                            <td class="fw-medium text-dark">
                                {{ ($kwitansis->currentPage() - 1) * $kwitansis->perPage() + $index + 1 }}
                            </td>
                            <td>
                                <span class="badge badge-code">
                                    {{ $kwitansi->rekeningBelanja->kode_rekening ?? '-' }}
                                </span>
                            </td>
                            <td class="text-dark"
                                title="{{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-text me-2 text-muted"></i>
                                    <span class="text-truncate" style="max-width: 300px;">
                                        {{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted">
                                <i class="bi bi-calendar me-2"></i>
                                {{ \Carbon\Carbon::parse($kwitansi->bukuKasUmum->tanggal_transaksi)->format('d/m/Y') }}
                            </td>
                            <td class="fw-bold text-success">
                                <i class="bi bi-currency-dollar me-2"></i>
                                Rp {{ number_format($kwitansi->bukuKasUmum->total_transaksi_kotor, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- Preview Button - Modal Trigger -->
                                    <button class="btn btn-action btn-outline-primary preview-kwitansi"
                                        title="Lihat Preview" data-id="{{ $kwitansi->id }}" data-bs-toggle="modal"
                                        data-bs-target="#previewModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <!-- Download PDF -->
                                    <a href="{{ route('kwitansi.pdf', $kwitansi->id ) }}"
                                        class="btn btn-action btn-outline-success" title="Download PDF"
                                        data-bs-toggle="tooltip" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <!-- Hapus -->
                                    <button class="btn btn-action btn-outline-danger delete-kwitansi"
                                        data-id="{{ $kwitansi->id }}"
                                        data-uraian="{{ $kwitansi->bukuKasUmum->uraian_opsional ?? $kwitansi->bukuKasUmum->uraian }}"
                                        title="Hapus Kwitansi" data-bs-toggle="tooltip">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <!-- EMPTY STATE DALAM TBODY -->
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state" style="padding: 2rem; background: transparent; border: none;">
                                    <i class="bi bi-file-earmark-x empty-state-icon" style="font-size: 3rem;"></i>
                                    <h4 class="text-dark mb-3">Belum ada data kwitansi</h4>
                                    <p class="text-muted mb-4">Mulai dengan membuat kwitansi baru atau generate otomatis
                                        dari data yang tersedia.</p>
                                    <button class="btn btn-primary btn-modern" id="generate-empty-btn">
                                        <i class="bi bi-magic me-2"></i>Generate Kwitansi Otomatis
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION CONTAINER -->
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top border-light">
                <div class="text-muted small mb-3 mb-md-0" id="pagination-info">
                    Menampilkan data
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-simple mb-0" id="pagination-container">
                        <!-- Pagination akan di-generate oleh JavaScript -->
                        @if($kwitansis->count() > 0)
                        {{ $kwitansis->links() }}
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal - DIPERBESAR -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Preview Kwitansi
                </h5>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-light" id="refreshPdf" title="Refresh PDF">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="fullscreenToggle" title="Fullscreen">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-2">
                <!-- Padding diperkecil untuk ruang lebih -->
                <div class="modal-pdf-container">
                    <!-- Loading State -->
                    <div id="pdfLoading" class="pdf-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Memuat dokumen PDF...</p>
                        <small class="text-muted">Mohon tunggu, dokumen sedang diproses</small>
                    </div>

                    <!-- PDF Iframe - DIPERBESAR -->
                    <iframe id="pdfIframe" src="about:blank"
                        style="border: none; display: none; width: 100%; height: 100%;"
                        onload="this.style.display='block'; document.getElementById('pdfLoading').style.display='none';"
                        onerror="this.style.display='none'; document.getElementById('pdfLoading').style.display='none'; document.getElementById('pdfFallback').style.display='flex';">
                    </iframe>

                    <!-- Fallback State -->
                    <div id="pdfFallback" style="display: none;">
                        <div class="text-center">
                            <i class="bi bi-exclamation-triangle text-warning mb-3"></i>
                            <h5 class="text-dark mb-3">Tidak dapat memuat PDF</h5>
                            <p class="text-muted mb-4">Browser tidak mendukung preview PDF atau terjadi kesalahan.</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="#" id="fallbackDownload" class="btn btn-primary" target="_blank">
                                    <i class="bi bi-download me-2"></i>Download PDF
                                </a>
                                <button class="btn btn-outline-secondary" onclick="location.reload()">
                                    <i class="bi bi-arrow-repeat me-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto" id="pdfInfo">PDF akan dimuat dalam beberapa detik...</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Tutup
                </button>
                <a href="#" id="downloadPdf" class="btn btn-success" target="_blank">
                    <i class="bi bi-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="position-fixed top-50 start-50 translate-middle" id="global-loading" style="display: none; z-index: 9999;">
    <div class="d-flex align-items-center bg-white rounded p-3 shadow">
        <div class="spinner-border text-primary me-3" role="status"></div>
        <span class="text-dark">Memproses...</span>
    </div>
</div>

@push('scripts')
<!-- Include Animate.css for animations -->
<link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">

<!-- Include Kwitansi JavaScript -->
<script src="{{ asset('assets/js/kwitansi.js') }}"></script>

<script>
    // Fallback untuk CSRF token
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection