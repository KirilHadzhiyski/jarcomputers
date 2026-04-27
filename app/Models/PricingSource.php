<?php

namespace App\Models;

use Database\Factories\PricingSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSource extends Model
{
    /** @use HasFactory<PricingSourceFactory> */
    use HasFactory;

    public const INPUT_TYPE_LABELS = [
        'manual' => 'Ръчен',
        'scraper' => 'Scraper',
        'hybrid' => 'Hybrid',
    ];

    protected $fillable = [
        'pricing_market_id',
        'name',
        'source_key',
        'base_url',
        'input_type',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): PricingSourceFactory
    {
        return PricingSourceFactory::new();
    }

    public function market()
    {
        return $this->belongsTo(PricingMarket::class, 'pricing_market_id');
    }

    public function benchmarks()
    {
        return $this->hasMany(PricingBenchmark::class)->latest('collected_at');
    }

    public function inputTypeLabel(): string
    {
        return self::INPUT_TYPE_LABELS[$this->input_type] ?? $this->input_type;
    }
}
