<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    protected $fillable = [
        'slug', 'name_tr', 'name_en', 'name_de',
        'icon_emoji', 'image_path',
        'sort_order', 'is_active', 'available_for',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'available_for'=> 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }

    public function name(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_tr;
    }
}
