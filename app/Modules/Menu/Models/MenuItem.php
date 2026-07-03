<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'image_path',
        'price', 'price_eur',
        'is_available', 'is_seasonal', 'is_featured',
        'is_vegetarian', 'is_vegan', 'is_gluten_free',
        'preparation_time_minutes', 'sort_order',
        'ingredients_tr', 'ingredients_en', 'ingredients_de',
        'meat_origin',
        'serving_size_g', 'calories', 'protein_g', 'fat_g', 'carbs_g',
    ];

    protected $casts = [
        'is_available'   => 'boolean',
        'is_seasonal'    => 'boolean',
        'is_featured'    => 'boolean',
        'is_vegetarian'  => 'boolean',
        'is_vegan'       => 'boolean',
        'is_gluten_free' => 'boolean',
        'price'          => 'decimal:2',
        'price_eur'      => 'decimal:2',
        'calories'       => 'integer',
        'serving_size_g' => 'integer',
        'protein_g'      => 'decimal:1',
        'fat_g'          => 'decimal:1',
        'carbs_g'        => 'decimal:1',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MenuItemTranslation::class);
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'menu_item_allergens');
    }

    public function translation(?string $locale = null): ?MenuItemTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'tr');
    }

    public function name(?string $locale = null): string
    {
        return $this->translation($locale)?->name ?? '';
    }

    public function description(?string $locale = null): string
    {
        return $this->translation($locale)?->description ?? '';
    }

    public function allergenCodes(): array
    {
        return $this->allergens->pluck('code')->toArray();
    }
}
