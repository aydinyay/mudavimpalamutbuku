<?php

namespace App\Modules\Vehicle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleParty extends Model
{
    use SoftDeletes;

    protected $fillable = ['ad', 'telefon', 'notlar'];

    public function paidExpenses(): HasMany
    {
        return $this->hasMany(VehicleExpense::class, 'odeyen_party_id');
    }

    public function owedExpenses(): HasMany
    {
        return $this->hasMany(VehicleExpense::class, 'borclu_party_id');
    }
}
