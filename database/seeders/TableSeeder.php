<?php

namespace Database\Seeders;

use App\Modules\TablePlan\Models\Area;
use App\Modules\TablePlan\Models\Lounger;
use App\Modules\TablePlan\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Areas
        $teras = Area::updateOrCreate(['slug' => 'teras'], [
            'name_tr' => 'Teras', 'name_en' => 'Terrace', 'name_de' => 'Terrasse',
            'type' => 'terrace', 'sort_order' => 1, 'plan_width_px' => 700, 'plan_height_px' => 500,
        ]);

        $plaj = Area::updateOrCreate(['slug' => 'plaj'], [
            'name_tr' => 'Plaj', 'name_en' => 'Beach', 'name_de' => 'Strand',
            'type' => 'beach', 'sort_order' => 2, 'plan_width_px' => 800, 'plan_height_px' => 400,
        ]);

        $ic = Area::updateOrCreate(['slug' => 'ic-mekan'], [
            'name_tr' => 'İç Mekan', 'name_en' => 'Indoor', 'name_de' => 'Innen',
            'type' => 'indoor', 'sort_order' => 3, 'plan_width_px' => 600, 'plan_height_px' => 400,
        ]);

        // Terrace tables (8)
        for ($i = 1; $i <= 8; $i++) {
            Table::updateOrCreate(['table_code' => "teras-{$i}"], [
                'area_id'      => $teras->id,
                'table_number' => "T{$i}",
                'seats_min'    => 2,
                'seats_max'    => $i <= 2 ? 2 : ($i <= 6 ? 4 : 6),
                'shape'        => 'square',
                'pos_x'        => 40 + (($i - 1) % 4) * 140,
                'pos_y'        => 40 + intdiv($i - 1, 4) * 140,
                'is_active'    => true,
                'is_reservable'=> true,
            ]);
        }

        // Indoor tables (4)
        for ($i = 1; $i <= 4; $i++) {
            Table::updateOrCreate(['table_code' => "ic-{$i}"], [
                'area_id'      => $ic->id,
                'table_number' => "İ{$i}",
                'seats_min'    => 2,
                'seats_max'    => 4,
                'shape'        => 'rectangle',
                'pos_x'        => 40 + ($i - 1) * 130,
                'pos_y'        => 80,
                'is_active'    => true,
                'is_reservable'=> true,
            ]);
        }

        // Beach loungers (12)
        for ($i = 1; $i <= 12; $i++) {
            Lounger::updateOrCreate(['lounger_code' => "sezlong-{$i}"], [
                'area_id'         => $plaj->id,
                'lounger_number'  => "S{$i}",
                'type'            => $i <= 10 ? 'sunbed' : 'cabana',
                'pos_x'           => 40 + (($i - 1) % 6) * 110,
                'pos_y'           => 40 + intdiv($i - 1, 6) * 120,
                'daily_rate'      => $i <= 10 ? 500 : 1500,
                'includes_umbrella'=> true,
                'is_active'       => true,
                'is_reservable'   => true,
            ]);
        }
    }
}
