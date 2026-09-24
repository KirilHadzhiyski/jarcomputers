<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex,nofollow">
    <title>Админ табло | JAR Computers Благоевград</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @if (file_exists(public_path('admin-build/manifest.json')))
        @php($entry = json_decode(file_get_contents(public_path('admin-build/manifest.json')), true)['src/admin/main.tsx'])
        @foreach ($entry['css'] ?? [] as $css)<link rel="stylesheet" href="{{ asset('admin-build/'.$css) }}">@endforeach
        <script type="module" src="{{ asset('admin-build/'.$entry['file']) }}"></script>
    @endif
</head>
<body>
    <div id="root"><p>Зареждане на оперативното табло…</p><a href="{{ route('admin.operations') }}">Отвори сървърния панел</a></div>
    <noscript>За оперативното табло е нужен JavaScript. <a href="{{ route('admin.operations') }}">Управление на заявките и интеграциите</a></noscript>
</body>
</html>
