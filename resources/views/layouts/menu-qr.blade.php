<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @isset($qrUuid)
        <meta name="qr-uuid" content="{{ $qrUuid }}">
    @endisset
    <title>{{ config('restaurant.name.' . app()->getLocale()) }} — {{ __('menu.title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/js/menu-qr.js'])
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --c-bg:#fafaf8;
  --c-white:#ffffff;
  --c-dark:#1a1a1a;
  --c-mid:#555;
  --c-light:#888;
  --c-border:#e0ddd6;
  --c-accent:#1a6b5e;
  --c-price:#1a6b5e;
  --c-header:#111;
  --font-serif:'Cormorant Garamond',Georgia,serif;
  --font-sans:'Inter',system-ui,sans-serif;
  --radius:10px;
}
html{font-size:16px;-webkit-text-size-adjust:100%}
body{background:var(--c-bg);color:var(--c-dark);font-family:var(--font-sans);font-weight:400;line-height:1.5;padding-bottom:80px}

/* ─── Header ─────────────────────────────── */
.qr-header{
  position:sticky;top:0;z-index:100;
  background:var(--c-header);
  padding:10px 20px;
  display:flex;align-items:center;justify-content:space-between;
  box-shadow:0 2px 8px rgba(0,0,0,.25);
}
.table-badge{
  background:rgba(255,255,255,.15);color:#fff;
  font-size:.72rem;font-family:var(--font-sans);letter-spacing:.05em;
  padding:4px 10px;border-radius:20px;border:1px solid rgba(255,255,255,.2);
}

/* ─── Search ──────────────────────────────── */
.menu-search-bar{
  background:var(--c-white);
  border-bottom:1px solid var(--c-border);
  padding:10px 16px;
}
.menu-search-bar .search-wrap{position:relative}
.menu-search-bar .bi-search{
  position:absolute;left:12px;top:50%;transform:translateY(-50%);
  color:var(--c-light);font-size:.9rem;pointer-events:none;
}
#menuSearch{
  width:100%;border:1px solid var(--c-border);border-radius:8px;
  padding:9px 14px 9px 36px;font-size:.9rem;font-family:var(--font-sans);
  background:#f5f5f3;color:var(--c-dark);outline:none;
  transition:border-color .2s,background .2s;
}
#menuSearch:focus{border-color:var(--c-accent);background:#fff}
#menuSearch::placeholder{color:#aaa}

