<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    public const CATEGORY_LABELS = [
        'parts' => 'Parts',
        'display' => 'Displays',
        'battery' => 'Batteries',
        'connector' => 'Connectors',
        'laptop' => 'Laptop parts',
        'gaming_pc' => 'Gaming PC components',
        'accessory' => 'Accessories',
    ];

    public const STATUS_LABELS = [
        'active' => 'Active',
        'low_stock' => 'Low stock',
        'ordered' => 'Ordered',
        'reserved' => 'Reserved',
        'archived' => 'Archived',
    ];

    protected $fillable = [
        'supplier_id',
        'name',
        'sku',
        'category',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_level',
        'unit_cost',
        'currency_code',
        'warranty_months',
        'location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'integer',
            'quantity_reserved' => 'integer',
            'reorder_level' => 'integer',
            'unit_cost' => 'decimal:2',
            'warranty_months' => 'integer',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function availableQuantity(): int
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

    public function needsReorder(): bool
    {
        return $this->availableQuantity() <= $this->reorder_level;
    }
}
