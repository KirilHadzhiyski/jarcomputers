@extends('layouts.admin')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Админ / Клиенти',
                    'title' => 'Клиенти и абонаменти',
                    'description' => 'Месечни планове, периодични плащания, лимити и разходи за външни AI/API услуги.',
                ])
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.client-subscriptions.create') }}" class="btn-primary">Нов абонамент</a>
                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn-secondary-dark">Планове</a>
                </div>
            </div>

            <div class="mt-8">@include('partials.admin-nav')</div>

            <div class="admin-shell-grid">
                <article class="admin-kpi-card"><p class="admin-stat-label">Активни абонаменти</p><p class="mt-3 text-3xl font-semibold">{{ $summary['active_count'] }}</p><p class="mt-2 text-xs text-muted-foreground">+ {{ $summary['trial_count'] }} в пробен период</p></article>
                <article class="admin-kpi-card"><p class="admin-stat-label">Месечен приход</p>@forelse ($summary['mrr_by_currency'] as $currency => $total)<p class="mt-3 text-3xl font-semibold">{{ number_format($total, 2) }} {{ $currency }}</p>@empty<p class="mt-3 text-3xl font-semibold">0.00</p>@endforelse</article>
                <article class="admin-kpi-card"><p class="admin-stat-label">AI/API разход този месец</p>@forelse ($summary['usage_cost_by_currency'] as $currency => $total)<p class="mt-3 text-3xl font-semibold">{{ number_format($total, 2) }} {{ $currency }}</p>@empty<p class="mt-3 text-3xl font-semibold">0.00</p>@endforelse<p class="mt-2 text-xs text-muted-foreground">AI записи: {{ number_format($summary['ai_cost_this_month'], 2) }}</p></article>
                <article class="admin-kpi-card"><p class="admin-stat-label">Нужда от внимание</p><p class="mt-3 text-3xl font-semibold">{{ $summary['past_due_count'] + $summary['failed_payments_this_month'] }}</p><p class="mt-2 text-xs text-muted-foreground">Просрочени абонаменти и неуспешни плащания</p></article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr,0.8fr]">
                <div class="card-soft">
                    <div class="flex items-center justify-between gap-3"><h2 class="text-xl font-semibold">Последни абонаменти</h2><a href="{{ route('admin.client-subscriptions.index') }}" class="text-sm font-medium text-primary">Всички</a></div>
                    <div class="admin-table-wrap mt-6">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground"><tr><th class="px-4 py-3">Клиент</th><th class="px-4 py-3">План</th><th class="px-4 py-3">Статус</th><th class="px-4 py-3">Подновяване</th></tr></thead>
                            <tbody>
                                @forelse ($subscriptions as $subscription)
                                    <tr class="border-t border-border/60"><td class="px-4 py-3"><a class="font-medium text-primary" href="{{ route('admin.client-subscriptions.edit', $subscription) }}">{{ $subscription->user?->name }}</a></td><td class="px-4 py-3">{{ $subscription->plan?->name }}</td><td class="px-4 py-3"><span class="status-pill status-pill-{{ $subscription->status }}">{{ $subscriptionStatusLabels[$subscription->status] ?? $subscription->status }}</span></td><td class="px-4 py-3">{{ $subscription->renews_at?->format('d.m.Y') ?? '—' }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Няма абонаменти.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-soft">
                    <h2 class="text-xl font-semibold">Разход по доставчик</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Текущият календарен месец.</p>
                    <div class="mt-6 space-y-3">
                        @forelse ($usageByProvider as $row)
                            <div class="rounded-2xl border border-border/60 bg-background p-4"><div class="flex justify-between gap-3"><span class="font-medium">{{ $row->provider }}</span><span>{{ number_format((float) $row->total_cost, 4) }} {{ $row->currency_code }}</span></div><p class="mt-2 text-xs text-muted-foreground">Количество: {{ number_format((float) $row->total_quantity, 2) }}</p></div>
                        @empty
                            <div class="admin-empty-state">Няма отчетено потребление.</div>
                        @endforelse
                    </div>
                    <a href="{{ route('admin.usage-records.index') }}" class="btn-secondary-dark mt-6">Подробен отчет</a>
                </div>
            </div>

            <div class="card-soft mt-8">
                <div class="flex items-center justify-between gap-3"><h2 class="text-xl font-semibold">Последни абонаментни плащания</h2><a href="{{ route('admin.subscription-payments.index') }}" class="text-sm font-medium text-primary">Всички плащания</a></div>
                <div class="admin-table-wrap mt-6"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Клиент</th><th class="px-4 py-3">Сума</th><th class="px-4 py-3">Период</th><th class="px-4 py-3">Статус</th></tr></thead><tbody>
                    @forelse ($payments as $payment)
                        <tr class="border-t border-border/60"><td class="px-4 py-3">{{ $payment->user?->name }}</td><td class="px-4 py-3">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency_code }}</td><td class="px-4 py-3">{{ $payment->period_start?->format('d.m.Y') ?? '—' }} – {{ $payment->period_end?->format('d.m.Y') ?? '—' }}</td><td class="px-4 py-3"><span class="status-pill status-pill-{{ $payment->status }}">{{ $paymentStatusLabels[$payment->status] ?? $payment->status }}</span></td></tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Няма абонаментни плащания.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
        </div>
    </section>
@endsection
