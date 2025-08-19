# Test Telegram Notification Extended

## Ringkasan Test

File ini berisi test case untuk memverifikasi implementasi fitur Telegram notification yang telah diperluas untuk mencakup:

1. **User Management** - CRUD User, Enable/Disable User
2. **Todo List** - Update, Create, Export, Filter
3. **Dashboard** - Mark Schedule Complete
4. **Database** - CRUD Peserta, Import/Export
5. **Settings** - Backup Database

## Test Cases

### **1. Test User Management**

#### **1.1 Create User**
```bash
# Akses halaman tambah user
GET /user/add

# Submit form create user
POST /user/add
{
    "username": "testuser",
    "password": "password123",
    "nama_lengkap": "Test User",
    "role": "operator",
    "status": 1
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Tambah Data User
📝 Detail: Nama: Test User | Username: testuser, Role: operator
```

#### **1.2 Update User**
```bash
# Akses halaman edit user
GET /user/edit/123

# Submit form update user
POST /user/edit/123
{
    "username": "testuser_updated",
    "nama_lengkap": "Test User Updated",
    "role": "admin",
    "status": 1
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Update Data User
📝 Detail: Nama: Test User Updated | Username: testuser_updated, Role: admin
```

#### **1.3 Enable User**
```bash
# Enable user
GET /user/enable/123

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Aktifkan User
📝 Detail: Nama: Test User | Username: testuser
```

#### **1.4 Disable User**
```bash
# Disable user
GET /user/disable/123

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Nonaktifkan User
📝 Detail: Nama: Test User | Username: testuser
```

#### **1.5 Delete User**
```bash
# Delete user
GET /user/delete/123

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Hapus Data User
📝 Detail: Nama: Test User | Username: testuser, Role: admin
```

### **2. Test Todo List**

#### **2.1 Update Data Peserta (Form)**
```bash
# Akses halaman edit peserta di todo list
GET /todo/edit/456

# Submit form update
POST /todo/update/456
{
    "nama": "Ahmad Hidayat",
    "nomor_paspor": "A123456",
    "no_visa": "V789012",
    "status": 1
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Update Data Peserta
📝 Detail: Nama: Ahmad Hidayat | ID: 456 (Todo List)
```

#### **2.2 Update Data Peserta (AJAX)**
```bash
# Update via AJAX
POST /todo/update_ajax/456
Content-Type: application/json
{
    "nama": "Ahmad Hidayat Updated",
    "status": 2
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Update Data Peserta
📝 Detail: Nama: Ahmad Hidayat Updated | ID: 456 (Todo List - AJAX)
```

#### **2.3 Create Data Peserta**
```bash
# Akses halaman tambah peserta di todo list
GET /todo/tambah

# Submit form create
POST /todo/store
{
    "nama": "New Peserta",
    "nomor_paspor": "B789012",
    "password": "password123"
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Tambah Data Peserta
📝 Detail: Nama: New Peserta | Username: B789012 (Todo List)
```

#### **2.4 Export Data**
```bash
# Export data dari todo list
GET /todo/export?nama=Ahmad&flag_doc=Batch-001

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Export Data berhasil
📝 Detail: File: Database_Peserta_2025-08-19_22-00-00.xlsx | 25 record
```

#### **2.5 Filter Data**
```bash
# Filter data di todo list
GET /todo/index?nama=Ahmad&flag_doc=Batch-001&tanggaljam=2025-08-19

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Filter Data Todo List
📝 Detail: Filter: Nama: Ahmad, Flag_doc: Batch-001, Tanggaljam: 2025-08-19
```

### **3. Test Dashboard**

#### **3.1 Mark Schedule Complete**
```bash
# Mark schedule complete via AJAX
POST /dashboard/mark_schedule_complete
{
    "tanggal": "2025-08-19",
    "jam": "08:00:00",
    "flag_doc": "Batch-001"
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Dashboard: Mark Schedule Complete
📝 Detail: Tanggal: 2025-08-19, Jam: 08:00:00, Flag Doc: Batch-001
```

### **4. Test Database**

#### **4.1 Update Data Peserta**
```bash
# Update data peserta di database
POST /database/update/789
{
    "nama": "John Doe",
    "nomor_paspor": "C345678"
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Update Data Peserta
📝 Detail: Nama: John Doe | ID: 789
```

#### **4.2 Delete Data Peserta**
```bash
# Delete data peserta
GET /database/delete/789

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Hapus Data Peserta
📝 Detail: Nama: John Doe | ID: 789
```

