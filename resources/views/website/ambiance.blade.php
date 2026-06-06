@extends('layouts.website')
@section('title', 'Şu An Müdavim\'de')
@section('meta_description', 'Palamutbükü\'nde şu an ne çalıyor, hava nasıl, deniz ne durumda — canlı atmosfer.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&display=optional" rel="stylesheet">
<style>
/* ── SCROLLBAR GUTTER — navbar shift'i önler ── */
html {
    scrollbar-gutter: stable;
}

:root {
    --serif: 'Cormorant Garamond', Georgia, serif;
    --sans:  'Inter', system-ui, sans-serif;
    --transition-sky: 12s linear;
}

body {
    font-family: var(--sans);
    background: #020610 !important;
    color: #fff;
}

@keyframes ambPulse {
    0%,100% { opacity:1; box-shadow:0 0 0 0 rgba(29,185,84,.5); }
    50%      { opacity:.7; box-shadow:0 0 0 4px rgba(29,185,84,0); }
}

/* ── SKY ── */
#sky {
    position: fixed;
    inset: 0;
    z-index: 0;
    transition: background var(--transition-sky);
    /* will-change:background kaldırıldı — fixed element positioning'i bozuyordu */
}

#star-canvas {
    position: fixed;
    inset: 0;
    z-index: 1;
    pointer-events: none;
}

#horizon-glow {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 35vh;
    z-index: 2;
    pointer-events: none;
    opacity: 0;
    transition: opacity var(--transition-sky), background var(--transition-sky);
}

/* ── CONTENT WRAPPER ── */
#page {
    position: relative;
    z-index: 10;
}

/* ── HERO ── */
#hero-section {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 110px 24px 80px;
    position: relative;
}

.hero-title {
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-weight: 700;
    font-size: clamp(2rem, 5vw, 4rem);
    letter-spacing: -.01em;
    line-height: 1.1;
    color: #fff;
    text-shadow: 0 2px 16px rgba(0,0,0,.4);
    margin-bottom: 1.2rem;
    margin-top: .4rem;
}

.hero-sub {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1rem;
    color: rgba(255,255,255,.5);
    letter-spacing: .05em;
    margin-bottom: .5rem;
}

.hero-ambient {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.05rem;
    color: rgba(255,255,255,.72);
    letter-spacing: .03em;
    line-height: 2;
    margin-bottom: 1.8rem;
    min-height: 2em;
}

.hero-music-intro {
    font-family: var(--serif);
    font-style: italic;
    font-size: .98rem;
    color: rgba(255,255,255,.5);
    letter-spacing: .04em;
    margin-bottom: 1rem;
}

@media(min-width:768px) {
    .hero-sub       { font-size: 1.1rem; }
    .hero-ambient   { font-size: 1.15rem; line-height: 2; }
    .hero-music-intro { font-size: 1.05rem; }
}

.hero-song-card {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    background: rgba(0,0,0,.35);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 16px;
    padding: 12px 20px 12px 12px;
    margin-bottom: 2rem;
}
.hero-song-card img {
    width: 52px; height: 52px;
    border-radius: 8px;
    object-fit: cover;
}
.hero-song-name {
    font-size: .95rem;
    font-weight: 600;
    color: #fff;
    text-align: left;
}
.hero-song-artist {
    font-size: .78rem;
    color: rgba(255,255,255,.55);
    text-align: left;
    margin-top: 2px;
}

/* Spotify hero pill */
#hero-spotify {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    background: rgba(0,0,0,.35);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50px;
    padding: 10px 20px 10px 12px;
    font-size: .85rem;
    color: rgba(255,255,255,.85);
    transition: opacity .5s;
    max-width: 420px;
    text-align: left;
}

#hero-spotify img {
    width: 40px; height: 40px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
}

#hero-spotify .note { font-size: 1rem; flex-shrink: 0; }
.spotify-track { font-weight: 500; line-height: 1.3; }
.spotify-artist { font-size: .75rem; color: rgba(255,255,255,.55); }

/* Scroll indicator */
.scroll-cue {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    opacity: .4;
    animation: scrollBounce 2s infinite;
}
.scroll-cue span {
    display: block;
    width: 1px;
    height: 40px;
    background: linear-gradient(to bottom, transparent, rgba(255,255,255,.7));
}
@keyframes scrollBounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50%       { transform: translateX(-50%) translateY(6px); }
}

/* ── GLASS SECTION ── */
.glass-section {
    backdrop-filter: blur(20px);
    background: rgba(0,0,0,.55);
    border-top: 1px solid rgba(255,255,255,.07);
    padding: 64px 0;
}

.section-inner {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 24px;
}

.section-label {
    font-family: var(--sans);
    font-size: .7rem;
    font-weight: 500;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: rgba(255,255,255,.35);
    margin-bottom: 2rem;
}

/* ── SPOTIFY DETAIL ── */
#spotify-section .now-playing-card {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 28px;
    align-items: center;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 20px;
}

@media (max-width: 600px) {
    #spotify-section .now-playing-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

#album-art {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 8px 40px rgba(0,0,0,.5);
}

.album-placeholder {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 12px;
    background: linear-gradient(135deg, #1a3a60, #2a6080);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: rgba(255,255,255,.3);
}

.track-name {
    font-family: var(--serif);
    font-size: clamp(1.3rem, 3vw, 1.9rem);
    font-weight: 400;
    line-height: 1.2;
    color: #fff;
    margin-bottom: 4px;
}

.track-artist {
    font-size: .9rem;
    color: rgba(255,255,255,.55);
    margin-bottom: 6px;
}

.track-album {
    font-size: .78rem;
    color: rgba(255,255,255,.3);
    font-style: italic;
    margin-bottom: 20px;
}

.progress-bar-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: .72rem;
    color: rgba(255,255,255,.4);
}

.progress-bar-bg {
    flex: 1;
    height: 3px;
    background: rgba(255,255,255,.15);
    border-radius: 2px;
    overflow: hidden;
}

#progress-fill {
    height: 100%;
    background: rgba(255,255,255,.7);
    border-radius: 2px;
    transition: width .5s linear;
}

.queue-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
@media(max-width:500px){ .queue-grid { grid-template-columns: 1fr; } }

.queue-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 12px;
    padding: 12px 14px;
}

.queue-card img {
    width: 36px; height: 36px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
}

.queue-label {
    font-size: .65rem;
    color: rgba(255,255,255,.3);
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-bottom: 2px;
}

.queue-name { font-size: .82rem; color: rgba(255,255,255,.85); line-height: 1.3; }
.queue-artist { font-size: .72rem; color: rgba(255,255,255,.4); }

