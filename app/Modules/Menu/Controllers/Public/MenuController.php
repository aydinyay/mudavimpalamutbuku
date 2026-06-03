<?php

namespace App\Modules\Menu\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Menu\Models\Allergen;
use App\Modules\Menu\Models\MenuCategory;
use App\Modules\QrCode\Models\QrCode;
use App\Modules\TablePlan\Models\Table;

class MenuController extends Controller
{
    public function index()
    {
        [$categories, $allergens] = $this->menuData();
        return view('menu.public.index', compact('categories', 'allergens'));
    }

    public function tableMenu(string $tableCode)
    {
        $table = Table::where('table_code', $tableCode)->where('is_active', true)->firstOrFail();
        $qr    = QrCode::where('target_type', 'table')->where('target_id', $table->id)->first();

        if ($qr) {
            $qr->increment('scan_count');
        }

        [$categories, $allergens] = $this->menuData();
        $tableName = $table->displayLabel();
        $qrUuid    = $qr?->qr_uuid;

        return view('menu.public.index', compact('categories', 'allergens', 'table', 'tableName', 'qrUuid', 'tableCode'));
    }

    private function menuData(): array
    {
        $categories = MenuCategory::where('is_active', true)
            ->with(['items' => fn($q) => $q->orderBy('sort_order')
                ->with(['translations', 'allergens'])])
            ->orderBy('sort_order')
            ->get();

        $allergens = Allergen::orderBy('sort_order')->get();

        return [$categories, $allergens];
    }
}
