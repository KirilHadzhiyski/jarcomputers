@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Operations',
                    'title' => 'Business operations cockpit',
                    'description' => 'The operating system for the service business: orders, customers, stock, payments, services, messages, reviews, SEO, and reports.',
                ])
                <a href="{{ route('admin.business.reports') }}" class="btn-primary">Open reports</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="admin-shell-grid">
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Open orders</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ $summary['open_order_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Ready orders</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ $summary['ready_order_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Low stock risks</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ $summary['low_stock_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Unpaid amount</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ number_format((float) $summary['unpaid_amount'], 2) }} BGN</p>
                </article>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($resources as $resource)
                    <a href="{{ route('admin.business.index', $resource['key']) }}" class="business-module-card">
                        <span class="business-module-kicker">Business module</span>
                        <strong class="mt-3 block text-xl text-foreground">{{ $resource['title'] }}</strong>
                        <span class="mt-3 block text-sm leading-6 text-muted-foreground">{{ $resource['description'] }}</span>
                    </a>
                @endforeach

                <a href="{{ route('admin.business.reports') }}" class="business-module-card business-module-card-featured">
                    <span class="business-module-kicker">Decision layer</span>
                    <strong class="mt-3 block text-xl text-foreground">Business reports</strong>
                    <span class="mt-3 block text-sm leading-6 text-muted-foreground">Revenue, stock risk, order status, ready work, unpaid balances, and launch signals.</span>
                </a>
            </div>
        </div>
    </section>
@endsection
