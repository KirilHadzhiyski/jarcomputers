@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="section-heading">Моите поръчки</h1>
                    <p class="section-copy">Преглед на всички активни и приключени сервизни поръчки.</p>
                </div>
                <a href="{{ route('tickets.create') }}" class="btn-primary">Нова поръчка</a>
            </div>

            <div class="mt-10 flex flex-col gap-4">
                @forelse ($tickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="card-service block">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-foreground">#{{ $ticket->id }} · {{ $ticket->subject }}</h2>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    {{ $ticket->device_model ?: 'Без модел' }}
                                    · {{ $ticket->categoryLabel() }}
                                    · {{ $ticket->priorityLabel() }}
                                </p>
                            </div>
                            <span class="rounded-full bg-secondary px-3 py-1 text-xs font-medium text-foreground">{{ $ticket->statusLabel() }}</span>
                        </div>
                        <p class="mt-3 text-sm text-muted-foreground">Създадена на {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
                    </a>
                @empty
                    <div class="card-soft">
                        <p class="text-sm text-muted-foreground">Нямате подадени поръчки.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $tickets->links() }}
            </div>
        </div>
    </section>
@endsection
