<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewSyncLog extends Model
{
    protected $table = 'review_sync_log';

    protected $fillable = [
        'source', 'fetched', 'new', 'status', 'error', 'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $timestamps = false;
}
