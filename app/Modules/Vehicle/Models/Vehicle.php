<?php

namespace App\Modules\Vehicle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = ['plaka', 'marka', 'model', 'model_yili', 'sase_no', 'motor_no', 'renk', 'yakit_cinsi', 'sahip_party_id', 'guncel_km', 'sonraki_bakim_km', 'sonraki_bakim_tarihi', 'sonraki_muayene_tarihi', 'sigorta_bitis_tarihi', 'onceki_sahipler_notu', 'donanimlar', 'erisim_sifresi_hash', 'credential_version', 'vitrin_acik', 'notlar'];

    protected $hidden = ['erisim_sifresi_hash'];

    protected $casts = ['donanimlar' => 'array', 'vitrin_acik' => 'boolean', 'sonraki_bakim_tarihi' => 'date', 'sonraki_muayene_tarihi' => 'date', 'sigorta_bitis_tarihi' => 'date'];

    protected static function booted(): void
    {
        static::saving(function (self $v) {
            $v->plate_key = self::normalizePlate($v->plaka);
        });
    }

    public static function normalizePlate(string $plate): string
    {
        return Str::upper(preg_replace('/[^\pL\pN]/u', '', $plate) ?? '');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(VehicleParty::class, 'sahip_party_id')->withTrashed();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(VehicleExpense::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(VehicleIssue::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(VehicleMedia::class);
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(VehicleChangeLog::class);
    }

    public function balances()
    {
        return $this->expenses()->where('mahsup_durumu', 'alinacak')->selectRaw('odeyen_party_id, borclu_party_id, SUM(tutar) toplam')->groupBy('odeyen_party_id', 'borclu_party_id')->with(['payer', 'debtor'])->get();
    }
}
