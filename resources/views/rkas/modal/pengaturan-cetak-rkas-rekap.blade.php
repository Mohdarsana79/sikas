{{-- Modal Pengaturan Kertas dan Margin --}}
<div class="modal fade" id="pengaturanKertasModal" tabindex="-1" aria-labelledby="pengaturanKertasModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pengaturanKertasModalLabel">Pengaturan Cetak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pengaturanKertasForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ukuran_kertas" class="form-label">Ukuran Kertas</label>
                            <select class="form-select" id="ukuran_kertas" name="ukuran_kertas" required>
                                <option value="A4">A4</option>
                                <option value="Folio">Folio</option>
                                <option value="Legal">Legal</option>
                                <option value="Letter">Letter</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orientasi" class="form-label">Orientasi</label>
                            <select class="form-select" id="orientasi" name="orientasi" required>
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="font_size" class="form-label">Ukuran Font</label>
                            <select class="form-select" id="font_size" name="font_size" required>
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
                        <input class="form-check-input" type="checkbox" id="simpan_pengaturan" name="simpan_pengaturan"
                            checked>
                        <label class="form-check-label" for="simpan_pengaturan">
                            Simpan pengaturan sebagai default
                        </label>
                    </div>

                    <!-- Preview Section -->
                    <div class="preview-section mt-4">
                        <h6 class="mb-3">Preview Pengaturan</h6>
                        <div class="preview-box border p-3 bg-light">
                            <div class="preview-content text-center">
                                <div class="preview-paper mb-2 mx-auto portrait A4" id="previewPaper">
                                    <div class="preview-header bg-primary text-white p-2 mb-2">
                                        <small>HEADER DOKUMEN</small>
                                    </div>
                                    <div class="preview-body p-2">
                                        <p class="mb-1 small">Contoh teks untuk preview</p>
                                        <p class="mb-1 small">Ukuran: <span id="previewUkuran">A4</span></p>
                                        <p class="mb-1 small">Orientasi: <span id="previewOrientasi">Portrait</span></p>
                                        <p class="mb-1 small">Font: <span id="previewFont">10pt</span></p>
                                    </div>
                                    <div class="preview-footer bg-secondary text-white p-2 mt-2">
                                        <small>FOOTER DOKUMEN</small>
                                    </div>
                                </div>
                                <small class="text-muted">Preview visual pengaturan cetak</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-danger" id="resetPengaturan">Reset Default</button>
                    <button type="submit" class="btn btn-primary">Terapkan & Cetak</button>
                </div>
            </form>
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
    const form = document.getElementById('pengaturanKertasForm');
    const ukuranKertas = document.getElementById('ukuran_kertas');
    const orientasi = document.getElementById('orientasi');
    const fontSize = document.getElementById('font_size');
    const previewPaper = document.getElementById('previewPaper');
    const previewUkuran = document.getElementById('previewUkuran');
    const previewOrientasi = document.getElementById('previewOrientasi');
    const previewFont = document.getElementById('previewFont');
    const resetBtn = document.getElementById('resetPengaturan');

    // Load saved settings
    function loadSettings() {
        const savedSettings = JSON.parse(localStorage.getItem('pengaturan_cetak_rka_rekap')) || {
            ukuran_kertas: 'A4',
            orientasi: 'portrait',
            font_size: '10pt'
        };

        ukuranKertas.value = savedSettings.ukuran_kertas;
        orientasi.value = savedSettings.orientasi;
        fontSize.value = savedSettings.font_size;
        updatePreview();
    }

    // Update preview
    function updatePreview() {
        // Update paper size and orientation
        previewPaper.className = 'preview-paper mb-2 mx-auto ' + orientasi.value + ' ' + ukuranKertas.value;
        
        // Update text
        previewUkuran.textContent = ukuranKertas.value;
        previewOrientasi.textContent = orientasi.value === 'portrait' ? 'Portrait' : 'Landscape';
        previewFont.textContent = fontSize.value;

        // Update font size in preview
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
        localStorage.setItem('pengaturan_cetak_rka_rekap', JSON.stringify(settings));
    }

    // Event listeners
    ukuranKertas.addEventListener('change', updatePreview);
    orientasi.addEventListener('change', updatePreview);
    fontSize.addEventListener('change', updatePreview);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Save settings if checkbox is checked
        if (document.getElementById('simpan_pengaturan').checked) {
            saveSettings();
        }

        const tahun = '{{ $tahun }}';
        const ukuranKertasValue = ukuranKertas.value;
        const orientasiValue = orientasi.value;
        const fontSizeValue = fontSize.value;

        // Build URL dengan tahun sebagai route parameter
        let pdfUrl = "{{ route('rkas.generate-pdf-rekap', ['tahun' => ':tahun']) }}";
        pdfUrl = pdfUrl.replace(':tahun', tahun);
        
        // Tambahkan parameter lainnya sebagai query string
        pdfUrl += `?ukuran_kertas=${encodeURIComponent(ukuranKertasValue)}`;
        pdfUrl += `&orientasi=${encodeURIComponent(orientasiValue)}`;
        pdfUrl += `&font_size=${encodeURIComponent(fontSizeValue)}`;

        console.log('PDF URL:', pdfUrl);

        // Buka PDF
        try {
            const newWindow = window.open(pdfUrl, '_blank');
            
            if (!newWindow) {
                // Jika popup diblokir, buat link temporary
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
        const modal = bootstrap.Modal.getInstance(document.getElementById('pengaturanKertasModal'));
        modal.hide();
    });

    // Reset settings
    resetBtn.addEventListener('click', function() {
        ukuranKertas.value = 'A4';
        orientasi.value = 'portrait';
        fontSize.value = '10pt';
        updatePreview();
        localStorage.removeItem('pengaturan_cetak_rka_rekap');
    });

    // Initialize
    loadSettings();
});
</script>