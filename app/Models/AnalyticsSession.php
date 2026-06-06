<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id', 'ip', 'country_code', 'country', 'city',
        'device_type', 'browser', 'os',
        'referrer_source', 'referrer_url', 'landing_page',
        'page_count', 'first_seen_at', 'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
    ];
}
