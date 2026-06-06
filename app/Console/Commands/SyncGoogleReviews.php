<?php

namespace App\Console\Commands;

use App\Modules\Reviews\Services\GoogleReviewService;
use Illuminate\Console\Command;

class SyncGoogleReviews extends Command
{
    protected $signature   = 'reviews:sync-google';
    protected $description = 'Google Places API\'den yorumları çeker ve filtreler';

    public function handle(GoogleReviewService $service): int
    {
        $result = $service->sync();
        $this->info("Çekildi: {$result['fetched']}, Yeni: {$result['new']}");
        return 0;
    }
}
