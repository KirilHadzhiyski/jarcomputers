@extends('layouts.site')

@section('content')
    @php
        $site = config('site');
        $accent = $model['accent'] ?? '#2563eb';
    @endphp

    <section class="hero-section page-section" style="--model-accent: {{ $accent }};">
        <div class="site-container max-w-6xl">
            <div class="model-hero-grid">
                <div>
                    <p class="model-carousel-eyebrow">Премиум сервиз за iPhone</p>
                    <h1 class="text-4xl font-bold text-balance md:text-5xl">
                        Ремонт на {{ $model['name'] }} с бърза диагностика и гаранция от
                        <span class="gradient-text">{{ $site['brand'] }}</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-[var(--hero-muted)]">
                        Професионален ремонт на {{ $model['name'] }} с куриерска услуга в цяла България.
                        Получавате ясна оценка, безплатна диагностика и сервизен процес, който следим от заявката до връщането на телефона.
                    </p>
                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('contact') }}#repair-form" class="btn-primary">Поръчай ремонт</a>
                        <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">Обади се</a>
                    </div>
                </div>

                <div class="model-visual-stage">
                    <span class="model-stage-kicker">Модел {{ $model['series'] }}</span>
                    <img
                        src="{{ asset($model['image']) }}"
                        alt="{{ $model['name'] }}"
                        class="model-stage-image"
                    >
                    <div class="model-stage-caption">
                        <span>Безплатна диагностика</span>
                        <span>Гаранция до 12 месеца</span>
                        <span>Куриер в двете посоки</span>
                    </div>
                </div>
            </div>

            <div class="model-carousel-shell" data-carousel-root>
                <div class="model-carousel-top">
                    <div>
                        <p class="model-carousel-eyebrow">Избери модел</p>
                        <h2 class="model-carousel-title">iPhone 11 до iPhone 16</h2>
                    </div>
                    <div class="model-carousel-actions">
                        <button type="button" class="model-carousel-button" data-carousel-prev aria-label="Предишен модел">
                            &#8592;
                        </button>
                        <button type="button" class="model-carousel-button" data-carousel-next aria-label="Следващ модел">
                            &#8594;
                        </button>
                    </div>
                </div>

                <div class="model-carousel-track" data-carousel-track>
                    @foreach ($models as $carouselModel)
                        @php($isActive = $carouselModel['slug'] === $model['slug'])
                        <a
                            href="{{ url($carouselModel['slug']) }}"
                            class="model-carousel-card"
                            data-carousel-item
                            @if ($isActive) aria-current="page" data-active-model @endif
                            style="--model-accent: {{ $carouselModel['accent'] ?? $accent }};"
                        >
                            <div class="model-carousel-thumb">
                                <img src="{{ asset($carouselModel['image']) }}" alt="{{ $carouselModel['name'] }}">
                            </div>
                            <div class="model-carousel-labels">
                                <h3 class="model-carousel-name">{{ $carouselModel['name'] }}</h3>
                                @if ($isActive)
                                    <span class="model-carousel-note">Текущ модел</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @include('partials.trust-bar', ['items' => $trustItems])

    <section class="page-section">
        <div class="site-container max-w-5xl">
            <div class="text-center">
                <h2 class="section-heading">Чести проблеми с {{ $model['name'] }}</h2>
                <p class="section-copy mx-auto">
                    Това са най-честите симптоми, с които пристига {{ $model['name'] }} в сервиза.
                    Ако проблемът ви е различен, можем да го установим при диагностика.
                </p>
            </div>
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
                <p class="section-copy mx-auto">
                    Най-поръчваните ремонти за {{ $model['name'] }} с ориентировъчни цени в евро.
                </p>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    @php($price = collect($pricing)->firstWhere('service', $service['name']))
                    <div class="card-service text-center">
                        <a href="{{ url(\App\Support\SiteData::seoSlug($service, $model)) }}" class="block">
                            <h3 class="text-lg font-semibold text-slate-950">{{ $service['name'] }}</h3>
                        </a>
                        <a href="{{ route('pricing') }}" class="mt-3 inline-block text-2xl font-bold text-blue-700 transition hover:text-blue-800 hover:underline">
                            {{ $price['price'] ?? 'Попитайте ни' }}
                        </a>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">с гаранция до 12 мес.</p>
                    </div>
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
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground">
                            {{ $step['num'] }}
                        </div>
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
