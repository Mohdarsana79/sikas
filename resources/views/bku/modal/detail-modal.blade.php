<div class="modal fade" id="detailModal{{ $bku->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $bku->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $bku->id }}">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ID Transaksi</label>
                            <p>{{ $bku->id_transaksi }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <p>{{ $bku->tanggal_transaksi->format('d/m/Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kegiatan</label>
                            <p>{{ $bku->kodeKegiatan->uraian }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rekening Belanja</label>
                            <p>{{ $bku->rekeningBelanja->rincian_objek }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jenis Transaksi</label>
                            <p>{{ ucfirst($bku->jenis_transaksi) }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Anggaran</label>
                            <p>Rp {{ number_format($bku->anggaran, 0, ',', '.') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dibelanjakan</label>
                            <p>Rp {{ number_format($bku->dibelanjakan, 0, ',', '.') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pajak</label>
                            <p>{{ $bku->total_pajak ? 'Rp ' . number_format($bku->total_pajak, 0, ',', '.') : '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Penyedia</label>
                            <p>{{ $bku->nama_penyedia_barang_jasa }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Uraian</label>
                            <p>{{ $bku->uraian }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>