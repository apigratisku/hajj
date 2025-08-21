# Manajemen Email - Sistem Hajj

## Deskripsi
Menu Manajemen Email adalah fitur untuk mengelola akun email di cPanel melalui UAPI (User API) menggunakan cURL dengan autentikasi Basic Auth. Fitur ini memungkinkan administrator untuk melakukan operasi CRUD (Create, Read, Update, Delete) pada akun email yang terdaftar di cPanel.

## Fitur Utama

### 1. Cek Akun Email Terdaftar di cPanel
- Menampilkan daftar semua akun email yang ada di cPanel
- Informasi detail: email address, quota, usage, status, dan tanggal pembuatan
- Visualisasi penggunaan quota dengan progress bar
- Status akun (Active/Suspended)
- Auto-refresh setiap 5 menit

### 2. CRUD Akun Email di cPanel
- **Create**: Membuat akun email baru dengan konfigurasi quota
- **Read**: Melihat detail akun email dan penggunaan
- **Update**: Mengubah password dan quota akun email
- **Delete**: Menghapus akun email dari cPanel

## Teknologi yang Digunakan

### Backend
- **CodeIgniter 3.x**: Framework PHP
- **cURL**: Untuk komunikasi dengan cPanel UAPI
- **Basic Auth**: Autentikasi username:password
- **JSON**: Format response dari cPanel UAPI

### Frontend
- **Bootstrap 5.3.0**: Framework CSS
- **Font Awesome**: Icon library
- **Vanilla JavaScript**: Client-side scripting
- **AJAX**: Asynchronous data loading

## Konfigurasi

### 1. File Konfigurasi
Edit file `application/config/cpanel_config.php`:

```php
$config['cpanel'] = array(
    'username' => 'your_cpanel_username',     // Username cPanel
    'password' => 'your_cpanel_password',     // Password cPanel
    'domain' => 'yourdomain.com',             // Domain cPanel
    'port' => '2083',                         // Port cPanel (2083 untuk SSL)
    'protocol' => 'https',                    // Protocol (http/https)
    'timeout' => 30,                          // Timeout request
    'ssl_verify' => false,                    // SSL verification
    'debug' => false                          // Debug mode
);
```

### 2. Telegram Notifications
Fitur ini terintegrasi dengan sistem notifikasi Telegram yang sudah ada. Notifikasi akan dikirim untuk:
- Create email account
- Update email account
- Delete email account

## Struktur File

```
application/
├── controllers/
│   └── Email_management.php          # Controller utama
├── views/
│   └── email_management/
│       ├── index.php                 # Halaman utama (list email)
│       ├── create.php                # Form tambah email
│       └── edit.php                  # Form edit email
├── config/
│   └── cpanel_config.php             # Konfigurasi cPanel
└── libraries/
    └── Telegram_notification.php     # Library notifikasi (updated)
```

## API Endpoints

### cPanel UAPI Endpoints yang Digunakan

1. **List Email Accounts**
   ```
   GET /execute/Email/list_pops
   ```

2. **Create Email Account**
   ```
   POST /execute/Email/add_pop
   Parameters: email, pass, quota, domain
   ```

3. **Update Email Account**
   ```
   POST /execute/Email/edit_pop
   Parameters: email, pass (optional), quota
   ```

4. **Delete Email Account**
   ```
   POST /execute/Email/delete_pop
   Parameters: email
   ```

5. **Test Connection**
   ```
   GET /execute/UAPI/get_user_information
   ```

## Cara Penggunaan

### 1. Akses Menu
- Login sebagai admin
- Klik menu "Manajemen Email" di sidebar
- Menu hanya tersedia untuk user dengan role 'admin'

### 2. Melihat Daftar Email
- Halaman utama menampilkan tabel semua akun email
- Klik tombol "Refresh" untuk memperbarui data
- Klik tombol "Test Koneksi" untuk mengecek koneksi ke cPanel

### 3. Membuat Akun Email Baru
- Klik tombol "Tambah Email"
- Isi form dengan data yang diperlukan:
  - Email address (format: user@domain.com)
  - Password (minimal 8 karakter)
  - Quota (10MB - 10GB)
- Gunakan preset quota untuk kemudahan
- Klik "Buat Akun Email"

### 4. Mengedit Akun Email
- Klik tombol edit (ikon pensil) pada baris email
- Ubah password (opsional) dan/atau quota
- Pastikan quota baru lebih besar dari penggunaan saat ini
- Klik "Simpan Perubahan"

### 5. Menghapus Akun Email
- Klik tombol delete (ikon trash) pada baris email
- Konfirmasi penghapusan
- Akun email akan dihapus dari cPanel

## Validasi dan Keamanan

### 1. Validasi Input
- Format email harus valid
- Password minimal 8 karakter dengan kombinasi huruf dan angka
- Quota harus dalam range yang diizinkan
- Quota baru tidak boleh lebih kecil dari penggunaan saat ini

### 2. Keamanan
- Autentikasi admin required
- Basic Auth untuk koneksi cPanel
- Validasi session untuk setiap request
- Error handling yang aman

### 3. Error Handling
- Koneksi gagal ke cPanel
- Email sudah ada
- Password tidak memenuhi syarat
- Quota tidak valid
- Permission denied

## Fitur Tambahan

### 1. Password Generator
- Generate password otomatis yang kuat
- Kombinasi huruf besar, kecil, angka, dan karakter khusus
- Minimal 12 karakter

### 2. Quota Presets
- Basic: 100 MB
- Standard: 250 MB
- Premium: 500 MB
- Business: 1 GB
- Enterprise: 5 GB

### 3. Visual Indicators
- Progress bar untuk penggunaan quota
- Color coding: hijau (aman), kuning (perhatian), merah (kritis)
- Status badges untuk akun aktif/suspended

### 4. Real-time Updates
- Auto-refresh setiap 5 menit
- Manual refresh dengan tombol
- Loading indicators

## Troubleshooting

### 1. Koneksi Gagal ke cPanel
- Periksa username dan password cPanel
- Pastikan domain dan port benar
- Cek apakah UAPI diaktifkan di cPanel
- Periksa firewall/security settings

### 2. Email Tidak Muncul
- Pastikan ada akun email di cPanel
- Cek permission UAPI untuk modul Email
- Periksa log error di cPanel

### 3. Gagal Membuat/Edit Email
- Periksa quota hosting
- Pastikan domain email valid
- Cek apakah email sudah ada
- Periksa permission untuk operasi tersebut

### 4. Notifikasi Telegram Tidak Terkirim
- Periksa konfigurasi bot token dan chat ID
- Pastikan bot memiliki permission untuk mengirim pesan
- Cek koneksi internet

## Log dan Monitoring

### 1. Error Logging
Semua error dan aktivitas dicatat di:
- CodeIgniter log files
- cPanel error logs
- Telegram notifications

### 2. Activity Tracking
- Semua operasi CRUD dicatat
- Notifikasi Telegram untuk setiap aktivitas
- Timestamp dan user information

## Dependencies

### Required PHP Extensions
- cURL
- JSON
- OpenSSL (untuk HTTPS)

### Required Libraries
- CodeIgniter 3.x
- Bootstrap 5.3.0
- Font Awesome 5.x

## Support

Untuk bantuan teknis atau pertanyaan, silakan hubungi administrator sistem.

---

**Versi**: 1.0  
**Tanggal**: 19 Agustus 2025  
**Author**: Sistem Hajj Development Team
