<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran</title>
    <!-- Tambahkan import font Cinzel (untuk judul) dan Roboto (untuk body) dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Murni untuk mereplikasi tampilan PDF */

        body {
            /* MENGGUNAKAN ROBOTO untuk teks badan */
            font-family: 'Roboto', 'Arial Narrow', Arial, sans-serif; 
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            font-size: 10pt; /* Ukuran font standar untuk dokumen */
            line-height: 1.2;
        }

        .kwitansi-container {
            width: 210mm; /* Lebar A4 */
            min-height: 297mm; /* Tinggi A4 */
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
        }

        /* Gaya untuk konten yang diborder (khusus No Kwitansi) */
        .bordered-content {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            /* Tambahkan padding horizontal di dalam sel untuk memisahkan teks dari border vertikal jika ada */
            padding-left: 5px; 
            padding-right: 5px;
            display: inline-block; /* Penting agar border hanya membungkus konten */
            line-height: 1.2; /* Memastikan tinggi sesuai baris */
        }

        /* Utility Class: Rata Kanan */
        .text-end {
            text-align: right;
        }

        /* Style untuk Tabel Informasi Header */
        .info-table {
            width: 100%;
            border-collapse: collapse; 
            border-spacing: 0;
            margin-bottom: 0px;
        }
        .info-table td {
            vertical-align: top;
            padding: 1px 5px; 
            border: none;
        }
        
        /* Mengatur lebar kolom label di kiri dan kanan */
        .info-table .label-col {
            width: 170px; 
            padding-right: 5px;
        }

        /* Lebar spesifik 5% untuk kolom label pendek di sisi kanan */
        .info-table .label-col-right {
            width: 5%;
        }

        .info-table .separator {
            width: 10px;
            text-align: center;
        }
        .info-table .info-content {
            width: auto; 
        }
        
        /* Lebar 20% untuk kolom kode/nilai di sisi kiri */
        .info-table .info-code-width {
            width: 20%;
        }


        /* Judul KWITANSI */
        .kwitansi-title {
            text-align: center;
            /* MENGURANGI JARAK BAWAH */
            margin-bottom: 5px; 
        }
        .kwitansi-title h1 {
            font-size: 24pt; 
            font-family: 'Cinzel', serif; 
            font-weight: 700;
            letter-spacing: 5px;
            /* WARNA HIJAU (SUCCESS) */
            color: #059669; 
            border: 2px solid #059669;
            display: inline-block;
            padding: 2px 30px;
        }

        /* Bagian Penerimaan Uang */
        .receipt-info {
            margin-bottom: 20px;
        }
        .receipt-info .data-row {
            display: flex; 
            margin-bottom: 8px;
        }
        .receipt-info .label-col {
            width: 170px; 
            flex-shrink: 0;
        }
        .receipt-info .info-col {
            flex-grow: 1;
            font-weight: bold;
        }


        /* ----------- STYLE TABEL RINCIAN ITEM ----------- */
        .detail-table-container {
            /* MENGHILANGKAN BORDER BAWAH */
            border: 2px solid #000;
            border-bottom: none; /* Hapus border bawah kontainer luar */
            /* MENGUBAH JARA KE BAWAH DARI 5px menjadi 0px */
            margin-bottom: 0px; 
        }
        .detail-table-container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        /* Header (TH) Styling */
        .detail-table-container th {
            font-weight: bold;
            border-bottom: 1px solid #000;
            background-color: #f5f5f5;
            padding: 4px;
            text-align: center; 
        }

        /* Data Cells (TD) Styling */
        .detail-table-container td {
            padding: 4px;
            border-bottom: 1px solid #e0e0e0; /* Garis tipis antara baris */
            vertical-align: middle;
        }
        .detail-table-container tbody tr:last-of-type td {
            border-bottom: none; /* Hilangkan garis di bawah baris data terakhir */
        }
        
        /* Widths and Borders for TH/TD */
        /* Rincian: 45% (Left Aligned for TD, Center Aligned for TH) */
        .detail-table-container th:nth-child(1),
        .detail-table-container td:nth-child(1) {
            width: 45%;
            border-right: 1px solid #000;
            text-align: left;
        }
        .detail-table-container th:nth-child(1) { text-align: center; } /* Override for Rincian header */

        /* Jml/Qty: 8% (Center Aligned) */
        .detail-table-container th:nth-child(2),
        .detail-table-container td:nth-child(2) {
            width: 8%;
            border-right: 1px solid #000;
            text-align: center;
        }
        /* Satuan: 10% (Center Aligned) */
        .detail-table-container th:nth-child(3),
        .detail-table-container td:nth-child(3) {
            width: 10%;
            border-right: 1px solid #000;
            text-align: center;
        }
        /* Harga Satuan: 15% (Right Aligned) */
        .detail-table-container th:nth-child(4),
        .detail-table-container td:nth-child(4) {
            width: 15%;
            border-right: 1px solid #000;
            text-align: right;
        }

        /* Jumlah: 22% (Right Aligned, No Border Right) */
        .detail-table-container th:nth-child(5),
        .detail-table-container td:nth-child(5) {
            width: 22%;
            text-align: right;
        }

        /* Total Row Styling */
        .total-row {
            font-weight: bold;
            border-top: 1px solid #000; /* Garis pemisah sebelum total */
            background-color: #f5f5f5; /* TAMBAH WARNA ARSIRAN */
        }
        .total-row td {
            border-bottom: none; /* Pastikan tidak ada garis di bawah baris total */
            padding-top: 5px;
            padding-bottom: 5px;
            vertical-align: middle;
        }
        /* ----------------------------------------------------------------------------------- */


        /* Bagian Pajak dan Total Akhir BARU */
        .tax-and-total {
            /* MENGURANGI JARAK KE TANDA TANGAN */
            margin-bottom: 30px; 
            width: 100%;
            overflow: auto; 
            /* UBAH BORDER ATAS DARI 1PX MENJADI 2PX */
            border-top: 2px solid #000; 
            padding-top: 10px; /* Tambahkan padding agar garis tidak menempel pada teks */
        }
        .tax-section {
            font-size: 10pt;
        }

        .tax-item {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-left: 20px;
        }
        .tax-item span:nth-child(1) {
            width: 50px; /* Lebar untuk label PPn, PPh, PB 1 */
        }
        .tax-item span:nth-child(2) {
            /* MENGURANGI JARAK ANTARA = DAN RP */
            margin-right: 2px;
        }


        /* Kotak Total Final (Diberi Border Atas Bawah dan Background Arsiran) */
        .total-final-box {
            /* MENGHILANGKAN BORDER */
            border-top: 1px solid #000; /* Border Atas */
            border-bottom: 1px solid #000; /* Border Bawah */
            background-color: #f0f0f0; /* Background Arsiran Abu-abu Muda */
            width: 250px; 
            display: flex;
            font-size: 10pt;
            font-weight: bold;
            margin-top: 15px; 
            margin-left: 0; 
        }
        .total-final-box div {
            padding: 5px;
        }
        .total-final-box div:first-child {
            width: 50%;
        }
        .total-final-box div:last-child {
            width: 50%;
            text-align: right;
        }

        /* Bagian Tanda Tangan */
        .signature-area {
            display: flex;
            /* MENGGUNAKAN SPACE-BETWEEN UNTUK JARA MAKSIMAL */
            justify-content: space-between; 
            text-align: center; /* MEMASTIKAN TEKS DI DALAM BLOK RATA TENGAH */
            font-size: 10pt;
            width: 100%; /* Pastikan kontainer mengambil lebar penuh */
        }
        .signature-block {
            /* Mengurangi lebar menjadi auto agar flexbox bisa mengatur jarak */
            width: auto; 
            padding: 0 15px; /* Tambahkan sedikit padding horizontal agar tidak menempel di tepi */
        }
        .signature-block p {
            margin: 2px 0;
        }
        .signature-block .role {
            font-weight: bold;
            margin-bottom: 50px;
        }
        .signature-block .name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-block .nip {
            font-size: 9pt;
        }
        /* Gaya Khusus untuk Kepala Sekolah yang Terpisah */
        .kepsek-block {
            text-align: center;
            margin-top: 40px; 
            font-size: 10pt;
        }
        .kepsek-block .role {
            font-weight: bold;
            margin-bottom: 50px;
        }
        .kepsek-block .name {
            font-weight: bold;
            text-decoration: underline;
            margin: 2px 0;
        }
        .kepsek-block .nip {
            font-size: 9pt;
            margin: 2px 0;
        }
    </style>
