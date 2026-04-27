@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <h1 class="section-heading">Admin панел</h1>
            <p class="section-copy">Управление на потребители, поръчки, клиентски известия и pricing intelligence.</p>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="admin-shell-grid">
                <div class="card-soft">
                    <p class="text-sm text-muted-foreground">Общо поръчки</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $ticketCount }}</p>
                </div>
                <div class="card-soft">
                    <p class="text-sm text-muted-foreground">Нови поръчки</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $openTicketCount }}</p>
                </div>
                <div class="card-soft">
                    <p class="text-sm text-muted-foreground">Готови за взимане</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $readyTicketCount }}</p>
                </div>
                <div class="card-soft">
                    <p class="text-sm text-muted-foreground">Потребители</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $userCount }}</p>
                </div>
            </div>

            <div class="mt-8 card-soft">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-foreground">Pricing intelligence</h2>
                        <p class="mt-2 text-sm text-muted-foreground">New internal module for configurations, markets, benchmark sources, and cross-border viability analysis.</p>
                    </div>
                    <a href="{{ route('admin.pricing.dashboard') }}" class="btn-primary">Open pricing module</a>
                </div>

                <div class="mt-6 admin-shell-grid">
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Configurations</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ $pricingSummary['configuration_count'] }}</p>
                    </article>
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Active markets</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ $pricingSummary['active_market_count'] }}</p>
                    </article>
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Recent benchmarks</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ $pricingSummary['recent_benchmark_count'] }}</p>
                    </article>
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Viable opportunities</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ $pricingSummary['viable_count'] }}</p>
                    </article>
                </div>
            </div>

            @php
                $readinessBadgeClasses = [
                    'ready' => 'bg-emerald-500/10 text-emerald-700 ring-1 ring-emerald-500/20',
                    'warning' => 'bg-amber-500/10 text-amber-700 ring-1 ring-amber-500/20',
                    'missing' => 'bg-rose-500/10 text-rose-700 ring-1 ring-rose-500/20',
                ];
                $readinessLabels = [
                    'ready' => 'Готово',
                    'warning' => 'Внимание',
                    'missing' => 'Липсва',
                ];
            @endphp

            <div class="mt-8 grid gap-6 xl:grid-cols-[0.95fr,1.55fr]">
                <div class="card-soft">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">Launch readiness</h2>
                            <p class="mt-2 text-sm text-muted-foreground">Бърз operational преглед преди да вържем домейните, имейла, базата и admin достъпа.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                        <div class="rounded-3xl border border-emerald-500/20 bg-emerald-500/5 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-emerald-700">Готово</p>
                            <p class="mt-3 text-3xl font-semibold text-foreground">{{ $launchReadiness['summary']['ready'] }}</p>
                        </div>
                        <div class="rounded-3xl border border-amber-500/20 bg-amber-500/5 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-amber-700">Внимание</p>
                            <p class="mt-3 text-3xl font-semibold text-foreground">{{ $launchReadiness['summary']['warning'] }}</p>
                        </div>
                        <div class="rounded-3xl border border-rose-500/20 bg-rose-500/5 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-rose-700">Липсва</p>
                            <p class="mt-3 text-3xl font-semibold text-foreground">{{ $launchReadiness['summary']['missing'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-soft">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">Домейни, имейл и инфраструктура</h2>
                            <p class="mt-2 text-sm text-muted-foreground">Тук веднага се вижда какво още липсва за публичен launch.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($launchReadiness['checks'] as $check)
                            <article class="rounded-3xl border border-border/60 bg-background/80 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-foreground">{{ $check['label'] }}</h3>
                                        <p class="mt-1 text-xs text-muted-foreground">{{ $check['value'] }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $readinessBadgeClasses[$check['status']] }}">
                                        {{ $readinessLabels[$check['status']] }}
                                    </span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-muted-foreground">{{ $check['help'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card-soft mt-8">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-foreground">Последни поръчки</h2>
                    <a href="{{ route('admin.tickets.index') }}" class="text-sm font-medium text-primary">Всички поръчки</a>
                </div>

                <div class="mt-6 admin-table-wrap">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Потребител</th>
                                <th class="px-4 py-3">Тема</th>
                                <th class="px-4 py-3">Статус</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestTickets as $ticket)
                                <tr class="border-t border-border/60">
                                    <td class="px-4 py-3">#{{ $ticket->id }}</td>
                                    <td class="px-4 py-3">{{ $ticket->user->name }}</td>
                                    <td class="px-4 py-3">{{ $ticket->subject }}</td>
                                    <td class="px-4 py-3">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="font-medium text-primary">Редакция</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
