<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reservation\Models\Reservation;
use App\Modules\TablePlan\Models\Table;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $todayReservations = Reservation::whereDate('reservation_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->with(['table'])
            ->orderBy('arrival_time')
            ->get();

        $stats = [
            'today_count'   => $todayReservations->count(),
            'pending_count' => $todayReservations->where('status', 'pending')->count(),
            'total_guests'  => $todayReservations->sum('guest_count'),
            'tables_active' => Table::where('is_active', true)->count(),
        ];

        return view('admin.dashboard', compact('todayReservations', 'stats'));
    }

    public function settings()
    {
        $settings = \App\Modules\Core\Models\RestaurantSetting::current();
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings()
    {
        $settings = \App\Modules\Core\Models\RestaurantSetting::current();
        $settings->update(request()->except(['_token', '_method']));
        return back()->with('success', 'Ayarlar kaydedildi.');
    }
}
