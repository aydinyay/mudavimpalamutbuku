<?php

use App\Modules\Menu\Controllers\Public\MenuController;
use Illuminate\Support\Facades\Route;

Route::middleware('setLocale')->group(function () {
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.public.index');
    Route::get('/menu/masa/{tableCode}', [MenuController::class, 'tableMenu'])->name('menu.public.table');

    Route::prefix('en')->group(function () {
        Route::get('/menu', [MenuController::class, 'index']);
        Route::get('/menu/table/{tableCode}', [MenuController::class, 'tableMenu']);
    });

    Route::prefix('de')->group(function () {
        Route::get('/menu', [MenuController::class, 'index']);
        Route::get('/menu/tisch/{tableCode}', [MenuController::class, 'tableMenu']);
    });
});

// API: QR scan counter (AJAX, no locale prefix needed)
Route::post('/api/qr-scan/{uuid}', function (string $uuid) {
    \App\Modules\QrCode\Models\QrCode::where('qr_uuid', $uuid)->increment('scan_count');
    return response()->json(['ok' => true]);
})->middleware('throttle:60,1');
