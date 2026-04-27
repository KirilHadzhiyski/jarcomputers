<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessOrder extends Model
{
    public const STATUS_LABELS = [
        'received' => 'Received',
        'diagnostics' => 'Diagnostics',
        'waiting_parts' => 'Waiting parts',
        'in_progress' => 'In progress',
        'ready' => 'Ready',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public const PRIORITY_LABELS = [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const SERVICE_TYPE_LABELS = [
        'repair' => 'Repair',
        'diagnostics' => 'Diagnostics',
        'warranty' => 'Warranty',
        'gaming_pc' => 'Gaming PC build',
        'consultation' => 'Consultation',
    ];

    protected $fillable = [
        'user_id',
        'customer_profile_id',
        'ticket_id',
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'device_or_product',
        'service_type',
        'status',
        'priority',
        'estimated_total',
        'amount_paid',
        'due_at',
        'ready_at',
        'picked_up_at',
        'contact_next_step',
        'notes',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_at' => 'datetime',
            'ready_at' => 'datetime',
            'picked_up_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->estimated_total - (float) $this->amount_paid);
    }
}
