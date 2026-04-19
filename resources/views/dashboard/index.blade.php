@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="section-heading">Моят профил</h1>
                    <p class="section-copy">Оттук следите поръчките си, поддържате данните за контакт и виждате кога устройство е готово.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tickets.create') }}" class="btn-primary">Нова поръчка</a>
                    <a href="{{ route('tickets.index') }}" class="btn-secondary-dark">Всички поръчки</a>
                </div>
            </div>

            <div class="mt-10 grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <div class="card-soft">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold text-foreground">Профил и контакт</h2>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">Имейл потвърден</span>
                    </div>

                    <form method="POST" action="{{ route('dashboard.profile.update') }}" class="mt-6 flex flex-col gap-5">
                        @csrf
                        @method('PUT')

                        <label class="block text-sm font-medium text-foreground">
                            Име
                            <input class="input-shell mt-2" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Имейл
                            <input class="input-shell mt-2 bg-slate-50" type="email" value="{{ auth()->user()->email }}" disabled>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Телефон
                            <input class="input-shell mt-2" type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Предпочитан контакт
                            <select class="input-shell mt-2" name="preferred_contact_channel">
                                @foreach ($contactChannels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('preferred_contact_channel', auth()->user()->preferred_contact_channel) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="rounded-2xl border border-border/70 bg-white/70 p-4 text-sm text-muted-foreground">
                            Ще използваме тези данни, когато поръчката ви е готова или има нужда от уточнение.
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="btn-primary">Запази профила</button>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark">Admin панел</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="card-soft">
                            <p class="text-sm text-muted-foreground">Общо поръчки</p>
                            <p class="mt-3 text-3xl font-semibold text-foreground">{{ $ticketStats->sum() }}</p>
                        </div>
                        <div class="card-soft">
                            <p class="text-sm text-muted-foreground">В процес</p>
                            <p class="mt-3 text-3xl font-semibold text-foreground">{{ $ticketStats->get('in_progress', 0) }}</p>
                        </div>
                        <div class="card-soft sm:col-span-2 xl:col-span-1">
                            <p class="text-sm text-muted-foreground">Готови за взимане</p>
                            <p class="mt-3 text-3xl font-semibold text-foreground">{{ $ticketStats->get('ready_for_pickup', 0) }}</p>
                        </div>
                    </div>

                    <div class="card-soft">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-xl font-semibold text-foreground">Последни поръчки</h2>
                            <a href="{{ route('tickets.index') }}" class="text-sm font-medium text-primary">Виж всички</a>
                        </div>

                        @if ($tickets->isEmpty())
                            <p class="mt-6 text-sm text-muted-foreground">Все още нямате подадени поръчки.</p>
                        @else
                            <div class="mt-6 flex flex-col gap-4">
                                @foreach ($tickets as $ticket)
                                    <a href="{{ route('tickets.show', $ticket) }}" class="card-service block p-5">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <h3 class="text-base font-semibold text-foreground">#{{ $ticket->id }} · {{ $ticket->subject }}</h3>
                                                <p class="mt-2 text-sm text-muted-foreground">{{ $ticket->device_model ?: 'Без модел' }}</p>
                                            </div>
                                            <span class="rounded-full bg-secondary px-3 py-1 text-xs font-medium text-foreground">{{ $ticket->statusLabel() }}</span>
                                        </div>
                                        <p class="mt-3 text-sm text-muted-foreground">Последна активност: {{ $ticket->updated_at->format('d.m.Y H:i') }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
