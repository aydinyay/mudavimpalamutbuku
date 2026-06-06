@extends('layouts.website')

@section('title', 'Galeri')

@push('styles')
<style>
.ig-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
@media(max-width:576px){.ig-grid{grid-template-columns:repeat(2,1fr)}}
.ig-item{position:relative;aspect-ratio:1;overflow:hidden;border-radius:6px;background:#f0f0f0;}
.ig-item img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.ig-item:hover img{transform:scale(1.05)}
.ig-item .ig-overlay{position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .3s;display:flex;align-items:center;justify-content:center}
.ig-item:hover .ig-overlay{background:rgba(0,0,0,.3)}
.ig-item .ig-overlay i{color:#fff;font-size:1.8rem;opacity:0;transition:opacity .3s}
.ig-item:hover .ig-overlay i{opacity:1}
</style>
@endpush

@section('content')
<div style="padding-top:80px;"></div>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">Galeri</h1>
            <div class="section-divider"></div>
        </div>

        @if($posts->isNotEmpty())
            <div class="text-center mb-4">
                <span class="badge py-2 px-3" style="background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045);color:#fff;font-size:.85rem;">
                    <i class="bi bi-instagram me-1"></i>Instagram'dan canlı
                </span>
            </div>
            <div class="ig-grid mb-4">
                @foreach($posts as $post)
                <a href="{{ $post->permalink }}" target="_blank" rel="noopener" class="ig-item">
                    <img src="{{ $post->media_url }}" alt="{{ $post->caption ? mb_substr($post->caption,0,60) : 'Müdavim' }}" loading="lazy">
                    <div class="ig-overlay"><i class="bi bi-instagram"></i></div>
                </a>
                @endforeach
            </div>
            <div class="text-center">
                <a href="{{ config('restaurant.social.instagram') }}" target="_blank" rel="noopener"
                   class="btn btn-outline-secondary" style="border-radius:20px;">
                    <i class="bi bi-instagram me-1"></i>Instagram'da Takip Et
                </a>
            </div>
        @else
            {{-- Instagram token gelmeden önce placeholder --}}
            <div class="ig-grid mb-4">
                @for($i=0;$i<9;$i++)
                <div class="ig-item" style="background:linear-gradient(135deg,#e8f4f2,#d0e8e4);">
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-image text-muted" style="font-size:2rem;opacity:.3;"></i>
                    </div>
                </div>
                @endfor
            </div>
            <div class="text-center">
                <a href="{{ config('restaurant.social.instagram') }}" target="_blank" rel="noopener"
                   class="btn" style="background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045);color:#fff;border-radius:20px;padding:10px 28px;">
                    <i class="bi bi-instagram me-1"></i>Instagram'ı Ziyaret Et
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
