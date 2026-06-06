<?php

namespace App\Modules\Analytics\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Modules\Reservation\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getOverview(Carbon $from, Carbon $to): array
    {
        $visitors  = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])->count();
        $pageviews = AnalyticsPageview::whereBetween('created_at', [$from, $to])->count();
        $reservations = Reservation::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')->count();
        $convRate = $visitors > 0 ? round($reservations / $visitors * 100, 1) : 0;

        // Önceki dönem karşılaştırma
        $diff = $from->diffInDays($to) + 1;
        $prevFrom = $from->copy()->subDays($diff);
        $prevVisitors = AnalyticsSession::whereBetween('first_seen_at', [$prevFrom, $from])->count();
        $visitorChange = $prevVisitors > 0 ? round(($visitors - $prevVisitors) / $prevVisitors * 100, 1) : null;

        // Aktif (son 15 dk)
        $activeNow = AnalyticsSession::where('last_seen_at', '>=', now()->subMinutes(15))->count();

        // Yeni vs tekrar gelen
        $returning = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->whereRaw('DATE(first_seen_at) != DATE(last_seen_at)')
            ->count();

        return [
            'visitors'       => $visitors,
            'pageviews'      => $pageviews,
            'reservations'   => $reservations,
            'conv_rate'      => $convRate,
            'visitor_change' => $visitorChange,
            'active_now'     => $activeNow,
            'new_visitors'   => $visitors - $returning,
            'returning'      => $returning,
            'avg_pages'      => $visitors > 0 ? round($pageviews / $visitors, 1) : 0,
        ];
    }

    public function getDailyTrend(Carbon $from, Carbon $to): array
    {
        $sessions = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->selectRaw('DATE(first_seen_at) as day, COUNT(*) as cnt')
            ->groupBy('day')->orderBy('day')
            ->pluck('cnt', 'day');

        $pageviews = AnalyticsPageview::whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->groupBy('day')->orderBy('day')
            ->pluck('cnt', 'day');

        $labels = [];
        $sData  = [];
        $pData  = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $d = $cursor->toDateString();
            $labels[] = $cursor->locale('tr')->isoFormat('D MMM');
            $sData[]  = $sessions[$d] ?? 0;
            $pData[]  = $pageviews[$d] ?? 0;
            $cursor->addDay();
        }

        return ['labels' => $labels, 'sessions' => $sData, 'pageviews' => $pData];
    }

    private const EXCLUDED_PATHS = [
        '/spotify/status', '/images/%', '/favicon%', '/build/%',
        '/wp-login.php', '/wp-admin%', '/xmlrpc.php', '//.%',
    ];

    public function getTopPages(Carbon $from, Carbon $to, int $limit = 20): array
    {
        $q = AnalyticsPageview::whereBetween('created_at', [$from, $to]);
        foreach (self::EXCLUDED_PATHS as $p) {
            $q->where('page_path', 'not like', $p);
        }
        return $q->selectRaw('page_path, COUNT(*) as views, COUNT(DISTINCT session_id) as unique_views')
            ->groupBy('page_path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getGeography(Carbon $from, Carbon $to): array
    {
        $countries = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->whereNotNull('country')
            ->selectRaw('country, country_code, COUNT(*) as cnt')
            ->groupBy('country', 'country_code')
            ->orderByDesc('cnt')
            ->limit(15)
            ->get()->toArray();

        $cities = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->where('country_code', 'TR')
            ->whereNotNull('city')
            ->selectRaw('city, COUNT(*) as cnt')
            ->groupBy('city')
            ->orderByDesc('cnt')
            ->limit(15)
            ->get()->toArray();

        return ['countries' => $countries, 'cities' => $cities];
    }

    public function getDevices(Carbon $from, Carbon $to): array
    {
        $devices = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->selectRaw('device_type, COUNT(*) as cnt')
            ->groupBy('device_type')->orderByDesc('cnt')
            ->pluck('cnt', 'device_type')->toArray();

        $browsers = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->whereNotNull('browser')
            ->selectRaw('browser, COUNT(*) as cnt')
            ->groupBy('browser')->orderByDesc('cnt')
            ->limit(6)->get()->toArray();

        $os = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->whereNotNull('os')
            ->selectRaw('os, COUNT(*) as cnt')
            ->groupBy('os')->orderByDesc('cnt')
            ->limit(6)->get()->toArray();

        return ['devices' => $devices, 'browsers' => $browsers, 'os' => $os];
    }

    public function getTrafficSources(Carbon $from, Carbon $to): array
    {
        return AnalyticsSession::whereBetween('first_seen_at', [$from, $to])
            ->selectRaw('referrer_source, COUNT(*) as cnt')
            ->groupBy('referrer_source')
            ->orderByDesc('cnt')
            ->get()->toArray();
    }

    public function getHourlyHeatmap(Carbon $from, Carbon $to): array
    {
        $rows = AnalyticsPageview::whereBetween('created_at', [$from, $to])
            ->selectRaw('DAYOFWEEK(created_at)-1 as dow, HOUR(created_at) as hour, COUNT(*) as cnt')
            ->groupBy('dow', 'hour')
            ->get();

        $grid = array_fill(0, 7, array_fill(0, 24, 0));
        foreach ($rows as $r) {
            // DAYOFWEEK: 0=Sun,1=Mon... convert to Mon=0..Sun=6
            $day = ($r->dow + 6) % 7;
            $grid[$day][$r->hour] = (int)$r->cnt;
        }

        return $grid;
    }

    public function getConversionFunnel(Carbon $from, Carbon $to): array
    {
        $visitors     = AnalyticsSession::whereBetween('first_seen_at', [$from, $to])->count();
        $menuViews    = AnalyticsPageview::whereBetween('created_at', [$from, $to])
            ->where('page_path', 'LIKE', '/menu%')->count();
        $resViews     = AnalyticsPageview::whereBetween('created_at', [$from, $to])
            ->where('page_path', 'LIKE', '/rezervasyon%')->count();
        $resSubs      = Reservation::whereBetween('created_at', [$from, $to])
            ->where('source', 'website')->count();

        $waClicks     = AnalyticsEvent::whereBetween('created_at', [$from, $to])
            ->where('event_type', 'whatsapp_click')->count();
        $googleClicks = AnalyticsEvent::whereBetween('created_at', [$from, $to])
            ->where('event_type', 'google_review_click')->count();
        $phoneClicks  = AnalyticsEvent::whereBetween('created_at', [$from, $to])
            ->where('event_type', 'phone_click')->count();

        return [
            'visitors'      => $visitors,
            'menu_views'    => $menuViews,
            'res_views'     => $resViews,
            'res_subs'      => $resSubs,
            'wa_clicks'     => $waClicks,
            'google_clicks' => $googleClicks,
            'phone_clicks'  => $phoneClicks,
        ];
    }

    public function getReservationStats(Carbon $from, Carbon $to): array
    {
        $monthly = Reservation::whereBetween('reservation_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE_FORMAT(reservation_date, "%Y-%m") as month, COUNT(*) as cnt, SUM(guest_count) as guests')
            ->groupBy('month')->orderBy('month')
            ->get();

        $bySource = Reservation::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('source, COUNT(*) as cnt')
            ->groupBy('source')->orderByDesc('cnt')
            ->get();

        $byDow = Reservation::whereBetween('reservation_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DAYOFWEEK(reservation_date) as dow, COUNT(*) as cnt')
            ->groupBy('dow')->orderBy('dow')
            ->pluck('cnt', 'dow')->toArray();

        $dowLabels = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
        $dowData = [];
        for ($i = 1; $i <= 7; $i++) {
            $dowData[] = $byDow[$i] ?? 0;
        }

        return [
            'monthly'   => $monthly,
            'by_source' => $bySource,
            'dow_labels'=> $dowLabels,
            'dow_data'  => $dowData,
        ];
    }
}
