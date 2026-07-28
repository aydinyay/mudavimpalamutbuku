<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $vehicle->plaka }} araç defteri</title>
<style>
:root{--blue:#176b87;--green:#0f9d78;--ink:#17324d;--muted:#64748b;--line:#e5edf2;--paper:#fff;--danger-bg:#fde2e1;--danger-fg:#b3261e;--warn-bg:#ffe8cc;--warn-fg:#b5570a;--neutral-bg:#eef2f5;--neutral-fg:#54646f;--open-bg:#e7f1fb;--open-fg:#1d5b96;--progress-bg:#fff4d6;--progress-fg:#8a6100;--done-bg:#e2f6ec;--done-fg:#0f7a51}
*{box-sizing:border-box}
body{margin:0;background:#f4f8fa;color:var(--ink);font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
.wrap{width:min(1040px,calc(100% - 2rem));margin:2rem auto}

/* Hero */
.hero{position:relative;background:linear-gradient(135deg,var(--blue),var(--green));color:#fff;padding:2rem;border-radius:24px}
.hero form{position:absolute;top:1.5rem;right:1.5rem}
.hero button{background:#fff2;border:1px solid #fff8;color:#fff;border-radius:9px;padding:.6rem 1rem;font-size:.85rem;cursor:pointer}
.hero .eyebrow{color:#d9f5ed;letter-spacing:.06em;font-size:.8rem;font-weight:600}
.hero h1{margin:.3rem 0 .2rem;font-size:2.1rem;letter-spacing:.01em}
.hero .hero-meta{opacity:.92;font-size:1.05rem}

/* Balance strip — deliberately small & secondary */
.balance-strip{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem .75rem;background:var(--paper);border-radius:14px;padding:.65rem 1rem;margin-top:.75rem;box-shadow:0 4px 14px #16324d0d;font-size:.82rem}
.balance-strip .label{text-transform:uppercase;letter-spacing:.05em;font-size:.68rem;font-weight:700;color:var(--muted)}
.balance-chip{background:var(--neutral-bg);color:var(--blue);border-radius:999px;padding:.3rem .75rem;font-weight:700;font-size:.78rem;white-space:nowrap}

/* Cards / listing */
.card{background:var(--paper);border-radius:18px;padding:1.5rem;box-shadow:0 7px 26px #16324d12;margin-top:1rem}
.card-head{margin-bottom:1rem}
.card-head h2{margin:0 0 .2rem;font-size:1.25rem}
.card-head p{margin:0;color:var(--muted);font-size:.88rem}
.sub-title{font-size:.95rem;margin:1.25rem 0 .6rem;color:var(--ink)}
.muted{color:var(--muted)}

.specs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem 1.5rem}
.spec{padding:.4rem 0;border-bottom:1px solid var(--line)}
.spec-label{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);margin-bottom:.2rem}
.spec-value{font-weight:600;font-size:1rem}

.tags{display:flex;flex-wrap:wrap;gap:.5rem}
.tag{background:#e8f3f0;color:var(--blue);border-radius:999px;padding:.4rem .9rem;font-size:.85rem;font-weight:600}

/* Issues — the star of the page */
.issue-list .issue-item{padding:1rem 0;border-bottom:1px solid var(--line)}
.issue-list .issue-item:last-child{border-bottom:none}
.issue-head{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.5rem}
.issue-head strong{font-size:1.02rem}
.issue-badges{display:flex;gap:.4rem;flex-wrap:wrap}
.badge{display:inline-flex;align-items:center;padding:.22rem .65rem;border-radius:999px;font-size:.68rem;font-weight:700;letter-spacing:.03em;text-transform:uppercase;white-space:nowrap}
.badge-danger{background:var(--danger-bg);color:var(--danger-fg)}
.badge-warn{background:var(--warn-bg);color:var(--warn-fg)}
.badge-neutral{background:var(--neutral-bg);color:var(--neutral-fg)}
.badge-open{background:var(--open-bg);color:var(--open-fg)}
.badge-progress{background:var(--progress-bg);color:var(--progress-fg)}
.badge-done{background:var(--done-bg);color:var(--done-fg)}
.issue-meta{margin-top:.3rem;font-size:.85rem}

/* Media — small fixed thumbnails, no more giant images */
.thumbs{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.7rem}
.thumb{display:block;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:var(--neutral-bg);border:1px solid var(--line)}
.thumb img{display:block;width:100%;height:100%;object-fit:cover}
.thumb .doc-chip{display:flex;align-items:center;justify-content:center;text-align:center;height:100%;padding:.5rem;font-size:.72rem;color:var(--muted);font-weight:600;word-break:break-word}

/* Expenses — collapsed by default, de-emphasized */
.expenses-card{padding:1.1rem 1.5rem}
.expenses-card summary{cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.expenses-card summary::-webkit-details-marker{display:none}
.expenses-card summary h2{margin:0;font-size:1.05rem}
.expenses-card summary .toggle-hint{font-size:.78rem;color:var(--muted)}
.expenses-card[open] summary{margin-bottom:.75rem}
.expense-row{padding:.6rem 0;border-bottom:1px solid var(--line);font-size:.9rem}
.expense-row:last-child{border-bottom:none}
.expense-row .top-line{display:flex;flex-wrap:wrap;justify-content:space-between;gap:.5rem;align-items:baseline}
.expense-row .amount{font-weight:800;color:var(--blue)}
.expense-row .muted{font-size:.82rem}

@media (max-width:560px){
  .hero{padding:1.5rem}
  .hero form{position:static;float:right}
  .card{padding:1.1rem}
}
</style>
</head>
<body>
<main class="wrap">

  <section class="hero">
    <form method="post" action="{{ route('vehicle.logout') }}">
      @csrf
      <button>Çıkış</button>
    </form>
    <div class="eyebrow">ARAÇ DEFTERİ</div>
    <h1>{{ $vehicle->plaka }}</h1>
    <div class="hero-meta">{{ $vehicle->marka }} {{ $vehicle->model }} @if($vehicle->model_yili) · {{ $vehicle->model_yili }} @endif · {{ number_format($vehicle->guncel_km,0,',','.') }} km</div>
  </section>

  <div class="balance-strip">
    <span class="label">Bakiye</span>
    @forelse($balances as $b)
      <span class="balance-chip">{{ $b->debtor?->ad }} → {{ $b->payer?->ad }} · {{ number_format($b->toplam,2,',','.') }} ₺</span>
    @empty
      <span class="muted">Açık bakiye yok</span>
    @endforelse
  </div>

  <section class="card">
    <div class="card-head">
      <h2>Araç Bilgileri</h2>
      <p>Tek bakışta özellikler ve donanım</p>
    </div>
    <div class="specs-grid">
      <div class="spec"><span class="spec-label">Sahip</span><span class="spec-value">{{ $vehicle->owner?->ad ?? '—' }}</span></div>
      <div class="spec"><span class="spec-label">Renk</span><span class="spec-value">{{ $vehicle->renk ?? '—' }}</span></div>
      <div class="spec"><span class="spec-label">Yakıt</span><span class="spec-value">{{ $vehicle->yakit_cinsi ?? '—' }}</span></div>
      <div class="spec"><span class="spec-label">Kilometre</span><span class="spec-value">{{ number_format($vehicle->guncel_km,0,',','.') }} km</span></div>
      @php
        $dateStatusClass = fn ($d) => $d ? ($d->isPast() ? 'badge-danger' : ($d->diffInDays(now()) <= 30 ? 'badge-warn' : 'badge-neutral')) : null;
      @endphp
      <div class="spec"><span class="spec-label">Sonraki Bakım</span><span class="spec-value">@if($vehicle->sonraki_bakim_tarihi)<span class="badge {{ $dateStatusClass($vehicle->sonraki_bakim_tarihi) }}">{{ $vehicle->sonraki_bakim_tarihi->format('d.m.Y') }}</span>@else — @endif @if($vehicle->sonraki_bakim_km) · {{ number_format($vehicle->sonraki_bakim_km,0,',','.') }} km @endif</span></div>
      <div class="spec"><span class="spec-label">Sonraki Muayene</span><span class="spec-value">@if($vehicle->sonraki_muayene_tarihi)<span class="badge {{ $dateStatusClass($vehicle->sonraki_muayene_tarihi) }}">{{ $vehicle->sonraki_muayene_tarihi->format('d.m.Y') }}</span>@else — @endif</span></div>
      <div class="spec"><span class="spec-label">Sigorta Bitiş</span><span class="spec-value">@if($vehicle->sigorta_bitis_tarihi)<span class="badge {{ $dateStatusClass($vehicle->sigorta_bitis_tarihi) }}">{{ $vehicle->sigorta_bitis_tarihi->format('d.m.Y') }}</span>@else — @endif</span></div>
    </div>
    <h3 class="sub-title">Donanım</h3>
    <div class="tags">
      @forelse(($vehicle->donanimlar ?? []) as $d)
        <span class="tag">{{ $d }}</span>
      @empty
        <span class="muted">Belirtilmemiş</span>
      @endforelse
    </div>
  </section>

  <section class="card">
    <div class="card-head">
      <h2>Yapılacaklar &amp; Sorunlar</h2>
      <p>Açık görevler ve bildirilen arızalar</p>
    </div>
    <div class="issue-list">
      @forelse($vehicle->issues as $i)
        @php
          $oncelikMap = ['acil' => ['Acil', 'badge-danger'], 'yuksek' => ['Yüksek', 'badge-warn'], 'normal' => ['Normal', 'badge-neutral'], 'dusuk' => ['Düşük', 'badge-neutral']];
          [$oncelikLabel, $oncelikClass] = $oncelikMap[$i->oncelik] ?? [$i->oncelik ?? null, 'badge-neutral'];
          $durumMap = ['acik' => ['Açık', 'badge-open'], 'devam_ediyor' => ['Devam Ediyor', 'badge-progress'], 'tamamlandi' => ['Tamamlandı', 'badge-done']];
          [$durumLabel, $durumClass] = $durumMap[$i->durum] ?? [$i->durum ?? null, 'badge-neutral'];
        @endphp
        <div class="issue-item">
          <div class="issue-head">
            <strong>{{ $i->baslik }}</strong>
            <span class="issue-badges">
              @if($oncelikLabel)<span class="badge {{ $oncelikClass }}">{{ $oncelikLabel }}</span>@endif
              @if($durumLabel)<span class="badge {{ $durumClass }}">{{ $durumLabel }}</span>@endif
            </span>
          </div>
          <div class="issue-meta muted">
            {{ $i->kategori }}
            @if($i->bildirilme_tarihi) · Bildirim: {{ $i->bildirilme_tarihi->format('d.m.Y') }} @endif
            @if($i->cozulme_tarihi) · Çözüm: {{ $i->cozulme_tarihi->format('d.m.Y') }} @endif
            @if($i->yapan_firma) · {{ $i->yapan_firma }} @endif
          </div>
        </div>
      @empty
        <p class="muted">Kayıtlı sorun veya görev yok.</p>
      @endforelse
    </div>
  </section>

  <section class="card">
    <div class="card-head">
      <h2>Medya</h2>
      <p>Küçük resme tıklayınca orijinali yeni sekmede açılır</p>
    </div>
    <div class="thumbs">
      @forelse($vehicle->media as $m)
        <a class="thumb" href="{{ route('vehicle.owner.media',$m) }}" target="_blank" rel="noopener">
          @if(str_starts_with($m->mime_type,'image/'))
            <img src="{{ route('vehicle.owner.media',$m) }}" alt="{{ $m->caption }}" loading="lazy">
          @else
            <span class="doc-chip">{{ $m->caption ?: $m->role }}</span>
          @endif
        </a>
      @empty
        <span class="muted">Kayıtlı medya yok.</span>
      @endforelse
    </div>
  </section>

  <details class="card expenses-card">
    <summary>
      <h2>Masraf Defteri</h2>
      <span class="toggle-hint">Detayları göster</span>
    </summary>
    <div class="expense-list">
      @forelse($vehicle->expenses as $e)
        <div class="expense-row">
          <div class="top-line">
            <strong>{{ $e->tarih->format('d.m.Y') }} · {{ $e->aciklama }}</strong>
            <span class="amount">{{ number_format($e->tutar,2,',','.') }} ₺</span>
          </div>
          <div class="muted">{{ $e->kategori }} · Ödeyen {{ $e->payer?->ad }} @if($e->debtor) · Borçlu {{ $e->debtor->ad }} @endif · {{ $e->mahsup_durumu }}</div>
        </div>
      @empty
        <p class="muted">Kayıt yok.</p>
      @endforelse
    </div>
  </details>

</main>
</body>
</html>
