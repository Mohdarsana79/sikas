<!-- Modal Tarik Tunai-->
<div class="modal fade" id="tarikTunai" tabindex="-1" aria-labelledby="tarikTunaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header flex-column align-items-start bg-primary text-white rounded-top-4 p-4">
                <h5 class="modal-title fw-bold" id="tarikTunaiModalLabel">Penarikan Tunai</h5>
                <p class="small mb-0 opacity-75">Isi sesuai dengan detail yang tertera di slip dari bank</p>
            </div>
            <div class="modal-body p-4">
                <form id="formTarikTunai">
                    @csrf
                    <input type="hidden" name="penganggaran_id" value="{{ $penganggaran->id }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="tanggal_penarikan" class="form-label fw-semibold">Tanggal Tarik Tunai</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </span>
                                <input type="date" class="form-control" name="tanggal_penarikan" id="tanggal_penarikan"
                                    placeholder="Pilih tanggal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="jumlah_penarikan" class="form-label fw-semibold mb-0">Jumlah
                                    Penarikan</label>
                                <small class="text-primary fw-bold">Saldo: Rp {{ number_format($saldoNonTunai, 0, ',',
                                    '.') }}</small>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control" name="jumlah_penarikan" id="jumlah_penarikan"
                                    placeholder="0" required data-max="{{ $saldoNonTunai }}">
                            </div>
                            <small class="text-muted">Maksimal: Rp {{ number_format($saldoNonTunai, 0, ',', '.')
                                }}</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSimpanTarik">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Format input jumlah penarikan
    const jumlahInput = document.getElementById('jumlah_penarikan');
    const maxAmount = parseFloat(jumlahInput.dataset.max);
    
    jumlahInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value);
            e.target.value = new Intl.NumberFormat('id-ID').format(value);
            
            if (value > maxAmount) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Jumlah penarikan melebihi saldo non tunai yang tersedia',
                    confirmButtonColor: '#0d6efd',
                });
                e.target.value = new Intl.NumberFormat('id-ID').format(maxAmount);
            }
        }
    });

    // Handle simpan penarikan tunai
    document.getElementById('btnSimpanTarik').addEventListener('click', function() {
        const form = document.getElementById('formTarikTunai');
        const formData = new FormData(form);
        
        // Validasi form
        const tanggal = formData.get('tanggal_penarikan');
        const jumlah = formData.get('jumlah_penarikan');
        
        if (!tanggal || !jumlah) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Harap isi semua field yang wajib diisi',
                confirmButtonColor: '#0d6efd',
            });
            return;
        }
        
        // Convert formatted number back to raw number
        const rawJumlah = jumlah.replace(/\./g, '');
        formData.set('jumlah_penarikan', rawJumlah);
        
        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Sedang menyimpan data penarikan tunai',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("bku.penarikan-tunai.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    confirmButtonColor: '#0d6efd',
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonColor: '#0d6efd',
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat menyimpan data',
                confirmButtonColor: '#0d6efd',
            });
        });
    });
});
</script>