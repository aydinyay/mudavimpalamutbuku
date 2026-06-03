@extends('layouts.website')

@section('title', __('common.nav_about'))

@section('content')
<div style="padding-top:80px;"></div>

<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div style="background:linear-gradient(135deg,#1a7fa8,#2ea887);border-radius:20px;height:400px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-water fs-1 text-white opacity-50"></i>
                </div>
            </div>
            <div class="col-lg-6">
                <h1 class="section-title">{{ __('common.nav_about') }}</h1>
                <div class="section-divider" style="margin:0 0 1.5rem;"></div>
                <p class="lead">
                    Datça yarımadasının ucunda, Palamutbükü koyunda, denize sıfır bir restoran.
                </p>
                <p class="text-muted">
                    İstanbul'un kurumsal hayatını arkamızda bıraktık. Mutfağa olan tutkumuzu, Ege'nin eşsiz doğasıyla buluşturduk.
                    Her gün, en taze malzemelerle, en samimi hizmetle karşılıyoruz misafirlerimizi.
                </p>
                <p class="text-muted">
                    Simit levrekten baklava tatlısına, sıcak ot tabağından ızgara ahtapota — her tabak bir hikaye anlatıyor.
                    Ağaçların altında, dalgaların sesinde, Akdeniz rüzgarıyla.
                </p>
                <div class="d-flex gap-3 mt-4 flex-wrap">
                    <span class="badge py-2 px-3 fs-6" style="background:var(--color-sand);color:var(--color-dark);">
                        <i class="bi bi-wifi me-1"></i>{{ __('common.free_wifi') }}
                    </span>
                    <span class="badge py-2 px-3 fs-6" style="background:var(--color-sand);color:var(--color-dark);">
                        <i class="bi bi-heart me-1"></i>{{ __('common.pet_friendly') }}
                    </span>
                    <span class="badge py-2 px-3 fs-6" style="background:var(--color-sand);color:var(--color-dark);">
                        <i class="bi bi-umbrella me-1"></i>Şezlong
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
