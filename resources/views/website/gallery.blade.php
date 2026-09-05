@extends('layouts.website')

@section('title', 'Galeri')

@push('styles')
@if((isset($photos) && $photos->isNotEmpty()) || (isset($reviewPhotos) && $reviewPhotos->isNotEmpty()))
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

        @if(isset($reviewPhotos) && $reviewPhotos->isNotEmpty())
        <div class="text-center mt-5 mb-4 pt-4" style="border-top:1px solid #eee;">
            <h2 class="section-title" style="font-size:1.6rem;">Google Yorumlarda Sizlerden Gelenler</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-3" id="review-photo-grid">
            @foreach($reviewPhotos as $review)
                @foreach($review->filenames as $file)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ asset('gallery/reviews/' . $file) }}"
                       class="glightbox d-block gallery-thumb"
                       data-gallery="review-photos"
                       data-title="{{ $review->reviewer_name }} — {{ $review->stars() }}{{ $review->comment ? ' — ' . mb_substr($review->comment, 0, 120) : '' }}">
                        <div style="aspect-ratio:1;overflow:hidden;border-radius:8px;background:#f0f0f0;position:relative;">
                            <img src="{{ asset('gallery/reviews/' . $file) }}"
                                 alt="{{ $review->reviewer_name }} tarafından paylaşılan fotoğraf"
                                 loading="lazy"
                                 style="width:100%;height:100%;object-fit:cover;">
                            <div style="position:absolute;left:0;right:0;bottom:0;padding:8px 44px 8px 10px;background:linear-gradient(to top,rgba(0,0,0,.65),transparent);color:#fff;">
                                <div style="font-size:.78rem;font-weight:600;">{{ $review->reviewer_name }}</div>
                                <div style="font-size:.72rem;color:#f59e0b;">{{ $review->stars() }}</div>
                            </div>
                            @if($review->reviewer_photo)
                            <img src="{{ $review->reviewer_photo }}"
                                 alt="{{ $review->reviewer_name }}"
                                 loading="lazy"
                                 style="position:absolute;right:8px;bottom:8px;width:28px;height:28px;border-radius:6px;object-fit:cover;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3);">
                            @endif
                        </div>
                    </a>
                </div>
                @endforeach
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="https://g.page/r/Cd4zYQe_40RuEBM/review" target="_blank" rel="noopener"
               class="btn btn-outline-secondary" style="border-radius:20px;">
                <i class="bi bi-google me-1"></i>Siz de Google'da yorum bırakın
            </a>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
@if((isset($photos) && $photos->isNotEmpty()) || (isset($reviewPhotos) && $reviewPhotos->isNotEmpty()))
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>GLightbox({ selector: '.glightbox' });</script>
@endif
@endpush
