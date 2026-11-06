@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        @include('admin.partials.shell-header', [
            'eyebrow' => 'Админ / Мониторинг',
            'title' => 'Изпълнение #'.$run->id,
            'description' => ($run->workflow?->name ?? 'Изтрит процес').' · '.($run->started_at?->format('d.m.Y H:i') ?? 'на опашка'),
        ])
        <div class="mt-8">@include('partials.admin-nav')</div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="admin-kpi-card"><p class="admin-stat-label">Статус</p><p class="mt-3"><span class="status-pill status-pill-{{ $run->status }}">{{ $statusLabels[$run->status] ?? $run->status }}</span></p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Събрани</p><p class="mt-3 text-3xl font-semibold">{{ $run->records_collected }}</p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Съпоставени</p><p class="mt-3 text-3xl font-semibold">{{ $run->records_matched }}</p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Грешки</p><p class="mt-3 text-3xl font-semibold">{{ $run->error_count }}</p></article>
            <article class="admin-kpi-card"><p class="admin-stat-label">Опит</p><p class="mt-3 text-3xl font-semibold">{{ $run->attempt }}</p></article>
        </div>

        <div class="card-soft mt-8">
            <div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="text-xl font-semibold">Резултат</h2><p class="mt-2 text-sm text-muted-foreground">{{ $run->summary ?? 'Изпълнението още няма обобщение.' }}</p>@if ($run->error_message)<p class="mt-3 rounded-xl bg-rose-500/10 p-3 text-sm text-rose-700">{{ $run->error_message }}</p>@endif</div><a href="{{ route('admin.workflow-runs.index', ['workflow_id' => $run->workflow_id]) }}" class="btn-secondary-dark">Към историята</a></div>
            @if ($run->metrics)<pre class="mt-5 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100">{{ json_encode($run->metrics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>@endif
        </div>

        @if ($run->alerts->isNotEmpty())
            <div class="card-soft mt-8"><h2 class="text-xl font-semibold">Сигнали</h2><div class="mt-5 space-y-3">@foreach ($run->alerts as $alert)<article class="rounded-2xl border border-border/70 p-4"><div class="flex justify-between gap-3"><div><p class="font-semibold">{{ $alert->title }}</p><p class="mt-1 text-sm text-muted-foreground">{{ $alert->message }}</p></div><span class="status-pill status-pill-{{ $alert->severity }}">{{ $alertSeverityLabels[$alert->severity] ?? $alert->severity }}</span></div></article>@endforeach</div></div>
        @endif

        <div class="card-soft mt-8">
            <h2 class="text-xl font-semibold">Нормализирани записи</h2>
            @if ($records->isEmpty())
                <div class="admin-empty-state mt-5">Няма записани резултати.</div>
            @else
                <div class="admin-table-wrap mt-5"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Идентификатор</th><th class="px-4 py-3">Заглавие</th><th class="px-4 py-3">Категория</th><th class="px-4 py-3">Цена</th><th class="px-4 py-3">Наличност</th><th class="px-4 py-3">Статус</th></tr></thead><tbody>@foreach ($records as $record)<tr class="border-t border-border/60"><td class="px-4 py-3">{{ $record->sku ?? $record->ean ?? $record->gtin ?? $record->external_id ?? '—' }}</td><td class="px-4 py-3"><p class="font-medium">{{ $record->title }}</p>@if ($record->source_url)<a href="{{ $record->source_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-primary">Източник</a>@endif</td><td class="px-4 py-3">{{ $record->category ?? '—' }}</td><td class="px-4 py-3">{{ $record->price !== null ? number_format((float) $record->price, 2).' '.($record->currency_code ?? '') : '—' }}</td><td class="px-4 py-3">{{ $record->availability ?? '—' }}</td><td class="px-4 py-3">{{ $record->status }}</td></tr>@endforeach</tbody></table></div>
                <div class="mt-8">{{ $records->links() }}</div>
            @endif
        </div>
    </div></section>
@endsection
