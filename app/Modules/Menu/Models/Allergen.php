<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergen extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name_tr', 'name_en', 'name_de', 'icon_path', 'sort_order'];

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_allergens');
    }

    public function name(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_tr;
    }
}
