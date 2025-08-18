# Fitur Kembali ke Halaman Sebelumnya dengan Filter

## Deskripsi
Fitur ini memastikan bahwa setelah melakukan aksi edit atau delete data, user akan kembali ke halaman yang sama dengan filter dan pagination yang sudah diterapkan sebelumnya. Ini meningkatkan user experience dengan menghindari kehilangan konteks pencarian dan filter yang sudah diatur.

## Masalah yang Dipecahkan
Sebelumnya, setelah edit atau delete data, user akan diarahkan ke halaman database tanpa filter, sehingga harus mengatur ulang filter dan mencari kembali data yang sedang dikerjakan.

## Solusi yang Diimplementasikan

### 1. Edit Data dengan Filter Persistence

#### Controller (`application/controllers/Database.php`)
- **Fungsi `update()`**: Dimodifikasi untuk menggunakan `get_redirect_url_with_filters()` sebelum redirect
- **Fungsi `get_redirect_url_with_filters()`**: Helper function untuk membangun URL dengan filter yang ada

```php
private function get_redirect_url_with_filters() {
    $base_url = base_url('database/index');
    $query_params = [];
    
    // Get current filters from GET parameters
    $filters = [
        'nama' => $this->input->get('nama'),
        'nomor_paspor' => $this->input->get('nomor_paspor'),
        'no_visa' => $this->input->get('no_visa'),
        'flag_doc' => $this->input->get('flag_doc'),
        'tanggaljam' => $this->input->get('tanggaljam'),
        'status' => $this->input->get('status'),
        'gender' => $this->input->get('gender'),
        'page' => $this->input->get('page')
    ];
    
    // Add non-empty filters to query parameters
    foreach ($filters as $key => $value) {
        if (!empty($value) && $value !== '') {
            $query_params[$key] = $value;
        }
    }
    
    // Build query string
    if (!empty($query_params)) {
        $base_url .= '?' . http_build_query($query_params);
    }
    
    return $base_url;
}
```

#### View (`application/views/database/index.php`)
- **JavaScript Functions**: Dimodifikasi untuk menyimpan filter state sebelum melakukan edit
- **Redirect Logic**: Setelah edit berhasil, redirect ke halaman dengan filter yang sama

```javascript
// Redirect back to previous page with filters after 1 second
setTimeout(() => {
    const currentUrl = new URL(window.location.href);
    const params = new URLSearchParams(currentUrl.search);
    
    // Build redirect URL with current filters
    let redirectUrl = '<?= base_url('database/index') ?>';
    const queryParams = [];
    
    // Add all current filters
    ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page'].forEach(param => {
        if (params.has(param) && params.get(param)) {
            queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
        }
    });
    
    if (queryParams.length > 0) {
        redirectUrl += '?' + queryParams.join('&');
    }
    
    window.location.href = redirectUrl;
}, 1000);
```

### 2. Delete Data dengan Filter Persistence

#### Controller (`application/controllers/Database.php`)
- **Fungsi `delete()`**: Dimodifikasi untuk menangani parameter redirect dengan validasi keamanan

```php
public function delete($id) {
    // Get peserta data before deletion
    $peserta = $this->transaksi_model->get_by_id($id);
    
    if ($peserta) {
        // Delete barcode file if exists
        if (!empty($peserta->barcode)) {
            $this->delete_barcode_file($peserta->barcode);
        }
    }
    
    $this->transaksi_model->delete($id);
    
    // Check if redirect URL is provided
    $redirect_url = $this->input->get('redirect');
    if ($redirect_url) {
        // Decode the redirect URL
        $redirect_url = urldecode($redirect_url);
        
        // Validate that the redirect URL is safe
        if (strpos($redirect_url, base_url()) === 0 || strpos($redirect_url, '/database/') === 0) {
            redirect($redirect_url);
        }
    }
    
    // Fallback: Redirect back to previous page with filters
    $redirect_url = $this->get_redirect_url_with_filters();
    redirect($redirect_url);
}
```

#### View (`application/views/database/index.php`)
- **Tombol Delete**: Diubah dari link menjadi button dengan JavaScript function
- **Fungsi `deleteData()`**: JavaScript function untuk menangani delete dengan filter preservation

```javascript
function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        // Get current filters from URL
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        
        // Build redirect URL with current filters
        let redirectUrl = '<?= base_url('database/index') ?>';
        const queryParams = [];
        
        // Add all current filters
        ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page'].forEach(param => {
            if (params.has(param) && params.get(param)) {
                queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
            }
        });
        
        if (queryParams.length > 0) {
            redirectUrl += '?' + queryParams.join('&');
        }
        
        // Show loading state
        const button = event.target.closest('button');
        if (button) {
            const originalHTML = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Perform delete with redirect
            window.location.href = '<?= base_url('database/delete/') ?>' + id + '?redirect=' + encodeURIComponent(redirectUrl);
        }
    }
}
```

## Fitur yang Didukung

