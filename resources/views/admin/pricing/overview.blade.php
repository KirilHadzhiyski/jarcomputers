@extends('layouts.site')

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
                    <h2 class="text-xl font-semibold text-foreground">Manual sync trigger</h2>
                    <p class="mt-2 text-sm text-muted-foreground">This stores deterministic sample benchmark data as scraper input, so the admin flow is ready before live crawlers are connected.</p>

                    @if ($configurations->isEmpty() || $markets->isEmpty() || $sources->isEmpty())
                        <div class="admin-empty-state mt-6">
                            Add at least one configuration, market, and source before running a sync.
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.pricing.sync.store') }}" class="mt-6 space-y-5">
                            @csrf
                            <div class="admin-field">
                                <label for="sync-pricing-configuration-id" class="text-sm font-medium text-foreground">Configuration</label>
                                <select id="sync-pricing-configuration-id" name="pricing_configuration_id" class="input-shell">
                                    @foreach ($configurations as $configuration)
                                        <option value="{{ $configuration->id }}">{{ $configuration->name }} ({{ $configuration->sku }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admin-field">
                                <label for="sync-pricing-market-id" class="text-sm font-medium text-foreground">Market</label>
                                <select id="sync-pricing-market-id" name="pricing_market_id" class="input-shell">
                                    @foreach ($markets as $market)
                                        <option value="{{ $market->id }}">{{ $market->name }} ({{ $market->currency_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admin-field">
                                <label for="sync-pricing-source-id" class="text-sm font-medium text-foreground">Source</label>
                                <select id="sync-pricing-source-id" name="pricing_source_id" class="input-shell">
                                    @foreach ($sources as $source)
                                        <option value="{{ $source->id }}">{{ $source->name }} / {{ $source->market?->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn-primary w-full">Run sample sync</button>
                        </form>
                    @endif
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
