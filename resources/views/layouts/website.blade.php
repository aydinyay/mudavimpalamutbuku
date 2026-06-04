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
            Müdavim
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
                <h5 style="color:#e8d5b0;font-weight:700;">Müdavim Restaurant</h5>
                <p class="small opacity-75">{{ config('restaurant.tagline.' . app()->getLocale()) }}</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="{{ config('restaurant.social.instagram') }}" target="_blank"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="{{ config('restaurant.social.facebook') }}" target="_blank"><i class="bi bi-facebook fs-5"></i></a>
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
