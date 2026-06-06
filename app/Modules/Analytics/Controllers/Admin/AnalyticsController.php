<?php

namespace App\Modules\Analytics\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $service) {}

    public function index(Request $request)
    {
        $period = $request->get('period', '30');

        $to   = Carbon::today()->endOfDay();
        $from = match($period) {
            'today'  => Carbon::today()->startOfDay(),
            '7'      => Carbon::today()->subDays(6)->startOfDay(),
            '90'     => Carbon::today()->subDays(89)->startOfDay(),
            '180'    => Carbon::today()->subDays(179)->startOfDay(),
            default  => Carbon::today()->subDays(29)->startOfDay(),
        };

        $overview    = $this->service->getOverview($from, $to);
        $dailyTrend  = $this->service->getDailyTrend($from, $to);
        $topPages    = $this->service->getTopPages($from, $to);
        $geography   = $this->service->getGeography($from, $to);
        $devices     = $this->service->getDevices($from, $to);
        $sources     = $this->service->getTrafficSources($from, $to);
        $heatmap     = $this->service->getHourlyHeatmap($from, $to);
        $funnel      = $this->service->getConversionFunnel($from, $to);
        $resStats    = $this->service->getReservationStats($from, $to);

        return view('admin.analytics.index', compact(
            'period', 'overview', 'dailyTrend', 'topPages',
            'geography', 'devices', 'sources', 'heatmap', 'funnel', 'resStats'
        ));
    }

    public function trackEvent(Request $request)
    {
        try {
            $sessionId = $request->cookie('_mud_sid');
            if ($sessionId) {
                \App\Models\AnalyticsEvent::create([
                    'session_id' => $sessionId,
                    'event_type' => $request->input('event', 'unknown'),
                    'event_data' => $request->input('data'),
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable) {}

        return response()->json(['ok' => true]);
    }
}
