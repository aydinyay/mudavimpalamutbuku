<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InstagramPost;
use App\Modules\Reviews\Services\GoogleReviewService;
use App\Modules\Spotify\Services\SpotifyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AmbianceController extends Controller
{
    public function index(SpotifyService $spotify, GoogleReviewService $reviews)
    {
        $data    = Cache::remember('ambiance_data', 1800, fn() => $this->fetchData());
        $spotify = $spotify->getStatus();

        $photos = InstagramPost::where('is_visible', true)
            ->whereIn('media_type', ['IMAGE', 'CAROUSEL_ALBUM'])
            ->orderByDesc('posted_at')
            ->limit(6)
            ->get();

        $reviewSummary = $reviews->getSummary();
        $reviewList    = $reviews->getVisibleReviews(5);

        return view('website.ambiance', compact('data', 'spotify', 'photos', 'reviewSummary', 'reviewList'));
    }

    private function fetchData(): array
    {
        $lat = config('restaurant.coordinates.lat', 36.6752);
        $lng = config('restaurant.coordinates.lng', 27.5096);

        $weather  = null;
        $forecast = [];
        $sunrise  = null;
        $sunset   = null;

        try {
            $resp = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude'      => $lat,
                'longitude'     => $lng,
                'current'       => 'temperature_2m,apparent_temperature,weather_code,wind_speed_10m,wind_direction_10m,relative_humidity_2m',
                'daily'         => 'weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset,precipitation_probability_max,wind_speed_10m_max,uv_index_max',
                'timezone'      => 'Europe/Istanbul',
                'forecast_days' => 8,
            ]);

            if ($resp->successful()) {
                $json    = $resp->json();
                $cur     = $json['current'] ?? [];
                $daily   = $json['daily']   ?? [];
                $code    = (int)($cur['weather_code'] ?? 0);

                $weather = [
                    'temp'      => (int)round($cur['temperature_2m'] ?? 0),
                    'feels'     => (int)round($cur['apparent_temperature'] ?? 0),
                    'wind'      => round($cur['wind_speed_10m'] ?? 0, 1),
                    'wind_dir'  => (int)($cur['wind_direction_10m'] ?? 0),
                    'humidity'  => (int)($cur['relative_humidity_2m'] ?? 0),
                    'code'      => $code,
                    'label'     => $this->wmoLabel($code),
                    'emoji'     => $this->wmoEmoji($code),
                ];

                // Sunrise/sunset as "HH:MM" strings (ISO: "2026-06-06T05:32")
                $sunrise = substr($daily['sunrise'][0] ?? '', 11, 5);
                $sunset  = substr($daily['sunset'][0]  ?? '', 11, 5);

                for ($i = 1; $i < min(8, count($daily['time'] ?? [])); $i++) {
                    $c = (int)($daily['weather_code'][$i] ?? 0);
                    $forecast[] = [
                        'date'  => $daily['time'][$i],
                        'code'  => $c,
                        'emoji' => $this->wmoEmoji($c),
                        'label' => $this->wmoLabel($c),
                        'max'   => (int)round($daily['temperature_2m_max'][$i] ?? 0),
                        'min'   => (int)round($daily['temperature_2m_min'][$i] ?? 0),
                        'rain'  => (int)($daily['precipitation_probability_max'][$i] ?? 0),
                    ];
                }
            }
        } catch (\Throwable) {}

        // Sea temperature + wave height from Marine API
        $sea = null;
        try {
            $seaResp = Http::timeout(5)->get('https://marine-api.open-meteo.com/v1/marine', [
                'latitude'      => $lat,
                'longitude'     => $lng,
                'hourly'        => 'sea_surface_temperature,wave_height',
                'timezone'      => 'Europe/Istanbul',
                'forecast_days' => 1,
            ]);
            if ($seaResp->successful()) {
                $h   = $seaResp->json('hourly');
                $idx = now()->hour;
                $waveH = round($h['wave_height'][$idx] ?? 0, 1);
                $sea = [
                    'temp'       => (int)round($h['sea_surface_temperature'][$idx] ?? 0),
                    'wave'       => $waveH,
                    'wave_label' => $this->waveLabel($waveH),
                ];
            }
        } catch (\Throwable) {}

        $moon = $this->moonPhase();

        return compact('weather', 'forecast', 'sunrise', 'sunset', 'sea', 'moon');
    }

    private function waveLabel(float $h): string
    {
        return match(true) {
            $h < 0.3  => 'Cam gibi sakin',
            $h < 0.5  => 'Sakin',
            $h < 1.0  => 'Hafif dalgalı',
            $h < 2.0  => 'Dalgalı',
            $h < 3.0  => 'Oldukça dalgalı',
            default   => 'Fırtınalı',
        };
    }

    private function wmoLabel(int $code): string
    {
        return match(true) {
            $code === 0                          => 'Açık ve güneşli',
            $code === 1                          => 'Çoğunlukla açık',
            $code === 2                          => 'Parçalı bulutlu',
            $code === 3                          => 'Kapalı',
            in_array($code, [45, 48])            => 'Sisli',
            in_array($code, [51, 53, 55])        => 'Hafif çisenti',
            in_array($code, [61, 63])            => 'Yağmurlu',
            $code === 65                         => 'Şiddetli yağmur',
            in_array($code, [71, 73, 75, 77])    => 'Karlı',
            in_array($code, [80, 81, 82])        => 'Sağanak',
            in_array($code, [85, 86])            => 'Kar fırtınası',
            $code === 95                         => 'Gök gürültülü',
            in_array($code, [96, 99])            => 'Dolu',
            default                              => 'Değişken',
        };
    }

    private function wmoEmoji(int $code): string
    {
        return match(true) {
            $code === 0                                        => '☀️',
            $code === 1                                        => '🌤',
            $code === 2                                        => '⛅',
            $code === 3                                        => '☁️',
            in_array($code, [45, 48])                         => '🌫',
            in_array($code, [51, 53, 55, 61, 63, 80, 81, 82]) => '🌧',
            $code === 65                                       => '🌧',
            in_array($code, [71, 73, 75, 77, 85, 86])         => '❄️',
            in_array($code, [95, 96, 99])                     => '⛈',
            default                                           => '🌡',
        };
    }

    private function moonPhase(): array
    {
        $now     = now()->timestamp;
        $ref     = mktime(0, 0, 0, 1, 6, 2000);
        $elapsed = ($now - $ref) / 86400;
        $age     = fmod($elapsed, 29.53059);
        if ($age < 0) $age += 29.53059;

        $fraction = $age / 29.53059;

        $name = match(true) {
            $age < 1.85  => 'Yeni Ay',
            $age < 7.38  => 'Hilal',
            $age < 9.22  => 'İlk Dördün',
            $age < 14.77 => 'Şişen Ay',
            $age < 16.61 => 'Dolunay',
            $age < 22.15 => 'Azalan Ay',
            $age < 23.99 => 'Son Dördün',
            $age < 29.53 => 'Batan Hilal',
            default      => 'Yeni Ay',
        };

        return [
            'name'     => $name,
            'fraction' => round($fraction, 4),
            'age'      => round($age, 2),
        ];
    }
}
