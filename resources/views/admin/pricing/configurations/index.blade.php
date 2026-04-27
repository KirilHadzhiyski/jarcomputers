@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Pricing',
                    'title' => 'Pricing configurations',
                    'description' => 'Manage the catalog of gaming PC configurations used in pricing analysis.',
                ])
                <a href="{{ route('admin.pricing.configurations.create') }}" class="btn-primary">New configuration</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                @if ($configurations->isEmpty())
                    <div class="admin-empty-state">No pricing configurations yet. Create the first one to start the pricing workflow.</div>
                @else
                    <div class="admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">SKU</th>
                                    <th class="px-4 py-3">Base price</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($configurations as $configuration)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $configuration->name }}</td>
                                        <td class="px-4 py-3">{{ $configuration->sku }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $configuration->base_price_bgn, 2) }} BGN</td>
                                        <td class="px-4 py-3">
                                            <span class="status-pill status-pill-{{ $configuration->status }}">{{ $configuration->statusLabel() }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.pricing.configurations.edit', $configuration) }}" class="font-medium text-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $configurations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
