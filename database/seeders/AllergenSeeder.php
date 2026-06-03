<?php

namespace Database\Seeders;

use App\Modules\Menu\Models\Allergen;
use Illuminate\Database\Seeder;

class AllergenSeeder extends Seeder
{
    public function run(): void
    {
        $allergens = [
            ['code'=>'gluten',      'name_tr'=>'Gluten',       'name_en'=>'Gluten',       'name_de'=>'Gluten'],
            ['code'=>'crustaceans', 'name_tr'=>'Kabuklu Deniz Ürünleri', 'name_en'=>'Crustaceans', 'name_de'=>'Krebstiere'],
            ['code'=>'eggs',        'name_tr'=>'Yumurta',      'name_en'=>'Eggs',         'name_de'=>'Eier'],
            ['code'=>'fish',        'name_tr'=>'Balık',        'name_en'=>'Fish',         'name_de'=>'Fisch'],
            ['code'=>'peanuts',     'name_tr'=>'Yer Fıstığı',  'name_en'=>'Peanuts',      'name_de'=>'Erdnüsse'],
            ['code'=>'soy',         'name_tr'=>'Soya',         'name_en'=>'Soy',          'name_de'=>'Soja'],
            ['code'=>'dairy',       'name_tr'=>'Süt Ürünleri', 'name_en'=>'Dairy',        'name_de'=>'Milch'],
            ['code'=>'nuts',        'name_tr'=>'Kuruyemiş',    'name_en'=>'Tree Nuts',    'name_de'=>'Schalenfrüchte'],
            ['code'=>'celery',      'name_tr'=>'Kereviz',      'name_en'=>'Celery',       'name_de'=>'Sellerie'],
            ['code'=>'mustard',     'name_tr'=>'Hardal',       'name_en'=>'Mustard',      'name_de'=>'Senf'],
            ['code'=>'sesame',      'name_tr'=>'Susam',        'name_en'=>'Sesame',       'name_de'=>'Sesam'],
            ['code'=>'so2',         'name_tr'=>'Sülfür Dioksit', 'name_en'=>'Sulphur Dioxide', 'name_de'=>'Schwefeldioxid'],
            ['code'=>'lupin',       'name_tr'=>'Acı Bakla',    'name_en'=>'Lupin',        'name_de'=>'Lupinen'],
            ['code'=>'molluscs',    'name_tr'=>'Yumuşakçalar', 'name_en'=>'Molluscs',     'name_de'=>'Weichtiere'],
        ];

        foreach ($allergens as $i => $data) {
            Allergen::updateOrCreate(['code' => $data['code']], array_merge($data, ['sort_order' => $i]));
        }
    }
}
