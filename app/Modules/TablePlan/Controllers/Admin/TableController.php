<?php

namespace App\Modules\TablePlan\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\TablePlan\Models\Area;
use App\Modules\TablePlan\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $areas = Area::with(['tables' => fn($q) => $q->orderBy('table_number')])
            ->orderBy('sort_order')
            ->get();

        return view('admin.tableplan.index', compact('areas'));
    }

    public function create()
    {
        $areas = Area::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.tableplan.table-form', compact('areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'area_id'      => 'required|exists:areas,id',
            'table_number' => 'required|string|max:20',
            'table_code'   => 'required|string|max:50|unique:tables',
            'seats_min'    => 'required|integer|min:1|max:20',
            'seats_max'    => 'required|integer|min:1|max:20',
            'shape'        => 'required|in:round,square,rectangle',
            'is_reservable'=> 'boolean',
            'notes'        => 'nullable|string',
        ]);

        Table::create($data);
        return redirect()->route('admin.tables.index')->with('success', 'Masa eklendi.');
    }

    public function edit(Table $table)
    {
        $areas = Area::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.tableplan.table-form', compact('table', 'areas'));
    }

    public function update(Request $request, Table $table)
    {
        $data = $request->validate([
            'area_id'      => 'required|exists:areas,id',
            'table_number' => 'required|string|max:20',
            'table_code'   => 'required|string|max:50|unique:tables,table_code,' . $table->id,
            'seats_min'    => 'required|integer|min:1|max:20',
            'seats_max'    => 'required|integer|min:1|max:20',
            'shape'        => 'required|in:round,square,rectangle',
            'is_reservable'=> 'boolean',
            'notes'        => 'nullable|string',
        ]);

        $table->update($data);
        return redirect()->route('admin.tables.index')->with('success', 'Masa güncellendi.');
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return redirect()->route('admin.tables.index')->with('success', 'Masa silindi.');
    }

    public function updatePosition(Request $request, Table $table)
    {
        $data = $request->validate([
            'pos_x' => 'required|integer|min:0',
            'pos_y' => 'required|integer|min:0',
        ]);
        $table->update($data);
        return response()->json(['ok' => true]);
    }
}
