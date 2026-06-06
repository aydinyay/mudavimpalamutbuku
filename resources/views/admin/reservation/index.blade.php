@extends('layouts.admin')

@section('title', 'Rezervasyonlar')
@section('page_title', 'Rezervasyonlar')

@push('styles')
<style>
/* ---- Takvim ---- */
.cal-wrap{overflow-x:auto;padding-bottom:4px;}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;min-width:560px;}
.cal-head{text-align:center;font-size:.68rem;font-weight:700;letter-spacing:.06em;padding:4px 0;color:#888;}
.cal-head.we{color:#c95d3f;}
.cal-day{
    border-radius:8px;padding:6px 4px 5px;text-align:center;cursor:pointer;
    border:1.5px solid transparent;transition:all .15s;text-decoration:none;display:block;
    background:#f8f9fa;color:#333;
}
.cal-day:hover{border-color:#1a6b5e;background:#f0faf8;color:#1a6b5e;}
.cal-day.today{background:#e6f4f1;border-color:#1a6b5e;}
.cal-day.selected{background:#1a6b5e!important;border-color:#1a6b5e!important;color:#fff!important;}
.cal-day.weekend{background:#fff5f3;}
.cal-day.weekend .cal-num{color:#c95d3f;}
.cal-day.selected .cal-num{color:#fff!important;}
.cal-num{font-size:.82rem;font-weight:700;line-height:1;}
.cal-cnt{
    display:inline-block;
    margin-top:4px;
    background:#1a6b5e;color:#fff;
    font-size:.68rem;font-weight:700;line-height:1;
    border-radius:20px;padding:2px 7px;
    min-width:20px;
}
.cal-day.weekend .cal-cnt{background:#c95d3f;}
.cal-day.selected .cal-cnt{background:rgba(255,255,255,.3);color:#fff;}
.cal-dot{display:none;}
.cal-day.selected.weekend .cal-cnt{color:rgba(255,255,255,.8);}
.cal-month-label{grid-column:1/-1;font-size:.7rem;font-weight:700;color:#aaa;letter-spacing:.08em;text-transform:uppercase;padding:8px 0 2px;}

/* ---- Bölge kartları ---- */
.area-cards{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;}
.area-card{
    flex:1;min-width:120px;
    border-radius:10px;padding:12px 16px;
    background:#f8f9fa;border:1.5px solid #e8e8e8;
}
.area-card .ac-name{font-size:.72rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
.area-card .ac-num{font-size:1.8rem;font-weight:700;color:#1a6b5e;line-height:1.1;}
.area-card .ac-sub{font-size:.7rem;color:#aaa;margin-top:1px;}

/* ---- Liste ---- */
.res-time-block{margin-bottom:4px;}
.res-time-label{font-size:.68rem;font-weight:700;color:#bbb;letter-spacing:.08em;text-transform:uppercase;padding:8px 0 4px;}
.res-row{
    display:grid;grid-template-columns:52px 1fr auto auto 80px 36px;
    align-items:center;gap:8px;
    padding:10px 14px;border-radius:10px;margin-bottom:4px;
    background:#fff;border:1px solid #f0f0f0;
    transition:box-shadow .15s;
}
.res-row:hover{box-shadow:0 2px 8px rgba(0,0,0,.07);}
.res-saat{font-size:.9rem;font-weight:700;color:#1a1a1a;}
.res-name{font-size:.85rem;font-weight:600;color:#1a1a1a;}
.res-phone{font-size:.72rem;color:#aaa;}
.res-kisi{font-size:.78rem;color:#666;white-space:nowrap;}
.res-masa{font-size:.72rem;color:#888;white-space:nowrap;}
.res-actions{display:flex;gap:4px;justify-content:flex-end;}

/* Dolu günler */
.busy-list{display:flex;flex-wrap:wrap;gap:6px;}
.busy-chip{
    display:flex;align-items:center;gap:6px;
    background:#fff;border:1px solid #e8e8e8;border-radius:20px;
    padding:4px 12px;font-size:.75rem;text-decoration:none;color:#333;
    transition:all .15s;
}
.busy-chip:hover{border-color:#1a6b5e;color:#1a6b5e;}
.busy-chip .bc-cnt{background:#1a6b5e;color:#fff;border-radius:10px;padding:1px 7px;font-size:.68rem;font-weight:700;}
</style>
@endpush

@section('content')

{{-- Üst Bar --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="date" name="tarih" class="form-control form-control-sm" value="{{ $date }}" style="width:auto;">
            <button class="btn btn-sm btn-outline-secondary">Git</button>
        </form>
        {{-- Hızlı navigasyon --}}
        <a href="?tarih={{ \Carbon\Carbon::parse($date)->subDay()->toDateString() }}"
           class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
        <a href="?tarih={{ \Carbon\Carbon::today()->toDateString() }}"
           class="btn btn-sm btn-outline-secondary">Bugün</a>
        <a href="?tarih={{ \Carbon\Carbon::parse($date)->addDay()->toDateString() }}"
           class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    </div>
    <a href="{{ route('admin.reservations.create') }}" class="btn btn-sm"
       style="background:var(--color-sea);color:#fff;border-radius:8px;">
        <i class="bi bi-plus me-1"></i>Yeni Rezervasyon
    </a>
</div>

<div class="row g-3">

    {{-- Sol: Takvim + dolu günler --}}
    <div class="col-lg-4">

        {{-- 60 günlük takvim --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
            <div class="card-body p-3">
                <div class="cal-wrap">
                    <div class="cal-grid">
                        @php
                            $days = ['Pt','Sa','Ça','Pe','Cu','Ct','Pz'];
                            foreach($days as $i => $d) {
                                $isWe = $i >= 5;
                                echo '<div class="cal-head'.($isWe?' we':'').'">'.$d.'</div>';
                            }

                            $cursor = $start->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                            $end60  = $start->copy()->addDays(59);
                            $currentMonth = null;

                            while($cursor->lte($end60)) {
                                // Ay etiketi
                                $monthKey = $cursor->format('Y-m');
                                if($monthKey !== $currentMonth && $cursor->gte($start)) {
                                    $currentMonth = $monthKey;
                                    echo '<div class="cal-month-label">'.mb_strtoupper($cursor->locale("tr")->isoFormat("MMMM YYYY")).'</div>';
                                    // Haftanın başı değilse boşluk doldur
                                    $dow = $cursor->dayOfWeekIso - 1; // 0=Mon
                                    for($p=0;$p<$dow;$p++) echo '<div></div>';
                                }

                                if($cursor->lt($start)) { $cursor->addDay(); continue; }

                                $dateStr = $cursor->toDateString();
                                $cnt     = $counts[$dateStr] ?? 0;
                                $isToday = $cursor->isToday();
                                $isSel   = $dateStr === $date;
                                $isWe    = $cursor->isWeekend();
                                $hasRes  = $cnt > 0;

                                $cls = 'cal-day';
                                if($isWe)   $cls .= ' weekend';
                                if($isToday)$cls .= ' today';
                                if($isSel)  $cls .= ' selected';
                                if($hasRes) $cls .= ' has-res';

                                echo '<a href="?tarih='.$dateStr.'" class="'.$cls.'">';
                                echo '<div class="cal-num">'.$cursor->day.'</div>';
                                if($hasRes) echo '<div class="cal-cnt">'.$cnt.'</div>';
                                echo '<div class="cal-dot"></div>';
                                echo '</a>';

                                $cursor->addDay();
                            }
                        @endphp
                    </div>
                </div>
            </div>
        </div>

        {{-- En dolu yaklaşan günler --}}
        @if($busyDays->isNotEmpty())
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body p-3">
                <div class="text-muted mb-2" style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">
                    En Kalabalık Günler
                </div>
                <div class="busy-list">
                    @foreach($busyDays as $bd)
                    @php $bd_date = \Carbon\Carbon::parse($bd->day); @endphp
                    <a href="?tarih={{ $bd->day }}" class="busy-chip {{ $bd->day === $date ? 'border-sea' : '' }}">
                        <span>
                            <span style="font-weight:600;">{{ $bd_date->locale('tr')->isoFormat('D MMM') }}</span>
                            <span class="text-muted ms-1" style="font-size:.68rem;">{{ $bd_date->locale('tr')->isoFormat('ddd') }}</span>
                        </span>
                        <span class="bc-cnt">{{ $bd->cnt }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sağ: Seçili gün detayı --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span style="font-size:1rem;font-weight:700;color:#1a1a1a;">
                            {{ $selected->locale('tr')->isoFormat('D MMMM YYYY, dddd') }}
                        </span>
                        @if($selected->isWeekend())
                            <span class="badge ms-2" style="background:#fff0ed;color:#c95d3f;font-size:.68rem;">Hafta Sonu</span>
                        @else
                            <span class="badge ms-2" style="background:#e6f4f1;color:#1a6b5e;font-size:.68rem;">Hafta İçi</span>
                        @endif
                    </div>
                    <span class="badge" style="background:#1a6b5e;color:#fff;font-size:.78rem;padding:5px 12px;">
                        {{ $reservations->count() }} Rezervasyon
                    </span>
                </div>
            </div>

            <div class="card-body p-3">

                {{-- Bölge özeti --}}
                @if($reservations->isNotEmpty())
                <div class="area-cards">
                    @foreach($byArea as $areaName => $areaRes)
                    <div class="area-card">
                        <div class="ac-name">{{ $areaName }}</div>
                        <div class="ac-num">{{ $areaRes->count() }}</div>
                        <div class="ac-sub">{{ $areaRes->sum('guest_count') }} kişi</div>
                    </div>
                    @endforeach
                    <div class="area-card" style="border-color:#1a6b5e;background:#e6f4f1;">
                        <div class="ac-name" style="color:#1a6b5e;">Toplam</div>
                        <div class="ac-num">{{ $reservations->count() }}</div>
                        <div class="ac-sub">{{ $reservations->sum('guest_count') }} kişi</div>
                    </div>
                </div>
                @endif

                {{-- Rezervasyon listesi --}}
                @if($reservations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-3 opacity-25"></i>
                        Bu gün için rezervasyon yok.
                    </div>
                @else
                    @php $grouped = $reservations->groupBy(fn($r) => substr($r->arrival_time,0,2).':00'); @endphp
                    @foreach($grouped as $hour => $group)
                    <div class="res-time-label">{{ $hour }}</div>
                    @foreach($group as $r)
                    <div class="res-row">
                        <div class="res-saat">{{ substr($r->arrival_time,0,5) }}</div>
                        <div>
                            <div class="res-name">{{ $r->guest_name }}</div>
                            <div class="res-phone">{{ $r->guest_phone }}</div>
                        </div>
                        <div class="res-kisi"><i class="bi bi-people me-1"></i>{{ $r->guest_count }}</div>
                        <div class="res-masa">
                            @if($r->table)
                                <i class="bi bi-grid-3x3-gap me-1"></i>{{ $r->table->table_number }}
                                <span class="text-muted">({{ $r->table->area?->name_tr }})</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div><span class="status-badge {{ $r->statusBadgeClass() }}">{{ __('reservation.status_'.$r->status) }}</span></div>
                        <div class="res-actions">
                            <a href="{{ route('admin.reservations.edit', $r) }}"
                               class="btn btn-sm btn-outline-secondary" style="padding:3px 7px;">
                                <i class="bi bi-pencil" style="font-size:.75rem;"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
