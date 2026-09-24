@extends('layouts.admin')

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Pricing',
                'title' => 'Pricing intelligence',
                'description' => 'Internal backoffice for gaming configurations, benchmark sources, market VAT assumptions, and viability analysis.',
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="admin-shell-grid">
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Configurations</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $summary['configuration_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Active markets</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $summary['active_market_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Active sources</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $summary['active_source_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Viable opportunities</p>
                    <p class="mt-3 text-4xl font-semibold text-foreground">{{ $summary['viable_count'] }}</p>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
                <div class="card-soft">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">Latest pricing activity</h2>
                            <p class="mt-2 text-sm text-muted-foreground">Recent benchmark syncs and the last stored analysis runs.</p>
                        </div>
                        <a href="{{ route('admin.pricing.analysis.index') }}" class="btn-primary">Open analysis</a>
                    </div>

                    <div class="mt-6 admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Configuration</th>
                                    <th class="px-4 py-3">Market</th>
                                    <th class="px-4 py-3">Margin</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestAnalyses as $analysis)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $analysis->configuration?->name }}</td>
                                        <td class="px-4 py-3">{{ $analysis->market?->name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $analysis->target_margin_percent, 2) }}%</td>
                                        <td class="px-4 py-3">
                                            <span class="status-pill status-pill-{{ $analysis->viability_status }}">{{ $analysis->viabilityLabel() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-muted-foreground">No pricing analysis has been run yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-soft">
                    <h2 class="text-xl font-semibold text-foreground">Автоматично получаване на цени</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Получавайте реални оферти от свързан източник. Анализът се обновява след успешен импорт и използва актуални данни в съответната валута.</p>
                    <a href="{{ route('admin.integrations.index') }}#integration-price_feed" class="btn-secondary-dark mt-5">Настройки на ценовия източник</a>
                    <form method="POST" action="{{ route('admin.pricing.sync.store') }}" class="mt-5">
                        @csrf
                        <button type="submit" class="btn-primary">Обнови реалните оферти</button>
                    </form>
                </div>
            </div>

            <div class="mt-8 card-soft">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-foreground">Latest benchmarks</h2>
                        <p class="mt-2 text-sm text-muted-foreground">Manual and scraper-originated competitor price observations.</p>
                    </div>
                    <a href="{{ route('admin.pricing.benchmarks.index') }}" class="btn-secondary-dark">View all benchmarks</a>
                </div>

                <div class="mt-6 admin-table-wrap">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Configuration</th>
                                <th class="px-4 py-3">Market</th>
                                <th class="px-4 py-3">Source</th>
                                <th class="px-4 py-3">Observed price</th>
                                <th class="px-4 py-3">Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestBenchmarks as $benchmark)
                                <tr class="border-t border-border/60">
                                    <td class="px-4 py-3">{{ $benchmark->configuration?->name }}</td>
                                    <td class="px-4 py-3">{{ $benchmark->market?->name }}</td>
                                    <td class="px-4 py-3">{{ $benchmark->source?->name }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $benchmark->observed_price, 2) }} {{ $benchmark->currency_code }}</td>
                                    <td class="px-4 py-3">{{ $benchmark->inputMethodLabel() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-muted-foreground">No benchmark records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
