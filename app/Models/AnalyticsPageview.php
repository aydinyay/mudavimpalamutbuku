<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsPageview extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id', 'page_path', 'page_title', 'duration_seconds', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
