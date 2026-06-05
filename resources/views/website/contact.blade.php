@extends('layouts.website')

@section('title', __('common.nav_contact'))

@section('content')
<div style="padding-top:80px;"></div>

{{-- Contact Info --}}
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
            <div class="col-md-4 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 488 512" fill="#4285F4"><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>
                <h5 class="mt-2">Google Yorum</h5>
                <p class="text-muted small mb-3">Deneyiminizi paylaşın</p>
                <a href="https://g.page/r/Cd4zYQe_40RuEBM/review" target="_blank" rel="noopener"
                   class="btn btn-sm" style="background:#4285F4;color:#fff;border-radius:20px;padding:6px 18px;">
                    ★ Değerlendir
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Seasonal Note --}}
<div style="background:linear-gradient(135deg,var(--color-sea),#1a5f7a);color:#fff;padding:14px 0;text-align:center;">
    <div class="container">
        <span style="font-family:'Cormorant Garamond',Georgia,serif;font-size:1.05rem;font-style:italic;opacity:.92;">
            🌊 &nbsp;Mayıs – Ekim arası açığız &nbsp;·&nbsp; Sezon dışı ziyaret için lütfen önceden arayın
            &nbsp;·&nbsp; <a href="tel:{{ config('restaurant.phone') }}" style="color:#fff;text-decoration:underline;">{{ config('restaurant.phone') }}</a>
        </span>
    </div>
</div>

