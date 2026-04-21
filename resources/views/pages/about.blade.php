@extends('layouts.site')

@section('content')
    @php($site = config('site'))

    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">За {{ $site['brand'] }}</h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-[var(--hero-muted)]">
                От 2004 година работим на пазара в Благоевград като представител на JAR Computers,
                с фокус върху дистрибуция, продажби, сервиз и дългосрочна поддръжка.
            </p>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container max-w-5xl">
            <div class="space-y-5 text-base leading-8 text-slate-600">
                <p>
                    От началото на 2004 година развиваме дейността си в Благоевград като представител на JAR Computers
                    за региона. През годините изградихме стабилно присъствие както в продажбата и дистрибуцията на техника,
                    така и в сервизното обслужване на крайни клиенти и бизнеси.
                </p>
                <p>
                    Работим както с хардуерни продажби и доставки, така и със сервиз, диагностика и поддръжка.
                    Това ни позволява да предложим завършено обслужване – от консултация и доставка до ремонт,
                    гаранционно съдействие и последваща комуникация с клиента.
                </p>
                <p>
                    За ремонтите на iPhone разчитаме на ясен процес, реални срокове, видими цени и публични оценки.
                    Поддържаме физически обект в Благоевград и обслужваме клиенти от цяла България с куриер в двете посоки.
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
                <h2 class="section-heading">С какво помагаме</h2>
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

    @include('partials.review-summary', ['sectionClass' => 'page-section', 'sectionId' => 'reviews', 'eyebrow' => 'Публични отзиви и ревюта'])
    @include('partials.cta-section')
@endsection
