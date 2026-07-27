<?php

namespace App\Modules\Vehicle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMedia extends Model
{
    protected $table = 'vehicle_media';

    protected $fillable = ['vehicle_id', 'vehicle_expense_id', 'vehicle_issue_id', 'tur', 'role', 'dosya_yolu', 'mime_type', 'caption'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(VehicleExpense::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(VehicleIssue::class);
    }
}
