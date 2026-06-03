@extends('layouts.website')

@section('title', __('common.nav_contact'))

@section('content')
<div style="padding-top:80px;"></div>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">{{ __('common.nav_contact') }}</h1>
            <div class="section-divider"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 text-center">
                <i class="bi bi-telephone-fill fs-1" style="color:var(--color-sea);"></i>
                <h5 class="mt-2">{{ __('common.phone') }}</h5>
                <a href="tel:{{ config('restaurant.phone') }}" class="text-muted">{{ config('restaurant.phone') }}</a>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-whatsapp fs-1" style="color:#25d366;"></i>
                <h5 class="mt-2">WhatsApp</h5>
                <a href="https://wa.me/{{ ltrim(config('restaurant.whatsapp'),'+') }}" class="text-muted" target="_blank">
                    {{ config('restaurant.whatsapp') }}
                </a>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-geo-alt-fill fs-1" style="color:var(--color-coral);"></i>
                <h5 class="mt-2">{{ __('common.address') }}</h5>
                <p class="text-muted small">{{ config('restaurant.address.' . app()->getLocale()) }}</p>
            </div>
        </div>
        <div class="mt-5" style="border-radius:16px;overflow:hidden;">
            <iframe
                src="https://maps.google.com/maps?q={{ config('restaurant.coordinates.lat') }},{{ config('restaurant.coordinates.lng') }}&z=15&output=embed"
                width="100%" height="400" style="border:0;" allowfullscreen loading="lazy">
            </iframe>
        </div>
    </div>
</section>
@endsection
