<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| cPanel Configuration
|--------------------------------------------------------------------------
|
| Konfigurasi untuk koneksi ke cPanel UAPI
| Sesuaikan dengan pengaturan cPanel Anda
|
*/

$config['cpanel_domains'] = array(
    'choco.web.id' => array(
        'user' => 'chocoweb',
        'pass' => '8MGisIC2Nm',
        'host' => 'genuine.jagoanhosting.id',
        'domain' => 'genuine.jagoanhosting.id',
        'port' => 2083,
        'auth_token' => '',
        'protocol' => 'https',
        'timeout' => 30,
        'ssl_verify' => false,
        'debug' => false
    ),
    'muntun.my.id' => array(
        'user' => 'munz6135',
        'pass' => '5#ls96LD!%dK7Zx3',
        'host' => 'muntun.my.id',
        'domain' => 'muntun.my.id',
        'port' => 2083,
        'auth_token' => '',
        'protocol' => 'https',
        'timeout' => 30,
        'ssl_verify' => false,
        'debug' => false
    ),
    'goreproject.com' => array(
        'user' => 'goreproj',
        'pass' => 'C^!YSQI(GZ',
        'host' => 'genuine.jagoanhosting.id',
        'domain' => 'genuine.jagoanhosting.id',
        'port' => 2083,
        'auth_token' => '',
        'protocol' => 'https',
        'timeout' => 30,
        'ssl_verify' => false,
        'debug' => false
    ),
    'menfins.site' => array(
        'user' => 'menb8295',
        'pass' => 'vj5xS09IaE!hc@8%',
        'host' => 'menfins.site',
        'domain' => 'menfins.site',
        'port' => 2083,
        'auth_token' => '',
        'protocol' => 'https',
        'timeout' => 30,
        'ssl_verify' => false,
        'debug' => false
    )
);

$config['cpanel'] = $config['cpanel_domains']['choco.web.id'];

/*
|--------------------------------------------------------------------------
| Email Quota Presets
|--------------------------------------------------------------------------
|
| Preset quota yang tersedia untuk akun email
|
*/

$config['email_quota_presets'] = array(
    'basic' => array(
        'name' => 'Basic',
        'quota' => 100,
        'description' => '100 MB - Untuk penggunaan dasar'
    ),
    'standard' => array(
        'name' => 'Standard',
        'quota' => 250,
        'description' => '250 MB - Untuk penggunaan standar'
    ),
    'premium' => array(
        'name' => 'Premium',
        'quota' => 500,
        'description' => '500 MB - Untuk penggunaan premium'
    ),
    'business' => array(
        'name' => 'Business',
        'quota' => 1000,
        'description' => '1 GB - Untuk penggunaan bisnis'
    ),
    'enterprise' => array(
        'name' => 'Enterprise',
        'quota' => 5000,
        'description' => '5 GB - Untuk penggunaan enterprise'
    )
);

/*
|--------------------------------------------------------------------------
| Email Validation Rules
|--------------------------------------------------------------------------
|
| Aturan validasi untuk pembuatan akun email
|
*/

$config['email_validation'] = array(
    // Minimal panjang password
    'min_password_length' => 8,
    
    // Maksimal panjang password
    'max_password_length' => 50,
    
    // Minimal quota (MB)
    'min_quota' => 10,
    
    // Maksimal quota (MB)
    'max_quota' => 10000,
    
    // Karakter yang diizinkan dalam username email
    'allowed_username_chars' => 'a-zA-Z0-9._-',
    
    // Minimal panjang username
    'min_username_length' => 3,
    
    // Maksimal panjang username
    'max_username_length' => 64
);

/*
|--------------------------------------------------------------------------
| Error Messages
|--------------------------------------------------------------------------
|
| Pesan error yang akan ditampilkan
|
*/

$config['email_error_messages'] = array(
    'connection_failed' => 'Koneksi ke cPanel gagal. Periksa pengaturan koneksi.',
    'invalid_email' => 'Format email tidak valid.',
    'email_exists' => 'Akun email sudah ada.',
    'invalid_password' => 'Password tidak memenuhi persyaratan keamanan.',
    'invalid_quota' => 'Quota tidak valid.',
    'quota_too_small' => 'Quota tidak boleh lebih kecil dari penggunaan saat ini.',
    'permission_denied' => 'Tidak memiliki izin untuk melakukan operasi ini.',
    'unknown_error' => 'Terjadi kesalahan yang tidak diketahui.'
);
