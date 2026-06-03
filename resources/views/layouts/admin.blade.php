<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Yönetim Paneli') — Müdavim Şef</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        Müdavim Şef
        <small>YÖNETİM PANELİ</small>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Özet
        </a>

        <div class="nav-section-label">Operasyon</div>
        <a href="{{ route('admin.reservations.index') }}" class="{{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Rezervasyonlar
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
        <a href="{{ route('admin.qrcodes.index') }}" class="{{ request()->routeIs('admin.qrcodes.*') ? 'active' : '' }}">
            <i class="bi bi-qr-code"></i> QR Kodlar
        </a>

        <div class="nav-section-label">Sistem</div>
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
