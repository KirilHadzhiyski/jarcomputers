@extends('layouts.site')

@section('content')
    @php($site = config('site'))

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl">
            <h1 class="text-4xl font-bold text-balance md:text-5xl">
                Ремонт на iPhone {{ $city['name'] }} – куриерска услуга от
                <span class="gradient-text">{{ $site['brand'] }}</span>
            </h1>
            <p class="mt-6 text-lg leading-8 text-[var(--hero-muted)]">
                Живеете в {{ $city['name'] }}? Изпращаме куриер до вашия адрес, ремонтираме iPhone-а ви и го връщаме – бързо, надеждно и с гаранция.
            </p>
            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                <a href="#repair-form" class="btn-primary">Поръчай ремонт от {{ $city['name'] }}</a>
                <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">Обади се</a>
            </div>
        </div>
    </section>

    @include('partials.trust-bar', ['items' => $trustItems])

    <section class="page-section">
        <div class="site-container max-w-4xl">
            <h2 class="section-heading">Как работи ремонтът от {{ $city['name'] }}?</h2>
            <div class="mt-6 space-y-5 text-base leading-8 text-slate-600">
                <p>
                    {{ $site['brand'] }} обслужва клиенти от {{ $city['name'] }} чрез надеждна куриерска услуга.
                    Всички ремонти се извършват в нашия специализиран сервиз в Благоевград, оборудван с професионални инструменти и качествени части.
                </p>
                <p>
                    Процесът е прост: поръчвате онлайн, куриер идва до вас, ние ремонтираме и връщаме.
                    Целият процес отнема 3–5 работни дни. Безплатна диагностика и плащане само при одобрение.
                </p>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-5">
                @foreach ($steps as $step)
                    <div class="card-service text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary font-bold text-primary-foreground">{{ $step['num'] }}</div>
                        <h3 class="mt-4 text-sm font-semibold text-slate-950">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-xs leading-6 text-slate-600">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container">
            <div class="text-center">
                <h2 class="section-heading">Услуги за {{ $city['name'] }}</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    <a href="{{ url($service['slug']) }}" class="card-service block text-center">
                        <h3 class="text-lg font-semibold text-slate-950">{{ $service['name'] }}</h3>
                        <p class="mt-3 text-2xl font-bold text-blue-700">от {{ $service['price_from'] }} лв</p>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">с гаранция до 12 мес.</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container max-w-3xl">
            <div class="text-center">
                <h2 class="section-heading">Заявка за ремонт от {{ $city['name'] }}</h2>
            </div>
            <div class="mt-8">
                @include('partials.repair-form', ['sourcePage' => request()->getPathInfo()])
            </div>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqItems])
    @include('partials.cta-section', ['title' => "Ремонт на iPhone от {$city['name']} с {$site['brand']}"])
@endsection
