<?php

use App\Modules\Admin\Controllers\AdminBridgeController;
use App\Modules\Admin\Controllers\DashboardController;
use App\Modules\Admin\Controllers\DailySpecialController;
use App\Modules\Admin\Controllers\GalleryPhotoController;
use App\Modules\Admin\Controllers\ServerToolsController;
use App\Modules\Admin\Controllers\SiteMediaController;
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
    Route::resource('rezervasyonlar', AdminReservationController::class)
        ->parameters(['rezervasyonlar' => 'reservation'])
        ->names([
            'index'   => 'reservations.index',
            'create'  => 'reservations.create',
            'store'   => 'reservations.store',
            'show'    => 'reservations.show',
            'edit'    => 'reservations.edit',
            'update'  => 'reservations.update',
            'destroy' => 'reservations.destroy',
        ]);
    Route::post('rezervasyonlar/{reservation}/durum', [AdminReservationController::class, 'updateStatus'])
        ->name('reservations.status');
    Route::get('hizli-rezervasyon', [AdminReservationController::class, 'quick'])->name('reservations.quick');

    // Table plan
    Route::resource('masalar', TableController::class)
        ->parameters(['masalar' => 'table'])
        ->names([
            'index'   => 'tables.index',
            'create'  => 'tables.create',
            'store'   => 'tables.store',
            'show'    => 'tables.show',
            'edit'    => 'tables.edit',
            'update'  => 'tables.update',
            'destroy' => 'tables.destroy',
        ]);
    Route::patch('masalar/{table}/pozisyon', [TableController::class, 'updatePosition'])
        ->name('tables.position');

    // Menu categories
    Route::resource('menu/kategoriler', CategoryController::class)
        ->parameters(['kategoriler' => 'category'])
        ->names([
            'index'   => 'menu.categories.index',
            'create'  => 'menu.categories.create',
            'store'   => 'menu.categories.store',
            'show'    => 'menu.categories.show',
            'edit'    => 'menu.categories.edit',
            'update'  => 'menu.categories.update',
            'destroy' => 'menu.categories.destroy',
        ]);

    // Menu items
    Route::resource('menu/urunler', MenuItemController::class)
        ->parameters(['urunler' => 'item'])
        ->names([
            'index'   => 'menu.items.index',
            'create'  => 'menu.items.create',
            'store'   => 'menu.items.store',
            'show'    => 'menu.items.show',
            'edit'    => 'menu.items.edit',
            'update'  => 'menu.items.update',
            'destroy' => 'menu.items.destroy',
        ]);
    Route::patch('menu/urunler/{item}/toggle-available', [MenuItemController::class, 'toggleAvailable'])
        ->name('menu.items.toggle');
    Route::post('menu/kategoriler/sirala', [CategoryController::class, 'reorder'])
        ->name('menu.categories.reorder');

    // QR codes
    Route::get('qr-kodlar', [QrCodeController::class, 'index'])->name('qrcodes.index');
    Route::post('qr-kodlar/{table}/uret', [QrCodeController::class, 'generate'])->name('qrcodes.generate');
    Route::get('qr-kodlar/yazdir', [QrCodeController::class, 'printSheet'])->name('qrcodes.print');

    // Site media / image uploads
    Route::get('gorseller', [SiteMediaController::class, 'index'])->name('media.index');
    Route::post('gorseller/yukle', [SiteMediaController::class, 'upload'])->name('media.upload');
    Route::post('gorseller/sil', [SiteMediaController::class, 'delete'])->name('media.delete');

    // Daily specials
    Route::resource('gunluk-ozel', DailySpecialController::class)
        ->except(['show'])
        ->parameters(['gunluk-ozel' => 'special'])
        ->names([
            'index'   => 'daily-specials.index',
            'create'  => 'daily-specials.create',
            'store'   => 'daily-specials.store',
            'edit'    => 'daily-specials.edit',
            'update'  => 'daily-specials.update',
            'destroy' => 'daily-specials.destroy',
        ]);

    // Gallery photos — bulk upload (must be before resource to avoid conflict)
    Route::get('galeri/toplu-yukle', [GalleryPhotoController::class, 'bulkCreate'])->name('gallery.bulk');
    Route::post('galeri/toplu-yukle', [GalleryPhotoController::class, 'bulkStore'])->name('gallery.bulk.store');

    Route::resource('galeri', GalleryPhotoController::class)
        ->except(['show'])
        ->parameters(['galeri' => 'photo'])
        ->names([
            'index'   => 'gallery.index',
            'create'  => 'gallery.create',
            'store'   => 'gallery.store',
            'edit'    => 'gallery.edit',
            'update'  => 'gallery.update',
            'destroy' => 'gallery.destroy',
        ]);

    // Bridge to restoran system
    Route::get('restoran-gecis', [AdminBridgeController::class, 'redirectToRestoran'])->name('bridge.restoran');

    // Server tools
    Route::get('sunucu-araclari', [ServerToolsController::class, 'index'])->name('server-tools.index');
    Route::post('sunucu-araclari/cache', [ServerToolsController::class, 'clearCache'])->name('server-tools.cache');
    Route::post('sunucu-araclari/migrate', [ServerToolsController::class, 'migrate'])->name('server-tools.migrate');
    Route::post('sunucu-araclari/mail', [ServerToolsController::class, 'testMail'])->name('server-tools.mail');
    Route::get('sunucu-araclari/sysinfo', [ServerToolsController::class, 'sysInfo'])->name('server-tools.sysinfo');
});
