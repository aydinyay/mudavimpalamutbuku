<?php

use Illuminate\Support\Facades\Route;

// Redirect /dashboard → /yonetim (Breeze uses /dashboard as post-login redirect)
Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))
    ->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
