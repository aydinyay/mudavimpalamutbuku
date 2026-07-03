@extends('layouts.website')

@section('title', __('menu.title'))

@push('styles')
<style>
.menu-page{max-width:700px;margin:40px auto;padding:0 16px 40px}
.menu-search{padding:0 0 20px}
.menu-search input{width:100%;border:1px solid #ddd;border-radius:6px;padding:10px 16px;font-size:.9rem;outline:none;background:#f7f7f7}
.menu-search input:focus{border-color:#1a6b5e;background:#fff}
.cat-block{margin-bottom:8px}
.cat-title{font-family:Georgia,serif;font-size:1.2rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:24px 0 8px;border-bottom:1.5px solid #1a1a1a;margin-bottom:4px}
.item{display:flex;justify-content:space-between;align-items:baseline;padding:10px 0;border-bottom:1px solid #ececec;gap:12px}
.item:last-child{border-bottom:none}
.item-name{font-family:Georgia,serif;font-size:1rem;font-weight:500;flex:1;line-height:1.4}
.item-desc{font-size:.78rem;color:#777;font-style:italic;margin-top:2px}
.item-price{font-size:.9rem;font-weight:600;color:#1a6b5e;white-space:nowrap;flex-shrink:0}
.item-unavail{opacity:.4}
.item-thumb{width:56px;height:56px;object-fit:cover;border-radius:6px;flex-shrink:0;margin-right:10px;}
.item.hidden{display:none}
.cat-block.hidden{display:none}
.no-res{text-align:center;padding:40px;color:#aaa;display:none;font-size:.9rem}
.item-meta{font-size:.72rem;color:#999;margin-top:3px;line-height:1.5}
.item-kcal{display:inline-block;color:#b45309;font-weight:600;font-size:.72rem;margin-top:2px}
.allergen-tags{display:flex;flex-wrap:wrap;gap:3px;margin-top:4px}
.allergen-tag{font-size:.65rem;background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:3px;padding:1px 5px}
.ingredients-text{font-size:.68rem;color:#aaa;margin-top:3px;font-style:italic}
.item-detail-toggle{font-size:.68rem;color:#1a6b5e;cursor:pointer;text-decoration:underline;margin-top:3px;display:inline-block;background:none;border:none;padding:0}
.item-detail{display:none;margin-top:6px}
.item-detail.open{display:block}
</style>
@endpush

@section('content')
<div class="menu-page">

  @isset($todaySpecial)
  <div style="background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
    @if($todaySpecial->image_path)
    <img src="{{ asset('daily-specials/' . $todaySpecial->image_path) }}"
         alt="{{ $todaySpecial->title(app()->getLocale()) }}"
         style="width:72px;height:54px;object-fit:cover;border-radius:7px;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.2);">
    @endif
    <div style="flex:1;min-width:0;">
      <div style="font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;opacity:.85;margin-bottom:4px;">🍽 Bugün Ne Var?</div>
      <div style="font-family:Georgia,serif;font-size:1.05rem;font-weight:700;">{{ $todaySpecial->title(app()->getLocale()) }}</div>
      @if($todaySpecial->description(app()->getLocale()))
        <div style="font-size:.82rem;opacity:.9;margin-top:3px;">{{ $todaySpecial->description(app()->getLocale()) }}</div>
      @endif
      @if($todaySpecial->price)
        <div style="margin-top:6px;font-weight:700;font-size:.95rem;">{{ number_format($todaySpecial->price, 0, ',', '.') }} ₺</div>
      @endif
    </div>
  </div>
  @endisset

  {{-- Yasal zorunlu ibare: QR kullanamayan müşteriler için --}}
  <p style="font-size:.68rem;color:#aaa;text-align:center;margin-bottom:16px;line-height:1.5;">
    Bu menüdeki ürün içerikleri, alerjenler ve besin değerlerine karekod ile ulaşabilirsiniz.<br>
    Karekod kullanamayan misafirlerimize bu bilgiler talep halinde ayrıca sunulur.
  </p>

  <div class="menu-search">
    <input type="text" id="q" placeholder="Menüde ara..." autocomplete="off">
  </div>

  @foreach($categories as $category)
    @if($category->items->isNotEmpty())
    <div class="cat-block" data-cat>
      <div class="cat-title">{{ $category->name() }}</div>
      @foreach($category->items as $item)
      @php $locale = app()->getLocale(); @endphp
      <div class="item {{ !$item->is_available ? 'item-unavail' : '' }}" data-name="{{ strtolower($item->name()) }}">
        @if($item->image_path)
        <img src="{{ $item->image_path }}" alt="{{ $item->name() }}" class="item-thumb">
        @endif
        <div style="flex:1;min-width:0;">
          <div class="item-name">{{ $item->name() }}</div>
          @if(trim($item->description() ?? '') !== '')
            <div class="item-desc">{{ $item->description() }}</div>
          @endif

          {{-- Kalori --}}
          @if($item->calories)
            <span class="item-kcal">⚡ {{ $item->calories }} kcal@if($item->serving_size_g) / {{ $item->serving_size_g }}g@endif</span>
          @endif

          {{-- Alerjenler --}}
          @if($item->allergens->isNotEmpty())
            <div class="allergen-tags">
              @foreach($item->allergens as $allergen)
                <span class="allergen-tag">{{ $allergen->name($locale) }}</span>
              @endforeach
            </div>
          @endif

          {{-- İçindekiler ve besin detayı (toggle ile) --}}
          @php
            $ingredients = $item->{'ingredients_' . $locale} ?? $item->ingredients_tr;
            $hasDetail = $ingredients || $item->meat_origin || ($item->protein_g || $item->fat_g || $item->carbs_g);
          @endphp
          @if($hasDetail)
            <button class="item-detail-toggle" onclick="this.nextElementSibling.classList.toggle('open');this.textContent=this.nextElementSibling.classList.contains('open')?'▲ Gizle':'▼ Detay / İçindekiler'">▼ Detay / İçindekiler</button>
            <div class="item-detail">
              @if($item->meat_origin)
                <div class="ingredients-text">🥩 Et kökeni: {{ $item->meat_origin }}</div>
              @endif
              @if($ingredients)
                <div class="ingredients-text">İçindekiler: {{ $ingredients }}</div>
              @endif
              @if($item->protein_g || $item->fat_g || $item->carbs_g)
                <div class="ingredients-text">
                  @if($item->protein_g) P: {{ $item->protein_g }}g &nbsp; @endif
                  @if($item->fat_g) Y: {{ $item->fat_g }}g &nbsp; @endif
                  @if($item->carbs_g) K: {{ $item->carbs_g }}g @endif
                </div>
              @endif
            </div>
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

  <div style="margin-top:40px;padding:24px 0;border-top:1px solid #ececec;text-align:center;">
    <p style="font-size:.82rem;color:#888;margin-bottom:12px;">Deneyiminizi paylaşır mısınız?</p>
    <a href="https://g.page/r/Cd4zYQe_40RuEBM/review" target="_blank" rel="noopener"
       style="display:inline-block;background:#4285F4;color:#fff;text-decoration:none;padding:8px 22px;border-radius:20px;font-size:.82rem;font-weight:600;">
      ★ Google'da Değerlendir
    </a>
    <div style="margin-top:14px;">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https%3A%2F%2Fg.page%2Fr%2FCd4zYQe_40RuEBM%2Freview"
           width="100" height="100" alt="Google Yorum QR" style="border-radius:8px;border:1px solid #eee;">
      <p style="font-size:.7rem;color:#bbb;margin-top:4px;">QR ile de ulaşabilirsiniz</p>
    </div>
  </div>
</div>

@push('scripts')
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
@endpush

@endsection
