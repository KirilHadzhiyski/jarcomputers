@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Мониторинг', 'title' => 'Изпълнения на процесите', 'description' => 'История на обработените данни, грешките и разходите за външни AI услуги.'])
        <div class="mt-8">@include('partials.admin-nav')</div>

        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="admin-kpi-card"><p class="admin-stat-label">На опашка</p><p class="mt-3 text-3xl font-semibold">{{ $monitoring['queued'] }}</p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Изпълняват се</p><p class="mt-3 text-3xl font-semibold">{{ $monitoring['running'] }}</p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Грешки днес</p><p class="mt-3 text-3xl font-semibold">{{ $monitoring['failed_today'] }}</p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Scheduler heartbeat</p><p class="mt-3 text-sm font-semibold">{{ $monitoring['scheduler_heartbeat'] ? \Illuminate\Support\Carbon::parse($monitoring['scheduler_heartbeat'])->format('d.m.Y H:i:s') : 'Няма получен сигнал' }}</p></article>
        </div>

        <div class="card-soft">
            <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="admin-field min-w-72"><label for="workflow_id" class="text-sm font-medium">Работен процес</label><select id="workflow_id" name="workflow_id" class="input-shell"><option value="">Всички процеси</option>@foreach ($workflows as $workflow)<option value="{{ $workflow->id }}" @selected($selectedWorkflowId === $workflow->id)>{{ $workflow->name }}</option>@endforeach</select></div>
                <button class="btn-primary">Филтрирай</button>
            </form>
        </div>

        <div class="card-soft mt-8">
            @if ($runs->isEmpty())
                <div class="admin-empty-state">Все още няма изпълнения. Стартирайте активен процес ръчно или изчакайте неговия график.</div>
            @else
                <div class="admin-table-wrap"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Процес</th><th class="px-4 py-3">Статус</th><th class="px-4 py-3">Събрани</th><th class="px-4 py-3">Съпоставени</th><th class="px-4 py-3">Грешки</th><th class="px-4 py-3">AI разход</th><th class="px-4 py-3">Начало</th></tr></thead><tbody>@foreach ($runs as $run)<tr class="border-t border-border/60"><td class="px-4 py-3"><a class="font-medium text-primary" href="{{ route('admin.workflow-runs.show', $run) }}">{{ $run->workflow?->name }} #{{ $run->id }}</a></td><td class="px-4 py-3"><span class="status-pill status-pill-{{ $run->status }}">{{ $statusLabels[$run->status] ?? $run->status }}</span></td><td class="px-4 py-3">{{ $run->records_collected }}</td><td class="px-4 py-3">{{ $run->records_matched }}</td><td class="px-4 py-3">{{ $run->error_count }}</td><td class="px-4 py-3">{{ number_format((float) $run->ai_cost, 4) }}</td><td class="px-4 py-3">{{ $run->started_at?->format('d.m.Y H:i') ?? '—' }}</td></tr>@endforeach</tbody></table></div>
                <div class="mt-8">{{ $runs->links() }}</div>
            @endif
        </div>

        <div class="card-soft mt-8"><h2 class="text-xl font-semibold">Активни сигнали</h2><div class="mt-5 space-y-3">@forelse ($alerts as $alert)<article class="rounded-2xl border border-border/70 p-4"><div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-semibold">{{ $alert->title }}</p><p class="mt-1 text-sm text-muted-foreground">{{ $alert->message }}</p><p class="mt-2 text-xs text-muted-foreground">{{ $alert->workflow?->name ?? 'Системен сигнал' }} · {{ $alert->occurred_at?->format('d.m.Y H:i') }}</p></div><div class="flex items-center gap-2"><span class="status-pill status-pill-{{ $alert->severity }}">{{ $alertSeverityLabels[$alert->severity] ?? $alert->severity }}</span>@if ($alert->status === 'open')<form method="POST" action="{{ route('admin.workflow-alerts.acknowledge', $alert) }}">@csrf @method('PATCH')<button class="btn-secondary-dark px-3 py-1.5">Прегледан</button></form>@endif</div></div></article>@empty<div class="admin-empty-state">Няма активни сигнали.</div>@endforelse</div></div>
    </div></section>
@endsection
