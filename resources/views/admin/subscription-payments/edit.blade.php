@extends('layouts.admin')

@php($isEditing = filled($payment?->id))

@section('content')
    <section class="page-section"><div class="site-container max-w-5xl">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Абонаменти', 'title' => $isEditing ? 'Редакция на плащане' : 'Ново абонаментно плащане', 'description' => 'Записвайте само потвърдени billing събития или ясно означени ръчни корекции.'])
        <div class="mt-8">@include('partials.admin-nav')</div>
        <div class="card-soft">
            <form method="POST" action="{{ $isEditing ? route('admin.subscription-payments.update', $payment) : route('admin.subscription-payments.store') }}">
                @csrf @if ($isEditing) @method('PUT') @endif
                <div class="admin-form-grid">
                    <div class="admin-field admin-field-span"><label class="text-sm font-medium">Абонамент</label><select name="client_subscription_id" class="input-shell" required>@foreach ($subscriptions as $subscription)<option value="{{ $subscription->id }}" @selected((string) old('client_subscription_id', $payment?->client_subscription_id) === (string) $subscription->id)>{{ $subscription->user?->name }} — {{ $subscription->plan?->name }} ({{ $subscription->status }})</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">Сума</label><input name="amount" type="number" min="0.01" step="0.01" class="input-shell" value="{{ old('amount', $payment?->amount) }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Валута</label><input name="currency_code" maxlength="3" class="input-shell uppercase" value="{{ old('currency_code', $payment?->currency_code ?? 'EUR') }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Статус</label><select name="status" class="input-shell">@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $payment?->status ?? 'pending') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">Доставчик</label><input name="provider" class="input-shell" value="{{ old('provider', $payment?->provider) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Външна референция</label><input name="external_reference" class="input-shell" value="{{ old('external_reference', $payment?->external_reference) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Фактурирано на</label><input type="datetime-local" name="billed_at" class="input-shell" value="{{ old('billed_at', $payment?->billed_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Начало на период</label><input type="date" name="period_start" class="input-shell" value="{{ old('period_start', $payment?->period_start?->format('Y-m-d')) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Край на период</label><input type="date" name="period_end" class="input-shell" value="{{ old('period_end', $payment?->period_end?->format('Y-m-d')) }}"></div>
                </div>
                <div class="mt-8 flex gap-3 border-t pt-6"><button class="btn-primary">Запази</button><a class="btn-secondary-dark" href="{{ route('admin.subscription-payments.index') }}">Назад</a></div>
            </form>
            @if ($isEditing)
                <form method="POST" action="{{ route('admin.subscription-payments.destroy', $payment) }}" class="mt-4" onsubmit="return confirm('Да изтрия ли плащането?')">@csrf @method('DELETE')<button class="text-sm font-medium text-rose-600">Изтрий плащането</button></form>
            @endif
        </div>
    </div></section>
@endsection
