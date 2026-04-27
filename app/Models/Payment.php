<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'deposit' => 'Deposit',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ];

    public const METHOD_LABELS = [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank' => 'Bank transfer',
        'online' => 'Online',
    ];

    protected $fillable = [
        'business_order_id',
        'customer_profile_id',
        'invoice_number',
        'method',
        'status',
        'amount',
        'currency_code',
        'due_date',
        'paid_at',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function businessOrder()
    {
        return $this->belongsTo(BusinessOrder::class);
    }

    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class);
    }
}
