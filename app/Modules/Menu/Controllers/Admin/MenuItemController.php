<?php

namespace App\Modules\Menu\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Menu\Models\Allergen;
use App\Modules\Menu\Models\MenuCategory;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Models\MenuItemTranslation;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->get('kategori');

        $items = MenuItem::with(['category', 'translations'])
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->paginate(30);

        $categories = MenuCategory::orderBy('sort_order')->get();

        return view('admin.menu.items', compact('items', 'categories', 'categoryId'));
    }

    public function create()
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();
        $allergens  = Allergen::orderBy('sort_order')->get();
        return view('admin.menu.item-form', compact('categories', 'allergens'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);
        $item = MenuItem::create($data['item']);

        $this->saveTranslations($item, $data);
        $item->allergens()->sync($request->input('allergen_ids', []));

        if ($request->hasFile('image')) {
            $item->update(['image_path' => $this->saveImage($request, $item->id)]);
        }

        return redirect()->route('admin.menu.items.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(MenuItem $item)
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();
        $allergens  = Allergen::orderBy('sort_order')->get();
        $item->load(['translations', 'allergens']);
        return view('admin.menu.item-form', compact('item', 'categories', 'allergens'));
    }

    public function update(Request $request, MenuItem $item)
    {
        $data = $this->validateItem($request, $item);
        $item->update($data['item']);

        $this->saveTranslations($item, $data);
        $item->allergens()->sync($request->input('allergen_ids', []));

        if ($request->hasFile('image')) {
            $item->update(['image_path' => $this->saveImage($request, $item->id)]);
        }

        return redirect()->route('admin.menu.items.index')->with('success', 'Ürün güncellendi.');
    }

    public function destroy(MenuItem $item)
    {
        $item->delete();
        return redirect()->route('admin.menu.items.index')->with('success', 'Ürün silindi.');
    }

    public function toggleAvailable(MenuItem $item)
    {
        $item->update(['is_available' => !$item->is_available]);
        return response()->json(['is_available' => $item->is_available]);
    }

    private function validateItem(Request $request, ?MenuItem $existing = null): array
    {
        $request->validate([
            'category_id'   => 'required|exists:menu_categories,id',
            'price'         => 'required|numeric|min:0',
            'price_eur'     => 'nullable|numeric|min:0',
            'sort_order'    => 'integer',
            'name_tr'       => 'required|string|max:200',
            'name_en'       => 'required|string|max:200',
            'name_de'       => 'required|string|max:200',
            'description_tr'=> 'nullable|string',
            'description_en'=> 'nullable|string',
            'description_de'=> 'nullable|string',
            'image'         => 'nullable|image|max:4096',
        ]);

        return [
            'item' => [
                'category_id'    => $request->category_id,
                'price'          => $request->price,
                'price_eur'      => $request->price_eur,
                'sort_order'     => $request->integer('sort_order'),
                'is_available'   => $request->boolean('is_available', true),
                'is_seasonal'    => $request->boolean('is_seasonal'),
                'is_featured'    => $request->boolean('is_featured'),
                'is_vegetarian'  => $request->boolean('is_vegetarian'),
                'is_vegan'       => $request->boolean('is_vegan'),
                'is_gluten_free' => $request->boolean('is_gluten_free'),
            ],
            'translations' => [
                'tr' => ['name' => $request->name_tr, 'description' => $request->description_tr],
                'en' => ['name' => $request->name_en, 'description' => $request->description_en],
                'de' => ['name' => $request->name_de, 'description' => $request->description_de],
            ],
        ];
    }

    private function saveTranslations(MenuItem $item, array $data): void
    {
        foreach ($data['translations'] as $locale => $trans) {
            MenuItemTranslation::updateOrCreate(
                ['menu_item_id' => $item->id, 'locale' => $locale],
                $trans,
            );
        }
    }

    private function saveImage(Request $request, int $itemId): string
    {
        $manager = new ImageManager(new Driver());
        $img     = $manager->read($request->file('image'));
        $img->cover(800, 600);

        $path = "images/menu/{$itemId}.webp";
        $img->toWebp(85)->save(public_path($path));

        return '/' . $path;
    }
}
