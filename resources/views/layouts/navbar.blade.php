@section('navbar')
    <header class="header">
        <div class="header-content">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <form method="GET" action="{{ request()->url() }}" class="ms-3">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" placeholder="Cari sesuatu..."
                            value="{{ request('search') }}">
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
                        <li><a class="dropdown-item" href="pages/profile.html"><i class="bi bi-person me-2"></i>Profile</a>
                        </li>
                        <li><a class="dropdown-item" href="pages/settings.html"><i class="bi bi-gear me-2"></i>Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
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
@endsection
