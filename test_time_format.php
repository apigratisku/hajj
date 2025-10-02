<?php
// Test format jam AM/PM seperti di API
echo "=== TEST FORMAT JAM AM/PM ===\n\n";

$test_times = [
    "16:20:00",  // 4:20 PM
    "16:20",     // 4:20 PM
    "08:30:00",  // 8:30 AM
    "08:30",     // 8:30 AM
    "00:00:00",  // 12:00 AM
    "12:00:00",  // 12:00 PM
    "23:59:59",  // 11:59 PM
];

echo "Format: Jam Sistem -> Jam Mekkah\n";
echo str_repeat("-", 40) . "\n";

foreach ($test_times as $jam) {
    // Normalize jam format
    if (strlen($jam) == 5) {
        $jam = $jam . ':00';
    }
    
    $jam_sistem = date('h:i A', strtotime($jam));
    $jam_mekkah = date('h:i A', strtotime($jam . ' +5 hours'));
    
    printf("%-8s -> %-8s -> %s\n", $jam, $jam_sistem, $jam_mekkah);
}

echo "\n=== CONTOH PESAN TELEGRAM ===\n";
echo "🔔 PENGINGAT • 3 jam setelah jadwal\n";
echo "📅 Tanggal: 15 September 2025\n";
echo "🕐 Jam Sistem: " . date('h:i A', strtotime('16:20:00')) . "\n";
echo "🕐 Jam Mekkah: " . date('h:i A', strtotime('16:20:00 +5 hours')) . "\n";
echo "\n📊 STATISTIK PESERTA\n";
echo "👥 Total: 10\n";
echo "✅ Dengan Barcode: 7\n";
echo "❌ Tanpa Barcode: 3\n";
echo "⚠️ PERHATIAN: Masih ada peserta yang belum upload barcode!\n";

echo "\n=== TEST COMPLETED ===\n";
?>
