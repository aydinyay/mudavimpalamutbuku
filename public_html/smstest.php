<?php
$base = '/home/mudavimp/mudavimpalamutbuku';
require_once $base . '/vendor/autoload.php';
$app = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>\n";

// Config kontrol
echo "SMS_KNO: " . (config('services.sms.kno') ?: 'BOŞ') . "\n";
echo "SMS_USERNAME: " . (config('services.sms.username') ?: 'BOŞ') . "\n";
echo "SMS_PASSWORD: " . (config('services.sms.password') ? '***' : 'BOŞ') . "\n";
echo "SMS_ORIGINATOR: " . (config('services.sms.originator') ?: 'BOŞ') . "\n\n";

// SmsService var mı?
echo "SmsService: " . (class_exists(\App\Services\SmsService::class) ? 'MEVCUT' : 'BULUNAMADI') . "\n\n";

// Test SMS gönder
$phone = $_GET['phone'] ?? '';
if ($phone) {
    $sms = app(\App\Services\SmsService::class);
    $result = $sms->send($phone, 'Mudavim Sef test mesaji. Sistem calisiyor!');
    echo "SMS sonucu: " . ($result ? 'GONDERILDI' : 'BASARISIZ') . "\n";
    echo "\nLog icin: storage/logs/laravel.log dosyasina bakın\n";
} else {
    echo "Test SMS göndermek için: smstest.php?phone=05XXXXXXXXX\n";
}

echo "</pre>\n";
