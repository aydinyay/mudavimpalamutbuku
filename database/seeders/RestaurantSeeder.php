<?php

namespace Database\Seeders;

use App\Modules\Core\Models\RestaurantSetting;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        RestaurantSetting::updateOrCreate(['id' => 1], [
            'name_tr'      => 'Müdavim Şef Restaurant',
            'name_en'      => 'Müdavim Şef Restaurant',
            'name_de'      => 'Müdavim Şef Restaurant',
            'tagline_tr'   => 'Palamutbükü\'nde Denize Sıfır Akdeniz Mutfağı',
            'tagline_en'   => 'Mediterranean Cuisine at the Water\'s Edge',
            'tagline_de'   => 'Mediterrane Küche direkt am Meer',
            'phone'        => '0505 185 10 20',
            'whatsapp'     => '+905051851020',
            'address_tr'   => 'Cumalı Mahallesi, Palamutbükü Sokak No:50, 48900 Datça / Muğla',
            'address_en'   => 'Cumalı Mah., Palamutbükü Sokak No:50, 48900 Datça / Muğla, Turkey',
            'address_de'   => 'Cumalı Mah., Palamutbükü Sokak No:50, 48900 Datça / Muğla, Türkei',
            'lat'          => 36.6752,
            'lng'          => 27.5096,
            'instagram_url'=> 'https://instagram.com/mudavimpalamutbuku',
            'facebook_url' => 'https://facebook.com/mudavimsefpalamutbuku',
            'open_time'    => '09:00:00',
            'close_time'   => '02:00:00',
            'season_open_date'  => '05-01',
            'season_close_date' => '10-31',
        ]);
    }
}
