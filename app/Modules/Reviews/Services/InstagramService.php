<?php

namespace App\Modules\Reviews\Services;

use App\Models\InstagramPost;
use App\Models\ReviewSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private string $accessToken;
    private string $userId;

    public function __construct()
    {
        $this->accessToken = config('services.instagram.access_token');
        $this->userId      = config('services.instagram.user_id');
    }

    /**
     * Zamanlanmış görev: Instagram Graph API'den son postları çeker.
     */
    public function sync(int $limit = 20): array
    {
        $fetched = 0;
        $new     = 0;

        try {
            $posts   = $this->fetchFromApi($limit);
            $fetched = count($posts);

            foreach ($posts as $post) {
                if (InstagramPost::where('instagram_id', $post['id'])->exists()) continue;

                InstagramPost::create([
                    'instagram_id'  => $post['id'],
                    'media_type'    => $post['media_type'],
                    'media_url'     => $post['media_url'] ?? ($post['thumbnail_url'] ?? ''),
                    'thumbnail_url' => $post['thumbnail_url'] ?? null,
                    'caption'       => isset($post['caption']) ? mb_substr($post['caption'], 0, 500) : null,
                    'permalink'     => $post['permalink'],
                    'posted_at'     => \Carbon\Carbon::parse($post['timestamp']),
                ]);

                $new++;
            }

            ReviewSyncLog::create([
                'source'    => 'instagram',
                'fetched'   => $fetched,
                'new'       => $new,
                'status'    => 'ok',
                'synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('InstagramService sync failed', ['error' => $e->getMessage()]);
            ReviewSyncLog::create([
                'source'    => 'instagram',
                'fetched'   => $fetched,
                'new'       => $new,
                'status'    => 'error',
                'error'     => $e->getMessage(),
                'synced_at' => now(),
            ]);
        }

        return ['fetched' => $fetched, 'new' => $new];
    }

    public function getGalleryPosts(int $limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return InstagramPost::where('is_visible', true)
            ->whereIn('media_type', ['IMAGE', 'CAROUSEL_ALBUM'])
            ->orderByDesc('posted_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Token her 60 günde expire olur. Bu metod yeni long-lived token üretir.
     * Admin panelinden manuel tetiklenir.
     */
    public function refreshToken(): string
    {
        $response = Http::get('https://graph.instagram.com/refresh_access_token', [
            'grant_type'   => 'ig_refresh_token',
            'access_token' => $this->accessToken,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Token refresh failed: ' . $response->body());
        }

        $newToken = $response->json('access_token');

        // Config yerine DB'ye veya .env'e yaz (admin panelinden çağrılır)
        \App\Models\RestaurantSetting::updateOrCreate(
            ['key' => 'instagram_access_token'],
            ['value' => $newToken]
        );

        return $newToken;
    }

    // -----------------------------------------------------------------------

    private function fetchFromApi(int $limit): array
    {
        $response = Http::timeout(10)->get(
            "https://graph.instagram.com/{$this->userId}/media",
            [
                'fields'       => 'id,media_type,media_url,thumbnail_url,caption,permalink,timestamp',
                'limit'        => $limit,
                'access_token' => $this->accessToken,
            ]
        );

        if (! $response->successful()) {
            throw new \RuntimeException('Instagram API error: ' . $response->status());
        }

        return $response->json('data', []);
    }
}
