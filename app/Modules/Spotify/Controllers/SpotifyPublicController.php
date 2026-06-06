<?php

namespace App\Modules\Spotify\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Spotify\Services\SpotifyService;

class SpotifyPublicController extends Controller
{
    public function __construct(private SpotifyService $spotify) {}

    public function status()
    {
        $data = $this->spotify->getStatus();
        return response()->json($data)
            ->header('Cache-Control', 'no-store')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
