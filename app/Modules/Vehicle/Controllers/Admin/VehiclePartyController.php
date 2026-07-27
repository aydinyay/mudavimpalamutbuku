<?php

namespace App\Modules\Vehicle\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\VehicleParty;
use Illuminate\Http\Request;

class VehiclePartyController extends Controller
{
    public function index()
    {
        $parties = VehicleParty::latest()->get();

        return view('admin.vehicles.parties', compact('parties'));
    }

    public function store(Request $request)
    {
        VehicleParty::create($request->validate(['ad' => 'required|string|max:150', 'telefon' => 'nullable|string|max:50', 'notlar' => 'nullable|string']));

        return back()->with('success', 'Taraf eklendi.');
    }

    public function update(Request $request, VehicleParty $party)
    {
        $party->update($request->validate(['ad' => 'required|string|max:150', 'telefon' => 'nullable|string|max:50', 'notlar' => 'nullable|string']));

        return back()->with('success', 'Taraf güncellendi.');
    }

    public function destroy(VehicleParty $party)
    {
        $party->delete();

        return back()->with('success', 'Taraf arşivlendi; geçmiş kayıtlardaki adı korunur.');
    }
}
