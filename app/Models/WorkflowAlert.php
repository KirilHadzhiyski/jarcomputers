<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAlert extends Model
{
    public const SEVERITY_LABELS = [
        'info' => 'Информация',
        'warning' => 'Предупреждение',
        'critical' => 'Критичен',
    ];

    public const STATUS_LABELS = [
        'open' => 'Отворен',
        'acknowledged' => 'Прегледан',
        'resolved' => 'Решен',
    ];

    protected $fillable = [
        'workflow_id', 'workflow_run_id', 'user_id', 'type', 'severity', 'status',
        'title', 'message', 'context', 'occurred_at', 'acknowledged_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'occurred_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
