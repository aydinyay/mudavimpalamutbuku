<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Yönetim Paneli') — Müdavim</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <a href="{{ url('/') }}" style="color:inherit;text-decoration:none;">Müdavim</a>
        <small>YÖNETİM PANELİ</small>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Özet
        </a>

        <a href="{{ route('admin.bridge.restoran') }}"
           style="background:rgba(234,179,8,.12);border-left:3px solid #ca8a04;margin-bottom:8px;">
            <i class="bi bi-arrow-left-right" style="color:#fbbf24;"></i>
            <strong style="color:#fde68a;">Restoran Sistemi</strong>
        </a>

        <div class="nav-section-label">Operasyon</div>
        @if(config('modules.vehicle'))
        <a href="{{ route('admin.vehicles.index') }}" class="{{ request()->routeIs('admin.vehicles.*') || request()->routeIs('admin.vehicle-parties.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Araçlar
        </a>
        @endif
        <a href="{{ route('admin.reservations.index') }}" class="{{ request()->routeIs('admin.reservations.index') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Rezervasyonlar
        </a>
        <a href="{{ route('admin.reservations.quick') }}"
           class="{{ request()->routeIs('admin.reservations.quick') ? 'active' : '' }}"
           style="background:rgba(22,163,74,.15);border-left:3px solid #16a34a;">
            <i class="bi bi-lightning-fill" style="color:#4ade80;"></i>
            <strong style="color:#4ade80;">Hızlı Rezervasyon</strong>
        </a>
        <a href="{{ route('admin.tables.index') }}" class="{{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i> Masa Planı
        </a>

        <div class="nav-section-label">Menü</div>
        <a href="{{ route('admin.menu.categories.index') }}" class="{{ request()->routeIs('admin.menu.categories.*') ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i> Kategoriler
        </a>
        <a href="{{ route('admin.menu.items.index') }}" class="{{ request()->routeIs('admin.menu.items.*') ? 'active' : '' }}">
            <i class="bi bi-egg-fried"></i> Menü Ürünleri
        </a>
        <a href="{{ route('admin.daily-specials.index') }}" class="{{ request()->routeIs('admin.daily-specials.*') ? 'active' : '' }}">
            <i class="bi bi-sun"></i> Günlük Özel
        </a>
        <a href="{{ route('admin.qrcodes.index') }}" class="{{ request()->routeIs('admin.qrcodes.*') ? 'active' : '' }}">
            <i class="bi bi-qr-code"></i> QR Kodlar
        </a>

        <div class="nav-section-label">İtibar</div>
        <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <i class="bi bi-star-half"></i> Yorumlar & Şikayetler
        </a>
        <a href="{{ route('admin.gallery.index') }}" class="{{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">
            <i class="bi bi-images"></i> Fotoğraf Galerisi
        </a>
        <a href="{{ route('admin.instagram.index') }}" class="{{ request()->routeIs('admin.instagram.*') ? 'active' : '' }}">
            <i class="bi bi-instagram"></i> Instagram Galeri
        </a>

        <div class="nav-section-label">Analitik</div>
        <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> İstatistikler
        </a>

        <a href="{{ route('admin.spotify.index') }}" class="{{ request()->routeIs('admin.spotify.*') ? 'active' : '' }}">
            <i class="bi bi-spotify"></i> Spotify Müzik
        </a>

        <div class="nav-section-label">Sistem</div>
        <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
            <i class="bi bi-images"></i> Görseller
        </a>
        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> Ayarlar
        </a>
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right"></i> Çıkış
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </nav>
</aside>

{{-- Main --}}
<div class="admin-main">
    <header class="admin-topbar">
        <button id="sidebarToggle" class="btn btn-sm btn-light d-lg-none">
            <i class="bi bi-list fs-5"></i>
        </button>
        <h1 class="page-title">@yield('page_title', 'Yönetim Paneli')</h1>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted d-none d-md-inline">{{ auth()->user()->name ?? '' }}</span>
            <a href="{{ route('website.home') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-box-arrow-up-right me-1"></i>Siteyi Gör
            </a>
        </div>
    </header>

    <main class="admin-content">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible alert-auto-dismiss show mb-3">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible alert-auto-dismiss show mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
