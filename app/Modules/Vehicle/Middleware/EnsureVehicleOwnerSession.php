<?php

namespace App\Modules\Vehicle\Middleware;

use App\Modules\Vehicle\Models\Vehicle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVehicleOwnerSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('vehicle_owner.id');
        $version = $request->session()->get('vehicle_owner.credential_version');
        $loggedAt = $request->session()->get('vehicle_owner.logged_at');
        $vehicle = $id ? Vehicle::find($id) : null;
        if (! $vehicle || ! $loggedAt || now()->timestamp - (int) $loggedAt >= 86400 || (int) $version !== $vehicle->credential_version) {
            $request->session()->forget('vehicle_owner');

            return redirect()->route('vehicle.access.form')->withErrors(['plaka' => 'Oturumunuz sona erdi.']);
        }
        $request->attributes->set('ownerVehicle', $vehicle);

        return $next($request);
    }
}
