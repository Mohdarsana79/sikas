{{-- Modal Pengaturan Kertas dan Margin --}}
<div class="modal fade" id="pengaturanKertasModalBa" tabindex="-1" aria-labelledby="pengaturanKertasModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pengaturanKertasModalLabel">Pengaturan Cetak Berita Acara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pengaturanKertasFormBa">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ukuran_kertas_ba" class="form-label">Ukuran Kertas</label>
                            <select class="form-select" id="ukuran_kertas_ba" name="ukuran_kertas" required>
                                <option value="A4">A4</option>
                                <option value="Folio">Folio</option>
                                <option value="Legal">Legal</option>
                                <option value="Letter">Letter</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orientasi_ba" class="form-label">Orientasi</label>
                            <select class="form-select" id="orientasi_ba" name="orientasi" required>
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="font_size_ba" class="form-label">Ukuran Font</label>
                            <select class="form-select" id="font_size_ba" name="font_size" required>
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
                        <input class="form-check-input" type="checkbox" id="simpan_pengaturan_ba"
                            name="simpan_pengaturan" checked>
                        <label class="form-check-label" for="simpan_pengaturan_ba">
                            Simpan pengaturan sebagai default
                        </label>
                    </div>

                    <!-- Preview Section -->
                    <div class="preview-section mt-4">
                        <h6 class="mb-3">Preview Pengaturan</h6>
                        <div class="preview-box border p-3 bg-light">
                            <div class="preview-content text-center">
                                <div class="preview-paper mb-2 mx-auto portrait A4" id="previewPaperBa">
                                    <div class="preview-header bg-primary text-white p-2 mb-2">
                                        <small>BA PEMERIKSAAN KAS BULAN - {{ strtoupper($bulan ?? '-') }}</small>
                                    </div>
                                    <div class="preview-body p-2">
                                        <p class="mb-1 small">Contoh teks untuk preview</p>
                                        <p class="mb-1 small">Ukuran: <span id="previewUkuranBa">A4</span></p>
                                        <p class="mb-1 small">Orientasi: <span
                                                id="previewOrientasiBa">Portrait</span></p>
                                        <p class="mb-1 small">Font: <span id="previewFontBa">10pt</span></p>
                                    </div>
                                    <div class="preview-footer bg-secondary text-white p-2 mt-2">
                                        <small>TANDA TANGAN</small>
                                    </div>
                                </div>
                                <small class="text-muted">Preview visual pengaturan cetak BA</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-outline-danger" id="resetPengaturanBa">Reset Default</button>
                <button type="button" class="btn btn-primary" id="terapkanCetakBa">Terapkan & Cetak</button>
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
        const ukuranKertas = document.getElementById('ukuran_kertas_ba');
        const orientasi = document.getElementById('orientasi_ba');
        const fontSize = document.getElementById('font_size_ba');
        const previewPaper = document.getElementById('previewPaperBa');
        const previewUkuran = document.getElementById('previewUkuranBa');
        const previewOrientasi = document.getElementById('previewOrientasiBa');
        const previewFont = document.getElementById('previewFontBa');
        const resetBtn = document.getElementById('resetPengaturanBa');
        const terapkanCetakBtn = document.getElementById('terapkanCetakBa');
        const simpanPengaturan = document.getElementById('simpan_pengaturan_ba');

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
            const savedSettings = JSON.parse(localStorage.getItem('pengaturan_cetak_ba')) || {
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
            localStorage.setItem('pengaturan_cetak_ba', JSON.stringify(settings));
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
            let pdfUrl = "{{ route('laporan.berita-acara-pdf', ['tahun' => ':tahun', 'bulan' => ':bulan']) }}";
            pdfUrl = pdfUrl.replace(':tahun', tahun).replace(':bulan', currentBulan);
            
            // Tambahkan parameter lainnya sebagai query string
            pdfUrl += `?ukuran_kertas=${encodeURIComponent(ukuranKertasValue)}`;
            pdfUrl += `&orientasi=${encodeURIComponent(orientasiValue)}`;
            pdfUrl += `&font_size=${encodeURIComponent(fontSizeValue)}`;

            console.log('PDF URL BKP BANK :', pdfUrl);

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
            const modal = bootstrap.Modal.getInstance(document.getElementById('pengaturanKertasModalBa'));
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
            localStorage.removeItem('pengaturan_cetak_ba');
        });

        // Initialize
        loadSettings();
    });
</script>