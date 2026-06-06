@extends('layouts.website')
@section('title', 'Ambiyans 2')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&display=optional" rel="stylesheet">
<style>
/* ── SCROLLBAR GUTTER — navbar shift'i önler ── */
html {
    scrollbar-gutter: stable;
}

/* Sayfa her zaman scrollable — scrollbar ani görünüp kayma yaratmasın */
main {
    min-height: 100vh;
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
<div id="sky" style="position:fixed;inset:0;z-index:0;background:#020610;"></div>
<canvas id="star-canvas" style="position:fixed;inset:0;z-index:1;pointer-events:none;"></canvas>
<div id="horizon-glow" style="position:fixed;bottom:0;left:0;right:0;height:35vh;z-index:2;pointer-events:none;opacity:0;"></div>

<div id="page" style="position:relative;z-index:10;">
    <div id="section-nav"></div>
    
    <section id="hero-section">
        <p class="hero-sub">Palamutbükü · Datça · Ege</p>
        <h1 class="hero-title">Şu An Müdavim'de,</h1>
        <div class="hero-ambient" id="hero-time"></div>
        <div class="scroll-cue" aria-hidden="true"><span></span></div>
    </section>

    <section class="glass-section" id="spotify-section">
        <div class="section-inner">
            <div class="section-label">Müdavim'de Şu An Çalan</div>
            <p style="color:rgba(255,255,255,.5);">Spotify placeholder</p>
        </div>
    </section>

    <section class="glass-section" id="weather-section">
        <div class="section-inner">
            <div class="section-label">Hava & Deniz</div>
            <p style="color:rgba(255,255,255,.5);">Hava placeholder</p>
        </div>
    </section>

    <section id="reviews-section" style="padding:64px 0;text-align:center;">
        <div class="section-inner">
            <div class="section-label">Yorumlar</div>
            <p style="color:rgba(255,255,255,.5);">5 yıldız</p>
        </div>
    </section>

    <section id="cta-section">
        <div class="cta-title">Bir masa ayırtın.</div>
    </section>
    
    <p style="text-align:center;padding:20px;color:rgba(255,255,255,.3);font-size:.85rem;">
        ADIM 8: TAM CSS + tam HTML yapısı (scroll-cue dahil). Kayma var mı?
    </p>
</div>
@endsection

@push('scripts')
<script>
(function(){
const DAYS=['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
const MONTHS=['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
function pad(n){return String(n).padStart(2,'0');}
function timeOfDay(h){if(h>=5&&h<12)return 'Sabahı';if(h>=12&&h<15)return 'Öğleden Sonrası';if(h>=15&&h<18)return 'Akşamüstü';if(h>=18&&h<22)return 'Akşamı';return 'Gecesi';}
function updateClock(){const now=new Date(),h=now.getHours(),el=document.getElementById('hero-time');if(!el)return;el.innerHTML=`Günlerden ${DAYS[now.getDay()]} · Saat ${pad(h)}:${pad(now.getMinutes())}<br>· Enfes bir ${MONTHS[now.getMonth()]} ${timeOfDay(h)}`;}
updateClock();setInterval(updateClock,30000);
function hexToRgb(hex){const r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);return[r,g,b];}
function lerp(a,b,t){return a+(b-a)*t;}function smoothstep(t){return t*t*(3-2*t);}
function lerpColor(c1,c2,t){const[r1,g1,b1]=hexToRgb(c1),[r2,g2,b2]=hexToRgb(c2);return`rgb(${Math.round(lerp(r1,r2,t))},${Math.round(lerp(g1,g2,t))},${Math.round(lerp(b1,b2,t))})`;}
const skyEl=document.getElementById('sky');
const KF=[{t:0,colors:['#020610','#04091a','#030810']},{t:360,colors:['#183a70','#c05828','#f09028']},{t:420,colors:['#4a8ab0','#7ab8d0','#b0d8e8']},{t:720,colors:['#5a9fc0','#88c8e0','#c0e0f0']},{t:1215,colors:['#8a2820','#d04820','#f08828']},{t:1305,colors:['#04091e','#070e28','#050b1c']},{t:1440,colors:['#020610','#04091a','#030810']}];
function getSkyState(m){for(let i=0;i<KF.length-1;i++){if(m>=KF[i].t&&m<KF[i+1].t){const t=smoothstep((m-KF[i].t)/(KF[i+1].t-KF[i].t));return KF[i].colors.map((c,j)=>lerpColor(c,KF[i+1].colors[j],t));}}return KF[KF.length-1].colors;}
function skyTick(){const now=new Date(),m=now.getHours()*60+now.getMinutes()+now.getSeconds()/60,c=getSkyState(m);if(skyEl)skyEl.style.background=`linear-gradient(to bottom,${c[0]},${c[1]} 50%,${c[2]})`;}
skyTick();setInterval(skyTick,10000);
const canvas=document.getElementById('star-canvas');
if(canvas){const ctx=canvas.getContext('2d');let W,H,stars=[];function resize(){W=canvas.width=window.innerWidth;H=canvas.height=window.innerHeight;}resize();window.addEventListener('resize',resize);for(let i=0;i<550;i++)stars.push({x:Math.random(),y:Math.random(),r:Math.random()*1.2+0.3,a:Math.random()});function drawFrame(){ctx.clearRect(0,0,W,H);stars.forEach(s=>{ctx.beginPath();ctx.arc(s.x*W,s.y*H,s.r,0,Math.PI*2);ctx.fillStyle=`rgba(255,255,255,${s.a})`;ctx.fill();});requestAnimationFrame(drawFrame);}drawFrame();}
(function(){const sections=[{id:'hero-section',label:'Şu An'},{id:'spotify-section',label:'Müzik'},{id:'weather-section',label:'Hava'},{id:'reviews-section',label:'Yorumlar'},{id:'cta-section',label:'Rezervasyon'}].filter(s=>document.getElementById(s.id));const nav=document.getElementById('section-nav');if(!nav||sections.length<2)return;sections.forEach(s=>{const item=document.createElement('div');item.className='snav-item';item.innerHTML=`<span class="snav-label">${s.label}</span><span class="snav-dot"></span>`;item.addEventListener('click',()=>document.getElementById(s.id).scrollIntoView({behavior:'smooth'}));item.dataset.target=s.id;nav.appendChild(item);});const items=nav.querySelectorAll('.snav-item');const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting)items.forEach(i=>i.classList.toggle('active',i.dataset.target===e.target.id));});},{threshold:0.4});sections.forEach(s=>obs.observe(document.getElementById(s.id)));})();
})();
</script>
@endpush
