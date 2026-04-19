@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-5xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="section-heading">Поръчка #{{ $ticket->id }}</h1>
                    <p class="section-copy">{{ $ticket->subject }}</p>
                </div>
                <span class="rounded-full bg-secondary px-3 py-1 text-sm font-medium text-foreground">{{ $ticket->statusLabel() }}</span>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="space-y-6">
                    <div class="card-soft">
                        <h2 class="text-lg font-semibold text-foreground">Детайли</h2>
                        <div class="mt-5 flex flex-col gap-3 text-sm text-muted-foreground">
                            <p><span class="font-medium text-foreground">Категория:</span> {{ $ticket->categoryLabel() }}</p>
                            <p><span class="font-medium text-foreground">Приоритет:</span> {{ $ticket->priorityLabel() }}</p>
                            <p><span class="font-medium text-foreground">Модел:</span> {{ $ticket->device_model ?: 'Без модел' }}</p>
                            <p><span class="font-medium text-foreground">Създадена на:</span> {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
                            <p><span class="font-medium text-foreground">Предпочитан контакт:</span> {{ $ticket->user->preferredContactLabel() }}</p>
                            <p><span class="font-medium text-foreground">Телефон:</span> {{ $ticket->user->phone ?: 'Няма зададен' }}</p>
                        </div>
                    </div>

                    <div class="card-soft">
                        <h2 class="text-lg font-semibold text-foreground">Описание</h2>
                        <p class="mt-5 whitespace-pre-line text-sm leading-7 text-muted-foreground">{{ $ticket->description }}</p>
                    </div>

                    @if ($ticket->admin_notes)
                        <div class="card-soft">
                            <h2 class="text-lg font-semibold text-foreground">Административни бележки</h2>
                            <p class="mt-5 whitespace-pre-line text-sm leading-7 text-muted-foreground">{{ $ticket->admin_notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="card-soft">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-foreground">Хронология</h2>
                        <span class="text-sm text-muted-foreground">{{ $ticket->updates->count() }} обновления</span>
                    </div>

                    @if ($ticket->updates->isEmpty())
                        <p class="mt-6 text-sm text-muted-foreground">Все още няма публикувани обновления по тази поръчка.</p>
                    @else
                        <div class="mt-6 flex flex-col gap-4">
                            @foreach ($ticket->updates as $update)
                                <div class="rounded-2xl border border-border/70 bg-white/70 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-foreground">
                                            {{ $update->author?->isAdmin() ? 'Екип на сервиза' : ($update->author?->name ?? 'Система') }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">{{ $update->created_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                    @if ($update->new_status)
                                        <p class="mt-3 text-xs uppercase tracking-[0.18em] text-primary">Статус: {{ $statusLabels[$update->new_status] ?? $update->new_status }}</p>
                                    @endif
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-muted-foreground">{{ $update->message }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ route('tickets.index') }}" class="btn-secondary-dark">Назад към поръчките</a>
            </div>
        </div>
    </section>
@endsection