{{-- How to Get Here --}}
<section class="py-5" style="background:#f8f5ef;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Buraya Nasıl Gelirsiniz?</h2>
            <div class="section-divider"></div>
            <p class="text-muted">Palamutbükü, Datça yarımadasının ucunda — her yoldan ulaşmak mümkün.</p>
        </div>

        <div class="row g-3 mb-4">

            {{-- By Car --}}
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 h-100 text-center" style="border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);">
                    <div class="card-body p-4">
                        <div style="font-size:2.5rem;margin-bottom:.75rem;">🚗</div>
                        <h5 class="fw-700 mb-3">Araçla</h5>
                        <div class="d-flex flex-column gap-2 text-start">
                            <div style="background:#f0f7fa;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#1a7fa8;">Marmaris'ten</div>
                                <div style="font-size:.88rem;color:#555;">~1.5 saat · 85 km</div>
                            </div>
                            <div style="background:#f0f7fa;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#1a7fa8;">Bodrum'dan</div>
                                <div style="font-size:.88rem;color:#555;">~2.5 saat · 130 km</div>
                            </div>
                            <div style="background:#f0f7fa;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#1a7fa8;">İzmir'den</div>
                                <div style="font-size:.88rem;color:#555;">~4.5 saat · 280 km</div>
                            </div>
                            <div style="background:#f0f7fa;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#1a7fa8;">İstanbul'dan</div>
                                <div style="font-size:.88rem;color:#555;">~7 saat · 860 km</div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">Datça'ya girdikten sonra Palamutbükü yönünü takip edin, son 18 km'lik kıyı yolu dar ve virajlıdır — dikkatli sürünüz.</p>
                    </div>
                </div>
            </div>

            {{-- By Ferry --}}
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 h-100 text-center" style="border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);">
                    <div class="card-body p-4">
                        <div style="font-size:2.5rem;margin-bottom:.75rem;">🚢</div>
                        <h5 class="fw-700 mb-3">Feribotla</h5>

                        {{-- Warning --}}
                        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:10px;padding:9px 12px;text-align:left;margin-bottom:.75rem;">
                            <div style="font-size:.78rem;color:#795500;font-weight:600;">
                                ⚠️ Dikkat: Feribot sabah erken saate
                            </div>
                            <div style="font-size:.75rem;color:#795500;margin-top:3px;">
                                Google Maps feribot önerebilir — ancak günlük 1 sefer, sabah hareket eder. Akşam yemeği için geliyorsanız karayolunu tercih edin.
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2 text-start">
                            <div style="background:#fff3e0;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#e08c00;">Bodrum → Datça</div>
                                <div style="font-size:.82rem;color:#555;">Kalkış: ~09:00 · ~2 saat</div>
                                <div style="font-size:.78rem;color:#888;">Dönüş: ~16:00 · Sezonluk (May–Eki)</div>
                            </div>
                            <div style="background:#fff3e0;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#e08c00;">Marmaris → Datça</div>
                                <div style="font-size:.82rem;color:#555;">Hızlı feribot · ~1 saat · sezonluk</div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">
                            Datça iskelesinden Palamutbükü ~30 dk.
                            Transfer gerekirse <a href="https://wa.me/{{ ltrim(config('restaurant.whatsapp'),'+') }}?text={{ urlencode('Feribot transferi hakkında bilgi almak istiyorum.') }}" target="_blank" style="color:var(--color-sea);">bize yazın</a>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- By Boat --}}
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 h-100 text-center" style="border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);">
                    <div class="card-body p-4">
                        <div style="font-size:2.5rem;margin-bottom:.75rem;">⛵</div>
                        <h5 class="fw-700 mb-3">Tekneyle</h5>
                        <div style="background:#e8f5e9;border-radius:10px;padding:12px 14px;text-align:left;margin-bottom:.75rem;">
                            <div style="font-weight:600;font-size:.82rem;color:#2d8a4e;">Palamutbükü Koyu</div>
                            <div style="font-size:.8rem;color:#555;margin-top:4px;">
                                36°40′31″ K / 27°30′34″ D<br>
                                <span style="font-family:monospace;font-size:.75rem;">36.6752, 27.5096</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">Koyda demir atabilir ya da bizim iskeleye yanaşabilirsiniz.</p>
                        <p class="text-muted small mb-0" style="font-style:italic;">
                            Tekne rezervasyonu için arayın — sahil masası ayırtıyoruz. 🌊
                        </p>
                    </div>
                </div>
            </div>

            {{-- By Bus --}}
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 h-100 text-center" style="border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);">
                    <div class="card-body p-4">
                        <div style="font-size:2.5rem;margin-bottom:.75rem;">🚌</div>
                        <h5 class="fw-700 mb-3">Otobüs & Dolmuş</h5>
                        <div class="d-flex flex-column gap-2 text-start">
                            <div style="background:#f3e5f5;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#7b3fa0;">Marmaris veya Bodrum'dan</div>
                                <div style="font-size:.88rem;color:#555;">Datça'ya otobüs var</div>
                            </div>
                            <div style="background:#f3e5f5;border-radius:10px;padding:10px 14px;">
                                <div style="font-weight:600;font-size:.82rem;color:#7b3fa0;">Datça → Palamutbükü</div>
                                <div style="font-size:.88rem;color:#555;">Sezonluk dolmuş · Yoksa taksi</div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">Toplu taşıma planınız varsa bizi önceden arayın, sizi almaya geliyoruz. 🤝</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Directions CTA --}}
        <div class="text-center mt-2 mb-2">
            <a href="https://maps.google.com/?q={{ config('restaurant.coordinates.lat') }},{{ config('restaurant.coordinates.lng') }}"
               target="_blank" rel="noopener"
               class="btn btn-lg"
               style="background:var(--color-sea);color:#fff;border-radius:30px;padding:14px 36px;font-weight:700;font-size:1rem;">
                <i class="bi bi-map me-2"></i>Google Maps'te Aç
            </a>
        </div>
    </div>
</section>

