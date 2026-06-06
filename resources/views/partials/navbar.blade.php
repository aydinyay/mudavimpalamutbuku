<nav class="navbar navbar-expand-lg navbar-mudavim fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ app()->getLocale() === 'tr' ? url('/') : url(app()->getLocale() . '/') }}">
            <img src="{{ asset('images/logo-light.png') }}" alt="Müdavim Restaurant" style="height:56px;width:auto;">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('website.home') }}">{{ __('common.nav_home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('website.about') }}">{{ __('common.nav_about') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('menu.public.index') }}">{{ __('common.nav_menu') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('website.contact') }}">{{ __('common.nav_contact') }}</a></li>
                @php $ambianceSetting = \App\Modules\Core\Models\RestaurantSetting::current(); @endphp
                @if($ambianceSetting->ambiance_page_active ?? true)
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="{{ route('website.ambiance') }}"
                       style="color:rgba(255,255,255,.85);">
                        <span style="width:7px;height:7px;border-radius:50%;background:#1DB954;display:inline-block;animation:ambPulse 2s infinite;"></span>
                        Şu An
                    </a>
                </li>
                @endif
                <li class="nav-item ms-lg-1">
                    <button onclick="sharePage()" class="btn btn-sm btn-outline-light"
                            style="border-radius:20px;padding:5px 12px;border-color:rgba(255,255,255,.3);"
                            title="Paylaş">
                        <i class="bi bi-share"></i>
                    </button>
                </li>
                <li class="nav-item ms-lg-1">
                    <a class="btn btn-sm" style="background:var(--color-coral);color:#fff;border-radius:20px;padding:6px 16px;"
                       href="{{ route('reservation.public.create') }}">
                        {{ __('common.nav_reserve') }}
                    </a>
                </li>
            </ul>
            <div class="locale-switcher ms-lg-3 mt-2 mt-lg-0 d-flex gap-1">
                @foreach(['tr','en','de'] as $loc)
                    <button class="btn btn-sm {{ app()->getLocale() === $loc ? 'btn-light' : 'btn-outline-light' }}"
                            data-locale="{{ $loc }}">
                        {{ strtoupper($loc) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</nav>
