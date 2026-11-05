<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRun extends Model
{
    public const STATUS_LABELS = [
        'queued' => 'На опашка',
        'running' => 'Изпълнява се',
        'completed' => 'Завършен',
        'failed' => 'Грешка',
        'cancelled' => 'Отказан',
    ];

    protected $fillable = [
        'workflow_id', 'triggered_by', 'status', 'records_collected', 'records_matched',
        'records_exported', 'error_count', 'ai_cost', 'summary', 'error_message',
        'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_cost' => 'decimal:6',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
