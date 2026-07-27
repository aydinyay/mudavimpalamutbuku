<?php

namespace App\Modules\Vehicle\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class VehicleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (! config('modules.vehicle')) {
            return;
        } Route::middleware('web')->group(fn () => $this->loadRoutesFrom(__DIR__.'/../routes.php'));
    }
}
