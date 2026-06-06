@extends('layouts.admin')

@section('title', 'Analitik')
@section('page_title', 'Analitik & İstatistikler')

@push('styles')
<style>
/* ---- Layout ---- */
.analytics-wrap { display:flex; flex-direction:column; gap:20px; }

/* ---- Period Tabs ---- */
.period-tabs { display:flex; gap:6px; flex-wrap:wrap; }
.period-tab {
    padding:6px 16px; border-radius:20px; font-size:.78rem; font-weight:600;
    border:1.5px solid #e0e0e0; background:#fff; color:#666; text-decoration:none;
    transition:all .15s;
}
.period-tab:hover { border-color:#1a6b5e; color:#1a6b5e; }
.period-tab.active { background:#1a6b5e; color:#fff; border-color:#1a6b5e; }

/* ---- KPI Cards ---- */
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; }
.kpi-card {
    background:#fff; border-radius:14px; padding:18px 20px;
    border:1px solid #f0f0f0; box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.kpi-label { font-size:.7rem; font-weight:700; color:#aaa; text-transform:uppercase; letter-spacing:.06em; }
.kpi-value { font-size:2rem; font-weight:800; color:#1a1a1a; line-height:1.1; margin:4px 0 2px; }
.kpi-sub { font-size:.72rem; color:#aaa; }
.kpi-badge { display:inline-block; font-size:.68rem; font-weight:700; padding:2px 8px; border-radius:10px; margin-left:6px; }
.kpi-badge.up { background:#d1fae5; color:#065f46; }
.kpi-badge.down { background:#fee2e2; color:#991b1b; }
.kpi-active { background:linear-gradient(135deg,#1a6b5e,#2d9b87); color:#fff; }
.kpi-active .kpi-label, .kpi-active .kpi-sub { color:rgba(255,255,255,.7); }
.kpi-active .kpi-value { color:#fff; }

/* ---- Section Cards ---- */
.stat-card {
    background:#fff; border-radius:14px; padding:20px;
    border:1px solid #f0f0f0; box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.stat-card-title {
    font-size:.75rem; font-weight:700; color:#aaa; text-transform:uppercase;
    letter-spacing:.07em; margin-bottom:16px; display:flex; align-items:center; gap:8px;
}
.stat-card-title i { color:#1a6b5e; font-size:1rem; }

/* ---- Charts ---- */
.chart-wrap { position:relative; }

/* ---- Top Pages Table ---- */
.page-table { width:100%; font-size:.8rem; border-collapse:collapse; }
.page-table th { font-size:.68rem; font-weight:700; color:#aaa; text-transform:uppercase;
    letter-spacing:.05em; padding:6px 8px; border-bottom:2px solid #f0f0f0; text-align:left; }
.page-table td { padding:8px; border-bottom:1px solid #f8f8f8; color:#333; }
.page-table tr:last-child td { border-bottom:none; }
.page-bar-bg { background:#f0f0f0; border-radius:4px; height:5px; margin-top:4px; }
.page-bar { background:#1a6b5e; border-radius:4px; height:5px; }

/* ---- Geo Table ---- */
.geo-row { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid #f8f8f8; }
.geo-row:last-child { border-bottom:none; }
.geo-flag { font-size:1.2rem; width:24px; text-align:center; }
.geo-name { flex:1; font-size:.82rem; font-weight:600; color:#333; }
.geo-bar-wrap { width:100px; }
.geo-bar-bg { background:#f0f0f0; border-radius:4px; height:6px; }
.geo-bar { background:#1a6b5e; border-radius:4px; height:6px; }
.geo-cnt { font-size:.78rem; font-weight:700; color:#666; width:30px; text-align:right; }

/* ---- Heatmap ---- */
.heatmap-wrap { overflow-x:auto; }
.heatmap { display:grid; grid-template-columns:50px repeat(24,1fr); gap:2px; min-width:600px; }
.hm-cell {
    aspect-ratio:1; border-radius:3px; background:#f5f5f5;
    display:flex; align-items:center; justify-content:center;
    font-size:.55rem; font-weight:700; color:transparent; cursor:default;
    transition:all .1s;
}
.hm-cell:hover { color:#fff; }
.hm-label { font-size:.62rem; color:#aaa; font-weight:600; display:flex; align-items:center; justify-content:flex-end; padding-right:4px; }
.hm-hour { font-size:.6rem; color:#bbb; text-align:center; }

/* ---- Funnel ---- */
.funnel { display:flex; flex-direction:column; gap:8px; }
.funnel-step {
    display:flex; align-items:center; gap:12px;
    padding:10px 14px; border-radius:10px; background:#f8f9fa;
}
.funnel-bar-wrap { flex:1; }
.funnel-bar-bg { background:#e8e8e8; border-radius:4px; height:8px; }
.funnel-bar { background:#1a6b5e; border-radius:4px; height:8px; transition:width .6s; }
.funnel-label { font-size:.78rem; font-weight:600; color:#555; width:160px; }
.funnel-cnt { font-size:.85rem; font-weight:800; color:#1a1a1a; width:50px; text-align:right; }
.funnel-pct { font-size:.7rem; color:#aaa; width:45px; text-align:right; }

/* ---- Source chip ---- */
.source-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:20px; font-size:.72rem; font-weight:700;
    margin:2px;
}
.source-google { background:#fff3f3; color:#c0392b; }
.source-instagram { background:#fdf0ff; color:#8e44ad; }
.source-facebook { background:#f0f4ff; color:#2980b9; }
.source-direct { background:#f0faf8; color:#1a6b5e; }
.source-referral { background:#fef9f0; color:#e67e22; }
.source-twitter { background:#f0f7ff; color:#2471a3; }
.source-tripadvisor { background:#f0fff4; color:#27ae60; }

/* ---- Active dot ---- */
.live-dot { width:8px; height:8px; background:#22c55e; border-radius:50%; display:inline-block; animation:pulse 1.5s infinite; margin-right:4px; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.3)} }

/* ---- Two col grid ---- */
.two-col { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.three-col { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
@media(max-width:768px){.two-col,.three-col{grid-template-columns:1fr;}}
</style>
@endpush

@section('content')
<div class="analytics-wrap">

{{-- Period seçici --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="period-tabs">
        @foreach(['today'=>'Bugün','7'=>'7 Gün','30'=>'30 Gün','90'=>'90 Gün','180'=>'180 Gün'] as $p=>$label)
        <a href="?period={{ $p }}" class="period-tab {{ $period == $p ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <small class="text-muted"><span class="live-dot"></span>Canlı takip aktif</small>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card kpi-active">
        <div class="kpi-label"><span class="live-dot"></span>Şu An Aktif</div>
        <div class="kpi-value">{{ $overview['active_now'] }}</div>
        <div class="kpi-sub">Son 15 dakika</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Ziyaretçi</div>
        <div class="kpi-value">
            {{ number_format($overview['visitors']) }}
            @if($overview['visitor_change'] !== null)
                <span class="kpi-badge {{ $overview['visitor_change'] >= 0 ? 'up' : 'down' }}">
                    {{ $overview['visitor_change'] >= 0 ? '+' : '' }}{{ $overview['visitor_change'] }}%
                </span>
            @endif
        </div>
        <div class="kpi-sub">Seçili dönem</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Sayfa Görüntüleme</div>
        <div class="kpi-value">{{ number_format($overview['pageviews']) }}</div>
        <div class="kpi-sub">Ort. {{ $overview['avg_pages'] }} sayfa/ziyaret</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Rezervasyon</div>
        <div class="kpi-value">{{ $overview['reservations'] }}</div>
        <div class="kpi-sub">Web kaynaklı</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Dönüşüm Oranı</div>
        <div class="kpi-value">%{{ $overview['conv_rate'] }}</div>
        <div class="kpi-sub">Ziyaret → Rezervasyon</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Yeni / Tekrar</div>
        <div class="kpi-value">{{ $overview['new_visitors'] }}</div>
        <div class="kpi-sub">{{ $overview['returning'] }} tekrar gelen</div>
    </div>
</div>

{{-- Günlük Trend --}}
<div class="stat-card">
    <div class="stat-card-title"><i class="bi bi-graph-up"></i> Günlük Trend</div>
    <div class="chart-wrap" style="height:220px;">
        <canvas id="trendChart"></canvas>
    </div>
</div>

{{-- Coğrafya + Kaynaklar --}}
<div class="two-col">

    {{-- Coğrafya --}}
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-geo-alt"></i> Coğrafya</div>

        {{-- Ülkeler --}}
        @if(!empty($geography['countries']))
        <div class="mb-3">
            <div style="font-size:.68rem;font-weight:700;color:#ccc;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Ülkeler</div>
            @php $maxC = $geography['countries'][0]['cnt'] ?? 1; @endphp
            @foreach($geography['countries'] as $c)
            <div class="geo-row">
                <div class="geo-flag" style="font-size:.7rem;font-weight:700;color:#aaa;">{{ $c['country_code'] ?? '??' }}</div>
                <div class="geo-name">{{ $c['country'] ?? 'Bilinmiyor' }}</div>
                <div class="geo-bar-wrap">
                    <div class="geo-bar-bg"><div class="geo-bar" style="width:{{ round($c['cnt']/$maxC*100) }}%"></div></div>
                </div>
                <div class="geo-cnt">{{ $c['cnt'] }}</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Türkiye şehirleri --}}
        @if(!empty($geography['cities']))
        <div>
            <div style="font-size:.68rem;font-weight:700;color:#ccc;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Türkiye — Şehirler</div>
            @php $maxCity = $geography['cities'][0]['cnt'] ?? 1; @endphp
            @foreach($geography['cities'] as $city)
            <div class="geo-row">
                <div class="geo-flag">🏙</div>
                <div class="geo-name">{{ $city['city'] }}</div>
                <div class="geo-bar-wrap">
                    <div class="geo-bar-bg"><div class="geo-bar" style="width:{{ round($city['cnt']/$maxCity*100) }}%"></div></div>
                </div>
                <div class="geo-cnt">{{ $city['cnt'] }}</div>
            </div>
            @endforeach
        </div>
        @endif

        @if(empty($geography['countries']) && empty($geography['cities']))
        <div class="text-center text-muted py-4" style="font-size:.82rem;">Henüz veri yok</div>
        @endif
    </div>

    {{-- Trafik Kaynakları --}}
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-signpost-split"></i> Trafik Kaynakları</div>
        <div class="chart-wrap" style="height:200px;margin-bottom:16px;">
            <canvas id="sourcesChart"></canvas>
        </div>
        <div class="d-flex flex-wrap gap-1 mt-2">
            @php
            $sourceLabels = [
                'google'     => ['Google Arama','source-google'],
                'direct'     => ['Direkt','source-direct'],
                'instagram'  => ['Instagram','source-instagram'],
                'facebook'   => ['Facebook','source-facebook'],
                'referral'   => ['Referans','source-referral'],
                'twitter'    => ['Twitter/X','source-twitter'],
                'tripadvisor'=> ['TripAdvisor','source-tripadvisor'],
            ];
            @endphp
            @foreach($sources as $src)
            @php [$lbl,$cls] = $sourceLabels[$src['referrer_source']] ?? [$src['referrer_source'],'source-direct']; @endphp
            <span class="source-chip {{ $cls }}">{{ $lbl }} <strong>{{ $src['cnt'] }}</strong></span>
            @endforeach
        </div>
    </div>
</div>

{{-- Cihazlar --}}
<div class="three-col">
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-phone"></i> Cihaz Türü</div>
        <div class="chart-wrap" style="height:180px;">
            <canvas id="devicesChart"></canvas>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-browser-chrome"></i> Tarayıcı</div>
        <div class="chart-wrap" style="height:180px;">
            <canvas id="browsersChart"></canvas>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-laptop"></i> İşletim Sistemi</div>
        <div class="chart-wrap" style="height:180px;">
            <canvas id="osChart"></canvas>
        </div>
    </div>
</div>

{{-- Yoğunluk Haritası --}}
<div class="stat-card">
    <div class="stat-card-title"><i class="bi bi-grid"></i> Günlük & Saatlik Yoğunluk Haritası</div>
    <div class="heatmap-wrap">
        <div class="heatmap">
            {{-- Saat başlıkları --}}
            <div></div>
            @for($h=0;$h<24;$h++)<div class="hm-hour">{{ str_pad($h,2,'0',STR_PAD_LEFT) }}</div>@endfor

            @php
            $dowNames = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
            $maxHeat = 1;
            foreach($heatmap as $day) foreach($day as $v) $maxHeat = max($maxHeat, $v);
            @endphp

            @foreach($heatmap as $d => $hours)
            <div class="hm-label">{{ $dowNames[$d] }}</div>
            @foreach($hours as $h => $cnt)
            @php
            $intensity = $maxHeat > 0 ? $cnt / $maxHeat : 0;
            $r = round(26 + (26-26)*$intensity);
            $g = round(107 + (160-107)*$intensity);
            $b = round(94 + (94-94)*$intensity);
            $alpha = $cnt > 0 ? max(0.15, $intensity) : 0.05;
            $bg = $cnt > 0 ? "rgba(26,107,94,{$alpha})" : "#f5f5f5";
            @endphp
            <div class="hm-cell" style="background:{{ $bg }};" title="{{ $dowNames[$d] }} {{ str_pad($h,2,'0',STR_PAD_LEFT) }}:00 — {{ $cnt }} görüntüleme">{{ $cnt > 0 ? $cnt : '' }}</div>
            @endforeach
            @endforeach
        </div>
    </div>
</div>

{{-- En Popüler Sayfalar --}}
<div class="stat-card">
    <div class="stat-card-title"><i class="bi bi-file-earmark-bar-graph"></i> En Çok Ziyaret Edilen Sayfalar</div>
    @if(!empty($topPages))
    @php $maxViews = $topPages[0]['views'] ?? 1; @endphp
    <table class="page-table">
        <thead>
            <tr>
                <th>Sayfa</th>
                <th style="text-align:right;">Görüntüleme</th>
                <th style="text-align:right;">Tekil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topPages as $p)
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $p['page_path'] }}</div>
                    <div class="page-bar-bg"><div class="page-bar" style="width:{{ round($p['views']/$maxViews*100) }}%"></div></div>
                </td>
                <td style="text-align:right;font-weight:700;">{{ $p['views'] }}</td>
                <td style="text-align:right;color:#aaa;">{{ $p['unique_views'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="text-center text-muted py-4" style="font-size:.82rem;">Henüz veri yok</div>
    @endif
</div>

{{-- Dönüşüm Hunisi --}}
<div class="two-col">
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-filter"></i> Dönüşüm Hunisi</div>
        @php $fMax = max(1, $funnel['visitors']); @endphp
        <div class="funnel">
            @foreach([
                ['Ziyaretçi', $funnel['visitors'], $funnel['visitors']],
                ['Menü Görüntüleme', $funnel['menu_views'], $funnel['visitors']],
                ['Rezervasyon Sayfası', $funnel['res_views'], $funnel['visitors']],
                ['Rezervasyon Tamamlama', $funnel['res_subs'], $funnel['visitors']],
            ] as [$label, $val, $base])
            <div class="funnel-step">
                <div class="funnel-label">{{ $label }}</div>
                <div class="funnel-bar-wrap">
                    <div class="funnel-bar-bg">
                        <div class="funnel-bar" style="width:{{ $base>0?round($val/$base*100):0 }}%"></div>
                    </div>
                </div>
                <div class="funnel-cnt">{{ $val }}</div>
                <div class="funnel-pct">%{{ $base>0?round($val/$base*100):0 }}</div>
            </div>
            @endforeach
        </div>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0;">
            <div style="font-size:.68rem;font-weight:700;color:#ccc;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Aksiyon Tıklamaları</div>
            <div class="d-flex gap-3 flex-wrap">
                <div class="text-center">
                    <div style="font-size:1.4rem;font-weight:800;color:#25D366;">{{ $funnel['wa_clicks'] }}</div>
                    <div style="font-size:.68rem;color:#aaa;">WhatsApp</div>
                </div>
                <div class="text-center">
                    <div style="font-size:1.4rem;font-weight:800;color:#4285F4;">{{ $funnel['google_clicks'] }}</div>
                    <div style="font-size:.68rem;color:#aaa;">Google Yorum</div>
                </div>
                <div class="text-center">
                    <div style="font-size:1.4rem;font-weight:800;color:#1a6b5e;">{{ $funnel['phone_clicks'] }}</div>
                    <div style="font-size:.68rem;color:#aaa;">Telefon</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rezervasyon Analitiği --}}
    <div class="stat-card">
        <div class="stat-card-title"><i class="bi bi-calendar-check"></i> Rezervasyon Analitiği</div>
        <div class="chart-wrap" style="height:160px;margin-bottom:16px;">
            <canvas id="resSourceChart"></canvas>
        </div>
        <div style="font-size:.68rem;font-weight:700;color:#ccc;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Haftanın Günlerine Göre</div>
        <div class="chart-wrap" style="height:100px;">
            <canvas id="resDowChart"></canvas>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const SEA   = '#1a6b5e';
const CORAL = '#c95d3f';
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
};

// Trend
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($dailyTrend['labels']),
        datasets: [
            {
                label: 'Ziyaretçi',
                data: @json($dailyTrend['sessions']),
                borderColor: SEA, backgroundColor: 'rgba(26,107,94,.1)',
                fill: true, tension: .35, pointRadius: 2, borderWidth: 2,
            },
            {
                label: 'Sayfa Görüntüleme',
                data: @json($dailyTrend['pageviews']),
                borderColor: CORAL, backgroundColor: 'rgba(201,93,63,.07)',
                fill: true, tension: .35, pointRadius: 2, borderWidth: 2,
            },
        ]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: { font: { size: 11 }, boxWidth: 12, padding: 12 }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { grid: { color: '#f5f5f5' }, ticks: { font: { size: 10 } } }
        }
    }
});

// Traffic Sources
@php
$srcMap = ['google'=>'Google','direct'=>'Direkt','instagram'=>'Instagram','facebook'=>'Facebook','referral'=>'Referans','twitter'=>'Twitter','tripadvisor'=>'TripAdvisor'];
$srcColors = ['#EA4335','#1a6b5e','#E1306C','#1877F2','#e67e22','#1DA1F2','#00AF87'];
$srcLabels = []; $srcData = [];
foreach($sources as $i => $s) {
    $srcLabels[] = $srcMap[$s['referrer_source']] ?? $s['referrer_source'];
    $srcData[] = $s['cnt'];
}
@endphp
new Chart(document.getElementById('sourcesChart'), {
    type: 'doughnut',
    data: {
        labels: @json($srcLabels),
        datasets: [{ data: @json($srcData), backgroundColor: @json($srcColors), borderWidth: 2, borderColor: '#fff' }]
    },
    options: { ...chartDefaults, plugins: { legend: { display: true, position: 'bottom', labels: { font:{size:10}, boxWidth:10, padding:8 } } }, cutout: '65%' }
});

// Devices
@php
$devLabels = ['mobile'=>'Mobil','tablet'=>'Tablet','desktop'=>'Masaüstü'];
$devColors = ['#c95d3f','#e8b4a2','#1a6b5e'];
$dLabels = []; $dData = [];
foreach($devices['devices'] as $type => $cnt) { $dLabels[] = $devLabels[$type] ?? $type; $dData[] = $cnt; }
@endphp
new Chart(document.getElementById('devicesChart'), {
    type: 'doughnut',
    data: {
        labels: @json($dLabels),
        datasets: [{ data: @json($dData), backgroundColor: ['#c95d3f','#e8b4a2','#1a6b5e'], borderWidth:2, borderColor:'#fff' }]
    },
    options: { ...chartDefaults, plugins: { legend: { display:true, position:'bottom', labels:{font:{size:10},boxWidth:10,padding:6} } }, cutout:'60%' }
});

// Browsers
@php
$bLabels = array_column($devices['browsers'], 'browser');
$bData   = array_column($devices['browsers'], 'cnt');
@endphp
new Chart(document.getElementById('browsersChart'), {
    type: 'bar',
    data: {
        labels: @json($bLabels),
        datasets: [{ data: @json($bData), backgroundColor: SEA, borderRadius: 6 }]
    },
    options: { ...chartDefaults, scales: { x:{grid:{display:false},ticks:{font:{size:10}}}, y:{grid:{color:'#f5f5f5'},ticks:{font:{size:10}}} } }
});

// OS
@php
$oLabels = array_column($devices['os'], 'os');
$oData   = array_column($devices['os'], 'cnt');
@endphp
new Chart(document.getElementById('osChart'), {
    type: 'bar',
    data: {
        labels: @json($oLabels),
        datasets: [{ data: @json($oData), backgroundColor: CORAL, borderRadius: 6 }]
    },
    options: { ...chartDefaults, scales: { x:{grid:{display:false},ticks:{font:{size:10}}}, y:{grid:{color:'#f5f5f5'},ticks:{font:{size:10}}} } }
});

// Rezervasyon Kaynağı
@php
$rsLabels = []; $rsData = [];
$rsColorMap = ['website'=>'#1a6b5e','admin'=>'#2980b9','phone'=>'#e67e22','walkin'=>'#8e44ad','boat'=>'#27ae60'];
foreach($resStats['by_source'] as $s) { $rsLabels[] = ucfirst($s['source']); $rsData[] = $s['cnt']; }
@endphp
new Chart(document.getElementById('resSourceChart'), {
    type: 'doughnut',
    data: {
        labels: @json($rsLabels),
        datasets: [{ data: @json($rsData), backgroundColor: Object.values(@json($rsColorMap)), borderWidth:2, borderColor:'#fff' }]
    },
    options: { ...chartDefaults, plugins: { legend: { display:true, position:'right', labels:{font:{size:10},boxWidth:10,padding:6} } }, cutout:'55%' }
});

// Rezervasyon Gün
new Chart(document.getElementById('resDowChart'), {
    type: 'bar',
    data: {
        labels: @json($resStats['dow_labels']),
        datasets: [{ data: @json($resStats['dow_data']), backgroundColor: [SEA,SEA,SEA,SEA,SEA,CORAL,CORAL], borderRadius:5 }]
    },
    options: { ...chartDefaults, scales: { x:{grid:{display:false},ticks:{font:{size:10}}}, y:{grid:{color:'#f5f5f5'},ticks:{font:{size:10},stepSize:1}} } }
});

// Aksiyon tracker
function trackEvent(eventType, data = {}) {
    fetch('{{ route("analytics.event") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
        body: JSON.stringify({ event: eventType, data: data })
    }).catch(() => {});
}
</script>
@endpush