{{-- Practical Info + Transfer --}}
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="row g-4">

            {{-- Parking & Practical --}}
            <div class="col-md-6">
                <div class="card border-0 h-100" style="border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3">
                            <i class="bi bi-p-circle-fill me-2" style="color:var(--color-sea);"></i>
                            Park & Pratik Bilgiler
                        </h5>
                        <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                            <li class="d-flex gap-3">
                                <span style="font-size:1.3rem;flex-shrink:0;">🅿️</span>
                                <div>
                                    <strong>Ücretsiz Park</strong>
                                    <p class="text-muted small mb-0">Restorana ~50 metre mesafede açık alan park mevcuttur. Sezon doruk döneminde erken gelin.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span style="font-size:1.3rem;flex-shrink:0;">⚓</span>
                                <div>
                                    <strong>Tekne Bağlama</strong>
                                    <p class="text-muted small mb-0">Koyda halka bağlama noktaları mevcut. Önceden bilgi almak için arayın.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span style="font-size:1.3rem;flex-shrink:0;">🌿</span>
                                <div>
                                    <strong>Piknik & Kamp Alanı</strong>
                                    <p class="text-muted small mb-0">Restoranın yakınında koy kenarında doğal piknik alanları mevcut.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span style="font-size:1.3rem;flex-shrink:0;">📶</span>
                                <div>
                                    <strong>Ücretsiz Wi-Fi</strong>
                                    <p class="text-muted small mb-0">Tüm masa ve sahil alanında ücretsiz internet erişimi.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span style="font-size:1.3rem;flex-shrink:0;">🐾</span>
                                <div>
                                    <strong>Evcil Hayvan Dostu</strong>
                                    <p class="text-muted small mb-0">Dört ayaklı dostlarınız bizimle memnuniyetle ağırlanır.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Transfer Service --}}
            <div class="col-md-6">
                <div class="card border-0 h-100" style="border-radius:16px;background:linear-gradient(135deg,#1a7fa8 0%,#2ea887 100%);color:#fff;">
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-700 mb-2">
                            🚐 &nbsp;Sizi Alalım
                        </h5>
                        <p class="mb-3" style="opacity:.9;font-size:.95rem;">
                            Datça merkez veya feribot iskelesinden transfer organize edebiliyoruz.
                            Grup rezervasyonlarında ücretsiz; bireysel ziyaretlerde uygun fiyatlı.
                        </p>
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-4" style="opacity:.88;font-size:.88rem;">
                            <li><i class="bi bi-check-circle-fill me-2"></i>Datça merkez transfer</li>
                            <li><i class="bi bi-check-circle-fill me-2"></i>Datça feribot iskelesi karşılama</li>
                            <li><i class="bi bi-check-circle-fill me-2"></i>Gece dönüşü için de organize ediyoruz</li>
                            <li><i class="bi bi-check-circle-fill me-2"></i>4+ kişi rezervasyonlarda öncelik</li>
                        </ul>
                        <div class="mt-auto d-flex flex-column gap-2">
                            <a href="https://wa.me/{{ ltrim(config('restaurant.whatsapp'),'+') }}?text={{ urlencode('Merhaba! Transfer hakkında bilgi almak istiyorum.') }}"
                               target="_blank" rel="noopener"
                               class="btn"
                               style="background:#25d366;color:#fff;border-radius:30px;padding:12px 24px;font-weight:700;font-size:.95rem;border:none;">
                                <i class="bi bi-whatsapp me-2"></i>WhatsApp'tan Transfer Sor
                            </a>
                            <a href="tel:{{ config('restaurant.phone') }}"
                               class="btn"
                               style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:30px;padding:10px 24px;font-size:.9rem;">
                                <i class="bi bi-telephone me-2"></i>{{ config('restaurant.phone') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Map --}}
<section class="pb-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Haritada Görün</h2>
            <div class="section-divider"></div>
        </div>
        <div style="border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1);">
            <iframe
                src="https://maps.google.com/maps?q=Müdavim+Palamutbükü+Datça&z=17&output=embed"
                width="100%" height="420" style="border:0;" allowfullscreen loading="lazy">
            </iframe>
        </div>
        <div class="text-center mt-3">
            <a href="https://maps.google.com/?q={{ config('restaurant.coordinates.lat') }},{{ config('restaurant.coordinates.lng') }}"
               target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-map me-1"></i>Google Maps'te Aç
            </a>
        </div>
    </div>
</section>

@endsection
