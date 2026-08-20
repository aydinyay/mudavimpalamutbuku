<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $table = 'restaurant_settings';

    protected $fillable = [
        'name_tr', 'name_en', 'name_de',
        'tagline_tr', 'tagline_en', 'tagline_de',
        'phone', 'whatsapp', 'email',
        'address_tr', 'address_en', 'address_de',
        'lat', 'lng',
        'instagram_url', 'facebook_url',
        'open_time', 'close_time',
        'season_open_date', 'season_close_date',
        'is_open_override',
        'reservation_online_enabled',
        'max_advance_days',
        'sms_provider', 'sms_api_key', 'sms_sender_name',
        'spotify_access_token', 'spotify_refresh_token', 'spotify_token_expires_at',
        'spotify_enabled', 'spotify_widget_on_website', 'spotify_widget_on_menu',
        'spotify_fallback_playlist_url', 'spotify_fallback_playlist_name',
        'ambiance_page_active',
        'hero_bg_image', 'about_image',
    ];

    protected $casts = [
        'is_open_override'          => 'boolean',
        'reservation_online_enabled' => 'boolean',
        'lat'                       => 'decimal:7',
        'lng'                       => 'decimal:7',
        'spotify_enabled'           => 'boolean',
        'spotify_widget_on_website' => 'boolean',
        'spotify_widget_on_menu'    => 'boolean',
        'ambiance_page_active'      => 'boolean',
        'spotify_token_expires_at'  => 'datetime',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'name_tr' => config('restaurant.name.tr'),
            'name_en' => config('restaurant.name.en'),
            'name_de' => config('restaurant.name.de'),
            'phone'   => config('restaurant.phone'),
            'whatsapp'=> config('restaurant.whatsapp'),
            'lat'     => config('restaurant.coordinates.lat'),
            'lng'     => config('restaurant.coordinates.lng'),
        ]);
    }

    public function name(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_tr;
    }
}
