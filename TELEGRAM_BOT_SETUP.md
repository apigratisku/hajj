# 📱 Telegram Bot Setup & Usage Guide

## 🎯 **Fitur Telegram Bot Hajj System**

Bot ini menyediakan akses cepat ke data dan statistik sistem Hajj melalui perintah Telegram.

### 📋 **Perintah yang Tersedia:**

1. **`/statistik_dashboard`** - Lihat statistik dashboard
2. **`/statistik_download_excel`** - Download data Excel
3. **`/statistik_download_pdf`** - Download data PDF
4. **`/history_data_harian`** - Lihat history update harian
5. **`/help`** - Bantuan
6. **`/start`** - Pesan selamat datang

---

## 🚀 **Setup Telegram Bot**

### **1. Buat Bot Telegram**

1. Buka Telegram dan cari **@BotFather**
2. Kirim perintah `/newbot`
3. Ikuti instruksi untuk membuat bot
4. Catat **Bot Token** yang diberikan

### **2. Konfigurasi Bot Token**

Edit file `application/controllers/Telegram_bot.php`:

```php
private $bot_token = 'YOUR_BOT_TOKEN_HERE';
private $chat_id = 'YOUR_CHAT_ID_HERE';
private $webhook_url = 'https://yourdomain.com/telegram_bot/webhook';
```

### **3. Set Webhook URL**

Ganti `yourdomain.com` dengan domain Anda yang sebenarnya.

### **4. Aktifkan Webhook**

Akses URL berikut untuk mengaktifkan webhook:
```
https://yourdomain.com/telegram_bot/set_webhook
```

### **5. Test Bot**

Akses URL berikut untuk test bot:
```
https://yourdomain.com/telegram_bot/test
```

---

## 📊 **Detail Perintah Bot**

### **1. `/statistik_dashboard`**

**Fungsi:** Menampilkan statistik lengkap data peserta

**Output:**
```
📊 STATISTIK DASHBOARD HAJJ
📅 Update: 15/01/2025 14:30:25

👥 Total Peserta: 150

✅ Status Done: 45 (30.0%)
🔄 Status Already: 30 (20.0%)
🎯 Status On Target: 75 (50.0%)

📈 Progress:
🟢🟢🟢🟢🟢🟢🔴🔴🔴🔵🔵🔵🔵🔵⚪⚪⚪⚪⚪⚪
```

**Fitur:**
- ✅ Progress bar visual
- ✅ Persentase status
- ✅ Inline keyboard untuk aksi cepat

### **2. `/statistik_download_excel`**

**Fungsi:** Download langsung file Excel ke chat Telegram

**Output:**
```
📊 Mempersiapkan file Excel...

⏳ Mohon tunggu sebentar...
```

**Setelah selesai:**
```
📊 DATA PESERTA EXCEL

📋 Fitur Excel:
• Freeze row header
• Warna kolom Done (hijau)
• Warna kolom Already (merah)
• Warna kolom On Target (biru)
• Statistik summary

📅 Generated: 15/01/2025 14:30:25
```

**Fitur:**
- ✅ File langsung terdownload ke chat
- ✅ Loading message saat proses
- ✅ Auto-delete file temporary
- ✅ Styling Excel dengan warna status

### **3. `/statistik_download_pdf`**

**Fungsi:** Download langsung file PDF ke chat Telegram

**Output:**
```
📄 Mempersiapkan file PDF...

⏳ Mohon tunggu sebentar...
```

**Setelah selesai:**
```
📄 DATA PESERTA PDF

📋 Fitur PDF:
• Format landscape
• Header dan footer
• Statistik summary
• Warna status

📅 Generated: 15/01/2025 14:30:25
```

**Fitur:**
- ✅ File langsung terdownload ke chat
- ✅ Loading message saat proses
- ✅ Auto-delete file temporary
- ✅ Format landscape A4
- ✅ Styling PDF dengan warna status

### **4. `/history_data_harian`**

**Fungsi:** Menampilkan perbandingan data Done vs Already harian

**Output:**
```
📅 HISTORY DATA HARIAN (DONE vs ALREADY)
📅 Update: 15/01/2025 14:30:25

📊 Perbandingan 7 Hari Terakhir:

📅 15/01/2025:
   ✅ Done: 25
   🔄 Already: 18
   📊 Total: 43

📅 14/01/2025:
   ✅ Done: 32
   🔄 Already: 15
   📊 Total: 47

📅 13/01/2025:
   ✅ Done: 28
   🔄 Already: 22
   📊 Total: 50

📈 GRAND TOTAL 7 HARI:
✅ Done: 85
🔄 Already: 55
📊 Total: 140

💡 Info: Data menunjukkan perbandingan status Done vs Already per hari.
```

**Fitur:**
- ✅ Perbandingan Done vs Already per hari
- ✅ Total per hari (Done + Already)
- ✅ Grand total 7 hari terakhir
- ✅ Hanya data dengan status 1 dan 2

---

## 🔧 **Fitur Teknis**

### **1. Inline Keyboard**

Bot menyediakan tombol inline untuk aksi cepat:
- 🔄 Refresh - Refresh statistik
- 📊 Download Excel - Download file Excel langsung ke chat
- 📄 Download PDF - Download file PDF langsung ke chat
- 📅 History Harian - Lihat perbandingan Done vs Already

