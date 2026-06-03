<?php

namespace App\Modules\TablePlan\Models;

use App\Modules\QrCode\Models\QrCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'area_id', 'table_number', 'table_code',
        'seats_min', 'seats_max', 'shape',
        'pos_x', 'pos_y', 'width_px', 'height_px',
        'is_active', 'is_reservable', 'notes',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_reservable' => 'boolean',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class, 'target_id')
            ->where('target_type', 'table');
    }

    public function displayLabel(): string
    {
        return $this->table_number;
    }
}
