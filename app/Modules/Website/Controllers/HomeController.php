<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GoogleReview;
use App\Models\InstagramPost;
use App\Modules\Core\Models\RestaurantSetting;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Reviews\Services\GoogleReviewService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index(GoogleReviewService $reviewService)
    {
        $featured = MenuItem::where('is_featured', true)
            ->where('is_available', true)
            ->with(['translations', 'category'])
            ->take(6)
            ->get();

        $reviewSummary = $reviewService->getSummary();
        $reviews       = $reviewService->getVisibleReviews(6);

        $setting = RestaurantSetting::current();
        $weather = $this->getWeather();
        $sea     = Cache::get('ambiance_data')['sea'] ?? null;
        return view('website.home', compact('featured', 'reviewSummary', 'reviews', 'setting', 'weather', 'sea'));
    }

    public function about()
    {
        $setting = RestaurantSetting::current();
        return view('website.about', compact('setting'));
    }

    private function getWeather(): ?array
    {
        return Cache::remember('weather_palamutbuku', 1800, function () {
            try {
                $lat = config('restaurant.coordinates.lat', 36.6752);
                $lng = config('restaurant.coordinates.lng', 27.5096);

                $resp = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'      => $lat,
                    'longitude'     => $lng,
                    'current'       => 'temperature_2m,weather_code,wind_speed_10m,wind_direction_10m',
                    'timezone'      => 'Europe/Istanbul',
                    'forecast_days' => 1,
                ]);

                if (! $resp->successful()) return null;

                $current  = $resp->json('current');
                $code     = (int) ($current['weather_code'] ?? 0);
                $temp     = (int) round($current['temperature_2m'] ?? 0);
                $wind     = round($current['wind_speed_10m'] ?? 0, 1);
                $windDir  = (int) ($current['wind_direction_10m'] ?? 0);

                return [
                    'code'     => $code,
                    'temp'     => $temp,
                    'wind'     => $wind,
                    'wind_dir' => $windDir,
                    'label'    => $this->wmoLabel($code),
                    'emoji'    => $this->wmoEmoji($code),
                    'moon'     => $this->moonPhase(),
                ];
            } catch (\Throwable) {
                return null;
            }
        });
    }

    private function wmoLabel(int $code): string
    {
        return match(true) {
            $code === 0             => 'Açık ve güneşli',
            $code === 1             => 'Çoğunlukla açık',
            $code === 2             => 'Parçalı bulutlu',
            $code === 3             => 'Kapalı',
            in_array($code,[45,48]) => 'Sisli',
            in_array($code,[51,53,55]) => 'Hafif çisenti',
            in_array($code,[61,63]) => 'Yağmurlu',
            $code === 65            => 'Şiddetli yağmur',
            in_array($code,[71,73,75,77]) => 'Karlı',
            in_array($code,[80,81,82]) => 'Sağanak yağışlı',
            in_array($code,[85,86]) => 'Kar fırtınası',
            $code === 95            => 'Gök gürültülü',
            in_array($code,[96,99]) => 'Dolu ile fırtına',
            default                 => 'Değişken',
        };
    }

    private function wmoEmoji(int $code): string
    {
        return match(true) {
            $code === 0             => '☀️',
            $code === 1             => '🌤',
            $code === 2             => '⛅',
            $code === 3             => '☁️',
            in_array($code,[45,48]) => '🌫',
            in_array($code,[51,53,55,61,63,64,80,81,82]) => '🌧',
            $code === 65            => '🌧',
            in_array($code,[71,73,75,77,85,86]) => '❄️',
            in_array($code,[95,96,99]) => '⛈',
            default                 => '🌡',
        };
    }

    private function moonPhase(): string
    {
        // Synodic period: 29.53059 days. Reference new moon: 2000-01-06
        $now      = now()->timestamp;
        $ref      = mktime(0, 0, 0, 1, 6, 2000);
        $elapsed  = ($now - $ref) / 86400;
        $age      = fmod($elapsed, 29.53059);
        if ($age < 0) $age += 29.53059;

        return match(true) {
            $age < 1.85  => '🌑 Yeni Ay',
            $age < 7.38  => '🌒 Hilal',
            $age < 9.22  => '🌓 İlk Dördün',
            $age < 14.77 => '🌔 Şişen Ay',
            $age < 16.61 => '🌕 Dolunay',
            $age < 22.15 => '🌖 Azalan Ay',
            $age < 23.99 => '🌗 Son Dördün',
            $age < 29.53 => '🌘 Batan Hilal',
            default      => '🌑 Yeni Ay',
        };
    }

    public function contact()
    {
        $sea = Cache::get('ambiance_data')['sea'] ?? null;
        return view('website.contact', compact('sea'));
    }

    public function gallery()
    {
        $posts = InstagramPost::where('is_visible', true)
            ->whereIn('media_type', ['IMAGE', 'CAROUSEL_ALBUM'])
            ->orderByDesc('posted_at')
            ->limit(12)
            ->get();

        return view('website.gallery', compact('posts'));
    }
}
