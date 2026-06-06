<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('common.site_name')) — Müdavim Restaurant</title>
    <meta name="description" content="@yield('meta_description', config('restaurant.tagline.' . app()->getLocale()))">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- hreflang for SEO --}}
    <link rel="alternate" hreflang="tr" href="{{ url(request()->path()) }}">
    <link rel="alternate" hreflang="en" href="{{ url('en/' . request()->path()) }}">
    <link rel="alternate" hreflang="de" href="{{ url('de/' . request()->path()) }}">

    @vite(['resources/css/website.css', 'resources/js/website.js'])
    <style>@keyframes ambPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.4)}}</style>
    @stack('styles')
</head>
<body>

{{-- Season banner --}}
@php
    $isOpen = \App\Modules\Core\Services\RestaurantService::isOpen();
@endphp
@unless($isOpen)
    <div class="season-banner">
        {{ __('common.season_closed') }}
    </div>
@endunless

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-mudavim fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ app()->getLocale() === 'tr' ? url('/') : url(app()->getLocale() . '/') }}">
            <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim Restaurant" style="height:56px;width:auto;">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('website.home') }}">{{ __('common.nav_home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('website.about') }}">{{ __('common.nav_about') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('menu.public.index') }}">{{ __('common.nav_menu') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('website.contact') }}">{{ __('common.nav_contact') }}</a></li>
                @php $ambianceSetting = \App\Modules\Core\Models\RestaurantSetting::current(); @endphp
                @if($ambianceSetting->ambiance_page_active ?? true)
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="{{ route('website.ambiance') }}"
                       style="color:rgba(255,255,255,.85);">
                        <span style="width:7px;height:7px;border-radius:50%;background:#1DB954;display:inline-block;animation:ambPulse 2s infinite;"></span>
                        Şu An
                    </a>
                </li>
                @endif
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-sm" style="background:var(--color-coral);color:#fff;border-radius:20px;padding:6px 16px;"
                       href="{{ route('reservation.public.create') }}">
                        {{ __('common.nav_reserve') }}
                    </a>
                </li>
            </ul>
            {{-- Locale switcher --}}
            <div class="locale-switcher ms-lg-3 mt-2 mt-lg-0 d-flex gap-1">
                @foreach(['tr','en','de'] as $loc)
                    <button class="btn btn-sm {{ app()->getLocale() === $loc ? 'btn-light' : 'btn-outline-light' }}"
                            data-locale="{{ $loc }}">
                        {{ strtoupper($loc) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</nav>

{{-- Main content --}}
<main>
    @yield('content')
</main>

{{-- Footer --}}
<footer class="footer-mudavim">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim Restaurant" style="height:56px;width:auto;opacity:0.9;margin-bottom:8px;display:block;">
                <p class="small opacity-75">{{ config('restaurant.tagline.' . app()->getLocale()) }}</p>
                <div class="d-flex gap-3 mt-3 align-items-center">
                    <a href="{{ config('restaurant.social.instagram') }}" target="_blank"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="{{ config('restaurant.social.facebook') }}" target="_blank"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="https://g.page/r/Cd4zYQe_40RuEBM/review" target="_blank" rel="noopener"
                       class="btn btn-sm d-flex align-items-center gap-1"
                       style="background:rgba(255,255,255,0.12);color:#fff;border-radius:20px;padding:4px 12px;font-size:.78rem;border:1px solid rgba(255,255,255,.2);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 488 512" fill="currentColor"><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>
                        Değerlendir
                    </a>
                </div>
            </div>
            <div class="col-lg-4">
                <h6 style="color:#e8d5b0;font-weight:700;" class="mb-3">{{ __('common.address') }}</h6>
                <p class="small opacity-75 mb-1">{{ config('restaurant.address.' . app()->getLocale()) }}</p>
                <p class="small opacity-75 mb-1">
                    <i class="bi bi-telephone-fill me-1"></i>
                    <a href="tel:{{ config('restaurant.phone') }}">{{ config('restaurant.phone') }}</a>
                </p>
                <p class="small opacity-75">
                    <i class="bi bi-clock-fill me-1"></i>{{ __('common.open_hours_value') }}
                </p>
            </div>
            <div class="col-lg-4">
                <h6 style="color:#e8d5b0;font-weight:700;" class="mb-3">{{ __('common.nav_reserve') }}</h6>
                <a href="{{ route('reservation.public.create') }}"
                   class="btn btn-sm" style="background:var(--color-coral);color:#fff;border-radius:20px;">
                    {{ __('common.nav_reserve') }}
                </a>
                <div class="mt-3 d-flex gap-2">
                    <span class="badge bg-secondary"><i class="bi bi-wifi me-1"></i>{{ __('common.free_wifi') }}</span>
                    <span class="badge bg-secondary"><i class="bi bi-heart me-1"></i>{{ __('common.pet_friendly') }}</span>
                </div>
            </div>
        </div>
        <hr class="mt-4" style="border-color:rgba(255,255,255,0.1);">
        <div class="row g-2 mb-3">
            <div class="col-12 text-center">
                <p class="small mb-1" style="opacity:.55;font-size:.75rem;letter-spacing:.03em;">
                    <span style="color:#e8d5b0;font-weight:600;">HK Seyahat Turizm ve Dış Tic. Ltd. Şti.</span>
                    <span class="mx-2 opacity-40">|</span>
                    Halaskargazi Mah. Zafer Sok. 55, Nişantaşı – Şişli / İstanbul
                    <span class="mx-2 opacity-40">|</span>
                    <a href="tel:02122337748" style="color:inherit;text-decoration:none;">0212 233 77 48</a>
                </p>
                <p class="small mb-0" style="opacity:.45;font-size:.72rem;">
                    <a href="https://www.supratur.com" target="_blank" rel="noopener" style="color:#c8b99a;text-decoration:none;">SUPRATUR</a>
                    <span class="mx-1 opacity-40">·</span>
                    <span style="color:#c8b99a;">MÜDAVİM Palamutbükü</span>
                </p>
            </div>
        </div>
        <p class="text-center small opacity-50 mb-0">
            &copy; {{ date('Y') }} Müdavim Restaurant. {{ __('common.footer_rights') }}
        </p>
    </div>
</footer>

{{-- WhatsApp float --}}
<a href="https://wa.me/{{ ltrim(config('restaurant.whatsapp'), '+') }}" target="_blank"
   class="whatsapp-float" title="WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

@stack('scripts')
</body>
</html>
