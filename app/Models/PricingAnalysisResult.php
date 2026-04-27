<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingAnalysisResult extends Model
{
    public const STATUS_LABELS = [
        'viable' => 'Viable',
        'borderline' => 'Borderline',
        'not_viable' => 'Not viable',
    ];

    protected $fillable = [
        'pricing_configuration_id',
        'pricing_market_id',
        'reference_benchmark_count',
        'avg_benchmark_price',
        'min_benchmark_price',
        'max_benchmark_price',
        'base_price_market_currency',
        'suggested_price_excluding_vat',
        'suggested_price_including_vat',
        'suggested_price_bgn_equivalent',
        'target_margin_amount',
        'target_margin_percent',
        'viability_status',
        'competition_note',
        'analysis_summary',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'avg_benchmark_price' => 'decimal:2',
            'min_benchmark_price' => 'decimal:2',
            'max_benchmark_price' => 'decimal:2',
            'base_price_market_currency' => 'decimal:2',
            'suggested_price_excluding_vat' => 'decimal:2',
            'suggested_price_including_vat' => 'decimal:2',
            'suggested_price_bgn_equivalent' => 'decimal:2',
            'target_margin_amount' => 'decimal:2',
            'target_margin_percent' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function configuration()
    {
        return $this->belongsTo(PricingConfiguration::class, 'pricing_configuration_id');
    }

    public function market()
    {
        return $this->belongsTo(PricingMarket::class, 'pricing_market_id');
    }

    public function viabilityLabel(): string
    {
        return self::STATUS_LABELS[$this->viability_status] ?? $this->viability_status;
    }
}
