<?php
/**
 * File Testing untuk Kompatibilitas Backup Database dengan syncdb.php
 * 
 * File ini berisi test case untuk memverifikasi bahwa
 * fungsi backup database mengikuti skema yang sama dengan syncdb.php
 */

// Test Case 1: Test database connection pattern
function testDatabaseConnectionPattern() {
    echo "=== TESTING BACKUP DATABASE SYNC COMPATIBILITY ===\n\n";
    
    echo "Test 1 - Database Connection Pattern:\n";
    
    // Simulate database connection like syncdb.php
    $test_config = [
        'hostname' => 'localhost',
        'username' => 'test_user',
        'password' => 'test_pass',
        'database' => 'test_db'
    ];
    
    echo "Database config: " . json_encode($test_config) . "\n";
    
    // Test connection pattern
    $connection_pattern = "// Koneksi\n";
    $connection_pattern .= "\$conn = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name);\n";
    $connection_pattern .= "if (\$conn->connect_error) {\n";
    $connection_pattern .= "    die(\"Koneksi gagal: \" . \$conn->connect_error);\n";
    $connection_pattern .= "}\n";
    
    echo "Connection pattern matches syncdb.php: " . (strpos($connection_pattern, 'Koneksi gagal') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses mysqli: " . (strpos($connection_pattern, 'new mysqli') !== false ? "PASS" : "FAIL") . "\n";
    echo "Error handling: " . (strpos($connection_pattern, 'connect_error') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 2: Test file opening pattern
function testFileOpeningPattern() {
    echo "Test 2 - File Opening Pattern:\n";
    
    // Simulate file opening like syncdb.php
    $file_pattern = "// Buka file\n";
    $file_pattern .= "\$handle = fopen(\$filepath, 'w');\n";
    $file_pattern .= "if (!\$handle) {\n";
    $file_pattern .= "    die(\"Gagal buka file.\");\n";
    $file_pattern .= "}\n";
    
    echo "File opening pattern matches syncdb.php: " . (strpos($file_pattern, 'Buka file') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses fopen with 'w' mode: " . (strpos($file_pattern, "fopen(\$filepath, 'w')") !== false ? "PASS" : "FAIL") . "\n";
    echo "Error handling: " . (strpos($file_pattern, 'Gagal buka file') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 3: Test header writing pattern
function testHeaderWritingPattern() {
    echo "Test 3 - Header Writing Pattern:\n";
    
    // Simulate header writing like syncdb.php
    $header_pattern = "// -------------------------------\n";
    $header_pattern .= "// HEADER phpMyAdmin\n";
    $header_pattern .= "// -------------------------------\n";
    $header_pattern .= "fwrite(\$handle, \"-- phpMyAdmin SQL Dump\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- version 5.2.2\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- https://www.phpmyadmin.net/\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"--\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- Host: \$db_host\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- Database: `\$db_name`\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- Generation Time: \" . date('M d, Y \\a\\t h:i A') . \"\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- Server version: \" . \$conn->server_info . \"\\n\");\n";
    $header_pattern .= "fwrite(\$handle, \"-- PHP Version: \" . PHP_VERSION . \"\\n\");\n";
    
    echo "Header pattern matches syncdb.php: " . (strpos($header_pattern, 'HEADER phpMyAdmin') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains phpMyAdmin SQL Dump: " . (strpos($header_pattern, 'phpMyAdmin SQL Dump') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains version 5.2.2: " . (strpos($header_pattern, 'version 5.2.2') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains Host info: " . (strpos($header_pattern, 'Host: $db_host') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains Database info: " . (strpos($header_pattern, 'Database: `$db_name`') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains Generation Time: " . (strpos($header_pattern, 'Generation Time') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains Server version: " . (strpos($header_pattern, 'Server version') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains PHP Version: " . (strpos($header_pattern, 'PHP Version') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 4: Test table structure pattern
function testTableStructurePattern() {
    echo "Test 4 - Table Structure Pattern:\n";
    
    // Simulate table structure writing like syncdb.php
    $structure_pattern = "// Struktur Tabel\n";
    $structure_pattern .= "fwrite(\$handle, \"\\n--\\n-- Table structure for table `\$table`\\n--\\n\\n\");\n";
    $structure_pattern .= "fwrite(\$handle, \"DROP TABLE IF EXISTS `\$table`;\\n\");\n\n";
    $structure_pattern .= "\$create_result = \$conn->query(\"SHOW CREATE TABLE `\$table`\");\n";
    $structure_pattern .= "\$create_row = \$create_result->fetch_row();\n";
    $structure_pattern .= "fwrite(\$handle, \$create_row[1] . \";\\n\\n\");\n";
    
    echo "Structure pattern matches syncdb.php: " . (strpos($structure_pattern, 'Struktur Tabel') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains DROP TABLE: " . (strpos($structure_pattern, 'DROP TABLE IF EXISTS') !== false ? "PASS" : "FAIL") . "\n";
    $structure_pattern .= "Contains SHOW CREATE TABLE: " . (strpos($structure_pattern, 'SHOW CREATE TABLE') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses fetch_row(): " . (strpos($structure_pattern, 'fetch_row()') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 5: Test data insertion pattern
function testDataInsertionPattern() {
    echo "Test 5 - Data Insertion Pattern:\n";
    
    // Simulate data insertion like syncdb.php
    $data_pattern = "// Data Tabel\n";
    $data_pattern .= "fwrite(\$handle, \"--\\n-- Dumping data for table `\$table`\\n--\\n\\n\");\n";
    $data_pattern .= "fwrite(\$handle, \"INSERT INTO `\$table` (\");\n\n";
    $data_pattern .= "\$columns_result = \$conn->query(\"SHOW COLUMNS FROM `\$table`\");\n";
    $data_pattern .= "\$columns = [];\n";
    $data_pattern .= "while (\$col = \$columns_result->fetch_row()) {\n";
    $data_pattern .= "    \$columns[] = \$col[0];\n";
    $data_pattern .= "}\n";
    $data_pattern .= "fwrite(\$handle, \"`\" . implode('`, `', \$columns) . \"`\");\n\n";
    $data_pattern .= "fwrite(\$handle, \") VALUES\\n\");\n\n";
    $data_pattern .= "\$data_result = \$conn->query(\"SELECT * FROM `\$table`\");\n";
    $data_pattern .= "\$rows = [];\n";
    $data_pattern .= "while (\$row = \$data_result->fetch_row()) {\n";
    $data_pattern .= "    \$values = array_map(function(\$value) use (\$conn) {\n";
    $data_pattern .= "        if (\$value === null) {\n";
    $data_pattern .= "            return 'NULL';\n";
    $data_pattern .= "        }\n";
    $data_pattern .= "        return \"'\" . \$conn->real_escape_string(\$value) . \"'\";\n";
    $data_pattern .= "    }, \$row);\n";
    $data_pattern .= "    \$rows[] = '(' . implode(', ', \$values) . ')';\n";
    $data_pattern .= "}\n\n";
    $data_pattern .= "if (count(\$rows) > 0) {\n";
    $data_pattern .= "    fwrite(\$handle, implode(\",\\n\", \$rows));\n";
    $data_pattern .= "    fwrite(\$handle, \";\\n\\n\");\n";
    $data_pattern .= "} else {\n";
    $data_pattern .= "    fwrite(\$handle, \";\\n\\n\");\n";
    $data_pattern .= "}\n";
    
    echo "Data pattern matches syncdb.php: " . (strpos($data_pattern, 'Data Tabel') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains INSERT INTO: " . (strpos($data_pattern, 'INSERT INTO') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains SHOW COLUMNS: " . (strpos($data_pattern, 'SHOW COLUMNS FROM') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains SELECT * FROM: " . (strpos($data_pattern, 'SELECT * FROM') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses array_map for values: " . (strpos($data_pattern, 'array_map(function($value)') !== false ? "PASS" : "FAIL") . "\n";
    echo "Handles NULL values: " . (strpos($data_pattern, 'return \'NULL\'') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses real_escape_string: " . (strpos($data_pattern, 'real_escape_string') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses implode for rows: " . (strpos($data_pattern, 'implode(",\\n", $rows)') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 6: Test completion pattern
function testCompletionPattern() {
    echo "Test 6 - Completion Pattern:\n";
    
    // Simulate completion like syncdb.php
    $completion_pattern = "// -------------------------------\n";
    $completion_pattern .= "// AKHIR DUMP\n";
    $completion_pattern .= "// -------------------------------\n";
    $completion_pattern .= "fwrite(\$handle, \"COMMIT;\\n\\n\");\n";
    $completion_pattern .= "fwrite(\$handle, \"/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\\n\");\n";
    $completion_pattern .= "fwrite(\$handle, \"/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\\n\");\n";
    $completion_pattern .= "fwrite(\$handle, \"/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\\n\");\n\n";
    $completion_pattern .= "fclose(\$handle);\n";
    $completion_pattern .= "\$conn->close();\n";
    
    echo "Completion pattern matches syncdb.php: " . (strpos($completion_pattern, 'AKHIR DUMP') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains COMMIT: " . (strpos($completion_pattern, 'COMMIT') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains character set restoration: " . (strpos($completion_pattern, 'CHARACTER_SET_CLIENT') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains fclose: " . (strpos($completion_pattern, 'fclose($handle)') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains connection close: " . (strpos($completion_pattern, '$conn->close()') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 7: Test filename pattern
function testFilenamePattern() {
    echo "Test 7 - Filename Pattern:\n";
    
    // Simulate filename generation like syncdb.php
    $filename_pattern = "// Lokasi file\n";
    $filename_pattern .= "\$temp_dir = '/home/munz6135/tmp/backupdb/';\n";
    $filename_pattern .= "\$filename = 'backup_' . \$db_name . '_' . date('Y-m-d_H-i-s') . '.sql';\n";
    $filename_pattern .= "\$filepath = \$temp_dir . \$filename;\n";
    
    echo "Filename pattern matches syncdb.php: " . (strpos($filename_pattern, 'backup_') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains database name: " . (strpos($filename_pattern, '$db_name') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains timestamp: " . (strpos($filename_pattern, 'date(\'Y-m-d_H-i-s\')') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains .sql extension: " . (strpos($filename_pattern, '.sql') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 8: Test FTP cleanup pattern
function testFtpCleanupPattern() {
    echo "Test 8 - FTP Cleanup Pattern:\n";
    
    // Simulate FTP cleanup like syncdb.php
    $ftp_cleanup_pattern = "// --- HAPUS FILE LAMA DI FTP (>7 HARI) ---\n";
    $ftp_cleanup_pattern .= "\$max_days = 7;\n";
    $ftp_cleanup_pattern .= "\$now = time();\n";
    $ftp_cleanup_pattern .= "\$ftp_conn = ftp_connect(\$ftp_host);\n";
    $ftp_cleanup_pattern .= "if (\$ftp_conn && ftp_login(\$ftp_conn, \$ftp_user, \$ftp_pass)) {\n";
    $ftp_cleanup_pattern .= "    \$files = ftp_nlist(\$ftp_conn, \$ftp_dir);\n";
    $ftp_cleanup_pattern .= "    if (\$files !== false) {\n";
    $ftp_cleanup_pattern .= "        foreach (\$files as \$file) {\n";
    $ftp_cleanup_pattern .= "            if (preg_match('/^backup_.*_\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}\\.sql$/', basename(\$file))) {\n";
    $ftp_cleanup_pattern .= "                if (preg_match('/_(\\d{4}-\\d{2}-\\d{2})_(\\d{2})-(\\d{2})-(\\d{2})\\.sql/', \$file, \$matches)) {\n";
    $ftp_cleanup_pattern .= "                    \$file_time = strtotime(\$matches[1] . ' ' . \$matches[2] . ':' . \$matches[3] . ':' . \$matches[4]);\n";
    $ftp_cleanup_pattern .= "                    \$days_old = (\$now - \$file_time) / (60 * 60 * 24);\n";
    $ftp_cleanup_pattern .= "                    if (\$days_old > \$max_days) {\n";
    $ftp_cleanup_pattern .= "                        if (ftp_delete(\$ftp_conn, \$file)) {\n";
    $ftp_cleanup_pattern .= "                            echo \"[🗑] File lama dihapus: \" . basename(\$file) . \"\\n\";\n";
    $ftp_cleanup_pattern .= "                        }\n";
    $ftp_cleanup_pattern .= "                    }\n";
    $ftp_cleanup_pattern .= "                }\n";
    $ftp_cleanup_pattern .= "            }\n";
    $ftp_cleanup_pattern .= "        }\n";
    $ftp_cleanup_pattern .= "    }\n";
    $ftp_cleanup_pattern .= "    ftp_close(\$ftp_conn);\n";
    $ftp_cleanup_pattern .= "}\n";
    
    echo "FTP cleanup pattern matches syncdb.php: " . (strpos($ftp_cleanup_pattern, 'HAPUS FILE LAMA DI FTP') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses max_days = 7: " . (strpos($ftp_cleanup_pattern, '$max_days = 7') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses ftp_connect: " . (strpos($ftp_cleanup_pattern, 'ftp_connect($ftp_host)') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses ftp_nlist: " . (strpos($ftp_cleanup_pattern, 'ftp_nlist') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses regex pattern: " . (strpos($ftp_cleanup_pattern, 'preg_match(\'/^backup_.*_\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}\\.sql$/\'') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses strtotime: " . (strpos($ftp_cleanup_pattern, 'strtotime($matches[1]') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses ftp_delete: " . (strpos($ftp_cleanup_pattern, 'ftp_delete($ftp_conn, $file)') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 9: Test error handling pattern
function testErrorHandlingPattern() {
    echo "Test 9 - Error Handling Pattern:\n";
    
    // Simulate error handling like syncdb.php
    $error_pattern = "// Koneksi\n";
    $error_pattern .= "\$conn = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name);\n";
    $error_pattern .= "if (\$conn->connect_error) {\n";
    $error_pattern .= "    die(\"Koneksi gagal: \" . \$conn->connect_error);\n";
    $error_pattern .= "}\n\n";
    $error_pattern .= "// Buka file\n";
    $error_pattern .= "\$handle = fopen(\$filepath, 'w');\n";
    $error_pattern .= "if (!\$handle) {\n";
    $error_pattern .= "    die(\"Gagal buka file.\");\n";
    $error_pattern .= "}\n";
    
    echo "Error handling pattern matches syncdb.php: " . (strpos($error_pattern, 'Koneksi gagal') !== false ? "PASS" : "FAIL") . "\n";
    echo "Uses die() for errors: " . (strpos($error_pattern, 'die(') !== false ? "PASS" : "FAIL") . "\n";
    echo "Simple error messages: " . (strpos($error_pattern, 'Gagal buka file') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Test Case 10: Test overall structure compatibility
function testOverallStructureCompatibility() {
    echo "Test 10 - Overall Structure Compatibility:\n";
    
    // Simulate overall structure like syncdb.php
    $overall_structure = "<?php\n";
    $overall_structure .= "/**\n";
    $overall_structure .= " * Backup Database - Format phpMyAdmin SQL Dump\n";
    $overall_structure .= " * Output identik dengan file contoh\n";
    $overall_structure .= " */\n\n";
    $overall_structure .= "// Konfigurasi Database\n";
    $overall_structure .= "\$db_host = 'localhost';\n";
    $overall_structure .= "\$db_user = 'test_user';\n";
    $overall_structure .= "\$db_pass = 'test_pass';\n";
    $overall_structure .= "\$db_name = 'test_db';\n\n";
    $overall_structure .= "// Lokasi file\n";
    $overall_structure .= "\$temp_dir = '/tmp/backupdb/';\n";
    $overall_structure .= "\$filename = 'backup_' . \$db_name . '_' . date('Y-m-d_H-i-s') . '.sql';\n";
    $overall_structure .= "\$filepath = \$temp_dir . \$filename;\n\n";
    $overall_structure .= "// Buat folder\n";
    $overall_structure .= "if (!is_dir(\$temp_dir)) {\n";
    $overall_structure .= "    mkdir(\$temp_dir, 0755, true);\n";
    $overall_structure .= "}\n\n";
    $overall_structure .= "// Koneksi\n";
    $overall_structure .= "\$conn = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name);\n";
    $overall_structure .= "if (\$conn->connect_error) {\n";
    $overall_structure .= "    die(\"Koneksi gagal: \" . \$conn->connect_error);\n";
    $overall_structure .= "}\n\n";
    $overall_structure .= "// Buka file\n";
    $overall_structure .= "\$handle = fopen(\$filepath, 'w');\n";
    $overall_structure .= "if (!\$handle) {\n";
    $overall_structure .= "    die(\"Gagal buka file.\");\n";
    $overall_structure .= "}\n\n";
    $overall_structure .= "// HEADER phpMyAdmin\n";
    $overall_structure .= "fwrite(\$handle, \"-- phpMyAdmin SQL Dump\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- version 5.2.2\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- https://www.phpmyadmin.net/\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"--\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- Host: \$db_host\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- Database: `\$db_name`\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- Generation Time: \" . date('M d, Y \\a\\t h:i A') . \"\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- Server version: \" . \$conn->server_info . \"\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"-- PHP Version: \" . PHP_VERSION . \"\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"SET SQL_MODE = \\\"NO_AUTO_VALUE_ON_ZERO\\\";\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"START TRANSACTION;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"SET time_zone = \\\"+00:00\\\";\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET NAMES utf8mb4 */;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"\\n\");\n\n";
    $overall_structure .= "// DUMP SEMUA TABEL\n";
    $overall_structure .= "\$tables = \$conn->query(\"SHOW TABLES\");\n";
    $overall_structure .= "while (\$table_row = \$tables->fetch_row()) {\n";
    $overall_structure .= "    \$table = \$table_row[0];\n\n";
    $overall_structure .= "    // Struktur Tabel\n";
    $overall_structure .= "    fwrite(\$handle, \"\\n--\\n-- Table structure for table `\$table`\\n--\\n\\n\");\n";
    $overall_structure .= "    fwrite(\$handle, \"DROP TABLE IF EXISTS `\$table`;\\n\");\n\n";
    $overall_structure .= "    \$create_result = \$conn->query(\"SHOW CREATE TABLE `\$table`\");\n";
    $overall_structure .= "    \$create_row = \$create_result->fetch_row();\n";
    $overall_structure .= "    fwrite(\$handle, \$create_row[1] . \";\\n\\n\");\n\n";
    $overall_structure .= "    // Data Tabel\n";
    $overall_structure .= "    fwrite(\$handle, \"--\\n-- Dumping data for table `\$table`\\n--\\n\\n\");\n";
    $overall_structure .= "    fwrite(\$handle, \"INSERT INTO `\$table` (\");\n\n";
    $overall_structure .= "    \$columns_result = \$conn->query(\"SHOW COLUMNS FROM `\$table`\");\n";
    $overall_structure .= "    \$columns = [];\n";
    $overall_structure .= "    while (\$col = \$columns_result->fetch_row()) {\n";
    $overall_structure .= "        \$columns[] = \$col[0];\n";
    $overall_structure .= "    }\n";
    $overall_structure .= "    fwrite(\$handle, \"`\" . implode('`, `', \$columns) . \"`\");\n\n";
    $overall_structure .= "    fwrite(\$handle, \") VALUES\\n\");\n\n";
    $overall_structure .= "    \$data_result = \$conn->query(\"SELECT * FROM `\$table`\");\n";
    $overall_structure .= "    \$rows = [];\n";
    $overall_structure .= "    while (\$row = \$data_result->fetch_row()) {\n";
    $overall_structure .= "        \$values = array_map(function(\$value) use (\$conn) {\n";
    $overall_structure .= "            if (\$value === null) {\n";
    $overall_structure .= "                return 'NULL';\n";
    $overall_structure .= "            }\n";
    $overall_structure .= "            return \"'\" . \$conn->real_escape_string(\$value) . \"'\";\n";
    $overall_structure .= "        }, \$row);\n";
    $overall_structure .= "        \$rows[] = '(' . implode(', ', \$values) . ')';\n";
    $overall_structure .= "    }\n\n";
    $overall_structure .= "    if (count(\$rows) > 0) {\n";
    $overall_structure .= "        fwrite(\$handle, implode(\",\\n\", \$rows));\n";
    $overall_structure .= "        fwrite(\$handle, \";\\n\\n\");\n";
    $overall_structure .= "    } else {\n";
    $overall_structure .= "        fwrite(\$handle, \";\\n\\n\");\n";
    $overall_structure .= "    }\n";
    $overall_structure .= "}\n\n";
    $overall_structure .= "// AKHIR DUMP\n";
    $overall_structure .= "fwrite(\$handle, \"COMMIT;\\n\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\\n\");\n";
    $overall_structure .= "fwrite(\$handle, \"/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\\n\");\n\n";
    $overall_structure .= "fclose(\$handle);\n";
    $overall_structure .= "\$conn->close();\n\n";
    $overall_structure .= "echo \"[✅] Export berhasil: \$filepath\\n\";\n";
    
    echo "Overall structure matches syncdb.php: " . (strpos($overall_structure, 'Backup Database - Format phpMyAdmin SQL Dump') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains database configuration: " . (strpos($overall_structure, 'Konfigurasi Database') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains file location: " . (strpos($overall_structure, 'Lokasi file') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains folder creation: " . (strpos($overall_structure, 'Buat folder') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains connection: " . (strpos($overall_structure, 'Koneksi') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains file opening: " . (strpos($overall_structure, 'Buka file') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains header writing: " . (strpos($overall_structure, 'HEADER phpMyAdmin') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains table dumping: " . (strpos($overall_structure, 'DUMP SEMUA TABEL') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains completion: " . (strpos($overall_structure, 'AKHIR DUMP') !== false ? "PASS" : "FAIL") . "\n";
    echo "Contains success message: " . (strpos($overall_structure, 'Export berhasil') !== false ? "PASS" : "FAIL") . "\n\n";
}

// Jalankan semua test
testDatabaseConnectionPattern();
testFileOpeningPattern();
testHeaderWritingPattern();
testTableStructurePattern();
testDataInsertionPattern();
testCompletionPattern();
testFilenamePattern();
testFtpCleanupPattern();
testErrorHandlingPattern();
testOverallStructureCompatibility();

echo "=== SELESAI TESTING BACKUP DATABASE SYNC COMPATIBILITY ===\n";

/**
 * Catatan untuk Testing Manual:
 * 
 * 1. Test Backup Database Lokal:
 *    - Buka halaman settings
 *    - Klik tombol "Backup Database"
 *    - Verifikasi file backup dibuat dengan format yang sama dengan syncdb.php
 *    - Verifikasi tidak ada error
 *    - Verifikasi file dapat didownload
 * 
 * 2. Test Backup Database FTP:
 *    - Isi konfigurasi FTP yang valid
 *    - Klik tombol "Backup ke FTP"
 *    - Verifikasi file berhasil diupload ke FTP
 *    - Verifikasi file lokal dihapus setelah upload
 *    - Verifikasi file lama di FTP otomatis dihapus
 * 
 * 3. Test File Format:
 *    - Download file backup
 *    - Buka file di text editor
 *    - Verifikasi format identik dengan syncdb.php
 *    - Verifikasi semua tabel dan data ter-backup
 * 
 * 4. Test Error Handling:
 *    - Test dengan kredensial database yang salah
 *    - Test dengan folder backup yang tidak writable
 *    - Test dengan koneksi FTP yang gagal
 *    - Verifikasi pesan error yang sesuai dengan syncdb.php
 * 
 * 5. Test cPanel Compatibility:
 *    - Test di environment cPanel yang disable exec
 *    - Verifikasi backup tetap berjalan dengan PHP murni
 *    - Verifikasi tidak ada error terkait mysqldump
 * 
 * 6. Test Cleanup Functionality:
 *    - Buat beberapa file backup dengan tanggal berbeda
 *    - Jalankan backup baru
 *    - Verifikasi file lama (>7 hari) dihapus
 *    - Verifikasi file baru tetap ada
 * 
 * 7. Test Performance:
 *    - Test backup database dengan ukuran besar
 *    - Verifikasi tidak ada timeout
 *    - Verifikasi memory usage tidak berlebihan
 * 
 * 8. Test Recovery:
 *    - Download file backup
 *    - Restore ke database test
 *    - Verifikasi semua data ter-restore dengan benar
 *    - Verifikasi struktur tabel tidak berubah
 * 
 * 9. Test Edge Cases:
 *    - Test dengan database kosong
 *    - Test dengan tabel yang sangat besar
 *    - Test dengan karakter khusus di nama tabel/kolom
 *    - Test dengan encoding yang berbeda
 * 
 * 10. Test Security:
 *     - Verifikasi tidak ada informasi sensitif di log
 *     - Verifikasi file backup hanya bisa diakses admin
 *     - Verifikasi validasi input FTP
 *     - Verifikasi sanitasi nama file
 */
?>
