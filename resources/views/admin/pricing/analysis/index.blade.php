@extends('layouts.site')

@php
    $statusClass = match ($analysis?->viability_status) {
        'viable' => 'status-pill-viable',
        'borderline' => 'status-pill-borderline',
        'not_viable' => 'status-pill-not_viable',
        default => 'status-pill-draft',
    };
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Pricing',
                'title' => 'Can this configuration be sold competitively in the selected market?',
                'description' => 'Run the internal VAT-aware viability engine using the latest active benchmark records.',
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ route('admin.pricing.analysis.store') }}" class="admin-form-grid">
                    @csrf
                    <div class="admin-field">
                        <label for="pricing_configuration_id" class="text-sm font-medium text-foreground">Configuration</label>
                        <select id="pricing_configuration_id" name="pricing_configuration_id" class="input-shell" required>
                            @foreach ($configurations as $configuration)
                                <option value="{{ $configuration->id }}" @selected((string) old('pricing_configuration_id', $selectedConfiguration?->id) === (string) $configuration->id)>{{ $configuration->name }} ({{ $configuration->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="pricing_market_id" class="text-sm font-medium text-foreground">Market</label>
                        <select id="pricing_market_id" name="pricing_market_id" class="input-shell" required>
                            @foreach ($markets as $market)
                                <option value="{{ $market->id }}" @selected((string) old('pricing_market_id', $selectedMarket?->id) === (string) $market->id)>{{ $market->name }} ({{ $market->currency_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field admin-field-span">
                        <button type="submit" class="btn-primary">Run analysis</button>
                    </div>
                </form>
            </div>

            @if (! $analysis)
                <div class="card-soft mt-8">
                    <div class="admin-empty-state">
                        No stored analysis for this configuration and market yet. Run the calculation after adding benchmark data.
                    </div>
                </div>
            @else
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <span class="status-pill {{ $statusClass }}">{{ $statusLabels[$analysis->viability_status] ?? $analysis->viability_status }}</span>
                    <p class="text-sm text-muted-foreground">Calculated at {{ optional($analysis->calculated_at)->format('d.m.Y H:i') }}</p>
                </div>

                <div class="mt-6 admin-shell-grid">
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Benchmark offers</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ $analysis->reference_benchmark_count }}</p>
                    </article>
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Average benchmark</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ number_format((float) $analysis->avg_benchmark_price, 2) }} {{ $selectedMarket?->currency_code }}</p>
                    </article>
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Margin amount</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ number_format((float) $analysis->target_margin_amount, 2) }} BGN</p>
                    </article>
                    <article class="admin-kpi-card">
                        <p class="admin-stat-label">Margin percent</p>
                        <p class="mt-3 text-3xl font-semibold text-foreground">{{ number_format((float) $analysis->target_margin_percent, 2) }}%</p>
                    </article>
                </div>

                <div class="mt-8 grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
                    <div class="card-soft">
                        <h2 class="text-xl font-semibold text-foreground">VAT-aware breakdown</h2>
                        <div class="mt-6 admin-form-grid">
                            <div class="admin-kpi-card">
                                <p class="admin-stat-label">Base price</p>
                                <p class="mt-3 text-2xl font-semibold text-foreground">{{ number_format((float) $selectedConfiguration?->base_price_bgn, 2) }} BGN</p>
                            </div>
                            <div class="admin-kpi-card">
                                <p class="admin-stat-label">Base in market currency</p>
                                <p class="mt-3 text-2xl font-semibold text-foreground">{{ number_format((float) $analysis->base_price_market_currency, 2) }} {{ $selectedMarket?->currency_code }}</p>
                            </div>
                            <div class="admin-kpi-card">
                                <p class="admin-stat-label">Suggested ex. VAT</p>
                                <p class="mt-3 text-2xl font-semibold text-foreground">{{ number_format((float) $analysis->suggested_price_excluding_vat, 2) }} {{ $selectedMarket?->currency_code }}</p>
                            </div>
                            <div class="admin-kpi-card">
                                <p class="admin-stat-label">Suggested inc. VAT</p>
                                <p class="mt-3 text-2xl font-semibold text-foreground">{{ number_format((float) $analysis->suggested_price_including_vat, 2) }} {{ $selectedMarket?->currency_code }}</p>
                            </div>
                            <div class="admin-kpi-card admin-field-span">
                                <p class="admin-stat-label">Suggested BGN equivalent</p>
                                <p class="mt-3 text-2xl font-semibold text-foreground">{{ number_format((float) $analysis->suggested_price_bgn_equivalent, 2) }} BGN</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-soft">
                        <h2 class="text-xl font-semibold text-foreground">Decision context</h2>
                        <div class="mt-6 space-y-4">
                            <article class="rounded-2xl border border-border/70 bg-background/75 p-4">
                                <p class="admin-stat-label">Competition note</p>
                                <p class="mt-2 text-sm leading-6 text-muted-foreground">{{ $analysis->competition_note }}</p>
                            </article>
                            <article class="rounded-2xl border border-border/70 bg-background/75 p-4">
                                <p class="admin-stat-label">Analysis summary</p>
                                <p class="mt-2 text-sm leading-6 text-muted-foreground">{{ $analysis->analysis_summary }}</p>
                            </article>
                        </div>
                    </div>
                </div>

                <div class="card-soft mt-8">
                    <h2 class="text-xl font-semibold text-foreground">Benchmarks used</h2>
                    <div class="mt-6 admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Source</th>
                                    <th class="px-4 py-3">Observed</th>
                                    <th class="px-4 py-3">Availability</th>
                                    <th class="px-4 py-3">Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($benchmarks as $benchmark)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $benchmark->source?->name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $benchmark->observed_price, 2) }} {{ $benchmark->currency_code }}</td>
                                        <td class="px-4 py-3">{{ $benchmark->availability_text ?: 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $benchmark->inputMethodLabel() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-muted-foreground">No benchmark rows are available for this selection.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
