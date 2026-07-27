<?php

namespace App\Modules\Vehicle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleIssue extends Model
{
    use SoftDeletes;

    protected $fillable = ['vehicle_id', 'baslik', 'aciklama', 'kategori', 'durum', 'oncelik', 'bildirilme_tarihi', 'cozulme_tarihi', 'yapan_firma', 'km', 'notlar'];

    protected $casts = ['bildirilme_tarihi' => 'date', 'cozulme_tarihi' => 'date'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(VehicleExpense::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(VehicleMedia::class);
    }
}
