<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private const MODULE_PROVIDERS = [
        'admin'       => \App\Modules\Admin\Providers\AdminServiceProvider::class,
        'website'     => \App\Modules\Website\Providers\WebsiteServiceProvider::class,
        'menu'        => \App\Modules\Menu\Providers\MenuServiceProvider::class,
        'tableplan'   => \App\Modules\TablePlan\Providers\TableplanServiceProvider::class,
        'reservation' => \App\Modules\Reservation\Providers\ReservationServiceProvider::class,
        'qrcode'      => \App\Modules\QrCode\Providers\QrcodeServiceProvider::class,
        'reviews'     => \App\Modules\Reviews\Providers\ReviewsServiceProvider::class,
        'analytics'   => \App\Modules\Analytics\Providers\AnalyticsServiceProvider::class,
        'spotify'     => \App\Modules\Spotify\Providers\SpotifyServiceProvider::class,
        'vehicle'     => \App\Modules\Vehicle\Providers\VehicleServiceProvider::class,
    ];

    public function register(): void
    {
        foreach (config('modules', []) as $module => $enabled) {
            if ($enabled && isset(self::MODULE_PROVIDERS[$module])) {
                $this->app->register(self::MODULE_PROVIDERS[$module]);
            }
        }
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
