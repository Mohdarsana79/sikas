@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
<div class="content-area" id="contentArea">
    <!-- Dashboard Content -->
    <div class="fade-in">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gradient">Buku Kas Umum (BKU)</h1>
                <p class="mb-0 text-muted">Kelola buku kas umum sekolah Anda.</p>
            </div>

            <!-- Tahun Selector -->
            <div class="d-flex align-items-center">
                <label class="me-2">Tahun:</label>
                <select class="form-select form-select-sm" id="tahunSelector" style="width: 120px;">
                    @foreach($tahunList as $tahunOption)
                    <option value="{{ $tahunOption }}" {{ $tahunOption==$tahun ? 'selected' : '' }}>
                        {{ $tahunOption }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Card BKU -->
        <div class="card">
            <div class="card-body p-0">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs border-bottom-0" id="bkuTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bosp-reguler-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-reguler" type="button" role="tab">
                            BOSP Reguler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-daerah-tab" data-bs-toggle="tab" data-bs-target="#bosp-daerah"
                            type="button" role="tab">
                            BOSP Daerah
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bosp-kinerja-tab" data-bs-toggle="tab"
                            data-bs-target="#bosp-kinerja" type="button" role="tab">
                            BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="silpa-bosp-tab" data-bs-toggle="tab" data-bs-target="#silpa-bosp"
                            type="button" role="tab">
                            SiLPA BOSP Kinerja
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lainnya-tab" data-bs-toggle="tab" data-bs-target="#lainnya"
                            type="button" role="tab">
                            Lainnya
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="bkuTabsContent">
                    <!-- BOSP Reguler Tab -->
                    <div class="tab-pane fade show active" id="bosp-reguler" role="tabpanel">
                        <div class="card bg-body-secondary ms-3 me-3 mt-3 mb-3">
                            <div class="p-4">
                                @if($penganggaran)
                                <div class="mb-4">
                                    <h5 class="fw-bold">BKU BOSP Reguler {{ $tahun }}</h5>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        Laporkan minimal 50% belanja dana tahap 2 paling lambat 31 Agustus {{ $tahun }}
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mb-4">
                                    <div>
                                        <button class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-printer me-2"></i>Cetak
                                        </button>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#tambahPenerimaanModal" id="mainTambahButton">
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Penerimaan Dana
                                        </button>
                                    </div>
                                </div>
                                <!-- Tampilkan data penerimaan dana yang sudah ada -->
                                <div id="daftarPenerimaanDana" class="mb-4">
                                    <!-- Data akan diisi melalui JavaScript -->
                                </div>
                                <div class="row">
                                    <!-- Month Cards -->
                                    @php
                                    $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    @endphp;

                                    @foreach($bulanList as $bulan)
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('bku.showByBulan', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="text-decoration-none">
                                            <div class="card h-100">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">{{ $bulan }}</h6>
                                                    @if(in_array($bulan, $bulanWithRkas))
                                                    <span class="badge bg-success">Sudah Selesai</span>
                                                    @else
                                                    <span class="badge bg-secondary">Belum Dibuat</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="text-center mt-4">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat me-2"></i>Perbarui Data BKU {{ $tahun }}
                                    </button>
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Tidak ada data RKAS untuk tahun {{ $tahun }}</p>
                                    <a href="{{ route('rkas.index') }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-plus-circle me-2"></i>Buat RKAS
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- BOSP Daerah Tab -->
                    <div class="tab-pane fade" id="bosp-daerah" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data BOSP Daerah</p>
                            </div>
                        </div>
                    </div>

                    <!-- BOSP Kinerja Tab -->
                    <div class="tab-pane fade" id="bosp-kinerja" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data BOSP Kinerja</p>
                            </div>
                        </div>
                    </div>

                    <!-- SiLPA BOSP Kinerja Tab -->
                    <div class="tab-pane fade" id="silpa-bosp" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data SiLPA BOSP Kinerja</p>
                            </div>
                        </div>
                    </div>

                    <!-- Lainnya Tab -->
                    <div class="tab-pane fade" id="lainnya" role="tabpanel">
                        <div class="p-4">
                            <div class="text-center py-5">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Belum ada data lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Penerimaan Dana -->
<div class="modal fade" id="tambahPenerimaanModal" tabindex="-1" aria-labelledby="tambahPenerimaanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahPenerimaanModalLabel">Tambah Penerimaan Dana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Container untuk daftar penerimaan dana -->
                <div id="daftarPenerimaanModal">
                    <!-- Data akan diisi melalui JavaScript -->
                </div>

                <!-- Form untuk tambah penerimaan baru -->
                <div id="formTambahPenerimaan" style="display: none;">
                    <hr>
                    <h5 class="mb-3">Tambah Penerimaan</h5>
                    <form id="formPenerimaanDana">
                        <input type="hidden" name="penganggaran_id" value="{{ $penganggaran->id ?? '' }}">

                        <div class="mb-3">
                            <label class="form-label">Sumber Dana</label>
                            <select class="form-select" name="sumber_dana" id="sumberDana" required>
                                <option value="">Pilih Sumber Dana</option>
                                <option value="Bosp Reguler Tahap 1">BOSP Reguler Tahap 1</option>
                                <option value="Bosp Reguler Tahap 2">BOSP Reguler Tahap 2</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Terima</label>
                            <input type="date" class="form-control" name="tanggal_terima" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Dana</label>
                            <input type="text" class="form-control" name="jumlah_dana" id="jumlahDana" required>
                        </div>

                        <div class="mb-3" id="saldoAwalContainer">
                            <label class="form-label">Saldo Awal</label>
                            <input type="text" class="form-control" name="saldo_awal" id="saldoAwal">
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="btnBatalTambah">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah Penerimaan (akan ditampilkan jika belum mencapai batas maksimal) -->
                <div id="tombolTambahContainer" class="text-center mt-3">
                    <button type="button" class="btn btn-outline-primary" id="btnTampilkanForm">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Penerimaan
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .penerimaan-item {
        padding: 12px;
        border-radius: 8px;
        background-color: #f8f9fa;
        margin-bottom: 15px;
    }

    .penerimaan-item strong {
        font-size: 16px;
        color: #333;
        display: block;
        margin-bottom: 5px;
    }

    .penerimaan-item p {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .penerimaan-item h5 {
        font-size: 18px;
        font-weight: bold;
        color: #198754;
        margin-bottom: 0;
    }

    #daftarPenerimaanModal {
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
    }

    #tombolTambahContainer {
        margin-top: 20px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    #formTambahPenerimaan {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    #saldoAwalContainer {
        transition: all 0.3s ease;
    }

    #btnTampilkanForm {
        transition: all 0.3s ease;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Format currency
        function formatRupiah(angka) {
            if (!angka) return '';
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }
        
        // Unformat currency
        function unformatRupiah(rupiah) {
            return parseInt(rupiah.replace(/[^0-9]/g, ''));
        }
        
        // Format input jumlah dana
        const jumlahDanaInput = document.getElementById('jumlahDana');
        if (jumlahDanaInput) {
            jumlahDanaInput.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }
        
        // Format input saldo awal
        const saldoAwalInput = document.getElementById('saldoAwal');
        if (saldoAwalInput) {
            saldoAwalInput.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }
        
        // Fungsi untuk menampilkan/menyembunyikan form saldo awal
        function toggleSaldoAwalField() {
            const sumberDanaSelect = document.getElementById('sumberDana');
            const saldoAwalContainer = document.getElementById('saldoAwalContainer');
            
            if (sumberDanaSelect && saldoAwalContainer) {
                // Tampilkan saldo awal hanya untuk BOSP Reguler Tahap 1
                if (sumberDanaSelect.value === 'Bosp Reguler Tahap 1') {
                    saldoAwalContainer.style.display = 'block';
                } else {
                    saldoAwalContainer.style.display = 'none';
                    // Reset nilai saldo awal jika bukan tahap 1
                    const saldoAwalInput = document.getElementById('saldoAwal');
                    if (saldoAwalInput) {
                        saldoAwalInput.value = '';
                    }
                }
            }
        }
        
        // Event listener untuk perubahan pilihan sumber dana
        const sumberDanaSelect = document.getElementById('sumberDana');
        if (sumberDanaSelect) {
            sumberDanaSelect.addEventListener('change', toggleSaldoAwalField);
            
            // Panggil sekali saat halaman dimuat untuk set status awal
            toggleSaldoAwalField();
        }
        
        // Tampilkan form tambah penerimaan
        const btnTampilkanForm = document.getElementById('btnTampilkanForm');
        if (btnTampilkanForm) {
            btnTampilkanForm.addEventListener('click', function() {
                document.getElementById('formTambahPenerimaan').style.display = 'block';
                document.getElementById('tombolTambahContainer').style.display = 'none';
            });
        }
        
        // Batal tambah penerimaan
        const btnBatalTambah = document.getElementById('btnBatalTambah');
        if (btnBatalTambah) {
            btnBatalTambah.addEventListener('click', function() {
                document.getElementById('formTambahPenerimaan').style.display = 'none';
                document.getElementById('tombolTambahContainer').style.display = 'block';
                document.getElementById('formPenerimaanDana').reset();
                toggleSaldoAwalField(); // Reset tampilan saldo awal
            });
        }
        
        // Form submission
        const formPenerimaanDana = document.getElementById('formPenerimaanDana');
        if (formPenerimaanDana) {
            formPenerimaanDana.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Jika saldo_awal disembunyikan (Tahap 2), set nilai ke string kosong
                const saldoAwalContainer = document.getElementById('saldoAwalContainer');
                if (saldoAwalContainer && saldoAwalContainer.style.display === 'none') {
                    formData.set('saldo_awal', '');
                }
                
                // Konversi nilai ke format angka
                formData.set('jumlah_dana', unformatRupiah(formData.get('jumlah_dana')));
                
                // Hanya proses saldo_awal jika tidak disembunyikan
                if (saldoAwalContainer && saldoAwalContainer.style.display !== 'none') {
                    const saldoAwalValue = formData.get('saldo_awal');
                    if (saldoAwalValue) {
                        formData.set('saldo_awal', unformatRupiah(saldoAwalValue));
                    } else {
                        formData.set('saldo_awal', '');
                    }
                }
                
                // Kirim data ke server
                fetch('{{ route("penerimaan-dana.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh daftar penerimaan dana
                        loadPenerimaanDana();
                        
                        // Reset dan sembunyikan form
                        document.getElementById('formPenerimaanDana').reset();
                        document.getElementById('formTambahPenerimaan').style.display = 'none';
                        document.getElementById('tombolTambahContainer').style.display = 'block';
                        
                        // Reset tampilan saldo awal
                        toggleSaldoAwalField();
                        
                        // Periksa apakah sudah ada BOSP Reguler Tahap 2
                        checkJumlahPenerimaan();
                        
                        // Tampilkan notifikasi sukses
                        alert('Penerimaan dana berhasil disimpan');
                        
                        // Jika yang ditambahkan adalah BOSP Reguler Tahap 2, tutup modal
                        const sumberDana = formData.get('sumber_dana');
                        if (sumberDana === 'Bosp Reguler Tahap 2') {
                            // Tutup modal setelah 1 detik
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('tambahPenerimaanModal'));
                                if (modal) {
                                    modal.hide();
                                }
                            }, 1000);
                        }
                    } else {
                        alert('Gagal menyimpan data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data');
                });
            });
        }
        
        // Load data penerimaan dana
        function loadPenerimaanDana() {
            const penganggaranId = {{ $penganggaran->id ?? 0 }};
            const container = document.getElementById('daftarPenerimaanModal');
            
            if (!container) {
                console.error('Container daftarPenerimaanModal tidak ditemukan');
                return;
            }
            
            if (penganggaranId) {
                console.log('Loading data for penganggaran ID:', penganggaranId);
                
                // Tampilkan loading indicator
                container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p>Memuat data...</p></div>';
                
                fetch('{{ route("penerimaan-dana.getByPenganggaran", ["penganggaran_id" => $penganggaran->id ?? 0]) }}')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    updateTampilanPenerimaanDana(data);
                })
                .catch(error => {
                    console.error('Error loading penerimaan dana:', error);
                    container.innerHTML = 
                        '<div class="alert alert-danger">Error memuat data penerimaan dana. Silakan refresh halaman dan coba lagi.</div>';
                });
            } else {
                console.error('No penganggaran ID found');
                container.innerHTML = 
                    '<div class="alert alert-warning">Tidak ada data penganggaran untuk tahun ini.</div>';
            }
        }
        
        // Update tampilan daftar penerimaan dana
        function updateTampilanPenerimaanDana(data) {
            const container = document.getElementById('daftarPenerimaanModal');
            
            if (!container) {
                console.error('Container daftarPenerimaanModal tidak ditemukan');
                return;
            }
            
            // Cek jika data adalah error response
            if (data && data.error) {
                container.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Belum ada penerimaan dana</p>';
                
                // Tampilkan tombol tambah jika belum ada data
                const tombolTambahContainer = document.getElementById('tombolTambahContainer');
                if (tombolTambahContainer) {
                    tombolTambahContainer.style.display = 'block';
                }
                return;
            }
            
            let html = '';
            
            data.forEach((item, index) => {
                // Pastikan properti yang diakses ada
                const sumberDana = item.sumber_dana || 'Sumber Dana Tidak Diketahui';
                const tanggalTerima = item.tanggal_terima ? new Date(item.tanggal_terima) : new Date();
                const jumlahDana = item.jumlah_dana || 0;
                
                // Format tanggal
                const formattedDate = tanggalTerima.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
                
                // Format jumlah dana
                const formattedAmount = 'Rp. ' + parseInt(jumlahDana).toLocaleString('id-ID');
                
                // Buat card untuk setiap penerimaan dana
                html += `
                <div class="penerimaan-item mb-3">
                    <strong>${sumberDana}</strong>
                    <p class="mb-1">${formattedDate}</p>
                    <h5 class="text-success">${formattedAmount}</h5>
                </div>`;
                
                // Tambahkan garis pemisah kecuali untuk item terakhir
                if (index < data.length - 1) {
                    html += '<hr>';
                }
            });
            
            container.innerHTML = html;
            
            // Periksa jumlah penerimaan setelah load
            checkJumlahPenerimaan();
        }
        
        // Periksa jumlah penerimaan dan sembunyikan tombol jika sudah ada BOSP Reguler Tahap 2
        function checkJumlahPenerimaan() {
            const penganggaranId = {{ $penganggaran->id ?? 0 }};
            const tombolTambahContainer = document.getElementById('tombolTambahContainer');
            const btnTampilkanForm = document.getElementById('btnTampilkanForm');
            const mainTambahButton = document.getElementById('mainTambahButton');
            
            if (!tombolTambahContainer || !btnTampilkanForm || !mainTambahButton) {
                console.error('Container tombolTambahContainer, btnTampilkanForm, atau mainTambahButton tidak ditemukan');
                return;
            }
            
            if (penganggaranId) {
                fetch('{{ route("penerimaan-dana.getByPenganggaran", ["penganggaran_id" => $penganggaran->id ?? 0]) }}')
                .then(response => response.json())
                .then(data => {
                    // Cek jika data adalah error response
                    if (data && data.error) {
                        console.error('Error checking penerimaan dana:', data.error);
                        return;
                    }
                    
                    // Cek jika sudah ada BOSP Reguler Tahap 2
                    const hasTahap2 = data.some(item => item.sumber_dana === 'Bosp Reguler Tahap 2');
                    const hasTwoItems = data.length >= 2;
                    
                    if (hasTahap2 || hasTwoItems) {
                        tombolTambahContainer.style.display = 'none';
                        btnTampilkanForm.style.display = 'none';
                        mainTambahButton.style.display = 'none';
                    } else {
                        tombolTambahContainer.style.display = 'block';
                        btnTampilkanForm.style.display = 'block';
                        mainTambahButton.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error checking penerimaan dana:', error);
                });
            }
        }
        
        // Reset modal ketika ditutup
        const modal = document.getElementById('tambahPenerimaanModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                const form = document.getElementById('formPenerimaanDana');
                const formTambah = document.getElementById('formTambahPenerimaan');
                const tombolTambah = document.getElementById('tombolTambahContainer');
                
                if (form) form.reset();
                if (formTambah) formTambah.style.display = 'none';
                if (tombolTambah) tombolTambah.style.display = 'block';
                
                // Reset tampilan saldo awal
                toggleSaldoAwalField();
            });
        }
        
        // Load data saat modal dibuka
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                loadPenerimaanDana();
            });
        }
        
        // Tahun selector change
        const tahunSelector = document.getElementById('tahunSelector');
        if (tahunSelector) {
            tahunSelector.addEventListener('change', function() {
                const tahun = this.value;
                window.location.href = "{{ route('penatausahaan.penatausahaan') }}?tahun=" + tahun;
            });
        }
        
        // Inisialisasi tampilan awal
        loadPenerimaanDana();
        checkJumlahPenerimaan();
    });
</script>
@endsection
@include('layouts.footer')