@section('sidebar')
    <nav class="sidebar" id="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand d-flex align-items-center">
            <div class="brand-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>
            <span class="sidebar-brand-text">SIKAS</span>
        </div>

        <!-- Navigation -->
        <ul class="sidebar-nav list-unstyled">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link active" data-page="dashboard">
                    <i class="bi bi-speedometer2 nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Referensi -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="submenu" data-target="referensi-submenu">
                    <i class="bi bi-puzzle nav-icon"></i>
                    <span class="nav-text">Referensi</span>
                    <i class="bi bi-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu list-unstyled" id="referensi-submenu">
                    <li>
                        <a href="{{ route('sekolah.index') }}" class="nav-link" data-page="comp-sekolah">Sekolah
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('referensi.kode-kegiatan.index') }}" class="nav-link"
                            data-page="comp-kodeKegiatan">Kode Kegiatan
                        </a>
                    </li>
                    <li><a href="{{ route('referensi.rekening-belanja.index') }}" class="nav-link"
                            data-page="comp-rekeningBelanja">Rekening Belanja</a>
                    </li>
                </ul>
            </li>

            <!-- Penganggaran -->
            <li class="nav-item">
                <a href="{{ route('penganggaran.index') }}" class="nav-link" data-page="penganggaran">
                    <i class="bi bi-house-add nav-icon"></i>
                    <span class="nav-text">Penganggaran</span>
                </a>
            </li>

            <!-- Components -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="submenu" data-target="components-submenu">
                    <i class="bi bi-puzzle nav-icon"></i>
                    <span class="nav-text">Laporan</span>
                    <i class="bi bi-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu list-unstyled" id="components-submenu">
                    <li><a href="pages/components/accordion.html" class="nav-link" data-page="comp-accordion">Kwitansi</a>
                    </li>
                </ul>
            </li>

            <!-- Profile -->
            <li class="nav-item">
                <a href="pages/profile.html" class="nav-link" data-page="profile">
                    <i class="bi bi-person nav-icon"></i>
                    <span class="nav-text">Profile</span>
                </a>
            </li>

            <!-- Settings -->
            <li class="nav-item">
                <a href="pages/settings.html" class="nav-link" data-page="settings">
                    <i class="bi bi-gear nav-icon"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
        </ul>
    </nav>
@endsection
