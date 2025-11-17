@extends('layouts.app')

@section('title', 'SPMTH - Dalam Pengembangan')

@section('content')
@include('layouts.sidebar')
@include('layouts.navbar')

<div class="content-wrapper">
    <!-- Wide Banner Section -->
    <section class="development-banner">
        <div class="container-fluid px-0">
            <div class="banner-content">
                <div class="banner-overlay">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <!-- Animated Title -->
                                <h1 class="banner-title">
                                    <span class="title-main">SPTJ</span>
                                </h1>
                                <p class="banner-description">
                                    Modul SPTJ sedang dalam tahap pengembangan aktif. Kami membangun solusi terbaik
                                    dengan teknologi terkini untuk kebutuhan pengelolaan data Anda.
                                </p>

                                <!-- Progress & Stats -->
                                <div class="banner-stats">
                                    <div class="stat-item">
                                        <div class="stat-number">50%</div>
                                        <div class="stat-label">Progress Development</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">Q4</div>
                                        <div class="stat-label">Target Rilis 2026</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">1</div>
                                        <div class="stat-label">Tim Developer</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center">
                                <!-- Animated Illustration -->
                                <div class="banner-illustration">
                                    <div class="floating-gear gear-1">
                                        <i class="bi bi-gear-fill"></i>
                                    </div>
                                    <div class="floating-gear gear-2">
                                        <i class="bi bi-gear"></i>
                                    </div>
                                    <div class="main-icon">
                                        <i class="bi bi-rocket-takeoff"></i>
                                    </div>
                                    <div class="pulse-effect"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Development Progress -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card progress-card shadow border-0">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="bi bi-graph-up-arrow me-2"></i>
                                Development Progress
                            </h4>

                            <!-- Main Progress -->
                            <div class="main-progress mb-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Overall Progress</span>
                                    <span class="fw-bold text-primary">50% Complete</span>
                                </div>
                                <div class="progress main-progress-bar">
                                    <div class="progress-bar bg-gradient-primary" style="width: 50%">
                                        <span class="progress-text">50%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="cta-card bg-gradient-primary text-white text-center">
                        <div class="cta-content">
                            <h3 class="cta-title">Siap untuk Pengalaman Terbaik?</h3>
                            <p class="cta-description">
                                Kami akan memberitahu Anda ketika SPTJ siap diluncurkan.
                                Dapatkan akses early bird dan fitur eksklusif.
                            </p>
                            <div class="cta-buttons">
                                <a href="{{ route('dashboard.dashboard') }}"
                                    class="btn btn-light btn-lg rounded-pill px-4 me-3">
                                    <i class="bi bi-house me-2"></i>Kembali ke Dashboard
                                </a>
                                <button class="btn btn-outline-light btn-lg rounded-pill px-4">
                                    <i class="bi bi-bell me-2"></i>Notify Me
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    /* Banner Styles */
    .development-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .banner-content {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: center;
    }

    .banner-overlay {
        width: 100%;
        padding: 80px 0;
        background: rgba(0, 0, 0, 0.3);
    }

    .banner-title {
        color: white;
        margin-bottom: 1.5rem;
    }

    .title-main {
        display: block;
        font-size: 4rem;
        font-weight: 900;
        line-height: 1;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        background: linear-gradient(45deg, #fff, #f0f4ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .title-sub {
        display: block;
        font-size: 1.5rem;
        font-weight: 300;
        opacity: 0.9;
        margin-top: 0.5rem;
    }

    .banner-description {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.2rem;
        line-height: 1.6;
        max-width: 600px;
        margin-bottom: 2rem;
    }

    .banner-stats {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .stat-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    /* Banner Illustration */
    .banner-illustration {
        position: relative;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .main-icon {
        font-size: 5rem;
        color: white;
        animation: float 3s ease-in-out infinite;
    }

    .floating-gear {
        position: absolute;
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .gear-1 {
        top: 20px;
        left: 30px;
        animation: spin 8s linear infinite;
    }

    .gear-2 {
        bottom: 30px;
        right: 40px;
        animation: spin-reverse 6s linear infinite;
    }

    .pulse-effect {
        position: absolute;
        width: 150px;
        height: 150px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        animation: pulse 2s ease-out infinite;
    }

    /* Animations */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes spin-reverse {
        0% {
            transform: rotate(360deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(0.8);
            opacity: 1;
        }

        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    /* Tech Cards */
    .tech-card {
        background: white;
        padding: 2rem 1.5rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .tech-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .tech-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: white;
        font-size: 1.8rem;
    }

    .tech-card h5 {
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1e293b;
    }

    .tech-card p {
        color: #64748b;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    /* Progress Cards */
    .progress-card {
        border-radius: 20px;
        background: white;
    }

    .main-progress-bar {
        height: 20px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f5f9;
    }

    .main-progress-bar .progress-bar {
        border-radius: 10px;
        position: relative;
        background: linear-gradient(45deg, #4f46e5, #7c3aed);
    }

    .progress-text {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .sub-progress {
        margin-bottom: 1.5rem;
    }

    .sub-progress-info {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .sub-progress-label {
        font-weight: 600;
        color: #475569;
    }

    .sub-progress-percent {
        font-weight: 700;
        color: #1e293b;
    }

    /* CTA Section */
    .cta-card {
        border-radius: 25px;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .cta-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1%, transparent 1%);
        background-size: 20px 20px;
        animation: moveBackground 20s linear infinite;
    }

    @keyframes moveBackground {
        0% {
            transform: translate(0, 0);
        }

        100% {
            transform: translate(20px, 20px);
        }
    }

    .cta-content {
        position: relative;
        z-index: 2;
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .cta-description {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .section-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #1e293b;
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(45deg, #4f46e5, #7c3aed);
        border-radius: 2px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .title-main {
            font-size: 2.5rem;
        }

        .title-sub {
            font-size: 1.2rem;
        }

        .banner-stats {
            flex-direction: column;
            gap: 1rem;
        }

        .banner-description {
            font-size: 1rem;
        }

        .cta-title {
            font-size: 2rem;
        }
    }
</style>

@endsection