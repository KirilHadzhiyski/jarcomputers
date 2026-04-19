@extends('layouts.site')

@php
    $isEdit = filled($ticket);
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container max-w-6xl">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="section-heading">{{ $isEdit ? "Редакция на поръчка #{$ticket->id}" : 'Нова поръчка' }}</h1>
                    <p class="section-copy">Редактирайте статуса, добавяйте клиентски update-и и изпращайте имейл, когато поръчката е готова.</p>
                </div>
                <a href="{{ route('admin.tickets.index') }}" class="btn-secondary-dark">Всички поръчки</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="card-soft">
                    <form method="POST" action="{{ $isEdit ? route('admin.tickets.update', $ticket) : route('admin.tickets.store') }}" class="flex flex-col gap-5">
                        @csrf
                        @if ($isEdit)
                            @method('PUT')
                        @endif

                        <label class="block text-sm font-medium text-foreground">
                            Потребител
                            <select class="input-shell mt-2" name="user_id">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $ticket->user_id ?? null) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Тема
                            <input class="input-shell mt-2" type="text" name="subject" value="{{ old('subject', $ticket->subject ?? '') }}" required>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Модел устройство
                            <input class="input-shell mt-2" type="text" name="device_model" value="{{ old('device_model', $ticket->device_model ?? '') }}">
                        </label>

                        <div class="grid gap-5 md:grid-cols-3">
                            <label class="block text-sm font-medium text-foreground">
                                Категория
                                <select class="input-shell mt-2" name="category">
                                    @foreach ($categoryLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('category', $ticket->category ?? 'repair') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block text-sm font-medium text-foreground">
                                Приоритет
                                <select class="input-shell mt-2" name="priority">
                                    @foreach ($priorityLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('priority', $ticket->priority ?? 'normal') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block text-sm font-medium text-foreground">
                                Статус
                                <select class="input-shell mt-2" name="status">
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $ticket->status ?? 'open') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <label class="block text-sm font-medium text-foreground">
                            Описание
                            <textarea class="input-shell mt-2 min-h-40 resize-none" name="description" rows="6" required>{{ old('description', $ticket->description ?? '') }}</textarea>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Административни бележки
                            <textarea class="input-shell mt-2 min-h-32 resize-none" name="admin_notes" rows="5">{{ old('admin_notes', $ticket->admin_notes ?? '') }}</textarea>
                        </label>

                        <div class="rounded-2xl border border-border/70 bg-white/70 p-5">
                            <h2 class="text-base font-semibold text-foreground">Update към клиента</h2>
                            <p class="mt-2 text-sm text-muted-foreground">Този текст ще се покаже в клиентския портал. Ако отметнете известието, ще бъде изпратен и по имейл.</p>

                            <label class="mt-4 block text-sm font-medium text-foreground">
                                Съобщение към клиента
                                <textarea class="input-shell mt-2 min-h-28 resize-none" name="customer_message" rows="4">{{ old('customer_message') }}</textarea>
                            </label>

                            <label class="mt-4 inline-flex items-center gap-3 text-sm text-muted-foreground">
                                <input type="checkbox" name="notify_customer" value="1" @checked(old('notify_customer'))>
                                Изпрати имейл известие до клиента
                            </label>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="btn-primary">{{ $isEdit ? 'Запази промените' : 'Създай поръчка' }}</button>
                            <a href="{{ route('admin.tickets.index') }}" class="btn-secondary-dark">Назад</a>
                        </div>
                    </form>

                    @if ($isEdit)
                        <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" class="mt-4">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary-dark" onclick="return confirm('Сигурни ли сте, че искате да изтриете тази поръчка?')">Изтрий</button>
                        </form>
                    @endif
                </div>

                <div class="space-y-6">
                    @if ($isEdit)
                        <div class="card-soft">
                            <h2 class="text-lg font-semibold text-foreground">Контакт с клиента</h2>
                            <div class="mt-5 flex flex-col gap-3 text-sm text-muted-foreground">
                                <p><span class="font-medium text-foreground">Име:</span> {{ $ticket->user->name }}</p>
                                <p><span class="font-medium text-foreground">Имейл:</span> {{ $ticket->user->email }}</p>
                                <p><span class="font-medium text-foreground">Телефон:</span> {{ $ticket->user->phone ?: 'Няма зададен' }}</p>
                                <p><span class="font-medium text-foreground">Предпочитан контакт:</span> {{ $ticket->user->preferredContactLabel() }}</p>
                            </div>
                        </div>

                        <div class="card-soft">
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-foreground">История</h2>
                                <span class="text-sm text-muted-foreground">{{ $ticket->updates->count() }} update-а</span>
                            </div>

                            @if ($ticket->updates->isEmpty())
                                <p class="mt-6 text-sm text-muted-foreground">Все още няма история по тази поръчка.</p>
                            @else
                                <div class="mt-6 flex flex-col gap-4">
                                    @foreach ($ticket->updates as $update)
                                        <div class="rounded-2xl border border-border/70 bg-white/70 p-4">
                                            <div class="flex flex-wrap items-center justify-between gap-3">
                                                <p class="text-sm font-medium text-foreground">{{ $update->author?->name ?? 'Система' }}</p>
                                                <p class="text-xs text-muted-foreground">{{ $update->created_at->format('d.m.Y H:i') }}</p>
                                            </div>
                                            @if ($update->new_status)
                                                <p class="mt-3 text-xs uppercase tracking-[0.18em] text-primary">Статус: {{ $statusLabels[$update->new_status] ?? $update->new_status }}</p>
                                            @endif
                                            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-muted-foreground">{{ $update->message }}</p>
                                            @if ($update->emailed_at)
                                                <p class="mt-3 text-xs text-primary">Имейл изпратен на {{ $update->emailed_at->format('d.m.Y H:i') }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
