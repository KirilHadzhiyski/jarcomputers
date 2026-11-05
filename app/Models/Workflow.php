<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    public const STATUS_LABELS = [
        'draft' => 'Чернова',
        'active' => 'Активен',
        'paused' => 'Временно спрян',
        'archived' => 'Архивиран',
    ];

    public const TRIGGER_LABELS = [
        'manual' => 'Ръчно стартиране',
        'schedule' => 'По график',
        'webhook' => 'Входящ webhook',
    ];

    public const SOURCE_LABELS = [
        'web_scraping' => 'Web scraping',
        'rss' => 'RSS',
        'rest_api' => 'REST API',
        'graphql' => 'GraphQL',
        'file_feed' => 'Файлов feed',
    ];

    public const APPROVAL_LABELS = [
        'manual' => 'Само ръчно',
        'review' => 'Автоматична подготовка с преглед',
        'automatic' => 'Автоматично решение',
    ];

    protected $fillable = [
        'client_user_id', 'created_by', 'updated_by', 'name', 'slug', 'description',
        'status', 'trigger_type', 'frequency_minutes', 'source_type', 'source_url',
        'category_filters', 'approval_mode', 'last_run_at', 'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'category_filters' => 'array',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('position');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class)->latest();
    }
}
