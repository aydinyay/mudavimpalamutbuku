<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('menu_item_translations')->truncate();
        DB::table('menu_item_allergens')->truncate();
        DB::table('menu_items')->truncate();
        DB::table('menu_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $categories = [
            ['slug' => 'atistirmaliklar',   'name_tr' => 'Atıştırmalıklar',  'name_en' => 'Starters',         'name_de' => 'Vorspeisen',        'icon_emoji' => '🍟', 'sort_order' => 1],
            ['slug' => 'salatalar',          'name_tr' => 'Salatalar',         'name_en' => 'Salads',           'name_de' => 'Salate',            'icon_emoji' => '🥗', 'sort_order' => 2],
            ['slug' => 'mezeler',            'name_tr' => 'Mezeler',           'name_en' => 'Mezes',            'name_de' => 'Meze',              'icon_emoji' => '🫙', 'sort_order' => 3],
            ['slug' => 'deniz-mezeleri',     'name_tr' => 'Deniz Mezeleri',    'name_en' => 'Seafood Mezes',    'name_de' => 'Meeresfrüchte Meze','icon_emoji' => '🦀', 'sort_order' => 4],
            ['slug' => 'ara-sicaklar',       'name_tr' => 'Ara Sıcaklar',      'name_en' => 'Hot Starters',     'name_de' => 'Warme Vorspeisen',  'icon_emoji' => '🔥', 'sort_order' => 5],
            ['slug' => 'et-urunleri',        'name_tr' => 'Et Ürünleri',       'name_en' => 'Meat',             'name_de' => 'Fleischgerichte',   'icon_emoji' => '🥩', 'sort_order' => 6],
            ['slug' => 'deniz-urunleri',     'name_tr' => 'Deniz Ürünleri',    'name_en' => 'Seafood',          'name_de' => 'Meeresfrüchte',     'icon_emoji' => '🐟', 'sort_order' => 7],
            ['slug' => 'tatlilar',           'name_tr' => 'Tatlılar',          'name_en' => 'Desserts',         'name_de' => 'Desserts',          'icon_emoji' => '🍮', 'sort_order' => 8],
            ['slug' => 'soguk-icecekler',    'name_tr' => 'Soğuk İçecekler',   'name_en' => 'Cold Drinks',      'name_de' => 'Kalte Getränke',    'icon_emoji' => '🧃', 'sort_order' => 9],
            ['slug' => 'biralar',            'name_tr' => 'Biralar',           'name_en' => 'Beers',            'name_de' => 'Biere',             'icon_emoji' => '🍺', 'sort_order' => 10],
            ['slug' => 'rakilar',            'name_tr' => 'Rakılar',           'name_en' => 'Raki',             'name_de' => 'Raki',              'icon_emoji' => '🥃', 'sort_order' => 11],
            ['slug' => 'beyaz-saraplar',     'name_tr' => 'Beyaz Şaraplar',    'name_en' => 'White Wines',      'name_de' => 'Weißweine',         'icon_emoji' => '🍾', 'sort_order' => 12],
            ['slug' => 'kirmizi-saraplar',   'name_tr' => 'Kırmızı Şaraplar', 'name_en' => 'Red Wines',        'name_de' => 'Rotweine',          'icon_emoji' => '🍷', 'sort_order' => 13],
            ['slug' => 'import-icecekler',   'name_tr' => 'Import İçecekler',  'name_en' => 'Imported Drinks',  'name_de' => 'Importgetränke',    'icon_emoji' => '🥂', 'sort_order' => 14],
            ['slug' => 'sicak-icecekler',    'name_tr' => 'Sıcak İçecekler',   'name_en' => 'Hot Drinks',       'name_de' => 'Heiße Getränke',    'icon_emoji' => '☕', 'sort_order' => 15],
        ];

        foreach ($categories as &$cat) {
            $cat['is_active'] = true;
            $cat['created_at'] = now();
            $cat['updated_at'] = now();
        }
        DB::table('menu_categories')->insert($categories);

        $catIds = DB::table('menu_categories')->pluck('id', 'slug');

        $items = [
            // Atıştırmalıklar
            ['cat' => 'atistirmaliklar', 'tr' => 'Patates Tava',              'price' => 300.00,  'order' => 1],
            ['cat' => 'atistirmaliklar', 'tr' => 'Yoğurtlama',                'price' => 600.00,  'order' => 2],
            ['cat' => 'atistirmaliklar', 'tr' => 'Sigara Böreği',             'price' => 400.00,  'order' => 3],

            // Salatalar
            ['cat' => 'salatalar', 'tr' => 'Roka Salatası',                   'price' => 500.00,  'order' => 1],
            ['cat' => 'salatalar', 'tr' => 'Çoban Salata',                    'price' => 500.00,  'order' => 2],
            ['cat' => 'salatalar', 'tr' => 'Domates Salatası',                'price' => 500.00,  'order' => 3],

            // Mezeler
            ['cat' => 'mezeler', 'tr' => 'Günlük Mezeler',                    'price' => 400.00,  'order' => 1],

            // Deniz Mezeleri
            ['cat' => 'deniz-mezeleri', 'tr' => 'Uskumru Çiroz',              'price' => 600.00,  'order' => 1],
            ['cat' => 'deniz-mezeleri', 'tr' => 'Lakerda',                    'price' => 650.00,  'order' => 2],
            ['cat' => 'deniz-mezeleri', 'tr' => 'Karides Salata',             'price' => 750.00,  'order' => 3],
            ['cat' => 'deniz-mezeleri', 'tr' => 'Ahtapot Salatası',           'price' => 900.00,  'order' => 4],

            // Ara Sıcaklar
            ['cat' => 'ara-sicaklar', 'tr' => 'Kalamar Beyti',                'price' => 850.00,  'order' => 1],
            ['cat' => 'ara-sicaklar', 'tr' => 'Karides Mantı',                'price' => 850.00,  'order' => 2],
            ['cat' => 'ara-sicaklar', 'tr' => 'Deniz Mahsullü Börek',         'price' => 600.00,  'order' => 3],
            ['cat' => 'ara-sicaklar', 'tr' => 'Çıtır Karides 1 Adet',        'price' => 600.00,  'order' => 4],
            ['cat' => 'ara-sicaklar', 'tr' => 'Kiremitte Mantar',             'price' => 650.00,  'order' => 5],
            ['cat' => 'ara-sicaklar', 'tr' => 'Kiremitte Sıcak Ot',          'price' => 600.00,  'order' => 6],
            ['cat' => 'ara-sicaklar', 'tr' => 'Kiremitte Karides',            'price' => 900.00,  'order' => 7],
            ['cat' => 'ara-sicaklar', 'tr' => 'Kalamar Tava',                 'price' => 1150.00, 'order' => 8],
            ['cat' => 'ara-sicaklar', 'tr' => 'Levrek Simit',                 'price' => 850.00,  'order' => 9],
            ['cat' => 'ara-sicaklar', 'tr' => 'Paçanga Böreği',               'price' => 450.00,  'order' => 10],
            ['cat' => 'ara-sicaklar', 'tr' => 'Arnavut Ciğeri',               'price' => 750.00,  'order' => 11],

            // Et Ürünleri
            ['cat' => 'et-urunleri', 'tr' => 'Izgara Köfte (170 Gr)',         'price' => 800.00,  'order' => 1],
            ['cat' => 'et-urunleri', 'tr' => 'Kuzu Şiş (170 Gr)',             'price' => 900.00,  'order' => 2],
            ['cat' => 'et-urunleri', 'tr' => 'Kuzu Pirzola (170 Gr)',         'price' => 1150.00, 'order' => 3],
            ['cat' => 'et-urunleri', 'tr' => 'Antrikot (200 Gr)',             'price' => 950.00,  'order' => 4],
            ['cat' => 'et-urunleri', 'tr' => 'Et Bonfile (180 Gr)',           'price' => 1200.00, 'order' => 5],
            ['cat' => 'et-urunleri', 'tr' => 'Lokum Bonfile (180 Gr)',        'price' => 1200.00, 'order' => 6],
            ['cat' => 'et-urunleri', 'tr' => 'Tavuk Şiş (250 Gr)',           'price' => 800.00,  'order' => 7],

            // Deniz Ürünleri
            ['cat' => 'deniz-urunleri', 'tr' => 'Levrek Izgara (400 Gr)',     'price' => 850.00,  'order' => 1],
            ['cat' => 'deniz-urunleri', 'tr' => 'Çipura Izgara (400 Gr)',     'price' => 850.00,  'order' => 2],
            ['cat' => 'deniz-urunleri', 'tr' => 'Tekir Tava (300 Gr)',        'price' => 900.00,  'order' => 3],
            ['cat' => 'deniz-urunleri', 'tr' => 'Günün Balıkları (Kg)',       'price' => 0.00,    'order' => 4, 'is_seasonal' => true],
            ['cat' => 'deniz-urunleri', 'tr' => 'Izgara Ahtapot',             'price' => 1350.00, 'order' => 5],
            ['cat' => 'deniz-urunleri', 'tr' => 'Güveç Ahtapot',              'price' => 1250.00, 'order' => 6],
            ['cat' => 'deniz-urunleri', 'tr' => 'Izgara Jumbo Karides 3 Ad.', 'price' => 1200.00, 'order' => 7],
            ['cat' => 'deniz-urunleri', 'tr' => 'Deniz Ürünleri Güveç',       'price' => 1200.00, 'order' => 8],

            // Tatlılar
            ['cat' => 'tatlilar', 'tr' => 'Katmer',                           'price' => 450.00,  'order' => 1],
            ['cat' => 'tatlilar', 'tr' => 'Kabak Tatlısı',                    'price' => 450.00,  'order' => 2],

            // Soğuk İçecekler
            ['cat' => 'soguk-icecekler', 'tr' => 'Gazlı İçecekler',          'price' => 150.00,  'order' => 1],
            ['cat' => 'soguk-icecekler', 'tr' => 'Meyve Suları',              'price' => 150.00,  'order' => 2],
            ['cat' => 'soguk-icecekler', 'tr' => 'Soğuk Çaylar',              'price' => 150.00,  'order' => 3],
            ['cat' => 'soguk-icecekler', 'tr' => 'Soda',                      'price' => 90.00,   'order' => 4],
            ['cat' => 'soguk-icecekler', 'tr' => 'Şalgam Suyu',               'price' => 140.00,  'order' => 5],
            ['cat' => 'soguk-icecekler', 'tr' => 'Ayran',                     'price' => 120.00,  'order' => 6],
            ['cat' => 'soguk-icecekler', 'tr' => 'Su (1 Lt)',                  'price' => 100.00,  'order' => 7],

            // Biralar
            ['cat' => 'biralar', 'tr' => 'Tuborg Malt',                       'price' => 300.00,  'order' => 1],
            ['cat' => 'biralar', 'tr' => 'Tuborg Filtresiz',                  'price' => 300.00,  'order' => 2],
            ['cat' => 'biralar', 'tr' => 'Carlsberg',                         'price' => 350.00,  'order' => 3],
            ['cat' => 'biralar', 'tr' => 'Corona',                            'price' => 350.00,  'order' => 4],

            // Rakılar
            ['cat' => 'rakilar', 'tr' => 'Tek Rakı (Yeni Rakı)',              'price' => 500.00,  'order' => 1],
            ['cat' => 'rakilar', 'tr' => 'Double Rakı (Yeni Rakı)',           'price' => 600.00,  'order' => 2],
            ['cat' => 'rakilar', 'tr' => 'Yeni Rakı (20 Cl)',                 'price' => 1500.00, 'order' => 3],
            ['cat' => 'rakilar', 'tr' => 'Yeni Rakı (35 Cl)',                 'price' => 2000.00, 'order' => 4],
            ['cat' => 'rakilar', 'tr' => 'Yeni Rakı (70 Cl)',                 'price' => 3000.00, 'order' => 5],
            ['cat' => 'rakilar', 'tr' => 'Efe Gold (20 Cl)',                  'price' => 1700.00, 'order' => 6],
            ['cat' => 'rakilar', 'tr' => 'Efe Gold (35 Cl)',                  'price' => 2250.00, 'order' => 7],
            ['cat' => 'rakilar', 'tr' => 'Efe Gold (70 Cl)',                  'price' => 3400.00, 'order' => 8],
            ['cat' => 'rakilar', 'tr' => 'Beylerbeyi Göbek (20 Cl)',          'price' => 1900.00, 'order' => 9],
            ['cat' => 'rakilar', 'tr' => 'Beylerbeyi Göbek (35 Cl)',          'price' => 2400.00, 'order' => 10],
            ['cat' => 'rakilar', 'tr' => 'Beylerbeyi Göbek (70 Cl)',          'price' => 3750.00, 'order' => 11],

            // Beyaz Şaraplar
            ['cat' => 'beyaz-saraplar', 'tr' => 'Kadeh Beyaz Şarap',         'price' => 650.00,  'order' => 1],
            ['cat' => 'beyaz-saraplar', 'tr' => 'Angora Beyaz (70 Cl)',       'price' => 2100.00, 'order' => 2],
            ['cat' => 'beyaz-saraplar', 'tr' => 'Kav Narince (70 Cl)',        'price' => 2500.00, 'order' => 3],
            ['cat' => 'beyaz-saraplar', 'tr' => 'Sarafin Sav. Blanc (70 Cl)','price' => 3700.00, 'order' => 4],
            ['cat' => 'beyaz-saraplar', 'tr' => 'Sarafin Chardonnay (70 Cl)','price' => 3900.00, 'order' => 5],

            // Kırmızı Şaraplar
            ['cat' => 'kirmizi-saraplar', 'tr' => 'Kadeh Kırmızı Şarap',     'price' => 650.00,  'order' => 1],
            ['cat' => 'kirmizi-saraplar', 'tr' => 'Angora Kırmızı (70 Cl)',  'price' => 2100.00, 'order' => 2],
            ['cat' => 'kirmizi-saraplar', 'tr' => 'Kav Öküzgözü (70 Cl)',    'price' => 2500.00, 'order' => 3],
            ['cat' => 'kirmizi-saraplar', 'tr' => 'Safarin Cab. Sau. (70 Cl)','price' => 3700.00, 'order' => 4],
            ['cat' => 'kirmizi-saraplar', 'tr' => 'Datça Wineyard Blush',    'price' => 2500.00, 'order' => 5],

            // Import İçecekler
            ['cat' => 'import-icecekler', 'tr' => "Gordon's Cin",             'price' => 700.00,  'order' => 1],
            ['cat' => 'import-icecekler', 'tr' => 'Chivas Regal 12 Yrs',     'price' => 800.00,  'order' => 2],
            ['cat' => 'import-icecekler', 'tr' => 'Smirnoff Votka',           'price' => 700.00,  'order' => 3],
            ['cat' => 'import-icecekler', 'tr' => 'Aperol Spritz',            'price' => 800.00,  'order' => 4],

            // Sıcak İçecekler
            ['cat' => 'sicak-icecekler', 'tr' => 'Türk Kahvesi',              'price' => 150.00,  'order' => 1],
            ['cat' => 'sicak-icecekler', 'tr' => 'Americano',                 'price' => 200.00,  'order' => 2],
            ['cat' => 'sicak-icecekler', 'tr' => 'Espresso',                  'price' => 180.00,  'order' => 3],
            ['cat' => 'sicak-icecekler', 'tr' => 'Çay',                       'price' => 60.00,   'order' => 4],
        ];

        foreach ($items as $item) {
            $id = DB::table('menu_items')->insertGetId([
                'category_id'  => $catIds[$item['cat']],
                'price'        => $item['price'],
                'is_seasonal'  => $item['is_seasonal'] ?? false,
                'is_available' => true,
                'sort_order'   => $item['order'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $name = $item['tr'];
            DB::table('menu_item_translations')->insert([
                ['menu_item_id' => $id, 'locale' => 'tr', 'name' => $name, 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['menu_item_id' => $id, 'locale' => 'en', 'name' => $name, 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ['menu_item_id' => $id, 'locale' => 'de', 'name' => $name, 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('menu_item_translations')->truncate();
        DB::table('menu_item_allergens')->truncate();
        DB::table('menu_items')->truncate();
        DB::table('menu_categories')->truncate();
        Schema::enableForeignKeyConstraints();
    }
};
