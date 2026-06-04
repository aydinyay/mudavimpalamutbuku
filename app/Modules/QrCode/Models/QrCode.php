<?php

namespace App\Modules\QrCode\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCode extends Model
{
    protected $table = 'qr_codes';

    protected $fillable = [
        'qr_uuid', 'target_type', 'target_id',
        'url', 'image_path', 'svg_content', 'last_generated_at',
        'scan_count', 'is_active',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $qr) {
            if (empty($qr->qr_uuid)) {
                $qr->qr_uuid = (string) Str::uuid();
            }
        });
    }
}
