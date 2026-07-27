<?php

namespace App\Modules\Vehicle\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Models\VehicleMedia;
use App\Modules\Vehicle\Services\VehicleMediaService;
use Illuminate\Support\Facades\Storage;

class VehicleShowcaseController extends Controller
{
    public function show(string $plate_key)
    {
        $vehicle = Vehicle::where('plate_key', $plate_key)->where('vitrin_acik', true)->firstOrFail();
        $media = $vehicle->media()->whereIn('role', VehicleMediaService::PUBLIC_ROLES)->get();
        $serviceHistory = $vehicle->expenses()->where('vitrinde_goster', true)->whereNotNull('public_summary')->select(['id', 'vehicle_id', 'tarih', 'public_summary'])->latest('tarih')->get();

        return view('vehicle.showcase', compact('vehicle', 'media', 'serviceHistory'));
    }

    public function media(string $plate_key, VehicleMedia $media)
    {
        $vehicle = Vehicle::where('plate_key', $plate_key)->where('vitrin_acik', true)->firstOrFail();
        abort_unless($media->vehicle_id === $vehicle->id && in_array($media->role, VehicleMediaService::PUBLIC_ROLES, true), 404);
        abort_unless(Storage::disk('local')->exists($media->dosya_yolu), 404);

        return response()->file(Storage::disk('local')->path($media->dosya_yolu), ['Content-Type' => $media->mime_type, 'X-Content-Type-Options' => 'nosniff', 'Content-Disposition' => 'inline; filename="'.basename($media->dosya_yolu).'"']);
    }
}
