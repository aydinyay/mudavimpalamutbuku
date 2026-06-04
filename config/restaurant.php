<?php

return [
    'name' => [
        'tr' => 'Müdavim Restaurant',
        'en' => 'Müdavim Restaurant',
        'de' => 'Müdavim Restaurant',
    ],
    'tagline' => [
        'tr' => 'Palamutbükü\'nde Denize Sıfır Akdeniz Mutfağı',
        'en' => 'Mediterranean Cuisine at the Water\'s Edge in Palamutbükü',
        'de' => 'Mediterrane Küche direkt am Meer in Palamutbükü',
    ],
    'phone'     => '0554 442 77 48',
    'whatsapp'  => '+905544427748',
    'email'     => '',
    'address'   => [
        'tr' => 'Cumalı Mahallesi, Palamutbükü Sokak No:50, 48900 Datça / Muğla',
        'en' => 'Cumalı Mahallesi, Palamutbükü Sokak No:50, 48900 Datça / Muğla',
        'de' => 'Cumalı Mahallesi, Palamutbükü Sokak No:50, 48900 Datça / Muğla',
    ],
    'coordinates' => [
        'lat' => 36.6752,
        'lng' => 27.5096,
    ],
    'social' => [
        'instagram' => 'https://instagram.com/mudavimpalamutbuku',
        'facebook'  => 'https://facebook.com/mudavimsefpalamutbuku',
    ],
    'hours' => [
        'open'  => '09:00',
        'close' => '02:00',
    ],
    'season' => [
        'open_date'  => env('RESTAURANT_SEASON_OPEN', '05-01'),   // MM-DD format
        'close_date' => env('RESTAURANT_SEASON_CLOSE', '10-31'),
    ],
    'is_open_override' => env('RESTAURANT_OPEN_OVERRIDE', null), // true/false/null
    'max_advance_days' => 30,
    'supported_locales' => ['tr', 'en', 'de'],
    'default_locale'    => 'tr',
];
