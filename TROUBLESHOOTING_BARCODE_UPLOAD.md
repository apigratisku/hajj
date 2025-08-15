# Troubleshooting Upload Barcode

## Masalah: Nama file barcode tidak masuk ke database

### Langkah-langkah Pemeriksaan:

#### 1. Periksa Field Database
Jalankan file `test_barcode_field.php` untuk memverifikasi field barcode ada di database:
```
http://localhost/hajj/test_barcode_field.php
```

Jika field tidak ada, jalankan SQL script:
```sql
ALTER TABLE peserta ADD COLUMN IF NOT EXISTS barcode VARCHAR(255) NULL COMMENT 'Nama file gambar barcode';
```

#### 2. Periksa Log Debug
Buka browser developer tools (F12) dan lihat console untuk debug messages:
- "Barcode value found in container: [filename]"
- "Barcode value found directly: [filename]"
- "Saving data to server: [data]"

#### 3. Periksa Server Log
Cek file log CodeIgniter di `application/logs/` untuk melihat:
- "Todo update_ajax - Raw input: [data]"
- "Todo update_ajax - Barcode value: [value]"
- "Transaksi_model update - Barcode in filtered data: [value]"

#### 4. Test Upload Manual
1. Buka halaman Todo
2. Edit data peserta
3. Upload gambar barcode
4. Periksa apakah nama file muncul di input field
5. Simpan data
6. Periksa database apakah nama file tersimpan

#### 5. Periksa Struktur HTML
Pastikan field barcode memiliki struktur yang benar:
```html
<td class="barcode text-center" data-field="barcode" data-value="[filename]">
    <span class="display-value">[filename]</span>
    <div class="edit-field" style="display:none;">
        <div class="barcode-edit-container">
            <input type="text" class="form-control" value="[filename]">
            <button class="barcode-upload-btn">...</button>
        </div>
    </div>
</td>
```

### Solusi Umum:

#### 1. Clear Browser Cache
- Tekan Ctrl+F5 untuk hard refresh
- Clear browser cache dan cookies

#### 2. Periksa Permission
- Pastikan folder `assets/uploads/barcode/` memiliki permission write (755)
- Pastikan file dapat dibuat di folder tersebut

#### 3. Periksa JavaScript Errors
- Buka browser developer tools
- Lihat tab Console untuk error JavaScript
- Lihat tab Network untuk error AJAX

#### 4. Test dengan Data Sederhana
Coba upload file dengan nama sederhana tanpa spasi atau karakter khusus.

#### 5. Test Keamanan
Jalankan file `test_barcode_security.php` untuk memverifikasi keamanan:
```
http://localhost/hajj/test_barcode_security.php
```

#### 6. Test Akses Gambar
- **Akses Aman**: `https://domain.com/upload/view_barcode/filename.jpg` (memerlukan login)
- **Akses Diblokir**: `https://domain.com/assets/uploads/barcode/filename.jpg` (diblokir .htaccess)

### Debug Commands:

#### 1. Test Database Connection
```php
// Di controller, tambahkan:
log_message('debug', 'Database connection test');
$result = $this->db->query("SELECT COUNT(*) as count FROM peserta");
log_message('debug', 'Database test result: ' . json_encode($result->row()));
```

#### 2. Test Field Existence
```php
// Di model, tambahkan:
$fields = $this->db->list_fields($this->table);
log_message('debug', 'Available fields: ' . json_encode($fields));
```

#### 3. Test Upload Response
```php
// Di controller Upload, tambahkan:
log_message('debug', 'Upload response: ' . json_encode($response));
```

### File yang Diperbaiki:
1. `application/controllers/Upload.php` - Menambahkan `barcode_value` dalam response
2. `application/controllers/Todo.php` - Menambahkan debug logging
3. `application/models/Transaksi_model.php` - Menambahkan debug logging
4. `application/views/database/todo.php` - Memperbaiki JavaScript untuk menangani field barcode
5. `application/views/database/edit.php` - Menggunakan `barcode_value` untuk database

### Status Perbaikan:
- ✅ Controller Upload mengirim `barcode_value`
- ✅ JavaScript menggunakan `barcode_value` untuk database
- ✅ Debug logging ditambahkan di semua level
- ✅ Special handling untuk field barcode di JavaScript
- ✅ File test dan SQL script dibuat
- ✅ **Session-based security implemented**
- ✅ **Direct file access blocked**
- ✅ **Security headers configured**
- ✅ **Audit logging enabled**
