<?php

use App\Modules\Website\Controllers\AmbianceController;
use App\Modules\Website\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $base = rtrim(url('/'), '/');
    $today = now()->toDateString();
    $urls = [
        [$base . '/',             '1.0', 'daily'],
        [$base . '/hakkimizda',   '0.8', 'monthly'],
        [$base . '/iletisim',     '0.8', 'monthly'],
        [$base . '/galeri',       '0.7', 'weekly'],
        [$base . '/ambiyans',     '0.9', 'always'],
        [$base . '/en/',          '0.8', 'daily'],
        [$base . '/en/about',     '0.6', 'monthly'],
        [$base . '/en/contact',   '0.6', 'monthly'],
        [$base . '/de/',          '0.8', 'daily'],
        [$base . '/de/uber-uns',  '0.6', 'monthly'],
        [$base . '/de/kontakt',   '0.6', 'monthly'],
    ];

    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as [$loc, $priority, $freq]) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$today}</lastmod>\n";
        $xml .= "    <changefreq>{$freq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::middleware('setLocale')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('website.home');
    Route::get('/ambiyans', [AmbianceController::class, 'index'])->name('website.ambiance');
    Route::get('/hakkimizda', [HomeController::class, 'about'])->name('website.about');
    Route::get('/iletisim', [HomeController::class, 'contact'])->name('website.contact');
    Route::get('/galeri', [HomeController::class, 'gallery'])->name('website.gallery');

    Route::prefix('en')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('/about', [HomeController::class, 'about']);
        Route::get('/contact', [HomeController::class, 'contact']);
        Route::get('/gallery', [HomeController::class, 'gallery']);
        Route::get('/ambiyans', [AmbianceController::class, 'index']);
    });

    Route::prefix('de')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('/uber-uns', [HomeController::class, 'about']);
        Route::get('/kontakt', [HomeController::class, 'contact']);
        Route::get('/galerie', [HomeController::class, 'gallery']);
        Route::get('/ambiyans', [AmbianceController::class, 'index']);
    });
});
