<?php

namespace App\Modules\TablePlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lounger extends Model
{
    protected $fillable = [
        'area_id', 'lounger_number', 'lounger_code',
        'type', 'pos_x', 'pos_y',
        'daily_rate', 'includes_umbrella',
        'is_active', 'is_reservable',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'is_reservable'    => 'boolean',
        'includes_umbrella'=> 'boolean',
        'daily_rate'       => 'decimal:2',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
