<?php
$base = '/home/mudavimp/mudavimpalamutbuku';

$svcContent = <<<'PHP'
<?php

namespace App\Services;

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

        $params = http_build_query([
            'kno'      => $kno,
            'kul_ad'   => $username,
            'sifre'    => $password,
            'gonderen' => config('services.sms.originator', 'MUDAVIM'),
            'mesaj'    => $message,
            'numaralar'=> $phone,
            'tur'      => 'Turkce',
        ]);

        // http kullan - toplusmsyolla.com SSL DH key uyumsuzlugu nedeniyle
        $url = 'http://www.toplusmsyolla.com/smsgonder1N.php?' . $params;

        try {
            $ctx = stream_context_create(['http' => ['timeout' => 15]]);
            $body = trim(file_get_contents($url, false, $ctx));

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

file_put_contents($base . '/app/Services/SmsService.php', $svcContent);

// Bootstrap cache temizle
foreach (glob($base . '/bootstrap/cache/*.php') as $f) unlink($f);

echo "<pre>SmsService.php guncellendi (HTTP kullaniliyor).\nBootstrap cache temizlendi.\n\nSimdi smstest.php ile tekrar test edin.</pre>\n";
unlink(__FILE__);
