<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRecord extends Model
{
    protected $fillable = [
        'workflow_id', 'workflow_run_id', 'external_id', 'sku', 'ean', 'gtin', 'mpn',
        'title', 'category', 'price', 'currency_code', 'availability', 'rating',
        'promotion', 'source_url', 'fingerprint', 'status', 'confidence', 'data', 'errors',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'rating' => 'decimal:2',
            'confidence' => 'decimal:4',
            'data' => 'array',
            'errors' => 'array',
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
}
