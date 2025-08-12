<<<<<<< HEAD
# asdp
=======
# Sistem Monitoring Pelabuhan

Sistem Monitoring Pelabuhan adalah aplikasi web berbasis PHP dengan framework CodeIgniter 3 untuk memantau kegiatan operasional di pelabuhan, khususnya layanan air tawar untuk kapal yang sandar di dermaga.

## Fitur

1. **Modul Dermaga**
   - Pemantauan kapal di Dermaga 1 & 2
   - Manajemen status sandar kapal
   - Kontrol valve air tawar
   - Pemantauan volume air dan laju aliran

2. **Master Data**
   - Manajemen data kapal
   - Import/export data

3. **Database**
   - Catatan histori transaksi
   - Download laporan transaksi

4. **User Setting**
   - Manajemen pengguna (admin)
   - Pengaturan profil pengguna

## Persyaratan Sistem

- PHP 7.0 atau lebih tinggi
- MySQL 5.6 atau lebih tinggi
- Web server (Apache/Nginx)
- XAMPP (untuk pengembangan lokal)

## Cara Instalasi

1. Clone atau download repositori ini ke direktori web server Anda (htdocs untuk XAMPP)
2. Buat database MySQL bernama `pelabuhan_db`
3. Import struktur database dari file SQL atau jalankan migration
4. Konfigurasi pengaturan database di `application/config/database.php`
5. Akses aplikasi melalui browser

## Cara Instalasi Database Manual

1. Buka aplikasi di browser
2. Akses URL: `http://localhost/PLC/install`
3. Database dan tabel akan dibuat secara otomatis
4. Akan dibuat user default dengan username: `admin` dan password: `admin123`

## Penggunaan

1. Login menggunakan kredensial default: 
   - Username: `admin`
   - Password: `admin123`

2. Navigasi menu:
   - **Dashboard**: Ringkasan informasi umum
   - **Modul**: Pemantauan Dermaga 1 & 2
   - **Master Data**: Manajemen data kapal
   - **Database**: Histori transaksi
   - **User Setting**: Pengaturan profil
   - **Logout**: Keluar dari aplikasi

## Struktur Database

Aplikasi ini menggunakan tiga tabel utama:

1. **kapal**: Menyimpan data master kapal
2. **transaksi_dermaga**: Menyimpan data transaksi kapal di dermaga
3. **users**: Menyimpan data pengguna aplikasi

## Keamanan

- Autentikasi pengguna
- Kontrol akses berbasis role (admin/operator)
- Password di-hash menggunakan algoritma password_hash PHP

## Pengembangan

Aplikasi ini dikembangkan menggunakan:
- CodeIgniter 3
- Bootstrap 5
- Font Awesome
- jQuery

## Support

Jika Anda menemukan masalah atau membutuhkan bantuan, silakan buat issue di repositori ini.

## Lisensi

Aplikasi ini dikembangkan untuk tujuan pendidikan dan pembelajaran. 
>>>>>>> cbd6bc8 (Initial Setup New Repo)
