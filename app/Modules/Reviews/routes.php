<?php

use App\Modules\Reviews\Controllers\SurveyController;
use App\Modules\Reviews\Controllers\Admin\ReviewAdminController;
use App\Modules\Reviews\Controllers\Admin\SurveyAdminController;
use App\Modules\Reviews\Controllers\Admin\InstagramAdminController;
use Illuminate\Support\Facades\Route;

// Anket (public — token korumalı)
Route::middleware('setLocale')->group(function () {
    Route::get('/anket/{token}',  [SurveyController::class, 'show'])->name('survey.show');
    Route::post('/anket/{token}', [SurveyController::class, 'submit'])->name('survey.submit');
    Route::get('/tesekkurler',    [SurveyController::class, 'thankYou'])->name('survey.thank_you');
});

// Admin — yorum & şikayet yönetimi
Route::prefix('yonetim')->middleware(['auth'])->name('admin.')->group(function () {
    Route::get('yorumlar',                     [ReviewAdminController::class, 'index'])->name('reviews.index');
    Route::post('yorumlar/{review}/okundu',    [ReviewAdminController::class, 'markRead'])->name('reviews.read');
    Route::post('sikayet/{survey}/okundu',     [ReviewAdminController::class, 'markComplaintRead'])->name('complaints.read');
    Route::post('yorumlar/sync',               [ReviewAdminController::class, 'syncNow'])->name('reviews.sync');

    // Anket — WhatsApp linki üret
    Route::get('rezervasyon/{reservation}/anket-whatsapp', [SurveyAdminController::class, 'whatsappLink'])->name('survey.whatsapp');

    // Instagram yönetimi
    Route::get('instagram',                    [InstagramAdminController::class, 'index'])->name('instagram.index');
    Route::post('instagram/{post}/toggle',     [InstagramAdminController::class, 'toggle'])->name('instagram.toggle');
    Route::post('instagram/sync',              [InstagramAdminController::class, 'syncNow'])->name('instagram.sync');
    Route::post('instagram/token-refresh',     [InstagramAdminController::class, 'refreshToken'])->name('instagram.token-refresh');
});
