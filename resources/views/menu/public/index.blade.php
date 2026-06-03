@extends('layouts.menu-qr')

@section('content')

@foreach($categories as $category)
    @if($category->items->isNotEmpty())
    <div id="cat-{{ $category->id }}" class="category-section">
        <h2>{{ $category->icon_emoji }} {{ $category->name() }}</h2>

        @foreach($category->items as $item)
            <div class="menu-card"
                 data-allergens='@json($item->allergenCodes())'
                 style="{{ !$item->is_available ? 'opacity:0.5;' : '' }}">

                @if($item->image_path)
                    <img src="{{ $item->image_path }}" class="menu-card-img" alt="{{ $item->name() }}" loading="lazy">
                @else
                    <div class="menu-card-img d-flex align-items-center justify-content-center"
                         style="background:linear-gradient(135deg,#e8d5b0,#c8b580);">
                        <span style="font-size:2rem;">{{ $category->icon_emoji }}</span>
                    </div>
                @endif

                <div class="menu-card-body">
                    <div class="item-name">{{ $item->name() }}</div>
                    @if($item->description())
                        <div class="item-description">{{ $item->description() }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-end">
                        <div class="item-badges">
                            @if(!$item->is_available)
                                <span class="badge badge-unavailable">{{ __('menu.unavailable') }}</span>
                            @endif
                            @if($item->is_featured)
                                <span class="badge badge-featured">★</span>
                            @endif
                            @if($item->is_seasonal)
                                <span class="badge badge-seasonal">{{ __('menu.seasonal') }}</span>
                            @endif
                            @if($item->is_vegetarian)
                                <span class="badge badge-vegetarian">🌿</span>
                            @endif
                            @if($item->is_vegan)
                                <span class="badge badge-vegan">🌱</span>
                            @endif
                            @if($item->is_gluten_free)
                                <span class="badge badge-gf">GF</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="item-price">{{ number_format($item->price, 0, ',', '.') }} ₺</div>
                            @if($item->price_eur)
                                <div class="small text-muted">≈ €{{ number_format($item->price_eur, 0) }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Allergens --}}
                    @if($item->allergens->isNotEmpty())
                        <div class="mt-1">
                            @foreach($item->allergens as $allergen)
                                <span class="allergen-chip" style="font-size:0.7rem;padding:2px 6px;cursor:default;">
                                    {{ $allergen->name() }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif
@endforeach

@endsection
