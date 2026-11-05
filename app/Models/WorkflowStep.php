<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    public const TYPE_LABELS = [
        'collect' => 'Събиране на данни',
        'extract' => 'Извличане на атрибути',
        'match' => 'Съпоставяне на продукти',
        'classify' => 'Класификация',
        'analyze' => 'Конкурентен анализ',
        'notify' => 'Предупреждение',
        'export' => 'Подготовка за канал',
        'publish' => 'Публикуване',
    ];

    protected $fillable = ['workflow_id', 'position', 'type', 'name', 'configuration', 'is_enabled'];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
