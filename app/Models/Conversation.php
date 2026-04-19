<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'repair_request_id',
        'channel',
        'customer_name',
        'customer_phone',
        'customer_email',
        'external_conversation_id',
        'external_user_id',
        'status',
        'last_message_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function notificationDeliveries()
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
