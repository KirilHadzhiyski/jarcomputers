@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Абонаменти', 'title' => 'Абонаментни плащания', 'description' => 'История на месечните такси, неуспешните плащания и възстановяванията.'])
            <a href="{{ route('admin.subscription-payments.create') }}" class="btn-primary">Ново плащане</a>
        </div>
        <div class="mt-8">@include('partials.admin-nav')</div>
        <div class="card-soft"><div class="admin-table-wrap"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Клиент</th><th class="px-4 py-3">План</th><th class="px-4 py-3">Сума</th><th class="px-4 py-3">Период</th><th class="px-4 py-3">Статус</th><th class="px-4 py-3">Доставчик</th><th></th></tr></thead><tbody>
            @forelse ($payments as $payment)
                <tr class="border-t border-border/60"><td class="px-4 py-3">{{ $payment->user?->name }}</td><td class="px-4 py-3">{{ $payment->subscription?->plan?->name }}</td><td class="px-4 py-3">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency_code }}</td><td class="px-4 py-3">{{ $payment->period_start?->format('d.m.Y') ?? '—' }} – {{ $payment->period_end?->format('d.m.Y') ?? '—' }}</td><td class="px-4 py-3"><span class="status-pill status-pill-{{ $payment->status }}">{{ $statusLabels[$payment->status] ?? $payment->status }}</span></td><td class="px-4 py-3">{{ $payment->provider ?: 'Ръчно' }}</td><td class="px-4 py-3 text-right"><a class="font-medium text-primary" href="{{ route('admin.subscription-payments.edit', $payment) }}">Редакция</a></td></tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Няма записани абонаментни плащания.</td></tr>
            @endforelse
        </tbody></table></div><div class="mt-8">{{ $payments->links() }}</div></div>
    </div></section>
@endsection
