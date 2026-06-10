@extends('layouts.website')

@section('title', 'Galeri')

@push('styles')
@if(isset($photos) && $photos->isNotEmpty())
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
@endif
<style>
/* Yerel galeri grid */
#gallery-grid .gallery-thumb img {
    transition: transform .35s ease;
}
#gallery-grid .gallery-thumb:hover img {
    transform: scale(1.06);
}

/* Instagram fallback grid */
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

        @if(isset($photos) && $photos->isNotEmpty())

        {{-- Yerel fotoğraf galerisi --}}
        <div class="row g-2" id="gallery-grid">
            @foreach($photos as $photo)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ asset('gallery/' . $photo->filename) }}"
                   class="glightbox d-block gallery-thumb"
                   data-gallery="mudavim"
                   data-title="{{ $photo->altText(app()->getLocale()) }}">
                    <div class="gallery-thumb" style="aspect-ratio:1;overflow:hidden;border-radius:8px;background:#f0f0f0;">
                        <img src="{{ asset('gallery/' . $photo->filename) }}"
                             alt="{{ $photo->altText(app()->getLocale()) }}"
                             loading="lazy"
                             style="width:100%;height:100%;object-fit:cover;">
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        @else

        {{-- Instagram fallback --}}
        @if(isset($posts) && $posts->isNotEmpty())
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
            {{-- Placeholder: ne yerel fotoğraf var ne de Instagram tokeni --}}
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

        @endif
    </div>
</section>
@endsection

@push('scripts')
@if(isset($photos) && $photos->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>GLightbox({ selector: '.glightbox' });</script>
@endif
@endpush
