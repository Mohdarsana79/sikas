@section('sidebar')
<nav class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand d-flex align-items-center">
        <div class="brand-icon">
        <img src="{{ asset('assets/images/large.png') }}" alt="logo" width="60">
        </div>
        <span class="sidebar-brand-text">$IKAS</span>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav list-unstyled" id="sidebarNav">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('dashboard.dashboard') }}" class="nav-link" data-page="dashboard" data-tooltip="Dashboard">
                <i class="bi bi-speedometer2 nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <!-- Referensi -->
        <li class="nav-item">
            <a href="#" class="nav-link" data-toggle="submenu" data-target="referensi-submenu">
                <i class="bi bi-book nav-icon"></i>
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
                <li>
                    <a href="{{ route('referensi.rekening-belanja.index') }}" class="nav-link"
                        data-page="comp-rekeningBelanja">Rekening Belanja</a>
                </li>
            </ul>
        </li>

        <!-- Penganggaran -->
        <li class="nav-item">
            <a href="{{ route('penganggaran.index') }}" class="nav-link" data-page="penganggaran">
                <i class="bi bi-currency-bitcoin nav-icon"></i>
                <span class="nav-text">Penganggaran</span>
            </a>
        </li>

        <!-- penatusahaan -->
        <li class="nav-item">
            <a href="{{ route('penatausahaan.penatausahaan') }}" class="nav-link" data-page="penatausahaan">
                <i class="bi bi-bar-chart-line-fill nav-icon"></i>
                <span class="nav-text">Penatausahaan</span>
            </a>
        </li>

        <!-- STS -->
        <li class="nav-item">
            <a href="{{ route('sts.index') }}" class="nav-link" data-page="sts">
                <i class="bi bi-wallet2 nav-icon"></i>
                <span class="nav-text">STS</span>
            </a>
        </li>

        <!-- Components -->
        <li class="nav-item">
            <a href="#" class="nav-link" data-toggle="submenu" data-target="laporan-submenu">
                <i class="bi bi-file-earmark-zip nav-icon"></i>
                <span class="nav-text">Laporan</span>
                <i class="bi bi-chevron-right nav-arrow"></i>
            </a>
            <ul class="nav-submenu list-unstyled" id="laporan-submenu">
                <li>
                    <a href="{{route('kwitansi.index')}}" class="nav-link" data-page="comp-kwitansi">Kwitansi</a>
                </li>
                <li>
                    <a href="{{ route('tanda-terima.index') }}" class="nav-link" data-page="comp-tanda-terima">Tanda Terima</a>
                </li>
                <li>
                    <a href="{{route('spmth.index')}}" class="nav-link" data-page="comp-sp2b">SPMTH</a>
                </li>
                <li>
                    <a href="{{route('sptj.index')}}" class="nav-link" data-page="comp-sptj">SPTJ</a>
                </li>
                <li>
                    <a href="{{route('laporan-realisasi.index')}}" class="nav-link" data-page="comp-sp2b">Lap.
                        Realisasi</a>
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
    </ul>
</nav>
@endsection