### **2. Progress Bar Visual**

Statistik dashboard menampilkan progress bar dengan emoji:
- 🟢 = Status Done (Hijau)
- 🔴 = Status Already (Merah)
- 🔵 = Status On Target (Biru)
- ⚪ = Kosong

### **3. Error Handling**

Bot menangani error dengan baik:
- ❌ Pesan error yang informatif
- 📝 Log error untuk debugging
- 🔄 Retry mechanism

### **4. File Management**

- ✅ Auto-generate Excel/PDF files
- ✅ Temporary file storage (`uploads/temp/`)
- ✅ Auto-cleanup temporary files
- ✅ Direct file upload to Telegram
- ✅ Loading messages during processing

### **5. Security**

- ✅ Authorization check (bisa dikustomisasi)
- ✅ Input validation
- ✅ Rate limiting (bisa ditambahkan)

---

## 📁 **File yang Dibuat/Dimodifikasi**

### **1. File Baru:**
- `application/controllers/Telegram_bot.php` - Controller utama bot
- `TELEGRAM_BOT_SETUP.md` - Dokumentasi ini

### **2. File Dimodifikasi:**
- `application/libraries/Telegram_notification.php` - Ditambah method baru
- `application/models/Transaksi_model.php` - Ditambah method `get_all_for_export()`
- `application/controllers/Telegram_bot.php` - Modifikasi fungsi download dan history

---

## 🛠️ **Troubleshooting**

### **1. Bot Tidak Merespon**

**Penyebab:** Webhook tidak aktif
**Solusi:** 
1. Cek webhook info: `https://yourdomain.com/telegram_bot/get_webhook_info`
2. Set ulang webhook: `https://yourdomain.com/telegram_bot/set_webhook`

### **2. Error 403/404**

**Penyebab:** Domain tidak bisa diakses
**Solusi:**
1. Pastikan domain bisa diakses dari internet
2. Cek SSL certificate (HTTPS wajib)
3. Pastikan file `.htaccess` tidak memblokir

### **3. Bot Token Invalid**

**Penyebab:** Token salah atau expired
**Solusi:**
1. Cek token di @BotFather
2. Generate token baru jika perlu
3. Update token di kode

### **4. Database Error**

**Penyebab:** Koneksi database bermasalah
**Solusi:**
1. Cek koneksi database
2. Pastikan model `transaksi_model` berfungsi
3. Cek log error di `application/logs/`

### **5. File Download Error**

**Penyebab:** File tidak bisa di-generate atau upload
**Solusi:**
1. Pastikan direktori `uploads/temp/` ada dan writable
2. Cek library PHPExcel dan TCPDF terinstall
3. Cek permission file system
4. Cek log error untuk detail masalah

### **6. History Data Error**

**Penyebab:** Data history tidak muncul
**Solusi:**
1. Pastikan ada data dengan `updated_at` dalam 7 hari terakhir
2. Cek query di method `get_daily_done_already_comparison()`
3. Pastikan data memiliki status 1 atau 2

---

## 🔐 **Security Best Practices**

### **1. Authorization**

Edit method `is_authorized_user()` di `Telegram_bot.php`:

```php
private function is_authorized_user($user_id) {
    // Daftar user ID yang diizinkan
    $authorized_users = [
        123456789, // User ID 1
        987654321, // User ID 2
    ];
    
    return in_array($user_id, $authorized_users);
}
```

### **2. Rate Limiting**

Tambahkan rate limiting untuk mencegah spam:

```php
private function check_rate_limit($user_id) {
    $cache_key = "telegram_rate_limit_{$user_id}";
    $current_time = time();
    
    // Allow max 10 requests per minute
    $requests = $this->cache->get($cache_key) ?: 0;
    
    if ($requests >= 10) {
        return false;
    }
    
    $this->cache->set($cache_key, $requests + 1, 60);
    return true;
}
```

### **3. Input Validation**

Semua input dari Telegram divalidasi:
- ✅ Sanitasi text input
- ✅ Validasi user ID
- ✅ Escape HTML characters

---

## 📈 **Monitoring & Analytics**

### **1. Log Monitoring**

Bot mencatat semua aktivitas di log:
- `application/logs/telegram_bot.log`
- `application/logs/telegram_webhook.log`

### **2. Error Tracking**

Error ditangkap dan dicatat:
```php
log_message('error', 'Telegram bot error: ' . $e->getMessage());
```

### **3. Usage Statistics**

Bisa ditambahkan tracking untuk:
- Jumlah perintah per hari
- User yang paling aktif
- Perintah yang paling sering digunakan

---

## 🎉 **Kesimpulan**

Telegram Bot Hajj System memberikan akses cepat dan mudah ke data sistem melalui perintah sederhana. Bot ini sangat berguna untuk:

- ✅ Monitoring real-time
- ✅ Download data cepat
- ✅ Notifikasi otomatis
- ✅ Akses mobile-friendly

Dengan setup yang benar, bot akan berfungsi dengan baik dan memberikan pengalaman yang smooth untuk pengguna.
