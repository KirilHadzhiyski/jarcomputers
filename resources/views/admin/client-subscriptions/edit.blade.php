@extends('layouts.admin')

@php($isEditing = filled($subscription?->id))

@section('content')
    <section class="page-section"><div class="site-container max-w-5xl">
        @include('admin.partials.shell-header', ['eyebrow' => 'Админ / Клиенти', 'title' => $isEditing ? 'Редакция на абонамент' : 'Нов клиентски абонамент', 'description' => 'Свържете клиент с план и задайте жизнения цикъл на абонамента.'])
        <div class="mt-8">@include('partials.admin-nav')</div>
        <div class="card-soft">
            <form method="POST" action="{{ $isEditing ? route('admin.client-subscriptions.update', $subscription) : route('admin.client-subscriptions.store') }}">
                @csrf @if ($isEditing) @method('PUT') @endif
                <div class="admin-form-grid">
                    <div class="admin-field"><label class="text-sm font-medium">Клиент</label><select name="user_id" class="input-shell" required>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) old('user_id', $subscription?->user_id) === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">План</label><select name="subscription_plan_id" class="input-shell" required>@foreach ($plans as $plan)<option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id', $subscription?->subscription_plan_id) === (string) $plan->id)>{{ $plan->name }}{{ $plan->is_active ? '' : ' (спрян)' }}</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">Статус</label><select name="status" class="input-shell">@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $subscription?->status ?? 'trial') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="admin-field"><label class="text-sm font-medium">Начало</label><input type="datetime-local" name="starts_at" class="input-shell" value="{{ old('starts_at', $subscription?->starts_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required></div>
                    <div class="admin-field"><label class="text-sm font-medium">Край на пробния период</label><input type="datetime-local" name="trial_ends_at" class="input-shell" value="{{ old('trial_ends_at', $subscription?->trial_ends_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Следващо подновяване</label><input type="datetime-local" name="renews_at" class="input-shell" value="{{ old('renews_at', $subscription?->renews_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Край</label><input type="datetime-local" name="ends_at" class="input-shell" value="{{ old('ends_at', $subscription?->ends_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Billing доставчик</label><input name="external_provider" class="input-shell" value="{{ old('external_provider', $subscription?->external_provider) }}" placeholder="Stripe, myPOS..."></div>
                    <div class="admin-field"><label class="text-sm font-medium">Външен customer ID</label><input name="external_customer_id" class="input-shell" value="{{ old('external_customer_id', $subscription?->external_customer_id) }}"></div>
                    <div class="admin-field"><label class="text-sm font-medium">Външен subscription ID</label><input name="external_subscription_id" class="input-shell" value="{{ old('external_subscription_id', $subscription?->external_subscription_id) }}"></div>
                    <div class="admin-field admin-field-span"><label class="text-sm font-medium">Бележки</label><textarea name="notes" rows="4" class="input-shell h-auto">{{ old('notes', $subscription?->notes) }}</textarea></div>
                </div>
                <div class="mt-8 flex gap-3 border-t pt-6"><button class="btn-primary">Запази</button><a class="btn-secondary-dark" href="{{ route('admin.client-subscriptions.index') }}">Назад</a></div>
            </form>
            @if ($isEditing)
                <form method="POST" action="{{ route('admin.client-subscriptions.destroy', $subscription) }}" class="mt-4" onsubmit="return confirm('Да изтрия ли абонамента?')">@csrf @method('DELETE')<button class="text-sm font-medium text-rose-600">Изтрий абонамента</button></form>
            @endif
        </div>
    </div></section>
@endsection
