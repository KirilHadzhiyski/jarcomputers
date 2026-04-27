@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Pricing',
                    'title' => 'Benchmarks',
                    'description' => 'Store benchmark prices from manual admin entry and scraper-originated sync events.',
                ])
                <a href="{{ route('admin.pricing.benchmarks.create') }}" class="btn-primary">New benchmark</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                @if ($benchmarks->isEmpty())
                    <div class="admin-empty-state">No benchmarks yet. Add the first competitor price observation to unlock analysis.</div>
                @else
                    <div class="admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Configuration</th>
                                    <th class="px-4 py-3">Market</th>
                                    <th class="px-4 py-3">Source</th>
                                    <th class="px-4 py-3">Observed</th>
                                    <th class="px-4 py-3">Method</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($benchmarks as $benchmark)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $benchmark->configuration?->name }}</td>
                                        <td class="px-4 py-3">{{ $benchmark->market?->name }}</td>
                                        <td class="px-4 py-3">{{ $benchmark->source?->name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $benchmark->observed_price, 2) }} {{ $benchmark->currency_code }}</td>
                                        <td class="px-4 py-3">{{ $inputMethodLabels[$benchmark->input_method] ?? $benchmark->input_method }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.pricing.benchmarks.edit', $benchmark) }}" class="font-medium text-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $benchmarks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
