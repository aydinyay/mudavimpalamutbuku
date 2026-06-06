<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python',
        'java/', 'go-http', 'libwww', 'okhttp', 'axios', 'node-fetch',
        'headless', 'phantom', 'selenium', 'scrapy', 'googlebot',
        'bingbot', 'yandex', 'baidu', 'duckduck', 'applebot',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldSkip($request)) {
                return $response;
            }

            $sessionId = $request->cookie('_mud_sid') ?? Str::random(40);
            $session   = AnalyticsSession::where('session_id', $sessionId)->first();

            if (! $session) {
                $geo = $this->resolveGeo($request->ip());
                $session = AnalyticsSession::create([
                    'session_id'      => $sessionId,
                    'ip'              => $request->ip(),
                    'country_code'    => $geo['country_code'] ?? null,
                    'country'         => $geo['country'] ?? null,
                    'city'            => $geo['city'] ?? null,
                    'device_type'     => $this->detectDevice($request->userAgent() ?? ''),
                    'browser'         => $this->detectBrowser($request->userAgent() ?? ''),
                    'os'              => $this->detectOs($request->userAgent() ?? ''),
                    'referrer_source' => $this->detectSource($request->headers->get('referer', '')),
                    'referrer_url'    => substr($request->headers->get('referer', ''), 0, 500),
                    'landing_page'    => $request->path(),
                    'page_count'      => 1,
                    'first_seen_at'   => now(),
                    'last_seen_at'    => now(),
                ]);
            } else {
                $session->increment('page_count');
                $session->last_seen_at = now();
                $session->save();
            }

            AnalyticsPageview::create([
                'session_id' => $sessionId,
                'page_path'  => '/' . $request->path(),
                'page_title' => null,
                'created_at' => now(),
            ]);

            $response->cookie('_mud_sid', $sessionId, 120, '/', null, false, true);
        } catch (\Throwable) {
            // Analytics hatası hiçbir zaman siteyi durdurmamalı
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        // Admin sayfaları
        if ($request->is('yonetim*') || $request->is('admin*')) return true;
        // Giriş yapmış kullanıcı
        if (auth()->check()) return true;
        // API / polling endpoint'leri
        if ($request->is('spotify/status', 'api/*')) return true;
        // Statik dosya uzantıları
        $path = $request->path();
        if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico|css|js|woff2?|ttf|eot|pdf|zip|map)$/i', $path)) return true;
        // Bilinen tarayıcı / araç yolları
        if ($request->is('_debugbar*', 'up', 'horizon*', 'telescope*',
                         'wp-login.php', 'wp-admin*', 'xmlrpc.php', '.env', 'phpmyadmin*')) return true;
        // Bot tespiti (UA)
        $ua = strtolower($request->userAgent() ?? '');
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) return true;
        }
        // Boş user agent
        if (empty($ua) || strlen($ua) < 10) return true;

        return false;
    }

    private function resolveGeo(string $ip): array
    {
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return ['country_code' => 'TR', 'country' => 'Turkey', 'city' => 'Localhost'];
        }

        return Cache::remember("geo_{$ip}", 86400, function () use ($ip) {
            try {
                $resp = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city,regionName");
                if ($resp->successful() && $resp->json('status') === 'success') {
                    $city = $resp->json('city');
                    // "Merkez" sadece ilçe adı — gerçek şehir için regionName kullan
                    if (empty($city) || $city === 'Merkez') {
                        $city = $resp->json('regionName');
                    }
                    return [
                        'country_code' => $resp->json('countryCode'),
                        'country'      => $resp->json('country'),
                        'city'         => $city,
                    ];
                }
            } catch (\Throwable) {}
            return [];
        });
    }

    private function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'tablet';
        if (preg_match('/mobile|android|iphone|ipod|windows phone|blackberry/i', $ua)) return 'mobile';
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg/')) return 'Edge';
        if (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) return 'Opera';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident')) return 'IE';
        return 'Diğer';
    }

    private function detectOs(string $ua): string
    {
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'iOS')) return 'iOS';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac OS')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        return 'Diğer';
    }

    private function detectSource(string $referrer): string
    {
        if (empty($referrer)) return 'direct';
        if (preg_match('/google\.|bing\.|yahoo\.|yandex\.|duckduckgo\./i', $referrer)) return 'google';
        if (str_contains($referrer, 'instagram.com') || str_contains($referrer, 'l.instagram')) return 'instagram';
        if (str_contains($referrer, 'facebook.com') || str_contains($referrer, 'fb.com')) return 'facebook';
        if (str_contains($referrer, 'twitter.com') || str_contains($referrer, 't.co')) return 'twitter';
        if (str_contains($referrer, 'tripadvisor')) return 'tripadvisor';
        return 'referral';
    }
}
