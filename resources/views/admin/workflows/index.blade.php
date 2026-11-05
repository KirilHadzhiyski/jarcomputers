@extends('layouts.admin')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Админ / Автоматизации',
                    'title' => 'Работни процеси',
                    'description' => 'Събиране, обогатяване, съпоставяне и анализ на продуктови данни с контролиран изход към търговски канали.',
                ])
                <a href="{{ route('admin.workflows.create') }}" class="btn-primary">Нов процес</a>
            </div>

            <div class="mt-8">@include('partials.admin-nav')</div>

            <div class="admin-shell-grid">
                <article class="admin-kpi-card"><p class="admin-stat-label">Всички процеси</p><p class="mt-3 text-3xl font-semibold">{{ $stats['total'] }}</p></article>
                <article class="admin-kpi-card"><p class="admin-stat-label">Активни</p><p class="mt-3 text-3xl font-semibold">{{ $stats['active'] }}</p></article>
                <article class="admin-kpi-card"><p class="admin-stat-label">По график</p><p class="mt-3 text-3xl font-semibold">{{ $stats['scheduled'] }}</p></article>
                <article class="admin-kpi-card"><p class="admin-stat-label">С човешки преглед</p><p class="mt-3 text-3xl font-semibold">{{ $stats['needs_review'] }}</p></article>
            </div>

            <div class="card-soft mt-8">
                @if ($workflows->isEmpty())
                    <div class="admin-empty-state">
                        Няма създадени работни процеси. Създайте първия и подредете стъпките му във визуалния редактор.
                    </div>
                @else
                    <div class="admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr><th class="px-4 py-3">Процес</th><th class="px-4 py-3">Клиент</th><th class="px-4 py-3">Стъпки</th><th class="px-4 py-3">Изпълнения</th><th class="px-4 py-3">Статус</th><th class="px-4 py-3"></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($workflows as $workflow)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3"><p class="font-semibold text-foreground">{{ $workflow->name }}</p><p class="mt-1 text-xs text-muted-foreground">{{ $workflow->slug }}</p></td>
                                        <td class="px-4 py-3">{{ $workflow->client?->name ?? 'Вътрешен процес' }}</td>
                                        <td class="px-4 py-3">{{ $workflow->steps->count() }}</td>
                                        <td class="px-4 py-3">{{ $workflow->runs_count }}</td>
                                        <td class="px-4 py-3"><span class="status-pill status-pill-{{ $workflow->status }}">{{ $statusLabels[$workflow->status] ?? $workflow->status }}</span></td>
                                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.workflows.edit', $workflow) }}" class="font-medium text-primary">Редакция</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $workflows->links() }}</div>
                @endif
            </div>
        </div>
    </section>
@endsection
