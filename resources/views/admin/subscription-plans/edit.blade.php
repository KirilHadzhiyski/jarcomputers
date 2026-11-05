@extends('layouts.admin')

@php($isEditing = filled($plan?->id))

@section('content')
    <section class="page-section"><div class="site-container max-w-5xl">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Абонаменти', 'title' => $isEditing ? "Редакция: {$plan->name}" : 'Нов абонаментен план', 'description' => 'Определете търговските условия и техническите лимити на плана.'])
        <div class="mt-8">@include('partials.admin-nav')</div>
        <div class="card-soft">
            <form method="POST" action="{{ $isEditing ? route('admin.subscription-plans.update', $plan) : route('admin.subscription-plans.store') }}">
                @csrf @if ($isEditing) @method('PUT') @endif
                <div class="admin-form-grid">
                    <div class="admin-field"><label class="text-sm font-medium">Име</label><input name="name" class="input-shell" value="{{ old('name', $plan?->name) }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Код</label><input name="code" class="input-shell" value="{{ old('code', $plan?->code) }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Месечна цена</label><input name="monthly_price" type="number" step="0.01" min="0" class="input-shell" value="{{ old('monthly_price', $plan?->monthly_price ?? 0) }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Валута</label><input name="currency_code" maxlength="3" class="input-shell uppercase" value="{{ old('currency_code', $plan?->currency_code ?? 'EUR') }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Лимит процеси</label><input name="workflow_limit" type="number" min="0" class="input-shell" value="{{ old('workflow_limit', $plan?->workflow_limit ?? 1) }}" required><p class="mt-2 text-xs text-muted-foreground">0 означава неограничен брой процеси.</p></div>
                    <div class="admin-field"><label class="text-sm font-medium">Записи месечно</label><input name="records_per_month" type="number" min="0" class="input-shell" value="{{ old('records_per_month', $plan?->records_per_month ?? 1000) }}" required><p class="mt-2 text-xs text-muted-foreground">0 означава неограничен брой обработени записи.</p></div>
                    <div class="admin-field"><label class="text-sm font-medium">AI бюджет</label><input name="ai_budget" type="number" step="0.01" min="0" class="input-shell" value="{{ old('ai_budget', $plan?->ai_budget ?? 0) }}" required><p class="mt-2 text-xs text-muted-foreground">Месечен бюджет във валутата на плана; 0 означава без приложен лимит.</p></div>
                    <div class="admin-field"><label class="flex items-center gap-3 text-sm font-medium"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan?->is_active ?? true))> Активен план</label></div>
                    <div class="admin-field admin-field-span"><label class="text-sm font-medium">Функции — една на ред</label><textarea name="features" rows="5" class="input-shell h-auto">{{ old('features', $plan ? implode("\n", $plan->features ?? []) : '') }}</textarea></div>
                    <div class="admin-field admin-field-span"><label class="text-sm font-medium">Описание</label><textarea name="description" rows="5" class="input-shell h-auto">{{ old('description', $plan?->description) }}</textarea></div>
                </div>
                <div class="mt-8 flex gap-3 border-t pt-6"><button class="btn-primary">Запази</button><a class="btn-secondary-dark" href="{{ route('admin.subscription-plans.index') }}">Назад</a></div>
            </form>
            @if ($isEditing)
                <form method="POST" action="{{ route('admin.subscription-plans.destroy', $plan) }}" class="mt-4" onsubmit="return confirm('Да изтрия ли плана?')">@csrf @method('DELETE')<button class="text-sm font-medium text-rose-600">Изтрий плана</button></form>
            @endif
        </div>
    </div></section>
@endsection
