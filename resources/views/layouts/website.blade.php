<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $canonicalUrl = url()->current();
        $ogImage      = file_exists(public_path('images/og-image.jpg'))
                            ? asset('images/og-image.jpg')
                            : asset('images/logo-dark.png');
        $locale       = app()->getLocale();
        $ogLocale     = ['tr'=>'tr_TR','en'=>'en_US','de'=>'de_DE'][$locale] ?? 'tr_TR';
        $seoSetting   = \App\Modules\Core\Models\RestaurantSetting::current();
        $openTime     = substr($seoSetting->open_time  ?? '09:00', 0, 5);
        $closeTime    = substr($seoSetting->close_time ?? '02:00', 0, 5);
        $defaultDesc  = config('restaurant.tagline.' . $locale);
    @endphp

    <title>@yield('title', 'Ana Sayfa') — Müdavim Restaurant</title>
    <meta name="description"  content="@yield('meta_description', $defaultDesc)">
    <meta name="keywords"     content="Müdavim Restaurant, Palamutbükü restoran, Datça restoran, Ege yemekleri, Akdeniz mutfağı, denize sıfır restoran, Datça yemek, Palamutbükü yemek, balık restoran Datça">
    <meta name="robots"       content="index, follow">
    <meta name="author"       content="Müdavim Restaurant">
    <meta name="theme-color"  content="#1a7fa8">
    <meta name="csrf-token"   content="{{ csrf_token() }}">
    <link rel="canonical"     href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:type"             content="restaurant">
    <meta property="og:site_name"        content="Müdavim Restaurant">
    <meta property="og:url"              content="{{ $canonicalUrl }}">
    <meta property="og:title"            content="@yield('title', 'Ana Sayfa') — Müdavim Restaurant">
    <meta property="og:description"      content="@yield('meta_description', $defaultDesc)">
    <meta property="og:image"            content="{{ $ogImage }}">
    <meta property="og:image:width"      content="1200">
    <meta property="og:image:height"     content="630">
    <meta property="og:image:alt"        content="Müdavim Restaurant — Palamutbükü">
    <meta property="og:image:type"       content="image/jpeg">
    <meta property="og:locale"           content="{{ $ogLocale }}">
    <meta property="og:locale:alternate" content="tr_TR">
    <meta property="og:locale:alternate" content="en_US">

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('title', 'Ana Sayfa') — Müdavim Restaurant">
    <meta name="twitter:description" content="@yield('meta_description', $defaultDesc)">
    <meta name="twitter:image"       content="{{ $ogImage }}">
    <meta name="twitter:image:alt"   content="Müdavim Restaurant — Palamutbükü">

    {{-- hreflang --}}
    <link rel="alternate" hreflang="tr"      href="{{ url(request()->path()) }}">
    <link rel="alternate" hreflang="en"      href="{{ url('en/' . request()->path()) }}">
    <link rel="alternate" hreflang="de"      href="{{ url('de/' . request()->path()) }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    {{-- Schema.org JSON-LD — PHP array ile oluşturulur (@context Blade çakışması önlemek için) --}}
    @php
        $schemaOrg = json_encode([
            '@context' => 'https://schema.org',
            '@type'    => 'Restaurant',
            'name'            => 'Müdavim Restaurant',
            'alternateName'   => 'Müdavim Palamutbükü',
            'url'             => url('/'),
            'logo'            => asset('images/logo-dark.png'),
            'image'           => $ogImage,
            'description'     => config('restaurant.tagline.tr'),
            'telephone'       => config('restaurant.phone'),
            'email'           => config('restaurant.email', 'info@mudavimpalamutbuku.com'),
            'address'         => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'Cumalı Mahallesi, Palamutbükü Sokak No:50',
                'addressLocality' => 'Datça',
                'addressRegion'   => 'Muğla',
                'postalCode'      => '48900',
                'addressCountry'  => 'TR',
            ],
            'geo' => [
                '@type'    => 'GeoCoordinates',
                'latitude' => (float) config('restaurant.coordinates.lat'),
                'longitude'=> (float) config('restaurant.coordinates.lng'),
            ],
            'openingHoursSpecification' => [[
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
                'opens'     => $openTime,
                'closes'    => $closeTime,
            ]],
            'servesCuisine'    => ['Akdeniz Mutfağı','Türk Mutfağı','Deniz Ürünleri'],
            'priceRange'       => '₺₺₺',
            'currenciesAccepted' => 'TRY',
            'paymentAccepted'  => 'Cash, Credit Card',
            'hasMap'           => 'https://maps.google.com/?q=' . config('restaurant.coordinates.lat') . ',' . config('restaurant.coordinates.lng'),
            'sameAs'           => [config('restaurant.social.instagram'), config('restaurant.social.facebook')],
            'amenityFeature'   => [
                ['@type'=>'LocationFeatureSpecification','name'=>'Ücretsiz Wi-Fi','value'=>true],
                ['@type'=>'LocationFeatureSpecification','name'=>'Evcil Hayvan Dostu','value'=>true],
                ['@type'=>'LocationFeatureSpecification','name'=>'Deniz Manzarası','value'=>true],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    @endphp
    <script type="application/ld+json">{!! $schemaOrg !!}</script>

    @vite(['resources/css/website.css', 'resources/js/website.js'])
    <style>
    html{scrollbar-gutter:stable}
    @keyframes ambPulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(29,185,84,.5)}50%{opacity:.7;box-shadow:0 0 0 4px rgba(29,185,84,0)}}
    </style>
    @stack('styles')
</head>
<body>

{{-- Season banner --}}
@php
    $isOpen = \App\Modules\Core\Services\RestaurantService::isOpen();
@endphp
@unless($isOpen)
    <div class="season-banner">
        {{ __('common.season_closed') }}
    </div>
@endunless

@include('partials.navbar')

{{-- Main content --}}
<main>
    @yield('content')
</main>

@include('partials.footer')

{{-- WhatsApp float --}}
<a href="https://wa.me/{{ ltrim(config('restaurant.whatsapp'), '+') }}?text={{ urlencode('Merhaba, rezervasyon yapmak istiyorum.') }}" target="_blank"
   class="whatsapp-float" title="WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

{{-- Paylaşım JS --}}
<script>
function sharePage(customTitle, customText, customUrl) {
    const title = customTitle || document.title;
    const text  = customText  || 'Müdavim Restaurant — Palamutbükü\'nde denize sıfır Akdeniz mutfağı. Mutlaka görün!';
    const url   = customUrl   || window.location.href;

    if (navigator.share) {
        navigator.share({ title, text, url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(function() {
            const btn = event?.target?.closest('button');
            if (btn) {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(() => btn.innerHTML = orig, 2000);
            } else {
                alert('Link kopyalandı: ' + url);
            }
        });
    }
}
</script>

@stack('scripts')
</body>
</html>
