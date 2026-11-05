<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageRecord extends Model
{
    public const SERVICE_LABELS = [
        'ai' => 'Изкуствен интелект',
        'api' => 'Външен API',
        'scraping' => 'Web scraping',
        'storage' => 'Съхранение',
        'notification' => 'Известяване',
    ];

    protected $fillable = [
        'user_id', 'workflow_id', 'workflow_run_id', 'service_type', 'provider', 'model',
        'quantity', 'unit', 'cost', 'currency_code', 'occurred_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'cost' => 'decimal:6',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}
