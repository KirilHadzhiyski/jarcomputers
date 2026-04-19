@php
    $site = config('site');
    $canonical = $seo['canonical'] ?? url()->current();
    $sameAs = collect($site['socials'] ?? [])->pluck('href')->filter()->values()->all();
    $defaultDescription = 'Професионален ремонт на iPhone с гаранция и куриер в цяла България.';
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $site['company_name'],
        'url' => $canonical,
        'telephone' => $site['phone_e164'],
        'email' => $site['email'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $site['short_address'],
            'addressLocality' => $site['city_name'],
            'postalCode' => $site['postal_code'],
            'addressCountry' => 'BG',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => $site['coordinates']['lat'],
            'longitude' => $site['coordinates']['lng'],
        ],
        'sameAs' => $sameAs,
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'opens' => '09:00',
                'closes' => '19:00',
            ],
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Saturday'],
                'opens' => '10:00',
                'closes' => '15:00',
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="bg">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $seo['title'] ?? $site['brand'] }}</title>
        <meta name="description" content="{{ $seo['description'] ?? $defaultDescription }}">
        <meta name="robots" content="{{ $seo['robots'] ?? 'index,follow' }}">
        <link rel="canonical" href="{{ $canonical }}">

        <meta property="og:type" content="website">
        <meta property="og:locale" content="bg_BG">
        <meta property="og:site_name" content="{{ $site['brand'] }}">
        <meta property="og:title" content="{{ $seo['title'] ?? $site['brand'] }}">
        <meta property="og:description" content="{{ $seo['description'] ?? $defaultDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta name="twitter:card" content="summary_large_image">

        <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col">
        @include('partials.header')
        @include('partials.flash')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.footer')

        <div class="fixed bottom-4 right-4 z-40 flex flex-col gap-3">
            <a href="tel:{{ $site['phone_href'] }}" class="floating-cta" aria-label="Обади се">
                call
            </a>
            <a href="{{ route('contact') }}#repair-form" class="floating-cta" aria-label="Поръчай ремонт">
                form
            </a>
        </div>
    </body>
</html>
