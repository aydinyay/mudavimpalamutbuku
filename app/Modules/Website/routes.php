<?php

use App\Modules\Website\Controllers\AmbianceController;
use App\Modules\Website\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'),             'priority' => '1.0', 'freq' => 'daily'],
        ['loc' => url('/hakkimizda'),   'priority' => '0.8', 'freq' => 'monthly'],
        ['loc' => url('/iletisim'),     'priority' => '0.8', 'freq' => 'monthly'],
        ['loc' => url('/galeri'),       'priority' => '0.7', 'freq' => 'weekly'],
        ['loc' => url('/ambiyans'),     'priority' => '0.9', 'freq' => 'always'],
        ['loc' => url('/en/'),          'priority' => '0.8', 'freq' => 'daily'],
        ['loc' => url('/en/about'),     'priority' => '0.6', 'freq' => 'monthly'],
        ['loc' => url('/en/contact'),   'priority' => '0.6', 'freq' => 'monthly'],
        ['loc' => url('/de/'),          'priority' => '0.8', 'freq' => 'daily'],
        ['loc' => url('/de/uber-uns'),  'priority' => '0.6', 'freq' => 'monthly'],
        ['loc' => url('/de/kontakt'),   'priority' => '0.6', 'freq' => 'monthly'],
    ];
    $xml = view('website.sitemap', compact('urls'));
    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::middleware('setLocale')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('website.home');
    Route::get('/ambiyans', [AmbianceController::class, 'index'])->name('website.ambiance');
    Route::get('/hakkimizda', [HomeController::class, 'about'])->name('website.about');
    Route::get('/iletisim', [HomeController::class, 'contact'])->name('website.contact');
    Route::get('/galeri', [HomeController::class, 'gallery'])->name('website.gallery');
    Route::get('/navtest', fn() => view('website.navtest'));

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
