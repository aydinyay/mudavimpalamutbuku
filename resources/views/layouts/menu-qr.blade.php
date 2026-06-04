<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('restaurant.name.' . app()->getLocale()) }} — Menü</title>
<style>
/* Critical inline — CDN gelmeden önce navbar sabit olsun */
body{padding-top:60px}
nav.navbar-mudavim{position:fixed!important;top:0!important;left:0!important;right:0!important;z-index:1030!important;background:rgba(30,32,35,.95)!important;backdrop-filter:blur(8px)}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#faf9f6;font-family:'Inter',sans-serif;color:#1a1a1a;padding-bottom:20px;padding-top:60px}
.navbar-mudavim{background:rgba(30,32,35,.95)!important;backdrop-filter:blur(8px)}
.navbar-mudavim .nav-link{color:rgba(255,255,255,.85)!important;font-size:.9rem;letter-spacing:.5px}
.navbar-mudavim .nav-link:hover{color:#e8d5b0!important}
.search-wrap{padding:10px 16px;background:#fff;border-bottom:1px solid #e5e5e5}
.search-wrap input{width:100%;border:1px solid #ddd;border-radius:6px;padding:9px 14px;font-size:.9rem;font-family:'Inter',sans-serif;outline:none;background:#f7f7f7}
.search-wrap input:focus{border-color:#1a6b5e;background:#fff}
.menu-body{max-width:700px;margin:0 auto;padding:0 16px}
.cat-block{margin-top:28px}
.cat-title{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;padding-bottom:8px;border-bottom:1.5px solid #1a1a1a;margin-bottom:4px}
.item{display:flex;justify-content:space-between;align-items:baseline;padding:10px 0;border-bottom:1px solid #ececec;gap:12px}
.item:last-child{border-bottom:none}
.item-name{font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;flex:1}
.item-desc{font-size:.78rem;color:#777;font-style:italic;margin-top:2px}
.item-price{font-size:.9rem;font-weight:600;color:#1a6b5e;white-space:nowrap;flex-shrink:0}
.item-unavail{opacity:.4}
.hidden{display:none}
.no-res{text-align:center;padding:40px;color:#aaa;display:none}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-mudavim fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('website.home') }}">
            <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim Restaurant" style="height:36px;width:auto;">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('website.home') }}">{{ __('common.nav_home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('website.about') }}">{{ __('common.nav_about') }}</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('menu.public.index') }}">{{ __('common.nav_menu') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('website.contact') }}">{{ __('common.nav_contact') }}</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-sm" style="background:#c95d3f;color:#fff;border-radius:20px;padding:6px 16px;"
                       href="{{ route('reservation.public.create') }}">
                        {{ __('common.nav_reserve') }}
                    </a>
                </li>
            </ul>
            <div class="locale-switcher ms-lg-3 mt-2 mt-lg-0 d-flex gap-1">
                @foreach(['tr','en','de'] as $loc)
                    <a href="{{ $loc === 'tr' ? url('/menu') : url($loc.'/menu') }}"
                       class="btn btn-sm {{ app()->getLocale() === $loc ? 'btn-light' : 'btn-outline-light' }}">
                        {{ strtoupper($loc) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>

<div class="search-wrap">
  <input type="text" id="q" placeholder="Menüde ara..." autocomplete="off">
</div>

<div class="menu-body">
@foreach($categories as $category)
  @if($category->items->isNotEmpty())
  <div class="cat-block" data-cat>
    <div class="cat-title">{{ $category->name() }}</div>
    @foreach($category->items as $item)
    <div class="item {{ !$item->is_available ? 'item-unavail' : '' }}" data-name="{{ strtolower($item->name()) }}">
      <div>
        <div class="item-name">{{ $item->name() }}</div>
        @if(trim($item->description() ?? '') !== '')
          <div class="item-desc">{{ $item->description() }}</div>
        @endif
      </div>
      <div class="item-price">
        @if($item->is_seasonal && $item->price == 0)
          Sorunuz
        @else
          {{ number_format($item->price, 0, ',', '.') }} ₺
        @endif
      </div>
    </div>
    @endforeach
  </div>
  @endif
@endforeach
<div class="no-res" id="noRes">Sonuç bulunamadı.</div>
</div>

<script>
var inp = document.getElementById('q');
inp.addEventListener('input', function(){
  var q = inp.value.trim().toLowerCase();
  var any = false;
  document.querySelectorAll('[data-cat]').forEach(function(cat){
    var vis = false;
    cat.querySelectorAll('.item').forEach(function(item){
      var match = !q || item.dataset.name.includes(q);
      item.classList.toggle('hidden', !match);
      if(match) vis = true;
    });
    cat.classList.toggle('hidden', !vis);
    if(vis) any = true;
  });
  document.getElementById('noRes').style.display = (!q||any) ? 'none' : 'block';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
