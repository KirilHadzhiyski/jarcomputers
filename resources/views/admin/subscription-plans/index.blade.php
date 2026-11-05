@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Абонаменти', 'title' => 'Абонаментни планове', 'description' => 'Лимити за работни процеси, обработени записи, AI бюджет и месечна цена.'])
            <a href="{{ route('admin.subscription-plans.create') }}" class="btn-primary">Нов план</a>
        </div>
        <div class="mt-8">@include('partials.admin-nav')</div>
        <div class="card-soft">
            @if ($plans->isEmpty())
                <div class="admin-empty-state">Няма създадени абонаментни планове.</div>
            @else
                <div class="admin-table-wrap"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">План</th><th class="px-4 py-3">Цена</th><th class="px-4 py-3">Процеси</th><th class="px-4 py-3">Записи / месец</th><th class="px-4 py-3">Абонаменти</th><th class="px-4 py-3">Статус</th><th></th></tr></thead><tbody>
                    @foreach ($plans as $plan)
                        <tr class="border-t border-border/60"><td class="px-4 py-3"><p class="font-semibold">{{ $plan->name }}</p><p class="text-xs text-muted-foreground">{{ $plan->code }}</p></td><td class="px-4 py-3">{{ number_format((float) $plan->monthly_price, 2) }} {{ $plan->currency_code }}</td><td class="px-4 py-3">{{ $plan->workflow_limit }}</td><td class="px-4 py-3">{{ number_format($plan->records_per_month) }}</td><td class="px-4 py-3">{{ $plan->subscriptions_count }}</td><td class="px-4 py-3">{{ $plan->is_active ? 'Активен' : 'Спрян' }}</td><td class="px-4 py-3 text-right"><a class="font-medium text-primary" href="{{ route('admin.subscription-plans.edit', $plan) }}">Редакция</a></td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="mt-8">{{ $plans->links() }}</div>
            @endif
        </div>
    </div></section>
@endsection
