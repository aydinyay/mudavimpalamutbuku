<?php

namespace App\Console\Commands;

use App\Modules\Reviews\Services\InstagramService;
use Illuminate\Console\Command;

class SyncInstagramPosts extends Command
{
    protected $signature   = 'instagram:sync';
    protected $description = 'Instagram Graph API\'den son gönderileri çeker';

    public function handle(InstagramService $service): int
    {
        $result = $service->sync();
        $this->info("Çekildi: {$result['fetched']}, Yeni: {$result['new']}");
        return 0;
    }
}
