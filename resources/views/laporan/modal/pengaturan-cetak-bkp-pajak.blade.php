{{-- Modal Pengaturan Kertas dan Margin --}}
<div class="modal fade" id="pengaturanKertasModalBkpPajak" tabindex="-1" aria-labelledby="pengaturanKertasModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pengaturanKertasModalLabel">Pengaturan Cetak BKP Pajak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pengaturanKertasFormBkpPajak">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ukuran_kertas_bkp_pajak" class="form-label">Ukuran Kertas</label>
                            <select class="form-select" id="ukuran_kertas_bkp_pajak" name="ukuran_kertas" required>
                                <option value="A4">A4</option>
                                <option value="Folio">Folio</option>
                                <option value="Legal">Legal</option>
                                <option value="Letter">Letter</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orientasi_bkp_pajak" class="form-label">Orientasi</label>
                            <select class="form-select" id="orientasi_bkp_pajak" name="orientasi" required>
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="font_size_bkp_pajak" class="form-label">Ukuran Font</label>
                            <select class="form-select" id="font_size_bkp_pajak" name="font_size" required>
                                <option value="8pt">8pt</option>
                                <option value="9pt">9pt</option>
                                <option value="10pt" selected>10pt</option>
                                <option value="11pt">11pt</option>
                                <option value="12pt">12pt</option>
                                <option value="13pt">13pt</option>
                                <option value="14pt">14pt</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="simpan_pengaturan_bkp_pajak"
                            name="simpan_pengaturan" checked>
                        <label class="form-check-label" for="simpan_pengaturan_bkp_pajak">
                            Simpan pengaturan sebagai default
                        </label>
                    </div>

                    <!-- Preview Section -->
                    <div class="preview-section mt-4">
                        <h6 class="mb-3">Preview Pengaturan</h6>
                        <div class="preview-box border p-3 bg-light">
                            <div class="preview-content text-center">
                                <div class="preview-paper mb-2 mx-auto portrait A4" id="previewPaperBkpPajak">
                                    <div class="preview-header bg-primary text-white p-2 mb-2">
                                        <small>BKP PAJAK BULAN - {{ strtoupper($bulan ?? '-') }}</small>
                                    </div>
                                    <div class="preview-body p-2">
                                        <p class="mb-1 small">Contoh teks untuk preview</p>
                                        <p class="mb-1 small">Ukuran: <span id="previewUkuranBkpPajak">A4</span></p>
                                        <p class="mb-1 small">Orientasi: <span
                                                id="previewOrientasiBkpPajak">Portrait</span></p>
                                        <p class="mb-1 small">Font: <span id="previewFontBkpPajak">10pt</span></p>
                                    </div>
                                    <div class="preview-footer bg-secondary text-white p-2 mt-2">
                                        <small>TANDA TANGAN</small>
                                    </div>
                                </div>
                                <small class="text-muted">Preview visual pengaturan cetak BKP Pajak</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-outline-danger" id="resetPengaturanBkpPajak">Reset
                    Default</button>
                <button type="button" class="btn btn-primary" id="terapkanCetakBkpPajak">Terapkan & Cetak</button>
            </div>
        </div>
    </div>
</div>

