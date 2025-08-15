# Fitur Upload Barcode

## Overview
Fitur upload barcode memungkinkan pengguna untuk mengupload gambar barcode pada field barcode di menu Peserta dan Todo. Gambar yang diupload akan dienkripsi namanya untuk keamanan dan disimpan di server.

## Fitur Utama

### 1. Upload Gambar Barcode
- Mendukung format gambar: JPG, JPEG, PNG, GIF, WebP
- Maksimal ukuran file: 5MB
- Preview gambar sebelum upload
- Validasi tipe file dan ukuran

### 2. Enkripsi Nama File
- Format nama file: `flagdoc_encrypted_originalname_timestamp.extension`
- Enkripsi menggunakan base64 dan MD5
- Contoh: `Batch-001_a1b2c3d4_1703123456.jpg`

### 3. Keamanan
- Validasi file type dan size
- Sanitasi nama file
- Proteksi direktori upload dengan .htaccess
- Validasi flag dokumen sebelum upload

## Struktur File

### Controller
- `application/controllers/Upload.php` - Controller untuk menangani upload

### Routes
```php
$route['upload/upload_barcode'] = 'upload/upload_barcode';
$route['upload/delete_barcode'] = 'upload/delete_barcode';
$route['upload/view_barcode/(:any)'] = 'upload/view_barcode/$1';
```

### Direktori
- `assets/uploads/barcode/` - Direktori penyimpanan gambar barcode
- `assets/uploads/.htaccess` - Proteksi direktori upload

## Cara Penggunaan

### 1. Menu Peserta (Edit)
1. Buka menu Peserta
2. Klik tombol Edit pada data yang ingin diubah
3. Pada field Barcode, klik tombol kamera (📷)
4. Pilih file gambar barcode
5. Gambar akan otomatis terupload dan nama file akan masuk ke field barcode
6. Klik Update untuk menyimpan

### 2. Menu Todo (Edit Inline)
1. Buka menu Todo
2. Klik tombol Edit pada baris yang ingin diubah
3. Pada field Barcode, klik tombol kamera (📷)
4. Pilih file gambar barcode
5. Gambar akan otomatis terupload dan nama file akan masuk ke field barcode
6. Klik Save untuk menyimpan

## Validasi

### File Type
- Hanya menerima file gambar: JPG, JPEG, PNG, GIF, WebP
- Validasi menggunakan MIME type

### File Size
- Maksimal 5MB per file
- Validasi sebelum upload

### Flag Dokumen
- Flag dokumen harus diisi sebelum upload
- Nama file akan menggunakan flag dokumen sebagai prefix

## Error Handling

### Error Messages
- "Pilih file gambar yang valid" - File bukan gambar
- "Ukuran file terlalu besar. Maksimal 5MB" - File terlalu besar
- "Flag dokumen harus diisi terlebih dahulu" - Flag dokumen kosong
- "Gagal mengupload gambar" - Error server

### Logging
- Error log disimpan di `application/logs/`
- Format: `Upload barcode error: [error message]`

## Keamanan

### File Upload Security
- Validasi MIME type
- Validasi file extension
- Sanitasi nama file
- Pembatasan ukuran file

### Directory Protection
```apache
# Protect uploads directory from direct access
Deny from all

# Allow access only to image files
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
    Allow from ::1
</FilesMatch>
```

### Access Control
- Hanya user yang sudah login yang bisa upload
- Validasi session di setiap request

## API Endpoints

### Upload Barcode
```
POST /upload/upload_barcode
Content-Type: multipart/form-data

Parameters:
- barcode_image: File gambar
- flag_doc: String flag dokumen

Response:
{
    "status": "success|error",
    "message": "Pesan response",
    "filename": "Nama file yang diupload",
    "file_url": "URL file",
    "original_name": "Nama file asli"
}
```

### Delete Barcode
```
POST /upload/delete_barcode
Content-Type: application/json

Parameters:
- filename: Nama file yang akan dihapus

Response:
{
    "status": "success|error",
    "message": "Pesan response"
}
```

### View Barcode
```
GET /upload/view_barcode/{filename}
Content-Type: image/*

Response: File gambar
```

## Troubleshooting

### Upload Gagal
1. Periksa permission direktori `assets/uploads/barcode/`
2. Periksa ukuran file (maksimal 5MB)
3. Periksa tipe file (hanya gambar)
4. Periksa flag dokumen sudah diisi

### File Tidak Muncul
1. Periksa file ada di direktori `assets/uploads/barcode/`
2. Periksa permission file
3. Periksa .htaccess tidak memblokir akses

### Error Server
1. Periksa log error di `application/logs/`
2. Periksa konfigurasi PHP upload settings
3. Periksa disk space server

## Maintenance

### Cleanup Files
- File barcode disimpan permanen
- Untuk menghapus file lama, gunakan endpoint delete atau hapus manual

### Backup
- Backup direktori `assets/uploads/barcode/` secara berkala
- File barcode penting untuk data peserta

### Monitoring
- Monitor ukuran direktori upload
- Monitor disk space server
- Monitor error log upload

## Dependencies

### PHP Extensions
- `mysqli` - Database connection
- `fileinfo` - File type detection
- `gd` atau `imagick` - Image processing (opsional)

### JavaScript
- `fetch` API - Upload file
- `FileReader` - Preview gambar
- `FormData` - Form data handling

### CSS
- Bootstrap 5.3.0
- Font Awesome 6.0.0
- Custom CSS untuk styling upload

## Changelog

### v1.0.0
- Initial release
- Upload gambar barcode dengan enkripsi nama
- Preview gambar sebelum upload
- Validasi file type dan size
- Proteksi direktori upload
- Support menu Peserta dan Todo
