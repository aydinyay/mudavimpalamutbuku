<?php
$base = '/home/mudavimp/mudavimpalamutbuku';
require_once $base . '/vendor/autoload.php';
$app = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>\n";

try {
    // Areas
    $areas = [
        ['slug' => 'teras',    'name_tr' => 'Teras',     'name_en' => 'Terrace', 'name_de' => 'Terrasse', 'type' => 'terrace', 'sort_order' => 1],
        ['slug' => 'plaj',     'name_tr' => 'Plaj',      'name_en' => 'Beach',   'name_de' => 'Strand',   'type' => 'beach',   'sort_order' => 2],
        ['slug' => 'ic-mekan', 'name_tr' => 'İç Mekan',  'name_en' => 'Indoor',  'name_de' => 'Innen',    'type' => 'indoor',  'sort_order' => 3],
    ];

    $areaIds = [];
    foreach ($areas as $a) {
        $area = \App\Modules\TablePlan\Models\Area::updateOrCreate(['slug' => $a['slug']], array_merge($a, ['plan_width_px' => 700, 'plan_height_px' => 500]));
        $areaIds[$a['slug']] = $area->id;
        echo "Area: {$a['name_tr']} (id={$area->id})\n";
    }

    // Teras masalar (8)
    for ($i = 1; $i <= 8; $i++) {
        \App\Modules\TablePlan\Models\Table::updateOrCreate(['table_code' => "teras-{$i}"], [
            'area_id'       => $areaIds['teras'],
            'table_number'  => "T{$i}",
            'seats_min'     => 2,
            'seats_max'     => $i <= 2 ? 2 : ($i <= 6 ? 4 : 6),
            'shape'         => 'square',
            'pos_x'         => 40 + (($i - 1) % 4) * 140,
            'pos_y'         => 40 + intdiv($i - 1, 4) * 140,
            'is_active'     => true,
            'is_reservable' => true,
        ]);
        echo "Masa: T{$i}\n";
    }

    // İç mekan masalar (4)
    for ($i = 1; $i <= 4; $i++) {
        \App\Modules\TablePlan\Models\Table::updateOrCreate(['table_code' => "ic-{$i}"], [
            'area_id'       => $areaIds['ic-mekan'],
            'table_number'  => "I{$i}",
            'seats_min'     => 2,
            'seats_max'     => 4,
            'shape'         => 'rectangle',
            'pos_x'         => 40 + ($i - 1) * 130,
            'pos_y'         => 80,
            'is_active'     => true,
            'is_reservable' => true,
        ]);
        echo "Masa: I{$i}\n";
    }

    echo "\nTAMAM: 3 bölge, 12 masa eklendi.\n";
} catch (\Throwable $e) {
    echo "HATA: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "</pre>\n";
unlink(__FILE__);
