<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex,nofollow">
    <title>{{ $seo['title'] ?? 'Админ меню' }} | JAR Computers Благоевград</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --background:#f8fafc; --border:#d0dce7; --primary:#1677f5; --radius:.65rem; --font-sans:Inter,sans-serif }
        body { font-family:Inter,sans-serif }
        .page-section { padding-top:2rem }
        .site-container { max-width:1280px; padding-inline:1rem }
        .card-soft { border-radius:.65rem; background:white; box-shadow:0 1px 2px #0000000d }
        .section-heading { font-size:1.65rem }
        .btn-secondary-dark { background:white; color:#334155; border-color:#d0dce7; border-radius:9999px }
        .nav-link-active { background:#1677f5; color:white }
    </style>
</head>
<body>
    <header class="border-b border-border bg-white"><div class="site-container flex flex-wrap items-center justify-between gap-4 py-5">
        <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Админ меню</p><a href="{{ route('admin.dashboard') }}" class="mt-1 block text-2xl font-extrabold">Оперативно табло</a><p class="mt-1 text-sm text-muted-foreground">Всичко е на български и е насочено към бърза реакция по заявките.</p></div>
        <div class="flex gap-3"><a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark">Към таблото</a><a href="{{ route('home') }}" class="btn-primary">Към сайта</a><form action="{{ route('logout') }}" method="POST">@csrf<button class="btn-secondary-dark">Изход</button></form></div>
    </div></header>
    @include('partials.flash')
    <main>@yield('content')</main>
</body>
</html>
