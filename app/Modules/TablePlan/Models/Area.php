<?php

namespace App\Modules\TablePlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = [
        'slug', 'name_tr', 'name_en', 'name_de',
        'type', 'sort_order', 'is_active',
        'plan_width_px', 'plan_height_px', 'background_image',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function loungers(): HasMany
    {
        return $this->hasMany(Lounger::class);
    }

    public function name(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_tr;
    }
}
