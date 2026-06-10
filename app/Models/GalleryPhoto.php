<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GalleryPhoto extends Model
{
    protected $table = 'gallery_photos';

    protected $fillable = [
        'filename',
        'caption',
        'alt',
        'alt_tr',
        'alt_en',
        'alt_de',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Scope: only active photos
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Return the most appropriate alt text for the given locale.
     */
    public function altText(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"alt_{$locale}"} ?? $this->alt_tr ?? $this->alt ?? 'Müdavim Restaurant';
    }

    /**
     * Full public URL of the photo.
     */
    public function getUrlAttribute(): string
    {
        return asset('gallery/' . $this->filename);
    }
}
