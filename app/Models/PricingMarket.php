<?php

namespace App\Models;

use Database\Factories\PricingMarketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingMarket extends Model
{
    /** @use HasFactory<PricingMarketFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'currency_code',
        'vat_rate',
        'exchange_rate_to_bgn',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'exchange_rate_to_bgn' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): PricingMarketFactory
    {
        return PricingMarketFactory::new();
    }

    public function sources()
    {
        return $this->hasMany(PricingSource::class);
    }

    public function benchmarks()
    {
        return $this->hasMany(PricingBenchmark::class)->latest('collected_at');
    }

    public function analysisResults()
    {
        return $this->hasMany(PricingAnalysisResult::class)->latest('calculated_at');
    }
}
