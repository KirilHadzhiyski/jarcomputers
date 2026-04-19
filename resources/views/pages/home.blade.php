@extends('layouts.site')

@section('content')
    @php
        $site = config('site');
        $primaryReview = collect($reviewPlatforms)->firstWhere('primary', true) ?? $reviewPlatforms[0] ?? null;
    @endphp

    <section class="hero-section hero-with-image">
        <div class="hero-inner page-section">
            <div class="site-container">
                <div class="max-w-3xl">
                    <div class="trust-badge">Физически сервиз в Благоевград · куриер в цяла България</div>
                    <h1 class="mt-6 text-4xl font-bold leading-tight text-balance md:text-5xl lg:text-6xl">
                        Професионален ремонт на iPhone от
                        <span class="gradient-text">{{ $site['brand'] }}</span>
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-[var(--hero-muted)] md:text-xl">
                        Обновен backend за заявки, имейл известия и готовност за WhatsApp, Viber и Messenger интеграции при пускане на домейна.
                    </p>
                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('contact') }}#repair-form" class="btn-primary">Поръчай ремонт</a>
                        <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">Попитай за цена</a>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3 text-sm font-medium text-muted-foreground">
                        <span>Безплатна диагностика</span>
                        <span>Проследима комуникация</span>
                        <span>Плащаш само при одобрение</span>
                        @if ($primaryReview)
                            <span>{{ number_format((float) $primaryReview['rating_value'], 1) }}/{{ $primaryReview['rating_scale'] }} {{ $primaryReview['label'] }} · {{ $primaryReview['reviews_count'] }} оценки</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.trust-bar', ['items' => $trustItems])
    @include('partials.review-summary', ['sectionClass' => 'page-section', 'eyebrow' => 'Публични оценки'])

    <section class="page-section">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Най-търсени услуги</h2>
                <p class="section-copy mx-auto">
                    Специализирани сме в ремонт на iPhone – от смяна на дисплей и батерия до Face ID и камера.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    <a href="{{ url($service['slug']) }}" class="card-service block">
                        <span class="badge-mark">{{ $service['badge'] }}</span>
                        <h3 class="mt-5 text-xl font-semibold text-slate-950">{{ $service['name'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $service['description'] }}</p>
                        <p class="mt-4 text-sm font-semibold text-blue-700">от {{ $service['price_from'] }} лв</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Как работи?</h2>
                <p class="section-copy mx-auto">
                    Формата, имейлите и чат каналите вече влизат в един и същ backend поток, така че всяка заявка остава проследима.
                </p>
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

    <section class="page-section">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Защо да изберете {{ $site['brand'] }}?</h2>
                <p class="section-copy mx-auto">
                    Реални клиентски оценки, официални контакти, ясни условия и подготвена инфраструктура за публикация на собствен домейн.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($whyUs as $item)
                    <div class="card-service">
                        <span class="badge-mark">{{ mb_substr($item['title'], 0, 1) }}</span>
                        <h3 class="mt-5 text-xl font-semibold text-slate-950">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Ремонт на iPhone в цяла България</h2>
                <p class="section-copy mx-auto">
                    Куриерска услуга в двете посоки – без значение къде се намирате.
                </p>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cities as $city)
                    <a href="{{ url($city['slug']) }}" class="card-service block text-center">
                        <h3 class="text-lg font-semibold text-slate-950">Ремонт на iPhone {{ $city['name'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Куриер до {{ $city['name'] }} и обратно</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container text-center">
            <h2 class="section-heading">Ориентировъчни цени</h2>
            <p class="section-copy mx-auto">
                Окончателната цена зависи от диагностиката. Безплатна диагностика при всеки ремонт.
            </p>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    <div class="card-service text-center">
                        <h3 class="text-lg font-semibold text-slate-950">{{ $service['short_name'] }}</h3>
                        <p class="mt-3 text-3xl font-bold text-blue-700">от {{ $service['price_from'] }} лв</p>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">с гаранция до 12 мес.</p>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('pricing') }}" class="btn-secondary-dark mt-8">Виж всички цени</a>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqHome])
    @include('partials.cta-section')
@endsection
