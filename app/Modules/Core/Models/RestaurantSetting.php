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
        'max_advance_days',
        'sms_provider', 'sms_api_key', 'sms_sender_name',
    ];

    protected $casts = [
        'is_open_override' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
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

    public function name(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_tr;
    }
}
