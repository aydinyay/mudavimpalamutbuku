<?php

namespace Database\Seeders;

use App\Modules\Menu\Models\Allergen;
use App\Modules\Menu\Models\MenuCategory;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Models\MenuItemTranslation;
use Illuminate\Database\Seeder;

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
        $celery      = Allergen::where('code', 'celery')->first();

        // ── Kategoriler ──────────────────────────────────────────────────────
        $atistirmaliklar = MenuCategory::updateOrCreate(['slug' => 'atistirmaliklar'], [
            'name_tr' => 'Atıştırmalıklar', 'name_en' => 'Snacks', 'name_de' => 'Snacks',
            'icon_emoji' => '🍟', 'sort_order' => 1,
        ]);
        $salatalar = MenuCategory::updateOrCreate(['slug' => 'salatalar'], [
            'name_tr' => 'Salatalar', 'name_en' => 'Salads', 'name_de' => 'Salate',
            'icon_emoji' => '🥗', 'sort_order' => 2,
        ]);
        $mezeler = MenuCategory::updateOrCreate(['slug' => 'mezeler'], [
            'name_tr' => 'Mezeler', 'name_en' => 'Appetizers', 'name_de' => 'Vorspeisen',
            'icon_emoji' => '🫙', 'sort_order' => 3,
        ]);
        $denizMezeler = MenuCategory::updateOrCreate(['slug' => 'deniz-mezeleri'], [
            'name_tr' => 'Deniz Mezeleri', 'name_en' => 'Seafood Starters', 'name_de' => 'Meeresfrüchte-Vorspeisen',
            'icon_emoji' => '🦑', 'sort_order' => 4,
        ]);
        $araSicaklar = MenuCategory::updateOrCreate(['slug' => 'ara-sicaklar'], [
            'name_tr' => 'Ara Sıcaklar', 'name_en' => 'Hot Starters', 'name_de' => 'Warme Vorspeisen',
            'icon_emoji' => '🍤', 'sort_order' => 5,
        ]);
        $etUrunleri = MenuCategory::updateOrCreate(['slug' => 'et-urunleri'], [
            'name_tr' => 'Et Ürünleri', 'name_en' => 'Meat', 'name_de' => 'Fleischgerichte',
            'icon_emoji' => '🥩', 'sort_order' => 6,
        ]);
        $denizUrunleri = MenuCategory::updateOrCreate(['slug' => 'deniz-urunleri'], [
            'name_tr' => 'Deniz Ürünleri', 'name_en' => 'Seafood', 'name_de' => 'Meeresfrüchte',
            'icon_emoji' => '🐟', 'sort_order' => 7,
        ]);
        $tatlilar = MenuCategory::updateOrCreate(['slug' => 'tatlilar'], [
            'name_tr' => 'Tatlılar', 'name_en' => 'Desserts', 'name_de' => 'Desserts',
            'icon_emoji' => '🍮', 'sort_order' => 8,
        ]);
        $icecekler = MenuCategory::updateOrCreate(['slug' => 'icecekler'], [
            'name_tr' => 'İçecekler', 'name_en' => 'Drinks', 'name_de' => 'Getränke',
            'icon_emoji' => '🥤', 'sort_order' => 9,
        ]);
        $sogukIcecekler = MenuCategory::updateOrCreate(['slug' => 'soguk-icecekler'], [
            'name_tr' => 'Soğuk İçecekler', 'name_en' => 'Cold Drinks', 'name_de' => 'Kalte Getränke',
            'icon_emoji' => '🧃', 'sort_order' => 9,
        ]);
        $biralar = MenuCategory::updateOrCreate(['slug' => 'biralar'], [
            'name_tr' => 'Biralar', 'name_en' => 'Beers', 'name_de' => 'Biere',
            'icon_emoji' => '🍺', 'sort_order' => 10,
        ]);
        $raki = MenuCategory::updateOrCreate(['slug' => 'raki'], [
            'name_tr' => 'Rakı', 'name_en' => 'Raki', 'name_de' => 'Raki',
            'icon_emoji' => '🥃', 'sort_order' => 11,
        ]);
        $sarap = MenuCategory::updateOrCreate(['slug' => 'sarap'], [
            'name_tr' => 'Şarap', 'name_en' => 'Wine', 'name_de' => 'Wein',
            'icon_emoji' => '🍷', 'sort_order' => 12,
        ]);
        $import = MenuCategory::updateOrCreate(['slug' => 'import-icecekler'], [
            'name_tr' => 'İthal İçecekler', 'name_en' => 'Import Drinks', 'name_de' => 'Import-Getränke',
            'icon_emoji' => '🍸', 'sort_order' => 13,
        ]);
        $sicakIcecekler = MenuCategory::updateOrCreate(['slug' => 'sicak-icecekler'], [
            'name_tr' => 'Sıcak İçecekler', 'name_en' => 'Hot Drinks', 'name_de' => 'Heiße Getränke',
            'icon_emoji' => '☕', 'sort_order' => 14,
        ]);

        // ── Ürünler ──────────────────────────────────────────────────────────
        $items = [
            // Atıştırmalıklar
            ['category' => $atistirmaliklar, 'data' => ['price' => 300, 'sort_order' => 1],
             'tr' => ['name' => 'Patates Tava', 'description' => ''],
             'en' => ['name' => 'Fried Potatoes', 'description' => ''],
             'de' => ['name' => 'Gebratene Kartoffeln', 'description' => ''],
             'allergens' => []],
            ['category' => $atistirmaliklar, 'data' => ['price' => 600, 'sort_order' => 2],
             'tr' => ['name' => 'Yoğurtlama', 'description' => ''],
             'en' => ['name' => 'Yogurt Dish', 'description' => ''],
             'de' => ['name' => 'Joghurtgericht', 'description' => ''],
             'allergens' => [$dairy]],
            ['category' => $atistirmaliklar, 'data' => ['price' => 400, 'sort_order' => 3],
             'tr' => ['name' => 'Sigara Böreği', 'description' => ''],
             'en' => ['name' => 'Fried Pastry Rolls', 'description' => ''],
             'de' => ['name' => 'Frittierte Teigrollen', 'description' => ''],
             'allergens' => [$gluten, $dairy, $eggs]],

            // Salatalar
            ['category' => $salatalar, 'data' => ['price' => 500, 'sort_order' => 1],
             'tr' => ['name' => 'Roka Salatası', 'description' => ''],
             'en' => ['name' => 'Rocket Salad', 'description' => ''],
             'de' => ['name' => 'Rucola-Salat', 'description' => ''],
             'allergens' => []],
            ['category' => $salatalar, 'data' => ['price' => 500, 'sort_order' => 2],
             'tr' => ['name' => 'Çoban Salata', 'description' => ''],
             'en' => ['name' => 'Shepherd\'s Salad', 'description' => ''],
             'de' => ['name' => 'Hirtensalat', 'description' => ''],
             'allergens' => []],
            ['category' => $salatalar, 'data' => ['price' => 500, 'sort_order' => 3],
             'tr' => ['name' => 'Domates Salatası', 'description' => ''],
             'en' => ['name' => 'Tomato Salad', 'description' => ''],
             'de' => ['name' => 'Tomatensalat', 'description' => ''],
             'allergens' => []],

            // Mezeler
            ['category' => $mezeler, 'data' => ['price' => 400, 'sort_order' => 1],
             'tr' => ['name' => 'Günlük Mezeler', 'description' => 'Günün taze mezeleri.'],
             'en' => ['name' => 'Daily Appetizers', 'description' => 'Fresh daily selection.'],
             'de' => ['name' => 'Tagesauswahl', 'description' => 'Frische Tagesauswahl.'],
             'allergens' => []],

            // Deniz Mezeleri
            ['category' => $denizMezeler, 'data' => ['price' => 600, 'sort_order' => 1],
             'tr' => ['name' => 'Uskumru Çiroz', 'description' => ''],
             'en' => ['name' => 'Dried Mackerel', 'description' => ''],
             'de' => ['name' => 'Getrocknete Makrele', 'description' => ''],
             'allergens' => [$fish]],
            ['category' => $denizMezeler, 'data' => ['price' => 650, 'sort_order' => 2],
             'tr' => ['name' => 'Lakerda', 'description' => ''],
             'en' => ['name' => 'Cured Bonito', 'description' => ''],
             'de' => ['name' => 'Gepökelter Bonito', 'description' => ''],
             'allergens' => [$fish]],
            ['category' => $denizMezeler, 'data' => ['price' => 750, 'sort_order' => 3],
             'tr' => ['name' => 'Karides Salata', 'description' => ''],
             'en' => ['name' => 'Shrimp Salad', 'description' => ''],
             'de' => ['name' => 'Garnelensalat', 'description' => ''],
             'allergens' => [$crustaceans]],
            ['category' => $denizMezeler, 'data' => ['price' => 900, 'sort_order' => 4],
             'tr' => ['name' => 'Ahtapot Salatası', 'description' => ''],
             'en' => ['name' => 'Octopus Salad', 'description' => ''],
             'de' => ['name' => 'Oktopussalat', 'description' => ''],
             'allergens' => [$molluscs]],

            // Ara Sıcaklar
            ['category' => $araSicaklar, 'data' => ['price' => 850, 'sort_order' => 1],
             'tr' => ['name' => 'Kalamar Beyti', 'description' => ''],
             'en' => ['name' => 'Calamari Beyti', 'description' => ''],
             'de' => ['name' => 'Kalmar-Beyti', 'description' => ''],
             'allergens' => [$molluscs, $gluten]],
            ['category' => $araSicaklar, 'data' => ['price' => 850, 'sort_order' => 2],
             'tr' => ['name' => 'Karides Mantı', 'description' => ''],
             'en' => ['name' => 'Shrimp Dumplings', 'description' => ''],
             'de' => ['name' => 'Garnelen-Manti', 'description' => ''],
             'allergens' => [$crustaceans, $gluten, $eggs]],
            ['category' => $araSicaklar, 'data' => ['price' => 600, 'sort_order' => 3],
             'tr' => ['name' => 'Deniz Mahsullü Börek', 'description' => ''],
             'en' => ['name' => 'Seafood Pastry', 'description' => ''],
             'de' => ['name' => 'Meeresfrüchte-Gebäck', 'description' => ''],
             'allergens' => [$gluten, $eggs, $fish]],
            ['category' => $araSicaklar, 'data' => ['price' => 600, 'sort_order' => 4],
             'tr' => ['name' => 'Çıtır Karides', 'description' => ''],
             'en' => ['name' => 'Crispy Shrimp', 'description' => ''],
             'de' => ['name' => 'Knusprige Garnelen', 'description' => ''],
             'allergens' => [$crustaceans, $gluten, $eggs]],
            ['category' => $araSicaklar, 'data' => ['price' => 650, 'sort_order' => 5, 'is_vegetarian' => true],
             'tr' => ['name' => 'Kiremitte Mantar', 'description' => ''],
             'en' => ['name' => 'Mushrooms on Tile', 'description' => ''],
             'de' => ['name' => 'Pilze auf Ziegel', 'description' => ''],
             'allergens' => [$dairy]],
            ['category' => $araSicaklar, 'data' => ['price' => 600, 'sort_order' => 6, 'is_vegetarian' => true],
             'tr' => ['name' => 'Kiremitte Sıcak Ot', 'description' => 'Taze mevsim otları, zeytinyağı ve sarımsakla kiremitte pişirilmiş.'],
             'en' => ['name' => 'Warm Herbs on Tile', 'description' => ''],
             'de' => ['name' => 'Warme Kräuter auf Ziegel', 'description' => ''],
             'allergens' => []],
            ['category' => $araSicaklar, 'data' => ['price' => 900, 'sort_order' => 7],
             'tr' => ['name' => 'Kiremitte Karides', 'description' => ''],
             'en' => ['name' => 'Shrimp on Tile', 'description' => ''],
             'de' => ['name' => 'Garnelen auf Ziegel', 'description' => ''],
             'allergens' => [$crustaceans, $dairy]],
            ['category' => $araSicaklar, 'data' => ['price' => 1150, 'sort_order' => 8],
             'tr' => ['name' => 'Kalamar Tava', 'description' => ''],
             'en' => ['name' => 'Fried Calamari', 'description' => ''],
             'de' => ['name' => 'Frittierter Kalmar', 'description' => ''],
             'allergens' => [$molluscs, $gluten, $eggs]],
            ['category' => $araSicaklar, 'data' => ['price' => 850, 'sort_order' => 9, 'is_featured' => true],
             'tr' => ['name' => 'Levrek Simit', 'description' => 'Taze levreğin en özel hali. Simit kaplamasıyla çıtır çıtır, içi puf puf. Müdavim\'in imza lezzeti.'],
             'en' => ['name' => 'Simit Crusted Sea Bass', 'description' => 'Our signature dish. Fresh sea bass with a crispy simit crust.'],
             'de' => ['name' => 'Seebarsch im Simit-Mantel', 'description' => 'Unser Signature-Gericht. Frischer Seebarsch mit knuspriger Simit-Kruste.'],
             'allergens' => [$fish, $gluten, $eggs]],
            ['category' => $araSicaklar, 'data' => ['price' => 450, 'sort_order' => 10],
             'tr' => ['name' => 'Paçanga Böreği', 'description' => ''],
             'en' => ['name' => 'Paçanga Pastry', 'description' => ''],
             'de' => ['name' => 'Paçanga-Gebäck', 'description' => ''],
             'allergens' => [$gluten, $eggs, $dairy]],
            ['category' => $araSicaklar, 'data' => ['price' => 750, 'sort_order' => 11],
             'tr' => ['name' => 'Arnavut Ciğeri', 'description' => ''],
             'en' => ['name' => 'Albanian Liver', 'description' => ''],
             'de' => ['name' => 'Albanische Leber', 'description' => ''],
             'allergens' => []],

            // Et Ürünleri
            ['category' => $etUrunleri, 'data' => ['price' => 800, 'sort_order' => 1],
             'tr' => ['name' => 'Izgara Köfte', 'description' => ''],
             'en' => ['name' => 'Grilled Meatballs', 'description' => ''],
             'de' => ['name' => 'Gegrillte Fleischbällchen', 'description' => ''],
             'allergens' => [$gluten]],
            ['category' => $etUrunleri, 'data' => ['price' => 900, 'sort_order' => 2],
             'tr' => ['name' => 'Kuzu Şiş', 'description' => ''],
             'en' => ['name' => 'Lamb Kebab', 'description' => ''],
             'de' => ['name' => 'Lammspieß', 'description' => ''],
             'allergens' => []],
            ['category' => $etUrunleri, 'data' => ['price' => 1150, 'sort_order' => 3],
             'tr' => ['name' => 'Kuzu Pirzola', 'description' => ''],
             'en' => ['name' => 'Lamb Chops', 'description' => ''],
             'de' => ['name' => 'Lammkoteletts', 'description' => ''],
             'allergens' => []],
            ['category' => $etUrunleri, 'data' => ['price' => 950, 'sort_order' => 4],
             'tr' => ['name' => 'Antrikot', 'description' => ''],
             'en' => ['name' => 'Entrecote', 'description' => ''],
             'de' => ['name' => 'Entrecôte', 'description' => ''],
             'allergens' => []],
            ['category' => $etUrunleri, 'data' => ['price' => 1200, 'sort_order' => 5],
             'tr' => ['name' => 'Et Bonfile', 'description' => ''],
             'en' => ['name' => 'Beef Tenderloin', 'description' => ''],
             'de' => ['name' => 'Rinderfilet', 'description' => ''],
             'allergens' => []],
            ['category' => $etUrunleri, 'data' => ['price' => 1200, 'sort_order' => 6],
             'tr' => ['name' => 'Lokum Bonfile', 'description' => ''],
             'en' => ['name' => 'Lokum Tenderloin', 'description' => ''],
             'de' => ['name' => 'Lokum-Filet', 'description' => ''],
             'allergens' => []],
            ['category' => $etUrunleri, 'data' => ['price' => 800, 'sort_order' => 7],
             'tr' => ['name' => 'Tavuk Şiş', 'description' => ''],
             'en' => ['name' => 'Chicken Kebab', 'description' => ''],
             'de' => ['name' => 'Hähnchenspieß', 'description' => ''],
             'allergens' => []],

            // Deniz Ürünleri
            ['category' => $denizUrunleri, 'data' => ['price' => 850, 'sort_order' => 1],
             'tr' => ['name' => 'Levrek Izgara', 'description' => ''],
             'en' => ['name' => 'Grilled Sea Bass', 'description' => ''],
             'de' => ['name' => 'Gegrillter Seebarsch', 'description' => ''],
             'allergens' => [$fish]],
            ['category' => $denizUrunleri, 'data' => ['price' => 850, 'sort_order' => 2],
             'tr' => ['name' => 'Çipura Izgara', 'description' => ''],
             'en' => ['name' => 'Grilled Sea Bream', 'description' => ''],
             'de' => ['name' => 'Gegrillte Dorade', 'description' => ''],
             'allergens' => [$fish]],
            ['category' => $denizUrunleri, 'data' => ['price' => 900, 'sort_order' => 3],
             'tr' => ['name' => 'Tekir Tava', 'description' => ''],
             'en' => ['name' => 'Fried Striped Red Mullet', 'description' => ''],
             'de' => ['name' => 'Gebratene Streifenbarbe', 'description' => ''],
             'allergens' => [$fish, $gluten]],
            ['category' => $denizUrunleri, 'data' => ['price' => 0, 'sort_order' => 4],
             'tr' => ['name' => 'Günün Balıkları', 'description' => 'Fiyat için sorunuz.'],
             'en' => ['name' => 'Fish of the Day', 'description' => 'Please ask for price.'],
             'de' => ['name' => 'Fisch des Tages', 'description' => 'Bitte nach dem Preis fragen.'],
             'allergens' => [$fish]],
            ['category' => $denizUrunleri, 'data' => ['price' => 1350, 'sort_order' => 5],
             'tr' => ['name' => 'Izgara Ahtapot', 'description' => 'Mermer gibi yumuşak, nar ekşisiyle marine edilmiş ahtapot.'],
             'en' => ['name' => 'Grilled Octopus', 'description' => 'Tender octopus marinated with pomegranate molasses.'],
             'de' => ['name' => 'Gegrillter Oktopus', 'description' => 'Zarter Oktopus, mariniert mit Granatapfelmelasse.'],
             'allergens' => [$molluscs]],
            ['category' => $denizUrunleri, 'data' => ['price' => 1250, 'sort_order' => 6],
             'tr' => ['name' => 'Güveç Ahtapot', 'description' => ''],
             'en' => ['name' => 'Octopus Casserole', 'description' => ''],
             'de' => ['name' => 'Oktopus-Eintopf', 'description' => ''],
             'allergens' => [$molluscs]],
            ['category' => $denizUrunleri, 'data' => ['price' => 1200, 'sort_order' => 7],
             'tr' => ['name' => 'Izgara Jumbo Karides', 'description' => ''],
             'en' => ['name' => 'Grilled Jumbo Shrimp', 'description' => ''],
             'de' => ['name' => 'Gegrillte Jumbo-Garnelen', 'description' => ''],
             'allergens' => [$crustaceans]],
            ['category' => $denizUrunleri, 'data' => ['price' => 1200, 'sort_order' => 8],
             'tr' => ['name' => 'Deniz Ürünleri Güveç', 'description' => ''],
             'en' => ['name' => 'Seafood Casserole', 'description' => ''],
             'de' => ['name' => 'Meeresfrüchte-Eintopf', 'description' => ''],
             'allergens' => [$fish, $crustaceans, $molluscs]],

            // Tatlılar
            ['category' => $tatlilar, 'data' => ['price' => 450, 'sort_order' => 1],
             'tr' => ['name' => 'Katmer', 'description' => ''],
             'en' => ['name' => 'Katmer', 'description' => ''],
             'de' => ['name' => 'Katmer', 'description' => ''],
             'allergens' => [$gluten, $dairy]],
            ['category' => $tatlilar, 'data' => ['price' => 450, 'sort_order' => 2, 'is_vegetarian' => true],
             'tr' => ['name' => 'Kabak Tatlısı', 'description' => ''],
             'en' => ['name' => 'Pumpkin Dessert', 'description' => ''],
             'de' => ['name' => 'Kürbisdessert', 'description' => ''],
             'allergens' => [$dairy]],

            // Soğuk İçecekler
            ['category' => $sogukIcecekler, 'data' => ['price' => 150, 'sort_order' => 1],
             'tr' => ['name' => 'Gazlı İçecek', 'description' => ''], 'en' => ['name' => 'Soft Drink', 'description' => ''], 'de' => ['name' => 'Softdrink', 'description' => ''], 'allergens' => []],
            ['category' => $sogukIcecekler, 'data' => ['price' => 150, 'sort_order' => 2],
             'tr' => ['name' => 'Meyve Suyu', 'description' => ''], 'en' => ['name' => 'Fruit Juice', 'description' => ''], 'de' => ['name' => 'Fruchtsaft', 'description' => ''], 'allergens' => []],
            ['category' => $sogukIcecekler, 'data' => ['price' => 150, 'sort_order' => 3],
             'tr' => ['name' => 'Soğuk Çay', 'description' => ''], 'en' => ['name' => 'Iced Tea', 'description' => ''], 'de' => ['name' => 'Eistee', 'description' => ''], 'allergens' => []],
            ['category' => $sogukIcecekler, 'data' => ['price' => 90, 'sort_order' => 4],
             'tr' => ['name' => 'Soda', 'description' => ''], 'en' => ['name' => 'Soda', 'description' => ''], 'de' => ['name' => 'Soda', 'description' => ''], 'allergens' => []],
            ['category' => $sogukIcecekler, 'data' => ['price' => 140, 'sort_order' => 5],
             'tr' => ['name' => 'Şalgam', 'description' => ''], 'en' => ['name' => 'Turnip Juice', 'description' => ''], 'de' => ['name' => 'Rübensaft', 'description' => ''], 'allergens' => []],
            ['category' => $sogukIcecekler, 'data' => ['price' => 120, 'sort_order' => 6],
             'tr' => ['name' => 'Ayran', 'description' => ''], 'en' => ['name' => 'Ayran', 'description' => ''], 'de' => ['name' => 'Ayran', 'description' => ''], 'allergens' => [$dairy]],
            ['category' => $sogukIcecekler, 'data' => ['price' => 100, 'sort_order' => 7],
             'tr' => ['name' => 'Su', 'description' => ''], 'en' => ['name' => 'Water', 'description' => ''], 'de' => ['name' => 'Wasser', 'description' => ''], 'allergens' => []],

            // Biralar
            ['category' => $biralar, 'data' => ['price' => 300, 'sort_order' => 1],
             'tr' => ['name' => 'Tuborg Malt', 'description' => ''], 'en' => ['name' => 'Tuborg Malt', 'description' => ''], 'de' => ['name' => 'Tuborg Malt', 'description' => ''], 'allergens' => [$gluten]],
            ['category' => $biralar, 'data' => ['price' => 300, 'sort_order' => 2],
             'tr' => ['name' => 'Tuborg Filtresiz', 'description' => ''], 'en' => ['name' => 'Tuborg Unfiltered', 'description' => ''], 'de' => ['name' => 'Tuborg Ungefiltert', 'description' => ''], 'allergens' => [$gluten]],
            ['category' => $biralar, 'data' => ['price' => 350, 'sort_order' => 3],
             'tr' => ['name' => 'Carlsberg', 'description' => ''], 'en' => ['name' => 'Carlsberg', 'description' => ''], 'de' => ['name' => 'Carlsberg', 'description' => ''], 'allergens' => [$gluten]],
            ['category' => $biralar, 'data' => ['price' => 350, 'sort_order' => 4],
             'tr' => ['name' => 'Corona', 'description' => ''], 'en' => ['name' => 'Corona', 'description' => ''], 'de' => ['name' => 'Corona', 'description' => ''], 'allergens' => [$gluten]],

            // Rakı
            ['category' => $raki, 'data' => ['price' => 500, 'sort_order' => 1],
             'tr' => ['name' => 'Rakı Tek', 'description' => ''], 'en' => ['name' => 'Raki Single', 'description' => ''], 'de' => ['name' => 'Raki Einfach', 'description' => ''], 'allergens' => []],
            ['category' => $raki, 'data' => ['price' => 600, 'sort_order' => 2],
             'tr' => ['name' => 'Rakı Double', 'description' => ''], 'en' => ['name' => 'Raki Double', 'description' => ''], 'de' => ['name' => 'Raki Doppelt', 'description' => ''], 'allergens' => []],
            ['category' => $raki, 'data' => ['price' => 1500, 'sort_order' => 3],
             'tr' => ['name' => 'Rakı Şişe 20cl', 'description' => ''], 'en' => ['name' => 'Raki Bottle 20cl', 'description' => ''], 'de' => ['name' => 'Raki Flasche 20cl', 'description' => ''], 'allergens' => []],
            ['category' => $raki, 'data' => ['price' => 2500, 'sort_order' => 4],
             'tr' => ['name' => 'Rakı Şişe 35cl', 'description' => ''], 'en' => ['name' => 'Raki Bottle 35cl', 'description' => ''], 'de' => ['name' => 'Raki Flasche 35cl', 'description' => ''], 'allergens' => []],
            ['category' => $raki, 'data' => ['price' => 3750, 'sort_order' => 5],
             'tr' => ['name' => 'Rakı Şişe 70cl', 'description' => ''], 'en' => ['name' => 'Raki Bottle 70cl', 'description' => ''], 'de' => ['name' => 'Raki Flasche 70cl', 'description' => ''], 'allergens' => []],

            // Şarap
            ['category' => $sarap, 'data' => ['price' => 650, 'sort_order' => 1],
             'tr' => ['name' => 'Beyaz Şarap Kadeh', 'description' => ''], 'en' => ['name' => 'White Wine Glass', 'description' => ''], 'de' => ['name' => 'Weißwein Glas', 'description' => ''], 'allergens' => [$molluscs]],
            ['category' => $sarap, 'data' => ['price' => 650, 'sort_order' => 2],
             'tr' => ['name' => 'Kırmızı Şarap Kadeh', 'description' => ''], 'en' => ['name' => 'Red Wine Glass', 'description' => ''], 'de' => ['name' => 'Rotwein Glas', 'description' => ''], 'allergens' => [$molluscs]],
            ['category' => $sarap, 'data' => ['price' => 2100, 'sort_order' => 3],
             'tr' => ['name' => 'Beyaz Şarap Şişe (Yerli)', 'description' => ''], 'en' => ['name' => 'White Wine Bottle (Local)', 'description' => ''], 'de' => ['name' => 'Weißwein Flasche (Lokal)', 'description' => ''], 'allergens' => [$molluscs]],
            ['category' => $sarap, 'data' => ['price' => 2100, 'sort_order' => 4],
             'tr' => ['name' => 'Kırmızı Şarap Şişe (Yerli)', 'description' => ''], 'en' => ['name' => 'Red Wine Bottle (Local)', 'description' => ''], 'de' => ['name' => 'Rotwein Flasche (Lokal)', 'description' => ''], 'allergens' => [$molluscs]],
            ['category' => $sarap, 'data' => ['price' => 3900, 'sort_order' => 5],
             'tr' => ['name' => 'Beyaz Şarap Şişe (İthal)', 'description' => ''], 'en' => ['name' => 'White Wine Bottle (Import)', 'description' => ''], 'de' => ['name' => 'Weißwein Flasche (Import)', 'description' => ''], 'allergens' => [$molluscs]],
            ['category' => $sarap, 'data' => ['price' => 3900, 'sort_order' => 6],
             'tr' => ['name' => 'Kırmızı Şarap Şişe (İthal)', 'description' => ''], 'en' => ['name' => 'Red Wine Bottle (Import)', 'description' => ''], 'de' => ['name' => 'Rotwein Flasche (Import)', 'description' => ''], 'allergens' => [$molluscs]],

            // İthal İçecekler
            ['category' => $import, 'data' => ['price' => 700, 'sort_order' => 1],
             'tr' => ['name' => "Gordon's Gin", 'description' => ''], 'en' => ['name' => "Gordon's Gin", 'description' => ''], 'de' => ['name' => "Gordon's Gin", 'description' => ''], 'allergens' => []],
            ['category' => $import, 'data' => ['price' => 800, 'sort_order' => 2],
             'tr' => ['name' => 'Chivas Regal', 'description' => ''], 'en' => ['name' => 'Chivas Regal', 'description' => ''], 'de' => ['name' => 'Chivas Regal', 'description' => ''], 'allergens' => []],
            ['category' => $import, 'data' => ['price' => 700, 'sort_order' => 3],
             'tr' => ['name' => 'Smirnoff Votka', 'description' => ''], 'en' => ['name' => 'Smirnoff Vodka', 'description' => ''], 'de' => ['name' => 'Smirnoff Wodka', 'description' => ''], 'allergens' => []],
            ['category' => $import, 'data' => ['price' => 800, 'sort_order' => 4],
             'tr' => ['name' => 'Aperol Spritz', 'description' => ''], 'en' => ['name' => 'Aperol Spritz', 'description' => ''], 'de' => ['name' => 'Aperol Spritz', 'description' => ''], 'allergens' => []],

            // Sıcak İçecekler
            ['category' => $sicakIcecekler, 'data' => ['price' => 150, 'sort_order' => 1],
             'tr' => ['name' => 'Türk Kahvesi', 'description' => ''], 'en' => ['name' => 'Turkish Coffee', 'description' => ''], 'de' => ['name' => 'Türkischer Kaffee', 'description' => ''], 'allergens' => []],
            ['category' => $sicakIcecekler, 'data' => ['price' => 200, 'sort_order' => 2],
             'tr' => ['name' => 'Americano', 'description' => ''], 'en' => ['name' => 'Americano', 'description' => ''], 'de' => ['name' => 'Americano', 'description' => ''], 'allergens' => []],
            ['category' => $sicakIcecekler, 'data' => ['price' => 180, 'sort_order' => 3],
             'tr' => ['name' => 'Espresso', 'description' => ''], 'en' => ['name' => 'Espresso', 'description' => ''], 'de' => ['name' => 'Espresso', 'description' => ''], 'allergens' => []],
            ['category' => $sicakIcecekler, 'data' => ['price' => 60, 'sort_order' => 4],
             'tr' => ['name' => 'Çay', 'description' => ''], 'en' => ['name' => 'Tea', 'description' => ''], 'de' => ['name' => 'Tee', 'description' => ''], 'allergens' => []],
        ];

        foreach ($items as $spec) {
            $item = MenuItem::create(array_merge($spec['data'], [
                'category_id' => $spec['category']->id,
            ]));

            foreach (['tr', 'en', 'de'] as $locale) {
                MenuItemTranslation::create([
                    'menu_item_id' => $item->id,
                    'locale'       => $locale,
                    'name'         => $spec[$locale]['name'],
                    'description'  => $spec[$locale]['description'],
                ]);
            }

            if (!empty($spec['allergens'])) {
                $item->allergens()->attach(collect($spec['allergens'])->filter()->pluck('id'));
            }
        }
    }
}
