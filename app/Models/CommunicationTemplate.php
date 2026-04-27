<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    public const CHANNEL_LABELS = [
        'email' => 'Email',
        'viber' => 'Viber',
        'whatsapp' => 'WhatsApp',
        'facebook' => 'Facebook',
        'sms' => 'SMS',
    ];

    public const EVENT_LABELS = [
        'order_received' => 'Order received',
        'diagnostics_ready' => 'Diagnostics ready',
        'waiting_parts' => 'Waiting parts',
        'order_ready' => 'Order ready',
        'payment_reminder' => 'Payment reminder',
        'review_request' => 'Review request',
    ];

    protected $fillable = [
        'name',
        'channel',
        'event_key',
        'subject',
        'body',
        'is_active',
        'last_used_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }
}
