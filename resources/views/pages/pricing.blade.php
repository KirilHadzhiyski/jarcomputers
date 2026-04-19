@extends('layouts.site')

@section('content')
    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">Цени за ремонт на iPhone</h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-[var(--hero-muted)]">
                Ориентировъчни цени. Окончателната цена зависи от диагностиката. Безплатна диагностика при всеки ремонт.
            </p>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container max-w-6xl">
            <div class="grid gap-6 lg:hidden">
                @foreach ($services as $service)
                    @php($row = collect($pricingTable)->firstWhere('service', $service['name']))
                    <div class="card-service">
                        <h3 class="text-xl font-semibold text-slate-950">{{ $service['name'] }}</h3>
                        <div class="mt-5 space-y-3 text-sm">
                            @foreach (['11', '12', '13', '14'] as $series)
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                    <span>iPhone {{ $series }}</span>
                                    <span class="font-semibold text-blue-700">{{ $row["iphone{$series}"] }}</span>
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
                            <th class="text-center">iPhone 11</th>
                            <th class="text-center">iPhone 12</th>
                            <th class="text-center">iPhone 13</th>
                            <th class="text-center">iPhone 14</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pricingTable as $row)
                            <tr class="border-t border-slate-100">
                                <td class="font-semibold text-slate-950">{{ $row['service'] }}</td>
                                <td class="text-center font-semibold text-blue-700">{{ $row['iphone11'] }}</td>
                                <td class="text-center font-semibold text-blue-700">{{ $row['iphone12'] }}</td>
                                <td class="text-center font-semibold text-blue-700">{{ $row['iphone13'] }}</td>
                                <td class="text-center font-semibold text-blue-700">{{ $row['iphone14'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-soft mt-8 text-center text-sm leading-7 text-slate-600">
                <p>
                    * Цените са ориентировъчни. Окончателната цена се определя след безплатна диагностика.
                    <br>Всички ремонти идват с гаранция до 12 месеца.
                    <br>-10% отстъпка при онлайн поръчка.
                </p>
            </div>
        </div>
    </section>

    @include('partials.cta-section')
@endsection
