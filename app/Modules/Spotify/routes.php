<?php

use App\Modules\Spotify\Controllers\Admin\SpotifyAdminController;
use App\Modules\Spotify\Controllers\SpotifyPublicController;
use Illuminate\Support\Facades\Route;

// Public — status endpoint (JS polling)
Route::get('/spotify/status', [SpotifyPublicController::class, 'status'])->name('spotify.status');

// Admin
Route::prefix('yonetim/spotify')->middleware(['auth'])->name('admin.spotify.')->group(function () {
    Route::get('/',           [SpotifyAdminController::class, 'index'])->name('index');
    Route::get('/auth',       [SpotifyAdminController::class, 'auth'])->name('auth');
    Route::get('/callback',   [SpotifyAdminController::class, 'callback'])->name('callback');
    Route::post('/toggle',    [SpotifyAdminController::class, 'toggle'])->name('toggle');
    Route::post('/settings',  [SpotifyAdminController::class, 'saveSettings'])->name('settings');
    Route::post('/disconnect',[SpotifyAdminController::class, 'disconnect'])->name('disconnect');
    Route::post('/refresh',   [SpotifyAdminController::class, 'refresh'])->name('refresh');
});
