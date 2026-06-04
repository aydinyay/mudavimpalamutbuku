@extends('layouts.website')

@section('title', __('common.nav_home'))

@section('content')

{{-- Hero --}}
<section class="hero-section">
    <div class="hero-content">
        <img src="{{ asset('images/logo-dark.png') }}" alt="Müdavim Restaurant"
             style="height:90px;width:auto;display:block;margin:0 auto 18px;filter:drop-shadow(0 2px 8px rgba(0,0,0,.35));">
        <h1>Müdavim Restaurant</h1>
        <p class="tagline">{{ config('restaurant.tagline.' . app()->getLocale()) }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('reservation.public.create') }}"
               class="btn btn-lg" style="background:var(--color-coral);color:#fff;border-radius:30px;padding:12px 32px;font-weight:600;">
                {{ __('common.nav_reserve') }}
            </a>
            <a href="{{ route('menu.public.index') }}"
               class="btn btn-lg btn-outline-light" style="border-radius:30px;padding:12px 32px;">
                {{ __('common.nav_menu') }}
            </a>
        </div>
        <div class="mt-4 d-flex gap-3 justify-content-center">
            <span class="badge bg-dark bg-opacity-50 py-2 px-3">
                <i class="bi bi-wifi me-1"></i>{{ __('common.free_wifi') }}
            </span>
            <span class="badge bg-dark bg-opacity-50 py-2 px-3">
                <i class="bi bi-heart me-1"></i>{{ __('common.pet_friendly') }}
            </span>
            <span class="badge bg-dark bg-opacity-50 py-2 px-3">
                <i class="bi bi-clock me-1"></i>09:00 – 02:00
            </span>
        </div>
    </div>
    {{-- Wave SVG --}}
    <div style="position:absolute;bottom:0;left:0;right:0;">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 60L60 50C120 40 240 20 360 15C480 10 600 20 720 25C840 30 960 30 1080 25C1200 20 1320 10 1380 5L1440 0V60H1380C1320 60 1200 60 1080 60C960 60 840 60 720 60C600 60 480 60 360 60C240 60 120 60 60 60H0Z" fill="#f8f5ef"/>
        </svg>
    </div>
</section>

{{-- About snippet --}}
<section class="py-5">
    <div class="container text-center">
        <h2 class="section-title">Palamutbükü'nde Bir Masa</h2>
        <div class="section-divider"></div>
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <p class="lead text-muted">
                    Datça'nın ucunda, Ege'nin en temiz koylarından birinde, dalgaların sesi eşliğinde yemek.
                    İstanbul kurumsal hayatını bırakıp buraya yerleşen bir ekip, Akdeniz mutfağının en saf halini sunuyor.
                </p>
                <a href="{{ route('website.about') }}" class="btn btn-outline-secondary mt-2">
                    {{ __('common.nav_about') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Featured menu items --}}
@if($featured->isNotEmpty())
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">İmza Lezzetler</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-3">
            @foreach($featured as $item)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                    @if($item->image_path)
                        <img src="{{ $item->image_path }}" class="card-img-top" style="height:200px;object-fit:cover;" alt="{{ $item->name() }}">
                    @else
                        <div style="height:180px;background:linear-gradient(135deg,#1a7fa8,#2ea887);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-egg-fried fs-1 text-white opacity-50"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title fw-700 mb-1">{{ $item->name() }}</h5>
                        <p class="card-text small text-muted mb-2">{{ $item->description() }}</p>
                        <span style="color:var(--color-coral);font-weight:700;">{{ number_format($item->price, 0, ',', '.') }} ₺</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('menu.public.index') }}" class="btn" style="background:var(--color-sea);color:#fff;border-radius:20px;padding:10px 28px;">
                Tüm Menüyü Gör <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>
@endif

{{-- Reservation CTA --}}
<section class="py-5" style="background:var(--color-sea);">
    <div class="container text-center text-white">
        <h2 class="fw-700 mb-2" style="font-size:2rem;">Masanızı Ayırtın</h2>
        <p class="opacity-75 mb-4">Tekne ile gelenler için sahil rezervasyonu da mevcut.</p>
        <a href="{{ route('reservation.public.create') }}"
           class="btn btn-lg" style="background:#fff;color:var(--color-sea);border-radius:30px;padding:14px 36px;font-weight:700;">
            {{ __('reservation.make_reservation') }}
        </a>
        <div class="mt-3">
            <a href="tel:{{ config('restaurant.phone') }}" class="text-white opacity-75 small">
                <i class="bi bi-telephone me-1"></i>{{ config('restaurant.phone') }}
            </a>
        </div>
    </div>
</section>

{{-- Location / Contact --}}
<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title">Bizi Bulun</h2>
                <div class="section-divider" style="margin:0 0 1.5rem;"></div>
                <p class="mb-2">
                    <i class="bi bi-geo-alt-fill me-2" style="color:var(--color-coral);"></i>
                    {{ config('restaurant.address.' . app()->getLocale()) }}
                </p>
                <p class="mb-2">
                    <i class="bi bi-telephone-fill me-2" style="color:var(--color-coral);"></i>
                    <a href="tel:{{ config('restaurant.phone') }}" class="text-dark">{{ config('restaurant.phone') }}</a>
                </p>
                <p class="mb-4">
                    <i class="bi bi-clock-fill me-2" style="color:var(--color-coral);"></i>
                    {{ __('common.open_hours_value') }}
                </p>
                <a href="https://maps.google.com/?q={{ config('restaurant.coordinates.lat') }},{{ config('restaurant.coordinates.lng') }}"
                   target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-map me-1"></i>Haritada Göster
                </a>
            </div>
            <div class="col-lg-6">
                <div style="border-radius:16px;overflow:hidden;height:300px;">
                    <iframe
                        src="https://maps.google.com/maps?q={{ config('restaurant.coordinates.lat') }},{{ config('restaurant.coordinates.lng') }}&z=15&output=embed"
                        width="100%" height="300" style="border:0;" allowfullscreen loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