<style>
    .preview-box {
        max-height: 300px;
        overflow: auto;
    }

    .preview-paper {
        border: 2px solid #ccc;
        background: white;
        transition: all 0.3s ease;
        position: relative;
    }

    .preview-paper.landscape {
        width: 250px;
        height: 160px;
    }

    .preview-paper.portrait {
        width: 160px;
        height: 250px;
    }

    .preview-paper.A4 {
        border-color: #0d6efd;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    }

    .preview-paper.Folio {
        border-color: #198754;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    }

    .preview-paper.Legal {
        border-color: #dc3545;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    }

    .preview-paper.Letter {
        border-color: #ffc107;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    }

    .preview-header,
    .preview-footer {
        font-size: 0.7em;
    }

    .preview-body {
        font-size: 0.8em;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ukuranKertas = document.getElementById('ukuran_kertas_bkp_pajak');
        const orientasi = document.getElementById('orientasi_bkp_pajak');
        const fontSize = document.getElementById('font_size_bkp_pajak');
        const previewPaper = document.getElementById('previewPaperBkpPajak');
        const previewUkuran = document.getElementById('previewUkuranBkpPajak');
        const previewOrientasi = document.getElementById('previewOrientasiBkpPajak');
        const previewFont = document.getElementById('previewFontBkpPajak');
        const resetBtn = document.getElementById('resetPengaturanBkpPajak');
        const terapkanCetakBtn = document.getElementById('terapkanCetakBkpPajak');
        const simpanPengaturan = document.getElementById('simpan_pengaturan_bkp_pajak');

        // Validasi tahun dan bulan
        const tahun = '{{ $tahun ?? "" }}';
        const bulanElement = document.getElementById('bulanSelect');
        const bulan = bulanElement ? bulanElement.value : '{{ $bulan ?? "" }}';
        
        if (!tahun || !bulan) {
            console.error('Tahun atau bulan tidak tersedia!');
            return;
        }

        // Load saved settings
        function loadSettings() {
            const savedSettings = JSON.parse(localStorage.getItem('pengaturan_cetak_bkp_pajak')) || {
                ukuran_kertas: 'A4',
                orientasi: 'landscape',
                font_size: '10pt'
            };

            ukuranKertas.value = savedSettings.ukuran_kertas;
            orientasi.value = savedSettings.orientasi;
            fontSize.value = savedSettings.font_size;
            updatePreview();
        }

        // Update preview
        function updatePreview() {
            previewPaper.className = 'preview-paper mb-2 mx-auto ' + orientasi.value + ' ' + ukuranKertas.value;
            previewUkuran.textContent = ukuranKertas.value;
            previewOrientasi.textContent = orientasi.value === 'portrait' ? 'Portrait' : 'Landscape';
            previewFont.textContent = fontSize.value;

            const previewBody = previewPaper.querySelector('.preview-body');
            previewBody.style.fontSize = fontSize.value;
        }

        // Save settings
        function saveSettings() {
            const settings = {
                ukuran_kertas: ukuranKertas.value,
                orientasi: orientasi.value,
                font_size: fontSize.value
            };
            localStorage.setItem('pengaturan_cetak_bkp_pajak', JSON.stringify(settings));
        }

        // Fungsi untuk membuka PDF
        function bukaPDF() {
            // Validasi tahun dan bulan sekali lagi
            const currentBulan = bulanElement ? bulanElement.value : bulan;
            if (!tahun || !currentBulan) {
                alert('Error: Tahun atau bulan tidak tersedia.');
                return;
            }
            
            // Save settings if checkbox is checked
            if (simpanPengaturan.checked) {
                saveSettings();
            }

            const ukuranKertasValue = ukuranKertas.value;
            const orientasiValue = orientasi.value;
            const fontSizeValue = fontSize.value;

            // Build URL dengan tahun dan bulan sebagai route parameter
            let pdfUrl = "{{ route('laporan.bkp-pajak-pdf', ['tahun' => ':tahun', 'bulan' => ':bulan']) }}";
            pdfUrl = pdfUrl.replace(':tahun', tahun).replace(':bulan', currentBulan);
            
            // Tambahkan parameter lainnya sebagai query string
            pdfUrl += `?ukuran_kertas=${encodeURIComponent(ukuranKertasValue)}`;
            pdfUrl += `&orientasi=${encodeURIComponent(orientasiValue)}`;
            pdfUrl += `&font_size=${encodeURIComponent(fontSizeValue)}`;

            console.log('PDF URL BKP Pajak :', pdfUrl);

            // Buka PDF
            try {
                const newWindow = window.open(pdfUrl, '_blank');
                
                if (!newWindow) {
                    const tempLink = document.createElement('a');
                    tempLink.href = pdfUrl;
                    tempLink.target = '_blank';
                    tempLink.style.display = 'none';
                    document.body.appendChild(tempLink);
                    tempLink.click();
                    document.body.removeChild(tempLink);
                }
            } catch (error) {
                console.error('Error opening PDF:', error);
                alert('Terjadi error: ' + error.message);
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('pengaturanKertasModalBkpPajak'));
            if (modal) {
                modal.hide();
            }
        }

        // Event listeners untuk perubahan setting
        ukuranKertas.addEventListener('change', updatePreview);
        orientasi.addEventListener('change', updatePreview);
        fontSize.addEventListener('change', updatePreview);

        // Event listener untuk tombol Terapkan & Cetak
        terapkanCetakBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            bukaPDF();
        });

        // Reset settings
        resetBtn.addEventListener('click', function() {
            ukuranKertas.value = 'A4';
            orientasi.value = 'landscape';
            fontSize.value = '10pt';
            updatePreview();
            localStorage.removeItem('pengaturan_cetak_bkp_pajak');
        });

        // Initialize
        loadSettings();
    });
</script>