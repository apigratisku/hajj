<?php
/**
 * Script: Cek Lokasi mysqldump di cPanel
 * Simpan sebagai: cek_mysqldump.php
 */

// Fungsi untuk cek apakah exec() aktif
function is_exec_enabled() {
    $disabled = explode(',', ini_get('disable_functions'));
    $disabled = array_map('trim', $disabled);
    return !in_array('exec', $disabled);
}

// Fungsi untuk cek path mysqldump
function find_mysqldump() {
    // Daftar path umum di cPanel
    $paths = [
        '/usr/bin/mysqldump',
        '/usr/local/bin/mysqldump',
        '/usr/mysql/bin/mysqldump',
        '/opt/local/bin/mysqldump',
        '/opt/lampp/bin/mysqldump', // XAMPP
    ];

    foreach ($paths as $path) {
        if (file_exists($path) && is_executable($path)) {
            return $path;
        }
    }

    // Jika tidak ketemu, coba pakai perintah `which`
    if (function_exists('exec')) {
        exec('which mysqldump', $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $path = trim($output[0]);
            if (file_exists($path)) {
                return $path;
            }
        }
    }

    return null;
}

// Mulai output
echo "<h2>🔍 Pengecekan Lokasi mysqldump</h2>";

// Cek apakah exec() aktif
if (!is_exec_enabled()) {
    echo "<p style='color: red;'><strong>❌ exec() dinonaktifkan</strong> di server Anda.</p>";
    echo "<p>Tidak bisa mengecek dengan perintah sistem. Hubungi support hosting.</p>";
    exit;
}

echo "<p><strong>✅ exec() aktif.</strong> Mengecek lokasi mysqldump...</p>";

// Cari mysqldump
$mysqldump_path = find_mysqldump();

if ($mysqldump_path) {
    echo "<p style='color: green;'><strong>✅ mysqldump ditemukan!</strong></p>";
    echo "<p><strong>Path:</strong> <code>$mysqldump_path</code></p>";
    
    // Cek versi (opsional)
    exec("$mysqldump_path --version", $version_output, $return);
    if ($return === 0) {
        echo "<p><strong>Versi:</strong> " . htmlspecialchars(implode(" ", $version_output)) . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ mysqldump tidak ditemukan.</strong></p>";
    echo "<p>Kemungkinan:</p>";
    echo "<ul>";
    echo "<li>Server tidak menginstal MySQL client tools</li>";
    echo "<li>Path tidak standar</li>";
    echo "<li>Anda perlu hubungi <strong>support hosting</strong> untuk info lengkap</li>";
    echo "</ul>";
}

// Info tambahan
echo "<hr>";
echo "<h3>📋 Informasi Server</h3>";
echo "<p><strong>OS:</strong> " . php_uname('s') . " " . php_uname('r') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Safe Mode:</strong> " . (ini_get('safe_mode') ? 'On' : 'Off') . "</p>";
echo "<p><strong>disable_functions:</strong> " . ini_get('disable_functions') . "</p>";
?>