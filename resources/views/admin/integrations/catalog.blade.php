@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">Каталог от JAR API</h1>
    <p class="section-copy">Цени и наличности при доставчика. Физическият склад на сервиза се управлява отделно в „Склад“.</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <form class="mb-6 flex flex-wrap gap-3" method="GET"><label class="sr-only" for="catalog-search">Търси по код или име</label><input id="catalog-search" name="q" value="{{ $search }}" class="input-shell max-w-lg" placeholder="Код или име на продукт"><button class="btn-primary">Търси</button></form>
    <div class="card-soft admin-table-wrap"><table class="min-w-full text-left text-sm"><thead><tr><th class="p-3">Продукт</th><th class="p-3">Дилърска цена</th><th class="p-3">Крайна цена</th><th class="p-3">Наличност при JAR</th><th class="p-3">Обновено</th></tr></thead><tbody>
    @forelse ($products as $product)
        <tr class="border-t border-border"><td class="p-3"><strong>{{ $product->name ?? $product->code }}</strong><p class="mt-1 text-xs text-muted-foreground">{{ $product->code }} · {{ $product->brand }} · {{ $product->category }}</p></td><td class="p-3 whitespace-nowrap">{{ $product->dealer_price !== null ? number_format($product->dealer_price, 2).' '.$product->currency_code : 'Няма данни' }}</td><td class="p-3 whitespace-nowrap">{{ $product->retail_price !== null ? number_format($product->retail_price, 2).' '.$product->currency_code : 'Няма данни' }}</td><td class="p-3">{{ $product->availability ?? 'Няма данни' }}</td><td class="p-3 whitespace-nowrap">{{ $product->synced_at->format('d.m.Y H:i') }}</td></tr>
    @empty<tr><td colspan="5" class="p-8 text-muted-foreground">Няма получени продукти. Свържете JAR акаунта в <a href="{{ route('admin.integrations.index') }}#integration-jar" class="text-primary">Интеграции</a>.</td></tr>@endforelse
    </tbody></table></div><div class="mt-6">{{ $products->links() }}</div>
</div></section>
@endsection
