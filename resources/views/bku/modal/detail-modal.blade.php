<div class="modal fade" id="detailModal{{ $bku->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Pembelanjaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informasi Umum -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Tanggal Transaksi:</strong><br>
                            {{ $bku->tanggal_transaksi->format('d M Y') }}
                        </div>
                        <div class="mb-2">
                            <strong>ID Transaksi:</strong><br>
                            {{ $bku->id_transaksi }}
                        </div>
                        <div class="mb-2">
                            <strong>Tanggal Lapor Pajak:</strong><br>
                            {{ ucfirst($bku->tanggal_lapor) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Nama Penyedia:</strong><br>
                            {{ $bku->nama_penyedia_barang_jasa }}
                        </div>
                        <div class="mb-2">
                            <strong>NPWP:</strong><br>
                            {{ $bku->npwp ?? '-' }}
                        </div>
                        <div class="mb-2">
                            <strong>NTPN :</strong><br>
                            {{ $bku->ntpn ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Informasi Kegiatan dan Rekening -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <strong>Kegiatan</strong>
                            </div>
                            <div class="card-body">
                                {{ $bku->kodeKegiatan->kode }} - {{ $bku->kodeKegiatan->uraian }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <strong>Rekening Belanja</strong>
                            </div>
                            <div class="card-body">
                                {{ $bku->rekeningBelanja->kode_rekening }} - {{ $bku->rekeningBelanja->rincian_objek }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Detail Uraian -->
                <h6 class="mb-3 border-bottom pb-2">Detail Uraian Pembelanjaan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Uraian</th>
                                <th class="text-center">Volume</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bku->uraianDetails as $detail)
                            <tr>
                                <td>{{ $detail->uraian }}</td>
                                <td class="text-center">{{ number_format($detail->volume, 0) }} {{ $detail->satuan ?? ''
                                    }}</td>
                                <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr>
                                <th colspan="3" class="text-end">Total Transaksi Kotor:</th>
                                <th class="text-end">Rp {{ number_format($bku->total_transaksi_kotor, 0, ',', '.') }}
                                </th>
                            </tr>
                            @if($bku->total_pajak)
                            <tr>
                                <th colspan="3" class="text-end">Pajak ({{ $bku->pajak }}):</th>
                                <th class="text-end">Rp {{ number_format($bku->total_pajak, 0, ',', '.') }}</th>
                            </tr>
                            @endif
                            @if($bku->total_pajak_daerah)
                            <tr>
                                <th colspan="3" class="text-end">Pajak Daerah ({{ $bku->pajak_daerah }}):</th>
                                <th class="text-end">Rp {{ number_format($bku->total_pajak_daerah, 0, ',', '.') }}</th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="3" class="text-end">Total Harga Bersih:</th>
                                <th class="text-end">Rp {{ number_format($bku->dibelanjakan, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>