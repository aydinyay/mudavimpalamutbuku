<?php

namespace App\Modules\Vehicle\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Services\VehicleMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleMediaController extends Controller
{
    public function store(Request $request, Vehicle $vehicle, VehicleMediaService $service)
    {
        $data = $request->validate(['dosya' => 'required|file|max:102400', 'role' => ['required', Rule::in(['genel', 'bolum', 'hasar_once', 'hasar_sonra', 'parca', 'ruhsat', 'sigorta_police', 'muayene_belgesi', 'fatura', 'diger'])], 'caption' => 'nullable|string|max:255', 'vehicle_expense_id' => 'nullable|integer', 'vehicle_issue_id' => 'nullable|integer', 'tur' => 'nullable|in:foto,video']);
        $expense = isset($data['vehicle_expense_id']) ? $vehicle->expenses()->findOrFail($data['vehicle_expense_id']) : null;
        $issue = isset($data['vehicle_issue_id']) ? $vehicle->issues()->findOrFail($data['vehicle_issue_id']) : null;
        $service->store($vehicle, $request->file('dosya'), $data['role'], $expense, $issue, $data['caption'] ?? null);

        return back()->with('success', 'Medya yüklendi.');
    }

    public function show(Vehicle $vehicle, int $media)
    {
        $media = $vehicle->media()->findOrFail($media);
        abort_unless(Storage::disk('local')->exists($media->dosya_yolu), 404);
        $inline = str_starts_with($media->mime_type, 'image/') || str_starts_with($media->mime_type, 'video/');

        return response()->file(Storage::disk('local')->path($media->dosya_yolu), ['Content-Type' => $media->mime_type, 'X-Content-Type-Options' => 'nosniff', 'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.basename($media->dosya_yolu).'"']);
    }

    public function destroy(Vehicle $vehicle, int $media, VehicleMediaService $service)
    {
        $service->delete($vehicle->media()->findOrFail($media));

        return back()->with('success','Medya silindi.');
    }
}
