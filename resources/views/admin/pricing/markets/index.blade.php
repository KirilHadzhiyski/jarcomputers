@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Pricing',
                    'title' => 'Markets & VAT',
                    'description' => 'Maintain the target country settings that drive VAT-aware analysis.',
                ])
                <a href="{{ route('admin.pricing.markets.create') }}" class="btn-primary">New market</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                @if ($markets->isEmpty())
                    <div class="admin-empty-state">No pricing markets yet. Add Greece, Romania, and any other target countries here.</div>
                @else
                    <div class="admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Market</th>
                                    <th class="px-4 py-3">Currency</th>
                                    <th class="px-4 py-3">VAT</th>
                                    <th class="px-4 py-3">BGN FX</th>
                                    <th class="px-4 py-3">Active</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($markets as $market)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $market->name }} ({{ $market->code }})</td>
                                        <td class="px-4 py-3">{{ $market->currency_code }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $market->vat_rate, 2) }}%</td>
                                        <td class="px-4 py-3">{{ number_format((float) $market->exchange_rate_to_bgn, 4) }}</td>
                                        <td class="px-4 py-3">{{ $market->is_active ? 'Yes' : 'No' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.pricing.markets.edit', $market) }}" class="font-medium text-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $markets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
