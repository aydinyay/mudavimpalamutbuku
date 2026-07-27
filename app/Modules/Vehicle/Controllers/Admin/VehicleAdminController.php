<?php

namespace App\Modules\Vehicle\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Models\VehicleChangeLog;
use App\Modules\Vehicle\Models\VehicleParty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VehicleAdminController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('owner')->withSum(['expenses as receivable_total' => fn ($q) => $q->where('mahsup_durumu', 'alinacak')], 'tutar')->latest()->get();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $parties = VehicleParty::orderBy('ad')->get();

        return view('admin.vehicles.form', compact('parties'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $key = Vehicle::normalizePlate($data['plaka']);
        if (Vehicle::withTrashed()->where('plate_key', $key)->exists()) {
            return back()->withErrors(['plaka' => 'Bu plaka zaten kayıtlı.'])->withInput();
        } $password = $this->password();
        $data['erisim_sifresi_hash'] = Hash::make($password);
        $vehicle = Vehicle::create($data);

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'Araç eklendi. Erişim şifresi yalnız şimdi gösterilir: '.$password);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['owner', 'expenses' => fn ($q) => $q->with(['payer', 'debtor', 'issue'])->latest('tarih'), 'issues' => fn ($q) => $q->latest('bildirilme_tarihi'), 'media', 'changeLogs' => fn ($q) => $q->latest('created_at')]);
        $parties = VehicleParty::orderBy('ad')->get();
        $balances = $vehicle->balances();

        return view('admin.vehicles.show', compact('vehicle', 'parties', 'balances'));
    }

    public function edit(Vehicle $vehicle)
    {
        $parties = VehicleParty::orderBy('ad')->get();

        return view('admin.vehicles.form', compact('vehicle', 'parties'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $this->validated($request, $vehicle);
        $key = Vehicle::normalizePlate($data['plaka']);
        if (Vehicle::withTrashed()->where('plate_key', $key)->whereKeyNot($vehicle->id)->exists()) {
            return back()->withErrors(['plaka' => 'Bu plaka zaten kayıtlı.'])->withInput();
        } if ((int) $data['guncel_km'] < $vehicle->guncel_km) {
            $request->validate(['km_duzeltme_nedeni' => 'required|string|min:5|max:500']);
        } DB::transaction(function () use ($vehicle, $data, $request) {
            $old = $vehicle->guncel_km;
            $vehicle->update($data);
            if ((int) $data['guncel_km'] < $old) {
                VehicleChangeLog::create(['vehicle_id' => $vehicle->id, 'alan_adi' => 'guncel_km', 'eski_deger' => (string) $old, 'yeni_deger' => (string) $data['guncel_km'], 'aciklama' => $request->km_duzeltme_nedeni]);
            }
        });

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'Araç güncellendi.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', 'Araç arşivlendi.');
    }

    public function resetPassword(Vehicle $vehicle)
    {
        $password = $this->password();
        $vehicle->update(['erisim_sifresi_hash' => Hash::make($password), 'credential_version' => $vehicle->credential_version + 1]);

        return back()->with('success', 'Yeni şifre yalnız şimdi gösterilir: '.$password);
    }

    private function password(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $out = '';
        for ($i = 0; $i < 12; $i++) {
            $out .= $alphabet[random_int(0, 61)];
        }

        return $out;
    }

    private function validated(Request $request, ?Vehicle $vehicle = null): array
    {
        $data = $request->validate(['plaka' => 'required|string|max:30', 'marka' => 'required|string|max:100', 'model' => 'required|string|max:100', 'model_yili' => 'required|integer|min:1886|max:'.(now()->year + 1), 'sase_no' => 'required|string|max:100', 'motor_no' => 'required|string|max:100', 'renk' => 'required|string|max:60', 'yakit_cinsi' => 'required|string|max:60', 'sahip_party_id' => 'nullable|exists:vehicle_parties,id', 'guncel_km' => 'required|integer|min:0', 'sonraki_bakim_km' => 'nullable|integer|min:0', 'sonraki_bakim_tarihi' => 'nullable|date', 'sonraki_muayene_tarihi' => 'nullable|date', 'sigorta_bitis_tarihi' => 'nullable|date', 'onceki_sahipler_notu' => 'nullable|string', 'donanimlar' => 'nullable|array', 'donanimlar.*' => 'string|max:100', 'vitrin_acik' => 'sometimes|boolean', 'notlar' => 'nullable|string']);
        $data['vitrin_acik'] = $request->boolean('vitrin_acik');

        return $data;
    }
}
