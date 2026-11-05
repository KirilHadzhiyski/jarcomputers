@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Клиенти', 'title' => 'Клиентски абонаменти', 'description' => 'Текущи и исторически планове, подновявания и външни billing идентификатори.'])
            <a href="{{ route('admin.client-subscriptions.create') }}" class="btn-primary">Нов абонамент</a>
        </div>
        <div class="mt-8">@include('partials.admin-nav')</div>
        <div class="card-soft"><div class="admin-table-wrap"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Клиент</th><th class="px-4 py-3">План</th><th class="px-4 py-3">Статус</th><th class="px-4 py-3">Начало</th><th class="px-4 py-3">Подновяване</th><th></th></tr></thead><tbody>
            @forelse ($subscriptions as $subscription)
                <tr class="border-t border-border/60"><td class="px-4 py-3">{{ $subscription->user?->name }}<p class="text-xs text-muted-foreground">{{ $subscription->user?->email }}</p></td><td class="px-4 py-3">{{ $subscription->plan?->name }}</td><td class="px-4 py-3"><span class="status-pill status-pill-{{ $subscription->status }}">{{ $statusLabels[$subscription->status] ?? $subscription->status }}</span></td><td class="px-4 py-3">{{ $subscription->starts_at?->format('d.m.Y') }}</td><td class="px-4 py-3">{{ $subscription->renews_at?->format('d.m.Y') ?? '—' }}</td><td class="px-4 py-3 text-right"><a class="font-medium text-primary" href="{{ route('admin.client-subscriptions.edit', $subscription) }}">Редакция</a></td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Няма абонаменти.</td></tr>
            @endforelse
        </tbody></table></div><div class="mt-8">{{ $subscriptions->links() }}</div></div>
    </div></section>
@endsection
