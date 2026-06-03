<?php

use App\Modules\Reservation\Controllers\Public\ReservationController;
use Illuminate\Support\Facades\Route;

Route::middleware('setLocale')->group(function () {
    Route::get('/rezervasyon', [ReservationController::class, 'create'])->name('reservation.public.create');
    Route::post('/rezervasyon', [ReservationController::class, 'store'])->name('reservation.public.store');
    Route::get('/rezervasyon/{uuid}', [ReservationController::class, 'show'])->name('reservation.public.show');
    Route::get('/rezervasyon/{uuid}/iptal', [ReservationController::class, 'cancelForm'])->name('reservation.public.cancel.form');
    Route::post('/rezervasyon/{uuid}/iptal', [ReservationController::class, 'cancel'])->name('reservation.public.cancel');

    // AJAX availability check
    Route::post('/api/rezervasyon/uygunluk', [ReservationController::class, 'checkAvailability'])
        ->name('reservation.availability');

    // English
    Route::prefix('en')->group(function () {
        Route::get('/reservation', [ReservationController::class, 'create']);
        Route::post('/reservation', [ReservationController::class, 'store']);
        Route::get('/reservation/{uuid}', [ReservationController::class, 'show']);
    });

    // German
    Route::prefix('de')->group(function () {
        Route::get('/reservierung', [ReservationController::class, 'create']);
        Route::post('/reservierung', [ReservationController::class, 'store']);
        Route::get('/reservierung/{uuid}', [ReservationController::class, 'show']);
    });
});
