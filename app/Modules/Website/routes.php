<?php

use App\Modules\Website\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('setLocale')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('website.home');
    Route::get('/hakkimizda', [HomeController::class, 'about'])->name('website.about');
    Route::get('/iletisim', [HomeController::class, 'contact'])->name('website.contact');
    Route::get('/galeri', [HomeController::class, 'gallery'])->name('website.gallery');

    Route::prefix('en')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('/about', [HomeController::class, 'about']);
        Route::get('/contact', [HomeController::class, 'contact']);
        Route::get('/gallery', [HomeController::class, 'gallery']);
    });

    Route::prefix('de')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('/uber-uns', [HomeController::class, 'about']);
        Route::get('/kontakt', [HomeController::class, 'contact']);
        Route::get('/galerie', [HomeController::class, 'gallery']);
    });
});
