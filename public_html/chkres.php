<?php
$base = '/home/mudavimp/mudavimpalamutbuku';

echo "<pre>\n";

// Reservation controller'da SMS kodu var mi?
$ctrl = file_get_contents($base . '/app/Modules/Reservation/Controllers/Public/ReservationController.php');
echo "=== SMS kodu var mi? ===\n";
echo strpos($ctrl, 'SmsService') !== false ? "EVET - SMS kodu mevcut\n" : "HAYIR - SMS kodu YOK, deploy gitmemis!\n";
echo "\n";

// Son log hatalari
echo "=== Son SMS log satirlari ===\n";
$log = $base . '/storage/logs/laravel.log';
if (file_exists($log)) {
    $lines = file($log);
    $relevant = array_filter($lines, fn($l) => stripos($l, 'sms') !== false || stripos($l, 'SMS') !== false);
    $last = array_slice($relevant, -10);
    echo $last ? implode('', $last) : "SMS ile ilgili log yok\n";
} else {
    echo "Log dosyasi bulunamadi\n";
}

echo "</pre>\n";
unlink(__FILE__);
