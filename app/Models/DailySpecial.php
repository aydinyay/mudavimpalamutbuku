<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DailySpecial extends Model
{
    protected $fillable = [
        'date',
        'title_tr',
        'title_en',
        'title_de',
        'description_tr',
        'description_en',
        'description_de',
        'price',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
    ];

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function title(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $field  = 'title_' . $locale;
        return $this->$field ?? $this->title_tr ?? '';
    }

    public function description(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $field  = 'description_' . $locale;
        return $this->$field ?? $this->description_tr ?? '';
    }

    // ─── Static ───────────────────────────────────────────────

    public static function today(): ?self
    {
        return static::whereDate('date', today())
            ->where('is_active', true)
            ->first();
    }
}
