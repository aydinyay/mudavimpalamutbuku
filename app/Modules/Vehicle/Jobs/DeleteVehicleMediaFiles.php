<?php

namespace App\Modules\Vehicle\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Support\Facades\Storage;

class DeleteVehicleMediaFiles implements ShouldQueue
{
    use FoundationQueueable;

    public int $tries = 5;

    public function __construct(public array $paths)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        foreach (array_unique($this->paths) as $path) {
            Storage::disk('local')->delete($path);
        }
    }
}
