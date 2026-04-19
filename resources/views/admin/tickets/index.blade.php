@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="section-heading">Admin поръчки</h1>
                    <p class="section-copy">Пълен контрол върху сервизните поръчки и клиентските известия.</p>
                </div>
                <a href="{{ route('admin.tickets.create') }}" class="btn-primary">Нова поръчка</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Потребител</th>
                            <th class="px-4 py-3">Тема</th>
                            <th class="px-4 py-3">Приоритет</th>
                            <th class="px-4 py-3">Статус</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr class="border-t">
                                <td class="px-4 py-3">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3">{{ $ticket->user->name }}</td>
                                <td class="px-4 py-3">{{ $ticket->subject }}</td>
                                <td class="px-4 py-3">{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</td>
                                <td class="px-4 py-3">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.tickets.edit', $ticket) }}" class="font-medium text-primary">Редакция</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $tickets->links() }}
            </div>
        </div>
    </section>
@endsection
