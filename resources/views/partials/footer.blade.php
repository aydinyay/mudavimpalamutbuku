<footer class="footer-mudavim">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim Restaurant" style="height:56px;width:auto;opacity:0.9;margin-bottom:8px;display:block;">
                <p class="small opacity-75">{{ config('restaurant.tagline.' . app()->getLocale()) }}</p>
                <div class="d-flex gap-3 mt-3 align-items-center">
                    <a href="{{ config('restaurant.social.instagram') }}" target="_blank"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="{{ config('restaurant.social.facebook') }}" target="_blank"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="https://g.page/r/Cd4zYQe_40RuEBM/review" target="_blank" rel="noopener"
                       class="btn btn-sm d-flex align-items-center gap-1"
                       style="background:rgba(255,255,255,0.12);color:#fff;border-radius:20px;padding:4px 12px;font-size:.78rem;border:1px solid rgba(255,255,255,.2);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 488 512" fill="currentColor"><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>
                        Değerlendir
                    </a>
                </div>
            </div>
            <div class="col-lg-4">
                <h6 style="color:#e8d5b0;font-weight:700;" class="mb-3">{{ __('common.address') }}</h6>
                <p class="small opacity-75 mb-1">
                    <a href="{{ route('website.contact') }}" style="color:inherit;text-decoration:none;">
                        {{ config('restaurant.address.' . app()->getLocale()) }}
                    </a>
                </p>
                <p class="small opacity-75 mb-1">
                    <i class="bi bi-telephone-fill me-1"></i>
                    <a href="tel:{{ config('restaurant.phone') }}">{{ config('restaurant.phone') }}</a>
                </p>
                <p class="small opacity-75">
                    <i class="bi bi-clock-fill me-1"></i>
                    @php $footerSetting = \App\Modules\Core\Models\RestaurantSetting::current(); @endphp
                    {{ substr($footerSetting->open_time ?? '09:00', 0, 5) }} – {{ substr($footerSetting->close_time ?? '02:00', 0, 5) }}, Haftanın 7 Günü
                </p>
            </div>
            <div class="col-lg-4">
                <h6 style="color:#e8d5b0;font-weight:700;" class="mb-3">{{ __('common.nav_reserve') }}</h6>
                <a href="{{ route('reservation.public.create') }}"
                   class="btn btn-sm" style="background:var(--color-coral);color:#fff;border-radius:20px;">
                    {{ __('common.nav_reserve') }}
                </a>
                <div class="mt-3 d-flex gap-2">
                    <span class="badge bg-secondary"><i class="bi bi-wifi me-1"></i>{{ __('common.free_wifi') }}</span>
                    <span class="badge bg-secondary"><i class="bi bi-heart me-1"></i>{{ __('common.pet_friendly') }}</span>
                </div>
            </div>
        </div>
        <hr class="mt-4" style="border-color:rgba(255,255,255,0.1);">
        <div class="row g-2 mb-3">
            <div class="col-12 text-center">
                <p class="small mb-1" style="opacity:.55;font-size:.75rem;letter-spacing:.03em;">
                    <span style="color:#e8d5b0;font-weight:600;">HK Seyahat Turizm ve Dış Tic. Ltd. Şti.</span>
                    <span class="mx-2 opacity-40">|</span>
                    Halaskargazi Mah. Zafer Sok. 55, Nişantaşı – Şişli / İstanbul
                    <span class="mx-2 opacity-40">|</span>
                    <a href="tel:02122337748" style="color:inherit;text-decoration:none;">0212 233 77 48</a>
                </p>
                <p class="small mb-0" style="opacity:.6;font-size:.72rem;display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;">
                    <a href="https://www.supratur.com" target="_blank" rel="noopener"
                       style="display:inline-flex;align-items:center;background:rgba(255,255,255,0.12);border-radius:6px;padding:3px 8px;">
                        <img src="{{ asset('images/supra-group.png') }}" alt="Supra Group"
                             style="height:22px;width:auto;vertical-align:middle;"
                             onerror="this.parentElement.style.display='none'">
                    </a>
                    <span style="color:#c8b99a;">Müdavim Palamutbükü bir Supra Group markasıdır.</span>
                </p>
            </div>
        </div>
        <p class="text-center small opacity-50 mb-0">
            &copy; {{ date('Y') }} Müdavim Restaurant. {{ __('common.footer_rights') }}
        </p>
        <p class="text-center mb-0" style="opacity:.3;font-size:.68rem;margin-top:6px;">
            Powered by <a href="https://www.gruptalepleri.com" target="_blank" rel="noopener"
               style="color:#c8b99a;text-decoration:none;">GrupTalepleri.com</a>
        </p>
    </div>
</footer>
