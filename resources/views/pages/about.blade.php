@extends('layouts.site')

@section('content')
    @php($site = config('site'))

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">За {{ $site['brand'] }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-[var(--hero-muted)]">
                Физически сервиз в Благоевград с официални контакти, публични рейтинги и обновена инфраструктура за заявки и комуникация.
            </p>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container max-w-5xl">
            <div class="space-y-5 text-base leading-8 text-slate-600">
                <p>
                    {{ $site['brand'] }} работи като специализиран сервиз за ремонт на iPhone с обслужване на място в Благоевград и логистика за клиенти от цяла България.
                    Сайтът вече е подготвен с централен backend за заявки, имейл известия и проследима история на комуникацията.
                </p>
                <p>
                    Контактите, адресът и работното време са подравнени по официалната информация за обекта. Добавени са готови конфигурации за собствен домейн,
                    фирмен имейл и webhook интеграции за WhatsApp, Viber и Facebook Messenger, така че след покупка на домейн да се настроят само реалните токени и DNS записи.
                </p>
                <p>
                    В публичната част на сайта са добавени snapshot рейтинги и статистики от наличните външни платформи, както и задължителни страници за поверителност,
                    общи условия, robots.txt и sitemap.xml.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="card-service text-center">
                        <p class="text-4xl font-bold text-blue-700">{{ $stat['value'] }}</p>
                        <p class="mt-3 text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="page-section section-soft">
        <div class="site-container max-w-5xl">
            <div class="text-center">
                <h2 class="section-heading">Какво е подготвено</h2>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach ($values as $value)
                    <div class="card-service text-center">
                        <span class="badge-mark mx-auto">{{ mb_substr($value['title'], 0, 1) }}</span>
                        <h3 class="mt-5 text-xl font-semibold text-slate-950">{{ $value['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $value['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.review-summary', ['sectionClass' => 'page-section'])
    @include('partials.cta-section')
@endsection
