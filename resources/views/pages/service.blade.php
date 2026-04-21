@extends('layouts.site')

@section('content')
    @php($site = config('site'))

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl">
            <h1 class="text-4xl font-bold text-balance md:text-5xl">
                {{ $service['name'] }} iPhone – бързо и с гаранция от
                <span class="gradient-text">{{ $site['brand'] }}</span>
            </h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-[var(--hero-muted)]">
                {{ $service['description'] }} Безплатна диагностика, куриер в двете посоки и гаранция до 12 месеца.
            </p>
            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                <a href="{{ route('contact') }}#repair-form" class="btn-primary">Поръчай ремонт</a>
                <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">Обади се</a>
            </div>
        </div>
    </section>

    @include('partials.trust-bar', ['items' => $trustItems])

    <section class="page-section">
        <div class="site-container max-w-4xl">
            <h2 class="section-heading">Професионален ремонт с куриерска услуга в цяла България</h2>
            <div class="mt-6 space-y-5 text-base leading-8 text-slate-600">
                <p>
                    {{ $site['brand'] }} предлага професионална услуга „{{ $service['name'] }}“ за всички модели iPhone.
                    Независимо дали се намирате в София, Пловдив, Варна, Бургас или друг град – ние изпращаме куриер до вашия адрес,
                    извършваме ремонта и връщаме устройството ви.
                </p>
                <p>
                    С над 10 години опит и повече от 5000 успешно ремонтирани устройства, ние гарантираме качество и надеждност.
                    Всеки ремонт идва с гаранция до 12 месеца.
                </p>
            </div>

            <div class="mt-8 card-soft">
                <h3 class="text-xl font-semibold text-slate-950">Какво включва услугата?</h3>
                <div class="bullet-list mt-5">
                    @foreach ([
                        'Безплатна диагностика на устройството',
                        'Качествени части с гаранция',
                        'Експресен ремонт 24–48 часа',
                        'Куриер в двете посоки – безплатно',
                        'Плащаш само ако одобриш цената',
                        'Възможност за -10% при онлайн поръчка',
                    ] as $bullet)
                        <div class="bullet-item">
                            <span class="bullet-dot">✓</span>
                            <span>{{ $bullet }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
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

    <section class="page-section">
        <div class="site-container text-center">
            <h2 class="section-heading">Цена за {{ mb_strtolower($service['name']) }}</h2>
            <a href="{{ route('pricing') }}" class="mt-4 inline-block text-5xl font-bold text-blue-700 transition hover:text-blue-800 hover:underline">{{ \App\Support\SiteData::formatPrice($service['price_from']) }}</a>
            <p class="mt-4 text-base text-slate-600">Окончателната цена зависи от модела и диагностиката.</p>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">{{ $service['name'] }} по модел</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($models as $model)
                    <div class="card-service text-center">
                        <a href="{{ url(\App\Support\SiteData::seoSlug($service, $model)) }}" class="block text-lg font-semibold text-slate-950">{{ $service['name'] }} {{ $model['name'] }}</a>
                        <a href="{{ route('pricing') }}" class="mt-2 inline-block text-sm text-blue-700 transition hover:text-blue-800 hover:underline">Виж ориентировъчна цена</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Обслужвани градове</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cities as $city)
                    <a href="{{ url($city['slug']) }}" class="card-service block text-center">
                        <h3 class="text-lg font-semibold text-slate-950">{{ $service['name'] }} iPhone {{ $city['name'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">Куриер до {{ $city['name'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqItems])
    @include('partials.cta-section')
@endsection
