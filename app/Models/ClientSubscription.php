<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientSubscription extends Model
{
    public const STATUS_LABELS = [
        'trial' => 'Пробен период',
        'active' => 'Активен',
        'past_due' => 'Просрочено плащане',
        'paused' => 'Временно спрян',
        'cancelled' => 'Прекратен',
        'expired' => 'Изтекъл',
    ];

    protected $fillable = [
        'user_id', 'subscription_plan_id', 'status', 'starts_at', 'trial_ends_at',
        'renews_at', 'ends_at', 'external_provider', 'external_customer_id',
        'external_subscription_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'renews_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
