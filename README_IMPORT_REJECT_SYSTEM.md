# Sistem Penanganan Data Import Ditolak

## Overview
Sistem ini dirancang untuk menangani data peserta yang gagal diimport ke database karena berbagai alasan seperti data duplikat, validasi gagal, atau error database. Data yang ditolak akan disimpan dalam tabel terpisah untuk memudahkan review dan perbaikan.

## Fitur Utama

### 1. Penanganan Data Ditolak
- **Otomatis menyimpan data yang gagal** ke tabel `peserta_reject`
- **Mencatat alasan penolakan** untuk setiap data
- **Menyimpan nomor baris Excel** untuk memudahkan identifikasi
- **Timestamp penolakan** untuk tracking

### 2. Halaman Data Ditolak
- **Tampilan tabel** dengan informasi lengkap data yang ditolak
- **Filter dan pencarian** berdasarkan nama, nomor paspor, gender, status
- **Pagination** untuk data yang banyak
- **Detail modal** untuk melihat informasi lengkap setiap data

### 3. Download Data Ditolak
- **Export ke Excel** dengan format yang sama seperti template import
- **Informasi tambahan** seperti alasan penolakan dan nomor baris
- **Styling khusus** untuk membedakan dengan data normal

### 4. Download Data Gagal Import
- **Export data yang gagal** masuk ke database dalam format Excel
- **Format khusus** untuk data yang gagal diimport
- **Informasi lengkap** termasuk alasan penolakan dan timestamp

### 4. Manajemen Data Ditolak
- **Hapus individual** data yang ditolak
- **Hapus semua** data yang ditolak sekaligus
- **Konfirmasi sebelum hapus** untuk mencegah kesalahan

## Struktur Database

### Tabel `peserta_reject`
```sql
CREATE TABLE `peserta_reject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `nomor_paspor` varchar(50) NOT NULL,
  `no_visa` varchar(50) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0 COMMENT '0=On Target, 1=Already, 2=Done',
  `tanggal` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `flag_doc` varchar(100) DEFAULT NULL,
  `reject_reason` text NOT NULL COMMENT 'Alasan penolakan data',
  `row_number` int(11) DEFAULT NULL COMMENT 'Nomor baris di file Excel',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nomor_paspor` (`nomor_paspor`),
  KEY `idx_flag_doc` (`flag_doc`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Alur Kerja

### 1. Proses Import
1. User upload file Excel
2. **Sistem otomatis truncate tabel `peserta_reject`** untuk memulai import bersih
3. Sistem membaca file baris per baris
4. Untuk setiap baris:
   - Validasi data (nama, paspor, password wajib)
   - Validasi format email (tidak boleh mengandung tanda petik ganda)
   - Cek duplikasi nomor paspor
   - Coba insert ke database
5. Jika gagal:
   - Simpan ke tabel `peserta_reject`
   - Catat alasan penolakan
   - Catat nomor baris Excel
6. Tampilkan hasil import dengan link ke data ditolak

### 2. Review Data Ditolak
1. User akses menu "Data Ditolak"
2. Sistem tampilkan tabel data yang ditolak
3. User bisa:
   - Filter berdasarkan kriteria
   - Lihat detail setiap data
   - Download data dalam format Excel
   - Hapus data yang tidak diperlukan

## File yang Dimodifikasi/Dibuat

### Model
- `application/models/Peserta_reject_model.php` - Model untuk tabel peserta_reject

### Controller
- `application/controllers/Database.php` - Modifikasi fungsi process_import dan tambah fungsi baru

### View
- `application/views/database/rejected_data.php` - Halaman tampilan data ditolak
- `application/views/database/import.php` - Modifikasi untuk tampilkan info data ditolak
- `application/views/templates/sidebar.php` - Tambah menu Data Ditolak

### Config
- `application/config/routes.php` - Tambah route untuk fitur baru

### SQL
- `create_peserta_reject_table.sql` - Script pembuatan tabel

## Cara Penggunaan

### 1. Setup Database
```sql
-- Jalankan script SQL untuk membuat tabel
source create_peserta_reject_table.sql;
```

### 2. Import Data
1. Akses menu "Data Peserta" → "Import"
2. Upload file Excel
3. Sistem akan memproses dan menampilkan hasil
4. Jika ada data ditolak, akan muncul notifikasi dengan link

### 3. Review Data Ditolak
1. Klik "Lihat Data Ditolak" atau akses menu "Data Ditolak"
2. Review data yang ditolak
3. Download file Excel untuk perbaikan
4. Perbaiki data di Excel
5. Import ulang file yang sudah diperbaiki

### 4. Download Data Ditolak
1. Di halaman Data Ditolak, klik "Download Data Ditolak"
2. File akan berisi semua data yang ditolak
3. Format sama dengan template import + kolom tambahan

### 5. Download Data Gagal Import
1. Di halaman Data Ditolak, klik "Download Data Gagal Import"
2. File akan berisi data yang gagal masuk ke database
3. Format khusus untuk data yang gagal diimport

## Alasan Penolakan yang Ditangani

### 1. Validasi Data
- **Nama Peserta kosong**
- **Nomor Paspor kosong**
- **Password kosong**
- **Email mengandung tanda petik ganda** (`"`) - tidak diperbolehkan

