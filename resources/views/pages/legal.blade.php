@extends('layouts.site')

@section('content')
    <section class="hero-section page-section">
        <div class="site-container max-w-4xl text-center">
            <h1 class="text-4xl font-bold md:text-5xl">{{ $pageTitle }}</h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-[var(--hero-muted)]">
                {{ $pageIntro }}
            </p>
        </div>
    </section>

    <section class="page-section">
        <div class="site-container max-w-5xl space-y-6">
            @foreach ($sections as $section)
                <article class="card-service">
                    <h2 class="text-2xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                    <div class="mt-4 space-y-4 text-base leading-8 text-slate-600">
                        @foreach ($section['body'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
