<?php
// Simple test untuk API data
// Jalankan dengan: php test_api_simple.php

$base_url = 'http://localhost/hajj/api';
$tanggal = '2025-09-15';
$jam = '16:20:00';

echo "=== TEST API DATA ===\n";
echo "Base URL: $base_url\n";
echo "Tanggal: $tanggal\n";
echo "Jam: $jam\n\n";

// Test 1: Health Check
echo "1. Testing Health Check...\n";
$health_url = "$base_url/health";
$health_response = file_get_contents($health_url);
echo "Health Response: $health_response\n\n";

// Test 2: Schedule Data
echo "2. Testing Schedule Data...\n";
$schedule_url = "$base_url/schedule?tanggal=$tanggal";
$schedule_response = file_get_contents($schedule_url);
echo "Schedule Response: $schedule_response\n\n";

// Test 3: Pending Barcode Data
echo "3. Testing Pending Barcode Data...\n";
$pending_url = "$base_url/pending-barcode?tanggal=$tanggal&jam=$jam";
$pending_response = file_get_contents($pending_url);
echo "Pending Barcode Response: $pending_response\n\n";

// Test 4: Debug Data
echo "4. Testing Debug Data...\n";
$debug_url = "$base_url/debug?tanggal=$tanggal&jam=$jam";
$debug_response = file_get_contents($debug_url);
echo "Debug Response: $debug_response\n\n";

echo "=== TEST COMPLETED ===\n";
?>
