@extends('layouts.site')

@php
    $isEdit = filled($user);
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container max-w-3xl">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="section-heading">{{ $isEdit ? "Редакция на {$user->name}" : 'Нов потребител' }}</h1>
                    <p class="section-copy">Редакция на достъпа, контактните данни и статуса на акаунта.</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary-dark">Всички потребители</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" class="flex flex-col gap-5">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <label class="block text-sm font-medium text-foreground">
                        Име
                        <input class="input-shell mt-2" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Телефон
                        <input class="input-shell mt-2" type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Предпочитан контакт
                        <select class="input-shell mt-2" name="preferred_contact_channel">
                            @foreach ($contactChannels as $value => $label)
                                <option value="{{ $value }}" @selected(old('preferred_contact_channel', $user->preferred_contact_channel ?? 'email') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Роля
                        <select class="input-shell mt-2" name="role">
                            @foreach (['user' => 'User', 'admin' => 'Admin'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->role ?? 'user') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    @if ($isEdit)
                        <div class="rounded-2xl border border-border/70 bg-white/70 p-4 text-sm text-muted-foreground">
                            Потвърден имейл: <span class="font-medium text-foreground">{{ $user->hasVerifiedEmail() ? 'Да' : 'Не' }}</span>
                        </div>
                    @endif

                    <label class="block text-sm font-medium text-foreground">
                        {{ $isEdit ? 'Нова парола (по желание)' : 'Парола' }}
                        <input class="input-shell mt-2" type="password" name="password" {{ $isEdit ? '' : 'required' }}>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Потвърди паролата
                        <input class="input-shell mt-2" type="password" name="password_confirmation" {{ $isEdit ? '' : 'required' }}>
                    </label>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="btn-primary">{{ $isEdit ? 'Запази промените' : 'Създай потребител' }}</button>
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary-dark">Назад</a>
                    </div>
                </form>

                @if ($isEdit)
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary-dark" onclick="return confirm('Сигурни ли сте, че искате да изтриете този потребител?')">Изтрий</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
