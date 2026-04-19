@extends('layouts.site')

@section('content')
    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">Често задавани въпроси</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-[var(--hero-muted)]">
                Всичко, което трябва да знаете за нашите услуги.
            </p>
        </div>
    </section>

    @include('partials.faq-section', ['items' => $faqItems, 'title' => ''])
    @include('partials.cta-section')
@endsection
