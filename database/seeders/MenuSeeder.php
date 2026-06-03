<?php

namespace Database\Seeders;

use App\Modules\Menu\Models\Allergen;
use App\Modules\Menu\Models\MenuCategory;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Models\MenuItemTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $fish        = Allergen::where('code', 'fish')->first();
        $crustaceans = Allergen::where('code', 'crustaceans')->first();
        $molluscs    = Allergen::where('code', 'molluscs')->first();
        $dairy       = Allergen::where('code', 'dairy')->first();
        $eggs        = Allergen::where('code', 'eggs')->first();
        $gluten      = Allergen::where('code', 'gluten')->first();

        // Categories
        $mezeler = MenuCategory::updateOrCreate(['slug' => 'mezeler'], [
            'name_tr' => 'Mezeler', 'name_en' => 'Appetizers', 'name_de' => 'Vorspeisen',
            'icon_emoji' => '🥗', 'sort_order' => 1,
        ]);

        $denizMahsulleri = MenuCategory::updateOrCreate(['slug' => 'deniz-mahsulleri'], [
            'name_tr' => 'Deniz Mahsulleri', 'name_en' => 'Seafood', 'name_de' => 'Meeresfrüchte',
            'icon_emoji' => '🐟', 'sort_order' => 2,
        ]);

        $salatalar = MenuCategory::updateOrCreate(['slug' => 'salatalar'], [
            'name_tr' => 'Salatalar', 'name_en' => 'Salads', 'name_de' => 'Salate',
            'icon_emoji' => '🥙', 'sort_order' => 3,
        ]);

        $icecekler = MenuCategory::updateOrCreate(['slug' => 'icecekler'], [
            'name_tr' => 'İçecekler', 'name_en' => 'Drinks', 'name_de' => 'Getränke',
            'icon_emoji' => '🥤', 'sort_order' => 4,
        ]);

        // Signature items
        $items = [
            [
                'category' => $denizMahsulleri,
                'data'     => [
                    'price' => 680, 'is_featured' => true, 'is_seasonal' => true,
                    'sort_order' => 1,
                ],
                'translations' => [
                    'tr' => ['name' => 'Simit Levrek', 'description' => 'Taze levreğin en özel hali. Simit kaplamasıyla çıtır çıtır, içi puf puf. Müdavim\'in imza lezzeti.'],
                    'en' => ['name' => 'Simit Crusted Sea Bass', 'description' => 'Our signature dish. Fresh sea bass with a crispy simit crust — the dish that keeps guests coming back.'],
                    'de' => ['name' => 'Seebarsch im Simit-Mantel', 'description' => 'Unser Signature-Gericht. Frischer Seebarsch mit knuspriger Simit-Kruste.'],
                ],
                'allergens' => [$fish, $gluten, $eggs],
            ],
            [
                'category' => $denizMahsulleri,
                'data' => ['price' => 420, 'is_featured' => true, 'sort_order' => 2],
                'translations' => [
                    'tr' => ['name' => 'Izgara Kalamar', 'description' => 'Taze kalamar, mangalda pişirilmiş, limon ve zeytinyağıyla.'],
                    'en' => ['name' => 'Grilled Calamari', 'description' => 'Fresh squid, charcoal grilled, with lemon and olive oil.'],
                    'de' => ['name' => 'Gegrillter Tintenfisch', 'description' => 'Frischer Tintenfisch, auf dem Holzkohlegrill, mit Zitrone und Olivenöl.'],
                ],
                'allergens' => [$molluscs],
            ],
            [
                'category' => $denizMahsulleri,
                'data' => ['price' => 380, 'is_seasonal' => true, 'sort_order' => 3],
                'translations' => [
                    'tr' => ['name' => 'Barbun', 'description' => 'Taze barbun balığı, tavada veya ızgarada. Günlük avdan.'],
                    'en' => ['name' => 'Red Mullet', 'description' => 'Fresh red mullet, pan-fried or grilled. Caught daily.'],
                    'de' => ['name' => 'Rotbarbe', 'description' => 'Frische Rotbarbe, in der Pfanne oder gegrillt. Täglich gefangen.'],
                ],
                'allergens' => [$fish],
            ],
            [
                'category' => $denizMahsulleri,
                'data' => ['price' => 520, 'sort_order' => 4],
                'translations' => [
                    'tr' => ['name' => 'Izgara Ahtapot', 'description' => 'Mermer gibi yumuşak, nar ekşisiyle marine edilmiş ahtapot.'],
                    'en' => ['name' => 'Grilled Octopus', 'description' => 'Tender octopus marinated with pomegranate molasses, perfectly grilled.'],
                    'de' => ['name' => 'Gegrillter Oktopus', 'description' => 'Zarter Oktopus, mariniert mit Granatapfelmelasse, perfekt gegrillt.'],
                ],
                'allergens' => [$molluscs],
            ],
            [
                'category' => $denizMahsulleri,
                'data' => ['price' => 360, 'sort_order' => 5],
                'translations' => [
                    'tr' => ['name' => 'Baby Kalamar', 'description' => 'Küçük ve narin, hafif çıtır bir kaplama ile.'],
                    'en' => ['name' => 'Baby Calamari', 'description' => 'Small and delicate, with a light crispy coating.'],
                    'de' => ['name' => 'Baby-Kalmar', 'description' => 'Klein und zart, mit einer leichten knusprigen Panade.'],
                ],
                'allergens' => [$molluscs, $gluten, $eggs],
            ],
            [
                'category' => $mezeler,
                'data' => ['price' => 180, 'is_vegetarian' => true, 'sort_order' => 1],
                'translations' => [
                    'tr' => ['name' => 'Sıcak Ot Tabağı', 'description' => 'Mevsim otları, zeytinyağı ve sarımsakla. Egeli usul.'],
                    'en' => ['name' => 'Warm Wild Herbs', 'description' => 'Seasonal wild herbs, olive oil and garlic. Aegean style.'],
                    'de' => ['name' => 'Warme Wildkräuter', 'description' => 'Saisonale Wildkräuter mit Olivenöl und Knoblauch. Ägäische Art.'],
                ],
                'allergens' => [],
            ],
            [
                'category' => $mezeler,
                'data' => ['price' => 220, 'is_vegetarian' => true, 'sort_order' => 2],
                'translations' => [
                    'tr' => ['name' => 'Kabak Çiçeği Dolması', 'description' => 'Taze kabak çiçeği, pirinç ve otlarla doldurulmuş, zeytinyağlı.'],
                    'en' => ['name' => 'Stuffed Zucchini Flowers', 'description' => 'Fresh zucchini blossoms stuffed with rice and herbs, drizzled with olive oil.'],
                    'de' => ['name' => 'Gefüllte Zucchiniblüten', 'description' => 'Frische Zucchiniblüten, gefüllt mit Reis und Kräutern, mit Olivenöl beträufelt.'],
                ],
                'allergens' => [],
            ],
            [
                'category' => $mezeler,
                'data' => ['price' => 160, 'is_vegetarian' => true, 'sort_order' => 3],
                'translations' => [
                    'tr' => ['name' => 'Deniz Börülcesi', 'description' => 'Zeytinyağlı, sarımsaklı, limonlu. Denizin tadını taşıyor.'],
                    'en' => ['name' => 'Sea Purslane', 'description' => 'Cooked with olive oil, garlic and lemon. Tastes like the sea.'],
                    'de' => ['name' => 'Meerfenchel', 'description' => 'Mit Olivenöl, Knoblauch und Zitrone. Schmeckt nach Meer.'],
                ],
                'allergens' => [],
            ],
        ];

        foreach ($items as $spec) {
            $item = MenuItem::create(array_merge($spec['data'], [
                'category_id' => $spec['category']->id,
            ]));

            foreach ($spec['translations'] as $locale => $trans) {
                MenuItemTranslation::create(array_merge(['menu_item_id' => $item->id, 'locale' => $locale], $trans));
            }

            if (!empty($spec['allergens'])) {
                $item->allergens()->attach(collect($spec['allergens'])->filter()->pluck('id'));
            }
        }
    }
}
