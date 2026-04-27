<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    public const STATUS_LABELS = [
        'active' => 'Active',
        'lead' => 'Lead',
        'vip' => 'VIP',
        'blocked' => 'Blocked',
    ];

    public const CHANNEL_LABELS = [
        'phone' => 'Phone',
        'email' => 'Email',
        'viber' => 'Viber',
        'whatsapp' => 'WhatsApp',
        'facebook' => 'Facebook',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'preferred_channel',
        'company',
        'address',
        'status',
        'last_contacted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_contacted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(BusinessOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function displayName(): string
    {
        return trim($this->name.' '.($this->phone ? "({$this->phone})" : ''));
    }
}
