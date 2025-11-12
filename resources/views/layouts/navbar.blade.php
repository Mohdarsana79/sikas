@section('navbar')
<header class="header">
    <div class="header-content">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle" id="sidebarToggle" data-drawer-toggle="sidebar">
                <i class="bi bi-list"></i>
            </button>

            <!-- Form Search Global -->
            <form method="GET" action="#" class="ms-3" id="globalSearchForm">
                <div class="search-box position-relative">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control" id="globalSearchInput" name="search" placeholder="Cari..."
                        value="{{ request('search') }}" autocomplete="off" title="Cari data berdasarkan kata kunci">
                    <div class="search-loading d-none position-absolute"
                        style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="header-actions">
            <button class="notification-btn">
                <i class="bi bi-bell"></i>
                <span class="notification-badge"></span>
            </button>

            <div class="dropdown">
                <button class="profile-btn dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=32&h=32&fit=crop&crop=face"
                        alt="Profile" class="profile-avatar">
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left;">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<style>
    .search-box {
        position: relative;
        min-width: 300px;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 3;
    }

    .search-box .form-control {
        padding-left: 35px;
        padding-right: 35px;
        border-radius: 20px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }

    .search-box .form-control:focus {
        background-color: #fff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .search-loading {
        z-index: 4;
    }

    .search-clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        z-index: 4;
    }

    .search-clear:hover {
        color: #dc3545;
    }

    /* Search result styling */
    .search-result-info {
        font-size: 0.875rem;
        border-left: 4px solid #0dcaf0;
    }

    .search-result-row {
        background-color: #f8f9fa !important;
        border-left: 3px solid #0d6efd;
    }

    .search-result-row:hover {
        background-color: #e9ecef !important;
    }
</style>
@endsection