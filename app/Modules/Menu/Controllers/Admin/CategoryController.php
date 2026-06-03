<?php

namespace App\Modules\Menu\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Menu\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::orderBy('sort_order')->withCount('items')->get();
        return view('admin.menu.categories', compact('categories'));
    }

    public function create()
    {
        return view('admin.menu.category-form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_tr'    => 'required|string|max:100',
            'name_en'    => 'required|string|max:100',
            'name_de'    => 'required|string|max:100',
            'icon_emoji' => 'nullable|string|max:10',
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name_tr'] . '-' . uniqid());
        MenuCategory::create($data);

        return redirect()->route('admin.menu.categories.index')->with('success', 'Kategori eklendi.');
    }

    public function edit(MenuCategory $category)
    {
        return view('admin.menu.category-form', compact('category'));
    }

    public function update(Request $request, MenuCategory $category)
    {
        $data = $request->validate([
            'name_tr'    => 'required|string|max:100',
            'name_en'    => 'required|string|max:100',
            'name_de'    => 'required|string|max:100',
            'icon_emoji' => 'nullable|string|max:10',
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ]);

        $category->update($data);
        return redirect()->route('admin.menu.categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function destroy(MenuCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.menu.categories.index')->with('success', 'Kategori silindi.');
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            MenuCategory::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['ok' => true]);
    }
}
