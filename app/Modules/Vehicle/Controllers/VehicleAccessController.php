<?php

namespace App\Modules\Vehicle\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class VehicleAccessController extends Controller
{
    public function form(): View
    {
        return view('vehicle.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['plaka' => 'required|string|max:30', 'sifre' => 'required|string|max:100']);
        $plateKey = Vehicle::normalizePlate($data['plaka']);
        $ip = $request->ip();
        $pair = 'vehicle-login:'.hash('sha256', $ip.'|'.$plateKey);
        $global = 'vehicle-login-ip:'.hash('sha256', $ip);
        if (RateLimiter::tooManyAttempts($pair, 5) || RateLimiter::tooManyAttempts($global, 20)) {
            return back()->withErrors(['plaka' => 'Çok fazla deneme yaptınız. Lütfen daha sonra tekrar deneyin.'])->withInput($request->only('plaka'))->setStatusCode(429);
        }
        RateLimiter::hit($global, 3600);
        $vehicle = Vehicle::where('plate_key', $plateKey)->first();
        $dummy = '$2y$04$F4RNz.E9R/h3rQF4Qh2kKujgKf7Z8C6kW/rtX8u4ZjQzMshY8UdQK';
        $valid = Hash::check($data['sifre'], $vehicle?->erisim_sifresi_hash ?: $dummy);
        if (! $vehicle || ! $vehicle->erisim_sifresi_hash || ! $valid) {
            RateLimiter::hit($pair, 900);
            Log::warning('Vehicle owner login failed', ['plate_key' => $plateKey, 'ip' => $ip]);

            return back()->withErrors(['plaka' => 'Plaka veya şifre hatalı.'])->withInput($request->only('plaka'));
        }
        RateLimiter::clear($pair);
        $request->session()->regenerate();
        $request->session()->put('vehicle_owner', ['id' => $vehicle->id, 'credential_version' => $vehicle->credential_version, 'logged_at' => now()->timestamp]);
        Log::info('Vehicle owner login succeeded', ['vehicle_id' => $vehicle->id, 'ip' => $ip]);

        return redirect()->route('vehicle.owner');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('vehicle_owner');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vehicle.access.form');
    }
}
