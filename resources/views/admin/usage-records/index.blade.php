@extends('layouts.admin')

@section('content')
    <section class="page-section"><div class="site-container">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Разходи', 'title' => 'AI и API потребление', 'description' => 'Проверим отчет по клиент, процес, доставчик, количество и реален разход.'])
        <div class="mt-8">@include('partials.admin-nav')</div>

        <div class="grid gap-6 xl:grid-cols-[0.7fr,1.3fr]">
            <div class="card-soft">
                <p class="admin-stat-label">Общ разход този месец</p>
                @forelse ($monthCosts as $currency => $total)
                    <p class="mt-3 text-3xl font-semibold">{{ number_format((float) $total, 4) }} {{ $currency }}</p>
                @empty
                    <p class="mt-3 text-3xl font-semibold">0.0000</p>
                @endforelse
                <h2 class="mt-8 text-lg font-semibold">Ръчна корекция</h2><p class="mt-2 text-sm text-muted-foreground">За миграция или потвърдена фактура. Реалните конектори ще записват автоматично.</p>
                <form method="POST" action="{{ route('admin.usage-records.store') }}" class="mt-6 flex flex-col gap-4">@csrf
                    <div class="admin-field"><label class="text-sm font-medium">Клиент</label><select name="user_id" class="input-shell"><option value="">Вътрешен разход</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">Процес</label><select name="workflow_id" class="input-shell"><option value="">Без процес</option>@foreach ($workflows as $workflow)<option value="{{ $workflow->id }}">{{ $workflow->name }}</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">Услуга</label><select name="service_type" class="input-shell">@foreach ($serviceLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="grid grid-cols-2 gap-3"><div class="admin-field"><label class="text-sm font-medium">Доставчик</label><input name="provider" class="input-shell" required></div><div class="admin-field"><label class="text-sm font-medium">Модел</label><input name="model" class="input-shell"></div></div>
                    <div class="grid grid-cols-2 gap-3"><div class="admin-field"><label class="text-sm font-medium">Количество</label><input name="quantity" type="number" step="0.000001" min="0" class="input-shell" required></div><div class="admin-field"><label class="text-sm font-medium">Единица</label><input name="unit" class="input-shell" value="request" required></div></div>
                    <div class="grid grid-cols-2 gap-3"><div class="admin-field"><label class="text-sm font-medium">Разход</label><input name="cost" type="number" step="0.000001" min="0" class="input-shell" required></div><div class="admin-field"><label class="text-sm font-medium">Валута</label><input name="currency_code" maxlength="3" class="input-shell" value="EUR" required></div></div>
                    <div class="admin-field"><label class="text-sm font-medium">Отчетено на</label><input name="occurred_at" type="datetime-local" class="input-shell" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                    <button class="btn-primary">Добави запис</button>
                </form>
            </div>

            <div class="card-soft">
                <form method="GET" class="flex gap-3"><select name="user_id" class="input-shell max-w-sm"><option value="">Всички клиенти</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>{{ $user->name }}</option>@endforeach</select><button class="btn-secondary-dark">Филтрирай</button></form>
                <div class="admin-table-wrap mt-6"><table class="min-w-full text-left text-sm"><thead class="text-muted-foreground"><tr><th class="px-4 py-3">Дата</th><th class="px-4 py-3">Клиент / процес</th><th class="px-4 py-3">Доставчик</th><th class="px-4 py-3">Количество</th><th class="px-4 py-3">Разход</th></tr></thead><tbody>
                    @forelse ($records as $record)
                        <tr class="border-t border-border/60"><td class="px-4 py-3 whitespace-nowrap">{{ $record->occurred_at?->format('d.m.Y H:i') }}</td><td class="px-4 py-3">{{ $record->user?->name ?? 'Вътрешен' }}<p class="text-xs text-muted-foreground">{{ $record->workflow?->name ?? 'Без процес' }}</p></td><td class="px-4 py-3">{{ $record->provider }}<p class="text-xs text-muted-foreground">{{ $serviceLabels[$record->service_type] ?? $record->service_type }}{{ $record->model ? " / {$record->model}" : '' }}</p></td><td class="px-4 py-3">{{ number_format((float) $record->quantity, 3) }} {{ $record->unit }}</td><td class="px-4 py-3">{{ number_format((float) $record->cost, 6) }} {{ $record->currency_code }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Няма записи за потребление.</td></tr>
                    @endforelse
                </tbody></table></div><div class="mt-8">{{ $records->links() }}</div>
            </div>
        </div>
    </div></section>
@endsection
