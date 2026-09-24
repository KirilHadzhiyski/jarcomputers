@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">Документи от JAR</h1>
    <p class="section-copy">Получени поръчки и документи за вашия JAR акаунт. Сумите не се отчитат автоматично като клиентски приходи или платени фактури.</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <div class="mb-6 flex flex-wrap gap-3"><a class="btn-secondary-dark" href="{{ route('admin.integrations.documents') }}">Всички</a>@foreach (['orders'=>'Поръчки','documents'=>'Документи','invoices'=>'Фактури'] as $key=>$label)<a class="btn-secondary-dark" href="{{ route('admin.integrations.documents', ['kind'=>$key]) }}">{{ $label }}</a>@endforeach</div>
    <div class="card-soft admin-table-wrap"><table class="min-w-full text-left text-sm"><thead><tr><th class="p-3">Тип / номер</th><th class="p-3">Статус</th><th class="p-3">Сума</th><th class="p-3">Данни от API</th></tr></thead><tbody>
    @forelse ($documents as $document)
        <tr class="border-t border-border"><td class="p-3">{{ ['orders'=>'Поръчка','documents'=>'Документ','invoices'=>'Фактура'][$document->kind] ?? $document->kind }} #{{ $document->external_id }}<p class="text-xs text-muted-foreground">{{ $document->document_date?->format('d.m.Y') }} {{ $document->reference }}</p></td><td class="p-3">{{ $document->status ?? 'Няма данни' }}</td><td class="p-3 whitespace-nowrap">{{ $document->total !== null ? number_format($document->total,2).' '.$document->currency_code : 'Няма данни' }}</td><td class="p-3"><details><summary class="cursor-pointer text-primary">Преглед</summary><pre class="mt-3 max-w-lg whitespace-pre-wrap break-all text-xs">{{ json_encode($document->payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></details></td></tr>
    @empty<tr><td colspan="4" class="p-8 text-muted-foreground">Няма получени документи. Настройте JAR връзката в „Интеграции“.</td></tr>@endforelse
    </tbody></table></div><div class="mt-6">{{ $documents->links() }}</div>
</div></section>
@endsection
