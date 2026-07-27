<?php

namespace App\Modules\Vehicle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleExpense extends Model
{
    use SoftDeletes;

    protected $fillable = ['vehicle_id', 'vehicle_issue_id', 'tarih', 'aciklama', 'tutar', 'kategori', 'odeyen_party_id', 'borclu_party_id', 'mahsup_durumu', 'settled_at', 'km', 'parca_markasi', 'uretim_yili', 'nereden_alindi', 'garanti_bitis_tarihi', 'vitrinde_goster', 'public_summary', 'notlar'];

    protected $casts = ['tarih' => 'date', 'settled_at' => 'datetime', 'garanti_bitis_tarihi' => 'date', 'vitrinde_goster' => 'boolean', 'tutar' => 'decimal:2'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(VehicleIssue::class, 'vehicle_issue_id')->withTrashed();
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(VehicleParty::class, 'odeyen_party_id')->withTrashed();
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(VehicleParty::class, 'borclu_party_id')->withTrashed();
    }

    public function media(): HasMany
    {
        return $this->hasMany(VehicleMedia::class);
    }
}
