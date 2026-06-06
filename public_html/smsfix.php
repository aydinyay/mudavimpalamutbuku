<?php
$base = '/home/mudavimp/mudavimpalamutbuku';

echo "<pre>\n";

// 1. .env SMS satirlari kontrol
$env = file_get_contents($base . '/.env');
preg_match_all('/^SMS_.+/m', $env, $m);
echo "=== .env SMS satirlari ===\n";
echo $m[0] ? implode("\n", $m[0]) . "\n" : "YOK - .env'e eklenmemis!\n";
echo "\n";

// 2. SmsService.php var mi?
$svcFile = $base . '/app/Services/SmsService.php';
echo "=== SmsService.php ===\n";
echo file_exists($svcFile) ? "MEVCUT\n" : "YOK - yaziliyor...\n";

// 3. SmsService.php yaz (yoksa veya eski ise)
$svcContent = <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);
        if (!$phone) {
            Log::warning('SMS: gecersiz telefon numarasi');
            return false;
        }

        $kno      = config('services.sms.kno');
        $username = config('services.sms.username');
        $password = config('services.sms.password');

        if (!$kno || !$username || !$password) {
            Log::warning('SMS: .env kimlik bilgileri eksik');
            return false;
        }

        $params = [
            'kno'      => $kno,
            'kul_ad'   => $username,
            'sifre'    => $password,
            'gonderen' => config('services.sms.originator', 'MUDAVIM'),
            'mesaj'    => $message,
            'numaralar'=> $phone,
            'tur'      => 'Turkce',
        ];

        try {
            $response = Http::timeout(15)->withoutVerifying()
                ->get('https://www.toplusmsyolla.com/smsgonder1N.php', $params);
            $body = trim($response->body());
            Log::info('SMS: ' . $body . ' => ' . $phone);
            if (str_starts_with($body, '1:')) return true;
            Log::warning('SMS basarisiz: ' . $body);
            return false;
        } catch (\Exception $e) {
            Log::error('SMS hatasi: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '90') && strlen($phone) === 12) $phone = substr($phone, 2);
        elseif (str_starts_with($phone, '0') && strlen($phone) === 11) $phone = substr($phone, 1);
        return strlen($phone) === 10 ? $phone : null;
    }
}
PHP;

if (!is_dir($base . '/app/Services')) mkdir($base . '/app/Services', 0755, true);
file_put_contents($svcFile, $svcContent);
echo "SmsService.php yazildi.\n\n";

// 4. Bootstrap cache temizle
foreach (glob($base . '/bootstrap/cache/*.php') as $f) unlink($f);
echo "Bootstrap cache temizlendi.\n\n";

// 5. config/services.php SMS bolumu var mi?
$cfg = file_get_contents($base . '/config/services.php');
echo "=== config/services.php SMS blogu ===\n";
echo strpos($cfg, "'sms'") !== false ? "MEVCUT\n" : "YOK - config guncelleniyor...\n";

if (strpos($cfg, "'sms'") === false) {
    $cfg = str_replace('];', "
    'sms' => [
        'kno'        => env('SMS_KNO'),
        'username'   => env('SMS_USERNAME'),
        'password'   => env('SMS_PASSWORD'),
        'originator' => env('SMS_ORIGINATOR', 'MUDAVIM'),
    ],

];", $cfg);
    file_put_contents($base . '/config/services.php', $cfg);
    echo "config/services.php guncellendi.\n";
}

echo "\nHEPSI TAMAM. Simdi smstest.php?phone=05XXXXXXXXX ile test edin.\n";
echo "</pre>\n";
unlink(__FILE__);
