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
            Log::warning('SMS: geçersiz telefon numarası');
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
            Log::info("SMS [{$phone}]: {$body}");
            if (str_starts_with($body, '1:')) return true;
            Log::warning("SMS başarısız [{$phone}]: {$body}");
            return false;
        } catch (\Throwable $e) {
            Log::error('SMS hatası: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '90') && strlen($phone) === 12) {
            $phone = substr($phone, 2);
        } elseif (str_starts_with($phone, '0') && strlen($phone) === 11) {
            $phone = substr($phone, 1);
        }
        return strlen($phone) === 10 ? $phone : null;
    }
}
