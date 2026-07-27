<?php

namespace App\Modules\Vehicle\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Services\VehicleMediaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleIssueController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        $vehicle->issues()->create($this->validated($request));

        return back()->with('success', 'Sorun/görev eklendi.');
    }

    public function update(Request $request, Vehicle $vehicle, int $issue)
    {
        $vehicle->issues()->findOrFail($issue)->update($this->validated($request));

        return back()->with('success', 'Sorun/görev güncellendi.');
    }

    public function destroy(Vehicle $vehicle, int $issue)
    {
        $vehicle->issues()->findOrFail($issue)->delete();

        return back()->with('success', 'Sorun/görev arşivlendi; medyası korundu.');
    }

    public function forceDestroy(Vehicle $vehicle, int $issue, VehicleMediaService $service)
    {
        $issue = $vehicle->issues()->withTrashed()->findOrFail($issue);
        $service->purgeParent($issue);

        return back()->with('success', 'Sorun/görev ve medyası kalıcı silindi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['baslik' => 'required|string|max:255', 'aciklama' => 'nullable|string', 'kategori' => ['required', Rule::in(['bakim', 'ariza', 'kaza', 'gorev', 'diger'])], 'durum' => ['required', Rule::in(['acik', 'devam_ediyor', 'tamamlandi'])], 'oncelik' => ['required', Rule::in(['dusuk', 'normal', 'yuksek', 'acil'])], 'bildirilme_tarihi' => 'required|date', 'cozulme_tarihi' => 'required_if:durum,tamamlandi|nullable|date', 'yapan_firma' => 'nullable|string|max:200', 'km' => 'nullable|integer|min:0', 'notlar' => 'nullable|string']);
    }
}
