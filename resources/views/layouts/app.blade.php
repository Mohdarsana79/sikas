<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aplikasi SIKAS</title>
    <link rel="icon" type="image/x-icon" width="100" href="{{ asset('assets/images/large.png') }}">

    <!-- CSS Files -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}" class="stylesheet">
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/auth.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/loading.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="{{ asset('assets/css/font-family-inter.css') }}" rel="stylesheet">

    
</head>

<body data-has-perubahan="{{ isset($hasPerubahan) && $hasPerubahan ? 'true' : 'false' }}">
    <!-- Sidebar -->
    @yield('sidebar')

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Header -->
        @yield('navbar')

        <!-- Content Area -->
        @yield('content')
    </main>

    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-backdrop d-md-none hidden" id="sidebar-backdrop"></div>

    <!-- Loading Spinner - GLASS MORPHISM STYLE -->
    <div id="globalLoading" class="loading-overlay-dark">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-logo">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="loading-text">SIKAS</div>
            <div class="loading-subtext">Sedang memuat...</div>
            <div class="loading-progress">
                <div class="loading-progress-bar"></div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/apexcharts.min.js') }}"></script>

    <!-- Scripts -->
    @if (request()->is('login') || request()->is('register'))
    <script src="{{ asset('assets/js/auth.js') }}"></script>
    @else
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @endif

    <script src="{{ asset('assets/js/search.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>

    @stack('scripts')

    <script>
        // Notifikasi Success
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif

        // Notifikasi Error
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session('error') }}',
            });
        @endif

        // Notifikasi Validasi Error
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
            });
        @endif
    </script>

    <!-- SIMPLE LOADING HANDLER - PASTI BEKERJA -->
    <script>
        // Simple global functions
        function showGlobalLoading() {
            console.log('🔄 Showing global loading');
            const loader = document.getElementById('globalLoading');
            if (loader) {
                loader.classList.add('show');
                document.body.classList.add('loading-active');
            }
        }

        function hideGlobalLoading() {
            console.log('🔄 Hiding global loading');
            const loader = document.getElementById('globalLoading');
            if (loader) {
                loader.classList.remove('show');
                document.body.classList.remove('loading-active');
            }
        }

        // Test function
        window.testLoading = function() {
            console.log('🧪 Testing loading...');
            showGlobalLoading();
            setTimeout(hideGlobalLoading, 3000);
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM Ready - Initializing loading handler');
            console.log('🔍 Global loading element:', document.getElementById('globalLoading'));
            
            // Event listener for sidebar clicks - SIMPLE VERSION
            document.addEventListener('click', function(e) {
                // Find the closest anchor tag
                let target = e.target;
                while (target && target.tagName !== 'A') {
                    target = target.parentElement;
                    if (!target) return;
                }

                const href = target.getAttribute('href');
                console.log('🔗 Click detected:', href);
                
                // Check if it's in sidebar
                if (!target.closest('.sidebar')) {
                    console.log('⏩ Not in sidebar, skipping');
                    return;
                }

                // Skip conditions
                if (target.getAttribute('data-toggle') === 'submenu' || 
                    !href || 
                    href === '#' || 
                    href.startsWith('javascript:') ||
                    href === window.location.pathname ||
                    href.startsWith('http')) {
                    console.log('⏩ Skipping - not page navigation');
                    return;
                }

                console.log('🎯 Navigation detected - showing loading');
                showGlobalLoading();

                // Safety timeout
                setTimeout(hideGlobalLoading, 15000);
            });

            // Hide loading on page load
            window.addEventListener('load', function() {
                console.log('📄 Page loaded - hiding loading');
                hideGlobalLoading();
            });

            // Also hide after 3 seconds as safety
            setTimeout(hideGlobalLoading, 3000);

            console.log('✅ Loading handler initialized');
            console.log('💡 Type testLoading() to test the loader');
        });

        // Make functions globally available
        window.showGlobalLoading = showGlobalLoading;
        window.hideGlobalLoading = hideGlobalLoading;
    </script>

    <script>
        // ULTIMATE LOADING FIX
    console.log('🔧 Ultimate loading fix installed');
    
    // Force show loading for testing
    window.forceShowLoading = function() {
        document.getElementById('globalLoading').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        console.log('💥 FORCE SHOW LOADING');
    }
    
    window.forceHideLoading = function() {
        document.getElementById('globalLoading').style.display = 'none';
        document.body.style.overflow = '';
        console.log('💥 FORCE HIDE LOADING');
    }
    
    // Override all link clicks in sidebar
    document.querySelectorAll('.sidebar a[href]').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            console.log('🎯 DIRECT CLICK HANDLER:', href);
            
            if (this.getAttribute('data-toggle') === 'submenu' || 
                href === '#' || 
                href === window.location.pathname) {
                return; // Allow default behavior
            }
            
            e.preventDefault();
            console.log('🎯 SHOWING LOADING AND NAVIGATING');
            
            // Show loading
            document.getElementById('globalLoading').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Navigate after short delay to ensure loading shows
            setTimeout(() => {
                window.location.href = href;
            }, 100);
        });
    });
    
    console.log('✅ Ultimate fix applied to', document.querySelectorAll('.sidebar a[href]').length, 'links');
    console.log('💥 Type forceShowLoading() to test');
    </script>

    @extends('layouts.footer')
</body>

</html>