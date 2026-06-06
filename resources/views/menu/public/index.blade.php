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
.item.hidden{display:none}
.cat-block.hidden{display:none}
.no-res{text-align:center;padding:40px;color:#aaa;display:none;font-size:.9rem}
</style>
@endpush

@section('content')
<div class="menu-page">

  <div class="menu-search">
    <input type="text" id="q" placeholder="Menüde ara..." autocomplete="off">
  </div>

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