/* ─── Allergen filter ─────────────────────── */
.allergen-filter{
  background:var(--c-white);border-bottom:1px solid var(--c-border);
  padding:10px 16px;
}
.allergen-chip{
  display:inline-block;
  background:#f0f7f5;color:var(--c-accent);
  border:1px solid #c8e3de;border-radius:20px;
  font-size:.72rem;padding:3px 10px;margin:2px 3px 2px 0;
  cursor:pointer;transition:background .15s,color .15s;
  font-family:var(--font-sans);
}
.allergen-chip.active,.allergen-chip:hover{background:var(--c-accent);color:#fff;border-color:var(--c-accent)}

/* ─── Category nav ────────────────────────── */
.category-nav{
  display:flex;gap:6px;overflow-x:auto;
  padding:10px 16px;
  background:var(--c-white);border-bottom:1px solid var(--c-border);
  scrollbar-width:none;-ms-overflow-style:none;
}
.category-nav::-webkit-scrollbar{display:none}
.nav-pill{
  flex-shrink:0;
  background:#f0f7f5;color:var(--c-accent);
  border:1px solid #c8e3de;border-radius:20px;
  font-size:.75rem;font-weight:500;padding:5px 13px;
  text-decoration:none;white-space:nowrap;
  transition:background .15s,color .15s;
}
.nav-pill:hover{background:var(--c-accent);color:#fff;border-color:var(--c-accent)}

/* ─── Menu wrapper ────────────────────────── */
.menu-wrapper{max-width:680px;margin:0 auto;padding:0 0 24px}

/* ─── Category section ────────────────────── */
.category-section{padding:0 16px 8px}
.category-heading{
  font-family:var(--font-serif);
  font-size:1.35rem;font-weight:600;
  letter-spacing:.06em;
  color:var(--c-dark);
  padding:22px 0 10px;
  border-bottom:1.5px solid var(--c-dark);
  margin-bottom:0;
}

/* ─── Menu item ───────────────────────────── */
.menu-item{
  display:flex;align-items:flex-start;gap:12px;
  padding:13px 0;
  border-bottom:1px solid var(--c-border);
}
.menu-item:last-child{border-bottom:none}
.menu-item.unavailable{opacity:.45}

.menu-item-thumb{
  width:72px;height:56px;
  object-fit:cover;border-radius:6px;
  flex-shrink:0;
}

.menu-item-body{flex:1;min-width:0}

.menu-item-top{
  display:flex;justify-content:space-between;align-items:baseline;gap:10px;
}
.menu-item-name{
  font-family:var(--font-serif);
  font-size:1.05rem;font-weight:500;
  color:var(--c-dark);line-height:1.3;
  flex:1;
}
.menu-item-price{
  font-family:var(--font-sans);
  font-size:.9rem;font-weight:600;
  color:var(--c-price);white-space:nowrap;
  flex-shrink:0;
}
.price-ask{color:var(--c-light);font-weight:400;font-style:italic;font-size:.82rem}
.menu-item-price-eur{
  font-size:.72rem;color:var(--c-light);margin-top:1px;text-align:right;
}
.menu-item-description{
  font-size:.8rem;color:var(--c-mid);margin-top:4px;line-height:1.5;
  font-style:italic;
}

/* ─── Badges ──────────────────────────────── */
.menu-item-badges{display:flex;flex-wrap:wrap;gap:4px;margin-top:5px}
.menu-badge{
  font-size:.65rem;font-weight:500;text-transform:uppercase;letter-spacing:.04em;
  padding:2px 7px;border-radius:4px;
}
.badge-unavailable{background:#fee2e2;color:#991b1b}
.badge-featured{background:#fef3c7;color:#92400e}
.badge-seasonal{background:#dbeafe;color:#1e40af}
.badge-vegetarian{background:#d1fae5;color:#065f46}
.badge-vegan{background:#dcfce7;color:#166534}
.badge-gf{background:#ede9fe;color:#5b21b6}

/* ─── No results ──────────────────────────── */
.no-results{
  display:none;text-align:center;
  padding:48px 24px;color:var(--c-light);
  font-size:.9rem;font-family:var(--font-sans);
}

/* ─── Bottom bar ──────────────────────────── */
.bottom-bar{
  position:fixed;bottom:0;left:0;right:0;z-index:100;
  background:var(--c-header);
  display:flex;align-items:center;justify-content:space-between;
  padding:10px 16px;
  box-shadow:0 -2px 10px rgba(0,0,0,.2);
}
.locale-btn{
  background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);
  border:1px solid rgba(255,255,255,.15);border-radius:6px;
  font-size:.72rem;font-weight:500;padding:5px 10px;cursor:pointer;
  font-family:var(--font-sans);transition:all .15s;
}
.locale-btn.active,.locale-btn:hover{background:rgba(255,255,255,.9);color:var(--c-header)}
.call-waiter-btn{
  background:var(--c-accent);color:#fff;
  border:none;border-radius:8px;
  font-size:.82rem;font-weight:500;padding:8px 16px;
  cursor:pointer;font-family:var(--font-sans);
  transition:background .15s;
  display:flex;align-items:center;gap:6px;
}
.call-waiter-btn:hover{background:#155a4e}

/* ─── Search hidden / nav active ─────────────── */
.search-hidden{display:none!important}
.nav-pill.active{background:var(--c-accent);color:#fff;border-color:var(--c-accent)}
.allergen-chip.active-exclude{background:var(--c-accent);color:#fff;border-color:var(--c-accent)}

/* ─── d-flex utilities (no Bootstrap dep) ─── */
.d-flex{display:flex}
.align-items-center{align-items:center}
.justify-content-between{justify-content:space-between}
.gap-1{gap:4px}
.small{font-size:.82rem}
.text-muted{color:var(--c-light)}
.mb-1{margin-bottom:4px}
.mt-1{margin-top:4px}
.position-relative{position:relative}
.me-1{margin-right:4px}
</style>
</head>
<body data-table-code="{{ $tableCode ?? '' }}">

{{-- Sticky header --}}
<div class="qr-header">
    <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim" style="height:28px;width:auto;">
    @isset($tableName)
        <span class="table-badge">{{ $tableName }}</span>
    @endisset
</div>

{{-- Search bar --}}
<div class="menu-search-bar">
    <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="menuSearch" placeholder="{{ __('menu.search_placeholder') }}" autocomplete="off">
    </div>
</div>

{{-- Allergen filter bar --}}
@isset($allergens)
@if($allergens->count())
<div class="allergen-filter">
    <div class="small text-muted mb-1">{{ __('menu.filter_allergens') }}:</div>
    @foreach($allergens as $allergen)
        <span class="allergen-chip" data-allergen="{{ $allergen->code }}">
            {{ $allergen->{'name_' . app()->getLocale()} }}
        </span>
    @endforeach
</div>
@endif
@endisset

{{-- Category quick nav --}}
@isset($categories)
<div class="category-nav">
    @foreach($categories as $cat)
        <a class="nav-pill" href="#cat-{{ $cat->id }}">
            {{ $cat->{'name_' . app()->getLocale()} }}
        </a>
    @endforeach
</div>
@endisset

{{-- Main content --}}
<div>
    @yield('content')
</div>

<div class="no-results" id="noResults">{{ __('menu.no_results') }}</div>

{{-- Bottom bar: locale + call waiter --}}
<div class="bottom-bar">
    <div class="d-flex gap-1">
        @foreach(['tr','en','de'] as $loc)
            <button class="locale-btn {{ app()->getLocale() === $loc ? 'active' : '' }}"
                    data-locale="{{ $loc }}">{{ strtoupper($loc) }}</button>
        @endforeach
    </div>
    <button class="call-waiter-btn" onclick="alert('{{ __('menu.call_waiter') }}')">
        <i class="bi bi-bell-fill me-1"></i>{{ __('menu.call_waiter') }}
    </button>
</div>

</body>
</html>
