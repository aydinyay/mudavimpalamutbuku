<?php

use App\Modules\Analytics\Controllers\Admin\AnalyticsController;
use Illuminate\Support\Facades\Route;

// Admin
Route::prefix('yonetim')->middleware(['auth'])->name('admin.')->group(function () {
    Route::get('analitik', [AnalyticsController::class, 'index'])->name('analytics.index');
});

// Public event tracker (CSRF korumalı)
Route::post('/_analytics/event', [AnalyticsController::class, 'trackEvent'])->name('analytics.event');
