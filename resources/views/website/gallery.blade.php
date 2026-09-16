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
        {{-- 2026-09-05: yerel galeri fotoğrafları ve Instagram bloğu kaldırıldı —
             Google yorumlarından gelen misafir fotoğrafları tek başına yeterli
             görüldü, sayfanın tek ve ana içeriği bu oldu. --}}
        @if(isset($reviewPhotos) && $reviewPhotos->isNotEmpty())
        <div class="text-center mb-5">
            <h1 class="section-title">Google yorumlarında misafirlerimizin paylaştığı fotoğraflar</h1>
            <div class="section-divider"></div>
        </div>
        <div class="row g-3" id="review-photo-grid">
            @foreach($reviewPhotos as $review)
                @foreach($review->filenames as $file)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ asset('gallery/reviews/' . $file) }}"
                       class="glightbox d-block gallery-thumb"
                       data-gallery="review-photos"
                       data-filename="{{ $file }}"
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
<script>
    GLightbox({ selector: '.glightbox' });

    // Ana sayfadaki yorum fotoğraflarına tıklayınca ?photo=dosya_adi.jpg ile buraya
    // gelinirse, o fotoğrafı otomatik olarak büyük görünümde (lightbox) açar
    // (2026-09-05).
    const requestedPhoto = new URLSearchParams(location.search).get('photo');
    if (requestedPhoto) {
        const target = document.querySelector('.glightbox[data-filename="' + CSS.escape(requestedPhoto) + '"]');
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
        }
    }
</script>
@endif
@endpush
