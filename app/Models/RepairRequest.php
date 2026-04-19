<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RepairRequest extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'city',
        'model',
        'issue',
        'preferred_contact',
        'status',
        'admin_notes',
        'source_page',
        'source_channel',
        'gdpr_consent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'gdpr_consent' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function notificationDeliveries()
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
