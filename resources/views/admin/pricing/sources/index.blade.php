@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Pricing',
                    'title' => 'Sources',
                    'description' => 'Define the benchmark sources used for manual entry today and scraper sync later.',
                ])
                <a href="{{ route('admin.pricing.sources.create') }}" class="btn-primary">New source</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                @if ($sources->isEmpty())
                    <div class="admin-empty-state">No sources yet. Add bestprice.gr, Skroutz, eMAG, or any future benchmark channel.</div>
                @else
                    <div class="admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Source</th>
                                    <th class="px-4 py-3">Market</th>
                                    <th class="px-4 py-3">Mode</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sources as $source)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $source->name }}</td>
                                        <td class="px-4 py-3">{{ $source->market?->name }}</td>
                                        <td class="px-4 py-3">{{ $inputTypeLabels[$source->input_type] ?? $source->input_type }}</td>
                                        <td class="px-4 py-3">{{ $source->is_active ? 'Active' : 'Paused' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.pricing.sources.edit', $source) }}" class="font-medium text-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $sources->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
