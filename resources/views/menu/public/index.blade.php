@extends('layouts.menu-qr')

@section('content')
<div class="menu-wrapper">

@foreach($categories as $category)
    @if($category->items->isNotEmpty())
    <div id="cat-{{ $category->id }}" class="category-section">

        <div class="category-heading">
            <h2>{{ $category->name() }}</h2>
        </div>

        @foreach($category->items as $item)
        @php
            $desc = trim($item->description() ?? '');
            $hasDesc = strlen($desc) > 1;
        @endphp
        <div class="menu-item {{ !$item->is_available ? 'unavailable' : '' }}"
             data-allergens='@json($item->allergenCodes())'>

            {{-- Thumbnail (only when image exists) --}}
            @if($item->image_path)
                <img src="{{ $item->image_path }}"
                     class="menu-item-thumb"
                     alt="{{ $item->name() }}"
                     loading="lazy">
            @endif

            <div class="menu-item-body">
                {{-- Name + dotted leader + price --}}
                <div class="menu-item-top">
                    <span class="menu-item-name">{{ $item->name() }}</span>
                    <span class="menu-item-dots"></span>
                    @if($item->is_seasonal && $item->price == 0)
                        <span class="menu-item-price price-ask">Sorunuz</span>
                    @else
                        <span class="menu-item-price">{{ number_format($item->price, 0, ',', '.') }} ₺</span>
                    @endif
                </div>

                {{-- EUR price --}}
                @if($item->price_eur)
                    <div class="menu-item-price-eur">≈ €{{ number_format($item->price_eur, 0) }}</div>
                @endif

                {{-- Description --}}
                @if($hasDesc)
                    <div class="menu-item-description">{{ $desc }}</div>
                @endif

                {{-- Badges --}}
                @php
                    $hasBadge = !$item->is_available || $item->is_featured || $item->is_seasonal || $item->is_vegetarian || $item->is_vegan || $item->is_gluten_free;
                @endphp
                @if($hasBadge)
                <div class="menu-item-badges">
                    @if(!$item->is_available)
                        <span class="menu-badge badge-unavailable">{{ __('menu.unavailable') }}</span>
                    @endif
                    @if($item->is_featured)
                        <span class="menu-badge badge-featured">{{ __('menu.featured') }}</span>
                    @endif
                    @if($item->is_seasonal)
                        <span class="menu-badge badge-seasonal">{{ __('menu.seasonal') }}</span>
                    @endif
                    @if($item->is_vegetarian)
                        <span class="menu-badge badge-vegetarian">{{ __('menu.vegetarian') }}</span>
                    @endif
                    @if($item->is_vegan)
                        <span class="menu-badge badge-vegan">{{ __('menu.vegan') }}</span>
                    @endif
                    @if($item->is_gluten_free)
                        <span class="menu-badge badge-gf">Gluten Free</span>
                    @endif
                </div>
                @endif

                {{-- Allergens --}}
                @if($item->allergens->isNotEmpty())
                    <div class="mt-1">
                        @foreach($item->allergens as $allergen)
                            <span class="allergen-chip" style="font-size:0.68rem;padding:2px 7px;cursor:default;">
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

</div>
@endsection
