@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">Подготовка за Skroutz</h1>
    <p class="section-copy">Конфигурации, готовност на каталога и получени поръчки.</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <div class="card-soft mb-6">
        <h2 class="text-xl font-semibold">Свързване на акаунта по-късно</h2>
        <p class="mt-3 text-sm leading-6">Подгответе продуктите и прегледайте XML файла. При свързване ще са нужни търговски API token, потвърдени условия за доставка, комисиони и данъчна основа. Новите продукти се създават чрез XML; Products API обновява съществуващи продукти.</p>
        <p class="mt-3 text-sm">Публичен каталог: <strong>{{ config('skroutz.feed_enabled') ? 'Включен за IP адресите на Skroutz' : 'Изключен' }}</strong>. Адрес за бъдещо свързване: <code>{{ url('/feeds/skroutz.xml') }}</code></p>
        <p class="mt-2 text-sm">Адрес за поръчки: <code>{{ url('/webhooks/skroutz') }}</code>. Поръчките се проверяват през вашия API акаунт. Приемането на поръчки и публикуването на цени остават под ваш контрол.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('admin.integrations.index') }}#integration-skroutz" class="btn-primary">Настройки на достъпа</a>
            <a href="{{ route('admin.skroutz.xml') }}" class="btn-secondary-dark">Изтегли XML за преглед</a>
            <a href="{{ route('admin.skroutz.batch') }}" class="btn-secondary-dark">Преглед на API данните</a>
            <a href="{{ route('admin.pricing.configurations.create') }}" class="btn-secondary-dark">Нова конфигурация</a>
        </div>
        <p class="mt-4 text-xs text-muted-foreground">Прегледът на API данните е локален JSON файл. Няма изпращане или промяна на оферти в Skroutz. Публичният XML се включва от сървърната настройка SKROUTZ_FEED_ENABLED след готовност.</p>
    </div>
    <div class="space-y-4">
    @forelse ($configurations as $configuration)
        @php($missing = $catalog->missing($configuration))
        <article class="card-soft flex flex-col gap-4 md:flex-row md:justify-between">
            <div><h2 class="text-xl font-semibold">{{ $configuration->name }}</h2><p class="mt-2 text-sm">SKU: {{ $configuration->sku }}</p>
            <p class="mt-3 text-sm {{ $missing ? 'text-amber-700' : 'text-emerald-700' }}">{{ $missing ? 'Липсват / изискват проверка: '.implode(', ', $missing) : 'Готова за XML · цена в EUR с ДДС' }}</p></div>
            <a href="{{ route('admin.skroutz.edit', $configuration) }}" class="btn-primary self-start">Подготви конфигурацията</a>
        </article>
    @empty
        <div class="card-soft"><p>Все още няма конфигурации. Създайте ги от анализатора в „AI ценообразуване“ или с бутона „Нова конфигурация“.</p></div>
    @endforelse
    </div>
    <div class="mt-6">{{ $configurations->links() }}</div>
    <div class="card-soft mt-6"><h2 class="text-xl font-semibold">Последни получени поръчки</h2>
        @forelse ($orders as $order)<details class="mt-4 border-t pt-4"><summary>{{ $order->external_id }} · {{ $order->status }} · {{ $order->synced_at->format('d.m.Y H:i') }}</summary><pre class="mt-3 overflow-auto text-xs">{{ json_encode($order->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>
        @empty<p class="mt-3 text-sm text-muted-foreground">Няма получени поръчки. Връзката очаква достъп до вашия Skroutz акаунт.</p>@endforelse
    </div>
    <p class="mt-6 text-sm"><a class="text-primary" href="https://developer.skroutz.gr/products/xml_feed/" target="_blank" rel="noopener">XML спецификация</a> · <a class="text-primary" href="https://developer.skroutz.gr/products/api/" target="_blank" rel="noopener">Products API</a> · <a class="text-primary" href="https://developer.skroutz.gr/smart_cart/orders_webhook/" target="_blank" rel="noopener">Поръчки и webhook</a></p>
</div></section>
@endsection
