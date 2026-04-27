<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public const STATUS_LABELS = [
        'active' => 'Active',
        'backup' => 'Backup',
        'paused' => 'Paused',
        'blocked' => 'Blocked',
    ];

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'website',
        'payment_terms',
        'lead_time_days',
        'warranty_terms',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'lead_time_days' => 'integer',
        ];
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
