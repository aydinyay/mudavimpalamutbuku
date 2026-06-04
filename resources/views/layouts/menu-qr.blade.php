<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @isset($qrUuid)
        <meta name="qr-uuid" content="{{ $qrUuid }}">
    @endisset
    <title>{{ config('restaurant.name.' . app()->getLocale()) }} — {{ __('menu.title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/menu-qr.css', 'resources/js/menu-qr.js'])
</head>
<body data-table-code="{{ $tableCode ?? '' }}">

{{-- Sticky header --}}
<div class="qr-header">
    <div class="d-flex justify-content-between align-items-center">
        <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim" style="height:30px;width:auto;">
        @isset($tableName)
            <span class="table-badge">{{ $tableName }}</span>
        @endisset
    </div>
</div>

{{-- Allergen filter bar --}}
@isset($allergens)
@if($allergens->count())
<div class="allergen-filter">
    <div class="small text-muted mb-1">{{ __('menu.filter_allergens') }}:</div>
    @foreach($allergens as $allergen)
        <span class="allergen-chip" data-allergen="{{ $allergen->code }}">
            {{ $allergen->{'name_' . app()->getLocale()} }}
        </span>
    @endforeach
</div>
@endif
@endisset

{{-- Search bar --}}
<div class="menu-search-bar">
    <div class="position-relative">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="menuSearch" placeholder="{{ __('menu.search_placeholder') }}" autocomplete="off">
    </div>
</div>

{{-- Category quick nav --}}
@isset($categories)
<div class="category-nav">
    @foreach($categories as $cat)
        <a class="nav-pill" href="#cat-{{ $cat->id }}">
            {{ $cat->{'name_' . app()->getLocale()} }}
        </a>
    @endforeach
</div>
@endisset

{{-- Main content --}}
<div class="container-fluid px-0">
    @yield('content')
</div>

<div class="no-results" id="noResults">{{ __('menu.no_results') }}</div>

{{-- Bottom bar: locale + call waiter --}}
<div class="bottom-bar">
    <div class="d-flex gap-1">
        @foreach(['tr','en','de'] as $loc)
            <button class="locale-btn {{ app()->getLocale() === $loc ? 'active' : '' }}"
                    data-locale="{{ $loc }}">{{ strtoupper($loc) }}</button>
        @endforeach
    </div>
    <button class="call-waiter-btn" onclick="alert('{{ __('menu.call_waiter') }}')">
        <i class="bi bi-bell-fill me-1"></i>{{ __('menu.call_waiter') }}
    </button>
</div>

</body>
</html>
