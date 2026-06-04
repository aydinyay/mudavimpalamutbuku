<?php

use App\Modules\Admin\Controllers\DashboardController;
use App\Modules\Admin\Controllers\ServerToolsController;
use App\Modules\Menu\Controllers\Admin\CategoryController;
use App\Modules\Menu\Controllers\Admin\MenuItemController;
use App\Modules\QrCode\Controllers\Admin\QrCodeController;
use App\Modules\Reservation\Controllers\Admin\ReservationController as AdminReservationController;
use App\Modules\TablePlan\Controllers\Admin\TableController;
use Illuminate\Support\Facades\Route;

Route::prefix('yonetim')->middleware(['auth'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/ayarlar', [DashboardController::class, 'settings'])->name('settings');
    Route::post('/ayarlar', [DashboardController::class, 'updateSettings'])->name('settings.update');

    // Reservations
    Route::resource('rezervasyonlar', AdminReservationController::class, [
        'as' => 'reservations',
        'parameters' => ['rezervasyonlar' => 'reservation'],
    ]);
    Route::post('rezervasyonlar/{reservation}/durum', [AdminReservationController::class, 'updateStatus'])
        ->name('reservations.status');

    // Table plan
    Route::resource('masalar', TableController::class, [
        'as' => 'tables',
        'parameters' => ['masalar' => 'table'],
    ]);
    Route::patch('masalar/{table}/pozisyon', [TableController::class, 'updatePosition'])
        ->name('tables.position');

    // Menu categories
    Route::resource('menu/kategoriler', CategoryController::class, [
        'as'         => 'menu.categories',
        'parameters' => ['kategoriler' => 'category'],
    ]);

    // Menu items
    Route::resource('menu/urunler', MenuItemController::class, [
        'as'         => 'menu.items',
        'parameters' => ['urunler' => 'item'],
    ]);
    Route::patch('menu/urunler/{item}/toggle-available', [MenuItemController::class, 'toggleAvailable'])
        ->name('menu.items.toggle');
    Route::post('menu/kategoriler/sirala', [CategoryController::class, 'reorder'])
        ->name('menu.categories.reorder');

    // QR codes
    Route::get('qr-kodlar', [QrCodeController::class, 'index'])->name('qrcodes.index');
    Route::post('qr-kodlar/{table}/uret', [QrCodeController::class, 'generate'])->name('qrcodes.generate');
    Route::get('qr-kodlar/yazdir', [QrCodeController::class, 'printSheet'])->name('qrcodes.print');

    // Server tools
    Route::get('sunucu-araclari', [ServerToolsController::class, 'index'])->name('server-tools.index');
    Route::post('sunucu-araclari/cache', [ServerToolsController::class, 'clearCache'])->name('server-tools.cache');
    Route::post('sunucu-araclari/migrate', [ServerToolsController::class, 'migrate'])->name('server-tools.migrate');
    Route::post('sunucu-araclari/mail', [ServerToolsController::class, 'testMail'])->name('server-tools.mail');
    Route::get('sunucu-araclari/sysinfo', [ServerToolsController::class, 'sysInfo'])->name('server-tools.sysinfo');
});
