<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public const STATUS_LABELS = [
        'open' => 'Нова',
        'in_progress' => 'В процес',
        'waiting_customer' => 'Чака клиент',
        'ready_for_pickup' => 'Готова за взимане',
        'resolved' => 'Завършена',
        'closed' => 'Затворена',
    ];

    public const CATEGORY_LABELS = [
        'repair' => 'Ремонт',
        'warranty' => 'Гаранция',
        'question' => 'Въпрос',
        'other' => 'Друго',
    ];

    public const PRIORITY_LABELS = [
        'low' => 'Нисък',
        'normal' => 'Нормален',
        'high' => 'Висок',
        'urgent' => 'Спешен',
    ];

    protected $fillable = [
        'user_id',
        'subject',
        'device_model',
        'category',
        'priority',
        'status',
        'description',
        'admin_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updates()
    {
        return $this->hasMany(TicketUpdate::class)->latest();
    }

    public function publicUpdates()
    {
        return $this->updates()->where('is_internal', false);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }

    public function priorityLabel(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }
}