</head>
<body>

<div class="kwitansi-container">

    <!-- Bagian Header (Informasi Progam dan Anggaran) -->
    <div>
        <table class="info-table">
            <tr>
                <!-- KIRI -->
                <td class="label-col">Sumber Dana</td>
                <td class="separator">:</td>
                <td class="info-content info-code-width">BOS Pusat</td>
                
                <!-- KANAN -->
                <td class="label-col label-col-right">No</td>
                <td class="separator">:</td>
                <!-- KLASIFIKASI KWITANSI DIBORDER -->
                <td class="info-content">
                    <span class="bordered-content">019/BOS/THP-1/2025</span>
                </td>
            </tr>
            <!-- ROW 2: Tahun Anggaran / KOSONG -->
            <tr>
                <!-- KIRI -->
                <td class="label-col">Tahun Anggaran</td>
                <td class="separator">:</td>
                <td class="info-content info-code-width">{{ $kwitansi->penganggaran->tahun_anggaran }}</td>
                
                <!-- KANAN: KOSONG -->
                <td class="label-col label-col-right">&nbsp;</td>
                <td class="separator">&nbsp;</td>
                <td class="info-content">&nbsp;</td>
            </tr>
            <!-- ROW 3: Program / Keterangan Program Penuh (MENGGUNAKAN COLSPAN 3) -->
            <tr>
                <!-- KIRI -->
                <td class="label-col">Program</td>
                <td class="separator">:</td>
                <td class="info-content info-code-width">{{ $kwitansi->kodeKegiatan->kode ?? '-' }}</td>
                
                <!-- KANAN: GABUNGKAN 3 KOLOM menjadi 1 menggunakan colspan="3" -->
                <td class="info-content" colspan="3">{{ $kwitansi->kodeKegiatan->program ?? '-' }}</td>
            </tr>
            <!-- ROW 4: Kode Sub Program / KOSONGKAN KANAN -->
            <tr>
                <!-- KIRI (Kode Sub Program) -->
                <td class="label-col">Kode Sub Program</td>
                <td class="separator">:</td>
                <td class="info-content info-code-width text-end">{{ $kwitansi->kodeKegiatan->kode ?? '-' }}</td>

                <!-- KANAN: KOSONG -->
                <td class="info-content" colspan="3">Pelaksaan Administrasi Kegiatan Sekolah</td>
            </tr>
            <!-- ROW 5: Kode Uraian/Kegiatan KIRI (DIPINDAH) / KOSONG KANAN -->
            <tr>
                <!-- KIRI (Kode Uraian/Kegiatan DIPINDAH) -->
                <td class="label-col">Kode Uraian/Kegiatan</td>
                <td class="separator">:</td>
                <td class="info-content info-code-width text-end">06.05.0</td>

                <!-- KANAN: KOSONG -->
                <td class="info-content" colspan="3">Pembelian Bahan Habis Pakai untuk mendukung pembelajaran dan administrasi sekolah (termasuk ATK, Tinta Printer, Kabel Ekstension, dsb)</td>
            </tr>

            <tr>
                <!-- KIRI (Kode Uraian/Kegiatan DIPINDAH) -->
                <td class="label-col">Kode Rekenig Rincian Objek</td>
                <td class="separator">:</td>
                <td class="info-content info-code-width text-end">{{ $kwitansi->rekeningBelanja->kode_rekening }}</td>

                <!-- KANAN: KOSONG -->
                <td class="info-content" colspan="3">{{ $kwitansi->rekeningBelanja->rincian_objek }}</td>
            </tr>
        </table>
    </div>

    <!-- Judul KWITANSI -->
    <div class="kwitansi-title">
        <h1>KWITANSI</h1>
    </div>

    <!-- Bagian Penerimaan Uang -->
    <div class="receipt-info">
        <div class="data-row">
            <span class="label-col">Sudah Terima Dari</span>
            <span class="info-col">: Bendahara Dana BOSP SMP MUHAMMADIYAH SONI</span>
        </div>
        <div class="data-row">
            <span class="label-col">Uang Sebanyak</span>
            <span class="info-col">: Tujuh ratus tujuh belas ribu Rupiah</span>
        </div>
        <div class="data-row">
            <span class="label-col">Untuk Pembayaran</span>
            <span class="info-col">: Lunas Bayar Belanja Alat Tulis Kantor</span>
        </div>
    </div>

    <!-- Bagian Rincian Item -->
    <div class="detail-table-container">
        <table>
            <thead>
                <tr>
                    <th>Rincian</th>
                    <th>Jml</th>
                    <th>Satuan</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <!-- Item 1 -->
                <tr>
                    <td>Tinta Stempel</td>
                    <td>2</td>
                    <td>Botol</td>
                    <td>18,000</td>
                    <td>36,000</td>
                </tr>
                
                <!-- Item 2 -->
                <tr>
                    <td>Amplop Kabinet / Putih Besar</td>
                    <td>2</td>
                    <td>Dus</td>
                    <td>35,000</td>
                    <td>70,000</td>
                </tr>

                <!-- Item 3 -->
                <tr>
                    <td>Stabilo</td>
                    <td>1</td>
                    <td>Buah</td>
                    <td>11,000</td>
                    <td>11,000</td>
                </tr>

            
                
                <!-- Total Jumlah (menggunakan colspan untuk menyatukan kolom) -->
                <tr class="total-row">
                    <!-- Colspan 3: Menyatukan Rincian, Jml, Satuan (45% + 8% + 10%) -->
                    <td colspan="3" style="text-align: right; padding-right: 10px; font-weight: bold; border-right: 1px solid #000;">Jumlah</td>
                    
                
                    
                    <!-- Col 5: Jumlah (22%) -->
                    <td style="text-align: right; font-weight: bold;" colspan="2">717,000</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Bagian Pajak dan Total Akhir (Layout diubah agar Total Box di bawah PB 1) -->
    <div class="tax-and-total">
        <!-- Bagian Kiri (Pajak dan Total Box) -->
        <div class="tax-section">
            <!-- PADA BAGIAN INI TIDAK ADA PADDING/MARGIN YANG PERLU DIUBAH, KARENA HANYA MENGUBAH JARAK KE BAWAH TABEL -->
            <p style="font-weight: bold; margin-bottom: 5px;">Sudah termasuk pajak</p>
            <div class="tax-item">
                <span>PPn</span>
                <span>=</span>
                <span style="width: 50px; text-align: right;">Rp</span>
            </div>
            <div class="tax-item">
                <span>PPh</span>
                <span>=</span>
                <span style="width: 50px; text-align: right;">Rp</span>
            </div>
            <div class="tax-item">
                <span>PB 1</span>
                <span>=</span>
                <span style="width: 50px; text-align: right;">Rp</span>
            </div>
            
            <!-- KOTAK TOTAL AKHIR DIPINDAH DI SINI -->
            <div class="total-final-box">
                <div>Jumlah</div>
                <div>: Rp 717,000</div>
            </div>
            
        </div>
        
        <!-- Bagian Kanan (.total-final-section) sekarang sudah dihapus/dikosongkan -->
        
    </div>

    <!-- Bagian Tanda Tangan (2 Kolom: Bendahara Kiri, Penerima Kanan) -->
    <div class="signature-area">
        <!-- Kolom Kiri Bawah: BENDAHARA BOSP (Teks sekarang rata tengah) -->
        <div class="signature-block">
            <p>Lunas Bayar, 19 Mei 2025</p>
            <p class="role">Bendahara BOSP</p>
            <p class="name">Dra. MASITAH ABDULLAH</p>
            <p class="nip">(NIP. 196909172007012017)</p>
        </div>

        <!-- Kolom Kanan Bawah: YANG MENERIMA (Teks sekarang rata tengah) -->
        <div class="signature-block">
            <p>&nbsp;</p> <!-- Dikosongkan agar sejajar dengan posisi Bendahara BOSP -->
            <p class="role">Yang Menerima</p>
            <p class="name">NAMA PENJUAL/PENYEDIA</p>
            <p class="nip">(NIK/KTP Penjual)</p>
        </div>
    </div>
    
    <!-- Area Tanda Tangan KEPALA SEKOLAH (DITENGAHKAN) -->
    <div class="kepsek-block">
        <p>Mengetahui,</p>
        <p class="role">Kepala Sekolah</p>
        <p class="name">Dra. MASITAH ABDULLAH</p>
        <p class="nip">(NIP. 196909172007012017)</p>
    </div>

</div>

</body>
</html>
