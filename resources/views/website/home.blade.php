@extends('layouts.website')

@section('title', __('common.nav_home'))

@section('content')

@if($setting->hero_bg_image)
@push('styles')
<style>.hero-section::before { background-image: url('{{ $setting->hero_bg_image }}') !important; }</style>
@endpush
@endif

{{-- Hero --}}
<section class="hero-section">
    <div class="hero-content">
        <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim Restaurant"
             style="height:140px;width:auto;display:block;margin:0 auto 18px;filter:drop-shadow(0 2px 12px rgba(0,0,0,.4));">
        <h1>Müdavim Restaurant</h1>
        <p class="tagline">{{ config('restaurant.tagline.' . app()->getLocale()) }}</p>

        {{-- Ambient moment line: day · time · Spotify · weather · moon --}}
        <p id="heroAmbient" style="
            font-family:'Cormorant Garamond',Georgia,serif;
            font-style:italic;
            font-size:1rem;
            color:rgba(255,255,255,.72);
            margin-bottom:1.8rem;
            letter-spacing:.04em;
            min-height:1.5em;
        "></p>

        <script>
        (function () {
            const DAYS   = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
            const MONTHS = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran',
                            'Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
            const weather = @json($weather);

            function pad(n) { return String(n).padStart(2, '0'); }

            function nightLabel(code, h) {
                const isNight = h >= 21 || h < 5;   // 21:00 – 04:59
                const isDawn  = h >= 5  && h < 7;   // 05:00 – 06:59 şafak
                if (!isNight && !isDawn) return null;
                if (code === 0) return isDawn ? 'Açık, şafak söküyor' : 'Açık, yıldızlı gece';
                if (code === 1) return isDawn ? 'Açık, şafak yaklaşıyor' : 'Çoğunlukla açık gece';
                if (code === 2) return isDawn ? 'Parçalı bulutlu' : 'Parçalı bulutlu gece';
                if (code === 3) return 'Bulutlu';
                return null;
            }

            function weatherPart() {
                if (!weather) return '';
                const h    = new Date().getHours();
                const over = nightLabel(weather.code, h);
                const lbl  = over ?? weather.label;
                const isNightOrDusk = h >= 20 || h < 7;
                const moon = isNightOrDusk ? weather.moon.split(' ').slice(1).join(' ') : null;
                return 'Hava ' + weather.temp + '°C · ' + lbl + (moon ? '. ' + moon : '') + '…';
            }

            function timeOfDay(h) {
                if (h >= 5  && h < 12) return 'Sabahı';
                if (h >= 12 && h < 15) return 'Öğleden Sonrası';
                if (h >= 15 && h < 18) return 'Akşamüstü';
                if (h >= 18 && h < 22) return 'Akşamı';
                return 'Gecesi';
            }

            function buildLine(song) {
                const now  = new Date();
                const h    = now.getHours();
                const day  = 'Günlerden ' + DAYS[now.getDay()];
                const time = 'Saat ' + pad(h) + ':' + pad(now.getMinutes());
                const w    = weatherPart();
                const greeting = 'Enfes bir ' + MONTHS[now.getMonth()] + ' ' + timeOfDay(h) + '…';

                let out = greeting + '<br>' + day + ' · ' + time + ', ' + w;
                if (song) {
                    out += '<br>Tam şu anda dalga sesleri arasında ♪ ' + song + ' çalıyor…';
                }
                return out;
            }

            const el = document.getElementById('heroAmbient');
            let currentSong = null;

            function render() { el.innerHTML = buildLine(currentSong); }

            async function fetchSpotify() {
                try {
                    const r = await fetch('/spotify/status');
                    if (!r.ok) return;
                    const d = await r.json();
                    if (d.is_playing && d.now_playing) {
                        currentSong = d.now_playing.artists + ' — ' + d.now_playing.name;
                    } else {
                        currentSong = null;
                    }
                } catch (e) {
                    currentSong = null;
                }
                render();
            }

            render();
            setInterval(render, 60000);
            fetchSpotify();
            setInterval(fetchSpotify, 30000);
        })();
        </script>

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
        <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap px-3">
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

{{-- Google Reviews Vitrin --}}
<section class="py-5" style="background:#f8f5ef;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Misafirlerimiz Ne Diyor?</h2>
            <div class="section-divider"></div>
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 488 512" fill="#4285F4"><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>
                <span style="font-size:2rem;font-weight:700;color:#1a1a1a;">{{ $reviewSummary['rating'] }}</span>
                <span style="color:#f59e0b;font-size:1.4rem;">
                    @for($i=1;$i<=5;$i++){{ $i <= round($reviewSummary['rating']) ? '★' : '☆' }}@endfor
                </span>
                <span class="text-muted small">{{ number_format($reviewSummary['total']) }} Google yorumu</span>
            </div>
        </div>

        @if($reviews->isNotEmpty())
        <div class="row g-3">
            @foreach($reviews as $review)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 h-100" style="border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            @if($review->author_photo)
                                <img src="{{ $review->author_photo }}" width="38" height="38"
                                     style="border-radius:50%;object-fit:cover;" alt="{{ $review->author_name }}">
                            @else
                                <div style="width:38px;height:38px;border-radius:50%;background:var(--color-sea);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;">
                                    {{ mb_substr($review->author_name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;font-size:.88rem;color:#1a1a1a;">{{ $review->author_name }}</div>
                                <div style="color:#f59e0b;font-size:.8rem;">{{ $review->stars() }}</div>
                            </div>
                        </div>
                        <p style="font-size:.85rem;color:#555;line-height:1.6;margin:0;">
                            "{{ mb_substr($review->comment, 0, 160) }}{{ mb_strlen($review->comment) > 160 ? '…' : '' }}"
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0 px-4 pb-3">
                        <span class="text-muted" style="font-size:.72rem;">{{ $review->review_time->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-3">
            <p class="text-muted small">Yorumlar yükleniyor...</p>
        </div>
        @endif

        <div class="text-center mt-4">
            <a href="https://g.page/r/Cd4zYQe_40RuEBM/review" target="_blank" rel="noopener"
               class="btn" style="background:#4285F4;color:#fff;border-radius:20px;padding:10px 28px;">
                ★ Siz de Değerlendirin
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
                        src="https://maps.google.com/maps?q=Müdavim+Palamutbükü+Datça&z=17&output=embed"
                        width="100%" height="300" style="border:0;" allowfullscreen loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
