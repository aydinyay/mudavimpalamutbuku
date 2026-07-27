<?php

namespace App\Modules\Vehicle\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\VehicleMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleOwnerController extends Controller
{
    public function show(Request $request)
    {
        $vehicle = $request->attributes->get('ownerVehicle');
        $vehicle->load(['owner', 'expenses' => fn ($q) => $q->with(['payer', 'debtor', 'issue'])->latest('tarih'), 'issues' => fn ($q) => $q->latest('bildirilme_tarihi'), 'media']);
        $balances = $vehicle->balances();

        return view('vehicle.owner', compact('vehicle', 'balances'));
    }

    public function media(Request $request, VehicleMedia $media)
    {
        $vehicle = $request->attributes->get('ownerVehicle');
        abort_unless($media->vehicle_id === $vehicle->id, 404);

        return $this->file($media);
    }

    private function file(VehicleMedia $media)
    {
        abort_unless(Storage::disk('local')->exists($media->dosya_yolu), 404);
        $inline = str_starts_with($media->mime_type, 'image/') || str_starts_with($media->mime_type, 'video/');

        return response()->file(Storage::disk('local')->path($media->dosya_yolu), ['Content-Type' => $media->mime_type, 'X-Content-Type-Options' => 'nosniff', 'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.basename($media->dosya_yolu).'"']);
    }
}
