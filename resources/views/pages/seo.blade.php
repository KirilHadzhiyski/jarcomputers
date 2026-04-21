@extends('layouts.site')

@section('content')
    @php($site = config('site'))
    @php($service = $page['service'])
    @php($model = $page['model'])

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl">
            <h1 class="text-4xl font-bold text-balance md:text-5xl">
                {{ $service['name'] }} {{ $model['name'] }} – бързо и с гаранция от
                <span class="gradient-text">{{ $site['brand'] }}</span>
            </h1>
            <p class="mt-6 text-lg leading-8 text-[var(--hero-muted)]">
                Професионален ремонт на {{ $model['name'] }} с куриерска услуга в цяла България.
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
            <h2 class="section-heading">Професионална {{ mb_strtolower($service['name']) }} за {{ $model['name'] }}</h2>
            <div class="mt-6 space-y-5 text-base leading-8 text-slate-600">
                <p>
                    Вашият {{ $model['name'] }} се нуждае от {{ mb_strtolower($service['name']) }}? {{ $site['brand'] }} предлага бърза и надеждна услуга
                    с качествени части и гаранция до 12 месеца. Благодарение на нашата куриерска услуга можете да ни изпратите
                    устройството от всяка точка на България.
                </p>
                <p>
                    С над 10 години опит в ремонта на Apple устройства и повече от 5000 успешно ремонтирани телефона, ние сме специалисти
                    в {{ mb_strtolower($service['name']) }} на {{ $model['name'] }}. Диагностиката е безплатна и плащате само ако одобрите предложената цена.
                </p>
            </div>

            <div class="mt-8 card-soft">
                <h3 class="text-xl font-semibold text-slate-950">Какво включва услугата?</h3>
                <div class="bullet-list mt-5">
                    @foreach ([
                        'Безплатна диагностика',
                        'Качествени части с гаранция',
                        'Експресен ремонт 24–48 часа',
                        'Куриер в двете посоки безплатно',
                        'Плащане само при одобрение',
                        '-10% при онлайн поръчка',
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
            <h2 class="section-heading">Цена за {{ mb_strtolower($service['name']) }} на {{ $model['name'] }}</h2>
            <a href="{{ route('pricing') }}" class="mt-4 inline-block text-5xl font-bold text-blue-700 transition hover:text-blue-800 hover:underline">{{ \App\Support\SiteData::formatPrice($service['price_from']) }}</a>
            <p class="mt-4 text-base text-slate-600">Окончателната цена зависи от диагностиката.</p>
            <p class="mt-2 text-sm text-slate-500">Време за ремонт: 24–48 часа · Гаранция: до 12 месеца</p>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Свързани услуги</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $related)
                    @continue($related['slug'] === $service['slug'])
                    <div class="card-service text-center">
                        <a href="{{ url(\App\Support\SiteData::seoSlug($related, $model)) }}" class="block text-lg font-semibold text-slate-950">{{ $related['name'] }} {{ $model['name'] }}</a>
                        <a href="{{ route('pricing') }}" class="mt-3 inline-block text-lg font-bold text-blue-700 transition hover:text-blue-800 hover:underline">{{ \App\Support\SiteData::formatPrice($related['price_from']) }}</a>
                    </div>
                @endforeach
                <a href="{{ url($model['slug']) }}" class="card-service block text-center">
                    <h3 class="text-lg font-semibold text-slate-950">Всички услуги за {{ $model['name'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">Виж повече за модела</p>
                </a>
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
                        <h3 class="text-sm font-semibold text-slate-950">{{ $service['name'] }} {{ $model['name'] }} {{ $city['name'] }}</h3>
                        <p class="mt-2 text-xs text-slate-600">Куриер до {{ $city['name'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqItems])
    @include('partials.cta-section')
@endsection
