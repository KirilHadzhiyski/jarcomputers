@extends('layouts.site')

@section('content')
    @php
        $formatEuro = static fn (?string $value): string => \App\Support\SiteData::formatPrice($value);
        $modelColumns = collect($models)
            ->filter(fn (array $model): bool => in_array($model['series'], ['11', '12', '13', '14', '15', '16'], true))
            ->values()
            ->all();
    @endphp

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">Цени за ремонт на iPhone</h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-[var(--hero-muted)]">
                Ориентировъчни цени в евро за най-честите ремонти на iPhone 11 до iPhone 16.
                Окончателната цена се потвърждава след безплатна диагностика.
            </p>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container max-w-7xl">
            <div class="grid gap-6 lg:hidden">
                @foreach ($services as $service)
                    @php($row = collect($pricingTable)->firstWhere('service', $service['name']))
                    <div class="card-service">
                        <h3 class="text-xl font-semibold text-slate-950">{{ $service['name'] }}</h3>
                        <div class="mt-5 space-y-3 text-sm">
                            @foreach ($modelColumns as $pricingModel)
                                @php($priceKey = 'iphone'.$pricingModel['series'])
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                    <span>{{ $pricingModel['name'] }}</span>
                                    <span class="font-semibold text-blue-700">{{ $formatEuro($row[$priceKey] ?? null) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden overflow-hidden rounded-[2rem] border bg-white shadow-[0_24px_55px_-32px_rgba(15,23,42,0.35)] lg:block">
                <table class="pricing-table w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="text-left">Услуга</th>
                            @foreach ($modelColumns as $pricingModel)
                                <th class="text-center">{{ $pricingModel['name'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pricingTable as $row)
                            <tr class="border-t border-slate-100">
                                <td class="font-semibold text-slate-950">{{ $row['service'] }}</td>
                                @foreach ($modelColumns as $pricingModel)
                                    @php($priceKey = 'iphone'.$pricingModel['series'])
                                    <td class="text-center font-semibold text-blue-700">{{ $formatEuro($row[$priceKey] ?? null) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-soft mt-8 text-center text-sm leading-7 text-slate-600">
                <p>
                    * Цените са ориентировъчни и са изписани в евро.
                    <br>Окончателната цена се определя след безплатна диагностика.
                    <br>Всички ремонти идват с гаранция до 12 месеца.
                    <br>-10% отстъпка при онлайн поръчка.
                </p>
            </div>
        </div>
    </section>

    @include('partials.cta-section')
@endsection
