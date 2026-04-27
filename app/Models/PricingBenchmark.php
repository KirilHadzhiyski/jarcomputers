<?php

namespace App\Models;

use Database\Factories\PricingBenchmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingBenchmark extends Model
{
    /** @use HasFactory<PricingBenchmarkFactory> */
    use HasFactory;

    public const INPUT_METHOD_LABELS = [
        'manual' => 'Ръчно',
        'scraper' => 'Scraper',
    ];

    protected $fillable = [
        'pricing_configuration_id',
        'pricing_market_id',
        'pricing_source_id',
        'observed_price',
        'currency_code',
        'price_includes_vat',
        'price_excluding_vat',
        'price_including_vat',
        'availability_text',
        'competitor_name',
        'product_title',
        'product_url',
        'input_method',
        'is_active',
        'collected_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'observed_price' => 'decimal:2',
            'price_includes_vat' => 'boolean',
            'price_excluding_vat' => 'decimal:2',
            'price_including_vat' => 'decimal:2',
            'is_active' => 'boolean',
            'collected_at' => 'datetime',
        ];
    }

    protected static function newFactory(): PricingBenchmarkFactory
    {
        return PricingBenchmarkFactory::new();
    }

    public function configuration()
    {
        return $this->belongsTo(PricingConfiguration::class, 'pricing_configuration_id');
    }

    public function market()
    {
        return $this->belongsTo(PricingMarket::class, 'pricing_market_id');
    }

    public function source()
    {
        return $this->belongsTo(PricingSource::class, 'pricing_source_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inputMethodLabel(): string
    {
        return self::INPUT_METHOD_LABELS[$this->input_method] ?? $this->input_method;
    }
}
