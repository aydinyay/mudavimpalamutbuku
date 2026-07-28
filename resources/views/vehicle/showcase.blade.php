<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $vehicle->plaka }} · {{ $vehicle->marka }} {{ $vehicle->model }}</title>
<style>
:root{--blue:#176b87;--green:#0f9d78;--ink:#17324d;--muted:#64748b;--line:#e5edf2;--bg:#f2f6f8;--card:#fff;--amber:#c8850f}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;-webkit-font-smoothing:antialiased}
.wrap{width:min(980px,calc(100% - 2rem));margin:0 auto;padding-bottom:3rem}
.topbar{background:#fff;border-bottom:1px solid var(--line);padding:.9rem 0;margin-bottom:1.4rem}
.topbar .wrap{display:flex;align-items:center;justify-content:space-between;padding-bottom:0}
.brand{font-weight:700;letter-spacing:.02em;color:var(--ink);font-size:1.02rem;display:flex;align-items:center;gap:.5rem}
.brand svg{flex:none}
.kicker-pill{background:#eef7f3;color:var(--green);font-size:.72rem;font-weight:700;letter-spacing:.06em;padding:.3rem .7rem;border-radius:99px;text-transform:uppercase}

.gallery-block{background:var(--card);border-radius:20px;box-shadow:0 8px 28px #16324d14;overflow:hidden}
.cover-link{display:block;background:#0d2436}
.cover{width:100%;max-height:440px;height:min(52vw,440px);object-fit:cover;display:block}
.cover-placeholder{width:100%;height:min(52vw,320px);display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--blue),var(--green));color:#ffffffcc}
.cover-placeholder svg{width:64px;height:64px}
.thumb-strip{display:flex;gap:.55rem;padding:.7rem;overflow-x:auto;background:#f7fafb}
.thumb-strip a{flex:none;display:block;width:84px;height:64px;border-radius:10px;overflow:hidden;border:2px solid transparent;background:#0d2436;display:flex;align-items:center;justify-content:center}
.thumb-strip a:hover{border-color:var(--green)}
.thumb-strip img{width:100%;height:100%;object-fit:cover;display:block}
.thumb-strip .video-chip{color:#fff;font-size:.65rem;font-weight:600;text-align:center;padding:0 .3rem;line-height:1.2}

.title-block{background:var(--card);border-radius:20px;box-shadow:0 8px 28px #16324d14;padding:1.5rem 1.6rem;margin-top:1rem;display:flex;flex-wrap:wrap;gap:1.2rem;align-items:flex-start;justify-content:space-between}
.title-main h1{margin:.15rem 0 0;font-size:1.7rem;line-height:1.2}
.title-main .sub{margin:.2rem 0 0;color:var(--muted);font-size:.95rem}
.plate{display:inline-flex;align-items:stretch;border:2px solid #17324d;border-radius:6px;overflow:hidden;font-family:'Arial Narrow',Arial,sans-serif;font-weight:800;letter-spacing:.03em;height:2.1rem;box-shadow:0 2px 5px #16324d22}
.plate span{display:flex;align-items:center}
.plate .tr{background:var(--blue);color:#fff;font-size:.6rem;font-weight:700;padding:0 .35rem;writing-mode:horizontal-tb}
.plate .num{background:#fff;color:#111;padding:0 .7rem;font-size:1.05rem}

.quick-facts{display:flex;flex-wrap:wrap;gap:.6rem}
.fact-pill{display:flex;align-items:center;gap:.45rem;background:#f4f8fa;border:1px solid var(--line);padding:.5rem .85rem;border-radius:12px;font-size:.88rem;font-weight:600;color:var(--ink)}
.fact-pill svg{flex:none;width:17px;height:17px;color:var(--blue)}

.card{background:var(--card);border-radius:18px;padding:1.4rem 1.6rem;box-shadow:0 8px 28px #16324d14;margin-top:1rem}
.card h2{font-size:1.08rem;margin:0 0 1rem;display:flex;align-items:center;gap:.5rem}
.card h2 svg{width:19px;height:19px;color:var(--green)}

.chips{display:flex;flex-wrap:wrap;gap:.55rem}
.chip{display:flex;align-items:center;gap:.4rem;background:#eef7f3;color:#0d6b4e;border:1px solid #d3ecdf;padding:.5rem .85rem;border-radius:99px;font-size:.86rem;font-weight:600}
.chip svg{width:14px;height:14px;flex:none}

.timeline{list-style:none;margin:0;padding:0;border-left:2px solid var(--line);margin-left:.55rem}
.timeline li{position:relative;padding:0 0 1.1rem 1.4rem}
.timeline li:last-child{padding-bottom:0}
.timeline li::before{content:"";position:absolute;left:-.4rem;top:.2rem;width:.65rem;height:.65rem;border-radius:50%;background:var(--green);border:2px solid #fff;box-shadow:0 0 0 2px #d3ecdf}
.timeline .date{font-weight:700;font-size:.92rem}
.timeline .summary{color:var(--muted);font-size:.88rem;margin-top:.15rem}
.empty-note{color:var(--muted);font-size:.9rem;margin:0}

.foot-note{text-align:center;color:var(--muted);font-size:.78rem;margin-top:1.6rem;line-height:1.6}

@media(max-width:640px){
  .title-block{padding:1.15rem}
  .title-main h1{font-size:1.35rem}
  .card{padding:1.1rem 1.15rem}
  .cover{height:56vw}
}
</style>
</head>
<body>

<header class="topbar">
  <div class="wrap">
    <span class="brand">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14M5 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm14 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM5 13l1.6-5.2A2 2 0 0 1 8.5 6.5h7a2 2 0 0 1 1.9 1.3L19 13M5 13h14"/></svg>
      Araç Vitrini
    </span>
    <span class="kicker-pill">Satılık İlan Görünümü</span>
  </div>
</header>

<main class="wrap">

  @php
    $coverMedia = $media->first(fn ($m) => str_starts_with($m->mime_type, 'image/'));
    $restMedia = $coverMedia ? $media->reject(fn ($m) => $m->is($coverMedia)) : $media;
  @endphp

  <section class="gallery-block">
    @if($coverMedia)
      <a class="cover-link" href="{{ route('vehicle.showcase.media', [$vehicle->plate_key, $coverMedia]) }}">
        <img class="cover" src="{{ route('vehicle.showcase.media', [$vehicle->plate_key, $coverMedia]) }}" alt="{{ $coverMedia->caption ?: ($vehicle->marka.' '.$vehicle->model) }}">
      </a>
    @else
      <div class="cover-placeholder">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14M5 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm14 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM5 13l1.6-5.2A2 2 0 0 1 8.5 6.5h7a2 2 0 0 1 1.9 1.3L19 13M5 13h14"/></svg>
      </div>
    @endif

    @if($restMedia->isNotEmpty())
      <div class="thumb-strip">
        @foreach($restMedia as $m)
          <a href="{{ route('vehicle.showcase.media', [$vehicle->plate_key, $m]) }}">
            @if(str_starts_with($m->mime_type, 'image/'))
              <img src="{{ route('vehicle.showcase.media', [$vehicle->plate_key, $m]) }}" alt="{{ $m->caption ?: ($vehicle->marka.' '.$vehicle->model) }}">
            @else
              <span class="video-chip">{{ $m->caption ?: 'Video' }}</span>
            @endif
          </a>
        @endforeach
      </div>
    @endif
  </section>

  <section class="title-block">
    <div class="title-main">
      <span class="plate"><span class="tr">TR</span><span class="num">{{ $vehicle->plaka }}</span></span>
      <h1>{{ $vehicle->marka }} {{ $vehicle->model }}</h1>
      @if($vehicle->model_yili)
        <p class="sub">{{ $vehicle->model_yili }} model {{ $vehicle->marka }} {{ $vehicle->model }}</p>
      @endif
    </div>
    <div class="quick-facts">
      @if($vehicle->model_yili)
        <span class="fact-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          {{ $vehicle->model_yili }} model
        </span>
      @endif
      <span class="fact-pill">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 12l4-4M12 7v1"/></svg>
        {{ number_format($vehicle->guncel_km, 0, ',', '.') }} km
      </span>
      @if($vehicle->renk)
        <span class="fact-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a8 8 0 0 1-1-15.9A5 5 0 0 0 16 9a3 3 0 0 0 3 3 8 8 0 0 1-7 9Z"/></svg>
          {{ $vehicle->renk }}
        </span>
      @endif
    </div>
  </section>

  @if($vehicle->vitrin_aciklama)
    <section class="card">
      <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
        Açıklama
      </h2>
      <p style="white-space:pre-line;margin:0;color:var(--ink);line-height:1.6">{{ $vehicle->vitrin_aciklama }}</p>
    </section>
  @endif

  @if($vehicle->donanimlar)
    <section class="card">
      <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4.2L18.5 21 12 16.8 5.5 21l2-7.8L2 9h7z"/></svg>
        Donanım ve özellikler
      </h2>
      <div class="chips">
        @foreach($vehicle->donanimlar as $donanim)
          <span class="chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            {{ $donanim }}
          </span>
        @endforeach
      </div>
    </section>
  @endif

  <section class="card">
    <h2>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
      Bakım ve servis geçmişi
    </h2>
    @forelse($serviceHistory as $item)
      @if($loop->first)
        <ul class="timeline">
      @endif
      <li>
        <div class="date">{{ $item->tarih->format('d.m.Y') }}</div>
        <div class="summary">{{ $item->public_summary }}</div>
      </li>
      @if($loop->last)
        </ul>
      @endif
    @empty
      <p class="empty-note">Paylaşılmış servis kaydı yok.</p>
    @endforelse
  </section>

  <p class="foot-note">Bu sayfa aracın vitrin görünümüdür. Finansal ve sahiplik bilgileri paylaşılmaz.</p>

</main>
</body>
</html>
