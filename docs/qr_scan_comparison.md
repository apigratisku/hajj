# Perbandingan Fungsi Scan QR (Implementasi Saat Ini)

## Scope
- Client chain di `application/views/qr_upload/index.php`:
  - `scanClientVariants` (Html5Qrcode)
  - `scanWithNativeBarcodeDetector` (BarcodeDetector)
  - fallback ke server endpoint `qr-upload/scan`
- Server chain di `application/controllers/Qr_upload.php`:
  - `decode_variants` (QrReader: original + preprocessed variants)
  - `decode_with_external_services` (QRServer + ZXing web)

## Metode Uji Yang Dijalankan
- Sampel lokal: `assets/uploads/qr_tmp/` (`crop_*`, `var_*`, `diamond_try`, `qr_upload_*`)
- Sampel kontrol known-good: `vendor/khanamiryan/qrcode-detector-decoder/tests/qrcodes/hello_world.png`
- Tool benchmark CLI:
  - `php tools/qr_scan_benchmark.php "assets/uploads/qr_tmp/crop_*.png"`
  - `php tools/qr_scan_benchmark.php "assets/uploads/qr_tmp/var_*.png"` (per file)
  - `php tools/qr_scan_benchmark.php "assets/uploads/qr_tmp/diamond_try.png"`
  - `php tools/qr_scan_benchmark.php "assets/uploads/qr_tmp/qr_upload_1777271761_4176.png" --with-external`
  - `php tools/qr_scan_benchmark.php "vendor/khanamiryan/qrcode-detector-decoder/tests/qrcodes/hello_world.png" --with-external`

## Ringkasan Hasil
- `crop_*.png`: 0/4 sukses (semua `failed`)
- `var_*.png`: 0/6 sukses (semua `failed`)
- `diamond_try.png`: 0/1 sukses (`failed`)
- `qr_upload_1777271761_4176.png` (dengan external fallback): 0/1 sukses (`failed`)
- `hello_world.png` (known-good): 1/1 sukses via `server_original`

## Interpretasi Perbandingan Fungsi
- **`decode_variants` server (QrReader)** terbukti berfungsi secara teknis (lolos pada known-good sample).
- **Kumpulan sampel di `assets/uploads/qr_tmp`** kemungkinan bukan QR valid/terlalu rusak, sehingga gagal di semua jalur server yang diuji.
- **`decode_with_external_services`** tidak memberi tambahan sukses pada sampel lokal yang tersedia.
- **Client decoder (`scanClientVariants` / `scanWithNativeBarcodeDetector`)** belum bisa dibenchmark objektif via CLI karena membutuhkan browser runtime; validasi akhir perlu dilakukan lewat UI browser.

## Fungsi yang Paling Aman Dijadikan Jalur Utama
Untuk target **success rate** lintas user:
1. Pertahankan alur berantai yang sudah ada: `client -> server_local -> external`.
2. Untuk jalur yang paling independen dari browser, prioritaskan **server `decode_variants`** sebagai fallback inti.
3. Gunakan external decoder hanya sebagai rescue layer (network-dependent).

## Catatan
- Benchmark ini menilai **fungsi mana yang bekerja** pada dataset yang tersedia sekarang.
- Untuk keputusan final berbasis metrik kuat, tambahkan batch uji QR valid 20-30 sampel (jelas, blur, miring, kompresi, crop sempit) lalu jalankan benchmark yang sama.
