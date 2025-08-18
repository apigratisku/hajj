# Troubleshooting Error: "Unexpected token '<', is not valid JSON"

## Deskripsi Error

Error ini terjadi ketika server mengembalikan HTML alih-alih JSON yang diharapkan. Ini biasanya terjadi karena:

1. **Session expired** - User sudah logout
2. **Permission denied** - User tidak memiliki akses
3. **Server error** - PHP error yang menyebabkan halaman error HTML
4. **Route tidak ditemukan** - 404 error

## Gejala Error

```
Error name: SyntaxError
Error message: Unexpected token '<', "<div style"... is not valid JSON
```

## Langkah Troubleshooting

### Step 1: Cek Session Status
1. Lihat informasi debug di halaman settings
2. Pastikan "Session Logged In" menunjukkan "true"
3. Pastikan "Session Role" menunjukkan "admin"

### Step 2: Test Connection
1. Klik tombol **"Test Connection"**
2. Lihat response di Console (F12 > Console)
3. Pastikan response adalah JSON, bukan HTML

### Step 3: Cek Log File
1. Buka file log di `application/logs/log-YYYY-MM-DD.php`
2. Cari error yang terjadi saat backup
3. Analisis error yang muncul

### Step 4: Refresh Session
1. Klik tombol **"Refresh Halaman"**
2. Atau klik tombol **"Login Ulang"** jika session expired
3. Coba backup lagi

## Solusi Berdasarkan Penyebab

### 1. **Session Expired**
**Gejala:**
- Response HTML menunjukkan halaman login
- Session Logged In: false

**Solusi:**
1. Klik tombol **"Login Ulang"**
2. Login kembali dengan kredensial yang benar
3. Pastikan role adalah "admin"
4. Coba backup lagi

### 2. **Permission Denied**
**Gejala:**
- Response HTML menunjukkan halaman error
- Session Logged In: true tapi role bukan admin

**Solusi:**
1. Pastikan user memiliki role "admin"
2. Hubungi administrator untuk memberikan akses
3. Login dengan user yang memiliki akses admin

### 3. **Server Error**
**Gejala:**
- Response HTML menunjukkan error PHP
- Log file menunjukkan error PHP

**Solusi:**
1. Cek log file untuk detail error
2. Hubungi provider hosting
3. Pastikan semua requirement terpenuhi

### 4. **Route Not Found**
**Gejala:**
- Response HTML menunjukkan 404 error
- URL endpoint tidak ditemukan

**Solusi:**
1. Pastikan URL endpoint benar
2. Cek konfigurasi routing
3. Pastikan file controller ada

## Debug Information

### Console Log
```javascript
// Lihat di Console (F12 > Console)
Response status: 200
Response headers: Headers {...}
Non-JSON response: <!DOCTYPE html>...
```

### Debug Info di Halaman
```
Session Logged In: true/false
Session Role: admin/user
Session User ID: 123
```

### Log File
```
# Cari di application/logs/log-YYYY-MM-DD.php
ERROR - Session expired
ERROR - Permission denied
ERROR - PHP Fatal error
```

## Langkah Pencegahan

### 1. **Session Management**
- Pastikan session timeout tidak terlalu pendek
- Implementasikan auto-refresh session
- Tambahkan pengecekan session di setiap request

### 2. **Error Handling**
- Tambahkan try-catch di semua endpoint
- Return JSON error alih-alih HTML error
- Log semua error untuk debugging

### 3. **Security**
- Validasi role di setiap endpoint
- Implementasikan CSRF protection
- Gunakan proper authentication

## Contoh Response yang Benar

### Success Response
```json
{
    "status": "success",
    "message": "Backup database berhasil dibuat",
    "filename": "backup_hajj_db_2025-01-15_10-30-00.sql",
    "file_size": "512.00 KB",
    "download_url": "http://localhost/hajj/settings/download_backup/backup_hajj_db_2025-01-15_10-30-00.sql"
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Session expired. Silakan login kembali.",
    "redirect": "http://localhost/hajj/auth"
}
```

## Contact Support

Jika masalah masih berlanjut:

1. **Kumpulkan informasi:**
   - Console log lengkap
   - Debug info dari halaman
   - Log file error
   - Screenshot error

2. **Berikan informasi ke support:**
   - URL yang diakses
   - User agent browser
   - Session status
   - Error message lengkap

3. **Alternatif:**
   - Gunakan browser lain
   - Clear cache dan cookies
   - Coba dari device lain
