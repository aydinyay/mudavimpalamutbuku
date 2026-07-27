<?php

use App\Modules\Vehicle\Controllers\Admin\VehicleAdminController;
use App\Modules\Vehicle\Controllers\Admin\VehicleExpenseController;
use App\Modules\Vehicle\Controllers\Admin\VehicleIssueController;
use App\Modules\Vehicle\Controllers\Admin\VehicleMediaController;
use App\Modules\Vehicle\Controllers\Admin\VehiclePartyController;
use App\Modules\Vehicle\Controllers\VehicleAccessController;
use App\Modules\Vehicle\Controllers\VehicleOwnerController;
use App\Modules\Vehicle\Controllers\VehicleShowcaseController;
use Illuminate\Support\Facades\Route;

Route::middleware('vehicle.headers')->group(function () {
    Route::get('arac-sorgula', [VehicleAccessController::class, 'form'])->name('vehicle.access.form');
    Route::post('arac-sorgula', [VehicleAccessController::class, 'login'])->name('vehicle.access.login');
    Route::middleware('vehicle.owner')->group(function () {
        Route::get('aracim', [VehicleOwnerController::class, 'show'])->name('vehicle.owner');
        Route::get('aracim/medya/{media}', [VehicleOwnerController::class, 'media'])->name('vehicle.owner.media');
        Route::post('arac-cikis', [VehicleAccessController::class, 'logout'])->name('vehicle.logout');
    });
    Route::get('arac/vitrin/{plate_key}', [VehicleShowcaseController::class, 'show'])->name('vehicle.showcase');
    Route::get('arac/vitrin/{plate_key}/medya/{media}', [VehicleShowcaseController::class, 'media'])->name('vehicle.showcase.media');
});
Route::prefix('yonetim')->middleware('auth')->name('admin.')->group(function () {
    Route::resource('araclar', VehicleAdminController::class)->parameters(['araclar' => 'vehicle'])->names('vehicles');
    Route::post('araclar/{vehicle}/sifre', [VehicleAdminController::class, 'resetPassword'])->name('vehicles.password');
    Route::post('araclar/{vehicle}/masraflar', [VehicleExpenseController::class, 'store'])->name('vehicles.expenses.store');
    Route::put('araclar/{vehicle}/masraflar/{expense}', [VehicleExpenseController::class, 'update'])->name('vehicles.expenses.update');
    Route::delete('araclar/{vehicle}/masraflar/{expense}', [VehicleExpenseController::class, 'destroy'])->name('vehicles.expenses.destroy');
    Route::delete('araclar/{vehicle}/masraflar/{expense}/kalici', [VehicleExpenseController::class, 'forceDestroy'])->name('vehicles.expenses.force');
    Route::post('araclar/{vehicle}/sorunlar', [VehicleIssueController::class, 'store'])->name('vehicles.issues.store');
    Route::put('araclar/{vehicle}/sorunlar/{issue}', [VehicleIssueController::class, 'update'])->name('vehicles.issues.update');
    Route::delete('araclar/{vehicle}/sorunlar/{issue}', [VehicleIssueController::class, 'destroy'])->name('vehicles.issues.destroy');
    Route::delete('araclar/{vehicle}/sorunlar/{issue}/kalici', [VehicleIssueController::class, 'forceDestroy'])->name('vehicles.issues.force');
    Route::post('araclar/{vehicle}/medya', [VehicleMediaController::class, 'store'])->name('vehicles.media.store');
    Route::get('araclar/{vehicle}/medya/{media}', [VehicleMediaController::class, 'show'])->name('vehicles.media.show');
    Route::delete('araclar/{vehicle}/medya/{media}', [VehicleMediaController::class, 'destroy'])->name('vehicles.media.destroy');
    Route::resource('arac-taraflari', VehiclePartyController::class)->except(['create', 'show', 'edit'])->parameters(['arac-taraflari' => 'party'])->names('vehicle-parties');
});
