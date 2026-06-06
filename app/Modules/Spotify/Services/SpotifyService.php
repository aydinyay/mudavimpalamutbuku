<?php

namespace App\Modules\Spotify\Services;

use App\Modules\Core\Models\RestaurantSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    private const AUTH_URL    = 'https://accounts.spotify.com/authorize';
    private const TOKEN_URL   = 'https://accounts.spotify.com/api/token';
    private const API_BASE    = 'https://api.spotify.com/v1/me/player';
    private const CACHE_TTL   = 20; // saniye
    private const SCOPES      = 'user-read-currently-playing user-read-recently-played user-read-playback-state';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId     = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
        $this->redirectUri  = config('services.spotify.redirect_uri');
    }

    // ── OAuth ──────────────────────────────────────────────────────────────

    public function getAuthUrl(string $state): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
            'scope'         => self::SCOPES,
            'state'         => $state,
        ]);
    }

    public function handleCallback(string $code): bool
    {
        try {
            $resp = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post(self::TOKEN_URL, [
                    'grant_type'   => 'authorization_code',
                    'code'         => $code,
                    'redirect_uri' => $this->redirectUri,
                ]);

            if (! $resp->successful()) return false;

            $this->saveTokens($resp->json());
            return true;
        } catch (\Throwable $e) {
            Log::error('Spotify handleCallback failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function refreshAccessToken(): bool
    {
        $setting = RestaurantSetting::current();
        if (empty($setting->spotify_refresh_token)) return false;

        try {
            $refresh = Crypt::decryptString($setting->spotify_refresh_token);
            $resp = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post(self::TOKEN_URL, [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refresh,
                ]);

            if (! $resp->successful()) return false;

            $data = $resp->json();
            // refresh_token bazen gelmez — eskisini koru
            if (empty($data['refresh_token'])) {
                $data['refresh_token'] = $refresh;
                $data['_raw_refresh']  = true;
            }

            $this->saveTokens($data);
            return true;
        } catch (\Throwable $e) {
            Log::error('Spotify refresh failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ── Status & Cache ─────────────────────────────────────────────────────

    public function getStatus(): array
    {
        $setting = RestaurantSetting::current();

        if (! $setting->spotify_enabled || empty($setting->spotify_refresh_token)) {
            return $this->fallbackResponse($setting);
        }

        // Cache kontrolü
        $cache = DB::table('spotify_cache')
            ->where('type', 'now_playing')
            ->where('updated_at', '>=', now()->subSeconds(self::CACHE_TTL))
            ->first();

        if ($cache) {
            return $this->buildResponse($setting);
        }

        // Access token yenile (gerekiyorsa)
        if ($this->isTokenExpired($setting)) {
            if (! $this->refreshAccessToken()) {
                return $this->fallbackResponse($setting);
            }
            $setting = RestaurantSetting::current();
        }

        // API'den çek & cache'e yaz
        $this->fetchAndCache($setting);
        return $this->buildResponse($setting);
    }

    private function fetchAndCache(RestaurantSetting $setting): void
    {
        $token = Crypt::decryptString($setting->spotify_access_token);

        // Şu an çalıyor
        try {
            $np = Http::withToken($token)->timeout(5)
                ->get(self::API_BASE . '/currently-playing', ['market' => 'TR']);

            if ($np->status() === 204 || ! $np->successful()) {
                $this->upsertCache('now_playing', null, false);
            } else {
                $this->upsertCache('now_playing', $np->json(), (bool)($np->json('is_playing') ?? false));
            }
        } catch (\Throwable) {
            $this->upsertCache('now_playing', null, false);
        }

        // Az önce çalanlar
        try {
            $recent = Http::withToken($token)->timeout(5)
                ->get(self::API_BASE . '/recently-played', ['limit' => 5]);
            if ($recent->successful()) {
                $this->upsertCache('recent', $recent->json());
            }
        } catch (\Throwable) {}

        // Kuyruk
        try {
            $queue = Http::withToken($token)->timeout(5)
                ->get(self::API_BASE . '/queue');
            if ($queue->successful()) {
                $this->upsertCache('queue', $queue->json());
            }
        } catch (\Throwable) {}
    }

    private function buildResponse(RestaurantSetting $setting): array
    {
        $caches = DB::table('spotify_cache')->get()->keyBy('type');

        $nowPlaying = $this->parseNowPlaying($caches->get('now_playing'));
        $recent     = $this->parseRecent($caches->get('recent'));
        $queue      = $this->parseQueue($caches->get('queue'));

        $isPlaying = (bool)($caches->get('now_playing')?->is_playing ?? false);

        return [
            'enabled'         => true,
            'connected'       => true,
            'is_playing'      => $isPlaying,
            'now_playing'     => $nowPlaying,
            'recent'          => $recent,
            'queue'           => $queue,
            'fallback'        => null,
            'widget_website'  => (bool)$setting->spotify_widget_on_website,
            'widget_menu'     => (bool)$setting->spotify_widget_on_menu,
        ];
    }

    private function fallbackResponse(RestaurantSetting $setting): array
    {
        return [
            'enabled'     => (bool)$setting->spotify_enabled,
            'connected'   => ! empty($setting->spotify_refresh_token),
            'is_playing'  => false,
            'now_playing' => null,
            'recent'      => [],
            'queue'       => [],
            'fallback'    => [
                'name' => $setting->spotify_fallback_playlist_name ?: 'Müdavim Playlist',
                'url'  => $setting->spotify_fallback_playlist_url,
            ],
            'widget_website' => (bool)$setting->spotify_widget_on_website,
            'widget_menu'    => (bool)$setting->spotify_widget_on_menu,
        ];
    }

    // ── Parsers ────────────────────────────────────────────────────────────

    private function parseNowPlaying(?\stdClass $cache): ?array
    {
        if (! $cache || ! $cache->is_playing) return null;
        $data = json_decode($cache->data, true);
        if (empty($data['item'])) return null;

        $item = $data['item'];
        return [
            'name'       => $item['name'] ?? '',
            'artists'    => implode(', ', array_column($item['artists'] ?? [], 'name')),
            'album'      => $item['album']['name'] ?? '',
            'cover'      => $item['album']['images'][0]['url'] ?? null,
            'duration_ms'=> $item['duration_ms'] ?? 0,
            'progress_ms'=> $data['progress_ms'] ?? 0,
            'spotify_url'=> $item['external_urls']['spotify'] ?? null,
        ];
    }

    private function parseRecent(?\stdClass $cache): array
    {
        if (! $cache) return [];
        $data = json_decode($cache->data, true);
        $items = $data['items'] ?? [];

        return array_map(fn($i) => [
            'name'    => $i['track']['name'] ?? '',
            'artists' => implode(', ', array_column($i['track']['artists'] ?? [], 'name')),
            'cover'   => $i['track']['album']['images'][1]['url'] ?? null,
            'played_at'=> $i['played_at'] ?? null,
        ], array_slice($items, 0, 5));
    }

    private function parseQueue(?\stdClass $cache): array
    {
        if (! $cache) return [];
        $data = json_decode($cache->data, true);
        $items = $data['queue'] ?? [];

        return array_map(fn($i) => [
            'name'    => $i['name'] ?? '',
            'artists' => implode(', ', array_column($i['artists'] ?? [], 'name')),
            'cover'   => $i['album']['images'][2]['url'] ?? null,
        ], array_slice($items, 0, 2));
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isConnected(): bool
    {
        return ! empty(RestaurantSetting::current()->spotify_refresh_token);
    }

    public function isEnabled(): bool
    {
        return (bool) RestaurantSetting::current()->spotify_enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        RestaurantSetting::current()->update(['spotify_enabled' => $enabled]);
    }

    public function disconnect(): void
    {
        RestaurantSetting::current()->update([
            'spotify_access_token'    => null,
            'spotify_refresh_token'   => null,
            'spotify_token_expires_at'=> null,
            'spotify_enabled'         => false,
        ]);
        DB::table('spotify_cache')->truncate();
    }

    private function saveTokens(array $data): void
    {
        $update = [
            'spotify_access_token'     => Crypt::encryptString($data['access_token']),
            'spotify_token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ];

        if (isset($data['refresh_token']) && ! ($data['_raw_refresh'] ?? false)) {
            $update['spotify_refresh_token'] = Crypt::encryptString($data['refresh_token']);
        }

        RestaurantSetting::current()->update($update);
    }

    private function isTokenExpired(RestaurantSetting $setting): bool
    {
        if (empty($setting->spotify_token_expires_at)) return true;
        return now()->addMinutes(5)->isAfter($setting->spotify_token_expires_at);
    }

    private function upsertCache(string $type, ?array $data, bool $isPlaying = false): void
    {
        DB::table('spotify_cache')->updateOrInsert(
            ['type' => $type],
            ['data' => $data ? json_encode($data) : null, 'is_playing' => $isPlaying, 'updated_at' => now()]
        );
    }
}
