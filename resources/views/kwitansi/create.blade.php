@extends('layouts.app')

@section('title', 'Buat Kwitansi Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Buat Kwitansi Baru</h3>
                    <div class="card-tools">
                        <a href="{{ route('kwitansi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="kwitansi-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="buku_kas_umum_id">Pilih Transaksi Buku Kas Umum</label>
                                    <select name="buku_kas_umum_id" id="buku_kas_umum_id" class="form-control" required>
                                        <option value="">-- Pilih Transaksi --</option>
                                        @foreach($bukuKasUmums as $bku)
                                        <option value="{{ $bku->id }}" data-details="{{ $bku->toJson() }}">
                                            {{ $bku->id_transaksi }} - {{ $bku->uraian_opsional ?? $bku->uraian }} - Rp
                                            {{ number_format($bku->total_transaksi_kotor, 0, ',', '.') }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bku_uraian_detail_id">Pilih Detail Uraian</label>
                                    <select name="bku_uraian_detail_id" id="bku_uraian_detail_id" class="form-control"
                                        required disabled>
                                        <option value="">-- Pilih transaksi terlebih dahulu --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Data -->
                        <div id="preview-section" class="mt-4" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Preview Kwitansi</h4>
                                </div>
                                <div class="card-body">
                                    <div id="preview-content">
                                        <!-- Preview akan diisi via JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-save"></i> Buat Kwitansi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // When buku_kas_umum_id changes
        $('#buku_kas_umum_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            const bkuData = selectedOption.data('details');
            
            if (bkuData) {
                // Enable and populate detail dropdown
                $('#bku_uraian_detail_id').prop('disabled', false).html('');
                $('#bku_uraian_detail_id').append('<option value="">-- Pilih Detail Uraian --</option>');
                
                // Populate detail options
                if (bkuData.uraian_details && bkuData.uraian_details.length > 0) {
                    bkuData.uraian_details.forEach(function(detail) {
                        $('#bku_uraian_detail_id').append(
                            `<option value="${detail.id}">
                                ${detail.uraian} - ${detail.volume} ${detail.satuan} - Rp ${formatRupiah(detail.subtotal)}
                            </option>`
                        );
                    });
                }
                
                // Show preview
                showPreview(bkuData);
            } else {
                $('#bku_uraian_detail_id').prop('disabled', true).html('<option value="">-- Pilih transaksi terlebih dahulu --</option>');
                $('#preview-section').hide();
            }
        });

        // Form submission
        $('#kwitansi-form').submit(function(e) {
            e.preventDefault();
            
            const submitBtn = $('#submit-btn');
            const originalText = submitBtn.html();
            
            // Disable button dan show loading
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membuat Kwitansi...');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("kwitansi.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response?.message || 'Terjadi kesalahan saat membuat kwitansi'
                    });
                },
                complete: function() {
                    // Enable button kembali
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        function showPreview(bkuData) {
            const previewContent = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Tahun Anggaran:</strong> ${bkuData.penganggaran?.tahun_anggaran || '-'}<br>
                        <strong>Program:</strong> ${bkuData.kode_kegiatan?.program || '-'}<br>
                        <strong>Sub Program:</strong> ${bkuData.kode_kegiatan?.sub_program || '-'}<br>
                        <strong>Kegiatan:</strong> ${bkuData.kode_kegiatan?.uraian || '-'}
                    </div>
                    <div class="col-md-6">
                        <strong>Kode Rekening:</strong> ${bkuData.rekening_belanja?.kode_rekening || '-'}<br>
                        <strong>Rincian Objek:</strong> ${bkuData.rekening_belanja?.rincian_objek || '-'}<br>
                        <strong>Uraian:</strong> ${bkuData.uraian_opsional || bkuData.uraian}<br>
                        <strong>Total:</strong> Rp ${formatRupiah(bkuData.total_transaksi_kotor)}
                    </div>
                </div>
                <hr>
                <h6>Rincian Item:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Uraian</th>
                                <th>Volume</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${bkuData.uraian_details ? bkuData.uraian_details.map(detail => `
                                <tr>
                                    <td>${detail.uraian}</td>
                                    <td>${detail.volume} ${detail.satuan || ''}</td>
                                    <td>Rp ${formatRupiah(detail.harga_satuan)}</td>
                                    <td>Rp ${formatRupiah(detail.subtotal)}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="4">Tidak ada detail</td></tr>'}
                        </tbody>
                    </table>
                </div>
            `;
            
            $('#preview-content').html(previewContent);
            $('#preview-section').show();
        }

        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }
    });
</script>
@endpush