### 2. Duplikasi Data
- **Nomor Paspor sudah ada** dalam database

### 3. Error Database
- **Gagal menyimpan** ke database karena error sistem

## Keunggulan Sistem

### 1. Transparansi
- User tahu persis data mana yang ditolak
- Alasan penolakan jelas dan spesifik
- Nomor baris Excel memudahkan identifikasi

### 2. Kemudahan Review
- Interface yang user-friendly
- Filter dan pencarian yang fleksibel
- Export data untuk perbaikan

### 3. Keamanan Data
- Data yang ditolak tidak hilang
- Bisa di-review dan diperbaiki
- Backup otomatis dalam tabel terpisah

### 4. Efisiensi
- Tidak perlu cek manual data yang gagal
- Download langsung untuk perbaikan
- Tracking waktu penolakan
- **Auto-truncate** tabel reject saat import untuk data bersih

## Troubleshooting

### 1. Data Tidak Muncul di Tabel Ditolak
- Cek apakah tabel `peserta_reject` sudah dibuat
- Pastikan model `Peserta_reject_model` sudah di-load
- Cek error log aplikasi

### 2. Download Excel Gagal
- Pastikan library PHPExcel sudah terinstall
- Cek permission folder untuk write file
- Pastikan memory limit cukup untuk file besar

### 3. Menu Data Ditolak Tidak Muncul
- Pastikan route sudah ditambahkan di `routes.php`
- Cek apakah file view `rejected_data.php` ada
- Pastikan tidak ada error syntax di sidebar

### 4. Validasi Email Tidak Berfungsi
- Pastikan kode validasi email sudah ditambahkan di `process_import()`
- Cek apakah fungsi `strpos()` berjalan dengan benar
- Pastikan data email di Excel tidak mengandung karakter khusus

## Maintenance

### 1. Backup Data Ditolak
```sql
-- Backup data yang ditolak
CREATE TABLE peserta_reject_backup_YYYYMMDD AS 
SELECT * FROM peserta_reject;
```

### 2. Cleanup Data Lama
```sql
-- Hapus data yang ditolak lebih dari 30 hari
DELETE FROM peserta_reject 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### 3. Monitoring
- Monitor ukuran tabel `peserta_reject`
- Cek pola penolakan data yang sering terjadi
- Review alasan penolakan untuk perbaikan sistem
- Monitor frekuensi penolakan email dengan tanda petik ganda

## Kesimpulan

Sistem penanganan data import yang ditolak ini memberikan solusi komprehensif untuk mengelola data yang gagal diimport. Dengan fitur yang lengkap dan interface yang user-friendly, user dapat dengan mudah mengidentifikasi, review, dan memperbaiki data yang ditolak sebelum melakukan import ulang.
