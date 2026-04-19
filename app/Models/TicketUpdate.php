<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketUpdate extends Model
{
    protected $fillable = [
        'ticket_id',
        'author_id',
        'old_status',
        'new_status',
        'message',
        'is_internal',
        'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'emailed_at' => 'datetime',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function newStatusLabel(): ?string
    {
        return $this->new_status ? (Ticket::STATUS_LABELS[$this->new_status] ?? $this->new_status) : null;
    }
}
