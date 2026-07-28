<?php

namespace App\Modules\Reviews\Services;

use App\Models\GoogleReview;
use App\Models\ReviewSyncLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GoogleReviewService
{
    private ?string $placeId;
    private ?string $apiKey;

    public function __construct()
    {
        $this->placeId = config('services.google.place_id');
        $this->apiKey  = config('services.google.api_key');
    }

    /**
     * Zamanlanmış görev tarafından çağrılır.
     * Google Places API'den yorumları çeker, filtreler, DB'ye yazar.
     */
    public function sync(): array
    {
        $fetched = 0;
        $new     = 0;

        try {
            $reviews = $this->fetchFromApi();
            $fetched = count($reviews);

            foreach ($reviews as $review) {
                $exists = GoogleReview::where('google_review_id', $review['id'])->exists();
                if ($exists) continue;

                $rating = $review['rating'];
                $status = $rating >= 4 ? 'visible' : 'hidden';

                $googleReview = GoogleReview::create([
                    'google_review_id' => $review['id'],
                    'author_name'      => $review['author_name'],
                    'author_photo'     => $review['author_photo'] ?? null,
                    'rating'           => $rating,
                    'comment'          => $review['text'] ?? null,
                    'review_time'      => $review['time'],
                    'display_status'   => $status,
                    'admin_read'       => $status === 'visible',
                ]);

                if ($rating <= 3) {
                    $this->notifyAdminNegativeReview($googleReview);
                }

                $new++;
            }

            ReviewSyncLog::create([
                'source'    => 'google',
                'fetched'   => $fetched,
                'new'       => $new,
                'status'    => 'ok',
                'synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('GoogleReviewService sync failed', ['error' => $e->getMessage()]);
            ReviewSyncLog::create([
                'source'    => 'google',
                'fetched'   => $fetched,
                'new'       => $new,
                'status'    => 'error',
                'error'     => $e->getMessage(),
                'synced_at' => now(),
            ]);
        }

        return ['fetched' => $fetched, 'new' => $new];
    }

    /**
     * Vitrin için: sadece 4-5 yıldız, görünür, yorumlu olanlar.
     */
    public function getVisibleReviews(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return GoogleReview::where('display_status', 'visible')
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->orderByDesc('review_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Genel puan özeti. Google'ın kendi bildirdiği gerçek toplam puan/yorum
     * sayısı (sync sırasında önbelleğe alınır) — bizim DB'de sadece bir kısım
     * yorum saklı olduğu için (Google en fazla ~5 son yorumu döndürüyor),
     * yerel ortalama gerçek toplamı YANSITMAZ. Önbellek boşsa (ilk sync
     * öncesi) yerel ortalamaya düşer.
     */
    public function getSummary(): array
    {
        $cached = Cache::get('google_place_summary');
        if ($cached) {
            return $cached;
        }

        $all = GoogleReview::all();
        if ($all->isEmpty()) {
            return ['rating' => 0, 'total' => 0];
        }

        return [
            'rating' => round($all->avg('rating'), 1),
            'total'  => $all->count(),
        ];
    }

    // -----------------------------------------------------------------------

    /**
     * İşletme özeti: puan + toplam yorum (review text olmadan da çalışır).
     * Demo key'de rating/count gelir, production key'de reviews de gelir.
     */
    public function fetchSummaryFromApi(): array
    {
        $response = Http::timeout(10)
            ->withHeaders(['X-Goog-Api-Key' => $this->apiKey, 'X-Goog-FieldMask' => 'rating,userRatingCount'])
            ->get('https://places.googleapis.com/v1/places/' . $this->placeId, ['languageCode' => 'tr']);

        if (! $response->successful()) {
            throw new \RuntimeException('Places API error: ' . $response->status());
        }

        return [
            'rating' => $response->json('rating', 0),
            'total'  => $response->json('userRatingCount', 0),
        ];
    }

    private function notifyAdminNegativeReview(GoogleReview $review): void
    {
        $adminEmail = config('mail.admin_email', 'aydinyay@gmail.com');
        try {
            Mail::send('emails.reviews.negative-review', ['review' => $review], function ($m) use ($adminEmail, $review) {
                $m->to($adminEmail)
                  ->subject('⚠️ Düşük Puanlı Google Yorumu — ' . $review->rating . '★ ' . $review->author_name);
            });
        } catch (\Throwable $e) {
            Log::error('Negative review mail failed', ['error' => $e->getMessage()]);
        }
    }

    private function fetchFromApi(): array
    {
        $response = Http::timeout(10)
            ->withHeaders(['X-Goog-Api-Key' => $this->apiKey, 'X-Goog-FieldMask' => 'reviews,rating,userRatingCount'])
            ->get('https://places.googleapis.com/v1/places/' . $this->placeId, ['languageCode' => 'tr']);

        if (! $response->successful()) {
            throw new \RuntimeException('Places API error: ' . $response->status() . ' ' . $response->body());
        }

        Cache::put('google_place_summary', [
            'rating' => $response->json('rating', 0),
            'total'  => $response->json('userRatingCount', 0),
        ], now()->addDay());

        return array_map(fn($r) => [
            'id'          => $r['name'],
            'author_name' => $r['authorAttribution']['displayName'] ?? 'Anonim',
            'author_photo'=> $r['authorAttribution']['photoUri'] ?? null,
            'rating'      => $r['rating'],
            'text'        => $r['text']['text'] ?? null,
            'time'        => \Carbon\Carbon::parse($r['publishTime']),
        ], $response->json('reviews', []));
    }
}
