@extends('layouts.site')

@section('content')
    @php($site = config('site'))

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">Свържете се с нас</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-[var(--hero-muted)]">
                Телефон, имейл и подготвени чат канали. Онлайн формата записва заявката директно в backend системата.
            </p>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container">
            <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr]">
                <div>
                    <h2 class="section-heading">Заявка за ремонт</h2>
                    <div class="mt-8">
                        @include('partials.repair-form', ['sourcePage' => request()->getPathInfo()])
                    </div>
                </div>

                <div>
                    <h2 class="section-heading">Контактна информация</h2>
                    <div class="mt-8 space-y-5">
                        <a href="tel:{{ $site['phone_href'] }}" class="card-service block">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Мобилен телефон</p>
                            <p class="mt-3 text-2xl font-bold text-blue-700">{{ $site['phone'] }}</p>
                        </a>

                        <a href="tel:{{ $site['landline_href'] }}" class="card-service block">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Стационарен телефон</p>
                            <p class="mt-3 text-2xl font-bold text-slate-950">{{ $site['landline'] }}</p>
                        </a>

                        <a href="mailto:{{ $site['email'] }}" class="card-service block">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Публичен имейл</p>
                            <p class="mt-3 text-xl font-bold text-slate-950">{{ $site['email'] }}</p>
                        </a>

                        <div class="card-service">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Адрес</p>
                            <p class="mt-3 text-base leading-7 text-slate-700">{{ $site['address'] }}</p>
                            <a href="{{ $site['google_maps_url'] }}" class="btn-secondary-dark mt-5" target="_blank" rel="noreferrer">
                                Отвори в Google Maps
                            </a>
                        </div>

                        <div class="card-service">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Чат канали</p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                @foreach ($messagingChannels as $channel)
                                    <a href="{{ $channel['href'] }}" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700" target="_blank" rel="noreferrer">
                                        {{ $channel['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="card-service">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Работно време</p>
                            <div class="mt-4 space-y-2 text-sm leading-7 text-slate-600">
                                @foreach ($site['hours'] as $hoursLine)
                                    <p>{{ $hoursLine }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.review-summary', ['sectionClass' => 'page-section section-soft', 'eyebrow' => 'Публични рейтинги'])
@endsection
