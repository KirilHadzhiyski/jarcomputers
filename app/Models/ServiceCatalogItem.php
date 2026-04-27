<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCatalogItem extends Model
{
    public const CATEGORY_LABELS = [
        'repair' => 'Repair',
        'diagnostics' => 'Diagnostics',
        'iphone' => 'iPhone',
        'laptop' => 'Laptop',
        'gaming_pc' => 'Gaming PC',
        'business' => 'Business support',
    ];

    protected $fillable = [
        'title',
        'slug',
        'category',
        'base_price',
        'price_note',
        'duration_estimate',
        'warranty_terms',
        'is_featured',
        'is_published',
        'sort_order',
        'description',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
