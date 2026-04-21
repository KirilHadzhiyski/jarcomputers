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
                        Диагностика, сервиз и проследим процес за ремонт на iPhone с ясни цени,
                        реални публични отзиви и куриерска услуга в двете посоки.
                    </p>
                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('contact') }}#repair-form" class="btn-primary">Поръчай ремонт</a>
                        <a href="{{ route('pricing') }}" class="btn-secondary">Виж цени</a>
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
    @include('partials.review-summary', ['sectionClass' => 'page-section', 'sectionId' => 'reviews', 'eyebrow' => 'Google и публични оценки'])

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Гаранция, куриер и срок за ремонт</h2>
                <p class="section-copy mx-auto">
                    Това са трите най-важни неща, за които клиентите ни питат още преди да изпратят телефона.
                </p>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                <article id="warranty" class="card-service">
                    <span class="badge-mark">12</span>
                    <h3 class="mt-5 text-xl font-semibold text-slate-950">Гаранция до 12 месеца</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Даваме гаранция за вложените части и извършената работа според конкретния ремонт.
                        Тя покрива дефекти, свързани с ремонта, и не важи за нови удари, намокряне или външни повреди след приемането.
                    </p>
                    <a href="{{ route('faq') }}" class="btn-secondary-dark mt-6">Виж повече за гаранцията</a>
                </article>

                <article id="courier" class="card-service">
                    <span class="badge-mark">Е</span>
                    <h3 class="mt-5 text-xl font-semibold text-slate-950">Куриер в двете посоки с Еконт</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Изпращаме и връщаме устройствата с Еконт. Можем да организираме взимане от адрес или от удобен офис,
                        а след ремонта телефонът се връща обратно по същия канал.
                    </p>
                    <a href="https://officelocator.econt.com/" class="btn-secondary-dark mt-6" target="_blank" rel="noreferrer">Офиси на Еконт</a>
                </article>

                <article id="express-service" class="card-service">
                    <span class="badge-mark">24</span>
                    <h3 class="mt-5 text-xl font-semibold text-slate-950">Експресен ремонт 24-48 часа</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        За най-честите ремонти като дисплей, батерия и камера работим в рамките на 24 до 48 часа след получаване,
                        диагностика и потвърждение от клиента. По-сложните случаи зависят от наличността на части.
                    </p>
                    <a href="{{ route('contact') }}#repair-form" class="btn-secondary-dark mt-6">Заяви експресен ремонт</a>
                </article>
            </div>
        </div>
    </section>

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
                    <div class="card-service">
                        <span class="badge-mark">{{ $service['badge'] }}</span>
                        <a href="{{ url($service['slug']) }}" class="mt-5 block text-xl font-semibold text-slate-950">{{ $service['name'] }}</a>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $service['description'] }}</p>
                        <a href="{{ route('pricing') }}" class="mt-4 inline-block text-sm font-semibold text-blue-700 transition hover:text-blue-800 hover:underline">{{ \App\Support\SiteData::formatPrice($service['price_from']) }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Как работи?</h2>
                <p class="section-copy mx-auto">
                    Всяка заявка минава през ясен процес – от приемането до връщането на ремонтирания телефон.
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
                    Реални клиентски оценки, физически обект в Благоевград и ясен сервизен процес за клиенти от цяла България.
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
                    Изпращаме и връщаме устройствата с куриер, независимо в кой град се намирате.
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
                Окончателната цена зависи от диагностиката. При всеки ремонт получавате ясна оценка преди започване на работа.
            </p>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    <a href="{{ route('pricing') }}" class="card-service block text-center">
                        <h3 class="text-lg font-semibold text-slate-950">{{ $service['short_name'] }}</h3>
                        <p class="mt-3 text-3xl font-bold text-blue-700">{{ \App\Support\SiteData::formatPrice($service['price_from']) }}</p>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">с гаранция до 12 мес.</p>
                    </a>
                @endforeach
            </div>

            <a href="{{ route('pricing') }}" class="btn-secondary-dark mt-8">Виж всички цени</a>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqHome])
    @include('partials.cta-section')
@endsection
