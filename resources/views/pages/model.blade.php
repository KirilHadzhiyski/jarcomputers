@extends('layouts.site')

@section('content')
    @php($site = config('site'))

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl">
            <h1 class="text-4xl font-bold text-balance md:text-5xl">
                Ремонт на {{ $model['name'] }} – бързо и с гаранция от
                <span class="gradient-text">{{ $site['brand'] }}</span>
            </h1>
            <p class="mt-6 text-lg leading-8 text-[var(--hero-muted)]">
                Професионален ремонт на {{ $model['name'] }} с куриерска услуга в цяла България. Безплатна диагностика.
            </p>
            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                <a href="{{ route('contact') }}#repair-form" class="btn-primary">Поръчай ремонт</a>
                <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">Обади се</a>
            </div>
        </div>
    </section>

    @include('partials.trust-bar', ['items' => $trustItems])

    <section class="page-section">
        <div class="site-container max-w-5xl">
            <h2 class="section-heading">Чести проблеми с {{ $model['name'] }}</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ($problems as $problem)
                    <div class="card-service flex items-center gap-3">
                        <span class="bullet-dot">•</span>
                        <span class="text-sm font-medium text-slate-700">{{ $problem }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Услуги за {{ $model['name'] }}</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    @php($price = collect($pricing)->firstWhere('service', $service['name']))
                    <a href="{{ url(\App\Support\SiteData::seoSlug($service, $model)) }}" class="card-service block text-center">
                        <h3 class="text-lg font-semibold text-slate-950">{{ $service['name'] }}</h3>
                        <p class="mt-3 text-2xl font-bold text-blue-700">{{ $price['price'] ?? 'Попитайте' }}</p>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">с гаранция до 12 мес.</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Как работи процесът?</h2>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-5">
                @foreach ($steps as $step)
                    <div class="card-service text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground">{{ $step['num'] }}</div>
                        <h3 class="mt-4 text-base font-semibold text-slate-950">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Ремонт на {{ $model['name'] }} по градове</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cities as $city)
                    <a href="{{ url($city['slug']) }}" class="card-service block text-center">
                        <h3 class="text-lg font-semibold text-slate-950">Ремонт {{ $model['name'] }} {{ $city['name'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">Куриер до {{ $city['name'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqItems])
    @include('partials.cta-section')
@endsection
