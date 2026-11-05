@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Сигурност', 'title' => 'Журнал на действията', 'description' => 'Проследяване на важните промени в настройките, профилите, интеграциите и работните процеси.'])
        <div class="mt-8">@include('partials.admin-nav')</div>

        <div class="card-soft">
            <form method="GET" class="flex flex-col gap-3 md:flex-row"><input name="search" value="{{ $search }}" class="input-shell max-w-xl" placeholder="Маршрут, събитие или адрес"><button class="btn-primary">Търси</button></form>
        </div>

        <div class="card-soft mt-8">
            @if ($logs->isEmpty())
                <div class="admin-empty-state">Няма намерени записи в журнала.</div>
            @else
                <div class="admin-table-wrap"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Дата</th><th class="px-4 py-3">Администратор</th><th class="px-4 py-3">Действие</th><th class="px-4 py-3">Маршрут</th><th class="px-4 py-3">Резултат</th><th class="px-4 py-3">Променени полета</th></tr></thead><tbody>@foreach ($logs as $log)<tr class="border-t border-border/60"><td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td><td class="px-4 py-3">{{ $log->user?->name ?? 'Система' }}</td><td class="px-4 py-3 font-medium">{{ $log->method }}</td><td class="px-4 py-3"><p>{{ $log->route_name ?? '—' }}</p><p class="mt-1 max-w-md truncate text-xs text-muted-foreground">{{ $log->path }}</p></td><td class="px-4 py-3">{{ $log->response_status }}</td><td class="px-4 py-3 text-xs text-muted-foreground">{{ implode(', ', $log->metadata['changed_fields'] ?? []) ?: '—' }}</td></tr>@endforeach</tbody></table></div>
                <div class="mt-8">{{ $logs->links() }}</div>
            @endif
        </div>
    </div></section>
@endsection
