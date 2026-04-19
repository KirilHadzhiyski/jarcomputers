@extends('layouts.site')

@section('content')
    <section class="hero-section page-section">
        <div class="site-container max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">404</p>
            <h1 class="mt-4 text-4xl font-bold text-balance md:text-5xl">
                Страницата не беше намерена
            </h1>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-[var(--hero-muted)] md:text-lg">
                Вероятно адресът е променен или страницата вече не съществува. Можете да се върнете към основните услуги и контактите ни.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('home') }}" class="btn-primary">Начало</a>
                <a href="{{ route('contact') }}" class="btn-secondary">Контакти</a>
            </div>
        </div>
    </section>
@endsection
