@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Reports',
                    'title' => 'Business reports',
                    'description' => 'A focused operating snapshot: revenue, order load, stock risk, ready work, and customer proof.',
                ])
                <a href="{{ route('admin.business.dashboard') }}" class="btn-secondary">Back to operations</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="admin-shell-grid">
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Paid this month</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ number_format((float) $report['summary']['paid_this_month'], 2) }} BGN</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Open orders</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ $report['summary']['open_order_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Low stock</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ $report['summary']['low_stock_count'] }}</p>
                </article>
                <article class="admin-kpi-card">
                    <p class="admin-stat-label">Average rating</p>
                    <p class="mt-3 text-3xl font-semibold text-foreground">{{ number_format((float) $report['summary']['average_rating'], 2) }}/5</p>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <div class="card-soft">
                    <h2 class="text-xl font-semibold text-foreground">Orders by status</h2>
                    <div class="mt-5 grid gap-3">
                        @forelse ($report['orders_by_status'] as $status => $count)
                            <div class="flex items-center justify-between rounded-2xl border border-border/60 bg-background/75 px-4 py-3">
                                <span class="status-pill status-pill-{{ $status }}">{{ $orderStatusLabels[$status] ?? $status }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                        @empty
                            <div class="admin-empty-state">No orders yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="card-soft">
                    <h2 class="text-xl font-semibold text-foreground">Payments by status</h2>
                    <div class="mt-5 grid gap-3">
                        @forelse ($report['payments_by_status'] as $status => $payment)
                            <div class="flex items-center justify-between rounded-2xl border border-border/60 bg-background/75 px-4 py-3">
                                <span class="status-pill status-pill-{{ $status }}">{{ $paymentStatusLabels[$status] ?? $status }}</span>
                                <strong>{{ $payment['count'] }} / {{ number_format((float) $payment['amount'], 2) }} BGN</strong>
                            </div>
                        @empty
                            <div class="admin-empty-state">No payments yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <div class="card-soft">
                    <h2 class="text-xl font-semibold text-foreground">Low stock watchlist</h2>
                    <div class="mt-5 admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Item</th>
                                    <th class="px-4 py-3">On hand</th>
                                    <th class="px-4 py-3">Reorder</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report['low_stock_items'] as $item)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $item->name }}</td>
                                        <td class="px-4 py-3">{{ $item->quantity_on_hand }}</td>
                                        <td class="px-4 py-3">{{ $item->reorder_level }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-muted-foreground">No stock risks.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-soft">
                    <h2 class="text-xl font-semibold text-foreground">Latest orders</h2>
                    <div class="mt-5 admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Order</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report['latest_orders'] as $order)
                                    <tr class="border-t border-border/60">
                                        <td class="px-4 py-3">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                                        <td class="px-4 py-3">
                                            <span class="status-pill status-pill-{{ $order->status }}">{{ $orderStatusLabels[$order->status] ?? $order->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-muted-foreground">No orders yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
