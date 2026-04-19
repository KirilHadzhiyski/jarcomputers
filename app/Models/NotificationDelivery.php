<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'repair_request_id',
        'conversation_id',
        'channel',
        'target',
        'subject',
        'status',
        'provider_message_id',
        'response_code',
        'response_body',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
        ];
    }

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
