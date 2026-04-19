<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'repair_request_id',
        'channel',
        'direction',
        'status',
        'provider_message_id',
        'sender_name',
        'sender_handle',
        'content',
        'payload',
        'sent_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class);
    }
}