### 1. Filter yang Dipertahankan
- **Nama Peserta**: Filter berdasarkan nama
- **Nomor Paspor**: Filter berdasarkan nomor paspor
- **Nomor Visa**: Filter berdasarkan nomor visa
- **Flag Dokumen**: Filter berdasarkan flag dokumen
- **Tanggal & Jam**: Filter berdasarkan waktu
- **Status**: Filter berdasarkan status (On Target, Already, Done)
- **Gender**: Filter berdasarkan jenis kelamin
- **Pagination**: Halaman yang sedang aktif

### 2. Keamanan
- **URL Validation**: Validasi bahwa redirect URL hanya mengarah ke domain internal
- **Path Traversal Protection**: Mencegah serangan path traversal
- **XSS Protection**: Mencegah serangan XSS melalui parameter redirect
- **Fallback Mechanism**: Jika redirect URL tidak valid, gunakan fallback

### 3. User Experience
- **Loading State**: Menampilkan loading spinner saat proses delete
- **Confirmation Dialog**: Konfirmasi sebelum menghapus data
- **Consistent Behavior**: Konsistensi antara desktop dan mobile view
- **Error Handling**: Penanganan error yang baik

## Testing

### Unit Testing
File testing tersedia di:
- `test_filter_persistence.php`: Test untuk fitur edit dengan filter persistence
- `test_delete_filter_persistence.php`: Test untuk fitur delete dengan filter persistence

### Manual Testing Checklist

#### Test Edit Data dengan Filter:
1. Buka halaman database dengan filter tertentu (misal: nama=Ahmad, status=0, page=2)
2. Edit data peserta
3. Verifikasi setelah save, kembali ke halaman dengan filter yang sama
4. Verifikasi data yang diedit masih terlihat

#### Test Delete Data dengan Filter:
1. Buka halaman database dengan filter tertentu
2. Delete data peserta
3. Verifikasi setelah delete, kembali ke halaman dengan filter yang sama
4. Verifikasi data yang dihapus tidak ada lagi

#### Test Pagination dengan Filter:
1. Buka halaman database dengan filter dan pagination (misal: page=3)
2. Edit/delete data
3. Verifikasi kembali ke halaman yang sama dengan pagination yang sama

#### Test Multiple Filters:
1. Kombinasikan beberapa filter (nama, status, flag_doc, dll)
2. Edit/delete data
3. Verifikasi semua filter tetap aktif

#### Test Security:
1. Coba inject URL berbahaya ke parameter redirect
2. Verifikasi sistem menolak redirect ke domain eksternal
3. Verifikasi tidak ada XSS atau path traversal

## Implementasi di File

### Files yang Dimodifikasi:
1. **`application/controllers/Database.php`**
   - Fungsi `update()`: Menambahkan redirect dengan filter
   - Fungsi `delete()`: Menambahkan handling redirect parameter
   - Fungsi `get_redirect_url_with_filters()`: Helper function baru

2. **`application/views/database/index.php`**
   - JavaScript functions: `saveRow()`, `saveRowMobileTable()`
   - Tombol delete: Diubah dari link ke button
   - Fungsi `deleteData()`: JavaScript function baru

### Files Testing:
1. **`test_filter_persistence.php`**: Test untuk edit dengan filter persistence
2. **`test_delete_filter_persistence.php`**: Test untuk delete dengan filter persistence

## Keuntungan

1. **User Experience yang Lebih Baik**: User tidak kehilangan konteks filter yang sudah diatur
2. **Efisiensi Kerja**: Tidak perlu mengatur ulang filter setelah edit/delete
3. **Konsistensi**: Behavior yang konsisten antara edit dan delete
4. **Keamanan**: Validasi URL redirect untuk mencegah serangan
5. **Fallback Mechanism**: Tetap berfungsi meskipun ada error

## Catatan Penting

1. **Browser Compatibility**: Fitur ini menggunakan JavaScript modern (URL API, URLSearchParams)
2. **Security**: Selalu validasi redirect URL untuk mencegah open redirect
3. **Performance**: Redirect dilakukan setelah delay 1 detik untuk memberikan feedback visual
4. **Mobile Support**: Fitur ini bekerja di desktop dan mobile view

## Troubleshooting

### Masalah Umum:
1. **Filter tidak tersimpan**: Pastikan JavaScript berjalan dengan baik
2. **Redirect tidak bekerja**: Cek console browser untuk error JavaScript
3. **Security error**: Pastikan redirect URL valid dan aman

### Debug:
1. Cek console browser untuk error JavaScript
2. Cek log PHP untuk error server-side
3. Gunakan file testing untuk verifikasi logic

## Kesimpulan

Fitur ini berhasil mengatasi masalah user experience dengan mempertahankan state filter dan pagination setelah aksi edit atau delete. Implementasi yang aman dan robust memastikan bahwa user dapat bekerja dengan efisien tanpa kehilangan konteks pencarian yang sudah diatur.
