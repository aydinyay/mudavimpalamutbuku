@extends('layouts.website')

@section('title', 'Teşekkürler')

@section('content')
<div style="padding-top:80px;background:var(--color-light);min-height:calc(100vh - 80px);">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-sm text-center" style="border-radius:20px;">
                <div class="card-body p-5">
                    <i class="bi bi-heart-fill fs-1 mb-3 d-block" style="color:var(--color-coral);"></i>
                    <h4 class="fw-700 mb-2">Geri Bildiriminiz İçin Teşekkürler</h4>
                    <p class="text-muted mb-4">
                        Görüşleriniz bizim için çok değerli. Daha iyi bir deneyim sunmak için çalışmaya devam edeceğiz.
                    </p>
                    <a href="{{ route('website.home') }}" class="btn"
                       style="background:var(--color-sea);color:#fff;border-radius:12px;padding:10px 28px;">
                        Ana Sayfaya Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
