<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Очаква плащане',
        'paid' => 'Платено',
        'failed' => 'Неуспешно',
        'refunded' => 'Възстановено',
        'cancelled' => 'Отказано',
    ];

    protected $fillable = [
        'client_subscription_id', 'user_id', 'amount', 'currency_code', 'status',
        'provider', 'external_reference', 'billed_at', 'period_start', 'period_end', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billed_at' => 'datetime',
            'period_start' => 'date',
            'period_end' => 'date',
            'metadata' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ClientSubscription::class, 'client_subscription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
