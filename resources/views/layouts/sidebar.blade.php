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
                <a href="{{ route('dashboard.dashboard') }}" class="nav-link" data-page="dashboard">
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
                        <a href="{{ route('kop-sekolah.index') }}" class="nav-link" data-page="comp-kop">Kop Sekolah
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

            <!-- penatusahaan -->
            <li class="nav-item">
                <a href="{{ route('penatausahaan.penatausahaan') }}" class="nav-link" data-page="penatausahaan">
                    <i class="bi bi-house-add nav-icon"></i>
                    <span class="nav-text">Penatausahaan</span>
                </a>
            </li>

            <!-- Components -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="submenu" data-target="laporan-submenu">
                    <i class="bi bi-puzzle nav-icon"></i>
                    <span class="nav-text">Laporan</span>
                    <i class="bi bi-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu list-unstyled" id="laporan-submenu">
                    <li><a href="{{route('kwitansi.index')}}" class="nav-link" data-page="comp-kwitansi">Kwitansi</a>
                    </li>
                </ul>
            </li>

            <!-- Backup dan Restore -->
            <li class="nav-item">
                <a href="{{ route('backup.index') }}" class="nav-link" data-page="backup">
                    <i class="bi bi-database-fill nav-icon"></i>
                    <span class="nav-text">Backup & Restore</span>
                </a>
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