.recent-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.recent-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 10px;
    transition: background .2s;
}
.recent-item:hover { background: rgba(255,255,255,.05); }
.recent-item img { width: 32px; height: 32px; border-radius: 5px; object-fit: cover; }
.recent-name { font-size: .83rem; color: rgba(255,255,255,.8); }
.recent-artist { font-size: .72rem; color: rgba(255,255,255,.4); }
.recent-time { margin-left: auto; font-size: .7rem; color: rgba(255,255,255,.25); white-space: nowrap; }

/* ── WEATHER ── */
#weather-section .weather-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 28px;
}
@media(max-width:600px){ #weather-section .weather-grid { grid-template-columns: 1fr; } }

.weather-card-main {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px;
    padding: 28px 24px;
}

.weather-temp {
    font-family: var(--serif);
    font-size: 4rem;
    font-weight: 300;
    line-height: 1;
    color: #fff;
}

.weather-feels {
    font-size: .8rem;
    color: rgba(255,255,255,.4);
    margin-bottom: 16px;
}

.weather-label-big {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.15rem;
    color: rgba(255,255,255,.75);
    margin-bottom: 16px;
}

.weather-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.weather-detail-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .8rem;
    color: rgba(255,255,255,.5);
}

.weather-card-sea {
    background: rgba(30, 80, 130, .25);
    border: 1px solid rgba(100, 180, 220, .15);
    border-radius: 20px;
    padding: 28px 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.sea-temp {
    font-family: var(--serif);
    font-size: 3.5rem;
    font-weight: 300;
    color: #a8daf0;
    line-height: 1;
    margin-bottom: 4px;
}

.sea-label {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1rem;
    color: rgba(168,218,240,.7);
    margin-bottom: 16px;
}

.sea-wave {
    font-size: .82rem;
    color: rgba(168,218,240,.5);
}

/* Sun times */
.sun-times {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    padding: 16px 20px;
}

.sun-item {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.sun-icon { font-size: 1.4rem; }
.sun-time-label { font-size: .7rem; color: rgba(255,255,255,.3); text-transform: uppercase; letter-spacing: .1em; }
.sun-time-value { font-family: var(--serif); font-size: 1.2rem; color: rgba(255,255,255,.8); }

.sun-track {
    flex: 2;
    height: 4px;
    background: rgba(255,255,255,.1);
    border-radius: 2px;
    position: relative;
    overflow: visible;
}

#sun-position {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 10px; height: 10px;
    background: #f8c840;
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(248,200,64,.6);
    transition: left 60s linear;
}

/* 7-day forecast */
.forecast-strip {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}
@media(max-width:600px){ .forecast-strip { grid-template-columns: repeat(4, 1fr); } }
@media(max-width:380px){ .forecast-strip { grid-template-columns: repeat(3, 1fr); } }

.forecast-day {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 12px;
    padding: 12px 6px;
    text-align: center;
}

.fday-name { font-size: .68rem; color: rgba(255,255,255,.4); margin-bottom: 6px; }
.fday-emoji { font-size: 1.2rem; margin-bottom: 6px; display: block; }
.fday-max { font-size: .88rem; color: rgba(255,255,255,.85); font-weight: 500; }
.fday-min { font-size: .75rem; color: rgba(255,255,255,.35); }
.fday-rain { font-size: .65rem; color: rgba(100,180,240,.5); margin-top: 4px; }

/* ── MOON ── */
.moon-card {
    display: flex;
    align-items: center;
    gap: 24px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    padding: 20px 24px;
    margin-top: 16px;
}

#moon-canvas { flex-shrink: 0; }
.moon-name { font-family: var(--serif); font-size: 1.3rem; color: rgba(255,255,255,.8); }
.moon-age { font-size: .78rem; color: rgba(255,255,255,.3); margin-top: 4px; }

/* ── INSTAGRAM PHOTOS ── */
#photos-section {
    padding: 64px 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255,255,255,.06);
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
@media(max-width:600px){ .photo-grid { grid-template-columns: repeat(2, 1fr); } }

.photo-item {
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
}

.photo-item img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .5s;
    display: block;
}
.photo-item:hover img { transform: scale(1.04); }
.photo-item a { display: block; width: 100%; height: 100%; }

/* ── REVIEWS ── */
#reviews-section {
    padding: 64px 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255,255,255,.06);
    text-align: center;
}

.review-score {
    font-family: var(--serif);
    font-size: 3.5rem;
    font-weight: 300;
    color: #fff;
    line-height: 1;
}

.review-stars { color: #f59e0b; font-size: 1.1rem; margin: 6px 0; }

.review-count { font-size: .78rem; color: rgba(255,255,255,.3); margin-bottom: 40px; }

#review-carousel {
    max-width: 680px;
    margin: 0 auto;
    position: relative;
    min-height: 140px;
}

.review-slide {
    display: none;
    animation: fadeSlide .6s ease;
}
.review-slide.active { display: block; }

@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.review-text {
    font-family: var(--serif);
    font-style: italic;
    font-size: clamp(1.05rem, 2.5vw, 1.3rem);
    color: rgba(255,255,255,.78);
    line-height: 1.7;
    margin-bottom: 16px;
}

.review-author {
    font-size: .78rem;
    color: rgba(255,255,255,.3);
    text-transform: uppercase;
    letter-spacing: .1em;
}

.review-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 24px;
}

.review-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    cursor: pointer;
    transition: background .3s;
}
.review-dot.active { background: rgba(255,255,255,.7); }

/* ── CTA ── */
#cta-section {
    padding: 80px 24px;
    text-align: center;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255,255,255,.06);
}

.cta-title {
    font-family: var(--serif);
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 300;
    color: #fff;
    margin-bottom: 8px;
}
.cta-sub { font-size: .9rem; color: rgba(255,255,255,.4); margin-bottom: 32px; }

.cta-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

