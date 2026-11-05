@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Мониторинг', 'title' => 'Изпълнения на процесите', 'description' => 'История на обработените данни, грешките и разходите за външни AI услуги.'])
        <div class="mt-8">@include('partials.admin-nav')</div>

        <div class="card-soft">
            <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="admin-field min-w-72"><label for="workflow_id" class="text-sm font-medium">Работен процес</label><select id="workflow_id" name="workflow_id" class="input-shell"><option value="">Всички процеси</option>@foreach ($workflows as $workflow)<option value="{{ $workflow->id }}" @selected($selectedWorkflowId === $workflow->id)>{{ $workflow->name }}</option>@endforeach</select></div>
                <button class="btn-primary">Филтрирай</button>
            </form>
        </div>

        <div class="card-soft mt-8">
            @if ($runs->isEmpty())
                <div class="admin-empty-state">Все още няма реални изпълнения. Историята ще се попълни, когато към процесите бъдат включени изпълними конектори.</div>
            @else
                <div class="admin-table-wrap"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Процес</th><th class="px-4 py-3">Статус</th><th class="px-4 py-3">Събрани</th><th class="px-4 py-3">Съпоставени</th><th class="px-4 py-3">Грешки</th><th class="px-4 py-3">AI разход</th><th class="px-4 py-3">Начало</th></tr></thead><tbody>@foreach ($runs as $run)<tr class="border-t border-border/60"><td class="px-4 py-3">{{ $run->workflow?->name }}</td><td class="px-4 py-3"><span class="status-pill status-pill-{{ $run->status }}">{{ $statusLabels[$run->status] ?? $run->status }}</span></td><td class="px-4 py-3">{{ $run->records_collected }}</td><td class="px-4 py-3">{{ $run->records_matched }}</td><td class="px-4 py-3">{{ $run->error_count }}</td><td class="px-4 py-3">{{ number_format((float) $run->ai_cost, 4) }}</td><td class="px-4 py-3">{{ $run->started_at?->format('d.m.Y H:i') ?? '—' }}</td></tr>@endforeach</tbody></table></div>
                <div class="mt-8">{{ $runs->links() }}</div>
            @endif
        </div>
    </div></section>
@endsection