#### **4.3 Import Data**
```bash
# Import file Excel
POST /database/process_import
file: data_peserta.xlsx

# Expected Telegram Notification (Success):
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Import Data berhasil
📝 Detail: File: data_peserta.xlsx | 25 record

# Expected Telegram Notification (Failed):
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Import Data gagal
📝 Detail: File: data_peserta.xlsx | 5 record
```

#### **4.4 Download Successful Data**
```bash
# Download data berhasil
GET /database/download_successful_data

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Download Data Import Berhasil
📝 Detail: File: data_import_berhasil_2025-08-19_22-00-00.xlsx | 25 record
```

### **5. Test Settings**

#### **5.1 Backup Database Success**
```bash
# Backup database
POST /settings/backup_database

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Backup Database berhasil
📝 Detail: File: backup_hajj_2025-08-19_22-00-00.sql
```

#### **5.2 Backup Database Failed**
```bash
# Backup database (failed scenario)
POST /settings/backup_database

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Backup Database gagal
📝 Detail: 
```

### **6. Test Authentication**

#### **6.1 Login Success**
```bash
# Login berhasil
POST /auth/login
{
    "username": "admin",
    "password": "password123"
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Login berhasil
📝 Detail: Username: admin
```

#### **6.2 Login Failed**
```bash
# Login gagal
POST /auth/login
{
    "username": "admin",
    "password": "wrongpassword"
}

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Unknown User
🔰 Level: Unknown Level
⚡ Aktivitas: Login gagal
📝 Detail: Username: admin
```

#### **6.3 Logout**
```bash
# Logout
GET /auth/logout

# Expected Telegram Notification:
📋 Log Report 19/08/2025 22.00
👤 User: Admin Sistem
🔰 Level: admin
⚡ Aktivitas: Logout dari sistem
📝 Detail: 
```

## Verification Steps

### **1. Check Telegram Bot**
1. Pastikan bot token valid: `8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ`
2. Pastikan chat ID benar: `250170651`
3. Pastikan bot sudah di-start di chat

### **2. Check CodeIgniter Logs**
```bash
# Cek log untuk error
tail -f application/logs/log-*.php

# Expected log entries:
# [INFO] Telegram notification sent successfully
# [ERROR] Telegram notification failed: [error details]
```

### **3. Check Database**
```sql
-- Cek apakah data tersimpan dengan benar
SELECT * FROM users WHERE username = 'testuser';
SELECT * FROM peserta WHERE nama = 'Ahmad Hidayat';
```

### **4. Check File Permissions**
```bash
# Pastikan folder dapat ditulis
ls -la application/logs/
chmod 755 application/logs/
```

## Expected Results

### **Success Criteria:**
1. ✅ Semua notifikasi terkirim ke Telegram
2. ✅ Format pesan sesuai template
3. ✅ Data tersimpan dengan benar di database
4. ✅ Tidak ada error di log
5. ✅ User experience tidak terganggu

### **Error Handling:**
1. ✅ Jika Telegram tidak tersedia, sistem tetap berjalan
2. ✅ Error log dicatat dengan detail
3. ✅ User mendapat feedback yang sesuai
4. ✅ Tidak ada crash atau fatal error

## Troubleshooting

### **Jika Notifikasi Tidak Terkirim:**

#### **1. Cek Koneksi Internet**
```php
// Test koneksi
$is_connected = $this->telegram_notification->test_connection();
var_dump($is_connected);
```

#### **2. Cek Bot Configuration**
```php
// Cek bot token dan chat ID
echo "Bot Token: " . $this->telegram_notification->bot_token;
echo "Chat ID: " . $this->telegram_notification->chat_id;
```

#### **3. Cek Error Log**
```bash
grep "Telegram" application/logs/log-*.php
```

#### **4. Cek Network**
```bash
# Test koneksi ke Telegram API
curl -X GET "https://api.telegram.org/bot8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ/getMe"
```

## Conclusion

Setelah menjalankan semua test case di atas, fitur Telegram notification yang diperluas seharusnya berfungsi dengan baik untuk:

- ✅ **User Management** - Semua aktivitas CRUD user
- ✅ **Todo List** - Update, create, export, filter
- ✅ **Dashboard** - Mark schedule complete
- ✅ **Database** - CRUD peserta, import/export
- ✅ **Settings** - Backup database
- ✅ **Authentication** - Login/logout

Semua aktivitas akan tercatat dan dikirim ke Telegram Bot untuk monitoring real-time.
