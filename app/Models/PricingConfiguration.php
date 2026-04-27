<?php

namespace App\Models;

use Database\Factories\PricingConfigurationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingConfiguration extends Model
{
    /** @use HasFactory<PricingConfigurationFactory> */
    use HasFactory;

    public const STATUS_LABELS = [
        'draft' => 'Чернова',
        'reviewed' => 'Прегледана',
        'approved' => 'Одобрена',
        'archived' => 'Архивирана',
    ];

    protected $fillable = [
        'name',
        'sku',
        'base_price_bgn',
        'description',
        'component_summary',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'base_price_bgn' => 'decimal:2',
        ];
    }

    protected static function newFactory(): PricingConfigurationFactory
    {
        return PricingConfigurationFactory::new();
    }

    public function benchmarks()
    {
        return $this->hasMany(PricingBenchmark::class)->latest('collected_at');
    }

    public function analysisResults()
    {
        return $this->hasMany(PricingAnalysisResult::class)->latest('calculated_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