.btn-rez {
    background: #c0583a;
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 14px 36px;
    font-size: .9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background .2s, transform .2s;
}
.btn-rez:hover { background: #a84830; transform: translateY(-1px); color: #fff; }

.btn-wa {
    background: rgba(37,211,102,.15);
    color: #25d366;
    border: 1px solid rgba(37,211,102,.3);
    border-radius: 50px;
    padding: 14px 36px;
    font-size: .9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background .2s, transform .2s;
}
.btn-wa:hover { background: rgba(37,211,102,.25); transform: translateY(-1px); }

.btn-map {
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.6);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 50px;
    padding: 14px 28px;
    font-size: .9rem;
    text-decoration: none;
    transition: background .2s;
}
.btn-map:hover { background: rgba(255,255,255,.1); color: #fff; }

/* ── FLOATING SECTION NAV ── */
#section-nav {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 200;
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
}
.snav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    opacity: .65;
    transition: opacity .3s;
}
.snav-item:hover, .snav-item.active { opacity: 1; }
.snav-label {
    font-size: .68rem;
    color: rgba(255,255,255,.9);
    letter-spacing: .07em;
    text-transform: uppercase;
    white-space: nowrap;
    opacity: 0;
    transform: translateX(8px);
    transition: opacity .2s, transform .2s;
    pointer-events: none;
    text-shadow: 0 1px 4px rgba(0,0,0,.5);
    font-weight: 600;
}
.snav-item:hover .snav-label, .snav-item.active .snav-label {
    opacity: 1;
    transform: translateX(0);
}
.snav-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,.55);
    border: 2px solid rgba(255,255,255,.7);
    box-shadow: 0 0 6px rgba(0,0,0,.4);
    transition: background .3s, transform .3s, border-color .3s;
    flex-shrink: 0;
}
.snav-item:hover .snav-dot {
    background: rgba(255,255,255,.8);
    border-color: #fff;
}
.snav-item.active .snav-dot {
    background: #fff;
    border-color: #fff;
    box-shadow: 0 0 10px rgba(255,255,255,.6);
    transform: scale(1.4);
}
@media(max-width:600px){ #section-nav { display: none; } }
</style>
@endpush

@section('content')
{{-- Fixed sky layers --}}
<div id="sky" style="position:fixed;inset:0;z-index:0;"></div>
<canvas id="star-canvas" style="position:fixed;inset:0;z-index:1;pointer-events:none;"></canvas>
<div id="horizon-glow" style="position:fixed;bottom:0;left:0;right:0;height:35vh;z-index:2;pointer-events:none;opacity:0;"></div>

<div id="page">
    {{-- Floating section dots --}}
    <div id="section-nav"></div>

    {{-- Hero --}}
    <section id="hero-section">
        <p class="hero-sub">Palamutbükü · Datça · Ege</p>
        <h1 class="hero-title">Şu An Müdavim'de,</h1>
        <div class="hero-ambient" id="hero-time"></div>

        @if($spotify['is_playing'] && $spotify['now_playing'])
        @php $heroNp = $spotify['now_playing']; @endphp
        <p class="hero-music-intro">İskelede dalga sesleri arasından tam olarak bu şarkı yükseliyor…</p>

        {{-- Büyük şarkı kartı --}}
        <div class="now-playing-card" style="margin:0 auto 0;max-width:520px;">
            @if($heroNp['cover'])
                <img src="{{ $heroNp['cover'] }}" alt="{{ $heroNp['name'] }}" id="album-art">
            @else
                <div class="album-placeholder"><i class="bi bi-music-note-beamed"></i></div>
            @endif
            <div>
                <div class="track-name">{{ $heroNp['name'] }}</div>
                <div class="track-artist">{{ $heroNp['artists'] }}</div>
                <div class="track-album">{{ $heroNp['album'] }}</div>
                <div class="progress-bar-wrap">
                    <span id="prog-current">{{ gmdate('i:s', intdiv($heroNp['progress_ms'], 1000)) }}</span>
                    <div class="progress-bar-bg">
                        <div id="progress-fill" style="width:{{ $heroNp['duration_ms'] > 0 ? round($heroNp['progress_ms']/$heroNp['duration_ms']*100,1) : 0 }}%"></div>
                    </div>
                    <span id="prog-total">{{ gmdate('i:s', intdiv($heroNp['duration_ms'], 1000)) }}</span>
                </div>
            </div>
        </div>

        {{-- Sırada --}}
        @if(!empty($spotify['queue']))
        <div class="section-label" style="margin-top:24px;">Sırada</div>
        <div class="queue-grid" style="max-width:520px;margin:8px auto 0;">
            @foreach(array_slice($spotify['queue'], 0, 2) as $q)
            <div class="queue-card">
                @if($q['cover'])
                    <img src="{{ $q['cover'] }}" alt="">
                @endif
                <div>
                    <div class="queue-label">Sırada</div>
                    <div class="queue-name">{{ $q['name'] }}</div>
                    <div class="queue-artist">{{ $q['artists'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif

        <div class="scroll-cue" aria-hidden="true"><span></span></div>
    </section>

    {{-- Spotify Detail --}}
    @if($spotify['enabled'] && $spotify['connected'])
    <section class="glass-section" id="spotify-section">
        <div class="section-inner">
            <div class="section-label">Müdavim'de Şu An Çalan</div>

            @if($spotify['is_playing'] && $spotify['now_playing'])
            @php $np = $spotify['now_playing']; @endphp
            <div class="now-playing-card">
                @if($np['cover'])
                    <img src="{{ $np['cover'] }}" alt="{{ $np['name'] }}" id="album-art">
                @else
                    <div class="album-placeholder"><i class="bi bi-music-note-beamed"></i></div>
                @endif
                <div>
                    <div class="track-name">{{ $np['name'] }}</div>
                    <div class="track-artist">{{ $np['artists'] }}</div>
                    <div class="track-album">{{ $np['album'] }}</div>
                    <div class="progress-bar-wrap">
                        <span id="prog-current">{{ gmdate('i:s', intdiv($np['progress_ms'], 1000)) }}</span>
                        <div class="progress-bar-bg">
                            <div id="progress-fill" style="width:{{ $np['duration_ms'] > 0 ? round($np['progress_ms']/$np['duration_ms']*100,1) : 0 }}%"></div>
                        </div>
                        <span id="prog-total">{{ gmdate('i:s', intdiv($np['duration_ms'], 1000)) }}</span>
                    </div>
                </div>
            </div>

            @if(!empty($spotify['queue']))
            <div class="section-label" style="margin-top:24px;">Sırada</div>
            <div class="queue-grid">
                @foreach(array_slice($spotify['queue'], 0, 2) as $q)
                <div class="queue-card">
                    @if($q['cover'])
                        <img src="{{ $q['cover'] }}" alt="">
                    @endif
                    <div>
                        <div class="queue-label">Sırada</div>
                        <div class="queue-name">{{ $q['name'] }}</div>
                        <div class="queue-artist">{{ $q['artists'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($spotify['recent']))
            <div class="section-label" style="margin-top:24px;">Az Önce Çaldı</div>
            <div class="recent-list">
                @foreach(array_slice($spotify['recent'], 0, 5) as $r)
                <div class="recent-item">
                    @if($r['cover'])
                        <img src="{{ $r['cover'] }}" alt="">
                    @endif
                    <div>
                        <div class="recent-name">{{ $r['name'] }}</div>
                        <div class="recent-artist">{{ $r['artists'] }}</div>
                    </div>
                    @if($r['played_at'])
                    <div class="recent-time">{{ \Carbon\Carbon::parse($r['played_at'])->diffForHumans() }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @else
            <div style="text-align:center;padding:40px 0;font-family:var(--serif);font-style:italic;color:rgba(255,255,255,.3);font-size:1.1rem;">
                Bu an müzik yok — yalnızca dalga sesleri.
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Weather --}}
    @if($data['weather'])
    @php
        $w  = $data['weather'];
        $sea = $data['sea'];
        $sr  = $data['sunrise'];
        $ss  = $data['sunset'];
        $moon = $data['moon'];
    @endphp
    <section class="glass-section" id="weather-section">
        <div class="section-inner">
            <div class="section-label">Palamutbükü'nde Hava & Deniz</div>

            <div class="weather-grid">
                <div class="weather-card-main">
                    <div class="weather-temp">{{ $w['temp'] }}°</div>
                    <div class="weather-feels">Hissedilen {{ $w['feels'] }}°C</div>
                    <div class="weather-label-big">{{ $w['emoji'] }} {{ $w['label'] }}</div>
                    <div class="weather-details">
                        @php
                            $dirs = ['kuzeyden','kuzeydoğudan','doğudan','güneydoğudan','güneyden','güneybatıdan','batıdan','kuzeybatıdan'];
                            $windFrom = $dirs[round($w['wind_dir'] / 45) % 8];
                            $windDesc = match(true) {
                                $w['wind'] < 5  => 'Hava durgun',
                                $w['wind'] < 10 => ucfirst($windFrom) . ' hafif esinti',
                                $w['wind'] < 15 => ucfirst($windFrom) . ' tatlı meltem',
                                $w['wind'] < 20 => ucfirst($windFrom) . ' meltem serinletiyor',
                                $w['wind'] < 30 => ucfirst($windFrom) . ' hoş bir rüzgar',
                                $w['wind'] < 40 => ucfirst($windFrom) . ' kuvvetli meltem',
                                default         => ucfirst($windFrom) . ' sert rüzgar',
                            };
                        @endphp
                        <div class="weather-detail-row">
                            <i class="bi bi-wind"></i> {{ $windDesc }} · {{ $w['wind'] }} km/s
                        </div>
                        <div class="weather-detail-row">
                            <i class="bi bi-droplet"></i> Nem %{{ $w['humidity'] }}
                        </div>
                    </div>
                </div>

                @if($sea)
                @php
                    $seaPhrase = match(true) {
                        $sea['temp'] < 18 => 'Cesur yüzücüler için',
                        $sea['temp'] < 20 => 'Serinlemek için birebir',
                        $sea['temp'] < 22 => 'Dalmak için harika',
                        $sea['temp'] < 24 => 'Yüzmek için mükemmel',
                        $sea['temp'] < 26 => 'Plajlar sizi bekliyor',
                        $sea['temp'] < 28 => 'Suya girmek tam zamanı',
                        default           => 'Serinlemenin tam vakti',
                    };
                @endphp
                <div class="weather-card-sea">
                    <div>
                        <div style="font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(168,218,240,.4);margin-bottom:8px;">Deniz Suyu</div>
                        <div class="sea-temp">{{ $sea['temp'] }}°</div>
                        <div class="sea-label" style="font-style:italic;color:rgba(168,218,240,.7);">{{ $seaPhrase }}</div>
                    </div>
                    <div>
                        <div class="sea-wave">
                            <i class="bi bi-water me-1"></i>
                            {{ $sea['wave_label'] }}
                            @if($sea['wave'] > 0) · {{ $sea['wave'] }}m @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sunrise/Sunset --}}
            @if($sr && $ss)
            <div class="sun-times">
                <div class="sun-item">
                    <span class="sun-icon">🌅</span>
                    <div>
                        <div class="sun-time-label">Gün doğumu</div>
                        <div class="sun-time-value">{{ $sr }}</div>
                    </div>
                </div>
                <div class="sun-track">
                    <div id="sun-position"></div>
                </div>
                <div class="sun-item" style="justify-content:flex-end;text-align:right;">
                    <div>
                        <div class="sun-time-label">Gün batımı</div>
                        <div class="sun-time-value">{{ $ss }}</div>
                    </div>
                    <span class="sun-icon">🌇</span>
                </div>
            </div>
            @endif

            {{-- Moon --}}
            <div class="moon-card">
                <canvas id="moon-canvas" width="70" height="70"></canvas>
                <div>
                    <div class="moon-name">{{ $moon['name'] }}</div>
                    <div class="moon-age">Ay yaşı {{ $moon['age'] }} gün</div>
                </div>
            </div>

            {{-- 7-day forecast --}}
            @if(!empty($data['forecast']))
            <div class="section-label" style="margin-top:32px;">Önümüzdeki 7 Gün</div>
            <div class="forecast-strip">
                @foreach($data['forecast'] as $f)
                @php
                    $carbon = \Carbon\Carbon::parse($f['date'])->locale('tr');
                    $dayName = mb_substr($carbon->dayName, 0, 3);
                @endphp
                <div class="forecast-day">
                    <div class="fday-name">{{ $dayName }}</div>
                    <span class="fday-emoji">{{ $f['emoji'] }}</span>
                    <div class="fday-max">{{ $f['max'] }}°</div>
                    <div class="fday-min">{{ $f['min'] }}°</div>
                    @if($f['rain'] > 20)
                    <div class="fday-rain">%{{ $f['rain'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </section>
    @endif

    {{-- Instagram Photos --}}
    @if($photos->isNotEmpty())
    <section id="photos-section">
        <div class="section-inner">
            <div class="section-label">Müdavim'den Kareler</div>
            <div class="photo-grid">
                @foreach($photos as $post)
                <div class="photo-item">
                    @if($post->permalink)
                    <a href="{{ $post->permalink }}" target="_blank" rel="noopener">
                    @endif
                        <img src="{{ $post->media_url }}" alt="Müdavim" loading="lazy">
                    @if($post->permalink)
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Google Reviews --}}
    @if($reviewList->isNotEmpty())
    <section id="reviews-section">
        <div class="section-inner">
            <div class="section-label" style="text-align:center;">Misafirlerimiz Diyor Ki</div>
            <div class="review-score">{{ $reviewSummary['rating'] }}</div>
            <div class="review-stars">
                @for($i=1;$i<=5;$i++){{ $i<=round($reviewSummary['rating'])? '★':'☆' }}@endfor
            </div>
            <div class="review-count">{{ number_format($reviewSummary['total']) }} Google yorumu</div>

            <div id="review-carousel">
                @foreach($reviewList as $i => $rev)
                <div class="review-slide {{ $i===0 ? 'active' : '' }}">
                    <div class="review-text">"{{ mb_substr($rev->comment, 0, 220) }}{{ mb_strlen($rev->comment)>220?'…':'' }}"</div>
                    <div class="review-author">{{ $rev->author_name }} &nbsp;·&nbsp; {{ str_repeat('★', $rev->rating ?? 5) }}</div>
                </div>
                @endforeach
            </div>
            <div class="review-dots">
                @foreach($reviewList as $i => $_)
                <div class="review-dot {{ $i===0 ? 'active' : '' }}" data-idx="{{ $i }}"></div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section id="cta-section">
        <div class="cta-title">Bir masa ayırtın.</div>
        <p class="cta-sub">Tekne ile gelenler için sahil rezervasyonu da mevcut.</p>
        <div class="cta-buttons">
            <a href="{{ route('reservation.public.create') }}" class="btn-rez">Rezervasyon Yap</a>
            <a href="https://wa.me/{{ ltrim(config('restaurant.whatsapp'),'+') }}?text={{ urlencode('Merhaba! Rezervasyon için bilgi almak istiyorum.') }}"
               target="_blank" class="btn-wa"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
            <a href="{{ route('website.contact') }}" class="btn-map"><i class="bi bi-map me-1"></i>Nasıl Gelinir</a>
            <button onclick="shareAmbiance()" class="btn-map" style="border:1px solid rgba(255,255,255,.15);">
                <i class="bi bi-share me-1"></i>Şu Anı Paylaş
            </button>
        </div>
    </section>

</div>{{-- #page --}}
@endsection

@push('scripts')
<script>
(function () {
'use strict';

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// DATA FROM PHP
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
const SUNRISE    = @json($data['sunrise'] ?? null);
const SUNSET     = @json($data['sunset']  ?? null);
const MOON_PHASE = @json($data['moon']['fraction'] ?? 0.5);
const MOON_AGE   = @json($data['moon']['age']      ?? 15);
const SEA_TEMP      = @json($sea['temp'] ?? null);
const WEATHER_TEMP  = @json($data['weather']['temp'] ?? null);
const WEATHER_LABEL = @json($data['weather']['label'] ?? null);
const WEATHER_CODE  = @json($data['weather']['code'] ?? null);
const WEATHER_WIND    = @json($data['weather']['wind'] ?? null);
const WEATHER_WDIR    = @json($data['weather']['wind_dir'] ?? null);
const SPOTIFY_PLAYING = @json($spotify['is_playing'] && !empty($spotify['now_playing']));

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// HELPERS
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function toMinutes(hhmm) {
    if (!hhmm) return null;
    const [h, m] = hhmm.split(':').map(Number);
    return h * 60 + m;
}

function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }
function lerp(a, b, t)    { return a + (b - a) * t; }
function smoothstep(t)    { return t * t * (3 - 2 * t); }

function hexToRgb(hex) {
    const r = parseInt(hex.slice(1,3),16);
    const g = parseInt(hex.slice(3,5),16);
    const b = parseInt(hex.slice(5,7),16);
    return [r, g, b];
}

function lerpColor(c1, c2, t) {
    const [r1,g1,b1] = hexToRgb(c1);
    const [r2,g2,b2] = hexToRgb(c2);
    const r = Math.round(lerp(r1, r2, t));
    const g = Math.round(lerp(g1, g2, t));
    const b = Math.round(lerp(b1, b2, t));
    return `rgb(${r},${g},${b})`;
}

function lerpGradient(f1, f2, t) {
    return f1.colors.map((c, i) => lerpColor(c, f2.colors[i], t));
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// SKY GRADIENT SYSTEM
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
const SR = toMinutes(SUNRISE) ?? 360;   // default 06:00
const SS = toMinutes(SUNSET)  ?? 1215;  // default 20:15

// Keyframes: {t: minutes, colors: [top, mid, bottom], stars: 0-1, horizon: {color, opacity}}
function buildKeyframes() {
    return [
        { t: 0,        colors: ['#020610','#04091a','#030810'], stars: 1.0, horizon: null },
        { t: SR - 90,  colors: ['#04091e','#070e28','#050b1c'], stars: 0.9, horizon: null },
        { t: SR - 50,  colors: ['#0c0d38','#1c1258','#0e0a28'], stars: 0.7, horizon: { color: '#4a1a60', opacity: 0.25 } },
        { t: SR - 25,  colors: ['#0e1240','#38185a','#c03830'], stars: 0.3, horizon: { color: '#e04828', opacity: 0.55 } },
        { t: SR,       colors: ['#183a70','#c05828','#f09028'], stars: 0.0, horizon: { color: '#f8a030', opacity: 0.70 } },
        { t: SR + 40,  colors: ['#2a5888','#d4884a','#f8c870'], stars: 0.0, horizon: { color: '#f8d070', opacity: 0.35 } },
        { t: SR + 90,  colors: ['#2e78b0','#60a8d0','#c8e0f0'], stars: 0.0, horizon: null },
        { t: 750,      colors: ['#1468b0','#3490d0','#98c8e8'], stars: 0.0, horizon: null },
        { t: SS - 90,  colors: ['#1868b0','#3490d0','#98c8e8'], stars: 0.0, horizon: null },
        { t: SS - 40,  colors: ['#1a5070','#c87840','#f0b048'], stars: 0.0, horizon: { color: '#f0a030', opacity: 0.40 } },
        { t: SS,       colors: ['#0c1e3a','#b84428','#e07028'], stars: 0.0, horizon: { color: '#f08020', opacity: 0.75 } },
        { t: SS + 30,  colors: ['#08101e','#3a1048','#882840'], stars: 0.3, horizon: { color: '#6a1840', opacity: 0.40 } },
        { t: SS + 65,  colors: ['#040c1a','#150828','#20102e'], stars: 0.7, horizon: null },
        { t: SS + 100, colors: ['#020610','#04091a','#030810'], stars: 1.0, horizon: null },
        { t: 1440,     colors: ['#020610','#04091a','#030810'], stars: 1.0, horizon: null },
    ];
}

const KEYFRAMES = buildKeyframes();

function getSkyState(nowMin) {
    let prev = KEYFRAMES[0], next = KEYFRAMES[KEYFRAMES.length - 1];
    for (let i = 0; i < KEYFRAMES.length - 1; i++) {
        if (nowMin >= KEYFRAMES[i].t && nowMin < KEYFRAMES[i+1].t) {
            prev = KEYFRAMES[i];
            next = KEYFRAMES[i+1];
            break;
        }
    }
    const span = next.t - prev.t;
    const raw  = span > 0 ? (nowMin - prev.t) / span : 0;
    const t    = smoothstep(clamp(raw, 0, 1));
    const colors = lerpGradient(prev, next, t);
    const stars  = lerp(prev.stars, next.stars, t);

    // Horizon glow
    let horizon = null;
    if (prev.horizon || next.horizon) {
        const ph = prev.horizon ?? { color: '#000000', opacity: 0 };
        const nh = next.horizon ?? { color: '#000000', opacity: 0 };
        const op = lerp(ph.opacity, nh.opacity, t);
        const hc = lerpColor(ph.color, nh.color, t);
        if (op > 0.01) horizon = { color: hc, opacity: op };
    }

    return { colors, stars, horizon };
}

function applysky(state) {
    const skyEl = document.getElementById('sky');
    const hgEl  = document.getElementById('horizon-glow');

    skyEl.style.background =
        `linear-gradient(to bottom, ${state.colors[0]} 0%, ${state.colors[1]} 55%, ${state.colors[2]} 100%)`;

    if (state.horizon) {
        hgEl.style.background =
            `linear-gradient(to top, ${state.horizon.color} 0%, transparent 100%)`;
        hgEl.style.opacity = state.horizon.opacity;
    } else {
        hgEl.style.opacity = 0;
    }

    return state.stars;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// STAR FIELD + MOON (Canvas)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
const canvas = document.getElementById('star-canvas');
const ctx    = canvas.getContext('2d');

let W, H, stars = [], milky = [], shoots = [], targetStarOpacity = 0, currentStarOpacity = 0;

function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
    buildStars();
}

function rng(a, b) { return a + Math.random() * (b - a); }

function buildStars() {
    const count = Math.min(550, Math.floor(W * H / 2800));
    stars = Array.from({ length: count }, () => ({
        x: Math.random(),
        y: Math.random() * 0.88,
        r: Math.pow(Math.random(), 2.2) * 1.7 + 0.25,
        h: rng(0, 360), s: rng(0, 25), l: rng(90, 100),
        phase: rng(0, Math.PI * 2),
        speed: rng(0.004, 0.022),
        amp:   rng(0.12, 0.40),
        base:  rng(0.55, 0.95),
    }));

    // Milky Way: diagonal band of dim dense stars
    milky = Array.from({ length: 180 }, () => {
        const t = Math.random();
        const s = (Math.random() - 0.5) * 0.18;
        return { x: t * 1.1 - 0.05 + s * 0.4, y: t * 0.55 + 0.08 + s, r: rng(0.1, 0.55), op: rng(0.04, 0.22) };
    });
}

function drawMilkyWay(globalAlpha) {
    if (globalAlpha < 0.1) return;
    ctx.save();

    // Soft glow band
    const grd = ctx.createLinearGradient(0, H * 0.08, W * 1.05, H * 0.6);
    grd.addColorStop(0,   `rgba(140,155,210,0)`);
    grd.addColorStop(0.35,`rgba(140,155,210,${0.04 * globalAlpha})`);
    grd.addColorStop(0.5, `rgba(140,155,210,${0.07 * globalAlpha})`);
    grd.addColorStop(0.65,`rgba(140,155,210,${0.04 * globalAlpha})`);
    grd.addColorStop(1,   `rgba(140,155,210,0)`);
    ctx.fillStyle = grd;
    ctx.fillRect(0, 0, W, H);

    milky.forEach(s => {
        ctx.beginPath();
        ctx.arc(s.x * W, s.y * H, s.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(200,210,235,${s.op * globalAlpha})`;
        ctx.fill();
    });

    ctx.restore();
}

function drawStars(t, globalAlpha) {
    stars.forEach(s => {
        const twinkle = s.base + s.amp * Math.sin(t * s.speed + s.phase);
        const alpha = clamp(twinkle, 0, 1) * globalAlpha;
        if (alpha < 0.02) return;

        const sx = s.x * W, sy = s.y * H;

        // Glow for larger stars
        if (s.r > 0.85) {
            const gr = ctx.createRadialGradient(sx, sy, 0, sx, sy, s.r * 4.5);
            gr.addColorStop(0, `hsla(${s.h},${s.s}%,${s.l}%,${alpha * 0.35})`);
            gr.addColorStop(1, `hsla(${s.h},${s.s}%,${s.l}%,0)`);
            ctx.fillStyle = gr;
            ctx.beginPath();
            ctx.arc(sx, sy, s.r * 4.5, 0, Math.PI * 2);
            ctx.fill();
        }

        // Core
        ctx.beginPath();
        ctx.arc(sx, sy, s.r, 0, Math.PI * 2);
        ctx.fillStyle = `hsla(${s.h},${s.s}%,${s.l}%,${alpha})`;
        ctx.fill();
    });
}

function drawMoon(globalAlpha) {
    if (globalAlpha < 0.05) return;

    const mx = W * 0.78, my = H * 0.18;
    const mr = Math.min(W, H) * 0.038;
    const phase = MOON_PHASE;
    const illum = 0.5 * (1 - Math.cos(phase * 2 * Math.PI));
    if (illum < 0.03) return;

    const alpha = clamp(globalAlpha * 0.95, 0, 1);
    const waxing = phase < 0.5;

    // Atmosphere glow
    const glr = ctx.createRadialGradient(mx, my, mr * 0.6, mx, my, mr * 4.5);
    glr.addColorStop(0, `rgba(255,248,190,${alpha * 0.18 * Math.sqrt(illum)})`);
    glr.addColorStop(1, 'rgba(255,248,190,0)');
    ctx.fillStyle = glr;
    ctx.beginPath();
    ctx.arc(mx, my, mr * 4.5, 0, Math.PI * 2);
    ctx.fill();

    // Moon phase using clip + composite
    ctx.save();
    ctx.beginPath();
    ctx.arc(mx, my, mr, 0, Math.PI * 2);
    ctx.clip();

    // Dark side
    ctx.fillStyle = `rgba(8,12,30,${alpha})`;
    ctx.fillRect(mx - mr - 1, my - mr - 1, mr * 2 + 2, mr * 2 + 2);

    // Lit side
    ctx.fillStyle = `rgba(255,248,200,${alpha})`;
    const termRx = mr * Math.cos(phase * 2 * Math.PI);

    ctx.beginPath();
    if (waxing) {
        ctx.moveTo(mx, my - mr);
        ctx.arc(mx, my, mr, -Math.PI / 2, Math.PI / 2); // right semicircle CW
        if (termRx >= 0) {
            ctx.ellipse(mx, my, termRx + 0.01, mr, 0, Math.PI / 2, -Math.PI / 2, true);
        } else {
            ctx.ellipse(mx, my, Math.abs(termRx) + 0.01, mr, 0, Math.PI / 2, -Math.PI / 2, false);
        }
    } else {
        ctx.moveTo(mx, my - mr);
        ctx.arc(mx, my, mr, -Math.PI / 2, Math.PI / 2, true); // left semicircle CCW
        if (termRx <= 0) {
            ctx.ellipse(mx, my, Math.abs(termRx) + 0.01, mr, 0, -Math.PI / 2, Math.PI / 2, false);
        } else {
            ctx.ellipse(mx, my, termRx + 0.01, mr, 0, -Math.PI / 2, Math.PI / 2, true);
        }
    }
    ctx.closePath();
    ctx.fill();
    ctx.restore();
}

function maybeShootingStar() {
    if (currentStarOpacity > 0.5 && Math.random() < 0.0012) {
        shoots.push({
            x: Math.random() * W, y: Math.random() * H * 0.45,
            len: rng(120, 220),
            angle: Math.PI * 0.22 + (Math.random() - 0.5) * 0.25,
            spd: rng(7, 14), progress: 0, op: 1,
        });
    }
}

function drawShootingStars() {
    shoots = shoots.filter(s => s.op > 0.01);
    shoots.forEach(s => {
        s.progress += s.spd;
        s.op = clamp(1 - s.progress / (s.len * 3.5), 0, 1);

        const x2 = s.x + Math.cos(s.angle) * s.progress;
        const y2 = s.y + Math.sin(s.angle) * s.progress;
        const x1 = x2 - Math.cos(s.angle) * Math.min(s.len, s.progress);
        const y1 = y2 - Math.sin(s.angle) * Math.min(s.len, s.progress);

        const grd = ctx.createLinearGradient(x1, y1, x2, y2);
        grd.addColorStop(0, `rgba(255,255,255,0)`);
        grd.addColorStop(1, `rgba(255,255,255,${s.op * currentStarOpacity})`);
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.strokeStyle = grd;
        ctx.lineWidth = 1.8;
        ctx.stroke();
    });
}

let animT = 0;
function animate() {
    requestAnimationFrame(animate);
    animT++;

    // Smooth star opacity
    currentStarOpacity += (targetStarOpacity - currentStarOpacity) * 0.012;

    ctx.clearRect(0, 0, W, H);

    if (currentStarOpacity > 0.01) {
        drawMilkyWay(currentStarOpacity);
        drawStars(animT, currentStarOpacity);
        drawMoon(currentStarOpacity);
        maybeShootingStar();
        drawShootingStars();
    }
}

window.addEventListener('resize', resize);
resize();
animate();

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// MOON PHASE CANVAS (small, in page)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
(function () {
    const mc   = document.getElementById('moon-canvas');
    if (!mc) return;
    const mctx = mc.getContext('2d');
    const cx = 35, cy = 35, mr = 26;
    const phase = MOON_PHASE;
    const illum = 0.5 * (1 - Math.cos(phase * 2 * Math.PI));
    const waxing = phase < 0.5;

    mctx.fillStyle = '#0a0e28';
    mctx.beginPath();
    mctx.arc(cx, cy, mr, 0, Math.PI * 2);
    mctx.fill();

    if (illum > 0.03) {
        mctx.save();
        mctx.beginPath();
        mctx.arc(cx, cy, mr, 0, Math.PI * 2);
        mctx.clip();

        mctx.fillStyle = 'rgba(255,248,200,0.9)';
        const termRx = mr * Math.cos(phase * 2 * Math.PI);
        mctx.beginPath();
        if (waxing) {
            mctx.moveTo(cx, cy - mr);
            mctx.arc(cx, cy, mr, -Math.PI/2, Math.PI/2);
            if (termRx >= 0) {
                mctx.ellipse(cx, cy, termRx + 0.01, mr, 0, Math.PI/2, -Math.PI/2, true);
            } else {
                mctx.ellipse(cx, cy, Math.abs(termRx) + 0.01, mr, 0, Math.PI/2, -Math.PI/2, false);
            }
        } else {
            mctx.moveTo(cx, cy - mr);
            mctx.arc(cx, cy, mr, -Math.PI/2, Math.PI/2, true);
            if (termRx <= 0) {
                mctx.ellipse(cx, cy, Math.abs(termRx) + 0.01, mr, 0, -Math.PI/2, Math.PI/2, false);
            } else {
                mctx.ellipse(cx, cy, termRx + 0.01, mr, 0, -Math.PI/2, Math.PI/2, true);
            }
        }
        mctx.closePath();
        mctx.fill();
        mctx.restore();
    }

    // Ring
    mctx.strokeStyle = 'rgba(255,255,255,0.1)';
    mctx.lineWidth = 1;
    mctx.beginPath();
    mctx.arc(cx, cy, mr, 0, Math.PI * 2);
    mctx.stroke();
})();

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// HERO TIME + CLOCK
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
const DAYS   = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
const MONTHS = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
function pad(n) { return String(n).padStart(2,'0'); }

function timeOfDay(h) {
    if (h >= 5  && h < 12) return 'Sabahı';
    if (h >= 12 && h < 15) return 'Öğleden Sonrası';
    if (h >= 15 && h < 18) return 'Akşamüstü';
    if (h >= 18 && h < 22) return 'Akşamı';
    return 'Gecesi';
}

function seaPhrase(temp) {
    if (temp === null) return null;
    if (temp < 18) return `Deniz ${temp}°C — cesur yüzücüler için`;
    if (temp < 20) return `Deniz ${temp}°C — serinlemek için birebir`;
    if (temp < 22) return `Deniz ${temp}°C — dalmak için harika`;
    if (temp < 24) return `Deniz ${temp}°C — yüzmek için mükemmel`;
    if (temp < 26) return `Deniz ${temp}°C — plajlar sizi bekliyor`;
    if (temp < 28) return `Deniz ${temp}°C — suya girmek tam zamanı`;
    return `Deniz ${temp}°C — serinlemenin tam vakti`;
}

function nightWeatherLabel(code, h) {
    const isNight = h >= 21 || h < 5;
    const isDawn  = h >= 5  && h < 7;
    if (!isNight && !isDawn) return null;
    if (code === 0) return isDawn ? 'Şafak söküyor' : 'Yıldızlı gece';
    if (code === 1) return isDawn ? 'Açık, şafak yaklaşıyor' : 'Çoğunlukla açık gece';
    if (code === 2) return 'Parçalı bulutlu';
    return null;
}

function updateClock() {
    const now  = new Date();
    const h    = now.getHours();
    const hStr = pad(h) + ':' + pad(now.getMinutes());
    const el   = document.getElementById('hero-time');
    if (!el) return;

    // Satır 1: gün + saat
    const lines = [`Günlerden ${DAYS[now.getDay()]} · Saat ${hStr}`];

    // Satır 2: selamlama
    lines.push(`· Enfes bir ${MONTHS[now.getMonth()]} ${timeOfDay(h)}`);

    // Satır 3: hava
    if (WEATHER_TEMP !== null) {
        const nightLbl = nightWeatherLabel(WEATHER_CODE, h);
        const lbl = nightLbl ?? WEATHER_LABEL;
        lines.push(`· Hava ${WEATHER_TEMP}°C, ${lbl}`);
    }

    // Satır 4: deniz (gündüz 06-17)
    if (h >= 6 && h < 17 && SEA_TEMP !== null) {
        const sp = seaPhrase(SEA_TEMP);
        if (sp) {
            const phrase = sp.includes('—') ? sp.split('—')[1].trim() : sp;
            lines.push(`· Deniz ${SEA_TEMP}°C, ${phrase.charAt(0).toUpperCase() + phrase.slice(1)}`);
        }
    }

    // Satır 5: rüzgar (ve eğer spotify çalıyorsa "ve" ile bitiyor)
    if (WEATHER_WIND !== null) {
        const dirs = ['kuzeyden','kuzeydoğudan','doğudan','güneydoğudan','güneyden','güneybatıdan','batıdan','kuzeybatıdan'];
        const from = dirs[Math.round((WEATHER_WDIR || 0) / 45) % 8];
        const cap  = from.charAt(0).toUpperCase() + from.slice(1);
        const spd  = WEATHER_WIND;
        let w;
        if      (spd < 5)  w = 'Hava durgun';
        else if (spd < 10) w = `${cap} hafif bir esinti var`;
        else if (spd < 15) w = `${cap} tatlı bir meltem esiyor`;
        else if (spd < 20) w = `${cap} meltem serinletiyor`;
        else if (spd < 30) w = `${cap} hoş bir rüzgar esiyor`;
        else               w = `${cap} kuvvetli bir meltem esiyor`;
        lines.push(`· ${w}${SPOTIFY_PLAYING ? ' ve' : ''}`);
    }

    // Satır 6: ay (sadece gece)
    if ((h >= 20 || h < 7) && !SPOTIFY_PLAYING) {
        const moonNames = ['Yeni Ay','Hilal','İlk Dördün','Şişen Ay','Dolunay','Azalan Ay','Son Dördün','Batan Hilal'];
        lines.push(`· ${moonNames[Math.round(MOON_PHASE * 8) % 8]}`);
    }

    el.innerHTML = lines.join('<br>');
}

updateClock();
setInterval(updateClock, 30000);

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// SKY TICK (every 10s)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
function skyTick() {
    const now = new Date();
    const nowMin = now.getHours() * 60 + now.getMinutes() + now.getSeconds() / 60;
    const state = getSkyState(nowMin);
    targetStarOpacity = state.stars;
    applysky(state);
}

skyTick();
setInterval(skyTick, 10000);

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// SUN POSITION ON TRACK
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
(function () {
    const el = document.getElementById('sun-position');
    if (!el || !SUNRISE || !SUNSET) return;
    const now = new Date();
    const nowMin = now.getHours() * 60 + now.getMinutes();
    const pct = clamp((nowMin - SR) / (SS - SR), 0, 1);
    el.style.left = (pct * 100).toFixed(1) + '%';
})();

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// SPOTIFY PROGRESS BAR
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
(function () {
    const fill = document.getElementById('progress-fill');
    const cur  = document.getElementById('prog-current');
    if (!fill) return;

    let progress  = @json($spotify['now_playing']['progress_ms'] ?? 0);
    let duration  = @json($spotify['now_playing']['duration_ms'] ?? 0);

    if (!duration) return;

    function tick() {
        progress = Math.min(progress + 1000, duration);
        const pct = (progress / duration * 100).toFixed(1);
        fill.style.width = pct + '%';
        if (cur) cur.textContent = new Date(progress).toISOString().substr(14, 5);
        if (progress < duration) setTimeout(tick, 1000);
    }

    setTimeout(tick, 1000);
})();

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// REVIEW CAROUSEL
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
(function () {
    const slides = document.querySelectorAll('.review-slide');
    const dots   = document.querySelectorAll('.review-dot');
    if (!slides.length) return;

    let current = 0;

    function go(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = idx % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }

    dots.forEach(d => d.addEventListener('click', () => go(+d.dataset.idx)));
    setInterval(() => go(current + 1), 7000);
})();

// ── FLOATING SECTION NAV ──
(function () {
    const sections = [
        { id: 'hero-section',    label: 'Şu An' },
        { id: 'spotify-section', label: 'Müzik' },
        { id: 'weather-section', label: 'Hava & Deniz' },
        { id: 'photos-section',  label: 'Fotoğraflar' },
        { id: 'reviews-section', label: 'Yorumlar' },
        { id: 'cta-section',     label: 'Rezervasyon' },
    ].filter(s => document.getElementById(s.id));

    const nav = document.getElementById('section-nav');
    if (!nav || sections.length < 2) return;

    sections.forEach(s => {
        const item = document.createElement('div');
        item.className = 'snav-item';
        item.innerHTML = `<span class="snav-label">${s.label}</span><span class="snav-dot"></span>`;
        item.addEventListener('click', () => {
            document.getElementById(s.id).scrollIntoView({ behavior: 'smooth' });
        });
        item.dataset.target = s.id;
        nav.appendChild(item);
    });

    const items = nav.querySelectorAll('.snav-item');
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                items.forEach(i => i.classList.toggle('active', i.dataset.target === e.target.id));
            }
        });
    }, { threshold: 0.4 });

    sections.forEach(s => obs.observe(document.getElementById(s.id)));
})();

// ── PAYLAŞIM ──
function shareAmbiance() {
    const now  = new Date();
    const h    = now.getHours();
    const hStr = pad(h) + ':' + pad(now.getMinutes());
    let text = `🌊 Şu an Müdavim'deyim — Palamutbükü, Datça\n`;
    text += `📅 ${DAYS[now.getDay()]}, ${hStr}\n`;
    if (WEATHER_TEMP !== null) text += `🌡 Hava ${WEATHER_TEMP}°C\n`;
    if (SEA_TEMP !== null) text += `🌊 Deniz ${SEA_TEMP}°C\n`;
    if (SPOTIFY_PLAYING) {
        const trackEl = document.querySelector('.track-name');
        const artistEl = document.querySelector('.track-artist');
        if (trackEl) text += `🎵 ${trackEl.textContent} — ${artistEl?.textContent || ''}\n`;
    }
    text += `\nSen de gelsene! 👇`;

    if (navigator.share) {
        navigator.share({
            title: 'Şu An Müdavim\'de',
            text: text,
            url: window.location.href
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(window.location.href + '\n\n' + text)
            .then(() => alert('Paylaşım metni ve link kopyalandı!'));
    }
}

})();
</script>

@endpush
