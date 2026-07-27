<?php

namespace App\Modules\Vehicle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleChangeLog extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = ['vehicle_id', 'vehicle_expense_id', 'alan_adi', 'eski_deger', 'yeni_deger', 'aciklama'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(VehicleExpense::class);
    }
}
