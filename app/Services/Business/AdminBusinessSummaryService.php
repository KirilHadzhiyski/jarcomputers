<?php

namespace App\Services\Business;

use App\Models\BusinessOrder;
use App\Models\CustomerProfile;
use App\Models\CustomerReview;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\ServiceCatalogItem;

class AdminBusinessSummaryService
{
    public function summary(): array
    {
        $paidThisMonth = Payment::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('amount');

        return [
            'order_count' => BusinessOrder::query()->count(),
            'open_order_count' => BusinessOrder::query()->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'ready_order_count' => BusinessOrder::query()->where('status', 'ready')->count(),
            'customer_count' => CustomerProfile::query()->count(),
            'low_stock_count' => InventoryItem::query()
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->count(),
            'unpaid_amount' => Payment::query()
                ->whereIn('status', ['pending', 'deposit'])
                ->sum('amount'),
            'paid_this_month' => $paidThisMonth,
            'published_service_count' => ServiceCatalogItem::query()->where('is_published', true)->count(),
            'average_rating' => round((float) CustomerReview::query()->where('is_published', true)->avg('rating'), 2),
            'published_review_count' => CustomerReview::query()->where('is_published', true)->count(),
        ];
    }

    public function report(): array
    {
        return [
            'summary' => $this->summary(),
            'orders_by_status' => BusinessOrder::query()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->orderByDesc('aggregate')
                ->pluck('aggregate', 'status')
                ->all(),
            'payments_by_status' => Payment::query()
                ->selectRaw('status, count(*) as aggregate, sum(amount) as total_amount')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn ($row) => [$row->status => [
                    'count' => (int) $row->aggregate,
                    'amount' => (float) $row->total_amount,
                ]])
                ->all(),
            'low_stock_items' => InventoryItem::query()
                ->with('supplier')
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->orderBy('quantity_on_hand')
                ->take(8)
                ->get(),
            'latest_orders' => BusinessOrder::query()->latest()->take(8)->get(),
        ];
    }
}
